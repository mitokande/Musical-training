<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('teacher.live_lessons.ended_title') }} - {{ config('app.name', 'Harmoniva') }}</title>
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
<body class="font-sans bg-gray-50 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center bg-white border border-gray-200 rounded-2xl shadow-sm p-8">
        <div class="w-12 h-12 mx-auto rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mb-4">
            <i data-lucide="video-off" class="w-6 h-6"></i>
        </div>
        <h1 class="text-lg font-bold text-gray-900">{{ __('teacher.live_lessons.ended_title') }}</h1>
        <p class="text-sm text-gray-500 mt-2">{{ $lesson->title }}</p>
        <p class="text-sm text-gray-600 mt-4">{{ __('teacher.live_lessons.ended_body') }}</p>
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 mt-6 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl">
            {{ __('teacher.live_lessons.go_home') }}
        </a>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
