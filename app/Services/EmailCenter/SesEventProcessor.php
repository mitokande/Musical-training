<?php

namespace App\Services\EmailCenter;

use App\Models\EmailEvent;
use App\Models\EmailMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Persists SES event-publishing notifications (and legacy feedback
 * notifications) and applies their side effects: message status updates,
 * campaign counters and automatic bounce/complaint suppression.
 */
class SesEventProcessor
{
    // SES event names → internal event_type
    protected const EVENT_MAP = [
        'Send' => 'sent',
        'Delivery' => 'delivered',
        'Open' => 'opened',
        'Click' => 'clicked',
        'Bounce' => 'bounced',
        'Complaint' => 'complained',
        'Reject' => 'rejected',
        'Rendering Failure' => 'rendering_failed',
        'DeliveryDelay' => 'delivery_delayed',
    ];

    // higher rank statuses are never downgraded by later events
    protected const STATUS_RANK = [
        'queued' => 0, 'suppressed' => 0, 'sent' => 1, 'delivered' => 2,
        'opened' => 3, 'clicked' => 4, 'bounced' => 5, 'complained' => 6,
        'failed' => 5, 'rejected' => 5,
    ];

    public function __construct(protected SuppressionService $suppressions) {}

    public function process(array $event): void
    {
        $sesType = $event['eventType'] ?? $event['notificationType'] ?? null;
        $internalType = self::EVENT_MAP[$sesType] ?? null;

        if ($internalType === null) {
            Log::info('SES event ignored (unknown type)', ['type' => $sesType]);

            return;
        }

        $mail = $event['mail'] ?? [];
        $sesMessageId = $mail['messageId'] ?? null;
        $recipient = mb_strtolower($mail['destination'][0] ?? '');

        // idempotency: identical SNS deliveries produce the same hash
        $dedupHash = hash('sha256', json_encode([$sesType, $sesMessageId, $event[strtolower($sesType)] ?? null, $mail['timestamp'] ?? null]));

        if (EmailEvent::where('dedup_hash', $dedupHash)->exists()) {
            return;
        }

        $message = $this->matchMessage($sesMessageId, $mail);

        $occurredAt = $this->eventTimestamp($event, $sesType);

        EmailEvent::create([
            'email_message_id' => $message?->id,
            'ses_message_id' => $sesMessageId,
            'event_type' => $internalType,
            'recipient_email' => $recipient ?: $message?->recipient_email,
            'source' => 'ses',
            'occurred_at' => $occurredAt,
            'metadata' => $this->eventDetails($event, $sesType),
            'dedup_hash' => $dedupHash,
        ]);

        if ($message) {
            $this->applyToMessage($message, $internalType, $occurredAt);
        }

        $this->applySuppression($event, $sesType, $recipient ?: $message?->recipient_email);
    }

    protected function matchMessage(?string $sesMessageId, array $mail): ?EmailMessage
    {
        if ($sesMessageId && ($message = EmailMessage::where('ses_message_id', $sesMessageId)->first())) {
            return $message;
        }

        // fallback: match by the tracking_token SES message tag
        $token = $mail['tags']['tracking_token'][0] ?? null;

        return $token ? EmailMessage::where('tracking_token', $token)->first() : null;
    }

    protected function applyToMessage(EmailMessage $message, string $type, Carbon $occurredAt): void
    {
        $updates = [];
        $campaign = $message->campaign;

        switch ($type) {
            case 'delivered':
                $updates['delivered_at'] = $message->delivered_at ?? $occurredAt;
                if (! $message->delivered_at) {
                    $campaign?->increment('delivered_count');
                }
                break;
            case 'opened':
                $updates['opened_at'] = $message->opened_at ?? $occurredAt;
                if (! $message->opened_at) {
                    $campaign?->increment('opened_count');
                }
                break;
            case 'clicked':
                $updates['clicked_at'] = $message->clicked_at ?? $occurredAt;
                if (! $message->clicked_at) {
                    $campaign?->increment('clicked_count');
                }
                // a click implies the mail was opened even if the pixel was blocked
                if (! $message->opened_at) {
                    $updates['opened_at'] = $occurredAt;
                    $campaign?->increment('opened_count');
                }
                break;
            case 'bounced':
                if (! in_array($message->status, ['bounced'])) {
                    $campaign?->increment('bounced_count');
                }
                break;
            case 'complained':
                if (! in_array($message->status, ['complained'])) {
                    $campaign?->increment('complained_count');
                }
                break;
        }

        $currentRank = self::STATUS_RANK[$message->status] ?? 0;
        $newRank = self::STATUS_RANK[$type] ?? 0;

        if ($newRank > $currentRank) {
            $updates['status'] = $type;
        }

        if ($updates !== []) {
            $message->update($updates);
        }
    }

    protected function applySuppression(array $event, string $sesType, ?string $recipient): void
    {
        if (! $recipient) {
            return;
        }

        if ($sesType === 'Bounce') {
            $bounce = $event['bounce'] ?? [];
            $bounced = collect($bounce['bouncedRecipients'] ?? [])->pluck('emailAddress')
                ->map(fn ($e) => mb_strtolower($e))
                ->whenEmpty(fn ($c) => $c->push($recipient));

            if (($bounce['bounceType'] ?? '') === 'Permanent') {
                foreach ($bounced as $email) {
                    $this->suppressions->suppress($email, 'hard_bounce', 'ses_event', $bounce['bounceSubType'] ?? null);
                }
            }
            // Transient (soft) bounces are recorded as events only; the admin
            // panel surfaces repeat offenders for manual suppression.
        }

        if ($sesType === 'Complaint') {
            $complained = collect($event['complaint']['complainedRecipients'] ?? [])->pluck('emailAddress')
                ->map(fn ($e) => mb_strtolower($e))
                ->whenEmpty(fn ($c) => $c->push($recipient));

            foreach ($complained as $email) {
                $this->suppressions->suppress($email, 'complaint', 'ses_event', $event['complaint']['complaintFeedbackType'] ?? null);
            }
        }
    }

    protected function eventTimestamp(array $event, string $sesType): Carbon
    {
        $detail = $this->eventDetails($event, $sesType);

        $raw = $detail['timestamp'] ?? $event['mail']['timestamp'] ?? null;

        return $raw ? Carbon::parse($raw) : now();
    }

    protected function eventDetails(array $event, string $sesType): array
    {
        $key = match ($sesType) {
            'Rendering Failure' => 'failure',
            'DeliveryDelay' => 'deliveryDelay',
            default => lcfirst(str_replace(' ', '', $sesType)),
        };

        return is_array($event[$key] ?? null) ? $event[$key] : [];
    }
}
