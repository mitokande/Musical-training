@extends('teacher.layouts.crm')

@section('title', $student->name.' '.$student->surname)

@section('content')
@php
    $p = 'teacher.students.profile';
    $statusMessages = [
        'note-added' => __('teacher.profile.saved'),
        'note-deleted' => __('teacher.profile.saved'),
        'tags-updated' => __('teacher.profile.saved'),
        'tag-created' => __('teacher.profile.saved'),
        'reward-given' => __('teacher.profile.saved'),
    ];
@endphp
@if (session('status') && isset($statusMessages[session('status')]))
    <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
        <p class="text-sm text-green-700">{{ $statusMessages[session('status')] }}</p>
    </div>
@endif

<a href="{{ route('teacher.students.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-4">
    <i data-lucide="arrow-left" class="w-4 h-4"></i> {{ __('teacher.students.title') }}
</a>

<div class="card p-5 mb-6 flex flex-col sm:flex-row items-start sm:items-center gap-4">
    @if($student->hasAvatar())
        <img src="{{ $student->avatar }}" class="w-16 h-16 rounded-full object-cover" alt="">
    @else
        <div class="w-16 h-16 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xl font-bold">
            {{ strtoupper(substr($student->name, 0, 1)) }}
        </div>
    @endif
    <div class="flex-1">
        <h1 class="text-xl font-bold text-gray-900">{{ $student->name }} {{ $student->surname }}</h1>
        <p class="text-sm text-gray-500">{{ __('teacher.students.active_since', ['date' => $relationship->approved_at?->format('M j, Y')]) }}</p>
        <div class="flex flex-wrap gap-1.5 mt-2">
            @foreach($tags->whereIn('id', $studentTagIds) as $tag)
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-primary-50 text-primary-700">{{ $tag->name }}</span>
            @endforeach
            @foreach($classes->whereIn('id', $studentClassIds) as $cls)
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700">{{ $cls->name }}</span>
            @endforeach
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
    @foreach([
        ['label' => __($p.'.total_exercises'), 'value' => number_format($stats['total_questions']), 'icon' => 'list-checks'],
        ['label' => __($p.'.accuracy'), 'value' => $stats['accuracy'] !== null ? $stats['accuracy'].'%' : '—', 'icon' => 'target'],
        ['label' => __($p.'.sessions'), 'value' => $stats['exercise_sessions'], 'icon' => 'timer'],
        ['label' => __($p.'.lp_completed'), 'value' => $stats['lp_completed'], 'icon' => 'route'],
        ['label' => __($p.'.best_game'), 'value' => $stats['game_best'] ?? '—', 'icon' => 'gamepad-2'],
    ] as $stat)
        <div class="card p-4">
            <div class="flex items-center gap-2 text-gray-400 mb-1"><i data-lucide="{{ $stat['icon'] }}" class="w-4 h-4"></i></div>
            <p class="text-xl font-bold text-gray-900">{{ $stat['value'] }}</p>
            <p class="text-xs text-gray-500">{{ $stat['label'] }}</p>
        </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Assignments --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-gray-900">{{ __($p.'.assignments') }}</h2>
                <div class="flex gap-3 text-xs text-gray-500">
                    <span>{{ __($p.'.assigned') }}: <b>{{ $assignmentStats['assigned'] }}</b></span>
                    <span>{{ __($p.'.completed') }}: <b>{{ $assignmentStats['completed'] }}</b></span>
                    <span>{{ __($p.'.overdue') }}: <b class="text-red-600">{{ $assignmentStats['overdue'] }}</b></span>
                    <span>{{ __($p.'.avg_score') }}: <b>{{ $assignmentStats['average_score'] !== null ? round($assignmentStats['average_score'], 1).'%' : '—' }}</b></span>
                </div>
            </div>
            @if($assignmentRecipients->isEmpty())
                <p class="text-sm text-gray-400">{{ __($p.'.no_activity') }}</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($assignmentRecipients->take(8) as $rec)
                        <li class="py-2.5 flex items-center gap-3">
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('teacher.assignments.show', $rec->assignment) }}" class="text-sm font-semibold text-gray-800 hover:text-primary-700 truncate block">{{ $rec->assignment->title }}</a>
                                <p class="text-xs text-gray-400">
                                    @if($rec->assignment->due_at) {{ __('teacher.assignments.due') }}: {{ $rec->assignment->due_at->format('M j, Y') }} @endif
                                </p>
                            </div>
                            @if($rec->status === 'completed')
                                <span class="text-xs font-bold text-green-700">{{ round((float) $rec->best_score, 1) }}%</span>
                            @elseif($rec->isOverdue())
                                <span class="text-xs font-semibold text-red-600">{{ __('teacher.assignments.recipient_overdue') }}</span>
                            @else
                                <span class="text-xs text-gray-400">{{ __('teacher.assignments.recipient_'.$rec->status) }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Recent practice --}}
        <div class="card p-5">
            <h2 class="font-bold text-gray-900 mb-3">{{ __($p.'.recent_sessions') }}</h2>
            @if($recentSessions->isEmpty())
                <p class="text-sm text-gray-400">{{ __($p.'.no_activity') }}</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($recentSessions as $session)
                        <li class="py-2 flex items-center gap-3 text-sm">
                            <span class="flex-1 text-gray-700">{{ $session->exercise_type }}</span>
                            <span class="text-xs text-gray-400">{{ $session->created_at->format('M j') }}</span>
                            <span class="text-xs font-semibold {{ ($session->accuracy ?? 0) >= 70 ? 'text-green-700' : 'text-amber-600' }}">
                                {{ $session->accuracy !== null ? round($session->accuracy).'%' : '—' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Learning path --}}
        <div class="card p-5">
            <h2 class="font-bold text-gray-900 mb-3">{{ __($p.'.lp_progress') }}</h2>
            @if($lpProgress->isEmpty())
                <p class="text-sm text-gray-400">{{ __($p.'.no_activity') }}</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($lpProgress as $prog)
                        <li class="py-2 flex items-center gap-3 text-sm">
                            <span class="flex-1 text-gray-700 truncate">{{ $prog->exercise?->getLocalizedTitle() ?? '#'.$prog->learning_path_exercise_id }}</span>
                            @if($prog->completed)
                                <span class="text-xs font-semibold text-green-700">{{ round($prog->score) }}%</span>
                            @else
                                <span class="text-xs text-gray-400">{{ $prog->correct_answers }}/{{ $prog->total_questions }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        {{-- Notes --}}
        <div class="card p-5">
            <h2 class="font-bold text-gray-900 mb-3">{{ __($p.'.notes') }}</h2>
            <form method="POST" action="{{ route('teacher.students.notes.store', $student) }}" class="mb-4">
                @csrf
                <textarea name="body" rows="2" required maxlength="5000" placeholder="{{ __($p.'.note_placeholder') }}" class="w-full rounded-lg border-gray-300 text-sm mb-2"></textarea>
                <button class="w-full py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold rounded-lg transition">{{ __($p.'.add_note') }}</button>
            </form>
            <ul class="space-y-3">
                @foreach($notes as $note)
                    <li class="bg-gray-50 rounded-lg p-3">
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $note->body }}</p>
                        <div class="flex items-center justify-between mt-1.5">
                            <span class="text-[11px] text-gray-400">{{ $note->created_at->format('M j, Y H:i') }}</span>
                            <form method="POST" action="{{ route('teacher.students.notes.destroy', $note) }}">
                                @csrf @method('DELETE')
                                <button class="text-[11px] text-red-500 hover:text-red-700 font-semibold">×</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Tags --}}
        <div class="card p-5">
            <h2 class="font-bold text-gray-900 mb-3">{{ __($p.'.tags') }}</h2>
            <form method="POST" action="{{ route('teacher.students.tags.store') }}" class="flex gap-2 mb-3">
                @csrf
                <input type="text" name="name" required maxlength="50" placeholder="{{ __($p.'.new_tag') }}" class="flex-1 rounded-lg border-gray-300 text-sm">
                <button class="px-3 py-2 bg-gray-800 text-white text-sm font-semibold rounded-lg">+</button>
            </form>
            @if($tags->isNotEmpty())
                <form method="POST" action="{{ route('teacher.students.tags.sync', $student) }}">
                    @csrf @method('PUT')
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach($tags as $tag)
                            <label class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-gray-50 rounded-lg text-xs font-medium text-gray-700 cursor-pointer">
                                <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" @checked(in_array($tag->id, $studentTagIds)) class="rounded border-gray-300 text-primary-600 w-3.5 h-3.5">
                                {{ $tag->name }}
                            </label>
                        @endforeach
                    </div>
                    <button class="w-full py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition">{{ __($p.'.save_tags') }}</button>
                </form>
            @endif
        </div>

        {{-- Classes --}}
        @if($classes->isNotEmpty())
        <div class="card p-5">
            <h2 class="font-bold text-gray-900 mb-3">{{ __($p.'.classes') }}</h2>
            <div class="space-y-2">
                @foreach($classes as $cls)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-700">{{ $cls->name }}</span>
                        @if(in_array($cls->id, $studentClassIds))
                            <form method="POST" action="{{ route('teacher.classes.students.remove', [$cls, $student]) }}">
                                @csrf @method('DELETE')
                                <button class="text-xs font-semibold text-red-500 hover:text-red-700">{{ __('teacher.classes.remove') }}</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('teacher.classes.students.add', $cls) }}">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                <button class="text-xs font-semibold text-primary-600 hover:text-primary-800">{{ __('teacher.classes.add') }}</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Rewards --}}
        <div class="card p-5">
            <h2 class="font-bold text-gray-900 mb-3">{{ __($p.'.rewards') }}</h2>
            <form method="POST" action="{{ route('teacher.students.rewards.store', $student) }}" class="space-y-2 mb-4">
                @csrf
                <select name="type" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="sticker">Sticker 🌟</option>
                    <option value="badge">Badge 🏅</option>
                    <option value="label">Encouragement 💬</option>
                    <option value="milestone">Milestone 🏆</option>
                </select>
                <input type="text" name="label" required maxlength="100" placeholder="{{ __($p.'.reward_label') }}" class="w-full rounded-lg border-gray-300 text-sm">
                <input type="text" name="note" maxlength="500" placeholder="{{ __($p.'.reward_note') }}" class="w-full rounded-lg border-gray-300 text-sm">
                <button class="w-full py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg transition">{{ __($p.'.give_reward') }}</button>
            </form>
            <div class="flex flex-wrap gap-1.5">
                @foreach($rewards as $reward)
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700" title="{{ $reward->created_at->format('M j, Y') }}">
                        {{ $reward->label }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
