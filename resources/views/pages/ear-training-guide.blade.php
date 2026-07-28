@extends('layouts.standalone')

@section('title', __('pages.ear_guide.meta_title'))
@section('description', __('pages.ear_guide.meta_description'))

@section('structured-data')
    @php
        // Built inside @php so Blade does not compile the "@context"/"@type"
        // literal keys as directives and corrupt the JSON.
        $guideJsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => __('pages.ear_guide.jsonld_headline'),
            'description' => __('pages.ear_guide.jsonld_description'),
            'about' => __('pages.ear_guide.jsonld_about'),
            'inLanguage' => str_replace('_', '-', app()->getLocale()),
            'image' => asset('images/og-image.png'),
            'author' => ['@id' => url('/').'#organization'],
            'publisher' => ['@id' => url('/').'#organization'],
            'mainEntityOfPage' => url()->current(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $guideBreadcrumbJsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => __('pages.ear_guide.jsonld_home'), 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => __('pages.ear_guide.jsonld_about'), 'item' => url()->current()],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @endphp
    <script type="application/ld+json">{!! $guideJsonLd !!}</script>
    <script type="application/ld+json">{!! $guideBreadcrumbJsonLd !!}</script>
@endsection

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-purple-700 via-purple-600 to-purple-800 text-white py-20 px-4">
    <div class="max-w-3xl mx-auto text-center reveal">
        <div class="inline-flex items-center gap-2 bg-white/10 text-white text-sm font-medium px-4 py-2 rounded-full mb-6">
            <i data-lucide="book-open" class="w-4 h-4"></i>
            {{ __('pages.ear_guide.hero_badge') }}
        </div>
        <h1 class="text-4xl md:text-5xl font-bold mb-5">{{ __('pages.ear_guide.hero_title') }}</h1>
        <p class="text-purple-200 text-xl max-w-2xl mx-auto leading-relaxed">{{ __('pages.ear_guide.hero_subtitle') }}</p>
        <div class="flex flex-wrap justify-center gap-4 mt-8 text-sm text-purple-200">
            <span class="flex items-center gap-1.5"><i data-lucide="clock" class="w-4 h-4"></i> {{ __('pages.ear_guide.hero_stat_1') }}</span>
            <span class="flex items-center gap-1.5"><i data-lucide="list" class="w-4 h-4"></i> {{ __('pages.ear_guide.hero_stat_2') }}</span>
            <span class="flex items-center gap-1.5"><i data-lucide="users" class="w-4 h-4"></i> {{ __('pages.ear_guide.hero_stat_3') }}</span>
        </div>
    </div>
</section>

{{-- Table of Contents --}}
<section class="bg-white border-b border-gray-100 py-8 px-4">
    <div class="max-w-4xl mx-auto reveal">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">{{ __('pages.ear_guide.toc_title') }}</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @php
            $chapters = [
                ['num' => 1, 'title' => __('pages.ear_guide.toc_1')],
                ['num' => 2, 'title' => __('pages.ear_guide.toc_2')],
                ['num' => 3, 'title' => __('pages.ear_guide.toc_3')],
                ['num' => 4, 'title' => __('pages.ear_guide.toc_4')],
                ['num' => 5, 'title' => __('pages.ear_guide.toc_5')],
                ['num' => 6, 'title' => __('pages.ear_guide.toc_6')],
            ];
            @endphp
            @foreach($chapters as $ch)
            <a href="#chapter-{{ $ch['num'] }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gray-50 hover:bg-purple-50 hover:text-purple-700 transition-colors group">
                <span class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $ch['num'] }}</span>
                <span class="text-sm font-medium text-gray-700 group-hover:text-purple-700 leading-tight">{{ $ch['title'] }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- Main Content --}}
<div class="bg-[#FAF7F2] py-16 px-4">
    <div class="max-w-3xl mx-auto space-y-24">

        {{-- Chapter 1 --}}
        <article id="chapter-1" class="reveal">
            <div class="flex items-center gap-4 mb-6">
                <span class="w-10 h-10 rounded-xl bg-purple-600 text-white font-bold text-lg flex items-center justify-center flex-shrink-0">1</span>
                <h2 class="text-3xl font-bold text-gray-900">{{ __('pages.ear_guide.h1') }}</h2>
            </div>
            <div class="prose prose-gray max-w-none">
                <p class="text-gray-700 leading-relaxed mb-4">{{ __('pages.ear_guide.ch1_p1') }}</p>
                <p class="text-gray-700 leading-relaxed mb-4">{{ __('pages.ear_guide.ch1_p2') }}</p>
                <p class="text-gray-700 leading-relaxed">{{ __('pages.ear_guide.ch1_p3') }}</p>
            </div>
            <div class="mt-6 bg-purple-50 border-l-4 border-purple-500 rounded-r-xl p-5">
                <div class="flex items-start gap-3">
                    <i data-lucide="lightbulb" class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="font-semibold text-purple-900 mb-1">{{ __('pages.ear_guide.takeaway_label') }}</p>
                        <p class="text-purple-800 text-sm leading-relaxed">{{ __('pages.ear_guide.ch1_takeaway') }}</p>
                    </div>
                </div>
            </div>
        </article>

        {{-- CTA 1 --}}
        <div class="bg-white rounded-2xl p-6 flex flex-col sm:flex-row items-center gap-5 shadow-sm border border-gray-100 reveal">
            <div class="w-14 h-14 rounded-2xl bg-purple-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="play-circle" class="w-7 h-7 text-purple-600"></i>
            </div>
            <div class="text-center sm:text-left flex-1">
                <p class="font-semibold text-gray-900">{{ __('pages.ear_guide.cta1_title') }}</p>
                <p class="text-gray-500 text-sm mt-1">{{ __('pages.ear_guide.cta1_desc') }}</p>
            </div>
            <a href="{{ locale_url('/practice/melodic-interval-practice') }}" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-xl transition-colors whitespace-nowrap flex-shrink-0">
                {{ __('pages.ear_guide.cta1_button') }}
            </a>
        </div>

        {{-- Chapter 2 --}}
        <article id="chapter-2" class="reveal">
            <div class="flex items-center gap-4 mb-6">
                <span class="w-10 h-10 rounded-xl bg-purple-600 text-white font-bold text-lg flex items-center justify-center flex-shrink-0">2</span>
                <h2 class="text-3xl font-bold text-gray-900">{{ __('pages.ear_guide.h2') }}</h2>
            </div>
            <div class="prose prose-gray max-w-none">
                <p class="text-gray-700 leading-relaxed mb-4">{{ __('pages.ear_guide.ch2_p1') }}</p>
                <p class="text-gray-700 leading-relaxed mb-4">{{ __('pages.ear_guide.ch2_p2') }}</p>
                <p class="text-gray-700 leading-relaxed">{{ __('pages.ear_guide.ch2_p3') }}</p>
            </div>
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach([
                    ['rocket', __('pages.ear_guide.ch2_b1_title'), __('pages.ear_guide.ch2_b1_desc')],
                    ['mic-2', __('pages.ear_guide.ch2_b2_title'), __('pages.ear_guide.ch2_b2_desc')],
                    ['sparkles', __('pages.ear_guide.ch2_b3_title'), __('pages.ear_guide.ch2_b3_desc')],
                ] as $benefit)
                <div class="bg-white rounded-xl p-4 text-center border border-gray-100">
                    <i data-lucide="{{ $benefit[0] }}" class="w-6 h-6 text-purple-600 mx-auto mb-2"></i>
                    <p class="font-semibold text-gray-900 text-sm mb-1">{{ $benefit[1] }}</p>
                    <p class="text-gray-500 text-xs leading-relaxed">{{ $benefit[2] }}</p>
                </div>
                @endforeach
            </div>
            <div class="mt-6 bg-purple-50 border-l-4 border-purple-500 rounded-r-xl p-5">
                <div class="flex items-start gap-3">
                    <i data-lucide="lightbulb" class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="font-semibold text-purple-900 mb-1">{{ __('pages.ear_guide.takeaway_label') }}</p>
                        <p class="text-purple-800 text-sm leading-relaxed">{{ __('pages.ear_guide.ch2_takeaway') }}</p>
                    </div>
                </div>
            </div>
        </article>

        {{-- Chapter 3 --}}
        <article id="chapter-3" class="reveal">
            <div class="flex items-center gap-4 mb-6">
                <span class="w-10 h-10 rounded-xl bg-purple-600 text-white font-bold text-lg flex items-center justify-center flex-shrink-0">3</span>
                <h2 class="text-3xl font-bold text-gray-900">{{ __('pages.ear_guide.h3') }}</h2>
            </div>
            <div class="space-y-6">
                @foreach([
                    ['music', __('pages.ear_guide.skill_intervals_title'), __('pages.ear_guide.skill_intervals_desc')],
                    ['layers', __('pages.ear_guide.skill_chords_title'), __('pages.ear_guide.skill_chords_desc')],
                    ['sliders', __('pages.ear_guide.skill_scales_title'), __('pages.ear_guide.skill_scales_desc')],
                    ['activity', __('pages.ear_guide.skill_rhythm_title'), __('pages.ear_guide.skill_rhythm_desc')],
                ] as $skill)
                <div class="bg-white rounded-2xl p-6 border border-gray-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center">
                            <i data-lucide="{{ $skill[0] }}" class="w-5 h-5 text-purple-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">{{ $skill[1] }}</h3>
                    </div>
                    <p class="text-gray-600 leading-relaxed text-sm">{{ $skill[2] }}</p>
                </div>
                @endforeach
            </div>
            <div class="mt-6 bg-purple-50 border-l-4 border-purple-500 rounded-r-xl p-5">
                <div class="flex items-start gap-3">
                    <i data-lucide="lightbulb" class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="font-semibold text-purple-900 mb-1">{{ __('pages.ear_guide.takeaway_label') }}</p>
                        <p class="text-purple-800 text-sm leading-relaxed">{{ __('pages.ear_guide.ch3_takeaway') }}</p>
                    </div>
                </div>
            </div>
        </article>

        {{-- CTA 2 --}}
        <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-2xl p-6 flex flex-col sm:flex-row items-center gap-5 border border-orange-200 reveal">
            <div class="w-14 h-14 rounded-2xl bg-orange-500 flex items-center justify-center flex-shrink-0">
                <i data-lucide="target" class="w-7 h-7 text-white"></i>
            </div>
            <div class="text-center sm:text-left flex-1">
                <p class="font-semibold text-gray-900">{{ __('pages.ear_guide.cta2_title') }}</p>
                <p class="text-gray-600 text-sm mt-1">{{ __('pages.ear_guide.cta2_desc') }}</p>
            </div>
            <a href="{{ route('register') }}" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-3 rounded-xl transition-colors whitespace-nowrap flex-shrink-0">
                {{ __('pages.ear_guide.cta2_button') }}
            </a>
        </div>

        {{-- Chapter 4 --}}
        <article id="chapter-4" class="reveal">
            <div class="flex items-center gap-4 mb-6">
                <span class="w-10 h-10 rounded-xl bg-purple-600 text-white font-bold text-lg flex items-center justify-center flex-shrink-0">4</span>
                <h2 class="text-3xl font-bold text-gray-900">{{ __('pages.ear_guide.h4') }}</h2>
            </div>
            <div class="prose prose-gray max-w-none">
                <p class="text-gray-700 leading-relaxed mb-4">{{ __('pages.ear_guide.ch4_p1') }}</p>
                <p class="text-gray-700 leading-relaxed mb-4">{{ __('pages.ear_guide.ch4_p2') }}</p>
                <p class="text-gray-700 leading-relaxed">{{ __('pages.ear_guide.ch4_p3') }}</p>
            </div>
            <div class="mt-6 bg-white rounded-2xl p-6 border border-gray-100">
                <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i data-lucide="calendar" class="w-5 h-5 text-purple-600"></i> {{ __('pages.ear_guide.routine_title') }}</h4>
                <ol class="space-y-2 text-sm text-gray-700">
                    @foreach([
                        [__('pages.ear_guide.routine_1_time'), __('pages.ear_guide.routine_1_text')],
                        [__('pages.ear_guide.routine_2_time'), __('pages.ear_guide.routine_2_text')],
                        [__('pages.ear_guide.routine_3_time'), __('pages.ear_guide.routine_3_text')],
                        [__('pages.ear_guide.routine_4_time'), __('pages.ear_guide.routine_4_text')],
                    ] as $step)
                    <li class="flex items-start gap-3">
                        <span class="bg-purple-100 text-purple-700 text-xs font-bold px-2 py-0.5 rounded-md flex-shrink-0 mt-0.5">{{ $step[0] }}</span>
                        <span>{{ $step[1] }}</span>
                    </li>
                    @endforeach
                </ol>
            </div>
            <div class="mt-6 bg-purple-50 border-l-4 border-purple-500 rounded-r-xl p-5">
                <div class="flex items-start gap-3">
                    <i data-lucide="lightbulb" class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="font-semibold text-purple-900 mb-1">{{ __('pages.ear_guide.takeaway_label') }}</p>
                        <p class="text-purple-800 text-sm leading-relaxed">{{ __('pages.ear_guide.ch4_takeaway') }}</p>
                    </div>
                </div>
            </div>
        </article>

        {{-- Chapter 5 --}}
        <article id="chapter-5" class="reveal">
            <div class="flex items-center gap-4 mb-6">
                <span class="w-10 h-10 rounded-xl bg-purple-600 text-white font-bold text-lg flex items-center justify-center flex-shrink-0">5</span>
                <h2 class="text-3xl font-bold text-gray-900">{{ __('pages.ear_guide.h5') }}</h2>
            </div>
            <div class="prose prose-gray max-w-none">
                <p class="text-gray-700 leading-relaxed mb-4">{{ __('pages.ear_guide.ch5_p1') }}</p>
                <p class="text-gray-700 leading-relaxed mb-4">{{ __('pages.ear_guide.ch5_p2') }}</p>
                <p class="text-gray-700 leading-relaxed">{{ __('pages.ear_guide.ch5_p3') }}</p>
            </div>
            <div class="mt-6 bg-purple-50 border-l-4 border-purple-500 rounded-r-xl p-5">
                <div class="flex items-start gap-3">
                    <i data-lucide="lightbulb" class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="font-semibold text-purple-900 mb-1">{{ __('pages.ear_guide.takeaway_label') }}</p>
                        <p class="text-purple-800 text-sm leading-relaxed">{{ __('pages.ear_guide.ch5_takeaway') }}</p>
                    </div>
                </div>
            </div>
        </article>

        {{-- Chapter 6 --}}
        <article id="chapter-6" class="reveal">
            <div class="flex items-center gap-4 mb-6">
                <span class="w-10 h-10 rounded-xl bg-purple-600 text-white font-bold text-lg flex items-center justify-center flex-shrink-0">6</span>
                <h2 class="text-3xl font-bold text-gray-900">{{ __('pages.ear_guide.h6') }}</h2>
            </div>
            <div class="prose prose-gray max-w-none">
                <p class="text-gray-700 leading-relaxed mb-4">{{ __('pages.ear_guide.ch6_p1') }}</p>
                <p class="text-gray-700 leading-relaxed mb-4">{{ __('pages.ear_guide.ch6_p2') }}</p>
                <p class="text-gray-700 leading-relaxed">{{ __('pages.ear_guide.ch6_p3') }}</p>
            </div>
            <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach([
                    [__('pages.ear_guide.level_beg_title'), __('pages.ear_guide.level_beg_period'), 'bg-green-50 border-green-200', 'text-green-700', [__('pages.ear_guide.level_beg_1'), __('pages.ear_guide.level_beg_2'), __('pages.ear_guide.level_beg_3'), __('pages.ear_guide.level_beg_4')]],
                    [__('pages.ear_guide.level_int_title'), __('pages.ear_guide.level_int_period'), 'bg-blue-50 border-blue-200', 'text-blue-700', [__('pages.ear_guide.level_int_1'), __('pages.ear_guide.level_int_2'), __('pages.ear_guide.level_int_3'), __('pages.ear_guide.level_int_4')]],
                    [__('pages.ear_guide.level_adv_title'), __('pages.ear_guide.level_adv_period'), 'bg-purple-50 border-purple-200', 'text-purple-700', [__('pages.ear_guide.level_adv_1'), __('pages.ear_guide.level_adv_2'), __('pages.ear_guide.level_adv_3'), __('pages.ear_guide.level_adv_4')]],
                ] as $level)
                <div class="rounded-2xl p-5 border {{ $level[2] }}">
                    <div class="mb-3">
                        <span class="font-bold {{ $level[3] }} text-base">{{ $level[0] }}</span>
                        <span class="text-gray-400 text-xs ml-2">{{ $level[1] }}</span>
                    </div>
                    <ul class="space-y-1.5">
                        @foreach($level[4] as $item)
                        <li class="flex items-center gap-2 text-sm text-gray-700">
                            <i data-lucide="check" class="w-3.5 h-3.5 {{ $level[3] }} flex-shrink-0"></i>
                            {{ $item }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
            <div class="mt-6 bg-purple-50 border-l-4 border-purple-500 rounded-r-xl p-5">
                <div class="flex items-start gap-3">
                    <i data-lucide="lightbulb" class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="font-semibold text-purple-900 mb-1">{{ __('pages.ear_guide.takeaway_label') }}</p>
                        <p class="text-purple-800 text-sm leading-relaxed">{{ __('pages.ear_guide.ch6_takeaway') }}</p>
                    </div>
                </div>
            </div>
        </article>

    </div>
</div>

{{-- Final CTA --}}
<section class="bg-gradient-to-br from-purple-600 to-purple-800 py-20 px-4">
    <div class="max-w-2xl mx-auto text-center reveal">
        <h2 class="text-3xl font-bold text-white mb-4">{{ __('pages.ear_guide.final_title') }}</h2>
        <p class="text-purple-200 text-lg mb-8 max-w-lg mx-auto">{{ __('pages.ear_guide.final_subtitle') }}</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold px-8 py-4 rounded-xl transition-colors shadow-lg text-lg">
                <i data-lucide="play-circle" class="w-5 h-5"></i>
                {{ __('pages.ear_guide.final_button_1') }}
            </a>
            <a href="{{ locale_url('/learn') }}" class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white font-semibold px-8 py-4 rounded-xl transition-colors">
                <i data-lucide="map" class="w-5 h-5"></i>
                {{ __('pages.ear_guide.final_button_2') }}
            </a>
        </div>
    </div>
</section>

@endsection
