<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\Analytics\PostHogService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;

/**
 * Mirrors the framework's auth events into PostHog.
 *
 * These are captured server-side because they are the top of the funnel: a
 * blocked browser script would otherwise leave signups missing while the paid
 * conversions further down still arrive, which quietly skews every funnel.
 *
 * Registered in AppServiceProvider::boot().
 */
class RecordAuthAnalytics
{
    public function __construct(private PostHogService $posthog) {}

    public function registered(Registered $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->posthog->capture('user_registered', [
            'role' => $event->user->role,
            // Social signups skip the password form entirely, so the two paths
            // are worth telling apart when comparing signup conversion.
            'method' => $event->user->google_id ? 'google' : 'password',
        ], $event->user);
    }

    public function login(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->posthog->capture('user_logged_in', [
            'role' => $event->user->role,
        ], $event->user);
    }

    public function verified(Verified $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->posthog->capture('email_verified', [], $event->user);
    }
}
