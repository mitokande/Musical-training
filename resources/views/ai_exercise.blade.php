<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>AI Assisted Exercises - {{ config('app.name', 'Ear Training Studio') }}</title>

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
            background: linear-gradient(135deg, #f3e8ff 0%, #faf5ff 50%, #fff7ed 100%);
        }
        .card {
            background: white;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #7c3aed 0%, #6b21a8 100%);
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
        .checkbox-card {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .checkbox-card:hover {
            border-color: #c084fc;
        }
        .checkbox-card.selected {
            border-color: #9333ea;
            background: #faf5ff;
        }
        .checkbox-card input[type="checkbox"]:checked + .checkbox-label {
            color: #7c3aed;
        }
        .difficulty-btn {
            transition: all 0.2s ease;
        }
        .difficulty-btn:hover {
            border-color: #c084fc;
        }
        .difficulty-btn.selected {
            background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%);
            color: white;
            border-color: transparent;
        }
        .select-input {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.25rem 1.25rem;
        }
        /* Musical notes decoration */
        .music-note {
            position: absolute;
            opacity: 0.1;
            font-size: 3rem;
            color: #9333ea;
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
                    <a href="/dashboard" class="nav-item  flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-700">
                        <i data-lucide="home" class="w-4 h-4"></i>
                        Home
                    </a>
                    <a href="/learn" class="nav-item flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-600">
                        <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                        Learn Path
                    </a>
                    <a href="/ai-exercises" class="nav-item active flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-700">
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
    <main class="hero-gradient min-h-[calc(100vh-64px)] py-12 relative overflow-hidden">
        <!-- Decorative musical elements -->
        <div class="music-note" style="top: 10%; left: 5%;">♪</div>
        <div class="music-note" style="top: 30%; right: 8%;">♫</div>
        <div class="music-note" style="bottom: 20%; left: 10%;">♩</div>
        <div class="music-note" style="bottom: 40%; right: 5%;">♬</div>
        
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Header Section -->
            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-purple-600 to-purple-400 flex items-center justify-center mx-auto mb-5 shadow-lg shadow-purple-200">
                    <i data-lucide="sparkles" class="w-7 h-7 text-white"></i>
                </div>
                <h1 class="text-3xl sm:text-4xl font-bold text-purple-600 mb-3">
                    AI Assisted Exercises
                </h1>
                <p class="text-gray-600 max-w-md mx-auto">
                    Create a personalized practice session tailored to your specific needs and goals.
                </p>
            </div>

            <!-- Session Configuration Card -->
            <div class="card p-6 sm:p-8">
                <form action="/ai-exercises/generate" method="POST" id="sessionForm">
                    @csrf
                    
                    <!-- Section Header -->
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center">
                            <i data-lucide="settings" class="w-3.5 h-3.5 text-purple-600"></i>
                        </div>
                        <h2 class="font-semibold text-gray-900">Session Configuration</h2>
                    </div>
                    <p class="text-sm text-gray-500 mb-6">Tell us what you want to practice</p>

                    <!-- Exercise Types -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-900 mb-3">Exercise Types</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($practices as $practice)
                                @php
                                    $isNew = in_array($practice->slug, ['improvisation', 'composition', 'mini-project']);
                                @endphp
                                <label class="checkbox-card flex items-center gap-3 p-3 border border-gray-200 rounded-lg" data-checkbox>
                                    <input type="checkbox" 
                                           name="exercise_types[]" 
                                           value="{{ $practice->id }}" 
                                           class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500 focus:ring-offset-0">
                                    <span class="checkbox-label text-sm text-gray-700">
                                        {{ $practice->name }}
                                        @if($isNew)
                                            <span class="text-purple-600 font-medium">(New!)</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Number of Questions & Student Level -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">Number of Questions</label>
                            <select name="num_questions" class="select-input w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="5" selected>5 Questions (Quick)</option>
                                <option value="10" >10 Questions (Standard)</option>
                                <option value="15">15 Questions (Extended)</option>
                                <option value="20">20 Questions (Comprehensive)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">Student Level</label>
                            <select name="student_level" class="select-input w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="beginner">Beginner</option>
                                <option value="intermediate" selected>Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                    </div>

                    <!-- Difficulty Mode -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-900 mb-3">Difficulty Mode</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button type="button" class="difficulty-btn px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700" data-difficulty="easy">
                                Easy
                            </button>
                            <button type="button" class="difficulty-btn px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700" data-difficulty="medium">
                                Medium
                            </button>
                            <button type="button" class="difficulty-btn px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700" data-difficulty="hard">
                                Hard
                            </button>
                            <button type="button" class="difficulty-btn selected px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700" data-difficulty="adaptive">
                                Adaptive
                            </button>
                        </div>
                        <input type="hidden" name="difficulty" id="difficultyInput" value="adaptive">
                    </div>

                    <!-- Notes for AI Coach -->
                    <div class="mb-8">
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            <div class="flex items-center gap-2">
                                <i data-lucide="sparkles" class="w-4 h-4 text-purple-500"></i>
                                Notes for AI Coach (Optional)
                            </div>
                        </label>
                        <textarea name="coach_notes" 
                                  rows="3" 
                                  class="w-full px-4 py-3 border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                                  placeholder="e.g., I struggle with descending intervals, focus on minor 3rds..."></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submitBtn" class="w-full btn-primary text-white font-semibold py-3.5 px-6 rounded-lg flex items-center justify-center gap-2 shadow-lg shadow-purple-200 hover:shadow-xl transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed">
                        <i data-lucide="sparkles" class="w-5 h-5 btn-icon"></i>
                        <svg class="animate-spin w-5 h-5 btn-spinner hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="btn-text">Start AI Practice Session</span>
                    </button>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400">
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
                        <li><a href="/dashboard" class="hover:text-white transition-colors">Home</a></li>
                        <li><a href="/learn" class="hover:text-white transition-colors">Learning Path</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Quick Drills</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Piano Studio</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Music Games</a></li>
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
        
        // Checkbox card selection styling
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxCards = document.querySelectorAll('[data-checkbox]');
            
            checkboxCards.forEach(card => {
                const checkbox = card.querySelector('input[type="checkbox"]');
                
                // Set initial state
                if (checkbox.checked) {
                    card.classList.add('selected');
                }
                
                card.addEventListener('click', function(e) {
                    if (e.target !== checkbox) {
                        checkbox.checked = !checkbox.checked;
                    }
                    
                    if (checkbox.checked) {
                        card.classList.add('selected');
                    } else {
                        card.classList.remove('selected');
                    }
                });
            });
            
            // Difficulty buttons
            const difficultyBtns = document.querySelectorAll('.difficulty-btn');
            const difficultyInput = document.getElementById('difficultyInput');
            
            difficultyBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    difficultyBtns.forEach(b => b.classList.remove('selected'));
                    this.classList.add('selected');
                    difficultyInput.value = this.dataset.difficulty;
                });
            });
            
            // Form submission loading state
            const sessionForm = document.getElementById('sessionForm');
            const submitBtn = document.getElementById('submitBtn');
            
            sessionForm.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.querySelector('.btn-icon').classList.add('hidden');
                submitBtn.querySelector('.btn-spinner').classList.remove('hidden');
                submitBtn.querySelector('.btn-text').textContent = 'Generating Your Session...';
            });
        });
    </script>
</body>
</html>

