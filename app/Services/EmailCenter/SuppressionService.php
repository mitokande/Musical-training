<?php

namespace App\Services\EmailCenter;

use App\Models\EmailMessage;
use App\Models\EmailSuppression;
use App\Models\SystemSetting;
use App\Models\User;

class SuppressionService
{
    public function isSuppressed(string $email): bool
    {
        return EmailSuppression::where('email', mb_strtolower($email))->exists();
    }

    public function suppress(string $email, string $reason, ?string $source = null, ?string $notes = null): EmailSuppression
    {
        return EmailSuppression::updateOrCreate(
            ['email' => mb_strtolower($email)],
            [
                'reason' => $reason,
                'source' => $source,
                'notes' => $notes,
                'suppressed_at' => now(),
            ]
        );
    }

    public function unsuppress(string $email): void
    {
        EmailSuppression::where('email', mb_strtolower($email))->delete();
    }

    /**
     * Frequency cap: marketing emails per user per rolling 7 days.
     */
    public function isOverFrequencyCap(User $user): bool
    {
        $cap = (int) SystemSetting::get('email_frequency_cap', config('email-center.frequency_cap'));

        if ($cap <= 0) {
            return false; // 0 or negative = unlimited
        }

        $recent = EmailMessage::where('user_id', $user->id)
            ->whereIn('email_type', EmailMessage::MARKETING_TYPES)
            ->where('created_at', '>=', now()->subDays(7))
            ->whereNotIn('status', ['failed', 'suppressed'])
            ->count();

        return $recent >= $cap;
    }

    /**
     * Marketing mail may only go to verified, unsuppressed, active accounts.
     * Transactional mail (password reset, support replies) skips these checks.
     */
    public function canReceiveMarketing(User $user): bool
    {
        return $user->email_verified_at !== null
            && $user->suspended_at === null
            && ! $this->isSuppressed($user->email)
            && ! $this->isOverFrequencyCap($user);
    }
}
