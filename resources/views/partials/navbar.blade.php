{{-- Professional Navbar Component --}}
{{-- Usage: @include('partials.navbar', ['active' => 'learn']) --}}

<header class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            
            {{-- Logo --}}
            <a href="/dashboard" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-purple-600 via-purple-500 to-orange-400 flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
                    <i data-lucide="music" class="w-5 h-5 text-white"></i>
                </div>
                <div class="hidden sm:block">
                    <span class="font-bold text-gray-900 text-lg tracking-tight">EarTune</span>
                </div>
            </a>

            {{-- Desktop Navigation --}}
            <nav class="hidden md:flex items-center gap-1">
                @php
                    $navItems = [
                        ['href' => '/dashboard', 'label' => 'Home', 'icon' => 'home', 'key' => 'dashboard'],
                        ['href' => '/learn', 'label' => 'Practice', 'icon' => 'music-2', 'key' => 'learn'],
                        ['href' => '/ai-exercises', 'label' => 'AI Exercises', 'icon' => 'sparkles', 'key' => 'ai'],
                        ['href' => '/piano-studio', 'label' => 'Piano', 'icon' => 'piano', 'key' => 'piano'],
                        ['href' => '/progress', 'label' => 'Progress', 'icon' => 'trending-up', 'key' => 'progress'],
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
            </nav>

            {{-- Right Side: User Menu --}}
            <div class="flex items-center gap-2">
                {{-- Admin Link (if admin) --}}
                @if(Auth::user() && Auth::user()->role === 'admin')
                    <a href="/admin" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-purple-600 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                        <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                        Admin
                    </a>
                @endif

                {{-- User Avatar & Dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" 
                            @click.away="open = false"
                            class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-sm font-semibold shadow-sm">
                            {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                        </div>
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
                            Profile
                        </a>
                        
                        <a href="/progress" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i data-lucide="bar-chart-2" class="w-4 h-4 text-gray-400"></i>
                            My Progress
                        </a>

                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Mobile Menu Button --}}
                <button x-data @click="$dispatch('toggle-mobile-menu')" 
                        class="md:hidden p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Navigation Drawer --}}
    <div x-data="{ mobileOpen: false }" 
         @toggle-mobile-menu.window="mobileOpen = !mobileOpen"
         x-show="mobileOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="md:hidden fixed inset-0 z-40 bg-black/20 backdrop-blur-sm"
         style="display: none;">
        
        <div @click.away="mobileOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="absolute right-0 top-0 h-full w-72 bg-white shadow-xl">
            
            {{-- Mobile Header --}}
            <div class="flex items-center justify-between p-4 border-b border-gray-100">
                <span class="font-bold text-gray-900">Menu</span>
                <button @click="mobileOpen = false" class="p-2 text-gray-500 hover:text-gray-700 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Mobile Nav Links --}}
            <nav class="p-4 space-y-1">
                @foreach($navItems as $item)
                    <a href="{{ $item['href'] }}" 
                       class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all
                              {{ $currentActive === $item['key'] 
                                 ? 'bg-purple-50 text-purple-700' 
                                 : 'text-gray-600 hover:bg-gray-50' }}">
                        <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
                        {{ $item['label'] }}
                    </a>
                @endforeach

                @if(Auth::user() && Auth::user()->role === 'admin')
                    <a href="/admin" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-purple-600 hover:bg-purple-50 transition-all">
                        <i data-lucide="shield" class="w-5 h-5"></i>
                        Admin Panel
                    </a>
                @endif
            </nav>

            {{-- Mobile User Section --}}
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-100 bg-gray-50">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold">
                        {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name ?? 'User' }}</p>
                        <p class="text-xs text-gray-500">{{ Auth::user()->email ?? '' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
