@extends('admin.layouts.admin')

@section('page-title', 'Mobile App — Screen')

@php
    use App\Services\Telemetry\JourneyAnalytics;
@endphp

@section('content')
<div class="space-y-6">
    @include('admin.app-analytics._header')

    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold text-gray-900 font-mono">{{ $name }}</h2>
        <div class="flex gap-3 text-sm">
            <a href="{{ route('admin.app-analytics.flows', $range->query() + ['step' => $name]) }}" class="text-purple-600 hover:underline">Flow after →</a>
            <a href="{{ route('admin.app-analytics.flows', $range->query() + ['step' => $name, 'direction' => 'before']) }}" class="text-purple-600 hover:underline">Flow before →</a>
        </div>
    </div>

    @if (! $screen)
        <div class="card p-8 text-center text-sm text-gray-500">Nobody saw this screen in the range.</div>
    @else
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            @include('admin.components.stat-card', ['title' => 'Views', 'value' => number_format($screen['views']), 'icon' => 'eye', 'color' => 'purple'])
            @include('admin.components.stat-card', ['title' => 'Installs', 'value' => $screen['viewers'] === null ? '—' : number_format($screen['viewers']), 'icon' => 'smartphone', 'color' => 'blue'])
            @include('admin.components.stat-card', ['title' => 'Median time', 'value' => $screen['medianSeconds'] === null ? '—' : $screen['medianSeconds'].'s', 'icon' => 'timer', 'color' => 'indigo'])
            @include('admin.components.stat-card', ['title' => 'Session ended here', 'value' => $screen['exitRate'] === null ? '—' : $screen['exitRate'].'%', 'icon' => 'log-out', 'color' => 'orange'])
            @include('admin.components.stat-card', ['title' => 'Taps', 'value' => number_format($screen['taps']), 'icon' => 'mouse-pointer-click', 'color' => 'teal'])
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @foreach ([['Came from', $incoming, $entries], ['Went to', $outgoing, null]] as [$title, $rows, $starts])
                <div class="card p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ $title }}</h3>
                    @php $total = array_sum($rows) + ($starts ?? 0); @endphp
                    <div class="space-y-2">
                        @if ($starts)
                            <div>
                                <div class="flex justify-between text-xs"><span class="font-mono text-gray-400">(session start)</span><span class="text-gray-500">{{ number_format($starts) }} · {{ round($starts / max(1, $total) * 100) }}%</span></div>
                                <div class="h-1.5 bg-gray-100 rounded mt-0.5"><div class="h-1.5 rounded bg-gray-300" style="width: {{ round($starts / max(1, $total) * 100) }}%"></div></div>
                            </div>
                        @endif
                        @forelse ($rows as $node => $count)
                            <div>
                                <div class="flex justify-between gap-2 text-xs">
                                    @if ($node === JourneyAnalytics::EXIT)
                                        <span class="font-mono text-gray-400">(left the app)</span>
                                    @else
                                        <a href="{{ route('admin.app-analytics.screen', $range->query() + ['name' => $node]) }}" class="font-mono text-gray-800 hover:text-purple-700 truncate">{{ $node }}</a>
                                    @endif
                                    <span class="text-gray-500 whitespace-nowrap">{{ number_format($count) }} · {{ round($count / max(1, $total) * 100) }}%</span>
                                </div>
                                <div class="h-1.5 bg-gray-100 rounded mt-0.5"><div class="h-1.5 rounded {{ $node === JourneyAnalytics::EXIT ? 'bg-gray-300' : 'bg-purple-500/70' }}" style="width: {{ round($count / max(1, $total) * 100) }}%"></div></div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">—</p>
                        @endforelse
                    </div>
                </div>
            @endforeach

            <div class="card p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-1">How long it stays up</h3>
                <p class="text-xs text-gray-400 mb-2">Visits by visible time.</p>
                <div id="dwellChart"></div>
            </div>
        </div>

        <div class="card p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Taps on this screen</h3>
            <p class="text-xs text-gray-400 mb-4"><em>Reach</em> is the share of this screen's visitors who tapped the control at least once.</p>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-4 font-medium">Control</th>
                        <th class="py-2 pr-4 font-medium text-right">Taps</th>
                        <th class="py-2 pr-4 font-medium text-right">Installs</th>
                        <th class="py-2 pr-4 font-medium text-right">Reach</th>
                        <th class="py-2 font-medium w-1/3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($taps as $tap)
                        <tr class="border-b border-gray-50">
                            <td class="py-2 pr-4 font-mono text-xs {{ $tap['control'] === null ? 'text-amber-600 italic' : 'text-gray-900' }}">{{ $tap['control'] ?? 'unnamed' }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($tap['taps']) }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($tap['tappers']) }}</td>
                            <td class="py-2 pr-4 text-right font-semibold">@include('admin.app-analytics._rate', ['value' => $tap['reach']])</td>
                            <td class="py-2"><div class="h-2 bg-gray-100 rounded"><div class="h-2 rounded bg-teal-500/80" style="width: {{ min(100, $tap['reach'] ?? 0) }}%"></div></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-gray-400">Nothing tapped here in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@if ($screen)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    new ApexCharts(document.querySelector('#dwellChart'), {
        // Horizontal: seven text labels do not fit under a third of the page.
        chart: { type: 'bar', height: 250, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
        series: [{ name: 'Visits', data: @json($screen['buckets']) }],
        xaxis: { categories: @json(JourneyAnalytics::DWELL_LABELS), min: 0, labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
        yaxis: { labels: { style: { fontSize: '11px', colors: '#6b7280' } } },
        colors: ['#9333ea'],
        plotOptions: { bar: { horizontal: true, borderRadius: 4, borderRadiusApplication: 'end', barHeight: '70%' } },
        grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: (v) => v.toLocaleString() + ' visits' } },
    }).render();
});
</script>
@endpush
@endif
