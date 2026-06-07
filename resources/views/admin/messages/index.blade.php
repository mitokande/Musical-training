@extends('admin.layouts.admin')

@section('page-title', 'Messages')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <p class="text-sm text-gray-500">Manage messages, support tickets, and notifications.</p>
        <a href="{{ route('admin.messages.create') }}" class="btn-primary inline-flex items-center gap-2 px-4 py-2.5 text-white text-sm font-semibold rounded-lg transition-all hover:shadow-lg">
            <i data-lucide="edit" class="w-4 h-4"></i> Compose Message
        </a>
    </div>

    {{-- Filters --}}
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.messages.index') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                <select name="type" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">All</option>
                    <option value="message" {{ request('type') == 'message' ? 'selected' : '' }}>Message</option>
                    <option value="support_ticket" {{ request('type') == 'support_ticket' ? 'selected' : '' }}>Support Ticket</option>
                    <option value="notification" {{ request('type') == 'notification' ? 'selected' : '' }}>Notification</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">All</option>
                    <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Unread</option>
                    <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
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
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-700 rounded-lg transition-colors">
                <i data-lucide="filter" class="w-4 h-4 inline mr-1"></i> Filter
            </button>
        </form>
    </div>

    {{-- Messages Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Subject</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">From</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">To</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Type</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Priority</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Status</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Date</th>
                        <th class="text-right py-3 px-4 font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($messages ?? [] as $message)
                    @php
                        $typeColors = [
                            'message' => 'bg-blue-100 text-blue-700',
                            'support_ticket' => 'bg-orange-100 text-orange-700',
                            'notification' => 'bg-purple-100 text-purple-700',
                        ];
                        $priorityColors = [
                            'low' => 'bg-gray-100 text-gray-700',
                            'normal' => 'bg-blue-100 text-blue-700',
                            'high' => 'bg-orange-100 text-orange-700',
                            'urgent' => 'bg-red-100 text-red-700',
                        ];
                        $statusColors = [
                            'unread' => 'bg-yellow-100 text-yellow-700',
                            'read' => 'bg-green-100 text-green-700',
                            'archived' => 'bg-gray-100 text-gray-700',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors {{ ($message->status ?? '') == 'unread' ? 'bg-purple-50/30' : '' }}">
                        <td class="py-3 px-4 font-medium text-gray-900">
                            <a href="{{ route('admin.messages.show', $message) }}" class="hover:text-purple-600">{{ $message->subject }}</a>
                        </td>
                        <td class="py-3 px-4 text-gray-600">{{ $message->sender->name ?? '-' }}</td>
                        <td class="py-3 px-4 text-gray-600">{{ $message->receiver->name ?? '-' }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $typeColors[$message->type] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst(str_replace('_', ' ', $message->type ?? 'message')) }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $priorityColors[$message->priority] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($message->priority ?? 'normal') }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$message->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($message->status ?? 'unread') }}</span>
                        </td>
                        <td class="py-3 px-4 text-gray-600">{{ $message->created_at->format('M d, Y') }}</td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.messages.show', $message) }}" class="p-1.5 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50" title="View">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                @if(($message->status ?? '') == 'unread')
                                <form action="{{ route('admin.messages.mark-read', $message) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-green-600 rounded-lg hover:bg-green-50" title="Mark Read">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                                @if(($message->status ?? '') != 'archived')
                                <form action="{{ route('admin.messages.archive', $message) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-orange-600 rounded-lg hover:bg-orange-50" title="Archive">
                                        <i data-lucide="archive" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-gray-500">No messages found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if(method_exists($messages ?? collect(), 'links'))
    <div>{{ $messages->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
