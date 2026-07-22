<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('teacher.my_appointments.title') }} - {{ config('app.name', 'Harmoniva') }}</title>
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
    <style>[x-cloak] { display: none !important; } .card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; }</style>
</head>
<body class="font-sans bg-gray-50 min-h-screen">
@include('partials.navbar')

@php
    $ma = 'teacher.my_appointments';
    $ap = 'teacher.appointments';
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
@endphp

<main class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-2xl font-bold text-gray-900">{{ __($ma.'.title') }}</h1>
    <p class="text-gray-500 text-sm mt-1 mb-6">{{ __($ma.'.subtitle') }}</p>

    @php $statusKey = $ma.'.status_'.session('status'); @endphp
    @if (session('status') && \Illuminate\Support\Facades\Lang::has($statusKey))
        <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
            <p class="text-sm text-green-700">{{ __($statusKey) }}</p>
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-xl">
            @foreach ($errors->all() as $error)<p class="text-sm text-red-600">{{ $error }}</p>@endforeach
        </div>
    @endif

    @if($upcoming->isEmpty() && $history->isEmpty())
        <div class="card p-10 text-center text-gray-500">
            <i data-lucide="calendar" class="w-10 h-10 mx-auto mb-3 text-gray-300"></i>
            <p class="text-sm">{{ __($ma.'.none') }}</p>
        </div>
    @endif

    @foreach([['title' => __($ma.'.upcoming'), 'items' => $upcoming], ['title' => __($ma.'.history'), 'items' => $history]] as $section)
        @if($section['items']->isNotEmpty())
            <h2 class="text-lg font-bold text-gray-900 mb-3 mt-6">{{ $section['title'] }}</h2>
            <div class="space-y-3">
                @foreach($section['items'] as $appointment)
                    @php
                        $teacher = $appointment->teacher;
                        $tz = $appointment->timezone ?? config('app.timezone');
                        $actionable = in_array($appointment->status, ['pending_teacher_approval', 'confirmed', 'reschedule_requested']);
                    @endphp
                    <div class="card p-5" x-data="{ resched: false }">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-bold text-gray-900">{{ $appointment->starts_at->timezone($tz)->format('D, M j, Y H:i') }}</p>
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $statusColors[$appointment->status] ?? '' }}">
                                        {{ __($ap.'.status_'.$appointment->status) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 mt-0.5">
                                    {{ __($ma.'.with', ['teacher' => trim($teacher->name.' '.$teacher->surname)]) }}
                                    @if($teacher->teacherProfile?->isPubliclyVisible())
                                        · <a href="{{ $teacher->teacherProfile->publicUrl() }}" class="text-primary-600 font-semibold hover:text-primary-800">{{ __('teacher.my_teachers.view_public_profile') }}</a>
                                    @endif
                                </p>
                                @if($appointment->isConfirmed() && $appointment->meeting_url)
                                    <a href="{{ $appointment->meeting_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 mt-2 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg transition">
                                        <i data-lucide="video" class="w-3.5 h-3.5"></i> {{ __($ma.'.join') }}
                                    </a>
                                @endif
                            </div>
                            @if($actionable && $appointment->starts_at->isFuture())
                                <div class="flex gap-2 shrink-0">
                                    @if($appointment->status !== 'reschedule_requested')
                                        <button @click="resched = !resched" class="px-3 py-2 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                                            {{ __($ma.'.request_reschedule') }}
                                        </button>
                                    @endif
                                    <form method="POST" action="{{ route('my-appointments.cancel', $appointment) }}">
                                        @csrf
                                        <button class="px-3 py-2 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">{{ __($ma.'.cancel') }}</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <form x-show="resched" x-cloak method="POST" action="{{ route('my-appointments.reschedule', $appointment) }}" class="mt-3 pt-3 border-t border-gray-100 flex flex-col sm:flex-row gap-2">
                            @csrf
                            <input type="datetime-local" name="starts_at" required class="rounded-lg border-gray-300 text-sm flex-1">
                            <input type="text" name="note" maxlength="500" placeholder="{{ __($ma.'.note') }}" class="rounded-lg border-gray-300 text-sm flex-1">
                            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">{{ __($ma.'.send_request') }}</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    @endforeach
</main>

@include('partials.footer')
<script>lucide.createIcons();</script>
</body>
</html>
