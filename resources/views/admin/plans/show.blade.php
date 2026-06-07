@extends('admin.layouts.admin')

@section('page-title', 'Plan Details')

@section('content')
<div class="max-w-2xl">
    <div class="card p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h2>
            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $plan->type === 'premium' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-700' }}">
                {{ ucfirst($plan->type) }}
            </span>
        </div>

        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500">Role</dt><dd class="font-medium text-gray-900 mt-1">{{ ucfirst($plan->role) }}</dd></div>
            <div><dt class="text-gray-500">Status</dt><dd class="mt-1"><span class="px-2 py-0.5 rounded-full text-xs {{ $plan->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span></dd></div>
            <div><dt class="text-gray-500">Monthly Price</dt><dd class="font-medium text-gray-900 mt-1">{{ $plan->price_monthly }} {{ $plan->currency }}</dd></div>
            <div><dt class="text-gray-500">Yearly Price</dt><dd class="font-medium text-gray-900 mt-1">{{ $plan->price_yearly }} {{ $plan->currency }}</dd></div>
        </dl>

        @if($plan->description)
            <div class="mt-4 pt-4 border-t"><p class="text-sm text-gray-600">{{ $plan->description }}</p></div>
        @endif

        @if($plan->features)
            <div class="mt-4 pt-4 border-t">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Features</h3>
                <pre class="text-xs bg-gray-50 p-3 rounded-lg overflow-x-auto">{{ json_encode($plan->features, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif

        <div class="flex gap-3 mt-6 pt-4 border-t">
            <a href="{{ route('admin.plans.edit', $plan) }}" class="px-4 py-2 btn-primary text-white text-sm rounded-lg">Edit</a>
            <a href="{{ route('admin.plans.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Back to Plans</a>
        </div>
    </div>
</div>
@endsection
