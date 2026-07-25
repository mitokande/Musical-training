<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Project API Key
    |--------------------------------------------------------------------------
    |
    | The "phc_..." project key from PostHog → Settings → Project. This key is
    | public by design — it is rendered into the browser snippet — so it is the
    | same value on both the server and the client. Leaving it empty disables
    | PostHog everywhere: the service becomes a no-op and the snippet is not
    | rendered, so local and CI runs never phone home.
    |
    */

    'key' => env('POSTHOG_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Host
    |--------------------------------------------------------------------------
    |
    | EU Cloud, matching the rest of the stack (SES eu-central-1) and keeping
    | EU user data in the EU. "assets_host" only matters to the browser: the
    | snippet loads its bundle from there and proxies API traffic to "host".
    |
    */

    'host' => env('POSTHOG_HOST', 'https://eu.i.posthog.com'),
    'assets_host' => env('POSTHOG_ASSETS_HOST', 'https://eu-assets.i.posthog.com'),

    /*
    |--------------------------------------------------------------------------
    | Kill Switch
    |--------------------------------------------------------------------------
    |
    | Set POSTHOG_ENABLED=false to silence PostHog without removing the key.
    | Useful for turning tracking off in staging or during an incident.
    |
    */

    'enabled' => env('POSTHOG_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Server-side Capture
    |--------------------------------------------------------------------------
    |
    | "errors" sends unhandled exceptions to PostHog error tracking from the
    | reportable callback in bootstrap/app.php. "events" covers the explicit
    | business events (registration, subscriptions) captured server-side so
    | ad-blockers cannot drop them.
    |
    */

    'capture_errors' => env('POSTHOG_CAPTURE_ERRORS', true),
    'capture_events' => env('POSTHOG_CAPTURE_EVENTS', true),

    /*
    |--------------------------------------------------------------------------
    | Client-side Capture
    |--------------------------------------------------------------------------
    |
    | Session replay records user sessions, including on-screen text. Form
    | inputs stay masked, so what a user types is never recorded — but whatever
    | is rendered to the page is. Flip this off to drop replay entirely.
    |
    */

    'autocapture' => env('POSTHOG_AUTOCAPTURE', true),
    'session_replay' => env('POSTHOG_SESSION_REPLAY', true),

    /*
    |--------------------------------------------------------------------------
    | Consumer
    |--------------------------------------------------------------------------
    |
    | "lib_curl" is the SDK default: it queues messages and flushes them with a
    | 1ms curl timeout (fire-and-forget), so captures do not add latency to web
    | requests. Tests force "noop" so nothing leaves the machine.
    |
    */

    'consumer' => env('POSTHOG_CONSUMER', 'lib_curl'),

];
