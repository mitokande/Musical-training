<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Interval Accuracy Multipliers (Preview) - {{ config('app.name', 'Harmoniva') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-800">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- Header --}}
        <div class="mb-8">
            <div class="inline-flex items-center gap-2 px-3 py-1 mb-3 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold uppercase tracking-wide">
                Temporary preview
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Interval Accuracy Multipliers</h1>
            <p class="mt-2 text-gray-600 max-w-2xl">
                Per-interval accuracy for <span class="font-semibold">{{ auth()->user()->name ?? 'you' }}</span>,
                grouped by practice type. The <span class="font-semibold">multiplier</span> is a practice weight derived
                from accuracy &mdash; it rises as accuracy falls, so weaker intervals are emphasized for future adaptive
                practice. Range <span class="font-mono">1.0</span> (mastered) &rarr; <span class="font-mono">2.0</span> (needs practice).
                Untested intervals default to <span class="font-mono">{{ number_format(\App\Models\UserIntervalStat::UNTESTED_MULTIPLIER, 1) }}</span>.
            </p>
        </div>

        @php
            $multiplierClass = function (float $m) {
                if ($m <= 1.25) return 'bg-emerald-100 text-emerald-700';
                if ($m <= 1.5)  return 'bg-lime-100 text-lime-700';
                if ($m <= 1.75) return 'bg-amber-100 text-amber-700';
                return 'bg-rose-100 text-rose-700';
            };
        @endphp

        <div class="space-y-8">
            @foreach($stats as $type)
                @php
                    $tested = collect($type['intervals'])->where('tested', true);
                    $attempts = $tested->sum('total_questions');
                    $correct  = $tested->sum('correct_answers');
                    $overall  = $attempts > 0 ? round($correct / $attempts * 100, 1) : null;
                @endphp
                <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between gap-4 px-6 py-4 border-b border-gray-100">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">{{ $type['name'] }}</h2>
                            <p class="text-xs text-gray-400 font-mono">{{ $type['slug'] }} &middot; practice_id {{ $type['practice_id'] }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500">Overall</div>
                            <div class="text-xl font-bold text-gray-900">
                                {{ $overall !== null ? $overall . '%' : '—' }}
                            </div>
                            <div class="text-xs text-gray-400">{{ $attempts }} attempts</div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-gray-400 bg-gray-50">
                                    <th class="px-6 py-3 font-semibold">Interval</th>
                                    <th class="px-3 py-3 font-semibold text-center">Semitones</th>
                                    <th class="px-3 py-3 font-semibold text-center">Attempts</th>
                                    <th class="px-3 py-3 font-semibold text-center">Correct</th>
                                    <th class="px-3 py-3 font-semibold text-center">Wrong</th>
                                    <th class="px-6 py-3 font-semibold">Accuracy</th>
                                    <th class="px-6 py-3 font-semibold text-center">Multiplier</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($type['intervals'] as $row)
                                    <tr class="{{ $row['tested'] ? '' : 'text-gray-400' }}">
                                        <td class="px-6 py-3 font-medium {{ $row['tested'] ? 'text-gray-900' : '' }}">
                                            {{ $row['interval'] }}
                                        </td>
                                        <td class="px-3 py-3 text-center font-mono text-xs">{{ $row['semitones'] }}</td>
                                        <td class="px-3 py-3 text-center">{{ $row['total_questions'] }}</td>
                                        <td class="px-3 py-3 text-center">{{ $row['correct_answers'] }}</td>
                                        <td class="px-3 py-3 text-center">{{ $row['incorrect_answers'] }}</td>
                                        <td class="px-6 py-3">
                                            @if($row['tested'])
                                                <div class="flex items-center gap-2">
                                                    <div class="flex-1 h-2 rounded-full bg-gray-100 overflow-hidden min-w-[80px]">
                                                        <div class="h-full rounded-full bg-purple-500" style="width: {{ $row['accuracy'] }}%"></div>
                                                    </div>
                                                    <span class="text-xs font-semibold text-gray-700 w-12 text-right">{{ $row['accuracy'] }}%</span>
                                                </div>
                                            @else
                                                <span class="text-xs italic">not practiced yet</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold font-mono {{ $multiplierClass($row['multiplier']) }}">
                                                {{ number_format($row['multiplier'], 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endforeach
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('dashboard') }}" class="text-sm text-purple-600 hover:text-purple-800 font-medium">&larr; Back to dashboard</a>
        </div>
    </div>
</body>
</html>
