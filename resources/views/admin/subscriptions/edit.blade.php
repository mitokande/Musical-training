@extends('admin.layouts.admin')
@section('page-title', 'Edit Subscription')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="p-2 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="text-2xl font-bold text-gray-800">Edit Subscription #{{ $subscription->id }}</h2>
    </div>

    <form action="{{ route('admin.subscriptions.update', $subscription) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
        @csrf
        @method('PUT')

        {{-- User Info (Read-only) --}}
        <div class="p-4 bg-gray-50 rounded-lg">
            <div class="text-xs font-semibold text-gray-400 uppercase mb-2">Subscriber</div>
            <div class="font-medium text-gray-800">{{ $subscription->user->name ?? 'N/A' }}</div>
            <div class="text-sm text-gray-500">{{ $subscription->user->email ?? '' }}</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Status --}}
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" required class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    <option value="active" {{ old('status', $subscription->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="cancelled" {{ old('status', $subscription->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="expired" {{ old('status', $subscription->status) === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="trial" {{ old('status', $subscription->status) === 'trial' ? 'selected' : '' }}>Trial</option>
                </select>
                @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Amount --}}
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                <input type="number" name="amount" id="amount" value="{{ old('amount', $subscription->amount) }}" step="0.01" min="0"
                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Start Date --}}
            <div>
                <label for="starts_at" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="starts_at" id="starts_at" value="{{ old('starts_at', $subscription->starts_at?->format('Y-m-d')) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                @error('starts_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- End Date --}}
            <div>
                <label for="ends_at" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="ends_at" id="ends_at" value="{{ old('ends_at', $subscription->ends_at?->format('Y-m-d')) }}"
                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                @error('ends_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                <i data-lucide="save" class="w-4 h-4"></i> Update Subscription
            </button>
        </div>
    </form>
</div>
@endsection
