<?php

namespace App\Services\Zoom;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin wrapper over the Zoom REST API using Server-to-Server OAuth.
 *
 * Held as a container singleton (see AppServiceProvider::register) so the
 * access token is fetched at most once per process; tests swap the binding for
 * a fake and never touch the network. Every method throws on failure — callers
 * decide whether a Zoom outage should be fatal (it never is for confirming a
 * lesson; see ZoomMeetingProvider).
 *
 * Nothing here logs meeting ids, join urls, ZAKs or credentials.
 */
class ZoomClient
{
    private const TOKEN_CACHE_KEY = 'zoom-s2s-token';

    /** Whether the S2S credentials are present. Empty credentials disable Zoom. */
    public function configured(): bool
    {
        return filled(config('services.zoom.account_id'))
            && filled(config('services.zoom.client_id'))
            && filled(config('services.zoom.client_secret'));
    }

    /**
     * Account-credentials access token. Zoom issues these with a 1h lifetime;
     * we cache slightly under that so a request never races the expiry.
     */
    public function accessToken(): string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, now()->addMinutes(50), function () {
            $response = Http::asForm()
                ->withBasicAuth(
                    (string) config('services.zoom.client_id'),
                    (string) config('services.zoom.client_secret'),
                )
                ->timeout(10)
                ->retry(2, 200)
                ->post(config('zoom.oauth_url'), [
                    'grant_type' => 'account_credentials',
                    'account_id' => (string) config('services.zoom.account_id'),
                ])
                ->throw();

            $token = $response->json('access_token');

            if (! is_string($token) || $token === '') {
                throw new RuntimeException('Zoom did not return an access token.');
            }

            return $token;
        });
    }

    /**
     * Create a scheduled meeting on a pooled host.
     *
     * @param  array{topic: string, start_time: string, duration: int, timezone: string, agenda?: string}  $attributes
     * @return array{id: string, uuid: ?string, join_url: string, password: ?string}
     */
    public function createMeeting(string $zoomUserId, array $attributes): array
    {
        $payload = [
            'type' => 2, // scheduled meeting
            'topic' => $attributes['topic'],
            'start_time' => $attributes['start_time'],
            'duration' => $attributes['duration'],
            'timezone' => $attributes['timezone'],
            'agenda' => $attributes['agenda'] ?? null,
            'settings' => $this->meetingSettings(),
        ];

        $meeting = $this->request('post', "/users/{$zoomUserId}/meetings", $payload)->json();

        return [
            'id' => (string) ($meeting['id'] ?? ''),
            'uuid' => $meeting['uuid'] ?? null,
            'join_url' => (string) ($meeting['join_url'] ?? ''),
            'password' => $meeting['password'] ?? null,
        ];
    }

    /**
     * Move an existing meeting to a new time. Zoom returns 204 with no body.
     *
     * @param  array{topic?: string, start_time: string, duration: int, timezone: string}  $attributes
     */
    public function updateMeeting(string $meetingId, array $attributes): void
    {
        $this->request('patch', "/meetings/{$meetingId}", $attributes + [
            'settings' => $this->meetingSettings(),
        ]);
    }

    /**
     * Delete a meeting. A meeting that is already gone (404) is treated as
     * success — cancelling twice must not raise.
     */
    public function deleteMeeting(string $meetingId): void
    {
        try {
            $this->request('delete', "/meetings/{$meetingId}");
        } catch (RequestException $e) {
            if ($e->response->status() !== 404) {
                throw $e;
            }
        }
    }

    /**
     * Licensed users on the account — the candidates for the host pool.
     * Zoom's user `type` 2 = Licensed, 3 = On-prem. Free (1) users cannot host
     * meetings longer than 40 minutes, so they are excluded.
     *
     * @return array<array{id: string, email: string, name: string}>
     */
    public function listLicensedUsers(): array
    {
        $users = [];
        $token = null;

        do {
            $page = $this->request('get', '/users', [
                'status' => 'active',
                'page_size' => 100,
                'next_page_token' => $token,
            ])->json();

            foreach ($page['users'] ?? [] as $user) {
                if ((int) ($user['type'] ?? 1) < 2) {
                    continue;
                }

                $users[] = [
                    'id' => (string) $user['id'],
                    'email' => (string) ($user['email'] ?? ''),
                    'name' => trim(($user['first_name'] ?? '').' '.($user['last_name'] ?? '')),
                ];
            }

            $token = $page['next_page_token'] ?? null;
        } while (filled($token));

        return $users;
    }

    /**
     * Zoom Access Key for a host — the credential that lets the Meeting SDK
     * start a meeting as that host. Short-lived by design and fetched only at
     * the moment a teacher opens the Lesson Room, never stored.
     */
    public function zakFor(string $zoomUserId): string
    {
        $zak = $this->request('get', "/users/{$zoomUserId}/token", ['type' => 'zak'])->json('token');

        if (! is_string($zak) || $zak === '') {
            throw new RuntimeException('Zoom did not return a ZAK token.');
        }

        return $zak;
    }

    /**
     * Meeting settings shared by create and update. Recording is explicitly
     * off: Phase 1 ships no recording, so no consent flow is required.
     */
    private function meetingSettings(): array
    {
        return [
            'join_before_host' => false,
            'waiting_room' => false,
            'host_video' => true,
            'participant_video' => true,
            'mute_upon_entry' => false,
            'approval_type' => 2, // no registration
            'auto_recording' => 'none',
            'meeting_authentication' => false,
        ];
    }

    /**
     * Authenticated call. A 401 means the cached token was invalidated at
     * Zoom's end (secret rotation, token revoked) — drop it and try once more
     * with a fresh one before giving up.
     */
    private function request(string $method, string $path, array $payload = [])
    {
        $payload = array_filter($payload, fn ($value) => $value !== null);

        $send = fn (string $token) => Http::withToken($token)
            ->timeout(10)
            ->retry(2, 200, throw: false)
            ->{$method}(config('zoom.api_base').$path, $payload);

        $response = $send($this->accessToken());

        if ($response->status() === 401) {
            Cache::forget(self::TOKEN_CACHE_KEY);
            $response = $send($this->accessToken());
        }

        return $response->throw();
    }
}
