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
                            50: '#faf5ff', 100: '#f3e8ff', 200: '#e9d5ff', 300: '#d8b4fe',
                            400: '#c084fc', 500: '#a855f7', 600: '#9333ea', 700: '#7c3aed',
                            800: '#6b21a8', 900: '#581c87',
                        },
                        accent: { 400: '#fb923c', 500: '#f97316', 600: '#ea580c' }
                    }
                }
            }
        }
    </script>

    <style>
        .hero-gradient { background: linear-gradient(135deg, #6d28d9 0%, #9333ea 28%, #db2777 62%, #fb923c 100%); }
        .note { position: absolute; line-height: 1; user-select: none; pointer-events: none; }
        .card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1); }
        .btn-primary { background: linear-gradient(135deg, #6d28d9 0%, #8b5cf6 100%); transition: all 0.2s; }
        .btn-primary:hover { background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%); transform: translateY(-1px); box-shadow: 0 8px 20px -4px rgb(109 40 217/0.4); }
        .premium-gradient { background: linear-gradient(135deg, #6b21a8 0%, #7c3aed 50%, #9333ea 100%); }
    </style>
    @livewireStyles
</head>
<body class="font-sans bg-gray-50 min-h-screen">
    {{-- Navbar --}}
    @include('partials.navbar', ['active' => $navActive ?? 'dashboard'])

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Hero Section -->
        <div class="hero-gradient rounded-2xl p-8 mb-6 relative overflow-hidden">
            {{-- Decorative notes --}}
            <span class="note" style="top:10px;right:40px;font-size:72px;color:rgba(253,224,71,0.45);transform:rotate(14deg);" aria-hidden="true">♫</span>
            <span class="note" style="top:50px;right:160px;font-size:36px;color:rgba(249,168,212,0.55);transform:rotate(-8deg);" aria-hidden="true">♪</span>
            <span class="note" style="top:18px;right:260px;font-size:24px;color:rgba(167,243,208,0.50);transform:rotate(20deg);" aria-hidden="true">♬</span>
            <span class="note" style="bottom:14px;right:80px;font-size:48px;color:rgba(196,181,253,0.40);transform:rotate(-12deg);" aria-hidden="true">♩</span>
            <span class="note" style="bottom:20px;right:230px;font-size:30px;color:rgba(253,186,116,0.50);transform:rotate(10deg);" aria-hidden="true">♫</span>
            <span class="note" style="top:-10px;left:-8px;font-size:100px;color:rgba(255,255,255,0.07);transform:rotate(-18deg);" aria-hidden="true">♬</span>

            {{-- Upgrade to Premium — top-right of the welcome box, for free accounts only --}}
            @unless(Auth::user()->isEffectivelyPremium())
                <a href="{{ route('checkout.show') }}"
                   class="absolute top-4 right-4 sm:top-6 sm:right-6 z-20 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white shadow-lg hover:-translate-y-0.5 transition-all"
                   style="background:linear-gradient(135deg,#fbbf24,#f59e0b);">
                    <i data-lucide="crown" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">{{ __('app.dashboard.upgrade_premium') }}</span>
                </a>
            @endunless

            @php $isTeacherViewer = Auth::check() && Auth::user()->hasTeacherAccount(); @endphp
            @if($isTeacherViewer)
                {{-- Teacher "Welcome back" box — same design as the student box, teacher-specific buttons. --}}
                @php
                    $heroUnreadNotif = Auth::user()->unreadNotifications()->count();
                    $heroUnreadMsg = app(\App\Services\Teacher\TeacherMessagingService::class)->unreadTotalFor(Auth::user());
                    $teacherHeroLinks = [
                        ['href' => route(auth()->user()->crmRouteName('dashboard')),        'icon' => 'layout-dashboard', 'label' => __('teacher.dashboard.hero_dashboard'),      'badge' => 0],
                        ['href' => route('notifications.index'),      'icon' => 'bell',             'label' => __('teacher.dashboard.hero_notifications'),  'badge' => $heroUnreadNotif],
                        ['href' => route(auth()->user()->crmRouteName('messages.index')),   'icon' => 'message-circle',   'label' => __('teacher.dashboard.hero_messages'),       'badge' => $heroUnreadMsg],
                        ['href' => route(auth()->user()->crmRouteName('profile.edit')),     'icon' => 'user-pen',         'label' => __('teacher.dashboard.hero_profile'),        'badge' => 0],
                        ['href' => route(auth()->user()->crmRouteName('students.index')),   'icon' => 'users',            'label' => __('teacher.dashboard.hero_students'),       'badge' => 0],
                        ['href' => route(auth()->user()->crmRouteName('calendar.index')),   'icon' => 'calendar',         'label' => __('teacher.dashboard.hero_calendar'),       'badge' => 0],
                        ['href' => route(auth()->user()->crmRouteName('assignments.index')),'icon' => 'clipboard-list',   'label' => __('teacher.dashboard.hero_assignments'),    'badge' => 0],
                        ['href' => url('/exercise-setup'),            'icon' => 'wand-sparkles',    'label' => __('teacher.dashboard.hero_exercise_setup'), 'badge' => 0],
                        ['href' => url('/ai-exercises'),              'icon' => 'sparkles',         'label' => __('app.nav.ai_exercises'),                  'badge' => 0],
                        ['href' => url('/games'),                     'icon' => 'gamepad-2',        'label' => __('app.nav.games'),                         'badge' => 0],
                        ['href' => route(auth()->user()->crmRouteName('content.index')),    'icon' => 'newspaper',        'label' => __('teacher.dashboard.hero_content'),        'badge' => 0],
                        ['href' => route(auth()->user()->crmRouteName('settings')),         'icon' => 'settings',         'label' => __('teacher.dashboard.hero_settings'),       'badge' => 0],
                    ];
                @endphp
                <div class="relative z-10">
                    <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                        {{ __('teacher.dashboard.welcome', ['name' => explode(' ', Auth::user()->name ?? 'Teacher')[0]]) }} 👋
                    </h1>
                    <p class="text-white/80 mb-6">{{ __('teacher.dashboard.welcome_subtitle') }}</p>

                    <div class="flex flex-wrap gap-2">
                        @foreach($teacherHeroLinks as $i => $link)
                            <a href="{{ $link['href'] }}"
                               class="relative inline-flex items-center gap-2 px-6 py-3 font-semibold rounded-lg transition-colors text-base backdrop-blur-sm {{ $i === 0 ? 'bg-white text-teal-800 hover:bg-gray-100' : 'bg-white/20 text-white hover:bg-white/30' }}">
                                <i data-lucide="{{ $link['icon'] }}" class="w-5 h-5"></i>
                                {{ $link['label'] }}
                                @if($link['badge'] > 0)
                                    <span class="ml-1 bg-white text-purple-700 text-xs font-bold rounded-full px-1.5 min-w-[18px] text-center leading-5">{{ $link['badge'] > 9 ? '9+' : $link['badge'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
            <div class="relative z-10">
                <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                    {{ __('app.dashboard.welcome', ['name' => explode(' ', Auth::user()->name ?? 'User')[0]]) }} 👋
                </h1>
                <p class="text-white/80 mb-6">{{ __('app.dashboard.subtitle') }}</p>

                <div class="flex flex-col gap-2">
                    {{-- Satır 1: My Profile → AI Coach --}}
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-teal-800 font-semibold rounded-lg hover:bg-gray-100 transition-colors text-base">
                            <i data-lucide="user" class="w-5 h-5"></i>
                            {{ __('app.nav.my_profile') }}
                        </a>
                        <a href="/ai-exercises" class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors backdrop-blur-sm text-base">
                            <i data-lucide="sparkles" class="w-5 h-5"></i>
                            {{ __('app.nav.ai_exercises') }}
                        </a>
                        <a href="/games" class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors backdrop-blur-sm text-base">
                            <i data-lucide="gamepad-2" class="w-5 h-5"></i>
                            {{ __('app.nav.games') }}
                        </a>
                        <a href="/exercise-setup" class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors backdrop-blur-sm text-base">
                            <i data-lucide="wand-sparkles" class="w-5 h-5"></i>
                            {{ __('app.nav.setup_studio') }}
                        </a>
                        <a href="/piano-studio" class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors backdrop-blur-sm text-base">
                            <i data-lucide="piano" class="w-5 h-5"></i>
                            {{ __('app.nav.piano') }}
                        </a>
                        <a href="/ai-coach" class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors backdrop-blur-sm text-base">
                            <i data-lucide="bot" class="w-5 h-5"></i>
                            {{ __('app.dashboard.ai_coach') }}
                        </a>
                    </div>
                    {{-- Satır 2: Ask AI → Notifications --}}
                    <div class="flex flex-wrap gap-2">
                        <a href="/ai-chat" class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors backdrop-blur-sm text-base">
                            <i data-lucide="message-square" class="w-5 h-5"></i>
                            {{ __('app.dashboard.ask_ai') }}
                        </a>
                        <a href="/learn" class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors backdrop-blur-sm text-base">
                            <i data-lucide="play-circle" class="w-5 h-5"></i>
                            Practice
                        </a>
                        <a href="/progress" class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors backdrop-blur-sm text-base">
                            <i data-lucide="trending-up" class="w-5 h-5"></i>
                            {{ __('app.nav.progress') }}
                        </a>
                        <a href="/messages" class="inline-flex items-center gap-2 px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors backdrop-blur-sm text-base">
                            <i data-lucide="message-circle" class="w-5 h-5"></i>
                            {{ __('app.nav.messages') }}
                        </a>
                        <a href="{{ route('notifications.index') }}" class="relative inline-flex items-center gap-2 px-6 py-3 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-colors backdrop-blur-sm text-base">
                            <i data-lucide="bell" class="w-5 h-5"></i>
                            {{ __('app.nav.notifications') }}
                            @php $heroUnreadNotif = Auth::user()->unreadNotifications()->count(); @endphp
                            @if($heroUnreadNotif > 0)
                                <span class="ml-1 bg-white text-purple-700 text-xs font-bold rounded-full px-1.5 min-w-[18px] text-center leading-5">{{ $heroUnreadNotif > 9 ? '9+' : $heroUnreadNotif }}</span>
                            @endif
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Main Grid: Feed + Sidebar -->
        <div class="grid grid-cols-1 lg:grid-cols-[7fr_3fr] gap-6 items-start">
            <!-- Left: Feed -->
            <div>
                @livewire('social-feed')
            </div>

            <!-- Right: Sidebar -->
            <div class="space-y-6">
                <!-- Compact Stats -->
                <div class="card p-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center shrink-0 text-base">🔥</div>
                            <div class="min-w-0">
                                <div class="text-lg font-bold text-gray-900 leading-none">{{ $streak }}</div>
                                <p class="text-xs text-gray-500 truncate">{{ __('app.dashboard.current_streak') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                                <i data-lucide="activity" class="w-4 h-4 text-blue-500"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-lg font-bold text-gray-900 leading-none">{{ $totalSessions }}</div>
                                <p class="text-xs text-gray-500 truncate">{{ __('app.social.sessions') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                                <i data-lucide="target" class="w-4 h-4 text-green-500"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-lg font-bold text-gray-900 leading-none">{{ $accuracy }}%</div>
                                <p class="text-xs text-gray-500 truncate">{{ __('app.social.accuracy') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-lg bg-purple-50 flex items-center justify-center shrink-0">
                                <i data-lucide="users" class="w-4 h-4 text-purple-500"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-lg font-bold text-gray-900 leading-none">{{ $followersCount }}</div>
                                <p class="text-xs text-gray-500 truncate">{{ __('app.social.followers') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- People to Follow -->
                @if($suggestedUsers->isNotEmpty())
                <div class="card p-5">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="user-plus" class="w-4 h-4 text-purple-600"></i>
                        {{ __('app.social.suggested_people') }}
                    </h3>
                    <div class="space-y-3">
                        @foreach($suggestedUsers as $suggested)
                            @php
                                // Avatar colour encodes account type + plan:
                                //   Teacher → purple, Student → pink;  Premium → base tone, Free → 1-2 tones lighter.
                                $suggestedIsTeacher = $suggested->isTeacher() || $suggested->teacherProfile !== null;
                                $suggestedIsPremium = $suggestedIsTeacher
                                    ? ($suggested->teacherProfile?->tier === 'premium')
                                    : $suggested->isPremium();
                                [$avatarBg, $avatarRing] = match(true) {
                                    $suggestedIsTeacher && $suggestedIsPremium => ['bg-purple-600', 'ring-purple-600'],
                                    $suggestedIsTeacher                        => ['bg-purple-400', 'ring-purple-400'],
                                    $suggestedIsPremium                        => ['bg-pink-500',   'ring-pink-500'],
                                    default                                    => ['bg-pink-300',   'ring-pink-300'],
                                };
                            @endphp
                            <div class="flex items-center gap-3" wire:key="suggest-{{ $suggested->id }}">
                                <a href="{{ url('/u/'.$suggested->username) }}" class="shrink-0">
                                    @if($suggested->hasAvatar())
                                        <img src="{{ $suggested->avatar }}" alt="" class="w-9 h-9 rounded-full object-cover ring-2 {{ $avatarRing }}">
                                    @else
                                        <div class="w-9 h-9 rounded-full {{ $avatarBg }} flex items-center justify-center text-white text-sm font-semibold">
                                            {{ substr($suggested->name ?? 'U', 0, 1) }}
                                        </div>
                                    @endif
                                </a>
                                <a href="{{ url('/u/'.$suggested->username) }}" class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $suggested->name }}</p>
                                    <p class="text-xs text-gray-400 truncate">&#64;{{ $suggested->username }}</p>
                                </a>
                                @livewire('follow-button', ['user' => $suggested], key('follow-'.$suggested->id))
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Popular Exercises -->
                @if($popularExercises->isNotEmpty())
                <div class="card p-5">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="flame" class="w-4 h-4 text-orange-500"></i>
                        {{ __('app.dashboard.popular_exercises') }}
                    </h3>
                    <div class="space-y-1">
                        @foreach($popularExercises as $exercise)
                            <a href="{{ url('/learn-exercise/'.$exercise->slug) }}"
                               class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-gray-50 transition-colors group">
                                <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center shrink-0">
                                    <i data-lucide="music-2" class="w-4 h-4 text-purple-600"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-purple-600 truncate">{{ $exercise->getLocalizedTitle() }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Quick Actions -->
                <div class="card p-5">
                    <h3 class="font-semibold text-gray-900 mb-4">{{ __('app.dashboard.quick_actions') }}</h3>
                    <div class="space-y-2">
                        <a href="/learn" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                                <i data-lucide="play-circle" class="w-4 h-4 text-green-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-purple-600">{{ __('app.dashboard.continue_learning_path') }}</span>
                        </a>
                        <a href="/games" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                                <i data-lucide="gamepad-2" class="w-4 h-4 text-orange-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-purple-600">{{ __('app.nav.games') }}</span>
                        </a>
                        <a href="/ai-exercises" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                <i data-lucide="sparkles" class="w-4 h-4 text-purple-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-purple-600">{{ __('app.dashboard.ai_exercises') }}</span>
                        </a>
                        <a href="/progress" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <i data-lucide="bar-chart-2" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-purple-600">{{ __('app.dashboard.view_progress_report') }}</span>
                        </a>
                    </div>
                </div>

                <!-- Upgrade to Premium -->
                @if($user->isFree())
                <div class="premium-gradient rounded-xl p-6 text-white">
                    <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center mb-4">
                        <i data-lucide="crown" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-2">{{ __('app.dashboard.upgrade_premium') }}</h3>
                    <p class="text-white/90 text-sm mb-5">{{ __('app.dashboard.premium_description') }}</p>
                    <a href="{{ route('checkout.show') }}" class="block text-center w-full bg-white text-purple-700 font-semibold py-3 px-4 rounded-lg hover:bg-gray-100 transition-colors">
                        {{ __('app.dashboard.learn_more') }}
                    </a>
                </div>
                @endif
            </div>
        </div>
    </main>

    @include('partials.footer')

    @livewireScripts
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', () => window.lucide && lucide.createIcons());
        });
        document.addEventListener('DOMContentLoaded', () => window.lucide && lucide.createIcons());
        document.addEventListener('livewire:navigated', () => window.lucide && lucide.createIcons());
    </script>
</body>
</html>
