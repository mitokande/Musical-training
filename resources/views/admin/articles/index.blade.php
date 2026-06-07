@extends('admin.layouts.admin')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Icerik Yonetimi</h1>
    <p class="text-sm text-gray-500 mt-1">Ogretmen ve okul iceriklerini yonetin, onaylayin veya reddedin.</p>
</div>

{{-- Onay Bekleyenler --}}
@if($pendingArticles->count() > 0)
    <div class="mb-8">
        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <div class="w-6 h-6 rounded-full bg-yellow-100 flex items-center justify-center">
                <i data-lucide="clock" class="w-3.5 h-3.5 text-yellow-600"></i>
            </div>
            Onay Bekleyenler ({{ $pendingArticles->count() }})
        </h2>
        <div class="bg-white rounded-2xl shadow-sm border border-yellow-200 overflow-hidden">
            <div class="divide-y divide-gray-100">
                @foreach($pendingArticles as $article)
                    <div class="p-5 flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900">{{ $article->title }}</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                <span class="font-medium">{{ $article->author->name }}</span> &middot;
                                {{ ucfirst($article->content_type) }} &middot;
                                {{ $article->created_at->format('d.m.Y H:i') }}
                            </p>
                            @if($article->excerpt)
                                <p class="text-sm text-gray-600 mt-2">{{ Str::limit($article->excerpt, 120) }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <a href="{{ route('admin.articles.show', $article) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                Incele
                            </a>
                            <form method="POST" action="{{ route('admin.articles.approve', $article) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    Onayla
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

{{-- Tum Icerikler --}}
<h2 class="text-lg font-bold text-gray-900 mb-4">Tum Icerikler</h2>
@if($allArticles->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Baslik</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Yazar</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Tur</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Durum</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Tarih</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Islem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($allArticles as $article)
                    @php
                        $statusColors = [
                            'draft' => 'bg-gray-100 text-gray-600',
                            'pending' => 'bg-yellow-100 text-yellow-700',
                            'published' => 'bg-green-100 text-green-700',
                            'rejected' => 'bg-red-100 text-red-700',
                        ];
                        $statusLabels = [
                            'draft' => 'Taslak',
                            'pending' => 'Bekliyor',
                            'published' => 'Yayinda',
                            'rejected' => 'Reddedildi',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ Str::limit($article->title, 40) }}</td>
                        <td class="px-5 py-3 text-sm text-gray-600">{{ $article->author->name }}</td>
                        <td class="px-5 py-3 text-sm text-gray-600">{{ ucfirst($article->content_type) }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$article->status] }}">
                                {{ $statusLabels[$article->status] }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-500">{{ $article->created_at->format('d.m.Y') }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.articles.show', $article) }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">Incele</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $allArticles->links() }}</div>
@else
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <p class="text-gray-500">Henuz icerik yok.</p>
    </div>
@endif
@endsection
