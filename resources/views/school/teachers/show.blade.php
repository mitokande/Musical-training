@extends('teacher.layouts.crm')

@section('title', $teacher->fullName().' - '.__('school.teachers.title'))

@section('content')
<a href="{{ route('school.teachers.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-4">
    <i data-lucide="arrow-left" class="w-4 h-4"></i> {{ __('school.teachers.back_to_list') }}
</a>

{{-- Header --}}
<div class="card p-6 mb-6 flex flex-col sm:flex-row sm:items-center gap-5">
    @if($teacher->hasAvatar())
        <img src="{{ $teacher->avatar }}" class="w-16 h-16 rounded-full object-cover" alt="">
    @else
        <div class="w-16 h-16 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xl font-bold">
            {{ strtoupper(substr($teacher->name, 0, 1)) }}
        </div>
    @endif
    <div class="flex-1 min-w-0">
        <h1 class="text-xl font-bold text-gray-900">{{ $teacher->fullName() }}</h1>
        @if($teacher->teacherProfile?->headline)
            <p class="text-sm text-gray-500 mt-0.5">{{ $teacher->teacherProfile->headline }}</p>
        @endif
        <p class="text-xs text-gray-400 mt-1">
            {{ __('school.teachers.active_since', ['date' => $relationship->approved_at?->format('M j, Y')]) }}
        </p>
    </div>
    <div class="flex items-center gap-2">
        @if($teacher->teacherProfile?->slug && $teacher->teacherProfile->isPubliclyVisible())
            <a href="{{ route('teachers.show', $teacher->teacherProfile->slug) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition">
                <i data-lucide="external-link" class="w-4 h-4"></i> {{ __('school.teachers.public_profile') }}
            </a>
        @endif
        <form method="POST" action="{{ route('school.teacher-relationships.destroy', $relationship) }}" onsubmit="return confirm(@js(__('school.teachers.remove_confirm')))">
            @csrf @method('DELETE')
            <button class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">
                <i data-lucide="user-x" class="w-4 h-4"></i> {{ __('school.teachers.remove_teacher') }}
            </button>
        </form>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    @foreach([
        ['label' => __('school.teachers.stat_students'), 'value' => $stats['active_students'], 'icon' => 'users'],
        ['label' => __('school.teachers.stat_classes'), 'value' => $stats['classes'], 'icon' => 'school'],
        ['label' => __('school.teachers.stat_assignments'), 'value' => $stats['assignments'], 'icon' => 'clipboard-list'],
    ] as $stat)
        <div class="card p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center">
                <i data-lucide="{{ $stat['icon'] }}" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $stat['value'] }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $stat['label'] }}</p>
            </div>
        </div>
    @endforeach
</div>

{{-- Students of this teacher --}}
<h2 class="text-lg font-bold text-gray-900 mb-3">{{ __('school.teachers.their_students') }}</h2>
<div class="card overflow-hidden">
    @if($students->isEmpty())
        <div class="p-8 text-center text-sm text-gray-500">{{ __('school.teachers.no_students') }}</div>
    @else
        <ul class="divide-y divide-gray-100">
            @foreach($students as $rel)
                <li class="flex items-center gap-4 px-5 py-3.5">
                    @if($rel->student->hasAvatar())
                        <img src="{{ $rel->student->avatar }}" class="w-10 h-10 rounded-full object-cover" alt="">
                    @else
                        <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold">
                            {{ strtoupper(substr($rel->student->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $rel->student->fullName() }}</p>
                        <p class="text-xs text-gray-400">{{ __('school.teachers.active_since', ['date' => $rel->approved_at?->format('M j, Y')]) }}</p>
                    </div>
                    <a href="{{ route('school.students.show', $rel->student) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i> {{ __('school.teachers.view_student') }}
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
