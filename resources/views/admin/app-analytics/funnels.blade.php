@extends('admin.layouts.admin')

@section('page-title', 'Mobile App — Funnels')

@section('content')
<div class="space-y-6">
    @include('admin.app-analytics._header')

    <div class="card p-5">
        <div class="flex flex-wrap items-baseline justify-between gap-2 mb-1">
            <h3 class="text-sm font-semibold text-gray-700">From first open to first lesson</h3>
            <span class="text-xs text-gray-500">Cohort: {{ number_format($onboarding['cohort']) }} installs first seen in this range</span>
        </div>
        <p class="text-xs text-gray-400 mb-4">
            Each step counts the cohort's installs that ever reached it, even after the range ends. Onboarding re-taken
            from the profile tab is left out. "Of previous" is the share that made it through from the step above.
        </p>

        <div class="space-y-2">
            @foreach ($onboarding['steps'] as $step)
                <div class="grid grid-cols-12 items-center gap-3 text-sm">
                    <div class="col-span-12 sm:col-span-4 text-gray-700 {{ str_starts_with($step['label'], 'Onboarding:') ? 'pl-4 font-mono text-xs' : 'font-medium' }}">{{ $step['label'] }}</div>
                    <div class="col-span-8 sm:col-span-5">
                        <div class="h-5 bg-gray-100 rounded">
                            <div class="h-5 rounded bg-purple-500/80" style="width: {{ min(100, $step['ofStart'] ?? 0) }}%"></div>
                        </div>
                    </div>
                    <div class="col-span-2 sm:col-span-1 text-right">{{ number_format($step['installs']) }}</div>
                    <div class="col-span-1 text-right text-gray-600">@include('admin.app-analytics._rate', ['value' => $step['ofStart']])</div>
                    <div class="col-span-1 text-right text-xs {{ ($step['ofPrevious'] ?? 100) < 70 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">@include('admin.app-analytics._rate', ['value' => $step['ofPrevious']])</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-1">Paywall, by where it was opened</h3>
        <p class="text-xs text-gray-400 mb-4">
            Distinct installs, events in the range. <em>Unavailable</em> is a paywall that had nothing to sell (store unreachable,
            empty placement) — a lost sale the store's own dashboard never sees.
        </p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-4 font-medium">Opened from</th>
                        <th class="py-2 pr-4 font-medium text-right">Shown</th>
                        <th class="py-2 pr-4 font-medium text-right">Unavailable</th>
                        <th class="py-2 pr-4 font-medium text-right">Changed plan</th>
                        <th class="py-2 pr-4 font-medium text-right">Tapped buy</th>
                        <th class="py-2 pr-4 font-medium text-right">Unlocked</th>
                        <th class="py-2 pr-4 font-medium text-right">Cancelled</th>
                        <th class="py-2 pr-4 font-medium text-right">Failed</th>
                        <th class="py-2 pr-4 font-medium text-right">Dismissed</th>
                        <th class="py-2 font-medium text-right">Conversion</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($paywall as $door)
                        <tr class="border-b border-gray-50">
                            <td class="py-2 pr-4 font-medium">{{ $door['door'] }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($door['shown']) }}</td>
                            <td class="py-2 pr-4 text-right {{ $door['unavailable'] ? 'text-amber-600' : 'text-gray-400' }}">{{ number_format($door['unavailable']) }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($door['selected']) }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($door['started']) }}</td>
                            <td class="py-2 pr-4 text-right text-green-700 font-semibold">{{ number_format($door['unlocked']) }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($door['cancelled']) }}</td>
                            <td class="py-2 pr-4 text-right {{ $door['failed'] ? 'text-red-600' : '' }}">{{ number_format($door['failed']) }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($door['dismissed']) }}</td>
                            <td class="py-2 text-right font-semibold">@include('admin.app-analytics._rate', ['value' => $door['conversion']])</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="py-6 text-center text-gray-400">No paywall views in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
