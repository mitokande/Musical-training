<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Dashboard - {{ config('app.name', 'Harmoniva') }}</title>

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
            background: linear-gradient(135deg, #0e3f5c 0%, #1a6e8a 45%, #0f8a7a 100%);
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
        .ai-coach-gradient {
            background: linear-gradient(135deg, #9333ea 0%, #c084fc 50%, #f97316 100%);
        }
        .premium-gradient {
            background: linear-gradient(135deg, #6b21a8 0%, #7c3aed 50%, #9333ea 100%);
        }
    </style>
</head>
<body class="font-sans bg-gray-50 min-h-screen">
    {{-- Navbar --}}
    @include('partials.navbar', ['active' => 'dashboard'])

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Hero Section -->
        <div class="hero-gradient rounded-2xl p-8 mb-8 relative overflow-hidden">
            <!-- Decorative elements -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                        <i data-lucide="music" class="w-6 h-6 text-white"></i>
                    </div>
                    <span class="px-3 py-1 bg-purple-500 text-white text-xs font-semibold rounded-full">{{ __('app.home_ui.role_student') }}</span>
                </div>
                
                <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                    Welcome back, {{ explode(' ', Auth::user()->name ?? 'Mithat')[0] }}! 🎵
                </h1>
                <p class="text-white/80 mb-6">{{ __('app.home_ui.welcome_sub') }}</p>
                
                <div class="flex flex-wrap gap-3">
                    <a href="{{ locale_url('/learn') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-teal-800 font-semibold rounded-lg hover:bg-gray-100 transition-colors">
                        <i data-lucide="play-circle" class="w-5 h-5"></i>
                        {{ __('app.home_ui.continue_learning') }}
                    </a>
                    <a href="/exercise-setup" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors backdrop-blur-sm">
                        <i data-lucide="wand-sparkles" class="w-5 h-5"></i>
                        {{ __('app.home_ui.exercise_setup') }}
                    </a>
                    <a href="/ai-exercises" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors backdrop-blur-sm">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                        {{ __('app.home_ui.ai_exercises') }}
                    </a>
                    <a href="/ai-chat" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors backdrop-blur-sm">
                        <i data-lucide="message-circle" class="w-5 h-5"></i>
                        {{ __('app.home_ui.ask_ai') }}
                    </a>
                    <a href="{{ locale_url('/piano-studio') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors backdrop-blur-sm">
                        <i data-lucide="piano" class="w-5 h-5"></i>
                        {{ __('app.home_ui.piano_studio') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Current Streak -->
            <div class="card p-5">
                <div class="flex items-start justify-between mb-3">
                    <span class="text-sm text-gray-500">{{ __('app.home_ui.current_streak') }}</span>
                    <span class="text-orange-500">🔥</span>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">0</div>
                <p class="text-sm text-gray-500">{{ __('app.dashboard.days_in_a_row') }} 🔥</p>
            </div>

            <!-- Total Practice -->
            <div class="card p-5">
                <div class="flex items-start justify-between mb-3">
                    <span class="text-sm text-gray-500">{{ __('app.home_ui.total_practice') }}</span>
                    <i data-lucide="clock" class="w-5 h-5 text-blue-500"></i>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">3</div>
                <p class="text-sm text-gray-500">{{ __('app.home_ui.minutes_logged') }}</p>
            </div>

            <!-- XP Points -->
            <div class="card p-5">
                <div class="flex items-start justify-between mb-3">
                    <span class="text-sm text-gray-500">{{ __('app.home_ui.xp_points') }}</span>
                    <i data-lucide="sparkles" class="w-5 h-5 text-yellow-500"></i>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">90</div>
                <p class="text-sm text-gray-500">{{ __('app.home_ui.experience_earned') }}</p>
            </div>

            <!-- Badges -->
            <div class="card p-5">
                <div class="flex items-start justify-between mb-3">
                    <span class="text-sm text-gray-500">{{ __('app.home_ui.badges') }}</span>
                    <i data-lucide="award" class="w-5 h-5 text-purple-500"></i>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">0</div>
                <p class="text-sm text-gray-500">{{ __('app.home_ui.achievements') }}</p>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Today's Practice Goal -->
                <div class="card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center">
                            <i data-lucide="target" class="w-4 h-4 text-green-600"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900">{{ __('app.home_ui.daily_goal') }}</h3>
                    </div>
                    
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-2xl font-bold text-gray-900">0 / 15 min</span>
                        <span class="text-sm text-gray-500">0% complete</span>
                    </div>
                    
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                        <div class="progress-bar h-2 rounded-full" style="width: 0%"></div>
                    </div>
                    
                    <p class="text-sm text-gray-500">{{ __('app.home_ui.goal_left', ['minutes' => 15]) }}</p>
                </div>

                <!-- Skill Mastery -->
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <i data-lucide="trending-up" class="w-5 h-5 text-purple-600"></i>
                            <h3 class="font-semibold text-gray-900">{{ __('app.home_ui.skill_mastery') }}</h3>
                        </div>
                        <a href="#" class="text-sm text-gray-500 hover:text-purple-600 flex items-center gap-1">
                            {{ __('app.home_ui.view_all') }}
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                    
                    <div class="space-y-5">
                        <!-- Scales -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">{{ __('app.home_ui.scales') }}</span>
                                <span class="text-sm font-semibold text-purple-600">50%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="progress-bar h-2 rounded-full" style="width: 50%"></div>
                            </div>
                        </div>
                        
                        <!-- Intervals -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">{{ __('app.home_ui.intervals') }}</span>
                                <span class="text-sm font-semibold text-purple-600">100%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="progress-bar h-2 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- AI Coach -->
                <div class="ai-coach-gradient rounded-xl p-6 text-white">
                    <div class="flex items-center gap-2 mb-4">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                        <h3 class="font-semibold">{{ __('app.home_ui.ai_coach') }}</h3>
                    </div>
                    
                    <p class="text-white/90 text-sm mb-5 text-center">
                        Get personalized guidance from your AI coach!
                    </p>
                    
                    <button class="w-full bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white font-semibold py-3 px-4 rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                        {{ __('app.home_ui.generate_plan') }}
                    </button>
                </div>

                <!-- Quick Actions -->
                <div class="card p-5">
                    <h3 class="font-semibold text-gray-900 mb-4">{{ __('app.home_ui.quick_actions') }}</h3>
                    
                    <div class="space-y-2">
                        <a href="{{ locale_url('/learn') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                                <i data-lucide="play-circle" class="w-4 h-4 text-green-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-purple-600">{{ __('app.home_ui.continue_path') }}</span>
                        </a>
                        
                        <a href="{{ locale_url('/piano-studio') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                                <i data-lucide="piano" class="w-4 h-4 text-orange-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-purple-600">{{ __('app.home_ui.piano_studio') }}</span>
                        </a>
                        
                        <a href="/exercise-setup" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                <i data-lucide="wand-sparkles" class="w-4 h-4 text-purple-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-purple-600">{{ __('app.home_ui.custom_session') }}</span>
                        </a>

                        <a href="/ai-exercises" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                                <i data-lucide="sparkles" class="w-4 h-4 text-indigo-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-purple-600">{{ __('app.home_ui.ai_exercises') }}</span>
                        </a>
                        
                        <a href="/progress" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <i data-lucide="bar-chart-2" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-purple-600">{{ __('app.home_ui.progress_report') }}</span>
                        </a>
                    </div>
                </div>

                <!-- Upgrade to Premium -->
                <div class="premium-gradient rounded-xl p-6 text-white">
                    <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center mb-4">
                        <i data-lucide="crown" class="w-6 h-6"></i>
                    </div>
                    
                    <h3 class="font-bold text-lg mb-2">{{ __('app.home_ui.upgrade_premium') }}</h3>
                    <p class="text-white/90 text-sm mb-5">
                        Unlock unlimited lessons, AI coaching, and advanced analytics.
                    </p>
                    
                    <a href="{{ route('checkout.show') }}" class="block text-center w-full bg-white text-purple-700 font-semibold py-3 px-4 rounded-lg hover:bg-gray-100 transition-colors">
                        {{ __('app.home_ui.learn_more') }}
                    </a>
                </div>
            </div>
        </div>
    </main>

    {{-- Shared Footer --}}
    @include('partials.footer')

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>

