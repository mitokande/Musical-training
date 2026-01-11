<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Mixed Practice Setup - {{ config('app.name', 'Ear Training Studio') }}</title>

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
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 50%, #c084fc 100%);
        }
        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #6b21a8 0%, #7c3aed 100%);
        }
        .exercise-checkbox:checked + label {
            border-color: #7c3aed;
            background-color: #f3e8ff;
        }
        .exercise-checkbox:checked + label .check-icon {
            display: flex;
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
                    <a href="/learn" class="nav-item flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-600">
                        <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                        Learn Path
                    </a>
                </nav>

                <!-- User Menu -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold text-sm">
                            {{ substr(Auth::user()->name ?? 'M', 0, 1) }}
                        </div>
                        <span class="hidden sm:block text-sm font-medium text-gray-700">{{ Auth::user()->name ?? 'Guest' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                <i data-lucide="shuffle" class="w-8 h-8 text-white"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Mixed Practice Session</h1>
            <p class="text-gray-600">Customize your practice session with multiple exercise types</p>
        </div>

        <!-- Setup Form -->
        <form action="{{ route('practice.mixed.start') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Exercise Types Selection -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="list-checks" class="w-5 h-5 text-purple-600"></i>
                    Select Exercise Types
                </h2>
                
                <div class="space-y-3">
                    <!-- Single Note -->
                    <div>
                        <input type="checkbox" id="single_note" name="exercise_types[]" value="single_note" class="exercise-checkbox sr-only" checked>
                        <label for="single_note" class="flex items-center gap-4 p-4 rounded-xl border-2 border-gray-200 cursor-pointer hover:border-purple-300 transition-all">
                            <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="music-2" class="w-6 h-6 text-purple-600"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">Single Note Recognition</h3>
                                <p class="text-sm text-gray-500">Identify individual notes by ear</p>
                            </div>
                            <div class="check-icon hidden w-8 h-8 rounded-full bg-purple-600 items-center justify-center">
                                <i data-lucide="check" class="w-5 h-5 text-white"></i>
                            </div>
                        </label>
                    </div>

                    <!-- Interval Direction -->
                    <div>
                        <input type="checkbox" id="interval_direction" name="exercise_types[]" value="interval_direction" class="exercise-checkbox sr-only" checked>
                        <label for="interval_direction" class="flex items-center gap-4 p-4 rounded-xl border-2 border-gray-200 cursor-pointer hover:border-purple-300 transition-all">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="arrow-up-down" class="w-6 h-6 text-blue-600"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">Interval Direction</h3>
                                <p class="text-sm text-gray-500">Determine if intervals go up or down</p>
                            </div>
                            <div class="check-icon hidden w-8 h-8 rounded-full bg-purple-600 items-center justify-center">
                                <i data-lucide="check" class="w-5 h-5 text-white"></i>
                            </div>
                        </label>
                    </div>
                    
                    <!-- Interval Comparison -->
                    <div>
                        <input type="checkbox" id="interval_comparison" name="exercise_types[]" value="interval_comparison" class="exercise-checkbox sr-only" checked>
                        <label for="interval_comparison" class="flex items-center gap-4 p-4 rounded-xl border-2 border-gray-200 cursor-pointer hover:border-purple-300 transition-all">
                            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="compare-horizontal" class="w-6 h-6 text-green-600"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">Interval Comparison</h3>
                                <p class="text-sm text-gray-500">Compare the distance of two intervals</p>
                            </div>
                            <div class="check-icon hidden w-8 h-8 rounded-full bg-purple-600 items-center justify-center">
                                <i data-lucide="check" class="w-5 h-5 text-white"></i>
                            </div>
                        </label>
                    </div>
                    
                </div>
                @error('exercise_types')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Number of Questions -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="hash" class="w-5 h-5 text-purple-600"></i>
                    Number of Questions
                </h2>
                
                <div class="flex items-center gap-4">
                    <input 
                        type="range" 
                        name="num_questions" 
                        id="num_questions" 
                        min="5" 
                        max="30" 
                        value="10" 
                        class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-purple-600"
                        oninput="document.getElementById('num_display').textContent = this.value"
                    >
                    <div class="w-16 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                        <span id="num_display" class="text-xl font-bold text-purple-600">10</span>
                    </div>
                </div>
                
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                    <span>5 questions</span>
                    <span>30 questions</span>
                </div>
            </div>

            <!-- Session Title (Optional) -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="type" class="w-5 h-5 text-purple-600"></i>
                    Session Title <span class="text-sm font-normal text-gray-400">(Optional)</span>
                </h2>
                
                <input 
                    type="text" 
                    name="title" 
                    placeholder="e.g., Morning Warm-up, Interval Focus..."
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                >
            </div>

            <!-- Start Button -->
            <button 
                type="submit" 
                class="w-full btn-primary text-white font-semibold py-4 px-8 rounded-xl flex items-center justify-center gap-2 hover:shadow-lg transition-all"
            >
                <i data-lucide="play" class="w-5 h-5"></i>
                Start Practice Session
            </button>

            <!-- Back Link -->
            <div class="text-center">
                <a href="/learn" class="text-sm text-gray-500 hover:text-purple-600 transition-colors">
                    ← Back to Learning Path
                </a>
            </div>
        </form>
    </main>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
        
        // Initialize checkbox states
        document.querySelectorAll('.exercise-checkbox').forEach(checkbox => {
            if (checkbox.checked) {
                checkbox.dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>
</html>

