@extends('teacher.layouts.crm')

@section('title', __('teacher.assignments.title'))

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
@endphp
@if (session('status') && str_starts_with(session('status'), 'assignment-'))
    <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
        <p class="text-sm text-green-700">{{ __($a.'.status_'.session('status')) }}</p>
    </div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ __($a.'.title') }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ __($a.'.subtitle') }}</p>
    </div>
    <a href="{{ route('teacher.assignments.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition">
        <i data-lucide="plus" class="w-4 h-4"></i> {{ __($a.'.create') }}
    </a>
</div>

<div class="card overflow-hidden">
    @if($assignments->isEmpty())
        <div class="p-10 text-center text-gray-500">
            <i data-lucide="clipboard-list" class="w-10 h-10 mx-auto mb-3 text-gray-300"></i>
            <p class="text-sm">{{ __($a.'.no_assignments') }}</p>
        </div>
    @else
        <div class="overflow-x-auto"><table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __($a.'.field_title') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">{{ __($a.'.field_type') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">{{ __($a.'.due') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __($a.'.recipients') }}</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($assignments as $assignment)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3.5">
                            <a href="{{ route('teacher.assignments.show', $assignment) }}" class="text-sm font-semibold text-gray-900 hover:text-primary-700">
                                {{ $assignment->title }}
                            </a>
                            <p class="text-xs text-gray-400">{{ $assignment->practice_type ?? __($a.'.type_practice_goal') }} · {{ $assignment->question_count }} Q</p>
                            @if($assignment->practice_type && in_array($assignment->status, ['draft', 'completed', 'sent']))
                                <a href="{{ route('teacher.assignments.preview', $assignment) }}"
                                   class="inline-flex items-center gap-1 mt-1 text-[11px] font-semibold text-primary-600 hover:text-primary-800">
                                    <i data-lucide="eye" class="w-3 h-3"></i> {{ __($a.'.preview') }}
                                </a>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-500 hidden sm:table-cell">{{ __($a.'.type_'.$assignment->type) }}</td>
                        <td class="px-5 py-3.5 text-xs text-gray-500 hidden md:table-cell">{{ $assignment->due_at?->format('M j, Y H:i') ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-xs text-gray-600 font-medium">
                            {{ $assignment->completed_count }}/{{ $assignment->recipients_count }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2 py-1 rounded-full text-[11px] font-semibold {{ $statusColors[$assignment->status] ?? '' }}">
                                {{ __($a.'.status_'.$assignment->status) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
        <div class="px-5 py-3">{{ $assignments->links() }}</div>
    @endif
</div>
@endsection
