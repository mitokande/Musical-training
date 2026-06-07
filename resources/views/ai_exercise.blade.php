<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>AI Assisted Exercises - {{ config('app.name', 'Harmoniva') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@0.460.0"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>

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
        .tempo-btn {
            transition: all 0.2s ease;
        }
        .tempo-btn:hover {
            border-color: #c084fc;
        }
        .tempo-btn.selected {
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
        .music-note {
            position: absolute;
            color: #9333ea;
            pointer-events: none;
            user-select: none;
            line-height: 1;
        }
    </style>
</head>
<body class="font-sans bg-gray-50 min-h-screen">
    {{-- Navbar --}}
    @include('partials.navbar', ['active' => 'ai'])

    <!-- Main Content -->
    <main class="hero-gradient min-h-[calc(100vh-64px)] py-6 relative overflow-hidden">

        <!-- ── Sol zemin: 5 nota sembolü ── -->
        <div class="music-note" style="top:6%;  left:2%;   font-size:2.2rem; opacity:0.13; transform:rotate(-15deg);">♪</div>
        <div class="music-note" style="top:22%; left:6%;   font-size:3.4rem; opacity:0.08; transform:rotate(8deg);">♫</div>
        <div class="music-note" style="top:45%; left:1.5%; font-size:2.8rem; opacity:0.11; transform:rotate(-5deg);">♩</div>
        <div class="music-note" style="top:65%; left:7%;   font-size:2rem;   opacity:0.09; transform:rotate(12deg);">♬</div>
        <div class="music-note" style="top:83%; left:3%;   font-size:3rem;   opacity:0.12; transform:rotate(-10deg);">♪</div>

        <!-- ── Sağ zemin: 5 nota sembolü ── -->
        <div class="music-note" style="top:8%;  right:3%;  font-size:3rem;   opacity:0.10; transform:rotate(10deg);">♫</div>
        <div class="music-note" style="top:28%; right:7%;  font-size:2.4rem; opacity:0.13; transform:rotate(-8deg);">♬</div>
        <div class="music-note" style="top:50%; right:2%;  font-size:2rem;   opacity:0.08; transform:rotate(15deg);">♩</div>
        <div class="music-note" style="top:68%; right:6%;  font-size:3.2rem; opacity:0.11; transform:rotate(-12deg);">♪</div>
        <div class="music-note" style="top:85%; right:4%;  font-size:2.6rem; opacity:0.09; transform:rotate(6deg);">♫</div>

        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Header Section -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center gap-3 mb-3">
                    <h1 class="text-3xl sm:text-4xl font-bold text-purple-600">
                        AI Assisted Exercises
                    </h1>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-600 to-purple-400 flex items-center justify-center shadow-lg shadow-purple-200 flex-shrink-0">
                        <i data-lucide="sparkles" class="w-6 h-6 text-white"></i>
                    </div>
                </div>
                <p class="text-gray-600 max-w-md mx-auto">
                    Create a personalized practice session tailored to your specific needs and goals.
                </p>
            </div>

            <!-- Error Messages -->
            @if(session('error'))
                <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700">
                    <div class="flex items-center gap-2 mb-2">
                        <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="text-sm font-semibold">Please fix the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside text-sm space-y-1 ml-7">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

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
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($practices as $practice)
                                @if($practice->slug === 'interval-direction-practice')
                                    @continue
                                @endif
                                @php
                                    $isNew = in_array($practice->slug, ['improvisation', 'composition', 'mini-project']);
                                    if ($practice->slug === 'rhythm-practice') {
                                        $displayName = 'Rhythm Dictation';
                                    } else {
                                        $displayName = preg_replace('/\s+Practice$/i', '', $practice->name);
                                    }
                                @endphp
                                <label class="checkbox-card flex items-center gap-3 p-3 border border-gray-200 rounded-lg" data-checkbox data-slug="{{ $practice->slug }}">
                                    <input type="checkbox"
                                           name="exercise_types[]"
                                           value="{{ $practice->id }}"
                                           class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500 focus:ring-offset-0">
                                    <span class="checkbox-label text-sm text-gray-700">
                                        {{ $displayName }}
                                        @if($isNew)
                                            <span class="text-purple-600 font-medium">(New!)</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Rhythm Dictation options (shown only when Rhythm Dictation is selected) -->
                    <div id="rhythmOptions" class="mb-6 hidden">
                        <div class="rounded-lg border border-purple-200 bg-purple-50/60 p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <i data-lucide="music-2" class="w-4 h-4 text-purple-600"></i>
                                <span class="text-sm font-semibold text-gray-900">Rhythm Dictation Settings</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Time Signature</label>
                                    <select name="rhythm_time_signature" class="select-input w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                        <option value="2/4">2/4</option>
                                        <option value="3/4">3/4</option>
                                        <option value="4/4" selected>4/4</option>
                                        <option value="6/8">6/8</option>
                                        <option value="9/8">9/8</option>
                                        <option value="2/2">2/2</option>
                                        <option value="3/2">3/2</option>
                                        <option value="4/2">4/2</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Tempo (BPM)</label>
                                    <div class="grid grid-cols-4 gap-2">
                                        @foreach ([60, 80, 90, 100] as $bpm)
                                            <button type="button"
                                                    class="tempo-btn px-3 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 {{ $bpm === 90 ? 'selected' : '' }}"
                                                    data-tempo="{{ $bpm }}">
                                                {{ $bpm }}
                                            </button>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="rhythm_tempo" id="rhythmTempo" value="90">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Number of Questions -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Number of Questions</label>
                        <select name="num_questions" class="select-input w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="5" selected>5 Questions (Quick)</option>
                            <option value="10" >10 Questions (Standard)</option>
                            <option value="15">15 Questions (Extended)</option>
                            <option value="20">20 Questions (Comprehensive)</option>
                        </select>
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

    @include('partials.footer')

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
        
        // Checkbox card selection styling
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxCards = document.querySelectorAll('[data-checkbox]');
            
            // Show the Rhythm Dictation settings panel only while its type is selected.
            const rhythmOptions = document.getElementById('rhythmOptions');
            const rhythmCard = document.querySelector('[data-slug="rhythm-practice"]');
            const syncRhythmOptions = function() {
                if (!rhythmOptions || !rhythmCard) return;
                const cb = rhythmCard.querySelector('input[type="checkbox"]');
                rhythmOptions.classList.toggle('hidden', !(cb && cb.checked));
            };

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

                    syncRhythmOptions();
                });
            });
            syncRhythmOptions();

            // Predefined tempo selection (60 / 80 / 90 / 100 BPM).
            const tempoBtns = document.querySelectorAll('.tempo-btn');
            const rhythmTempoInput = document.getElementById('rhythmTempo');
            tempoBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    tempoBtns.forEach(b => b.classList.remove('selected'));
                    this.classList.add('selected');
                    if (rhythmTempoInput) rhythmTempoInput.value = this.dataset.tempo;
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

