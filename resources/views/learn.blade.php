<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Learning Path - {{ config('app.name', 'Harmoniva') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@0.460.0"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>

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
        .progress-bar {
            background: linear-gradient(90deg, #9333ea 0%, #c084fc 100%);
        }
        .progress-bar-yellow {
            background: linear-gradient(90deg, #f97316 0%, #fbbf24 100%);
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
        .filter-tab {
            transition: all 0.2s ease;
        }
        .filter-tab:hover {
            background: #f3f4f6;
        }
        .filter-tab.active {
            background: #1f2937;
            color: white;
        }
        .module-card {
            transition: all 0.2s ease;
        }
        .module-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }
    </style>
</head>
<body class="font-sans bg-gray-50 min-h-screen">
    {{-- Navbar --}}
    @include('partials.navbar', ['active' => 'learn'])

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Hero Section -->
        <div class="hero-gradient rounded-2xl p-8 mb-8 relative overflow-hidden">
            <!-- Decorative elements -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                        Your Learning Path
                    </h1>
                    <p class="text-white/80">Master essential ear training skills step by step</p>
                </div>
                
                <div class="mt-6 md:mt-0 text-right">
                    <div class="text-4xl font-bold text-white mb-1">4%</div>
                    <p class="text-white/80 text-sm mb-3">Overall Progress</p>
                    <div class="w-48 bg-white/30 rounded-full h-2">
                        <div class="progress-bar-yellow h-2 rounded-full" style="width: 4%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        {{-- Main category tabs --}}
        <div class="flex flex-wrap gap-2 mb-3" id="main-filter-tabs">
            <button class="filter-tab main-tab active flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium" data-filter="all">
                <i data-lucide="music" class="w-4 h-4"></i>
                All Modules
            </button>
            <button class="filter-tab main-tab flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-200" data-filter="intervals">
                <i data-lucide="git-branch" class="w-4 h-4"></i>
                Intervals
            </button>
            <button class="filter-tab main-tab flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-200" data-filter="scale-practice">
                <i data-lucide="trending-up" class="w-4 h-4"></i>
                Scales &amp; Modes
            </button>
            <button class="filter-tab main-tab flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-200" data-filter="chord-practice">
                <i data-lucide="star" class="w-4 h-4"></i>
                Chords
            </button>
            <button class="filter-tab main-tab flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-200" data-filter="rhythm-practice">
                <i data-lucide="clock" class="w-4 h-4"></i>
                Rhythm
            </button>
            <button class="filter-tab main-tab flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-200" data-filter="melodic-dictation">
                <i data-lucide="pencil" class="w-4 h-4"></i>
                Melodic Dictation
            </button>
            <button class="filter-tab main-tab flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-200" data-filter="single-note-practice">
                <i data-lucide="music-2" class="w-4 h-4"></i>
                Single Note
            </button>
        </div>

        {{-- Interval sub-tabs (hidden by default) --}}
        <div id="interval-subtabs" class="hidden flex flex-wrap gap-2 mb-5 pl-3 border-l-2 border-purple-300">
            <button class="sub-tab flex items-center px-4 py-1.5 rounded-full text-sm font-medium bg-purple-50 text-purple-700 border border-purple-200" data-sub-filter="intervals-all">
                All Intervals
            </button>
            <button class="sub-tab flex items-center px-4 py-1.5 rounded-full text-sm font-medium text-gray-600 bg-white border border-gray-200" data-sub-filter="melodic-interval-practice">
                Melodic Intervals
            </button>
            <button class="sub-tab flex items-center px-4 py-1.5 rounded-full text-sm font-medium text-gray-600 bg-white border border-gray-200" data-sub-filter="interval-direction-practice">
                Intervals Direction
            </button>
            <button class="sub-tab flex items-center px-4 py-1.5 rounded-full text-sm font-medium text-gray-600 bg-white border border-gray-200" data-sub-filter="harmonic-interval-practice">
                Harmonic Intervals
            </button>
            <button class="sub-tab flex items-center px-4 py-1.5 rounded-full text-sm font-medium text-gray-600 bg-white border border-gray-200" data-sub-filter="interval-construction-practice">
                Intervals Construction
            </button>
            <button class="sub-tab flex items-center px-4 py-1.5 rounded-full text-sm font-medium text-gray-600 bg-white border border-gray-200" data-sub-filter="interval-comparison-practice">
                Interval Comparison
            </button>
        </div>

        {{-- spacer when subtabs hidden --}}
        <div id="filter-spacer" class="mb-5"></div>

        <!-- Module Cards Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

            {{-- LP Exercise Cards --}}
            @php
            $catToSlug = [
                'melodic-intervals'     => 'melodic-interval-practice',
                'interval-direction'    => 'interval-direction-practice',
                'harmonic-intervals'    => 'harmonic-interval-practice',
                'interval-comparison'   => 'interval-comparison-practice',
                'interval-construction' => 'interval-construction-practice',
                'single-note'           => 'single-note-practice',
                'scales-modes'          => 'scale-practice',
                'chords'                => 'chord-practice',
                'rhythm'                => 'rhythm-practice',
                'melodic-dictation'     => 'melodic-dictation',
            ];
            $lpSlugColors = [
                'single-note-practice'           => ['icon' => '#7c3aed', 'band' => 'linear-gradient(90deg,#7c3aed,#a855f7)', 'bar' => 'linear-gradient(90deg,#7c3aed,#a855f7)', 'pct' => '#7c3aed'],
                'interval-direction-practice'    => ['icon' => '#2563eb', 'band' => 'linear-gradient(90deg,#2563eb,#3b82f6)', 'bar' => 'linear-gradient(90deg,#2563eb,#3b82f6)', 'pct' => '#2563eb'],
                'interval-comparison-practice'   => ['icon' => '#4f46e5', 'band' => 'linear-gradient(90deg,#4f46e5,#7c3aed)', 'bar' => 'linear-gradient(90deg,#4f46e5,#7c3aed)', 'pct' => '#4f46e5'],
                'melodic-interval-practice'      => ['icon' => '#0d9488', 'band' => 'linear-gradient(90deg,#0d9488,#14b8a6)', 'bar' => 'linear-gradient(90deg,#0d9488,#14b8a6)', 'pct' => '#0d9488'],
                'harmonic-interval-practice'     => ['icon' => '#059669', 'band' => 'linear-gradient(90deg,#059669,#10b981)', 'bar' => 'linear-gradient(90deg,#059669,#10b981)', 'pct' => '#059669'],
                'interval-construction-practice' => ['icon' => '#0284c7', 'band' => 'linear-gradient(90deg,#0284c7,#06b6d4)', 'bar' => 'linear-gradient(90deg,#0284c7,#06b6d4)', 'pct' => '#0284c7'],
                'chord-practice'                 => ['icon' => '#d97706', 'band' => 'linear-gradient(90deg,#d97706,#f59e0b)', 'bar' => 'linear-gradient(90deg,#d97706,#f59e0b)', 'pct' => '#d97706'],
                'scale-practice'                 => ['icon' => '#16a34a', 'band' => 'linear-gradient(90deg,#16a34a,#22c55e)', 'bar' => 'linear-gradient(90deg,#16a34a,#22c55e)', 'pct' => '#16a34a'],
                'rhythm-practice'                => ['icon' => '#dc2626', 'band' => 'linear-gradient(90deg,#dc2626,#ec4899)', 'bar' => 'linear-gradient(90deg,#dc2626,#ec4899)', 'pct' => '#dc2626'],
                'melodic-dictation'              => ['icon' => '#ea580c', 'band' => 'linear-gradient(90deg,#ea580c,#f97316)', 'bar' => 'linear-gradient(90deg,#ea580c,#f97316)', 'pct' => '#ea580c'],
            ];
            $levelLabels = ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'];
            $levelColors = ['beginner' => 'bg-green-100 text-green-700', 'intermediate' => 'bg-yellow-100 text-yellow-700', 'advanced' => 'bg-red-100 text-red-700'];
            @endphp

            @foreach($lpExercises as $ex)
                @php
                    $catSlug = $ex->category->slug ?? '';
                    $filterSlug = $catToSlug[$catSlug] ?? $catSlug;
                    $col = $lpSlugColors[$filterSlug] ?? ['icon' => '#9333ea', 'band' => 'linear-gradient(90deg,#9333ea,#7c3aed)', 'bar' => 'linear-gradient(90deg,#9333ea,#c084fc)', 'pct' => '#9333ea'];
                    $prog = $lpProgress[$ex->id] ?? null;
                    $progPct = $prog ? (int) $prog->score : 0;
                    $isCompleted = $prog && $prog->completed;
                @endphp
                <div class="module-card lp-card card relative overflow-hidden" data-type="{{ $filterSlug }}" data-slug="{{ $filterSlug }}" data-exercise-slug="{{ $ex->slug }}">
                    <div class="h-1.5 w-full" style="background: {{ $col['band'] }}"></div>
                    <div class="p-6">
                        @if($isCompleted)
                            <div class="absolute top-4 right-4">
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                    <i data-lucide="check-circle" class="w-3 h-3"></i>
                                    Done
                                </span>
                            </div>
                        @endif

                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background-color: {{ $col['icon'] }}">
                                <i data-lucide="music" class="w-5 h-5 text-white"></i>
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wide">Lesson {{ $ex->sort_order }}</span>
                        </div>

                        <h3 class="font-bold text-gray-900 mb-1 text-sm leading-snug">{{ $ex->getLocalizedTitle() }}</h3>
                        <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $ex->getLocalizedDescription() }}</p>

                        <div class="flex items-center gap-2 text-xs mb-2 flex-wrap">
                            <span class="px-2 py-0.5 rounded-full font-semibold {{ $levelColors[$ex->level] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $levelLabels[$ex->level] ?? $ex->level }}
                            </span>
                            <span class="flex items-center gap-1 text-gray-500">
                                <i data-lucide="clock" class="w-3 h-3"></i>
                                ~{{ $ex->estimated_duration_minutes }} min
                            </span>
                            @if($progPct > 0)
                                <span class="ml-auto font-semibold" style="color: {{ $col['pct'] }}">{{ $progPct }}%</span>
                            @endif
                        </div>

                        <div class="w-full bg-gray-100 rounded-full h-1 mb-3">
                            <div class="h-1 rounded-full" style="width: {{ $progPct }}%; background: {{ $col['bar'] }}"></div>
                        </div>

                        <a href="{{ route('learning-path.show', $ex->slug) }}"
                           class="w-full btn-primary text-white font-semibold py-2 px-4 rounded-lg flex items-center justify-center gap-2 text-sm">
                            <i data-lucide="play" class="w-3.5 h-3.5"></i>
                            {{ $isCompleted ? 'Practice Again' : 'Start Lesson' }}
                        </a>
                    </div>
                </div>
            @endforeach

        </div>
    </main>

    @include('partials.footer')

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
        
        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mainTabs       = document.querySelectorAll('.main-tab');
            const subTabs        = document.querySelectorAll('.sub-tab');
            const moduleCards    = document.querySelectorAll('.module-card');
            const intervalPanel  = document.getElementById('interval-subtabs');
            const filterSpacer   = document.getElementById('filter-spacer');

            const intervalSlugs = [
                'melodic-interval-practice',
                'interval-direction-practice',
                'harmonic-interval-practice',
                'interval-construction-practice',
                'interval-comparison-practice',
            ];

            function setMainActive(el) {
                mainTabs.forEach(t => {
                    t.classList.remove('active');
                    t.classList.add('text-gray-600', 'bg-white', 'border', 'border-gray-200');
                });
                el.classList.add('active');
                el.classList.remove('text-gray-600', 'bg-white', 'border', 'border-gray-200');
            }

            function setSubActive(el) {
                subTabs.forEach(s => {
                    s.classList.remove('bg-purple-50', 'text-purple-700', 'border-purple-200');
                    s.classList.add('text-gray-600', 'bg-white', 'border-gray-200');
                });
                el.classList.add('bg-purple-50', 'text-purple-700', 'border-purple-200');
                el.classList.remove('text-gray-600', 'bg-white', 'border-gray-200');
            }

            mainTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const filter = this.dataset.filter;
                    setMainActive(this);

                    if (filter === 'intervals') {
                        intervalPanel.classList.remove('hidden');
                        filterSpacer.classList.add('hidden');
                        // Reset sub-tabs to "All Intervals"
                        setSubActive(subTabs[0]);
                        moduleCards.forEach(card => {
                            card.style.display = intervalSlugs.includes(card.dataset.slug) ? '' : 'none';
                        });
                    } else {
                        intervalPanel.classList.add('hidden');
                        filterSpacer.classList.remove('hidden');
                        moduleCards.forEach(card => {
                            card.style.display = (filter === 'all' || card.dataset.slug === filter) ? '' : 'none';
                        });
                    }
                });
            });

            subTabs.forEach(sub => {
                sub.addEventListener('click', function() {
                    const subFilter = this.dataset.subFilter;
                    setSubActive(this);
                    moduleCards.forEach(card => {
                        if (subFilter === 'intervals-all') {
                            card.style.display = intervalSlugs.includes(card.dataset.slug) ? '' : 'none';
                        } else {
                            card.style.display = card.dataset.slug === subFilter ? '' : 'none';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>

