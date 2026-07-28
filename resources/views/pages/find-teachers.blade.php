@extends('layouts.standalone')

@section('title', __('pages.find_teachers.meta_title'))
@section('description', __('pages.find_teachers.meta_description'))

@section('content')

{{-- ============ HERO / EXPLANATION ============ --}}
<section class="bg-gradient-to-br from-purple-700 via-purple-600 to-purple-800 text-white py-20 px-4 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 15% 25%, #fff 0, transparent 40%), radial-gradient(circle at 85% 75%, #f97316 0, transparent 40%);"></div>
    <div class="max-w-3xl mx-auto text-center reveal relative">
        <div class="hero-badge inline-flex items-center gap-2 bg-white/10 text-white text-sm font-medium px-4 py-2 rounded-full mb-6">
            <i data-lucide="users" class="w-4 h-4"></i>
            {{ __('pages.find_teachers.hero_badge') }}
        </div>
        <h1 class="text-4xl md:text-5xl font-bold mb-5">{{ __('pages.find_teachers.hero_title') }}</h1>
        <p class="text-purple-200 text-xl max-w-2xl mx-auto leading-relaxed">{{ __('pages.find_teachers.hero_subtitle') }}</p>
        <div class="flex flex-wrap justify-center gap-4 mt-8 text-sm text-purple-200">
            <span class="flex items-center gap-1.5"><i data-lucide="badge-check" class="w-4 h-4"></i> {{ __('pages.find_teachers.hero_check_1') }}</span>
            <span class="flex items-center gap-1.5"><i data-lucide="star" class="w-4 h-4"></i> {{ __('pages.find_teachers.hero_check_2') }}</span>
            <span class="flex items-center gap-1.5"><i data-lucide="calendar-check" class="w-4 h-4"></i> {{ __('pages.find_teachers.hero_check_3') }}</span>
        </div>
    </div>
</section>

{{-- ============ HOW IT WORKS (mini) ============ --}}
<section class="bg-white border-b border-gray-100 py-12 px-4">
    <div class="max-w-4xl mx-auto reveal">
        <div class="grid sm:grid-cols-3 gap-6">
            @php
            $steps = [
                ['icon'=>'search','title'=>__('pages.find_teachers.step_1_title'),'desc'=>__('pages.find_teachers.step_1_desc')],
                ['icon'=>'message-circle','title'=>__('pages.find_teachers.step_2_title'),'desc'=>__('pages.find_teachers.step_2_desc')],
                ['icon'=>'graduation-cap','title'=>__('pages.find_teachers.step_3_title'),'desc'=>__('pages.find_teachers.step_3_desc')],
            ];
            @endphp
            @foreach($steps as $s)
            <div class="text-center">
                <div class="w-12 h-12 mx-auto rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center mb-3">
                    <i data-lucide="{{ $s['icon'] }}" class="w-5 h-5"></i>
                </div>
                <h3 class="font-bold text-gray-900 text-sm mb-1.5">{{ $s['title'] }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed">{{ $s['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<div class="bg-[#FAF7F2] py-16 px-4">
    <div class="max-w-5xl mx-auto space-y-16">

        @php
            // Shared card renderer data helper: chips from expertise areas or genres.
            $chipSource = fn ($p) => collect($p->expertise_areas ?? [])->merge($p->genres ?? [])->filter()->unique()->take(3);
        @endphp

        {{-- ============ TEACHERS ============ --}}
        <section id="teachers" class="reveal scroll-mt-24">
            <div class="flex items-end justify-between mb-6 flex-wrap gap-3">
                <div>
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-purple-600 mb-2 block">{{ __('pages.find_teachers.teachers_eyebrow') }}</span>
                    <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900">{{ __('pages.find_teachers.teachers_title') }}</h2>
                </div>
                <span class="text-sm text-gray-400 font-medium">{{ trans_choice('pages.find_teachers.teachers_count', $teachers->count(), ['count' => $teachers->count()]) }}</span>
            </div>

            @if($teachers->isEmpty())
                <div class="light-card rounded-2xl p-10 text-center text-gray-500">
                    <i data-lucide="users" class="w-8 h-8 mx-auto mb-3 text-gray-300"></i>
                    {{ __('pages.find_teachers.teachers_empty') }}
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($teachers as $p)
                    @php $stats = $reviewStats->get($p->id); @endphp
                    <a href="{{ $p->publicUrl() }}" class="light-card rounded-2xl overflow-hidden group hover:shadow-lg hover:-translate-y-1 transition-all flex flex-col">
                        <div class="h-20 relative" style="background:linear-gradient(135deg,#9333ea22,#f9731622);">
                            @if($p->coverImageUrl())
                                <img src="{{ $p->coverImageUrl() }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                            @endif
                            @if($p->accepts_students)
                                <span class="absolute top-3 right-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-100/95 text-green-700 text-[11px] font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> {{ __('pages.find_teachers.accepting_students') }}
                                </span>
                            @endif
                        </div>
                        <div class="px-5 pb-5 flex-1 flex flex-col">
                            <div class="-mt-7 mb-3">
                                @if($p->user->hasAvatar())
                                    <img src="{{ $p->user->avatar }}" alt="{{ $p->displayName() }}" class="w-14 h-14 rounded-2xl object-cover ring-4 ring-white shadow">
                                @else
                                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-orange-400 ring-4 ring-white shadow flex items-center justify-center text-white font-bold text-lg">
                                        {{ strtoupper(mb_substr($p->displayName(), 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <h3 class="font-bold text-gray-900 group-hover:text-purple-700 transition-colors leading-tight">{{ $p->displayName() }}</h3>
                            @if($p->headline)
                                <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">{{ $p->headline }}</p>
                            @endif
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2.5 text-xs text-gray-400">
                                @if($p->primary_instrument)
                                    <span class="flex items-center gap-1"><i data-lucide="music-2" class="w-3.5 h-3.5"></i>{{ $p->primary_instrument }}</span>
                                @endif
                                @if($p->city || $p->country)
                                    <span class="flex items-center gap-1"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i>{{ collect([$p->city, $p->country])->filter()->implode(', ') }}</span>
                                @endif
                            </div>
                            @if($chipSource($p)->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5 mt-3">
                                    @foreach($chipSource($p) as $chip)
                                        <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[11px] font-semibold">{{ $chip }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="mt-auto pt-4 flex items-center justify-between">
                                @if($stats)
                                    <span class="flex items-center gap-1 text-sm">
                                        <i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i>
                                        <span class="font-bold text-gray-800">{{ number_format($stats->rating_avg, 1) }}</span>
                                        <span class="text-gray-400 text-xs">({{ $stats->reviews_count }})</span>
                                    </span>
                                @else
                                    <span class="text-xs text-gray-300">{{ __('pages.find_teachers.new_profile') }}</span>
                                @endif
                                <span class="inline-flex items-center gap-1 text-sm font-semibold text-purple-600">{{ __('pages.find_teachers.view_profile') }} <i data-lucide="arrow-right" class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5"></i></span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ============ SCHOOLS ============ --}}
        <section id="schools" class="reveal scroll-mt-24">
            <div class="flex items-end justify-between mb-6 flex-wrap gap-3">
                <div>
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-orange-600 mb-2 block">{{ __('pages.find_teachers.schools_eyebrow') }}</span>
                    <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900">{{ __('pages.find_teachers.schools_title') }}</h2>
                </div>
                <span class="text-sm text-gray-400 font-medium">{{ trans_choice('pages.find_teachers.schools_count', $schools->count(), ['count' => $schools->count()]) }}</span>
            </div>

            <p class="text-gray-600 leading-relaxed mb-6 max-w-3xl">{{ __('pages.find_teachers.schools_desc') }}</p>

            @if($schools->isEmpty())
                <div class="light-card rounded-2xl p-10 text-center text-gray-500">
                    <i data-lucide="building-2" class="w-8 h-8 mx-auto mb-3 text-gray-300"></i>
                    {{ __('pages.find_teachers.schools_empty') }}
                </div>
            @else
                <div class="grid sm:grid-cols-2 gap-5">
                    @foreach($schools as $p)
                    @php $stats = $reviewStats->get($p->id); @endphp
                    <a href="{{ $p->publicUrl() }}" class="light-card rounded-2xl p-5 group hover:shadow-lg hover:-translate-y-1 transition-all flex gap-4">
                        @if($p->user->hasAvatar())
                            <img src="{{ $p->user->avatar }}" alt="{{ $p->displayName() }}" class="w-16 h-16 rounded-2xl object-cover flex-shrink-0 shadow">
                        @else
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-400 to-purple-500 flex items-center justify-center text-white flex-shrink-0 shadow">
                                <i data-lucide="building-2" class="w-7 h-7"></i>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-gray-900 group-hover:text-purple-700 transition-colors leading-tight">{{ $p->displayName() }}</h3>
                            @if($p->headline)
                                <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">{{ $p->headline }}</p>
                            @endif
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-xs text-gray-400">
                                @if($p->city || $p->country)
                                    <span class="flex items-center gap-1"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i>{{ collect([$p->city, $p->country])->filter()->implode(', ') }}</span>
                                @endif
                                @if($stats)
                                    <span class="flex items-center gap-1"><i data-lucide="star" class="w-3.5 h-3.5 text-amber-400 fill-amber-400"></i><span class="font-bold text-gray-700">{{ number_format($stats->rating_avg, 1) }}</span> ({{ $stats->reviews_count }})</span>
                                @endif
                            </div>
                            <span class="inline-flex items-center gap-1 text-sm font-semibold text-purple-600 mt-3">{{ __('pages.find_teachers.view_school') }} <i data-lucide="arrow-right" class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5"></i></span>
                        </div>
                    </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ============ FAQ ============ --}}
        <section class="reveal">
            @include('pages.partials.guide-faq', ['label' => __('pages.find_teachers.faq_label'), 'faqs' => [
                ['q'=>__('pages.find_teachers.faq_q1'),'a'=>__('pages.find_teachers.faq_a1')],
                ['q'=>__('pages.find_teachers.faq_q2'),'a'=>__('pages.find_teachers.faq_a2')],
                ['q'=>__('pages.find_teachers.faq_q3'),'a'=>__('pages.find_teachers.faq_a3')],
                ['q'=>__('pages.find_teachers.faq_q4'),'a'=>__('pages.find_teachers.faq_a4')],
            ]])
        </section>

    </div>
</div>

{{-- ============ BOTTOM CTA ============ --}}
<section class="bg-white py-20 px-4">
    <div class="max-w-2xl mx-auto text-center reveal">
        <span class="text-xs font-bold uppercase tracking-[0.2em] text-purple-600 mb-3 block">{{ __('pages.find_teachers.cta_eyebrow') }}</span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">{{ __('pages.find_teachers.cta_title') }}</h2>
        <p class="text-gray-500 text-lg mb-8">{{ __('pages.find_teachers.cta_subtitle') }}</p>
        <div class="flex flex-wrap justify-center gap-3">
            <a href="{{ locale_url('/teachers') }}" class="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl text-white font-semibold shadow-lg shadow-purple-500/25 hover:-translate-y-0.5 transition-all" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">{{ __('pages.find_teachers.cta_teachers') }} <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
            <a href="{{ locale_url('/schools') }}" class="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl font-semibold border-2 border-gray-200 text-gray-700 hover:bg-gray-50 transition-all">{{ __('pages.find_teachers.cta_schools') }} <i data-lucide="building-2" class="w-4 h-4"></i></a>
        </div>
    </div>
</section>

@endsection
