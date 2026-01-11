@extends('admin.layouts.admin')

@section('content')
<div class="space-y-8">
    <!-- Hero Section -->
    <div class="hero-gradient rounded-2xl p-8 relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
        
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                    <i data-lucide="settings" class="w-6 h-6 text-white"></i>
                </div>
                <span class="px-3 py-1 bg-purple-500 text-white text-xs font-semibold rounded-full">Admin Panel</span>
            </div>
            
            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                Practice Questions Dashboard
            </h1>
            <p class="text-white/80">Manage and organize all practice questions for your students.</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Single Note Stats -->
        <div class="card p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Single Note Practices</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['single_note_count'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                    <i data-lucide="music" class="w-6 h-6 text-purple-600"></i>
                </div>
            </div>
            <a href="{{ route('admin.single-note.index') }}" class="inline-flex items-center text-sm font-medium text-purple-600 hover:text-purple-700 transition-colors">
                Manage questions
                <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
            </a>
        </div>

        <!-- Interval Direction Stats -->
        <div class="card p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Interval Direction Practices</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['interval_direction_count'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                    <i data-lucide="arrow-up-down" class="w-6 h-6 text-blue-600"></i>
                </div>
            </div>
            <a href="{{ route('admin.interval-direction.index') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">
                Manage questions
                <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
            </a>
        </div>

        <!-- Interval Comparison Stats -->
        <div class="card p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Interval Comparison Practices</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['interval_comparison_count'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center">
                    <i data-lucide="bar-chart-2" class="w-6 h-6 text-orange-600"></i>
                </div>
            </div>
            <a href="{{ route('admin.interval-comparison.index') }}" class="inline-flex items-center text-sm font-medium text-orange-600 hover:text-orange-700 transition-colors">
                Manage questions
                <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card p-6">
        <div class="flex items-center gap-2 mb-6">
            <i data-lucide="zap" class="w-5 h-5 text-purple-600"></i>
            <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('admin.single-note.create') }}" 
               class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                    <i data-lucide="plus" class="w-6 h-6 text-purple-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 group-hover:text-purple-700">Add Single Note</p>
                    <p class="text-sm text-gray-500">Create new practice</p>
                </div>
            </a>

            <a href="{{ route('admin.interval-direction.create') }}" 
               class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    <i data-lucide="plus" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 group-hover:text-blue-700">Add Interval Direction</p>
                    <p class="text-sm text-gray-500">Create new practice</p>
                </div>
            </a>

            <a href="{{ route('admin.interval-comparison.create') }}" 
               class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 hover:border-orange-300 hover:bg-orange-50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                    <i data-lucide="plus" class="w-6 h-6 text-orange-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 group-hover:text-orange-700">Add Interval Comparison</p>
                    <p class="text-sm text-gray-500">Create new practice</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
