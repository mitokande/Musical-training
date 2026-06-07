@extends('admin.layouts.admin')
@section('page-title', 'Edit Coupon')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.coupons.index') }}" class="p-2 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="text-2xl font-bold text-gray-800">Edit Coupon: {{ $coupon->code }}</h2>
    </div>

    <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6"
        x-data="{ code: '{{ old('code', $coupon->code) }}' }">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Code --}}
            <div class="md:col-span-2">
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Coupon Code</label>
                <div class="flex gap-2">
                    <input type="text" name="code" id="code" x-model="code" required
                        class="flex-1 rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 font-mono uppercase">
                    <button type="button"
                        @click="code = Array.from({length: 8}, () => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'[Math.floor(Math.random() * 36)]).join('')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-orange-100 text-orange-700 rounded-lg hover:bg-orange-200 transition text-sm whitespace-nowrap">
                        <i data-lucide="shuffle" class="w-4 h-4"></i> Generate
                    </button>
                </div>
                @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Description --}}
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <input type="text" name="description" id="description" value="{{ old('description', $coupon->description) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Type --}}
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Discount Type</label>
                <select name="type" id="type" required class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    <option value="percentage" {{ old('type', $coupon->type) === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                    <option value="fixed" {{ old('type', $coupon->type) === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                </select>
                @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Value --}}
            <div>
                <label for="value" class="block text-sm font-medium text-gray-700 mb-1">Value</label>
                <input type="number" name="value" id="value" value="{{ old('value', $coupon->value) }}" step="0.01" min="0" required
                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                @error('value') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Max Uses --}}
            <div>
                <label for="max_uses" class="block text-sm font-medium text-gray-700 mb-1">Max Uses</label>
                <input type="number" name="max_uses" id="max_uses" value="{{ old('max_uses', $coupon->max_uses) }}" min="0"
                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                    placeholder="Leave empty for unlimited">
                <p class="text-xs text-gray-400 mt-1">Used: {{ $coupon->used_count ?? 0 }} times</p>
                @error('max_uses') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Starts At --}}
            <div>
                <label for="starts_at" class="block text-sm font-medium text-gray-700 mb-1">Starts At</label>
                <input type="datetime-local" name="starts_at" id="starts_at"
                    value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                @error('starts_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Expires At --}}
            <div>
                <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-1">Expires At</label>
                <input type="datetime-local" name="expires_at" id="expires_at"
                    value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                @error('expires_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Active --}}
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}
                class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
            <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <a href="{{ route('admin.coupons.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                <i data-lucide="save" class="w-4 h-4"></i> Update Coupon
            </button>
        </div>
    </form>
</div>
@endsection
