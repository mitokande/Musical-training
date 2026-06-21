<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('app.messenger.title') }} – {{ config('app.name', 'Harmoniva') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: { extend: {
                fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                colors: {
                    primary: { 50:'#faf5ff',100:'#f3e8ff',200:'#e9d5ff',300:'#d8b4fe',400:'#c084fc',500:'#a855f7',600:'#9333ea',700:'#7c3aed',800:'#6b21a8',900:'#581c87' },
                    accent: { 400:'#fb923c',500:'#f97316',600:'#ea580c' }
                }
            } }
        }
    </script>
    <style>
        .card { background:white; border-radius:16px; border:1px solid #ede9fe; box-shadow:0 2px 8px 0 rgb(109 40 217/0.06),0 1px 2px -1px rgb(0 0 0/0.06); }
        .btn-primary { background:linear-gradient(135deg,#6d28d9 0%,#8b5cf6 100%); transition:all 0.2s; }
        .btn-primary:hover { background:linear-gradient(135deg,#5b21b6 0%,#7c3aed 100%); }
    </style>
    @livewireStyles
</head>
<body class="font-sans bg-gray-50 min-h-screen">

    @include('partials.navbar', ['active' => 'messages'])

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @livewire('messenger', ['to' => $to])
    </div>

    @livewireScripts
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', () => window.lucide && lucide.createIcons());
        });
        document.addEventListener('DOMContentLoaded', () => window.lucide && lucide.createIcons());
        document.addEventListener('livewire:navigated', () => window.lucide && lucide.createIcons());
    </script>
</body>
</html>
