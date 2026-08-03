<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('pages.ai_exercises.meta_title') }} - {{ config('app.name', 'Harmoniva') }}</title>

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
            background: #f5f3ff;
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

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Header Section -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center gap-3 mb-3">
                    <h1 class="text-3xl sm:text-4xl font-bold text-purple-600">
                        {{ __('pages.ai_exercises.hero_title') }}
                    </h1>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-600 to-purple-400 flex items-center justify-center shadow-lg shadow-purple-200 flex-shrink-0">
                        <i data-lucide="sparkles" class="w-6 h-6 text-white"></i>
                    </div>
                </div>
                <p class="text-gray-600 max-w-md mx-auto">
                    {{ __('pages.ai_exercises.hero_subtitle') }}
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
                        <span class="text-sm font-semibold">{{ __('pages.ai_exercises.errors_title') }}</span>
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
                        <h2 class="font-semibold text-gray-900">{{ __('pages.ai_exercises.section_title') }}</h2>
                    </div>
                    <p class="text-sm text-gray-500 mb-6">{{ __('pages.ai_exercises.section_subtitle') }}</p>

                    <!-- Exercise Types -->
                    <div class="mb-6">
                        @php $aiExNames = trans('pages.ai_exercises.ex_names'); @endphp
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
                                    // Localized override (English falls back to the DB-derived name above).
                                    if (is_array($aiExNames) && isset($aiExNames[$practice->slug])) {
                                        $displayName = $aiExNames[$practice->slug];
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
                                            <span class="text-purple-600 font-medium">{{ __('pages.ai_exercises.new_badge') }}</span>
                                        @endif
                                    </span>
                                </label>
                                @if($practice->slug === 'rhythm-practice')
                                    {{-- Rhythm Recognition & Rhythm Reading: Exercise Setup Studio's other
                                         two rhythm modes, offered as their own AI exercise types. They have
                                         no Practice DB record — AIController maps them onto rhythm-practice
                                         generation with a different answer UI. --}}
                                    <label class="checkbox-card flex items-center gap-3 p-3 border border-gray-200 rounded-lg" data-checkbox data-slug="rhythm-recognition">
                                        <input type="checkbox"
                                               name="rhythm_modes[]"
                                               value="recognition"
                                               class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500 focus:ring-offset-0">
                                        <span class="checkbox-label text-sm text-gray-700">{{ __('pages.ai_exercises.rhythm_recognition_label') }}</span>
                                    </label>
                                    <label class="checkbox-card flex items-center gap-3 p-3 border border-gray-200 rounded-lg" data-checkbox data-slug="rhythm-reading">
                                        <input type="checkbox"
                                               name="rhythm_modes[]"
                                               value="reading"
                                               class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500 focus:ring-offset-0">
                                        <span class="checkbox-label text-sm text-gray-700">{{ __('pages.ai_exercises.rhythm_reading_label') }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Number of Questions + Difficulty Mode (side-by-side on desktop) -->
                    <div class="mb-6 flex flex-col lg:flex-row gap-6 lg:gap-4">
                        <!-- Number of Questions -->
                        <div class="lg:order-last lg:flex-[0.5]">
                            <label class="block text-sm font-semibold text-gray-900 mb-2">{{ __('pages.ai_exercises.num_questions_label') }}</label>
                            <select name="num_questions" class="select-input w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="5" selected>{{ __('pages.ai_exercises.q5') }}</option>
                                <option value="10">{{ __('pages.ai_exercises.q10') }}</option>
                                <option value="15">{{ __('pages.ai_exercises.q15') }}</option>
                                <option value="20">{{ __('pages.ai_exercises.q20') }}</option>
                            </select>
                        </div>

                        <!-- Difficulty Mode -->
                        <div class="lg:flex-1">
                            <label class="block text-sm font-semibold text-gray-900 mb-3">{{ __('pages.ai_exercises.difficulty_label') }}</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <button type="button" class="difficulty-btn px-2 sm:px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700" data-difficulty="easy">
                                    {{ __('pages.ai_exercises.diff_easy') }}
                                </button>
                                <button type="button" class="difficulty-btn px-2 sm:px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700" data-difficulty="medium">
                                    {{ __('pages.ai_exercises.diff_medium') }}
                                </button>
                                <button type="button" class="difficulty-btn px-2 sm:px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700" data-difficulty="hard">
                                    {{ __('pages.ai_exercises.diff_hard') }}
                                </button>
                                <button type="button" class="difficulty-btn selected px-2 sm:px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700" data-difficulty="adaptive">
                                    {{ __('pages.ai_exercises.diff_adaptive') }}
                                </button>
                            </div>
                            <input type="hidden" name="difficulty" id="difficultyInput" value="adaptive">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submitBtn" class="w-full btn-primary text-white font-semibold py-3.5 px-6 rounded-lg flex items-center justify-center gap-2 shadow-lg shadow-purple-200 hover:shadow-xl transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed">
                        <i data-lucide="{{ $canUseAi ? 'sparkles' : 'lock' }}" class="w-5 h-5 btn-icon"></i>
                        <svg class="animate-spin w-5 h-5 btn-spinner hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="btn-text">{{ $canUseAi ? __('pages.ai_exercises.submit_start') : __('pages.ai_exercises.submit_locked') }}</span>
                    </button>
                    @unless($canUseAi)
                        <p class="mt-3 text-center text-xs text-gray-500">
                            {!! __('pages.ai_exercises.premium_note', ['premium' => '<span class="font-semibold text-purple-600">'.__('pages.ai_exercises.premium_word').'</span>']) !!}
                        </p>
                    @endunless
                </form>
            </div>
        </div>
    </main>

    @unless($canUseAi)
    {{-- Premium gate banner: free users can configure a session but starting it
         opens this upgrade prompt instead of generating (POST route is also
         hard-gated by 'plan:ai_exercises'). --}}
    <div id="premiumModal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" data-premium-close></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="px-6 pt-7 pb-6 text-center">
                <div class="mx-auto w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-600 to-purple-400 flex items-center justify-center shadow-lg shadow-purple-200 mb-4">
                    <i data-lucide="crown" class="w-7 h-7 text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('pages.ai_exercises.modal_title') }}</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    {!! __('pages.ai_exercises.modal_desc', ['premium' => '<span class="font-semibold text-purple-600">'.__('pages.ai_exercises.modal_premium_brand').'</span>']) !!}
                </p>
                <div class="mt-6 flex flex-col gap-2.5">
                    <a href="{{ route('checkout.show') }}" class="w-full btn-primary text-white font-semibold py-3 px-6 rounded-lg flex items-center justify-center gap-2 shadow-lg shadow-purple-200 hover:shadow-xl transition-all">
                        <i data-lucide="sparkles" class="w-4.5 h-4.5"></i>
                        <span>{{ __('pages.ai_exercises.modal_unlock') }}</span>
                    </a>
                    <button type="button" data-premium-close class="w-full py-2.5 px-6 rounded-lg text-sm font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-50 transition-all">
                        {{ __('pages.ai_exercises.modal_later') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endunless

    @include('partials.footer')

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
            const canUseAi = @json($canUseAi);

            sessionForm.addEventListener('submit', function(e) {
                // Free users: intercept and show the upgrade banner instead of generating.
                if (!canUseAi) {
                    e.preventDefault();
                    const modal = document.getElementById('premiumModal');
                    if (modal) modal.classList.remove('hidden');
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.querySelector('.btn-icon').classList.add('hidden');
                submitBtn.querySelector('.btn-spinner').classList.remove('hidden');
                submitBtn.querySelector('.btn-text').textContent = @json(__('pages.ai_exercises.generating'));
            });

            // Premium banner dismissal (backdrop + "Maybe later").
            document.querySelectorAll('[data-premium-close]').forEach(el => {
                el.addEventListener('click', function() {
                    document.getElementById('premiumModal').classList.add('hidden');
                });
            });
        });
    </script>
</body>
</html>

