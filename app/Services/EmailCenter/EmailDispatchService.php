<?php

namespace App\Services\EmailCenter;

use App\Jobs\SendEmailCenterMessage;
use App\Models\EmailAutomation;
use App\Models\EmailCampaign;
use App\Models\EmailMessage;
use App\Models\EmailPreference;
use App\Models\EmailSuppression;
use App\Models\EmailTemplate;
use App\Models\User;

/**
 * Single entry point for queueing Email Center mail. Creates the
 * email_messages row (which owns the tracking token) and dispatches the
 * rate-limited send job. Marketing mail is checked against the suppression
 * list and frequency cap; transactional mail is only checked against
 * hard-bounce suppression.
 */
class EmailDispatchService
{
    public function __construct(protected SuppressionService $suppressions) {}

    /**
     * @param  array<string, string>  $context  extra template variables, stored on the message
     */
    public function dispatch(
        User|string $recipient,
        string $emailType,
        ?EmailTemplate $template = null,
        ?string $subject = null,
        ?string $html = null,
        ?EmailCampaign $campaign = null,
        ?EmailAutomation $automation = null,
        array $context = [],
    ): ?EmailMessage {
        $user = $recipient instanceof User ? $recipient : null;
        $email = mb_strtolower($user?->email ?? $recipient);

        $isMarketing = in_array($emailType, EmailMessage::MARKETING_TYPES);

        if ($isMarketing) {
            if ($user && ! $this->suppressions->canReceiveMarketing($user)) {
                return null;
            }
            if (! $user && $this->suppressions->isSuppressed($email)) {
                return null;
            }
            // Per-user topic/frequency preferences. Only a known user can have
            // preferences; addressed-only marketing (rare) is unaffected.
            if ($user) {
                $category = EmailPreference::categoryFor($automation, $emailType);
                if (! EmailPreference::for($user)->allows($category)) {
                    return null;
                }
            }
        } elseif ($this->isHardSuppressed($email)) {
            // even transactional mail must not go to hard-bounced addresses
            return null;
        }

        $message = EmailMessage::create([
            'user_id' => $user?->id,
            'recipient_email' => $email,
            'campaign_id' => $campaign?->id,
            'automation_id' => $automation?->id,
            'template_id' => $template?->id,
            'email_type' => $emailType,
            'subject' => $subject ?? $template?->subject ?? '',
            'status' => 'queued',
            'metadata' => [
                'context' => $context,
                'html' => $template ? null : $html, // custom HTML stored inline
            ],
        ]);

        SendEmailCenterMessage::dispatch($message->id);

        return $message;
    }

    protected function isHardSuppressed(string $email): bool
    {
        return EmailSuppression::where('email', $email)
            ->where('reason', 'hard_bounce')
            ->exists();
    }
}
