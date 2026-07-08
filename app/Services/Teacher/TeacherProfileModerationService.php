<?php

namespace App\Services\Teacher;

use App\Models\TeacherProfile;
use App\Models\User;
use App\Notifications\Teacher\TeacherProfileApproved;
use App\Notifications\Teacher\TeacherProfileRejected;
use App\Notifications\Teacher\TeacherProfileSubmitted;
use App\Notifications\Teacher\TeacherProfileSuspended;
use Illuminate\Support\Facades\Notification;

/**
 * Central state machine for the teacher-profile moderation lifecycle.
 * Every transition writes a moderation log row and dispatches the queued
 * notifications; controllers must never mutate `status` directly.
 */
class TeacherProfileModerationService
{
    /** Teacher submits their draft (or rejected) profile for admin review. */
    public function submit(TeacherProfile $profile): void
    {
        $from = $profile->status;

        $profile->update([
            'status' => TeacherProfile::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'rejection_reason' => null,
        ]);

        $this->log($profile, null, 'submitted', $from);

        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new TeacherProfileSubmitted($profile));
    }

    public function approve(TeacherProfile $profile, User $admin, ?string $notes = null): void
    {
        $from = $profile->status;

        $profile->update([
            'status' => TeacherProfile::STATUS_APPROVED,
            'approved_at' => now(),
            'published_at' => $profile->published_at ?? now(),
            'admin_forced_private' => false,
            'rejection_reason' => null,
        ]);

        $this->log($profile, $admin, 'approved', $from, $notes);
        $profile->user->notify(new TeacherProfileApproved($profile));
    }

    public function reject(TeacherProfile $profile, User $admin, ?string $reason = null): void
    {
        $from = $profile->status;

        $profile->update([
            'status' => TeacherProfile::STATUS_REJECTED,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->log($profile, $admin, 'rejected', $from, $reason);
        $profile->user->notify(new TeacherProfileRejected($profile, $reason));
    }

    public function suspend(TeacherProfile $profile, User $admin, ?string $reason = null): void
    {
        $from = $profile->status;

        $profile->update([
            'status' => TeacherProfile::STATUS_SUSPENDED,
            'suspended_at' => now(),
        ]);

        $this->log($profile, $admin, 'suspended', $from, $reason);
        $profile->user->notify(new TeacherProfileSuspended($profile, $reason));
    }

    /** Lift a suspension back to approved. */
    public function reinstate(TeacherProfile $profile, User $admin, ?string $notes = null): void
    {
        $from = $profile->status;

        $profile->update([
            'status' => TeacherProfile::STATUS_APPROVED,
            'suspended_at' => null,
        ]);

        $this->log($profile, $admin, 'reinstated', $from, $notes);
        $profile->user->notify(new TeacherProfileApproved($profile));
    }

    /** Hide an approved profile without changing its approval state. */
    public function forcePrivate(TeacherProfile $profile, User $admin, bool $private, ?string $notes = null): void
    {
        $profile->update(['admin_forced_private' => $private]);

        $this->log($profile, $admin, $private ? 'forced_private' : 'made_public', $profile->status, $notes);
    }

    /** Internal moderation note with no status change. */
    public function addNote(TeacherProfile $profile, User $admin, string $notes): void
    {
        $this->log($profile, $admin, 'note', $profile->status, $notes, $profile->status);
    }

    private function log(TeacherProfile $profile, ?User $admin, string $action, ?string $from, ?string $notes = null, ?string $to = null): void
    {
        $profile->moderationLogs()->create([
            'admin_id' => $admin?->id,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to ?? $profile->status,
            'notes' => $notes,
        ]);

        activity('teacher')
            ->causedBy($admin)
            ->performedOn($profile)
            ->withProperties(['action' => $action, 'from' => $from, 'to' => $profile->status])
            ->log('teacher_profile_'.$action);
    }
}
