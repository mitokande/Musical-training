@extends('admin.layouts.admin')

@section('page-title', 'Coupon Details')

@section('content')
<div class="max-w-2xl">
    <div class="card p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900">{{ $coupon->code }}</h2>
            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $coupon->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ $coupon->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>

        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500">Type</dt><dd class="font-medium text-gray-900 mt-1">{{ ucfirst($coupon->type) }}</dd></div>
            <div><dt class="text-gray-500">Value</dt><dd class="font-medium text-gray-900 mt-1">{{ $coupon->type === 'percentage' ? $coupon->value . '%' : $coupon->value . ' TRY' }}</dd></div>
            <div><dt class="text-gray-500">Usage</dt><dd class="font-medium text-gray-900 mt-1">{{ $coupon->used_count }} / {{ $coupon->max_uses ?? '∞' }}</dd></div>
            <div><dt class="text-gray-500">Valid</dt><dd class="font-medium text-gray-900 mt-1"><span class="px-2 py-0.5 rounded-full text-xs {{ $coupon->isValid() ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $coupon->isValid() ? 'Yes' : 'No' }}</span></dd></div>
            <div><dt class="text-gray-500">Starts</dt><dd class="font-medium text-gray-900 mt-1">{{ $coupon->starts_at?->format('Y-m-d') ?? 'N/A' }}</dd></div>
            <div><dt class="text-gray-500">Expires</dt><dd class="font-medium text-gray-900 mt-1">{{ $coupon->expires_at?->format('Y-m-d') ?? 'N/A' }}</dd></div>
        </dl>

        @if($coupon->description)
            <div class="mt-4 pt-4 border-t"><p class="text-sm text-gray-600">{{ $coupon->description }}</p></div>
        @endif

        <div class="flex gap-3 mt-6 pt-4 border-t">
            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="px-4 py-2 btn-primary text-white text-sm rounded-lg">Edit</a>
            <a href="{{ route('admin.coupons.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Back to Coupons</a>
        </div>
    </div>
</div>
@endsection
