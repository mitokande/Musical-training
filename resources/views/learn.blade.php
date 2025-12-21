<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Learning Path - {{ config('app.name', 'Ear Training Studio') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#faf5ff',
                            100: '#f3e8ff',
                            200: '#e9d5ff',
                            300: '#d8b4fe',
                            400: '#c084fc',
                            500: '#a855f7',
                            600: '#9333ea',
                            700: '#7c3aed',
                            800: '#6b21a8',
                            900: '#581c87',
                        },
                        accent: {
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #9333ea 0%, #c084fc 35%, #f97316 100%);
        }
        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #7c3aed 0%, #6b21a8 100%);
        }
        .progress-bar {
            background: linear-gradient(90deg, #9333ea 0%, #c084fc 100%);
        }
        .progress-bar-yellow {
            background: linear-gradient(90deg, #f97316 0%, #fbbf24 100%);
        }
        .nav-item {
            transition: all 0.2s ease;
        }
        .nav-item:hover {
            background: #f3f4f6;
        }
        .nav-item.active {
            background: #f3f4f6;
            font-weight: 600;
        }
        .filter-tab {
            transition: all 0.2s ease;
        }
        .filter-tab:hover {
            background: #f3f4f6;
        }
        .filter-tab.active {
            background: #1f2937;
            color: white;
        }
        .module-card {
            transition: all 0.2s ease;
        }
        .module-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }
    </style>
</head>
<body class="font-sans bg-gray-50 min-h-screen">
    <!-- Header/Navigation -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-purple-600 to-orange-500 flex items-center justify-center">
                        <i data-lucide="music" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-bold text-lg text-gray-900">Ear Training<br class="sm:hidden"><span class="text-purple-600"> Studio</span></span>
                </div>

                <!-- Navigation -->
                <nav class="hidden lg:flex items-center gap-1">
                    <a href="/dashboard" class="nav-item flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-600">
                        <i data-lucide="home" class="w-4 h-4"></i>
                        Home
                    </a>
                    <a href="/learn" class="nav-item active flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-700">
                        <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                        Learn Path
                    </a>
                    <a href="#" class="nav-item flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-600">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                        AI Exercises
                    </a>
                    <a href="#" class="nav-item flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-600">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        Resources
                    </a>
                    <a href="#" class="nav-item flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-600">
                        <i data-lucide="zap" class="w-4 h-4"></i>
                        Quick Drills
                    </a>
                    <a href="#" class="nav-item flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-600">
                        <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                        My Progress
                    </a>
                </nav>

                <!-- User Menu -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold text-sm">
                            {{ substr(Auth::user()->name ?? 'M', 0, 1) }}
                        </div>
                        <span class="hidden sm:block text-sm font-medium text-gray-700">{{ Auth::user()->name ?? 'Mithat Can Turan' }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                            <i data-lucide="log-out" class="w-5 h-5"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Hero Section -->
        <div class="hero-gradient rounded-2xl p-8 mb-8 relative overflow-hidden">
            <!-- Decorative elements -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                        Your Learning Path
                    </h1>
                    <p class="text-white/80">Master essential ear training skills step by step</p>
                </div>
                
                <div class="mt-6 md:mt-0 text-right">
                    <div class="text-4xl font-bold text-white mb-1">4%</div>
                    <p class="text-white/80 text-sm mb-3">Overall Progress</p>
                    <div class="w-48 bg-white/30 rounded-full h-2">
                        <div class="progress-bar-yellow h-2 rounded-full" style="width: 4%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="flex flex-wrap gap-2 mb-8">
            <button class="filter-tab active flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium">
                <i data-lucide="music" class="w-4 h-4"></i>
                All Modules
            </button>
            <button class="filter-tab flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-200">
                <i data-lucide="git-branch" class="w-4 h-4"></i>
                Intervals
            </button>
            <button class="filter-tab flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-200">
                <i data-lucide="music-2" class="w-4 h-4"></i>
                Melodic Intervals
            </button>
            <button class="filter-tab flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-200">
                <i data-lucide="arrow-right-left" class="w-4 h-4"></i>
                Intervals Direction
            </button>
            <button class="filter-tab flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-200">
                <i data-lucide="music-3" class="w-4 h-4"></i>
                Harmonic Intervals
            </button>
            <button class="filter-tab flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-200">
                <i data-lucide="waves" class="w-4 h-4"></i>
                Scales & Modes
            </button>
            <button class="filter-tab flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-200">
                <i data-lucide="layers" class="w-4 h-4"></i>
                Chords
            </button>
            <button class="filter-tab flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-200">
                <i data-lucide="drum" class="w-4 h-4"></i>
                Rhythm
            </button>
            <button class="filter-tab flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-200">
                <i data-lucide="pencil" class="w-4 h-4"></i>
                Melodic Dictation
            </button>
        </div>

        <!-- Module Cards Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            @forelse($practices as $practice)
                @php
                    // Define icon colors based on practice type
                    $iconColors = [
                        'Recognition' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
                        'Dictation' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600'],
                        'Theory' => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-600'],
                        'Rhythm' => ['bg' => 'bg-red-100', 'text' => 'text-red-600'],
                        'default' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
                    ];
                    $colors = $iconColors[$practice['type']] ?? $iconColors['default'];
                    $isPremium = $practice['is_premium'] == '1' || $practice['is_premium'] == 1;
                @endphp

                <div class="module-card card p-6 relative">
                    @if($isPremium)
                        <div class="absolute top-4 right-4">
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">
                                <i data-lucide="crown" class="w-3 h-3"></i>
                                Premium
                            </span>
                        </div>
                    @endif

                    <div class="w-12 h-12 rounded-xl {{ $colors['bg'] }} flex items-center justify-center mb-4">
                        <i data-lucide="music" class="w-6 h-6 {{ $colors['text'] }}"></i>
                    </div>

                    <h3 class="font-bold text-gray-900 mb-1">{{ $practice['name'] }}</h3>
                    <p class="text-sm text-gray-500 mb-4 line-clamp-2">{{ $practice['description'] }}</p>
                    
                    <div class="flex items-center justify-between text-sm mb-3">
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-1 text-gray-500">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                {{ $practice['type'] }}
                            </span>
                        </div>
                        <span class="text-gray-400">0%</span>
                    </div>
                    
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mb-4">
                        <div class="progress-bar h-1.5 rounded-full" style="width: 0%"></div>
                    </div>
                    
                    @if($isPremium)
                        <button class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 px-4 rounded-lg flex items-center justify-center gap-2 border border-gray-200">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                            Unlock with Premium
                        </button>
                    @else
                        <a href="/practice/{{ $practice['slug'] }}" class="w-full btn-primary text-white font-semibold py-2.5 px-4 rounded-lg flex items-center justify-center gap-2">
                            <i data-lucide="play" class="w-4 h-4"></i>
                            Start Module
                        </a>
                    @endif
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="music" class="w-8 h-8 text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No practices available</h3>
                    <p class="text-gray-500">Check back later for new ear training exercises.</p>
                </div>
            @endforelse

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8">
                <!-- Brand -->
                <div class="col-span-2 md:col-span-4 lg:col-span-1">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-purple-600 to-orange-500 flex items-center justify-center">
                            <i data-lucide="music" class="w-5 h-5 text-white"></i>
                        </div>
                        <span class="font-bold text-lg text-white">Ear Training Studio</span>
                    </div>
                    <p class="text-sm mb-4">
                        An AI-powered ear training platform for musicians, students, and educators. Master your musical ear with personalized exercises.
                    </p>
                    <div class="flex items-center gap-3">
                        <a href="#" class="w-8 h-8 rounded-lg bg-gray-800 flex items-center justify-center hover:bg-gray-700 transition-colors">
                            <i data-lucide="youtube" class="w-4 h-4"></i>
                        </a>
                        <a href="#" class="w-8 h-8 rounded-lg bg-gray-800 flex items-center justify-center hover:bg-gray-700 transition-colors">
                            <i data-lucide="instagram" class="w-4 h-4"></i>
                        </a>
                        <a href="#" class="w-8 h-8 rounded-lg bg-gray-800 flex items-center justify-center hover:bg-gray-700 transition-colors">
                            <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                        </a>
                        <a href="#" class="w-8 h-8 rounded-lg bg-gray-800 flex items-center justify-center hover:bg-gray-700 transition-colors">
                            <i data-lucide="twitter" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

                <!-- Platform -->
                <div>
                    <h4 class="font-semibold text-white mb-4">Platform</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">Home</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Learning Path</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Quick Drills</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Pricing & Plans</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Search Content</a></li>
                    </ul>
                </div>

                <!-- Resources -->
                <div>
                    <h4 class="font-semibold text-white mb-4">Resources</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">All Resources</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Articles</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Documents</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Videos</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">FAQ & Help Center</a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div>
                    <h4 class="font-semibold text-white mb-4">Company</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">About Us</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contact Support</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Privacy & Terms</a></li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                            <span>support@eartraining.com</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                            <span>San Francisco, CA</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- App Download & Search -->
            <div class="border-t border-gray-800 mt-8 pt-8">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                    <div>
                        <p class="text-sm text-gray-500 mb-3">Get the App (Coming Soon)</p>
                        <div class="flex items-center gap-3">
                            <a href="#" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 rounded-lg hover:bg-gray-700 transition-colors">
                                <i data-lucide="apple" class="w-5 h-5"></i>
                                <div class="text-left">
                                    <p class="text-[10px] text-gray-400">Download on the</p>
                                    <p class="text-sm font-semibold text-white">App Store</p>
                                </div>
                            </a>
                            <a href="#" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 rounded-lg hover:bg-gray-700 transition-colors">
                                <i data-lucide="smartphone" class="w-5 h-5"></i>
                                <div class="text-left">
                                    <p class="text-[10px] text-gray-400">GET IT ON</p>
                                    <p class="text-sm font-semibold text-white">Google Play</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                            <input type="text" placeholder="Search content..." class="w-64 bg-gray-800 border border-gray-700 rounded-lg pl-10 pr-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        <button class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 rounded-lg hover:bg-gray-700 transition-colors text-sm">
                            <i data-lucide="globe" class="w-4 h-4"></i>
                            English
                        </button>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-gray-800 mt-8 pt-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-gray-500">© {{ date('Y') }} Ear Training Studio. All rights reserved.</p>
                    <div class="flex items-center gap-6 text-sm">
                        <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                        <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                        <a href="#" class="hover:text-white transition-colors">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>

