@extends('admin.layouts.admin')
@section('page-title', 'Email Center')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Email Center</h2>
            <p class="text-sm text-gray-500 mt-1">Amazon SES delivery, engagement and campaign performance — last 30 days</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.email-campaigns.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                <i data-lucide="send" class="w-4 h-4"></i> New Campaign
            </a>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
        @foreach ([
            ['label' => 'Sent', 'value' => number_format($totals['sent']), 'icon' => 'send', 'color' => 'text-indigo-600'],
            ['label' => 'Delivered', 'value' => number_format($totals['delivered']).($totals['delivery_rate'] !== null ? " ({$totals['delivery_rate']}%)" : ''), 'icon' => 'check-circle', 'color' => 'text-green-600'],
            ['label' => 'Opened*', 'value' => number_format($totals['opened']).($totals['open_rate'] !== null ? " ({$totals['open_rate']}%)" : ''), 'icon' => 'mail-open', 'color' => 'text-blue-600'],
            ['label' => 'Clicked', 'value' => number_format($totals['clicked']).($totals['click_rate'] !== null ? " ({$totals['click_rate']}%)" : ''), 'icon' => 'mouse-pointer-click', 'color' => 'text-purple-600'],
            ['label' => 'Bounced', 'value' => number_format($totals['bounced']), 'icon' => 'alert-triangle', 'color' => 'text-orange-600'],
            ['label' => 'Complaints', 'value' => number_format($totals['complained']), 'icon' => 'shield-alert', 'color' => 'text-red-600'],
            ['label' => 'In Queue', 'value' => number_format($totals['queued']), 'icon' => 'clock', 'color' => 'text-gray-600'],
        ] as $card)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase">
                    <i data-lucide="{{ $card['icon'] }}" class="w-3.5 h-3.5 {{ $card['color'] }}"></i> {{ $card['label'] }}
                </div>
                <div class="text-xl font-bold text-gray-800 mt-2">{{ $card['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="bg-blue-50 border border-blue-200 text-blue-800 text-xs rounded-lg px-4 py-3 flex gap-2">
        <i data-lucide="info" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
        <span>* Open rate is an estimated metric because privacy protections and blocked tracking pixels can affect results. Click, conversion, exercise activity, and Premium upgrade data are more reliable indicators of campaign performance.</span>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Recent campaigns --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Recent Campaigns</h3>
                <a href="{{ route('admin.email-campaigns.index') }}" class="text-sm text-indigo-600 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse ($recentCampaigns as $campaign)
                    <a href="{{ route('admin.email-campaigns.show', $campaign) }}" class="flex items-center justify-between px-6 py-3 hover:bg-gray-50">
                        <div>
                            <div class="text-sm font-medium text-gray-800">{{ $campaign->name }}</div>
                            <div class="text-xs text-gray-500">{{ $campaign->created_at->format('M j, Y') }} · {{ number_format($campaign->sent_count) }} sent</div>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full font-medium
                            @switch($campaign->status)
                                @case('sent') bg-green-100 text-green-700 @break
                                @case('sending') bg-blue-100 text-blue-700 @break
                                @case('scheduled') bg-amber-100 text-amber-700 @break
                                @case('failed') @case('cancelled') bg-red-100 text-red-700 @break
                                @default bg-gray-100 text-gray-600
                            @endswitch">{{ ucfirst($campaign->status) }}</span>
                    </a>
                @empty
                    <div class="px-6 py-8 text-sm text-gray-500 text-center">No campaigns yet. <a href="{{ route('admin.email-campaigns.create') }}" class="text-indigo-600 hover:underline">Create the first one</a>.</div>
                @endforelse
            </div>
        </div>

        {{-- Recent events --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Latest Email Events</h3>
                <a href="{{ route('admin.email-center.logs') }}" class="text-sm text-indigo-600 hover:underline">Email log</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse ($recentEvents as $event)
                    <div class="flex items-center justify-between px-6 py-3">
                        <div>
                            <div class="text-sm text-gray-800">{{ $event->recipient_email }}</div>
                            <div class="text-xs text-gray-500">{{ $event->occurred_at?->diffForHumans() }} · {{ $event->source }}</div>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full font-medium
                            @switch($event->event_type)
                                @case('delivered') bg-green-100 text-green-700 @break
                                @case('opened') bg-blue-100 text-blue-700 @break
                                @case('clicked') bg-purple-100 text-purple-700 @break
                                @case('bounced') bg-orange-100 text-orange-700 @break
                                @case('complained') bg-red-100 text-red-700 @break
                                @default bg-gray-100 text-gray-600
                            @endswitch">{{ str_replace('_', ' ', $event->event_type) }}</span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-sm text-gray-500 text-center">No events recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 14 day activity table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Daily Activity (14 days)</h3>
            <a href="{{ route('admin.email-center.suppressions') }}" class="text-sm text-gray-500">Suppression list: <span class="font-semibold text-gray-700">{{ number_format($suppressionCount) }}</span></a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Sent</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Delivered</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Opened*</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Clicked</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Bounced</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($daily as $day => $events)
                        @php $byType = $events->keyBy('event_type'); @endphp
                        <tr>
                            <td class="px-6 py-3 text-gray-700">{{ \Carbon\Carbon::parse($day)->format('M j, Y') }}</td>
                            <td class="px-6 py-3 text-right">{{ number_format($byType['sent']->total ?? 0) }}</td>
                            <td class="px-6 py-3 text-right text-green-700">{{ number_format($byType['delivered']->total ?? 0) }}</td>
                            <td class="px-6 py-3 text-right text-blue-700">{{ number_format($byType['opened']->total ?? 0) }}</td>
                            <td class="px-6 py-3 text-right text-purple-700">{{ number_format($byType['clicked']->total ?? 0) }}</td>
                            <td class="px-6 py-3 text-right text-orange-700">{{ number_format($byType['bounced']->total ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No email activity in the last 14 days.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
