<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Mixed Practice' }} - {{ config('app.name', 'Harmoniva') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@0.460.0"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <!-- Tone.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tone/15.3.5/Tone.js" integrity="sha512-F1myjNkIKU5XJtOs1HXRo/zOjiUsABgFEEGKLx/riwK82jRThZFebEnfF2HWo9eeC+iC1Nwwnn9Vj6OGq+r7rQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

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
            background: linear-gradient(135deg, #4c1d95 0%, #6d28d9 60%, #7c3aed 100%);
        }
        .card {
            background: white;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px -1px rgb(0 0 0 / 0.1), 0 1px 3px -1px rgb(0 0 0 / 0.08);
        }
        /* Play button — yeşil */
        .btn-primary {
            background: linear-gradient(135deg, #15803d 0%, #16a34a 60%, #22c55e 100%);
            box-shadow: 0 4px 14px -2px rgba(22,163,74,0.40);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #166534 0%, #15803d 60%, #16a34a 100%);
            box-shadow: 0 6px 18px -2px rgba(22,163,74,0.50);
        }
        .progress-bar {
            background: linear-gradient(90deg, #22c55e 0%, #4ade80 100%);
        }
        /* Cevap butonları */
        .answer-btn {
            background: #f5f3ff;
            border: 1.5px solid #ddd6fe;
            transition: all 0.18s cubic-bezier(0.34,1.56,0.64,1);
        }
        .answer-btn:hover {
            background: #ede9fe;
            border-color: #7c3aed;
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 8px 20px -4px rgba(124,58,237,0.25);
        }
        .answer-btn:active {
            transform: translateY(-1px) scale(1.01);
        }
        .answer-btn.selected {
            border-color: #7c3aed;
            background: #ede9fe;
        }
        .answer-btn.correct {
            border-color: #16a34a;
            background: #dcfce7;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px -4px rgba(22,163,74,0.30);
        }
        .answer-btn.incorrect {
            border-color: #ef4444;
            background: #fee2e2;
        }
    </style>
    @livewireStyles
</head>
<body class="font-sans bg-gray-50 min-h-screen">
    {{-- Navbar --}}
    @include('partials.navbar', ['active' => 'learn'])

    <!-- Livewire Mixed Practice Component -->
    <livewire:practice-mixed :practices="$practices" :title="$title" />

    @include('partials.footer')

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>

    <script>
    window.HarmonivaAudio = (function () {
        let sampler = null;
        let ready = false;

        function init() {
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

        async function ensureReady() {
            await Tone.start();
            if (!sampler) init();
            const deadline = Date.now() + 8000;
            while (!ready && Date.now() < deadline) {
                await new Promise(r => setTimeout(r, 80));
            }
        }

        return {
            // Resolve once the sampler is loaded (so callers can schedule on the audio clock).
            async prepare() { await ensureReady(); },
            // Current audio-context time (seconds) — shared clock for sample-accurate scheduling.
            now() { return Tone.now(); },
            async playNote(note, duration) {
                await ensureReady();
                sampler.triggerAttackRelease(note, duration ?? 1);
            },
            // Schedule a note at an absolute audio-context time (call after prepare()).
            playNoteAt(note, duration, time) {
                if (!sampler) return;
                sampler.triggerAttackRelease(note, duration ?? 1, time);
            },
            async playSimultaneous(notes, duration) {
                await ensureReady();
                const now = Tone.now();
                notes.forEach(n => sampler.triggerAttackRelease(n, duration ?? 2, now));
            },
            async playSequential(notes, intervalMs, duration) {
                await ensureReady();
                const now = Tone.now();
                notes.forEach((n, i) =>
                    sampler.triggerAttackRelease(n, duration ?? 1, now + i * ((intervalMs ?? 600) / 1000)));
            },
            async playArpeggio(notes, delayMs, duration) {
                await ensureReady();
                const now = Tone.now();
                notes.forEach((n, i) =>
                    sampler.triggerAttackRelease(n, duration ?? 1.5, now + i * ((delayMs ?? 400) / 1000)));
            },
            totalMs(notes, delayMs) {
                return (notes.length - 1) * (delayMs ?? 400) + 2000;
            },
            stop() { if (sampler) sampler.releaseAll(); }
        };
    })();

    document.addEventListener('livewire:init', function() {
        Livewire.on('practice-updated', () => {
            if (window.HarmonivaAudio) window.HarmonivaAudio.stop();
        });
    });
    </script>

    @livewireScripts
</body>
</html>

