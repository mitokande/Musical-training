<?php

/*
 * Admin Ad Studio — generates and renders HyperFrames ad creatives.
 *
 * The studio writes a real HyperFrames project per creative under `projects_root`
 * and shells out to the pinned HyperFrames CLI to render it. Nothing here is
 * reachable by non-admins; every path below is server-local.
 */

return [

    // Master switch. With this false the sidebar entry disappears and every
    // route 404s — the safe state for an environment with no ffmpeg/node.
    'enabled' => env('AD_STUDIO_ENABLED', true),

    /*
     * Where generated projects live, relative to base_path(). They sit beside
     * the hand-authored variants (harmoniva-promo, harmoniva-tiktok, …) because
     * they are the same kind of artefact and share the same asset conventions.
     */
    'projects_root' => 'hyperframes/videos',

    // Template stubs — tokenized copies of the hand-authored variants.
    'templates_root' => 'resources/ad-templates',

    /*
     * CLI. The version is pinned so a creative re-renders identically over time;
     * bumping it is a deliberate act, exactly as in a scaffolded project's
     * package.json. `npx` resolves through the node install on PATH.
     */
    'cli' => [
        'npx' => env('AD_STUDIO_NPX', 'npx'),
        'version' => env('AD_STUDIO_HYPERFRAMES_VERSION', '0.7.81'),
        // A 30s 1080x1920 render measured ~3 min single-worker on this box.
        // The ceiling is generous because a killed render leaves a half-written
        // MP4 that the panel would have to explain.
        'render_timeout' => (int) env('AD_STUDIO_RENDER_TIMEOUT', 1500),
        'check_timeout' => (int) env('AD_STUDIO_CHECK_TIMEOUT', 420),
    ],

    /*
     * Narration. Gemini TTS is used directly rather than a HyperFrames bundled
     * provider — same call the hand-authored variants make from
     * scripts/gemini-tts.mjs, ported so the panel can measure durations itself.
     *
     * An empty key disables voice generation: the editor still builds and
     * renders, using whatever takes are already cached on disk.
     */
    'tts' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('AD_STUDIO_TTS_MODEL', 'gemini-2.5-flash-preview-tts'),
        'sample_rate' => 24000,
        'timeout' => (int) env('AD_STUDIO_TTS_TIMEOUT', 120),
        'retries' => 4,

        // The preview endpoint is the only Gemini voice surface that takes
        // direction as prose, so the style instruction is part of the prompt.
        'voices' => [
            'Kore' => 'Kore — warm female, upbeat (used by every shipped variant)',
            'Puck' => 'Puck — bright male, playful',
            'Charon' => 'Charon — low male, measured',
            'Aoede' => 'Aoede — soft female, conversational',
        ],

        /*
         * Gemini reads the brand name as "harmonica" — close enough to a real
         * word that the model snaps to it. Respellings are applied to the
         * synthesis prompt only; the stored script keeps the real spelling.
         */
        'say_as' => [
            'Harmoniva' => 'Harmoneeva',
            'harmoniva' => 'harmoneeva',
        ],
    ],

    // ffmpeg/ffprobe are required for silence-trimming and duration measurement.
    // Without them the studio cannot compute frame windows, so voice generation
    // hard-fails rather than guessing.
    'ffmpeg' => env('AD_STUDIO_FFMPEG', 'ffmpeg'),
    'ffprobe' => env('AD_STUDIO_FFPROBE', 'ffprobe'),
];
