@extends('admin.layouts.admin')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <a href="{{ route('admin.articles.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-primary-600 transition mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Icerik Listesine Don
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $article->title }}</h1>
    </div>
</div>

{{-- Meta Bilgiler --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-100 p-4">
        <p class="text-xs text-gray-500 mb-1">Yazar</p>
        <p class="text-sm font-medium text-gray-900">{{ $article->author->name }}</p>
        <p class="text-xs text-gray-500">{{ ucfirst($article->author->role) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-4">
        <p class="text-xs text-gray-500 mb-1">Icerik Turu</p>
        <p class="text-sm font-medium text-gray-900">{{ ucfirst($article->content_type) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-4">
        <p class="text-xs text-gray-500 mb-1">Gorunurluk</p>
        <p class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $article->visibility)) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-4">
        <p class="text-xs text-gray-500 mb-1">Durum</p>
        @php
            $statusColors = [
                'draft' => 'bg-gray-100 text-gray-600',
                'pending' => 'bg-yellow-100 text-yellow-700',
                'published' => 'bg-green-100 text-green-700',
                'rejected' => 'bg-red-100 text-red-700',
            ];
            $statusLabels = [
                'draft' => 'Taslak',
                'pending' => 'Onay Bekliyor',
                'published' => 'Yayinda',
                'rejected' => 'Reddedildi',
            ];
        @endphp
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$article->status] }}">
            {{ $statusLabels[$article->status] }}
        </span>
    </div>
</div>

{{-- Icerik --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
    @if($article->excerpt)
        <div class="mb-4 p-4 bg-gray-50 rounded-xl">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Ozet</p>
            <p class="text-sm text-gray-700">{{ $article->excerpt }}</p>
        </div>
    @endif

    @if($article->body)
        <div class="prose prose-sm max-w-none text-gray-700">
            {!! nl2br(e($article->body)) !!}
        </div>
    @endif

    @if($article->video_url)
        <div class="mt-4 p-4 bg-blue-50 rounded-xl">
            <p class="text-xs font-semibold text-blue-600 uppercase mb-1">Video URL</p>
            <a href="{{ $article->video_url }}" target="_blank" class="text-sm text-blue-700 hover:underline">{{ $article->video_url }}</a>
        </div>
    @endif

    @if($article->audio_file)
        <div class="mt-4 p-4 bg-purple-50 rounded-xl">
            <p class="text-xs font-semibold text-purple-600 uppercase mb-1">Ses Dosyasi</p>
            <audio controls class="w-full mt-1">
                <source src="{{ asset('storage/' . $article->audio_file) }}">
            </audio>
        </div>
    @endif

    @if($article->document_file)
        <div class="mt-4 p-4 bg-orange-50 rounded-xl">
            <p class="text-xs font-semibold text-orange-600 uppercase mb-1">Dokuman</p>
            <a href="{{ asset('storage/' . $article->document_file) }}" target="_blank" class="text-sm text-orange-700 hover:underline flex items-center gap-1.5">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                {{ basename($article->document_file) }}
            </a>
        </div>
    @endif

    @if($article->featured_image)
        <div class="mt-4">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Kapak Gorseli</p>
            <img src="{{ asset('storage/' . $article->featured_image) }}" alt="" class="rounded-xl max-h-64 object-cover">
        </div>
    @endif

    @if($article->tags && count($article->tags) > 0)
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach($article->tags as $tag)
                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">{{ $tag }}</span>
            @endforeach
        </div>
    @endif
</div>

{{-- Onay/Red Islemleri --}}
@if($article->status === 'pending')
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" x-data="{ showReject: false }">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Islem</h2>
        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('admin.articles.approve', $article) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Onayla ve Yayinla
                </button>
            </form>
            <button @click="showReject = !showReject" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium">
                <i data-lucide="x" class="w-4 h-4"></i>
                Reddet
            </button>
        </div>

        <div x-show="showReject" x-cloak class="mt-4 p-4 bg-red-50 rounded-xl border border-red-200">
            <form method="POST" action="{{ route('admin.articles.reject', $article) }}">
                @csrf
                <label class="block text-sm font-medium text-red-700 mb-2">Red Nedeni *</label>
                <textarea name="admin_note" rows="3" required placeholder="Icerigin neden reddedildigini aciklayin..." class="w-full px-4 py-2.5 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"></textarea>
                <div class="mt-3 flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium">
                        Reddet
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

@if($article->status === 'rejected' && $article->admin_note)
    <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
        <p class="text-sm font-medium text-red-700 mb-1">Red Nedeni:</p>
        <p class="text-sm text-red-600">{{ $article->admin_note }}</p>
    </div>
@endif
@endsection
