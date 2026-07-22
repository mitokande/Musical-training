<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AI-Powered Ear Training') — Harmoniva</title>
    <meta name="description" content="@yield('description', 'AI-powered ear training for musicians, students, teachers, and music schools.')">
    @hasSection('robots')
    <meta name="robots" content="@yield('robots')">
    @endif
    <link rel="canonical" href="@yield('canonical', request()->url())">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="Harmoniva">
    <meta property="og:title" content="@yield('title', 'AI-Powered Ear Training')">
    <meta property="og:description" content="@yield('description', 'AI-powered ear training for musicians, students, teachers, and music schools.')">
    <meta property="og:url" content="@yield('canonical', request()->url())">
    <meta property="og:image" content="@yield('og_image', asset('images/og-image.png'))">
    @hasSection('og_image')
    @else
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'AI-Powered Ear Training')">
    <meta name="twitter:description" content="@yield('description', 'AI-powered ear training for musicians, students, teachers, and music schools.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og-image.png'))">

    @php
        // Built inside @php: Blade would otherwise compile the literal
        // "@context" key as its @context directive and corrupt the JSON.
        $organizationJsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => url('/').'#organization',
            'name' => 'Harmoniva',
            'url' => url('/'),
            'logo' => asset('images/logo-full.png'),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @endphp
    <script type="application/ld+json">{!! $organizationJsonLd !!}</script>
    @yield('structured-data')

    @include('partials.google-analytics')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&family=instrument-serif:400,400i" rel="stylesheet" />

    @vite('resources/css/marketing.css')
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
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
                <a href="/" class="flex items-center group shrink-0">
                    <img src="{{ asset('images/logo-full.png') }}" alt="Harmoniva" width="1374" height="340" class="h-[43px] sm:h-[47px] w-auto">
                </a>
                <div class="flex items-center gap-3">
                    <a href="/" class="hidden sm:inline-flex px-4 py-2 text-sm text-gray-500 hover:text-gray-900 transition-colors">
                        ← {{ __('app.welcome.back_to_home') }}
                    </a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="hidden sm:inline-flex px-5 py-2 text-sm font-semibold text-white rounded-lg hover:opacity-90 transition-all shadow-lg" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                            {{ __('app.welcome.nav_dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">{{ __('app.auth.sign_in') }}</a>
                        <a href="{{ route('register') }}" class="hidden sm:inline-flex px-5 py-2 text-sm font-semibold text-white rounded-lg hover:opacity-90 transition-all shadow-lg" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                            {{ __('app.welcome.nav_start_free') }}
                        </a>
                    @endauth
                    {{-- Mobile hamburger (same drawer pattern as the landing page) --}}
                    <button id="sa-nav-burger" onclick="saMenuToggle()"
                            class="sm:hidden flex items-center justify-center p-2 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
                            style="width:40px;height:40px;background:none;border:none;cursor:pointer;" aria-label="Menu">
                        <svg id="sa-icon-menu" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                            <line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/>
                        </svg>
                        <svg id="sa-icon-close" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24" style="display:none;">
                            <line x1="5" y1="5" x2="19" y2="19"/><line x1="19" y1="5" x2="5" y2="19"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="h-16"></div>

    {{-- ===== Mobile drawer — same style as the landing-page menu ===== --}}
    <div id="sa-overlay" onclick="if(event.target===this)saMenuClose()"
         style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:999999;background:rgba(0,0,0,0.6);">
        <div id="sa-panel"
             style="position:absolute;top:0;right:0;width:88vw;max-width:320px;height:100%;background:#111827;display:flex;flex-direction:column;overflow-y:auto;box-shadow:-8px 0 32px rgba(0,0,0,0.5);transform:translateX(100%);transition:transform 0.28s ease-out;">

            {{-- Header --}}
            <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid #374151;flex-shrink:0;">
                <span style="font-weight:700;color:white;font-size:0.95rem;">Menu</span>
                <button onclick="saMenuClose()" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#9ca3af;background:none;border:none;cursor:pointer;border-radius:0.5rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><line x1="5" y1="5" x2="19" y2="19"/><line x1="19" y1="5" x2="5" y2="19"/></svg>
                </button>
            </div>

            {{-- Auth buttons --}}
            <div style="padding:1rem 1.25rem;border-bottom:1px solid #374151;display:flex;flex-direction:column;gap:0.625rem;flex-shrink:0;">
                @auth
                    <a href="{{ url('/dashboard') }}" onclick="saMenuClose()"
                       style="display:flex;align-items:center;justify-content:center;gap:0.5rem;padding:0.75rem 1rem;font-size:0.875rem;font-weight:600;color:white;background:linear-gradient(135deg,#7c3aed,#9333ea);border-radius:0.75rem;text-decoration:none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        {{ __('app.welcome.nav_dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       style="display:flex;align-items:center;justify-content:center;gap:0.5rem;padding:0.75rem 1rem;font-size:0.875rem;font-weight:600;color:white;border:1px solid #4b5563;border-radius:0.75rem;text-decoration:none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        {{ __('app.auth.sign_in') }}
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
                    $saLinks = [
                        ['href'=>'/',              'label'=> __('app.welcome.back_to_home'),        'svg'=>'<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'],
                        ['href'=>'/how-it-works',  'label'=> __('app.footer.how_it_works_guide'),   'svg'=>'<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'],
                        ['href'=>'/find-teachers', 'label'=> __('app.footer.find_teachers'),        'svg'=>'<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>'],
                        ['href'=>'/pricing',       'label'=> __('app.welcome.nav_pricing'),         'svg'=>'<path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>'],
                        ['href'=>'/faq',           'label'=> __('app.footer.faq'),                  'svg'=>'<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>'],
                        ['href'=>'/help',          'label'=> __('app.footer.help_center'),          'svg'=>'<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>'],
                    ];
                @endphp
                @foreach($saLinks as $lnk)
                    <a href="{{ $lnk['href'] }}" onclick="saMenuClose()"
                       style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem 0.875rem;font-size:0.875rem;color:#d1d5db;border-radius:0.75rem;text-decoration:none;"
                       onmouseover="this.style.background='rgba(255,255,255,0.08)';this.style.color='white';"
                       onmouseout="this.style.background='';this.style.color='#d1d5db';">
                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $lnk['svg'] !!}</svg>
                        {{ $lnk['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    </div>

    <script>
        window.saMenuToggle = function() {
            var ov    = document.getElementById('sa-overlay');
            var panel = document.getElementById('sa-panel');
            if (ov.getAttribute('data-open') === '1') { saMenuClose(); return; }

            ov.setAttribute('data-open', '1');
            panel.style.transition = 'none';
            panel.style.transform  = 'translateX(100%)';
            ov.style.display = 'block';

            document.getElementById('sa-icon-menu').style.display  = 'none';
            document.getElementById('sa-icon-close').style.display = 'block';

            requestAnimationFrame(function() {
                panel.style.transition = 'transform 0.28s ease-out';
                panel.style.transform  = 'translateX(0)';
            });
        };

        window.saMenuClose = function() {
            var ov    = document.getElementById('sa-overlay');
            var panel = document.getElementById('sa-panel');
            if (!ov || ov.getAttribute('data-open') !== '1') return;
            ov.setAttribute('data-open', '0');
            panel.style.transition = 'transform 0.28s ease-out';
            panel.style.transform  = 'translateX(100%)';
            document.getElementById('sa-icon-menu').style.display  = 'block';
            document.getElementById('sa-icon-close').style.display = 'none';
            setTimeout(function() { ov.style.display = 'none'; }, 300);
        };
    </script>

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
