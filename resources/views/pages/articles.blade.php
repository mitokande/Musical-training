@extends('layouts.standalone')

@section('title', __('pages.articles.meta_title'))
@section('description', __('pages.articles.meta_description'))

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-purple-700 via-purple-600 to-purple-800 text-white py-20 px-4">
    <div class="max-w-3xl mx-auto text-center reveal">
        <div class="inline-flex items-center gap-2 bg-white/10 text-white text-sm font-medium px-4 py-2 rounded-full mb-6">
            <i data-lucide="book-open" class="w-4 h-4"></i>
            {{ __('pages.articles.hero_badge') }}
        </div>
        <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ __('pages.articles.hero_title') }}</h1>
        <p class="text-purple-200 text-lg max-w-xl mx-auto">{{ __('pages.articles.hero_subtitle') }}</p>
    </div>
</section>

{{-- Category Filter Tabs --}}
<section class="bg-white border-b border-gray-100 sticky top-0 z-10 px-4 shadow-sm">
    <div class="max-w-6xl mx-auto flex items-center gap-2 overflow-x-auto py-4 scrollbar-none" x-data="{ active: '{{ __('pages.articles.cat_all') }}' }">
        @php
        $cats = [
            __('pages.articles.cat_all'),
            __('pages.articles.cat_ear'),
            __('pages.articles.cat_theory'),
            __('pages.articles.cat_tips'),
            __('pages.articles.cat_ai'),
        ];
        @endphp
        @foreach($cats as $cat)
        <button
            @click="active = @js($cat)"
            :class="active === @js($cat) ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
            class="flex-shrink-0 px-5 py-2 rounded-full text-sm font-medium transition-colors"
        >{{ $cat }}</button>
        @endforeach
    </div>
</section>

{{-- Articles Grid --}}
<section class="bg-[#FAF7F2] py-20 px-4">
    <div class="max-w-6xl mx-auto">

        @php
        // Teasers still waiting for their article. Keyed by slot so a published
        // post can claim one through its `featured_slot` in config('blog.posts').
        $articles = [
            'a1' => ['category' => __('pages.articles.cat_ear'), 'cat_color' => 'purple', 'title' => __('pages.articles.a1_title'), 'excerpt' => __('pages.articles.a1_excerpt'), 'read_time' => 5, 'icon' => 'headphones'],
            'a2' => ['category' => __('pages.articles.cat_theory'), 'cat_color' => 'blue', 'title' => __('pages.articles.a2_title'), 'excerpt' => __('pages.articles.a2_excerpt'), 'read_time' => 7, 'icon' => 'music-2'],
            'a3' => ['category' => __('pages.articles.cat_tips'), 'cat_color' => 'green', 'title' => __('pages.articles.a3_title'), 'excerpt' => __('pages.articles.a3_excerpt'), 'read_time' => 6, 'icon' => 'alert-triangle'],
            'a4' => ['category' => __('pages.articles.cat_ai'), 'cat_color' => 'orange', 'title' => __('pages.articles.a4_title'), 'excerpt' => __('pages.articles.a4_excerpt'), 'read_time' => 8, 'icon' => 'brain'],
            'a5' => ['category' => __('pages.articles.cat_theory'), 'cat_color' => 'blue', 'title' => __('pages.articles.a5_title'), 'excerpt' => __('pages.articles.a5_excerpt'), 'read_time' => 5, 'icon' => 'sliders'],
            'a6' => ['category' => __('pages.articles.cat_ear'), 'cat_color' => 'purple', 'title' => __('pages.articles.a6_title'), 'excerpt' => __('pages.articles.a6_excerpt'), 'read_time' => 9, 'icon' => 'pen-tool'],
            'a7' => ['category' => __('pages.articles.cat_tips'), 'cat_color' => 'green', 'title' => __('pages.articles.a7_title'), 'excerpt' => __('pages.articles.a7_excerpt'), 'read_time' => 6, 'icon' => 'calendar-check'],
            'a8' => ['category' => __('pages.articles.cat_ear'), 'cat_color' => 'purple', 'title' => __('pages.articles.a8_title'), 'excerpt' => __('pages.articles.a8_excerpt'), 'read_time' => 7, 'icon' => 'layers'],
            'a9' => ['category' => __('pages.articles.cat_ai'), 'cat_color' => 'orange', 'title' => __('pages.articles.a9_title'), 'excerpt' => __('pages.articles.a9_excerpt'), 'read_time' => 6, 'icon' => 'refresh-cw'],
        ];

        $catColorByKey = ['ear' => 'purple', 'theory' => 'blue', 'tips' => 'green', 'ai' => 'orange'];

        // Published posts take over their declared slot. Title and excerpt come
        // from the post's own blog.* section, so a translated post shows its
        // translated card and an untranslated one falls back to English —
        // exactly what the reader gets when they follow the link.
        foreach ((array) config('blog.posts') as $slug => $blogPost) {
            $slot = $blogPost['featured_slot'] ?? null;
            if ($slot === null || ! isset($articles[$slot])) {
                continue;
            }

            $section = 'blog.'.$blogPost['section'].'.';
            $articles[$slot] = [
                'category' => __('pages.articles.cat_'.$blogPost['category']),
                'cat_color' => $catColorByKey[$blogPost['category']] ?? 'purple',
                'title' => __($section.'title'),
                'excerpt' => __($section.'meta_description'),
                'read_time' => $blogPost['reading_time'],
                'icon' => $blogPost['icon'],
                'url' => locale_url('/blog/'.$slug),
            ];
        }

        $catBgMap = [
            'purple' => 'bg-purple-100 text-purple-700',
            'blue'   => 'bg-blue-100 text-blue-700',
            'green'  => 'bg-green-100 text-green-700',
            'orange' => 'bg-orange-100 text-orange-700',
        ];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-7">
            @foreach($articles as $article)
            {{-- A published card is a single <a>, so the whole box is clickable;
                 a teaser stays an <article> with no link to nowhere. --}}
            @php $cardTag = isset($article['url']) ? 'a' : 'article'; @endphp
            <{{ $cardTag }}
                @isset($article['url']) href="{{ $article['url'] }}" @endisset
                class="block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-shadow group reveal {{ isset($article['url']) ? 'hover:shadow-lg hover:border-purple-200 cursor-pointer no-underline' : 'hover:shadow-md' }}">
                <div class="p-6 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $catBgMap[$article['cat_color']] }}">
                            {{ $article['category'] }}
                        </span>
                        <span class="text-xs text-gray-400 flex items-center gap-1">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            {{ __('pages.articles.read_time', ['min' => $article['read_time']]) }}
                        </span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-{{ $article['cat_color'] }}-50 flex items-center justify-center mb-4">
                        <i data-lucide="{{ $article['icon'] }}" class="w-5 h-5 text-{{ $article['cat_color'] }}-600"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 mb-3 group-hover:text-purple-600 transition-colors leading-snug">
                        {{ $article['title'] }}
                    </h2>
                    <p class="text-sm text-gray-500 leading-relaxed mb-5 flex-1">
                        {{ $article['excerpt'] }}
                    </p>
                    @isset($article['url'])
                    <span class="inline-flex items-center gap-1.5 text-purple-600 font-semibold text-sm">
                        {{ __('pages.articles.read_more') }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 text-gray-400 font-semibold text-sm">
                        {{ __('pages.articles.coming_soon') }}
                    </span>
                    @endisset
                </div>
            </{{ $cardTag }}>
            @endforeach
        </div>

    </div>
</section>

{{-- Newsletter Signup --}}
<section class="bg-gradient-to-br from-purple-600 to-purple-800 py-20 px-4">
    <div class="max-w-2xl mx-auto text-center reveal">
        <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center mx-auto mb-6">
            <i data-lucide="mail" class="w-7 h-7 text-white"></i>
        </div>
        <h2 class="text-3xl font-bold text-white mb-3">{{ __('pages.articles.newsletter_title') }}</h2>
        <p class="text-purple-200 mb-8 text-lg">{{ __('pages.articles.newsletter_subtitle') }}</p>
        <form action="#" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
            @csrf
            <input
                type="email"
                placeholder="{{ __('pages.articles.newsletter_ph') }}"
                class="flex-1 rounded-xl px-5 py-3.5 text-gray-800 focus:outline-none focus:ring-4 focus:ring-orange-400 shadow-lg"
                required
            />
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-3.5 rounded-xl transition-colors shadow-lg whitespace-nowrap">
                {{ __('pages.articles.newsletter_button') }}
            </button>
        </form>
        <p class="text-purple-300 text-sm mt-4">{{ __('pages.articles.newsletter_note') }}</p>
    </div>
</section>

@endsection
