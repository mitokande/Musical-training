<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>{{ __('teacher.lesson_room.title') }} - {{ config('app.name', 'Harmoniva') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script>
        tailwind.config = { theme: { extend: {
            fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
            colors: { primary: { 50:'#faf5ff',100:'#f3e8ff',600:'#9333ea',700:'#7c3aed' } }
        } } }
    </script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="font-sans bg-gray-900 text-gray-100 min-h-screen flex flex-col">

@php
    $lr = 'teacher.lesson_room';
    $tz = $appointment->timezone ?: config('app.timezone');
@endphp

<header class="shrink-0 flex items-center gap-3 px-4 sm:px-6 py-3 border-b border-gray-800">
    <a href="{{ $links['back'] }}"
       class="p-2 -ml-2 text-gray-400 hover:text-white" title="{{ __($lr.'.leave') }}">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <div class="min-w-0">
        <h1 class="text-sm font-bold truncate">
            {{ __($lr.'.with', ['name' => trim($counterpart->name.' '.$counterpart->surname)]) }}
        </h1>
        <p class="text-xs text-gray-400">
            {{ $appointment->starts_at->timezone($tz)->format('D, M j · H:i') }}–{{ $appointment->ends_at->timezone($tz)->format('H:i') }}
        </p>
    </div>
    @if($appointment->meeting_url)
        <a href="{{ $appointment->meeting_url }}" target="_blank" rel="noopener"
           class="ml-auto shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-300 hover:text-white border border-gray-700 rounded-lg">
            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
            <span class="hidden sm:inline">{{ __($lr.'.open_in_zoom') }}</span>
        </a>
    @endif
</header>

<div class="flex-1 min-h-0 grid lg:grid-cols-[1fr_320px]">

    {{-- Video --}}
    <section class="relative bg-black min-h-[50vh] lg:min-h-0">
        @if($open && $meetingNumber)
            <div id="zoom-root" class="absolute inset-0"></div>
            <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 pointer-events-none">
                <button id="zoom-join" type="button"
                        class="pointer-events-auto px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition disabled:opacity-50">
                    {{ $isTeacher ? __($lr.'.start_lesson') : __($lr.'.join_lesson') }}
                </button>
                <p id="zoom-status" hidden class="pointer-events-auto max-w-xs text-center text-xs text-red-300"></p>
            </div>
        @else
            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 px-6 text-center">
                <i data-lucide="clock" class="w-8 h-8 text-gray-600"></i>
                <p class="text-sm font-semibold text-gray-300">{{ __($lr.'.not_open') }}</p>
                <p class="text-xs text-gray-500">
                    {{ __($lr.'.opens_at', ['time' => $appointment->starts_at->timezone($tz)->subMinutes((int) config('zoom.join.opens_minutes_before'))->format('D, M j · H:i')]) }}
                </p>
            </div>
        @endif
    </section>

    {{-- Side panel --}}
    <aside class="border-t lg:border-t-0 lg:border-l border-gray-800 bg-gray-950 overflow-y-auto">

        <div class="p-4 border-b border-gray-800">
            <h2 class="text-[11px] font-bold uppercase tracking-wide text-gray-500 mb-2">{{ __($lr.'.lesson_info') }}</h2>
            <dl class="space-y-1.5 text-xs">
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">{{ __($lr.'.topic') }}</dt>
                    <dd class="text-gray-200 text-right">{{ $appointment->topic ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">{{ __($lr.'.duration') }}</dt>
                    <dd class="text-gray-200">{{ __($lr.'.minutes', ['count' => $appointment->starts_at->diffInMinutes($appointment->ends_at)]) }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-gray-500">{{ __($lr.'.timezone') }}</dt>
                    <dd class="text-gray-200">{{ $tz }}</dd>
                </div>
            </dl>
        </div>

        @if($isTeacher)
            <div class="p-4 border-b border-gray-800 space-y-2">
                <h2 class="text-[11px] font-bold uppercase tracking-wide text-gray-500">{{ __($lr.'.student') }}</h2>
                <a href="{{ $links['student'] }}" target="_blank"
                   class="flex items-center gap-2.5 group">
                    <div class="w-8 h-8 rounded-full bg-primary-600/20 text-primary-300 flex items-center justify-center text-xs font-bold shrink-0">
                        {{ strtoupper(substr($counterpart->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-gray-200 truncate group-hover:text-white">{{ trim($counterpart->name.' '.$counterpart->surname) }}</p>
                        <p class="text-[11px] text-gray-500">{{ __($lr.'.view_profile') }}</p>
                    </div>
                </a>
                <a href="{{ $links['assign'] }}" target="_blank"
                   class="flex items-center justify-center gap-1.5 w-full px-3 py-2 text-xs font-semibold text-primary-200 bg-primary-600/15 hover:bg-primary-600/25 rounded-lg transition">
                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i> {{ __($lr.'.assign_exercise') }}
                </a>
            </div>

            <div class="p-4" x-data="{ saved: false }">
                <h2 class="text-[11px] font-bold uppercase tracking-wide text-gray-500 mb-2">{{ __($lr.'.notes') }}</h2>
                {{-- Reuses the existing appointment-note endpoint; no room-specific writes. --}}
                <form method="POST" action="{{ $links['note'] }}" @submit="saved = true">
                    @csrf
                    @method('PUT')
                    <textarea name="teacher_note" rows="6" maxlength="2000"
                              placeholder="{{ __($lr.'.notes_placeholder') }}"
                              class="w-full rounded-lg bg-gray-900 border-gray-700 text-xs text-gray-200 placeholder-gray-600 focus:border-primary-600 focus:ring-primary-600">{{ $appointment->teacher_note }}</textarea>
                    <button class="mt-2 w-full px-3 py-2 bg-gray-800 hover:bg-gray-700 text-xs font-semibold text-gray-200 rounded-lg transition">
                        <span x-show="!saved">{{ __($lr.'.save_notes') }}</span>
                        <span x-show="saved" x-cloak>{{ __($lr.'.saving') }}</span>
                    </button>
                </form>
            </div>
        @endif

    </aside>
</div>

@if($open && $meetingNumber)
    <script>
        window.__zoomRoom = {
            signatureUrl: @json(route('lessons.room.signature', $appointment)),
            csrfToken: @json(csrf_token()),
            language: @json(str_replace('_', '-', app()->getLocale())),
            autoJoin: false,
            strings: { error: @json(__($lr.'.error')) },
        };
    </script>
    @vite(['resources/js/zoom-room.js'])
@endif

<script>lucide.createIcons();</script>
</body>
</html>
