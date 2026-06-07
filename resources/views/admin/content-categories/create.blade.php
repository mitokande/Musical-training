@extends('admin.layouts.admin')
@section('page-title', 'Create Content Category')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.content-categories.index') }}" class="p-2 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="text-2xl font-bold text-gray-800">Create Content Category</h2>
    </div>

    <form action="{{ route('admin.content-categories.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Name --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                    x-data x-on:input="$refs.slugField.value = $event.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Slug --}}
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" id="slug" x-ref="slugField" value="{{ old('slug') }}" required
                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 bg-gray-50 font-mono text-sm">
                @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Parent --}}
            <div>
                <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">Parent Category</label>
                <select name="parent_id" id="parent_id" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    <option value="">None (Root Category)</option>
                    @foreach($parentCategories ?? [] as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                    @endforeach
                </select>
                @error('parent_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Sort Order --}}
            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                @error('sort_order') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Description --}}
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" id="description" rows="3"
                class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">{{ old('description') }}</textarea>
            @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Active --}}
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
            <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <a href="{{ route('admin.content-categories.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                <i data-lucide="save" class="w-4 h-4"></i> Create Category
            </button>
        </div>
    </form>
</div>
@endsection
