<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\EmailCenter\SesEventProcessor;
use App\Services\EmailCenter\SnsMessageValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SesWebhookController extends Controller
{
    public function __construct(
        protected SnsMessageValidator $validator,
        protected SesEventProcessor $processor,
    ) {}

    public function events(Request $request)
    {
        $payload = json_decode($request->getContent(), true);

        if (! is_array($payload)) {
            return response()->json(['error' => 'invalid payload'], 400);
        }

        if (! $this->validator->validate($payload)) {
            return response()->json(['error' => 'validation failed'], 403);
        }

        // SNS-level replay guard on top of the per-event dedup hash
        $replayKey = 'sns-mid-'.($payload['MessageId'] ?? '');
        if (! Cache::add($replayKey, 1, now()->addHours(6))) {
            return response()->json(['status' => 'duplicate ignored']);
        }

        switch ($payload['Type']) {
            case 'SubscriptionConfirmation':
                Http::timeout(10)->get($payload['SubscribeURL']);
                Log::info('SNS subscription confirmed', ['topic' => $payload['TopicArn']]);
                break;

            case 'Notification':
                $event = json_decode($payload['Message'], true);
                if (is_array($event)) {
                    $this->processor->process($event);
                }
                break;

            case 'UnsubscribeConfirmation':
                Log::warning('SNS unsubscribe confirmation received', ['topic' => $payload['TopicArn']]);
                break;
        }

        return response()->json(['status' => 'ok']);
    }
}
