<?php

namespace Tests\Feature;

use App\Mail\SchoolTeacherInvitationMail;
use App\Models\SchoolTeacherInvitation;
use App\Models\SchoolTeacherRelationship;
use App\Models\TeacherProfile;
use App\Models\TeacherStudentRelationship;
use App\Models\User;
use App\Services\School\SchoolTeacherService;
use App\Services\Teacher\TeacherCapabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SchoolTeacherRelationshipTest extends TestCase
{
    use RefreshDatabase;

    private function school(string $tier = 'basic'): User
    {
        $school = User::factory()->create(['role' => 'school', 'plan' => 'free']);
        $profile = TeacherProfile::createDraftFor($school, TeacherProfile::ENTITY_SCHOOL);
        if ($tier === 'premium') {
            $profile->update(['tier' => TeacherProfile::TIER_PREMIUM]);
        }

        return $school->fresh();
    }

    public function test_full_request_approve_lifecycle(): void
    {
        Notification::fake();

        $school = $this->school();
        $teacher = User::factory()->create(['role' => 'user']);

        $this->actingAs($school)->post('/school/teacher-relationships', ['user_id' => $teacher->id])
            ->assertSessionHas('status', 'teacher-relationship-requested');

        $relationship = SchoolTeacherRelationship::firstOrFail();
        $this->assertSame(SchoolTeacherRelationship::STATUS_PENDING_TEACHER_APPROVAL, $relationship->status);

        // Teacher approves from /my-schools; a draft teacher profile is auto-created.
        $this->actingAs($teacher)->post('/my-schools/'.$relationship->id.'/approve')
            ->assertSessionHas('status', 'school-approved');

        $this->assertSame(SchoolTeacherRelationship::STATUS_ACTIVE, $relationship->fresh()->status);
        $this->assertDatabaseHas('teacher_profiles', ['user_id' => $teacher->id, 'entity_type' => 'teacher']);
    }

    public function test_teacher_can_decline_a_request(): void
    {
        Notification::fake();
        $school = $this->school();
        $teacher = User::factory()->create();
        $relationship = app(SchoolTeacherService::class)->requestExistingTeacher($school, $teacher);

        $this->actingAs($teacher)->post('/my-schools/'.$relationship->id.'/decline');

        $this->assertSame(SchoolTeacherRelationship::STATUS_DECLINED, $relationship->fresh()->status);
    }

    public function test_email_invitation_flow_creates_active_membership_and_profile(): void
    {
        Mail::fake();
        Notification::fake();

        $school = $this->school();

        $this->actingAs($school)->post('/school/teacher-invitations', ['email' => 'newteacher@example.com'])
            ->assertSessionHas('status', 'teacher-invitation-sent');

        Mail::assertQueued(SchoolTeacherInvitationMail::class);
        $invitation = SchoolTeacherInvitation::firstOrFail();

        $teacher = User::factory()->create(['email' => 'newteacher@example.com']);
        $this->actingAs($teacher)->post('/school-invitations/'.$invitation->token)
            ->assertRedirect(route('my-schools.index'));

        $this->assertSame(SchoolTeacherInvitation::STATUS_ACCEPTED, $invitation->fresh()->status);
        $this->assertDatabaseHas('school_teacher_relationships', [
            'school_id' => $school->id,
            'teacher_id' => $teacher->id,
            'status' => SchoolTeacherRelationship::STATUS_ACTIVE,
        ]);
        $this->assertTrue($teacher->fresh()->hasTeacherAccount());
    }

    public function test_membership_grants_premium_capabilities_and_revocation_removes_them(): void
    {
        Notification::fake();

        $school = $this->school();
        $teacher = User::factory()->create();
        TeacherProfile::createDraftFor($teacher); // basic tier

        // Student management is a basic capability now (quota-limited);
        // availability management is still premium-only, so it is the
        // signal for the membership-granted premium toolset.
        $caps = app(TeacherCapabilityService::class);
        $this->assertFalse($caps->canManageAvailability($teacher));
        $this->assertTrue($caps->canManageStudents($teacher));

        $service = app(SchoolTeacherService::class);
        $relationship = $service->requestExistingTeacher($school, $teacher);
        $service->approve($relationship);

        $this->assertTrue($caps->canManageAvailability(User::find($teacher->id)));

        $service->revokeBySchool($relationship->fresh());

        $this->assertFalse($caps->canManageAvailability(User::find($teacher->id)));
    }

    public function test_school_roster_aggregates_member_teachers_students(): void
    {
        Notification::fake();

        $school = $this->school();
        $teacher = User::factory()->create();
        TeacherProfile::createDraftFor($teacher);
        $student = User::factory()->create();

        $service = app(SchoolTeacherService::class);
        $service->approve($service->requestExistingTeacher($school, $teacher));

        TeacherStudentRelationship::create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'status' => TeacherStudentRelationship::STATUS_ACTIVE,
            'approved_at' => now(),
        ]);

        $this->actingAs(User::find($school->id))->get('/school/students')
            ->assertOk()
            ->assertSee($student->name);

        // Student detail opens through the member teacher's relationship.
        $this->actingAs(User::find($school->id))->get('/school/students/'.$student->id)->assertOk();
    }

    public function test_plain_teacher_cannot_see_another_teachers_student(): void
    {
        $teacherA = User::factory()->create();
        TeacherProfile::createDraftFor($teacherA)->update(['tier' => TeacherProfile::TIER_PREMIUM]);
        $teacherB = User::factory()->create();
        $student = User::factory()->create();

        TeacherStudentRelationship::create([
            'teacher_id' => $teacherB->id,
            'student_id' => $student->id,
            'status' => TeacherStudentRelationship::STATUS_ACTIVE,
            'approved_at' => now(),
        ]);

        $this->actingAs($teacherA)->get('/teacher/students/'.$student->id)->assertForbidden();
    }

    public function test_basic_school_is_blocked_at_the_plan_teacher_limit(): void
    {
        Notification::fake();
        config(['plans.school.free.max_teachers' => 2]);

        $school = $this->school();
        $service = app(SchoolTeacherService::class);

        $service->requestExistingTeacher($school, User::factory()->create());
        $service->requestExistingTeacher($school, User::factory()->create());

        $this->expectException(\InvalidArgumentException::class);
        $service->requestExistingTeacher($school, User::factory()->create());
    }

    public function test_premium_school_has_no_teacher_limit(): void
    {
        Notification::fake();
        config(['plans.school.free.max_teachers' => 2, 'plans.school.premium.max_teachers' => -1]);

        $school = $this->school('premium');
        $service = app(SchoolTeacherService::class);

        foreach (range(1, 3) as $i) {
            $service->requestExistingTeacher($school, User::factory()->create());
        }

        $this->assertSame(3, SchoolTeacherRelationship::where('school_id', $school->id)->count());
    }

    public function test_school_accounts_cannot_be_added_as_teachers(): void
    {
        $schoolA = $this->school();
        $schoolB = $this->school();

        $this->expectException(\InvalidArgumentException::class);
        app(SchoolTeacherService::class)->requestExistingTeacher($schoolA, $schoolB);
    }
}
