<?php

namespace App\Policies;

use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\Teacher\TeacherCapabilityService;

class TeacherProfilePolicy
{
    public function __construct(private TeacherCapabilityService $capabilities) {}

    /** Private CRM view of the profile: owner or admin only. */
    public function view(User $user, TeacherProfile $profile): bool
    {
        return $user->id === $profile->user_id || $user->isAdmin();
    }

    public function update(User $user, TeacherProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }

    public function submit(User $user, TeacherProfile $profile): bool
    {
        return $user->id === $profile->user_id && $profile->canBeSubmitted();
    }

    public function managePaymentLinks(User $user, TeacherProfile $profile): bool
    {
        return $user->id === $profile->user_id
            && $this->capabilities->canUseExternalPaymentLinks($user);
    }

    public function moderate(User $user, TeacherProfile $profile): bool
    {
        return $user->isAdmin();
    }
}
