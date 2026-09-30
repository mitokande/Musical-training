@extends('admin.layouts.admin')

@section('page-title', 'Mobile App — Flows')

@php
    use App\Services\Telemetry\JourneyAnalytics;
@endphp

@section('content')
<div class="space-y-6">
    @include('admin.app-analytics._header')

    <div class="card p-4">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            @foreach ($range->query() as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Show the</label>
                <select name="direction" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                    <option value="after" @selected($options['direction'] === 'after')>steps after</option>
                    <option value="before" @selected($options['direction'] === 'before')>steps before</option>
                </select>
            </div>
            <div class="min-w-[240px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Screen or milestone</label>
                <select name="step" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-purple-500">
                    <option value="">session start</option>
                    @foreach ($nodes as $node)
                        <option value="{{ $node }}" @selected($options['from'] === $node)>{{ $node }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Steps</label>
                <select name="depth" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                    @foreach (range(1, 6) as $n)
                        <option value="{{ $n }}" @selected($options['depth'] === $n)>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Per step</label>
                <select name="width" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                    @foreach ([4, 7, 10, 12] as $n)
                        <option value="{{ $n }}" @selected($options['width'] === $n)>top {{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-gray-600 pb-2">
                <input type="hidden" name="milestones" value="0">
                <input type="checkbox" name="milestones" value="1" @checked($options['milestones']) class="rounded text-purple-600">
                Milestones (★)
            </label>
            <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-sm font-medium text-white rounded-lg transition-colors">
                Draw
            </button>
        </form>
    </div>

    <div class="card p-5">
        <div class="flex flex-wrap items-baseline justify-between gap-2 mb-1">
            <h3 class="text-sm font-semibold text-gray-700">
                @if ($options['from'] === null)
                    How sessions unfold from their start
                @elseif ($options['direction'] === 'before')
                    What leads to <span class="font-mono text-purple-700">{{ $options['from'] }}</span>
                @else
                    What happens after <span class="font-mono text-purple-700">{{ $options['from'] }}</span>
                @endif
            </h3>
            <span class="text-xs text-gray-500">{{ number_format($flow['matched']) }} of {{ number_format($flow['sessions']) }} sessions</span>
        </div>
        <p class="text-xs text-gray-400 mb-4">
            Each column is one step; the band between two is how many sessions went that way. A session counts from the first time
            it reaches the chosen step. <em>(exit)</em> is the session ending there; <em>(other)</em> gathers the quieter steps.
            Onboarding appears beat by beat. Hover a band or a step for its numbers.
        </p>

        @if ($flow['matched'] === 0)
            <p class="py-10 text-center text-gray-400 text-sm">No sessions in this range reached that step.</p>
        @else
            <div id="flowChart" style="height: {{ max(420, min(900, 70 * $options['width'])) }}px"></div>

            {{-- The same numbers as a table, for reading exact values and for anyone the chart does not serve. --}}
            <details class="mt-4">
                <summary class="text-xs text-gray-500 cursor-pointer hover:text-purple-600">Show as table</summary>
                <div class="overflow-x-auto mt-3">
                    <div class="flex gap-4 min-w-max">
                        @foreach ($flow['columns'] as $c => $column)
                            <div class="w-56">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Step {{ $c }}</p>
                                @foreach ($column as $node)
                                    <div class="mb-1.5">
                                        <div class="flex justify-between gap-2 text-xs">
                                            <span class="font-mono truncate {{ in_array($node['label'], [JourneyAnalytics::EXIT, JourneyAnalytics::OTHER, JourneyAnalytics::START], true) ? 'text-gray-400' : 'text-gray-800' }}">{{ $node['label'] }}</span>
                                            <span class="text-gray-500 whitespace-nowrap">{{ number_format($node['sessions']) }} · @include('admin.app-analytics._rate', ['value' => $node['share']])</span>
                                        </div>
                                        <div class="h-1.5 bg-gray-100 rounded mt-0.5"><div class="h-1.5 rounded bg-purple-500/70" style="width: {{ min(100, $node['share'] ?? 0) }}%"></div></div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </details>
        @endif
    </div>

    <div class="card p-5">
        <div class="flex flex-wrap items-baseline justify-between gap-2 mb-1">
            <h3 class="text-sm font-semibold text-gray-700">How sessions most often go</h3>
            <span class="text-xs text-gray-500">{{ number_format($paths['sessions']) }} sessions</span>
        </div>
        <p class="text-xs text-gray-400 mb-4">Each session's first five steps as one path, and how many sessions took exactly it. A path ending in <em>(exit)</em> is the whole session.</p>
        <div class="space-y-2">
            @forelse ($paths['paths'] as $path)
                <div class="grid grid-cols-12 items-center gap-3">
                    <div class="col-span-12 md:col-span-9 flex flex-wrap items-center gap-1 text-[11px] font-mono">
                        @foreach ($path['steps'] as $j => $step)
                            @if ($j > 0)<span class="text-gray-300">→</span>@endif
                            @if ($step === JourneyAnalytics::EXIT)
                                <span class="px-1.5 py-0.5 rounded bg-gray-50 text-gray-400">exit</span>
                            @else
                                <a href="{{ route('admin.app-analytics.flows', $range->query() + ['step' => $step]) }}"
                                   class="px-1.5 py-0.5 rounded {{ JourneyAnalytics::isMilestone($step) ? 'bg-amber-50 text-amber-800' : 'bg-purple-50 text-purple-800' }} hover:ring-1 hover:ring-purple-300">{{ $step }}</a>
                            @endif
                        @endforeach
                    </div>
                    <div class="col-span-9 md:col-span-2"><div class="h-2 bg-gray-100 rounded"><div class="h-2 rounded bg-purple-500/70" style="width: {{ min(100, ($path['share'] ?? 0) * 2) }}%"></div></div></div>
                    <div class="col-span-3 md:col-span-1 text-right text-sm">{{ number_format($path['sessions']) }} <span class="text-xs text-gray-400">@include('admin.app-analytics._rate', ['value' => $path['share']])</span></div>
                </div>
            @empty
                <p class="py-6 text-center text-gray-400 text-sm">No sessions in this range.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@if ($flow['matched'] > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/echarts@5.5.1/dist/echarts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const flow = @json($flow);
    const muted = new Set(['{{ JourneyAnalytics::EXIT }}', '{{ JourneyAnalytics::OTHER }}', '{{ JourneyAnalytics::START }}']);
    const label = (id) => id.slice(id.indexOf('|') + 1);
    // One hue for every screen, so a colour never pretends to mean something
    // the position already says; milestones in amber, the end markers in grey.
    const colour = (name) => muted.has(name) ? '#d1d5db' : name.startsWith('★') ? '#f59e0b' : '#9333ea';

    const chart = echarts.init(document.getElementById('flowChart'), null, { renderer: 'svg' });
    chart.setOption({
        textStyle: { fontFamily: 'Plus Jakarta Sans, system-ui, sans-serif' },
        tooltip: {
            trigger: 'item',
            formatter: (p) => {
                const share = (v) => flow.matched ? ` (${(v / flow.matched * 100).toFixed(1)}%)` : '';
                if (p.dataType === 'edge') {
                    return `${label(p.data.source)} → ${label(p.data.target)}<br><b>${p.data.value.toLocaleString()}</b> sessions${share(p.data.value)}`;
                }
                return `${label(p.name)}<br><b>${p.value.toLocaleString()}</b> sessions${share(p.value)}`;
            },
        },
        series: [{
            type: 'sankey',
            left: 8, right: 160, top: 8, bottom: 8,
            nodeWidth: 12,
            nodeGap: 10,
            nodeAlign: '{{ $options['direction'] === 'before' ? 'right' : 'left' }}',
            layoutIterations: 64,
            draggable: false,
            emphasis: { focus: 'adjacency' },
            data: flow.nodes.map((n) => ({ name: n.id, value: n.value, depth: n.column, itemStyle: { color: colour(n.label), borderWidth: 0 } })),
            links: flow.links,
            lineStyle: { color: 'source', opacity: 0.22, curveness: 0.5 },
            label: {
                formatter: (p) => `${label(p.name)}  ${p.value.toLocaleString()}`,
                fontSize: 11,
                color: '#374151',
            },
        }],
    });
    window.addEventListener('resize', () => chart.resize());

    // Clicking a step re-centres the flow on it.
    chart.on('click', (p) => {
        if (p.dataType !== 'node') return;
        const name = label(p.name);
        if (muted.has(name)) return;
        const url = new URL(window.location.href);
        url.searchParams.set('step', name);
        window.location = url.toString();
    });
});
</script>
@endpush
@endif
