<?php

namespace App\Services\EmailCenter;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Validates Amazon SNS HTTP(S) messages per the official algorithm:
 * signing-cert host allow-list, canonical string reconstruction and
 * RSA signature verification (SignatureVersion 1 = SHA1, 2 = SHA256),
 * plus topic ARN allow-list and timestamp sanity checks.
 */
class SnsMessageValidator
{
    public function validate(array $payload): bool
    {
        foreach (['Type', 'MessageId', 'TopicArn', 'Message', 'Timestamp', 'SignatureVersion', 'Signature', 'SigningCertURL'] as $field) {
            if (empty($payload[$field])) {
                Log::warning('SNS message missing field', ['field' => $field]);

                return false;
            }
        }

        if (! $this->isAllowedTopic($payload['TopicArn'])) {
            Log::warning('SNS message from unexpected topic', ['topic' => $payload['TopicArn']]);

            return false;
        }

        if (! $this->isValidTimestamp($payload['Timestamp'])) {
            Log::warning('SNS message timestamp out of tolerance', ['timestamp' => $payload['Timestamp']]);

            return false;
        }

        if (! $this->isValidCertUrl($payload['SigningCertURL'])) {
            Log::warning('SNS signing cert URL rejected', ['url' => $payload['SigningCertURL']]);

            return false;
        }

        return $this->verifySignature($payload);
    }

    public function isAllowedTopic(string $topicArn): bool
    {
        $allowed = config('email-center.allowed_topic_arns', []);

        return $allowed !== [] && in_array($topicArn, $allowed, true);
    }

    protected function isValidTimestamp(string $timestamp): bool
    {
        $time = strtotime($timestamp);

        if ($time === false) {
            return false;
        }

        // SNS retries HTTP endpoints for hours; idempotency handles true
        // replays, this only blocks grossly stale or future-dated messages.
        return $time > now()->subDay()->timestamp && $time < now()->addMinutes(15)->timestamp;
    }

    protected function isValidCertUrl(string $url): bool
    {
        $parts = parse_url($url);

        return ($parts['scheme'] ?? '') === 'https'
            && preg_match('/^sns\.[a-z0-9\-]+\.amazonaws\.com$/', $parts['host'] ?? '')
            && str_ends_with($parts['path'] ?? '', '.pem');
    }

    protected function verifySignature(array $payload): bool
    {
        $certificate = Cache::remember(
            'sns-cert-'.sha1($payload['SigningCertURL']),
            now()->addDay(),
            fn () => Http::timeout(10)->get($payload['SigningCertURL'])->throw()->body()
        );

        $publicKey = openssl_pkey_get_public($certificate);

        if ($publicKey === false) {
            Log::error('SNS signing certificate could not be parsed');

            return false;
        }

        $stringToSign = $this->buildStringToSign($payload);
        $algorithm = ($payload['SignatureVersion'] ?? '1') === '2' ? OPENSSL_ALGO_SHA256 : OPENSSL_ALGO_SHA1;

        $valid = openssl_verify($stringToSign, base64_decode($payload['Signature']), $publicKey, $algorithm) === 1;

        if (! $valid) {
            Log::warning('SNS signature verification failed', ['message_id' => $payload['MessageId'] ?? null]);
        }

        return $valid;
    }

    protected function buildStringToSign(array $payload): string
    {
        $keys = $payload['Type'] === 'Notification'
            ? ['Message', 'MessageId', 'Subject', 'Timestamp', 'TopicArn', 'Type']
            : ['Message', 'MessageId', 'SubscribeURL', 'Timestamp', 'Token', 'TopicArn', 'Type'];

        $string = '';

        foreach ($keys as $key) {
            if (array_key_exists($key, $payload) && $payload[$key] !== null) {
                $string .= $key."\n".$payload[$key]."\n";
            }
        }

        return $string;
    }
}
