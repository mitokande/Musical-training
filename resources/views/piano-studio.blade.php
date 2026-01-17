<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Piano Studio - {{ config('app.name', 'Ear Training Studio') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- VexFlow for music notation -->
    <script src="https://cdn.jsdelivr.net/npm/vexflow@4.2.2/build/cjs/vexflow.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#faf5ff',
                            100: '#f3e8ff',
                            200: '#e9d5ff',
                            300: '#d8b4fe',
                            400: '#c084fc',
                            500: '#a855f7',
                            600: '#9333ea',
                            700: '#7c3aed',
                            800: '#6b21a8',
                            900: '#581c87',
                        },
                        accent: {
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                        }
                    }
                }
            }
        }
    </script>

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
            min-height: 200px;
            overflow-x: auto;
            scroll-behavior: smooth;
        }

        #notation-container svg {
            display: block;
            margin: 0 auto;
        }

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

        /* Responsive: Hide sidebars on smaller screens */
        @media (max-width: 1024px) {
            .sidebar {
                display: none;
            }
            .mobile-controls {
                display: block;
            }
        }

        @media (min-width: 1025px) {
            .mobile-controls {
                display: none;
            }
        }
    </style>
</head>
<body class="font-sans bg-gray-50 min-h-screen">
    <!-- Header/Navigation -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-purple-600 to-orange-500 flex items-center justify-center">
                        <i data-lucide="music" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-bold text-lg text-gray-900">Ear Training<br class="sm:hidden"><span class="text-purple-600"> Studio</span></span>
                </div>

                <!-- Navigation -->
                <nav class="hidden lg:flex items-center gap-1">
                    <a href="/dashboard" class="nav-item flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-600">
                        <i data-lucide="home" class="w-4 h-4"></i>
                        Home
                    </a>
                    <a href="/learn" class="nav-item flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-600">
                        <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                        Learn Path
                    </a>
                    <a href="/ai-exercises" class="nav-item flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-600">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                        AI Exercises
                    </a>
                    <a href="/piano-studio" class="nav-item active flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-700">
                        <i data-lucide="piano" class="w-4 h-4"></i>
                        Piano Studio
                    </a>
                    <a href="/progress" class="nav-item flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-600">
                        <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                        My Progress
                    </a>
                </nav>

                <!-- User Menu -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold text-sm">
                            {{ substr(Auth::user()->name ?? 'M', 0, 1) }}
                        </div>
                        <span class="hidden sm:block text-sm font-medium text-gray-700">{{ Auth::user()->name ?? 'Guest' }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                            <i data-lucide="log-out" class="w-5 h-5"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-600 to-orange-500 flex items-center justify-center">
                    <i data-lucide="music-2" class="w-6 h-6 text-white"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Piano Studio</h1>
            </div>
        </div>

        <!-- Mobile Controls (shown on smaller screens) -->
        <div class="mobile-controls card p-4 mb-4">
            <div class="flex flex-wrap items-center gap-4">
                <button id="playbackBtnMobile" class="inline-flex items-center gap-2 px-4 py-2 btn-primary text-white font-semibold rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <i data-lucide="play" class="w-4 h-4"></i>
                    <span>Playback</span>
                </button>
                <button id="clearBtnMobile" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-all">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Clear
                </button>
                <button id="metronomeBtnMobile" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-all">
                    <i data-lucide="timer" class="w-4 h-4"></i>
                    Metronome
                </button>
                <div class="flex-1"></div>
                <div class="text-sm text-gray-500">
                    <span id="noteCountMobile">0</span> notes recorded
                </div>
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
                            <h3 class="font-semibold text-gray-900 text-sm">Metronome</h3>
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
                            <option value="">Select Preset...</option>
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

                <!-- Music Notation Display -->
                <div class="card mb-4">
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex items-center gap-2">
                            <i data-lucide="music-2" class="w-5 h-5 text-purple-600"></i>
                            <h3 class="font-semibold text-gray-900">Music Notation</h3>
                        </div>
                    </div>
                    <div id="notation-container">
                        <div id="notation-output"></div>
                        <p id="notation-placeholder" class="text-center text-gray-400 py-12">Play some notes to see them appear here...</p>
                    </div>
                </div>

                <!-- Piano Keyboard -->
                <div class="card p-3">
                    <div class="piano-wrapper">
                        <div class="piano-container" id="piano">
                            <!-- Piano keys will be generated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar - Playback -->
            <aside class="sidebar sidebar-right">
                <div class="playback-widget">
                    <div class="playback-header">
                        <i data-lucide="music-2" class="w-5 h-5 text-purple-600"></i>
                        <h3 class="font-semibold text-gray-900">Playback</h3>
                    </div>
                    
                    <!-- Playback Buttons -->
                    <div class="playback-btn-group">
                        <button id="playbackBtn" class="inline-flex items-center justify-center gap-2 px-4 py-2 btn-primary text-white font-semibold rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            <i data-lucide="play" class="w-4 h-4"></i>
                            <span>Playback</span>
                        </button>
                        <button id="clearBtn" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-all">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            Clear
                        </button>
                    </div>
                    
                    <!-- Note Count -->
                    <div class="note-count-display">
                        <div class="note-count-value" id="noteCount">0</div>
                        <div class="note-count-label">notes recorded</div>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8">
                <!-- Brand -->
                <div class="col-span-2 md:col-span-4 lg:col-span-1">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-purple-600 to-orange-500 flex items-center justify-center">
                            <i data-lucide="music" class="w-5 h-5 text-white"></i>
                        </div>
                        <span class="font-bold text-lg text-white">Ear Training Studio</span>
                    </div>
                    <p class="text-sm mb-4">
                        An AI-powered ear training platform for musicians, students, and educators. Master your musical ear with personalized exercises.
                    </p>
                </div>

                <!-- Platform -->
                <div>
                    <h4 class="font-semibold text-white mb-4">Platform</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/dashboard" class="hover:text-white transition-colors">Home</a></li>
                        <li><a href="/learn" class="hover:text-white transition-colors">Learning Path</a></li>
                        <li><a href="/piano-studio" class="hover:text-white transition-colors">Piano Studio</a></li>
                    </ul>
                </div>

                <!-- Resources -->
                <div>
                    <h4 class="font-semibold text-white mb-4">Resources</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">All Resources</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Articles</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">FAQ & Help Center</a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div>
                    <h4 class="font-semibold text-white mb-4">Company</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">About Us</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contact Support</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Privacy & Terms</a></li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-gray-800 mt-8 pt-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-gray-500">© {{ date('Y') }} Ear Training Studio. All rights reserved.</p>
                    <div class="flex items-center gap-6 text-sm">
                        <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                        <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
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

        // DOM Elements
        const pianoContainer = document.getElementById('piano');
        const playbackBtn = document.getElementById('playbackBtn');
        const clearBtn = document.getElementById('clearBtn');
        const noteCountEl = document.getElementById('noteCount');
        const notationOutput = document.getElementById('notation-output');
        const notationPlaceholder = document.getElementById('notation-placeholder');

        // Play a note using cached audio files (falls back to API if not cached)
        function playNote(note, octave, duration = 1) {
            // Format note name for cached file (e.g., "C#" -> "Cs")
            const noteName = note.replace('#', 's');
            const cachedUrl = `/audio/piano/${noteName}${octave}_d${duration}.mp3`;
            
            const audio = new Audio(cachedUrl);
            
            // Try cached file first, fall back to API if it fails
            audio.play().catch(err => {
                console.log('Cached audio not found, falling back to API...');
                const apiNoteName = note.replace('#', '%23');
                const apiUrl = `https://mithatck.com/music/api/note.php?note=${apiNoteName}${octave}&duration=${duration}`;
                const apiAudio = new Audio(apiUrl);
                apiAudio.play().catch(apiErr => {
                    console.log('Audio playback failed:', apiErr);
                });
            });
            
            return audio;
        }

        // Build piano keyboard
        function buildPiano() {
            const whiteKeys = NOTES.filter(n => n.type === 'white');
            const blackKeys = NOTES.filter(n => n.type === 'black');
            const totalWhiteKeys = whiteKeys.length; // 28 keys for 4 octaves
            
            // Create white keys container
            const whiteKeysContainer = document.createElement('div');
            whiteKeysContainer.className = 'piano-keys-container';
            
            whiteKeys.forEach((noteData, index) => {
                const key = document.createElement('div');
                key.className = 'piano-key white-key';
                key.dataset.note = noteData.note;
                key.dataset.octave = noteData.octave;
                key.dataset.midi = noteData.midi;
                key.id = `key-${noteData.midi}`;
                
                // Only show key label if it has a keyboard shortcut
                if (noteData.key) {
                    const label = document.createElement('span');
                    label.className = 'key-label';
                    label.textContent = noteData.key.toUpperCase();
                    key.appendChild(label);
                }
                
                key.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    triggerNote(noteData);
                });
                key.addEventListener('mouseup', () => releaseNote(noteData));
                key.addEventListener('mouseleave', () => releaseNote(noteData));
                
                whiteKeysContainer.appendChild(key);
            });
            
            // Add black keys - positions for 4 octaves
            // Each octave has black keys after white key positions: 0(C#), 1(D#), 3(F#), 4(G#), 5(A#)
            const blackKeyPositions = [
                0, 1, 3, 4, 5,       // Octave 2
                7, 8, 10, 11, 12,    // Octave 3
                14, 15, 17, 18, 19,  // Octave 4
                21, 22, 24, 25, 26   // Octave 5
            ];
            let blackKeyIndex = 0;
            
            // Calculate percentage-based positioning
            const whiteKeyWidthPercent = 100 / totalWhiteKeys;
            const blackKeyWidthPercent = whiteKeyWidthPercent * 0.6; // Black keys are 60% width of white keys
            
            blackKeys.forEach((noteData) => {
                const key = document.createElement('div');
                key.className = 'piano-key black-key';
                key.dataset.note = noteData.note;
                key.dataset.octave = noteData.octave;
                key.dataset.midi = noteData.midi;
                key.id = `key-${noteData.midi}`;
                
                // Position black key using percentages for full-width layout
                const position = blackKeyPositions[blackKeyIndex];
                const leftPercent = ((position + 1) * whiteKeyWidthPercent) - (blackKeyWidthPercent / 2);
                key.style.left = `${leftPercent}%`;
                key.style.width = `${blackKeyWidthPercent}%`;
                
                // Only show key label if it has a keyboard shortcut
                if (noteData.key) {
                    const label = document.createElement('span');
                    label.className = 'key-label';
                    label.textContent = noteData.key.toUpperCase();
                    key.appendChild(label);
                }
                
                key.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    triggerNote(noteData);
                });
                key.addEventListener('mouseup', () => releaseNote(noteData));
                key.addEventListener('mouseleave', () => releaseNote(noteData));
                
                whiteKeysContainer.appendChild(key);
                blackKeyIndex++;
            });
            
            pianoContainer.appendChild(whiteKeysContainer);
        }

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
            
            const renderer = new VF.Renderer(notationOutput, VF.Renderer.Backends.SVG);
            renderer.resize(totalWidth, 200);
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
            
            // Render each measure
            let xPos = 10;
            measures.forEach((measureNotes, measureIndex) => {
                const stave = new VF.Stave(xPos, 20, measureWidth);
                
                if (measureIndex === 0) {
                    stave.addClef('treble');
                }
                
                stave.setContext(context).draw();
                
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
                
                xPos += measureWidth;
            });
            
            // Auto-scroll to the right to show the latest notes
            const notationContainer = document.getElementById('notation-container');
            notationContainer.scrollLeft = notationContainer.scrollWidth;
        }

        // Playback recorded notes
        async function playback() {
            if (recordedNotes.length === 0 || isPlaying) return;
            
            isPlaying = true;
            playbackBtn.classList.add('playing');
            playbackBtn.innerHTML = '<i data-lucide="pause" class="w-4 h-4"></i><span>Playing...</span>';
            lucide.createIcons();
            
            for (let i = 0; i < recordedNotes.length; i++) {
                if (!isPlaying) break;
                
                const noteData = recordedNotes[i];
                const nextNote = recordedNotes[i + 1];
                
                // Visual feedback
                const keyEl = document.getElementById(`key-${noteData.midi}`);
                if (keyEl) keyEl.classList.add('active');
                
                playNote(noteData.note, noteData.octave, 1);
                
                // Calculate delay until next note
                let delay = 500; // Default delay
                if (nextNote) {
                    delay = Math.min(Math.max(nextNote.timestamp - noteData.timestamp, 200), 1500);
                }
                
                await new Promise(resolve => setTimeout(resolve, delay));
                
                if (keyEl) keyEl.classList.remove('active');
            }
            
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
            
            // Update tempo preset selector to match
            const matchingOption = Array.from(tempoPreset.options).find(opt => opt.value === String(metronomeBpm));
            if (matchingOption) {
                tempoPreset.value = metronomeBpm;
            } else {
                tempoPreset.value = '';
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

        // Initialize piano on page load
        document.addEventListener('DOMContentLoaded', () => {
            buildPiano();
        });
    </script>
</body>
</html>
