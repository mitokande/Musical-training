@extends('admin.layouts.admin')

@section('page-title', 'Mobile App — Retention')

@section('content')
<div class="space-y-6">
    @include('admin.app-analytics._header')

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach ($retention['days'] as $n => $day)
            @include('admin.components.stat-card', [
                'title' => "Day {$n} return · ".number_format($day['returned']).' of '.number_format($day['eligible']),
                'value' => $day['rate'] === null ? '—' : $day['rate'].'%',
                'icon' => 'repeat',
                'color' => ['1' => 'purple', '7' => 'blue', '30' => 'green'][$n] ?? 'purple',
            ])
        @endforeach
    </div>

    <div class="card p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-1">Weekly cohorts</h3>
        <p class="text-xs text-gray-400 mb-4">
            Installs grouped by the week they were first seen (weeks start Monday). Each cell is the share of the cohort
            that opened the app at least once in that week after installing. Day N above counts a return on exactly that
            day, among installs old enough to have had one.
        </p>
        <div class="overflow-x-auto">
            <table class="text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-4 font-medium">Week of</th>
                        <th class="py-2 pr-4 font-medium text-right">Installs</th>
                        @for ($w = 0; $w <= $retention['weeks']; $w++)
                            <th class="py-2 px-2 font-medium text-center w-16">W{{ $w }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @forelse ($retention['cohorts'] as $cohort)
                        <tr class="border-b border-gray-50">
                            <td class="py-1.5 pr-4 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($cohort['week'])->format('M j, Y') }}</td>
                            <td class="py-1.5 pr-4 text-right">{{ number_format($cohort['size']) }}</td>
                            @foreach ($cohort['rates'] as $rate)
                                @if ($rate === null)
                                    <td class="py-1.5 px-2"></td>
                                @else
                                    <td class="py-1.5 px-2 text-center text-xs rounded {{ $rate >= 45 ? 'text-white' : 'text-gray-800' }}"
                                        style="background: rgba(147, 51, 234, {{ round(0.08 + $rate / 100 * 0.85, 2) }})">
                                        {{ round($rate) }}%
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ $retention['weeks'] + 3 }}" class="py-6 text-center text-gray-400">No installs first seen in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
