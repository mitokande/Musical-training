@extends('admin.layouts.admin')
@section('page-title', 'Exercises Overview')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Exercises Overview</h2>
            <p class="text-sm text-gray-500 mt-0.5">Learning Path egzersizlerinin genel istatistikleri</p>
        </div>
        <a href="{{ route('admin.learning-path-exercises.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Yeni Egzersiz
        </a>
    </div>

    {{-- Stat Cards Row 1 --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Kategoriler</span>
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="folder-tree" class="w-4 h-4 text-purple-600"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ $totalCategories }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Toplam Egzersiz</span>
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="music" class="w-4 h-4 text-blue-600"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ $totalExercises }}</div>
            <div class="flex gap-2 mt-1">
                <span class="text-xs text-green-600">{{ $activeExercises }} aktif</span>
                <span class="text-xs text-gray-400">·</span>
                <span class="text-xs text-gray-400">{{ $inactiveExercises }} pasif</span>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Ücretsiz / Premium</span>
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="tag" class="w-4 h-4 text-green-600"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ $freeExercises }} <span class="text-lg text-gray-400">/ {{ $premiumExercises }}</span></div>
            <div class="text-xs text-gray-400 mt-1">Free · Premium</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Toplam Deneme</span>
                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="activity" class="w-4 h-4 text-orange-600"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ number_format($totalAttempts) }}</div>
        </div>
    </div>

    {{-- Stat Cards Row 2 --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Aktif</span>
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-green-700">{{ $activeExercises }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Pasif</span>
                <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="pause-circle" class="w-4 h-4 text-gray-500"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-500">{{ $inactiveExercises }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Ort. Skor</span>
                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="star" class="w-4 h-4 text-yellow-600"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ $avgScore }}<span class="text-lg text-gray-400">%</span></div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tamamlanma</span>
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="trophy" class="w-4 h-4 text-purple-600"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ $completionRate }}<span class="text-lg text-gray-400">%</span></div>
        </div>
    </div>

    {{-- Bottom 3-column section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Most Used --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <i data-lucide="trending-up" class="w-4 h-4 text-green-600"></i>
                <h4 class="font-semibold text-gray-800 text-sm">En Çok Kullanılan</h4>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($mostUsed as $ex)
                <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $ex->title }}</p>
                        <p class="text-xs text-gray-400">{{ $ex->category->name ?? '—' }}</p>
                    </div>
                    <span class="ml-3 text-sm font-bold text-green-600">{{ $ex->user_progress_count }}</span>
                </div>
                @empty
                <div class="px-5 py-6 text-center text-xs text-gray-400">Henüz deneme yok</div>
                @endforelse
            </div>
        </div>

        {{-- Least Used --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <i data-lucide="trending-down" class="w-4 h-4 text-orange-500"></i>
                <h4 class="font-semibold text-gray-800 text-sm">En Az Kullanılan</h4>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($leastUsed as $ex)
                <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $ex->title }}</p>
                        <p class="text-xs text-gray-400">{{ $ex->category->name ?? '—' }}</p>
                    </div>
                    <span class="ml-3 text-sm font-bold text-orange-500">{{ $ex->user_progress_count }}</span>
                </div>
                @empty
                <div class="px-5 py-6 text-center text-xs text-gray-400">Henüz deneme yok</div>
                @endforelse
            </div>
        </div>

        {{-- Recently Edited --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4 text-purple-600"></i>
                <h4 class="font-semibold text-gray-800 text-sm">Son Düzenlenenler</h4>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentlyEdited as $ex)
                <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $ex->title }}</p>
                        <p class="text-xs text-gray-400">{{ $ex->updated_at->diffForHumans() }}</p>
                    </div>
                    <a href="{{ route('admin.learning-path-exercises.edit', $ex) }}"
                       class="ml-3 text-xs text-purple-600 hover:text-purple-800 font-medium shrink-0">
                        Düzenle
                    </a>
                </div>
                @empty
                <div class="px-5 py-6 text-center text-xs text-gray-400">Henüz düzenleme yok</div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
