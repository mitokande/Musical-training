@extends('admin.layouts.admin')

@section('page-title', 'Compose Message')

@section('content')
<div class="max-w-2xl space-y-6">

    <a href="{{ route('admin.messages.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-purple-600 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Messages
    </a>

    <div class="card p-6">
        <div class="flex items-center gap-2 mb-6">
            <i data-lucide="edit" class="w-5 h-5 text-purple-600"></i>
            <h2 class="text-lg font-semibold text-gray-900">New Message</h2>
        </div>

        <form action="{{ route('admin.messages.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="receiver_id" class="block text-sm font-medium text-gray-700 mb-1">To</label>
                <select name="receiver_id" id="receiver_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">-- Select Recipient --</option>
                    @foreach($users ?? [] as $user)
                        <option value="{{ $user->id }}" {{ old('receiver_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                @error('receiver_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @error('subject') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="body" class="block text-sm font-medium text-gray-700 mb-1">Body</label>
                <textarea name="body" id="body" rows="6" required
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">{{ old('body') }}</textarea>
                @error('body') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" id="type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="message" {{ old('type', 'message') == 'message' ? 'selected' : '' }}>Message</option>
                        <option value="support_ticket" {{ old('type') == 'support_ticket' ? 'selected' : '' }}>Support Ticket</option>
                        <option value="notification" {{ old('type') == 'notification' ? 'selected' : '' }}>Notification</option>
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
            </div>

            <div class="pt-4 border-t border-gray-200 flex items-center gap-3">
                <button type="submit" class="btn-primary px-6 py-2.5 text-white text-sm font-semibold rounded-lg transition-all hover:shadow-lg">
                    <i data-lucide="send" class="w-4 h-4 inline mr-1"></i> Send Message
                </button>
                <a href="{{ route('admin.messages.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
