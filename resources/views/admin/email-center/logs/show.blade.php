@extends('admin.layouts.admin')
@section('page-title', 'Email Detail')

@section('content')
<div class="space-y-6 max-w-4xl">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Email to {{ $message->recipient_email }}</h2>
        <a href="{{ route('admin.email-center.logs') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to log</a>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Details</h3>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between gap-4"><dt class="text-gray-500">Subject</dt><dd class="text-gray-800 text-right">{{ $message->subject }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Type</dt><dd class="text-gray-800">{{ str_replace('_', ' ', $message->email_type) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd class="text-gray-800 font-medium">{{ ucfirst($message->status) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">User</dt><dd class="text-gray-800">{{ $message->user?->name ?? 'Guest' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Campaign</dt><dd class="text-gray-800">{{ $message->campaign?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Automation</dt><dd class="text-gray-800">{{ $message->automation?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Template</dt><dd class="text-gray-800">{{ $message->template?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Queued</dt><dd class="text-gray-800">{{ $message->created_at->format('M j, Y H:i:s') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Sent</dt><dd class="text-gray-800">{{ $message->sent_at?->format('M j, Y H:i:s') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Delivered</dt><dd class="text-gray-800">{{ $message->delivered_at?->format('M j, Y H:i:s') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">First open</dt><dd class="text-gray-800">{{ $message->opened_at?->format('M j, Y H:i:s') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">First click</dt><dd class="text-gray-800">{{ $message->clicked_at?->format('M j, Y H:i:s') ?? '—' }}</dd></div>
                <div class="flex flex-col gap-1"><dt class="text-gray-500">SES Message ID</dt><dd class="text-gray-800 font-mono text-xs break-all">{{ $message->ses_message_id ?? '—' }}</dd></div>
                @if ($message->error)
                    <div class="flex flex-col gap-1"><dt class="text-gray-500">Error</dt><dd class="text-red-600 text-xs">{{ $message->error }}</dd></div>
                @endif
            </dl>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Event Timeline</h3>
            <div class="space-y-3">
                @forelse ($message->events as $event)
                    <div class="flex gap-3 text-sm">
                        <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0
                            @switch($event->event_type)
                                @case('delivered') bg-green-500 @break
                                @case('opened') bg-blue-500 @break
                                @case('clicked') bg-purple-500 @break
                                @case('bounced') @case('complained') bg-red-500 @break
                                @default bg-gray-400
                            @endswitch"></div>
                        <div>
                            <div class="text-gray-800 font-medium">{{ str_replace('_', ' ', ucfirst($event->event_type)) }}
                                <span class="text-xs text-gray-400 font-normal">via {{ $event->source }}</span>
                            </div>
                            <div class="text-xs text-gray-500">{{ $event->occurred_at?->format('M j, Y H:i:s') }}</div>
                            @if (!empty($event->metadata['url']))
                                <div class="text-xs text-indigo-600 break-all">{{ $event->metadata['url'] }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No events recorded.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
