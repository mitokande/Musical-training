@extends('teacher.layouts.crm')

@section('title', __('teacher.appointments.title'))

@section('content')
@php
    $ap = 'teacher.appointments';
    $days = [__('Sunday'), __('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday')];
    $statusColors = [
        'pending_teacher_approval' => 'bg-amber-50 text-amber-700',
        'confirmed' => 'bg-green-50 text-green-700',
        'rejected' => 'bg-gray-100 text-gray-500',
        'cancelled_by_teacher' => 'bg-red-50 text-red-600',
        'cancelled_by_student' => 'bg-red-50 text-red-600',
        'reschedule_requested' => 'bg-blue-50 text-blue-700',
        'completed' => 'bg-primary-50 text-primary-700',
        'no_show' => 'bg-gray-100 text-gray-500',
    ];
    $tz = $settings->timezone ?: config('app.timezone');
@endphp

@if (session('status'))
    <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
        <p class="text-sm text-green-700">{{ __('teacher.profile.saved') }}</p>
    </div>
@endif

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">{{ __($ap.'.title') }}</h1>
    <p class="text-gray-500 text-sm mt-1">{{ __($ap.'.subtitle') }}</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Pending requests --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <h2 class="font-bold text-gray-900">{{ __($ap.'.pending_requests') }} ({{ $pending->count() }})</h2>
            </div>
            @if($pending->isEmpty())
                <div class="p-6 text-sm text-gray-400">{{ __($ap.'.no_pending') }}</div>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($pending as $appointment)
                        <li class="px-5 py-4">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900">
                                        {{ $appointment->student->name }} {{ $appointment->student->surname }}
                                        <span class="ml-2 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $statusColors[$appointment->status] ?? '' }}">
                                            {{ __($ap.'.status_'.$appointment->status) }}
                                        </span>
                                    </p>
                                    <p class="text-sm text-gray-600 mt-0.5">
                                        {{ $appointment->starts_at->timezone($tz)->format('D, M j, Y H:i') }}–{{ $appointment->ends_at->timezone($tz)->format('H:i') }}
                                    </p>
                                    @if($appointment->status === 'reschedule_requested' && $appointment->requested_starts_at)
                                        <p class="text-sm text-blue-700 mt-0.5">
                                            {{ __($ap.'.reschedule_to') }}: {{ $appointment->requested_starts_at->timezone($tz)->format('D, M j, Y H:i') }}
                                        </p>
                                    @endif
                                    @if($appointment->topic)
                                        <p class="text-xs text-gray-400 mt-1">{{ __($ap.'.topic') }}: {{ $appointment->topic }}</p>
                                    @endif
                                </div>
                                <div class="flex flex-col gap-2 shrink-0 w-full sm:w-56">
                                    <form method="POST" action="{{ route('teacher.appointments.confirm', $appointment) }}" class="flex flex-col gap-1.5">
                                        @csrf
                                        <input type="url" name="meeting_url" placeholder="{{ __($ap.'.meeting_url') }}" class="rounded-lg border-gray-300 text-xs">
                                        <button class="w-full py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg transition">{{ __($ap.'.confirm') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('teacher.appointments.reject', $appointment) }}">
                                        @csrf
                                        <button class="w-full py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-lg transition">{{ __($ap.'.reject') }}</button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Upcoming --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <h2 class="font-bold text-gray-900">{{ __($ap.'.upcoming') }}</h2>
            </div>
            @if($upcoming->isEmpty())
                <div class="p-6 text-sm text-gray-400">{{ __($ap.'.no_upcoming') }}</div>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($upcoming as $appointment)
                        <li class="px-5 py-4" x-data="{ open: false }">
                            <div class="flex items-center gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900">{{ $appointment->student->name }} {{ $appointment->student->surname }}</p>
                                    <p class="text-sm text-gray-600">{{ $appointment->starts_at->timezone($tz)->format('D, M j, Y H:i') }}–{{ $appointment->ends_at->timezone($tz)->format('H:i') }}</p>
                                    @if($appointment->meeting_url)
                                        <a href="{{ $appointment->meeting_url }}" target="_blank" rel="noopener" class="text-xs font-semibold text-primary-600 hover:text-primary-800 inline-flex items-center gap-1">
                                            <i data-lucide="video" class="w-3 h-3"></i> {{ $appointment->meeting_url }}
                                        </a>
                                    @endif
                                </div>
                                <button @click="open = !open" class="p-2 text-gray-400 hover:text-gray-600"><i data-lucide="settings-2" class="w-4 h-4"></i></button>
                            </div>
                            <div x-show="open" x-cloak class="mt-3 pt-3 border-t border-gray-100 grid sm:grid-cols-2 gap-2">
                                <form method="POST" action="{{ route('teacher.appointments.reschedule', $appointment) }}" class="flex gap-1.5">
                                    @csrf
                                    <input type="datetime-local" name="starts_at" required class="flex-1 rounded-lg border-gray-300 text-xs">
                                    <button class="px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg">↻</button>
                                </form>
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('teacher.appointments.complete', $appointment) }}" class="flex-1">
                                        @csrf
                                        <button class="w-full py-1.5 bg-primary-50 text-primary-700 text-xs font-semibold rounded-lg hover:bg-primary-100">{{ __($ap.'.complete') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('teacher.appointments.no-show', $appointment) }}" class="flex-1">
                                        @csrf
                                        <button class="w-full py-1.5 bg-gray-100 text-gray-600 text-xs font-semibold rounded-lg hover:bg-gray-200">{{ __($ap.'.no_show') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('teacher.appointments.cancel', $appointment) }}" class="flex-1">
                                        @csrf
                                        <button class="w-full py-1.5 bg-red-50 text-red-600 text-xs font-semibold rounded-lg hover:bg-red-100">{{ __($ap.'.cancel') }}</button>
                                    </form>
                                </div>
                                <form method="POST" action="{{ route('teacher.appointments.note', $appointment) }}" class="sm:col-span-2 flex gap-1.5">
                                    @csrf @method('PUT')
                                    <input type="text" name="teacher_note" value="{{ $appointment->teacher_note }}" placeholder="{{ __($ap.'.note') }}" class="flex-1 rounded-lg border-gray-300 text-xs">
                                    <button class="px-3 py-1.5 bg-gray-800 text-white text-xs font-semibold rounded-lg">✓</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Past --}}
        @if($past->isNotEmpty())
        <div class="card overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <h2 class="font-bold text-gray-900">{{ __($ap.'.past') }}</h2>
            </div>
            <ul class="divide-y divide-gray-100">
                @foreach($past as $appointment)
                    <li class="px-5 py-3 flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800">{{ $appointment->student->name }} {{ $appointment->student->surname }}</p>
                            <p class="text-xs text-gray-400">{{ $appointment->starts_at->timezone($tz)->format('M j, Y H:i') }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $statusColors[$appointment->status] ?? '' }}">
                            {{ __($ap.'.status_'.$appointment->status) }}
                        </span>
                        @if($appointment->status === 'confirmed')
                            <form method="POST" action="{{ route('teacher.appointments.complete', $appointment) }}">
                                @csrf
                                <button class="text-xs font-semibold text-primary-600 hover:text-primary-800">{{ __($ap.'.complete') }}</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <div class="space-y-6">
        {{-- Booking settings --}}
        <div class="card p-5">
            <h2 class="font-bold text-gray-900 mb-3">{{ __($ap.'.settings') }}</h2>
            <form method="POST" action="{{ route('teacher.calendar.settings') }}" class="space-y-3">
                @csrf @method('PUT')
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 cursor-pointer">
                    <input type="checkbox" name="booking_enabled" value="1" @checked($settings->booking_enabled) class="rounded border-gray-300 text-primary-600 w-4 h-4">
                    {{ __($ap.'.booking_enabled') }}
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($ap.'.lesson_duration') }}</label>
                        <input type="number" name="lesson_duration_minutes" min="15" max="180" value="{{ $settings->lesson_duration_minutes }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($ap.'.buffer') }}</label>
                        <input type="number" name="buffer_minutes" min="0" max="60" value="{{ $settings->buffer_minutes }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($ap.'.advance_days') }}</label>
                        <input type="number" name="advance_booking_days" min="1" max="120" value="{{ $settings->advance_booking_days }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($ap.'.min_notice') }}</label>
                        <input type="number" name="min_notice_hours" min="0" max="168" value="{{ $settings->min_notice_hours }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __($ap.'.timezone') }}</label>
                    <select name="timezone" class="w-full rounded-lg border-gray-300 text-sm">
                        @foreach(timezone_identifiers_list() as $tzOption)
                            <option value="{{ $tzOption }}" @selected($settings->timezone === $tzOption)>{{ $tzOption }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="w-full py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition">{{ __($ap.'.save') }}</button>
            </form>
        </div>

        {{-- Weekly availability --}}
        <div class="card p-5">
            <h2 class="font-bold text-gray-900 mb-3">{{ __($ap.'.weekly_availability') }}</h2>
            @if($availabilities->isEmpty())
                <p class="text-sm text-gray-400 mb-3">{{ __($ap.'.no_windows') }}</p>
            @else
                <ul class="space-y-1.5 mb-3">
                    @foreach($availabilities as $window)
                        <li class="flex items-center gap-2 text-sm text-gray-700">
                            <span class="flex-1">{{ $days[$window->weekday] }} · {{ substr($window->start_time, 0, 5) }}–{{ substr($window->end_time, 0, 5) }}</span>
                            <form method="POST" action="{{ route('teacher.calendar.availability.destroy', $window) }}">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-500 hover:text-red-700 font-semibold">×</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
            <form method="POST" action="{{ route('teacher.calendar.availability.store') }}" class="flex gap-1.5">
                @csrf
                <select name="weekday" class="rounded-lg border-gray-300 text-xs w-24">
                    @foreach($days as $i => $day)
                        <option value="{{ $i }}">{{ $day }}</option>
                    @endforeach
                </select>
                <input type="time" name="start_time" required class="rounded-lg border-gray-300 text-xs flex-1">
                <input type="time" name="end_time" required class="rounded-lg border-gray-300 text-xs flex-1">
                <button class="px-3 py-1.5 bg-gray-800 text-white text-xs font-semibold rounded-lg">+</button>
            </form>
        </div>

        {{-- Exceptions --}}
        <div class="card p-5">
            <h2 class="font-bold text-gray-900 mb-3">{{ __($ap.'.exceptions') }}</h2>
            @if($exceptions->isNotEmpty())
                <ul class="space-y-1.5 mb-3">
                    @foreach($exceptions as $exception)
                        <li class="flex items-center gap-2 text-sm text-gray-700">
                            <span class="flex-1">
                                {{ $exception->date->format('M j, Y') }}
                                · {{ $exception->start_time ? substr($exception->start_time, 0, 5).'–'.substr($exception->end_time, 0, 5) : __($ap.'.whole_day') }}
                                <span class="text-[11px] font-semibold {{ $exception->is_blocked ? 'text-red-500' : 'text-green-600' }}">
                                    {{ $exception->is_blocked ? __($ap.'.blocked') : __($ap.'.extra') }}
                                </span>
                            </span>
                            <form method="POST" action="{{ route('teacher.calendar.exceptions.destroy', $exception) }}">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-500 hover:text-red-700 font-semibold">×</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
            <form method="POST" action="{{ route('teacher.calendar.exceptions.store') }}" class="space-y-1.5">
                @csrf
                <div class="flex gap-1.5">
                    <input type="date" name="date" required min="{{ now()->format('Y-m-d') }}" class="rounded-lg border-gray-300 text-xs flex-1">
                    <select name="is_blocked" class="rounded-lg border-gray-300 text-xs">
                        <option value="1">{{ __($ap.'.blocked') }}</option>
                        <option value="0">{{ __($ap.'.extra') }}</option>
                    </select>
                </div>
                <div class="flex gap-1.5">
                    <input type="time" name="start_time" class="rounded-lg border-gray-300 text-xs flex-1" placeholder="start">
                    <input type="time" name="end_time" class="rounded-lg border-gray-300 text-xs flex-1">
                    <button class="px-3 py-1.5 bg-gray-800 text-white text-xs font-semibold rounded-lg">+</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
