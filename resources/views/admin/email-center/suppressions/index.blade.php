@extends('admin.layouts.admin')
@section('page-title', 'Email Suppressions')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Suppression List</h2>
            <p class="text-sm text-gray-500 mt-1">Addresses that never receive marketing email. Hard bounces and complaints are added automatically from SES events.</p>
        </div>
        <form method="POST" action="{{ route('admin.email-center.suppressions.store') }}" class="flex gap-2 items-center flex-wrap">
            @csrf
            <input type="email" name="email" required placeholder="address@example.com" class="rounded-lg border-gray-300 text-sm">
            <select name="reason" class="rounded-lg border-gray-300 text-sm">
                @foreach (\App\Models\EmailSuppression::REASONS as $reason)
                    <option value="{{ $reason }}" @selected($reason === 'manual')>{{ str_replace('_', ' ', ucfirst($reason)) }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 text-sm">Suppress</button>
        </form>
    </div>

    @if ($softBouncers->isNotEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
            <h3 class="text-sm font-semibold text-amber-800 flex items-center gap-2"><i data-lucide="alert-triangle" class="w-4 h-4"></i> Repeat soft bounces (last 30 days)</h3>
            <p class="text-xs text-amber-700 mt-1">These addresses soft-bounced {{ config('email-center.soft_bounce_threshold') }}+ times. Consider suppressing them manually.</p>
            <div class="mt-3 space-y-1">
                @foreach ($softBouncers as $bouncer)
                    <form method="POST" action="{{ route('admin.email-center.suppressions.store') }}" class="flex items-center gap-3 text-sm">
                        @csrf
                        <input type="hidden" name="email" value="{{ $bouncer->recipient_email }}">
                        <input type="hidden" name="reason" value="soft_bounce">
                        <span class="text-amber-900">{{ $bouncer->recipient_email }}</span>
                        <span class="text-xs text-amber-600">{{ $bouncer->bounce_count }} bounces</span>
                        <button class="text-xs px-2 py-1 bg-amber-600 text-white rounded hover:bg-amber-700">Suppress</button>
                    </form>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-3 border-b border-gray-100">
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search email…" class="rounded-lg border-gray-300 text-sm">
                <select name="reason" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                    <option value="">All reasons</option>
                    @foreach (\App\Models\EmailSuppression::REASONS as $reason)
                        <option value="{{ $reason }}" @selected(request('reason') === $reason)>{{ str_replace('_', ' ', ucfirst($reason)) }}</option>
                    @endforeach
                </select>
                <button class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Filter</button>
            </form>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Email</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Reason</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Source</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($suppressions as $suppression)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-gray-800">{{ $suppression->email }}</td>
                        <td class="px-6 py-3">
                            <span class="text-xs px-2 py-1 rounded-full font-medium
                                @switch($suppression->reason)
                                    @case('hard_bounce') bg-orange-100 text-orange-700 @break
                                    @case('complaint') bg-red-100 text-red-700 @break
                                    @case('unsubscribe') bg-blue-100 text-blue-700 @break
                                    @default bg-gray-100 text-gray-600
                                @endswitch">{{ str_replace('_', ' ', $suppression->reason) }}</span>
                        </td>
                        <td class="px-6 py-3 text-gray-500">{{ $suppression->source ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $suppression->suppressed_at->format('M j, Y H:i') }}</td>
                        <td class="px-6 py-3 text-right">
                            <form method="POST" action="{{ route('admin.email-center.suppressions.destroy', $suppression) }}"
                                  onsubmit="return confirm('Remove {{ $suppression->email }} from the suppression list? They will start receiving marketing email again.')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-600 hover:underline">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">No suppressed addresses.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t border-gray-100">{{ $suppressions->links() }}</div>
    </div>
</div>
@endsection
