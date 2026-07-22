@extends('teacher.layouts.crm')

@section('title', $assignment->title)

@section('content')
@php
    $a = 'assignments';
    $statusColors = [
        'draft' => 'bg-gray-100 text-gray-600',
        'scheduled' => 'bg-blue-50 text-blue-700',
        'sent' => 'bg-primary-50 text-primary-700',
        'completed' => 'bg-green-50 text-green-700',
        'archived' => 'bg-gray-100 text-gray-400',
    ];
    $editable = ! $assignment->questionsLocked();
@endphp

@if (session('status') && (str_starts_with(session('status'), 'assignment-') || str_starts_with(session('status'), 'question')))
    <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
        <p class="text-sm text-green-700">{{ crm_trans($a.'.status_'.session('status')) }}</p>
    </div>
@endif

@if ($errors->has('questions'))
    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3">
        <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600 shrink-0"></i>
        <p class="text-sm text-red-700">{{ $errors->first('questions') }}</p>
    </div>
@endif

<a href="{{ crm_route('assignments.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-4">
    <i data-lucide="arrow-left" class="w-4 h-4"></i> {{ crm_trans($a.'.title') }}
</a>

<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900">{{ $assignment->title }}</h1>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $statusColors[$assignment->status] ?? '' }}">
                {{ crm_trans($a.'.status_'.$assignment->status) }}
            </span>
        </div>
        <p class="text-sm text-gray-500 mt-1">
            {{ crm_trans($a.'.type_'.$assignment->type) }}
            @if($assignment->practice_type) · {{ ucwords(str_replace('-', ' ', $assignment->practice_type)) }} @endif
            @if($assignment->difficulty) · {{ crm_trans($a.'.difficulty_'.$assignment->difficulty) }} @endif
            · {{ $assignment->question_count }} Q
            @if($assignment->due_at) · {{ crm_trans($a.'.due') }}: {{ $assignment->due_at->format('M j, Y H:i') }} @endif
        </p>
        @if($assignment->description)<p class="text-sm text-gray-600 mt-2 max-w-2xl">{{ $assignment->description }}</p>@endif
    </div>
    <div class="flex flex-wrap gap-2.5">
        @if($assignment->type !== 'practice_goal' && count($previews) > 0)
            <a href="{{ crm_route('assignments.preview', $assignment) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow-sm transition">
                <i data-lucide="eye" class="w-4 h-4"></i> {{ crm_trans($a.'.preview') }}
            </a>
        @endif
        <form method="POST" action="{{ crm_route('assignments.duplicate', $assignment) }}">
            @csrf
            <button class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-200 rounded-xl shadow-sm transition">
                <i data-lucide="copy" class="w-4 h-4"></i> {{ crm_trans($a.'.duplicate') }}
            </button>
        </form>
        @if($assignment->status !== 'archived')
            <form method="POST" action="{{ crm_route('assignments.archive', $assignment) }}">
                @csrf
                <button class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-200 rounded-xl shadow-sm transition">
                    <i data-lucide="archive" class="w-4 h-4"></i> {{ crm_trans($a.'.archive') }}
                </button>
            </form>
        @endif
        @if($assignment->isDraft())
            <form method="POST" action="{{ crm_route('assignments.destroy', $assignment) }}" onsubmit="return confirm(@js(crm_trans($a.'.delete_confirm')))">
                @csrf @method('DELETE')
                <button class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 rounded-xl shadow-sm transition">
                    <i data-lucide="trash-2" class="w-4 h-4"></i> {{ crm_trans($a.'.delete') }}
                </button>
            </form>
        @endif
    </div>
</div>

@unless($editable)
    <div class="mb-6 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl flex items-center gap-3">
        <i data-lucide="lock" class="w-4 h-4 text-blue-600 shrink-0"></i>
        <p class="text-sm text-blue-700">{{ crm_trans($a.'.locked_note') }}</p>
    </div>
@endunless

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Questions --}}
    <div class="lg:col-span-2">
        @if($assignment->media->isNotEmpty())
        <div class="card p-5 mb-6">
            <h2 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                <i data-lucide="paperclip" class="w-4 h-4 text-gray-500"></i> {{ crm_trans($a.'.attachments') }}
            </h2>
            <div class="space-y-1.5">
                @foreach($assignment->media as $m)
                    <a href="{{ $m->isPublic() ? $m->publicUrl() : crm_route('media.download', $m) }}"
                       @if($m->isPublic()) target="_blank" rel="noopener" @endif
                       class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-50 hover:bg-gray-100 text-sm text-gray-700 transition">
                        <i data-lucide="{{ $m->kind === 'photo' ? 'image' : 'file-text' }}" class="w-4 h-4 text-gray-400 shrink-0"></i>
                        <span class="truncate">{{ $m->title ?: $m->original_name }}</span>
                        <i data-lucide="download" class="w-4 h-4 text-gray-400 ml-auto shrink-0"></i>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        @if($assignment->type !== 'practice_goal')
        <div class="card overflow-hidden" x-data="assignmentQuestions('{{ crm_route('assignments.questions.update', [$assignment, '__ID__']) }}')">
            <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-bold text-gray-900">{{ crm_trans($a.'.questions') }} ({{ count($previews) }})</h2>
                @if($editable)
                    <form method="POST" action="{{ crm_route('assignments.regenerate', $assignment) }}">
                        @csrf
                        <button class="text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 px-3 py-1.5 rounded-lg transition">
                            {{ crm_trans($a.'.regenerate_all') }}
                        </button>
                    </form>
                @endif
            </div>
            <ul class="divide-y divide-gray-100">
                @foreach($previews as $preview)
                    <li class="px-5 py-4">
                        <div class="flex items-start gap-3">
                            <span class="w-7 h-7 rounded-full bg-gray-100 text-gray-600 text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                                {{ $preview['position'] }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="text-sm font-bold text-gray-900">{{ crm_trans($a.'.correct_answer') }}:</span>
                                    <span class="px-2 py-0.5 rounded-full bg-green-50 text-green-700 text-xs font-bold">{{ $preview['correct'] }}</span>
                                    @if(!empty($preview['notes']))
                                        <button type="button"
                                                class="preview-play inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-50 text-primary-700 text-xs font-semibold hover:bg-primary-100 transition"
                                                data-notes="{{ implode(',', $preview['notes']) }}"
                                                data-mode="{{ $preview['mode'] }}">
                                            <i data-lucide="play" class="w-3 h-3"></i> {{ crm_trans($a.'.play') }}
                                        </button>
                                    @endif
                                </div>
                                <div class="flex flex-wrap gap-x-4 gap-y-0.5 text-xs text-gray-500">
                                    @foreach($preview['meta'] as $key => $value)
                                        <span><span class="text-gray-400">{{ str_replace('_', ' ', $key) }}:</span> <b class="text-gray-600 font-medium">{{ $value }}</b></span>
                                    @endforeach
                                </div>
                                @if(!empty($preview['options']))
                                    <p class="text-xs text-gray-400 mt-1">{{ crm_trans($a.'.options') }}: {{ implode(' · ', (array) $preview['options']) }}</p>
                                @endif
                            </div>
                            @if($editable)
                                <div class="flex gap-1 shrink-0">
                                    @php
                                    $correctStr = trim((string) $preview['correct']);
                                    $distractors = array_values(array_filter(
                                        array_map(fn ($o) => (string) $o, (array) ($preview['options'] ?? [])),
                                        fn ($o) => mb_strtolower(trim($o)) !== mb_strtolower($correctStr),
                                    ));
                                    $editPayload = [
                                        'id' => $preview['id'],
                                        'position' => $preview['position'],
                                        'fields' => $preview['edit_fields'] ?: new stdClass,
                                        'correct' => $correctStr,
                                        'distractors' => $distractors,
                                        'rhythm_values' => $preview['rhythm_values'] ?? [],
                                    ];
                                @endphp
                                    <button type="button"
                                            x-on:click='editing = @json($editPayload)'
                                            class="p-1.5 text-gray-400 hover:text-primary-600 transition" title="{{ crm_trans($a.'.edit') }}">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    <form method="POST" action="{{ crm_route('assignments.questions.regenerate', [$assignment, $preview['id']]) }}">
                                        @csrf
                                        <button class="p-1.5 text-gray-400 hover:text-primary-600 transition" title="{{ crm_trans($a.'.regenerate') }}">
                                            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ crm_route('assignments.questions.destroy', [$assignment, $preview['id']]) }}">
                                        @csrf @method('DELETE')
                                        <button class="p-1.5 text-gray-400 hover:text-red-600 transition" title="{{ crm_trans($a.'.remove') }}">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            {{-- Edit question modal (draft only) --}}
            <div x-show="editing" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
                <div class="absolute inset-0 bg-black/40" x-on:click="editing = null"></div>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[85vh] overflow-y-auto" x-on:keydown.escape.window="editing = null">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900">
                            {{ crm_trans($a.'.edit_question') }} <span class="text-gray-400 font-normal" x-text="'#' + (editing?.position ?? '')"></span>
                        </h3>
                        <button type="button" class="text-gray-400 hover:text-gray-600" x-on:click="editing = null">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <template x-if="editing">
                        <form method="POST" x-bind:action="actionBase.replace('__ID__', editing.id)" class="p-5">
                            @csrf @method('PUT')
                            @php $slug = (string) $assignment->practice_type; @endphp
                            <div class="grid sm:grid-cols-2 gap-6">
                                {{-- Left: question parameters (type-aware, canonical vocabularies only) --}}
                                <div class="space-y-3">
                                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide">{{ crm_trans($a.'.question_params') }}</p>
                                    @foreach($editSchema as $field)
                                        @php $key = $field['key']; @endphp
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">{{ crm_trans($a.'.f_'.$key) }}</label>
                                            @if($field['input'] === 'select')
                                                <select name="fields[{{ $key }}]" x-model="editing.fields['{{ $key }}']"
                                                        class="w-full rounded-lg border-gray-300 text-sm">
                                                    @foreach($field['options'] as $value => $optionLabel)
                                                        <option value="{{ $value }}">{{ $optionLabel }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($field['input'] === 'number')
                                                <input type="number" name="fields[{{ $key }}]" x-model="editing.fields['{{ $key }}']"
                                                       min="{{ $field['min'] ?? '' }}" max="{{ $field['max'] ?? '' }}"
                                                       class="w-full rounded-lg border-gray-300 text-sm">
                                            @else
                                                <input type="text" name="fields[{{ $key }}]" x-model="editing.fields['{{ $key }}']"
                                                       placeholder="{{ $field['placeholder'] ?? '' }}"
                                                       class="w-full rounded-lg border-gray-300 text-sm">
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Right: choices / rhythm builder / dictation rhythm --}}
                                <div class="space-y-2">
                                    @if($slug === 'rhythm-practice')
                                        {{-- Visual rhythm pattern builder --}}
                                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide">{{ crm_trans($a.'.rhythm_pattern') }}</p>
                                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-2 min-h-[52px] flex flex-wrap gap-1.5">
                                            <template x-for="(tok, i) in rhythmTokens()" :key="i">
                                                <button type="button" x-on:click="removeToken(i)"
                                                        class="inline-flex items-center gap-1 pl-2 pr-1.5 py-1 rounded-md bg-white border border-gray-300 hover:border-red-300 hover:bg-red-50 transition group"
                                                        title="{{ crm_trans($a.'.rhythm_remove_token') }}">
                                                    <span class="leading-none inline-flex items-center text-gray-800" x-html="rhythmIcons[tok] || tok"></span>
                                                    <span class="text-[10px] text-gray-400 group-hover:text-red-500">×</span>
                                                </button>
                                            </template>
                                            <span x-show="rhythmTokens().length === 0" class="text-xs text-gray-400 self-center">{{ crm_trans($a.'.rhythm_empty') }}</span>
                                        </div>
                                        <p class="text-[11px] font-semibold"
                                           x-bind:class="patternTwelfths() === requiredTwelfths() ? 'text-green-600' : 'text-amber-600'"
                                           x-text="beatStatus()"></p>
                                        <div class="grid grid-cols-4 gap-1.5 pt-1">
                                            <template x-for="tok in Object.keys(rhythmIcons)" :key="tok">
                                                <button type="button" x-on:click="addToken(tok)"
                                                        class="flex flex-col items-center py-1.5 rounded-lg border border-gray-200 hover:border-primary-400 hover:bg-primary-50 transition text-gray-800">
                                                    <span class="leading-none inline-flex items-center" x-html="rhythmIcons[tok]"></span>
                                                    <span class="text-[9px] text-gray-400 mt-0.5" x-text="tok.replace(/_/g, ' ')"></span>
                                                </button>
                                            </template>
                                        </div>
                                        <input type="hidden" name="fields[note_values]" x-bind:value="rhythmTokens().join(', ')">
                                        <p class="text-[11px] text-gray-400 mt-1">{{ crm_trans($a.'.rhythm_pattern_hint') }}</p>

                                    @elseif($slug === 'melodic-dictation')
                                        {{-- Rhythm building blocks (same vocabulary as the Exercise Setup studio) --}}
                                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide">{{ crm_trans($a.'.dictation_rhythm_title') }}</p>
                                        <div class="grid grid-cols-2 gap-1.5">
                                            @foreach($dictationRhythmValues as $value)
                                                <label class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-700 cursor-pointer hover:bg-gray-50">
                                                    <input type="checkbox" name="rhythm_values[]" value="{{ $value }}"
                                                           x-model="editing.rhythm_values"
                                                           class="rounded border-gray-300 text-primary-600 w-3.5 h-3.5">
                                                    {{ str_replace('-', ' ', $value) }}
                                                </label>
                                            @endforeach
                                        </div>
                                        <label class="flex items-start gap-2 mt-2 px-2.5 py-2 rounded-lg bg-indigo-50 border border-indigo-100 text-xs text-indigo-800 cursor-pointer">
                                            <input type="checkbox" name="regenerate_melody" value="1" class="rounded border-indigo-300 text-indigo-600 w-3.5 h-3.5 mt-0.5">
                                            <span>{{ crm_trans($a.'.dictation_regenerate_label') }}</span>
                                        </label>
                                        <p class="text-[11px] text-gray-400 mt-1">{{ crm_trans($a.'.dictation_rhythm_hint') }}</p>

                                    @elseif($choicesEditable)
                                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide">{{ crm_trans($a.'.answer_choices') }}</p>

                                        {{-- Correct answer: green, ticked, read-only (tied to the audio) --}}
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-md bg-green-600 text-white text-xs font-bold flex items-center justify-center shrink-0">A</span>
                                            <div class="flex-1 flex items-center gap-2 rounded-lg border-2 border-green-400 bg-green-50 px-3 py-2 min-w-0">
                                                <span class="flex-1 truncate text-sm font-semibold text-green-800" x-text="editing.correct"></span>
                                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-green-600 shrink-0" title="{{ crm_trans($a.'.correct_answer') }}">
                                                    <svg class="w-3 h-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Distractors: editable --}}
                                        <template x-for="(d, i) in editing.distractors" :key="i">
                                            <div class="flex items-center gap-2">
                                                <span class="w-6 h-6 rounded-md bg-gray-100 text-gray-500 text-xs font-bold flex items-center justify-center shrink-0" x-text="String.fromCharCode(66 + i)"></span>
                                                <div class="flex-1 flex items-center gap-2 rounded-lg border border-gray-300 focus-within:border-primary-400 focus-within:ring-1 focus-within:ring-primary-400 px-3 py-2 min-w-0">
                                                    <input type="text" x-model="editing.distractors[i]"
                                                           class="flex-1 min-w-0 border-0 p-0 text-sm text-gray-700 focus:ring-0">
                                                    <span class="inline-block w-5 h-5 rounded-full border-2 border-gray-200 shrink-0"></span>
                                                </div>
                                            </div>
                                        </template>

                                        <input type="hidden" name="options" x-bind:value="editing.distractors.join('\n')">
                                        <p class="text-[11px] text-gray-400 mt-1">{{ crm_trans($a.'.answer_choices_hint') }}</p>
                                    @else
                                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide">{{ crm_trans($a.'.answer_choices') }}</p>
                                        <div class="rounded-lg bg-gray-50 border border-gray-200 px-3 py-3 text-xs text-gray-500 leading-relaxed">
                                            @if($slug === 'single-note-practice')
                                                {{ crm_trans($a.'.choices_keyboard_note') }}
                                            @elseif($slug === 'interval-comparison-practice')
                                                {{ crm_trans($a.'.choices_pair_note') }}
                                            @else
                                                {{ crm_trans($a.'.choices_auto_note') }}
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 pt-5 mt-1 border-t border-gray-100">
                                <button type="button" x-on:click="editing = null"
                                        class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">{{ crm_trans($a.'.cancel') }}</button>
                                <button type="submit"
                                        class="px-5 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">{{ crm_trans($a.'.save') }}</button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </div>
        @endif

        {{-- Results (sent assignments) --}}
        @if($assignment->isSent())
        <div class="card overflow-hidden mt-6">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <h2 class="font-bold text-gray-900">{{ crm_trans($a.'.report') }}</h2>
            </div>
            <div class="overflow-x-auto"><table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">{{ crm_trans($a.'.report_student') }}</th>
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">{{ crm_trans($a.'.report_status') }}</th>
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">{{ crm_trans($a.'.report_score') }}</th>
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">{{ crm_trans($a.'.report_attempts') }}</th>
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">{{ crm_trans($a.'.report_completed_at') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($assignment->recipients as $recipient)
                        <tr>
                            <td class="px-5 py-3">
                                <a href="{{ crm_route('students.show', $recipient->student) }}" class="text-sm font-semibold text-gray-800 hover:text-primary-700">
                                    {{ $recipient->student->name }} {{ $recipient->student->surname }}
                                </a>
                            </td>
                            <td class="px-5 py-3">
                                @if($recipient->status === 'completed')
                                    <span class="text-xs font-semibold text-green-700">{{ crm_trans($a.'.recipient_completed') }}</span>
                                @elseif($recipient->isOverdue())
                                    <span class="text-xs font-semibold text-red-600">{{ crm_trans($a.'.recipient_overdue') }}</span>
                                @else
                                    <span class="text-xs text-gray-500">{{ crm_trans($a.'.recipient_'.$recipient->status) }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-sm font-bold text-gray-800">
                                {{ $recipient->best_score !== null ? round((float) $recipient->best_score, 1).'%' : '—' }}
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500">{{ $recipient->attempts_count }}</td>
                            <td class="px-5 py-3 text-xs text-gray-400 hidden sm:table-cell">{{ $recipient->completed_at?->format('M j, Y H:i') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        </div>
        @endif
    </div>

    <div class="space-y-6">
        {{-- Send --}}
        @if($editable)
        <div class="card p-5">
            <h2 class="font-bold text-gray-900 mb-3">{{ crm_trans($a.'.send_title') }}</h2>
            @if($students->isEmpty() && $classes->isEmpty())
                <p class="text-sm text-gray-400">{{ crm_trans($a.'.error_no_recipients') }}</p>
            @else
                <form method="POST" action="{{ crm_route('assignments.send', $assignment) }}" class="space-y-3">
                    @csrf
                    @if($students->isNotEmpty())
                        <p class="text-xs font-semibold text-gray-500">{{ crm_trans($a.'.select_students') }}</p>
                        <div class="space-y-1.5 max-h-44 overflow-y-auto pr-1">
                            @foreach($students as $s)
                                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="student_ids[]" value="{{ $s->id }}" class="rounded border-gray-300 text-primary-600 w-4 h-4">
                                    {{ $s->name }} {{ $s->surname }}
                                </label>
                            @endforeach
                        </div>
                    @endif
                    @if($classes->isNotEmpty())
                        <p class="text-xs font-semibold text-gray-500">{{ crm_trans($a.'.select_classes') }}</p>
                        <div class="space-y-1.5">
                            @foreach($classes as $c)
                                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="class_ids[]" value="{{ $c->id }}" class="rounded border-gray-300 text-primary-600 w-4 h-4">
                                    {{ $c->name }}
                                </label>
                            @endforeach
                        </div>
                    @endif
                    <button class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition">
                        {{ crm_trans($a.'.send') }}
                    </button>
                </form>
            @endif
        </div>
        @endif

        {{-- Settings --}}
        <div class="card p-5">
            <h2 class="font-bold text-gray-900 mb-3">{{ crm_trans($a.'.manual_section') }}</h2>
            <form method="POST" action="{{ crm_route('assignments.update', $assignment) }}" class="space-y-3">
                @csrf @method('PUT')
                <input type="text" name="title" required maxlength="150" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('title', $assignment->title) }}">
                <textarea name="description" rows="2" maxlength="2000" class="w-full rounded-lg border-gray-300 text-sm" placeholder="{{ crm_trans($a.'.field_description') }}">{{ old('description', $assignment->description) }}</textarea>
                <textarea name="instructions" rows="2" maxlength="5000" class="w-full rounded-lg border-gray-300 text-sm" placeholder="{{ crm_trans($a.'.field_instructions') }}">{{ old('instructions', $assignment->instructions) }}</textarea>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ crm_trans($a.'.field_due_at') }}</label>
                    <input type="datetime-local" name="due_at" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('due_at', $assignment->due_at?->format('Y-m-d\TH:i')) }}">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ crm_trans($a.'.field_max_attempts') }}</label>
                    <input type="number" name="max_attempts" min="1" max="20" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('max_attempts', $assignment->max_attempts) }}">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ crm_trans($a.'.field_reward') }}</label>
                    <input type="text" name="reward_label" maxlength="100" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('reward_label', $assignment->reward_label) }}">
                </div>
                <button class="w-full py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold rounded-lg transition">{{ crm_trans('classes.save') }}</button>
            </form>
        </div>

        @if($assignment->ai_prompt)
        <div class="card p-5">
            <h2 class="font-bold text-gray-900 mb-2 flex items-center gap-2">
                <i data-lucide="sparkles" class="w-4 h-4 text-indigo-500"></i> {{ crm_trans($a.'.ai_section') }}
            </h2>
            <p class="text-sm text-gray-600 italic">"{{ $assignment->ai_prompt }}"</p>
        </div>
        @endif
    </div>
</div>

@push('head')
<script src="https://unpkg.com/tone@14.7.77/build/Tone.js"></script>
@endpush

@push('scripts')
<script>
// Question-list Alpine state + the visual rhythm pattern builder used by the
// edit modal. Durations are counted in "twelfths of a quarter" — the same
// unit RhythmGroupingService uses server-side, so client and server always
// agree on whether a pattern fills the meter.
function assignmentQuestions(actionBase) {
    return {
        editing: null,
        actionBase: actionBase,
        // Inline-SVG note glyphs — Unicode musical symbols (U+1D15D…) are
        // missing from many system fonts, so every token is drawn explicitly.
        rhythmIcons: (function () {
            const head = hollow => hollow
                ? '<ellipse cx="7.5" cy="20.5" rx="4.4" ry="3" transform="rotate(-20 7.5 20.5)" fill="none" stroke="currentColor" stroke-width="1.5"/>'
                : '<ellipse cx="7.5" cy="20.5" rx="4.4" ry="3" transform="rotate(-20 7.5 20.5)" fill="currentColor"/>';
            const stem = '<line x1="11.6" y1="19.8" x2="11.6" y2="3.5" stroke="currentColor" stroke-width="1.3"/>';
            const flag1 = '<path d="M11.6 3.5 C15.8 6 16.2 9.4 13.2 12.6 C15 8.9 14 6.8 11.6 6 Z" fill="currentColor"/>';
            const flag2 = '<path d="M11.6 8 C15.8 10.5 16.2 13.9 13.2 17.1 C15 13.4 14 11.3 11.6 10.5 Z" fill="currentColor"/>';
            const dot = '<circle cx="16" cy="20.5" r="1.4" fill="currentColor"/>';
            const svg = inner => '<svg width="19" height="26" viewBox="0 0 19 26" aria-hidden="true">' + inner + '</svg>';
            return {
                'whole': svg('<ellipse cx="9.5" cy="20.5" rx="5.4" ry="3.4" fill="none" stroke="currentColor" stroke-width="1.7"/>'),
                'dotted-half': svg(head(true) + stem + dot),
                'half': svg(head(true) + stem),
                'dotted-quarter': svg(head(false) + stem + dot),
                'quarter': svg(head(false) + stem),
                'dotted-eighth': svg(head(false) + stem + flag1 + dot),
                'eighth': svg(head(false) + stem + flag1),
                'sixteenth': svg(head(false) + stem + flag1 + flag2),
                'triplet-eighth': svg(head(false) + stem + flag1 + '<text x="0.5" y="10" font-size="8" font-weight="bold" fill="currentColor">3</text>'),
                'whole_rest': svg('<line x1="3" y1="9" x2="16" y2="9" stroke="currentColor" stroke-width="1.2"/><rect x="6" y="9" width="7" height="4" fill="currentColor"/>'),
                'half_rest': svg('<rect x="6" y="9.5" width="7" height="4" fill="currentColor"/><line x1="3" y1="13.5" x2="16" y2="13.5" stroke="currentColor" stroke-width="1.2"/>'),
                'quarter_rest': svg('<path d="M8 4 L12.5 9.5 L8.8 13 L13 18 C8.8 17 8 20 10 23" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'),
                'eighth_rest': svg('<circle cx="6.5" cy="9" r="2" fill="currentColor"/><path d="M6.8 10.8 C9.5 12.6 12 12.2 13.8 9.5 L10 22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>'),
            };
        })(),
        rhythmTwelfths: {
            'whole': 48, 'dotted-half': 36, 'half': 24, 'dotted-quarter': 18, 'quarter': 12,
            'dotted-eighth': 9, 'eighth': 6, 'triplet-eighth': 4, 'sixteenth': 3,
            'whole_rest': 48, 'half_rest': 24, 'quarter_rest': 12, 'eighth_rest': 6,
        },
        rhythmTokens() {
            const raw = this.editing?.fields?.note_values || '';
            return raw.split(',').map(s => s.trim()).filter(Boolean);
        },
        setTokens(tokens) {
            if (this.editing) this.editing.fields.note_values = tokens.join(', ');
        },
        addToken(token) {
            const t = this.rhythmTokens();
            t.push(token);
            this.setTokens(t);
        },
        removeToken(index) {
            const t = this.rhythmTokens();
            t.splice(index, 1);
            this.setTokens(t);
        },
        patternTwelfths() {
            return this.rhythmTokens().reduce((sum, t) => sum + (this.rhythmTwelfths[t] || 0), 0);
        },
        requiredTwelfths() {
            const ts = String(this.editing?.fields?.time_signature || '4/4').split('/').map(Number);
            const bars = parseInt(this.editing?.fields?.bars || '1', 10) || 1;
            if (!ts[0] || !ts[1]) return 0;
            return bars * ts[0] * (48 / ts[1]);
        },
        beatStatus() {
            const got = this.patternTwelfths() / 12;
            const need = this.requiredTwelfths() / 12;
            const fmt = n => (Math.round(n * 100) / 100).toString();
            return fmt(got) + ' / ' + fmt(need) + ' ' + @js(crm_trans('assignments.rhythm_beats_label'));
        },
    };
}

// Same Salamander piano sampler as the student practice engine, so previews
// sound identical to what students hear.
const PreviewAudio = (function () {
    let sampler = null, ready = false;
    function init() {
        if (sampler) return;
        sampler = new Tone.Sampler({
            urls: {
                A1:'A1.mp3', C2:'C2.mp3', 'D#2':'Ds2.mp3', 'F#2':'Fs2.mp3',
                A2:'A2.mp3', C3:'C3.mp3', 'D#3':'Ds3.mp3', 'F#3':'Fs3.mp3',
                A3:'A3.mp3', C4:'C4.mp3', 'D#4':'Ds4.mp3', 'F#4':'Fs4.mp3',
                A4:'A4.mp3', C5:'C5.mp3', 'D#5':'Ds5.mp3', 'F#5':'Fs5.mp3',
                A5:'A5.mp3', C6:'C6.mp3', 'D#6':'Ds6.mp3', 'F#6':'Fs6.mp3',
            },
            release: 1,
            baseUrl: 'https://tonejs.github.io/audio/salamander/',
            onload: () => { ready = true; }
        }).toDestination();
    }
    async function ensureReady() {
        await Tone.start();
        if (!sampler) init();
        const deadline = Date.now() + 8000;
        while (!ready && Date.now() < deadline) await new Promise(r => setTimeout(r, 80));
    }
    return {
        async play(notes, mode) {
            await ensureReady();
            const now = Tone.now();
            if (mode === 'simultaneous') {
                notes.forEach(n => sampler.triggerAttackRelease(n, 2, now));
            } else if (mode === 'pairs') {
                // Two interval pairs: A1+A2 together, then B1+B2 together.
                const [a1, a2, b1, b2] = notes;
                if (a1) sampler.triggerAttackRelease(a1, 1.2, now);
                if (a2) sampler.triggerAttackRelease(a2, 1.2, now);
                if (b1) sampler.triggerAttackRelease(b1, 1.2, now + 1.4);
                if (b2) sampler.triggerAttackRelease(b2, 1.2, now + 1.4);
            } else if (mode === 'arpeggio') {
                notes.forEach((n, i) => sampler.triggerAttackRelease(n, 1.5, now + i * 0.4));
            } else {
                notes.forEach((n, i) => sampler.triggerAttackRelease(n, 1, now + i * 0.55));
            }
        }
    };
})();

document.querySelectorAll('.preview-play').forEach(btn => {
    btn.addEventListener('click', () => {
        const notes = btn.dataset.notes.split(',').filter(Boolean);
        if (notes.length) PreviewAudio.play(notes, btn.dataset.mode);
    });
});
</script>
@endpush
@endsection
