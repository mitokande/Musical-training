<?php

namespace App\Http\Controllers;

use App\Models\EmailEvent;
use App\Models\EmailMessage;
use App\Services\EmailCenter\SuppressionService;
use Illuminate\Http\Request;

/**
 * Internal tracking layer on top of SES events: open pixel fallback,
 * click attribution redirect and the unsubscribe flow (page + RFC 8058
 * one-click POST).
 */
class EmailTrackingController extends Controller
{
    /**
     * 1x1 transparent GIF. Complements SES open tracking — whichever
     * arrives first sets opened_at; events are deduped separately.
     */
    public function open(string $token)
    {
        $message = EmailMessage::where('tracking_token', $token)->first();

        if ($message) {
            $this->recordInternalEvent($message, 'opened');

            if (! $message->opened_at) {
                $message->update(['opened_at' => now()]);
                $message->campaign?->increment('opened_count');
                if (($message->status === 'sent' || $message->status === 'delivered')) {
                    $message->update(['status' => 'opened']);
                }
            }
        }

        return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'))
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function click(Request $request, string $token)
    {
        $target = (string) $request->query('url', '');

        // only ever redirect to http(s) URLs; anything else goes home
        if (! preg_match('#^https?://#i', $target)) {
            return redirect(config('app.url'));
        }

        $message = EmailMessage::where('tracking_token', $token)->first();

        if ($message) {
            $this->recordInternalEvent($message, 'clicked', ['url' => $target]);

            if (! $message->clicked_at) {
                $message->update(['clicked_at' => now(), 'opened_at' => $message->opened_at ?? now()]);
                $message->campaign?->increment('clicked_count');
                if (in_array($message->status, ['sent', 'delivered', 'opened'])) {
                    $message->update(['status' => 'clicked']);
                }
            }
        }

        return redirect()->away($target);
    }

    public function unsubscribe(string $token)
    {
        $message = EmailMessage::where('tracking_token', $token)->firstOrFail();

        return view('email.unsubscribe', [
            'message' => $message,
            'alreadyUnsubscribed' => app(SuppressionService::class)->isSuppressed($message->recipient_email),
        ]);
    }

    /**
     * Handles both the confirm button on the unsubscribe page and
     * one-click List-Unsubscribe POSTs from mail clients.
     */
    public function unsubscribePost(string $token, SuppressionService $suppressions)
    {
        $message = EmailMessage::where('tracking_token', $token)->firstOrFail();

        $suppressions->suppress($message->recipient_email, 'unsubscribe', 'user');
        $this->recordInternalEvent($message, 'unsubscribed');

        return view('email.unsubscribe', [
            'message' => $message,
            'alreadyUnsubscribed' => true,
        ]);
    }

    protected function recordInternalEvent(EmailMessage $message, string $type, array $metadata = []): void
    {
        // one internal event per message+type (+url for clicks)
        $hash = hash('sha256', 'internal-'.$type.'-'.$message->id.'-'.($metadata['url'] ?? ''));

        EmailEvent::firstOrCreate(
            ['dedup_hash' => $hash],
            [
                'email_message_id' => $message->id,
                'ses_message_id' => $message->ses_message_id,
                'event_type' => $type,
                'recipient_email' => $message->recipient_email,
                'source' => 'internal',
                'occurred_at' => now(),
                'metadata' => $metadata ?: null,
            ]
        );
    }
}
