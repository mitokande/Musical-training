<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('teacher.invitations.title') }} - {{ config('app.name', 'Harmoniva') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script>
        tailwind.config = { theme: { extend: {
            fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
            colors: { primary: { 50:'#faf5ff',100:'#f3e8ff',600:'#9333ea',700:'#7c3aed' } }
        } } }
    </script>
</head>
<body class="font-sans bg-gray-50 min-h-screen">
@include('partials.navbar')

<main class="max-w-lg mx-auto px-4 py-16">
    @php $teacher = $invitation->teacher; @endphp
    <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center shadow-sm">
        @if($teacher->hasAvatar())
            <img src="{{ $teacher->avatar }}" class="w-20 h-20 rounded-full object-cover mx-auto mb-4 ring-4 ring-primary-50" alt="">
        @else
            <div class="w-20 h-20 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                {{ strtoupper(substr($teacher->name, 0, 1)) }}
            </div>
        @endif
        <h1 class="text-xl font-bold text-gray-900 mb-2">{{ __('teacher.invitations.title') }}</h1>

        @if($usable)
            <p class="text-gray-600 mb-6">{{ __('teacher.invitations.invited_you', ['teacher' => trim($teacher->name.' '.$teacher->surname)]) }}</p>
            <form method="POST" action="{{ route('teacher-invitations.accept.store', $invitation->token) }}">
                @csrf
                <button class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition">
                    {{ __('teacher.invitations.accept') }}
                </button>
            </form>
            <p class="text-xs text-gray-400 mt-4">{{ __('teacher.invitations.decline_hint') }}</p>
        @else
            <p class="text-gray-500 mb-4">{{ __('teacher.invitations.unusable') }}</p>
            <a href="{{ route('dashboard') }}" class="inline-block px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition">
                Harmoniva →
            </a>
        @endif
    </div>
</main>

<script>lucide.createIcons();</script>
</body>
</html>
