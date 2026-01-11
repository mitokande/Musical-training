<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>My Progress - {{ config('app.name', 'Ear Training Studio') }}</title>

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
        .progress-bar-green {
            background: linear-gradient(90deg, #22c55e 0%, #4ade80 100%);
        }
        .progress-bar-orange {
            background: linear-gradient(90deg, #f97316 0%, #fbbf24 100%);
        }
        .progress-bar-blue {
            background: linear-gradient(90deg, #3b82f6 0%, #60a5fa 100%);
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
        .ring-chart {
            --progress: 0;
            background: conic-gradient(#9333ea calc(var(--progress) * 1%), #e5e7eb 0);
            border-radius: 50%;
        }
        .stat-card {
            transition: all 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }
        .chart-bar {
            transition: height 0.3s ease;
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
                    <a href="/ai-exercises" class="nav-item flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-600">
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
                    <a href="/progress" class="nav-item active flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-700">
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
                        <span class="hidden sm:block text-sm font-medium text-gray-700">{{ Auth::user()->name ?? 'User' }}</span>
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
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="bar-chart-2" class="w-6 h-6 text-white"></i>
                        <span class="px-3 py-1 bg-white/20 text-white text-xs font-semibold rounded-full">Statistics</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">
                        My Progress
                    </h1>
                    <p class="text-white/80">Track your ear training journey and see how far you've come</p>
                </div>
                
                <!-- Overall Accuracy Ring -->
                <div class="mt-6 md:mt-0 flex flex-col items-center">
                    <div class="relative w-32 h-32">
                        <!-- Ring Chart -->
                        <div class="ring-chart w-full h-full flex items-center justify-center" style="--progress: {{ $overallAccuracy }}">
                            <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-white">{{ $overallAccuracy }}%</div>
                                    <div class="text-xs text-white/80">Accuracy</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="text-white/80 text-sm mt-2">Overall Performance</p>
                </div>
            </div>
        </div>

        <!-- Summary Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Sessions -->
            <div class="stat-card card p-5">
                <div class="flex items-start justify-between mb-3">
                    <span class="text-sm text-gray-500">Practice Sessions</span>
                    <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                        <i data-lucide="play-circle" class="w-4 h-4 text-purple-600"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">{{ $totalSessions }}</div>
                <p class="text-sm text-gray-500">sessions completed</p>
            </div>

            <!-- Total Questions -->
            <div class="stat-card card p-5">
                <div class="flex items-start justify-between mb-3">
                    <span class="text-sm text-gray-500">Questions Answered</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                        <i data-lucide="help-circle" class="w-4 h-4 text-blue-600"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">{{ $totalQuestions }}</div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-green-600">{{ $totalCorrect }} correct</span>
                    <span class="text-gray-300">|</span>
                    <span class="text-red-500">{{ $totalIncorrect }} wrong</span>
                </div>
            </div>

            <!-- Total Time -->
            <div class="stat-card card p-5">
                <div class="flex items-start justify-between mb-3">
                    <span class="text-sm text-gray-500">Time Practiced</span>
                    <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                        <i data-lucide="clock" class="w-4 h-4 text-orange-600"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">{{ $formattedTime }}</div>
                <p class="text-sm text-gray-500">total practice time</p>
            </div>

            <!-- Streak -->
            <div class="stat-card card p-5">
                <div class="flex items-start justify-between mb-3">
                    <span class="text-sm text-gray-500">Current Streak</span>
                    <span class="text-2xl">🔥</span>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">{{ $streak }}</div>
                <p class="text-sm text-gray-500">{{ $streak == 1 ? 'day' : 'days' }} in a row</p>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="grid lg:grid-cols-3 gap-6 mb-8">
            <!-- Left Column - Practice Type Breakdown -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Practice Type Cards -->
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <i data-lucide="layers" class="w-5 h-5 text-purple-600"></i>
                            <h3 class="font-semibold text-gray-900">Practice Type Breakdown</h3>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        @forelse($practiceBreakdown as $index => $practice)
                            @php
                                $colors = [
                                    0 => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'bar' => 'progress-bar'],
                                    1 => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600', 'bar' => 'progress-bar-orange'],
                                    2 => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'bar' => 'progress-bar-blue'],
                                ];
                                $color = $colors[$index % 3];
                            @endphp
                            <div class="p-4 bg-gray-50 rounded-xl">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg {{ $color['bg'] }} flex items-center justify-center">
                                            <i data-lucide="music" class="w-5 h-5 {{ $color['text'] }}"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $practice['name'] }}</h4>
                                            <p class="text-sm text-gray-500">{{ $practice['sessions'] }} sessions</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-bold {{ $color['text'] }}">{{ $practice['accuracy'] }}%</div>
                                        <p class="text-xs text-gray-500">accuracy</p>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-3 gap-4 mb-3">
                                    <div class="text-center p-2 bg-white rounded-lg">
                                        <div class="text-lg font-semibold text-gray-900">{{ $practice['total_questions'] }}</div>
                                        <p class="text-xs text-gray-500">Questions</p>
                                    </div>
                                    <div class="text-center p-2 bg-white rounded-lg">
                                        <div class="text-lg font-semibold text-green-600">{{ $practice['correct_answers'] }}</div>
                                        <p class="text-xs text-gray-500">Correct</p>
                                    </div>
                                    <div class="text-center p-2 bg-white rounded-lg">
                                        <div class="text-lg font-semibold text-gray-900">{{ $practice['avg_time'] }}s</div>
                                        <p class="text-xs text-gray-500">Avg Time</p>
                                    </div>
                                </div>
                                
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="{{ $color['bar'] }} h-2 rounded-full transition-all duration-500" style="width: {{ $practice['accuracy'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                                    <i data-lucide="music" class="w-8 h-8 text-gray-400"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900 mb-2">No practice data yet</h4>
                                <p class="text-gray-500 text-sm mb-4">Start practicing to see your progress here!</p>
                                <a href="/learn" class="inline-flex items-center gap-2 px-4 py-2 btn-primary text-white rounded-lg font-medium">
                                    <i data-lucide="play" class="w-4 h-4"></i>
                                    Start Learning
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Weekly Performance Chart -->
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <i data-lucide="trending-up" class="w-5 h-5 text-purple-600"></i>
                            <h3 class="font-semibold text-gray-900">Weekly Performance</h3>
                        </div>
                        <span class="text-sm text-gray-500">Last 7 days</span>
                    </div>
                    
                    <!-- Simple Bar Chart -->
                    <div class="flex items-end justify-between gap-2 h-40 mb-4">
                        @foreach($weeklyPerformance as $day)
                            <div class="flex-1 flex flex-col items-center">
                                <div class="w-full bg-gray-100 rounded-t-lg relative" style="height: 120px;">
                                    @if($day['questions'] > 0)
                                        <div class="chart-bar absolute bottom-0 w-full bg-gradient-to-t from-purple-600 to-purple-400 rounded-t-lg transition-all duration-300" 
                                             style="height: {{ max(10, $day['accuracy']) }}%"
                                             title="{{ $day['accuracy'] }}% accuracy">
                                        </div>
                                    @endif
                                </div>
                                <div class="mt-2 text-center">
                                    <div class="text-xs font-medium text-gray-900">{{ $day['day'] }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $day['questions'] }}q</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="flex items-center justify-center gap-6 text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded bg-purple-500"></div>
                            <span class="text-gray-600">Accuracy %</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Performance Insights -->
                <div class="card p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <i data-lucide="lightbulb" class="w-5 h-5 text-yellow-500"></i>
                        <h3 class="font-semibold text-gray-900">Performance Insights</h3>
                    </div>
                    
                    @if($bestArea && $bestArea['sessions'] > 0)
                        <!-- Best Area -->
                        <div class="p-4 bg-green-50 border border-green-100 rounded-xl mb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="trophy" class="w-4 h-4 text-green-600"></i>
                                <span class="text-sm font-medium text-green-800">Strongest Area</span>
                            </div>
                            <p class="text-green-900 font-semibold">{{ $bestArea['name'] }}</p>
                            <p class="text-sm text-green-700">{{ $bestArea['accuracy'] }}% accuracy</p>
                        </div>
                    @endif
                    
                    @if($weakestArea && $weakestArea['sessions'] > 0 && (!$bestArea || $weakestArea['name'] !== $bestArea['name']))
                        <!-- Needs Improvement -->
                        <div class="p-4 bg-orange-50 border border-orange-100 rounded-xl mb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="target" class="w-4 h-4 text-orange-600"></i>
                                <span class="text-sm font-medium text-orange-800">Focus Area</span>
                            </div>
                            <p class="text-orange-900 font-semibold">{{ $weakestArea['name'] }}</p>
                            <p class="text-sm text-orange-700">{{ $weakestArea['accuracy'] }}% accuracy - keep practicing!</p>
                        </div>
                    @endif
                    
                    @if(!$bestArea || $bestArea['sessions'] == 0)
                        <div class="text-center py-4">
                            <p class="text-gray-500 text-sm">Complete some practice sessions to see insights!</p>
                        </div>
                    @endif
                    
                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 gap-3 mt-4">
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <div class="text-xl font-bold text-purple-600">{{ $totalCorrect }}</div>
                            <p class="text-xs text-gray-500">Correct Answers</p>
                        </div>
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <div class="text-xl font-bold text-red-500">{{ $totalIncorrect }}</div>
                            <p class="text-xs text-gray-500">Incorrect</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card p-5">
                    <h3 class="font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    
                    <div class="space-y-2">
                        <a href="/learn" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                                <i data-lucide="play-circle" class="w-4 h-4 text-green-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-purple-600">Continue Learning</span>
                        </a>
                        
                        <a href="/ai-exercises" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                <i data-lucide="sparkles" class="w-4 h-4 text-purple-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-purple-600">AI Practice Session</span>
                        </a>
                        
                        <a href="/dashboard" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <i data-lucide="home" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-purple-600">Back to Dashboard</span>
                        </a>
                    </div>
                </div>

                <!-- Motivation Card -->
                <div class="bg-gradient-to-br from-purple-600 to-purple-800 rounded-xl p-6 text-white">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                        <span class="font-semibold">Keep Going!</span>
                    </div>
                    @if($totalSessions == 0)
                        <p class="text-white/90 text-sm mb-4">Start your ear training journey today. Every expert was once a beginner!</p>
                    @elseif($overallAccuracy >= 80)
                        <p class="text-white/90 text-sm mb-4">Amazing work! You're performing excellently. Keep up the great progress!</p>
                    @elseif($overallAccuracy >= 60)
                        <p class="text-white/90 text-sm mb-4">Good progress! You're on the right track. A little more practice and you'll master it!</p>
                    @else
                        <p class="text-white/90 text-sm mb-4">Every practice session makes you better. Keep practicing and you'll see improvement!</p>
                    @endif
                    <div class="flex items-center gap-2 text-white/70 text-xs">
                        <i data-lucide="info" class="w-3 h-3"></i>
                        <span>Practice daily for best results</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2">
                    <i data-lucide="history" class="w-5 h-5 text-purple-600"></i>
                    <h3 class="font-semibold text-gray-900">Recent Activity</h3>
                </div>
                <span class="text-sm text-gray-500">Last 10 sessions</span>
            </div>
            
            @if($recentActivity->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left border-b border-gray-200">
                                <th class="pb-3 text-sm font-medium text-gray-500">Date</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">Practice Type</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">Questions</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">Score</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">Accuracy</th>
                                <th class="pb-3 text-sm font-medium text-gray-500">Duration</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentActivity as $activity)
                                @php
                                    $activityAccuracy = $activity->total_questions > 0 
                                        ? round(($activity->correct_answers / $activity->total_questions) * 100, 1) 
                                        : 0;
                                    $duration = $activity->total_time >= 60 
                                        ? floor($activity->total_time / 60) . 'm ' . ($activity->total_time % 60) . 's'
                                        : $activity->total_time . 's';
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="py-4 text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($activity->created_at)->format('M d, Y') }}
                                        <span class="text-gray-500 text-xs block">{{ \Carbon\Carbon::parse($activity->created_at)->format('h:i A') }}</span>
                                    </td>
                                    <td class="py-4">
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">
                                            <i data-lucide="music" class="w-3 h-3"></i>
                                            {{ $activity->practice->name ?? 'Practice' }}
                                        </span>
                                    </td>
                                    <td class="py-4 text-sm text-gray-900">{{ $activity->total_questions }}</td>
                                    <td class="py-4 text-sm">
                                        <span class="text-green-600 font-medium">{{ $activity->correct_answers }}</span>
                                        <span class="text-gray-400">/</span>
                                        <span class="text-gray-600">{{ $activity->total_questions }}</span>
                                    </td>
                                    <td class="py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-16 bg-gray-200 rounded-full h-1.5">
                                                <div class="h-1.5 rounded-full {{ $activityAccuracy >= 70 ? 'bg-green-500' : ($activityAccuracy >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $activityAccuracy }}%"></div>
                                            </div>
                                            <span class="text-sm font-medium {{ $activityAccuracy >= 70 ? 'text-green-600' : ($activityAccuracy >= 50 ? 'text-yellow-600' : 'text-red-600') }}">{{ $activityAccuracy }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-4 text-sm text-gray-500">{{ $duration }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="calendar" class="w-8 h-8 text-gray-400"></i>
                    </div>
                    <h4 class="font-semibold text-gray-900 mb-2">No recent activity</h4>
                    <p class="text-gray-500 text-sm mb-4">Complete some practice sessions to see your activity history.</p>
                    <a href="/learn" class="inline-flex items-center gap-2 px-4 py-2 btn-primary text-white rounded-lg font-medium">
                        <i data-lucide="play" class="w-4 h-4"></i>
                        Start Practicing
                    </a>
                </div>
            @endif
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
                        <li><a href="/dashboard" class="hover:text-white transition-colors">Home</a></li>
                        <li><a href="/learn" class="hover:text-white transition-colors">Learning Path</a></li>
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

