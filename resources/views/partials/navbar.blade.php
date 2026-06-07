{{-- Professional Navbar Component --}}
{{-- Usage: @include('partials.navbar', ['active' => 'learn']) --}}

<header class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            
            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 group md:-ml-20">
                {{-- Icon mark: black box, white H --}}
                <div class="w-11 h-11 rounded-xl bg-gray-900 flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow shrink-0">
                    <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6">
                        <rect x="2" y="3" width="5.5" height="22" rx="2" fill="white"/>
                        <rect x="20.5" y="3" width="5.5" height="22" rx="2" fill="white"/>
                        <path d="M7.5 14 Q11 9 14 14 Q17 19 20.5 14" stroke="white" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                    </svg>
                </div>
                {{-- Wordmark --}}
                <div class="hidden sm:block">
                    <span class="font-bold text-2xl tracking-tight leading-none">
                        <span style="background: linear-gradient(135deg,#9333ea,#fb923c); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">H</span><span class="text-gray-900">armoniva</span>
                    </span>
                </div>
            </a>

            {{-- Desktop Navigation --}}
            <nav class="hidden md:flex items-center gap-1">
                @php
                    $navItems = [
                        ['href' => '/dashboard', 'label' => __('app.nav.home'), 'icon' => 'home', 'key' => 'dashboard'],
                        ['href' => '/learn', 'label' => __('app.nav.practice'), 'icon' => 'music-2', 'key' => 'learn'],
                        ['href' => '/games', 'label' => __('app.nav.games'), 'icon' => 'gamepad-2', 'key' => 'games'],
                        ['href' => '/exercise-setup', 'label' => __('app.nav.setup_studio'), 'icon' => 'wand-sparkles', 'key' => 'exercise-setup'],
                        ['href' => '/ai-exercises', 'label' => __('app.nav.ai_exercises'), 'icon' => 'sparkles', 'key' => 'ai'],
                        ['href' => '/piano-studio', 'label' => __('app.nav.piano'), 'icon' => 'piano', 'key' => 'piano'],
                        ['href' => '/progress', 'label' => __('app.nav.progress'), 'icon' => 'trending-up', 'key' => 'progress'],
                    ];
                    $currentActive = $active ?? '';
                @endphp
                
                @foreach($navItems as $item)
                    <a href="{{ $item['href'] }}"
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all
                              {{ $currentActive === $item['key']
                                 ? 'bg-purple-50 text-purple-700'
                                 : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                        <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4"></i>
                        {{ $item['label'] }}
                    </a>
                @endforeach

                @if(Auth::check() && (Auth::user()->isTeacher() || Auth::user()->isSchool()))
                    <a href="{{ route('articles.index') }}"
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all
                              {{ $currentActive === 'articles' ? 'bg-purple-50 text-purple-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        {{ __('app.nav.blog') }}
                    </a>
                @endif
            </nav>

            {{-- Right Side: User Menu --}}
            <div class="flex items-center gap-2">
                {{-- Search Button --}}
                <a href="/search"
                   class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-lg transition-colors inline-flex items-center"
                   title="{{ __('app.nav.search') }}">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </a>
                {{-- Admin Link (if admin) --}}
                @if(Auth::user() && Auth::user()->role === 'admin')
                    <a href="/admin" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-purple-600 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                        <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                        Admin
                    </a>
                @endif

                @auth
                {{-- User Avatar & Dropdown --}}
                <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                    <button @click="open = !open"
                            class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-50 transition-colors">
                        @if(Auth::user()->hasAvatar())
                            <img src="{{ Auth::user()->avatar }}" alt="" class="w-8 h-8 rounded-full object-cover shadow-sm">
                        @else
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-sm font-semibold shadow-sm">
                                {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                            </div>
                        @endif
                        <span class="hidden lg:block text-sm font-medium text-gray-700 max-w-[100px] truncate">
                            {{ Auth::user()->name ?? 'User' }}
                        </span>
                        <i data-lucide="chevron-down" class="hidden lg:block w-4 h-4 text-gray-400"></i>
                    </button>

                    {{-- Dropdown Menu --}}
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
                         style="display: none;">

                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name ?? 'User' }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email ?? '' }}</p>
                        </div>

                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                            {{ __('app.nav.profile') }}
                        </a>

                        <a href="{{ route('profile.edit', ['tab' => 'settings']) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i data-lucide="settings" class="w-4 h-4 text-gray-400"></i>
                            {{ __('app.nav.profile_settings') }}
                        </a>

                        <a href="/progress" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i data-lucide="bar-chart-2" class="w-4 h-4 text-gray-400"></i>
                            {{ __('app.nav.my_progress') }}
                        </a>

                        @if(Auth::user()->isTeacher())
                            <a href="{{ route('teacher.profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i data-lucide="book-open" class="w-4 h-4 text-gray-400"></i>
                                {{ __('app.nav.teacher_panel') }}
                            </a>
                        @endif

                        @if(Auth::user()->isSchool())
                            <a href="{{ route('school.profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i data-lucide="building-2" class="w-4 h-4 text-gray-400"></i>
                                {{ __('app.nav.school_panel') }}
                            </a>
                        @endif

                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                    {{ __('app.nav.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @else
                {{-- Guest: Login / Register links --}}
                <div class="hidden md:flex items-center gap-2">
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                        {{ __('app.welcome.nav_login') }}
                    </a>
                    @if(Route::has('register'))
                    <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-purple-600 to-purple-700 rounded-lg hover:from-purple-500 hover:to-purple-600 transition-all shadow-sm">
                        {{ __('app.welcome.nav_start_free') }}
                    </a>
                    @endif
                </div>
                @endauth

                {{-- Mobile Menu Button (inline SVG — no Lucide dependency) --}}
                <button x-data @click="$dispatch('toggle-mobile-menu')"
                        class="md:hidden flex items-center justify-center p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors"
                        style="width:40px; height:40px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                        <line x1="4" y1="6"  x2="20" y2="6"/>
                        <line x1="4" y1="12" x2="20" y2="12"/>
                        <line x1="4" y1="18" x2="20" y2="18"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>


</header>

{{-- Mobile Navigation Drawer --}}
{{-- Overlay: inline styles garantili fixed positioning --}}
<div x-data="{ mobileOpen: false }"
     @toggle-mobile-menu.window="mobileOpen = !mobileOpen"
     x-show="mobileOpen"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:9999; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px);"
     @click.self="mobileOpen = false">

    <div style="position:absolute; right:0; top:0; width:18rem; height:100%; background:#111827; box-shadow:-4px 0 24px rgba(0,0,0,0.4); display:flex; flex-direction:column; overflow-y:auto;">

        {{-- Drawer Header --}}
        <div class="flex items-center justify-between p-4 border-b border-gray-700">
            <span class="font-bold text-white">{{ __('app.nav.menu') }}</span>
            <button @click="mobileOpen = false" class="p-2 text-gray-400 hover:text-white rounded-lg transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        {{-- Search Box --}}
        <div class="px-4 pt-4 pb-3 border-b border-gray-700">
            <form action="/search" method="GET">
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        <input type="text" name="q"
                               placeholder="{{ __('app.nav.search') }}..."
                               style="width:100%; padding:9px 12px 9px 34px; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); border-radius:10px; color:white; font-size:13px; outline:none;"
                               onfocus="this.style.borderColor='#9333ea'; this.style.background='rgba(255,255,255,0.15)'"
                               onblur="this.style.borderColor='rgba(255,255,255,0.2)'; this.style.background='rgba(255,255,255,0.1)'">
                    </div>
                    <button type="submit"
                            style="flex-shrink:0; padding:9px 14px; background:#9333ea; color:white; border-radius:10px; font-size:13px; font-weight:600; border:none; cursor:pointer;">
                        {{ __('app.nav.search') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Nav Links --}}
        <nav class="p-4 space-y-1 flex-1">
            @foreach($navItems as $item)
                <a href="{{ $item['href'] }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all
                          {{ $currentActive === $item['key']
                             ? 'bg-purple-600 text-white'
                             : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                    <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 shrink-0"></i>
                    {{ $item['label'] }}
                </a>
            @endforeach

            @if(Auth::check() && Auth::user()->isTeacher() || Auth::check() && Auth::user()->isSchool())
                <a href="{{ route('articles.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all
                          {{ $currentActive === 'articles' ? 'bg-purple-600 text-white' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                    <i data-lucide="file-text" class="w-5 h-5 shrink-0"></i>
                    {{ __('app.nav.blog') }}
                </a>
            @endif

            @if(Auth::user() && Auth::user()->role === 'admin')
                <a href="/admin" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-purple-400 hover:text-purple-300 hover:bg-white/10 transition-all">
                    <i data-lucide="shield" class="w-5 h-5 shrink-0"></i>
                    {{ __('app.nav.admin_panel') }}
                </a>
            @endif
        </nav>

        @auth
        {{-- User Section --}}
        <div class="p-4 border-t border-gray-700 bg-gray-800/60">
            <div class="flex items-center gap-3 mb-4">
                @if(Auth::user()->hasAvatar())
                    <img src="{{ Auth::user()->avatar }}" alt="" class="w-10 h-10 rounded-full object-cover">
                @else
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold shrink-0">
                        {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name ?? 'User' }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email ?? '' }}</p>
                </div>
            </div>
            <div class="space-y-1 mb-3">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                    <i data-lucide="user" class="w-4 h-4 shrink-0"></i>
                    {{ __('app.nav.profile') }}
                </a>
                <a href="{{ route('profile.edit', ['tab' => 'settings']) }}" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                    <i data-lucide="settings" class="w-4 h-4 shrink-0"></i>
                    {{ __('app.nav.profile_settings') }}
                </a>
                @if(Auth::user()->isTeacher())
                    <a href="{{ route('teacher.profile.edit') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                        <i data-lucide="book-open" class="w-4 h-4 shrink-0"></i>
                        {{ __('app.nav.teacher_panel') }}
                    </a>
                @endif
                @if(Auth::user()->isSchool())
                    <a href="{{ route('school.profile.edit') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                        <i data-lucide="building-2" class="w-4 h-4 shrink-0"></i>
                        {{ __('app.nav.school_panel') }}
                    </a>
                @endif
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-red-900/50 hover:bg-red-900/80 text-red-300 hover:text-red-100 text-sm font-medium rounded-lg transition-colors border border-red-800/50">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    {{ __('app.nav.logout') }}
                </button>
            </form>
        </div>
        @else
        {{-- Guest: Login / Register in mobile drawer --}}
        <div class="p-4 border-t border-gray-700 bg-gray-800/60 flex flex-col gap-2">
            <a href="{{ route('login') }}"
               style="display:flex;align-items:center;justify-content:center;gap:0.5rem;padding:0.75rem 1rem;font-size:0.875rem;font-weight:600;color:white;border:1px solid #4b5563;border-radius:0.75rem;text-decoration:none;">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                {{ __('app.welcome.nav_login') }}
            </a>
            @if(Route::has('register'))
            <a href="{{ route('register') }}"
               style="display:flex;align-items:center;justify-content:center;gap:0.5rem;padding:0.75rem 1rem;font-size:0.875rem;font-weight:600;color:white;background:linear-gradient(135deg,#7c3aed,#9333ea);border-radius:0.75rem;text-decoration:none;">
                <i data-lucide="zap" class="w-4 h-4"></i>
                {{ __('app.welcome.nav_start_free') }}
            </a>
            @endif
        </div>
        @endauth
    </div>
</div>
