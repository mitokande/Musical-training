<!DOCTYPE html>
<html lang="{{ $seoHtmlLang }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- App-like fullscreen hints (removes browser chrome where supported / when installed) -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#111827">

    <title>{{ __('pages.piano_studio.meta_title') }} - {{ config('app.name', 'Harmoniva') }}</title>
    <meta name="description" content="{{ __('pages.piano_studio.meta_description') }}">
    @include('partials.public-seo-alt', [
        'seoPageTitle' => __('pages.piano_studio.meta_title'),
        'seoPageDescription' => __('pages.piano_studio.meta_description'),
    ])
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Harmoniva">
    <meta property="og:locale" content="{{ $seoOgLocale }}">
    <meta property="og:title" content="{{ __('pages.piano_studio.og_title') }}">
    <meta property="og:description" content="{{ __('pages.piano_studio.og_description') }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:image" content="{{ asset('images/og-image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('pages.piano_studio.tw_title') }}">
    <meta name="twitter:description" content="{{ __('pages.piano_studio.tw_description') }}">
    <meta name="twitter:image" content="{{ asset('images/og-image.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />

    <!-- Compiled Tailwind (marketing bundle) -->
    @vite('resources/css/marketing.css')

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@0.460.0"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <!-- Tone.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tone/15.3.5/Tone.js" integrity="sha512-F1myjNkIKU5XJtOs1HXRo/zOjiUsABgFEEGKLx/riwK82jRThZFebEnfF2HWo9eeC+iC1Nwwnn9Vj6OGq+r7rQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- VexFlow for music notation -->
    <script src="https://cdn.jsdelivr.net/npm/vexflow@4.2.2/build/cjs/vexflow.js"></script>


    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #9333ea 0%, #c084fc 35%, #f97316 100%);
        }
        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #7c3aed 0%, #6b21a8 100%);
        }
        .nav-item {
            transition: all 0.2s ease;
        }
        .nav-item:hover {
            background: #f3f4f6;
        }
        .nav-item.active {
            background: #f3f4f6;
            font-weight: 600;
        }

        /* Piano Styles */
        /* Wrapper is layout-transparent by default (mobile keeps piano-dock as an
           effective child of <main>); desktop turns it into the black stage. */
        .piano-stage { display: contents; }

        .piano-wrapper {
            overflow-x: auto;
            padding-bottom: 10px;
            width: 100%;
        }
        
        .piano-container {
            display: flex;
            justify-content: center;
            position: relative;
            user-select: none;
            width: 100%;
        }
        
        .piano-keys-container {
            display: flex;
            position: relative;
            width: 100%;
        }
        
        .piano-key {
            cursor: pointer;
            transition: all 0.08s ease;
            position: relative;
            touch-action: none;
            -webkit-tap-highlight-color: transparent;
            -webkit-user-select: none;
        }
        
        .white-key {
            flex: 1;
            min-width: 36px;
            height: 200px;
            background: linear-gradient(180deg, #fefefe 0%, #f5f5f5 100%);
            border: 1px solid #d1d5db;
            border-radius: 0 0 5px 5px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1), inset 0 -2px 4px rgb(0 0 0 / 0.05);
            z-index: 1;
        }
        
        .white-key:hover {
            background: linear-gradient(180deg, #f0f0f0 0%, #e8e8e8 100%);
        }
        
        .white-key.active {
            background: linear-gradient(180deg, #c084fc 0%, #a855f7 100%);
            transform: translateY(2px);
            box-shadow: 0 2px 4px -1px rgb(0 0 0 / 0.1);
        }
        
        .black-key {
            height: 120px;
            min-width: 22px;
            background: linear-gradient(180deg, #374151 0%, #1f2937 100%);
            border-radius: 0 0 3px 3px;
            position: absolute;
            z-index: 2;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.3), inset 0 -4px 8px rgb(0 0 0 / 0.3);
        }
        
        .black-key:hover {
            background: linear-gradient(180deg, #4b5563 0%, #374151 100%);
        }
        
        .black-key.active {
            background: linear-gradient(180deg, #7c3aed 0%, #6b21a8 100%);
            transform: translateY(2px);
            height: 118px;
            box-shadow: 0 2px 4px -1px rgb(0 0 0 / 0.2);
        }

        .key-label {
            position: absolute;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 10px;
            color: #9ca3af;
            font-weight: 500;
        }

        .black-key .key-label {
            color: #9ca3af;
        }

        /* Notation container */
        #notation-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            min-height: 240px;
            overflow-x: auto;
            scroll-behavior: smooth;
        }

        #notation-container svg {
            display: block;
            margin: 0 auto;
        }

        /* The staff keeps a CONSTANT size and the area scrolls horizontally as
           notes are added — override the site-wide shrink-to-fit that
           responsive-notation.blade.php applies (inline maxWidth:100% on >=640px). */
        #notation-container svg { max-width: none !important; }

        /* Playback animation */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .playing {
            animation: pulse 0.5s ease-in-out infinite;
        }

        /* Sidebar Layout */
        .studio-layout {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .sidebar {
            width: 240px;
            flex-shrink: 0;
            position: sticky;
            top: 80px;
        }

        .sidebar-left {
            order: 1;
        }

        .center-content {
            flex: 1;
            min-width: 0;
            order: 2;
        }

        .sidebar-right {
            order: 3;
        }

        /* Metronome Styles - Lean Design */
        .metronome-widget {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            padding: 1rem;
        }

        .metronome-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .metronome-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .bpm-display-inline {
            display: flex;
            align-items: baseline;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            background: #f9fafb;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }

        .bpm-value-inline {
            font-size: 1.125rem;
            font-weight: 700;
            color: #7c3aed;
        }

        .bpm-label-inline {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 500;
        }

        .metronome-controls {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .metronome-play-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .metronome-play-btn.start {
            background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%);
            color: white;
        }

        .metronome-play-btn.start:hover {
            background: linear-gradient(135deg, #7c3aed 0%, #6b21a8 100%);
        }

        .metronome-play-btn.stop {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .metronome-play-btn.stop:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }

        .slider-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .bpm-slider-lean {
            width: 100%;
            height: 6px;
            -webkit-appearance: none;
            appearance: none;
            background: linear-gradient(to right, #c084fc 0%, #e5e7eb 0%);
            border-radius: 3px;
            outline: none;
        }

        .bpm-slider-lean::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 14px;
            height: 14px;
            background: #7c3aed;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 2px 4px rgb(0 0 0 / 0.2);
            transition: transform 0.15s ease;
        }

        .bpm-slider-lean::-webkit-slider-thumb:hover {
            transform: scale(1.15);
        }

        .slider-labels {
            display: flex;
            justify-content: space-between;
            font-size: 0.625rem;
            color: #9ca3af;
        }

        /* Beat indicator bars */
        .beat-indicator-bars {
            display: flex;
            gap: 4px;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e5e7eb;
        }

        .beat-bar {
            flex: 1;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            transition: all 0.1s ease;
        }

        .beat-bar.active {
            background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%);
            box-shadow: 0 0 6px rgba(147, 51, 234, 0.4);
        }

        /* Hidden elements for lean design */
        .beat-indicator {
            display: none;
        }

        /* Tempo preset dropdown - lean style */
        .tempo-preset-lean {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e5e7eb;
        }

        .tempo-preset-lean select {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 0.8rem;
            color: #374151;
            background: white;
            cursor: pointer;
            transition: border-color 0.2s ease;
        }

        .tempo-preset-lean select:hover {
            border-color: #c084fc;
        }

        .tempo-preset-lean select:focus {
            outline: none;
            border-color: #9333ea;
            box-shadow: 0 0 0 2px rgba(147, 51, 234, 0.1);
        }

        /* Playback Sidebar Styles */
        .playback-widget {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            padding: 1rem;
        }

        .playback-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .playback-btn-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .playback-btn-group button {
            width: 100%;
        }

        .note-count-display {
            text-align: center;
            padding: 0.75rem;
            background: #f9fafb;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .note-count-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #9333ea;
        }

        .note-count-label {
            font-size: 0.75rem;
            color: #6b7280;
        }

        /* ============================================================
           MOBILE STUDIO EXPERIENCE
           ============================================================ */
        [x-cloak] { display: none !important; }

        .mobile-studio-bar { display: none; }

        @media (min-width: 1025px) {
            .mobile-controls { display: none; }

            /* Desktop: full-bleed black "stage" behind the piano — the site's
               dark colour fills the whole bottom band edge-to-edge, and the
               framed piano sits centred on top (black shows OUTSIDE its frame). */
            html { overflow-x: hidden; }          /* contain the full-bleed 100vw */
            .piano-stage {
                display: block;
                width: 100vw;
                margin-left: calc(50% - 50vw);
                margin-top: 1rem;
                padding: 30px 16px 116px;         /* black: 30px above, 116px below the piano frame */
                background: #111827;
            }
            .piano-dock {
                margin: 0 auto;                   /* centre the framed piano on the stage */
                max-width: 1520px;
                background: #fff;                 /* keep the piano's own white frame */
            }
            .piano-dock .piano-wrapper { padding-bottom: 0; }
        }

        /* --- Shared toolbar look (portrait + landscape phones) --- */
        @media (max-width: 1024px) {
            .sidebar { display: none; }
            .mobile-controls { display: none; }

            .mobile-studio-bar {
                display: block;
                background: rgba(255, 255, 255, 0.97);
                -webkit-backdrop-filter: blur(8px);
                backdrop-filter: blur(8px);
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                margin-bottom: 1rem;
                box-shadow: 0 2px 8px rgb(0 0 0 / 0.06);
            }
            .msb-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.5rem;
                padding: 0.55rem 0.65rem;
            }
            .msb-left { position: relative; }
            .msb-center {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            .msb-count {
                display: flex;
                flex-direction: column;
                line-height: 1.1;
                padding-left: 0.35rem;
                min-width: 40px;
            }
            .msb-count span { font-size: 1.05rem; font-weight: 700; color: #9333ea; }
            .msb-count small { font-size: 0.58rem; color: #6b7280; }

            /* Section tags flanking the controls — each fills the empty gap
               on its side and centers its own text */
            .msb-tag {
                flex: 1;
                min-width: 0;
                text-align: center;
                font-size: 0.8rem;
                font-weight: 600;
                color: #6b7280;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                padding: 0 0.15rem;
            }

            .msb-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.4rem;
                padding: 0.5rem 0.75rem;
                border-radius: 9px;
                font-size: 0.82rem;
                font-weight: 600;
                background: #f3f4f6;
                color: #374151;
                border: none;
                cursor: pointer;
                transition: transform 0.12s ease, background 0.15s ease;
            }
            .msb-btn:active { transform: scale(0.95); }
            .msb-btn:disabled { opacity: 0.45; cursor: not-allowed; }
            .msb-btn-primary { background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%); color: #fff; }
            .msb-metro-toggle.active { background: #ede9fe; color: #7c3aed; }
            .msb-menu-btn { background: #f3f4f6; color: #374151; padding: 0.5rem; }

            /* --- Metronome dropdown panel (anchored under the left button) --- */
            .msb-panel {
                position: absolute;
                top: calc(100% + 8px);
                left: 0;
                width: min(280px, 82vw);
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                box-shadow: 0 12px 32px rgb(0 0 0 / 0.18);
                padding: 0.85rem 0.9rem 1rem;
                z-index: 70;
            }
            .msb-panel-header {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin-bottom: 0.75rem;
                color: #374151;
                font-weight: 600;
                font-size: 0.85rem;
            }
            .msb-panel-header i { color: #9333ea; }
            .msb-bpm { margin-left: auto; font-size: 0.85rem; color: #7c3aed; font-weight: 700; }
            .msb-metro-row { display: flex; align-items: center; gap: 0.75rem; }
            .msb-metro-play {
                width: 42px; height: 42px; flex-shrink: 0;
                border-radius: 9px; border: none; cursor: pointer; color: #fff;
                display: flex; align-items: center; justify-content: center;
                background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%);
            }
            .msb-metro-play.running { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
            .msb-select {
                width: 100%; margin-top: 0.75rem;
                padding: 0.55rem 0.7rem; border: 1px solid #e5e7eb;
                border-radius: 8px; font-size: 0.85rem; color: #374151; background: #fff;
            }

            /* --- Piano tuned for touch --- */
            .piano-wrapper { -webkit-overflow-scrolling: touch; }
            .key-label { display: none; }
        }

        /* ============================================================
           MOBILE / TABLET — single-screen APP MODE.
           The whole studio is ALWAYS presented in landscape: <main> is a
           fixed "studio viewport" and its children are positioned as % of
           it, so the layout is identical whichever way the phone is held.
           In portrait we rotate <main> 90° (force-landscape).
           ============================================================ */
        @media (max-width: 1024px) {
            /* Strip the site chrome — the studio owns the whole screen */
            .piano-studio-page > header,
            .piano-studio-page > footer,
            main > .mb-4 { display: none !important; }

            /* One stable screen — no page scroll, fit the visible viewport */
            html, body { overflow: hidden; height: 100%; margin: 0; }
            body { padding: 0; }

            /* <main> = the landscape studio viewport (default: real landscape) */
            main {
                position: fixed;
                top: 0; left: 0;
                width: 100vw;  width: 100dvw;
                height: 100vh; height: 100dvh;
                max-width: none;
                margin: 0;
                padding: 0;
                overflow: hidden;
                background: linear-gradient(160deg, #f5f3ff 0%, #eef2ff 100%);
            }

            /* Children are positioned as % of <main> (orientation-independent) */

            /* --- Thin top control bar --- */
            .mobile-studio-bar {
                position: absolute;
                top: 0; left: 0; right: 0;
                z-index: 60;
                margin: 0;
                border-radius: 0;
                border: none;
                border-bottom: 1px solid #e5e7eb;
                box-shadow: 0 2px 10px rgb(0 0 0 / 0.08);
            }
            .msb-row { padding: 0.35rem 0.7rem; }

            /* --- Notation fills the middle (bar → piano) --- */
            .studio-layout { display: block; margin: 0; }
            .notation-card-header { display: none; }   /* label moved into the top bar */
            .center-content .card {
                position: absolute;
                top: 50px; left: 8px; right: 8px;
                bottom: calc(42% + 8px);
                margin: 0;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }
            /* Staff centered vertically in the freed space */
            #notation-container {
                flex: 1;
                min-height: 0;
                display: flex;
                align-items: center;
                justify-content: flex-start;
            }
            #notation-output { width: 100%; }

            /* --- Piano: single solid panel docked to the bottom (42% of height) --- */
            .piano-dock {
                position: absolute;
                left: 0; right: 0; bottom: 0;
                height: 42%;
                z-index: 55;
                margin: 0;
                padding: 6px 6px 0;               /* no bottom gap → scroll bar sits on the screen edge */
                border-radius: 16px 16px 0 0;
                border: none;
                box-shadow: 0 -8px 30px rgb(0 0 0 / 0.22);
                background: #fff;
            }
            .piano-dock .piano-wrapper {
                height: 100%;
                overflow-x: auto;    /* 4 octaves at 2-octave key size → swipe sideways */
                overflow-y: hidden;
                padding: 0;
                touch-action: none;  /* swipe handled manually (reliable on touch) */
                scrollbar-width: auto;
                scrollbar-color: #9333ea rgba(147, 51, 234, 0.15);
            }
            /* Purple horizontal scroll bar, stuck to the bottom edge (thick) */
            .piano-dock .piano-wrapper::-webkit-scrollbar { height: 64px; }
            .piano-dock .piano-wrapper::-webkit-scrollbar-track {
                background: rgba(147, 51, 234, 0.15);
                border-radius: 4px;
            }
            .piano-dock .piano-wrapper::-webkit-scrollbar-thumb {
                background: #9333ea;
                border-radius: 4px;
            }
            /* Keyboard is twice as wide as the viewport (4 octaves at the same
               per-key size as the previous 2-octave layout); the extra octaves
               sit to the left/right and are reached by scrolling. Keys stop 10px
               above the purple scroll bar (which sits on the very bottom edge). */
            .piano-dock .piano-container { height: calc(100% - 10px); width: 200%; justify-content: stretch; }
            .piano-dock .piano-keys-container { height: 100%; width: 100%; }

            /* Keys keep the 2-octave size (each = 1/28 of the doubled width) */
            .piano-dock .white-key { min-width: 0; height: 100%; border-radius: 0 0 6px 6px; }
            .piano-dock .black-key { height: 62%; min-width: 0; }
            /* Gesture handled manually so a light swipe scrolls; a tap plays */
            .piano-dock .piano-key { touch-action: none; }
        }

        /* --- FORCE LANDSCAPE: rotate the studio when the phone is portrait --- */
        @media (max-width: 1024px) and (orientation: portrait) {
            main {
                /* swap dimensions: main-width = viewport height (the long side) */
                width: 100vh;  width: 100dvh;
                height: 100vw; height: 100dvw;
                transform-origin: top left;
                transform: translateX(100vw) rotate(90deg);
                transform: translateX(100dvw) rotate(90deg);
            }
        }

        /* Keep the bar on one line; it renders along the wide (landscape) edge */
        @media (max-width: 1024px) {
            .msb-row { flex-wrap: nowrap; }
            .msb-center { flex-wrap: nowrap; min-width: 0; }
        }
    </style>
</head>
<body class="font-sans bg-gray-50 min-h-screen piano-studio-page">
    {{-- Navbar --}}
    @include('partials.navbar', ['active' => 'piano'])

    <!-- Main Content -->
    <main class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-600 to-orange-500 flex items-center justify-center">
                    <i data-lucide="music-2" class="w-6 h-6 text-white"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ __('pages.piano_studio.h1') }}</h1>
            </div>
        </div>

        <!-- Mobile / Landscape Toolbar -->
        <div class="mobile-studio-bar" x-data="{ metroOpen: false }">
            <div class="msb-row">
                <!-- Left: Metronome dropdown -->
                <div class="msb-left">
                    <button type="button" class="msb-btn msb-metro-toggle" :class="{ 'active': metroOpen }" @click.stop="metroOpen = !metroOpen" aria-label="{{ __('pages.piano_studio.metronome') }}">
                        <i data-lucide="activity" class="w-4 h-4"></i>
                        <span class="msb-label">{{ __('pages.piano_studio.metronome') }}</span>
                    </button>

                    <!-- Metronome dropdown panel -->
                    <div class="msb-panel" x-show="metroOpen" x-transition x-cloak @click.outside="metroOpen = false">
                        <div class="msb-panel-header">
                            <i data-lucide="activity" class="w-4 h-4"></i>
                            <span>{{ __('pages.piano_studio.metronome') }}</span>
                            <div class="msb-bpm"><span id="bpmValueMobile">120</span> BPM</div>
                        </div>
                        <div class="msb-metro-row">
                            <button id="metronomeBtnMobile" class="msb-metro-play" aria-label="{{ __('pages.piano_studio.metro_start_stop') }}">
                                <i data-lucide="play" class="w-4 h-4"></i>
                            </button>
                            <input type="range" id="bpmSliderMobile" class="bpm-slider-lean" min="40" max="240" value="120">
                        </div>
                        <select id="tempoPresetMobile" class="msb-select">
                            <option value="">{{ __('pages.piano_studio.select_tempo') }}</option>
                            <option value="60">Largo (60)</option>
                            <option value="76">Adagio (76)</option>
                            <option value="92">Andante (92)</option>
                            <option value="120">Moderato (120)</option>
                            <option value="140">Allegro (140)</option>
                            <option value="180">Presto (180)</option>
                        </select>
                    </div>
                </div>

                <!-- Left label centered in the gap before the controls -->
                <span class="msb-tag msb-tag-left">{{ __('pages.piano_studio.h1') }}</span>

                <!-- Center: basic controls -->
                <div class="msb-center">
                    <button id="playbackBtnMobile" class="msb-btn msb-btn-primary" disabled>
                        <i data-lucide="play" class="w-4 h-4"></i>
                        <span class="msb-label">{{ __('pages.piano_studio.play') }}</span>
                    </button>
                    <button id="clearBtnMobile" class="msb-btn" aria-label="{{ __('pages.piano_studio.clear_notes') }}">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                    <div class="msb-count">
                        <span id="noteCountMobile">0</span>
                        <small>{{ __('pages.piano_studio.notes') }}</small>
                    </div>
                </div>

                <!-- Right label centered in the gap after the controls -->
                <span class="msb-tag msb-tag-right">{{ __('pages.piano_studio.music_notation') }}</span>

                <!-- Right: Harmoniva standard menu -->
                <button type="button" class="msb-btn msb-menu-btn" x-data @click="$dispatch('toggle-mobile-menu')" aria-label="{{ __('pages.piano_studio.menu') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                        <line x1="4" y1="6" x2="20" y2="6"/>
                        <line x1="4" y1="12" x2="20" y2="12"/>
                        <line x1="4" y1="18" x2="20" y2="18"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Studio Layout with Sidebars -->
        <div class="studio-layout">
            <!-- Left Sidebar - Metronome -->
            <aside class="sidebar sidebar-left">
                <div class="metronome-widget">
                    <div class="metronome-header">
                        <div class="metronome-title">
                            <i data-lucide="activity" class="w-4 h-4 text-purple-600"></i>
                            <h3 class="font-semibold text-gray-900 text-sm">{{ __('pages.piano_studio.metronome') }}</h3>
                        </div>
                        <div class="bpm-display-inline">
                            <span class="bpm-value-inline" id="bpmValue">120</span>
                            <span class="bpm-label-inline">BPM</span>
                        </div>
                    </div>
                    
                    <!-- Controls Row -->
                    <div class="metronome-controls">
                        <button id="metronomeBtn" class="metronome-play-btn start">
                            <i data-lucide="play" class="w-4 h-4"></i>
                        </button>
                        <div class="slider-container">
                            <input type="range" id="bpmSlider" class="bpm-slider-lean" min="40" max="240" value="120">
                            <div class="slider-labels">
                                <span>40</span>
                                <span>240</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Beat Indicator Bars -->
                    <div class="beat-indicator-bars">
                        <div class="beat-bar first-beat" data-beat="0"></div>
                        <div class="beat-bar" data-beat="1"></div>
                        <div class="beat-bar" data-beat="2"></div>
                        <div class="beat-bar" data-beat="3"></div>
                    </div>
                    
                    <!-- Tempo Presets -->
                    <div class="tempo-preset-lean">
                        <select id="tempoPreset">
                            <option value="">{{ __('pages.piano_studio.select_preset') }}</option>
                            <option value="60">Largo (60)</option>
                            <option value="76">Adagio (76)</option>
                            <option value="92">Andante (92)</option>
                            <option value="120">Moderato (120)</option>
                            <option value="140">Allegro (140)</option>
                            <option value="180">Presto (180)</option>
                        </select>
                    </div>
                    
                    <!-- Hidden elements for JS compatibility -->
                    <div class="beat-indicator" style="display:none;">
                        <div class="beat-dot first-beat" data-beat="1"></div>
                        <div class="beat-dot" data-beat="2"></div>
                        <div class="beat-dot" data-beat="3"></div>
                        <div class="beat-dot" data-beat="4"></div>
                    </div>
                </div>
            </aside>

            <!-- Center Content -->
            <div class="center-content">

        <!-- Keyboard Shortcuts Guide -->
        {{-- <div class="card p-4 mb-6">
            <div class="flex items-center gap-2 mb-3">
                <i data-lucide="keyboard" class="w-5 h-5 text-purple-600"></i>
                <h3 class="font-semibold text-gray-900">Keyboard Shortcuts</h3>
                <span class="text-xs text-gray-400">(Octaves 2 & 5 are mouse/touch only)</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                <div>
                    <p class="font-medium text-gray-700 mb-1">Octave 3 (C3-B3)</p>
                    <p>White keys: <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">A</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">S</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">D</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">F</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">G</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">H</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">J</kbd></p>
                    <p>Black keys: <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">W</kbd> <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">E</kbd> <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">T</kbd> <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">Y</kbd> <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">U</kbd></p>
                </div>
                <div>
                    <p class="font-medium text-gray-700 mb-1">Octave 4 (C4-B4)</p>
                    <p>White keys: <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">K</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">L</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">;</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">Z</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">X</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">C</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">V</kbd></p>
                    <p>Black keys: <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">O</kbd> <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">P</kbd> <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">.</kbd> <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">/</kbd> <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">'</kbd></p>
                </div>
            </div>
        </div> --}}

                <!-- Music Notation Display (full-width; playback controls moved into its header) -->
                <div class="card mb-4">
                    <div class="p-4 border-b border-gray-200 notation-card-header flex items-center gap-4">
                        <div class="flex items-center gap-2 shrink-0">
                            <i data-lucide="music-2" class="w-5 h-5 text-purple-600"></i>
                            <h3 class="font-semibold text-gray-900">{{ __('pages.piano_studio.music_notation') }}</h3>
                        </div>
                        <div class="flex flex-1 items-center justify-center gap-3">
                            <button id="playbackBtn" class="inline-flex items-center gap-2 px-4 py-2 btn-primary text-white font-semibold rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                <i data-lucide="play" class="w-4 h-4"></i>
                                <span>{{ __('pages.piano_studio.playback') }}</span>
                            </button>
                            <button id="clearBtn" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-all">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                {{ __('pages.piano_studio.clear') }}
                            </button>
                            <div class="text-sm text-gray-500 whitespace-nowrap">
                                <span id="noteCount" class="font-bold text-purple-600 text-base">0</span> {{ __('pages.piano_studio.notes_recorded') }}
                            </div>
                        </div>
                    </div>
                    <div id="notation-container">
                        <div id="notation-output"></div>
                        <p id="notation-placeholder" class="text-center text-gray-400 py-12">{{ __('pages.piano_studio.placeholder') }}</p>
                    </div>
                </div>
                </div>{{-- /center-content --}}
            {{-- Right sidebar removed — Playback/Clear/count now live in the notation header, freeing space for a wider notation box --}}
        </div>

        <!-- Piano Keyboard — full-bleed black stage (desktop) wraps the framed piano -->
        <div class="piano-stage">
            <div class="card p-3 mt-4 piano-dock">
                <div class="piano-wrapper">
                    <div class="piano-container" id="piano">
                        <!-- Piano keys will be generated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('partials.footer')

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>

    <script>
    window.HarmonivaAudio = (function () {
        // Tone spells a double sharp "Fx"; the staff (and VexFlow) spell it
        // "F##", which is what the server sends. Without this the sampler
        // rejects a correctly spelled augmented chord outright — its note test
        // is /^[a-g](b|#|x|bb)?[0-9]/, and "F##5" does not match it. Double
        // flats ("Bbb4") Tone already understands, so they pass straight
        // through, as does every everyday spelling.
        const toneNote = (n) => (typeof n === 'string' ? n.replace('##', 'x') : n);
        let sampler = null;
        let synth = null;
        let ready = false;      // Salamander samples finished loading
        let starting = null;    // memoised start() promise

        function initSampler() {
            if (sampler) return;
            sampler = new Tone.Sampler({
                urls: {
                    A1:'A1.mp3', C2:'C2.mp3', 'D#2':'Ds2.mp3', 'F#2':'Fs2.mp3',
                    A2:'A2.mp3', C3:'C3.mp3', 'D#3':'Ds3.mp3', 'F#3':'Fs3.mp3',
                    A3:'A3.mp3', C4:'C4.mp3', 'D#4':'Ds4.mp3', 'F#4':'Fs4.mp3',
                    A4:'A4.mp3', C5:'C5.mp3', 'D#5':'Ds5.mp3', 'F#5':'Fs5.mp3',
                    A5:'A5.mp3', C6:'C6.mp3', 'D#6':'Ds6.mp3', 'F#6':'Fs6.mp3',
                    A6:'A6.mp3', C7:'C7.mp3', 'D#7':'Ds7.mp3', 'F#7':'Fs7.mp3',
                },
                release: 1,
                baseUrl: 'https://tonejs.github.io/audio/salamander/',
                onload: () => { ready = true; }
            }).toDestination();
        }

        // Instant fallback voice — no samples to download, so it sounds the
        // moment the AudioContext resumes.
        function initSynth() {
            if (synth) return;
            synth = new Tone.PolySynth(Tone.Synth).toDestination();
            synth.set({
                oscillator: { type: 'triangle' },
                envelope: { attack: 0.005, decay: 0.15, sustain: 0.25, release: 1.1 }
            });
            synth.volume.value = -6;
        }

        // Resume audio + build instruments once. Returns a memoised promise so
        // callers never re-await the whole thing.
        function start() {
            if (starting) return starting;
            starting = (async () => {
                await Tone.start();
                initSynth();     // ready immediately
                initSampler();   // loads in the background, then takes over
            })();
            return starting;
        }

        // Prefer the realistic sampler once loaded, else the instant synth.
        function voice() { return (ready && sampler) ? sampler : synth; }

        // Wait briefly for the piano samples so the first note is a real piano
        // (not the synth), without a noticeable delay. Since samples are
        // preloaded on page load, they're almost always ready already.
        function waitForReady(ms) {
            return new Promise((resolve) => {
                if (ready) return resolve();
                const deadline = Date.now() + ms;
                (function poll() {
                    if (ready || Date.now() > deadline) return resolve();
                    setTimeout(poll, 25);
                })();
            });
        }

        return {
            // Start downloading/decoding piano samples immediately on page load
            // (no gesture needed) so they're ready by the time a key is pressed.
            preload() { initSampler(); },
            // Call on the first user gesture to resume audio before playing.
            warmup() { return start(); },
            async playNote(note, duration) {
                await start();
                if (!ready) await waitForReady(350);   // prefer real piano on the first notes
                voice().triggerAttackRelease(toneNote(note), duration ?? 1);
            },
            async playSimultaneous(notes, duration) {
                await start();
                const v = voice(), now = Tone.now();
                notes.forEach(n => v.triggerAttackRelease(toneNote(n), duration ?? 2, now));
            },
            async playSequential(notes, intervalMs, duration) {
                await start();
                const v = voice(), now = Tone.now();
                notes.forEach((n, i) =>
                    v.triggerAttackRelease(toneNote(n), duration ?? 1, now + i * ((intervalMs ?? 600) / 1000)));
            },
            async playArpeggio(notes, delayMs, duration) {
                await start();
                const v = voice(), now = Tone.now();
                notes.forEach((n, i) =>
                    v.triggerAttackRelease(toneNote(n), duration ?? 1.5, now + i * ((delayMs ?? 400) / 1000)));
            },
            totalMs(notes, delayMs) {
                return (notes.length - 1) * (delayMs ?? 400) + 2000;
            },
            stop() {
                if (sampler) sampler.releaseAll();
                if (synth) synth.releaseAll();
            }
        };
    })();
    </script>

    <!-- Piano Studio Logic -->
    <script>
        // Piano configuration - 4 octaves from C2 to B5
        const NOTES = [
            // Octave 2 (C2-B2) - No keyboard shortcuts, mouse only
            { note: 'C', octave: 2, type: 'white', key: null, midi: 36 },
            { note: 'C#', octave: 2, type: 'black', key: null, midi: 37 },
            { note: 'D', octave: 2, type: 'white', key: null, midi: 38 },
            { note: 'D#', octave: 2, type: 'black', key: null, midi: 39 },
            { note: 'E', octave: 2, type: 'white', key: null, midi: 40 },
            { note: 'F', octave: 2, type: 'white', key: null, midi: 41 },
            { note: 'F#', octave: 2, type: 'black', key: null, midi: 42 },
            { note: 'G', octave: 2, type: 'white', key: null, midi: 43 },
            { note: 'G#', octave: 2, type: 'black', key: null, midi: 44 },
            { note: 'A', octave: 2, type: 'white', key: null, midi: 45 },
            { note: 'A#', octave: 2, type: 'black', key: null, midi: 46 },
            { note: 'B', octave: 2, type: 'white', key: null, midi: 47 },
            // Octave 3 (C3-B3) - With keyboard shortcuts
            { note: 'C', octave: 3, type: 'white', key: 'a', midi: 48 },
            { note: 'C#', octave: 3, type: 'black', key: 'w', midi: 49 },
            { note: 'D', octave: 3, type: 'white', key: 's', midi: 50 },
            { note: 'D#', octave: 3, type: 'black', key: 'e', midi: 51 },
            { note: 'E', octave: 3, type: 'white', key: 'd', midi: 52 },
            { note: 'F', octave: 3, type: 'white', key: 'f', midi: 53 },
            { note: 'F#', octave: 3, type: 'black', key: 't', midi: 54 },
            { note: 'G', octave: 3, type: 'white', key: 'g', midi: 55 },
            { note: 'G#', octave: 3, type: 'black', key: 'y', midi: 56 },
            { note: 'A', octave: 3, type: 'white', key: 'h', midi: 57 },
            { note: 'A#', octave: 3, type: 'black', key: 'u', midi: 58 },
            { note: 'B', octave: 3, type: 'white', key: 'j', midi: 59 },
            // Octave 4 (C4-B4) - With keyboard shortcuts
            { note: 'C', octave: 4, type: 'white', key: 'k', midi: 60 },
            { note: 'C#', octave: 4, type: 'black', key: 'o', midi: 61 },
            { note: 'D', octave: 4, type: 'white', key: 'l', midi: 62 },
            { note: 'D#', octave: 4, type: 'black', key: 'p', midi: 63 },
            { note: 'E', octave: 4, type: 'white', key: ';', midi: 64 },
            { note: 'F', octave: 4, type: 'white', key: 'z', midi: 65 },
            { note: 'F#', octave: 4, type: 'black', key: '.', midi: 66 },
            { note: 'G', octave: 4, type: 'white', key: 'x', midi: 67 },
            { note: 'G#', octave: 4, type: 'black', key: '/', midi: 68 },
            { note: 'A', octave: 4, type: 'white', key: 'c', midi: 69 },
            { note: 'A#', octave: 4, type: 'black', key: "'", midi: 70 },
            { note: 'B', octave: 4, type: 'white', key: 'v', midi: 71 },
            // Octave 5 (C5-B5) - No keyboard shortcuts, mouse only
            { note: 'C', octave: 5, type: 'white', key: null, midi: 72 },
            { note: 'C#', octave: 5, type: 'black', key: null, midi: 73 },
            { note: 'D', octave: 5, type: 'white', key: null, midi: 74 },
            { note: 'D#', octave: 5, type: 'black', key: null, midi: 75 },
            { note: 'E', octave: 5, type: 'white', key: null, midi: 76 },
            { note: 'F', octave: 5, type: 'white', key: null, midi: 77 },
            { note: 'F#', octave: 5, type: 'black', key: null, midi: 78 },
            { note: 'G', octave: 5, type: 'white', key: null, midi: 79 },
            { note: 'G#', octave: 5, type: 'black', key: null, midi: 80 },
            { note: 'A', octave: 5, type: 'white', key: null, midi: 81 },
            { note: 'A#', octave: 5, type: 'black', key: null, midi: 82 },
            { note: 'B', octave: 5, type: 'white', key: null, midi: 83 },
        ];

        // State
        let recordedNotes = [];
        let isPlaying = false;
        let activeKeys = new Set();
        let notationNoteElements = []; // Store VexFlow SVG elements for highlighting

        // DOM Elements
        const pianoContainer = document.getElementById('piano');
        const playbackBtn = document.getElementById('playbackBtn');
        const clearBtn = document.getElementById('clearBtn');
        const noteCountEl = document.getElementById('noteCount');
        const notationOutput = document.getElementById('notation-output');
        const notationPlaceholder = document.getElementById('notation-placeholder');

        // Play a note using Tone.js Salamander Grand Piano sampler
        function playNote(note, octave, duration = 1) {
            window.HarmonivaAudio.playNote(note + octave, duration);
        }

        // Full 4-octave keyboard on every viewport. On mobile the keys keep
        // their (previous 2-octave) size and the keyboard is twice as wide as
        // the screen — the extra octaves sit left/right and are reached by
        // scrolling; the view starts centred on the middle two octaves.
        function isCompactViewport() {
            return window.matchMedia('(max-width: 1024px)').matches;
        }
        function activeNotes() {
            return NOTES;
        }

        // Track which layout is currently rendered so we only rebuild on change
        let builtCompact = null;

        // Bind pointer (mouse + touch + pen) events to a key
        function attachKeyEvents(el, noteData) {
            el.addEventListener('pointerdown', (e) => {
                e.preventDefault();
                triggerNote(noteData);
            });
            el.addEventListener('pointerup', () => releaseNote(noteData));
            el.addEventListener('pointerleave', () => releaseNote(noteData));
            el.addEventListener('pointercancel', () => releaseNote(noteData));
        }

        // Build piano keyboard (octave-count agnostic — black keys positioned
        // dynamically from the running white-key count)
        function buildPiano() {
            const notes = activeNotes();
            builtCompact = isCompactViewport();

            pianoContainer.innerHTML = '';

            const totalWhiteKeys = notes.filter(n => n.type === 'white').length;
            const whiteKeyWidthPercent = 100 / totalWhiteKeys;
            const blackKeyWidthPercent = whiteKeyWidthPercent * 0.6; // 60% of a white key

            const keysContainer = document.createElement('div');
            keysContainer.className = 'piano-keys-container';

            let whiteCount = 0; // white keys placed so far

            notes.forEach((noteData) => {
                const key = document.createElement('div');
                key.dataset.note = noteData.note;
                key.dataset.octave = noteData.octave;
                key.dataset.midi = noteData.midi;
                key.id = `key-${noteData.midi}`;

                if (noteData.type === 'white') {
                    key.className = 'piano-key white-key';
                    if (noteData.key) {
                        const label = document.createElement('span');
                        label.className = 'key-label';
                        label.textContent = noteData.key.toUpperCase();
                        key.appendChild(label);
                    }
                    attachKeyEvents(key, noteData);
                    keysContainer.appendChild(key);
                    whiteCount++;
                } else {
                    key.className = 'piano-key black-key';
                    // A black key sits on the boundary after the white keys placed so far
                    const leftPercent = (whiteCount * whiteKeyWidthPercent) - (blackKeyWidthPercent / 2);
                    key.style.left = `${leftPercent}%`;
                    key.style.width = `${blackKeyWidthPercent}%`;
                    if (noteData.key) {
                        const label = document.createElement('span');
                        label.className = 'key-label';
                        label.textContent = noteData.key.toUpperCase();
                        key.appendChild(label);
                    }
                    attachKeyEvents(key, noteData);
                    keysContainer.appendChild(key);
                }
            });

            pianoContainer.appendChild(keysContainer);

            // On mobile the keyboard is wider than the screen — start centred on
            // the middle two octaves (C3–B4), with the extra octaves to each side.
            if (isCompactViewport()) {
                requestAnimationFrame(centerPianoScroll);
            }
        }

        // Centre the horizontal scroll of the (overflowing) mobile keyboard
        function centerPianoScroll() {
            const wrap = pianoContainer.parentElement; // .piano-wrapper
            if (wrap && wrap.scrollWidth > wrap.clientWidth) {
                wrap.scrollLeft = (wrap.scrollWidth - wrap.clientWidth) / 2;
            }
        }

        // Cancel any highlighted keys (used when a tap turns into a scroll)
        function releaseAllKeys() {
            activeKeys.forEach((midi) => {
                const el = document.getElementById(`key-${midi}`);
                if (el) el.classList.remove('active');
            });
            activeKeys.clear();
        }

        // Drag-to-scroll the wide mobile keyboard: a light horizontal swipe
        // scrolls to the extra octaves, while a tap still plays the key.
        // Works whether the studio is real-landscape or CSS force-landscape
        // (portrait), where the on-screen axis is rotated 90°.
        function setupPianoDragScroll() {
            const wrap = pianoContainer.parentElement; // .piano-wrapper
            if (!wrap || wrap.dataset.dragBound) return;
            wrap.dataset.dragBound = '1';

            let down = false, startX = 0, startY = 0, startScroll = 0, moved = false, pid = null, startLen = 0;

            // Displacement along the keyboard's horizontal axis (accounts for the
            // 90° force-landscape rotation used on portrait phones).
            function alongAxis(e) {
                const rotated = isCompactViewport() &&
                    window.matchMedia('(orientation: portrait)').matches;
                return rotated ? (e.clientY - startY) : (e.clientX - startX);
            }

            wrap.addEventListener('pointerdown', (e) => {
                if (wrap.scrollWidth <= wrap.clientWidth + 1) return; // nothing to scroll
                down = true; moved = false; pid = e.pointerId;
                startX = e.clientX; startY = e.clientY; startScroll = wrap.scrollLeft;
                startLen = recordedNotes.length; // to undo the note the swipe started on
            });
            wrap.addEventListener('pointermove', (e) => {
                if (!down || e.pointerId !== pid) return;
                const ax = alongAxis(e);
                if (!moved && Math.abs(ax) > 5) {
                    moved = true;
                    releaseAllKeys();
                    // Undo the note that was triggered when the swipe began
                    if (recordedNotes.length > startLen) {
                        recordedNotes.length = startLen;
                        updateNoteCount();
                        renderNotation();
                    }
                }
                if (moved) wrap.scrollLeft = startScroll - ax;
            });
            const end = () => { down = false; pid = null; };
            wrap.addEventListener('pointerup', end);
            wrap.addEventListener('pointercancel', end);
            wrap.addEventListener('pointerleave', end);
        }

        // Rebuild only when crossing the compact/full breakpoint (e.g. rotate/resize)
        let pianoResizeTimer = null;
        window.addEventListener('resize', () => {
            clearTimeout(pianoResizeTimer);
            pianoResizeTimer = setTimeout(() => {
                if (isCompactViewport() !== builtCompact) buildPiano();
                else if (isCompactViewport()) centerPianoScroll();
            }, 200);
        });

        // Trigger a note (play sound, visual feedback, record)
        function triggerNote(noteData) {
            if (activeKeys.has(noteData.midi)) return;
            
            activeKeys.add(noteData.midi);
            
            const keyEl = document.getElementById(`key-${noteData.midi}`);
            if (keyEl) keyEl.classList.add('active');
            
            playNote(noteData.note, noteData.octave, 1);
            
            // Record the note
            recordedNotes.push({
                ...noteData,
                timestamp: Date.now()
            });
            
            updateNoteCount();
            renderNotation();
        }

        // Release a note (visual feedback)
        function releaseNote(noteData) {
            activeKeys.delete(noteData.midi);
            
            const keyEl = document.getElementById(`key-${noteData.midi}`);
            if (keyEl) keyEl.classList.remove('active');
        }

        // Update note count display
        function updateNoteCount() {
            noteCountEl.textContent = recordedNotes.length;
            playbackBtn.disabled = recordedNotes.length === 0;
        }

        // Render notation using VexFlow
        function renderNotation() {
            // Reset stored note elements
            notationNoteElements = [];
            
            if (recordedNotes.length === 0) {
                notationOutput.innerHTML = '';
                notationPlaceholder.style.display = 'block';
                return;
            }
            
            notationPlaceholder.style.display = 'none';
            notationOutput.innerHTML = '';
            
            const VF = Vex.Flow;
            
            // Calculate width based on number of notes
            const notesPerMeasure = 4;
            const measureWidth = 250;
            const numMeasures = Math.ceil(recordedNotes.length / notesPerMeasure);
            const totalWidth = Math.max(measureWidth * numMeasures, 600);
            
            // On mobile the notation area is short — size the SVG to the
            // available height and vertically center the staff inside it.
            const notationContainerEl = document.getElementById('notation-container');
            const compact = window.matchMedia('(max-width: 1024px)').matches;
            const svgHeight = compact
                ? Math.max(notationContainerEl.clientHeight || 140, 120)
                : 200;
            // Draw with generous top room; the viewBox pass below re-centers it.
            const staveY = compact ? 55 : 20;

            const renderer = new VF.Renderer(notationOutput, VF.Renderer.Backends.SVG);
            renderer.resize(totalWidth, svgHeight);
            const context = renderer.getContext();
            context.setFont('Arial', 10);
            
            // Create stave notes from recorded notes
            const staveNotes = recordedNotes.map(noteData => {
                const noteName = noteData.note.replace('#', '#');
                const vexNote = `${noteName.toLowerCase()}/${noteData.octave}`;
                
                const staveNote = new VF.StaveNote({
                    keys: [vexNote],
                    duration: 'q',
                    clef: 'treble'
                });
                
                // Add accidental if needed
                if (noteData.note.includes('#')) {
                    staveNote.addModifier(new VF.Accidental('#'), 0);
                }
                
                return staveNote;
            });
            
            // Split into measures
            const measures = [];
            for (let i = 0; i < staveNotes.length; i += notesPerMeasure) {
                measures.push(staveNotes.slice(i, i + notesPerMeasure));
            }
            
            // Keep track of original note indices for each measure
            let noteIndex = 0;
            
            // Render each measure
            let xPos = 10;
            measures.forEach((measureNotes, measureIndex) => {
                const stave = new VF.Stave(xPos, staveY, measureWidth);
                
                if (measureIndex === 0) {
                    stave.addClef('treble');
                }
                
                stave.setContext(context).draw();
                
                // Track how many actual notes (non-rests) in this measure
                const actualNoteCount = measureNotes.length;
                
                // Pad with rests if needed to fill measure
                while (measureNotes.length < notesPerMeasure) {
                    measureNotes.push(new VF.StaveNote({
                        keys: ['b/4'],
                        duration: 'qr'
                    }));
                }
                
                const voice = new VF.Voice({ num_beats: 4, beat_value: 4 });
                voice.addTickables(measureNotes);
                
                new VF.Formatter().joinVoices([voice]).format([voice], measureWidth - 50);
                voice.draw(context, stave);
                
                // Store SVG element references for actual notes (not rests)
                for (let i = 0; i < actualNoteCount; i++) {
                    const staveNote = measureNotes[i];
                    const svg = staveNote.getSVGElement();
                    if (svg) {
                        notationNoteElements.push({
                            svg: svg,
                            staveNote: staveNote,
                            xPos: xPos + (i * (measureWidth - 50) / notesPerMeasure)
                        });
                    }
                }
                
                xPos += measureWidth;
            });

            // On mobile, vertically center the actual rendered content inside
            // the short notation area. If notes sit high or low, this pans the
            // view (no scaling) so they always stay visible.
            if (compact) {
                const svgEl = notationOutput.querySelector('svg');
                if (svgEl) {
                    try {
                        const bbox = svgEl.getBBox();
                        const pad = 6;
                        const contentH = bbox.height + pad * 2;
                        const minY = bbox.y - pad - Math.max(0, (svgHeight - contentH) / 2);
                        svgEl.setAttribute('viewBox', `0 ${minY} ${totalWidth} ${svgHeight}`);
                    } catch (e) { /* getBBox unavailable — leave as drawn */ }
                }
            }

            // Auto-scroll to the right to show the latest notes
            const notationContainer = document.getElementById('notation-container');
            notationContainer.scrollLeft = notationContainer.scrollWidth;
        }
        
        // Highlight a note in the notation
        function highlightNotationNote(index, highlight = true) {
            if (index < 0 || index >= notationNoteElements.length) return;
            
            const noteEl = notationNoteElements[index];
            if (!noteEl || !noteEl.svg) return;
            
            const svg = noteEl.svg;
            const color = highlight ? '#9333ea' : 'black'; // Purple when highlighted, black otherwise
            
            // Change fill and stroke colors on all child elements
            svg.querySelectorAll('*').forEach((child) => {
                child.setAttribute('fill', color);
                child.setAttribute('stroke', color);
            });
            
            // Update selection state on parent
            if (highlight) {
                svg.classList.add('playing-note');
            } else {
                svg.classList.remove('playing-note');
            }
        }
        
        // Scroll notation container to center on a specific note
        function scrollToNote(index) {
            if (index < 0 || index >= notationNoteElements.length) return;
            
            const noteEl = notationNoteElements[index];
            if (!noteEl || !noteEl.svg) return;
            
            const notationContainer = document.getElementById('notation-container');
            const containerWidth = notationContainer.clientWidth;
            
            // Get the note's bounding rect relative to the SVG
            const svgRect = noteEl.svg.getBoundingClientRect();
            const containerRect = notationContainer.getBoundingClientRect();
            
            // Calculate the note's position relative to the scroll container
            const noteCenter = svgRect.left - containerRect.left + notationContainer.scrollLeft + (svgRect.width / 2);
            
            // Scroll to center the note
            const targetScroll = noteCenter - (containerWidth / 2);
            
            notationContainer.scrollTo({
                left: Math.max(0, targetScroll),
                behavior: 'smooth'
            });
        }
        
        // Reset all notation highlighting
        function resetNotationHighlighting() {
            notationNoteElements.forEach((noteEl, index) => {
                highlightNotationNote(index, false);
            });
        }

        // Playback recorded notes
        async function playback() {
            if (recordedNotes.length === 0 || isPlaying) return;
            
            isPlaying = true;
            playbackBtn.classList.add('playing');
            playbackBtn.innerHTML = '<i data-lucide="pause" class="w-4 h-4"></i><span>Playing...</span>';
            lucide.createIcons();
            
            // Reset any previous highlighting
            resetNotationHighlighting();
            
            for (let i = 0; i < recordedNotes.length; i++) {
                if (!isPlaying) break;
                
                const noteData = recordedNotes[i];
                const nextNote = recordedNotes[i + 1];
                
                // Visual feedback on piano key
                const keyEl = document.getElementById(`key-${noteData.midi}`);
                if (keyEl) keyEl.classList.add('active');
                
                // Reset all previous highlighting and highlight only current note
                resetNotationHighlighting();
                highlightNotationNote(i, true);
                scrollToNote(i);
                
                playNote(noteData.note, noteData.octave, 1);
                
                // Calculate delay until next note
                let delay = 500; // Default delay
                if (nextNote) {
                    delay = Math.min(Math.max(nextNote.timestamp - noteData.timestamp, 200), 1500);
                }
                
                await new Promise(resolve => setTimeout(resolve, delay));
                
                if (keyEl) keyEl.classList.remove('active');
            }
            
            // Reset last note highlighting after a short delay
            await new Promise(resolve => setTimeout(resolve, 300));
            resetNotationHighlighting();
            
            isPlaying = false;
            playbackBtn.classList.remove('playing');
            playbackBtn.innerHTML = '<i data-lucide="play" class="w-4 h-4"></i><span>Playback</span>';
            lucide.createIcons();
        }

        // Clear recorded notes
        function clearNotes() {
            recordedNotes = [];
            updateNoteCount();
            renderNotation();
        }

        // Keyboard event handlers
        function handleKeyDown(e) {
            if (e.repeat) return;
            
            const key = e.key.toLowerCase();
            const noteData = NOTES.find(n => n.key === key);
            
            if (noteData) {
                e.preventDefault();
                triggerNote(noteData);
            }
        }

        function handleKeyUp(e) {
            const key = e.key.toLowerCase();
            const noteData = NOTES.find(n => n.key === key);
            
            if (noteData) {
                releaseNote(noteData);
            }
        }

        // Event listeners
        document.addEventListener('keydown', handleKeyDown);
        document.addEventListener('keyup', handleKeyUp);
        playbackBtn.addEventListener('click', playback);
        clearBtn.addEventListener('click', clearNotes);

        // Mobile button event listeners
        const playbackBtnMobile = document.getElementById('playbackBtnMobile');
        const clearBtnMobile = document.getElementById('clearBtnMobile');
        const noteCountMobile = document.getElementById('noteCountMobile');
        
        if (playbackBtnMobile) {
            playbackBtnMobile.addEventListener('click', playback);
        }
        if (clearBtnMobile) {
            clearBtnMobile.addEventListener('click', clearNotes);
        }

        // Update note count to work with both elements
        const originalUpdateNoteCount = updateNoteCount;
        updateNoteCount = function() {
            noteCountEl.textContent = recordedNotes.length;
            if (noteCountMobile) {
                noteCountMobile.textContent = recordedNotes.length;
            }
            playbackBtn.disabled = recordedNotes.length === 0;
            if (playbackBtnMobile) {
                playbackBtnMobile.disabled = recordedNotes.length === 0;
            }
        };

        // =============================================
        // METRONOME FUNCTIONALITY
        // =============================================
        
        // Metronome state
        let metronomeRunning = false;
        let metronomeBpm = 120;
        let metronomeInterval = null;
        let currentBeat = 0;
        let audioContext = null;

        // Metronome DOM elements
        const bpmValueEl = document.getElementById('bpmValue');
        const bpmSlider = document.getElementById('bpmSlider');
        const tempoPreset = document.getElementById('tempoPreset');
        const metronomeBtn = document.getElementById('metronomeBtn');
        const beatDots = document.querySelectorAll('.beat-dot');

        // Initialize Audio Context (needs user interaction first)
        function initAudioContext() {
            if (!audioContext) {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (audioContext.state === 'suspended') {
                audioContext.resume();
            }
            return audioContext;
        }

        // Create click sound using Web Audio API
        function playClick(isFirstBeat = false) {
            const ctx = initAudioContext();
            const oscillator = ctx.createOscillator();
            const gainNode = ctx.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(ctx.destination);
            
            // Higher pitch and louder for first beat
            oscillator.frequency.value = isFirstBeat ? 1200 : 800;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(isFirstBeat ? 0.5 : 0.3, ctx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.08);
            
            oscillator.start(ctx.currentTime);
            oscillator.stop(ctx.currentTime + 0.08);
        }

        // Update BPM display and slider
        function updateBpm(bpm) {
            metronomeBpm = Math.max(40, Math.min(240, parseInt(bpm)));
            bpmValueEl.textContent = metronomeBpm;
            bpmSlider.value = metronomeBpm;

            // Mirror to mobile settings panel
            const bpmValueMobile = document.getElementById('bpmValueMobile');
            const bpmSliderMobile = document.getElementById('bpmSliderMobile');
            const tempoPresetMobile = document.getElementById('tempoPresetMobile');
            if (bpmValueMobile) bpmValueMobile.textContent = metronomeBpm;
            if (bpmSliderMobile) bpmSliderMobile.value = metronomeBpm;

            // Update tempo preset selector to match
            const matchingOption = Array.from(tempoPreset.options).find(opt => opt.value === String(metronomeBpm));
            if (matchingOption) {
                tempoPreset.value = metronomeBpm;
                if (tempoPresetMobile) tempoPresetMobile.value = metronomeBpm;
            } else {
                tempoPreset.value = '';
                if (tempoPresetMobile) tempoPresetMobile.value = '';
            }
            
            // If metronome is running, restart with new tempo
            if (metronomeRunning) {
                stopMetronome();
                startMetronome();
            }
        }

        // Update beat indicator
        function updateBeatIndicator(beat) {
            // Update old dot indicators (hidden but kept for compatibility)
            beatDots.forEach((dot, index) => {
                dot.classList.remove('active');
                if (index === beat) {
                    dot.classList.add('active');
                }
            });
            
            // Update new bar indicators
            const beatBars = document.querySelectorAll('.beat-bar');
            beatBars.forEach((bar, index) => {
                bar.classList.remove('active');
                if (index === beat) {
                    bar.classList.add('active');
                }
            });
        }

        // Start metronome
        function startMetronome() {
            if (metronomeRunning) return;
            
            metronomeRunning = true;
            currentBeat = 0;
            
            // Update button appearance
            metronomeBtn.classList.remove('start');
            metronomeBtn.classList.add('stop');
            metronomeBtn.innerHTML = '<i data-lucide="square" class="w-4 h-4"></i>';

            // Mobile metronome button
            const mBtnMobile = document.getElementById('metronomeBtnMobile');
            if (mBtnMobile) {
                mBtnMobile.classList.add('running');
                mBtnMobile.innerHTML = '<i data-lucide="square" class="w-4 h-4"></i>';
            }
            lucide.createIcons();

            // Calculate interval in ms
            const intervalMs = (60 / metronomeBpm) * 1000;
            
            // Play first beat immediately
            playClick(true);
            updateBeatIndicator(0);
            currentBeat = 1;
            
            // Start interval for subsequent beats
            metronomeInterval = setInterval(() => {
                const isFirstBeat = currentBeat === 0;
                playClick(isFirstBeat);
                updateBeatIndicator(currentBeat);
                currentBeat = (currentBeat + 1) % 4;
            }, intervalMs);
        }

        // Stop metronome
        function stopMetronome() {
            if (!metronomeRunning) return;
            
            metronomeRunning = false;
            
            if (metronomeInterval) {
                clearInterval(metronomeInterval);
                metronomeInterval = null;
            }
            
            // Reset beat indicator
            beatDots.forEach(dot => dot.classList.remove('active'));
            document.querySelectorAll('.beat-bar').forEach(bar => bar.classList.remove('active'));
            currentBeat = 0;
            
            // Update button appearance
            metronomeBtn.classList.remove('stop');
            metronomeBtn.classList.add('start');
            metronomeBtn.innerHTML = '<i data-lucide="play" class="w-4 h-4"></i>';

            // Mobile metronome button
            const mBtnMobile = document.getElementById('metronomeBtnMobile');
            if (mBtnMobile) {
                mBtnMobile.classList.remove('running');
                mBtnMobile.innerHTML = '<i data-lucide="play" class="w-4 h-4"></i>';
            }
            lucide.createIcons();
        }

        // Toggle metronome
        function toggleMetronome() {
            if (metronomeRunning) {
                stopMetronome();
            } else {
                startMetronome();
            }
        }

        // Metronome event listeners
        if (bpmSlider) {
            bpmSlider.addEventListener('input', (e) => {
                updateBpm(e.target.value);
            });
        }

        if (tempoPreset) {
            tempoPreset.addEventListener('change', (e) => {
                if (e.target.value) {
                    updateBpm(e.target.value);
                }
            });
        }

        if (metronomeBtn) {
            metronomeBtn.addEventListener('click', toggleMetronome);
        }

        // Mobile metronome button toggle
        const metronomeBtnMobile = document.getElementById('metronomeBtnMobile');
        if (metronomeBtnMobile) {
            metronomeBtnMobile.addEventListener('click', toggleMetronome);
        }

        // Mobile BPM slider + tempo preset
        const bpmSliderMobileEl = document.getElementById('bpmSliderMobile');
        if (bpmSliderMobileEl) {
            bpmSliderMobileEl.addEventListener('input', (e) => updateBpm(e.target.value));
        }
        const tempoPresetMobileEl = document.getElementById('tempoPresetMobile');
        if (tempoPresetMobileEl) {
            tempoPresetMobileEl.addEventListener('change', (e) => {
                if (e.target.value) updateBpm(e.target.value);
            });
        }

        // =============================================
        // APP MODE — go fullscreen (removes the browser
        // address bar) AND lock the screen to landscape so
        // the device itself rotates: no browser chrome is
        // left showing on the side of the studio.
        // (Android/Chrome: full support. iOS Safari can't do
        //  element-fullscreen / orientation lock, so the CSS
        //  force-landscape + 100dvh keep it fitted; true
        //  chrome-less needs "Add to Home Screen".)
        // =============================================
        async function enterStudioMode() {
            const el = document.documentElement;
            const rfs = el.requestFullscreen || el.webkitRequestFullscreen || el.msRequestFullscreen;
            if (rfs) {
                try { await rfs.call(el); } catch (e) { /* denied — ignore */ }
            }
            try {
                if (screen.orientation && screen.orientation.lock) {
                    await screen.orientation.lock('landscape');
                }
            } catch (e) { /* unsupported / not allowed — ignore */ }
        }

        function fullscreenSupported() {
            const el = document.documentElement;
            return !!(el.requestFullscreen || el.webkitRequestFullscreen || el.msRequestFullscreen);
        }
        function isFullscreen() {
            return !!(document.fullscreenElement || document.webkitFullscreenElement);
        }

        // First-gesture handler: prime audio immediately and (re)try fullscreen.
        // We keep it armed and retry on every tap until fullscreen actually
        // succeeds — a single failed/denied request must not leave the address
        // bar showing for the rest of the session.
        let studioModeHandler = null;
        function studioFirstGesture() {
            HarmonivaAudio.warmup();               // memoised — safe to call repeatedly
            if (fullscreenSupported()) {
                enterStudioMode();                 // fullscreenchange disarms on success
            } else {
                disarmStudioMode();                // iOS: nothing to do; audio is primed
            }
        }
        function armStudioModeOnce() {
            if (!isCompactViewport() || studioModeHandler) return;
            studioModeHandler = studioFirstGesture;
            document.addEventListener('pointerdown', studioModeHandler, true);
            document.addEventListener('touchstart', studioModeHandler, true);
        }
        function disarmStudioMode() {
            if (!studioModeHandler) return;
            document.removeEventListener('pointerdown', studioModeHandler, true);
            document.removeEventListener('touchstart', studioModeHandler, true);
            studioModeHandler = null;
        }
        function onFsChange() {
            if (isFullscreen()) disarmStudioMode();   // success → stop retrying
            else armStudioModeOnce();                 // exited → re-arm
        }
        document.addEventListener('fullscreenchange', onFsChange);
        document.addEventListener('webkitfullscreenchange', onFsChange);

        // If the browser restores this page from its back/forward cache
        // (common on mobile), force a fresh load so updates are never stale.
        window.addEventListener('pageshow', (e) => {
            if (e.persisted) window.location.reload();
        });

        // Initialize piano on page load
        document.addEventListener('DOMContentLoaded', () => {
            buildPiano();
            setupPianoDragScroll();
            armStudioModeOnce();
            // Begin downloading the piano samples right away so the first key
            // press already sounds like a real piano (not the fallback synth).
            try { window.HarmonivaAudio.preload(); } catch (e) {}
        });
    </script>

    @include('partials.guest-timer-popup', ['timerKey' => 'piano-studio', 'initialSeconds' => 240, 'repeatSeconds' => 120])
    @include('partials.responsive-notation')
</body>
</html>
