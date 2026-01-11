<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $slug ?? 'Practice' }} - {{ config('app.name', 'Ear Training Studio') }}</title>

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
            background: linear-gradient(135deg, #ec4899 0%, #f472b6 50%, #fda4af 100%);
        }
        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #ec4899 0%, #f472b6 100%);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #db2777 0%, #ec4899 100%);
        }
        .progress-bar {
            background: linear-gradient(90deg, #22c55e 0%, #4ade80 100%);
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
        .answer-btn {
            transition: all 0.2s ease;
        }
        .answer-btn:hover {
            border-color: #ec4899;
            background: #fdf2f8;
        }
        .answer-btn.selected {
            border-color: #ec4899;
            background: #fdf2f8;
        }
        .answer-btn.correct {
            border-color: #22c55e;
            background: #f0fdf4;
        }
        .answer-btn.incorrect {
            border-color: #ef4444;
            background: #fef2f2;
        }
    </style>
    @livewireStyles
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
                    <a href="/learn" class="nav-item flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-600">
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
                    <a href="/progress" class="nav-item flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-600">
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

    @switch ($slug)
        @case ('single-note-practice')
            <livewire:practice-single-note :practices="$practices" />
            @break
        @case ('interval-direction-practice')
            <livewire:practice-interval-direction :practices="$practices" />
            @break
        @case ('interval-comparison-practice')
            <livewire:practice-interval-comparison :practices="$practices" />
            @break
    @endswitch



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
        
        // Practice interaction logic (placeholder)
        document.addEventListener('DOMContentLoaded', function() {
            const playButton = document.getElementById('playButton');
            const answerButtons = document.querySelectorAll('.answer-btn');
            
            // Play button click handler
            playButton.addEventListener('click', function() {
                // TODO: Play the interval sound
                console.log('Playing interval...');
                this.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5"></i> Playing...';
                lucide.createIcons();
                
                setTimeout(() => {
                    this.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Play Again';
                    lucide.createIcons();
                }, 2000);
            });
            
            // Answer button click handlers
            answerButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Remove selected state from all buttons
                    answerButtons.forEach(b => b.classList.remove('selected'));
                    
                    // Add selected state to clicked button
                    this.classList.add('selected');
                    
                    // TODO: Check answer and update score
                    console.log('Selected answer:', this.dataset.answer);
                });
            });
        });
    </script>
    @livewireScripts
</body>
</html>

