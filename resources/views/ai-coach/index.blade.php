<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('app.ai_coach.title') }} - {{ config('app.name', 'Harmoniva') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: { 50:'#faf5ff',100:'#f3e8ff',200:'#e9d5ff',300:'#d8b4fe',400:'#c084fc',500:'#a855f7',600:'#9333ea',700:'#7c3aed',800:'#6b21a8',900:'#581c87' },
                        accent:  { 400:'#fb923c',500:'#f97316',600:'#ea580c' }
                    }
                }
            }
        }
    </script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="font-sans bg-gray-50 min-h-screen">

@include('partials.navbar', ['active' => 'ai-coach'])

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Page Header (outside grid) --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center shadow-sm">
            <i data-lucide="brain" class="w-5 h-5 text-white"></i>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('app.ai_coach.title') }}</h1>
            <p class="text-sm text-gray-500">{{ __('app.ai_coach.generate_plan_desc') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[7fr_3fr] gap-6 items-start"
         x-data="aiCoach()"
         x-init="init()">

        {{-- Left: Main content --}}
        <div class="space-y-6">

            {{-- Profile Summary Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-primary-600"></i>
                    {{ __('app.ai_coach.your_profile') }}
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="text-center p-3 bg-gray-50 rounded-xl">
                        <p class="text-xs text-gray-500 mb-1">{{ __('app.ai_coach.instrument') }}</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $profile?->primary_instrument ?: '—' }}</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-xl">
                        <p class="text-xs text-gray-500 mb-1">{{ __('app.ai_coach.level') }}</p>
                        @php
                            $levelMap = [
                                'beginner'     => __('app.ai_coach.level_beginner'),
                                'intermediate' => __('app.ai_coach.level_intermediate'),
                                'advanced'     => __('app.ai_coach.level_advanced'),
                            ];
                        @endphp
                        <p class="text-sm font-semibold text-gray-900">{{ $levelMap[$profile?->musical_level] ?? '—' }}</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-xl">
                        <p class="text-xs text-gray-500 mb-1">{{ __('app.ai_coach.weekly_practice') }}</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $profile?->weekly_practice_hours ? $profile->weekly_practice_hours . ' ' . __('app.ai_coach.hours_suffix') : '—' }}</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-xl">
                        <p class="text-xs text-gray-500 mb-1">{{ __('app.ai_coach.recent_sessions') }}</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $practiceHistory->count() }} {{ __('app.ai_coach.records_suffix') }}</p>
                    </div>
                </div>
                @if(!$profile || !$profile->primary_instrument || !$profile->musical_level)
                    <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0"></i>
                        <p class="text-sm text-amber-700">
                            {{ __('app.ai_coach.complete_profile') }}
                            <a href="{{ route('profile.edit', ['tab' => 'music']) }}" class="font-semibold underline">{{ __('app.ai_coach.music_profile_link') }}</a>
                        </p>
                    </div>
                @endif
            </div>

            {{-- Generate Button --}}
            <div x-show="!plan && !loading">
                <div class="bg-gradient-to-br from-primary-50 to-purple-50 border border-primary-100 rounded-2xl p-8 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i data-lucide="sparkles" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('app.ai_coach.generate_plan_title') }}</h3>
                    <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">{{ __('app.ai_coach.generate_plan_desc') }}</p>
                    <button @click="generatePlan()"
                            class="inline-flex items-center gap-2 px-8 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition text-sm font-semibold shadow-sm">
                        <i data-lucide="wand-2" class="w-5 h-5"></i>
                        {{ __('app.ai_coach.generate') }}
                    </button>
                </div>
            </div>

            {{-- Loading State --}}
            <div x-show="loading" x-cloak class="text-center py-16">
                <div class="w-16 h-16 rounded-full bg-primary-100 flex items-center justify-center mx-auto mb-4 animate-pulse">
                    <i data-lucide="brain" class="w-8 h-8 text-primary-600"></i>
                </div>
                <p class="text-lg font-semibold text-gray-800 mb-1">{{ __('app.ai_coach.preparing') }}</p>
                <p class="text-sm text-gray-500">{{ __('app.ai_coach.analyzing') }}</p>
                <div class="flex items-center justify-center gap-1 mt-4">
                    <div class="w-2 h-2 rounded-full bg-primary-400 animate-bounce" style="animation-delay: 0ms"></div>
                    <div class="w-2 h-2 rounded-full bg-primary-400 animate-bounce" style="animation-delay: 150ms"></div>
                    <div class="w-2 h-2 rounded-full bg-primary-400 animate-bounce" style="animation-delay: 300ms"></div>
                </div>
            </div>

            {{-- Error State --}}
            <div x-show="error" x-cloak class="p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5"></i>
                <div>
                    <p class="text-sm font-medium text-red-700">{{ __('app.ai_coach.error') }}</p>
                    <p class="text-sm text-red-600" x-text="error"></p>
                    <button @click="error = null; plan = null" class="mt-2 text-xs text-red-600 underline">{{ __('app.ai_coach.try_again') }}</button>
                </div>
            </div>

            {{-- Plan Result --}}
            <div x-show="plan" x-cloak class="space-y-6">

                {{-- Motivation --}}
                <div x-show="plan?.motivation" class="bg-gradient-to-r from-primary-600 to-purple-600 rounded-2xl p-6 text-white">
                    <div class="flex items-start gap-3">
                        <i data-lucide="quote" class="w-6 h-6 text-white/60 flex-shrink-0 mt-0.5"></i>
                        <p class="text-base font-medium italic leading-relaxed" x-text="plan?.motivation"></p>
                    </div>
                </div>

                {{-- Focus Areas --}}
                <div x-show="plan?.focus_areas?.length" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="target" class="w-4 h-4 text-primary-600"></i>
                        {{ __('app.ai_coach.focus_areas') }}
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="(area, idx) in plan?.focus_areas" :key="idx">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-primary-50 border border-primary-200 text-primary-700"
                                  x-text="area"></span>
                        </template>
                    </div>
                </div>

                {{-- Weekly Plan --}}
                <div x-show="plan?.weekly_plan?.length" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-5 flex items-center gap-2">
                        <i data-lucide="calendar" class="w-4 h-4 text-primary-600"></i>
                        {{ __('app.ai_coach.weekly_plan_section') }}
                    </h3>
                    <div class="space-y-3">
                        <template x-for="(day, idx) in plan?.weekly_plan" :key="idx">
                            <div class="border border-gray-100 rounded-xl overflow-hidden">
                                <div class="flex items-center justify-between px-4 py-3 bg-gray-50">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-primary-100 flex items-center justify-center">
                                            <span class="text-xs font-bold text-primary-700" x-text="idx + 1"></span>
                                        </div>
                                        <span class="font-semibold text-gray-800 text-sm" x-text="day.day"></span>
                                    </div>
                                    <span x-show="day.duration_minutes" class="inline-flex items-center gap-1 text-xs text-gray-500 bg-white px-2 py-1 rounded-full border border-gray-200">
                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                        <span x-text="day.duration_minutes + ' {{ __('app.ai_coach.minutes_suffix') }}'"></span>
                                    </span>
                                </div>
                                <div class="px-4 py-3">
                                    <ul class="space-y-1.5">
                                        <template x-for="(exercise, eIdx) in day.exercises" :key="eIdx">
                                            <li class="flex items-start gap-2 text-sm text-gray-700">
                                                <i data-lucide="check-circle-2" class="w-4 h-4 text-primary-400 flex-shrink-0 mt-0.5"></i>
                                                <span x-text="exercise"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Tips --}}
                <div x-show="plan?.tips?.length" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="lightbulb" class="w-4 h-4 text-accent-500"></i>
                        {{ __('app.ai_coach.tips_section') }}
                    </h3>
                    <div class="space-y-3">
                        <template x-for="(tip, idx) in plan?.tips" :key="idx">
                            <div class="flex items-start gap-3 p-3 bg-amber-50 rounded-xl border border-amber-100">
                                <div class="w-6 h-6 rounded-full bg-accent-500 flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-bold text-white" x-text="idx + 1"></span>
                                </div>
                                <p class="text-sm text-gray-700 leading-relaxed" x-text="tip"></p>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Regenerate --}}
                <div class="flex justify-center pt-2">
                    <button @click="generatePlan()"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition text-sm font-medium shadow-sm">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        {{ __('app.ai_coach.regenerate') }}
                    </button>
                </div>

            </div>
        </div>

        {{-- Right: Sidebar (1/3) --}}
        <div class="space-y-5">

            {{-- Ask Your AI Music Assistant Card --}}
            <div class="rounded-2xl overflow-hidden shadow-sm">
                <div class="bg-gradient-to-br from-violet-500 to-fuchsia-600 p-5 text-white">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="message-square" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-base leading-tight">Ask Your AI Music Assistant</h3>
                            <p class="text-white/70 text-xs">Music theory & ear training</p>
                        </div>
                    </div>
                    <p class="text-white/85 text-sm mb-4 leading-relaxed">Ask questions about music theory and ear training</p>
                    <a href="/ai-chat" class="block text-center bg-white text-violet-700 font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-100 transition-colors text-sm">
                        Ask a Question →
                    </a>
                </div>
            </div>

            {{-- Your Profile --}}
            @php
                $levelLabels = ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'];
            @endphp
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2 text-sm">
                    <i data-lucide="user-check" class="w-4 h-4 text-purple-500"></i>
                    Your Profile
                </h3>
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Instrument</span>
                        <span class="font-medium text-gray-800">{{ $profile?->primary_instrument ?: '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Level</span>
                        <span class="font-medium text-gray-800">{{ $levelLabels[$profile?->musical_level ?? ''] ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Weekly Practice</span>
                        <span class="font-medium text-gray-800">{{ $profile?->weekly_practice_hours ? $profile->weekly_practice_hours . ' hrs' : '—' }}</span>
                    </div>
                </div>
                @if(!$profile || !$profile->primary_instrument || !$profile->musical_level)
                    <a href="{{ route('profile.edit', ['tab' => 'music']) }}" class="mt-3 block text-center text-xs font-medium text-purple-600 bg-purple-50 hover:bg-purple-100 rounded-lg py-2 transition-colors">
                        Complete Your Profile →
                    </a>
                @endif
            </div>

            {{-- My Progress --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-1 flex items-center gap-2 text-sm">
                    <i data-lucide="trending-up" class="w-4 h-4 text-green-500"></i>
                    My Progress
                </h3>
                <p class="text-xs text-gray-400 mb-4">Track your ear training journey and see how far you've come</p>
                <div class="grid grid-cols-2 gap-2.5">
                    <div class="bg-orange-50 rounded-xl p-3 text-center">
                        <div class="text-xl font-bold text-gray-900">{{ $streak }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">🔥 Streak</div>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-3 text-center">
                        <div class="text-xl font-bold text-gray-900">{{ $totalSessions }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">Sessions</div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-3 text-center">
                        <div class="text-xl font-bold text-gray-900">{{ $accuracy }}%</div>
                        <div class="text-xs text-gray-500 mt-0.5">Accuracy</div>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-3 text-center">
                        <div class="text-xl font-bold text-gray-900">{{ $totalQuestions }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">Questions</div>
                    </div>
                </div>
                <a href="/progress" class="mt-3 block text-center text-xs font-medium text-purple-600 hover:text-purple-700 transition-colors">
                    View Full Report →
                </a>
            </div>

        </div>
    </div>
</div>

<script>
    function aiCoach() {
        return {
            plan: null,
            loading: false,
            error: null,

            init() {
                lucide.createIcons();
            },

            async generatePlan() {
                this.loading = true;
                this.plan = null;
                this.error = null;

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response = await fetch('{{ route('ai-coach.generate') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({}),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.error = data.error || '{{ __('app.messages.error_occurred') }}';
                    } else {
                        this.plan = data.plan;
                        this.$nextTick(() => {
                            lucide.createIcons();
                        });
                    }
                } catch (e) {
                    this.error = '{{ __('app.ai_coach.connection_error') }}';
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
</body>
</html>
