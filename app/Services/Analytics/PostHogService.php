<?php

namespace App\Services\Analytics;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use PostHog\PostHog;
use Throwable;

/**
 * Thin wrapper around the PostHog PHP SDK.
 *
 * Every public method is best-effort: analytics must never break a request, so
 * failures are swallowed and logged rather than thrown back into the caller.
 * When no project key is configured the whole service is an inert no-op, which
 * is what keeps local development and the test suite from phoning home.
 */
class PostHogService
{
    private bool $booted = false;

    private ?bool $enabled = null;

    /**
     * Whether PostHog is configured and switched on.
     */
    public function enabled(): bool
    {
        return $this->enabled ??= config('posthog.enabled', true)
            && filled(config('posthog.key'));
    }

    /**
     * Record an event.
     *
     * Passing a $user both attributes the event to that person and refreshes
     * their person properties, so the profile stays current without a separate
     * identify() call on every hit.
     */
    public function capture(string $event, array $properties = [], ?User $user = null): void
    {
        if (! $this->enabled() || ! config('posthog.capture_events', true)) {
            return;
        }

        if ($user) {
            $properties['$set'] = array_merge(
                $this->personProperties($user),
                $properties['$set'] ?? []
            );
        }

        $this->send(fn () => PostHog::capture([
            'distinctId' => $this->distinctId($user),
            'event' => $event,
            'properties' => $properties,
        ]));
    }

    /**
     * Attach person properties to a user without recording a separate event.
     */
    public function identify(User $user, array $properties = []): void
    {
        if (! $this->enabled() || ! config('posthog.capture_events', true)) {
            return;
        }

        $this->send(fn () => PostHog::identify([
            'distinctId' => (string) $user->id,
            'properties' => array_merge($this->personProperties($user), $properties),
        ]));
    }

    /**
     * Send an exception to PostHog error tracking.
     */
    public function captureException(Throwable $e, array $context = []): void
    {
        if (! $this->enabled() || ! config('posthog.capture_errors', true)) {
            return;
        }

        $this->send(fn () => PostHog::captureException(
            $e,
            $this->distinctId($this->currentUser()),
            $context
        ));
    }

    /**
     * Resolve the identity an event belongs to.
     *
     * Authenticated traffic is keyed on the user id. Anonymous traffic reuses
     * the device id posthog-js already stored in its cookie, so server-side and
     * browser-side events for the same visitor land on one person instead of
     * two — and pre-signup events still stitch to the account on registration.
     */
    public function distinctId(?User $user = null): string
    {
        $user ??= $this->currentUser();

        if ($user) {
            return (string) $user->id;
        }

        return $this->cookieDistinctId() ?? 'anonymous';
    }

    /**
     * Read posthog-js's distinct id out of its own cookie, if the visitor has one.
     */
    private function cookieDistinctId(): ?string
    {
        $name = 'ph_'.config('posthog.key').'_posthog';

        $raw = $_COOKIE[$name] ?? null;

        if (! is_string($raw) || $raw === '') {
            return null;
        }

        $decoded = json_decode(urldecode($raw), true);

        $distinctId = is_array($decoded) ? ($decoded['distinct_id'] ?? null) : null;

        return is_string($distinctId) && $distinctId !== '' ? $distinctId : null;
    }

    /**
     * The person properties mirrored onto every PostHog profile.
     *
     * Deliberately excludes name and email: PostHog is an analytics store, not
     * a second copy of the user table, and role/plan/locale are what the funnels
     * actually segment on.
     */
    private function personProperties(User $user): array
    {
        return [
            'role' => $user->role,
            'plan' => $user->plan,
            'locale' => $user->locale,
            'country' => $user->country,
            'signed_up_at' => $user->created_at?->toIso8601String(),
        ];
    }

    private function currentUser(): ?User
    {
        // auth() resolves the session guard, which does not exist in console
        // context (queued jobs, artisan commands) — hence the rescue().
        return rescue(fn () => auth()->user(), null, false);
    }

    /**
     * Boot the SDK once per process, then run the given capture call.
     */
    private function send(callable $call): void
    {
        try {
            $this->boot();

            $call();
        } catch (Throwable $e) {
            Log::warning('PostHog capture failed', ['exception' => $e->getMessage()]);
        }
    }

    private function boot(): void
    {
        if ($this->booted) {
            return;
        }

        PostHog::init((string) config('posthog.key'), [
            'host' => config('posthog.host'),
            'consumer' => config('posthog.consumer', 'lib_curl'),
            // Without a handler the SDK writes transport errors to stderr.
            'error_handler' => function ($code, $message) {
                Log::warning('PostHog transport error', ['code' => $code, 'message' => $message]);
            },
        ]);

        $this->booted = true;
    }
}
