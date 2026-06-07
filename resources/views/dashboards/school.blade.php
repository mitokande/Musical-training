<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('app.school.dashboard_title') }} - {{ config('app.name', 'Harmoniva') }}</title>
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
                        accent: { 400:'#fb923c',500:'#f97316',600:'#ea580c' }
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans bg-gray-50 min-h-screen">

@include('partials.navbar', ['active' => 'dashboard'])

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Hero --}}
    <div class="bg-gradient-to-r from-purple-600 via-purple-500 to-orange-400 rounded-2xl p-8 text-white mb-8">
        <h1 class="text-2xl font-bold mb-2">{{ __('app.dashboard.welcome', ['name' => $user->name]) }}</h1>
        <p class="text-purple-100">{{ __('app.school.dashboard_subtitle') }}</p>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <a href="{{ route('school.profile.edit') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition group">
            <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center mb-4 group-hover:bg-purple-200 transition">
                <i data-lucide="building-2" class="w-6 h-6 text-purple-600"></i>
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">{{ __('app.school.profile') }}</h3>
            <p class="text-sm text-gray-500">{{ __('app.school.profile_desc') }}</p>
        </a>

        <a href="{{ route('articles.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition group">
            <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center mb-4 group-hover:bg-orange-200 transition">
                <i data-lucide="file-text" class="w-6 h-6 text-orange-600"></i>
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">{{ __('app.school.my_content') }}</h3>
            <p class="text-sm text-gray-500">{{ __('app.school.my_content_desc') }}</p>
        </a>

        <a href="{{ route('profile.edit') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition group">
            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center mb-4 group-hover:bg-green-200 transition">
                <i data-lucide="settings" class="w-6 h-6 text-green-600"></i>
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">{{ __('app.school.account_settings') }}</h3>
            <p class="text-sm text-gray-500">{{ __('app.school.account_settings_desc') }}</p>
        </a>
    </div>

    {{-- Info --}}
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
            </div>
            <div>
                <h3 class="font-semibold text-blue-900 mb-1">{{ __('app.school.coming_soon') }}</h3>
                <p class="text-sm text-blue-700">{{ __('app.school.coming_soon_desc') }}</p>
            </div>
        </div>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
