@extends('admin.layouts.admin')
@section('page-title', 'Subscription Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.subscriptions.index') }}" class="p-2 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="text-2xl font-bold text-gray-800">Subscription #{{ $subscription->id }}</h2>
        @php
            $statusColors = [
                'active' => 'bg-green-100 text-green-700',
                'cancelled' => 'bg-red-100 text-red-700',
                'expired' => 'bg-gray-100 text-gray-600',
                'trial' => 'bg-blue-100 text-blue-700',
            ];
        @endphp
        <span class="px-3 py-1 text-sm font-medium rounded-full {{ $statusColors[$subscription->status] ?? 'bg-gray-100 text-gray-600' }}">
            {{ ucfirst($subscription->status) }}
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Subscription Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="credit-card" class="w-5 h-5 text-purple-600"></i>
                Subscription Info
            </h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Status</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ ucfirst($subscription->status) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Amount</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ number_format($subscription->amount, 2) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Billing Cycle</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ ucfirst($subscription->billing_cycle ?? 'N/A') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Start Date</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $subscription->starts_at?->format('M d, Y') ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">End Date</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $subscription->ends_at?->format('M d, Y') ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Created</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $subscription->created_at?->format('M d, Y H:i') }}</dd>
                </div>
            </dl>
        </div>

        {{-- User Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="user" class="w-5 h-5 text-orange-500"></i>
                User Info
            </h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Name</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $subscription->user->name ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Email</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $subscription->user->email ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Role</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ ucfirst($subscription->user->role ?? 'N/A') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Joined</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $subscription->user->created_at?->format('M d, Y') ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Plan Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:col-span-2">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="package" class="w-5 h-5 text-purple-600"></i>
                Plan Info
            </h3>
            <dl class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <dt class="text-xs text-gray-500 uppercase">Plan Name</dt>
                    <dd class="text-sm font-medium text-gray-800 mt-1">{{ $subscription->plan->name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase">Type</dt>
                    <dd class="text-sm font-medium text-gray-800 mt-1">{{ ucfirst($subscription->plan->type ?? 'N/A') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase">Monthly Price</dt>
                    <dd class="text-sm font-medium text-gray-800 mt-1">{{ number_format($subscription->plan->price_monthly ?? 0, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500 uppercase">Yearly Price</dt>
                    <dd class="text-sm font-medium text-gray-800 mt-1">{{ number_format($subscription->plan->price_yearly ?? 0, 2) }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
            <i data-lucide="pencil" class="w-4 h-4"></i> Edit Subscription
        </a>
    </div>
</div>
@endsection
