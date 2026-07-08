@extends('teacher.layouts.crm')

@section('title', $assignment->title)

@section('content')
@php
    $a = 'teacher.assignments';
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
        <p class="text-sm text-green-700">{{ __($a.'.status_'.session('status')) }}</p>
    </div>
@endif

<a href="{{ route('teacher.assignments.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-4">
    <i data-lucide="arrow-left" class="w-4 h-4"></i> {{ __($a.'.title') }}
</a>

<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-900">{{ $assignment->title }}</h1>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $statusColors[$assignment->status] ?? '' }}">
                {{ __($a.'.status_'.$assignment->status) }}
            </span>
        </div>
        <p class="text-sm text-gray-500 mt-1">
            {{ __($a.'.type_'.$assignment->type) }}
            @if($assignment->practice_type) · {{ ucwords(str_replace('-', ' ', $assignment->practice_type)) }} @endif
            @if($assignment->difficulty) · {{ __($a.'.difficulty_'.$assignment->difficulty) }} @endif
            · {{ $assignment->question_count }} Q
            @if($assignment->due_at) · {{ __($a.'.due') }}: {{ $assignment->due_at->format('M j, Y H:i') }} @endif
        </p>
        @if($assignment->description)<p class="text-sm text-gray-600 mt-2 max-w-2xl">{{ $assignment->description }}</p>@endif
    </div>
    <div class="flex flex-wrap gap-2">
        @if($assignment->type !== 'practice_goal' && count($previews) > 0)
            <a href="{{ route('teacher.assignments.preview', $assignment) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition">
                <i data-lucide="eye" class="w-4 h-4"></i> {{ __($a.'.preview') }}
            </a>
        @endif
        <form method="POST" action="{{ route('teacher.assignments.duplicate', $assignment) }}">
            @csrf
            <button class="px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">{{ __($a.'.duplicate') }}</button>
        </form>
        @if($assignment->status !== 'archived')
            <form method="POST" action="{{ route('teacher.assignments.archive', $assignment) }}">
                @csrf
                <button class="px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">{{ __($a.'.archive') }}</button>
            </form>
        @endif
        @if($assignment->isDraft())
            <form method="POST" action="{{ route('teacher.assignments.destroy', $assignment) }}" onsubmit="return confirm(@js(__($a.'.delete_confirm')))">
                @csrf @method('DELETE')
                <button class="px-3 py-2 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">{{ __($a.'.delete') }}</button>
            </form>
        @endif
    </div>
</div>

@unless($editable)
    <div class="mb-6 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl flex items-center gap-3">
        <i data-lucide="lock" class="w-4 h-4 text-blue-600 shrink-0"></i>
        <p class="text-sm text-blue-700">{{ __($a.'.locked_note') }}</p>
    </div>
@endunless

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Questions --}}
    <div class="lg:col-span-2">
        @if($assignment->media->isNotEmpty())
        <div class="card p-5 mb-6">
            <h2 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                <i data-lucide="paperclip" class="w-4 h-4 text-gray-500"></i> {{ __($a.'.attachments') }}
            </h2>
            <div class="space-y-1.5">
                @foreach($assignment->media as $m)
                    <a href="{{ $m->isPublic() ? $m->publicUrl() : route('teacher.media.download', $m) }}"
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
        <div class="card overflow-hidden" x-data="{ editing: null, actionBase: '{{ route('teacher.assignments.questions.update', [$assignment, '__ID__']) }}' }">
            <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-bold text-gray-900">{{ __($a.'.questions') }} ({{ count($previews) }})</h2>
                @if($editable)
                    <form method="POST" action="{{ route('teacher.assignments.regenerate', $assignment) }}">
                        @csrf
                        <button class="text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 px-3 py-1.5 rounded-lg transition">
                            {{ __($a.'.regenerate_all') }}
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
                                    <span class="text-sm font-bold text-gray-900">{{ __($a.'.correct_answer') }}:</span>
                                    <span class="px-2 py-0.5 rounded-full bg-green-50 text-green-700 text-xs font-bold">{{ $preview['correct'] }}</span>
                                    @if(!empty($preview['notes']))
                                        <button type="button"
                                                class="preview-play inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary-50 text-primary-700 text-xs font-semibold hover:bg-primary-100 transition"
                                                data-notes="{{ implode(',', $preview['notes']) }}"
                                                data-mode="{{ $preview['mode'] }}">
                                            <i data-lucide="play" class="w-3 h-3"></i> {{ __($a.'.play') }}
                                        </button>
                                    @endif
                                </div>
                                <div class="flex flex-wrap gap-x-4 gap-y-0.5 text-xs text-gray-500">
                                    @foreach($preview['meta'] as $key => $value)
                                        <span><span class="text-gray-400">{{ str_replace('_', ' ', $key) }}:</span> <b class="text-gray-600 font-medium">{{ $value }}</b></span>
                                    @endforeach
                                </div>
                                @if(!empty($preview['options']))
                                    <p class="text-xs text-gray-400 mt-1">{{ __($a.'.options') }}: {{ implode(' · ', (array) $preview['options']) }}</p>
                                @endif
                            </div>
                            @if($editable)
                                <div class="flex gap-1 shrink-0">
                                    @php($editPayload = ['id' => $preview['id'], 'position' => $preview['position'], 'fields' => $preview['edit_fields'] ?: new stdClass, 'options' => $preview['options_input']])
                                    <button type="button"
                                            x-on:click='editing = @json($editPayload)'
                                            class="p-1.5 text-gray-400 hover:text-primary-600 transition" title="{{ __($a.'.edit') }}">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    <form method="POST" action="{{ route('teacher.assignments.questions.regenerate', [$assignment, $preview['id']]) }}">
                                        @csrf
                                        <button class="p-1.5 text-gray-400 hover:text-primary-600 transition" title="{{ __($a.'.regenerate') }}">
                                            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('teacher.assignments.questions.destroy', [$assignment, $preview['id']]) }}">
                                        @csrf @method('DELETE')
                                        <button class="p-1.5 text-gray-400 hover:text-red-600 transition" title="{{ __($a.'.remove') }}">
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
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[85vh] overflow-y-auto" x-on:keydown.escape.window="editing = null">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900">
                            {{ __($a.'.edit_question') }} <span class="text-gray-400 font-normal" x-text="'#' + (editing?.position ?? '')"></span>
                        </h3>
                        <button type="button" class="text-gray-400 hover:text-gray-600" x-on:click="editing = null">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <template x-if="editing">
                        <form method="POST" x-bind:action="actionBase.replace('__ID__', editing.id)" class="p-5 space-y-3">
                            @csrf @method('PUT')
                            <template x-for="key in Object.keys(editing.fields)" :key="key">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1 capitalize" x-text="key.replace(/_/g, ' ')"></label>
                                    <input type="text" x-bind:name="'fields[' + key + ']'" x-model="editing.fields[key]"
                                           class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                            </template>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.options') }}</label>
                                <textarea name="options" x-model="editing.options" rows="3"
                                          class="w-full rounded-lg border-gray-300 text-sm font-mono"></textarea>
                                <p class="text-[11px] text-gray-400 mt-1">{{ __($a.'.edit_hint') }}</p>
                            </div>
                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" x-on:click="editing = null"
                                        class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">{{ __($a.'.cancel') }}</button>
                                <button type="submit"
                                        class="px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">{{ __($a.'.save') }}</button>
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
                <h2 class="font-bold text-gray-900">{{ __($a.'.report') }}</h2>
            </div>
            <div class="overflow-x-auto"><table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">{{ __($a.'.report_student') }}</th>
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">{{ __($a.'.report_status') }}</th>
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">{{ __($a.'.report_score') }}</th>
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">{{ __($a.'.report_attempts') }}</th>
                        <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">{{ __($a.'.report_completed_at') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($assignment->recipients as $recipient)
                        <tr>
                            <td class="px-5 py-3">
                                <a href="{{ route('teacher.students.show', $recipient->student) }}" class="text-sm font-semibold text-gray-800 hover:text-primary-700">
                                    {{ $recipient->student->name }} {{ $recipient->student->surname }}
                                </a>
                            </td>
                            <td class="px-5 py-3">
                                @if($recipient->status === 'completed')
                                    <span class="text-xs font-semibold text-green-700">{{ __($a.'.recipient_completed') }}</span>
                                @elseif($recipient->isOverdue())
                                    <span class="text-xs font-semibold text-red-600">{{ __($a.'.recipient_overdue') }}</span>
                                @else
                                    <span class="text-xs text-gray-500">{{ __($a.'.recipient_'.$recipient->status) }}</span>
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
            <h2 class="font-bold text-gray-900 mb-3">{{ __($a.'.send_title') }}</h2>
            @if($students->isEmpty() && $classes->isEmpty())
                <p class="text-sm text-gray-400">{{ __($a.'.error_no_recipients') }}</p>
            @else
                <form method="POST" action="{{ route('teacher.assignments.send', $assignment) }}" class="space-y-3">
                    @csrf
                    @if($students->isNotEmpty())
                        <p class="text-xs font-semibold text-gray-500">{{ __($a.'.select_students') }}</p>
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
                        <p class="text-xs font-semibold text-gray-500">{{ __($a.'.select_classes') }}</p>
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
                        {{ __($a.'.send') }}
                    </button>
                </form>
            @endif
        </div>
        @endif

        {{-- Settings --}}
        <div class="card p-5">
            <h2 class="font-bold text-gray-900 mb-3">{{ __($a.'.manual_section') }}</h2>
            <form method="POST" action="{{ route('teacher.assignments.update', $assignment) }}" class="space-y-3">
                @csrf @method('PUT')
                <input type="text" name="title" required maxlength="150" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('title', $assignment->title) }}">
                <textarea name="description" rows="2" maxlength="2000" class="w-full rounded-lg border-gray-300 text-sm" placeholder="{{ __($a.'.field_description') }}">{{ old('description', $assignment->description) }}</textarea>
                <textarea name="instructions" rows="2" maxlength="5000" class="w-full rounded-lg border-gray-300 text-sm" placeholder="{{ __($a.'.field_instructions') }}">{{ old('instructions', $assignment->instructions) }}</textarea>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_due_at') }}</label>
                    <input type="datetime-local" name="due_at" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('due_at', $assignment->due_at?->format('Y-m-d\TH:i')) }}">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_max_attempts') }}</label>
                    <input type="number" name="max_attempts" min="1" max="20" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('max_attempts', $assignment->max_attempts) }}">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_reward') }}</label>
                    <input type="text" name="reward_label" maxlength="100" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('reward_label', $assignment->reward_label) }}">
                </div>
                <button class="w-full py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold rounded-lg transition">{{ __('teacher.classes.save') }}</button>
            </form>
        </div>

        @if($assignment->ai_prompt)
        <div class="card p-5">
            <h2 class="font-bold text-gray-900 mb-2 flex items-center gap-2">
                <i data-lucide="sparkles" class="w-4 h-4 text-indigo-500"></i> {{ __($a.'.ai_section') }}
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
