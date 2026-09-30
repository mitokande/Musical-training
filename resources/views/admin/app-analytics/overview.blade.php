@extends('admin.layouts.admin')

@section('page-title', 'Mobile App — Overview')

@section('content')
<div class="space-y-6">
    @include('admin.app-analytics._header')

    {{-- Active: rolling windows ending on the last day of the range. --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('admin.components.stat-card', ['title' => 'Active installs · day', 'value' => number_format($active['day']['installs']), 'icon' => 'smartphone', 'color' => 'purple'])
        @include('admin.components.stat-card', ['title' => 'Active installs · 7 days', 'value' => number_format($active['week']['installs']), 'icon' => 'calendar-days', 'color' => 'blue'])
        @include('admin.components.stat-card', ['title' => 'Active installs · 30 days', 'value' => number_format($active['month']['installs']), 'icon' => 'calendar-range', 'color' => 'indigo'])
        @include('admin.components.stat-card', ['title' => 'Signed-in learners · 7 days', 'value' => number_format($active['week']['users']), 'icon' => 'user-check', 'color' => 'green'])
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @include('admin.components.stat-card', ['title' => 'New installs in range', 'value' => number_format($newInstalls), 'icon' => 'download', 'color' => 'teal'])
        @include('admin.components.stat-card', ['title' => 'Sessions in range', 'value' => number_format($sessions), 'icon' => 'layers', 'color' => 'orange'])
        @include('admin.components.stat-card', ['title' => 'Avg. session', 'value' => $avgSessionMinutes === null ? '—' : $avgSessionMinutes.' min', 'icon' => 'timer', 'color' => 'pink'])
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            @include('admin.components.chart-card', ['title' => 'Daily activity', 'chartId' => 'activityChart'])
        </div>
        @include('admin.components.chart-card', ['title' => 'New installs per day', 'chartId' => 'newInstallsChart'])
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="card p-5 lg:col-span-2">
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Screens</h3>
            <p class="text-xs text-gray-400 mb-4">Average time excludes stays over 30 minutes — that is the app in the background, not someone reading.</p>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 border-b">
                            <th class="py-2 pr-4 font-medium">Screen</th>
                            <th class="py-2 pr-4 font-medium text-right">Views</th>
                            <th class="py-2 pr-4 font-medium text-right">Installs</th>
                            <th class="py-2 font-medium text-right">Avg. time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($screens as $screen)
                            <tr class="border-b border-gray-50">
                                <td class="py-2 pr-4 font-mono text-xs text-gray-800">
                                    @if ($screen['screen'] === 'onboarding')
                                        {{ $screen['screen'] }}
                                    @else
                                        <a href="{{ route('admin.app-analytics.screen', $range->query() + ['name' => $screen['screen']]) }}" class="hover:text-purple-700 hover:underline">{{ $screen['screen'] }}</a>
                                    @endif
                                </td>
                                <td class="py-2 pr-4 text-right">{{ number_format($screen['views']) }}</td>
                                <td class="py-2 pr-4 text-right">{{ number_format($screen['installs']) }}</td>
                                <td class="py-2 text-right text-gray-600">{{ $screen['avgSeconds'] === null ? '—' : $screen['avgSeconds'].'s' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-gray-400">No screen views in this range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">App versions</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-4 font-medium">Platform</th>
                        <th class="py-2 pr-4 font-medium">Version</th>
                        <th class="py-2 font-medium text-right">Installs</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($versions as $version)
                        <tr class="border-b border-gray-50">
                            <td class="py-2 pr-4">{{ $version->platform }}</td>
                            <td class="py-2 pr-4 font-mono text-xs">{{ $version->app_version ?? '—' }}</td>
                            <td class="py-2 text-right">{{ number_format($version->installs) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-6 text-center text-gray-400">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-5">
        <div class="flex items-baseline justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700">Busiest events</h3>
            <a href="{{ route('admin.app-analytics.events', $range->query()) }}" class="text-xs text-purple-600 hover:underline">All events →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
            @forelse ($topEvents as $event)
                <div class="flex items-center justify-between gap-3 text-sm border-b border-gray-50 py-1.5">
                    <a href="{{ route('admin.app-analytics.event', ['name' => $event['name']] + $range->query()) }}" class="font-mono text-xs text-purple-700 hover:underline">{{ $event['name'] }}</a>
                    <span class="text-gray-900">{{ number_format($event['events']) }} <span class="text-xs text-gray-400">· {{ number_format($event['installs']) }} installs</span></span>
                </div>
            @empty
                <p class="text-sm text-gray-400">No events in this range.</p>
            @endforelse
        </div>
    </div>

    <div class="card p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-1">Server errors seen by the app</h3>
        <p class="text-xs text-gray-400 mb-4">5xx responses only, as the app reported them. Paths are routes, with ids replaced by <code>:id</code>.</p>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-500 border-b">
                    <th class="py-2 pr-4 font-medium">Request</th>
                    <th class="py-2 pr-4 font-medium text-right">Status</th>
                    <th class="py-2 pr-4 font-medium text-right">Count</th>
                    <th class="py-2 pr-4 font-medium text-right">Installs</th>
                    <th class="py-2 font-medium text-right">Last seen</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($serverErrors as $error)
                    <tr class="border-b border-gray-50">
                        <td class="py-2 pr-4 font-mono text-xs">{{ $error->method }} {{ $error->path }}</td>
                        <td class="py-2 pr-4 text-right text-red-600">{{ $error->status }}</td>
                        <td class="py-2 pr-4 text-right">{{ number_format($error->count) }}</td>
                        <td class="py-2 pr-4 text-right">{{ number_format($error->installs) }}</td>
                        <td class="py-2 text-right text-gray-500">{{ \Illuminate\Support\Carbon::parse($error->last_at)->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-gray-400">No server errors reported. </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const trend = @json($daily);
    const axis = { labels: { style: { fontSize: '11px', colors: '#9ca3af' } } };

    new ApexCharts(document.querySelector('#activityChart'), {
        chart: { type: 'line', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
        series: [
            { name: 'Active installs', data: trend.map(d => d.installs) },
            { name: 'Signed-in learners', data: trend.map(d => d.users) },
            { name: 'Sessions', data: trend.map(d => d.sessions) },
        ],
        xaxis: { categories: trend.map(d => d.day), ...axis, tickAmount: 10 },
        yaxis: { ...axis, min: 0, forceNiceScale: true },
        stroke: { curve: 'smooth', width: 2.5 },
        colors: ['#9333ea', '#10b981', '#f97316'],
        grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
        legend: { position: 'top', fontSize: '12px' },
        dataLabels: { enabled: false },
    }).render();

    new ApexCharts(document.querySelector('#newInstallsChart'), {
        chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
        series: [{ name: 'New installs', data: trend.map(d => d.new) }],
        xaxis: { categories: trend.map(d => d.day), ...axis, tickAmount: 6 },
        yaxis: { ...axis, min: 0, forceNiceScale: true },
        colors: ['#14b8a6'],
        plotOptions: { bar: { borderRadius: 3, columnWidth: '60%' } },
        grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
        dataLabels: { enabled: false },
    }).render();
});
</script>
@endpush
