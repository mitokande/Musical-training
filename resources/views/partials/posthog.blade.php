{{--
    PostHog browser client.

    Renders only when a project key is configured, so local development and the
    test suite stay silent without needing an environment-specific @production
    guard. Settings are handed to resources/js/posthog.js, which Vite bundles —
    the SDK is version-pinned in package.json rather than pulled from a CDN.

    Person properties mirror PostHogService::personProperties() on the server so
    a profile looks the same whichever side wrote it last. Name and email are
    deliberately omitted; PostHog segments on role/plan/locale, not identity.
--}}
@if (config('posthog.enabled') && config('posthog.key'))
    @php
        // Built inside @php rather than with @json(): Blade's directive parser
        // does not survive a multi-line array literal like this one.
        $posthogUser = auth()->user();

        $posthogSettings = json_encode([
            'key' => config('posthog.key'),
            'apiHost' => config('posthog.host'),
            'autocapture' => (bool) config('posthog.autocapture', true),
            'sessionReplay' => (bool) config('posthog.session_replay', true),
            'user' => $posthogUser ? [
                'id' => (string) $posthogUser->id,
                'properties' => [
                    'role' => $posthogUser->role,
                    'plan' => $posthogUser->plan,
                    'locale' => $posthogUser->locale,
                    'country' => $posthogUser->country,
                    'signed_up_at' => $posthogUser->created_at?->toIso8601String(),
                ],
            ] : null,
            // JSON_HEX_TAG escapes "<" so a value can never close the script tag.
        ], JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @endphp

    <script>window.__posthogSettings = {!! $posthogSettings !!};</script>
    @vite('resources/js/posthog.js')
@endif
