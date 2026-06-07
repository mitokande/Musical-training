@extends('admin.layouts.admin')

@section('page-title', 'Create Task')

@section('content')
<div class="max-w-2xl space-y-6">

    <a href="{{ route('admin.tasks.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-purple-600 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Tasks
    </a>

    <div class="card p-6">
        <div class="flex items-center gap-2 mb-6">
            <i data-lucide="plus-circle" class="w-5 h-5 text-purple-600"></i>
            <h2 class="text-lg font-semibold text-gray-900">New Task</h2>
        </div>

        <form action="{{ route('admin.tasks.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" id="description" rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">{{ old('description') }}</textarea>
                @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Related User</label>
                <select name="user_id" id="user_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">-- Select User --</option>
                    @foreach($users ?? [] as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                @error('user_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" id="type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        @foreach($types ?? ['follow_up', 'support', 'onboarding', 'renewal', 'general'] as $type)
                            <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                    @error('type') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <select name="priority" id="priority" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                    @error('priority') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                <input type="datetime-local" name="due_date" id="due_date" value="{{ old('due_date') }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @error('due_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4 border-t border-gray-200 flex items-center gap-3">
                <button type="submit" class="btn-primary px-6 py-2.5 text-white text-sm font-semibold rounded-lg transition-all hover:shadow-lg">
                    <i data-lucide="save" class="w-4 h-4 inline mr-1"></i> Create Task
                </button>
                <a href="{{ route('admin.tasks.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
