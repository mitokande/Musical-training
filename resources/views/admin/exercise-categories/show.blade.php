@extends('admin.layouts.admin')
@section('page-title', $exerciseCategory->name . ' — Egzersizler')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.exercises.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            @if($exerciseCategory->parent)
            <div class="flex items-center gap-2 text-sm text-gray-400">
                <a href="{{ route('admin.exercise-categories.show', $exerciseCategory->parent) }}" class="hover:text-purple-600">
                    {{ $exerciseCategory->parent->name }}
                </a>
                <i data-lucide="chevron-right" class="w-3 h-3"></i>
            </div>
            @endif
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $exerciseCategory->name }}</h2>
                @if($exerciseCategory->description)
                <p class="text-sm text-gray-500 mt-0.5">{{ $exerciseCategory->description }}</p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.exercise-categories.edit', $exerciseCategory) }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <i data-lucide="settings" class="w-4 h-4"></i>
                Kategoriyi Düzenle
            </a>
            <a href="{{ route('admin.learning-path-exercises.create') }}?category_id={{ $exerciseCategory->id }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Yeni Egzersiz
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    <div x-data="{ open: false, deleteUrl: '', exerciseTitle: '' }"
         @open-delete-modal.window="open = true; deleteUrl = $event.detail.url; exerciseTitle = $event.detail.title">
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 z-10">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                        <i data-lucide="trash-2" class="w-5 h-5 text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Egzersizi Sil</h3>
                </div>
                <p class="text-sm text-gray-600 mb-1">
                    <span class="font-medium" x-text="exerciseTitle"></span> egzersizini silmek istediğinize emin misiniz?
                </p>
                <p class="text-xs text-gray-400 mb-6">Kullanıcı ilerlemesi olan egzersizler arşive taşınır, diğerleri kalıcı olarak silinir.</p>
                <div class="flex justify-end gap-3">
                    <button @click="open = false"
                            class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
                        İptal
                    </button>
                    <form :action="deleteUrl" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                            Sil
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Direct exercises in this category --}}
    @if($exercises->count() > 0)
    <div>
        @if($subcategories->count() > 0)
        <h3 class="text-base font-semibold text-gray-700 mb-3">Direkt Egzersizler</h3>
        @endif
        @include('admin.exercise-categories._exercise-grid', ['exerciseList' => $exercises, 'category' => $exerciseCategory])
    </div>
    @endif

    {{-- Subcategory sections --}}
    @foreach($subcategories as $sub)
    <div>
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                @if($sub->icon)
                <div class="w-7 h-7 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="{{ $sub->icon }}" class="w-3.5 h-3.5 text-purple-600"></i>
                </div>
                @endif
                <h3 class="text-base font-semibold text-gray-700">{{ $sub->name }}</h3>
                <span class="text-xs text-gray-400">({{ $sub->exercises->count() }} egzersiz)</span>
            </div>
            <a href="{{ route('admin.exercise-categories.show', $sub) }}"
               class="text-xs text-purple-600 hover:text-purple-800 font-medium">
                Tümünü Gör →
            </a>
        </div>

        @if($sub->exercises->count() > 0)
            @include('admin.exercise-categories._exercise-grid', ['exerciseList' => $sub->exercises, 'category' => $sub])
        @else
        <div class="bg-white rounded-xl border border-dashed border-gray-200 p-8 text-center text-gray-400">
            <i data-lucide="music" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
            <p class="text-sm">Bu alt kategoride egzersiz yok.</p>
            <a href="{{ route('admin.learning-path-exercises.create') }}?category_id={{ $sub->id }}"
               class="inline-flex items-center gap-1 mt-3 text-xs text-purple-600 hover:text-purple-800 font-medium">
                <i data-lucide="plus" class="w-3 h-3"></i> Egzersiz Ekle
            </a>
        </div>
        @endif
    </div>
    @endforeach

    {{-- Empty state --}}
    @if($exercises->count() === 0 && $subcategories->filter(fn($s) => $s->exercises->count() > 0)->count() === 0)
    <div class="bg-white rounded-xl border border-dashed border-gray-200 p-16 text-center text-gray-400">
        <i data-lucide="music" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
        <p class="text-lg font-medium mb-1">Henüz Egzersiz Yok</p>
        <p class="text-sm mb-4">Bu kategoriye ilk egzersizi ekleyin.</p>
        <a href="{{ route('admin.learning-path-exercises.create') }}?category_id={{ $exerciseCategory->id }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium">
            <i data-lucide="plus" class="w-4 h-4"></i> Egzersiz Ekle
        </a>
    </div>
    @endif

</div>
@endsection
