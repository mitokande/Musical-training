<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Harmoniva') — Harmoniva</title>
    <meta name="description" content="@yield('description', 'AI-powered ear training for musicians, students, teachers, and music schools.')">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&family=instrument-serif:400,400i" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                        serif: ['Instrument Serif', 'Georgia', 'serif'],
                    },
                    colors: {
                        primary: {
                            50: '#faf5ff', 100: '#f3e8ff', 200: '#e9d5ff',
                            300: '#d8b4fe', 400: '#c084fc', 500: '#a855f7',
                            600: '#9333ea', 700: '#7c3aed', 800: '#6b21a8', 900: '#581c87',
                        },
                        accent: { 400: '#fb923c', 500: '#f97316', 600: '#ea580c' }
                    }
                }
            }
        }
    </script>

    <style>
        body { background: #FAF7F2; overflow-x: hidden; }
        .gradient-text { background: linear-gradient(135deg,#9333ea 0%,#f97316 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .gradient-text-orange { background: linear-gradient(135deg,#ea580c 0%,#f97316 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .reveal { opacity:0; transform:translateY(24px); transition:all 0.6s cubic-bezier(0.16,1,0.3,1); }
        .reveal.visible { opacity:1; transform:translateY(0); }
        .hero-badge { animation:pulse-soft 3s ease-in-out infinite; }
        @keyframes pulse-soft {
            0%,100% { box-shadow:0 0 0 0 rgba(147,51,234,0.3); }
            50% { box-shadow:0 0 0 12px rgba(147,51,234,0); }
        }
        .light-card { background:#fff; border:1px solid rgba(0,0,0,0.07); box-shadow:0 2px 12px rgba(0,0,0,0.05); }
        .feature-row { border-bottom:1px solid rgba(0,0,0,0.06); transition:background 0.2s ease; }
        .feature-row:hover { background:rgba(147,51,234,0.03); }
        .feature-row:last-child { border-bottom:none; }
    </style>

    @yield('head')
</head>

<body class="font-sans text-gray-700 antialiased" @yield('body-attrs')>

    {{-- Navbar --}}
    <nav class="fixed top-0 left-0 right-0 z-50 border-b border-black/10 backdrop-blur-xl bg-white/80">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-lg bg-gray-900 flex items-center justify-center shadow-lg shrink-0">
                        <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5">
                            <defs>
                                <linearGradient id="sl-g" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#9333ea"/>
                                    <stop offset="100%" stop-color="#fb923c"/>
                                </linearGradient>
                            </defs>
                            <rect x="2" y="3" width="5.5" height="22" rx="2" fill="url(#sl-g)"/>
                            <rect x="20.5" y="3" width="5.5" height="22" rx="2" fill="url(#sl-g)"/>
                            <path d="M7.5 14 Q11 9 14 14 Q17 19 20.5 14" stroke="url(#sl-g)" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold tracking-tight leading-none">
                        <span style="background:linear-gradient(135deg,#9333ea,#fb923c);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">H</span><span class="text-gray-900">armoniva</span>
                    </span>
                </a>
                <div class="flex items-center gap-3">
                    <a href="/" class="hidden sm:inline-flex px-4 py-2 text-sm text-gray-500 hover:text-gray-900 transition-colors">
                        ← Back to Home
                    </a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-5 py-2 text-sm font-semibold text-white rounded-lg hover:opacity-90 transition-all shadow-lg" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">Sign In</a>
                        <a href="{{ route('register') }}" class="px-5 py-2 text-sm font-semibold text-white rounded-lg hover:opacity-90 transition-all shadow-lg" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                            Get Started Free
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="h-16"></div>

    @yield('content')

    @include('partials.footer')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            const reveals = document.querySelectorAll('.reveal');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
            }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });
            reveals.forEach(el => observer.observe(el));
        });
    </script>

    @yield('scripts')
</body>
</html>
