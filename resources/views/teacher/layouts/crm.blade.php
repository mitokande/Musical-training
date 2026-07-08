<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('teacher.dashboard.title')) - {{ config('app.name', 'Harmoniva') }}</title>
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
                        accent: { 400:'#fb923c',500:'#f97316',600:'#ea580c' }
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1); }
        .sidebar-item { transition: all 0.15s ease; border-radius: 8px; }
        .sidebar-item:hover { background: #f3f4f6; }
        .sidebar-item.active { background: #f3e8ff; color: #9333ea; font-weight: 600; }
        .premium-badge { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; }
    </style>

    @stack('head')
</head>
<body class="font-sans bg-gray-50 min-h-screen" x-data="{ sidebarOpen: window.innerWidth >= 1024 }" @resize.window="sidebarOpen = window.innerWidth >= 1024">

@php
    // Highlight the matching top-bar button for the current CRM page.
    $topNavActive = match(true) {
        request()->routeIs('teacher.messages.*')                                              => 'messages',
        request()->routeIs('teacher.profile.*')                                               => 'my-profile',
        request()->routeIs('teacher.students.*', 'teacher.relationships.*', 'teacher.invitations.*') => 'students',
        request()->routeIs('teacher.calendar.*', 'teacher.appointments.*')                    => 'calendar',
        default                                                                               => 'teacher',
    };
@endphp
@include('partials.navbar', ['active' => $topNavActive])

@php
    $tp = auth()->user()->teacherProfile;
    $caps = $capabilities ?? app(\App\Services\Teacher\TeacherCapabilityService::class)->capabilities(auth()->user());
@endphp

{{-- Mobile overlay --}}
<div x-show="sidebarOpen && window.innerWidth < 1024" @click="sidebarOpen = false" class="fixed inset-0 bg-black/30 z-40 lg:hidden" x-transition.opacity x-cloak></div>

{{-- Sidebar --}}
<aside x-show="sidebarOpen"
       x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
       x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
       class="fixed top-16 left-0 z-40 w-[270px] h-[calc(100vh-4rem)] bg-white border-r border-gray-200 flex flex-col lg:translate-x-0" x-cloak>

    {{-- Teacher identity --}}
    <div class="px-5 py-5 border-b border-gray-100 text-center shrink-0">
        @if(auth()->user()->hasAvatar())
            <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-16 h-16 rounded-full object-cover mx-auto ring-2 ring-primary-100">
        @else
            <div class="w-16 h-16 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center mx-auto text-xl font-bold">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
        @endif
        <p class="mt-2 font-semibold text-gray-900 leading-tight">{{ auth()->user()->name }} {{ auth()->user()->surname }}</p>
        <p class="text-xs text-gray-500">{{ __('teacher.nav.role_teacher') }}</p>
        <span class="inline-flex items-center gap-1 mt-1.5 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ ($tp?->tier === 'premium') ? 'premium-badge' : 'bg-gray-100 text-gray-600' }}">
            <i data-lucide="{{ ($tp?->tier === 'premium') ? 'crown' : 'user' }}" class="w-3 h-3"></i>
            {{ ($tp?->tier === 'premium') ? __('teacher.nav.tier_premium') : __('teacher.nav.tier_basic') }}
        </span>
        @if($tp)
            <a href="{{ route('teacher.profile.preview') }}" class="mt-3 flex items-center justify-center gap-2 w-full px-3 py-2 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition">
                <i data-lucide="eye" class="w-3.5 h-3.5"></i> {{ __('teacher.nav.view_as_student') }}
            </a>
        @endif
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        @php
            $lockedBadge = '<span class="ml-auto inline-flex items-center gap-1 text-[10px] font-semibold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded-full">'.__('teacher.nav.premium_only').'</span>';
            $soonBadge = '<span class="ml-auto text-[10px] font-medium text-gray-400">'.__('teacher.nav.coming_soon').'</span>';
        @endphp

        <a href="{{ route('teacher.dashboard') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-sm {{ request()->routeIs('teacher.dashboard') ? 'active' : 'text-gray-700' }}">
            <i data-lucide="layout-dashboard" class="w-[18px] h-[18px]"></i> {{ __('teacher.nav.dashboard') }}
        </a>

        {{-- Package 2 modules --}}
        @if($caps['manageStudents'])
            <a href="{{ route('teacher.students.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-sm {{ request()->routeIs('teacher.students.*', 'teacher.relationships.*', 'teacher.invitations.*') ? 'active' : 'text-gray-700' }}">
                <i data-lucide="users" class="w-[18px] h-[18px]"></i> {{ __('teacher.nav.students') }}
            </a>
        @else
            <div class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                <i data-lucide="users" class="w-[18px] h-[18px]"></i> {{ __('teacher.nav.students') }}
                {!! $lockedBadge !!}
            </div>
        @endif
        @if($caps['createClasses'])
            <a href="{{ route('teacher.classes.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-sm {{ request()->routeIs('teacher.classes.*') ? 'active' : 'text-gray-700' }}">
                <i data-lucide="school" class="w-[18px] h-[18px]"></i> {{ __('teacher.nav.classes') }}
            </a>
        @else
            <div class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                <i data-lucide="school" class="w-[18px] h-[18px]"></i> {{ __('teacher.nav.classes') }}
                {!! $lockedBadge !!}
            </div>
        @endif
        @if($caps['createAssignments'])
            <a href="{{ route('teacher.assignments.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-sm {{ request()->routeIs('teacher.assignments.*') ? 'active' : 'text-gray-700' }}">
                <i data-lucide="clipboard-list" class="w-[18px] h-[18px]"></i> {{ __('teacher.nav.assignments') }}
            </a>
        @else
            <div class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                <i data-lucide="clipboard-list" class="w-[18px] h-[18px]"></i> {{ __('teacher.nav.assignments') }}
                {!! $lockedBadge !!}
            </div>
        @endif
        @php $teacherUnreadMessages = app(\App\Services\Teacher\TeacherMessagingService::class)->unreadTotalFor(auth()->user()); @endphp
        <a href="{{ route('teacher.messages.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-sm {{ request()->routeIs('teacher.messages.*') ? 'active' : 'text-gray-700' }}">
            <i data-lucide="message-circle" class="w-[18px] h-[18px]"></i> {{ __('teacher.nav.messages') }}
            @if($teacherUnreadMessages > 0)
                <span class="ml-auto bg-primary-600 text-white text-[10px] font-bold rounded-full px-1.5 min-w-[18px] text-center leading-4">{{ $teacherUnreadMessages > 9 ? '9+' : $teacherUnreadMessages }}</span>
            @endif
        </a>
        @if($caps['manageAvailability'])
            <a href="{{ route('teacher.calendar.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-sm {{ request()->routeIs('teacher.calendar.*') ? 'active' : 'text-gray-700' }}">
                <i data-lucide="calendar" class="w-[18px] h-[18px]"></i> {{ __('teacher.nav.calendar') }}
            </a>
        @else
            <div class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                <i data-lucide="calendar" class="w-[18px] h-[18px]"></i> {{ __('teacher.nav.calendar') }}
                {!! $lockedBadge !!}
            </div>
        @endif

        <a href="{{ route('teacher.profile.edit') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-sm {{ request()->routeIs('teacher.profile.*') ? 'active' : 'text-gray-700' }}">
            <i data-lucide="user-pen" class="w-[18px] h-[18px]"></i> {{ __('teacher.nav.profile') }}
        </a>

        <a href="{{ route('teacher.content.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-sm {{ request()->routeIs('teacher.content.*', 'articles.*') ? 'active' : 'text-gray-700' }}">
            <i data-lucide="newspaper" class="w-[18px] h-[18px]"></i> {{ __('teacher.nav.content') }}
        </a>

        <div class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-sm text-gray-400 cursor-not-allowed">
            <i data-lucide="bar-chart-3" class="w-[18px] h-[18px]"></i> {{ __('teacher.nav.statistics') }}
            {!! $caps['viewTeacherStatistics'] ? $soonBadge : $lockedBadge !!}
        </div>

        <a href="{{ route('teacher.settings') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-sm {{ request()->routeIs('teacher.settings') ? 'active' : 'text-gray-700' }}">
            <i data-lucide="settings" class="w-[18px] h-[18px]"></i> {{ __('teacher.nav.settings') }}
        </a>
    </nav>
</aside>

{{-- Main content --}}
<div class="lg:ml-[270px] min-h-screen flex flex-col">
    <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6">
        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden mb-4 inline-flex items-center gap-2 p-2 text-gray-500 bg-white border border-gray-200 rounded-lg">
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>

        @if (session('status'))
            @php
                $statusMessages = [
                    'profile-submitted' => __('teacher.profile.submitted'),
                    'cover-updated' => __('teacher.profile.cover_updated'),
                    'teacher-account-created' => __('teacher.become.created'),
                ];
            @endphp
            <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                <p class="text-sm text-green-700">{{ $statusMessages[session('status')] ?? __('teacher.profile.saved') }}</p>
            </div>
        @endif

        @if (session('submit-error'))
            <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-sm font-semibold text-red-700 mb-1">{{ __('teacher.profile.submit_missing_intro') }}</p>
                <ul class="list-disc list-inside text-sm text-red-600">
                    @foreach (session('submit-error') as $field)
                        <li>{{ $field }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-xl">
                <ul class="list-disc list-inside text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
    document.addEventListener('alpine:initialized', () => lucide.createIcons());
</script>
@stack('scripts')
</body>
</html>
