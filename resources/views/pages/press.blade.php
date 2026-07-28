@extends('layouts.standalone')

@section('title', __('pages.press.meta_title'))
@section('description', __('pages.press.meta_description'))

@section('content')

{{-- Hero --}}
<section class="py-24 bg-gray-900 text-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-10" style="background: radial-gradient(ellipse at top right, #9333ea, transparent 60%), radial-gradient(ellipse at bottom left, #f97316, transparent 60%);"></div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 text-white text-sm font-semibold mb-6">
            <i data-lucide="newspaper" class="w-4 h-4"></i>
            {{ __('pages.press.hero_badge') }}
        </div>
        <h1 class="text-4xl sm:text-5xl font-extrabold mb-5">{{ __('pages.press.hero_title') }}</h1>
        <p class="text-gray-300 text-xl max-w-2xl mx-auto">
            {!! __('pages.press.hero_subtitle', ['email' => '<a href="mailto:press@harmoniva.app" class="text-purple-400 hover:text-purple-300 transition-colors">press@harmoniva.app</a>']) !!}
        </p>
    </div>
</section>

{{-- Fact Sheet --}}
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">

            {{-- Fact Sheet --}}
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-100 text-purple-700 text-sm font-semibold mb-5">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    {{ __('pages.press.factsheet_badge') }}
                </div>
                <h2 class="text-3xl font-extrabold text-gray-900 mb-8">{{ __('pages.press.factsheet_title') }}</h2>

                <dl class="space-y-4">
                    <div class="flex gap-4 py-4 border-b border-gray-100">
                        <dt class="w-36 text-sm font-semibold text-gray-500 uppercase tracking-wide flex-shrink-0">{{ __('pages.press.fact_founded_label') }}</dt>
                        <dd class="text-gray-900 font-medium">2024</dd>
                    </div>
                    <div class="flex gap-4 py-4 border-b border-gray-100">
                        <dt class="w-36 text-sm font-semibold text-gray-500 uppercase tracking-wide flex-shrink-0">{{ __('pages.press.fact_hq_label') }}</dt>
                        <dd class="text-gray-900 font-medium">{{ __('pages.press.fact_hq_value') }}</dd>
                    </div>
                    <div class="flex gap-4 py-4 border-b border-gray-100">
                        <dt class="w-36 text-sm font-semibold text-gray-500 uppercase tracking-wide flex-shrink-0">{{ __('pages.press.fact_entity_label') }}</dt>
                        <dd class="text-gray-900 font-medium">H&amp;P LLC</dd>
                    </div>
                    <div class="flex gap-4 py-4 border-b border-gray-100">
                        <dt class="w-36 text-sm font-semibold text-gray-500 uppercase tracking-wide flex-shrink-0">{{ __('pages.press.fact_product_label') }}</dt>
                        <dd class="text-gray-900 font-medium">{{ __('pages.press.fact_product_value') }}</dd>
                    </div>
                    <div class="flex gap-4 py-4 border-b border-gray-100">
                        <dt class="w-36 text-sm font-semibold text-gray-500 uppercase tracking-wide flex-shrink-0">{{ __('pages.press.fact_platform_label') }}</dt>
                        <dd class="text-gray-900 font-medium">{{ __('pages.press.fact_platform_value') }}</dd>
                    </div>
                    <div class="flex gap-4 py-4 border-b border-gray-100">
                        <dt class="w-36 text-sm font-semibold text-gray-500 uppercase tracking-wide flex-shrink-0">{{ __('pages.press.fact_languages_label') }}</dt>
                        <dd class="text-gray-900 font-medium">{{ __('pages.press.fact_languages_value') }}</dd>
                    </div>
                    <div class="flex gap-4 py-4 border-b border-gray-100">
                        <dt class="w-36 text-sm font-semibold text-gray-500 uppercase tracking-wide flex-shrink-0">{{ __('pages.press.fact_users_label') }}</dt>
                        <dd class="text-gray-900 font-medium">{{ __('pages.press.fact_users_value') }}</dd>
                    </div>
                    <div class="flex gap-4 py-4">
                        <dt class="w-36 text-sm font-semibold text-gray-500 uppercase tracking-wide flex-shrink-0">{{ __('pages.press.fact_contact_label') }}</dt>
                        <dd><a href="mailto:press@harmoniva.app" class="text-purple-600 font-medium hover:text-purple-700 transition-colors">press@harmoniva.app</a></dd>
                    </div>
                </dl>
            </div>

            {{-- Brand Assets --}}
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-orange-100 text-orange-700 text-sm font-semibold mb-5">
                    <i data-lucide="palette" class="w-4 h-4"></i>
                    {{ __('pages.press.brand_badge') }}
                </div>
                <h2 class="text-3xl font-extrabold text-gray-900 mb-8">{{ __('pages.press.brand_title') }}</h2>

                <div class="space-y-4 mb-8">
                    <a href="#" class="flex items-center gap-4 p-4 border border-gray-200 rounded-xl hover:border-purple-300 hover:bg-purple-50 transition-all duration-200 group">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                            <i data-lucide="image" class="w-6 h-6 text-purple-600"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ __('pages.press.asset_logo_title') }}</div>
                            <div class="text-sm text-gray-500">{{ __('pages.press.asset_logo_desc') }}</div>
                        </div>
                        <i data-lucide="download" class="w-5 h-5 text-gray-400 ml-auto group-hover:text-purple-600 transition-colors"></i>
                    </a>

                    <a href="#" class="flex items-center gap-4 p-4 border border-gray-200 rounded-xl hover:border-purple-300 hover:bg-purple-50 transition-all duration-200 group">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                            <i data-lucide="swatch-book" class="w-6 h-6 text-orange-600"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ __('pages.press.asset_guidelines_title') }}</div>
                            <div class="text-sm text-gray-500">{{ __('pages.press.asset_guidelines_desc') }}</div>
                        </div>
                        <i data-lucide="download" class="w-5 h-5 text-gray-400 ml-auto group-hover:text-purple-600 transition-colors"></i>
                    </a>

                    <a href="#" class="flex items-center gap-4 p-4 border border-gray-200 rounded-xl hover:border-purple-300 hover:bg-purple-50 transition-all duration-200 group">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                            <i data-lucide="monitor" class="w-6 h-6 text-blue-600"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ __('pages.press.asset_screenshots_title') }}</div>
                            <div class="text-sm text-gray-500">{{ __('pages.press.asset_screenshots_desc') }}</div>
                        </div>
                        <i data-lucide="download" class="w-5 h-5 text-gray-400 ml-auto group-hover:text-purple-600 transition-colors"></i>
                    </a>
                </div>

                {{-- Brand Colors --}}
                <h3 class="font-bold text-gray-900 mb-3">{{ __('pages.press.colors_title') }}</h3>
                <div class="flex gap-3 mb-6">
                    <div class="flex-1">
                        <div class="h-12 rounded-lg mb-2" style="background: #9333ea;"></div>
                        <div class="text-xs font-semibold text-gray-700">{{ __('pages.press.color_purple') }}</div>
                        <div class="text-xs text-gray-500">#9333EA</div>
                    </div>
                    <div class="flex-1">
                        <div class="h-12 rounded-lg mb-2" style="background: #f97316;"></div>
                        <div class="text-xs font-semibold text-gray-700">{{ __('pages.press.color_orange') }}</div>
                        <div class="text-xs text-gray-500">#F97316</div>
                    </div>
                    <div class="flex-1">
                        <div class="h-12 rounded-lg mb-2 border border-gray-200" style="background: #FAF7F2;"></div>
                        <div class="text-xs font-semibold text-gray-700">{{ __('pages.press.color_cream') }}</div>
                        <div class="text-xs text-gray-500">#FAF7F2</div>
                    </div>
                    <div class="flex-1">
                        <div class="h-12 rounded-lg mb-2" style="background: #111827;"></div>
                        <div class="text-xs font-semibold text-gray-700">{{ __('pages.press.color_dark') }}</div>
                        <div class="text-xs text-gray-500">#111827</div>
                    </div>
                </div>

                <h3 class="font-bold text-gray-900 mb-2">{{ __('pages.press.typography_title') }}</h3>
                <p class="text-sm text-gray-600">{!! __('pages.press.typography_desc', ['primary' => '<span class="font-semibold">Inter</span>', 'display' => '<span class="font-semibold">Lora</span>']) !!}</p>
            </div>
        </div>
    </div>
</section>

{{-- Press Mentions --}}
<section class="py-20 bg-[#FAF7F2]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-purple-100 text-purple-700 text-sm font-semibold mb-4">
                <i data-lucide="quote" class="w-4 h-4"></i>
                {{ __('pages.press.mentions_badge') }}
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900">{{ __('pages.press.mentions_title') }}</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php $mentions = [
                ['outlet' => 'Music Tech Review', 'date' => __('pages.press.mention_1_date'), 'headline' => __('pages.press.mention_1_headline'), 'quote' => __('pages.press.mention_1_quote')],
                ['outlet' => 'EdTech Insider', 'date' => __('pages.press.mention_2_date'), 'headline' => __('pages.press.mention_2_headline'), 'quote' => __('pages.press.mention_2_quote')],
                ['outlet' => 'SaaS Weekly', 'date' => __('pages.press.mention_3_date'), 'headline' => __('pages.press.mention_3_headline'), 'quote' => __('pages.press.mention_3_quote')],
            ]; @endphp
            @foreach ($mentions as $m)
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm font-bold text-gray-500 uppercase tracking-wide">{{ $m['outlet'] }}</div>
                    <div class="text-xs text-gray-400">{{ $m['date'] }}</div>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-3 leading-snug">
                    {{ $m['headline'] }}
                </h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-4">
                    {{ $m['quote'] }}
                </p>
                <a href="#" class="text-purple-600 text-sm font-semibold hover:text-purple-700 transition-colors inline-flex items-center gap-1">
                    {{ __('pages.press.read_more') }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Press Contact CTA --}}
<section class="py-20 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gray-900 rounded-2xl p-10 text-center text-white">
            <div class="w-14 h-14 bg-purple-600 rounded-xl flex items-center justify-center mx-auto mb-5">
                <i data-lucide="mail" class="w-7 h-7 text-white"></i>
            </div>
            <h2 class="text-2xl font-extrabold mb-3">{{ __('pages.press.media_title') }}</h2>
            <p class="text-gray-400 mb-6 leading-relaxed">
                {{ __('pages.press.media_desc') }}
            </p>
            <a href="mailto:press@harmoniva.app" class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-bold px-7 py-3.5 rounded-xl transition-colors duration-200">
                <i data-lucide="send" class="w-5 h-5"></i>
                press@harmoniva.app
            </a>
        </div>
    </div>
</section>

@endsection
