@extends('layouts.standalone')

@section('title', __('pages.help.meta_title'))
@section('description', __('pages.help.meta_description'))

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-purple-700 via-purple-600 to-purple-800 text-white py-20 px-4">
    <div class="max-w-3xl mx-auto text-center reveal">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ __('pages.help.hero_title') }}</h1>
        <p class="text-purple-200 text-lg mb-8">{{ __('pages.help.hero_subtitle') }}</p>
        <div class="relative max-w-xl mx-auto">
            <input
                type="text"
                placeholder="{{ __('pages.help.search_ph') }}"
                class="w-full rounded-2xl py-4 pl-6 pr-14 text-gray-800 text-lg shadow-lg focus:outline-none focus:ring-4 focus:ring-orange-400"
            />
            <button class="absolute right-3 top-1/2 -translate-y-1/2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl p-2.5 transition-colors">
                <i data-lucide="search" class="w-5 h-5"></i>
            </button>
        </div>
    </div>
</section>

{{-- Category Cards --}}
<section class="bg-[#FAF7F2] py-20 px-4">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-12 reveal">{{ __('pages.help.browse_title') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            @php $categories = [
                ['icon' => 'rocket', 'bg' => 'bg-purple-100', 'fg' => 'text-purple-600', 'title' => __('pages.help.cat_start_title'), 'links' => [__('pages.help.cat_start_1'), __('pages.help.cat_start_2'), __('pages.help.cat_start_3'), __('pages.help.cat_start_4')]],
                ['icon' => 'credit-card', 'bg' => 'bg-orange-100', 'fg' => 'text-orange-500', 'title' => __('pages.help.cat_billing_title'), 'links' => [__('pages.help.cat_billing_1'), __('pages.help.cat_billing_2'), __('pages.help.cat_billing_3'), __('pages.help.cat_billing_4')]],
                ['icon' => 'music', 'bg' => 'bg-green-100', 'fg' => 'text-green-600', 'title' => __('pages.help.cat_exercises_title'), 'links' => [__('pages.help.cat_exercises_1'), __('pages.help.cat_exercises_2'), __('pages.help.cat_exercises_3'), __('pages.help.cat_exercises_4')]],
                ['icon' => 'map', 'bg' => 'bg-blue-100', 'fg' => 'text-blue-600', 'title' => __('pages.help.cat_lp_title'), 'links' => [__('pages.help.cat_lp_1'), __('pages.help.cat_lp_2'), __('pages.help.cat_lp_3'), __('pages.help.cat_lp_4')]],
                ['icon' => 'graduation-cap', 'bg' => 'bg-yellow-100', 'fg' => 'text-yellow-600', 'title' => __('pages.help.cat_teachers_title'), 'links' => [__('pages.help.cat_teachers_1'), __('pages.help.cat_teachers_2'), __('pages.help.cat_teachers_3'), __('pages.help.cat_teachers_4')]],
                ['icon' => 'wrench', 'bg' => 'bg-red-100', 'fg' => 'text-red-500', 'title' => __('pages.help.cat_tech_title'), 'links' => [__('pages.help.cat_tech_1'), __('pages.help.cat_tech_2'), __('pages.help.cat_tech_3'), __('pages.help.cat_tech_4')]],
            ]; @endphp

            @foreach ($categories as $cat)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow reveal">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl {{ $cat['bg'] }} flex items-center justify-center">
                        <i data-lucide="{{ $cat['icon'] }}" class="w-5 h-5 {{ $cat['fg'] }}"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $cat['title'] }}</h3>
                </div>
                <ul class="space-y-2 text-sm text-gray-600">
                    @foreach ($cat['links'] as $link)
                    <li><a href="#" class="hover:text-purple-600 transition-colors flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-purple-400"></i> {{ $link }}</a></li>
                    @endforeach
                </ul>
            </div>
            @endforeach

        </div>
    </div>
</section>

{{-- Popular Articles --}}
<section class="bg-white py-20 px-4">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-3 reveal">{{ __('pages.help.popular_title') }}</h2>
        <p class="text-gray-500 text-center mb-12 reveal">{{ __('pages.help.popular_subtitle') }}</p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

            @php
            $articles = [
                ['icon' => 'headphones', 'color' => 'purple', 'title' => __('pages.help.art_1_title'), 'desc' => __('pages.help.art_1_desc')],
                ['icon' => 'settings-2', 'color' => 'orange', 'title' => __('pages.help.art_2_title'), 'desc' => __('pages.help.art_2_desc')],
                ['icon' => 'music-2', 'color' => 'green', 'title' => __('pages.help.art_3_title'), 'desc' => __('pages.help.art_3_desc')],
                ['icon' => 'brain', 'color' => 'blue', 'title' => __('pages.help.art_4_title'), 'desc' => __('pages.help.art_4_desc')],
                ['icon' => 'bar-chart-2', 'color' => 'yellow', 'title' => __('pages.help.art_5_title'), 'desc' => __('pages.help.art_5_desc')],
                ['icon' => 'users', 'color' => 'red', 'title' => __('pages.help.art_6_title'), 'desc' => __('pages.help.art_6_desc')],
            ];
            @endphp

            @foreach($articles as $article)
            <a href="#" class="group flex items-start gap-4 bg-[#FAF7F2] rounded-2xl p-5 hover:shadow-md transition-shadow reveal">
                <div class="w-10 h-10 rounded-xl bg-{{ $article['color'] }}-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i data-lucide="{{ $article['icon'] }}" class="w-5 h-5 text-{{ $article['color'] }}-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 group-hover:text-purple-600 transition-colors mb-1">{{ $article['title'] }}</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ $article['desc'] }}</p>
                </div>
            </a>
            @endforeach

        </div>
    </div>
</section>

{{-- Bottom CTA --}}
<section class="bg-gradient-to-br from-purple-50 to-orange-50 py-20 px-4">
    <div class="max-w-2xl mx-auto text-center reveal">
        <div class="w-16 h-16 rounded-2xl bg-purple-100 flex items-center justify-center mx-auto mb-6">
            <i data-lucide="life-buoy" class="w-8 h-8 text-purple-600"></i>
        </div>
        <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ __('pages.help.cta_title') }}</h2>
        <p class="text-gray-600 mb-8 text-lg">{{ __('pages.help.cta_subtitle') }}</p>
        <a href="{{ locale_url('/contact') }}" class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-8 py-4 rounded-xl transition-colors text-lg shadow-lg hover:shadow-xl">
            <i data-lucide="mail" class="w-5 h-5"></i>
            {{ __('pages.help.cta_contact') }}
        </a>
    </div>
</section>

@endsection
