@extends('teacher.layouts.crm')

@section('title', __('teacher.assignments.create'))

@section('content')
@php $a = 'teacher.assignments'; @endphp

<a href="{{ route('teacher.assignments.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-4">
    <i data-lucide="arrow-left" class="w-4 h-4"></i> {{ __($a.'.title') }}
</a>

<h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __($a.'.create') }}</h1>

<div class="max-w-3xl" x-data="assignmentForm()">
    @if($canUseAi)
    {{-- AI Homework Builder --}}
    <div class="card p-5 mb-6 border-indigo-200 bg-gradient-to-br from-indigo-50/60 to-white">
        <div class="flex items-center gap-2 mb-3">
            <i data-lucide="sparkles" class="w-5 h-5 text-indigo-600"></i>
            <h2 class="font-bold text-gray-900">{{ __($a.'.ai_section') }}</h2>
        </div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.ai_prompt_label') }}</label>
        <textarea x-model="aiPrompt" rows="2" maxlength="2000" placeholder="{{ __($a.'.ai_prompt_placeholder') }}" class="w-full rounded-lg border-gray-300 text-sm mb-3"></textarea>
        <button type="button" @click="aiSuggest" :disabled="aiLoading" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-semibold rounded-lg transition">
            <i data-lucide="sparkles" class="w-4 h-4"></i>
            <span x-text="aiLoading ? @js(__($a.'.ai_thinking')) : @js(__($a.'.ai_suggest'))"></span>
        </button>
        <p x-show="aiError" x-text="aiError" class="mt-2 text-sm text-red-600" x-cloak></p>
        <p x-show="aiApplied" class="mt-2 text-sm text-green-700 font-medium" x-cloak>
            {{ __($a.'.ai_applied') }} <span x-text="aiExplanation" class="font-normal text-gray-600"></span>
        </p>
    </div>
    @endif

    <form method="POST" action="{{ route('teacher.assignments.store') }}" class="card p-5 space-y-4">
        @csrf
        <input type="hidden" name="ai_prompt" :value="aiApplied ? aiPrompt : ''">
        <input type="hidden" name="overrides" :value="JSON.stringify(overrides)">

        <h2 class="font-bold text-gray-900">{{ __($a.'.manual_section') }}</h2>

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_title') }}</label>
            <input type="text" name="title" x-model="title" required maxlength="150" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('title') }}">
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_type') }}</label>
                <select name="type" x-model="type" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="exercise">{{ __($a.'.type_exercise') }}</option>
                    <option value="ai_generated">{{ __($a.'.type_ai_generated') }}</option>
                    <option value="learning_path">{{ __($a.'.type_learning_path') }}</option>
                    <option value="practice_goal">{{ __($a.'.type_practice_goal') }}</option>
                </select>
            </div>
            <div x-show="type === 'learning_path'">
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_lp_exercise') }}</label>
                <select name="learning_path_exercise_id" class="w-full rounded-lg border-gray-300 text-sm">
                    @foreach($lpExercises as $lp)
                        <option value="{{ $lp->id }}">{{ $lp->getLocalizedTitle() }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="type === 'exercise' || type === 'ai_generated'">
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_practice_type') }}</label>
                <select name="practice_type" x-model="practiceType" class="w-full rounded-lg border-gray-300 text-sm">
                    @foreach($practiceTypes as $pt)
                        <option value="{{ $pt }}">{{ ucwords(str_replace('-', ' ', $pt)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div x-show="type === 'practice_goal'" x-cloak>
            <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_attachments') }}</label>
            @forelse($mediaLibrary as $m)
                <label class="flex items-center gap-2 py-1.5 text-sm text-gray-700 cursor-pointer">
                    <input type="checkbox" name="media_ids[]" value="{{ $m->id }}" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <i data-lucide="{{ $m->kind === 'photo' ? 'image' : 'file-text' }}" class="w-4 h-4 text-gray-400 shrink-0"></i>
                    <span class="truncate">{{ $m->title ?: $m->original_name }}</span>
                    <span class="text-xs text-gray-400 shrink-0">· {{ __('teacher.media.visibility_'.$m->visibility) }}</span>
                </label>
            @empty
                <p class="text-xs text-gray-400">
                    {{ __($a.'.no_media') }}
                    <a href="{{ route('teacher.profile.edit') }}#media" class="text-primary-600 hover:underline">{{ __($a.'.upload_media') }}</a>
                </p>
            @endforelse
        </div>

        <div class="grid sm:grid-cols-2 gap-4" x-show="type !== 'practice_goal' && type !== 'learning_path'">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_difficulty') }}</label>
                <select name="difficulty" x-model="difficulty" class="w-full rounded-lg border-gray-300 text-sm">
                    @foreach(['beginner', 'intermediate', 'advanced'] as $level)
                        <option value="{{ $level }}">{{ __($a.'.difficulty_'.$level) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_question_count') }}</label>
                <input type="number" name="question_count" x-model="questionCount" min="3" max="30" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
        </div>
        <div x-show="type === 'learning_path'">
            <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_question_count') }}</label>
            <input type="number" name="question_count" min="3" max="30" value="10" class="w-full sm:w-48 rounded-lg border-gray-300 text-sm" x-bind:disabled="type !== 'learning_path'">
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_description') }}</label>
            <textarea name="description" rows="2" maxlength="2000" class="w-full rounded-lg border-gray-300 text-sm">{{ old('description') }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_instructions') }}</label>
            <textarea name="instructions" rows="2" maxlength="5000" class="w-full rounded-lg border-gray-300 text-sm">{{ old('instructions') }}</textarea>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_starts_at') }}</label>
                <input type="datetime-local" name="starts_at" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('starts_at') }}">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_due_at') }}</label>
                <input type="datetime-local" name="due_at" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('due_at') }}">
            </div>
        </div>

        <div class="grid sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_max_attempts') }}</label>
                <input type="number" name="max_attempts" min="1" max="20" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('max_attempts') }}">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_daily_minutes') }}</label>
                <input type="number" name="daily_practice_minutes" min="5" max="240" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('daily_practice_minutes') }}">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_weekly_minutes') }}</label>
                <input type="number" name="weekly_practice_minutes" min="10" max="1000" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('weekly_practice_minutes') }}">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($a.'.field_reward') }}</label>
            <input type="text" name="reward_label" maxlength="100" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('reward_label') }}" placeholder="Rhythm Star ⭐">
        </div>

        <button class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition">
            {{ __($a.'.generate') }}
        </button>
    </form>
</div>

@push('scripts')
<script>
function assignmentForm() {
    return {
        type: @js(old('type', 'exercise')),
        title: @js(old('title', '')),
        practiceType: @js(old('practice_type', 'melodic-interval-practice')),
        difficulty: @js(old('difficulty', 'beginner')),
        questionCount: {{ (int) old('question_count', 10) }},
        overrides: {},
        aiPrompt: '',
        aiLoading: false,
        aiError: '',
        aiApplied: false,
        aiExplanation: '',
        async aiSuggest() {
            if (this.aiPrompt.trim().length < 10) return;
            this.aiLoading = true; this.aiError = ''; this.aiApplied = false;
            try {
                const res = await fetch(@js(route('teacher.assignments.ai-suggest')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ prompt: this.aiPrompt.trim() })
                });
                const data = await res.json();
                if (!res.ok) { this.aiError = data.error || 'Error'; return; }
                this.type = 'ai_generated';
                this.practiceType = data.practice_type;
                this.difficulty = data.difficulty;
                this.questionCount = data.question_count;
                this.overrides = data.overrides || {};
                if (data.title && !this.title) this.title = data.title;
                this.aiExplanation = data.explanation || '';
                this.aiApplied = true;
            } catch (e) {
                this.aiError = @js(__($a.'.ai_failed'));
            } finally {
                this.aiLoading = false;
            }
        }
    };
}
</script>
@endpush
@endsection
