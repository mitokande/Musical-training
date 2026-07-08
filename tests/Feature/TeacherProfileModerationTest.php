<?php

namespace Tests\Feature;

use App\Models\TeacherProfile;
use App\Models\User;
use App\Notifications\Teacher\TeacherProfileApproved;
use App\Notifications\Teacher\TeacherProfileRejected;
use App\Notifications\Teacher\TeacherProfileSubmitted;
use App\Notifications\Teacher\TeacherProfileSuspended;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TeacherProfileModerationTest extends TestCase
{
    use RefreshDatabase;

    private function makeTeacher(array $profileAttributes = []): User
    {
        $user = User::factory()->create([
            'role' => 'user',
            'avatar_url' => 'pub:images/avatars/test.jpg',
        ]);

        TeacherProfile::create(array_merge([
            'user_id' => $user->id,
            'tier' => 'basic',
            'status' => 'draft',
            'slug' => 'teacher-'.$user->id,
            'headline' => 'Experienced piano teacher',
            'expertise' => 'Piano Teacher',
            'about' => 'I teach piano.',
            'primary_instrument' => 'Piano',
        ], $profileAttributes));

        return $user->fresh();
    }

    public function test_teacher_can_submit_a_complete_profile_and_admins_are_notified(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = $this->makeTeacher();

        $this->actingAs($teacher)->post(route('teacher.profile.submit'))->assertRedirect();

        $this->assertSame(TeacherProfile::STATUS_SUBMITTED, $teacher->teacherProfile->fresh()->status);
        Notification::assertSentTo($admin, TeacherProfileSubmitted::class);
    }

    public function test_incomplete_profile_cannot_be_submitted(): void
    {
        Notification::fake();

        $teacher = $this->makeTeacher(['headline' => null, 'about' => null]);

        $this->actingAs($teacher)->post(route('teacher.profile.submit'))
            ->assertRedirect()
            ->assertSessionHas('submit-error');

        $this->assertSame(TeacherProfile::STATUS_DRAFT, $teacher->teacherProfile->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_admin_can_approve_a_submitted_profile(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = $this->makeTeacher(['status' => TeacherProfile::STATUS_SUBMITTED]);
        $profile = $teacher->teacherProfile;

        $this->actingAs($admin)->post(route('admin.teacher-profiles.approve', $profile))->assertRedirect();

        $profile->refresh();
        $this->assertTrue($profile->isApproved());
        $this->assertTrue($profile->isPubliclyVisible());
        $this->assertNotNull($profile->approved_at);
        $this->assertNotNull($profile->published_at);
        $this->assertDatabaseHas('teacher_profile_moderation_logs', [
            'teacher_profile_id' => $profile->id,
            'action' => 'approved',
            'admin_id' => $admin->id,
        ]);
        Notification::assertSentTo($teacher, TeacherProfileApproved::class);
    }

    public function test_admin_can_reject_with_reason(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = $this->makeTeacher(['status' => TeacherProfile::STATUS_SUBMITTED]);
        $profile = $teacher->teacherProfile;

        $this->actingAs($admin)
            ->post(route('admin.teacher-profiles.reject', $profile), ['reason' => 'Photo missing'])
            ->assertRedirect();

        $profile->refresh();
        $this->assertSame(TeacherProfile::STATUS_REJECTED, $profile->status);
        $this->assertSame('Photo missing', $profile->rejection_reason);
        $this->assertFalse($profile->isPubliclyVisible());
        Notification::assertSentTo($teacher, TeacherProfileRejected::class);
    }

    public function test_admin_can_suspend_and_reinstate(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = $this->makeTeacher(['status' => TeacherProfile::STATUS_APPROVED]);
        $profile = $teacher->teacherProfile;

        $this->actingAs($admin)->post(route('admin.teacher-profiles.suspend', $profile));
        $this->assertSame(TeacherProfile::STATUS_SUSPENDED, $profile->fresh()->status);
        $this->assertFalse($profile->fresh()->isPubliclyVisible());
        Notification::assertSentTo($teacher, TeacherProfileSuspended::class);

        $this->actingAs($admin)->post(route('admin.teacher-profiles.reinstate', $profile));
        $this->assertTrue($profile->fresh()->isPubliclyVisible());
    }

    public function test_admin_can_force_a_profile_private_without_unapproving(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = $this->makeTeacher(['status' => TeacherProfile::STATUS_APPROVED]);
        $profile = $teacher->teacherProfile;

        $this->actingAs($admin)->post(route('admin.teacher-profiles.force-private', $profile), ['private' => 1]);

        $profile->refresh();
        $this->assertTrue($profile->isApproved());
        $this->assertFalse($profile->isPubliclyVisible());
    }

    public function test_non_admin_cannot_moderate(): void
    {
        $teacher = $this->makeTeacher(['status' => TeacherProfile::STATUS_SUBMITTED]);
        $other = User::factory()->create(['role' => 'user']);

        $this->actingAs($other)->post(route('admin.teacher-profiles.approve', $teacher->teacherProfile))
            ->assertForbidden();
    }

    public function test_admin_can_update_teacher_tier(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = $this->makeTeacher();

        $this->actingAs($admin)->patch(route('admin.teacher-profiles.tier', $teacher->teacherProfile), ['tier' => 'premium']);

        $this->assertTrue($teacher->fresh()->isTeacherPremium());
    }

    // --- Public visibility + SEO ---

    public function test_approved_profile_is_public_and_indexable(): void
    {
        $teacher = $this->makeTeacher(['status' => TeacherProfile::STATUS_APPROVED]);

        $response = $this->get('/teachers/'.$teacher->teacherProfile->slug);

        $response->assertOk();
        $response->assertDontSee('X-Robots-Tag');
        $this->assertNull($response->headers->get('X-Robots-Tag'));
        $response->assertSee('rel="canonical"', false);
    }

    public function test_unapproved_profile_returns_404_for_visitors(): void
    {
        $teacher = $this->makeTeacher(['status' => TeacherProfile::STATUS_DRAFT]);
        $visitor = User::factory()->create();

        $this->get('/teachers/'.$teacher->teacherProfile->slug)->assertNotFound();
        $this->actingAs($visitor)->get('/teachers/'.$teacher->teacherProfile->slug)->assertNotFound();
    }

    public function test_owner_previews_unapproved_profile_with_noindex(): void
    {
        $teacher = $this->makeTeacher(['status' => TeacherProfile::STATUS_DRAFT]);

        $response = $this->actingAs($teacher)->get('/teachers/'.$teacher->teacherProfile->slug);

        $response->assertOk();
        $this->assertSame('noindex, nofollow', $response->headers->get('X-Robots-Tag'));
        $response->assertSee('noindex', false);
    }

    public function test_forced_private_profile_is_hidden_from_public(): void
    {
        $teacher = $this->makeTeacher([
            'status' => TeacherProfile::STATUS_APPROVED,
            'admin_forced_private' => true,
        ]);

        $this->get('/teachers/'.$teacher->teacherProfile->slug)->assertNotFound();
    }

    public function test_sitemap_lists_only_approved_profiles(): void
    {
        $approved = $this->makeTeacher(['status' => TeacherProfile::STATUS_APPROVED, 'slug' => 'approved-teacher']);
        $draft = $this->makeTeacher(['status' => TeacherProfile::STATUS_DRAFT, 'slug' => 'draft-teacher']);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertSee('/teachers/approved-teacher');
        $response->assertDontSee('/teachers/draft-teacher');
    }

    public function test_public_contact_details_respect_visibility_toggles(): void
    {
        $teacher = $this->makeTeacher([
            'status' => TeacherProfile::STATUS_APPROVED,
            'public_email' => 'hidden-teacher-email@example.com',
            'show_email' => false,
            'public_phone' => '+90 555 000 11 22',
            'show_phone' => true,
        ]);

        $response = $this->get('/teachers/'.$teacher->teacherProfile->slug);

        $response->assertDontSee('hidden-teacher-email@example.com');
        $response->assertSee('+90 555 000 11 22');
    }

    public function test_profile_view_count_increments_once_per_visitor_per_day(): void
    {
        $teacher = $this->makeTeacher(['status' => TeacherProfile::STATUS_APPROVED]);
        $profile = $teacher->teacherProfile;

        $this->get('/teachers/'.$profile->slug);
        $this->get('/teachers/'.$profile->slug);

        $this->assertSame(1, $profile->fresh()->view_count);
    }

    public function test_owner_views_do_not_count(): void
    {
        $teacher = $this->makeTeacher(['status' => TeacherProfile::STATUS_APPROVED]);
        $profile = $teacher->teacherProfile;

        $this->actingAs($teacher)->get('/teachers/'.$profile->slug);

        $this->assertSame(0, $profile->fresh()->view_count);
    }
}
