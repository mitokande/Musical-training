<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $lpTitle = $exercise->getLocalizedTitle();
        // Lesson titles are only unique inside their own curriculum: "The Full
        // Twelve" is lesson 14 of Melodic Intervals, lesson 14 of Harmonic
        // Intervals AND lesson 13 of Interval Construction. Nine lesson pages
        // shipped byte-identical <title>s, which reads as duplicate content and
        // gives searchers no way to tell the results apart — so the curriculum
        // name goes in the tab title (the H1 on the page keeps the short form).
        $lpTitleTag = trim($lpTitle.($category?->name ? ' — '.$category->name : ''));
    @endphp
    <title>{{ $lpTitleTag }} — {{ config('app.name') }}</title>
    @php
        $lpDescription = \Illuminate\Support\Str::limit(
            strip_tags((string) ($exercise->getLocalizedDescription() ?: $lpTitle)),
            160
        );
        $lpUrl = route('learning-path.show', $exercise->slug);
        // Built inside @php so Blade does not treat the "@context"/"@type"
        // literal keys as directives and corrupt the JSON.
        $lpJsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $lpTitle,
            'description' => $lpDescription,
            'url' => $lpUrl,
            'isAccessibleForFree' => true,
            'inLanguage' => str_replace('_', '-', app()->getLocale()),
            'provider' => [
                '@type' => 'Organization',
                'name' => 'Harmoniva',
                'url' => url('/'),
                'logo' => asset('images/logo-full.png'),
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $lpBreadcrumbJsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Learning Path', 'item' => locale_url('/learn')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $lpTitle, 'item' => $lpUrl],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @endphp
    <meta name="description" content="{{ $lpDescription }}">
    <link rel="canonical" href="{{ $lpUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Harmoniva">
    <meta property="og:title" content="{{ $lpTitleTag }} — Harmoniva">
    <meta property="og:description" content="{{ $lpDescription }}">
    <meta property="og:url" content="{{ $lpUrl }}">
    <meta property="og:image" content="{{ asset('images/og-image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $lpTitleTag }} — Harmoniva">
    <meta name="twitter:description" content="{{ $lpDescription }}">
    <meta name="twitter:image" content="{{ asset('images/og-image.png') }}">
    <script type="application/ld+json">{!! $lpJsonLd !!}</script>
    <script type="application/ld+json">{!! $lpBreadcrumbJsonLd !!}</script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: { 500: '#a855f7', 600: '#9333ea', 700: '#7c3aed' },
                        accent:  { 500: '#f97316' }
                    }
                }
            }
        }
    </script>
    <style>
        .hero-gradient { background: linear-gradient(135deg, #9333ea 0%, #c084fc 35%, #f97316 100%); }
        .btn-primary   { background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%); }
        .btn-primary:hover { background: linear-gradient(135deg, #7c3aed 0%, #6b21a8 100%); }
    </style>
</head>
<body class="bg-gray-50 font-sans min-h-screen">

{{-- Navbar --}}
<nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
        <div class="flex items-center gap-3">
            <a href="{{ locale_url('/learn') }}" class="flex items-center gap-2 text-gray-500 hover:text-gray-700">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                <span class="text-sm font-medium">{{ __('app.home_ui.lp_title') }}</span>
            </a>
            <span class="text-gray-300">/</span>
            <span class="text-sm text-gray-700 font-medium truncate max-w-xs">{{ $exercise->getLocalizedTitle() }}</span>
        </div>
        <div class="flex items-center gap-2">
            @if($prev)
                <a href="{{ route('learning-path.show', $prev->slug) }}"
                   class="px-3 py-1.5 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 flex items-center gap-1">
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i> {{ __('app.home_ui.lp_previous') }}
                </a>
            @endif
            @if($next)
                <a href="{{ route('learning-path.show', $next->slug) }}"
                   class="px-3 py-1.5 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 flex items-center gap-1">
                    {{ __('app.home_ui.lp_next') }} <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            @endif
        </div>
    </div>
</nav>

<main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

    {{-- Flash messages --}}
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 flex items-center gap-2 text-sm">
            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Header card --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="h-2 hero-gradient"></div>
        <div class="p-8">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    {{-- Breadcrumb --}}
                    <div class="flex items-center gap-2 text-xs text-gray-400 mb-3">
                        <span>{{ $category->parent->name ?? $category->name }}</span>
                        @if($category->parent)
                            <i data-lucide="chevron-right" class="w-3 h-3"></i>
                            <span>{{ $category->name }}</span>
                        @endif
                        <i data-lucide="chevron-right" class="w-3 h-3"></i>
                        <span>Lesson {{ $exercise->sort_order }}</span>
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $exercise->getLocalizedTitle() }}</h1>
                    <p class="text-gray-500 leading-relaxed">{{ $exercise->getLocalizedDescription() }}</p>

                    <div class="flex items-center gap-3 mt-4">
                        @php
                            $levelColors = ['beginner'=>'bg-green-100 text-green-700','intermediate'=>'bg-yellow-100 text-yellow-700','advanced'=>'bg-red-100 text-red-700'];
                            $levelLabels = ['beginner'=>'Beginner','intermediate'=>'Intermediate','advanced'=>'Advanced'];
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $levelColors[$exercise->level] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $levelLabels[$exercise->level] ?? $exercise->level }}
                        </span>
                        <span class="flex items-center gap-1 text-xs text-gray-500">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            ~{{ $exercise->estimated_duration_minutes }} min
                        </span>
                        @if(!empty($exercise->tags))
                            @foreach(array_slice($exercise->tags, 0, 3) as $tag)
                                <span class="px-2 py-0.5 bg-purple-50 text-purple-600 rounded text-xs">{{ $tag }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>

                {{-- Progress badge --}}
                @if($progress && $progress->completed)
                    <div class="shrink-0 flex flex-col items-center gap-1">
                        <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center">
                            <i data-lucide="check-circle" class="w-7 h-7 text-green-600"></i>
                        </div>
                        <span class="text-xs font-semibold text-green-700">{{ $progress->score }}%</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Question count variants --}}
    @php
        $isPremiumUser = auth()->user()?->isEffectivelyPremium() ?? false;
        $quotaLimit = $sessionQuota['limit'] ?? -1;
        $quotaUsed = $sessionQuota['used'] ?? 0;
        $quotaExhausted = $quotaLimit !== -1 && $quotaUsed >= $quotaLimit;
    @endphp

    {{-- Daily session limit banner / limit-reached CTA --}}
    @if(session('lp_limit_reached') || $quotaExhausted)
        <div class="rounded-2xl border-2 border-orange-300 bg-orange-50 p-5 flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-1">
                <div class="font-bold text-orange-800 mb-1">
                    @guest
                        {{ __('app.limits.lp_guest_reached_title') }}
                    @else
                        {{ __('app.limits.lp_free_reached_title') }}
                    @endguest
                </div>
                <p class="text-sm text-orange-700">
                    @guest
                        {{ __('app.limits.lp_guest_reached_desc', ['limit' => $quotaLimit]) }}
                    @else
                        {{ __('app.limits.lp_free_reached_desc', ['limit' => $quotaLimit]) }}
                    @endguest
                </p>
            </div>
            @guest
                <a href="{{ route('register') }}" class="shrink-0 px-5 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-pink-500 text-white text-sm font-bold">
                    {{ __('app.limits.cta_create_account') }}
                </a>
            @else
                <a href="{{ locale_url('/pricing') }}" class="shrink-0 px-5 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-pink-500 text-white text-sm font-bold">
                    {{ __('app.limits.cta_upgrade') }}
                </a>
            @endguest
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm px-4 py-3">{{ session('error') }}</div>
    @endif

    <div class="space-y-3">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="text-lg font-bold text-gray-800">{{ __('app.home_ui.lp_choose_count') }}</h2>
            @if($quotaLimit !== -1)
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $quotaExhausted ? 'bg-orange-100 text-orange-700' : 'bg-purple-50 text-purple-600' }}">
                    {{ __('app.limits.sessions_used_today', ['used' => min($quotaUsed, $quotaLimit), 'limit' => $quotaLimit]) }}
                </span>
            @endif
        </div>
        <p class="text-sm text-gray-500">{{ __('app.home_ui.lp_free_note') }}</p>

        <div class="grid grid-cols-3 gap-4">

            {{-- 5 Questions — Free --}}
            <form method="POST" action="{{ route('learning-path.start', $exercise->slug) }}">
                @csrf
                <input type="hidden" name="question_count" value="5">
                <button type="submit"
                        class="w-full bg-white border-2 border-purple-300 hover:border-purple-500 rounded-2xl p-5 text-left transition group">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-2xl font-black text-purple-600">5</span>
                        <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-semibold">{{ __('app.home_ui.lp_free') }}</span>
                    </div>
                    <div class="text-sm font-semibold text-gray-700">{{ __('app.home_ui.lp_quick') }}</div>
                    <div class="text-xs text-gray-400 mt-1">~{{ max(1, round($exercise->estimated_duration_minutes * 0.35)) }} min</div>
                    <div class="mt-3 flex items-center gap-1 text-purple-600 text-xs font-medium group-hover:gap-2 transition-all">
                        {{ __('app.home_ui.lp_start') }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </div>
                </button>
            </form>

            {{-- 10 Questions — Premium --}}
            @if($isPremiumUser)
                <form method="POST" action="{{ route('learning-path.start', $exercise->slug) }}">
                    @csrf
                    <input type="hidden" name="question_count" value="10">
                    <button type="submit"
                            class="w-full bg-white border-2 border-purple-300 hover:border-purple-500 rounded-2xl p-5 text-left transition group">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-2xl font-black text-purple-600">10</span>
                            <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold flex items-center gap-1">
                                <i data-lucide="crown" class="w-3 h-3"></i> {{ __('app.home_ui.lp_premium') }}
                            </span>
                        </div>
                        <div class="text-sm font-semibold text-gray-700">{{ __('app.home_ui.lp_standard') }}</div>
                        <div class="text-xs text-gray-400 mt-1">~{{ max(1, round($exercise->estimated_duration_minutes * 0.7)) }} min</div>
                        <div class="mt-3 flex items-center gap-1 text-purple-600 text-xs font-medium group-hover:gap-2 transition-all">
                            {{ __('app.home_ui.lp_start') }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </div>
                    </button>
                </form>
            @else
                <div class="bg-gray-50 border-2 border-gray-200 rounded-2xl p-5 opacity-70">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-2xl font-black text-gray-300">10</span>
                        <span class="px-2 py-0.5 bg-gray-200 text-gray-500 rounded-full text-xs font-semibold flex items-center gap-1">
                            <i data-lucide="lock" class="w-3 h-3"></i> {{ __('app.home_ui.lp_premium') }}
                        </span>
                    </div>
                    <div class="text-sm font-semibold text-gray-400">{{ __('app.home_ui.lp_standard') }}</div>
                    <div class="text-xs text-gray-300 mt-1">~{{ max(1, round($exercise->estimated_duration_minutes * 0.7)) }} min</div>
                    <a href="{{ auth()->check() ? locale_url('/pricing') : route('register') }}"
                       class="mt-3 flex items-center gap-1 text-orange-500 text-xs font-medium hover:underline">
                        {{ __('app.home_ui.lp_unlock') }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            @endif

            {{-- 15 Questions — Premium --}}
            @if($isPremiumUser)
                <form method="POST" action="{{ route('learning-path.start', $exercise->slug) }}">
                    @csrf
                    <input type="hidden" name="question_count" value="15">
                    <button type="submit"
                            class="w-full bg-gradient-to-br from-purple-50 to-orange-50 border-2 border-purple-400 hover:border-purple-600 rounded-2xl p-5 text-left transition group">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-2xl font-black text-purple-700">15</span>
                            <span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold flex items-center gap-1">
                                <i data-lucide="zap" class="w-3 h-3"></i> {{ __('app.home_ui.lp_full') }}
                            </span>
                        </div>
                        <div class="text-sm font-semibold text-gray-700">{{ __('app.home_ui.lp_full_lesson') }}</div>
                        <div class="text-xs text-gray-400 mt-1">~{{ $exercise->estimated_duration_minutes }} min</div>
                        <div class="mt-3 flex items-center gap-1 text-purple-700 text-xs font-medium group-hover:gap-2 transition-all">
                            {{ __('app.home_ui.lp_start') }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </div>
                    </button>
                </form>
            @else
                <div class="bg-gray-50 border-2 border-gray-200 rounded-2xl p-5 opacity-70">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-2xl font-black text-gray-300">15</span>
                        <span class="px-2 py-0.5 bg-gray-200 text-gray-500 rounded-full text-xs font-semibold flex items-center gap-1">
                            <i data-lucide="lock" class="w-3 h-3"></i> {{ __('app.home_ui.lp_premium') }}
                        </span>
                    </div>
                    <div class="text-sm font-semibold text-gray-400">{{ __('app.home_ui.lp_full_lesson') }}</div>
                    <div class="text-xs text-gray-300 mt-1">~{{ $exercise->estimated_duration_minutes }} min</div>
                    <a href="{{ auth()->check() ? locale_url('/pricing') : route('register') }}"
                       class="mt-3 flex items-center gap-1 text-orange-500 text-xs font-medium hover:underline">
                        {{ __('app.home_ui.lp_unlock') }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            @endif

        </div>
    </div>

    {{-- Skills trained --}}
    @if(!empty($exercise->skills_trained))
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                <i data-lucide="target" class="w-4 h-4 text-purple-500"></i>
                {{ __('app.home_ui.lp_skills') }}
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach($exercise->skills_trained as $skill)
                    <span class="px-3 py-1.5 bg-purple-50 text-purple-700 rounded-full text-sm font-medium">{{ str_replace('-', ' ', $skill) }}</span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Prev/Next navigation --}}
    @if($prev || $next)
        <div class="flex justify-between gap-4">
            @if($prev)
                <a href="{{ route('learning-path.show', $prev->slug) }}"
                   class="flex-1 bg-white border border-gray-200 rounded-xl p-4 hover:border-purple-300 hover:shadow-sm transition flex items-center gap-3">
                    <i data-lucide="chevron-left" class="w-5 h-5 text-gray-400 shrink-0"></i>
                    <div>
                        <div class="text-xs text-gray-400">{{ __('app.home_ui.lp_previous') }}</div>
                        <div class="text-sm font-semibold text-gray-700 truncate">{{ $prev->getLocalizedTitle() }}</div>
                    </div>
                </a>
            @else
                <div class="flex-1"></div>
            @endif

            @if($next)
                <a href="{{ route('learning-path.show', $next->slug) }}"
                   class="flex-1 bg-white border border-gray-200 rounded-xl p-4 hover:border-purple-300 hover:shadow-sm transition flex items-center justify-end gap-3">
                    <div class="text-right">
                        <div class="text-xs text-gray-400">{{ __('app.home_ui.lp_next') }}</div>
                        <div class="text-sm font-semibold text-gray-700 truncate">{{ $next->getLocalizedTitle() }}</div>
                    </div>
                    <i data-lucide="chevron-right" class="w-5 h-5 text-gray-400 shrink-0"></i>
                </a>
            @endif
        </div>
    @endif

</main>

<script>
    lucide.createIcons();
</script>
</body>
</html>
