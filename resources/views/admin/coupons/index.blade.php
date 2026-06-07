@extends('admin.layouts.admin')
@section('page-title', 'Coupons')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Coupons</h2>
        <a href="{{ route('admin.coupons.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Coupon
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Code</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Value</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Max Uses</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Used</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Expires</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Active</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($coupons as $coupon)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <span class="font-mono font-medium text-purple-600 bg-purple-50 px-2 py-1 rounded">{{ $coupon->code }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $coupon->type === 'percentage' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ ucfirst($coupon->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-800">
                                {{ $coupon->type === 'percentage' ? $coupon->value . '%' : number_format($coupon->value, 2) }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $coupon->max_uses ?? 'Unlimited' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $coupon->used_count ?? 0 }}</td>
                            <td class="px-6 py-4 text-gray-500">
                                @if($coupon->expires_at)
                                    <span class="{{ $coupon->expires_at->isPast() ? 'text-red-500' : '' }}">
                                        {{ $coupon->expires_at->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-gray-400">Never</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($coupon->is_active)
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-500 rounded-full">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="p-1.5 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50 transition" title="Edit">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Delete this coupon?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition" title="Delete">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                <i data-lucide="ticket" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                                <p>No coupons found. Create your first coupon.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
