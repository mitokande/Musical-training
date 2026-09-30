@extends('admin.layouts.admin')

@section('page-title', 'Mobile App — '.$name)

@php
    use App\Services\Telemetry\EventPresenter;
    $tones = EventPresenter::TONES;
    $display = fn ($value) => $value === null ? '∅' : (is_bool($value) ? ($value ? 'true' : 'false') : (string) $value);
@endphp

@section('content')
<div class="space-y-6">
    @include('admin.app-analytics._header')

    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold text-gray-900 font-mono">{{ $name }}</h2>
        <a href="{{ route('admin.app-analytics.events', $range->query()) }}" class="text-sm text-gray-500 hover:text-purple-600">← All events</a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @include('admin.components.stat-card', ['title' => 'Count', 'value' => number_format($totals['events']), 'icon' => 'hash', 'color' => 'purple'])
        @include('admin.components.stat-card', ['title' => 'Installs', 'value' => number_format($totals['installs']), 'icon' => 'smartphone', 'color' => 'blue'])
        @include('admin.components.stat-card', ['title' => 'Signed-in learners', 'value' => number_format($totals['users']), 'icon' => 'user-check', 'color' => 'green'])
        @include('admin.components.stat-card', ['title' => 'Per install', 'value' => $totals['installs'] ? round($totals['events'] / $totals['installs'], 1) : '—', 'icon' => 'divide', 'color' => 'orange'])
    </div>

    {{-- Two charts, not one with two scales: a count and a count of people are different sizes. --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @include('admin.components.chart-card', ['title' => 'Per day', 'chartId' => 'eventsChart'])
        @include('admin.components.chart-card', ['title' => 'Installs per day', 'chartId' => 'installsChart'])
    </div>

    <div class="grid grid-cols-1 {{ $screens ? 'lg:grid-cols-3' : '' }} gap-6">
        <div class="card p-5 {{ $screens ? 'lg:col-span-2' : '' }}">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h3 class="text-sm font-semibold text-gray-700">By <span class="font-mono">{{ $by }}</span></h3>
                <div class="flex flex-wrap gap-1">
                    @foreach (array_merge($keys, \App\Services\Telemetry\EventMetrics::COLUMNS) as $key)
                        <a href="{{ request()->fullUrlWithQuery(['by' => $key]) }}"
                           class="px-2 py-1 text-xs font-mono rounded {{ $key === $by ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">{{ $key }}</a>
                    @endforeach
                </div>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-4 font-medium">Value</th>
                        <th class="py-2 pr-4 font-medium text-right">Count</th>
                        <th class="py-2 pr-4 font-medium text-right">Installs</th>
                        <th class="py-2 pr-4 font-medium text-right">Share</th>
                        <th class="py-2 font-medium w-1/4"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($breakdown as $row)
                        <tr class="border-b border-gray-50">
                            <td class="py-2 pr-4 font-mono text-xs break-all">
                                @if ($by === 'screen' && $row['value'] !== null)
                                    <a href="{{ route('admin.app-analytics.screen', $range->query() + ['name' => $row['value']]) }}" class="text-purple-700 hover:underline">{{ $row['value'] }}</a>
                                @else
                                    {{ $display($row['value']) }}
                                @endif
                            </td>
                            <td class="py-2 pr-4 text-right">{{ number_format($row['events']) }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($row['installs']) }}</td>
                            <td class="py-2 pr-4 text-right text-gray-600">@include('admin.app-analytics._rate', ['value' => $row['share']])</td>
                            <td class="py-2"><div class="h-2 bg-gray-100 rounded"><div class="h-2 rounded bg-purple-500/70" style="width: {{ min(100, $row['share'] ?? 0) }}%"></div></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-gray-400">Nothing in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($screens)
            <div class="card p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Where it happens</h3>
                <div class="space-y-2">
                    @foreach ($screens as $row)
                        <div>
                            <div class="flex justify-between gap-2 text-xs">
                                <span class="font-mono text-gray-800 truncate">{{ $row['value'] ?? '(no screen)' }}</span>
                                <span class="text-gray-500 whitespace-nowrap">{{ number_format($row['events']) }} · @include('admin.app-analytics._rate', ['value' => $row['share']])</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded mt-0.5"><div class="h-1.5 rounded bg-purple-500/70" style="width: {{ min(100, $row['share'] ?? 0) }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="card p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Latest</h3>
        <div class="space-y-1">
            @forelse ($recent as $event)
                @php
                    $d = EventPresenter::describe($event->name, $event->props ?? []);
                    [$dot, $text] = $tones[$d['tone']] ?? $tones['system'];
                @endphp
                <div class="grid grid-cols-12 gap-3 items-start text-sm py-1 px-2 rounded hover:bg-gray-50">
                    <div class="col-span-12 sm:col-span-2 text-xs text-gray-500 font-mono">{{ $event->occurred_at->format('M j H:i:s') }}</div>
                    <div class="col-span-12 sm:col-span-7 flex items-start gap-2">
                        <span class="flex-none w-5 h-5 rounded-full flex items-center justify-center {{ $dot }}"><i data-lucide="{{ $d['icon'] }}" class="w-3 h-3"></i></span>
                        <span class="{{ $text }} font-medium">{{ $d['title'] }}</span>
                        @if ($d['detail'])<span class="text-xs text-gray-500 pt-0.5">{{ $d['detail'] }}</span>@endif
                    </div>
                    <div class="col-span-12 sm:col-span-3 text-xs text-gray-500 sm:text-right">
                        <span class="font-mono text-gray-400">{{ $event->screen }}</span> ·
                        <a href="{{ route('admin.app-analytics.journey', ['q' => $event->user?->id ?? $event->install_id, 'session' => $event->session_id]) }}" class="text-purple-600 hover:underline">{{ $event->user ? Str::limit($event->user->name, 16) : 'anonymous' }}</a>
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-gray-400 text-sm">Nothing in this range.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const daily = @json($daily);
    const axis = { labels: { style: { fontSize: '11px', colors: '#9ca3af' } } };
    const base = (type, name, key, colour) => ({
        chart: { type, height: 260, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
        series: [{ name, data: daily.map(d => d[key]) }],
        xaxis: { categories: daily.map(d => d.day), ...axis, tickAmount: 8 },
        yaxis: { ...axis, min: 0, forceNiceScale: true },
        colors: [colour],
        grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        plotOptions: { bar: { borderRadius: 3, borderRadiusApplication: 'end', columnWidth: '60%' } },
    });
    new ApexCharts(document.querySelector('#eventsChart'), base('area', 'Events', 'events', '#9333ea')).render();
    new ApexCharts(document.querySelector('#installsChart'), base('bar', 'Installs', 'installs', '#6366f1')).render();
});
</script>
@endpush
