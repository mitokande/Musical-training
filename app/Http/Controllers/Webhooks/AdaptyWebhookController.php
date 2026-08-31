<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Payments\AdaptyEventProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Receives Adapty's server-side events — the mobile app's App Store and Play
 * Store subscriptions.
 *
 * This is the only trustworthy way the backend can learn about a store
 * purchase. The app knows its own entitlement (Adapty's SDK reads it from the
 * receipt), but a client claiming "I am Premium" is a claim, not a fact, and it
 * cannot report the things that happen while the app is closed: a renewal at
 * 3am, a cancellation from the iOS Settings screen, a refund granted by Apple
 * three weeks later. Those only ever arrive here.
 *
 * Authenticated the same way Stripe's is — by a secret the caller must present,
 * not by a session — which is why the route is CSRF-exempt in bootstrap/app.php.
 * Adapty sends whatever header pair is configured in
 * Dashboard → Integrations → Webhook → "Authorization header"; set the same name
 * and value in ADAPTY_WEBHOOK_HEADER / ADAPTY_WEBHOOK_SECRET. If the workspace
 * also signs requests, set ADAPTY_WEBHOOK_SIGNING_SECRET and the HMAC over the
 * raw body is checked as well.
 *
 * Every event is recorded in the adapty_events ledger before it is applied, so a
 * re-delivery (Adapty retries anything that is not a 2xx) is acknowledged
 * without being processed twice.
 */
class AdaptyWebhookController extends Controller
{
    public function __construct(private AdaptyEventProcessor $processor) {}

    public function events(Request $request): JsonResponse
    {
        $secret = config('services.adapty.webhook_secret');
        if (! $secret) {
            Log::error('Adapty webhook received but no shared secret configured.');

            return response()->json(['error' => 'not configured'], 500);
        }

        if (! $this->authentic($request, $secret)) {
            return response()->json(['error' => 'invalid signature'], 401);
        }

        $payload = json_decode($request->getContent(), true);
        if (! is_array($payload)) {
            return response()->json(['error' => 'invalid payload'], 400);
        }

        $eventId = $this->eventId($payload, $request->getContent());

        // Idempotency: claim the event id; if it is already there, we have seen
        // this delivery. The payload is stored with it, because an event whose
        // profile has no account yet is parked in this same table and replayed
        // later — see AdaptyEventProcessor::replayDeferred().
        $claimed = DB::table('adapty_events')->insertOrIgnore([
            'event_id' => $eventId,
            'type' => $this->stringOrNull($payload['event_type'] ?? null),
            'profile_id' => $this->stringOrNull($payload['profile_id'] ?? null),
            'customer_user_id' => $this->stringOrNull($payload['customer_user_id'] ?? null),
            'payload' => $request->getContent(),
            'processed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($claimed === 0) {
            return response()->json(['status' => 'duplicate ignored']);
        }

        try {
            $handled = $this->processor->handle($payload);
        } catch (\Throwable $e) {
            // Release the claim so Adapty's retry can reprocess.
            DB::table('adapty_events')->where('event_id', $eventId)->delete();
            report($e);

            return response()->json(['error' => 'processing failed'], 500);
        }

        if ($handled) {
            DB::table('adapty_events')
                ->where('event_id', $eventId)
                ->update(['processed_at' => now(), 'updated_at' => now()]);

            return response()->json(['status' => 'ok']);
        }

        // Nobody owns this Adapty profile yet — a purchase made during
        // onboarding, before the account existed. processed_at stays null and
        // the row is applied the moment the app links the profile. Answering 200
        // is deliberate: this is a success, and a retry would only re-park it.
        Log::info('Adapty webhook deferred: profile not linked to an account yet', [
            'event_id' => $eventId,
            'profile_id' => $payload['profile_id'] ?? null,
        ]);

        return response()->json(['status' => 'deferred']);
    }

    /**
     * Constant-time check of the shared secret, plus the HMAC signature when the
     * workspace is configured to send one.
     */
    private function authentic(Request $request, string $secret): bool
    {
        $header = (string) config('services.adapty.webhook_header', 'Authorization');
        $presented = (string) $request->header($header, '');

        // Tolerate the value being configured with or without a Bearer prefix on
        // either side, so the dashboard field can be filled in either style.
        $presented = preg_replace('/^Bearer\s+/i', '', trim($presented)) ?? '';
        $expected = preg_replace('/^Bearer\s+/i', '', trim($secret)) ?? '';

        if ($presented === '' || ! hash_equals($expected, $presented)) {
            return false;
        }

        $signingSecret = config('services.adapty.signing_secret');
        if (! $signingSecret) {
            return true;
        }

        $signature = (string) $request->header(
            (string) config('services.adapty.signature_header', 'Adapty-Signature'),
            '',
        );

        return $signature !== '' && hash_equals(
            hash_hmac('sha256', $request->getContent(), $signingSecret),
            $signature,
        );
    }

    /**
     * The id this delivery is deduplicated by. Adapty carries one on the event;
     * a hash of the body is the fallback, which still collapses an identical
     * re-delivery into one.
     */
    private function eventId(array $payload, string $body): string
    {
        $id = $payload['event_id'] ?? $payload['id'] ?? null;

        return is_scalar($id) && (string) $id !== ''
            ? (string) $id
            : 'sha256:'.hash('sha256', $body);
    }

    private function stringOrNull(mixed $value): ?string
    {
        return is_scalar($value) && (string) $value !== '' ? (string) $value : null;
    }
}
