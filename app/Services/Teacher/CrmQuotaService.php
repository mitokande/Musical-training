<?php

namespace App\Services\Teacher;

use App\Models\SchoolTeacherRelationship;
use App\Models\TeacherAssignment;
use App\Models\TeacherMedia;
use App\Models\TeacherStudentRelationship;
use App\Models\User;

/**
 * Central quota layer for teacher / school CRM resources.
 *
 * Free (basic-tier) teacher and school accounts get the full CRM toolset but
 * with hard resource caps: max free-plan students, max active assignments,
 * daily sent messages and document uploads. Premium-tier accounts — and
 * accounts holding an earned free-period benefit (premium-student incentive)
 * — are exempt. Limits come from config/plans.php teacher/school sections so
 * they stay admin-overridable through the Plans module.
 */
class CrmQuotaService
{
    /** Whether CRM resource quotas apply to this account at all. */
    public function quotasApply(User $user): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        if ($user->isEffectivelyPremium()) {
            return false;
        }

        if ($user->crmNamespace() === 'school') {
            // School accounts: premium plan lifts every cap.
            return ! $user->isPremium();
        }

        // Teacher accounts: premium tier or an active school membership
        // grants the premium toolset without caps (existing behaviour).
        return ! $user->isTeacherPremium() && ! $user->hasActiveSchoolMembership();
    }

    /** Config limit in the account's CRM namespace; -1 = unlimited. */
    public function limit(User $user, string $key): int
    {
        if (! $this->quotasApply($user)) {
            return -1;
        }

        $role = $user->crmNamespace() === 'school' ? 'school' : 'teacher';

        return (int) (config("plans.{$role}.free.{$key}") ?? -1);
    }

    // ── Students (free-plan students capped, premium students unlimited) ───

    public function freeStudentCount(User $owner): int
    {
        return TeacherStudentRelationship::query()
            ->where('teacher_id', $owner->id)
            ->whereIn('status', [
                TeacherStudentRelationship::STATUS_ACTIVE,
                TeacherStudentRelationship::STATUS_PENDING_STUDENT_APPROVAL,
            ])
            ->whereHas('student', fn ($q) => $q->where('plan', '!=', 'premium'))
            ->count();
    }

    public function canAddStudent(User $owner, ?User $student): bool
    {
        $limit = $this->limit($owner, 'max_free_students');

        if ($limit === -1) {
            return true;
        }

        // Premium-plan students never count against the cap.
        if ($student && $student->isPremium()) {
            return true;
        }

        return $this->freeStudentCount($owner) < $limit;
    }

    // ── Member teachers (schools) ──────────────────────────────────────────

    public function activeTeacherCount(User $school): int
    {
        return SchoolTeacherRelationship::query()
            ->where('school_id', $school->id)
            ->whereIn('status', [
                SchoolTeacherRelationship::STATUS_ACTIVE,
                SchoolTeacherRelationship::STATUS_PENDING_TEACHER_APPROVAL,
            ])
            ->count();
    }

    // ── Assignments ────────────────────────────────────────────────────────

    public function activeAssignmentCount(User $owner): int
    {
        return TeacherAssignment::query()
            ->where('teacher_id', $owner->id)
            ->where('status', '!=', TeacherAssignment::STATUS_ARCHIVED)
            ->count();
    }

    public function canCreateAssignment(User $owner): bool
    {
        $limit = $this->limit($owner, 'max_active_assignments');

        return $limit === -1 || $this->activeAssignmentCount($owner) < $limit;
    }

    // ── Documents (teacher media archive) ──────────────────────────────────

    public function documentCount(User $owner): int
    {
        $profile = $owner->teacherProfile;

        if (! $profile) {
            return 0;
        }

        return TeacherMedia::query()
            ->where('teacher_profile_id', $profile->id)
            ->where('kind', 'document')
            ->count();
    }

    public function canUploadDocument(User $owner): bool
    {
        $limit = $this->limit($owner, 'max_documents');

        return $limit === -1 || $this->documentCount($owner) < $limit;
    }
}
