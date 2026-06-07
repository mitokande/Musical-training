@extends('admin.layouts.admin')

@section('page-title', 'Task Details')

@section('content')
<div class="max-w-2xl space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.tasks.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-purple-600 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Tasks
        </a>
        <a href="{{ route('admin.tasks.edit', $task) }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-purple-600 hover:bg-purple-50 rounded-lg transition-colors">
            <i data-lucide="pencil" class="w-4 h-4"></i> Edit
        </a>
    </div>

    @php
        $priorityColors = [
            'low' => 'bg-gray-100 text-gray-700',
            'normal' => 'bg-blue-100 text-blue-700',
            'high' => 'bg-orange-100 text-orange-700',
            'urgent' => 'bg-red-100 text-red-700',
        ];
        $statusColors = [
            'pending' => 'bg-yellow-100 text-yellow-700',
            'in_progress' => 'bg-blue-100 text-blue-700',
            'completed' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-gray-100 text-gray-700',
        ];
    @endphp

    <div class="card p-6">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $task->title }}</h2>
                <p class="text-sm text-gray-500 mt-1">Created {{ $task->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $priorityColors[$task->priority] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($task->priority ?? 'normal') }}</span>
                <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $statusColors[$task->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst(str_replace('_', ' ', $task->status ?? 'pending')) }}</span>
            </div>
        </div>

        <div class="space-y-4">
            @if($task->description)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Description</p>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $task->description }}</p>
            </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Type</p>
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-700">{{ ucfirst(str_replace('_', ' ', $task->type ?? '-')) }}</span>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Due Date</p>
                    @if($task->due_date)
                        <p class="text-sm {{ $task->due_date->isPast() && !in_array($task->status, ['completed', 'cancelled']) ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                            {{ $task->due_date->format('M d, Y H:i') }}
                            @if($task->due_date->isPast() && !in_array($task->status, ['completed', 'cancelled']))
                                <span class="text-xs text-red-500">(Overdue)</span>
                            @endif
                        </p>
                    @else
                        <p class="text-sm text-gray-500">Not set</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Related User</p>
                    <p class="text-sm text-gray-700">{{ $task->user->name ?? 'None' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Assigned To</p>
                    <p class="text-sm text-gray-700">{{ $task->assignee->name ?? 'Unassigned' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
