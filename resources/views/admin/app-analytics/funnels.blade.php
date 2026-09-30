@extends('admin.layouts.admin')

@section('page-title', 'Mobile App — Funnels')

@section('content')
<div class="space-y-6">
    @include('admin.app-analytics._header')

    {{-- Any funnel: steps in order, each a screen, a tap or an event. --}}
    <div class="card p-5" x-data="{ steps: @js($specs ?: ['']) }">
        <div class="flex flex-wrap items-baseline justify-between gap-2 mb-1">
            <h3 class="text-sm font-semibold text-gray-700">Build a funnel</h3>
            <span class="text-xs text-gray-500">{{ number_format($custom['units']) }} {{ $custom['unit'] === 'session' ? 'sessions' : 'installs' }} active in this range</span>
        </div>
        <p class="text-xs text-gray-400 mb-4">
            Each step is <code>screen:&lt;name&gt;</code>, <code>tap:&lt;control&gt;</code>, <code>event:&lt;name&gt;</code> or
            <code>event:&lt;name&gt;:&lt;property&gt;=&lt;value&gt;</code> — for instance <code>event:purchase_result:outcome=unlocked</code>.
            A step counts only after the one before it. Per install looks across the whole range; per session asks for it all in one sitting.
        </p>

        <form method="GET" class="space-y-2">
            @foreach ($range->query() as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <datalist id="funnel-suggestions">
                @foreach ($suggestions as $suggestion)
                    <option value="{{ $suggestion }}"></option>
                @endforeach
            </datalist>
            <template x-for="(step, i) in steps" :key="i">
                <div class="flex items-center gap-2">
                    <span class="w-6 text-right text-xs text-gray-400" x-text="i + 1"></span>
                    <input type="text" name="steps[]" x-model="steps[i]" list="funnel-suggestions" placeholder="screen:paywall"
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <button type="button" @click="steps.splice(i, 1)" x-show="steps.length > 1" class="p-2 text-gray-400 hover:text-red-600" aria-label="Remove step">
                        <span aria-hidden="true" class="text-lg leading-none">×</span>
                    </button>
                </div>
            </template>
            <div class="flex flex-wrap items-center gap-3 pl-8 pt-1">
                <button type="button" @click="steps.push('')" x-show="steps.length < 8" class="text-sm text-purple-600 hover:underline">+ Add step</button>
                <select name="unit" class="ml-auto px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                    <option value="install" @selected($unit === 'install')>Per install</option>
                    <option value="session" @selected($unit === 'session')>Per session</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-sm font-medium text-white rounded-lg transition-colors">Run</button>
            </div>
        </form>

        @if ($custom['steps'])
            <div class="space-y-2 mt-6">
                @foreach ($custom['steps'] as $i => $step)
                    <div class="grid grid-cols-12 items-center gap-3 text-sm">
                        <div class="col-span-12 sm:col-span-4 font-mono text-xs text-gray-800 truncate" title="{{ $step['spec'] }}">{{ $i + 1 }}. {{ $step['label'] }}</div>
                        <div class="col-span-7 sm:col-span-4">
                            <div class="h-5 bg-gray-100 rounded"><div class="h-5 rounded bg-purple-500/80" style="width: {{ min(100, $step['ofStart'] ?? 0) }}%"></div></div>
                        </div>
                        <div class="col-span-2 sm:col-span-1 text-right">{{ number_format($step['reached']) }}</div>
                        <div class="col-span-1 text-right text-gray-600">@include('admin.app-analytics._rate', ['value' => $step['ofStart']])</div>
                        <div class="col-span-1 text-right text-xs {{ ($step['ofPrevious'] ?? 100) < 50 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">@include('admin.app-analytics._rate', ['value' => $step['ofPrevious']])</div>
                        <div class="col-span-1 text-right text-xs text-gray-400" title="Median time from the step before">{{ $step['medianSeconds'] === null ? '' : \App\Services\Telemetry\EventPresenter::duration((int) $step['medianSeconds'] * 1000) }}</div>
                    </div>
                @endforeach
                <p class="text-xs text-gray-400 pt-1">Columns: reached · share of everyone active · share of the step before · median time from the step before.</p>
            </div>
        @endif
    </div>

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
