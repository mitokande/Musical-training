<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('teacher.messaging.title') }} - {{ config('app.name', 'Harmoniva') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script>
        tailwind.config = { theme: { extend: {
            fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
            colors: { primary: { 50:'#faf5ff',100:'#f3e8ff',200:'#e9d5ff',600:'#9333ea',700:'#7c3aed' } }
        } } }
    </script>
    <style>.card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; }</style>
</head>
<body class="font-sans bg-gray-50 min-h-screen">
@include('partials.navbar')

<main class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
    <a href="{{ route('teacher-messages.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> {{ __('teacher.messaging.title') }}
    </a>

    <div class="card p-4 mb-4 flex items-center gap-3">
        @if($conversation->teacher->hasAvatar())
            <img src="{{ $conversation->teacher->avatar }}" class="w-10 h-10 rounded-full object-cover" alt="">
        @else
            <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold">
                {{ strtoupper(substr($conversation->teacher->name, 0, 1)) }}
            </div>
        @endif
        <div>
            <p class="font-bold text-gray-900">{{ $conversation->teacher->name }} {{ $conversation->teacher->surname }}</p>
            @if($conversation->teacher->teacherProfile?->isPubliclyVisible())
                <a href="{{ $conversation->teacher->teacherProfile->publicUrl() }}" class="text-xs font-semibold text-primary-600 hover:text-primary-800">
                    {{ __('teacher.my_teachers.view_public_profile') }} →
                </a>
            @endif
        </div>
    </div>

    @include('teacher-messages.partials.thread', ['messages' => $messages, 'attachmentRoute' => 'teacher-messages.attachment'])

    @include('teacher-messages.partials.composer', ['action' => route('teacher-messages.store', $conversation)])
</main>

@include('partials.footer')
<script>lucide.createIcons();</script>
</body>
</html>
