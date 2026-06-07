@extends('admin.layouts.admin')

@section('page-title', 'Member Segments')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
            <i data-lucide="pie-chart" class="w-5 h-5 text-purple-600"></i>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Member Segments</h1>
            <p class="text-gray-500 text-sm">Quick filters to view specific member groups</p>
        </div>
    </div>

    <!-- Segment Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- All Members -->
        <a href="{{ route('admin.users.index') }}" class="card p-6 hover:shadow-md hover:border-purple-300 transition-all group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                    <i data-lucide="users" class="w-6 h-6 text-purple-600"></i>
                </div>
                <span class="text-2xl font-bold text-gray-900">{{ $segments['all'] ?? 0 }}</span>
            </div>
            <h3 class="font-semibold text-gray-900 group-hover:text-purple-700 transition-colors">All Members</h3>
            <p class="text-sm text-gray-500 mt-1">Total registered members</p>
        </a>

        <!-- Free Members -->
        <a href="{{ route('admin.users.index', ['plan' => 'free']) }}" class="card p-6 hover:shadow-md hover:border-blue-300 transition-all group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    <i data-lucide="user" class="w-6 h-6 text-blue-600"></i>
                </div>
                <span class="text-2xl font-bold text-gray-900">{{ $segments['free'] ?? 0 }}</span>
            </div>
            <h3 class="font-semibold text-gray-900 group-hover:text-blue-700 transition-colors">Free Members</h3>
            <p class="text-sm text-gray-500 mt-1">Members on free plan</p>
        </a>

        <!-- Premium Members -->
        <a href="{{ route('admin.users.index', ['plan' => 'premium']) }}" class="card p-6 hover:shadow-md hover:border-orange-300 transition-all group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                    <i data-lucide="crown" class="w-6 h-6 text-orange-600"></i>
                </div>
                <span class="text-2xl font-bold text-gray-900">{{ $segments['premium'] ?? 0 }}</span>
            </div>
            <h3 class="font-semibold text-gray-900 group-hover:text-orange-700 transition-colors">Premium Members</h3>
            <p class="text-sm text-gray-500 mt-1">Paying subscribers</p>
        </a>

        <!-- Students -->
        <a href="{{ route('admin.users.index', ['role' => 'user']) }}" class="card p-6 hover:shadow-md hover:border-green-300 transition-all group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center group-hover:bg-green-200 transition-colors">
                    <i data-lucide="graduation-cap" class="w-6 h-6 text-green-600"></i>
                </div>
                <span class="text-2xl font-bold text-gray-900">{{ $segments['students'] ?? 0 }}</span>
            </div>
            <h3 class="font-semibold text-gray-900 group-hover:text-green-700 transition-colors">Students</h3>
            <p class="text-sm text-gray-500 mt-1">Regular student accounts</p>
        </a>

        <!-- Teachers -->
        <a href="{{ route('admin.users.index', ['role' => 'teacher']) }}" class="card p-6 hover:shadow-md hover:border-indigo-300 transition-all group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                    <i data-lucide="briefcase" class="w-6 h-6 text-indigo-600"></i>
                </div>
                <span class="text-2xl font-bold text-gray-900">{{ $segments['teachers'] ?? 0 }}</span>
            </div>
            <h3 class="font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors">Teachers</h3>
            <p class="text-sm text-gray-500 mt-1">Instructor accounts</p>
        </a>

        <!-- Music Schools -->
        <a href="{{ route('admin.users.index', ['role' => 'school']) }}" class="card p-6 hover:shadow-md hover:border-pink-300 transition-all group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-pink-100 flex items-center justify-center group-hover:bg-pink-200 transition-colors">
                    <i data-lucide="building" class="w-6 h-6 text-pink-600"></i>
                </div>
                <span class="text-2xl font-bold text-gray-900">{{ $segments['schools'] ?? 0 }}</span>
            </div>
            <h3 class="font-semibold text-gray-900 group-hover:text-pink-700 transition-colors">Music Schools</h3>
            <p class="text-sm text-gray-500 mt-1">Institutional accounts</p>
        </a>

        <!-- Active (last 7 days) -->
        <a href="{{ route('admin.users.index', ['status' => 'active']) }}" class="card p-6 hover:shadow-md hover:border-green-300 transition-all group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center group-hover:bg-green-200 transition-colors">
                    <i data-lucide="activity" class="w-6 h-6 text-green-600"></i>
                </div>
                <span class="text-2xl font-bold text-gray-900">{{ $segments['active'] ?? 0 }}</span>
            </div>
            <h3 class="font-semibold text-gray-900 group-hover:text-green-700 transition-colors">Active (7 days)</h3>
            <p class="text-sm text-gray-500 mt-1">Active in the last week</p>
        </a>

        <!-- Inactive (30+ days) -->
        <a href="{{ route('admin.users.index', ['status' => 'inactive']) }}" class="card p-6 hover:shadow-md hover:border-red-300 transition-all group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center group-hover:bg-red-200 transition-colors">
                    <i data-lucide="user-x" class="w-6 h-6 text-red-600"></i>
                </div>
                <span class="text-2xl font-bold text-gray-900">{{ $segments['inactive'] ?? 0 }}</span>
            </div>
            <h3 class="font-semibold text-gray-900 group-hover:text-red-700 transition-colors">Inactive (30+ days)</h3>
            <p class="text-sm text-gray-500 mt-1">No activity for a month</p>
        </a>
    </div>
</div>
@endsection
