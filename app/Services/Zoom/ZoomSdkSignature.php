<?php

namespace App\Services\Zoom;

use RuntimeException;

/**
 * Signs the short-lived JWT the browser Meeting SDK joins with.
 *
 * This uses the Meeting SDK app's key/secret, which are a different credential
 * pair from the Server-to-Server OAuth app used by ZoomClient. The secret never
 * leaves the server: the browser receives only the resulting signature and the
 * (public) SDK key, handed out by LessonRoomController::signature() after it
 * re-checks every authorisation guard.
 *
 * HS256 is implemented inline rather than pulling in a JWT package — the claim
 * set is fixed and three lines of hashing is less surface than a dependency.
 */
class ZoomSdkSignature
{
    public const ROLE_PARTICIPANT = 0;

    public const ROLE_HOST = 1;

    public function configured(): bool
    {
        return filled(config('services.zoom.sdk_key')) && filled(config('services.zoom.sdk_secret'));
    }

    /**
     * @param  string  $meetingNumber  Numeric Zoom meeting id.
     * @param  int  $role  ROLE_HOST for the teacher, ROLE_PARTICIPANT for the student.
     */
    public function generate(string $meetingNumber, int $role): string
    {
        if (! $this->configured()) {
            throw new RuntimeException('Zoom Meeting SDK credentials are not configured.');
        }

        $key = (string) config('services.zoom.sdk_key');
        $secret = (string) config('services.zoom.sdk_secret');

        $issuedAt = time() - 30; // clock-skew allowance
        $expiresAt = $issuedAt + (int) config('zoom.signature_ttl');

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $claims = [
            'appKey' => $key,
            'sdkKey' => $key,
            'mn' => $meetingNumber,
            'role' => $role,
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'tokenExp' => $expiresAt,
        ];

        $payload = $this->encode($header).'.'.$this->encode($claims);
        $signature = hash_hmac('sha256', $payload, $secret, true);

        return $payload.'.'.$this->base64Url($signature);
    }

    private function encode(array $segment): string
    {
        return $this->base64Url(json_encode($segment, JSON_UNESCAPED_SLASHES));
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
