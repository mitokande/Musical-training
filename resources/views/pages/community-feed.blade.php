@extends('layouts.standalone')

@section('title', __('pages.community.meta_title'))
@section('description', __('pages.community.meta_description'))

@section('content')

{{-- ============ HERO ============ --}}
<section class="py-20 sm:py-28 relative overflow-hidden" style="background: linear-gradient(135deg, #ede9fe 0%, #FAF7F2 55%, #fce7f3 100%);">
    <div class="absolute -top-32 -right-32 w-[600px] h-[600px] rounded-full bg-primary-100/50 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-[350px] h-[350px] rounded-full bg-rose-100/50 blur-2xl pointer-events-none"></div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="text-center max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-100 text-primary-700 text-sm font-semibold mb-6 hero-badge">
                <i data-lucide="users" class="w-4 h-4"></i>
                {{ __('pages.community.hero_badge') }}
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
                {{ __('pages.community.hero_title_a') }}<br>
                <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#7c3aed,#db2777);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ __('pages.community.hero_title_b') }}</span>
            </h1>

            <p class="text-gray-500 text-lg max-w-2xl mx-auto mb-10">
                {{ __('pages.community.hero_subtitle') }}
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-8">
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                    {{ __('pages.community.hero_join') }}
                </a>
                <a href="{{ locale_url('/pricing') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold rounded-xl transition-all shadow-xl hover:-translate-y-0.5 text-white" style="background:linear-gradient(135deg,#db2777,#be185d);">
                    <i data-lucide="crown" class="w-5 h-5"></i>
                    {{ __('pages.community.hero_premium') }}
                </a>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-400">
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ __('pages.community.hero_check_1') }}</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ __('pages.community.hero_check_2') }}</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ __('pages.community.hero_check_3') }}</span>
            </div>
        </div>
    </div>
</section>

{{-- ============ TWO INFO CARDS ============ --}}
<section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 reveal">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-3 block">{{ __('pages.community.cards_eyebrow') }}</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">
                {{ __('pages.community.cards_title_a') }}<br>
                <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#7c3aed,#db2777);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ __('pages.community.cards_title_b') }}</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
            {{-- Card 1: The Feed --}}
            <div class="flex flex-col p-8 bg-gradient-to-br from-primary-50 to-white rounded-2xl border border-primary-100 reveal h-full">
                <div class="w-14 h-14 rounded-2xl bg-primary-100 text-primary-600 flex items-center justify-center mb-5">
                    <i data-lucide="rss" class="w-7 h-7"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('pages.community.card_feed_title') }}</h3>
                <p class="text-gray-600 leading-relaxed">
                    {{ __('pages.community.card_feed_desc') }}
                </p>
            </div>

            {{-- Card 2: Teachers --}}
            <div class="flex flex-col p-8 bg-gradient-to-br from-rose-50 to-white rounded-2xl border border-rose-100 reveal h-full" style="transition-delay:.08s">
                <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mb-5">
                    <i data-lucide="graduation-cap" class="w-7 h-7"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('pages.community.card_teachers_title') }}</h3>
                <p class="text-gray-600 leading-relaxed">
                    {{ __('pages.community.card_teachers_desc') }}
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ============ DETAILED EXPLANATIONS ============ --}}
<section class="py-20" style="background:#FAF7F2;">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">

        {{-- Feed detailed --}}
        <div class="reveal">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-11 h-11 rounded-xl bg-primary-100 text-primary-600 flex items-center justify-center shrink-0">
                    <i data-lucide="rss" class="w-5 h-5"></i>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ __('pages.community.feed_detail_title') }}</h2>
            </div>
            <div class="space-y-4 text-gray-600 text-[17px] leading-relaxed">
                <p>{{ __('pages.community.feed_detail_p1') }}</p>
                <p>{{ __('pages.community.feed_detail_p2') }}</p>
            </div>
        </div>

        {{-- Teachers detailed --}}
        <div class="reveal">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-11 h-11 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ __('pages.community.teachers_detail_title') }}</h2>
            </div>
            <div class="space-y-4 text-gray-600 text-[17px] leading-relaxed">
                <p>
                    {!! __('pages.community.teachers_detail_p1', [
                        'directory_link' => '<a href="'.locale_url('/find-teachers').'" class="text-primary-600 font-semibold underline decoration-primary-300 underline-offset-2 hover:text-primary-700">'.__('pages.community.find_a_teacher').'</a>',
                        'profile_link' => '<a href="'.url('/teachers/tuba-gunvar').'" class="text-rose-600 font-semibold underline decoration-rose-300 underline-offset-2 hover:text-rose-700">Tuba Günvar</a>',
                    ]) !!}
                </p>
                <p>{{ __('pages.community.teachers_detail_p2') }}</p>
            </div>

            <div class="flex flex-col sm:flex-row flex-wrap gap-4 mt-8">
                <a href="{{ locale_url('/find-teachers') }}" class="inline-flex items-center gap-2 px-6 py-3.5 text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg hover:-translate-y-0.5" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    {{ __('pages.community.find_a_teacher') }}
                </a>
                <a href="{{ url('/teachers/tuba-gunvar') }}" class="inline-flex items-center gap-2 px-6 py-3.5 text-sm font-bold text-gray-700 bg-white border border-gray-200 rounded-xl hover:border-rose-400 hover:text-rose-600 transition-all shadow-sm">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    {{ __('pages.community.view_profile') }}
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============ CTA ============ --}}
<section class="py-24 relative overflow-hidden" style="background: linear-gradient(135deg, #ede9fe 0%, #FAF7F2 50%, #fce7f3 100%);">
    <div class="absolute -top-20 -right-20 w-[400px] h-[400px] rounded-full bg-primary-100/50 blur-3xl pointer-events-none"></div>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center relative reveal">
        <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-8 shadow-xl hero-badge" style="background:linear-gradient(135deg,#7c3aed,#db2777);">
            <i data-lucide="sparkles" class="w-8 h-8 text-white"></i>
        </div>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-5">
            {{ __('pages.community.cta_title_a') }}<br>
            <span class="font-serif italic font-normal" style="background:linear-gradient(135deg,#7c3aed,#db2777);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ __('pages.community.cta_title_b') }}</span>
        </h2>
        <p class="text-gray-500 text-lg mb-10 max-w-xl mx-auto">
            {{ __('pages.community.cta_subtitle') }}
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
                {{ __('pages.community.hero_join') }}
            </a>
            <a href="{{ locale_url('/pricing') }}" class="inline-flex items-center gap-2 px-6 py-4 text-base font-medium text-gray-600 hover:text-gray-900 transition-colors">
                {{ __('pages.community.cta_compare') }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</section>

@endsection
