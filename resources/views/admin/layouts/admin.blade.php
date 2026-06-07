<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin - {{ config('app.name', 'Harmoniva') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#faf5ff', 100: '#f3e8ff', 200: '#e9d5ff', 300: '#d8b4fe',
                            400: '#c084fc', 500: '#a855f7', 600: '#9333ea', 700: '#7c3aed',
                            800: '#6b21a8', 900: '#581c87',
                        },
                        accent: { 400: '#fb923c', 500: '#f97316', 600: '#ea580c' }
                    }
                }
            }
        }
    </script>

    <style>
        .hero-gradient { background: linear-gradient(135deg, #9333ea 0%, #c084fc 35%, #f97316 100%); }
        .card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1); }
        .btn-primary { background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%); }
        .btn-primary:hover { background: linear-gradient(135deg, #7c3aed 0%, #6b21a8 100%); }
        .sidebar-item { transition: all 0.15s ease; border-radius: 8px; }
        .sidebar-item:hover { background: #f3f4f6; }
        .sidebar-item.active { background: #f3e8ff; color: #9333ea; font-weight: 600; }
        .sidebar-sub-item { transition: all 0.15s ease; border-radius: 6px; }
        .sidebar-sub-item:hover { background: #f3f4f6; }
        .sidebar-sub-item.active { background: #f3e8ff; color: #9333ea; font-weight: 600; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    </style>

    @stack('head')
</head>
<body class="font-sans bg-gray-50 min-h-screen" x-data="{ sidebarOpen: window.innerWidth >= 1024 }" @resize.window="sidebarOpen = window.innerWidth >= 1024">

    @include('partials.navbar', ['active' => 'admin'])

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen && window.innerWidth < 1024" @click="sidebarOpen = false" class="fixed inset-0 bg-black/30 z-40 lg:hidden" x-transition.opacity x-cloak></div>

    {{-- Sidebar --}}
    <aside x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
           class="fixed top-16 left-0 z-40 w-[270px] h-[calc(100vh-4rem)] bg-white border-r border-gray-200 flex flex-col lg:translate-x-0" x-cloak>

        {{-- Logo --}}
        <div class="flex items-center gap-2.5 px-5 h-16 border-b border-gray-100 shrink-0">
            <div class="w-9 h-9 rounded-lg bg-gray-900 flex items-center justify-center shadow-sm shrink-0">
                <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5">
                    <defs>
                        <linearGradient id="adm-g" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#9333ea"/>
                            <stop offset="100%" stop-color="#fb923c"/>
                        </linearGradient>
                    </defs>
                    <rect x="2" y="3" width="5.5" height="22" rx="2" fill="url(#adm-g)"/>
                    <rect x="20.5" y="3" width="5.5" height="22" rx="2" fill="url(#adm-g)"/>
                    <path d="M7.5 14 Q11 9 14 14 Q17 19 20.5 14" stroke="url(#adm-g)" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                </svg>
            </div>
            <span class="font-bold text-xl tracking-tight leading-none">
                <span style="background: linear-gradient(135deg,#9333ea,#fb923c); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">H</span><span class="text-gray-900">armoniva</span> <span class="text-primary-600 text-sm font-semibold">Admin</span>
            </span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1" x-data="{
            openMenus: JSON.parse(localStorage.getItem('admin_menus') || '{}'),
            toggle(key) { this.openMenus[key] = !this.openMenus[key]; localStorage.setItem('admin_menus', JSON.stringify(this.openMenus)); },
            isOpen(key) { return this.openMenus[key] || false; }
        }">

            {{-- Dashboard --}}
            <a href="{{ route('admin.dashboard') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-sm {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-gray-700' }}">
                <i data-lucide="layout-dashboard" class="w-[18px] h-[18px]"></i> Dashboard
            </a>

            {{-- Users --}}
            <div>
                <button @click="toggle('users')" class="sidebar-item flex items-center justify-between w-full px-3 py-2.5 text-sm {{ request()->routeIs('admin.users.*') ? 'active' : 'text-gray-700' }}">
                    <span class="flex items-center gap-3"><i data-lucide="users" class="w-[18px] h-[18px]"></i> Members</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="isOpen('users') ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="isOpen('users')" x-collapse class="ml-6 mt-1 space-y-0.5">
                    <a href="{{ route('admin.users.index') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.users.index') && !request()->has('segment') ? 'active' : 'text-gray-600' }}">All Members</a>
                    <a href="{{ route('admin.users.segments') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.users.segments') ? 'active' : 'text-gray-600' }}">Segments</a>
                    <a href="{{ route('admin.users.create') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.users.create') ? 'active' : 'text-gray-600' }}">Add Member</a>
                </div>
            </div>

            {{-- Exercises --}}
            <div>
                <button @click="toggle('exercises')" class="sidebar-item flex items-center justify-between w-full px-3 py-2.5 text-sm {{ request()->routeIs('admin.exercises.*') || request()->routeIs('admin.single-note.*') || request()->routeIs('admin.interval-*') || request()->routeIs('admin.melodic-*') || request()->routeIs('admin.harmonic-*') || request()->routeIs('admin.exercise-categories.*') || request()->routeIs('admin.learning-path-exercises.*') ? 'active' : 'text-gray-700' }}">
                    <span class="flex items-center gap-3"><i data-lucide="headphones" class="w-[18px] h-[18px]"></i> Exercises</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="isOpen('exercises') ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="isOpen('exercises')" x-collapse class="ml-6 mt-1 space-y-0.5">
                    <a href="{{ route('admin.exercises.index') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.exercises.index') ? 'active' : 'text-gray-600' }}">Overview</a>

                    {{-- Intervals collapsible --}}
                    <div>
                        <button @click="toggle('intervals')" class="sidebar-sub-item flex items-center justify-between w-full px-3 py-2 text-sm {{ request()->is('admin/exercise-categories/melodic-intervals*') || request()->is('admin/exercise-categories/interval-direction*') || request()->is('admin/exercise-categories/harmonic-intervals*') || request()->is('admin/exercise-categories/interval-comparison*') || request()->is('admin/exercise-categories/interval-construction*') ? 'active' : 'text-gray-600' }}">
                            <span>Intervals</span>
                            <i data-lucide="chevron-down" class="w-3 h-3 transition-transform" :class="isOpen('intervals') ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="isOpen('intervals')" x-collapse class="ml-4 mt-0.5 space-y-0.5">
                            <a href="{{ route('admin.exercise-categories.show', 'melodic-intervals') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->is('admin/exercise-categories/melodic-intervals*') ? 'active' : 'text-gray-600' }}">Melodic Intervals</a>
                            <a href="{{ route('admin.exercise-categories.show', 'interval-direction') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->is('admin/exercise-categories/interval-direction*') ? 'active' : 'text-gray-600' }}">Interval Direction</a>
                            <a href="{{ route('admin.exercise-categories.show', 'harmonic-intervals') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->is('admin/exercise-categories/harmonic-intervals*') ? 'active' : 'text-gray-600' }}">Harmonic Intervals</a>
                            <a href="{{ route('admin.exercise-categories.show', 'interval-comparison') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->is('admin/exercise-categories/interval-comparison*') ? 'active' : 'text-gray-600' }}">Interval Comparison</a>
                            <a href="{{ route('admin.exercise-categories.show', 'interval-construction') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->is('admin/exercise-categories/interval-construction*') ? 'active' : 'text-gray-600' }}">Interval Construction</a>
                        </div>
                    </div>

                    <a href="{{ route('admin.exercise-categories.show', 'scales-modes') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->is('admin/exercise-categories/scales-modes*') ? 'active' : 'text-gray-600' }}">Scales & Modes</a>
                    <a href="{{ route('admin.exercise-categories.show', 'chords') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->is('admin/exercise-categories/chords*') ? 'active' : 'text-gray-600' }}">Chords</a>
                    <a href="{{ route('admin.exercise-categories.show', 'rhythm') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->is('admin/exercise-categories/rhythm*') ? 'active' : 'text-gray-600' }}">Rhythm</a>
                    <a href="{{ route('admin.exercise-categories.show', 'melodic-dictation') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->is('admin/exercise-categories/melodic-dictation*') ? 'active' : 'text-gray-600' }}">Melodic Dictation</a>
                    <a href="{{ route('admin.exercise-categories.show', 'single-note') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->is('admin/exercise-categories/single-note*') ? 'active' : 'text-gray-600' }}">Single Note</a>

                    <div class="border-t border-gray-100 my-1"></div>
                    <a href="{{ route('admin.learning-path-exercises.index') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.learning-path-exercises.*') ? 'active' : 'text-gray-600' }}">Learning Path</a>
                    <a href="{{ route('admin.exercise-categories.index') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.exercise-categories.index') || request()->routeIs('admin.exercise-categories.create') || request()->routeIs('admin.exercise-categories.edit') ? 'active' : 'text-gray-600' }}">Categories</a>
                </div>
            </div>

            {{-- Content Library --}}
            <div>
                <button @click="toggle('content')" class="sidebar-item flex items-center justify-between w-full px-3 py-2.5 text-sm {{ request()->routeIs('admin.content.*') || request()->routeIs('admin.content-categories.*') ? 'active' : 'text-gray-700' }}">
                    <span class="flex items-center gap-3"><i data-lucide="library" class="w-[18px] h-[18px]"></i> Content Library</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="isOpen('content') ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="isOpen('content')" x-collapse class="ml-6 mt-1 space-y-0.5">
                    <a href="{{ route('admin.content.index') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.content.index') ? 'active' : 'text-gray-600' }}">All Content</a>
                    <a href="{{ route('admin.content.create') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.content.create') ? 'active' : 'text-gray-600' }}">Add Content</a>
                    <a href="{{ route('admin.content-categories.index') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.content-categories.*') ? 'active' : 'text-gray-600' }}">Categories</a>
                </div>
            </div>

            {{-- Payments --}}
            <div>
                <button @click="toggle('payments')" class="sidebar-item flex items-center justify-between w-full px-3 py-2.5 text-sm {{ request()->routeIs('admin.plans.*') || request()->routeIs('admin.subscriptions.*') || request()->routeIs('admin.invoices.*') || request()->routeIs('admin.coupons.*') ? 'active' : 'text-gray-700' }}">
                    <span class="flex items-center gap-3"><i data-lucide="credit-card" class="w-[18px] h-[18px]"></i> Payments</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="isOpen('payments') ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="isOpen('payments')" x-collapse class="ml-6 mt-1 space-y-0.5">
                    <a href="{{ route('admin.plans.index') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.plans.*') ? 'active' : 'text-gray-600' }}">Plans</a>
                    <a href="{{ route('admin.subscriptions.index') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.subscriptions.*') ? 'active' : 'text-gray-600' }}">Subscriptions</a>
                    <a href="{{ route('admin.invoices.index') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.invoices.*') ? 'active' : 'text-gray-600' }}">Invoices</a>
                    <a href="{{ route('admin.coupons.index') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.coupons.*') ? 'active' : 'text-gray-600' }}">Coupons</a>
                </div>
            </div>

            {{-- AI Coach --}}
            <div>
                <button @click="toggle('aicoach')" class="sidebar-item flex items-center justify-between w-full px-3 py-2.5 text-sm {{ request()->routeIs('admin.ai-coach-admin.*') ? 'active' : 'text-gray-700' }}">
                    <span class="flex items-center gap-3"><i data-lucide="brain" class="w-[18px] h-[18px]"></i> AI Coach</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="isOpen('aicoach') ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="isOpen('aicoach')" x-collapse class="ml-6 mt-1 space-y-0.5">
                    <a href="{{ route('admin.ai-coach-admin.index') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.ai-coach-admin.index') ? 'active' : 'text-gray-600' }}">AI Profiles</a>
                    <a href="{{ route('admin.ai-coach-admin.settings') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.ai-coach-admin.settings') ? 'active' : 'text-gray-600' }}">AI Settings</a>
                </div>
            </div>

            {{-- CRM --}}
            <div>
                <button @click="toggle('crm')" class="sidebar-item flex items-center justify-between w-full px-3 py-2.5 text-sm {{ request()->routeIs('admin.tasks.*') || request()->routeIs('admin.appointments.*') ? 'active' : 'text-gray-700' }}">
                    <span class="flex items-center gap-3"><i data-lucide="contact" class="w-[18px] h-[18px]"></i> CRM</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="isOpen('crm') ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="isOpen('crm')" x-collapse class="ml-6 mt-1 space-y-0.5">
                    <a href="{{ route('admin.tasks.index') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.tasks.*') ? 'active' : 'text-gray-600' }}">Tasks</a>
                    <a href="{{ route('admin.appointments.create') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.appointments.*') ? 'active' : 'text-gray-600' }}">Appointments</a>
                </div>
            </div>

            {{-- Calendar --}}
            <a href="{{ route('admin.calendar.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-sm {{ request()->routeIs('admin.calendar.*') ? 'active' : 'text-gray-700' }}">
                <i data-lucide="calendar" class="w-[18px] h-[18px]"></i> Calendar
            </a>

            {{-- Messages --}}
            <a href="{{ route('admin.messages.index') }}" class="sidebar-item flex items-center gap-3 px-3 py-2.5 text-sm {{ request()->routeIs('admin.messages.*') ? 'active' : 'text-gray-700' }}">
                <i data-lucide="message-square" class="w-[18px] h-[18px]"></i> Messages
                @php $unreadCount = \App\Models\Message::unread()->count(); @endphp
                @if($unreadCount > 0)
                    <span class="ml-auto px-1.5 py-0.5 text-xs font-bold bg-red-100 text-red-700 rounded-full">{{ $unreadCount }}</span>
                @endif
            </a>

            {{-- Reports --}}
            <div>
                <button @click="toggle('reports')" class="sidebar-item flex items-center justify-between w-full px-3 py-2.5 text-sm {{ request()->routeIs('admin.reports.*') ? 'active' : 'text-gray-700' }}">
                    <span class="flex items-center gap-3"><i data-lucide="bar-chart-3" class="w-[18px] h-[18px]"></i> Reports</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="isOpen('reports') ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="isOpen('reports')" x-collapse class="ml-6 mt-1 space-y-0.5">
                    <a href="{{ route('admin.reports.members') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.reports.members') ? 'active' : 'text-gray-600' }}">Members</a>
                    <a href="{{ route('admin.reports.revenue') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.reports.revenue') ? 'active' : 'text-gray-600' }}">Revenue</a>
                    <a href="{{ route('admin.reports.subscriptions') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.reports.subscriptions') ? 'active' : 'text-gray-600' }}">Subscriptions</a>
                    <a href="{{ route('admin.reports.exercises') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.reports.exercises') ? 'active' : 'text-gray-600' }}">Exercises</a>
                    <a href="{{ route('admin.reports.ai-coach') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.reports.ai-coach') ? 'active' : 'text-gray-600' }}">AI Coach</a>
                    <a href="{{ route('admin.reports.content') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.reports.content') ? 'active' : 'text-gray-600' }}">Content</a>
                </div>
            </div>

            {{-- Settings --}}
            <div>
                <button @click="toggle('settings')" class="sidebar-item flex items-center justify-between w-full px-3 py-2.5 text-sm {{ request()->routeIs('admin.settings.*') || request()->routeIs('admin.piano-studio.*') || request()->routeIs('admin.games.*') ? 'active' : 'text-gray-700' }}">
                    <span class="flex items-center gap-3"><i data-lucide="settings" class="w-[18px] h-[18px]"></i> Settings</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="isOpen('settings') ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="isOpen('settings')" x-collapse class="ml-6 mt-1 space-y-0.5">
                    <a href="{{ route('admin.settings.index') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.settings.index') ? 'active' : 'text-gray-600' }}">General</a>
                    <a href="{{ route('admin.piano-studio.settings') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.piano-studio.*') ? 'active' : 'text-gray-600' }}">Piano Studio</a>
                    <a href="{{ route('admin.games.index') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.games.*') ? 'active' : 'text-gray-600' }}">Music Games</a>
                    <a href="{{ route('admin.settings.activity-log') }}" class="sidebar-sub-item block px-3 py-2 text-sm {{ request()->routeIs('admin.settings.activity-log') ? 'active' : 'text-gray-600' }}">Activity Log</a>
                </div>
            </div>
        </nav>

        {{-- Sidebar footer --}}
        <div class="border-t border-gray-100 px-4 py-3 shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-sm text-gray-500 hover:text-primary-600 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Site
            </a>
        </div>
    </aside>

    {{-- Main content area --}}
    <div class="lg:ml-[270px] min-h-screen flex flex-col">
        {{-- Top bar --}}
        <header class="bg-white border-b border-gray-200 sticky top-16 z-30 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                @hasSection('page-title')
                    <h1 class="text-lg font-semibold text-gray-900">@yield('page-title')</h1>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold text-sm">
                        {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                    </div>
                    <span class="hidden sm:block text-sm font-medium text-gray-700">{{ Auth::user()->name ?? 'Admin' }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                    </button>
                </form>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6">
            @if (session('success'))
                <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                        <i data-lucide="check" class="w-4 h-4 text-green-600"></i>
                    </div>
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                        <i data-lucide="x" class="w-4 h-4 text-red-600"></i>
                    </div>
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-xl">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                        </div>
                        <p class="text-sm font-medium text-red-700">Please fix the following errors:</p>
                    </div>
                    <ul class="list-disc list-inside text-sm text-red-600 ml-11">
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
        lucide.createIcons();
    </script>

    @stack('scripts')
</body>
</html>
