{{-- Professional Navbar Component --}}
{{-- Usage: @include('partials.navbar', ['active' => 'learn']) --}}

@if(session('impersonator_id'))
<div class="bg-amber-500 text-white text-sm font-medium px-4 py-2 flex items-center justify-center gap-3 relative z-[60]">
    <span>{!! __('app.nav.impersonating', ['name' => '<strong>'.e(auth()->user()->name ?? '').'</strong>']) !!}</span>
    <form method="POST" action="{{ route('impersonate.leave') }}" class="inline">
        @csrf
        <button type="submit" class="underline font-semibold hover:text-amber-100">{{ __('app.nav.return_admin') }}</button>
    </form>
</div>
@endif

<header class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center group shrink-0">
                <img src="{{ asset('images/logo-full.png') }}" alt="Harmoniva" width="1374" height="340" class="h-[43px] sm:h-[52px] w-auto">
            </a>

            {{-- Desktop Navigation (revealed at lg so the full item set never
                 overflows a tablet-width bar; below lg the drawer holds everything) --}}
            <nav class="hidden lg:flex items-center gap-1 ml-6">
                @php
                    // Students (plain "user" role) get a streamlined top bar:
                    // Dashboard · Exercise Setup · Music Games · AI Exercises · Piano · Notifications
                    // (Notifications is rendered separately below.) Teacher/school/admin keep the full set.
                    $isStudent = Auth::check() && Auth::user()->role === 'user';
                    // Teacher accounts (any user holding a TeacherProfile, plus legacy teacher role)
                    // get a dedicated top bar. Every other teacher tool lives in the CRM sidebar.
                    $isTeacherAccount = Auth::check() && Auth::user()->hasTeacherAccount();
                    // Admins get a focused top bar linking straight into the admin modules.
                    $isAdmin = Auth::check() && Auth::user()->role === 'admin';

                    $currentActive = $active ?? '';
                    // Unified Messages badge: general messages + teacher-student conversations.
                    $unreadMessages = 0;
                    $unreadNotifications = 0;
                    $myProfileUrl = '#';
                    if (Auth::check()) {
                        $unreadMessages = \App\Models\Message::where('receiver_id', Auth::id())->where('type', 'message')->unread()->count()
                            + app(\App\Services\Teacher\TeacherMessagingService::class)->unreadTotalFor(Auth::user());
                        $unreadNotifications = Auth::user()->unreadNotifications()->count();
                        $myProfileUrl = Auth::user()->username
                            ? route('profile.public', Auth::user()->username)
                            : route('profile.edit');
                    }

                    if ($isAdmin) {
                        // Order (left→right): Dashboard, Members, Teachers, Feed, Messages, Email, Support.
                        // Messages carries an unread badge; the shared trailing Messages/My Profile
                        // blocks below are suppressed for admins.
                        $navItems = [
                            ['href' => route('admin.dashboard'),          'label' => __('app.nav.dashboard'), 'icon' => 'layout-dashboard', 'key' => 'admin-dashboard'],
                            ['href' => route('admin.users.index'),        'label' => __('app.nav.members'),   'icon' => 'users',            'key' => 'admin-members'],
                            ['href' => route('admin.teacher-profiles.index'), 'label' => __('app.nav.teachers'), 'icon' => 'graduation-cap', 'key' => 'admin-teachers'],
                            ['href' => route('admin.community.index'),    'label' => __('app.nav.feed'),      'icon' => 'rss',              'key' => 'admin-feed'],
                            ['href' => route('admin.messages.index'),     'label' => __('app.nav.messages'),  'icon' => 'message-circle',   'key' => 'admin-messages', 'badge' => $unreadMessages],
                            ['href' => route('admin.email-center.dashboard'), 'label' => __('app.nav.email'), 'icon' => 'mail',           'key' => 'admin-email'],
                            ['href' => route('admin.support-inbox.index'), 'label' => __('app.nav.support'),  'icon' => 'life-buoy',        'key' => 'admin-support'],
                        ];
                    } elseif ($isTeacherAccount) {
                        // Profile → the teacher's public, shareable profile URL (/teachers/{slug}).
                        // Falls back to the owner preview when no slug exists yet (legacy teacher role).
                        $tp = Auth::user()->teacherProfile;
                        $teacherPublicUrl = ($tp && $tp->slug)
                            ? route($tp->isSchoolEntity() ? 'schools.show' : 'teachers.show', $tp->slug)
                            : route(Auth::user()->crmRouteName('profile.preview'));

                        // Order (left→right): Dashboard, Feed, Notifications, Profile, My Students, Calendar.
                        // Notifications carries an unread badge; Messages lives in the CRM inbox
                        // (sidebar), so it is intentionally omitted from this top nav for teachers.
                        $navItems = [
                            ['href' => route(Auth::user()->crmRouteName('dashboard')),       'label' => __('app.nav.dashboard'),          'icon' => 'layout-dashboard', 'key' => 'teacher'],
                            ['href' => route(Auth::user()->crmRouteName('feed')),            'label' => __('app.nav.feed'),               'icon' => 'rss',              'key' => 'feed'],
                            ['href' => route('notifications.index'),     'label' => __('app.nav.notifications'),      'icon' => 'bell',             'key' => 'notifications', 'badge' => $unreadNotifications],
                            ['href' => $teacherPublicUrl,                'label' => __('teacher.nav.profile'),        'icon' => 'user-pen',         'key' => 'my-profile'],
                            ['href' => route(Auth::user()->crmRouteName('students.index')),  'label' => __('teacher.dashboard.hero_students'), 'icon' => 'users',       'key' => 'students'],
                            ['href' => route(Auth::user()->crmRouteName('calendar.index')),  'label' => __('teacher.nav.calendar'),       'icon' => 'calendar',         'key' => 'calendar'],
                        ];
                    } elseif ($isStudent) {
                        $navItems = [
                            ['href' => '/dashboard', 'label' => __('app.nav.dashboard'), 'icon' => 'home', 'key' => 'dashboard'],
                            ['href' => '/exercise-setup', 'label' => __('app.nav.setup_studio'), 'icon' => 'wand-sparkles', 'key' => 'exercise-setup'],
                            ['href' => '/games', 'label' => __('app.nav.games'), 'icon' => 'gamepad-2', 'key' => 'games'],
                            ['href' => '/ai-exercises', 'label' => __('app.nav.ai_exercises'), 'icon' => 'sparkles', 'key' => 'ai'],
                            ['href' => locale_url('/piano-studio'), 'label' => __('app.nav.piano'), 'icon' => 'piano', 'key' => 'piano'],
                        ];
                    } else {
                        // Guest / default top bar. Home shows the icon only (no label) to keep the
                        // bar on a single line; the Progress entry is intentionally omitted here.
                        $navItems = [
                            ['href' => '/dashboard', 'label' => __('app.nav.home'), 'icon' => 'home', 'key' => 'dashboard', 'icon_only' => true],
                            ['href' => locale_url('/learn'), 'label' => __('app.nav.practice'), 'icon' => 'music-2', 'key' => 'learn'],
                            ['href' => '/games', 'label' => __('app.nav.games'), 'icon' => 'gamepad-2', 'key' => 'games'],
                            ['href' => '/exercise-setup', 'label' => __('app.nav.setup_studio'), 'icon' => 'wand-sparkles', 'key' => 'exercise-setup'],
                            ['href' => '/ai-exercises', 'label' => __('app.nav.ai_exercises'), 'icon' => 'sparkles', 'key' => 'ai'],
                            ['href' => locale_url('/piano-studio'), 'label' => __('app.nav.piano'), 'icon' => 'piano', 'key' => 'piano'],
                        ];
                    }
                @endphp
                
                @foreach($navItems as $item)
                    @php $iconOnly = $item['icon_only'] ?? false; @endphp
                    <a href="{{ $item['href'] }}"
                       @if($iconOnly) title="{{ $item['label'] }}" aria-label="{{ $item['label'] }}" @endif
                       class="relative flex items-center {{ $iconOnly ? '' : 'gap-2' }} px-3 py-2 rounded-lg text-sm font-medium transition-all
                              {{ $currentActive === $item['key']
                                 ? 'bg-purple-50 text-purple-700'
                                 : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                        <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4 {{ ($item['key'] === 'notifications' && ($item['badge'] ?? 0) > 0) ? 'text-purple-600' : '' }}"></i>
                        @unless($iconOnly){{ $item['label'] }}@endunless
                        @if(($item['badge'] ?? 0) > 0)
                            <span class="absolute -top-0.5 -right-0.5 bg-purple-600 text-white text-[10px] font-bold rounded-full px-1.5 min-w-[18px] text-center leading-4">{{ $item['badge'] > 9 ? '9+' : $item['badge'] }}</span>
                        @endif
                    </a>
                @endforeach

                {{-- My Profile (replaces the former Feed slot) — hidden for students, teacher accounts and admins, who reach it via the avatar dropdown / dashboard grid --}}
                @auth
                    @unless($isStudent || $isTeacherAccount || $isAdmin)
                    <a href="{{ $myProfileUrl }}"
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all
                              {{ $currentActive === 'my-profile' ? 'bg-purple-50 text-purple-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        {{ __('app.nav.my_profile') }}
                    </a>
                    @endunless
                @endauth

                {{-- Messages: hidden for students and any teacher/school account (they use the CRM inbox) --}}
                @auth
                    @unless($isStudent || $isTeacherAccount || $isAdmin || Auth::user()->isTeacher() || Auth::user()->isSchool())
                        <a href="/messages"
                           class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all
                                  {{ $currentActive === 'messages' ? 'bg-purple-50 text-purple-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                            {{ __('app.nav.messages') }}
                            @if($unreadMessages > 0)
                                <span class="absolute -top-0.5 -right-0.5 bg-purple-600 text-white text-[10px] font-bold rounded-full px-1.5 min-w-[18px] text-center leading-4">{{ $unreadMessages > 9 ? '9+' : $unreadMessages }}</span>
                            @endif
                        </a>
                    @endunless

                    {{-- Notifications: visible to every authenticated user (teachers get it inside their nav set above; admins use the focused admin bar) --}}
                    @unless($isTeacherAccount || $isAdmin)
                    <a href="{{ route('notifications.index') }}"
                       class="relative flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all
                              {{ $currentActive === 'notifications' ? 'bg-purple-50 text-purple-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                        <i data-lucide="bell" class="w-4 h-4 {{ $unreadNotifications > 0 ? 'text-purple-600' : '' }}"></i>
                        {{ __('app.nav.notifications') }}
                        @if($unreadNotifications > 0)
                            <span class="absolute -top-0.5 -right-0.5 bg-purple-600 text-white text-[10px] font-bold rounded-full px-1.5 min-w-[18px] text-center leading-4">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
                        @endif
                    </a>
                    @endunless
                @endauth
            </nav>

            {{-- Right Side: User Menu --}}
            <div class="flex items-center gap-2">
                {{-- Search: compact expandable input (xl+ only — keeps the lg nav bar from overflowing) --}}
                <div x-data="{ focused: false }" class="hidden xl:flex items-center ml-2">
                    <form action="/search" method="GET">
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                            <input type="text" name="q"
                                   placeholder="{{ __('app.nav.search') }}..."
                                   @focus="focused = true"
                                   @blur="focused = false"
                                   :class="focused ? 'w-48 bg-white border-purple-300 ring-1 ring-purple-100' : 'w-28 bg-gray-50 border-gray-200'"
                                   class="pl-8 pr-3 py-1.5 text-sm rounded-lg border outline-none transition-all duration-300 text-gray-700 placeholder-gray-400">
                        </div>
                    </form>
                </div>
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
                            {{ $isAdmin ? 'Admin' : (Auth::user()->name ?? 'User') }}
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
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $isAdmin ? 'Admin' : (Auth::user()->name ?? 'User') }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email ?? '' }}</p>
                        </div>

                        @if($isAdmin)
                            {{-- Admins: straight to the admin dashboard; general user settings are hidden --}}
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i data-lucide="layout-dashboard" class="w-4 h-4 text-gray-400"></i>
                                {{ __('app.nav.dashboard') }}
                            </a>
                        @elseif(Auth::user()->hasTeacherAccount())
                            {{-- Teacher accounts: CRM-centric menu, old profile pages are gone --}}
                            <a href="{{ route(Auth::user()->crmRouteName('dashboard')) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i data-lucide="layout-dashboard" class="w-4 h-4 text-gray-400"></i>
                                {{ __('app.nav.teacher_panel') }}
                            </a>

                            <a href="{{ route(Auth::user()->crmRouteName('profile.edit')) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i data-lucide="user-pen" class="w-4 h-4 text-gray-400"></i>
                                {{ __('teacher.nav.profile') }}
                            </a>

                            <a href="{{ route(Auth::user()->crmRouteName('profile.preview')) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i data-lucide="eye" class="w-4 h-4 text-gray-400"></i>
                                {{ __('teacher.nav.public_profile') }}
                            </a>

                            <a href="{{ route(Auth::user()->crmRouteName('settings')) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i data-lucide="settings" class="w-4 h-4 text-gray-400"></i>
                                {{ __('teacher.nav.settings') }}
                            </a>
                        @else
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
                        @endif

                        @if(!$isAdmin && Auth::user()->isSchool())
                            <a href="{{ route('school.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
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
                <div class="hidden lg:flex items-center gap-2">
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
                        class="lg:hidden flex items-center justify-center p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors"
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
                    @if(($item['badge'] ?? 0) > 0)
                        <span class="ml-auto bg-purple-600 text-white text-[10px] font-bold rounded-full px-1.5 min-w-[18px] text-center leading-4">{{ $item['badge'] > 9 ? '9+' : $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach

            {{-- My Profile (replaces the former Feed slot) — hidden for students and teacher accounts, who reach it via the avatar menu / dashboard grid --}}
            @auth
                @unless($isStudent || $isTeacherAccount)
                <a href="{{ $myProfileUrl }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all
                          {{ $currentActive === 'my-profile' ? 'bg-purple-600 text-white' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                    <i data-lucide="user" class="w-5 h-5 shrink-0"></i>
                    {{ __('app.nav.my_profile') }}
                </a>
                @endunless
            @endauth

            {{-- Messages: hidden for students and any teacher/school account (they use the CRM inbox) --}}
            @auth
                @unless($isStudent || $isTeacherAccount || Auth::user()->isTeacher() || Auth::user()->isSchool())
                    <a href="/messages"
                       class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all
                              {{ $currentActive === 'messages' ? 'bg-purple-600 text-white' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                        <i data-lucide="message-circle" class="w-5 h-5 shrink-0"></i>
                        {{ __('app.nav.messages') }}
                        @if($unreadMessages > 0)
                            <span class="ml-auto bg-purple-600 text-white text-[10px] font-bold rounded-full px-1.5 min-w-[18px] text-center leading-4">{{ $unreadMessages > 9 ? '9+' : $unreadMessages }}</span>
                        @endif
                    </a>
                @endunless

                {{-- Notifications: visible to every authenticated user (teachers get it inside their nav set above) --}}
                @unless($isTeacherAccount)
                <a href="{{ route('notifications.index') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all
                          {{ $currentActive === 'notifications' ? 'bg-purple-600 text-white' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                    <i data-lucide="bell" class="w-5 h-5 shrink-0"></i>
                    {{ __('app.nav.notifications') }}
                    @if($unreadNotifications > 0)
                        <span class="ml-auto bg-purple-600 text-white text-[10px] font-bold rounded-full px-1.5 min-w-[18px] text-center leading-4">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
                    @endif
                </a>
                @endunless
            @endauth

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
                @if(Auth::user()->hasTeacherAccount())
                    <a href="{{ route(Auth::user()->crmRouteName('dashboard')) }}" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0"></i>
                        {{ __('app.nav.teacher_panel') }}
                    </a>
                    <a href="{{ route(Auth::user()->crmRouteName('profile.edit')) }}" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                        <i data-lucide="user-pen" class="w-4 h-4 shrink-0"></i>
                        {{ __('teacher.nav.profile') }}
                    </a>
                    <a href="{{ route(Auth::user()->crmRouteName('profile.preview')) }}" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                        <i data-lucide="eye" class="w-4 h-4 shrink-0"></i>
                        {{ __('teacher.nav.public_profile') }}
                    </a>
                    <a href="{{ route(Auth::user()->crmRouteName('settings')) }}" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                        <i data-lucide="settings" class="w-4 h-4 shrink-0"></i>
                        {{ __('teacher.nav.settings') }}
                    </a>
                @else
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                        <i data-lucide="user" class="w-4 h-4 shrink-0"></i>
                        {{ __('app.nav.profile') }}
                    </a>
                    <a href="{{ route('profile.edit', ['tab' => 'settings']) }}" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                        <i data-lucide="settings" class="w-4 h-4 shrink-0"></i>
                        {{ __('app.nav.profile_settings') }}
                    </a>
                @endif
                @if(Auth::user()->isSchool())
                    <a href="{{ route('school.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
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
