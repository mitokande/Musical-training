<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    @php $teacherName = $profile->displayName(); @endphp
    <title>{{ __('teacher.booking.title') }} — {{ $teacherName }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: { 50:'#faf5ff',100:'#f3e8ff',200:'#e9d5ff',300:'#d8b4fe',400:'#c084fc',500:'#a855f7',600:'#9333ea',700:'#7c3aed',800:'#6b21a8',900:'#581c87' },
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .card { background: white; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.07); }
    </style>
</head>
<body class="font-sans bg-gray-50 min-h-screen">

@include('partials.navbar', ['active' => 'teachers'])

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ $profile->publicUrl() }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary-600 hover:text-primary-800 mb-3">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> {{ $teacherName }}
        </a>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ __('teacher.booking.title') }}</h1>
        <p class="text-gray-500 mt-1">{{ $teacherName }}</p>
    </div>

    {{-- Appointment calendar --}}
    <div class="card p-6 mb-6">
        <h2 class="font-bold text-gray-900 text-lg mb-4 flex items-center gap-2">
            <i data-lucide="calendar" class="w-5 h-5 text-primary-500"></i> {{ __('teacher.booking.pick_date') }}
        </h2>
        @if($bookingEnabled)
            @auth
                @include('teachers.partials.booking-calendar', ['slug' => $profile->slug])
            @else
                <p class="text-[15px] text-gray-500 mb-4">{{ __('teacher.booking.guest_hint') }}</p>
                <a href="{{ route('login') }}" class="block w-full py-3 bg-primary-600 hover:bg-primary-700 text-white text-[15px] font-semibold rounded-xl transition text-center">
                    {{ __('teacher.booking.login_to_book') }}
                </a>
            @endauth
        @else
            <p class="text-[15px] text-gray-400">{{ __('teacher.public.booking_unavailable') }}</p>
        @endif
    </div>

    {{-- General description + booking rules --}}
    <div class="card p-6">
        <h2 class="font-bold text-gray-900 text-lg mb-3">{{ __('teacher.public.booking_rules_title') }}</h2>
        <p class="text-[15px] text-gray-600 leading-relaxed whitespace-pre-line">{{ __('teacher.public.booking_rules_intro') }}</p>

        <ul class="mt-4 space-y-2.5 text-[15px] text-gray-600">
            <li class="flex items-start gap-2.5"><i data-lucide="clock" class="w-4 h-4 text-primary-500 mt-1 shrink-0"></i> {{ __('teacher.appointments.lesson_duration') }}: <b class="text-gray-800">{{ $settings->lesson_duration_minutes }} min</b></li>
            <li class="flex items-start gap-2.5"><i data-lucide="bell" class="w-4 h-4 text-primary-500 mt-1 shrink-0"></i> {{ __('teacher.appointments.min_notice') }}: <b class="text-gray-800">{{ $settings->min_notice_hours }} h</b></li>
            <li class="flex items-start gap-2.5"><i data-lucide="calendar-range" class="w-4 h-4 text-primary-500 mt-1 shrink-0"></i> {{ __('teacher.appointments.advance_days') }}: <b class="text-gray-800">{{ $settings->advance_booking_days }}</b></li>
        </ul>
    </div>
</div>

@include('partials.footer')

<script>
    document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
</script>
</body>
</html>
