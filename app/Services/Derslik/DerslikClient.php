<?php

namespace App\Services\Derslik;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper over Derslik's REST API (/api/v1, Bearer key).
 *
 * Every method throws on failure (Illuminate\Http\Client\RequestException /
 * ConnectionException) — callers decide what an outage means. A 404 on end or
 * delete is treated as success: the room is already gone, which is the goal.
 *
 * Room objects carry teacherUrl (contains the teacher key — a credential) and
 * studentUrl. Nothing here logs either.
 */
class DerslikClient
{
    public function configured(): bool
    {
        return filled(config('services.derslik.url')) && filled(config('services.derslik.api_key'));
    }

    /** The origin the room iframe is served from, e.g. https://meeting.harmoniva.app */
    public function origin(): string
    {
        $parts = parse_url((string) config('services.derslik.url'));

        return ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '').(isset($parts['port']) ? ':'.$parts['port'] : '');
    }

    /**
     * @param  array<string, string>  $metadata  free-form string pairs stored on the room (≤20 keys)
     * @return array{id: string, title: string, teacherUrl: string, studentUrl: string}
     */
    public function createRoom(string $title, array $metadata = []): array
    {
        return $this->request()
            ->post('/rooms', array_filter([
                'title' => $title,
                'waitingRoom' => true,
                'metadata' => $metadata ?: null,
            ], fn ($v) => $v !== null))
            ->throw()
            ->json();
    }

    /** @return array<string, mixed> Room object including live{active, participants, teacherPresent} */
    public function room(string $roomId): array
    {
        return $this->request()->get('/rooms/'.rawurlencode($roomId))->throw()->json();
    }

    /** Disconnect everyone; the room itself stays. */
    public function endRoom(string $roomId): void
    {
        $response = $this->request()->post('/rooms/'.rawurlencode($roomId).'/end');

        if ($response->status() !== 404) {
            $response->throw();
        }
    }

    /** Delete the room — its links stop working. */
    public function deleteRoom(string $roomId): void
    {
        $response = $this->request()->delete('/rooms/'.rawurlencode($roomId));

        if ($response->status() !== 404) {
            $response->throw();
        }
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(config('services.derslik.url').'/api/v1')
            ->withToken((string) config('services.derslik.api_key'))
            ->acceptJson()
            ->timeout(8)
            ->connectTimeout(4);
    }
}
