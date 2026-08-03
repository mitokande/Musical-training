<?php

/*
 * Zoom live-lesson behaviour.
 *
 * Credentials live in config/services.php under the `zoom` key; this file holds
 * only behaviour flags. The feature is inert unless `enabled` is true AND the
 * Server-to-Server OAuth credentials are present (see ZoomClient::configured()),
 * which is how local dev and CI stay offline without any extra setup.
 *
 * Meetings are hosted on a small pool of Harmoniva-owned licensed Zoom users
 * (`zoom_hosts`), allocated per lesson by ZoomHostAllocator. Teachers never own
 * a Zoom licence. When the pool is exhausted the appointment simply falls back
 * to the manual meeting-link flow — confirming a lesson never fails because of
 * Zoom.
 */

return [

    // Master switch. False keeps every appointment on the manual provider.
    'enabled' => (bool) env('ZOOM_ENABLED', false),

    'api_base' => 'https://api.zoom.us/v2',
    'oauth_url' => 'https://zoom.us/oauth/token',

    // Lesson Room join window, relative to the appointment.
    'join' => [
        'opens_minutes_before' => (int) env('ZOOM_JOIN_OPENS_MINUTES', 10),
        'closes_minutes_after' => (int) env('ZOOM_JOIN_CLOSES_MINUTES', 15),
    ],

    // Padding around a lesson during which a host counts as busy, so a meeting
    // that runs over does not collide with the next lesson on the same licence.
    'host_buffer_minutes' => (int) env('ZOOM_HOST_BUFFER_MINUTES', 5),

    // Zoom REST calls per second. Zoom rate-limits per endpoint; this is a
    // conservative global ceiling for the queued/interactive calls we make.
    'api_rate_per_second' => (int) env('ZOOM_API_RATE', 5),

    // Meeting SDK JWT lifetime (seconds). Must comfortably outlast a lesson.
    'signature_ttl' => (int) env('ZOOM_SIGNATURE_TTL', 7200),
];
