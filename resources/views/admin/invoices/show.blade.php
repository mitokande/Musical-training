@extends('admin.layouts.admin')
@section('page-title', 'Invoice Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.invoices.index') }}" class="p-2 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="text-2xl font-bold text-gray-800">Invoice {{ $invoice->invoice_number ?? '#' . $invoice->id }}</h2>
        @php
            $invoiceStatusColors = [
                'paid' => 'bg-green-100 text-green-700',
                'pending' => 'bg-yellow-100 text-yellow-700',
                'failed' => 'bg-red-100 text-red-700',
                'refunded' => 'bg-blue-100 text-blue-700',
            ];
        @endphp
        <span class="px-3 py-1 text-sm font-medium rounded-full {{ $invoiceStatusColors[$invoice->status] ?? 'bg-gray-100 text-gray-600' }}">
            {{ ucfirst($invoice->status) }}
        </span>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {{-- Invoice Header --}}
        <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-orange-50">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="text-xs font-semibold text-gray-400 uppercase mb-2">Billed To</h3>
                    <p class="font-medium text-gray-800">{{ $invoice->user->name ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-500">{{ $invoice->user->email ?? '' }}</p>
                </div>
                <div class="text-right">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase mb-2">Invoice Details</h3>
                    <p class="text-sm text-gray-600">Invoice #: <span class="font-medium text-gray-800">{{ $invoice->invoice_number ?? $invoice->id }}</span></p>
                    <p class="text-sm text-gray-600">Date: <span class="font-medium text-gray-800">{{ $invoice->created_at?->format('M d, Y') }}</span></p>
                    @if($invoice->paid_at)
                        <p class="text-sm text-gray-600">Paid: <span class="font-medium text-green-600">{{ $invoice->paid_at->format('M d, Y') }}</span></p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Invoice Body --}}
        <div class="p-6">
            <table class="w-full text-sm mb-6">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-2 text-xs font-semibold text-gray-500 uppercase">Description</th>
                        <th class="text-right py-2 text-xs font-semibold text-gray-500 uppercase">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-100">
                        <td class="py-3 text-gray-800">{{ $invoice->description ?? 'Subscription Payment' }}</td>
                        <td class="py-3 text-right text-gray-800">{{ number_format($invoice->amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="flex justify-end">
                <div class="w-64 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="text-gray-800">{{ number_format($invoice->amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Tax</span>
                        <span class="text-gray-800">{{ number_format($invoice->tax ?? 0, 2) }}</span>
                    </div>
                    @if($invoice->discount ?? false)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Discount</span>
                            <span class="text-green-600">-{{ number_format($invoice->discount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm font-bold border-t pt-2">
                        <span class="text-gray-800">Total</span>
                        <span class="text-purple-600">{{ number_format($invoice->total ?? ($invoice->amount + ($invoice->tax ?? 0) - ($invoice->discount ?? 0)), 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Info --}}
        @if($invoice->payment_method || $invoice->transaction_id)
            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">Payment Information</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    @if($invoice->payment_method)
                        <div>
                            <span class="text-gray-500">Method</span>
                            <p class="font-medium text-gray-800">{{ ucfirst($invoice->payment_method) }}</p>
                        </div>
                    @endif
                    @if($invoice->transaction_id)
                        <div>
                            <span class="text-gray-500">Transaction ID</span>
                            <p class="font-medium text-gray-800 font-mono text-xs">{{ $invoice->transaction_id }}</p>
                        </div>
                    @endif
                    @if($invoice->currency)
                        <div>
                            <span class="text-gray-500">Currency</span>
                            <p class="font-medium text-gray-800">{{ strtoupper($invoice->currency) }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
