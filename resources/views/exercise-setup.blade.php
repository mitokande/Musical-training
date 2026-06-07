<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Exercise Setup Studio – {{ config('app.name', 'Harmoniva') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: { 50:'#faf5ff',100:'#f3e8ff',200:'#e9d5ff',300:'#d8b4fe',400:'#c084fc',500:'#a855f7',600:'#9333ea',700:'#7c3aed',800:'#6b21a8',900:'#581c87' },
                        accent: { 400:'#fb923c',500:'#f97316',600:'#ea580c' }
                    }
                }
            }
        }
    </script>

    <style>
        .hero-gradient { background: linear-gradient(135deg, #6d28d9 0%, #8b5cf6 50%, #a78bfa 100%); }
        .card { background:white; border-radius:16px; border:1px solid #ede9fe; box-shadow:0 2px 8px 0 rgb(109 40 217/0.06),0 1px 2px -1px rgb(0 0 0/0.06); }
        .card-header { background:linear-gradient(135deg,#f5f3ff 0%,#ede9fe 100%); margin:-1.5rem -1.5rem 1.25rem; padding:0.875rem 1.5rem; border-radius:15px 15px 0 0; border-bottom:1px solid #ddd6fe; }
        .card-header-neutral { background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%); border-bottom-color:#e2e8f0; }
        .card-header-orange { background:linear-gradient(135deg,#fff7ed 0%,#ffedd5 100%); border-bottom-color:#fed7aa; }
        .card-header-amber { background:linear-gradient(135deg,#fffbeb 0%,#fef3c7 100%); border-bottom-color:#fde68a; }
        .card-header-red { background:linear-gradient(135deg,#fff1f2 0%,#ffe4e6 100%); border-bottom-color:#fecdd3; }
        .card-header-pink { background:linear-gradient(135deg,#fdf2f8 0%,#fce7f3 100%); border-bottom-color:#fbcfe8; }
        .card-header-indigo { background:linear-gradient(135deg,#eef2ff 0%,#e0e7ff 100%); border-bottom-color:#c7d2fe; }
        .btn-primary { background:linear-gradient(135deg,#6d28d9 0%,#8b5cf6 100%); transition:all 0.2s; }
        .btn-primary:hover { background:linear-gradient(135deg,#5b21b6 0%,#7c3aed 100%); transform:translateY(-1px); box-shadow:0 8px 20px -4px rgb(109 40 217/0.4); }
        .category-card { transition:all 0.2s ease; cursor:pointer; }
        .category-card:hover { transform:translateY(-1px); box-shadow:0 8px 20px -4px rgb(0 0 0/0.12); border-color:#c4b5fd; }
        .category-card.active { border-color:#7c3aed; background:linear-gradient(135deg,#faf5ff,#f3e8ff); box-shadow:0 0 0 3px #ddd6fe; }
        .category-card.active .cat-icon { background:linear-gradient(135deg,#6d28d9,#8b5cf6); color:white; box-shadow:0 4px 12px rgb(109 40 217/0.3); }
        .toggle-btn { transition:all 0.2s; }
        .toggle-btn.active { background:linear-gradient(135deg,#1e293b,#334155); color:white; border-color:#475569; box-shadow:0 3px 10px rgb(30 41 59/0.3); }
        .interval-chip { transition:all 0.15s; }
        .interval-chip.selected { background:linear-gradient(135deg,#1e293b,#334155); color:white; border-color:#475569; box-shadow:0 2px 8px rgb(30 41 59/0.3); transform:scale(1.05); }
        .clef-btn { transition:all 0.2s; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; flex:1; padding:0.5rem; border-radius:0.75rem; border:1px solid #e5e7eb; font-size:0.75rem; font-weight:600; cursor:pointer; min-height:0; }
        .clef-btn:hover { border-color:#94a3b8; background:#f8fafc; }
        .clef-btn.active { background:linear-gradient(135deg,#1e293b,#334155); color:white; border-color:#475569; box-shadow:0 3px 10px rgb(30 41 59/0.3); }
        .clef-symbol { font-size:2.75rem; line-height:1; }
        .premium-lock { position:relative; }
        .premium-lock::after { content:''; position:absolute; inset:0; border-radius:8px; background:rgba(255,255,255,0.6); }
        input[type="range"] { accent-color:#334155; }
        .summary-badge { display:inline-flex; align-items:center; background:linear-gradient(135deg,#f8fafc,#f1f5f9); color:#334155; font-weight:700; padding:2px 10px; border-radius:999px; font-size:0.8rem; border:1px solid #cbd5e1; }
        .start-btn { background:linear-gradient(135deg,#16a34a 0%,#22c55e 100%); transition:all 0.3s; }
        .start-btn:hover { background:linear-gradient(135deg,#15803d 0%,#16a34a 100%); transform:translateY(-2px); box-shadow:0 12px 28px -6px rgb(22 163 74/0.5); }
        @keyframes pulse-ring { 0%,100%{box-shadow:0 0 0 0 rgb(22 163 74/0.4)} 50%{box-shadow:0 0 0 8px rgb(22 163 74/0)} }
        .start-btn:not(:hover) { animation:pulse-ring 2.5s ease-in-out infinite; }
        @media(max-width:768px) {
            .three-col { flex-direction:column; }
            .sidebar-col { width:100% !important; }
        }
    </style>
    @livewireStyles
</head>
<body class="font-sans bg-gray-50 min-h-screen"
      x-data="exerciseSetup()"
      x-init="init()"
      @ai-recommendation-ready.window="applyAiRecommendation($event.detail)"
      @apply-ai-recommendation.window="applyAiRecommendation($event.detail)"
      @load-plan.window="loadPlan($event.detail)">

    @include('partials.navbar', ['active' => 'setup-studio'])

    {{-- Premium Modal --}}
    <div x-show="showPremiumModal" x-transition
         class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
         @click.self="showPremiumModal = false">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full text-center">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="crown" class="w-8 h-8 text-white"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ __('app.exercises.premium_feature') }}</h2>
            <p class="text-gray-600 mb-6">{{ __('app.exercises.premium_desc') }}</p>
            <div class="flex gap-3">
                <a href="/plans" class="flex-1 btn-primary text-white font-semibold py-3 px-6 rounded-xl flex items-center justify-center gap-2">
                    <i data-lucide="crown" class="w-4 h-4"></i> {{ __('app.exercises.upgrade_to_premium') }}
                </a>
                <button @click="showPremiumModal = false"
                    class="flex-1 border border-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-xl hover:bg-gray-50 transition-colors">
                    {{ __('app.common.close') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Save Plan Modal --}}
    <div x-show="showSavePlanModal" x-transition
         class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
         @click.self="showSavePlanModal = false">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i data-lucide="bookmark-plus" class="w-5 h-5 text-purple-600"></i> {{ __('app.exercises.save_plan') }}
            </h3>
            <input x-model="savePlanName" type="text" placeholder="{{ __('app.exercises.plan_name_placeholder') }}"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500 mb-4"
                @keydown.enter="saveCurrentPlan()">
            <div x-show="savePlanError" class="text-sm text-red-600 mb-3" x-text="savePlanError"></div>
            <div class="flex gap-3">
                <button @click="saveCurrentPlan()"
                    :disabled="!savePlanName.trim() || savePlanLoading"
                    class="flex-1 btn-primary text-white font-semibold py-2.5 px-4 rounded-xl disabled:opacity-50 flex items-center justify-center gap-2">
                    <span x-show="!savePlanLoading">{{ __('app.common.save') }}</span>
                    <span x-show="savePlanLoading">{{ __('app.common.saving') }}</span>
                </button>
                <button @click="showSavePlanModal = false"
                    class="border border-gray-200 text-gray-600 font-medium py-2.5 px-4 rounded-xl hover:bg-gray-50">
                    {{ __('app.common.cancel') }}
                </button>
            </div>
        </div>
    </div>

    <!-- ===== HEADER ===== -->
    <div class="hero-gradient">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                            <i data-lucide="wand-sparkles" class="w-5 h-5 text-white"></i>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-white">Exercise Setup Studio</h1>
                    </div>
                    <p class="text-white/80 text-sm sm:text-base">{{ __('app.exercises.setup_subtitle') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @if($isPremium)
                        <button wire:target="getAiRecommendation"
                                onclick="document.querySelector('[wire\\\\:id]').__livewire.call('getAiRecommendation')"
                                class="bg-white/20 hover:bg-white/30 text-white font-semibold py-2.5 px-4 rounded-xl flex items-center gap-2 transition-colors border border-white/30 text-sm">
                            <i data-lucide="sparkles" class="w-4 h-4"></i> {{ __('app.exercises.ai_suggestion') }}
                        </button>
                    @else
                        <button @click="showPremiumModal = true"
                            class="bg-white/20 hover:bg-white/30 text-white font-semibold py-2.5 px-4 rounded-xl flex items-center gap-2 transition-colors border border-white/30 text-sm">
                            <i data-lucide="sparkles" class="w-4 h-4"></i> {{ __('app.exercises.ai_suggestion') }}
                            <span class="bg-yellow-400 text-yellow-900 text-xs font-bold px-1.5 rounded">PRO</span>
                        </button>
                    @endif
                    <button @click="showSavePlanModal = true"
                        class="bg-white/20 hover:bg-white/30 text-white font-semibold py-2.5 px-4 rounded-xl flex items-center gap-2 transition-colors border border-white/30 text-sm">
                        <i data-lucide="bookmark" class="w-4 h-4"></i> {{ __('app.exercises.save_plan') }}
                    </button>
                    <button @click="startExercise()"
                        class="bg-green-500 hover:bg-green-400 text-white font-bold py-2.5 px-6 rounded-xl flex items-center gap-2 transition-colors text-sm shadow-lg">
                        <i data-lucide="play" class="w-4 h-4"></i> {{ __('app.exercises.start') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== FLASH MESSAGES ===== -->
    @if(session('status') || $errors->any())
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        @if(session('status'))
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-green-700 text-sm">{{ session('status') }}</div>
        @endif
        @foreach($errors->all() as $error)
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700 text-sm mt-2">{{ $error }}</div>
        @endforeach
    </div>
    @endif

    <!-- ===== MAIN LAYOUT ===== -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex gap-6 three-col">

            <!-- ===== LEFT: Category Selector ===== -->
            <div class="w-72 flex-shrink-0 space-y-2 sidebar-col">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide px-1 mb-3">{{ __('app.exercises.category_select') }}</h2>

                @php
                $categories = [
                    ['key' => 'intervals',              'label' => 'Intervals',              'icon' => 'music-2',          'color' => 'purple'],
                    ['key' => 'single-note',            'label' => 'Single Note',            'icon' => 'music-2',          'color' => 'lime'],
                    ['key' => 'chords',                 'label' => 'Chords',                 'icon' => 'piano',            'color' => 'orange'],
                    ['key' => 'scales',                 'label' => 'Scales & Modes',         'icon' => 'waves',            'color' => 'amber'],
                    ['key' => 'rhythm',                 'label' => 'Rhythm',                 'icon' => 'drum',             'color' => 'red'],
                    ['key' => 'melodic-dictation',      'label' => 'Melodic Dictation',      'icon' => 'pencil-line',      'color' => 'pink'],
                ];
                $colorMap = [
                    'purple'=>['bg'=>'bg-purple-100','text'=>'text-purple-600'],
                    'blue'=>['bg'=>'bg-blue-100','text'=>'text-blue-600'],
                    'cyan'=>['bg'=>'bg-cyan-100','text'=>'text-cyan-600'],
                    'teal'=>['bg'=>'bg-teal-100','text'=>'text-teal-600'],
                    'green'=>['bg'=>'bg-green-100','text'=>'text-green-600'],
                    'lime'=>['bg'=>'bg-lime-100','text'=>'text-lime-600'],
                    'orange'=>['bg'=>'bg-orange-100','text'=>'text-orange-600'],
                    'amber'=>['bg'=>'bg-amber-100','text'=>'text-amber-600'],
                    'red'=>['bg'=>'bg-red-100','text'=>'text-red-600'],
                    'pink'=>['bg'=>'bg-pink-100','text'=>'text-pink-600'],
                    'indigo'=>['bg'=>'bg-indigo-100','text'=>'text-indigo-600'],
                ];
                @endphp

                @foreach($categories as $cat)
                @php $c = $colorMap[$cat['color']]; @endphp
                <div class="category-card card p-3 flex items-center gap-3 border-2 border-transparent"
                     :class="selectedCategory === '{{ $cat['key'] }}' ? 'active' : ''"
                     @click="selectedCategory = '{{ $cat['key'] }}'">
                    <div class="cat-icon w-9 h-9 rounded-xl {{ $c['bg'] }} {{ $c['text'] }} flex items-center justify-center flex-shrink-0 transition-all">
                        <i data-lucide="{{ $cat['icon'] }}" class="w-4 h-4"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900">{{ $cat['label'] }}</span>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400 ml-auto" :class="selectedCategory === '{{ $cat['key'] }}' ? 'text-purple-600' : ''"></i>
                </div>
                @endforeach
            </div>

            <!-- ===== CENTER: Settings Panel (45%) ===== -->
            <div class="flex-1 min-w-0 space-y-5">

                <!-- Chord Settings — shown BEFORE general settings when chords selected -->
                <div class="card p-6" x-show="selectedCategory === 'chords'" x-cloak>
                    <div class="card-header card-header-orange flex items-center gap-2">
                        <i data-lucide="piano" class="w-5 h-5 text-orange-700"></i>
                        <h2 class="text-base font-bold text-orange-900">{{ __('app.exercises.chord_settings') }}</h2>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('app.exercises.chord_types_label') }}</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['Major','Minor','Diminished','Augmented','Dominant 7th','Major 7th','Minor 7th','Half Diminished','Diminished 7th','Augmented 7th'] as $chord)
                                <button class="interval-chip py-2 px-3 text-sm font-semibold rounded-lg border border-gray-200 hover:border-orange-400 transition-all"
                                        :class="chordTypes.includes('{{ $chord }}') ? 'bg-orange-600 text-white border-orange-600' : ''"
                                        @click="toggleChordType('{{ $chord }}')">{{ $chord }}</button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Voicing (left) + Include Inversions (right) — 50/50 -->
                    <div class="flex gap-3">
                        <!-- Left 50%: Voicing — 2 options stacked -->
                        <div class="w-1/2 flex flex-col">
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Voicing</label>
                            <div class="flex flex-col gap-1.5 h-20">
                                <button class="flex-1 text-sm font-medium rounded-xl border transition-all"
                                        :class="voicing === 'block' ? 'bg-slate-800 text-white border-slate-700 shadow-md' : 'border-gray-200 text-gray-600 hover:border-slate-400'"
                                        @click="voicing = 'block'">🎹 Block</button>
                                <button class="flex-1 text-sm font-medium rounded-xl border transition-all"
                                        :class="voicing === 'arpeggiated' ? 'bg-slate-800 text-white border-slate-700 shadow-md' : 'border-gray-200 text-gray-600 hover:border-slate-400'"
                                        @click="voicing = 'arpeggiated'">🎸 Arpeggio</button>
                            </div>
                        </div>
                        <!-- Right 50%: Include Inversions -->
                        <div class="w-1/2 flex flex-col">
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5">{{ __('app.exercises.include_inversions') }}</label>
                            <div class="flex-1 flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                <p class="text-xs text-gray-500 leading-snug">{{ __('app.exercises.inversions_desc') }}</p>
                                <button @click="includeInversions = !includeInversions"
                                    class="relative flex-shrink-0 ml-3 w-12 h-6 rounded-full transition-colors"
                                    :class="includeInversions ? 'bg-slate-700' : 'bg-gray-300'">
                                    <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform"
                                          :class="includeInversions ? 'translate-x-6' : 'translate-x-0'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rhythm Settings — shown BEFORE general settings when rhythm selected -->
                <div class="card p-6" x-show="selectedCategory === 'rhythm'" x-cloak>
                    <div class="card-header card-header-red flex items-center gap-2">
                        <i data-lucide="drum" class="w-5 h-5 text-red-700"></i>
                        <h2 class="text-base font-bold text-red-900">{{ __('app.exercises.rhythm_settings') }}</h2>
                    </div>

                    <!-- Mode Selector -->
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Rhythm Mode</label>
                        <div class="flex gap-2">
                            <button class="flex-1 py-2.5 text-sm font-semibold rounded-xl border-2 transition-all flex items-center justify-center gap-2"
                                    :class="rhythmMode === 'dictation' ? 'bg-red-600 text-white border-red-600 shadow-md' : 'border-gray-200 text-gray-600 hover:border-red-300'"
                                    @click="rhythmMode = 'dictation'">
                                <i data-lucide="headphones" class="w-4 h-4"></i>
                                Rhythm Dictation
                            </button>
                            <button class="flex-1 py-2.5 text-sm font-semibold rounded-xl border-2 transition-all flex items-center justify-center gap-2"
                                    :class="rhythmMode === 'reading' ? 'bg-red-600 text-white border-red-600 shadow-md' : 'border-gray-200 text-gray-600 hover:border-red-300'"
                                    @click="rhythmMode = 'reading'">
                                <i data-lucide="music" class="w-4 h-4"></i>
                                Rhythmic Reading
                            </button>
                        </div>
                    </div>

                    <!-- Time Signature -->
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('app.exercises.time_sig_label') }}</label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach(['2/4','3/4','4/4','6/8','9/8','2/2','3/2','4/2'] as $ts)
                                <button class="py-2 text-sm font-bold rounded-xl border transition-all"
                                        :class="timeSignature === '{{ $ts }}' ? 'bg-slate-800 text-white border-slate-700 shadow-md' : 'border-gray-200 text-gray-600 hover:border-slate-400'"
                                        @click="timeSignature = '{{ $ts }}'">{{ $ts }}</button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Note Values -->
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">{{ __('app.exercises.note_values') }}</label>

                        <!-- Row 1: Main note values as 80×80 symbol boxes -->
                        <div class="flex gap-2 flex-wrap mb-3">
                            <!-- Whole Note -->
                            <button class="w-20 h-20 rounded-xl border-2 transition-all flex flex-col items-center justify-center gap-1 flex-shrink-0"
                                    :class="noteValues.includes('whole') ? 'bg-red-500 text-white border-red-500 shadow-md' : 'border-gray-200 bg-white text-gray-700 hover:border-red-300'"
                                    @click="toggleNoteValue('whole')">
                                <svg viewBox="0 0 38 20" width="38" height="20"><ellipse cx="19" cy="10" rx="16" ry="8" fill="none" stroke="currentColor" stroke-width="3"/></svg>
                                <span class="text-xs font-bold">Whole</span>
                            </button>
                            <!-- Half Note -->
                            <button class="w-20 h-20 rounded-xl border-2 transition-all flex flex-col items-center justify-center gap-1 flex-shrink-0"
                                    :class="noteValues.includes('half') ? 'bg-red-500 text-white border-red-500 shadow-md' : 'border-gray-200 bg-white text-gray-700 hover:border-red-300'"
                                    @click="toggleNoteValue('half')">
                                <svg viewBox="0 0 28 44" width="22" height="35">
                                    <ellipse cx="12" cy="34" rx="10" ry="6.5" fill="none" stroke="currentColor" stroke-width="2.5" transform="rotate(-15 12 34)"/>
                                    <line x1="21.5" y1="29" x2="21.5" y2="6" stroke="currentColor" stroke-width="2.5"/>
                                </svg>
                                <span class="text-xs font-bold">Half</span>
                            </button>
                            <!-- Quarter Note -->
                            <button class="w-20 h-20 rounded-xl border-2 transition-all flex flex-col items-center justify-center gap-1 flex-shrink-0"
                                    :class="noteValues.includes('quarter') ? 'bg-red-500 text-white border-red-500 shadow-md' : 'border-gray-200 bg-white text-gray-700 hover:border-red-300'"
                                    @click="toggleNoteValue('quarter')">
                                <svg viewBox="0 0 28 44" width="22" height="35">
                                    <ellipse cx="12" cy="34" rx="10" ry="6.5" fill="currentColor" transform="rotate(-15 12 34)"/>
                                    <line x1="21.5" y1="29" x2="21.5" y2="6" stroke="currentColor" stroke-width="2.5"/>
                                </svg>
                                <span class="text-xs font-bold">Quarter</span>
                            </button>
                            <!-- Eighth Note -->
                            <button class="w-20 h-20 rounded-xl border-2 transition-all flex flex-col items-center justify-center gap-1 flex-shrink-0"
                                    :class="noteValues.includes('eighth') ? 'bg-red-500 text-white border-red-500 shadow-md' : 'border-gray-200 bg-white text-gray-700 hover:border-red-300'"
                                    @click="toggleNoteValue('eighth')">
                                <svg viewBox="0 0 32 44" width="24" height="35">
                                    <ellipse cx="12" cy="34" rx="10" ry="6.5" fill="currentColor" transform="rotate(-15 12 34)"/>
                                    <line x1="21.5" y1="29" x2="21.5" y2="6" stroke="currentColor" stroke-width="2.5"/>
                                    <path d="M21.5 6 C28 10 28 18 21.5 22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                </svg>
                                <span class="text-xs font-bold">Eighth</span>
                            </button>
                            <!-- Sixteenth Note -->
                            <button class="w-20 h-20 rounded-xl border-2 transition-all flex flex-col items-center justify-center gap-1 flex-shrink-0"
                                    :class="noteValues.includes('sixteenth') ? 'bg-red-500 text-white border-red-500 shadow-md' : 'border-gray-200 bg-white text-gray-700 hover:border-red-300'"
                                    @click="toggleNoteValue('sixteenth')">
                                <svg viewBox="0 0 32 44" width="24" height="35">
                                    <ellipse cx="12" cy="34" rx="10" ry="6.5" fill="currentColor" transform="rotate(-15 12 34)"/>
                                    <line x1="21.5" y1="29" x2="21.5" y2="4" stroke="currentColor" stroke-width="2.5"/>
                                    <path d="M21.5 4 C28 8 28 16 21.5 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                    <path d="M21.5 13 C28 17 28 25 21.5 29" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                </svg>
                                <span class="text-xs font-bold">16th</span>
                            </button>
                        </div>

                        <!-- Row 2: Special options (responsive width, 80px height) -->
                        <div class="flex gap-2 flex-wrap">
                            <button class="h-20 px-5 rounded-xl border-2 transition-all flex items-center justify-center text-sm font-bold"
                                    style="min-width:150px;"
                                    :class="rhythmRests ? 'bg-red-400 text-white border-red-400 shadow-md' : 'border-gray-100 bg-gray-50 text-gray-700 hover:border-red-300'"
                                    @click="rhythmRests = !rhythmRests">
                                Rests
                            </button>
                            <button class="h-20 px-5 rounded-xl border-2 transition-all flex items-center justify-center text-sm font-bold"
                                    style="min-width:150px;"
                                    :class="rhythmDotted ? 'bg-red-400 text-white border-red-400 shadow-md' : 'border-gray-100 bg-gray-50 text-gray-700 hover:border-red-300'"
                                    @click="rhythmDotted = !rhythmDotted">
                                Dotted Notes
                            </button>
                            <button class="h-20 px-5 rounded-xl border-2 transition-all flex items-center justify-center text-sm font-bold"
                                    style="min-width:150px;"
                                    :class="rhythmTriplets ? 'bg-red-400 text-white border-red-400 shadow-md' : 'border-gray-100 bg-gray-50 text-gray-700 hover:border-red-300'"
                                    @click="rhythmTriplets = !rhythmTriplets">
                                Triplets
                            </button>
                        </div>
                    </div>

                    <!-- Tempo -->
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tempo: <span class="text-red-600" x-text="tempo"></span> BPM</label>
                        <input type="range" min="40" max="160" step="10" x-model.number="tempo" class="w-full h-2 rounded-lg">
                        <div class="flex justify-between text-xs text-gray-400 mt-1"><span>40</span><span>100</span><span>160</span></div>
                    </div>

                    <!-- Metronome -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <div>
                            <p class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <i data-lucide="activity" class="w-4 h-4 text-red-600"></i>
                                Metronome
                            </p>
                            <p class="text-xs text-gray-500">Plays a click-track before and alongside the rhythm</p>
                        </div>
                        <button @click="metronome = !metronome"
                            class="relative w-12 h-6 rounded-full transition-colors duration-200"
                            :class="metronome ? 'bg-red-500' : 'bg-gray-300'">
                            <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200"
                                  :class="metronome ? 'translate-x-6' : 'translate-x-0'"></span>
                        </button>
                    </div>
                </div>

                <!-- Melodic Dictation Settings — shown BEFORE general settings when dictation selected -->
                <div class="card p-6" x-show="selectedCategory === 'melodic-dictation'" x-cloak>
                    <div class="card-header card-header-pink flex items-center gap-2">
                        <i data-lucide="pencil-line" class="w-5 h-5 text-pink-700"></i>
                        <h2 class="text-base font-bold text-pink-900">{{ __('app.exercises.dictation_settings') }}</h2>
                    </div>

                    <!-- Measure Count | Listen Count | Answer Mode — 3 columns -->
                    <div class="mb-5">
                        <div class="flex gap-3">
                            <!-- Col 1: Measure Count — 3 options stacked -->
                            <div class="flex-1 flex flex-col">
                                <label class="block text-xs font-semibold text-gray-500 mb-1.5">{{ __('app.exercises.measure_count') }}</label>
                                <div class="flex flex-col gap-1.5 h-24">
                                    @foreach([2 => __('app.exercises.measures_2'), 4 => __('app.exercises.measures_4'), 8 => __('app.exercises.measures_8')] as $val => $label)
                                        <button class="flex-1 text-sm font-medium rounded-xl border transition-all"
                                                :class="dictationBars === {{ $val }} ? 'bg-slate-800 text-white border-slate-700 shadow-md' : 'border-gray-200 text-gray-600 hover:border-slate-400'"
                                                @click="dictationBars = {{ $val }}">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>
                            <!-- Col 2: Listen Count — 2×2 grid -->
                            <div class="flex-1 flex flex-col">
                                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Listen Count</label>
                                <div class="grid grid-cols-2 grid-rows-2 gap-1.5 h-24">
                                    @foreach(['1' => '1×', '2' => '2×', '3' => '3×', 'unlimited' => '∞'] as $val => $label)
                                        <button class="text-sm font-medium rounded-xl border transition-all"
                                                :class="dictationListenCount === '{{ $val }}' ? 'bg-slate-800 text-white border-slate-700 shadow-md' : 'border-gray-200 text-gray-600 hover:border-slate-400'"
                                                @click="dictationListenCount = '{{ $val }}'">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>
                            <!-- Col 3: Answer Mode — 2 options stacked -->
                            <div class="flex-1 flex flex-col">
                                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Answer Mode</label>
                                <div class="flex flex-col gap-1.5 h-24">
                                    <button class="flex-1 text-sm font-medium rounded-xl border transition-all"
                                            :class="dictationAnswerMode === 'keyboard' ? 'bg-slate-800 text-white border-slate-700 shadow-md' : 'border-gray-200 text-gray-600 hover:border-slate-400'"
                                            @click="dictationAnswerMode = 'keyboard'">
                                        ⌨️ Type Notes
                                    </button>
                                    <button class="flex-1 text-sm font-medium rounded-xl border transition-all"
                                            :class="dictationAnswerMode === 'choices' ? 'bg-slate-800 text-white border-slate-700 shadow-md' : 'border-gray-200 text-gray-600 hover:border-slate-400'"
                                            @click="dictationAnswerMode = 'choices'">
                                        🎹 Select Notes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <div>
                            <p class="text-sm font-semibold text-gray-700">{{ __('app.exercises.include_rhythm') }}</p>
                            <p class="text-xs text-gray-500">{{ __('app.exercises.include_rhythm_desc') }}</p>
                        </div>
                        <button @click="dictationIncludeRhythm = !dictationIncludeRhythm"
                            class="relative w-12 h-6 rounded-full transition-colors"
                            :class="dictationIncludeRhythm ? 'bg-slate-700' : 'bg-gray-300'">
                            <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform"
                                  :class="dictationIncludeRhythm ? 'translate-x-6' : 'translate-x-0'"></span>
                        </button>
                    </div>
                </div>

                <!-- Intervals (Melodic / Harmonic / Construction / Comparison) -->
                <div class="card p-6" x-show="selectedCategory === 'intervals'" x-cloak>
                    <div class="card-header flex items-center gap-2">
                        <i data-lucide="music-2" class="w-5 h-5 text-purple-700"></i>
                        <h2 class="text-base font-bold text-purple-900">{{ __('app.exercises.interval_settings') }}</h2>
                    </div>

                    <!-- Interval Sub-Type Selector -->
                    <div class="flex gap-2 mb-5">
                        <button class="flex-1 py-2 px-2 text-sm font-semibold rounded-xl border-2 transition-all text-center leading-tight"
                                :class="intervalSubType === 'melodic-intervals' ? 'bg-purple-600 text-white border-purple-600 shadow-md' : 'border-gray-200 text-gray-600 hover:border-purple-300 bg-white'"
                                @click="intervalSubType = 'melodic-intervals'">
                            Melodic<br>Intervals
                        </button>
                        <button class="flex-1 py-2 px-2 text-sm font-semibold rounded-xl border-2 transition-all text-center leading-tight"
                                :class="intervalSubType === 'harmonic-intervals' ? 'bg-purple-600 text-white border-purple-600 shadow-md' : 'border-gray-200 text-gray-600 hover:border-purple-300 bg-white'"
                                @click="intervalSubType = 'harmonic-intervals'">
                            Harmonic<br>Intervals
                        </button>
                        <button class="flex-1 py-2 px-2 text-sm font-semibold rounded-xl border-2 transition-all text-center leading-tight"
                                :class="intervalSubType === 'intervals-construction' ? 'bg-purple-600 text-white border-purple-600 shadow-md' : 'border-gray-200 text-gray-600 hover:border-purple-300 bg-white'"
                                @click="intervalSubType = 'intervals-construction'">
                            Interval<br>Construction
                        </button>
                        <button class="flex-1 py-2 px-2 text-sm font-semibold rounded-xl border-2 transition-all text-center leading-tight"
                                :class="intervalSubType === 'interval-comparison' ? 'bg-purple-600 text-white border-purple-600 shadow-md' : 'border-gray-200 text-gray-600 hover:border-purple-300 bg-white'"
                                @click="intervalSubType = 'interval-comparison'">
                            Interval<br>Comparison
                        </button>
                    </div>

                    <!-- Direction (for melodic / construction) -->
                    <div x-show="['melodic-intervals','intervals-construction'].includes(intervalSubType)" class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('app.exercises.direction') }}</label>
                        <div class="flex gap-2">
                            @foreach(['ascending' => __('app.exercises.dir_ascending'), 'descending' => __('app.exercises.dir_descending'), 'mixed' => __('app.exercises.dir_mixed')] as $val => $label)
                                <button class="toggle-btn flex-1 py-2 text-sm font-medium rounded-xl border border-gray-200 hover:border-slate-400"
                                        :class="direction === '{{ $val }}' ? 'active' : ''"
                                        @click="direction = '{{ $val }}'">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Interval Pool -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('app.exercises.interval_pool') }} <span class="font-normal text-gray-400">{{ __('app.exercises.interval_pool_hint') }}</span></label>
                        <div class="grid grid-cols-8 gap-1.5">
                            @foreach(['m2','M2','m3','M3','P4','TT','P5','m6'] as $interval)
                                <button class="interval-chip w-full py-2 text-sm font-semibold rounded-xl border border-gray-200 hover:border-slate-400 transition-all text-center"
                                        :class="intervalPool.includes('{{ $interval }}') ? 'selected' : ''"
                                        @click="toggleInterval('{{ $interval }}')">{{ $interval }}</button>
                            @endforeach
                            @foreach(['M6','m7','M7','8ve'] as $interval)
                                <button class="interval-chip w-full py-2 text-sm font-semibold rounded-xl border border-gray-200 hover:border-slate-400 transition-all text-center"
                                        :class="intervalPool.includes('{{ $interval }}') ? 'selected' : ''"
                                        @click="toggleInterval('{{ $interval }}')">{{ $interval }}</button>
                            @endforeach
                            <button class="col-span-2 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 rounded-xl border border-slate-300 transition-all"
                                    @click="intervalPool = {{ json_encode(['m2','M2','m3','M3','P4','TT','P5','m6','M6','m7','M7','8ve']) }}">{{ __('app.exercises.select_all') }}</button>
                            <button class="col-span-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 rounded-xl border border-gray-200 transition-all"
                                    @click="intervalPool = []">{{ __('app.common.clear') }}</button>
                        </div>
                    </div>
                </div>

                <!-- Scales Settings — shown BEFORE general settings when scales selected -->
                <div class="card p-6" x-show="selectedCategory === 'scales'" x-cloak>
                    <div class="card-header card-header-amber flex items-center gap-2">
                        <i data-lucide="waves" class="w-5 h-5 text-amber-700"></i>
                        <h2 class="text-base font-bold text-amber-900">{{ __('app.exercises.scale_settings') }}</h2>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('app.exercises.scale_types_label') }}</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['Major','Natural Minor','Harmonic Minor','Melodic Minor','Pentatonic','Blues','Dorian','Phrygian','Lydian','Mixolydian','Locrian'] as $scale)
                                <button class="interval-chip py-2 px-3 text-sm font-semibold rounded-lg border border-gray-200 hover:border-amber-400 transition-all"
                                        :class="scaleTypes.includes('{{ $scale }}') ? 'bg-amber-500 text-white border-amber-500' : ''"
                                        @click="toggleScaleType('{{ $scale }}')">{{ $scale }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('app.exercises.direction') }}</label>
                        <div class="flex gap-2">
                            @foreach(['ascending' => __('app.exercises.dir_ascending'), 'descending' => __('app.exercises.dir_descending'), 'both' => __('app.exercises.dir_both')] as $val => $label)
                                <button class="flex-1 py-2 text-sm font-medium rounded-xl border transition-all"
                                        :class="scaleDirection === '{{ $val }}' ? 'bg-slate-800 text-white border-slate-700 shadow-md' : 'border-gray-200 text-gray-600 hover:border-slate-400'"
                                        @click="scaleDirection = '{{ $val }}'">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Single Note Settings — shown BEFORE general settings when single-note selected -->
                <div class="card p-6" x-show="selectedCategory === 'single-note'" x-cloak>
                    <div class="card-header card-header-indigo flex items-center gap-2">
                        <i data-lucide="music-2" class="w-5 h-5 text-indigo-700"></i>
                        <h2 class="text-base font-bold text-indigo-900">Single Note Settings</h2>
                    </div>

                    <!-- Note Keyboard Selector (white + black clickable) -->
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Notes to use</label>
                        <div class="relative select-none" style="height:92px;">
                            <!-- White keys -->
                            <div class="flex h-full" style="gap:2px;">
                                @foreach(['C','D','E','F','G','A','B'] as $note)
                                <button
                                    class="flex-1 rounded-b-xl border-2 transition-all flex items-end justify-center pb-2 text-xs font-bold"
                                    :class="singleNoteAllowedNotes.includes('{{ $note }}')
                                        ? 'bg-indigo-500 border-indigo-600 text-white'
                                        : 'bg-white border-gray-300 text-gray-500 hover:border-indigo-400 hover:bg-indigo-50'"
                                    @click="toggleSingleNote('{{ $note }}')">
                                    {{ $note }}
                                </button>
                                @endforeach
                            </div>
                            <!-- Black keys overlay (clickable) -->
                            <div class="absolute inset-0 pointer-events-none">
                                @php
                                $setupBlackKeys = [
                                    ['left' => '10.7%', 'note' => 'C#'],
                                    ['left' => '24.9%', 'note' => 'D#'],
                                    ['left' => '53.3%', 'note' => 'F#'],
                                    ['left' => '67.5%', 'note' => 'G#'],
                                    ['left' => '81.7%', 'note' => 'A#'],
                                ];
                                @endphp
                                @foreach($setupBlackKeys as $bk)
                                <button class="absolute top-0 rounded-b-md pointer-events-auto cursor-pointer transition-all"
                                        :style="'left:{{ $bk['left'] }};width:8%;height:55%;border:1px solid;' +
                                            (singleNoteAllowedNotes.includes('{{ $bk['note'] }}')
                                                ? 'background:linear-gradient(180deg,#4f46e5,#3730a3);border-color:#818cf8;box-shadow:0 0 0 2px #a5b4fc;'
                                                : 'background:linear-gradient(180deg,#374151,#111827);border-color:#1f2937;box-shadow:2px 4px 6px rgba(0,0,0,.4);')"
                                        @click.stop="toggleSingleNote('{{ $bk['note'] }}')">
                                </button>
                                @endforeach
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Click white or black keys to include/exclude notes.</p>
                    </div>

                    <!-- Play notes in groups of + Answer using -->
                    <div class="flex gap-3 items-start">
                        <!-- Group size (2-9, compact) -->
                        <div class="shrink-0">
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Play notes in groups of</label>
                            <div class="flex gap-1">
                                @foreach([2,3,4,5,6,7,8,9] as $n)
                                <button class="rounded-xl border-2 transition-all font-bold text-sm flex items-center justify-center"
                                        style="width:2.25rem;height:2.75rem;"
                                        :class="singleNoteGroupSize === {{ $n }} ? 'bg-indigo-700 text-white border-indigo-700 shadow-md' : 'border-gray-200 text-gray-600 hover:border-indigo-400'"
                                        @click="singleNoteGroupSize = {{ $n }}">{{ $n }}</button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Answer using (flex-1 — fills remaining space) -->
                        <div class="flex-1 flex flex-col">
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Answer using</label>
                            <div class="flex gap-2" style="height:2.75rem;">
                                <button class="flex-1 text-sm font-medium rounded-xl border-2 transition-all flex items-center justify-center gap-1.5"
                                        :class="singleNoteAnswerMode === 'keyboard' ? 'bg-indigo-700 text-white border-indigo-700 shadow-md' : 'border-gray-200 text-gray-600 hover:border-indigo-400'"
                                        @click="singleNoteAnswerMode = 'keyboard'">
                                    🎹 Keyboard
                                </button>
                                <button class="flex-1 text-sm font-medium rounded-xl border-2 transition-all flex items-center justify-center gap-1.5"
                                        :class="singleNoteAnswerMode === 'note-names' ? 'bg-indigo-700 text-white border-indigo-700 shadow-md' : 'border-gray-200 text-gray-600 hover:border-indigo-400'"
                                        @click="singleNoteAnswerMode = 'note-names'">
                                    🎵 Note Names
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- General Settings -->
                <div class="card p-6">
                    <div class="card-header card-header-neutral flex items-center gap-2">
                        <i data-lucide="settings-2" class="w-5 h-5 text-slate-600"></i>
                        <h2 class="text-base font-bold text-slate-800">{{ __('app.exercises.general_settings') }}</h2>
                    </div>
                    <div class="space-y-5">

                        <!-- Clef + Octave Range -->
                        <div x-show="selectedCategory !== 'rhythm'">
                            <div class="flex gap-3">
                                <!-- Clef: full-width for single-note, half-width otherwise -->
                                <div class="flex flex-col" :class="selectedCategory === 'single-note' ? 'w-full' : 'w-1/2'">
                                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">{{ __('app.exercises.clef') }}</label>
                                    <div class="flex gap-1.5 h-20">
                                        <button class="clef-btn" :class="clef === 'treble' ? 'active' : ''" @click="clef = 'treble'">
                                            <span class="clef-symbol">𝄞</span>
                                        </button>
                                        <button class="clef-btn" :class="clef === 'bass' ? 'active' : ''" @click="clef = 'bass'">
                                            <span class="clef-symbol">𝄢</span>
                                        </button>
                                        <button class="clef-btn" :class="clef === 'alto' ? 'active' : ''" @click="clef = 'alto'">
                                            <span class="clef-symbol">𝄡</span>
                                        </button>
                                    </div>
                                    <!-- Single-note: clef note range hint -->
                                    <p class="text-xs text-gray-400 mt-1" x-show="selectedCategory === 'single-note'" x-cloak>
                                        <span x-show="clef === 'treble'">Treble (Sol) — octaves 4–5</span>
                                        <span x-show="clef === 'bass'">Bass (Fa) — octaves 2–3</span>
                                        <span x-show="clef === 'alto'">Alto (Do) — octaves 3–4</span>
                                    </p>
                                </div>
                                <!-- Octave Range: hidden for single-note (clef determines octave) -->
                                <div class="w-1/2 flex flex-col" x-show="selectedCategory !== 'single-note'" x-cloak>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Octave Range</label>
                                    <div class="grid grid-cols-3 grid-rows-2 gap-1.5 h-20">
                                        @foreach([1, 2, 3, 4, 5, 6] as $oct)
                                            <button class="text-sm font-semibold rounded-xl border transition-all"
                                                    :class="octaveRange.includes({{ $oct }}) ? 'bg-slate-800 text-white border-slate-700 shadow-md' : 'border-gray-200 text-gray-600 hover:border-slate-400'"
                                                    @click="toggleOctave({{ $oct }})">{{ $oct }}</button>
                                        @endforeach
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">At least one must be selected.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Question Count | Difficulty | Feedback Mode -->
                        <div class="flex gap-2">

                            <!-- Question Count -->
                            <div class="flex-1 relative" @click.outside="qcOpen = false">
                                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('app.exercises.question_count_label') }}</label>
                                <button @click="qcOpen = !qcOpen"
                                        class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-xl border border-gray-200 hover:border-slate-400 bg-white transition-all">
                                    <span x-text="questionCount + ' Q'"></span>
                                    <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400 transition-transform" :class="qcOpen ? 'rotate-180' : ''"></i>
                                </button>
                                <div x-show="qcOpen" x-transition
                                     class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-20 py-1">
                                    @foreach([5 => '5 Questions', 10 => '10 Questions', 15 => '15 Questions', 20 => '20 Questions'] as $count => $label)
                                        <button class="w-full text-left px-3 py-1.5 text-sm transition-colors flex items-center justify-between"
                                                :class="questionCount === {{ $count }} ? 'bg-slate-800 text-white font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                                                @click="{{ (!$isPremium && $count > 10) ? 'showPremiumModal = true' : 'questionCount = ' . $count . '; qcOpen = false' }}">
                                            <span>{{ $label }}</span>
                                            @if(!$isPremium && $count > 10)<span class="text-[10px] font-bold opacity-70">PRO</span>@endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Difficulty -->
                            <div class="flex-1 relative" @click.outside="diffOpen = false">
                                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('app.exercises.difficulty_label') }}</label>
                                <button @click="diffOpen = !diffOpen"
                                        class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-xl border border-gray-200 hover:border-slate-400 bg-white transition-all capitalize">
                                    <span x-text="difficulty"></span>
                                    <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400 transition-transform" :class="diffOpen ? 'rotate-180' : ''"></i>
                                </button>
                                <div x-show="diffOpen" x-transition
                                     class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-20 py-1">
                                    @if($isPremium)
                                        <button class="w-full text-left px-3 py-1.5 text-sm transition-colors"
                                                :class="difficulty === 'adaptive' ? 'bg-slate-800 text-white font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                                                @click="difficulty = 'adaptive'; diffOpen = false">{{ __('app.exercises.difficulty_adaptive') }}</button>
                                    @else
                                        <button class="w-full text-left px-3 py-1.5 text-sm text-gray-400 flex items-center justify-between"
                                                @click="showPremiumModal = true">
                                            <span>{{ __('app.exercises.difficulty_adaptive') }}</span>
                                            <span class="bg-slate-700 text-white text-[9px] px-1 rounded font-bold">PRO</span>
                                        </button>
                                    @endif
                                    @foreach(['beginner' => __('app.exercises.difficulty_beginner'), 'intermediate' => __('app.exercises.difficulty_intermediate'), 'advanced' => __('app.exercises.difficulty_advanced')] as $val => $label)
                                        <button class="w-full text-left px-3 py-1.5 text-sm transition-colors"
                                                :class="difficulty === '{{ $val }}' ? 'bg-slate-800 text-white font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                                                @click="difficulty = '{{ $val }}'; diffOpen = false">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Feedback Mode -->
                            <div class="flex-1 relative" @click.outside="fbOpen = false">
                                <label class="block text-xs font-semibold text-gray-500 mb-1">{{ __('app.exercises.feedback_mode') }}</label>
                                <button @click="fbOpen = !fbOpen"
                                        class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-xl border border-gray-200 hover:border-slate-400 bg-white transition-all">
                                    <span x-text="({'immediate': '{{ __('app.exercises.feedback_immediate') }}', 'end': '{{ __('app.exercises.feedback_end') }}'})[feedbackMode]"></span>
                                    <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400 transition-transform" :class="fbOpen ? 'rotate-180' : ''"></i>
                                </button>
                                <div x-show="fbOpen" x-transition
                                     class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-20 py-1">
                                    <button class="w-full text-left px-3 py-1.5 text-sm transition-colors"
                                            :class="feedbackMode === 'immediate' ? 'bg-slate-800 text-white font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                                            @click="feedbackMode = 'immediate'; fbOpen = false">{{ __('app.exercises.feedback_immediate') }}</button>
                                    <button class="w-full text-left px-3 py-1.5 text-sm transition-colors"
                                            :class="feedbackMode === 'end' ? 'bg-slate-800 text-white font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                                            @click="feedbackMode = 'end'; fbOpen = false">{{ __('app.exercises.feedback_end') }}</button>
                                </div>
                            </div>
                        </div>

                        <!-- Shuffle + AI Mode (side by side) -->
                        <div class="flex gap-2">
                            <div class="flex-1 flex items-center justify-between p-3 bg-gray-50 rounded-xl gap-2">
                                <p class="text-sm font-semibold text-gray-700">{{ __('app.exercises.shuffle') }}</p>
                                <button @click="randomize = !randomize"
                                    class="relative flex-shrink-0 w-12 h-6 rounded-full transition-colors duration-200 focus:outline-none"
                                    :class="randomize ? 'bg-slate-700' : 'bg-gray-300'">
                                    <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200"
                                          :class="randomize ? 'translate-x-6' : 'translate-x-0'"></span>
                                </button>
                            </div>
                            <div class="flex-1 flex items-center justify-between p-3 rounded-xl border-2 transition-all gap-2"
                                 :class="aiMode ? 'border-slate-400 bg-slate-50' : 'bg-gray-50 border-transparent'">
                                <p class="text-sm font-semibold text-gray-700 flex items-center gap-1 min-w-0">
                                    <i data-lucide="sparkles" class="w-4 h-4 text-slate-600 flex-shrink-0"></i>
                                    <span class="truncate">{{ __('app.exercises.ai_mode') }}</span>
                                    @if(!$isPremium)
                                        <span class="bg-slate-700 text-white text-[9px] px-1 rounded font-bold flex-shrink-0">PRO</span>
                                    @endif
                                </p>
                                @if($isPremium)
                                    <button @click="aiMode = !aiMode"
                                        class="relative flex-shrink-0 w-12 h-6 rounded-full transition-colors duration-200"
                                        :class="aiMode ? 'bg-slate-700' : 'bg-gray-300'">
                                        <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200"
                                              :class="aiMode ? 'translate-x-6' : 'translate-x-0'"></span>
                                    </button>
                                @else
                                    <button @click="showPremiumModal = true"
                                        class="relative flex-shrink-0 w-12 h-6 bg-gray-200 rounded-full cursor-not-allowed">
                                        <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow"></span>
                                    </button>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- ===== RIGHT: Summary + Plans ===== -->
            <div class="w-[238px] flex-shrink-0 space-y-4 sidebar-col">

                <!-- Summary Card -->
                <div class="card p-5">
                    <div class="card-header card-header-neutral flex items-center gap-2" style="margin:-1.25rem -1.25rem 1rem;">
                        <i data-lucide="clipboard-list" class="w-4 h-4 text-slate-600"></i>
                        <h3 class="text-sm font-bold text-slate-800">{{ __('app.exercises.session_summary') }}</h3>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">{{ __('app.exercises.category_label') }}</span>
                            <span class="summary-badge" x-text="(selectedCategory === 'intervals' ? intervalSubType : selectedCategory).replace(/-/g,' ')"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">{{ __('app.exercises.question_count_label') }}</span>
                            <span class="summary-badge" x-text="questionCount"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">{{ __('app.exercises.difficulty_short') }}</span>
                            <span class="summary-badge" x-text="difficulty"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">{{ __('app.exercises.clef') }}</span>
                            <span class="summary-badge">
                                <span x-text="clef === 'treble' ? '𝄞 {{ __('app.exercises.treble_short') }}' : clef === 'bass' ? '𝄢 {{ __('app.exercises.bass_short') }}' : '𝄡 Alto'"></span>
                            </span>
                        </div>
                        <div class="flex items-center justify-between" x-show="aiMode">
                            <span class="text-gray-500">{{ __('app.exercises.mode_label') }}</span>
                            <span class="summary-badge">
                                <i data-lucide="sparkles" class="w-3 h-3 mr-1"></i> AI
                            </span>
                        </div>
                    </div>

                    <!-- Session Name Input -->
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <input x-model="sessionName" type="text" placeholder="{{ __('app.exercises.session_name_placeholder') }}"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-400 bg-slate-50/60 placeholder-gray-400">
                    </div>
                </div>

                <!-- Livewire: AI recommendation + Saved plans -->
                <livewire:exercise-setup-studio />

                <!-- Start Exercise Button -->
                <button @click="startExercise()"
                    class="w-full start-btn text-white font-bold py-4 rounded-2xl flex items-center justify-center gap-2 text-base shadow-lg">
                    <i data-lucide="play-circle" class="w-5 h-5"></i>
                    {{ __('app.exercises.start_exercise') }}
                </button>

                <!-- Error display -->
                <div id="launchError" class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl p-3"></div>
            </div>
        </div>
    </div>

    <!-- Hidden Launch Form -->
    <form id="launchForm" action="{{ route('exercise-setup.launch') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="exercise_type" id="f_exercise_type">
        <input type="hidden" name="difficulty" id="f_difficulty">
        <input type="hidden" name="question_count" id="f_question_count">
        <input type="hidden" name="ai_mode" id="f_ai_mode">
        <input type="hidden" name="settings" id="f_settings">
    </form>

    <script>
    function exerciseSetup() {
        return {
            // Core state
            selectedCategory: 'intervals',
            intervalSubType: 'melodic-intervals',
            difficulty: '{{ $isPremium ? "adaptive" : "intermediate" }}',
            questionCount: 5,
            clef: 'treble',
            replayLimit: 'unlimited',
            timeLimitSeconds: 0,
            feedbackMode: 'immediate',
            randomize: true,
            aiMode: false,
            sessionName: '',

            // Dropdown open states
            qcOpen: false,
            diffOpen: false,
            fbOpen: false,

            // Interval specific
            intervalPool: ['m2','M2','m3','M3','P4','P5'],
            direction: 'mixed',

            // Chord specific
            chordTypes: ['Major','Minor'],
            voicing: 'block',
            includeInversions: false,

            // Scale specific
            scaleTypes: ['Major','Natural Minor'],
            scaleDirection: 'ascending',

            // Rhythm specific
            rhythmMode: 'dictation',
            timeSignature: '4/4',
            noteValues: ['quarter','eighth'],
            rhythmRests: false,
            rhythmDotted: false,
            rhythmTriplets: false,
            tempo: 80,
            metronome: true,

            // Octave range
            octaveRange: [3, 4, 5],

            // Single Note specific
            singleNoteAllowedNotes: ['C'],
            singleNoteGroupSize: 2,
            singleNoteAnswerMode: 'keyboard',

            // Melodic dictation
            dictationBars: 2,
            dictationIncludeRhythm: false,
            dictationListenCount: 'unlimited',
            dictationAnswerMode: 'keyboard',

            // UI state
            mobileStep: 1, // kept for compatibility
            showPremiumModal: false,
            showSavePlanModal: false,
            savePlanName: '',
            savePlanError: '',
            savePlanLoading: false,

            init() {
                const intervalSubTypes = ['melodic-intervals','harmonic-intervals','intervals-construction','interval-comparison','intervals-direction'];
                const urlType = new URLSearchParams(window.location.search).get('type');
                const preselected = urlType || '{{ $preselectedType ?? "melodic-intervals" }}';
                if (intervalSubTypes.includes(preselected)) {
                    this.selectedCategory = 'intervals';
                    this.intervalSubType = (preselected === 'intervals-direction') ? 'melodic-intervals' : preselected;
                } else {
                    this.selectedCategory = preselected;
                }
                if (typeof lucide !== 'undefined') lucide.createIcons();
            },

            toggleInterval(interval) {
                const idx = this.intervalPool.indexOf(interval);
                if (idx === -1) { this.intervalPool.push(interval); }
                else { this.intervalPool.splice(idx, 1); }
            },

            toggleChordType(type) {
                const idx = this.chordTypes.indexOf(type);
                if (idx === -1) { this.chordTypes.push(type); }
                else { this.chordTypes.splice(idx, 1); }
            },

            toggleScaleType(type) {
                const idx = this.scaleTypes.indexOf(type);
                if (idx === -1) { this.scaleTypes.push(type); }
                else { this.scaleTypes.splice(idx, 1); }
            },

            toggleNoteValue(val) {
                const idx = this.noteValues.indexOf(val);
                if (idx === -1) { this.noteValues.push(val); }
                else { this.noteValues.splice(idx, 1); }
            },

            toggleSingleNote(note) {
                const idx = this.singleNoteAllowedNotes.indexOf(note);
                if (idx === -1) { this.singleNoteAllowedNotes.push(note); }
                else if (this.singleNoteAllowedNotes.length > 1) { this.singleNoteAllowedNotes.splice(idx, 1); }
            },

            toggleOctave(oct) {
                const idx = this.octaveRange.indexOf(oct);
                if (idx === -1) {
                    this.octaveRange.push(oct);
                    this.octaveRange.sort((a, b) => a - b);
                } else if (this.octaveRange.length > 1) {
                    this.octaveRange.splice(idx, 1);
                }
            },

            buildSettings() {
                // Expand base note values with rests and dotted variants if toggled
                let finalNoteValues = [...this.noteValues];
                if (this.rhythmRests) {
                    const restMap = { whole: 'whole_rest', half: 'half_rest', quarter: 'quarter_rest', eighth: 'eighth_rest' };
                    this.noteValues.forEach(v => { if (restMap[v]) finalNoteValues.push(restMap[v]); });
                }
                if (this.rhythmDotted) {
                    const dotMap = { half: 'dotted-half', quarter: 'dotted-quarter', eighth: 'dotted-eighth' };
                    this.noteValues.forEach(v => { if (dotMap[v]) finalNoteValues.push(dotMap[v]); });
                }
                if (this.rhythmTriplets) {
                    // triplet-quarter needs at least a quarter or half note in the pool (fills 2 beats)
                    if (finalNoteValues.some(v => ['quarter','half','dotted-quarter'].includes(v))) {
                        finalNoteValues.push('triplet-quarter');
                    }
                    // triplet-eighth needs at least an eighth note in the pool (fills 1 beat)
                    if (finalNoteValues.some(v => ['eighth','quarter'].includes(v))) {
                        finalNoteValues.push('triplet-eighth');
                    }
                }

                return {
                    question_count: this.questionCount,
                    difficulty: this.difficulty,
                    clef: this.clef,
                    replay_limit: this.replayLimit,
                    time_limit_seconds: this.timeLimitSeconds,
                    feedback_mode: this.feedbackMode,
                    randomize: this.randomize,
                    direction: this.direction,
                    interval_pool: this.intervalPool,
                    chord_types: this.chordTypes,
                    voicing: this.voicing,
                    include_inversions: this.includeInversions,
                    scale_types: this.scaleTypes,
                    scale_direction: this.scaleDirection,
                    time_signature: this.timeSignature,
                    note_values: finalNoteValues,
                    rhythm_mode: this.rhythmMode,
                    rhythm_rests: this.rhythmRests,
                    rhythm_dotted: this.rhythmDotted,
                    rhythm_triplets: this.rhythmTriplets,
                    tempo: this.tempo,
                    metronome: this.metronome,
                    dictation_bars: this.dictationBars,
                    dictation_include_rhythm: this.dictationIncludeRhythm,
                    dictation_listen_count: this.dictationListenCount,
                    dictation_answer_mode: this.dictationAnswerMode,
                    octave_range: this.octaveRange,
                    session_name: this.sessionName,
                    single_note_allowed_notes: this.singleNoteAllowedNotes,
                    single_note_group_size: this.singleNoteGroupSize,
                    single_note_answer_mode: this.singleNoteAnswerMode,
                };
            },

            startExercise() {
                const settings = this.buildSettings();
                const effectiveType = this.selectedCategory === 'intervals' ? this.intervalSubType : this.selectedCategory;
                document.getElementById('f_exercise_type').value = effectiveType;
                document.getElementById('f_difficulty').value = this.difficulty;
                document.getElementById('f_question_count').value = this.questionCount;
                document.getElementById('f_ai_mode').value = this.aiMode ? '1' : '0';
                document.getElementById('f_settings').value = JSON.stringify(settings);
                document.getElementById('launchForm').submit();
            },

            async saveCurrentPlan() {
                if (!this.savePlanName.trim()) return;
                this.savePlanLoading = true;
                this.savePlanError = '';
                try {
                    const res = await fetch('{{ route("exercise-setup.templates.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({
                            name: this.savePlanName,
                            category: this.selectedCategory === 'intervals' ? this.intervalSubType : this.selectedCategory,
                            exercise_type: this.selectedCategory === 'intervals' ? this.intervalSubType : this.selectedCategory,
                            settings_json: this.buildSettings(),
                            is_ai_generated: false,
                        }),
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.showSavePlanModal = false;
                        this.savePlanName = '';
                        // Refresh Livewire saved plans
                        if (window.Livewire) {
                            window.Livewire.dispatch('refresh');
                        }
                    } else {
                        this.savePlanError = data.message || '{{ __("app.exercises.plan_save_failed") }}';
                    }
                } catch(e) {
                    this.savePlanError = '{{ __("app.exercises.error_occurred") }}';
                }
                this.savePlanLoading = false;
            },

            loadPlan(template) {
                const s = template.settings_json || {};
                const intervalSubTypes = ['melodic-intervals','harmonic-intervals','intervals-construction','interval-comparison','intervals-direction'];
                const cat = template.exercise_type || template.category || this.selectedCategory;
                if (intervalSubTypes.includes(cat)) {
                    this.selectedCategory = 'intervals';
                    this.intervalSubType = (cat === 'intervals-direction') ? 'melodic-intervals' : cat;
                } else {
                    this.selectedCategory = cat;
                }
                this.difficulty = s.difficulty || 'intermediate';
                this.questionCount = s.question_count || 10;
                this.clef = s.clef || 'treble';
                this.replayLimit = s.replay_limit || 'unlimited';
                this.timeLimitSeconds = s.time_limit_seconds || 0;
                this.feedbackMode = s.feedback_mode || 'immediate';
                this.randomize = s.randomize !== undefined ? s.randomize : true;
                this.aiMode = s.ai_mode || false;
                this.direction = s.direction || 'mixed';
                this.intervalPool = s.interval_pool || ['m2','M2','m3','M3','P4','P5'];
                this.chordTypes = s.chord_types || ['Major','Minor'];
                this.voicing = s.voicing || 'block';
                this.includeInversions = s.include_inversions || false;
                this.scaleTypes = s.scale_types || ['Major','Natural Minor'];
                this.scaleDirection = s.scale_direction || 'ascending';
                this.timeSignature = s.time_signature || '4/4';
                this.noteValues = (s.note_values || ['quarter','eighth']).filter(v => ['whole','half','quarter','eighth','sixteenth'].includes(v));
                this.rhythmMode = s.rhythm_mode || 'dictation';
                this.rhythmRests = s.rhythm_rests || false;
                this.rhythmDotted = s.rhythm_dotted || false;
                this.rhythmTriplets = s.rhythm_triplets || false;
                this.tempo = s.tempo || 80;
                this.metronome = s.metronome !== undefined ? s.metronome : true;
                this.dictationBars = s.dictation_bars || 2;
                this.dictationIncludeRhythm = s.dictation_include_rhythm || false;
                this.dictationListenCount = s.dictation_listen_count || 'unlimited';
                this.dictationAnswerMode = s.dictation_answer_mode || 'keyboard';
                this.octaveRange = s.octave_range || [3, 4, 5];
                this.singleNoteAllowedNotes = s.single_note_allowed_notes || ['C'];
                this.singleNoteGroupSize = s.single_note_group_size || 2;
                this.singleNoteAnswerMode = s.single_note_answer_mode || 'keyboard';
                this.sessionName = template.name || '';
            },

            applyAiRecommendation(rec) {
                if (!rec) return;
                const s = rec.settings || {};
                if (rec.exercise_type) {
                    const intervalSubTypes = ['melodic-intervals','harmonic-intervals','intervals-construction','interval-comparison','intervals-direction'];
                    if (intervalSubTypes.includes(rec.exercise_type)) {
                        this.selectedCategory = 'intervals';
                        this.intervalSubType = (rec.exercise_type === 'intervals-direction') ? 'melodic-intervals' : rec.exercise_type;
                    } else {
                        this.selectedCategory = rec.exercise_type;
                    }
                }
                if (s.difficulty) this.difficulty = s.difficulty;
                if (s.question_count) this.questionCount = s.question_count;
                if (s.direction) this.direction = s.direction;
                if (s.interval_pool) this.intervalPool = s.interval_pool;
                if (s.replay_limit) this.replayLimit = s.replay_limit;
                if (s.clef) this.clef = s.clef;
                if (s.chord_types) this.chordTypes = s.chord_types;
                if (s.scale_types) this.scaleTypes = s.scale_types;
                if (s.time_signature) this.timeSignature = s.time_signature;
            },
        };
    }
    </script>

    @include('partials.footer')

    @livewireScripts
    <script>
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
        document.addEventListener('livewire:navigated', () => lucide.createIcons());
    </script>
</body>
</html>
