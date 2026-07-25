import posthog from 'posthog-js';

/**
 * PostHog browser client.
 *
 * Configuration is handed over from Blade (see resources/views/partials/posthog.blade.php)
 * rather than baked in at build time, so the key, host and per-user identity stay
 * server-rendered and the same bundle works across every environment.
 */
const settings = window.__posthogSettings;

if (settings && settings.key) {
    posthog.init(settings.key, {
        api_host: settings.apiHost,

        // Opt in to the current default set instead of the legacy ones kept for
        // backwards compatibility with older installs.
        defaults: '2026-06-25',

        // Anonymous traffic still sends events, it just does not create a person
        // profile until the visitor registers or logs in.
        person_profiles: 'identified_only',

        autocapture: settings.autocapture,

        // Frontend error tracking — unhandled errors and promise rejections.
        capture_exceptions: true,

        disable_session_recording: !settings.sessionReplay,
        session_recording: {
            // On-screen text stays visible so replays are actually readable.
            // Inputs remain masked, which keeps whatever the user types — email,
            // password, message bodies — out of the recording.
            maskAllInputs: true,
        },
    });

    if (settings.user) {
        posthog.identify(settings.user.id, settings.user.properties);
    } else if (posthog.get_property('$is_identified')) {
        // Nobody is logged in but this browser still carries an identity from a
        // previous session — clear it so the next visitor on a shared device is
        // not attributed to the account that logged out. Guarded by the check so
        // ordinary anonymous page views keep their device id (and session) intact.
        posthog.reset();
    }

    // Let inline page scripts reach the client without importing the module.
    window.posthog = posthog;
}
