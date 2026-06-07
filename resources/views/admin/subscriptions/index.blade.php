@extends('admin.layouts.admin')
@section('page-title', 'Subscriptions')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Subscriptions</h2>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search User</label>
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email..."
                        class="w-full pl-9 rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Plan</label>
                <select name="plan_id" class="rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm">
                    <option value="">All Plans</option>
                    @foreach($plans ?? [] as $plan)
                        <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm">
                <i data-lucide="filter" class="w-4 h-4"></i> Filter
            </button>
            <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700 transition text-sm">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">User</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Plan</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Amount</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Start Date</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">End Date</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($subscriptions as $subscription)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800">{{ $subscription->user->name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-400">{{ $subscription->user->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $subscription->plan->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        'expired' => 'bg-gray-100 text-gray-600',
                                        'trial' => 'bg-blue-100 text-blue-700',
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$subscription->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ number_format($subscription->amount, 2) }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $subscription->starts_at?->format('M d, Y') ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $subscription->ends_at?->format('M d, Y') ?? '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="p-1.5 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50 transition" title="View">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="p-1.5 text-gray-400 hover:text-orange-600 rounded-lg hover:bg-orange-50 transition" title="Edit">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                <i data-lucide="credit-card" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                                <p>No subscriptions found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscriptions->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $subscriptions->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
