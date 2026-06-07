<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.welcome.page_title') }}</title>
    <meta name="description" content="{{ __('app.welcome.page_description') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&family=instrument-serif:400,400i" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tone/15.3.5/Tone.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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
                        surface: {
                            DEFAULT: '#0C0A10',
                            raised: '#14111C',
                            overlay: '#1C1828',
                            soft: '#18142A',
                        },
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
        html { overflow-x: hidden; }
        body { background: #0C0A10; }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.025'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 9999;
        }

        .wave-bar { animation: wave 1.2s ease-in-out infinite; }
        .wave-bar:nth-child(1) { animation-delay: 0s; }
        .wave-bar:nth-child(2) { animation-delay: 0.1s; }
        .wave-bar:nth-child(3) { animation-delay: 0.2s; }
        .wave-bar:nth-child(4) { animation-delay: 0.3s; }
        .wave-bar:nth-child(5) { animation-delay: 0.15s; }
        .wave-bar:nth-child(6) { animation-delay: 0.25s; }
        .wave-bar:nth-child(7) { animation-delay: 0.05s; }
        @keyframes wave {
            0%, 100% { transform: scaleY(0.3); }
            50% { transform: scaleY(1); }
        }

        .piano-key-white { transition: all 0.15s ease; transform-origin: top center; }
        .piano-key-white:hover {
            background: linear-gradient(180deg, #e9d5ff 0%, #f3e8ff 100%);
            box-shadow: 0 0 20px rgba(147, 51, 234, 0.3);
            transform: scaleY(0.98);
        }
        .piano-key-white:active {
            background: linear-gradient(180deg, #c084fc 0%, #d8b4fe 100%);
            transform: scaleY(0.96);
        }
        .piano-key-black { transition: all 0.15s ease; transform-origin: top center; }
        .piano-key-black:hover {
            background: linear-gradient(180deg, #3b1f6e 0%, #1a0a30 100%);
            box-shadow: 0 0 15px rgba(147, 51, 234, 0.4);
            transform: scaleY(0.97);
        }
        .piano-key-black:active {
            background: linear-gradient(180deg, #7c3aed 0%, #3b1f6e 100%);
            transform: scaleY(0.95);
        }

        @keyframes float-note {
            0% { transform: translateY(0) rotate(0deg); opacity: 0; }
            10% { opacity: 0.5; }
            90% { opacity: 0.5; }
            100% { transform: translateY(-400px) rotate(20deg); opacity: 0; }
        }
        .floating-note { animation: float-note linear infinite; }

        @keyframes gentle-float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(3deg); }
        }
        .animate-gentle-float { animation: gentle-float 6s ease-in-out infinite; }

        .gradient-text {
            background: linear-gradient(135deg, #c084fc 0%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .feature-card {
            position: relative;
            background: #14111C;
            border: 1px solid rgba(255,255,255,0.06);
            transition: all 0.4s ease;
        }
        .feature-card::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: inherit;
            background: linear-gradient(135deg, rgba(147,51,234,0.3), transparent, rgba(249,115,22,0.2));
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: -1;
        }
        .feature-card:hover::before { opacity: 1; }
        .feature-card:hover {
            border-color: transparent;
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(147, 51, 234, 0.15);
        }

        .light-card {
            position: relative;
            background: #FFFFFF;
            border: 1px solid rgba(0,0,0,0.07);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 4px 12px rgba(0,0,0,0.03);
            transition: all 0.4s ease;
        }
        .light-card:hover {
            border-color: rgba(147,51,234,0.2);
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(147,51,234,0.1);
        }

        .glass-card {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(147, 51, 234, 0.4); }
            70% { box-shadow: 0 0 0 16px rgba(147, 51, 234, 0); }
            100% { box-shadow: 0 0 0 0 rgba(147, 51, 234, 0); }
        }
        .pulse-ring { animation: pulse-ring 2.5s infinite; }

        @keyframes grow-bar {
            0% { transform: scaleY(0); }
            100% { transform: scaleY(1); }
        }
        .chart-bar { transform-origin: bottom; }
        .chart-bar.visible { animation: grow-bar 0.8s ease-out forwards; }

        .dashboard-preview {
            transform: perspective(1200px) rotateX(2deg);
            transition: transform 0.6s ease;
        }
        .dashboard-preview:hover { transform: perspective(1200px) rotateX(0deg); }

        .section-glow {
            position: absolute;
            border-radius: 9999px;
            filter: blur(64px);
            pointer-events: none;
        }

        .piano-scroll::-webkit-scrollbar { display: none; }
        .piano-scroll { -ms-overflow-style: none; scrollbar-width: none; -webkit-overflow-scrolling: touch; }

        /* Hero description: mobil ~%15, desktop ~%20 harf aralığı, desktop 3 satır */
        .hero-desc {
            letter-spacing: 0.015em;
        }
        @media (min-width: 640px) {
            .hero-desc {
                letter-spacing: 0.04em;
                max-width: 34rem;
            }
        }

        /* ── Navbar FOUC fix: Tailwind CDN yüklenmeden önce layout'u kilitler ── */
        #wl-nav-center  { display: none; }
        #wl-nav-right   { display: none; }
        #wl-nav-burger  { display: flex; }
        @media (min-width: 1024px) {
            #wl-nav-center { display: flex; }
            #wl-nav-right  { display: flex; }
            #wl-nav-burger { display: none; }
        }
    </style>
</head>

<body class="font-sans text-gray-300 antialiased" x-data="{ mobileMenu: false }">

    {{-- ============ NAVBAR ============ --}}
    {{-- ===== TOP NAV ===== --}}
    <nav style="position:fixed; top:0; left:0; right:0; z-index:50; border-bottom:1px solid rgba(0,0,0,0.08); backdrop-filter:blur(20px); background:rgba(255,255,255,0.88);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center h-16">

                {{-- Logo (no negative margin — aligns with page content/piano) --}}
                <a href="/" class="flex items-center gap-2.5 group shrink-0">
                    <div class="w-11 h-11 rounded-xl bg-gray-900 flex items-center justify-center shadow-lg group-hover:shadow-gray-900/40 transition-shadow">
                        <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6">
                            <rect x="2" y="3" width="5.5" height="22" rx="2" fill="white"/>
                            <rect x="20.5" y="3" width="5.5" height="22" rx="2" fill="white"/>
                            <path d="M7.5 14 Q11 9 14 14 Q17 19 20.5 14" stroke="white" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold tracking-tight leading-none">
                        <span style="background:linear-gradient(135deg,#9333ea,#fb923c);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">H</span><span class="text-gray-900">armoniva</span>
                    </span>
                </a>

                {{-- Desktop centre nav: flex-1 so it never overlaps right section --}}
                <div id="wl-nav-center" class="hidden lg:flex items-center justify-center flex-1 gap-0 px-2">
                    <a href="#features"     class="px-2.5 py-2 text-[0.8rem] text-gray-500 hover:text-gray-900 transition-colors rounded-lg hover:bg-black/5 whitespace-nowrap">{{ __('app.welcome.nav_features') }}</a>
                    <a href="#exercises"    class="px-2.5 py-2 text-[0.8rem] text-gray-500 hover:text-gray-900 transition-colors rounded-lg hover:bg-black/5 whitespace-nowrap">{{ __('app.welcome.nav_exercises') }}</a>
                    <a href="#piano"        class="px-2.5 py-2 text-[0.8rem] text-gray-500 hover:text-gray-900 transition-colors rounded-lg hover:bg-black/5 whitespace-nowrap">{{ __('app.welcome.nav_piano') }}</a>
                    <a href="#ai-tutor"     class="px-2.5 py-2 text-[0.8rem] text-gray-500 hover:text-gray-900 transition-colors rounded-lg hover:bg-black/5 whitespace-nowrap">{{ __('app.welcome.nav_ai_tools') }}</a>
                    <a href="#games"        class="px-2.5 py-2 text-[0.8rem] text-gray-500 hover:text-gray-900 transition-colors rounded-lg hover:bg-black/5 whitespace-nowrap">{{ __('app.welcome.nav_games') }}</a>
                    <a href="#pricing"      class="px-2.5 py-2 text-[0.8rem] text-gray-500 hover:text-gray-900 transition-colors rounded-lg hover:bg-black/5 whitespace-nowrap">{{ __('app.welcome.nav_pricing') }}</a>
                    <a href="#how-it-works" class="px-2.5 py-2 text-[0.8rem] text-gray-500 hover:text-gray-900 transition-colors rounded-lg hover:bg-black/5 whitespace-nowrap">{{ __('app.welcome.nav_how_it_works') }}</a>
                </div>

                {{-- Desktop right: auth + search (shrink-0 so it never gets compressed) --}}
                <div id="wl-nav-right" class="hidden lg:flex items-center gap-2 ml-auto shrink-0">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-primary-600 to-primary-700 rounded-lg hover:from-primary-500 hover:to-primary-600 transition-all shadow-lg shadow-primary-600/25 whitespace-nowrap">
                            {{ __('app.welcome.nav_dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors font-medium whitespace-nowrap">
                            {{ __('app.welcome.nav_login') }}
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-primary-600 to-primary-700 rounded-lg hover:from-primary-500 hover:to-primary-600 transition-all shadow-lg shadow-primary-600/25 whitespace-nowrap">
                                {{ __('app.welcome.nav_start_free') }}
                            </a>
                        @endif
                    @endauth
                    <form action="/search" method="GET" class="relative flex items-center ml-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute left-3 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                        <input type="text" name="q" placeholder="{{ __('app.welcome.nav_search') }}"
                               class="pl-9 pr-3 py-1.5 w-36 text-sm bg-gray-100 border border-gray-200 rounded-lg text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent focus:w-48 transition-all duration-300">
                    </form>
                </div>

                {{-- Hamburger: pure JS, no Alpine/Lucide dependency --}}
                <button id="wl-nav-burger" onclick="wlMenuToggle()"
                        class="lg:hidden ml-auto flex items-center justify-center p-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
                        style="width:40px; height:40px; background:none; border:none; cursor:pointer;">
                    <svg id="wl-icon-menu" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                        <line x1="4" y1="6"  x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/>
                    </svg>
                    <svg id="wl-icon-close" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24" style="display:none;">
                        <line x1="5" y1="5" x2="19" y2="19"/><line x1="19" y1="5" x2="5" y2="19"/>
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    {{-- ===== MOBİL DRAWER — doğrudan DOM'da, display:none ile gizli ===== --}}
    <div id="wl-overlay" onclick="if(event.target===this)wlMenuClose()"
         style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:999999;background:rgba(0,0,0,0.6);">
        <div id="wl-panel"
             style="position:absolute;top:0;right:0;width:88vw;max-width:320px;height:100%;background:#111827;display:flex;flex-direction:column;overflow-y:auto;box-shadow:-8px 0 32px rgba(0,0,0,0.5);transform:translateX(100%);transition:transform 0.28s ease-out;">

            {{-- Header --}}
            <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid #374151;flex-shrink:0;">
                <span style="font-weight:700;color:white;font-size:0.95rem;">Menu</span>
                <button onclick="wlMenuClose()" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#9ca3af;background:none;border:none;cursor:pointer;border-radius:0.5rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><line x1="5" y1="5" x2="19" y2="19"/><line x1="19" y1="5" x2="5" y2="19"/></svg>
                </button>
            </div>

            {{-- Search Box --}}
            <div style="padding:0.875rem 1.25rem;border-bottom:1px solid #374151;flex-shrink:0;">
                <form action="/search" method="GET" style="display:flex;gap:0.5rem;">
                    <div style="position:relative;flex:1;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none;"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                        <input type="text" name="q" placeholder="{{ __('app.welcome.nav_search') }}"
                               style="width:100%;padding:9px 12px 9px 34px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);border-radius:10px;color:white;font-size:13px;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#9333ea';"
                               onblur="this.style.borderColor='rgba(255,255,255,0.15)';">
                    </div>
                    <button type="submit" style="flex-shrink:0;padding:9px 14px;background:#9333ea;color:white;border-radius:10px;font-size:13px;font-weight:600;border:none;cursor:pointer;">{{ __('app.nav.search') }}</button>
                </form>
            </div>

            {{-- Auth buttons --}}
            <div style="padding:1rem 1.25rem;border-bottom:1px solid #374151;display:flex;flex-direction:column;gap:0.625rem;flex-shrink:0;">
                @auth
                    <a href="{{ url('/dashboard') }}" onclick="wlMenuClose()"
                       style="display:flex;align-items:center;justify-content:center;gap:0.5rem;padding:0.75rem 1rem;font-size:0.875rem;font-weight:600;color:white;background:linear-gradient(135deg,#7c3aed,#9333ea);border-radius:0.75rem;text-decoration:none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        {{ __('app.welcome.nav_dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       style="display:flex;align-items:center;justify-content:center;gap:0.5rem;padding:0.75rem 1rem;font-size:0.875rem;font-weight:600;color:white;border:1px solid #4b5563;border-radius:0.75rem;text-decoration:none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        {{ __('app.welcome.nav_login') }}
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           style="display:flex;align-items:center;justify-content:center;gap:0.5rem;padding:0.75rem 1rem;font-size:0.875rem;font-weight:600;color:white;background:linear-gradient(135deg,#7c3aed,#9333ea);border-radius:0.75rem;text-decoration:none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                            {{ __('app.welcome.nav_start_free') }}
                        </a>
                    @endif
                @endauth
            </div>

            {{-- Nav links --}}
            <nav style="padding:1rem 1.25rem;flex:1;display:flex;flex-direction:column;gap:0.25rem;">
                @php
                    $wlLinks = [
                        ['href'=>'#features',    'label'=> __('app.welcome.nav_features'),    'svg'=>'<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>'],
                        ['href'=>'#exercises',   'label'=> __('app.welcome.nav_exercises'),   'svg'=>'<path d="M3 18v-6a9 9 0 0118 0v6"/><path d="M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3zM3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z"/>'],
                        ['href'=>'#piano',       'label'=> __('app.welcome.nav_piano'),       'svg'=>'<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M6 4v16M10 4v10M14 4v16M18 4v10"/>'],
                        ['href'=>'#ai-tutor',    'label'=> __('app.welcome.nav_ai_tools'),    'svg'=>'<path d="M12 2l2 7h7l-5.5 4 2 7L12 16l-5.5 4 2-7L3 9h7z"/>'],
                        ['href'=>'#games',       'label'=> __('app.welcome.nav_games'),       'svg'=>'<line x1="6" y1="12" x2="10" y2="12"/><line x1="8" y1="10" x2="8" y2="14"/><circle cx="15" cy="13" r="1"/><circle cx="18" cy="11" r="1"/><rect x="2" y="6" width="20" height="12" rx="5"/>'],
                        ['href'=>'#pricing',     'label'=> __('app.welcome.nav_pricing'),     'svg'=>'<path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>'],
                        ['href'=>'#how-it-works','label'=> __('app.welcome.nav_how_it_works'),'svg'=>'<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'],
                    ];
                @endphp
                @foreach($wlLinks as $lnk)
                    <a href="{{ $lnk['href'] }}" onclick="wlMenuClose()"
                       style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem 0.875rem;font-size:0.875rem;color:#d1d5db;border-radius:0.75rem;text-decoration:none;"
                       onmouseover="this.style.background='rgba(255,255,255,0.08)';this.style.color='white';"
                       onmouseout="this.style.background='';this.style.color='#d1d5db';">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="flex-shrink:0;">{!! $lnk['svg'] !!}</svg>
                        {{ $lnk['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    </div>

    <script>
        window.wlMenuToggle = function() {
            var ov    = document.getElementById('wl-overlay');
            var panel = document.getElementById('wl-panel');
            if (ov.getAttribute('data-open') === '1') { wlMenuClose(); return; }

            ov.setAttribute('data-open', '1');
            panel.style.transition = 'none';
            panel.style.transform  = 'translateX(100%)';
            ov.style.display = 'block';

            document.getElementById('wl-icon-menu').style.display  = 'none';
            document.getElementById('wl-icon-close').style.display = 'block';

            requestAnimationFrame(function() {
                panel.style.transition = 'transform 0.28s ease-out';
                panel.style.transform  = 'translateX(0)';
            });
        };

        window.wlMenuClose = function() {
            var ov    = document.getElementById('wl-overlay');
            var panel = document.getElementById('wl-panel');
            if (!ov || ov.getAttribute('data-open') !== '1') return;
            ov.setAttribute('data-open', '0');
            panel.style.transition = 'transform 0.28s ease-out';
            panel.style.transform  = 'translateX(100%)';
            document.getElementById('wl-icon-menu').style.display  = 'block';
            document.getElementById('wl-icon-close').style.display = 'none';
            setTimeout(function() { ov.style.display = 'none'; }, 300);
        };
    </script>


    {{-- ============ HERO ============ --}}
    <section class="relative min-h-screen flex flex-col overflow-hidden" style="background: #FFFFFF;">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-50/60 via-white to-orange-50/30 pointer-events-none"></div>
        <div class="absolute -top-60 -right-60 w-[600px] h-[600px] rounded-full bg-primary-100/50 blur-3xl pointer-events-none"></div>
        <div class="absolute -top-40 -left-60 w-[500px] h-[500px] rounded-full bg-orange-50/60 blur-3xl pointer-events-none"></div>

        <div class="h-16 shrink-0"></div>

        <div class="flex-1 flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8 py-10 text-center relative z-10">
            <div class="flex items-end justify-center gap-1 mb-8 h-8">
                <div class="wave-bar w-1 bg-gradient-to-t from-primary-600 to-primary-400 rounded-full h-8"></div>
                <div class="wave-bar w-1 bg-gradient-to-t from-primary-600 to-accent-400 rounded-full h-8"></div>
                <div class="wave-bar w-1 bg-gradient-to-t from-primary-500 to-primary-300 rounded-full h-8"></div>
                <div class="wave-bar w-1 bg-gradient-to-t from-accent-500 to-accent-400 rounded-full h-8"></div>
                <div class="wave-bar w-1 bg-gradient-to-t from-primary-600 to-primary-400 rounded-full h-8"></div>
                <div class="wave-bar w-1 bg-gradient-to-t from-primary-500 to-accent-400 rounded-full h-8"></div>
                <div class="wave-bar w-1 bg-gradient-to-t from-accent-600 to-primary-400 rounded-full h-8"></div>
            </div>

            <div class="inline-flex items-center gap-2 px-4 py-2 mb-8 rounded-full bg-gray-900 text-white text-sm font-semibold shadow-md">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                {{ __('app.welcome.hero_badge') }}
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-[1.08] tracking-tight mb-6">
                {{ __('app.welcome.hero_title_main') }}<br>
                <span class="font-serif italic font-normal gradient-text text-2xl sm:text-3xl lg:text-4xl">{{ __('app.welcome.hero_title_accent') }}</span>
            </h1>

            <p class="hero-desc text-sm sm:text-lg text-gray-500 max-w-2xl mx-auto mb-10 leading-relaxed">
                {{ __('app.welcome.hero_description') }}
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-10">
                @auth
                    <a href="{{ url('/dashboard') }}" class="pulse-ring px-8 py-3.5 text-base font-bold text-white bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl hover:from-primary-500 hover:to-primary-600 transition-all shadow-xl shadow-primary-600/30 hover:shadow-primary-500/40 hover:-translate-y-0.5">
                        {{ __('app.welcome.hero_cta_dashboard') }}
                    </a>
                @else
                    <a href="{{ route('register') }}" class="pulse-ring px-8 py-3.5 text-base font-bold text-white bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl hover:from-primary-500 hover:to-primary-600 transition-all shadow-xl shadow-primary-600/30 hover:shadow-primary-500/40 hover:-translate-y-0.5">
                        {{ __('app.welcome.hero_cta_register') }}
                    </a>
                    <a href="{{ route('login') }}" class="group px-6 py-3.5 text-base font-medium text-gray-500 hover:text-gray-900 transition-colors flex items-center gap-2">
                        {{ __('app.welcome.hero_cta_login') }}
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                @endauth
            </div>

        </div>

        {{-- Wide black piano at bottom --}}
        <div class="relative z-10 mx-0 sm:mx-[10%]" x-data="pianoHero()">
            <div class="bg-gray-950 rounded-t-2xl sm:rounded-t-3xl shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-800">
                    <div class="flex items-center gap-2">
                        <i data-lucide="piano" class="w-4 h-4 text-gray-500"></i>
                        <span class="text-xs text-gray-500 font-medium uppercase tracking-widest">{{ __('app.welcome.hero_piano_label') }}</span>
                    </div>
                    <span class="text-xs font-semibold text-primary-400" x-text="activeNote" x-show="activeNote" x-transition>&nbsp;</span>
                </div>
                <div class="piano-scroll overflow-x-auto" x-ref="pianoScroll">
                    <div class="relative" :style="containerStyle">
                        <div class="flex h-full" :style="containerStyle">
                            <template x-for="key in whites" :key="key.note">
                                <button
                                    @mousedown="playNote(key)"
                                    @touchstart.prevent="playNote(key)"
                                    class="piano-key-white h-full bg-gradient-to-b from-gray-50 to-white border-x border-b border-gray-300/40 rounded-b-sm shadow-sm"
                                    :style="keyFlexStyle"
                                ></button>
                            </template>
                        </div>
                        <template x-for="key in blacks" :key="key.note">
                            <button
                                @mousedown.stop="playNote(key)"
                                @touchstart.prevent.stop="playNote(key)"
                                class="piano-key-black absolute top-0 z-10 rounded-b-md bg-gradient-to-b from-gray-700 to-gray-950 border border-gray-600/40 shadow-lg"
                                :style="'left:' + key.offset + '; width:' + key.width + '; height: 100px;'"
                            ></button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </section>


    {{-- ============ FEATURES (dark) ============ --}}
    <section id="features" class="py-24 sm:py-32 relative overflow-hidden" style="background: #0C0A10;">
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="section-glow w-[500px] h-[500px] bg-primary-600/[0.04] top-20 -right-40"></div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-16 reveal">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-400 mb-3 block">{{ __('app.welcome.features_label') }}</span>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white mb-4">
                    {{ __('app.welcome.features_title_main') }}<br><span class="font-serif italic font-normal gradient-text">{{ __('app.welcome.features_title_accent') }}</span>
                </h2>
                <p class="text-gray-400 max-w-xl mx-auto">{{ __('app.welcome.features_description') }}</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @php
                    $features = [
                        ['icon' => 'bot', 'color' => 'cyan-400', 'bg' => 'cyan-500/15', 'title' => __('app.welcome.feature_ai_title'), 'desc' => __('app.welcome.feature_ai_desc')],
                        ['icon' => 'piano', 'color' => 'primary-300', 'bg' => 'primary-400/15', 'title' => __('app.welcome.feature_piano_title'), 'desc' => __('app.welcome.feature_piano_desc')],
                        ['icon' => 'gamepad-2', 'color' => 'green-400', 'bg' => 'green-500/15', 'title' => __('app.welcome.feature_games_title'), 'desc' => __('app.welcome.feature_games_desc')],
                        ['icon' => 'bar-chart-3', 'color' => 'blue-400', 'bg' => 'blue-500/15', 'title' => __('app.welcome.feature_analytics_title'), 'desc' => __('app.welcome.feature_analytics_desc')],
                        ['icon' => 'zap', 'color' => 'amber-400', 'bg' => 'amber-500/15', 'title' => __('app.welcome.feature_feedback_title'), 'desc' => __('app.welcome.feature_feedback_desc')],
                        ['icon' => 'message-circle', 'color' => 'rose-400', 'bg' => 'rose-500/15', 'title' => __('app.welcome.feature_assistant_title'), 'desc' => __('app.welcome.feature_assistant_desc')],
                    ];
                @endphp

                @foreach ($features as $i => $f)
                    <div class="feature-card rounded-2xl p-6 group reveal" style="transition-delay:{{ ($i * 0.05) + 0.05 }}s">
                        <div class="w-14 h-14 rounded-2xl bg-{{ $f['bg'] }} flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <i data-lucide="{{ $f['icon'] }}" class="w-6 h-6 text-{{ $f['color'] }}"></i>
                        </div>
                        <h3 class="text-white font-bold text-lg mb-2">{{ $f['title'] }}</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">{{ $f['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>


    {{-- ============ EXERCISE LIBRARY (cream) ============ --}}
    <section id="exercises" class="py-24 sm:py-32 relative overflow-hidden" style="background: #FAF7F2;">
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="section-glow w-[500px] h-[500px] bg-primary-200/25 bottom-0 -left-40"></div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-16 reveal">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-3 block">{{ __('app.welcome.exercises_label') }}</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-4">
                    {{ __('app.welcome.exercises_title_main') }}<br><span class="font-serif italic font-normal gradient-text">{{ __('app.welcome.exercises_title_accent') }}</span>
                </h2>
                <p class="text-gray-500 max-w-xl mx-auto">{{ __('app.welcome.exercises_description') }}</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @php
                    $exercises = [
                        ['icon' => 'arrow-up-down', 'color' => 'blue-600', 'bg' => 'blue-100', 'bar' => 'from-blue-500 to-blue-400', 'title' => __('app.welcome.ex_intervals_title'), 'desc' => __('app.welcome.ex_intervals_desc'), 'progress' => 55, 'level' => __('app.welcome.level_intermediate'), 'levelColor' => 'amber-700', 'levelBg' => 'amber-100'],
                        ['icon' => 'layers', 'color' => 'amber-600', 'bg' => 'amber-100', 'bar' => 'from-amber-500 to-amber-400', 'title' => __('app.welcome.ex_chords_title'), 'desc' => __('app.welcome.ex_chords_desc'), 'progress' => 45, 'level' => __('app.welcome.level_intermediate'), 'levelColor' => 'amber-700', 'levelBg' => 'amber-100'],
                        ['icon' => 'trending-up', 'color' => 'green-600', 'bg' => 'green-100', 'bar' => 'from-green-500 to-green-400', 'title' => __('app.welcome.ex_scales_title'), 'desc' => __('app.welcome.ex_scales_desc'), 'progress' => 50, 'level' => __('app.welcome.level_intermediate'), 'levelColor' => 'amber-700', 'levelBg' => 'amber-100'],
                        ['icon' => 'activity', 'color' => 'orange-600', 'bg' => 'orange-100', 'bar' => 'from-accent-500 to-accent-400', 'title' => __('app.welcome.ex_rhythm_title'), 'desc' => __('app.welcome.ex_rhythm_desc'), 'progress' => 70, 'level' => __('app.welcome.level_beginner'), 'levelColor' => 'green-600', 'levelBg' => 'green-100'],
                        ['icon' => 'pen-line', 'color' => 'rose-600', 'bg' => 'rose-100', 'bar' => 'from-rose-500 to-rose-400', 'title' => __('app.welcome.ex_dictation_title'), 'desc' => __('app.welcome.ex_dictation_desc'), 'progress' => 35, 'level' => __('app.welcome.level_advanced'), 'levelColor' => 'red-600', 'levelBg' => 'red-100'],
                        ['icon' => 'sliders-horizontal', 'color' => 'purple-600', 'bg' => 'purple-100', 'bar' => 'from-purple-500 to-purple-400', 'title' => __('app.welcome.ex_setup_title'), 'desc' => __('app.welcome.ex_setup_desc'), 'progress' => 60, 'level' => __('app.welcome.level_adaptive'), 'levelColor' => 'primary-700', 'levelBg' => 'primary-100'],
                    ];
                @endphp

                @foreach ($exercises as $i => $ex)
                    <div class="light-card rounded-2xl p-5 group reveal" style="transition-delay:{{ ($i * 0.05) + 0.05 }}s">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-12 h-12 rounded-xl bg-{{ $ex['bg'] }} flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i data-lucide="{{ $ex['icon'] }}" class="w-5 h-5 text-{{ $ex['color'] }}"></i>
                            </div>
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-{{ $ex['levelColor'] }} bg-{{ $ex['levelBg'] }} px-2 py-0.5 rounded-full">{{ $ex['level'] }}</span>
                        </div>
                        <h3 class="text-gray-900 font-bold mb-1">{{ $ex['title'] }}</h3>
                        <p class="text-gray-500 text-sm leading-relaxed mb-3">{{ $ex['desc'] }}</p>
                        <div class="h-1.5 rounded-full bg-gray-200 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r {{ $ex['bar'] }}" style="width: {{ $ex['progress'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-10 reveal">
                @auth
                    <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl hover:from-primary-500 hover:to-primary-600 transition-all shadow-lg shadow-primary-600/25 hover:-translate-y-0.5">
                        <i data-lucide="play" class="w-4 h-4"></i>
                        {{ __('app.welcome.exercises_cta_dashboard') }}
                    </a>
                @else
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl hover:from-primary-500 hover:to-primary-600 transition-all shadow-lg shadow-primary-600/25 hover:-translate-y-0.5">
                        <i data-lucide="play" class="w-4 h-4"></i>
                        {{ __('app.welcome.exercises_cta_register') }}
                    </a>
                @endauth
            </div>
        </div>
    </section>


    {{-- ============ VIRTUAL PIANO SHOWCASE (dark) ============ --}}
    <section id="piano" class="pt-24 sm:pt-32 pb-[80px] relative overflow-hidden" style="background: #0C0A10;">
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="section-glow w-[400px] h-[400px] bg-accent-500/[0.03] top-20 right-0"></div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative mb-[88px]">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-x-[148px] items-start">
                <div class="reveal">
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-accent-400 mb-3 block">{{ __('app.welcome.piano_label') }}</span>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-5">
                        {{ __('app.welcome.piano_title_main') }}<br><span class="font-serif italic font-normal gradient-text">{{ __('app.welcome.piano_title_accent') }}</span>
                    </h2>
                    <p class="text-gray-400 text-lg leading-relaxed">
                        {{ __('app.welcome.piano_description') }}
                    </p>
                </div>

                <div class="reveal flex flex-col gap-6" style="transition-delay:0.15s">
                    <div class="flex justify-end">
                        <a href="{{ route('piano.studio') }}" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-accent-500 hover:bg-accent-400 text-white text-sm font-semibold transition-all shadow-lg shadow-accent-500/25 hover:shadow-accent-400/30 hover:-translate-y-0.5 group">
                            Piano Studio
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5"></i>
                        </a>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-accent-500/15 flex items-center justify-center shrink-0">
                                <i data-lucide="volume-2" class="w-5 h-5 text-accent-400"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold text-sm">{{ __('app.welcome.piano_sound_title') }}</h4>
                                <p class="text-gray-500 text-sm">{{ __('app.welcome.piano_sound_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-primary-600/15 flex items-center justify-center shrink-0">
                                <i data-lucide="music-2" class="w-5 h-5 text-primary-400"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold text-sm">{{ __('app.welcome.piano_recognition_title') }}</h4>
                                <p class="text-gray-500 text-sm">{{ __('app.welcome.piano_recognition_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-green-500/15 flex items-center justify-center shrink-0">
                                <i data-lucide="eye" class="w-5 h-5 text-green-400"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-semibold text-sm">{{ __('app.welcome.piano_visual_title') }}</h4>
                                <p class="text-gray-500 text-sm">{{ __('app.welcome.piano_visual_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative z-10 mx-0 sm:mx-[10%]" x-data="pianoSection()">
            <div class="bg-gray-950 rounded-2xl sm:rounded-3xl shadow-2xl overflow-hidden border border-gray-700/50">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-800">
                    <div class="flex items-center gap-2">
                        <i data-lucide="piano" class="w-4 h-4 text-gray-500"></i>
                        <span class="text-xs text-gray-500 font-medium uppercase tracking-widest">{{ __('app.welcome.piano_studio_label') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-400"></span>
                        <span class="text-xs text-gray-500">{{ __('app.welcome.piano_connected') }}</span>
                        <span class="text-xs font-semibold text-primary-400 ml-1" x-text="activeNote" x-show="activeNote" x-transition>&nbsp;</span>
                    </div>
                </div>
                <div class="piano-scroll overflow-x-auto" x-ref="pianoScroll">
                    <div class="relative" :style="containerStyle">
                        <div class="flex h-full" :style="containerStyle">
                            <template x-for="key in whites" :key="key.note">
                                <button
                                    @mousedown="playNote(key)"
                                    @touchstart.prevent="playNote(key)"
                                    class="piano-key-white h-full bg-gradient-to-b from-gray-50 to-white border-x border-b border-gray-300/40 rounded-b-sm shadow-sm"
                                    :style="keyFlexStyle"
                                ></button>
                            </template>
                        </div>
                        <template x-for="key in blacks" :key="key.note">
                            <button
                                @mousedown.stop="playNote(key)"
                                @touchstart.prevent.stop="playNote(key)"
                                class="piano-key-black absolute top-0 z-10 rounded-b-md bg-gradient-to-b from-gray-700 to-gray-950 border border-gray-600/40 shadow-lg"
                                :style="'left:' + key.offset + '; width:' + key.width + '; height: 100px;'"
                            ></button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </section>


    {{-- ============ AI TUTOR PREVIEW (cream) ============ --}}
    <section id="ai-tutor" class="py-24 sm:py-32 relative overflow-hidden" style="background: #FAF7F2;">
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="section-glow w-[500px] h-[500px] bg-primary-200/20 top-40 left-1/4"></div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-16 reveal">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-3 block">{{ __('app.welcome.ai_label') }}</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-4">
                    {{ __('app.welcome.ai_title_main') }}<br><span class="font-serif italic font-normal gradient-text">{{ __('app.welcome.ai_title_accent') }}</span>
                </h2>
                <p class="text-gray-500 max-w-xl mx-auto">{{ __('app.welcome.ai_description') }}</p>
            </div>

            <div class="grid lg:grid-cols-2 gap-10 items-start">
                <div class="reveal">
                    <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-600 to-accent-500 flex items-center justify-center">
                                <i data-lucide="brain" class="w-4 h-4 text-white"></i>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900">{{ __('app.welcome.ai_chat_name') }}</div>
                                <div class="text-xs text-green-500 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> {{ __('app.welcome.ai_chat_online') }}
                                </div>
                            </div>
                        </div>
                        <div class="p-5 space-y-4 min-h-[280px]">
                            <div class="flex justify-end">
                                <div class="bg-primary-100 text-gray-800 px-4 py-2.5 rounded-2xl rounded-br-md max-w-[80%] text-sm">
                                    {{ __('app.welcome.ai_chat_q1') }}
                                </div>
                            </div>
                            <div class="flex justify-start">
                                <div class="bg-gray-50 text-gray-700 px-4 py-2.5 rounded-2xl rounded-bl-md max-w-[80%] text-sm leading-relaxed border border-gray-100">
                                    {{ __('app.welcome.ai_chat_a1') }}
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <div class="bg-primary-100 text-gray-800 px-4 py-2.5 rounded-2xl rounded-br-md max-w-[80%] text-sm">
                                    {{ __('app.welcome.ai_chat_q2') }}
                                </div>
                            </div>
                            <div class="flex justify-start">
                                <div class="bg-gray-50 text-gray-700 px-4 py-2.5 rounded-2xl rounded-bl-md max-w-[80%] text-sm leading-relaxed border border-gray-100">
                                    {{ __('app.welcome.ai_chat_a2') }}
                                </div>
                            </div>
                        </div>
                        <div class="px-5 pb-4">
                            <div class="flex items-center gap-2 bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                                <i data-lucide="message-circle" class="w-4 h-4 text-gray-400"></i>
                                <span class="text-sm text-gray-400">{{ __('app.welcome.ai_chat_placeholder') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 reveal" style="transition-delay:0.1s">
                    <div class="light-card rounded-2xl p-6 group">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-primary-100 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                <i data-lucide="lightbulb" class="w-6 h-6 text-primary-600"></i>
                            </div>
                            <div>
                                <h3 class="text-gray-900 font-bold mb-1">{{ __('app.welcome.ai_card_recs_title') }}</h3>
                                <p class="text-gray-500 text-sm leading-relaxed">{{ __('app.welcome.ai_card_recs_desc') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="light-card rounded-2xl p-6 group">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                <i data-lucide="route" class="w-6 h-6 text-orange-600"></i>
                            </div>
                            <div>
                                <h3 class="text-gray-900 font-bold mb-1">{{ __('app.welcome.ai_card_path_title') }}</h3>
                                <p class="text-gray-500 text-sm leading-relaxed">{{ __('app.welcome.ai_card_path_desc') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="light-card rounded-2xl p-6 group">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                <i data-lucide="message-square" class="w-6 h-6 text-green-600"></i>
                            </div>
                            <div>
                                <h3 class="text-gray-900 font-bold mb-1">{{ __('app.welcome.ai_card_ask_title') }}</h3>
                                <p class="text-gray-500 text-sm leading-relaxed">{{ __('app.welcome.ai_card_ask_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    {{-- ============ MUSIC GAMES (dark) ============ --}}
    <section id="games" class="py-24 sm:py-32 relative overflow-hidden" style="background: #0C0A10;">
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="section-glow w-[500px] h-[500px] bg-purple-600/[0.05] top-0 left-1/3"></div>
            <div class="section-glow w-[300px] h-[300px] bg-pink-600/[0.04] bottom-10 right-0"></div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="grid lg:grid-cols-2 gap-12 items-center">

                {{-- LEFT: Label + heading + game list (2 per row, no badges/desc) --}}
                <div class="reveal min-w-0">
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-purple-400 mb-3 block">{{ __('app.welcome.games_label') }}</span>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-5">
                        {{ __('app.welcome.games_title_main') }}<br>
                        <span class="font-serif italic font-normal" style="background: linear-gradient(135deg, #a855f7 0%, #ec4899 50%, #f97316 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">{{ __('app.welcome.games_title_accent') }}</span>
                    </h2>
                    <p class="text-gray-400 text-lg leading-relaxed mb-8">
                        {{ __('app.welcome.games_description') }}
                    </p>

                    @php
                        $landingGames = [
                            ['name' => __('app.welcome.game_note_rush_name'),      'icon' => 'zap',                'color' => 'text-yellow-400',  'bg' => 'bg-yellow-500/15'],
                            ['name' => __('app.welcome.game_melody_memory_name'),  'icon' => 'music',              'color' => 'text-purple-400',  'bg' => 'bg-purple-500/15'],
                            ['name' => __('app.welcome.game_interval_blitz_name'),'icon' => 'timer',              'color' => 'text-sky-400',     'bg' => 'bg-sky-500/15'],
                            ['name' => __('app.welcome.game_chord_clash_name'),    'icon' => 'layers',             'color' => 'text-rose-400',    'bg' => 'bg-rose-500/15'],
                            ['name' => __('app.welcome.game_note_fall_name'),      'icon' => 'arrow-down-to-line', 'color' => 'text-emerald-400', 'bg' => 'bg-emerald-500/15'],
                            ['name' => __('app.welcome.game_note_catcher_name'),   'icon' => 'move-horizontal',    'color' => 'text-violet-400',  'bg' => 'bg-violet-500/15'],
                        ];
                    @endphp

                    <div class="grid grid-cols-2 gap-3">
                        @foreach ($landingGames as $g)
                        <div class="flex items-center gap-3 group min-w-0">
                            <div class="w-10 h-10 rounded-xl {{ $g['bg'] }} flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                <i data-lucide="{{ $g['icon'] }}" class="w-5 h-5 {{ $g['color'] }}"></i>
                            </div>
                            <h4 class="text-white font-semibold text-sm leading-snug truncate">{{ $g['name'] }}</h4>
                        </div>
                        @endforeach
                    </div>

                </div>

                {{-- RIGHT: Note Fall game mockup + button below --}}
                <div class="reveal flex flex-col gap-4 min-w-0" style="transition-delay:0.15s">

                    {{-- Note Fall mockup --}}
                    <div class="relative overflow-hidden rounded-2xl">
                        <div class="absolute inset-0 rounded-2xl blur-2xl pointer-events-none" style="background: radial-gradient(ellipse at 60% 40%, rgba(52,211,153,0.08) 0%, rgba(168,85,247,0.06) 60%, transparent 100%);"></div>

                        <div class="relative rounded-2xl overflow-hidden shadow-2xl select-none" style="background:#0f0a1e; border:1px solid rgba(255,255,255,0.1);">

                            {{-- Header bar --}}
                            <div class="flex items-center justify-between px-3 py-3" style="background: rgba(255,255,255,0.03); border-bottom:1px solid rgba(255,255,255,0.08);">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg,#10b981,#059669);">
                                        <i data-lucide="arrow-down-to-line" class="w-4 h-4 text-white"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-white font-bold text-sm leading-none truncate">{{ __('app.welcome.game_note_fall_name') }}</div>
                                        <div class="text-gray-500 text-xs mt-0.5">{{ __('app.welcome.games_mockup_level') }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <div class="flex gap-0.5">
                                        <span class="text-red-400 text-sm">&hearts;</span>
                                        <span class="text-gray-600 text-sm">&hearts;</span>
                                        <span class="text-gray-600 text-sm">&hearts;</span>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-gray-500 text-[9px] uppercase tracking-wider">{{ __('app.welcome.games_mockup_score') }}</div>
                                        <div class="text-white font-black text-sm leading-none">0</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-gray-500 text-[9px] uppercase tracking-wider">{{ __('app.welcome.games_mockup_streak') }}</div>
                                        <div class="text-white font-black text-sm leading-none">0</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Game area: falling notes --}}
                            <div class="relative overflow-hidden" style="background:#080510; height:200px;">
                                @php
                                $fallingNotes = [
                                    ['left'=>'32%',  'top'=>'10%', 'label'=>'E'],
                                    ['left'=>'55%',  'top'=>'25%', 'label'=>'G'],
                                    ['left'=>'20%',  'top'=>'44%', 'label'=>'D'],
                                    ['left'=>'68%',  'top'=>'35%', 'label'=>'B'],
                                    ['left'=>'44%',  'top'=>'58%', 'label'=>'F'],
                                    ['left'=>'14%',  'top'=>'70%', 'label'=>'A'],
                                ];
                                @endphp
                                @foreach ($fallingNotes as $fn)
                                <div class="absolute flex flex-col items-center" style="left:{{ $fn['left'] }}; top:{{ $fn['top'] }};">
                                    <svg width="36" height="30" viewBox="0 0 40 34" fill="none">
                                        <line x1="4" y1="6"  x2="36" y2="6"  stroke="rgba(255,255,255,0.35)" stroke-width="1.2"/>
                                        <line x1="4" y1="12" x2="36" y2="12" stroke="rgba(255,255,255,0.35)" stroke-width="1.2"/>
                                        <line x1="4" y1="18" x2="36" y2="18" stroke="rgba(255,255,255,0.35)" stroke-width="1.2"/>
                                        <line x1="4" y1="24" x2="36" y2="24" stroke="rgba(255,255,255,0.35)" stroke-width="1.2"/>
                                        <line x1="27" y1="27" x2="27" y2="6" stroke="rgba(255,255,255,0.7)" stroke-width="1.5"/>
                                        <ellipse cx="20" cy="28" rx="7" ry="5" fill="rgba(255,255,255,0.85)" transform="rotate(-15 20 28)"/>
                                    </svg>
                                </div>
                                @endforeach

                                {{-- Active note near bottom --}}
                                <div class="absolute flex flex-col items-center" style="left:24%; top:74%;">
                                    <svg width="36" height="30" viewBox="0 0 40 34" fill="none">
                                        <line x1="4" y1="6"  x2="36" y2="6"  stroke="rgba(52,211,153,0.6)" stroke-width="1.2"/>
                                        <line x1="4" y1="12" x2="36" y2="12" stroke="rgba(52,211,153,0.6)" stroke-width="1.2"/>
                                        <line x1="4" y1="18" x2="36" y2="18" stroke="rgba(52,211,153,0.6)" stroke-width="1.2"/>
                                        <line x1="4" y1="24" x2="36" y2="24" stroke="rgba(52,211,153,0.6)" stroke-width="1.2"/>
                                        <line x1="27" y1="27" x2="27" y2="6" stroke="rgba(52,211,153,0.9)" stroke-width="1.5"/>
                                        <ellipse cx="20" cy="28" rx="7" ry="5" fill="rgba(52,211,153,0.95)" transform="rotate(-15 20 28)"/>
                                    </svg>
                                    <div class="w-full h-px mt-0.5" style="background: rgba(52,211,153,0.4); box-shadow:0 0 6px rgba(52,211,153,0.5);"></div>
                                </div>
                            </div>

                            {{-- Piano keyboard (8 beyaz tuş, doğru siyah tuş pozisyonları) --}}
                            <div class="relative" style="background:#1a1030; padding: 5px 5px 0; border-top:2px solid rgba(255,255,255,0.08);">
                                @php $pianoNotes = ['C','D','E','F','G','A','B','C']; @endphp
                                <div class="flex gap-[2px]" style="height:60px;">
                                    @foreach ($pianoNotes as $k => $pn)
                                    <div class="flex-1 rounded-b-md flex items-end justify-center pb-1 relative"
                                         style="background: linear-gradient(180deg,#f8f8f8 0%,#ffffff 100%); border:1px solid rgba(0,0,0,0.15); box-shadow: inset 0 -2px 4px rgba(0,0,0,0.1){{ $k === 2 ? ', 0 0 10px rgba(52,211,153,0.4)' : '' }};"
                                    >
                                        <span class="text-[8px] font-bold" style="color:rgba(0,0,0,0.4);">{{ $pn }}</span>
                                        @if ($k === 2)
                                        <div class="absolute inset-0 rounded-b-md" style="background: linear-gradient(180deg, rgba(52,211,153,0.15) 0%, rgba(52,211,153,0.05) 100%);"></div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                                {{-- Black keys: pct=12.5%, left = pos*12.5 + 12.5*0.65, width = 12.5*0.58 --}}
                                <div class="absolute top-[5px] left-[5px] right-[5px] pointer-events-none" style="height:38px;">
                                    @php
                                    $mockBlackKeys = [
                                        ['left'=>'8.1%'],   // C#
                                        ['left'=>'20.6%'],  // D#
                                        ['left'=>'45.6%'],  // F#
                                        ['left'=>'58.1%'],  // G#
                                        ['left'=>'70.6%'],  // A#
                                    ];
                                    @endphp
                                    @foreach ($mockBlackKeys as $bk)
                                    <div class="absolute rounded-b-sm" style="
                                        left: {{ $bk['left'] }};
                                        width: 7.3%;
                                        height: 100%;
                                        background: linear-gradient(180deg,#2a1a4a 0%,#0f0820 100%);
                                        border:1px solid rgba(255,255,255,0.05);
                                    "></div>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Explore All Games button --}}
                    <div class="flex justify-end">
                        <a href="{{ route('games.index') }}" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white rounded-xl hover:opacity-90 transition-all shadow-lg hover:-translate-y-0.5" style="background: linear-gradient(135deg, #a855f7 0%, #ec4899 60%, #f97316 100%);">
                            <i data-lucide="gamepad-2" class="w-4 h-4"></i>
                            {{ __('app.welcome.games_cta_explore') }}
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </section>


    {{-- ============ PRICING (cream) ============ --}}
    <section id="pricing" class="py-24 sm:py-32 relative overflow-hidden" style="background: #FAF7F2;">
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="section-glow w-[500px] h-[500px] bg-primary-200/20 top-20 right-0"></div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-16 reveal">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-3 block">{{ __('app.welcome.pricing_label') }}</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-4">
                    {{ __('app.welcome.pricing_title_main') }}<br><span class="font-serif italic font-normal gradient-text">{{ __('app.welcome.pricing_title_accent') }}</span>
                </h2>
                <p class="text-gray-500 max-w-xl mx-auto">{{ __('app.welcome.pricing_description') }}</p>
            </div>

            {{-- Equal-height grid --}}
            <div class="grid md:grid-cols-3 gap-6 items-stretch">

                {{-- FREE --}}
                <div class="light-card rounded-2xl reveal flex flex-col h-full" style="transition-delay:0.05s; padding:24px;">
                    <div class="mb-5">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gray-100 text-gray-600 text-xs font-semibold uppercase tracking-wider mb-3">
                            <i data-lucide="gift" class="w-3.5 h-3.5"></i> {{ __('app.welcome.plan_free_badge') }}
                        </div>
                        <div class="flex items-end gap-1 mb-1">
                            <span class="text-4xl font-extrabold text-gray-900">{{ __('app.welcome.plan_free_price') }}</span>
                            <span class="text-gray-400 text-sm mb-1.5">{{ __('app.welcome.plan_free_period') }}</span>
                        </div>
                        <p class="text-gray-500 text-sm">{{ __('app.welcome.plan_free_tagline') }}</p>
                    </div>

                    <ul class="space-y-2.5 flex-1">
                        <li class="flex items-start gap-3 text-sm text-gray-600">
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-500 shrink-0 mt-0.5"></i>
                            <span><strong class="text-gray-800">{{ __('app.welcome.plan_free_feat_1_strong') }}</strong> {{ __('app.welcome.plan_free_feat_1_rest') }}</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-600">
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-500 shrink-0 mt-0.5"></i>
                            <span><strong class="text-gray-800">{{ __('app.welcome.plan_free_feat_2_strong') }}</strong> {{ __('app.welcome.plan_free_feat_2_rest') }}</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-600">
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-500 shrink-0 mt-0.5"></i>
                            <span>{{ __('app.welcome.plan_free_feat_3_pre') }} <strong class="text-gray-800">{{ __('app.welcome.plan_free_feat_3_strong') }}</strong> {{ __('app.welcome.plan_free_feat_3_rest') }}</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-600">
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-500 shrink-0 mt-0.5"></i>
                            <span>{{ __('app.welcome.plan_free_feat_4_pre') }} <strong class="text-gray-800">{{ __('app.welcome.plan_free_feat_4_strong') }}</strong></span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-600">
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-500 shrink-0 mt-0.5"></i>
                            <span>{{ __('app.welcome.plan_free_feat_5_pre') }} <strong class="text-gray-800">{{ __('app.welcome.plan_free_feat_5_strong') }}</strong></span>
                        </li>
                    </ul>

                    <div class="flex flex-col gap-2.5 mt-6">
                        @auth
                        <a href="{{ url('/dashboard') }}" class="block w-full text-center px-5 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">
                            {{ __('app.welcome.plan_free_cta_dashboard') }}
                        </a>
                        @else
                        <a href="{{ route('register') }}" class="block w-full text-center px-5 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">
                            {{ __('app.welcome.plan_free_cta_register') }}
                        </a>
                        @endauth
                        <a href="/pricing" class="block w-full text-center px-5 py-2.5 text-sm font-semibold text-gray-500 hover:text-gray-800 hover:bg-gray-50 rounded-xl transition-all border border-gray-200/60">
                            Learn More — Full Details <i data-lucide="arrow-right" class="w-3.5 h-3.5 inline ml-1"></i>
                        </a>
                    </div>
                </div>

                {{-- PREMIUM --}}
                <div class="relative rounded-2xl reveal flex flex-col h-full"
                     style="transition-delay:0.1s; background:#1a0a30; border:2px solid rgba(147,51,234,0.5); box-shadow:0 24px 64px -12px rgba(147,51,234,0.2); padding:24px; margin-top:-16px; margin-bottom:-16px;">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 z-10">
                        <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-bold text-white shadow-lg" style="background:linear-gradient(135deg,#9333ea,#f97316);">
                            <i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i> {{ __('app.welcome.plan_popular_badge') }}
                        </span>
                    </div>

                    <div class="h-6"></div>{{-- space for badge --}}

                    <div class="mb-5">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider mb-3" style="background:rgba(147,51,234,0.15);color:#c084fc;">
                            <i data-lucide="zap" class="w-3.5 h-3.5"></i> {{ __('app.welcome.plan_premium_badge') }}
                        </div>
                        <div class="flex items-end gap-1 mb-1">
                            <span class="text-4xl font-extrabold text-white">{{ __('app.welcome.plan_premium_price') }}</span>
                            <span class="text-gray-400 text-sm mb-1.5">{{ __('app.welcome.plan_premium_period') }}</span>
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold mb-2" style="background:rgba(249,115,22,0.15);color:#fb923c;">
                            <i data-lucide="tag" class="w-3 h-3"></i> {{ __('app.welcome.plan_premium_discount') }}
                        </div>
                        <p class="text-gray-400 text-sm">{{ __('app.welcome.plan_premium_tagline') }}</p>
                    </div>

                    <ul class="space-y-2.5 flex-1">
                        <li class="flex items-start gap-3 text-sm text-gray-300">
                            <i data-lucide="check-circle" class="w-4 h-4 text-primary-400 shrink-0 mt-0.5"></i>
                            <span><strong class="text-white">{{ __('app.welcome.plan_premium_feat_1_strong') }}</strong> {{ __('app.welcome.plan_premium_feat_1_rest') }}</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-300">
                            <i data-lucide="check-circle" class="w-4 h-4 text-primary-400 shrink-0 mt-0.5"></i>
                            <span><strong class="text-white">{{ __('app.welcome.plan_premium_feat_2_strong') }}</strong> {{ __('app.welcome.plan_premium_feat_2_rest') }}</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-300">
                            <i data-lucide="check-circle" class="w-4 h-4 text-primary-400 shrink-0 mt-0.5"></i>
                            <span><strong class="text-white">{{ __('app.welcome.plan_premium_feat_3_strong') }}</strong> {{ __('app.welcome.plan_premium_feat_3_rest') }}</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-300">
                            <i data-lucide="check-circle" class="w-4 h-4 text-primary-400 shrink-0 mt-0.5"></i>
                            <span><strong class="text-white">{{ __('app.welcome.plan_premium_feat_4_strong') }}</strong> {{ __('app.welcome.plan_premium_feat_4_rest') }}</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-300">
                            <i data-lucide="check-circle" class="w-4 h-4 text-primary-400 shrink-0 mt-0.5"></i>
                            <span>{{ __('app.welcome.plan_premium_feat_5_pre') }} <strong class="text-white">{{ __('app.welcome.plan_premium_feat_5_strong') }}</strong></span>
                        </li>
                    </ul>

                    <div class="flex flex-col gap-2.5 mt-6">
                        @auth
                        <a href="{{ url('/dashboard') }}" class="block w-full text-center px-5 py-2.5 text-sm font-bold text-white rounded-xl transition-all hover:-translate-y-0.5 hover:opacity-90 shadow-xl" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                            {{ __('app.welcome.plan_premium_cta_dashboard') }}
                        </a>
                        @else
                        <a href="{{ route('register') }}" class="block w-full text-center px-5 py-2.5 text-sm font-bold text-white rounded-xl transition-all hover:-translate-y-0.5 hover:opacity-90 shadow-xl" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                            {{ __('app.welcome.plan_premium_cta_register') }}
                        </a>
                        @endauth
                        <a href="{{ route('pricing.teachers') }}" class="block w-full text-center px-5 py-2.5 text-sm font-semibold rounded-xl transition-all border" style="color:#c084fc;background:rgba(147,51,234,0.08);border-color:rgba(147,51,234,0.25);">
                            Teachers &amp; Schools <i data-lucide="arrow-right" class="w-3.5 h-3.5 inline ml-1"></i>
                        </a>
                    </div>
                </div>

                {{-- TEACHERS & SCHOOLS — no icon strip --}}
                <div class="light-card rounded-2xl reveal flex flex-col h-full" style="transition-delay:0.15s; padding:24px;">
                    <div class="mb-5">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider mb-3" style="background:rgba(249,115,22,0.1);color:#ea580c;">
                            <i data-lucide="graduation-cap" class="w-3.5 h-3.5"></i> {{ __('app.welcome.plan_teachers_badge') }}
                        </div>
                        <div class="flex items-end gap-1 mb-1">
                            <span class="text-4xl font-extrabold text-gray-900">{{ __('app.welcome.plan_teachers_price') }}</span>
                            <span class="text-gray-400 text-sm mb-1.5">{{ __('app.welcome.plan_teachers_period') }}</span>
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold mb-2" style="background:rgba(234,88,12,0.1);color:#ea580c;">
                            <i data-lucide="tag" class="w-3 h-3"></i> {{ __('app.welcome.plan_teachers_discount') }}
                        </div>
                        <p class="text-gray-500 text-sm">{{ __('app.welcome.plan_teachers_tagline') }}</p>
                    </div>

                    <ul class="space-y-2.5 flex-1">
                        <li class="flex items-start gap-3 text-sm text-gray-600">
                            <i data-lucide="check-circle" class="w-4 h-4 text-accent-500 shrink-0 mt-0.5"></i>
                            <span>{{ __('app.welcome.plan_teachers_feat_1_pre') }} <strong class="text-gray-800">{{ __('app.welcome.plan_teachers_feat_1_strong') }}</strong> {{ __('app.welcome.plan_teachers_feat_1_rest') }}</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-600">
                            <i data-lucide="check-circle" class="w-4 h-4 text-accent-500 shrink-0 mt-0.5"></i>
                            <span><strong class="text-gray-800">{{ __('app.welcome.plan_teachers_feat_2_strong') }}</strong> {{ __('app.welcome.plan_teachers_feat_2_rest') }}</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-600">
                            <i data-lucide="check-circle" class="w-4 h-4 text-accent-500 shrink-0 mt-0.5"></i>
                            <span><strong class="text-gray-800">{{ __('app.welcome.plan_teachers_feat_3_strong') }}</strong> {{ __('app.welcome.plan_teachers_feat_3_rest') }}</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-600">
                            <i data-lucide="check-circle" class="w-4 h-4 text-accent-500 shrink-0 mt-0.5"></i>
                            <span>{{ __('app.welcome.plan_teachers_feat_4_pre') }} <strong class="text-gray-800">{{ __('app.welcome.plan_teachers_feat_4_strong') }}</strong> {{ __('app.welcome.plan_teachers_feat_4_rest') }}</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-600">
                            <i data-lucide="check-circle" class="w-4 h-4 text-accent-500 shrink-0 mt-0.5"></i>
                            <span>{{ __('app.welcome.plan_teachers_feat_5_pre') }} <strong class="text-gray-800">{{ __('app.welcome.plan_teachers_feat_5_strong') }}</strong></span>
                        </li>
                    </ul>

                    <div class="flex flex-col gap-2.5 mt-6">
                        @auth
                        <a href="{{ url('/dashboard') }}" class="block w-full text-center px-5 py-2.5 text-sm font-bold text-white rounded-xl transition-all hover:-translate-y-0.5 hover:opacity-90 shadow-lg" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                            {{ __('app.welcome.plan_teachers_cta_start') }}
                        </a>
                        @else
                        <a href="{{ route('register') }}" class="block w-full text-center px-5 py-2.5 text-sm font-bold text-white rounded-xl transition-all hover:-translate-y-0.5 hover:opacity-90 shadow-lg" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                            {{ __('app.welcome.plan_teachers_cta_start') }}
                        </a>
                        @endauth
                        <a href="{{ route('pricing.teachers') }}" class="block w-full text-center px-5 py-2.5 text-sm font-semibold text-accent-600 bg-orange-50 hover:bg-orange-100 rounded-xl transition-all border border-orange-200/60">
                            {{ __('app.welcome.plan_teachers_cta_learn') }} <i data-lucide="arrow-right" class="w-3.5 h-3.5 inline ml-1"></i>
                        </a>
                    </div>
                </div>

            </div>

            <p class="text-center text-gray-400 text-sm mt-8 reveal">
                {{ __('app.welcome.pricing_footnote') }}
            </p>
        </div>
    </section>


    {{-- ============ STATISTICS PREVIEW (dark) ============ --}}
    <section id="statistics" class="py-24 sm:py-32 relative overflow-hidden" style="background: #0C0A10;">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-16 reveal">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-accent-400 mb-3 block">{{ __('app.welcome.stats_label') }}</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-4">
                    {{ __('app.welcome.stats_title_main') }}<br><span class="font-serif italic font-normal gradient-text">{{ __('app.welcome.stats_title_accent') }}</span>
                </h2>
                <p class="text-gray-400 max-w-xl mx-auto">{{ __('app.welcome.stats_description') }}</p>
            </div>

            <div class="dashboard-preview reveal" style="transition-delay:0.1s">
                <div class="glass-card rounded-2xl p-6 sm:p-8 shadow-2xl">
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-0 sm:mb-8">
                        @php
                            $stats = [
                                ['icon' => 'play-circle', 'color' => 'primary-400', 'bg' => 'primary-600/15', 'value' => '2,847', 'label' => __('app.welcome.stat_sessions')],
                                ['icon' => 'clock', 'color' => 'accent-400', 'bg' => 'accent-500/15', 'value' => '1,420h', 'label' => __('app.welcome.stat_hours')],
                                ['icon' => 'flame', 'color' => 'amber-400', 'bg' => 'amber-500/15', 'value' => '14', 'label' => __('app.welcome.stat_streak')],
                                ['icon' => 'award', 'color' => 'green-400', 'bg' => 'green-500/15', 'value' => '28', 'label' => __('app.welcome.stat_achievements')],
                            ];
                        @endphp
                        @foreach ($stats as $s)
                            <div class="bg-white/[0.03] rounded-xl p-4 border border-white/5">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-8 h-8 rounded-lg bg-{{ $s['bg'] }} flex items-center justify-center">
                                        <i data-lucide="{{ $s['icon'] }}" class="w-4 h-4 text-{{ $s['color'] }}"></i>
                                    </div>
                                </div>
                                <div class="text-xl font-bold text-white">{{ $s['value'] }}</div>
                                <div class="text-xs text-gray-500">{{ $s['label'] }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="hidden sm:grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 bg-white/[0.03] rounded-xl p-5 border border-white/5" x-data="{ visible: false }" x-intersect.once="visible = true">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-sm font-semibold text-white">{{ __('app.welcome.stat_weekly_progress') }}</span>
                                <span class="text-xs text-gray-500">{{ __('app.welcome.stat_accuracy_subtitle') }}</span>
                            </div>
                            <div class="flex items-end justify-between gap-2 h-32">
                                @php $days = [['d' => __('app.welcome.day_mon'), 'h' => 65], ['d' => __('app.welcome.day_tue'), 'h' => 78], ['d' => __('app.welcome.day_wed'), 'h' => 72], ['d' => __('app.welcome.day_thu'), 'h' => 88], ['d' => __('app.welcome.day_fri'), 'h' => 82], ['d' => __('app.welcome.day_sat'), 'h' => 95], ['d' => __('app.welcome.day_sun'), 'h' => 90]]; @endphp
                                @foreach ($days as $di => $day)
                                    <div class="flex-1 flex flex-col items-center gap-1">
                                        <div class="w-full rounded-t-md bg-gradient-to-t from-primary-600 to-primary-400 chart-bar" :class="visible ? 'visible' : ''" style="height: {{ $day['h'] }}%; animation-delay: {{ $di * 0.1 }}s;"></div>
                                        <span class="text-[10px] text-gray-500">{{ $day['d'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-white/[0.03] rounded-xl p-5 border border-white/5">
                            <span class="text-sm font-semibold text-white mb-4 block">{{ __('app.welcome.stat_achievements_title') }}</span>
                            <div class="grid grid-cols-2 gap-3">
                                @php
                                    $badges = [
                                        ['icon' => 'star', 'label' => __('app.welcome.badge_perfect_score'), 'color' => 'amber-400', 'bg' => 'amber-500/15'],
                                        ['icon' => 'flame', 'label' => __('app.welcome.badge_7_day_streak'), 'color' => 'red-400', 'bg' => 'red-500/15'],
                                        ['icon' => 'zap', 'label' => __('app.welcome.badge_quick_learner'), 'color' => 'blue-400', 'bg' => 'blue-500/15'],
                                        ['icon' => 'trophy', 'label' => __('app.welcome.badge_interval_pro'), 'color' => 'green-400', 'bg' => 'green-500/15'],
                                    ];
                                @endphp
                                @foreach ($badges as $b)
                                    <div class="flex flex-col items-center gap-2 p-3 rounded-xl bg-white/[0.03] border border-white/5">
                                        <div class="w-10 h-10 rounded-full bg-{{ $b['bg'] }} flex items-center justify-center">
                                            <i data-lucide="{{ $b['icon'] }}" class="w-5 h-5 text-{{ $b['color'] }}"></i>
                                        </div>
                                        <span class="text-[10px] text-gray-400 text-center font-medium">{{ $b['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    {{-- ============ HOW IT WORKS (cream) ============ --}}
    <section id="how-it-works" class="py-24 sm:py-32 relative overflow-hidden" style="background: #FAF7F2;">
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-3 block">{{ __('app.welcome.how_label') }}</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-4">
                    {{ __('app.welcome.how_title_main') }}<br><span class="font-serif italic font-normal gradient-text">{{ __('app.welcome.how_title_accent') }}</span>
                </h2>
            </div>

            <div class="grid gap-12 md:gap-8 md:grid-cols-3 relative">
                <div class="hidden md:block absolute top-12 left-[calc(16.67%+20px)] right-[calc(16.67%+20px)] h-px opacity-20" style="background-image: repeating-linear-gradient(to right, #9333ea 0px, #9333ea 8px, transparent 8px, transparent 16px)"></div>

                @php
                    $steps = [
                        ['num' => '1', 'icon' => 'headphones', 'title' => __('app.welcome.how_step1_title'), 'desc' => __('app.welcome.how_step1_desc'), 'border' => 'primary-300'],
                        ['num' => '2', 'icon' => 'music', 'title' => __('app.welcome.how_step2_title'), 'desc' => __('app.welcome.how_step2_desc'), 'border' => 'primary-400'],
                        ['num' => '3', 'icon' => 'trending-up', 'title' => __('app.welcome.how_step3_title'), 'desc' => __('app.welcome.how_step3_desc'), 'border' => 'accent-400'],
                    ];
                @endphp

                @foreach ($steps as $i => $step)
                    <div class="text-center reveal" style="transition-delay:{{ ($i * 0.1) + 0.05 }}s">
                        <div class="w-16 h-16 mx-auto rounded-2xl bg-white border-2 border-{{ $step['border'] }}/50 flex flex-col items-center justify-center mb-5 relative z-10 shadow-md">
                            <span class="text-lg font-extrabold gradient-text leading-none">{{ $step['num'] }}</span>
                            <i data-lucide="{{ $step['icon'] }}" class="w-4 h-4 text-gray-400 mt-0.5"></i>
                        </div>
                        <h3 class="text-gray-900 font-bold text-lg mb-2">{{ $step['title'] }}</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>


    {{-- ============ STUDENTS & TEACHERS (dark) ============ --}}
    <section class="py-24 sm:py-32 relative overflow-hidden" style="background: #0C0A10;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-400 mb-3 block">{{ __('app.welcome.roles_label') }}</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-4">
                    {{ __('app.welcome.roles_title_main') }}<br><span class="font-serif italic font-normal gradient-text">{{ __('app.welcome.roles_title_accent') }}</span>
                </h2>
                <p class="text-gray-400 max-w-xl mx-auto">{{ __('app.welcome.roles_description') }}</p>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <div class="feature-card rounded-2xl p-8 group reveal">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-primary-600/15 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="graduation-cap" class="w-7 h-7 text-primary-400"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-xl">{{ __('app.welcome.role_student_title') }}</h3>
                            <p class="text-gray-500 text-sm">{{ __('app.welcome.role_student_subtitle') }}</p>
                        </div>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-sm text-gray-400"><i data-lucide="check-circle" class="w-4 h-4 text-green-400 shrink-0"></i>{{ __('app.welcome.role_student_item_1') }}</li>
                        <li class="flex items-center gap-3 text-sm text-gray-400"><i data-lucide="check-circle" class="w-4 h-4 text-green-400 shrink-0"></i>{{ __('app.welcome.role_student_item_2') }}</li>
                        <li class="flex items-center gap-3 text-sm text-gray-400"><i data-lucide="check-circle" class="w-4 h-4 text-green-400 shrink-0"></i>{{ __('app.welcome.role_student_item_3') }}</li>
                        <li class="flex items-center gap-3 text-sm text-gray-400"><i data-lucide="check-circle" class="w-4 h-4 text-green-400 shrink-0"></i>{{ __('app.welcome.role_student_item_4') }}</li>
                    </ul>
                </div>

                <div class="feature-card rounded-2xl p-8 group reveal" style="transition-delay:0.1s">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-accent-500/15 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="users" class="w-7 h-7 text-accent-400"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-xl">{{ __('app.welcome.role_teacher_title') }}</h3>
                            <p class="text-gray-500 text-sm">{{ __('app.welcome.role_teacher_subtitle') }}</p>
                        </div>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-sm text-gray-400"><i data-lucide="check-circle" class="w-4 h-4 text-green-400 shrink-0"></i>{{ __('app.welcome.role_teacher_item_1') }}</li>
                        <li class="flex items-center gap-3 text-sm text-gray-400"><i data-lucide="check-circle" class="w-4 h-4 text-green-400 shrink-0"></i>{{ __('app.welcome.role_teacher_item_2') }}</li>
                        <li class="flex items-center gap-3 text-sm text-gray-400"><i data-lucide="check-circle" class="w-4 h-4 text-green-400 shrink-0"></i>{{ __('app.welcome.role_teacher_item_3') }}</li>
                        <li class="flex items-center gap-3 text-sm text-gray-400"><i data-lucide="check-circle" class="w-4 h-4 text-green-400 shrink-0"></i>{{ __('app.welcome.role_teacher_item_4') }}</li>
                    </ul>
                </div>

                <div class="feature-card rounded-2xl p-8 group reveal" style="transition-delay:0.2s">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-violet-500/15 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="building-2" class="w-7 h-7 text-violet-400"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-xl">{{ __('app.welcome.role_school_title') }}</h3>
                            <p class="text-gray-500 text-sm">{{ __('app.welcome.role_school_subtitle') }}</p>
                        </div>
                    </div>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-sm text-gray-400"><i data-lucide="check-circle" class="w-4 h-4 text-green-400 shrink-0"></i>{{ __('app.welcome.role_school_item_1') }}</li>
                        <li class="flex items-center gap-3 text-sm text-gray-400"><i data-lucide="check-circle" class="w-4 h-4 text-green-400 shrink-0"></i>{{ __('app.welcome.role_school_item_2') }}</li>
                        <li class="flex items-center gap-3 text-sm text-gray-400"><i data-lucide="check-circle" class="w-4 h-4 text-green-400 shrink-0"></i>{{ __('app.welcome.role_school_item_3') }}</li>
                        <li class="flex items-center gap-3 text-sm text-gray-400"><i data-lucide="check-circle" class="w-4 h-4 text-green-400 shrink-0"></i>{{ __('app.welcome.role_school_item_4') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>


    {{-- ============ FINAL CTA (cream) ============ --}}
    <section class="py-24 sm:py-32 relative overflow-hidden" style="background: #FAF7F2;">
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[600px] h-[400px] bg-gradient-to-t from-primary-200/30 via-primary-100/10 to-transparent rounded-full blur-3xl"></div>
            <span class="floating-note absolute text-primary-400/25 text-4xl" style="left:10%;animation-duration:10s;top:70%">&#9835;</span>
            <span class="floating-note absolute text-accent-500/20 text-3xl" style="left:30%;animation-duration:12s;top:80%">&#9834;</span>
            <span class="floating-note absolute text-primary-400/20 text-5xl" style="right:15%;animation-duration:14s;top:60%">&#119070;</span>
            <span class="floating-note absolute text-accent-500/20 text-3xl" style="right:35%;animation-duration:9s;top:75%">&#9839;</span>
        </div>

        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 text-center reveal">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-primary-600 to-accent-500 flex items-center justify-center mb-8 shadow-xl shadow-primary-600/20 pulse-ring">
                <i data-lucide="headphones" class="w-8 h-8 text-white"></i>
            </div>

            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-5">
                {{ __('app.welcome.cta_title_main') }}<br><span class="font-serif italic font-normal gradient-text">{{ __('app.welcome.cta_title_accent') }}</span>
            </h2>
            <p class="text-gray-500 text-lg mb-10 max-w-lg mx-auto">{{ __('app.welcome.cta_description') }}</p>

            @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl hover:from-primary-500 hover:to-primary-600 transition-all shadow-xl shadow-primary-600/30 hover:shadow-primary-500/40 hover:-translate-y-0.5">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    {{ __('app.welcome.cta_dashboard') }}
                </a>
            @else
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-8">
                    <a href="{{ route('register') }}" class="pulse-ring inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl hover:from-primary-500 hover:to-primary-600 transition-all shadow-xl shadow-primary-600/30 hover:shadow-primary-500/40 hover:-translate-y-0.5">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                        {{ __('app.welcome.cta_register') }}
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-6 py-4 text-base font-medium text-gray-600 hover:text-gray-900 transition-colors">
                        <i data-lucide="log-in" class="w-5 h-5"></i>
                        {{ __('app.welcome.cta_signin') }}
                    </a>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-500">
                    <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ __('app.welcome.cta_perk_free') }}</span>
                    <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ __('app.welcome.cta_perk_nocard') }}</span>
                    <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>{{ __('app.welcome.cta_perk_ai') }}</span>
                </div>
            @endauth
        </div>
    </section>


    {{-- ============ FOOTER ============ --}}
    @include('partials.footer')


    {{-- ============ SCRIPTS ============ --}}
    <script>
        lucide.createIcons();

        const reveals = document.querySelectorAll('.reveal');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) entry.target.classList.add('visible');
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
        reveals.forEach(el => observer.observe(el));

        const chartBars = document.querySelectorAll('.chart-bar');
        const chartObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) entry.target.classList.add('visible');
            });
        }, { threshold: 0.2 });
        chartBars.forEach(el => chartObserver.observe(el));

        function pianoSection() {
            const whiteDefs = [
                {note:'C3'},{note:'D3'},{note:'E3'},{note:'F3'},{note:'G3'},{note:'A3'},{note:'B3'},
                {note:'C4'},{note:'D4'},{note:'E4'},{note:'F4'},{note:'G4'},{note:'A4'},{note:'B4'},
                {note:'C5'},{note:'D5'},{note:'E5'},{note:'F5'},{note:'G5'},{note:'A5'},{note:'B5'},
            ];
            const blackDefs = [
                {note:'C#3',pos:0},{note:'D#3',pos:1},{note:'F#3',pos:3},{note:'G#3',pos:4},{note:'A#3',pos:5},
                {note:'C#4',pos:7},{note:'D#4',pos:8},{note:'F#4',pos:10},{note:'G#4',pos:11},{note:'A#4',pos:12},
                {note:'C#5',pos:14},{note:'D#5',pos:15},{note:'F#5',pos:17},{note:'G#5',pos:18},{note:'A#5',pos:19},
            ];
            const n = whiteDefs.length;
            const pct = 100 / n;
            const blacks = blackDefs.map(b => ({
                note: b.note,
                offset: (b.pos * pct + pct * 0.65) + '%',
                width: (pct * 0.58) + '%'
            }));

            let sampler = null;
            let samplerReady = false;

            function initSampler() {
                if (sampler) return;
                sampler = new Tone.Sampler({
                    urls: {
                        A2:'A2.mp3', C3:'C3.mp3', 'D#3':'Ds3.mp3', 'F#3':'Fs3.mp3',
                        A3:'A3.mp3', C4:'C4.mp3', 'D#4':'Ds4.mp3', 'F#4':'Fs4.mp3',
                        A4:'A4.mp3', C5:'C5.mp3', 'D#5':'Ds5.mp3', 'F#5':'Fs5.mp3',
                    },
                    release: 1,
                    baseUrl: 'https://tonejs.github.io/audio/salamander/',
                    onload: () => { samplerReady = true; }
                }).toDestination();
            }

            return {
                whites: whiteDefs,
                blacks,
                activeNote: '',
                containerStyle: 'height: 160px; min-width: 672px;',
                keyFlexStyle: 'flex: 1 0 32px',
                init() {
                    this.$nextTick(() => {
                        const el = this.$refs.pianoScroll;
                        if (el) el.scrollLeft = (el.scrollWidth - el.clientWidth) / 2;
                    });
                },
                async playNote(key) {
                    this.activeNote = key.note;
                    try {
                        await Tone.start();
                        if (!sampler) initSampler();
                        const deadline = Date.now() + 6000;
                        while (!samplerReady && Date.now() < deadline) {
                            await new Promise(r => setTimeout(r, 80));
                        }
                        if (samplerReady) sampler.triggerAttackRelease(key.note, 1.5);
                    } catch(e) {}
                },
                stopNote() {}
            };
        }

        function pianoHero() {
            const mobile = window.innerWidth < 640;

            // 3 oktav desktop (C3–B5 = 21 beyaz tuş), 1 oktav mobil (C4–B4 = 7 beyaz tuş)
            const whiteDefs3 = [
                {note:'C3'},{note:'D3'},{note:'E3'},{note:'F3'},{note:'G3'},{note:'A3'},{note:'B3'},
                {note:'C4'},{note:'D4'},{note:'E4'},{note:'F4'},{note:'G4'},{note:'A4'},{note:'B4'},
                {note:'C5'},{note:'D5'},{note:'E5'},{note:'F5'},{note:'G5'},{note:'A5'},{note:'B5'},
            ];
            const blackDefs3 = [
                {note:'C#3',pos:0},{note:'D#3',pos:1},{note:'F#3',pos:3},{note:'G#3',pos:4},{note:'A#3',pos:5},
                {note:'C#4',pos:7},{note:'D#4',pos:8},{note:'F#4',pos:10},{note:'G#4',pos:11},{note:'A#4',pos:12},
                {note:'C#5',pos:14},{note:'D#5',pos:15},{note:'F#5',pos:17},{note:'G#5',pos:18},{note:'A#5',pos:19},
            ];
            const whiteDefs1 = [
                {note:'C4'},{note:'D4'},{note:'E4'},{note:'F4'},{note:'G4'},{note:'A4'},{note:'B4'},
            ];
            const blackDefs1 = [
                {note:'C#4',pos:0},{note:'D#4',pos:1},{note:'F#4',pos:3},{note:'G#4',pos:4},{note:'A#4',pos:5},
            ];

            const whiteDefs = mobile ? whiteDefs1 : whiteDefs3;
            const chosenBlackDefs = mobile ? blackDefs1 : blackDefs3;
            const n = whiteDefs.length;
            const pct = 100 / n;

            const blacks = chosenBlackDefs.map(b => ({
                note: b.note,
                offset: (b.pos * pct + pct * 0.65) + '%',
                width: (pct * 0.58) + '%'
            }));

            // Desktop: 21 tuş × 32px = 672px (aynı genişlik); mobil: container genişliğini doldur
            const minW = mobile ? '100%' : '672px';
            const keyFlex = mobile ? 'flex: 1 0 0' : 'flex: 1 0 32px';

            let sampler = null;
            let samplerReady = false;

            function initSampler() {
                if (sampler) return;
                sampler = new Tone.Sampler({
                    urls: {
                        A2:'A2.mp3', C3:'C3.mp3', 'D#3':'Ds3.mp3', 'F#3':'Fs3.mp3',
                        A3:'A3.mp3', C4:'C4.mp3', 'D#4':'Ds4.mp3', 'F#4':'Fs4.mp3',
                        A4:'A4.mp3', C5:'C5.mp3', 'D#5':'Ds5.mp3', 'F#5':'Fs5.mp3',
                    },
                    release: 1,
                    baseUrl: 'https://tonejs.github.io/audio/salamander/',
                    onload: () => { samplerReady = true; }
                }).toDestination();
            }

            return {
                whites: whiteDefs,
                blacks,
                activeNote: '',
                containerStyle: `height: 160px; min-width: ${minW};`,
                keyFlexStyle: keyFlex,
                init() {
                    this.$nextTick(() => {
                        if (!mobile) {
                            const el = this.$refs.pianoScroll;
                            if (el) el.scrollLeft = (el.scrollWidth - el.clientWidth) / 2;
                        }
                    });
                },
                async playNote(key) {
                    this.activeNote = key.note;
                    try {
                        await Tone.start();
                        if (!sampler) initSampler();
                        const deadline = Date.now() + 6000;
                        while (!samplerReady && Date.now() < deadline) {
                            await new Promise(r => setTimeout(r, 80));
                        }
                        if (samplerReady) sampler.triggerAttackRelease(key.note, 1.5);
                    } catch(e) {}
                },
                stopNote() {}
            };
        }
    </script>

</body>
</html>
