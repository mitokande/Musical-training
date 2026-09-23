@extends('admin.layouts.admin')

@section('page-title', 'Mobile App — Learning')

@section('content')
<div class="space-y-6">
    @include('admin.app-analytics._header')

    <div class="card p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-1">Lessons</h3>
        <p class="text-xs text-gray-400 mb-4">
            Graded runs only (reviews are counted separately). Score and time are for finished runs. <em>Quit at</em> is how far
            through the lesson an abandoned run had got, on average.
        </p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-4 font-medium">Lesson</th>
                        <th class="py-2 pr-4 font-medium text-right">Started</th>
                        <th class="py-2 pr-4 font-medium text-right">Learners</th>
                        <th class="py-2 pr-4 font-medium text-right">Finished</th>
                        <th class="py-2 pr-4 font-medium text-right">Completion</th>
                        <th class="py-2 pr-4 font-medium text-right">Abandoned</th>
                        <th class="py-2 pr-4 font-medium text-right">Out of hearts</th>
                        <th class="py-2 pr-4 font-medium text-right">Quit at</th>
                        <th class="py-2 pr-4 font-medium text-right">Avg. score</th>
                        <th class="py-2 pr-4 font-medium text-right">Avg. time</th>
                        <th class="py-2 font-medium text-right">Reviews</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lessons as $lesson)
                        <tr class="border-b border-gray-50">
                            <td class="py-2 pr-4 font-mono text-xs">
                                {{ $lesson['node'] }}
                                @if ($lesson['checkpoint'])<span class="ml-1 px-1.5 py-0.5 text-[10px] rounded bg-amber-50 text-amber-700">checkpoint</span>@endif
                            </td>
                            <td class="py-2 pr-4 text-right">{{ number_format($lesson['started']) }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($lesson['learners']) }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($lesson['finished']) }}</td>
                            <td class="py-2 pr-4 text-right font-semibold {{ ($lesson['completion'] ?? 100) < 60 ? 'text-red-600' : '' }}">@include('admin.app-analytics._rate', ['value' => $lesson['completion']])</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($lesson['abandoned']) }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($lesson['outOfHearts']) }}</td>
                            <td class="py-2 pr-4 text-right text-gray-600">{{ $lesson['quitAt'] === null ? '—' : $lesson['quitAt'].'%' }}</td>
                            <td class="py-2 pr-4 text-right">{{ $lesson['avgScore'] ?? '—' }}</td>
                            <td class="py-2 pr-4 text-right text-gray-600">{{ $lesson['avgMinutes'] === null ? '—' : $lesson['avgMinutes'].' min' }}</td>
                            <td class="py-2 text-right text-gray-500">{{ number_format($lesson['reviews']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="py-6 text-center text-gray-400">No lessons started in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-1">Intervals</h3>
        <p class="text-xs text-gray-400 mb-4">
            First attempts only. <em>Hear it</em> is identifying a played interval; <em>Build it</em> is playing one on the
            keyboard. Replays are plays the learner asked for, beyond the automatic one.
        </p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b">
                        <th class="py-2 pr-4 font-medium">Interval</th>
                        <th class="py-2 pr-4 font-medium text-right">Hear it · answers</th>
                        <th class="py-2 pr-4 font-medium text-right">Accuracy</th>
                        <th class="py-2 pr-4 font-medium">Most mistaken for</th>
                        <th class="py-2 pr-4 font-medium text-right">Avg. replays</th>
                        <th class="py-2 pr-4 font-medium text-right">Avg. time</th>
                        <th class="py-2 pr-4 font-medium text-right">Build it · answers</th>
                        <th class="py-2 font-medium text-right">Accuracy</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($concepts['rows'] as $row)
                        <tr class="border-b border-gray-50">
                            <td class="py-2 pr-4 font-mono text-xs font-semibold">{{ $row['concept'] }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($row['answers']) }}</td>
                            <td class="py-2 pr-4 text-right font-semibold {{ ($row['accuracy'] ?? 100) < 60 ? 'text-red-600' : '' }}">@include('admin.app-analytics._rate', ['value' => $row['accuracy']])</td>
                            <td class="py-2 pr-4">
                                @if ($row['topConfusion'])
                                    <span class="font-mono text-xs">{{ $row['topConfusion']['chosen'] }}</span>
                                    <span class="text-xs text-gray-500">({{ $row['topConfusion']['share'] }}%)</span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4 text-right">{{ $row['avgReplays'] ?? '—' }}</td>
                            <td class="py-2 pr-4 text-right text-gray-600">{{ $row['avgSeconds'] === null ? '—' : $row['avgSeconds'].'s' }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format($row['builds']) }}</td>
                            <td class="py-2 text-right">@include('admin.app-analytics._rate', ['value' => $row['buildAccuracy']])</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-6 text-center text-gray-400">No answers in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if (count($concepts['matrix']))
    <div class="card p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-1">Confusion matrix</h3>
        <p class="text-xs text-gray-400 mb-4">
            Rows are the interval that played, columns what the learner picked, cells the share of that row's first answers.
            The diagonal is getting it right; anything bright off it is a pair worth teaching side by side.
        </p>
        <div class="overflow-x-auto">
            <table class="text-xs">
                <thead>
                    <tr>
                        <th class="p-1 text-left text-gray-500 font-medium">played ↓ / picked →</th>
                        @foreach ($concepts['columns'] as $column)
                            <th class="p-1 font-mono font-medium text-gray-600 text-center">{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($concepts['matrix'] as $played => $cells)
                        <tr>
                            <th class="p-1 font-mono font-medium text-gray-600 text-left">{{ $played }}</th>
                            @foreach ($concepts['columns'] as $column)
                                @php $share = $cells[$column] ?? null; @endphp
                                @if ($share === null)
                                    <td class="p-1 w-12 h-8 text-center text-gray-200">·</td>
                                @else
                                    @php $right = $column === $played; @endphp
                                    <td class="p-1 w-12 h-8 text-center rounded {{ $share >= 45 ? 'text-white' : 'text-gray-800' }}"
                                        style="background: {{ $right ? 'rgba(16, 185, 129, '.round(0.1 + $share / 100 * 0.8, 2).')' : 'rgba(239, 68, 68, '.round(0.1 + min($share * 2, 100) / 100 * 0.8, 2).')' }}">
                                        {{ round($share) }}
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
