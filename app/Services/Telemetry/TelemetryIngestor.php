<?php

namespace App\Services\Telemetry;

use App\Models\AppEvent;
use App\Models\AppInstall;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Writes one batch from the mobile app's event outbox.
 *
 * Lenient per event, strict per batch. The controller has already refused a
 * batch that is malformed as a whole — no device, no events array — and the
 * app drops a batch it is refused, because the same bytes would be refused
 * forever. So a single bad event must never cost its neighbours: it is
 * counted as rejected and skipped, and everything else lands.
 *
 * Replays are free. The outbox resends a batch whose response it never saw,
 * and `insertOrIgnore` on the event uuid turns the second copy into nothing.
 */
class TelemetryIngestor
{
    /** Event names are the app's snake_case vocabulary. */
    public const NAME_PATTERN = '/^[a-z][a-z0-9_]{0,63}$/';

    /**
     * A screen is an expo-router route pattern — `(tabs)/index`,
     * `learn/[lesson]/run` — never a concrete URL, so it is short and plain.
     */
    public const SCREEN_PATTERN = '/^[A-Za-z0-9_\-\/\[\]\(\)\.\+]{1,96}$/';

    /**
     * The largest `props` accepted, encoded. The biggest event the app sends
     * today is a few hundred bytes; anything near this is a bug, not data.
     */
    public const MAX_PROPS_BYTES = 4096;

    /**
     * Correct a device clock only when it is off by more than this. Below it
     * the "skew" is mostly the request's own flight time, and applying it
     * would shift every event by a network round trip for nothing.
     */
    private const SKEW_TOLERANCE_MS = 5_000;

    /**
     * @param  array{install_id: string, platform: string, os_version?: ?string, app_version?: ?string, build?: ?string, locale?: ?string, timezone?: ?string}  $device
     * @param  int  $sentAt  the device clock when the batch left, epoch ms
     * @param  array<int, mixed>  $events
     * @return array{accepted: int, duplicates: int, rejected: int}
     */
    public function ingest(array $device, int $sentAt, array $events, ?User $user): array
    {
        $now = CarbonImmutable::now();
        $skew = $now->getTimestampMs() - $sentAt;
        $correction = abs($skew) > self::SKEW_TOLERANCE_MS ? $skew : 0;
        $oldest = $now->subDays(AppEvent::RETENTION_DAYS);

        $rows = [];
        $rejected = 0;

        foreach ($events as $event) {
            $row = $this->row($event, $device, $user, $correction, $now, $oldest);

            if ($row === null) {
                $rejected++;

                continue;
            }

            // A uuid twice in one batch is a client bug; keep the first.
            $rows[$row['uuid']] ??= $row;
        }

        $rows = array_values($rows);

        return DB::transaction(function () use ($device, $user, $now, $rows, $rejected, $events) {
            $this->touchInstall($device, $user, $now);

            $accepted = 0;
            foreach (array_chunk($rows, 500) as $chunk) {
                $accepted += DB::table('app_events')->insertOrIgnore($chunk);
            }

            if ($user) {
                $this->attributeAnonymous($device['install_id'], $user);
            }

            return [
                'accepted' => $accepted,
                'duplicates' => count($events) - $rejected - $accepted,
                'rejected' => $rejected,
            ];
        });
    }

    /** The row for one event, or null when it cannot be stored. */
    private function row(
        mixed $event,
        array $device,
        ?User $user,
        int $correction,
        CarbonImmutable $now,
        CarbonImmutable $oldest,
    ): ?array {
        if (! is_array($event)) {
            return null;
        }

        $uuid = $event['uuid'] ?? null;
        $name = $event['name'] ?? null;
        $at = $event['at'] ?? null;
        $props = $event['props'] ?? [];

        if (! is_string($uuid) || ! Str::isUuid($uuid)) {
            return null;
        }
        if (! is_string($name) || ! preg_match(self::NAME_PATTERN, $name)) {
            return null;
        }
        if (! is_int($at) || $at <= 0) {
            return null;
        }
        if (! is_array($props)) {
            return null;
        }

        $encoded = json_encode((object) $props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encoded === false || strlen($encoded) > self::MAX_PROPS_BYTES) {
            return null;
        }

        // Never in the future: a device clock set ahead is corrected above,
        // and what is left over is the request's own flight time.
        $occurred = CarbonImmutable::createFromTimestampMs($at + $correction)->min($now);
        // Older than we keep: it would only be pruned tonight.
        if ($occurred->lt($oldest)) {
            return null;
        }

        $session = $event['session_id'] ?? null;

        return [
            'uuid' => strtolower($uuid),
            'user_id' => $user?->id,
            'install_id' => $device['install_id'],
            'session_id' => is_string($session) && Str::isUuid($session) ? strtolower($session) : null,
            'screen' => $this->screen($event['screen'] ?? null, $name, $props),
            'name' => $name,
            'props' => $encoded,
            'platform' => $device['platform'],
            'app_version' => $device['app_version'] ?? null,
            'occurred_at' => $occurred->format('Y-m-d H:i:s.v'),
            'received_at' => $now,
        ];
    }

    /**
     * The screen an event happened on: the one the app stamped it with, or —
     * for a build from before the stamp — a screen event's own. A value that is
     * not a route is dropped rather than the event: the screen is context, and
     * the event is still true without it.
     */
    private function screen(mixed $stamped, string $name, array $props): ?string
    {
        if ($stamped === null && $name === 'screen') {
            $stamped = $props['name'] ?? null;
        }

        return is_string($stamped) && preg_match(self::SCREEN_PATTERN, $stamped) ? $stamped : null;
    }

    /**
     * Records the install, and — when the batch is signed in — whose it is now.
     *
     * An anonymous batch never clears the link: a learner who signs out and
     * browses the onboarding again is still the last account seen here.
     */
    private function touchInstall(array $device, ?User $user, CarbonImmutable $now): void
    {
        $attributes = [
            'platform' => $device['platform'],
            'os_version' => $device['os_version'] ?? null,
            'app_version' => $device['app_version'] ?? null,
            'build' => $device['build'] ?? null,
            'locale' => $device['locale'] ?? null,
            'timezone' => $device['timezone'] ?? null,
            'last_seen_at' => $now,
        ];

        if ($user) {
            $attributes['user_id'] = $user->id;
        }

        AppInstall::upsert(
            [['install_id' => $device['install_id'], 'first_seen_at' => $now] + $attributes],
            ['install_id'],
            array_keys($attributes),
        );
    }

    /**
     * Files the install's anonymous events under the account now signed in.
     *
     * Onboarding runs before sign-up, so the answers that explain an account —
     * why they came, where they nearly left — arrive with nobody's name on them.
     * The app only ever sends a signed-in batch its own account's events and
     * anonymous ones (see `pendingEvents` in the app), so an anonymous event on
     * this install is this learner's, from before they signed in. Once done
     * this is a no-op: the index finds nothing left to update.
     */
    private function attributeAnonymous(string $installId, User $user): void
    {
        AppEvent::where('install_id', $installId)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);
    }
}
