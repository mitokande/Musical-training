@extends('teacher.layouts.crm')

@section('title', crm_trans('classes.title'))

@section('content')
@if (session('status') === 'class-created')
    <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
        <p class="text-sm text-green-700">{{ crm_trans('profile.saved') }}</p>
    </div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6" x-data>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ crm_trans('classes.title') }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ crm_trans('classes.subtitle') }}</p>
    </div>
    <button @click="$dispatch('open-class-form')" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition">
        <i data-lucide="plus" class="w-4 h-4"></i> {{ crm_trans('classes.create') }}
    </button>
</div>

@if($classes->isEmpty())
    <div class="card p-10 text-center text-gray-500">
        <i data-lucide="school" class="w-10 h-10 mx-auto mb-3 text-gray-300"></i>
        <p class="text-sm">{{ crm_trans('classes.no_classes') }}</p>
    </div>
@else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($classes as $class)
            <a href="{{ crm_route('classes.show', $class) }}" class="card p-5 hover:shadow-md transition {{ $class->isArchived() ? 'opacity-60' : '' }}">
                <div class="flex items-start justify-between mb-2">
                    <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center">
                        <i data-lucide="school" class="w-5 h-5 text-primary-600"></i>
                    </div>
                    @if($class->isArchived())
                        <span class="text-[11px] font-semibold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">{{ crm_trans('classes.archived') }}</span>
                    @endif
                </div>
                <h3 class="font-bold text-gray-900">{{ $class->name }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ crm_trans('classes.students_count', ['count' => $class->students_count]) }}
                    @if($class->level) · {{ crm_trans('assignments.difficulty_'.$class->level) }} @endif
                    @if($class->instrument_focus) · {{ $class->instrument_focus }} @endif
                </p>
            </a>
        @endforeach
    </div>
@endif

{{-- Create class modal --}}
<div x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }" @open-class-form.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-24">
    <div class="fixed inset-0 bg-black/40" @click="open = false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">{{ crm_trans('classes.create') }}</h3>
            <button @click="open = false" class="p-1 text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form method="POST" action="{{ crm_route('classes.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ crm_trans('classes.name') }}</label>
                <input type="text" name="name" required maxlength="100" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('name') }}">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ crm_trans('classes.description') }}</label>
                <textarea name="description" rows="2" maxlength="2000" class="w-full rounded-lg border-gray-300 text-sm">{{ old('description') }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ crm_trans('classes.level') }}</label>
                    <select name="level" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">—</option>
                        @foreach(['beginner', 'intermediate', 'advanced'] as $level)
                            <option value="{{ $level }}">{{ crm_trans('assignments.difficulty_'.$level) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ crm_trans('classes.instrument_focus') }}</label>
                    <input type="text" name="instrument_focus" maxlength="100" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('instrument_focus') }}">
                </div>
            </div>
            <button class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition">{{ crm_trans('classes.create') }}</button>
        </form>
    </div>
</div>
@endsection
