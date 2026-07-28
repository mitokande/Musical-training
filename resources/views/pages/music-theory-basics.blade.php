@extends('layouts.standalone')

@section('title', __('pages.music_theory.meta_title'))
@section('description', __('pages.music_theory.meta_description'))

@section('structured-data')
    @php
        // Built inside @php so Blade does not compile the "@context"/"@type"
        // literal keys as directives and corrupt the JSON.
        $theoryJsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => __('pages.music_theory.jsonld_headline'),
            'description' => __('pages.music_theory.jsonld_description'),
            'about' => __('pages.music_theory.jsonld_about'),
            'inLanguage' => str_replace('_', '-', app()->getLocale()),
            'image' => asset('images/og-image.png'),
            'author' => ['@id' => url('/').'#organization'],
            'publisher' => ['@id' => url('/').'#organization'],
            'mainEntityOfPage' => url()->current(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $theoryBreadcrumbJsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => __('pages.music_theory.jsonld_home'), 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => __('pages.music_theory.jsonld_headline'), 'item' => url()->current()],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @endphp
    <script type="application/ld+json">{!! $theoryJsonLd !!}</script>
    <script type="application/ld+json">{!! $theoryBreadcrumbJsonLd !!}</script>
@endsection

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-purple-700 via-purple-600 to-purple-800 text-white py-20 px-4">
    <div class="max-w-3xl mx-auto text-center reveal">
        <div class="inline-flex items-center gap-2 bg-white/10 text-white text-sm font-medium px-4 py-2 rounded-full mb-6">
            <i data-lucide="book" class="w-4 h-4"></i>
            {{ __('pages.music_theory.hero_badge') }}
        </div>
        <h1 class="text-4xl md:text-5xl font-bold mb-5">{{ __('pages.music_theory.hero_title') }}</h1>
        <p class="text-purple-200 text-xl max-w-2xl mx-auto leading-relaxed">{{ __('pages.music_theory.hero_subtitle') }}</p>
    </div>
</section>

{{-- Topic Cards --}}
<section class="bg-[#FAF7F2] py-20 px-4">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-3 reveal">{{ __('pages.music_theory.topics_title') }}</h2>
        <p class="text-gray-500 text-center mb-12 reveal">{{ __('pages.music_theory.topics_subtitle') }}</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            @php
            $topics = [
                ['icon' => 'music', 'color' => 'purple', 'title' => __('pages.music_theory.topic_notes_title'), 'desc' => __('pages.music_theory.topic_notes_desc')],
                ['icon' => 'arrow-up-down', 'color' => 'blue', 'title' => __('pages.music_theory.topic_intervals_title'), 'desc' => __('pages.music_theory.topic_intervals_desc')],
                ['icon' => 'sliders', 'color' => 'green', 'title' => __('pages.music_theory.topic_scales_title'), 'desc' => __('pages.music_theory.topic_scales_desc')],
                ['icon' => 'layers', 'color' => 'orange', 'title' => __('pages.music_theory.topic_chords_title'), 'desc' => __('pages.music_theory.topic_chords_desc')],
                ['icon' => 'activity', 'color' => 'red', 'title' => __('pages.music_theory.topic_rhythm_title'), 'desc' => __('pages.music_theory.topic_rhythm_desc')],
                ['icon' => 'key', 'color' => 'yellow', 'title' => __('pages.music_theory.topic_keys_title'), 'desc' => __('pages.music_theory.topic_keys_desc')],
            ];
            $colorMap = [
                'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'badge' => 'bg-purple-600'],
                'blue'   => ['bg' => 'bg-blue-100',   'text' => 'text-blue-600',   'badge' => 'bg-blue-600'],
                'green'  => ['bg' => 'bg-green-100',  'text' => 'text-green-600',  'badge' => 'bg-green-600'],
                'orange' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-500', 'badge' => 'bg-orange-500'],
                'red'    => ['bg' => 'bg-red-100',    'text' => 'text-red-500',    'badge' => 'bg-red-500'],
                'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600', 'badge' => 'bg-yellow-600'],
            ];
            @endphp

            @foreach($topics as $topic)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition-shadow group reveal">
                <div class="w-12 h-12 rounded-xl {{ $colorMap[$topic['color']]['bg'] }} flex items-center justify-center mb-4">
                    <i data-lucide="{{ $topic['icon'] }}" class="w-6 h-6 {{ $colorMap[$topic['color']]['text'] }}"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $topic['title'] }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed mb-5">{{ $topic['desc'] }}</p>
                <a href="#" class="inline-flex items-center gap-1.5 text-sm font-semibold {{ $colorMap[$topic['color']]['text'] }} hover:gap-2.5 transition-all">
                    {{ __('pages.music_theory.explore') }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
            @endforeach

        </div>
    </div>
</section>

{{-- Featured: Intervals --}}
<section class="bg-white py-20 px-4">
    <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="reveal">
                <div class="inline-flex items-center gap-2 bg-purple-100 text-purple-700 text-sm font-semibold px-4 py-2 rounded-full mb-6">
                    <i data-lucide="star" class="w-4 h-4"></i>
                    {{ __('pages.music_theory.featured_badge') }}
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-5">{{ __('pages.music_theory.featured_title') }}</h2>
                <p class="text-gray-600 leading-relaxed mb-4">{{ __('pages.music_theory.featured_p1') }}</p>
                <p class="text-gray-600 leading-relaxed mb-4">{{ __('pages.music_theory.featured_p2') }}</p>
                <p class="text-gray-600 leading-relaxed">{{ __('pages.music_theory.featured_p3') }}</p>
            </div>
            <div class="reveal">
                <div class="bg-[#FAF7F2] rounded-2xl p-6 border border-gray-100">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="table" class="w-5 h-5 text-purple-600"></i>
                        {{ __('pages.music_theory.table_title') }}
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 pr-4 text-gray-500 font-semibold">{{ __('pages.music_theory.table_col_semitones') }}</th>
                                    <th class="text-left py-2 pr-4 text-gray-500 font-semibold">{{ __('pages.music_theory.table_col_name') }}</th>
                                    <th class="text-left py-2 text-gray-500 font-semibold">{{ __('pages.music_theory.table_col_abbr') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach([
                                    [1,  __('pages.music_theory.int_m2'),     'm2'],
                                    [2,  __('pages.music_theory.int_M2'),     'M2'],
                                    [3,  __('pages.music_theory.int_m3'),     'm3'],
                                    [4,  __('pages.music_theory.int_M3'),     'M3'],
                                    [5,  __('pages.music_theory.int_P4'),     'P4'],
                                    [6,  __('pages.music_theory.int_TT'),     'TT'],
                                    [7,  __('pages.music_theory.int_P5'),     'P5'],
                                    [8,  __('pages.music_theory.int_m6'),     'm6'],
                                    [9,  __('pages.music_theory.int_M6'),     'M6'],
                                    [10, __('pages.music_theory.int_m7'),     'm7'],
                                    [11, __('pages.music_theory.int_M7'),     'M7'],
                                    [12, __('pages.music_theory.int_octave'), '8ve'],
                                ] as $row)
                                <tr>
                                    <td class="py-2 pr-4">
                                        <span class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 text-xs font-bold inline-flex items-center justify-center">{{ $row[0] }}</span>
                                    </td>
                                    <td class="py-2 pr-4 text-gray-800 font-medium">{{ $row[1] }}</td>
                                    <td class="py-2 text-gray-500 font-mono">{{ $row[2] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Quick Reference: Note Names --}}
<section class="bg-[#FAF7F2] py-16 px-4">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center reveal">{{ __('pages.music_theory.quickref_title') }}</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-8 reveal">
            @foreach(['C', 'D', 'E', 'F', 'G', 'A', 'B'] as $note)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
                <span class="text-3xl font-bold text-purple-600">{{ $note }}</span>
                <div class="mt-2 space-y-1">
                    <div class="text-xs text-gray-400">{{ $note }}♯ / {{ chr(ord($note)+1 > ord('G') ? ord('A') : ord($note)+1) }}♭</div>
                </div>
            </div>
            @endforeach
        </div>
        <p class="text-gray-500 text-sm text-center reveal">{{ __('pages.music_theory.quickref_note') }}</p>
    </div>
</section>

{{-- Theory + Ear Training Connection --}}
<section class="bg-white py-20 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12 reveal">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ __('pages.music_theory.connection_title') }}</h2>
            <p class="text-gray-500 text-lg max-w-2xl mx-auto">{{ __('pages.music_theory.connection_subtitle') }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 reveal">
            @foreach([
                ['book', 'purple', __('pages.music_theory.step_learn_title'), __('pages.music_theory.step_learn_desc')],
                ['headphones', 'orange', __('pages.music_theory.step_train_title'), __('pages.music_theory.step_train_desc')],
                ['music', 'green', __('pages.music_theory.step_apply_title'), __('pages.music_theory.step_apply_desc')],
            ] as $step)
            <div class="text-center">
                <div class="w-14 h-14 rounded-2xl bg-{{ $step[1] }}-100 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="{{ $step[0] }}" class="w-7 h-7 text-{{ $step[1] }}-600"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">{{ $step[2] }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed">{{ $step[3] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="bg-gradient-to-br from-purple-600 to-purple-800 py-20 px-4">
    <div class="max-w-2xl mx-auto text-center reveal">
        <h2 class="text-3xl font-bold text-white mb-4">{{ __('pages.music_theory.cta_title') }}</h2>
        <p class="text-purple-200 text-lg mb-8">{{ __('pages.music_theory.cta_subtitle') }}</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold px-8 py-4 rounded-xl transition-colors shadow-lg text-lg">
                <i data-lucide="play-circle" class="w-5 h-5"></i>
                {{ __('pages.music_theory.cta_practice') }}
            </a>
            <a href="{{ locale_url('/ear-training-guide') }}" class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white font-semibold px-8 py-4 rounded-xl transition-colors">
                <i data-lucide="book-open" class="w-5 h-5"></i>
                {{ __('pages.music_theory.cta_guide') }}
            </a>
        </div>
    </div>
</section>

@endsection
