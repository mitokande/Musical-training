@extends('teacher.layouts.crm')

@section('title', __('teacher.content.title'))

@section('content')
@php $c = 'teacher.content'; @endphp

@if (session('status') && in_array(session('status'), ['media-saved', 'media-deleted', 'video-saved', 'video-deleted']))
    <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
        <p class="text-sm text-green-700">{{ __('teacher.profile.saved') }}</p>
    </div>
@endif
@if ($errors->any())
    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-xl">
        @foreach ($errors->all() as $error)<p class="text-sm text-red-600">{{ $error }}</p>@endforeach
    </div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ __($c.'.title') }}</h1>
        <p class="text-gray-500 text-[15px] mt-1">{{ __($c.'.subtitle') }}</p>
    </div>
    <a href="{{ route('articles.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition">
        <i data-lucide="plus" class="w-4 h-4"></i> {{ __($c.'.new_article') }}
    </a>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Articles --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-bold text-gray-900">{{ __($c.'.articles') }} ({{ $articles->count() }})</h2>
            </div>
            @if($articles->isEmpty())
                <div class="p-8 text-center text-[15px] text-gray-400">{{ __($c.'.no_articles') }}</div>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($articles as $article)
                        <li class="flex items-center gap-4 px-5 py-4">
                            <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center shrink-0">
                                <i data-lucide="newspaper" class="w-5 h-5 text-primary-600"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[15px] font-semibold text-gray-900 truncate">{{ $article->title }}</p>
                                <p class="text-sm text-gray-400">
                                    {{ $article->created_at->format('M j, Y') }}
                                    · <span class="font-medium {{ $article->status === 'published' ? 'text-green-600' : 'text-gray-500' }}">{{ ucfirst($article->status) }}</span>
                                </p>
                            </div>
                            <a href="{{ route('articles.edit', $article) }}" class="px-3 py-2 text-sm font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition">
                                {{ __($c.'.edit') }}
                            </a>
                            <form method="POST" action="{{ route('articles.destroy', $article) }}" onsubmit="return confirm(@js(__($c.'.delete_article_confirm')))">
                                @csrf @method('DELETE')
                                <button class="p-2 text-gray-400 hover:text-red-600 transition"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Videos --}}
        <div class="card p-5 sm:p-6">
            <h2 class="font-bold text-gray-900 mb-4">{{ __($c.'.videos') }} ({{ $videos->count() }})</h2>
            @if($hasProfile)
                <form method="POST" action="{{ route('teacher.videos.store') }}" class="flex flex-col sm:flex-row gap-2 mb-4">
                    @csrf
                    <input type="text" name="title" required maxlength="255" placeholder="{{ __($c.'.video_title') }}" class="flex-1 rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-2.5 text-[15px]">
                    <input type="url" name="url" required placeholder="https://www.youtube.com/watch?v=…" class="flex-1 rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-2.5 text-[15px]">
                    <button class="px-5 py-2.5 bg-gray-800 hover:bg-gray-900 text-white text-[15px] font-semibold rounded-xl transition">{{ __($c.'.add') }}</button>
                </form>
            @endif
            @if($videos->isEmpty())
                <p class="text-[15px] text-gray-400">{{ __($c.'.no_videos') }}</p>
            @else
                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach($videos as $video)
                        <div class="rounded-2xl overflow-hidden border border-gray-100">
                            <div class="aspect-video">
                                <iframe src="{{ $video->embedUrl() }}" title="{{ $video->title }}" class="w-full h-full" frameborder="0" loading="lazy" allowfullscreen></iframe>
                            </div>
                            <div class="p-3 flex items-center gap-2">
                                <p class="flex-1 text-sm font-semibold text-gray-800 truncate">{{ $video->title }}</p>
                                <form method="POST" action="{{ route('teacher.videos.destroy', $video) }}">
                                    @csrf @method('DELETE')
                                    <button class="p-1.5 text-gray-400 hover:text-red-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        {{-- Document upload (PDF) --}}
        <div class="card p-5 sm:p-6">
            <h2 class="font-bold text-gray-900 mb-1">{{ __($c.'.documents') }}</h2>
            <p class="text-sm text-gray-500 mb-4">{{ __($c.'.documents_hint') }}</p>
            @if($hasProfile)
                <form method="POST" action="{{ route('teacher.media.store') }}" enctype="multipart/form-data" class="space-y-3 mb-5">
                    @csrf
                    <input type="hidden" name="kind" value="document">
                    <input type="text" name="title" maxlength="255" placeholder="{{ __($c.'.document_title') }}" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-2.5 text-[15px]">
                    <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full text-sm text-gray-600 file:mr-3 file:px-4 file:py-2 file:rounded-xl file:border-0 file:bg-primary-50 file:text-primary-700 file:font-semibold file:text-sm">
                    <select name="visibility" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-2.5 text-[15px]">
                        <option value="public">{{ __($c.'.visibility_public') }}</option>
                        <option value="private">{{ __($c.'.visibility_private') }}</option>
                    </select>
                    <button class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white text-[15px] font-semibold rounded-xl transition">
                        {{ __($c.'.upload_document') }}
                    </button>
                </form>
            @endif
            <ul class="space-y-2">
                @forelse($documents as $doc)
                    <li class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                        <i data-lucide="file-text" class="w-5 h-5 text-primary-500 shrink-0"></i>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ $doc->title ?: $doc->original_name }}</p>
                            <p class="text-xs text-gray-400">{{ strtoupper(pathinfo($doc->original_name, PATHINFO_EXTENSION)) }} · {{ number_format($doc->size / 1024) }} KB · {{ __($c.'.visibility_'.$doc->visibility) }}</p>
                        </div>
                        @if($doc->disk === 'local')
                            <a href="{{ route('teacher.media.download', $doc) }}" class="p-1.5 text-gray-400 hover:text-primary-600"><i data-lucide="download" class="w-4 h-4"></i></a>
                        @else
                            <a href="{{ $doc->publicUrl() }}" target="_blank" rel="noopener" class="p-1.5 text-gray-400 hover:text-primary-600"><i data-lucide="external-link" class="w-4 h-4"></i></a>
                        @endif
                        <form method="POST" action="{{ route('teacher.media.destroy', $doc) }}">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-gray-400 hover:text-red-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </form>
                    </li>
                @empty
                    <li class="text-sm text-gray-400">{{ __($c.'.no_documents') }}</li>
                @endforelse
            </ul>
        </div>

        {{-- Photos --}}
        <div class="card p-5 sm:p-6">
            <h2 class="font-bold text-gray-900 mb-1">{{ __($c.'.photos') }}</h2>
            <p class="text-sm text-gray-500 mb-4">{{ __($c.'.photos_hint') }}</p>
            @if($hasProfile)
                <form method="POST" action="{{ route('teacher.media.store') }}" enctype="multipart/form-data" class="space-y-3 mb-5">
                    @csrf
                    <input type="hidden" name="kind" value="photo">
                    <input type="hidden" name="visibility" value="public">
                    <input type="file" name="file" required accept=".jpg,.jpeg,.png,.webp" class="w-full text-sm text-gray-600 file:mr-3 file:px-4 file:py-2 file:rounded-xl file:border-0 file:bg-primary-50 file:text-primary-700 file:font-semibold file:text-sm">
                    <button class="w-full py-2.5 bg-gray-800 hover:bg-gray-900 text-white text-[15px] font-semibold rounded-xl transition">
                        {{ __($c.'.upload_photo') }}
                    </button>
                </form>
            @endif
            <div class="grid grid-cols-3 gap-2">
                @foreach($photos as $photo)
                    <div class="relative group">
                        <img src="{{ $photo->publicUrl() }}" alt="{{ $photo->title }}" class="w-full h-20 object-cover rounded-xl">
                        <form method="POST" action="{{ route('teacher.media.destroy', $photo) }}" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition">
                            @csrf @method('DELETE')
                            <button class="p-1 bg-white/90 rounded-lg text-red-600"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
