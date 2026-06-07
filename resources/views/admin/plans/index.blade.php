@extends('admin.layouts.admin')
@section('page-title', 'Plans')

@section('content')
<div x-data="{ activeTab: 'user' }" class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Plans</h2>
        <a href="{{ route('admin.plans.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Plan
        </a>
    </div>

    {{-- Tab Navigation --}}
    <div class="border-b border-gray-200">
        <nav class="flex gap-4">
            <button @click="activeTab = 'user'"
                :class="activeTab === 'user' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="px-4 py-3 border-b-2 font-medium transition">
                <i data-lucide="user" class="w-4 h-4 inline mr-1"></i> User Plans
            </button>
            <button @click="activeTab = 'teacher'"
                :class="activeTab === 'teacher' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="px-4 py-3 border-b-2 font-medium transition">
                <i data-lucide="graduation-cap" class="w-4 h-4 inline mr-1"></i> Teacher Plans
            </button>
            <button @click="activeTab = 'school'"
                :class="activeTab === 'school' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="px-4 py-3 border-b-2 font-medium transition">
                <i data-lucide="school" class="w-4 h-4 inline mr-1"></i> School Plans
            </button>
        </nav>
    </div>

    {{-- Plans Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($plans as $plan)
            <div x-show="activeTab === '{{ $plan->role }}'" x-transition
                class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">{{ $plan->name }}</h3>
                        <div class="flex items-center gap-2">
                            @if($plan->is_active)
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-500 rounded-full">Inactive</span>
                            @endif
                            @if($plan->type === 'premium')
                                <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-700 rounded-full">Premium</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-700 rounded-full">Free</span>
                            @endif
                        </div>
                    </div>

                    <p class="text-sm text-gray-500 mb-4">{{ $plan->description }}</p>

                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Monthly</span>
                            <span class="font-semibold text-gray-800">{{ $plan->currency }} {{ number_format($plan->price_monthly, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Yearly</span>
                            <span class="font-semibold text-gray-800">{{ $plan->currency }} {{ number_format($plan->price_yearly, 2) }}</span>
                        </div>
                    </div>

                    @if($plan->features)
                        <div class="border-t pt-4">
                            <h4 class="text-xs font-semibold text-gray-400 uppercase mb-2">Features</h4>
                            <ul class="space-y-1">
                                @foreach((is_array($plan->features) ? $plan->features : json_decode($plan->features, true)) ?? [] as $feature)
                                    <li class="flex items-center gap-2 text-sm text-gray-600">
                                        <i data-lucide="check" class="w-3 h-3 text-green-500"></i>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <div class="px-6 py-3 bg-gray-50 flex items-center justify-end gap-2">
                    <a href="{{ route('admin.plans.edit', $plan) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-purple-600 hover:bg-purple-50 rounded-lg transition">
                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                    </a>
                    <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this plan?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-400">
                <i data-lucide="package" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                <p>No plans found. Create your first plan to get started.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
