@extends('admin.layouts.admin')
@section('page-title', 'Learning Path Exercises')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Learning Path Exercises</h2>
            <p class="text-sm text-gray-500 mt-0.5">Tüm egzersizler — kategori sayfasına gitmek için kategori adına tıklayın</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.exercises.index') }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                Overview
            </a>
            <a href="{{ route('admin.learning-path-exercises.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Yeni Egzersiz
            </a>
        </div>
    </div>

    {{-- Category filter --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.learning-path-exercises.index') }}"
           class="px-3 py-1.5 text-xs rounded-lg border {{ !request('category') ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-600 border-gray-200 hover:border-purple-300' }} transition">
            Tümü
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('admin.learning-path-exercises.index', ['category' => $cat->id]) }}"
           class="px-3 py-1.5 text-xs rounded-lg border {{ request('category') == $cat->id ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-600 border-gray-200 hover:border-purple-300' }} transition">
            {{ $cat->parent_id ? '└ ' : '' }}{{ $cat->name }}
        </a>
        @endforeach
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">#</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Kategori</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Başlık</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Seviye</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Tip</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Denemeler</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Durum</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($exercises as $ex)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $ex->sort_order }}</td>
                        <td class="px-4 py-3">
                            @if($ex->category)
                            <a href="{{ route('admin.exercise-categories.show', $ex->category) }}"
                               class="text-xs text-purple-600 hover:text-purple-800 font-medium">
                                {{ $ex->category->name }}
                            </a>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-900">{{ $ex->title }}</div>
                            <div class="text-xs text-gray-400 truncate max-w-xs">{{ $ex->description }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $lc = ['beginner'=>'bg-green-100 text-green-700','intermediate'=>'bg-yellow-100 text-yellow-700','advanced'=>'bg-red-100 text-red-700'];
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $lc[$ex->level] ?? '' }}">
                                {{ ucfirst($ex->level) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400 font-mono">{{ $ex->config_json['practice_type'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ number_format($ex->user_progress_count) }}</td>
                        <td class="px-4 py-3">
                            @if($ex->is_active)
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs">Aktif</span>
                            @else
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-xs">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.learning-path-exercises.edit', $ex) }}"
                                   class="px-2.5 py-1.5 text-xs bg-yellow-100 text-yellow-700 hover:bg-yellow-200 rounded-lg transition">Düzenle</a>
                                <form method="POST" action="{{ route('admin.learning-path-exercises.toggle-status', $ex) }}">
                                    @csrf @method('PATCH')
                                    <button class="px-2.5 py-1.5 text-xs {{ $ex->is_active ? 'bg-gray-100 text-gray-500 hover:bg-gray-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }} rounded-lg transition">
                                        {{ $ex->is_active ? 'Pasif' : 'Aktif' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.learning-path-exercises.destroy', $ex) }}"
                                      onsubmit="return confirm('Bu egzersizi silmek istediğinize emin misiniz?')">
                                    @csrf @method('DELETE')
                                    <button class="px-2.5 py-1.5 text-xs bg-red-100 text-red-700 hover:bg-red-200 rounded-lg transition">Sil</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400">Henüz egzersiz yok.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-200">{{ $exercises->links() }}</div>
    </div>
</div>
@endsection
