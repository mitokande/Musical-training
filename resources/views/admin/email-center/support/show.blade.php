@extends('admin.layouts.admin')
@section('page-title', 'Support: '.$conversation->subject)

@section('content')
<div class="space-y-6 max-w-5xl">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <a href="{{ route('admin.support-inbox.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Support Inbox</a>
            <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ $conversation->subject }}</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $conversation->contact_name ? $conversation->contact_name.' — ' : '' }}{{ $conversation->contact_email }}
                @if ($conversation->user)
                    · <a href="{{ route('admin.users.show', $conversation->user) }}" class="text-indigo-600 hover:underline">View member profile</a>
                @endif
            </p>
        </div>
        <form method="POST" action="{{ route('admin.support-inbox.status', $conversation) }}" class="flex items-center gap-2">
            @csrf @method('PUT')
            <select name="status" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                @foreach (\App\Models\SupportConversation::STATUSES as $status)
                    <option value="{{ $status }}" @selected($conversation->status === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Reply (newest messages are listed right below) --}}
    <form method="POST" action="{{ route('admin.support-inbox.reply', $conversation) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-3">
        @csrf
        <h3 class="font-semibold text-gray-800 text-sm">Reply as {{ config('app.name') }} Support</h3>
        <textarea name="body" rows="5" required placeholder="Write your reply…" class="w-full rounded-lg border-gray-300 text-sm">{{ old('body') }}</textarea>
        <div class="flex items-center justify-between">
            <p class="text-xs text-gray-400">Sent from support@harmoniva.app via Amazon SES with a "{{ config('app.name') }} / Support Team" signature and proper threading headers.</p>
            <button class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                <i data-lucide="send" class="w-4 h-4"></i> Send Reply
            </button>
        </div>
    </form>

    <div class="space-y-4">
        @foreach ($conversation->messages as $message)
            <div class="bg-white rounded-xl shadow-sm border {{ $message->direction === 'outbound' ? 'border-indigo-200 ml-8' : 'border-gray-200 mr-8' }} overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between {{ $message->direction === 'outbound' ? 'bg-indigo-50' : 'bg-gray-50' }}">
                    <div class="text-sm">
                        <span class="font-medium text-gray-800">{{ $message->from_name ?: $message->from_email }}</span>
                        <span class="text-gray-400 text-xs">&lt;{{ $message->from_email }}&gt;</span>
                        @if ($message->direction === 'outbound')
                            <span class="text-xs px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 ml-2">reply via SES{{ $message->sentByAdmin ? ' · '.$message->sentByAdmin->name : '' }}</span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-400">{{ $message->received_at?->format('M j, Y H:i') }}</div>
                </div>
                <div class="p-5">
                    @if ($message->html_body_sanitized)
                        <iframe sandbox="" srcdoc="{{ $message->html_body_sanitized }}" class="w-full border-0" style="min-height: 120px;" onload="this.style.height = Math.min(this.contentWindow.document.body.scrollHeight + 40, 800) + 'px'"></iframe>
                    @else
                        <pre class="text-sm text-gray-700 whitespace-pre-wrap font-sans">{{ $message->plain_text_body }}</pre>
                    @endif

                    @if ($message->attachment_metadata)
                        <div class="mt-4 pt-3 border-t border-gray-100 flex flex-wrap gap-2">
                            @foreach ($message->attachment_metadata as $index => $attachment)
                                @if ($attachment['stored'] ?? false)
                                    <a href="{{ route('admin.support-inbox.attachment', [$message, $index]) }}"
                                       class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1.5 bg-gray-100 rounded-lg text-gray-700 hover:bg-gray-200">
                                        <i data-lucide="paperclip" class="w-3 h-3"></i> {{ $attachment['name'] }} ({{ number_format(($attachment['size'] ?? 0) / 1024) }} KB)
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">{{ $attachment['name'] }} (not stored)</span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
