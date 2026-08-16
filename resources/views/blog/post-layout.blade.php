{{--
    Shared chrome for every /blog/{slug} post: <head>, structured data, hero,
    byline, table of contents, author box and closing CTA. A post blade only
    supplies its body:

        @extends('blog.post-layout')
        @section('article')  … @endsection

    Data comes from BlogPostController — $post (config('blog.posts') entry),
    $t (translator bound to the post's blog.* section) and $author. Nothing here
    is post-specific, so the next article reuses this file untouched.
--}}
@extends('layouts.standalone')

@section('title', $t('meta_title'))
@section('description', $t('meta_description'))
@section('og_type', 'article')

@php
    $blogAuthorName = $author ? trim($author->user->name.' '.($author->user->surname ?? '')) : null;
    $blogAuthorUrl = $author ? url('/teachers/'.$author->slug) : null;

    // Author portrait: the account avatar when there is one, otherwise the first
    // public photo on the teacher profile. Both are optional — the box falls
    // back to an initial rather than a broken image.
    $blogAuthorPhoto = null;
    if ($author) {
        if ($author->user->hasAvatar()) {
            $blogAuthorPhoto = $author->user->avatar;
        } elseif ($photo = $author->media->first(fn ($m) => $m->kind === 'photo' && $m->visibility === 'public')) {
            $blogAuthorPhoto = asset($photo->path);
        }
    }

    $blogPublished = \Illuminate\Support\Carbon::parse($post['published_at']);
    $blogUpdated = \Illuminate\Support\Carbon::parse($post['updated_at'] ?? $post['published_at']);

    $blogCategoryLabel = __('pages.articles.cat_'.($post['category'] ?? 'theory'));
@endphp

@section('structured-data')
    @php
        // Built inside @php so Blade does not compile the literal "@context" /
        // "@type" keys as directives and corrupt the JSON.
        $blogPostingJsonLd = json_encode(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            '@id' => $seoCanonical.'#article',
            'headline' => $t('title'),
            'description' => $t('meta_description'),
            'inLanguage' => $seoHtmlLang,
            'datePublished' => $blogPublished->toDateString(),
            'dateModified' => $blogUpdated->toDateString(),
            'image' => asset('images/og-image.png'),
            'wordCount' => null,
            'articleSection' => $blogCategoryLabel,
            'author' => $blogAuthorName
                ? array_filter([
                    '@type' => 'Person',
                    'name' => $blogAuthorName,
                    'url' => $blogAuthorUrl,
                    'image' => $blogAuthorPhoto,
                ])
                : ['@id' => url('/').'#organization'],
            'publisher' => ['@id' => url('/').'#organization'],
            'isPartOf' => ['@id' => $seoCanonical.'#webpage'],
            'mainEntityOfPage' => $seoCanonical,
        ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $blogBreadcrumbJsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Harmoniva', 'item' => locale_url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => __('pages.articles.hero_badge'), 'item' => locale_url('/blog')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $t('title'), 'item' => $seoCanonical],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // FAQPage is only emitted when the post actually renders the same
        // questions on the page — schema that describes invisible content is a
        // structured-data violation, not a shortcut to a rich result.
        $blogFaqJsonLd = null;
        if (($post['faq_count'] ?? 0) > 0) {
            $blogFaqJsonLd = json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'inLanguage' => $seoHtmlLang,
                'mainEntity' => array_map(fn ($i) => [
                    '@type' => 'Question',
                    'name' => $t('faq_'.$i.'_q'),
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $t('faq_'.$i.'_a')],
                ], range(1, $post['faq_count'])),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
    @endphp
    <script type="application/ld+json">{!! $blogPostingJsonLd !!}</script>
    <script type="application/ld+json">{!! $blogBreadcrumbJsonLd !!}</script>
    @if ($blogFaqJsonLd)
    <script type="application/ld+json">{!! $blogFaqJsonLd !!}</script>
    @endif
@endsection

@section('head')
    {{-- Piano samples for the in-article exercise boxes. Deferred: the sampler
         itself is only built when a reader actually presses play. --}}
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/tone/15.3.5/Tone.js" integrity="sha512-F1myjNkIKU5XJtOs1HXRo/zOjiUsABgFEEGKLx/riwK82jRThZFebEnfF2HWo9eeC+iC1Nwwnn9Vj6OGq+r7rQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {{-- Notation for those boxes, same build as the practice screens. --}}
    <script defer src="https://cdn.jsdelivr.net/npm/vexflow@4.2.2/build/cjs/vexflow.js"></script>

    <style>
        /* Article typography. Deliberately hand-written rather than Tailwind
           utilities: the compiled marketing CSS is purged against the existing
           blades, so a class that appears for the first time here would not
           exist in the bundle. */
        .hv-article { color: #374151; }
        .hv-article > * + * { margin-top: 18px; }
        .hv-article p { font-size: 17px; line-height: 1.75; }
        /* Numbered chapter heading — the chip pattern from /ear-training-guide. */
        .hv-article .hv-h2 {
            display: flex; align-items: center; gap: 14px;
            font-size: 28px; font-weight: 700; color: #111827;
            line-height: 1.25; margin-top: 60px; scroll-margin-top: 88px;
        }
        .hv-h2-num {
            width: 42px; height: 42px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            border-radius: 14px; font-size: 18px; font-weight: 800; color: #fff;
            background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%);
            box-shadow: 0 8px 18px -10px rgba(124, 58, 237, .95);
        }
        .hv-article h3 {
            font-size: 20px; font-weight: 700; color: #1f2937;
            line-height: 1.3; margin-top: 34px;
        }
        /* Table of contents */
        .hv-toc-item {
            display: flex; align-items: center; gap: 12px;
            padding: 13px 14px; border-radius: 14px;
            background: #fbfaff; border: 1px solid #f1e9fd;
            text-decoration: none; transition: all .18s ease;
        }
        .hv-toc-item:hover { background: #f5f0ff; border-color: #d8b4fe; transform: translateY(-1px); }
        .hv-toc-num {
            width: 28px; height: 28px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            border-radius: 9px; background: #ede9fe; color: #6d28d9;
            font-size: 12px; font-weight: 800;
        }
        .hv-toc-item:hover .hv-toc-num { background: #7c3aed; color: #fff; }
        .hv-toc-text { font-size: 14px; font-weight: 600; color: #4b5563; line-height: 1.3; }
        .hv-toc-item:hover .hv-toc-text { color: #6d28d9; }
        /* Illustration cards. Capped at 300px tall, which is really a cap on
           width: the drawings are ~360×140 viewBoxes, so letting one stretch to
           the full 720px column would scale every label and notehead up with it
           and swamp the prose. max-width keeps them at their drawn size. */
        .hv-figure {
            margin: 28px 0; padding: 14px 14px 2px;
            border: 1px solid; border-radius: 18px;
            box-shadow: 0 10px 28px -22px rgba(76, 29, 149, .8);
        }
        /* 14 pad + 186 art + 8 + caption ≈ 254px, and a three-line caption in a
           longer language still lands under 300. */
        .hv-figure-art { max-height: 230px; overflow-x: auto; }
        .hv-figure svg {
            display: block; margin: 0 auto;
            width: 100%; max-width: 430px; height: auto; min-width: 300px;
        }
        .hv-figure figcaption {
            padding: 8px 6px 12px; text-align: center;
            font-size: 12.5px; line-height: 1.45; color: #8b93a1;
        }
        /* Key takeaway */
        .hv-takeaway {
            display: flex; gap: 14px; align-items: flex-start;
            margin: 26px 0; padding: 18px 20px;
            background: #faf5ff; border-left: 4px solid #9333ea;
            border-radius: 0 16px 16px 0;
        }
        .hv-takeaway-icon {
            width: 34px; height: 34px; flex-shrink: 0; margin-top: 1px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 11px; background: #ede9fe; color: #7c3aed;
        }
        .hv-takeaway-label {
            font-size: 11px; font-weight: 800; letter-spacing: .09em;
            text-transform: uppercase; color: #7c3aed; margin-bottom: 3px;
        }
        .hv-takeaway-text { font-size: 15.5px; line-height: 1.6; color: #4c1d95; }
        .hv-article strong { color: #111827; font-weight: 700; }
        .hv-article ul, .hv-article ol { padding-left: 4px; }
        .hv-article li {
            position: relative; padding-left: 22px; font-size: 17px; line-height: 1.7;
        }
        .hv-article li + li { margin-top: 7px; }
        .hv-article ul li::before {
            content: ''; position: absolute; left: 4px; top: 11px;
            width: 7px; height: 7px; border-radius: 999px; background: #c084fc;
        }
        .hv-article ol { counter-reset: hv-step; }
        .hv-article ol li { counter-increment: hv-step; padding-left: 32px; }
        .hv-article ol li::before {
            content: counter(hv-step);
            position: absolute; left: 0; top: 2px;
            width: 22px; height: 22px; border-radius: 8px;
            background: #f3e8ff; color: #7c3aed;
            font-size: 12px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        /* Worked examples and counted-out answers, set apart from prose. */
        .hv-callout {
            background: #fff; border: 1px solid #eee7f7; border-left: 4px solid #a855f7;
            border-radius: 0 14px 14px 0; padding: 14px 18px;
            font-size: 17px; color: #4b5563;
        }
        .hv-callout strong { color: #6d28d9; }
        .hv-mono {
            display: inline-block; padding: 6px 14px; border-radius: 10px;
            background: #f5f3ff; color: #5b21b6;
            font-weight: 700; letter-spacing: .04em;
        }
        /* Interval table — scrolls inside its own box so the page body never
           scrolls sideways on a phone. */
        .hv-table-wrap {
            overflow-x: auto; background: #fff; border: 1px solid #ede9fe;
            border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.04);
        }
        .hv-table { width: 100%; border-collapse: collapse; min-width: 520px; }
        .hv-table caption { caption-side: bottom; padding: 10px 16px; font-size: 12px; color: #9ca3af; text-align: left; }
        .hv-table th {
            text-align: left; padding: 12px 16px;
            font-size: 12px; font-weight: 700; color: #6b21a8;
            text-transform: uppercase; letter-spacing: .06em;
            background: #faf5ff; border-bottom: 1px solid #ede9fe; white-space: nowrap;
        }
        .hv-table th.is-num, .hv-table td.is-num { text-align: right; }
        .hv-table td { padding: 11px 16px; font-size: 15px; color: #374151; border-bottom: 1px solid #f5f3ff; }
        .hv-table tbody tr:last-child td { border-bottom: none; }
        .hv-table tbody tr:hover td { background: #fdfaff; }
        .hv-table .is-abbrev { font-weight: 700; color: #7c3aed; white-space: nowrap; }
        .hv-table .is-name { font-weight: 600; color: #111827; white-space: nowrap; }
        /* Byline sits between the hero subtitle and the article, in the serif
           the rest of the site uses for editorial voice. */
        .hv-byline { font-family: 'Instrument Serif', Georgia, serif; }
        /* Author card */
        .hv-author {
            background: #fff; border: 1px solid #ede9fe; border-radius: 22px;
            overflow: hidden; box-shadow: 0 16px 40px -30px rgba(76, 29, 149, .9);
        }
        .hv-author-band {
            height: 42px; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
            color: #fff; font-size: 12px; font-weight: 800;
            letter-spacing: .14em; text-transform: uppercase;
        }
        .hv-author-body { display: flex; flex-direction: column; gap: 22px; padding: 26px 26px 28px; }
        .hv-author-photo {
            width: 116px; height: 116px; border-radius: 20px;
            object-fit: cover; object-position: top;
            box-shadow: 0 0 0 4px #f5f3ff, 0 10px 24px -14px rgba(76, 29, 149, .8);
        }
        .hv-author-initial {
            display: flex; align-items: center; justify-content: center;
            background: #ede9fe; color: #6d28d9; font-size: 40px; font-weight: 800;
        }
        .hv-author-name { font-size: 26px; color: #111827; text-decoration: none; transition: color .15s ease; }
        .hv-author-name:hover { color: #7c3aed; }
        .hv-author-bio { margin-top: 10px; font-size: 15px; line-height: 1.65; color: #4b5563; }
        .hv-author-link {
            display: inline-flex; align-items: center; gap: 6px; margin-top: 16px;
            font-size: 14px; font-weight: 700; color: #7c3aed; text-decoration: none;
        }
        .hv-author-link:hover { color: #5b21b6; }
        @media (min-width: 640px) {
            .hv-author-body { flex-direction: row; padding: 30px; }
        }
        @media (max-width: 640px) {
            .hv-article p, .hv-article li { font-size: 16px; }
            .hv-article h2 { font-size: 24px; }
        }
    </style>
@endsection

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-purple-700 via-purple-600 to-purple-800 text-white py-16 px-4">
    <div class="max-w-3xl mx-auto text-center reveal">
        <div class="flex flex-wrap items-center justify-center gap-2 mb-5 text-sm">
            <a href="{{ locale_url('/blog') }}" class="inline-flex items-center gap-1.5 bg-white/10 hover:bg-white/20 transition-colors px-3.5 py-1.5 rounded-full font-medium">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                {{ __('blog.ui.back_to_blog') }}
            </a>
            <span class="inline-flex items-center gap-1.5 bg-orange-500/90 px-3.5 py-1.5 rounded-full font-semibold">
                {{ $blogCategoryLabel }}
            </span>
        </div>

        <h1 class="text-3xl md:text-[2.75rem] font-bold leading-tight mb-5">{{ $t('title') }}</h1>
        <p class="text-purple-200 text-lg max-w-2xl mx-auto leading-relaxed">{{ $t('meta_description') }}</p>

        {{-- Byline --}}
        <div class="mt-8 flex flex-col items-center gap-2">
            @if ($blogAuthorName)
                <p class="hv-byline text-xl md:text-2xl text-white">
                    <span class="text-purple-300 italic">{{ __('blog.ui.by') }}</span>
                    @if ($blogAuthorUrl)
                        <a href="{{ $blogAuthorUrl }}" class="underline decoration-purple-400/60 underline-offset-4 hover:decoration-white transition-colors">{{ $blogAuthorName }}</a>
                    @else
                        {{ $blogAuthorName }}
                    @endif
                </p>
            @endif
            <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1 text-sm text-purple-200">
                <time datetime="{{ $blogPublished->toDateString() }}" class="flex items-center gap-1.5">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    {{ $blogPublished->translatedFormat('j F Y') }}
                </time>
                <span class="flex items-center gap-1.5">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    {{ __('blog.ui.read_time', ['min' => $post['reading_time']]) }}
                </span>
            </div>
        </div>
    </div>
</section>

{{-- Table of contents --}}
@if (! empty($post['toc']))
<section class="bg-white border-b border-gray-100 py-12 sm:py-14 px-4">
    <div class="max-w-4xl mx-auto reveal">
        <div class="flex items-center gap-3 mb-7">
            <span class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="list" class="w-5 h-5 text-purple-600"></i>
            </span>
            <div>
                <p class="text-lg font-bold text-gray-900 leading-tight">{{ __('blog.ui.toc_title') }}</p>
                <p class="text-sm text-gray-400">{{ __('blog.ui.toc_subtitle', ['count' => count($post['toc'])]) }}</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($post['toc'] as $i => $tocKey)
            <a href="#s{{ $i + 1 }}" class="hv-toc-item">
                <span class="hv-toc-num">{{ $i + 1 }}</span>
                <span class="hv-toc-text">{{ $t($tocKey) }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Article body --}}
<div class="bg-[#FAF7F2] py-14 px-4">
    <article class="max-w-3xl mx-auto hv-article">
        @yield('article')
    </article>

    {{-- About the author. The heading lives inside the card as a solid band
         rather than as a label floating above it, and the card keeps clear air
         above and below so it never reads as part of the last paragraph. --}}
    @if ($blogAuthorName)
    <aside class="max-w-3xl mx-auto reveal" style="margin-top:72px;margin-bottom:24px;">
        <div class="hv-author">
            <div class="hv-author-band">{{ __('blog.ui.author_box_title') }}</div>
            <div class="hv-author-body">
                <div class="shrink-0 mx-auto sm:mx-0">
                    @if ($blogAuthorPhoto)
                        <a href="{{ $blogAuthorUrl }}">
                            <img src="{{ $blogAuthorPhoto }}" alt="{{ $blogAuthorName }}" loading="lazy" class="hv-author-photo">
                        </a>
                    @else
                        <div class="hv-author-photo hv-author-initial">{{ mb_strtoupper(mb_substr($blogAuthorName, 0, 1)) }}</div>
                    @endif
                </div>
                <div class="flex-1 min-w-0 text-center sm:text-left">
                    <a href="{{ $blogAuthorUrl }}" class="hv-byline hv-author-name">{{ $blogAuthorName }}</a>
                    {{-- No profile fields here on purpose. Teacher profiles are
                         written in whatever language the teacher uses (this one
                         is Turkish), so pulling headline/city in would drop a
                         foreign-language line into every translation of the
                         post. The bio below is part of the post's own section
                         and therefore travels with it. --}}
                    <p class="hv-author-bio">{{ $t('author_bio') }}</p>
                    <a href="{{ $blogAuthorUrl }}" class="hv-author-link">
                        {{ __('blog.ui.author_view_profile') }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </div>
    </aside>
    @endif
</div>

{{-- Closing CTA --}}
<section class="bg-gradient-to-br from-purple-600 to-purple-800 py-16 px-4">
    <div class="max-w-2xl mx-auto text-center reveal">
        <h2 class="text-3xl font-bold text-white mb-3">{{ $t('cta_title') }}</h2>
        <p class="text-purple-200 text-lg mb-8">{{ $t('cta_body') }}</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold px-8 py-4 rounded-xl transition-colors shadow-lg">
                <i data-lucide="play-circle" class="w-5 h-5"></i>
                {{ $t('cta_primary') }}
            </a>
            <a href="{{ locale_url('/learn') }}" class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white font-semibold px-8 py-4 rounded-xl transition-colors">
                <i data-lucide="map" class="w-5 h-5"></i>
                {{ $t('cta_secondary') }}
            </a>
        </div>
    </div>
</section>

@endsection
