@extends('admin.layouts.admin')
@section('page-title', 'Support Inbox')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Support Inbox</h2>
            <p class="text-sm text-gray-500 mt-1">support@harmoniva.app — fetched from the local mail server every 5 minutes; replies go out via Amazon SES.</p>
        </div>
        <a href="{{ $webmailUrl }}" target="_blank" rel="noopener" class="text-sm text-indigo-600 hover:underline">Open webmail ↗</a>
    </div>

    @if ($mode !== 'imap')
        <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-lg px-4 py-3">
            Support inbound mode is <strong>{{ $mode }}</strong> — new mail is not being imported. Use the webmail link above, or set <code>SUPPORT_INBOUND_MODE=imap</code>.
        </div>
    @endif

    <div class="flex gap-2">
        @foreach (['' => 'All', 'open' => 'Open', 'pending' => 'Pending', 'closed' => 'Closed'] as $value => $label)
            <a href="{{ route('admin.support-inbox.index', array_filter(['status' => $value])) }}"
               class="px-3 py-1.5 text-sm rounded-lg {{ request('status', '') === $value ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                {{ $label }}
                @if ($value !== '')<span class="text-xs opacity-75">({{ $counts[$value] ?? 0 }})</span>@endif
            </a>
        @endforeach
        <form method="GET" class="ml-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search subject or email…" class="rounded-lg border-gray-300 text-sm w-64">
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden divide-y divide-gray-100">
        @forelse ($conversations as $conversation)
            <a href="{{ route('admin.support-inbox.show', $conversation) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50">
                <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold flex-shrink-0">
                    {{ strtoupper(substr($conversation->contact_name ?: $conversation->contact_email, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-800 truncate">{{ $conversation->subject }}</span>
                        @if ($conversation->user)
                            <span class="text-xs px-1.5 py-0.5 rounded bg-green-50 text-green-700 border border-green-200">member</span>
                        @else
                            <span class="text-xs px-1.5 py-0.5 rounded bg-gray-50 text-gray-500 border border-gray-200">guest</span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-500 truncate">{{ $conversation->contact_name ? $conversation->contact_name.' · ' : '' }}{{ $conversation->contact_email }} · {{ $conversation->message_count }} message(s)</div>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="text-xs px-2 py-1 rounded-full font-medium
                        @switch($conversation->status)
                            @case('open') bg-blue-100 text-blue-700 @break
                            @case('pending') bg-amber-100 text-amber-700 @break
                            @default bg-gray-100 text-gray-600
                        @endswitch">{{ ucfirst($conversation->status) }}</span>
                    <div class="text-xs text-gray-400 mt-1">{{ $conversation->last_message_at?->diffForHumans() }}</div>
                </div>
            </a>
        @empty
            <div class="px-6 py-12 text-center text-gray-500 text-sm">No support conversations yet. New mail to support@harmoniva.app appears here automatically.</div>
        @endforelse
    </div>
    <div>{{ $conversations->links() }}</div>
</div>
@endsection
