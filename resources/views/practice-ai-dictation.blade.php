<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>AI Melodic Dictation - {{ config('app.name', 'Harmoniva') }}</title>

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
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
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
            background: linear-gradient(135deg, #312e81 0%, #4f46e5 55%, #9333ea 100%);
        }
        .card {
            background: white;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px -1px rgb(0 0 0 / 0.1), 0 1px 3px -1px rgb(0 0 0 / 0.08);
        }
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
        }
        .progress-bar {
            background: linear-gradient(90deg, #22c55e 0%, #4ade80 100%);
        }
        .answer-btn {
            transition: all 0.2s ease;
        }
        .answer-btn:hover {
            border-color: #6366f1;
            background: #eef2ff;
        }
        .answer-btn.selected {
            border-color: #6366f1;
            background: #eef2ff;
        }
        .answer-btn.correct {
            border-color: #22c55e;
            background: #f0fdf4;
        }
        .answer-btn.incorrect {
            border-color: #ef4444;
            background: #fef2f2;
        }
    </style>
    @livewireStyles
</head>
<body class="font-sans bg-gray-50 min-h-screen">
    {{-- Navbar --}}
    @include('partials.navbar', ['active' => 'ai'])

    <livewire:practice-ai-melodic-dictation :practices="$practices" />

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
            async playNote(note, duration) {
                await ensureReady();
                sampler.triggerAttackRelease(note, duration ?? 1);
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
            // Sample-accurate scheduling helpers (used by the dictation builder):
            // prepare() loads the sampler, now() returns the shared audio clock, and
            // playNoteAt() fires a note at an absolute time on that clock.
            async prepare() { await ensureReady(); },
            now() { return Tone.now(); },
            playNoteAt(note, duration, time) {
                if (!sampler) return;
                sampler.triggerAttackRelease(note, duration ?? 1, time);
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

    @include('partials.responsive-notation')

    @livewireScripts
</body>
</html>
