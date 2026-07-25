<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        // Teachers and music schools share this page; school profiles pull
        // school.{key} label overrides and fall back to teacher.{key}.
        $isSchoolProfile = $profile->isSchoolEntity();
        $trans = fn (string $key, array $replace = []) => $isSchoolProfile && \Illuminate\Support\Facades\Lang::has('school.'.$key)
            ? __('school.'.$key, $replace)
            : __('teacher.'.$key, $replace);

        $teacherName = $profile->displayName();
        $seoTitle = $profile->seo_title ?: ($teacherName.($profile->expertise ? ' — '.$profile->expertise : ''));
        $seoDescription = $profile->seo_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $profile->about), 160);
        $publicPhotos = $profile->media->where('visibility', 'public');
    @endphp

    <title>{{ $seoTitle }} - {{ config('app.name', 'Harmoniva') }}</title>
    @if($isPreview)
        <meta name="robots" content="noindex, nofollow">
    @else
        <link rel="canonical" href="{{ $profile->publicUrl() }}">
        <meta name="description" content="{{ $seoDescription }}">
        <meta property="og:type" content="profile">
        <meta property="og:site_name" content="Harmoniva">
        <meta property="og:title" content="{{ $seoTitle }}">
        <meta property="og:description" content="{{ $seoDescription }}">
        <meta property="og:url" content="{{ $profile->publicUrl() }}">
        <meta property="og:image" content="{{ $profile->user->hasAvatar() ? $profile->user->avatar : asset('images/og-image.png') }}">
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="{{ $seoTitle }}">
        <meta name="twitter:description" content="{{ $seoDescription }}">
        <meta name="twitter:image" content="{{ $profile->user->hasAvatar() ? $profile->user->avatar : asset('images/og-image.png') }}">
        <script type="application/ld+json">
        {!! json_encode(array_filter([
            '@context' => 'https://schema.org',
            // Schools are LocalBusiness entities; individual teachers are Persons.
            '@type' => $isSchoolProfile ? 'MusicSchool' : 'Person',
            'name' => $teacherName,
            'jobTitle' => $isSchoolProfile ? null : $profile->expertise,
            'description' => $seoDescription,
            'url' => $profile->publicUrl(),
            'mainEntityOfPage' => $profile->publicUrl(),
            'image' => $profile->user->hasAvatar() ? $profile->user->avatar : null,
            'address' => array_filter([
                '@type' => 'PostalAddress',
                'addressCountry' => $profile->country,
                'addressLocality' => $profile->city,
            ]),
            'knowsLanguage' => $profile->languages,
            'aggregateRating' => ($reviewStats['count'] ?? 0) > 0 ? [
                '@type' => 'AggregateRating',
                'ratingValue' => $reviewStats['average'],
                'reviewCount' => $reviewStats['count'],
                'bestRating' => 5,
                'worstRating' => 1,
            ] : null,
        ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    @vite('resources/css/marketing.css')
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .card { background: white; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.07); }
        .hero-gradient { background: linear-gradient(135deg, #9333ea 0%, #c084fc 55%, #f97316 100%); }
        .btn-primary { background: linear-gradient(135deg, #7c3aed 0%, #9333ea 100%); color: #fff; }
        .btn-primary:hover { filter: brightness(1.08); }
    </style>
    @livewireStyles
</head>
<body class="font-sans bg-gray-50 min-h-screen">

@include('partials.navbar', ['active' => 'teachers'])

@if($isPreview)
    <div class="bg-amber-500 text-white text-center text-sm font-semibold py-2 px-4">
        {{ $trans('public.preview_banner') }}
    </div>
@endif

@php
    $user = $profile->user;
    $publicPaymentLinks = $profile->paymentLinks->where('visibility', 'public')->where('is_active', true);
    $showBookingPanel = $profile->isPremiumTier();
    $socials = collect($profile->social_links ?? [])->filter();
@endphp

{{-- Hero band: always has height so the profile card overlaps it cleanly --}}
<div class="hero-gradient">
    <div class="max-w-[1200px] mx-auto">
        @if($profile->coverImageUrl())
            <img src="{{ $profile->coverImageUrl() }}" alt="" class="w-full h-44 sm:h-64 object-cover opacity-90">
        @else
            <div class="h-32 sm:h-44"></div>
        @endif
    </div>
</div>

<div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 -mt-16 sm:-mt-20 relative z-10 pb-16">

    <div class="grid lg:grid-cols-[2.03fr_1fr] gap-6 items-start">

        {{-- Main (wide) column --}}
        <div class="space-y-6" x-data="{ msgModal: false }">

            {{-- Profile card: photo on the LEFT, teacher info on the right --}}
            <div class="card p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row gap-6 sm:gap-8">
                    {{-- Photo: left, enlarged (~20% wider, ~20% taller than the original) --}}
                    <div class="shrink-0 mx-auto sm:mx-0">
                        @if($user->hasAvatar())
                            <img src="{{ $user->avatar }}" alt="{{ $teacherName }}" class="w-[212px] h-[230px] sm:w-[250px] sm:h-[324px] rounded-3xl object-cover ring-4 ring-white shadow-xl">
                        @else
                            <div class="w-[212px] h-[230px] sm:w-[250px] sm:h-[324px] rounded-3xl bg-primary-100 text-primary-700 flex items-center justify-center text-7xl font-bold ring-4 ring-white shadow-xl">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    {{-- Info: right of the photo (flex column so actions align to photo bottom) --}}
                    <div class="flex-1 min-w-0 flex flex-col">
                        {{-- Name --}}
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center gap-2 flex-wrap">
                            {{ $teacherName }}
                            @if($isSchoolProfile)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 ring-1 ring-teal-200">
                                    <i data-lucide="building-2" class="w-3.5 h-3.5"></i> {{ $trans('public.school_badge') }}
                                </span>
                            @endif
                        </h1>

                        {{-- Short headline: first line, italic, colorful, ~20% smaller, max 55 chars --}}
                        @if($profile->headline)
                            <p class="text-primary-600 font-semibold italic text-sm sm:text-base mt-2">{{ \Illuminate\Support\Str::limit($profile->headline, 55) }}</p>
                        @endif

                        {{-- Lessons · format/duration/price · location · languages --}}
                        @php
                            $primaryService = $profile->services->first();
                            $formatParts = collect($profile->teaching_formats ?? [])->map(fn ($f) => $trans('fields.format_'.$f))->all();
                            if ($primaryService) {
                                if ($primaryService->duration_minutes) $formatParts[] = $primaryService->duration_minutes.' min';
                                if ($primaryService->price_text) $formatParts[] = $primaryService->price_text;
                            }
                        @endphp
                        <div class="mt-4 space-y-2.5 text-[15px] text-gray-700">
                            @if(!empty($profile->lesson_types))
                                <p class="flex items-start gap-2.5"><i data-lucide="music" class="w-4 h-4 text-primary-500 mt-1 shrink-0"></i> <span>{{ implode(', ', $profile->lesson_types) }}</span></p>
                            @endif
                            @if(!empty($formatParts))
                                <p class="flex items-start gap-2.5"><i data-lucide="monitor" class="w-4 h-4 text-primary-500 mt-1 shrink-0"></i> <span>{{ implode(' / ', $formatParts) }}</span></p>
                            @endif
                            @if($profile->country || $profile->city)
                                <p class="flex items-start gap-2.5"><i data-lucide="map-pin" class="w-4 h-4 text-primary-500 mt-1 shrink-0"></i> <span>{{ implode(', ', array_filter([$profile->city, $profile->country])) }}</span></p>
                            @endif
                            @if(!empty($profile->languages))
                                <p class="flex items-start gap-2.5"><i data-lucide="languages" class="w-4 h-4 text-primary-500 mt-1 shrink-0"></i> <span>{{ implode(', ', $profile->languages) }}</span></p>
                            @endif
                        </div>

                        {{-- Rating + views --}}
                        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mt-4 text-[15px] text-gray-500">
                            <span class="inline-flex items-center gap-1.5">
                                <i data-lucide="star" class="w-4 h-4 text-amber-400"></i>
                                @if(($reviewStats['count'] ?? 0) > 0)
                                    <b class="text-gray-800">{{ $reviewStats['average'] }}</b>&nbsp;/ 5 · {{ $trans('reviews.reviews_count', ['count' => $reviewStats['count']]) }}
                                @else
                                    {{ $trans('public.no_reviews') }}
                                @endif
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <i data-lucide="eye" class="w-4 h-4"></i> {{ number_format($profile->view_count) }} {{ $trans('public.views') }}
                            </span>
                        </div>

                        {{-- Message + Write a Review: always fixed in every case (guest / user / admin / owner).
                             No Follow button here. On mobile they sit side by side (flex-1). --}}
                        <div class="flex flex-wrap items-center gap-2.5 mt-auto pt-5">
                            @auth
                                <a href="{{ route('messages', ['to' => $user->username]) }}" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-3 sm:px-5 py-2.5 text-[15px] font-semibold text-white bg-gray-900 hover:bg-black rounded-xl transition">
                                    <i data-lucide="message-circle" class="w-4 h-4"></i> {{ $trans('public.message_teacher') }}
                                </a>
                            @else
                                <button type="button" @click="msgModal = true" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-3 sm:px-5 py-2.5 text-[15px] font-semibold text-white bg-gray-900 hover:bg-black rounded-xl transition">
                                    <i data-lucide="message-circle" class="w-4 h-4"></i> {{ $trans('public.message_teacher') }}
                                </button>
                            @endauth
                            <button type="button" @click="document.getElementById('reviewsSection')?.scrollIntoView({behavior:'smooth'})" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-3 sm:px-5 py-2.5 text-[15px] font-semibold text-white bg-gray-900 hover:bg-black rounded-xl transition">
                                <i data-lucide="star" class="w-4 h-4"></i> {{ $trans('public.write_review') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- About: always visible (reduced heading gap + bottom padding) --}}
            <div class="card px-6 pt-6 pb-4 sm:px-7 sm:pt-7 sm:pb-5">
                <h2 class="font-bold text-gray-900 text-lg mb-2">{{ $trans('public.about') }}</h2>
                @if($profile->about)
                    @php
                        $aboutLen = mb_strlen($profile->about);
                        $aboutMobile = $aboutLen > 300 ? rtrim(mb_substr($profile->about, 0, 300)).'…' : $profile->about;
                        $aboutDesktop = $aboutLen > 500 ? rtrim(mb_substr($profile->about, 0, 500)).'…' : $profile->about;
                    @endphp
                    {{-- Content kept on single lines so whitespace-pre-line does not inject phantom line breaks --}}
                    <div x-data="{ expanded: false }" class="text-[15px] text-gray-600 leading-relaxed">
                        <p x-show="!expanded" class="whitespace-pre-line"><span class="sm:hidden">{{ $aboutMobile }}</span><span class="hidden sm:inline">{{ $aboutDesktop }}</span>@if($aboutLen > 300)<button type="button" @click="expanded = true" class="font-semibold text-primary-600 hover:text-primary-700 whitespace-nowrap block text-right mt-1 sm:inline sm:ml-5 sm:mt-0 {{ $aboutLen <= 500 ? 'sm:hidden' : '' }}">{{ $trans('public.see_more') }}</button>@endif</p>
                        <p x-show="expanded" x-cloak class="whitespace-pre-line">{{ $profile->about }}<button type="button" @click="expanded = false" class="font-semibold text-primary-600 hover:text-primary-700 whitespace-nowrap block text-right mt-1 sm:inline sm:ml-5 sm:mt-0">{{ $trans('public.see_less') }}</button></p>
                    </div>
                @else
                    <p class="text-[15px] text-gray-400 italic">{{ $isPreview ? $trans('public.empty_hint_about') : '—' }}</p>
                @endif

                @if($profile->teaching_methodology)
                    <h3 class="font-bold text-gray-900 mt-6 mb-2">{{ $trans('public.methodology') }}</h3>
                    <p class="text-[15px] text-gray-600 whitespace-pre-line leading-relaxed">{{ $profile->teaching_methodology }}</p>
                @endif
            </div>

            {{-- Jump buttons: Services / Articles / Videos / Reviews — aligned with the About box --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                @foreach([
                    'servicesSection' => $trans('public.services'),
                    'articlesSection' => $trans('public.articles'),
                    'videosSection' => $trans('public.videos'),
                    'reviewsSection' => $trans('public.reviews'),
                ] as $target => $label)
                    <button type="button" @click="document.getElementById('{{ $target }}')?.scrollIntoView({behavior:'smooth'})"
                            class="w-full px-3 py-2.5 rounded-xl text-[15px] font-semibold text-center bg-white text-gray-700 border border-gray-200 hover:bg-primary-50 hover:text-primary-700 transition">
                        {{ $label }}
                        @if($target === 'reviewsSection' && ($reviewStats['count'] ?? 0) > 0)
                            <span class="ml-1 text-gray-400">({{ $reviewStats['count'] }})</span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- Education: shown right after About when present --}}
            @if($profile->educations->isNotEmpty())
                <div class="card p-6 sm:p-7">
                    <h2 class="font-bold text-gray-900 text-lg mb-4">{{ $trans('public.education') }}</h2>
                    <div class="space-y-4">
                        @foreach($profile->educations as $education)
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center shrink-0">
                                    <i data-lucide="graduation-cap" class="w-5 h-5 text-primary-600"></i>
                                </div>
                                <div>
                                    <p class="text-[15px] font-semibold text-gray-900">{{ $education->institution }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ implode(' · ', array_filter([$education->program, $education->field_of_study, $education->graduation_year])) }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Book a lesson (mobile only, placed right after Education) --}}
            <div class="card p-6 lg:hidden">
                <h2 class="font-bold text-gray-900 text-lg mb-4 flex items-center gap-2">
                    <i data-lucide="calendar" class="w-5 h-5 text-primary-500"></i> {{ $trans('public.booking') }}
                </h2>
                @include('teachers.partials.booking-mini', [
                    'slug' => $profile->slug,
                    'bookingEnabled' => ($bookingEnabled ?? false),
                    'bookingDays' => ($bookingDays ?? []),
                ])
            </div>

            {{-- Services (always shown, even when empty) --}}
            <div class="card p-6 sm:p-7 scroll-mt-6" id="servicesSection">
                <h2 class="font-bold text-gray-900 text-lg mb-4">{{ $trans('public.services') }}</h2>
                <div class="space-y-3">
                    @forelse($profile->services as $service)
                        <div class="border border-gray-100 rounded-2xl p-4 sm:p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-bold text-gray-900 text-[15px]">{{ $service->title }}</p>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        {{ implode(' · ', array_filter([
                                            $service->lesson_type,
                                            $service->format ? $trans('fields.format_'.$service->format) : null,
                                            $service->duration_minutes ? $service->duration_minutes.' min' : null,
                                        ])) }}
                                    </p>
                                    @if($service->description)<p class="text-[15px] text-gray-600 mt-2">{{ $service->description }}</p>@endif
                                </div>
                                @if($service->price_text)
                                    <span class="shrink-0 px-3.5 py-1.5 rounded-full text-[15px] font-bold bg-primary-50 text-primary-700">{{ $service->price_text }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-[15px] text-gray-400">{{ $trans('public.no_services') }}</p>
                    @endforelse
                </div>
            </div>

            {{-- Contact (mobile only, placed right after Services) --}}
            @include('teachers.partials.contact', ['wrapperClass' => 'lg:hidden'])

            {{-- Photos & Certificates: shown above Articles (mobile + desktop), always visible --}}
            <div class="card p-6 sm:p-7" x-data="{ lb: false, lbSrc: '', lbTitle: '' }">
                <h2 class="font-bold text-gray-900 text-lg mb-4">{{ $trans('public.documents_photos') }}</h2>
                @if($publicPhotos->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($publicPhotos as $item)
                            @if($item->isImage())
                                <button type="button" @click="lb=true; lbSrc=@js($item->publicUrl()); lbTitle=@js($item->title ?: $item->original_name)" class="block group">
                                    <img src="{{ $item->publicUrl() }}" alt="{{ $item->title }}" class="w-full h-36 object-cover rounded-2xl transition group-hover:opacity-90">
                                </button>
                            @else
                                <a href="{{ $item->publicUrl() }}" target="_blank" rel="noopener" class="flex items-center gap-2 p-4 bg-gray-50 rounded-2xl text-[15px] text-gray-700 hover:bg-gray-100">
                                    <i data-lucide="file-text" class="w-5 h-5 text-primary-500 shrink-0"></i>
                                    <span class="truncate">{{ $item->title ?: $item->original_name }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>

                    {{-- Lightbox popup --}}
                    <div x-show="lb" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none" @keydown.escape.window="lb=false">
                        <div class="absolute inset-0 bg-black/80" @click="lb=false"></div>
                        <div class="relative max-w-4xl max-h-[90vh]">
                            <img :src="lbSrc" :alt="lbTitle" class="max-w-full max-h-[85vh] rounded-xl object-contain">
                            <p class="text-center text-white/80 text-sm mt-3" x-text="lbTitle"></p>
                            <button type="button" @click="lb=false" class="absolute -top-3 -right-3 w-9 h-9 bg-white rounded-full flex items-center justify-center text-gray-700 hover:text-gray-900 shadow-lg"><i data-lucide="x" class="w-5 h-5"></i></button>
                        </div>
                    </div>
                @else
                    <p class="text-[15px] text-gray-400">{{ $trans('media.none') }}</p>
                @endif
            </div>

            {{-- Articles (always shown, even when empty) --}}
            <div class="card p-6 sm:p-7 scroll-mt-6" id="articlesSection">
                <h2 class="font-bold text-gray-900 text-lg mb-4">{{ $trans('public.articles') }}</h2>
                @if($articles->isEmpty())
                    <p class="text-[15px] text-gray-400">{{ $trans('public.no_articles') }}</p>
                @else
                    <div class="grid sm:grid-cols-2 gap-4">
                        @foreach($articles as $article)
                            <a href="{{ route('articles.show', $article->slug) }}" class="block border border-gray-100 rounded-2xl overflow-hidden hover:shadow-md hover:border-gray-200 transition group">
                                @if($article->featured_image)
                                    <img src="{{ asset('storage/' . $article->featured_image) }}" alt="{{ $article->title }}" class="w-full h-36 object-cover">
                                @endif
                                <div class="p-5">
                                    <p class="font-bold text-gray-900 text-[15px] group-hover:text-primary-600 transition-colors">{{ $article->title }}</p>
                                    @if($article->excerpt)<p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $article->excerpt }}</p>@endif
                                    <p class="text-sm text-gray-400 mt-2">{{ $article->published_at?->format('d.m.Y') }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Videos (always shown, even when empty) --}}
            <div class="card p-6 sm:p-7 scroll-mt-6" id="videosSection">
                <h2 class="font-bold text-gray-900 text-lg mb-4">{{ $trans('public.videos') }}</h2>
                @if($profile->videos->isEmpty())
                    <p class="text-[15px] text-gray-400">{{ $trans('public.no_videos') }}</p>
                @else
                    <div class="grid sm:grid-cols-2 gap-4">
                        @foreach($profile->videos as $video)
                            <div class="border border-gray-100 rounded-2xl overflow-hidden">
                                <div class="aspect-video">
                                    <iframe src="{{ $video->embedUrl() }}" title="{{ $video->title }}"
                                            class="w-full h-full" frameborder="0" loading="lazy"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen></iframe>
                                </div>
                                <div class="p-4">
                                    <p class="text-[15px] font-bold text-gray-900">{{ $video->title }}</p>
                                    @if($video->description)<p class="text-sm text-gray-500 mt-1">{{ $video->description }}</p>@endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Reviews (always shown): Write a Review form + list newest → oldest --}}
            <div class="card p-6 sm:p-7 scroll-mt-6" id="reviewsSection">
                <h2 class="font-bold text-gray-900 text-lg mb-4">
                    {{ $trans('public.reviews') }}
                    @if(($reviewStats['count'] ?? 0) > 0)<span class="text-gray-400 font-semibold">({{ $reviewStats['count'] }})</span>@endif
                </h2>

                @if(session('status') === 'review-saved')
                    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl">
                        <p class="text-sm text-green-700">{{ $trans('reviews.status_review-saved') }}</p>
                    </div>
                @endif

                {{-- Write a Review — always visible --}}
                <div class="border border-gray-100 rounded-2xl p-5 mb-5">
                    <h3 class="font-bold text-gray-900 mb-3">{{ $trans('reviews.write') }}</h3>
                    @auth
                        <form method="POST" action="{{ route('teachers.reviews.store', $profile->slug) }}" x-data="{ rating: {{ $myReview?->rating ?? 5 }} }">
                            @csrf
                            <div class="flex items-center gap-1 mb-3">
                                <template x-for="i in 5" :key="i">
                                    <button type="button" @click="rating = i" class="p-0.5">
                                        <svg class="w-7 h-7" :class="i <= rating ? 'text-amber-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118L10.586 15.58a1 1 0 00-1.175 0l-3.367 2.445c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.062 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.287-3.958z"/></svg>
                                    </button>
                                </template>
                                <input type="hidden" name="rating" :value="rating">
                            </div>
                            <textarea name="body" rows="3" maxlength="2000" placeholder="{{ $trans('reviews.body_placeholder') }}" class="w-full rounded-xl border-gray-300 text-[15px] mb-3">{{ old('body', $myReview?->body ?? '') }}</textarea>
                            <button class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-[15px] font-semibold rounded-xl transition">{{ $trans('reviews.submit') }}</button>
                            @error('body')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                        </form>
                        @unless($canReview ?? false)
                            <p class="text-xs text-gray-400 mt-3">{{ $trans('reviews.error_not_eligible') }}</p>
                        @endunless
                    @else
                        <p class="text-[15px] text-gray-500 mb-3">{{ $trans('public.message_login_required') }}</p>
                        <div class="flex gap-2">
                            <a href="{{ route('login') }}" class="inline-flex px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-[15px] font-semibold rounded-xl transition">{{ $trans('public.login') }}</a>
                            <a href="{{ route('register') }}" class="inline-flex px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 text-[15px] font-semibold rounded-xl transition">{{ $trans('public.register') }}</a>
                        </div>
                    @endauth
                </div>

                {{-- Existing reviews, newest → oldest --}}
                @if(($reviews ?? collect())->isEmpty())
                    <p class="text-[15px] text-gray-400">{{ $trans('public.no_reviews') }}</p>
                @else
                    <div class="space-y-3">
                        @foreach($reviews as $review)
                            <div class="border border-gray-100 rounded-2xl p-5">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-bold">
                                        {{ strtoupper(substr($review->student->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-[15px] font-semibold text-gray-800">{{ $review->student->name }} {{ substr($review->student->surname ?? '', 0, 1) }}.</p>
                                        <div class="flex items-center gap-0.5">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 00-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118L10.586 15.58a1 1 0 00-1.175 0l-3.367 2.445c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.062 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.287-3.958z"/></svg>
                                            @endfor
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-400">{{ $review->created_at->format('M j, Y') }}</span>
                                    @if(auth()->id() === $profile->user_id)
                                        <form method="POST" action="{{ route('teacher-reviews.report', $review) }}">
                                            @csrf
                                            <button class="text-xs font-semibold text-gray-400 hover:text-red-600">{{ $trans('reviews.report') }}</button>
                                        </form>
                                    @endif
                                </div>
                                @if($review->body)
                                    <p class="text-[15px] text-gray-600 whitespace-pre-line">{{ $review->body }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Login-required popup for messaging (guests) --}}
            <div x-show="msgModal" x-cloak style="display:none"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 @keydown.escape.window="msgModal = false">
                <div class="absolute inset-0 bg-black/40" @click="msgModal = false"></div>
                <div class="relative card p-6 max-w-sm w-full text-center">
                    <div class="w-12 h-12 rounded-full bg-primary-50 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="lock" class="w-6 h-6 text-primary-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg mb-1">{{ $trans('public.message_teacher') }}</h3>
                    <p class="text-[15px] text-gray-600 mb-5">{{ $trans('public.message_login_required') }}</p>
                    <div class="flex gap-2">
                        <a href="{{ route('login') }}" class="flex-1 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition">{{ $trans('public.login') }}</a>
                        <a href="{{ route('register') }}" class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold rounded-xl transition">{{ $trans('public.register') }}</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right column: booking calendar + payment links + contact --}}
        <div class="space-y-6" x-data="{ followModal: false }">
            {{-- Book a lesson: compact fixed weekly booking widget (desktop; on mobile it's rendered after Education) --}}
            <div class="card p-6 hidden lg:block" id="bookingPanel">
                <h2 class="font-bold text-gray-900 text-lg mb-4 flex items-center gap-2">
                    <i data-lucide="calendar" class="w-5 h-5 text-primary-500"></i> {{ $trans('public.booking') }}
                </h2>
                @include('teachers.partials.booking-mini', [
                    'slug' => $profile->slug,
                    'bookingEnabled' => ($bookingEnabled ?? false),
                    'bookingDays' => ($bookingDays ?? []),
                ])
            </div>

            {{-- Contact + socials (desktop only; on mobile it's rendered after Services) --}}
            @include('teachers.partials.contact', ['wrapperClass' => 'hidden lg:block'])

            {{-- Teacher feed: double height, scrollable --}}
            <div class="card p-6">
                <div class="flex items-center justify-between gap-2 mb-4">
                    <h2 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                        <i data-lucide="rss" class="w-5 h-5 text-primary-500"></i> {{ $trans('public.feed') }}
                    </h2>
                    @auth
                        @if(auth()->id() !== $user->id)
                            @livewire('follow-button', ['user' => $user], key('feed-follow-'.$user->id))
                        @endif
                    @else
                        <button type="button" @click="followModal = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white btn-primary transition">
                            <i data-lucide="user-plus" class="w-4 h-4"></i> {{ __('app.social.follow') }}
                        </button>
                    @endauth
                </div>
                <div class="max-h-[640px] overflow-y-auto pr-1 space-y-4">
                    @forelse(($feedItems ?? collect()) as $item)
                        @php $actor = $item->actor; @endphp
                        <div class="flex gap-3">
                            @if($actor && $actor->hasAvatar())
                                <img src="{{ $actor->avatar }}" alt="" class="w-9 h-9 rounded-full object-cover shrink-0">
                            @else
                                <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-bold shrink-0">
                                    {{ strtoupper(substr($actor->name ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm">
                                    <span class="font-semibold text-gray-900">{{ $actor ? $actor->fullName() : '' }}</span>
                                    <span class="text-gray-400">· {{ $item->created_at->diffForHumans() }}</span>
                                </p>
                                <div class="mt-1 text-sm text-gray-700">
                                    @switch($item->type)
                                        @case('post')
                                            <p class="whitespace-pre-line break-words">{{ $item->body }}</p>
                                            @break
                                        @case('achievement')
                                            <div class="flex items-center gap-2 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2 text-amber-800">
                                                <i data-lucide="trophy" class="w-4 h-4 shrink-0"></i><span>{{ $item->body }}</span>
                                            </div>
                                            @break
                                        @case('new_member')
                                            <div class="flex items-center gap-2 bg-primary-50 border border-primary-100 rounded-xl px-3 py-2 text-primary-800">
                                                <i data-lucide="sparkles" class="w-4 h-4 shrink-0"></i><span>{{ __('app.social.new_member_joined') }}</span>
                                            </div>
                                            @break
                                        @case('follow')
                                            <p class="text-gray-600">
                                                <i data-lucide="user-plus" class="w-4 h-4 inline-block align-text-bottom"></i>
                                                {{ __('app.social.started_following') }}
                                                @if($item->subject)<span class="font-semibold text-gray-900">{{ $item->subject->fullName() }}</span>@endif
                                            </p>
                                            @break
                                    @endswitch
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">{{ $trans('public.no_feed') }}</p>
                    @endforelse
                </div>
            </div>

            {{-- Payment links (public only) --}}
            @if($showBookingPanel && $publicPaymentLinks->isNotEmpty())
                <div class="card p-6">
                    <h2 class="font-bold text-gray-900 text-lg mb-4 flex items-center gap-2">
                        <i data-lucide="credit-card" class="w-5 h-5 text-primary-500"></i> {{ $trans('public.payment_links') }}
                    </h2>
                    <div class="space-y-2">
                        @foreach($publicPaymentLinks as $link)
                            <a href="{{ $link->url }}" target="_blank" rel="noopener nofollow"
                               class="flex items-center justify-between gap-2 p-3.5 bg-gray-50 hover:bg-primary-50 rounded-xl text-[15px] transition">
                                <span class="font-semibold text-gray-800">{{ $link->label }}</span>
                                <span class="flex items-center gap-2 shrink-0">
                                    @if($link->price_text)<span class="text-primary-700 font-semibold">{{ $link->price_text }}</span>@endif
                                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-gray-400"></i>
                                </span>
                            </a>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-3">{{ $trans('payment_links.external_disclaimer') }}</p>
                </div>
            @endif

            {{-- Instruments / expertise chips --}}
            @if($profile->primary_instrument || $profile->instruments->isNotEmpty() || !empty($profile->expertise_areas))
                <div class="card p-6">
                    <h2 class="font-bold text-gray-900 text-lg mb-4">{{ $trans('public.teaches') }}</h2>
                    <div class="flex flex-wrap gap-2">
                        @if($profile->primary_instrument)
                            <span class="px-3.5 py-1.5 rounded-full text-sm font-semibold bg-primary-600 text-white">{{ $profile->primary_instrument }}</span>
                        @endif
                        @foreach($profile->instruments as $instrument)
                            <span class="px-3.5 py-1.5 rounded-full text-sm font-semibold bg-primary-50 text-primary-700">{{ $instrument->instrument }}</span>
                        @endforeach
                        @foreach($profile->expertise_areas ?? [] as $area)
                            <span class="px-3.5 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-600">{{ $area }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Login-required popup for following (guests) --}}
            <div x-show="followModal" x-cloak style="display:none"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 @keydown.escape.window="followModal = false">
                <div class="absolute inset-0 bg-black/40" @click="followModal = false"></div>
                <div class="relative card p-6 max-w-sm w-full text-center">
                    <div class="w-12 h-12 rounded-full bg-primary-50 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="user-plus" class="w-6 h-6 text-primary-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg mb-1">{{ $trans('public.follow_teacher') }}</h3>
                    <p class="text-[15px] text-gray-600 mb-5">{{ $trans('public.follow_login_prompt') }}</p>
                    <div class="flex gap-2">
                        <a href="{{ route('login') }}" class="flex-1 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition">{{ $trans('public.login') }}</a>
                        <a href="{{ route('register') }}" class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold rounded-xl transition">{{ $trans('public.register') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.footer')

@livewireScripts
<script>
    document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
    document.addEventListener('livewire:init', () => {
        Livewire.hook('morph.updated', () => window.lucide && lucide.createIcons());
    });
</script>
</body>
</html>
