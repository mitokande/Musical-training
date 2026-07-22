@extends('layouts.standalone')

@section('title', $article->meta_title ?: $article->title)
@section('description', $article->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) ($article->excerpt ?: $article->body)), 160))
@section('og_type', 'article')

@if(! $article->isPublished())
    @section('robots', 'noindex, nofollow')
@endif

@if($article->og_image || $article->featured_image)
    @section('og_image', asset('storage/'.($article->og_image ?: $article->featured_image)))
@endif

@section('head')
    @if($article->published_at)
    <meta property="article:published_time" content="{{ $article->published_at->toIso8601String() }}">
    <meta property="article:modified_time" content="{{ ($article->updated_at ?? $article->published_at)->toIso8601String() }}">
    @endif
@endsection

@section('structured-data')
    @php
        $articleAuthor = $article->author;
        $authorProfileSlug = $articleAuthor?->teacherProfile?->slug;
        $articleImage = $article->og_image ?: $article->featured_image;
        $articleJsonLd = json_encode(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->title,
            'description' => $article->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) ($article->excerpt ?: $article->body)), 160),
            'image' => $articleImage ? asset('storage/'.$articleImage) : null,
            'datePublished' => $article->published_at?->toIso8601String(),
            'dateModified' => ($article->updated_at ?? $article->published_at)?->toIso8601String(),
            'author' => $articleAuthor ? array_filter([
                '@type' => 'Person',
                'name' => $articleAuthor->name,
                'url' => $authorProfileSlug ? route('teachers.show', $authorProfileSlug) : null,
            ]) : null,
            'publisher' => ['@id' => url('/').'#organization'],
            'mainEntityOfPage' => route('articles.show', $article->slug),
        ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $breadcrumbJsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => url('/blog')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $article->title, 'item' => route('articles.show', $article->slug)],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @endphp
    <script type="application/ld+json">{!! $articleJsonLd !!}</script>
    <script type="application/ld+json">{!! $breadcrumbJsonLd !!}</script>
@endsection

@section('content')

@php
    $author = $article->author;
    $teacherSlug = $author?->teacherProfile?->slug;
@endphp

<article class="max-w-3xl mx-auto px-4 sm:px-6 py-10 sm:py-14">

    @if(! $article->isPublished())
        <div class="mb-6 px-4 py-3 bg-yellow-50 border border-yellow-200 rounded-xl flex items-center gap-3">
            <i data-lucide="eye" class="w-5 h-5 text-yellow-600"></i>
            <p class="text-sm text-yellow-700">{{ __('app.articles.preview_notice') }}</p>
        </div>
    @endif

    {{-- Cover image (kapak) --}}
    @if($article->featured_image)
        <img src="{{ asset('storage/' . $article->featured_image) }}"
             alt="{{ $article->title }}"
             class="w-full h-56 sm:h-80 object-cover rounded-3xl shadow-sm mb-8">
    @endif

    {{-- Meta --}}
    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-500 mb-4">
        @if($article->category)
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-primary-50 text-primary-700 font-medium text-xs">{{ $article->category }}</span>
        @endif
        @if($article->published_at)
            <span class="inline-flex items-center gap-1.5"><i data-lucide="calendar" class="w-4 h-4"></i>{{ $article->published_at->format('d.m.Y') }}</span>
        @endif
        @if($article->reading_time)
            <span class="inline-flex items-center gap-1.5"><i data-lucide="clock" class="w-4 h-4"></i>{{ $article->reading_time }} {{ __('app.articles.min_read') }}</span>
        @endif
    </div>

    {{-- Title (single, below the cover) --}}
    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 leading-tight mb-6">{{ $article->title }}</h1>

    {{-- Author --}}
    @if($author)
        <div class="flex items-center gap-3 pb-8 mb-8 border-b border-gray-100">
            <div class="w-11 h-11 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold">
                {{ strtoupper(mb_substr($author->name, 0, 1)) }}
            </div>
            <div>
                @if($teacherSlug)
                    <a href="{{ route('teachers.show', $teacherSlug) }}" class="font-semibold text-gray-900 hover:text-primary-600 transition-colors">{{ $author->fullName() }}</a>
                @else
                    <p class="font-semibold text-gray-900">{{ $author->fullName() }}</p>
                @endif
                <p class="text-sm text-gray-400">{{ __('app.articles.author_label') }}</p>
            </div>
        </div>
    @endif

    {{-- Video --}}
    @if($article->content_type === 'video' && $article->video_url)
        @php
            $vid = null;
            if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([\w-]{11})~', $article->video_url, $m)) {
                $vid = $m[1];
            }
        @endphp
        @if($vid)
            <div class="aspect-video rounded-2xl overflow-hidden mb-8 shadow-sm">
                <iframe src="https://www.youtube.com/embed/{{ $vid }}" class="w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        @else
            <a href="{{ $article->video_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-primary-600 font-semibold mb-8"><i data-lucide="play-circle" class="w-5 h-5"></i>{{ __('app.articles.watch_video') }}</a>
        @endif
    @endif

    {{-- Audio --}}
    @if($article->content_type === 'audio' && $article->audio_file)
        <audio controls src="{{ asset('storage/' . $article->audio_file) }}" class="w-full mb-8"></audio>
    @endif

    {{-- Document --}}
    @if(in_array($article->content_type, ['document', 'sheet_music']) && $article->document_file)
        <a href="{{ asset('storage/' . $article->document_file) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-5 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition font-medium mb-8">
            <i data-lucide="download" class="w-5 h-5"></i>{{ __('app.articles.download_document') }}
        </a>
    @endif

    {{-- Body --}}
    @if($article->body)
        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed text-[17px]">
            {!! nl2br(e($article->body)) !!}
        </div>
    @endif

    {{-- Tags --}}
    @if(! empty($article->tags))
        <div class="flex flex-wrap gap-2 mt-10 pt-8 border-t border-gray-100">
            @foreach($article->tags as $tag)
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">#{{ $tag }}</span>
            @endforeach
        </div>
    @endif

    {{-- Owner / admin edit shortcut --}}
    @if($isOwner || $isAdmin)
        <div class="mt-10">
            <a href="{{ route('articles.edit', $article) }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-primary-600 transition">
                <i data-lucide="pencil" class="w-4 h-4"></i>{{ __('app.articles.edit_this') }}
            </a>
        </div>
    @endif

</article>

@endsection
