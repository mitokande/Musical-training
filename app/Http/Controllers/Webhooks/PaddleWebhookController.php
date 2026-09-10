<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaddleEventProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Receives Paddle Billing webhooks. Every event is signature-verified against
 * the notification destination's signing secret, then recorded in the
 * paddle_events ledger so a re-delivered event (Paddle retries any non-2xx) is
 * acknowledged without being processed twice. This route is CSRF-exempt
 * (registered in bootstrap/app.php) — the signature IS its authentication.
 *
 * The signature is verified here rather than through the SDK's Verifier, which
 * takes a PSR-7 request we would have to convert, and defaults to a 5-second
 * clock-skew window that this server cannot reliably meet.
 */
class PaddleWebhookController extends Controller
{
    public function __construct(private PaddleEventProcessor $processor) {}

    public function events(Request $request)
    {
        $secret = config('services.paddle.webhook_secret');
        if (! $secret) {
            Log::error('Paddle webhook received but no signing secret configured.');

            return response()->json(['error' => 'not configured'], 500);
        }

        $payload = $request->getContent();

        if (! $this->signatureIsValid($request->header('Paddle-Signature', ''), $payload, (string) $secret)) {
            return response()->json(['error' => 'invalid signature'], 400);
        }

        $event = json_decode($payload, true);
        if (! is_array($event) || empty($event['event_id']) || empty($event['event_type'])) {
            return response()->json(['error' => 'invalid payload'], 400);
        }

        // Idempotency: claim the event id; if it already exists, we've handled it.
        $claimed = DB::table('paddle_events')->insertOrIgnore([
            'event_id' => $event['event_id'],
            'type' => $event['event_type'],
            'processed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        if ($claimed === 0) {
            return response()->json(['status' => 'duplicate ignored']);
        }

        try {
            $this->processor->handle($event['event_type'], $event['data'] ?? []);
        } catch (\Throwable $e) {
            // Release the idempotency claim so Paddle's retry can reprocess.
            DB::table('paddle_events')->where('event_id', $event['event_id'])->delete();
            report($e);

            return response()->json(['error' => 'processing failed'], 500);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Paddle-Signature is `ts=<unix>;h1=<hmac>`, where the HMAC is SHA-256 over
     * "<ts>:<raw body>" keyed with the destination's secret. The timestamp is
     * part of the signed payload, so checking its age is what stops a captured
     * delivery from being replayed later.
     */
    private function signatureIsValid(string $header, string $payload, string $secret): bool
    {
        $timestamp = null;
        $hashes = [];

        foreach (explode(';', $header) as $part) {
            if (! str_contains($part, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $part, 2);
            match ($key) {
                'ts' => $timestamp = (int) $value,
                'h1' => $hashes[] = $value,
                default => null,
            };
        }

        if (! $timestamp || $hashes === []) {
            return false;
        }

        $tolerance = (int) config('services.paddle.signature_tolerance', 60);
        if ($tolerance > 0 && abs(time() - $timestamp) > $tolerance) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.':'.$payload, $secret);

        foreach ($hashes as $hash) {
            if (hash_equals($expected, $hash)) {
                return true;
            }
        }

        return false;
    }
}
