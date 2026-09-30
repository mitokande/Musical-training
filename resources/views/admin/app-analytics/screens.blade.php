@extends('admin.layouts.admin')

@section('page-title', 'Mobile App — Screens')

@section('content')
<div class="space-y-6">
    @include('admin.app-analytics._header')

    <div class="card p-5">
        <div class="flex flex-wrap items-baseline justify-between gap-2 mb-1">
            <h3 class="text-sm font-semibold text-gray-700">Screens</h3>
            <span class="text-xs text-gray-500">{{ number_format($sessions) }} sessions</span>
        </div>
        <p class="text-xs text-gray-400 mb-4">
            A view is one visit; returning later in the session is another. Time is how long the screen was actually visible —
            time with the app in the background does not count — as a median, so one screen left open all afternoon does not
            speak for everyone. <em>Exit</em> is the share of visits that were the session's last. Onboarding is broken into its beats.
        </p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-4 font-medium">Screen</th>
                        <th class="py-2 pr-4 font-medium text-right">Views</th>
                        <th class="py-2 pr-4 font-medium text-right">Installs</th>
                        <th class="py-2 pr-4 font-medium text-right">Median time</th>
                        <th class="py-2 pr-4 font-medium text-right">Avg. time</th>
                        <th class="py-2 pr-4 font-medium text-right">Entries</th>
                        <th class="py-2 pr-4 font-medium text-right">Exit</th>
                        <th class="py-2 pr-4 font-medium text-right">Taps</th>
                        <th class="py-2 font-medium">Most often next</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($screens as $name => $screen)
                        @php $next = array_slice($transitions[$name] ?? [], 0, 2, true); @endphp
                        <tr class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="py-2 pr-4 font-mono text-xs">
                                <a href="{{ route('admin.app-analytics.screen', $range->query() + ['name' => $name]) }}" class="text-purple-700 hover:underline">{{ $name }}</a>
                            </td>
                            <td class="py-2 pr-4 text-right">{{ number_format($screen['views']) }}</td>
                            <td class="py-2 pr-4 text-right">{{ $screen['viewers'] === null ? '—' : number_format($screen['viewers']) }}</td>
                            <td class="py-2 pr-4 text-right">{{ $screen['medianSeconds'] === null ? '—' : $screen['medianSeconds'].'s' }}</td>
                            <td class="py-2 pr-4 text-right text-gray-500">{{ $screen['avgSeconds'] === null ? '—' : $screen['avgSeconds'].'s' }}</td>
                            <td class="py-2 pr-4 text-right text-gray-600">{{ number_format($screen['entries']) }}</td>
                            <td class="py-2 pr-4 text-right {{ ($screen['exitRate'] ?? 0) >= 40 ? 'text-red-600 font-semibold' : '' }}">@include('admin.app-analytics._rate', ['value' => $screen['exitRate']])</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($screen['taps']) }}</td>
                            <td class="py-2 text-xs font-mono text-gray-600">
                                @foreach ($next as $to => $count)
                                    <span class="whitespace-nowrap {{ $to === \App\Services\Telemetry\JourneyAnalytics::EXIT ? 'text-gray-400' : '' }}">{{ $to }} <span class="text-gray-400">{{ round($count / max(1, $screen['views']) * 100) }}%</span></span>@if (! $loop->last)<span class="text-gray-300"> · </span>@endif
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="py-6 text-center text-gray-400">No screen views in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-1">What gets tapped</h3>
        <p class="text-xs text-gray-400 mb-4">
            Every control, by the screen it is on. The name is the control's translation key (the same in every language) or the
            id the app gave it. <em>Reach</em> is the share of the screen's visitors who tapped it at least once.
            <em>unnamed</em> is a control the app could not name — the fix is a <code>trackId</code> on it.
        </p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-4 font-medium">Screen</th>
                        <th class="py-2 pr-4 font-medium">Control</th>
                        <th class="py-2 pr-4 font-medium text-right">Taps</th>
                        <th class="py-2 pr-4 font-medium text-right">Installs</th>
                        <th class="py-2 font-medium text-right">Reach</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($taps as $tap)
                        <tr class="border-b border-gray-50">
                            <td class="py-2 pr-4 font-mono text-xs text-gray-600">
                                <a href="{{ route('admin.app-analytics.screen', $range->query() + ['name' => $tap['screen']]) }}" class="hover:text-purple-700">{{ $tap['screen'] ?? '—' }}</a>
                            </td>
                            <td class="py-2 pr-4 font-mono text-xs {{ $tap['control'] === null ? 'text-amber-600 italic' : 'text-gray-900' }}">{{ $tap['control'] ?? 'unnamed' }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($tap['taps']) }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($tap['tappers']) }}</td>
                            <td class="py-2 text-right">@include('admin.app-analytics._rate', ['value' => $tap['reach']])</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-gray-400">No taps in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
