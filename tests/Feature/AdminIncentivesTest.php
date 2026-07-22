<?php

namespace Tests\Feature;

use App\Models\TeacherStudentRelationship;
use App\Models\TeacherSubscriptionBenefit;
use App\Models\User;
use App\Services\Teacher\TeacherSubscriptionBenefitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminIncentivesTest extends TestCase
{
    use RefreshDatabase;

    private function addPremiumStudents(User $account, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $student = User::factory()->create(['role' => 'user', 'plan' => 'premium']);
            TeacherStudentRelationship::create([
                'teacher_id' => $account->id,
                'student_id' => $student->id,
                'status' => TeacherStudentRelationship::STATUS_ACTIVE,
                'approved_at' => now(),
            ]);
        }
    }

    public function test_incentives_index_is_admin_only(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->get(route('admin.incentives.index'))->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.incentives.index'))->assertOk();
    }

    public function test_admin_can_approve_a_pending_school_grant(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $school = User::factory()->create(['role' => 'school']);
        $this->addPremiumStudents($school, 20);
        app(TeacherSubscriptionBenefitService::class)->recalculate($school);

        $pending = TeacherSubscriptionBenefit::pending()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.incentives.approve', $pending))
            ->assertRedirect();

        $pending->refresh();
        $this->assertSame(TeacherSubscriptionBenefit::STATUS_ACTIVE, $pending->status);
        $this->assertTrue($school->fresh()->isEffectivelyPremium());
    }

    public function test_admin_can_revoke_a_benefit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'user']);
        $benefit = $teacher->teacherSubscriptionBenefits()->create([
            'type' => TeacherSubscriptionBenefit::TYPE_FREE_PERIOD,
            'status' => TeacherSubscriptionBenefit::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addYear(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.incentives.revoke', $benefit), ['reason' => 'test'])
            ->assertRedirect();

        $this->assertSame(TeacherSubscriptionBenefit::STATUS_REVOKED, $benefit->fresh()->status);
    }
}
