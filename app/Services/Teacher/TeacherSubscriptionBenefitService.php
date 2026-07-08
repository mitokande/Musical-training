<?php

namespace App\Services\Teacher;

use App\Models\SystemSetting;
use App\Models\TeacherStudentRelationship;
use App\Models\TeacherSubscriptionBenefit;
use App\Models\TeacherSubscriptionBenefitHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Teacher Premium incentive engine.
 *
 * Business rules (all thresholds admin-configurable via SystemSetting):
 *  - >= teacher_discount_student_threshold active approved Premium students
 *    → teacher_discount_percentage % Teacher Premium discount.
 *  - >= teacher_free_subscription_student_threshold active approved Premium
 *    students → teacher_free_subscription_duration_months of free Teacher
 *    Premium (highest benefit wins; only one benefit active at a time).
 *
 * Harmoniva does not process payments in Phase 1, so benefits are recorded
 * and surfaced to admins/teachers; billing integration consumes them later.
 */
class TeacherSubscriptionBenefitService
{
    public function isEnabled(): bool
    {
        return (bool) SystemSetting::get('teacher_premium_incentive_enabled', true);
    }

    public function discountThreshold(): int
    {
        return (int) SystemSetting::get('teacher_discount_student_threshold', 5);
    }

    public function discountPercentage(): int
    {
        return (int) SystemSetting::get('teacher_discount_percentage', 50);
    }

    public function freePeriodThreshold(): int
    {
        return (int) SystemSetting::get('teacher_free_subscription_student_threshold', 10);
    }

    public function freePeriodMonths(): int
    {
        return (int) SystemSetting::get('teacher_free_subscription_duration_months', 12);
    }

    public function gracePeriodDays(): int
    {
        return (int) SystemSetting::get('teacher_premium_incentive_grace_period_days', 14);
    }

    /**
     * Active, mutually-approved relationships whose student holds an active
     * Premium student subscription and is neither suspended nor restricted.
     */
    public function eligibleStudentCount(User $teacher): int
    {
        return TeacherStudentRelationship::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', TeacherStudentRelationship::STATUS_ACTIVE)
            ->whereHas('student', function ($q) {
                $q->where('plan', 'premium')
                    ->whereNull('suspended_at')
                    ->where(fn ($r) => $r->where('is_restricted', false)->orWhereNull('is_restricted'));
            })
            ->count();
    }

    public function activeBenefit(User $teacher): ?TeacherSubscriptionBenefit
    {
        return $teacher->teacherSubscriptionBenefits()->active()->latest('id')->first();
    }

    /**
     * Recalculate and apply the highest benefit the teacher currently earns.
     * Idempotent: re-running without eligibility changes performs no writes
     * beyond keeping the qualifying student count current.
     */
    public function recalculate(User $teacher): ?TeacherSubscriptionBenefit
    {
        if (! $this->isEnabled()) {
            return $this->activeBenefit($teacher);
        }

        return DB::transaction(function () use ($teacher) {
            $count = $this->eligibleStudentCount($teacher);
            $earnedType = $this->earnedBenefitType($count);
            $current = $this->activeBenefit($teacher);

            // An earned free period is kept until its end date even if the
            // student count later drops — it was already granted.
            if ($current?->type === TeacherSubscriptionBenefit::TYPE_FREE_PERIOD) {
                if ($earnedType !== TeacherSubscriptionBenefit::TYPE_FREE_PERIOD) {
                    $this->touchCount($current, $count);

                    return $current;
                }

                $this->touchCount($current, $count);

                return $current; // no duplicate free-period grants while one is active
            }

            if ($earnedType === TeacherSubscriptionBenefit::TYPE_FREE_PERIOD) {
                if ($current) {
                    $this->supersede($current, $count);
                }

                return $this->grant($teacher, TeacherSubscriptionBenefit::TYPE_FREE_PERIOD, $count);
            }

            if ($earnedType === TeacherSubscriptionBenefit::TYPE_DISCOUNT) {
                if ($current?->type === TeacherSubscriptionBenefit::TYPE_DISCOUNT) {
                    // Still earned: clear any pending grace expiry and update count.
                    if ($current->ends_at !== null) {
                        $current->update(['ends_at' => null]);
                        $this->record($teacher, $current, 'recalculated', [
                            'note' => 'grace period cancelled, eligibility restored',
                            'qualifying_student_count' => $count,
                        ]);
                    }
                    $this->touchCount($current, $count);

                    return $current;
                }

                return $this->grant($teacher, TeacherSubscriptionBenefit::TYPE_DISCOUNT, $count);
            }

            // No benefit earned any more.
            if ($current && $current->ends_at === null) {
                $graceEnd = now()->addDays($this->gracePeriodDays());
                $current->update(['ends_at' => $graceEnd, 'qualifying_student_count' => $count]);
                $this->record($teacher, $current, 'recalculated', [
                    'note' => 'eligibility lost, grace period started',
                    'grace_ends_at' => $graceEnd->toIso8601String(),
                    'qualifying_student_count' => $count,
                ]);
            }

            return $this->activeBenefit($teacher);
        });
    }

    /** Expire benefits whose end date has passed (run from scheduler). */
    public function expireLapsedBenefits(): int
    {
        $lapsed = TeacherSubscriptionBenefit::query()
            ->where('status', TeacherSubscriptionBenefit::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->get();

        foreach ($lapsed as $benefit) {
            $benefit->update(['status' => TeacherSubscriptionBenefit::STATUS_EXPIRED]);
            $this->record($benefit->user, $benefit, 'expired', []);
        }

        return $lapsed->count();
    }

    /** Admin manual override — grants a benefit outside the automatic rules. */
    public function adminOverride(User $teacher, string $type, array $attributes, User $admin): TeacherSubscriptionBenefit
    {
        return DB::transaction(function () use ($teacher, $type, $attributes, $admin) {
            if ($current = $this->activeBenefit($teacher)) {
                $this->supersede($current, $current->qualifying_student_count);
            }

            $benefit = $teacher->teacherSubscriptionBenefits()->create([
                'type' => $type,
                'discount_percentage' => $attributes['discount_percentage'] ?? null,
                'qualifying_student_count' => $this->eligibleStudentCount($teacher),
                'status' => TeacherSubscriptionBenefit::STATUS_ACTIVE,
                'source' => 'admin_override',
                'starts_at' => now(),
                'ends_at' => $attributes['ends_at'] ?? null,
                'notes' => $attributes['notes'] ?? null,
            ]);

            $this->record($teacher, $benefit, 'admin_override', ['admin_id' => $admin->id], $admin);

            activity('teacher')
                ->causedBy($admin)
                ->performedOn($teacher)
                ->withProperties(['benefit_id' => $benefit->id, 'type' => $type])
                ->log('teacher_benefit_admin_override');

            return $benefit;
        });
    }

    public function revoke(TeacherSubscriptionBenefit $benefit, ?User $admin = null, ?string $reason = null): void
    {
        $benefit->update(['status' => TeacherSubscriptionBenefit::STATUS_REVOKED, 'ends_at' => now()]);
        $this->record($benefit->user, $benefit, 'revoked', ['reason' => $reason], $admin);
    }

    private function earnedBenefitType(int $count): ?string
    {
        if ($count >= $this->freePeriodThreshold()) {
            return TeacherSubscriptionBenefit::TYPE_FREE_PERIOD;
        }

        if ($count >= $this->discountThreshold()) {
            return TeacherSubscriptionBenefit::TYPE_DISCOUNT;
        }

        return null;
    }

    private function grant(User $teacher, string $type, int $count): TeacherSubscriptionBenefit
    {
        $benefit = $teacher->teacherSubscriptionBenefits()->create([
            'type' => $type,
            'discount_percentage' => $type === TeacherSubscriptionBenefit::TYPE_DISCOUNT
                ? $this->discountPercentage() : null,
            'qualifying_student_count' => $count,
            'status' => TeacherSubscriptionBenefit::STATUS_ACTIVE,
            'source' => 'automatic',
            'starts_at' => now(),
            'ends_at' => $type === TeacherSubscriptionBenefit::TYPE_FREE_PERIOD
                ? now()->addMonths($this->freePeriodMonths()) : null,
        ]);

        $this->record($teacher, $benefit, 'granted', [
            'type' => $type,
            'qualifying_student_count' => $count,
        ]);

        return $benefit;
    }

    private function supersede(TeacherSubscriptionBenefit $benefit, int $count): void
    {
        $benefit->update([
            'status' => TeacherSubscriptionBenefit::STATUS_SUPERSEDED,
            'qualifying_student_count' => $count,
        ]);
        $this->record($benefit->user, $benefit, 'superseded', []);
    }

    private function touchCount(TeacherSubscriptionBenefit $benefit, int $count): void
    {
        if ($benefit->qualifying_student_count !== $count) {
            $benefit->update(['qualifying_student_count' => $count]);
        }
    }

    private function record(User $teacher, ?TeacherSubscriptionBenefit $benefit, string $event, array $details, ?User $creator = null): void
    {
        TeacherSubscriptionBenefitHistory::create([
            'user_id' => $teacher->id,
            'teacher_subscription_benefit_id' => $benefit?->id,
            'event' => $event,
            'details' => $details ?: null,
            'created_by' => $creator?->id,
        ]);
    }
}
