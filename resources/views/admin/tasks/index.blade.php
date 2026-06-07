@extends('admin.layouts.admin')

@section('page-title', 'CRM Tasks')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <p class="text-sm text-gray-500">Manage and track all CRM tasks.</p>
        <a href="{{ route('admin.tasks.create') }}" class="btn-primary inline-flex items-center gap-2 px-4 py-2.5 text-white text-sm font-semibold rounded-lg transition-all hover:shadow-lg">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Task
        </a>
    </div>

    {{-- Filters --}}
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.tasks.index') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Priority</label>
                <select name="priority" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">All</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                <select name="type" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">All Types</option>
                    @foreach($types ?? [] as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-700 rounded-lg transition-colors">
                <i data-lucide="filter" class="w-4 h-4 inline mr-1"></i> Filter
            </button>
        </form>
    </div>

    {{-- Tasks Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Title</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Related User</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Type</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Priority</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Status</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Due Date</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Assigned To</th>
                        <th class="text-right py-3 px-4 font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tasks ?? [] as $task)
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
                        $isOverdue = $task->due_date && $task->due_date->isPast() && !in_array($task->status, ['completed', 'cancelled']);
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-4 font-medium text-gray-900">
                            <a href="{{ route('admin.tasks.show', $task) }}" class="hover:text-purple-600">{{ $task->title }}</a>
                        </td>
                        <td class="py-3 px-4 text-gray-600">{{ $task->user->name ?? '-' }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-700">{{ ucfirst(str_replace('_', ' ', $task->type ?? '-')) }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $priorityColors[$task->priority] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($task->priority ?? 'normal') }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$task->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst(str_replace('_', ' ', $task->status ?? 'pending')) }}</span>
                        </td>
                        <td class="py-3 px-4 {{ $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                            {{ $task->due_date ? $task->due_date->format('M d, Y H:i') : '-' }}
                            @if($isOverdue)
                                <span class="text-xs text-red-500 block">Overdue</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-gray-600">{{ $task->assignee->name ?? '-' }}</td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.tasks.show', $task) }}" class="p-1.5 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50" title="View">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('admin.tasks.edit', $task) }}" class="p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50" title="Edit">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-gray-500">No tasks found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if(method_exists($tasks ?? collect(), 'links'))
    <div>{{ $tasks->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
