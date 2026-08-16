<!DOCTYPE html>
<html lang="{{ $seoHtmlLang }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php $pk = fn ($k, $r = []) => __('pages.pricing_teachers.'.$k, $r); @endphp
    <title>{{ $pk('meta_title') }}</title>
    <meta name="description" content="{{ $pk('meta_description') }}">
    @include('partials.public-seo-alt', [
        'seoPageTitle' => $pk('meta_title'),
        'seoPageDescription' => $pk('meta_description'),
    ])
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Harmoniva">
    <meta property="og:locale" content="{{ $seoOgLocale }}">
    <meta property="og:title" content="{{ $pk('og_title') }}">
    <meta property="og:description" content="{{ $pk('og_description') }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:image" content="{{ asset('images/og-image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pk('tw_title') }}">
    <meta name="twitter:description" content="{{ $pk('tw_description') }}">
    <meta name="twitter:image" content="{{ asset('images/og-image.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&family=instrument-serif:400,400i" rel="stylesheet" />

    @vite('resources/css/marketing.css')
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>


    <style>
        [x-cloak] { display: none !important; }
        body { background: #FAF7F2; overflow-x: hidden; }

        .gradient-text {
            background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .feature-row {
            border-bottom: 1px solid rgba(0,0,0,0.06);
            transition: background 0.2s ease;
        }
        .feature-row:hover { background: rgba(234,88,12,0.03); }
        .feature-row:last-child { border-bottom: none; }

        .reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        .hero-badge { animation: pulse-soft 3s ease-in-out infinite; }
        @keyframes pulse-soft {
            0%, 100% { box-shadow: 0 0 0 0 rgba(234,88,12,0.3); }
            50% { box-shadow: 0 0 0 12px rgba(234,88,12,0); }
        }
    </style>
</head>

<body class="font-sans text-gray-700 antialiased" x-data="{ billingYearly: true }">

    {{-- Navbar --}}
    <nav class="fixed top-0 left-0 right-0 z-50 border-b border-black/10 backdrop-blur-xl bg-white/80">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ locale_url('/') }}" class="flex items-center group shrink-0">
                    <img src="{{ asset('images/logo-full.png') }}" alt="Harmoniva" width="1374" height="340" class="h-[43px] sm:h-[47px] w-auto">
                </a>
                <div class="flex items-center gap-3">
                    <a href="{{ locale_url('/pricing') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-900 transition-colors">
                        ← {{ $pk('nav_all_plans') }}
                    </a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-accent-600 to-accent-500 rounded-lg hover:opacity-90 transition-all shadow-lg">
                            {{ $pk('nav_dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-accent-600 to-accent-500 rounded-lg hover:opacity-90 transition-all shadow-lg">
                            {{ $pk('nav_start') }}
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="h-16"></div>

    {{-- Hero --}}
    <section class="py-20 sm:py-24 relative overflow-hidden" style="background: linear-gradient(135deg, #fff7ed 0%, #FAF7F2 60%, #fef3c7 100%);">
        <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full bg-orange-100/60 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-[300px] h-[300px] rounded-full bg-amber-50/80 blur-2xl pointer-events-none"></div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-100 text-orange-700 text-sm font-semibold mb-6 hero-badge">
                <i data-lucide="users" class="w-4 h-4"></i>
                {{ $pk('hero_badge') }}
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
                {{ $pk('hero_title_a') }}<br>
                <span class="font-serif italic font-normal gradient-text">{{ $pk('hero_title_b') }}</span>
            </h1>

            <p class="text-gray-500 text-lg max-w-2xl mx-auto mb-8">
                {!! $pk('hero_subtitle', ['free' => '<strong class="text-gray-700">'.$pk('hero_subtitle_free').'</strong>']) !!}
            </p>

            {{-- Billing Toggle --}}
            <div class="inline-flex items-center gap-1 p-1.5 bg-white rounded-2xl shadow-sm border border-gray-100">
                <button @click="billingYearly = false"
                        :class="!billingYearly ? 'bg-gray-900 text-white shadow' : 'text-gray-500 hover:text-gray-700'"
                        class="px-6 py-2.5 rounded-xl text-sm font-semibold transition-all">
                    {{ $pk('billing_monthly') }}
                </button>
                <button @click="billingYearly = true"
                        :class="billingYearly ? 'text-white shadow' : 'text-gray-500 hover:text-gray-700'"
                        class="px-6 py-2.5 rounded-xl text-sm font-semibold transition-all relative"
                        :style="billingYearly ? 'background: linear-gradient(135deg,#ea580c,#f97316)' : ''">
                    {{ $pk('billing_yearly') }}
                    <span class="ml-2 text-xs font-bold px-2 py-0.5 rounded-full"
                          :class="billingYearly ? 'bg-white/20 text-white' : 'bg-orange-100 text-orange-700'">
                        {{ $pk('billing_save') }}
                    </span>
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-3" x-show="billingYearly" x-cloak>{{ $pk('billing_note') }}</p>
        </div>
    </section>


    {{-- Pricing Cards: Teacher + School --}}
    <section class="pb-8 -mt-8 relative z-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">

                {{-- Teacher Card --}}
                <div class="bg-white rounded-3xl border-2 border-orange-100 shadow-lg p-8 reveal flex flex-col relative overflow-hidden">
                    <div class="absolute -top-16 -right-16 w-40 h-40 rounded-full blur-3xl" style="background:rgba(249,115,22,0.10);"></div>
                    <div class="relative flex flex-col h-full">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-11 h-11 rounded-2xl bg-orange-100 flex items-center justify-center">
                                <i data-lucide="graduation-cap" class="w-6 h-6 text-orange-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-extrabold text-gray-900">{{ $pk('tc_name') }}</div>
                                <div class="text-xs text-gray-400">{{ $pk('tc_tagline') }}</div>
                            </div>
                        </div>

                        <div x-show="!billingYearly">
                            <div class="flex items-end gap-1 mb-1">
                                <span class="text-5xl font-extrabold text-gray-900">$16.90</span>
                                <span class="text-gray-400 text-base mb-2">{{ $pk('tc_per_month') }}</span>
                            </div>
                            <p class="text-gray-400 text-sm mb-6">{{ $pk('tc_billed_monthly') }}</p>
                        </div>
                        <div x-show="billingYearly" x-cloak>
                            <div class="flex items-end gap-1 mb-1">
                                <span class="text-5xl font-extrabold text-gray-900">$6.67</span>
                                <span class="text-gray-400 text-base mb-2">{{ $pk('tc_per_month') }}</span>
                            </div>
                            <p class="text-gray-400 text-sm mb-1">{{ $pk('tc_billed_annually', ['total' => '$80']) }}</p>
                            <span class="inline-block mb-6 px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700">{{ $pk('tc_save') }}</span>
                        </div>

                        <ul class="space-y-3 mb-8">
                            @php $teacherFeatures = [$pk('tc_feat_1'), $pk('tc_feat_2'), $pk('tc_feat_3'), $pk('tc_feat_4'), $pk('tc_feat_5'), $pk('tc_feat_6')]; @endphp
                            @foreach ($teacherFeatures as $f)
                            <li class="flex items-start gap-3 text-sm">
                                <div class="w-5 h-5 rounded-full bg-orange-100 flex items-center justify-center shrink-0 mt-0.5">
                                    <i data-lucide="check" class="w-3 h-3 text-orange-600"></i>
                                </div>
                                <span class="text-gray-700">{{ $f }}</span>
                            </li>
                            @endforeach
                        </ul>

                        <div class="mt-auto">
                            <div class="flex items-start gap-2 mb-4 p-3 rounded-xl bg-green-50 border border-green-100">
                                <i data-lucide="gift" class="w-4 h-4 text-green-600 shrink-0 mt-0.5"></i>
                                <span class="text-xs text-green-800 font-medium">{!! $pk('tc_free_note', ['students' => '<strong>'.$pk('tc_free_note_students').'</strong>']) !!}</span>
                            </div>
                            @if (auth()->check() && auth()->user()->role === 'teacher')
                            <a href="{{ route('checkout.show') }}" class="block w-full py-3.5 text-center text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg" style="background: linear-gradient(135deg,#ea580c,#f97316);">
                                {{ $pk('tc_cta_upgrade') }}
                            </a>
                            @else
                            <a href="{{ route('register', ['role' => 'teacher']) }}" class="block w-full py-3.5 text-center text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg" style="background: linear-gradient(135deg,#ea580c,#f97316);">
                                {{ $pk('tc_cta_start') }}
                            </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- School Card --}}
                <div class="bg-gray-900 rounded-3xl shadow-2xl p-8 reveal flex flex-col relative overflow-hidden" style="transition-delay:0.1s">
                    <div class="absolute -top-16 -right-16 w-48 h-48 rounded-full blur-3xl" style="background:rgba(249,115,22,0.25);"></div>
                    <div class="absolute -bottom-10 -left-10 w-36 h-36 rounded-full blur-2xl" style="background:rgba(147,51,234,0.18);"></div>
                    <div class="relative flex flex-col h-full">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-2xl bg-orange-500/20 flex items-center justify-center">
                                    <i data-lucide="building-2" class="w-6 h-6 text-orange-300"></i>
                                </div>
                                <div>
                                    <div class="text-lg font-extrabold text-white">{{ $pk('sc_name') }}</div>
                                    <div class="text-xs text-gray-400">{{ $pk('sc_tagline') }}</div>
                                </div>
                            </div>
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-orange-500/20 text-orange-300 border border-orange-500/30">{{ $pk('sc_badge') }}</span>
                        </div>

                        <div x-show="!billingYearly">
                            <div class="flex items-end gap-1 mb-1">
                                <span class="text-5xl font-extrabold text-white">$29.90</span>
                                <span class="text-gray-400 text-base mb-2">{{ $pk('tc_per_month') }}</span>
                            </div>
                            <p class="text-gray-400 text-sm mb-6">{{ $pk('sc_billed_monthly') }}</p>
                        </div>
                        <div x-show="billingYearly" x-cloak>
                            <div class="flex items-end gap-1 mb-1">
                                <span class="text-5xl font-extrabold text-white">$14.08</span>
                                <span class="text-gray-400 text-base mb-2">{{ $pk('tc_per_month') }}</span>
                            </div>
                            <p class="text-gray-400 text-sm mb-1">{{ $pk('sc_billed_annually', ['total' => '$169']) }}</p>
                            <span class="inline-block mb-6 px-3 py-1 rounded-full text-xs font-bold" style="background:rgba(249,115,22,0.2);color:#fb923c;border:1px solid rgba(249,115,22,0.3);">{{ $pk('sc_save') }}</span>
                        </div>

                        <ul class="space-y-3 mb-8">
                            @php $schoolFeatures = [$pk('sc_feat_0'), $pk('sc_feat_1'), $pk('sc_feat_2'), $pk('sc_feat_3'), $pk('sc_feat_4'), $pk('sc_feat_5')]; @endphp
                            @foreach ($schoolFeatures as $i => $f)
                            <li class="flex items-start gap-3 text-sm">
                                <div class="w-5 h-5 rounded-full {{ $i === 0 ? 'bg-white/10' : 'bg-orange-500/20 border border-orange-500/30' }} flex items-center justify-center shrink-0 mt-0.5">
                                    <i data-lucide="{{ $i === 0 ? 'plus' : 'check' }}" class="w-3 h-3 {{ $i === 0 ? 'text-gray-300' : 'text-orange-300' }}"></i>
                                </div>
                                <span class="{{ $i === 0 ? 'text-gray-400 font-semibold' : 'text-gray-300' }}">{{ $f }}</span>
                            </li>
                            @endforeach
                        </ul>

                        <div class="mt-auto">
                            <div class="flex items-start gap-2 mb-4 p-3 rounded-xl bg-green-500/10 border border-green-500/20">
                                <i data-lucide="gift" class="w-4 h-4 text-green-400 shrink-0 mt-0.5"></i>
                                <span class="text-xs text-green-300 font-medium">{!! $pk('sc_free_note', ['students' => '<strong>'.$pk('sc_free_note_students').'</strong>']) !!}</span>
                            </div>
                            @if (auth()->check() && auth()->user()->role === 'school')
                            <a href="{{ route('checkout.show') }}" class="block w-full py-3.5 text-center text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg" style="background: linear-gradient(135deg,#ea580c,#f97316);">
                                {{ $pk('sc_cta_upgrade') }}
                            </a>
                            @else
                            <a href="{{ route('register', ['role' => 'school']) }}" class="block w-full py-3.5 text-center text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg" style="background: linear-gradient(135deg,#ea580c,#f97316);">
                                {{ $pk('sc_cta_start') }}
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-center text-xs text-gray-400 mt-6 reveal">{{ $pk('cards_note') }}</p>
        </div>
    </section>


    {{-- Bring Premium Students, Use Free --}}
    <section class="py-20" style="background: linear-gradient(135deg, #f0fdf4 0%, #FAF7F2 55%, #ecfdf5 100%);">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-100 text-green-700 text-sm font-semibold mb-5">
                    <i data-lucide="gift" class="w-4 h-4"></i>
                    {{ $pk('bring_badge') }}
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">
                    {{ $pk('bring_title_a') }}<br><span class="font-serif italic font-normal" style="color:#16a34a;">{{ $pk('bring_title_b') }}</span>
                </h2>
                <p class="text-gray-500 max-w-2xl mx-auto">
                    {{ $pk('bring_subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 reveal">
                {{-- Teacher tier --}}
                <div class="bg-white rounded-3xl border border-green-100 shadow-sm p-8">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-orange-100 flex items-center justify-center">
                            <i data-lucide="graduation-cap" class="w-6 h-6 text-orange-600"></i>
                        </div>
                        <div>
                            <div class="font-extrabold text-gray-900">{{ $pk('bring_t_name') }}</div>
                            <div class="text-xs text-gray-400">{{ $pk('bring_t_tagline') }}</div>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-3">
                        <span class="text-5xl font-extrabold" style="color:#16a34a;">10+</span>
                        <span class="text-gray-500 font-medium">{{ $pk('bring_t_count_label') }}</span>
                    </div>
                    <p class="text-sm text-gray-500 mb-5 leading-relaxed">
                        {!! $pk('bring_t_desc', ['n' => '<strong>'.$pk('bring_t_desc_n').'</strong>']) !!}
                    </p>
                    <ul class="space-y-2.5">
                        @foreach ([$pk('bring_t_li_1'), $pk('bring_t_li_2'), $pk('bring_t_li_3')] as $li)
                        <li class="flex items-center gap-2.5 text-sm text-gray-700">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-green-500 shrink-0"></i> {{ $li }}
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- School tier --}}
                <div class="bg-white rounded-3xl border border-green-100 shadow-sm p-8">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-orange-100 flex items-center justify-center">
                            <i data-lucide="building-2" class="w-6 h-6 text-orange-600"></i>
                        </div>
                        <div>
                            <div class="font-extrabold text-gray-900">{{ $pk('bring_s_name') }}</div>
                            <div class="text-xs text-gray-400">{{ $pk('bring_s_tagline') }}</div>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-3">
                        <span class="text-5xl font-extrabold" style="color:#16a34a;">20+</span>
                        <span class="text-gray-500 font-medium">{{ $pk('bring_s_count_label') }}</span>
                    </div>
                    <p class="text-sm text-gray-500 mb-5 leading-relaxed">
                        {!! $pk('bring_s_desc', ['n' => '<strong>'.$pk('bring_s_desc_n').'</strong>']) !!}
                    </p>
                    <ul class="space-y-2.5">
                        @foreach ([$pk('bring_s_li_1'), $pk('bring_s_li_2'), $pk('bring_s_li_3')] as $li)
                        <li class="flex items-center gap-2.5 text-sm text-gray-700">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-green-500 shrink-0"></i> {{ $li }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="text-center mt-8 reveal">
                <p class="text-sm text-gray-500">
                    {{ $pk('bring_footer') }}
                </p>
            </div>
        </div>
    </section>


    {{-- What's Included --}}
    <section class="py-20 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14 reveal">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-accent-600 mb-3 block">{{ $pk('inc_eyebrow') }}</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">
                    {{ $pk('inc_title_a') }}<br><span class="font-serif italic font-normal gradient-text">{{ $pk('inc_title_b') }}</span>
                </h2>
                <p class="text-gray-500 max-w-xl mx-auto">{{ $pk('inc_subtitle') }}</p>
            </div>

            @php
            $featureGroups = [
                [
                    'icon' => 'layout-dashboard', 'color' => 'text-orange-600', 'bg' => 'bg-orange-100',
                    'title' => $pk('grp_classroom_title'),
                    'features' => [
                        ['icon' => 'users', 'title' => $pk('f_roster_title'), 'desc' => $pk('f_roster_desc')],
                        ['icon' => 'folder-plus', 'title' => $pk('f_groups_title'), 'desc' => $pk('f_groups_desc')],
                        ['icon' => 'user-check', 'title' => $pk('f_multiteacher_title'), 'desc' => $pk('f_multiteacher_desc')],
                        ['icon' => 'building-2', 'title' => $pk('f_multidept_title'), 'desc' => $pk('f_multidept_desc')],
                    ]
                ],
                [
                    'icon' => 'clipboard-list', 'color' => 'text-primary-600', 'bg' => 'bg-primary-100',
                    'title' => $pk('grp_assignment_title'),
                    'features' => [
                        ['icon' => 'send', 'title' => $pk('f_assign_title'), 'desc' => $pk('f_assign_desc')],
                        ['icon' => 'sliders-horizontal', 'title' => $pk('f_difficulty_title'), 'desc' => $pk('f_difficulty_desc')],
                        ['icon' => 'repeat', 'title' => $pk('f_templates_title'), 'desc' => $pk('f_templates_desc')],
                        ['icon' => 'calendar-check', 'title' => $pk('f_homework_title'), 'desc' => $pk('f_homework_desc')],
                    ]
                ],
                [
                    'icon' => 'bar-chart-3', 'color' => 'text-blue-600', 'bg' => 'bg-blue-100',
                    'title' => $pk('grp_analytics_title'),
                    'features' => [
                        ['icon' => 'trending-up', 'title' => $pk('f_perstudent_title'), 'desc' => $pk('f_perstudent_desc')],
                        ['icon' => 'layers', 'title' => $pk('f_classwide_title'), 'desc' => $pk('f_classwide_desc')],
                        ['icon' => 'file-text', 'title' => $pk('f_pdf_title'), 'desc' => $pk('f_pdf_desc')],
                        ['icon' => 'bell', 'title' => $pk('f_alerts_title'), 'desc' => $pk('f_alerts_desc')],
                    ]
                ],
                [
                    'icon' => 'bot', 'color' => 'text-cyan-600', 'bg' => 'bg-cyan-100',
                    'title' => $pk('grp_ai_title'),
                    'features' => [
                        ['icon' => 'sparkles', 'title' => $pk('f_ai_lp_title'), 'desc' => $pk('f_ai_lp_desc')],
                        ['icon' => 'message-circle', 'title' => $pk('f_ai_assistant_title'), 'desc' => $pk('f_ai_assistant_desc')],
                        ['icon' => 'brain', 'title' => $pk('f_adaptive_title'), 'desc' => $pk('f_adaptive_desc')],
                        ['icon' => 'lightbulb', 'title' => $pk('f_ai_rec_title'), 'desc' => $pk('f_ai_rec_desc')],
                    ]
                ],
                [
                    'icon' => 'shield-check', 'color' => 'text-green-600', 'bg' => 'bg-green-100',
                    'title' => $pk('grp_support_title'),
                    'features' => [
                        ['icon' => 'credit-card', 'title' => $pk('f_billing_title'), 'desc' => $pk('f_billing_desc')],
                        ['icon' => 'palette', 'title' => $pk('f_branding_title'), 'desc' => $pk('f_branding_desc')],
                        ['icon' => 'headphones', 'title' => $pk('f_support_title'), 'desc' => $pk('f_support_desc')],
                        ['icon' => 'lock', 'title' => $pk('f_privacy_title'), 'desc' => $pk('f_privacy_desc')],
                    ]
                ],
            ];
            @endphp

            <div class="space-y-16">
                @foreach ($featureGroups as $gi => $group)
                <div class="reveal" style="transition-delay:{{ $gi * 0.08 }}s">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 rounded-2xl {{ $group['bg'] }} flex items-center justify-center">
                            <i data-lucide="{{ $group['icon'] }}" class="w-6 h-6 {{ $group['color'] }}"></i>
                        </div>
                        <h3 class="text-xl font-extrabold text-gray-900">{{ $group['title'] }}</h3>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        @foreach ($group['features'] as $fi => $feat)
                        <div class="feature-row flex items-start gap-5 px-6 py-5">
                            <div class="w-10 h-10 rounded-xl {{ $group['bg'] }} flex items-center justify-center shrink-0 mt-0.5">
                                <i data-lucide="{{ $feat['icon'] }}" class="w-5 h-5 {{ $group['color'] }}"></i>
                            </div>
                            <div>
                                <h4 class="text-gray-900 font-bold text-sm mb-1">{{ $feat['title'] }}</h4>
                                <p class="text-gray-500 text-sm leading-relaxed">{{ $feat['desc'] }}</p>
                            </div>
                            <div class="shrink-0 mt-1">
                                <i data-lucide="check" class="w-5 h-5 text-green-500"></i>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>


    {{-- Comparison Table --}}
    <section class="py-20" style="background: #FAF7F2;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 reveal">
                <h2 class="text-3xl font-extrabold text-gray-900 mb-3">{{ $pk('comp_title') }}</h2>
                <p class="text-gray-500">{{ $pk('comp_subtitle') }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden reveal">
                <div class="overflow-x-auto"><div class="min-w-[640px]">
                {{-- Header --}}
                <div class="grid grid-cols-4 gap-0 border-b border-gray-100">
                    <div class="px-5 py-4 col-span-1"></div>
                    <div class="px-3 py-4 text-center">
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">{{ $pk('comp_col_free') }}</div>
                        <div class="text-lg font-extrabold text-gray-900">$0</div>
                    </div>
                    <div class="px-3 py-4 text-center" style="background: rgba(234,88,12,0.05);">
                        <div class="text-xs font-bold uppercase tracking-wider text-accent-600 mb-1">{{ $pk('comp_col_teacher') }}</div>
                        <div class="text-lg font-extrabold text-gray-900" x-show="!billingYearly">$16.90<span class="text-xs text-gray-400 font-normal">{{ $pk('comp_per_mo') }}</span></div>
                        <div class="text-lg font-extrabold text-gray-900" x-show="billingYearly" x-cloak>$6.67<span class="text-xs text-gray-400 font-normal">{{ $pk('comp_per_mo') }}</span></div>
                    </div>
                    <div class="px-3 py-4 text-center" style="background: rgba(147,51,234,0.05);">
                        <div class="text-xs font-bold uppercase tracking-wider text-primary-600 mb-1">{{ $pk('comp_col_school') }}</div>
                        <div class="text-lg font-extrabold text-gray-900" x-show="!billingYearly">$29.90<span class="text-xs text-gray-400 font-normal">{{ $pk('comp_per_mo') }}</span></div>
                        <div class="text-lg font-extrabold text-gray-900" x-show="billingYearly" x-cloak>$14.08<span class="text-xs text-gray-400 font-normal">{{ $pk('comp_per_mo') }}</span></div>
                    </div>
                </div>

                @php
                $u = $pk('val_unlimited');
                $compRows = [
                    ['label' => $pk('comp_row_1'),  'free' => $pk('val_5_day'),   'teacher' => $u, 'school' => $u],
                    ['label' => $pk('comp_row_2'),  'free' => false,              'teacher' => true, 'school' => true],
                    ['label' => $pk('comp_row_3'),  'free' => $pk('val_up_to_3'), 'teacher' => $u, 'school' => $u],
                    ['label' => $pk('comp_row_4'),  'free' => $pk('val_basic'),   'teacher' => $pk('val_advanced'), 'school' => $pk('val_advanced')],
                    ['label' => $pk('comp_row_5'),  'free' => false,              'teacher' => true, 'school' => true],
                    ['label' => $pk('comp_row_6'),  'free' => false,              'teacher' => true, 'school' => true],
                    ['label' => $pk('comp_row_7'),  'free' => '2',                'teacher' => $u, 'school' => $u],
                    ['label' => $pk('comp_row_8'),  'free' => false,              'teacher' => false, 'school' => true],
                    ['label' => $pk('comp_row_9'),  'free' => false,              'teacher' => false, 'school' => true],
                    ['label' => $pk('comp_row_10'), 'free' => false,              'teacher' => false, 'school' => true],
                    ['label' => $pk('comp_row_11'), 'free' => false,              'teacher' => false, 'school' => true],
                    ['label' => $pk('comp_row_12'), 'free' => false,              'teacher' => true, 'school' => true],
                    ['label' => $pk('comp_row_13'), 'free' => false,              'teacher' => $pk('val_10_students'), 'school' => $pk('val_20_students')],
                ];
                @endphp

                @foreach ($compRows as $ri => $row)
                <div class="grid grid-cols-4 gap-0 border-b border-gray-50 last:border-0 {{ $ri % 2 === 0 ? '' : 'bg-gray-50/50' }}">
                    <div class="px-5 py-3.5 text-sm text-gray-700 font-medium">{{ $row['label'] }}</div>
                    <div class="px-3 py-3.5 text-center text-sm">
                        @if ($row['free'] === false)
                            <i data-lucide="minus" class="w-4 h-4 text-gray-300 mx-auto"></i>
                        @elseif ($row['free'] === true)
                            <i data-lucide="check" class="w-4 h-4 text-green-500 mx-auto"></i>
                        @else
                            <span class="text-gray-500">{{ $row['free'] }}</span>
                        @endif
                    </div>
                    <div class="px-3 py-3.5 text-center text-sm" style="background: rgba(234,88,12,0.02);">
                        @if ($row['teacher'] === false)
                            <i data-lucide="minus" class="w-4 h-4 text-gray-300 mx-auto"></i>
                        @elseif ($row['teacher'] === true)
                            <i data-lucide="check" class="w-4 h-4 text-green-500 mx-auto"></i>
                        @else
                            <span class="text-accent-600 font-semibold text-xs">{{ $row['teacher'] }}</span>
                        @endif
                    </div>
                    <div class="px-3 py-3.5 text-center text-sm" style="background: rgba(147,51,234,0.02);">
                        @if ($row['school'] === false)
                            <i data-lucide="minus" class="w-4 h-4 text-gray-300 mx-auto"></i>
                        @elseif ($row['school'] === true)
                            <i data-lucide="check" class="w-4 h-4 text-green-500 mx-auto"></i>
                        @else
                            <span class="text-primary-700 font-semibold text-xs">{{ $row['school'] }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
                </div></div>
            </div>
        </div>
    </section>


    {{-- FAQ --}}
    <section class="py-20 bg-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 reveal">
                <h2 class="text-3xl font-extrabold text-gray-900 mb-3">{{ $pk('faq_title') }}</h2>
            </div>

            @php
            $faqs = [
                ['q' => $pk('faq_q1'), 'a' => $pk('faq_a1')],
                ['q' => $pk('faq_q2'), 'a' => $pk('faq_a2')],
                ['q' => $pk('faq_q3'), 'a' => $pk('faq_a3')],
                ['q' => $pk('faq_q4'), 'a' => $pk('faq_a4')],
                ['q' => $pk('faq_q5'), 'a' => $pk('faq_a5')],
                ['q' => $pk('faq_q6'), 'a' => $pk('faq_a6')],
                ['q' => $pk('faq_q7'), 'a' => $pk('faq_a7')],
            ];
            @endphp

            <div class="space-y-3" x-data="{ open: null }">
                @foreach ($faqs as $fi => $faq)
                <div class="bg-gray-50 rounded-2xl border border-gray-100 overflow-hidden reveal" style="transition-delay:{{ $fi * 0.05 }}s">
                    <button @click="open === {{ $fi }} ? open = null : open = {{ $fi }}"
                            class="w-full flex items-center justify-between px-6 py-5 text-left gap-4">
                        <span class="font-bold text-gray-900 text-sm">{{ $faq['q'] }}</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 shrink-0 transition-transform" :class="open === {{ $fi }} ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open === {{ $fi }}" x-collapse>
                        <div class="px-6 pb-5 text-sm text-gray-500 leading-relaxed border-t border-gray-100 pt-4">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>


    {{-- CTA --}}
    <section class="py-24 relative overflow-hidden" style="background: linear-gradient(135deg, #fff7ed 0%, #FAF7F2 50%, #fef3c7 100%);">
        <div class="absolute -top-20 -right-20 w-[400px] h-[400px] rounded-full bg-orange-100/50 blur-3xl pointer-events-none"></div>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center relative reveal">
            <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-8 shadow-xl hero-badge" style="background: linear-gradient(135deg,#ea580c,#f97316);">
                <i data-lucide="graduation-cap" class="w-8 h-8 text-white"></i>
            </div>

            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-5">
                {{ $pk('cta_title_a') }}<br><span class="font-serif italic font-normal gradient-text">{{ $pk('cta_title_b') }}</span>
            </h2>
            <p class="text-gray-500 text-lg mb-10 max-w-xl mx-auto">
                {{ $pk('cta_subtitle') }}
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background: linear-gradient(135deg,#ea580c,#f97316);">
                    <i data-lucide="rocket" class="w-5 h-5"></i>
                    {{ $pk('cta_dashboard') }}
                </a>
                @else
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background: linear-gradient(135deg,#ea580c,#f97316);">
                    <i data-lucide="rocket" class="w-5 h-5"></i>
                    {{ $pk('cta_register') }}
                </a>
                @endauth
                <a href="{{ locale_url('/pricing') }}" class="inline-flex items-center gap-2 px-6 py-4 text-base font-medium text-gray-500 hover:text-gray-800 transition-colors">
                    {{ $pk('cta_student_plans') }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-400 mt-8">
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ $pk('cta_check_1') }}</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ $pk('cta_check_2') }}</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ $pk('cta_check_3') }}</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ $pk('cta_check_4') }}</span>
            </div>
        </div>
    </section>

    @include('partials.footer')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();

            const reveals = document.querySelectorAll('.reveal');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
            }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });
            reveals.forEach(el => observer.observe(el));
        });
    </script>
</body>
</html>
