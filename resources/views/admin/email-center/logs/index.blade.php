@extends('admin.layouts.admin')
@section('page-title', 'Email Log')

@section('content')
<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">Email Log</h2>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-3 border-b border-gray-100">
            <form method="GET" class="flex gap-2 flex-wrap">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search recipient…" class="rounded-lg border-gray-300 text-sm">
                <select name="status" class="rounded-lg border-gray-300 text-sm">
                    <option value="">All statuses</option>
                    @foreach (['queued', 'sent', 'delivered', 'opened', 'clicked', 'bounced', 'complained', 'failed', 'suppressed'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <select name="type" class="rounded-lg border-gray-300 text-sm">
                    <option value="">All types</option>
                    @foreach (['campaign', 'automation', 'transactional', 'test', 'support_reply'] as $type)
                        <option value="{{ $type }}" @selected(request('type') === $type)>{{ str_replace('_', ' ', ucfirst($type)) }}</option>
                    @endforeach
                </select>
                <select name="automation" class="rounded-lg border-gray-300 text-sm">
                    <option value="">All automations</option>
                    @foreach ($automations as $automation)
                        <option value="{{ $automation->id }}" @selected((string) request('automation') === (string) $automation->id)>{{ $automation->name }}</option>
                    @endforeach
                </select>
                <select name="audience" class="rounded-lg border-gray-300 text-sm">
                    <option value="">All audiences</option>
                    @foreach (['student', 'teacher', 'school'] as $audience)
                        <option value="{{ $audience }}" @selected(request('audience') === $audience)>{{ ucfirst($audience) }}</option>
                    @endforeach
                </select>
                <button class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Filter</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Recipient</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Subject</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Source</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Sent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($messages as $message)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <a href="{{ route('admin.email-center.logs.show', $message) }}" class="text-indigo-600 hover:underline">{{ $message->recipient_email }}</a>
                                @if ($message->user)<div class="text-xs text-gray-400">{{ $message->user->name }}</div>@endif
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ Str::limit($message->subject, 45) }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ str_replace('_', ' ', $message->email_type) }}</td>
                            <td class="px-6 py-3 text-gray-500 text-xs">{{ $message->campaign?->name ?? $message->automation?->name ?? $message->template?->name ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <span class="text-xs px-2 py-1 rounded-full font-medium
                                    @switch($message->status)
                                        @case('delivered') @case('opened') @case('clicked') bg-green-100 text-green-700 @break
                                        @case('sent') bg-blue-100 text-blue-700 @break
                                        @case('queued') bg-amber-100 text-amber-700 @break
                                        @case('bounced') @case('complained') @case('failed') bg-red-100 text-red-700 @break
                                        @default bg-gray-100 text-gray-600
                                    @endswitch">{{ ucfirst($message->status) }}</span>
                            </td>
                            <td class="px-6 py-3 text-gray-500">{{ ($message->sent_at ?? $message->created_at)->format('M j, H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">No emails logged yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-gray-100">{{ $messages->links() }}</div>
    </div>
</div>
@endsection
