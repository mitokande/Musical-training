<?php

namespace App\Services\Teacher;

use App\Models\TeacherProfile;
use App\Models\User;

/**
 * Single source of truth for what a teacher account may do.
 *
 * Teacher-ness is orthogonal to User::role — any user with a TeacherProfile
 * has a teacher account. The profile's tier (basic|premium) decides the
 * capability set. Never scatter tier checks across controllers; add a
 * capability here and call it via policy/controller/blade.
 */
class TeacherCapabilityService
{
    /** Capabilities every teacher account has, regardless of tier. */
    private const BASIC_CAPABILITIES = [
        'viewTeacherCrm',
        'editProfile',
        'submitProfileForReview',
        'receiveMessages',
    ];

    /** Capabilities that require the premium teacher tier. */
    private const PREMIUM_CAPABILITIES = [
        'replyToMessages',
        'manageStudents',
        'createClasses',
        'createAssignments',
        'viewStudentAnalytics',
        'manageAvailability',
        'acceptAppointments',
        'publishContent',
        'useExternalPaymentLinks',
        'useAIHomeworkBuilder',
        'viewTeacherStatistics',
    ];

    public function can(User $user, string $capability): bool
    {
        if (! $user->hasTeacherAccount()) {
            return false;
        }

        if (in_array($capability, self::BASIC_CAPABILITIES, true)) {
            return true;
        }

        if (in_array($capability, self::PREMIUM_CAPABILITIES, true)) {
            return $user->teacherTier() === TeacherProfile::TIER_PREMIUM;
        }

        return false;
    }

    /** Full capability map, e.g. for rendering locked/unlocked CRM navigation. */
    public function capabilities(User $user): array
    {
        $all = array_merge(self::BASIC_CAPABILITIES, self::PREMIUM_CAPABILITIES);

        return collect($all)
            ->mapWithKeys(fn (string $cap) => [$cap => $this->can($user, $cap)])
            ->all();
    }

    // Explicit helpers for the most common checks.

    public function canViewTeacherCrm(User $user): bool
    {
        return $this->can($user, 'viewTeacherCrm');
    }

    public function canReplyToMessages(User $user): bool
    {
        return $this->can($user, 'replyToMessages');
    }

    public function canManageStudents(User $user): bool
    {
        return $this->can($user, 'manageStudents');
    }

    public function canCreateClasses(User $user): bool
    {
        return $this->can($user, 'createClasses');
    }

    public function canCreateAssignments(User $user): bool
    {
        return $this->can($user, 'createAssignments');
    }

    public function canViewStudentAnalytics(User $user): bool
    {
        return $this->can($user, 'viewStudentAnalytics');
    }

    public function canManageAvailability(User $user): bool
    {
        return $this->can($user, 'manageAvailability');
    }

    public function canAcceptAppointments(User $user): bool
    {
        return $this->can($user, 'acceptAppointments');
    }

    public function canPublishContent(User $user): bool
    {
        return $this->can($user, 'publishContent');
    }

    public function canUseExternalPaymentLinks(User $user): bool
    {
        return $this->can($user, 'useExternalPaymentLinks');
    }

    public function canUseAIHomeworkBuilder(User $user): bool
    {
        return $this->can($user, 'useAIHomeworkBuilder');
    }
}
