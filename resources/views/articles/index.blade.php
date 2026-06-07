<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Blog Yazılarım - {{ config('app.name', 'Harmoniva') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: { 50:'#faf5ff',100:'#f3e8ff',200:'#e9d5ff',300:'#d8b4fe',400:'#c084fc',500:'#a855f7',600:'#9333ea',700:'#7c3aed',800:'#6b21a8',900:'#581c87' },
                        accent: { 400:'#fb923c',500:'#f97316',600:'#ea580c' }
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans bg-gray-50 min-h-screen">

@include('partials.navbar', ['active' => 'articles'])

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Blog Yazılarım</h1>
            <p class="text-sm text-gray-500 mt-1">Blog yazısı, video, ders notu ve nota paylaşımlarınız.</p>
        </div>
        <a href="{{ route('articles.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Yeni Blog Yazısı
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
                                        'draft' => 'Taslak',
                                        'pending' => 'Onay Bekliyor',
                                        'published' => 'Yayinda',
                                        'rejected' => 'Reddedildi',
                                    ];
                                    $typeLabels = [
                                        'article' => 'Makale',
                                        'video' => 'Video',
                                        'document' => 'Dokuman',
                                        'audio' => 'Ses',
                                        'sheet_music' => 'Nota',
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
                                    <p class="text-xs font-medium text-red-700 mb-1">Admin Notu:</p>
                                    <p class="text-sm text-red-600">{{ $article->admin_note }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <a href="{{ route('articles.edit', $article) }}" class="p-2 text-gray-500 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                            <form method="POST" action="{{ route('articles.destroy', $article) }}" onsubmit="return confirm('Bu icerigi silmek istediginize emin misiniz?')">
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
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Henuz icerik yok</h3>
            <p class="text-sm text-gray-500 mb-6">Ilk iceriginizi olusturmaya baslayin.</p>
            <a href="{{ route('articles.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Yeni Icerik Olustur
            </a>
        </div>
    @endif
</div>

<script>lucide.createIcons();</script>
</body>
</html>
