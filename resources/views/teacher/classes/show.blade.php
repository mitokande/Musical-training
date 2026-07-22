@extends('teacher.layouts.crm')

@section('title', $class->name)

@section('content')
@if (session('status') && in_array(session('status'), ['class-updated', 'student-added', 'student-removed', 'class-archived', 'class-restored']))
    <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
        <p class="text-sm text-green-700">{{ crm_trans('profile.saved') }}</p>
    </div>
@endif

<a href="{{ crm_route('classes.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-4">
    <i data-lucide="arrow-left" class="w-4 h-4"></i> {{ crm_trans('classes.title') }}
</a>

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
            {{ $class->name }}
            @if($class->isArchived())
                <span class="text-xs font-semibold text-gray-400 bg-gray-100 px-2 py-1 rounded-full">{{ crm_trans('classes.archived') }}</span>
            @endif
        </h1>
        @if($class->description)<p class="text-gray-500 text-sm mt-1">{{ $class->description }}</p>@endif
    </div>
    <form method="POST" action="{{ crm_route('classes.archive', $class) }}">
        @csrf
        <button class="px-4 py-2 text-sm font-semibold rounded-lg transition {{ $class->isArchived() ? 'bg-green-50 text-green-700 hover:bg-green-100' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            {{ $class->isArchived() ? crm_trans('classes.restore') : crm_trans('classes.archive') }}
        </button>
    </form>
</div>

{{-- Summary --}}
<div class="grid grid-cols-3 gap-3 mb-6">
    <div class="card p-4">
        <p class="text-xl font-bold text-gray-900">{{ $summary['assignments'] }}</p>
        <p class="text-xs text-gray-500">{{ crm_trans('classes.summary_assignments') }}</p>
    </div>
    <div class="card p-4">
        <p class="text-xl font-bold text-gray-900">{{ $summary['completed'] }}</p>
        <p class="text-xs text-gray-500">{{ crm_trans('classes.summary_completed') }}</p>
    </div>
    <div class="card p-4">
        <p class="text-xl font-bold text-gray-900">{{ $summary['average_score'] !== null ? round($summary['average_score'], 1).'%' : '—' }}</p>
        <p class="text-xs text-gray-500">{{ crm_trans('classes.summary_avg') }}</p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="card overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-bold text-gray-900">{{ crm_trans('nav.students') }} ({{ $students->count() }})</h2>
            </div>
            @if($students->isEmpty())
                <div class="p-8 text-center text-sm text-gray-400">{{ crm_trans('classes.no_students') }}</div>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($students as $student)
                        <li class="flex items-center gap-3 px-5 py-3">
                            @if($student->hasAvatar())
                                <img src="{{ $student->avatar }}" class="w-9 h-9 rounded-full object-cover" alt="">
                            @else
                                <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-sm font-bold">
                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                </div>
                            @endif
                            <a href="{{ crm_route('students.show', $student) }}" class="flex-1 text-sm font-semibold text-gray-800 hover:text-primary-700 truncate">
                                {{ $student->name }} {{ $student->surname }}
                            </a>
                            <form method="POST" action="{{ crm_route('classes.students.remove', [$class, $student]) }}">
                                @csrf @method('DELETE')
                                <button class="text-xs font-semibold text-red-500 hover:text-red-700">{{ crm_trans('classes.remove') }}</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        @unless($class->isArchived())
        <div class="card p-5">
            <h2 class="font-bold text-gray-900 mb-3">{{ crm_trans('classes.add_students') }}</h2>
            @if($addableStudents->isEmpty())
                <p class="text-sm text-gray-400">—</p>
            @else
                <form method="POST" action="{{ crm_route('classes.students.add', $class) }}" class="flex gap-2">
                    @csrf
                    <select name="student_id" class="flex-1 rounded-lg border-gray-300 text-sm">
                        @foreach($addableStudents as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} {{ $s->surname }}</option>
                        @endforeach
                    </select>
                    <button class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition">{{ crm_trans('classes.add') }}</button>
                </form>
            @endif
        </div>
        @endunless

        <div class="card p-5">
            <h2 class="font-bold text-gray-900 mb-3">{{ crm_trans('classes.edit') }}</h2>
            <form method="POST" action="{{ crm_route('classes.update', $class) }}" class="space-y-3">
                @csrf @method('PUT')
                <input type="text" name="name" required maxlength="100" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('name', $class->name) }}">
                <textarea name="description" rows="2" maxlength="2000" class="w-full rounded-lg border-gray-300 text-sm">{{ old('description', $class->description) }}</textarea>
                <div class="grid grid-cols-2 gap-3">
                    <select name="level" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">—</option>
                        @foreach(['beginner', 'intermediate', 'advanced'] as $level)
                            <option value="{{ $level }}" @selected($class->level === $level)>{{ crm_trans('assignments.difficulty_'.$level) }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="instrument_focus" maxlength="100" class="w-full rounded-lg border-gray-300 text-sm" value="{{ old('instrument_focus', $class->instrument_focus) }}" placeholder="{{ crm_trans('classes.instrument_focus') }}">
                </div>
                <button class="w-full py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold rounded-lg transition">{{ crm_trans('classes.save') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
