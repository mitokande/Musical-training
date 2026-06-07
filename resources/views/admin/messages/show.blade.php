@extends('admin.layouts.admin')

@section('page-title', 'Message Thread')

@section('content')
<div class="max-w-3xl space-y-6">

    <a href="{{ route('admin.messages.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-purple-600 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Messages
    </a>

    {{-- Original Message --}}
    <div class="card p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900">{{ $message->subject }}</h2>
                <div class="flex items-center gap-3 mt-2 text-sm text-gray-500">
                    <span><strong>From:</strong> {{ $message->sender->name ?? 'System' }}</span>
                    <span><strong>To:</strong> {{ $message->receiver->name ?? 'N/A' }}</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $message->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @php
                    $statusColors = [
                        'unread' => 'bg-yellow-100 text-yellow-700',
                        'read' => 'bg-green-100 text-green-700',
                        'archived' => 'bg-gray-100 text-gray-700',
                    ];
                @endphp
                <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $statusColors[$message->status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($message->status ?? 'unread') }}</span>
            </div>
        </div>

        <div class="prose prose-sm max-w-none text-gray-700 border-t border-gray-100 pt-4">
            {!! nl2br(e($message->body)) !!}
        </div>
    </div>

    {{-- Thread Replies --}}
    @if(($replies ?? collect())->count() > 0)
    <div class="space-y-4">
        <h3 class="text-sm font-semibold text-gray-600 flex items-center gap-2">
            <i data-lucide="message-circle" class="w-4 h-4"></i> Replies ({{ $replies->count() }})
        </h3>

        @foreach($replies as $reply)
        <div class="card p-5 {{ $reply->sender_id === auth()->id() ? 'border-l-4 border-purple-400' : 'border-l-4 border-gray-200' }}">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-purple-100 flex items-center justify-center text-xs font-semibold text-purple-700">
                        {{ substr($reply->sender->name ?? 'U', 0, 1) }}
                    </div>
                    <span class="text-sm font-medium text-gray-900">{{ $reply->sender->name ?? 'Unknown' }}</span>
                </div>
                <span class="text-xs text-gray-400">{{ $reply->created_at->format('M d, Y H:i') }}</span>
            </div>
            <div class="text-sm text-gray-700 ml-9">
                {!! nl2br(e($reply->body)) !!}
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Reply Form --}}
    <div class="card p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <i data-lucide="reply" class="w-4 h-4 text-purple-600"></i> Reply
        </h3>

        <form action="{{ route('admin.messages.reply', $message) }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <textarea name="body" rows="4" required placeholder="Type your reply..."
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">{{ old('body') }}</textarea>
                @error('body') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn-primary px-6 py-2.5 text-white text-sm font-semibold rounded-lg transition-all hover:shadow-lg">
                <i data-lucide="send" class="w-4 h-4 inline mr-1"></i> Send Reply
            </button>
        </form>
    </div>
</div>
@endsection
