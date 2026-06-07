@extends('admin.layouts.admin')
@section('page-title', 'Invoices')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Invoices</h2>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('admin.invoices.index') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm">
                    <option value="">All Statuses</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm">
            </div>
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm">
                <i data-lucide="filter" class="w-4 h-4"></i> Filter
            </button>
            <a href="{{ route('admin.invoices.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700 transition text-sm">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Invoice #</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">User</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Amount</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Tax</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Total</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Paid At</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $invoice->invoice_number ?? '#' . $invoice->id }}</td>
                            <td class="px-6 py-4">
                                <div class="text-gray-800">{{ $invoice->user->name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-400">{{ $invoice->user->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ number_format($invoice->amount, 2) }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ number_format($invoice->tax ?? 0, 2) }}</td>
                            <td class="px-6 py-4 font-medium text-gray-800">{{ number_format($invoice->total ?? ($invoice->amount + ($invoice->tax ?? 0)), 2) }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $invoiceStatusColors = [
                                        'paid' => 'bg-green-100 text-green-700',
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'failed' => 'bg-red-100 text-red-700',
                                        'refunded' => 'bg-blue-100 text-blue-700',
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $invoiceStatusColors[$invoice->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $invoice->paid_at?->format('M d, Y') ?? '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.invoices.show', $invoice) }}" class="p-1.5 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50 transition inline-block" title="View">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                <i data-lucide="file-text" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                                <p>No invoices found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $invoices->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
