<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Payments\StripeEventProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

/**
 * Receives Stripe Billing webhooks. Every event is signature-verified against the
 * endpoint's signing secret, then recorded in the stripe_events ledger so a
 * re-delivered event (Stripe retries any non-2xx, and occasionally duplicates) is
 * acknowledged without being processed twice. This route is CSRF-exempt
 * (registered in bootstrap/app.php) — the signature IS its authentication.
 */
class StripeWebhookController extends Controller
{
    public function __construct(private StripeEventProcessor $processor) {}

    public function events(Request $request)
    {
        $secret = config('services.stripe.webhook_secret');
        if (! $secret) {
            Log::error('Stripe webhook received but no signing secret configured.');

            return response()->json(['error' => 'not configured'], 500);
        }

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                $secret,
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'invalid signature'], 400);
        }

        // Idempotency: claim the event id; if it already exists, we've handled it.
        $claimed = DB::table('stripe_events')->insertOrIgnore([
            'event_id' => $event->id,
            'type' => $event->type,
            'processed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        if ($claimed === 0) {
            return response()->json(['status' => 'duplicate ignored']);
        }

        try {
            $this->processor->handle($event->type, $event->data->object->toArray());
        } catch (\Throwable $e) {
            // Release the idempotency claim so Stripe's retry can reprocess.
            DB::table('stripe_events')->where('event_id', $event->id)->delete();
            report($e);

            return response()->json(['error' => 'processing failed'], 500);
        }

        return response()->json(['status' => 'ok']);
    }
}
