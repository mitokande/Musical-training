<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Harmoniva') }}</title>
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
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#faf5ff', 100: '#f3e8ff', 200: '#e9d5ff',
                            300: '#d8b4fe', 400: '#c084fc', 500: '#a855f7',
                            600: '#9333ea', 700: '#7c3aed', 800: '#6b21a8', 900: '#581c87',
                        },
                        accent: { 400: '#fb923c', 500: '#f97316', 600: '#ea580c' },
                        cream: '#FAF7F2',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #FAF7F2;
            overflow-x: hidden;
        }

        /* Subtle dot pattern on background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, rgba(147,51,234,0.06) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
            z-index: 0;
        }

        .gradient-text {
            background: linear-gradient(135deg, #9333ea 0%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-gradient {
            background: linear-gradient(135deg, #9333ea 0%, #c084fc 35%, #f97316 100%);
        }

        /* Navbar */
        .site-nav {
            background: rgba(250,247,242,0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(147,51,234,0.1);
        }

        /* Form card */
        .form-card {
            background: #ffffff;
            border: 1px solid rgba(147,51,234,0.12);
            box-shadow:
                0 4px 6px -1px rgba(0,0,0,0.04),
                0 10px 30px -5px rgba(147,51,234,0.08),
                0 20px 60px -10px rgba(0,0,0,0.06);
        }

        /* Input fields - white background */
        .input-field {
            width: 100%;
            padding: 10px 14px;
            background: #ffffff;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            color: #111827;
            font-size: 14px;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            transition: all 0.2s ease;
            outline: none;
        }
        .input-field::placeholder { color: #9ca3af; }
        .input-field:focus {
            border-color: #9333ea;
            box-shadow: 0 0 0 3px rgba(147,51,234,0.12);
        }

        /* Role selector cards */
        .role-card {
            border: 1.5px solid #e5e7eb;
            background: #f9fafb;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .role-card:hover {
            border-color: rgba(147,51,234,0.35);
            background: #faf5ff;
        }
        .role-card.active {
            border-color: #9333ea;
            background: #faf5ff;
            box-shadow: 0 0 0 1px rgba(147,51,234,0.2);
        }

        /* Primary button */
        .btn-primary {
            background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%);
            transition: all 0.2s ease;
            box-shadow: 0 4px 15px rgba(147,51,234,0.3);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #7c3aed 0%, #6b21a8 100%);
            box-shadow: 0 6px 20px rgba(147,51,234,0.4);
            transform: translateY(-1px);
        }

        /* Google button */
        .google-btn {
            background: #ffffff;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        .google-btn:hover {
            border-color: #d1d5db;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        label.input-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        label.input-label.mb-0 { margin-bottom: 0; }

        .error-text {
            font-size: 12px;
            color: #dc2626;
            margin-top: 4px;
        }

        /* Floating notes */
        @keyframes float-note {
            0%   { transform: translateY(0px) rotate(0deg);   opacity: 0.18; }
            33%  { transform: translateY(-18px) rotate(4deg);  opacity: 0.28; }
            66%  { transform: translateY(-8px) rotate(-3deg);  opacity: 0.20; }
            100% { transform: translateY(0px) rotate(0deg);   opacity: 0.18; }
        }
        .float-note { animation: float-note ease-in-out infinite; }

        /* Staff lines */
        .staff-line { background: rgba(147,51,234,0.07); height: 1px; }

        /* Subtle gradient blob */
        .bg-blob {
            position: fixed;
            border-radius: 9999px;
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
        }
    </style>
</head>
<body class="font-sans antialiased min-h-screen">

    <!-- Background blobs -->
    <div class="bg-blob w-96 h-96 opacity-30" style="background:#e9d5ff; top:-5%; left:-5%;"></div>
    <div class="bg-blob w-80 h-80 opacity-20" style="background:#fed7aa; bottom:-5%; right:-5%;"></div>

    <!-- Left-side floating notes (8 notes) -->
    <div class="fixed left-0 top-0 bottom-0 w-32 pointer-events-none overflow-hidden" style="z-index:1;">
        <span class="float-note absolute text-primary-400 text-4xl select-none"  style="top:8%;  left:18%; animation-duration:7s;  animation-delay:0s;">♩</span>
        <span class="float-note absolute text-accent-400 text-3xl select-none"   style="top:20%; left:40%; animation-duration:9s;  animation-delay:1.2s;">♪</span>
        <span class="float-note absolute text-primary-500 text-5xl select-none"  style="top:34%; left:10%; animation-duration:11s; animation-delay:0.5s;">𝄞</span>
        <span class="float-note absolute text-primary-300 text-3xl select-none"  style="top:50%; left:50%; animation-duration:8s;  animation-delay:2s;">♫</span>
        <span class="float-note absolute text-accent-400 text-4xl select-none"   style="top:63%; left:20%; animation-duration:10s; animation-delay:0.8s;">♬</span>
        <span class="float-note absolute text-primary-400 text-2xl select-none"  style="top:75%; left:45%; animation-duration:7.5s;animation-delay:1.5s;">♩</span>
        <span class="float-note absolute text-primary-300 text-4xl select-none"  style="top:85%; left:12%; animation-duration:9.5s;animation-delay:3s;">♪</span>
        <span class="float-note absolute text-accent-500 text-3xl select-none"   style="top:92%; left:38%; animation-duration:8.5s;animation-delay:0.3s;">𝄢</span>
    </div>

    <!-- Right-side floating notes (9 notes) -->
    <div class="fixed right-0 top-0 bottom-0 w-32 pointer-events-none overflow-hidden" style="z-index:1;">
        <span class="float-note absolute text-primary-400 text-3xl select-none"  style="top:6%;  right:15%; animation-duration:8s;  animation-delay:0.7s;">♬</span>
        <span class="float-note absolute text-accent-400 text-4xl select-none"   style="top:18%; right:42%; animation-duration:10s; animation-delay:0s;">♫</span>
        <span class="float-note absolute text-primary-500 text-2xl select-none"  style="top:30%; right:18%; animation-duration:7s;  animation-delay:1.8s;">♩</span>
        <span class="float-note absolute text-primary-300 text-5xl select-none"  style="top:44%; right:35%; animation-duration:12s; animation-delay:0.4s;">𝄞</span>
        <span class="float-note absolute text-accent-500 text-3xl select-none"   style="top:56%; right:12%; animation-duration:9s;  animation-delay:2.5s;">♪</span>
        <span class="float-note absolute text-primary-400 text-4xl select-none"  style="top:68%; right:44%; animation-duration:8.5s;animation-delay:1s;">♬</span>
        <span class="float-note absolute text-accent-400 text-3xl select-none"   style="top:78%; right:20%; animation-duration:11s; animation-delay:0.2s;">♩</span>
        <span class="float-note absolute text-primary-300 text-2xl select-none"  style="top:88%; right:38%; animation-duration:7.5s;animation-delay:1.3s;">♫</span>
        <span class="float-note absolute text-primary-500 text-3xl select-none"  style="top:95%; right:10%; animation-duration:9.5s;animation-delay:2s;">♪</span>
    </div>

    <!-- Horizontal staff lines (subtle) -->
    <div class="fixed inset-0 flex flex-col justify-center pointer-events-none" style="z-index:0; padding: 0 8rem; gap: 16px;">
        @foreach(range(1,5) as $i)
        <div class="staff-line"></div>
        @endforeach
    </div>

    <!-- Navbar -->
    <nav class="site-nav fixed top-0 left-0 right-0 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-lg bg-gray-900 flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow shrink-0">
                        <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5">
                            <defs>
                                <linearGradient id="gs-g" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#9333ea"/>
                                    <stop offset="100%" stop-color="#fb923c"/>
                                </linearGradient>
                            </defs>
                            <rect x="2" y="3" width="5.5" height="22" rx="2" fill="url(#gs-g)"/>
                            <rect x="20.5" y="3" width="5.5" height="22" rx="2" fill="url(#gs-g)"/>
                            <path d="M7.5 14 Q11 9 14 14 Q17 19 20.5 14" stroke="url(#gs-g)" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold tracking-tight leading-none">
                        <span style="background: linear-gradient(135deg,#9333ea,#fb923c); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">H</span><span class="text-gray-900">armoniva</span>
                    </span>
                </a>
                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}"
                       class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-black/5 rounded-lg transition-all">
                        Giriş Yap
                    </a>
                    <a href="{{ route('register') }}"
                       class="btn-primary px-4 py-2 text-sm font-semibold text-white rounded-lg">
                        Kayıt Ol
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="relative min-h-screen flex items-center justify-center pt-16 px-4 py-12" style="z-index:2;">
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </div>

    @include('partials.footer')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</body>
</html>
