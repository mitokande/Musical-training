@extends('admin.layouts.admin')

@section('page-title', 'Exercise Reports')

@section('content')
<div class="space-y-6">

    {{-- Export --}}
    <div class="flex justify-end">
        <a href="{{ route('admin.reports.export', ['type' => 'exercises']) }}" class="px-4 py-2 bg-green-50 hover:bg-green-100 text-sm font-medium text-green-700 rounded-lg transition-colors">
            <i data-lucide="download" class="w-4 h-4 inline mr-1"></i> Export
        </a>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('admin.components.stat-card', ['title' => 'Total Completed', 'value' => number_format($totalCompleted ?? 0), 'icon' => 'check-circle', 'color' => 'purple'])
        @include('admin.components.stat-card', ['title' => 'Today', 'value' => $today ?? 0, 'icon' => 'calendar', 'color' => 'blue'])
        @include('admin.components.stat-card', ['title' => 'Avg Score', 'value' => ($avgScore ?? 0) . '%', 'icon' => 'target', 'color' => 'green'])
        @include('admin.components.stat-card', ['title' => 'Most Popular', 'value' => $mostPopular ?? 'N/A', 'icon' => 'star', 'color' => 'orange'])
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @include('admin.components.chart-card', ['title' => 'Daily Volume', 'chartId' => 'dailyVolumeChart'])
        @include('admin.components.chart-card', ['title' => 'Score by Type', 'chartId' => 'scoreByTypeChart'])
    </div>

    {{-- Top Exercises Table --}}
    <div class="card p-6">
        <div class="flex items-center gap-2 mb-6">
            <i data-lucide="trophy" class="w-5 h-5 text-orange-500"></i>
            <h2 class="text-lg font-semibold text-gray-900">Top Exercises</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">#</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Exercise</th>
                        <th class="text-right py-3 px-4 font-semibold text-gray-600">Completions</th>
                        <th class="text-right py-3 px-4 font-semibold text-gray-600">Avg Score</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($topExercises ?? [] as $index => $exercise)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-4 text-gray-500 font-medium">{{ $index + 1 }}</td>
                        <td class="py-3 px-4 font-medium text-gray-900">{{ $exercise->name ?? $exercise->title ?? 'Unknown' }}</td>
                        <td class="py-3 px-4 text-right text-gray-700">{{ number_format($exercise->completions ?? 0) }}</td>
                        <td class="py-3 px-4 text-right">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ ($exercise->avg_score ?? 0) >= 80 ? 'bg-green-100 text-green-700' : (($exercise->avg_score ?? 0) >= 50 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ $exercise->avg_score ?? 0 }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-gray-500">No exercise data available.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dailyVolume = @json($dailyVolume ?? []);
    if (dailyVolume.length > 0) {
        new ApexCharts(document.querySelector('#dailyVolumeChart'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
            series: [{ name: 'Exercises', data: dailyVolume.map(i => i.count) }],
            xaxis: { categories: dailyVolume.map(i => i.date), labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            colors: ['#9333ea'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '60%' } },
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
            dataLabels: { enabled: false }
        }).render();
    }

    const scoreByType = @json($scoreByType ?? []);
    if (scoreByType.length > 0) {
        new ApexCharts(document.querySelector('#scoreByTypeChart'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans' },
            series: [{ name: 'Avg Score %', data: scoreByType.map(i => i.score) }],
            xaxis: { categories: scoreByType.map(i => i.type), labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            yaxis: { max: 100, labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
            colors: ['#f97316'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '50%', horizontal: true } },
            grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
            dataLabels: { enabled: false }
        }).render();
    }
});
</script>
@endpush
