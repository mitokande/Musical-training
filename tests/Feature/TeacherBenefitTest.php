<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\TeacherProfile;
use App\Models\TeacherStudentRelationship;
use App\Models\TeacherSubscriptionBenefit;
use App\Models\User;
use App\Services\Teacher\TeacherSubscriptionBenefitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherBenefitTest extends TestCase
{
    use RefreshDatabase;

    private TeacherSubscriptionBenefitService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TeacherSubscriptionBenefitService::class);
    }

    private function makeTeacher(): User
    {
        $user = User::factory()->create(['role' => 'user']);
        TeacherProfile::create([
            'user_id' => $user->id,
            'tier' => 'premium',
            'status' => 'approved',
            'slug' => 'teacher-'.$user->id,
        ]);

        return $user->fresh();
    }

    private function addPremiumStudents(User $teacher, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $student = User::factory()->create(['role' => 'user', 'plan' => 'premium']);
            TeacherStudentRelationship::create([
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
                'status' => TeacherStudentRelationship::STATUS_ACTIVE,
                'approved_at' => now(),
            ]);
        }
    }

    public function test_only_active_premium_students_count_as_eligible(): void
    {
        $teacher = $this->makeTeacher();

        // 2 active premium students
        $this->addPremiumStudents($teacher, 2);

        // active free student — must not count
        $freeStudent = User::factory()->create(['plan' => 'free']);
        TeacherStudentRelationship::create([
            'teacher_id' => $teacher->id, 'student_id' => $freeStudent->id,
            'status' => TeacherStudentRelationship::STATUS_ACTIVE,
        ]);

        // pending premium student — must not count
        $pending = User::factory()->create(['plan' => 'premium']);
        TeacherStudentRelationship::create([
            'teacher_id' => $teacher->id, 'student_id' => $pending->id,
            'status' => TeacherStudentRelationship::STATUS_PENDING_STUDENT_APPROVAL,
        ]);

        // suspended premium student — must not count
        $suspended = User::factory()->create(['plan' => 'premium', 'suspended_at' => now()]);
        TeacherStudentRelationship::create([
            'teacher_id' => $teacher->id, 'student_id' => $suspended->id,
            'status' => TeacherStudentRelationship::STATUS_ACTIVE,
        ]);

        $this->assertSame(2, $this->service->eligibleStudentCount($teacher));
    }

    public function test_five_premium_students_earn_the_discount(): void
    {
        $teacher = $this->makeTeacher();
        $this->addPremiumStudents($teacher, 5);

        $benefit = $this->service->recalculate($teacher);

        $this->assertNotNull($benefit);
        $this->assertSame(TeacherSubscriptionBenefit::TYPE_DISCOUNT, $benefit->type);
        $this->assertSame(50, $benefit->discount_percentage);
        $this->assertSame(5, $benefit->qualifying_student_count);
        $this->assertDatabaseHas('teacher_subscription_benefit_histories', [
            'user_id' => $teacher->id, 'event' => 'granted',
        ]);
    }

    public function test_below_threshold_earns_nothing(): void
    {
        $teacher = $this->makeTeacher();
        $this->addPremiumStudents($teacher, 4);

        $this->assertNull($this->service->recalculate($teacher));
    }

    public function test_ten_premium_students_earn_free_period_and_supersede_discount(): void
    {
        $teacher = $this->makeTeacher();
        $this->addPremiumStudents($teacher, 5);
        $discount = $this->service->recalculate($teacher);

        $this->addPremiumStudents($teacher, 5);
        $benefit = $this->service->recalculate($teacher);

        $this->assertSame(TeacherSubscriptionBenefit::TYPE_FREE_PERIOD, $benefit->type);
        $this->assertNotNull($benefit->ends_at);
        $this->assertTrue($benefit->ends_at->greaterThan(now()->addMonths(11)));
        $this->assertSame(TeacherSubscriptionBenefit::STATUS_SUPERSEDED, $discount->fresh()->status);
    }

    public function test_recalculation_is_idempotent_and_never_duplicates_grants(): void
    {
        $teacher = $this->makeTeacher();
        $this->addPremiumStudents($teacher, 10);

        $this->service->recalculate($teacher);
        $this->service->recalculate($teacher);
        $this->service->recalculate($teacher);

        $this->assertSame(1, TeacherSubscriptionBenefit::where('user_id', $teacher->id)
            ->where('status', TeacherSubscriptionBenefit::STATUS_ACTIVE)->count());
        $this->assertSame(1, TeacherSubscriptionBenefit::where('user_id', $teacher->id)->count());
    }

    public function test_losing_eligibility_starts_the_grace_period(): void
    {
        $teacher = $this->makeTeacher();
        $this->addPremiumStudents($teacher, 5);
        $benefit = $this->service->recalculate($teacher);
        $this->assertNull($benefit->ends_at);

        TeacherStudentRelationship::where('teacher_id', $teacher->id)->limit(2)
            ->update(['status' => TeacherStudentRelationship::STATUS_REVOKED_BY_STUDENT]);

        $this->service->recalculate($teacher);

        $benefit->refresh();
        $this->assertNotNull($benefit->ends_at);
        $this->assertEqualsWithDelta(
            now()->addDays($this->service->gracePeriodDays())->timestamp,
            $benefit->ends_at->timestamp,
            5
        );

        // Eligibility restored within grace → end date cleared again.
        TeacherStudentRelationship::where('teacher_id', $teacher->id)
            ->update(['status' => TeacherStudentRelationship::STATUS_ACTIVE]);
        $this->service->recalculate($teacher);
        $this->assertNull($benefit->fresh()->ends_at);
    }

    public function test_expired_benefits_are_marked_by_the_scheduler_job(): void
    {
        $teacher = $this->makeTeacher();
        $benefit = TeacherSubscriptionBenefit::create([
            'user_id' => $teacher->id,
            'type' => TeacherSubscriptionBenefit::TYPE_DISCOUNT,
            'discount_percentage' => 50,
            'status' => TeacherSubscriptionBenefit::STATUS_ACTIVE,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->subDay(),
        ]);

        $this->service->expireLapsedBenefits();

        $this->assertSame(TeacherSubscriptionBenefit::STATUS_EXPIRED, $benefit->fresh()->status);
    }

    public function test_thresholds_are_admin_configurable(): void
    {
        SystemSetting::set('teacher_discount_student_threshold', 2, 'integer', 'teacher');
        SystemSetting::set('teacher_discount_percentage', 30, 'integer', 'teacher');

        $teacher = $this->makeTeacher();
        $this->addPremiumStudents($teacher, 2);

        $benefit = $this->service->recalculate($teacher);

        $this->assertSame(30, $benefit->discount_percentage);
    }

    public function test_disabled_incentive_grants_nothing(): void
    {
        SystemSetting::set('teacher_premium_incentive_enabled', '0', 'boolean', 'teacher');

        $teacher = $this->makeTeacher();
        $this->addPremiumStudents($teacher, 10);

        $this->assertNull($this->service->recalculate($teacher));
    }

    public function test_admin_override_supersedes_the_automatic_benefit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = $this->makeTeacher();
        $this->addPremiumStudents($teacher, 5);
        $auto = $this->service->recalculate($teacher);

        $override = $this->service->adminOverride($teacher, TeacherSubscriptionBenefit::TYPE_FREE_PERIOD, [
            'ends_at' => now()->addMonths(3),
            'notes' => 'Launch partner',
        ], $admin);

        $this->assertSame('admin_override', $override->source);
        $this->assertSame(TeacherSubscriptionBenefit::STATUS_SUPERSEDED, $auto->fresh()->status);
        $this->assertDatabaseHas('teacher_subscription_benefit_histories', [
            'user_id' => $teacher->id, 'event' => 'admin_override', 'created_by' => $admin->id,
        ]);
    }
}
