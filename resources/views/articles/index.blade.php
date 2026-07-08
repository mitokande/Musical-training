@extends('teacher.layouts.crm')

@section('title', __('app.articles.page_title'))

@section('content')

<div class="max-w-5xl">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('app.articles.heading') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('app.articles.subtitle') }}</p>
        </div>
        <a href="{{ route('articles.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium">
            <i data-lucide="plus" class="w-4 h-4"></i>
            {{ __('app.articles.new_post') }}
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    @if($articles->count() > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="divide-y divide-gray-100">
                @foreach($articles as $article)
                    <div class="p-5 flex items-start justify-between gap-4 hover:bg-gray-50 transition">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-semibold text-gray-900 truncate">{{ $article->title }}</h3>
                                @php
                                    $statusColors = [
                                        'draft' => 'bg-gray-100 text-gray-600',
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'published' => 'bg-green-100 text-green-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                    ];
                                    $statusLabels = [
                                        'draft' => __('app.articles.status_draft'),
                                        'pending' => __('app.articles.status_pending'),
                                        'published' => __('app.articles.status_published'),
                                        'rejected' => __('app.articles.status_rejected'),
                                    ];
                                    $typeLabels = [
                                        'article' => __('app.articles.type_article'),
                                        'video' => __('app.articles.type_video'),
                                        'document' => __('app.articles.type_document'),
                                        'audio' => __('app.articles.type_audio'),
                                        'sheet_music' => __('app.articles.type_sheet_music'),
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$article->status] }}">
                                    {{ $statusLabels[$article->status] }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-600">
                                    {{ $typeLabels[$article->content_type] ?? $article->content_type }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500">{{ $article->created_at->format('d.m.Y H:i') }}</p>
                            @if($article->status === 'rejected' && $article->admin_note)
                                <div class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <p class="text-xs font-medium text-red-700 mb-1">{{ __('app.articles.admin_note_label') }}</p>
                                    <p class="text-sm text-red-600">{{ $article->admin_note }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <a href="{{ route('articles.edit', $article) }}" class="p-2 text-gray-500 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                            <form method="POST" action="{{ route('articles.destroy', $article) }}" onsubmit="return confirm('{{ __('app.articles.delete_confirm') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="mt-6">{{ $articles->links() }}</div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            <i data-lucide="file-text" class="w-16 h-16 mx-auto text-gray-300 mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('app.articles.empty_title') }}</h3>
            <p class="text-sm text-gray-500 mb-6">{{ __('app.articles.empty_desc') }}</p>
            <a href="{{ route('articles.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                <i data-lucide="plus" class="w-4 h-4"></i>
                {{ __('app.articles.create_content') }}
            </a>
        </div>
    @endif
</div>

@endsection
