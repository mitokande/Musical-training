<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $slug ?? 'Practice' }} - {{ config('app.name', 'Harmoniva') }}</title>

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
        .btn-primary {
            background: linear-gradient(135deg, #6d28d9 0%, #7c3aed 100%);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5b21b6 0%, #6d28d9 100%);
        }
        .progress-bar {
            background: linear-gradient(90deg, #22c55e 0%, #4ade80 100%);
        }
        .answer-btn {
            transition: all 0.2s ease;
        }
        .answer-btn:hover {
            border-color: #ec4899;
            background: #fdf2f8;
        }
        .answer-btn.selected {
            border-color: #ec4899;
            background: #fdf2f8;
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
    <script>
        window._practiceBackUrl = '{{ $backUrl ?? "/learn" }}';
        function _updatePracticeBackUrls() {
            const url = window._practiceBackUrl;
            if (!url || url === '/learn') return;
            document.querySelectorAll('a[href="/learn"]').forEach(a => { a.href = url; });
        }
        document.addEventListener('DOMContentLoaded', _updatePracticeBackUrls);
        document.addEventListener('livewire:init', function() {
            _updatePracticeBackUrls();
            if (typeof Livewire !== 'undefined') {
                Livewire.on('practice-updated', () => setTimeout(_updatePracticeBackUrls, 80));
            }
        });
    </script>
    {{-- Navbar --}}
    @include('partials.navbar', ['active' => 'learn'])

    @switch ($slug)
        @case ('single-note-practice')
            <livewire:practice-single-note :practices="$practices" />
            @break
        @case ('interval-direction-practice')
            <livewire:practice-interval-direction :practices="$practices" />
            @break
        @case ('interval-comparison-practice')
            <livewire:practice-interval-comparison :practices="$practices" />
            @break
        @case ('melodic-interval-practice')
            <livewire:practice-melodic-interval :practices="$practices" />
            @break
        @case ('harmonic-interval-practice')
            <livewire:practice-harmonic-interval :practices="$practices" />
            @break
        @case ('interval-construction-practice')
            <livewire:practice-interval-construction :practices="$practices" />
            @break
        @case ('chord-practice')
            <livewire:practice-chord :practices="$practices" />
            @break
        @case ('scale-practice')
            <livewire:practice-scale :practices="$practices" />
            @break
        @case ('rhythm-practice')
            <livewire:practice-rhythm :practices="$practices" />
            @break
        @case ('melodic-dictation')
            <livewire:practice-melodic-dictation :practices="$practices" />
            @break
    @endswitch

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

