@extends('admin.layouts.admin')
@section('page-title', 'Create Plan')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.plans.index') }}" class="p-2 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="text-2xl font-bold text-gray-800">Create Plan</h2>
    </div>

    <form action="{{ route('admin.plans.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
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
                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 bg-gray-50">
                @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Role --}}
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select name="role" id="role" required class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    <option value="">Select Role</option>
                    <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                    <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                    <option value="school" {{ old('role') === 'school' ? 'selected' : '' }}>School</option>
                </select>
                @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Type --}}
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" id="type" required class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    <option value="free" {{ old('type') === 'free' ? 'selected' : '' }}>Free</option>
                    <option value="premium" {{ old('type', 'premium') === 'premium' ? 'selected' : '' }}>Premium</option>
                </select>
                @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Price Monthly --}}
            <div>
                <label for="price_monthly" class="block text-sm font-medium text-gray-700 mb-1">Monthly Price</label>
                <input type="number" name="price_monthly" id="price_monthly" value="{{ old('price_monthly', '0.00') }}" step="0.01" min="0"
                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                @error('price_monthly') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Price Yearly --}}
            <div>
                <label for="price_yearly" class="block text-sm font-medium text-gray-700 mb-1">Yearly Price</label>
                <input type="number" name="price_yearly" id="price_yearly" value="{{ old('price_yearly', '0.00') }}" step="0.01" min="0"
                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                @error('price_yearly') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Currency --}}
            <div>
                <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                <select name="currency" id="currency" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                    <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR</option>
                    <option value="TRY" {{ old('currency') === 'TRY' ? 'selected' : '' }}>TRY</option>
                    <option value="GBP" {{ old('currency') === 'GBP' ? 'selected' : '' }}>GBP</option>
                </select>
                @error('currency') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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

        {{-- Features --}}
        <div>
            <label for="features" class="block text-sm font-medium text-gray-700 mb-1">Features (JSON array)</label>
            <textarea name="features" id="features" rows="4" placeholder='["Feature 1", "Feature 2", "Feature 3"]'
                class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 font-mono text-sm">{{ old('features') }}</textarea>
            <p class="text-xs text-gray-400 mt-1">Enter as JSON array, e.g. ["Unlimited access", "Priority support"]</p>
            @error('features') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Active --}}
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
            <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <a href="{{ route('admin.plans.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                <i data-lucide="save" class="w-4 h-4"></i> Create Plan
            </button>
        </div>
    </form>
</div>
@endsection
