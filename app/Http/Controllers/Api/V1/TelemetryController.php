<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Telemetry\TelemetryIngestor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The mobile app's event outbox, arriving in batches.
 *
 * Signed in or not. Most of onboarding happens before there is an account, and
 * the visitors who leave it without making one are exactly who the funnel has
 * to count — so the route sits outside `auth:sanctum` and asks the guard
 * itself. A token that no longer works is not a 401 here: the api client signs
 * the learner out on any 401, and telemetry must never be what does that. The
 * batch is simply taken as anonymous.
 *
 * Only the envelope is validated here. The events themselves are checked one
 * by one in TelemetryIngestor, because the app drops a batch this refuses, and
 * one malformed event must not take ninety-nine good ones down with it.
 */
class TelemetryController extends Controller
{
    /** The app sends at most 100; the slack is for a future build, not a license. */
    public const MAX_EVENTS = 200;

    public function store(Request $request, TelemetryIngestor $ingestor): JsonResponse
    {
        $validated = $request->validate([
            'device' => ['required', 'array'],
            'device.install_id' => ['required', 'uuid'],
            'device.platform' => ['required', 'string', 'in:ios,android,web'],
            'device.os_version' => ['nullable', 'string', 'max:32'],
            'device.app_version' => ['nullable', 'string', 'max:32'],
            'device.build' => ['nullable', 'string', 'max:32'],
            'device.locale' => ['nullable', 'string', 'max:35'],
            'device.timezone' => ['nullable', 'string', 'max:64'],
            'sent_at' => ['required', 'integer', 'min:1'],
            'events' => ['required', 'array', 'max:'.self::MAX_EVENTS],
        ]);

        $device = $validated['device'];
        $device['install_id'] = strtolower($device['install_id']);

        $counts = $ingestor->ingest(
            $device,
            (int) $validated['sent_at'],
            $request->input('events'),
            $request->user('sanctum'),
        );

        return response()->json(['data' => $counts], 202);
    }
}
