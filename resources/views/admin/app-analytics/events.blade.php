@extends('admin.layouts.admin')

@section('page-title', 'Mobile App — Events')

@section('content')
<div class="space-y-6" x-data="{ filter: '' }">
    @include('admin.app-analytics._header')

    <div class="card p-5">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-1">
            <h3 class="text-sm font-semibold text-gray-700">Every event the app sends</h3>
            <input type="search" x-model="filter" placeholder="Filter by name…"
                   class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
        </div>
        <p class="text-xs text-gray-400 mb-4">
            Busiest first. <em>Change</em> is against the same number of days just before the range. Open an event to break it
            down by any of its properties, by screen, or by app version.
        </p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-4 font-medium">Event</th>
                        <th class="py-2 pr-4 font-medium text-right">Count</th>
                        <th class="py-2 pr-4 font-medium text-right">Change</th>
                        <th class="py-2 pr-4 font-medium text-right">Installs</th>
                        <th class="py-2 pr-4 font-medium text-right">Signed in</th>
                        <th class="py-2 pr-4 font-medium text-right">Per session</th>
                        <th class="py-2 font-medium">Per day</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        @php
                            $series = $event['series'];
                            $peak = max(1, max($series ?: [0]));
                            $n = max(1, count($series) - 1);
                            $points = collect($series)->map(fn ($v, $i) => round($i / $n * 118 + 1, 1).','.round(27 - $v / $peak * 25, 1))->implode(' ');
                        @endphp
                        <tr class="border-b border-gray-50 hover:bg-gray-50" x-show="! filter || '{{ $event['name'] }}'.includes(filter.toLowerCase())">
                            <td class="py-2 pr-4 font-mono text-xs">
                                <a href="{{ route('admin.app-analytics.event', ['name' => $event['name']] + $range->query()) }}" class="text-purple-700 hover:underline">{{ $event['name'] }}</a>
                            </td>
                            <td class="py-2 pr-4 text-right font-semibold">{{ number_format($event['events']) }}</td>
                            <td class="py-2 pr-4 text-right text-xs">
                                @if ($event['change'] === null)
                                    <span class="text-gray-300">new</span>
                                @else
                                    <span class="{{ $event['change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $event['change'] >= 0 ? '+' : '' }}{{ $event['change'] }}%</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4 text-right">{{ number_format($event['installs']) }}</td>
                            <td class="py-2 pr-4 text-right text-gray-600">{{ number_format($event['users']) }}</td>
                            <td class="py-2 pr-4 text-right text-gray-600">{{ $event['perSession'] ?? '—' }}</td>
                            <td class="py-2">
                                <svg width="120" height="28" viewBox="0 0 120 28" class="overflow-visible" role="img" aria-label="{{ $event['name'] }} per day, peak {{ max($series ?: [0]) }}">
                                    <title>Peak {{ number_format(max($series ?: [0])) }} a day</title>
                                    <line x1="0" y1="27" x2="120" y2="27" stroke="#f3f4f6" stroke-width="1" />
                                    <polyline points="{{ $points }}" fill="none" stroke="#9333ea" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round" />
                                </svg>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-6 text-center text-gray-400">No events in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
