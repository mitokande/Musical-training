<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $q ? '"' . $q . '" — ' . __('app.search.page_title') : __('app.search.page_title') }} · {{ config('app.name', 'Harmoniva') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: {
                            50:'#faf5ff',100:'#f3e8ff',200:'#e9d5ff',300:'#d8b4fe',
                            400:'#c084fc',500:'#a855f7',600:'#9333ea',700:'#7c3aed',
                            800:'#6b21a8',900:'#581c87'
                        },
                        accent: { 400:'#fb923c',500:'#f97316',600:'#ea580c' }
                    },
                }
            }
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .search-glass:focus { box-shadow: 0 0 0 3px rgba(147,51,234,0.25); }
        .tool-card { transition: transform .15s ease, box-shadow .15s ease; }
        .tool-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,0,0,.1); }
        .result-row:hover { background: rgba(147,51,234,.03); }
        .line-clamp-2 { display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
    </style>
</head>
<body class="font-sans bg-gray-50 min-h-screen">

@include('partials.navbar', ['active' => ''])

{{-- ══════════════════════════════════════════
     HERO BANNER
══════════════════════════════════════════ --}}
<div class="relative overflow-hidden"
     style="background: linear-gradient(135deg, #0f0720 0%, #1e0a40 40%, #12021e 100%);">

    {{-- Decorative blobs --}}
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-32 -left-32 w-[420px] h-[420px] rounded-full bg-purple-700/20 blur-[80px]"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 rounded-full bg-orange-500/15 blur-[60px]"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-52 rounded-full bg-purple-500/10 blur-3xl"></div>
    </div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 pt-10 pb-12 sm:pt-14 sm:pb-16">

        {{-- Badge + headline --}}
        <div class="text-center mb-8">
            <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-sm text-purple-200 text-xs font-semibold px-3 py-1.5 rounded-full mb-4">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                {{ __('app.search.badge') }}
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                @if($q)
                    <span class="bg-gradient-to-r from-purple-300 to-orange-300 bg-clip-text text-transparent">"{{ $q }}"</span>
                    @if($totalResults > 0)
                         {{ __('app.search.results_for') }} <span class="text-white">{{ $totalResults }} {{ __('app.search.page_title') }}</span>
                    @else
                         {{ __('app.search.no_results_for') }}
                    @endif
                @else
                    {{ __('app.search.search_title') }}
                @endif
            </h1>
            @if(!$q)
            <p class="text-gray-400 text-sm mt-2">{{ __('app.search.subtitle') }}</p>
            @endif
        </div>

        {{-- Quick-action pills --}}
        <div class="flex flex-wrap justify-center gap-2 mb-8">
            @php
            $quickActions = [
                ['label'=> __('app.search.quick_ask_ai'),       'href'=>'/ai-exercises',  'icon'=>'sparkles',       'from'=>'from-violet-500','to'=>'to-purple-600'],
                ['label'=> __('app.search.quick_music_games'),  'href'=>'/games',         'icon'=>'gamepad-2',      'from'=>'from-emerald-500','to'=>'to-teal-600'],
                ['label'=> __('app.search.quick_ai_exercises'), 'href'=>'/ai-exercises',  'icon'=>'brain-circuit',  'from'=>'from-purple-500','to'=>'to-indigo-600'],
                ['label'=> __('app.search.quick_piano'),        'href'=>'/piano-studio',  'icon'=>'piano',          'from'=>'from-blue-500','to'=>'to-cyan-600'],
                ['label'=> __('app.search.quick_learning'),     'href'=>'/dashboard',     'icon'=>'route',          'from'=>'from-orange-500','to'=>'to-amber-500'],
                ['label'=> __('app.search.quick_setup'),        'href'=>'/exercise-setup','icon'=>'wand-sparkles',  'from'=>'from-rose-500','to'=>'to-pink-600'],
            ];
            @endphp
            @foreach($quickActions as $a)
            <a href="{{ $a['href'] }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white bg-gradient-to-r {{ $a['from'] }} {{ $a['to'] }} shadow-lg shadow-black/20 hover:-translate-y-0.5 transition-transform">
                <i data-lucide="{{ $a['icon'] }}" class="w-4 h-4"></i>
                {{ $a['label'] }}
            </a>
            @endforeach
        </div>

        {{-- Search bar --}}
        <form action="/search" method="GET">
            <div style="display:flex; gap:12px; max-width:672px; margin:0 auto;">
                <div style="position:relative; flex:1;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#9ca3af; pointer-events:none;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input
                        type="text"
                        name="q"
                        value="{{ $q }}"
                        autofocus
                        autocomplete="off"
                        placeholder="{{ __('app.search.placeholder') }}"
                        style="width:100%; padding:16px 40px 16px 48px; background:rgba(255,255,255,0.12); border:1.5px solid rgba(255,255,255,0.25); border-radius:16px; color:white; font-size:16px; outline:none; font-family:inherit; box-sizing:border-box;"
                        onfocus="this.style.borderColor='#9333ea'; this.style.background='rgba(255,255,255,0.18)';"
                        onblur="this.style.borderColor='rgba(255,255,255,0.25)'; this.style.background='rgba(255,255,255,0.12)';"
                    >
                    @if($q)
                    <a href="/search" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); color:rgba(255,255,255,0.5); text-decoration:none; line-height:1; padding:4px;">✕</a>
                    @endif
                </div>
                <button type="submit"
                        style="flex-shrink:0; padding:16px 28px; background:#9333ea; color:white; font-weight:700; border-radius:16px; border:none; cursor:pointer; font-size:15px; font-family:inherit; white-space:nowrap;">
                    {{ __('app.search.button') }}
                </button>
            </div>
        </form>

    </div>
</div>

{{-- ══════════════════════════════════════════
     RESULTS
══════════════════════════════════════════ --}}
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 space-y-10">

@if($q)

    {{-- ── SECTION 1: Content (Articles / Videos) ────────────── --}}
    @if($articles->count() > 0)
    <section x-data="{ more: false }">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center">
                <i data-lucide="file-text" class="w-4.5 h-4.5"></i>
            </div>
            <h2 class="text-base font-bold text-gray-900">{{ __('app.search.section_content') }}</h2>
            <span class="text-xs font-semibold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">{{ $articles->count() }}</span>
        </div>

        {{-- First 3 as image cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 mb-4">
            @foreach($articles->take(3) as $article)
            <a href="{{ route('articles.edit', $article) }}"
               class="tool-card group bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:border-purple-200">
                <div class="aspect-[16/9] relative overflow-hidden
                    @if($article->content_type === 'video') bg-gradient-to-br from-red-500 to-rose-600
                    @elseif($article->content_type === 'audio') bg-gradient-to-br from-blue-500 to-cyan-600
                    @else bg-gradient-to-br from-purple-500 to-indigo-600
                    @endif">
                    @if($article->featured_image)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($article->featured_image) }}"
                         alt="{{ $article->title }}"
                         class="w-full h-full object-cover absolute inset-0">
                    @else
                    <div class="absolute inset-0 flex items-center justify-center opacity-20">
                        <i data-lucide="{{ $article->content_type === 'video' ? 'play-circle' : 'file-text' }}" class="w-16 h-16 text-white"></i>
                    </div>
                    @endif
                    <div class="absolute bottom-2 left-2">
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-white bg-black/40 backdrop-blur-sm px-2 py-1 rounded-lg">
                            @if($article->content_type === 'video')
                                <i data-lucide="play-circle" class="w-3 h-3"></i> Video
                            @elseif($article->content_type === 'audio')
                                <i data-lucide="music" class="w-3 h-3"></i> Audio
                            @else
                                <i data-lucide="file-text" class="w-3 h-3"></i> {{ __('app.search.badge_article') }}
                            @endif
                        </span>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 text-sm mb-1.5 group-hover:text-purple-700 transition-colors line-clamp-2">
                        {{ $article->title }}
                    </h3>
                    @if($article->excerpt)
                    <p class="text-xs text-gray-500 line-clamp-2">{{ $article->excerpt }}</p>
                    @endif
                    <div class="flex items-center gap-2 mt-3 text-xs text-gray-400">
                        @if($article->published_at)
                            <span>{{ $article->published_at->format('d.m.Y') }}</span>
                        @endif
                        @if($article->reading_time)
                            <span>· {{ $article->reading_time }} {{ __('app.search.min_abbr') }}</span>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Rest as list rows --}}
        @if($articles->count() > 3)
        <div x-show="more" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-50 overflow-hidden mb-3">
            @foreach($articles->skip(3) as $article)
            <a href="{{ route('articles.edit', $article) }}"
               class="result-row flex items-center gap-4 px-5 py-3.5 group transition-colors">
                <div class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center
                    {{ $article->content_type === 'video' ? 'bg-red-100 text-red-600' : 'bg-purple-100 text-purple-600' }}">
                    <i data-lucide="{{ $article->content_type === 'video' ? 'play-circle' : 'file-text' }}" class="w-4 h-4"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 text-sm truncate group-hover:text-purple-700">{{ $article->title }}</p>
                    @if($article->excerpt)
                    <p class="text-xs text-gray-500 truncate">{{ Str::limit($article->excerpt, 90) }}</p>
                    @endif
                </div>
                <i data-lucide="arrow-right" class="w-4 h-4 text-gray-300 flex-shrink-0"></i>
            </a>
            @endforeach
        </div>
        @php $moreContentCount = $articles->count() - 3; @endphp
        <button @click="more = !more"
                class="flex items-center gap-1.5 text-sm font-semibold text-purple-600 hover:text-purple-800 transition-colors">
            <span x-text="more ? '{{ __('app.search.show_less') }}' : '{{ str_replace(':count', $moreContentCount, __('app.search.more_content')) }}'"></span>
            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="more && 'rotate-180'"></i>
        </button>
        @endif
    </section>
    @endif

    {{-- ── SECTION 2: Apps & Tools ──────────────────────────── --}}
    @php
    $iconMap = [
        'single-note-practice'           => ['icon'=>'circle-dot',    'color'=>'purple'],
        'melodic-interval-practice'      => ['icon'=>'music',         'color'=>'blue'],
        'harmonic-interval-practice'     => ['icon'=>'layers',        'color'=>'indigo'],
        'interval-direction-practice'    => ['icon'=>'arrow-up-down', 'color'=>'violet'],
        'interval-comparison-practice'   => ['icon'=>'bar-chart-2',   'color'=>'fuchsia'],
        'interval-construction-practice' => ['icon'=>'puzzle',        'color'=>'pink'],
        'chord-practice'                 => ['icon'=>'layers',        'color'=>'rose'],
        'scale-practice'                 => ['icon'=>'git-branch',    'color'=>'orange'],
        'rhythm-practice'                => ['icon'=>'timer',         'color'=>'amber'],
        'melodic-dictation'              => ['icon'=>'pencil',        'color'=>'teal'],
    ];
    $catColorMap = [
        'intervals'=>'purple','melodic-intervals'=>'blue','interval-direction'=>'violet',
        'harmonic-intervals'=>'indigo','interval-comparison'=>'fuchsia',
        'interval-construction'=>'pink','single-note'=>'purple',
        'scales-modes'=>'orange','chords'=>'rose','rhythm'=>'amber','melodic-dictation'=>'teal',
    ];
    $toolItems = collect();
    foreach($practices as $p) {
        $m = $iconMap[$p->slug] ?? ['icon'=>'music','color'=>'purple'];
        $toolItems->push(['name'=>$p->name,'desc'=>$p->description,'url'=>route('practice',$p->slug),'icon'=>$m['icon'],'color'=>$m['color'],'badge'=>$p->type]);
    }
    foreach($categories as $cat) {
        $toolItems->push(['name'=>$cat->name,'desc'=>$cat->description??'','url'=>'/learn','icon'=>$cat->icon??'folder','color'=>$catColorMap[$cat->slug]??'purple','badge'=> __('app.search.badge_category')]);
    }
    $colorBg = ['purple'=>'bg-purple-50 hover:bg-purple-100 border-purple-100','blue'=>'bg-blue-50 hover:bg-blue-100 border-blue-100','indigo'=>'bg-indigo-50 hover:bg-indigo-100 border-indigo-100','violet'=>'bg-violet-50 hover:bg-violet-100 border-violet-100','fuchsia'=>'bg-fuchsia-50 hover:bg-fuchsia-100 border-fuchsia-100','pink'=>'bg-pink-50 hover:bg-pink-100 border-pink-100','rose'=>'bg-rose-50 hover:bg-rose-100 border-rose-100','orange'=>'bg-orange-50 hover:bg-orange-100 border-orange-100','amber'=>'bg-amber-50 hover:bg-amber-100 border-amber-100','teal'=>'bg-teal-50 hover:bg-teal-100 border-teal-100','green'=>'bg-green-50 hover:bg-green-100 border-green-100','gray'=>'bg-gray-50 hover:bg-gray-100 border-gray-100'];
    $iconBg = ['purple'=>'bg-purple-100 text-purple-600','blue'=>'bg-blue-100 text-blue-600','indigo'=>'bg-indigo-100 text-indigo-600','violet'=>'bg-violet-100 text-violet-600','fuchsia'=>'bg-fuchsia-100 text-fuchsia-600','pink'=>'bg-pink-100 text-pink-600','rose'=>'bg-rose-100 text-rose-600','orange'=>'bg-orange-100 text-orange-600','amber'=>'bg-amber-100 text-amber-600','teal'=>'bg-teal-100 text-teal-600','green'=>'bg-green-100 text-green-600','gray'=>'bg-gray-100 text-gray-600'];
    @endphp

    @if($toolItems->count() > 0)
    <section>
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center">
                <i data-lucide="zap" class="w-4.5 h-4.5"></i>
            </div>
            <h2 class="text-base font-bold text-gray-900">{{ __('app.search.section_tools') }}</h2>
            <span class="text-xs font-semibold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">{{ $toolItems->count() }}</span>
        </div>
        <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3">
            @foreach($toolItems->take(12) as $t)
            @php $c=$t['color']; @endphp
            <a href="{{ $t['url'] }}"
               class="tool-card group flex flex-col items-center text-center p-4 rounded-2xl border {{ $colorBg[$c]??$colorBg['purple'] }} transition-colors">
                <div class="w-11 h-11 rounded-xl {{ $iconBg[$c]??$iconBg['purple'] }} flex items-center justify-center mb-2.5">
                    <i data-lucide="{{ $t['icon'] }}" class="w-5 h-5"></i>
                </div>
                <span class="text-[11px] font-semibold text-gray-800 group-hover:text-gray-900 leading-tight">{{ $t['name'] }}</span>
                @if($t['badge'])
                <span class="mt-1 text-[9px] font-medium text-gray-400 uppercase tracking-wide">{{ $t['badge'] }}</span>
                @endif
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ── SECTION 3: Learning Path Exercises ───────────────── --}}
    @if($exercises->count() > 0)
    <section x-data="{ more: false }">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                <i data-lucide="route" class="w-4.5 h-4.5"></i>
            </div>
            <h2 class="text-base font-bold text-gray-900">{{ __('app.search.section_exercises') }}</h2>
            <span class="text-xs font-semibold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">{{ $exercises->count() }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            @foreach($exercises->take(5) as $i => $ex)
            <a href="{{ route('learning-path.show', $ex->slug) }}"
               class="result-row flex items-center gap-4 px-5 py-4 group transition-colors border-b border-gray-50 last:border-0">
                <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 text-xs font-bold">
                    {{ $i + 1 }}
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 text-sm group-hover:text-purple-700 transition-colors">
                        {{ $ex->getLocalizedTitle() }}
                    </h3>
                    <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                        @if($ex->category)
                        <span class="text-[11px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $ex->category->name }}</span>
                        @endif
                        @if($ex->level)
                        <span class="text-[11px] text-gray-400">{{ __('app.search.level') }} {{ $ex->level }}</span>
                        @endif
                        @if($ex->estimated_duration_minutes)
                        <span class="text-[11px] text-gray-400">· ~{{ $ex->estimated_duration_minutes }} {{ __('app.search.min_abbr') }}</span>
                        @endif
                    </div>
                </div>
                <span class="hidden sm:inline-flex items-center gap-1 text-xs font-semibold text-purple-600 bg-purple-50 px-2.5 py-1 rounded-lg flex-shrink-0">
                    <i data-lucide="play" class="w-3 h-3"></i> {{ __('app.search.start') }}
                </span>
                <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 flex-shrink-0"></i>
            </a>
            @endforeach

            @if($exercises->count() > 5)
            <div x-show="more" x-cloak>
                @foreach($exercises->skip(5) as $i => $ex)
                <a href="{{ route('learning-path.show', $ex->slug) }}"
                   class="result-row flex items-center gap-4 px-5 py-4 group transition-colors border-t border-gray-50">
                    <div class="w-8 h-8 rounded-xl bg-gray-100 text-gray-500 flex items-center justify-center flex-shrink-0 text-xs font-bold">
                        {{ $i + 6 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 text-sm group-hover:text-purple-700 transition-colors">{{ $ex->getLocalizedTitle() }}</h3>
                        @if($ex->category)
                        <span class="text-[11px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $ex->category->name }}</span>
                        @endif
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 flex-shrink-0"></i>
                </a>
                @endforeach
            </div>
            @endif
        </div>
        @if($exercises->count() > 5)
        @php $moreExCount = $exercises->count() - 5; @endphp
        <button @click="more = !more"
                class="mt-3 flex items-center gap-1.5 text-sm font-semibold text-purple-600 hover:text-purple-800 transition-colors">
            <span x-text="more ? '{{ __('app.search.show_less') }}' : '{{ str_replace(':count', $moreExCount, __('app.search.more_exercises')) }}'"></span>
            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="more && 'rotate-180'"></i>
        </button>
        @endif
    </section>
    @endif

    {{-- ── SECTION 4: Pages ─────────────────────────────────── --}}
    @if($pages->count() > 0)
    <section>
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-xl bg-gray-100 text-gray-600 flex items-center justify-center">
                <i data-lucide="layout" class="w-4.5 h-4.5"></i>
            </div>
            <h2 class="text-base font-bold text-gray-900">{{ __('app.search.section_pages') }}</h2>
            <span class="text-xs font-semibold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">{{ $pages->count() }}</span>
        </div>
        @php
        $pgBg  = ['blue'=>'bg-blue-50 border-blue-100','purple'=>'bg-purple-50 border-purple-100','green'=>'bg-green-50 border-green-100','orange'=>'bg-orange-50 border-orange-100','teal'=>'bg-teal-50 border-teal-100','rose'=>'bg-rose-50 border-rose-100','gray'=>'bg-gray-50 border-gray-100'];
        $pgIc  = ['blue'=>'bg-blue-100 text-blue-600','purple'=>'bg-purple-100 text-purple-600','green'=>'bg-green-100 text-green-600','orange'=>'bg-orange-100 text-orange-600','teal'=>'bg-teal-100 text-teal-600','rose'=>'bg-rose-100 text-rose-600','gray'=>'bg-gray-100 text-gray-600'];
        @endphp
        <div class="grid gap-3 sm:grid-cols-2">
            @foreach($pages as $pg)
            @php $pc=$pg['color']; @endphp
            <a href="{{ $pg['url'] }}"
               class="tool-card group flex items-center gap-4 p-4 rounded-2xl border {{ $pgBg[$pc]??$pgBg['gray'] }} transition-colors">
                <div class="w-10 h-10 rounded-xl {{ $pgIc[$pc]??$pgIc['gray'] }} flex items-center justify-center flex-shrink-0">
                    <i data-lucide="{{ $pg['icon'] }}" class="w-5 h-5"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 text-sm group-hover:text-purple-700 transition-colors">{{ $pg['title'] }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $pg['description'] }}</p>
                </div>
                <i data-lucide="arrow-right" class="w-4 h-4 text-gray-300 flex-shrink-0 group-hover:text-purple-400 transition-colors"></i>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- No results state --}}
    @if($totalResults === 0)
    <div class="text-center py-16">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-5">
            <i data-lucide="search-x" class="w-9 h-9 text-gray-400"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">"{{ $q }}" {{ __('app.search.no_results_for') }}</h3>
        <p class="text-gray-500 text-sm mb-8 max-w-sm mx-auto">{{ __('app.search.no_results_hint') }}</p>
        <div class="flex flex-wrap justify-center gap-2">
            @foreach(['interval','chord','scale','rhythm','piano','melodic','harmonic'] as $sug)
            <a href="/search?q={{ $sug }}"
               class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm hover:border-purple-300 hover:text-purple-700 transition-colors shadow-sm font-medium">
                {{ $sug }}
            </a>
            @endforeach
        </div>
    </div>
    @endif

@else

    {{-- ══ EMPTY STATE (no query yet) ══ --}}
    <div class="text-center">
        <p class="text-sm text-gray-500 mb-4">{{ __('app.search.popular') }}</p>
        <div class="flex flex-wrap justify-center gap-2 mb-12">
            @foreach(['interval','chord','scale','piano','rhythm','melodic','harmonic','dictation'] as $tag)
            <a href="/search?q={{ $tag }}"
               class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm hover:border-purple-300 hover:text-purple-700 transition-colors shadow-sm font-semibold">
                {{ $tag }}
            </a>
            @endforeach
        </div>

        {{-- All Practices showcase --}}
        <div class="text-left">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center">
                    <i data-lucide="headphones" class="w-4 h-4"></i>
                </div>
                <h2 class="text-base font-bold text-gray-900">{{ __('app.search.all_practices') }}</h2>
            </div>
            @php
            $allPractices = \App\Models\Practice::all();
            $allIconMap = ['single-note-practice'=>['icon'=>'circle-dot','color'=>'purple'],'melodic-interval-practice'=>['icon'=>'music','color'=>'blue'],'harmonic-interval-practice'=>['icon'=>'layers','color'=>'indigo'],'interval-direction-practice'=>['icon'=>'arrow-up-down','color'=>'violet'],'interval-comparison-practice'=>['icon'=>'bar-chart-2','color'=>'fuchsia'],'interval-construction-practice'=>['icon'=>'puzzle','color'=>'pink'],'chord-practice'=>['icon'=>'layers','color'=>'rose'],'scale-practice'=>['icon'=>'git-branch','color'=>'orange'],'rhythm-practice'=>['icon'=>'timer','color'=>'amber'],'melodic-dictation'=>['icon'=>'pencil','color'=>'teal']];
            $cBg=['purple'=>'bg-purple-50 hover:bg-purple-100 border-purple-100','blue'=>'bg-blue-50 hover:bg-blue-100 border-blue-100','indigo'=>'bg-indigo-50 hover:bg-indigo-100 border-indigo-100','violet'=>'bg-violet-50 hover:bg-violet-100 border-violet-100','fuchsia'=>'bg-fuchsia-50 hover:bg-fuchsia-100 border-fuchsia-100','pink'=>'bg-pink-50 hover:bg-pink-100 border-pink-100','rose'=>'bg-rose-50 hover:bg-rose-100 border-rose-100','orange'=>'bg-orange-50 hover:bg-orange-100 border-orange-100','amber'=>'bg-amber-50 hover:bg-amber-100 border-amber-100','teal'=>'bg-teal-50 hover:bg-teal-100 border-teal-100'];
            $cIc=['purple'=>'bg-purple-100 text-purple-600','blue'=>'bg-blue-100 text-blue-600','indigo'=>'bg-indigo-100 text-indigo-600','violet'=>'bg-violet-100 text-violet-600','fuchsia'=>'bg-fuchsia-100 text-fuchsia-600','pink'=>'bg-pink-100 text-pink-600','rose'=>'bg-rose-100 text-rose-600','orange'=>'bg-orange-100 text-orange-600','amber'=>'bg-amber-100 text-amber-600','teal'=>'bg-teal-100 text-teal-600'];
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                @foreach($allPractices as $p)
                @php $m=$allIconMap[$p->slug]??['icon'=>'music','color'=>'purple']; $c=$m['color']; @endphp
                <a href="{{ route('practice', $p->slug) }}"
                   class="tool-card group flex flex-col items-center text-center p-4 rounded-2xl border {{ $cBg[$c]??$cBg['purple'] }} transition-colors">
                    <div class="w-11 h-11 rounded-xl {{ $cIc[$c]??$cIc['purple'] }} flex items-center justify-center mb-2.5">
                        <i data-lucide="{{ $m['icon'] }}" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[11px] font-semibold text-gray-800 group-hover:text-gray-900 leading-tight">{{ $p->name }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>

@endif

</div>

<script>lucide.createIcons();</script>
</body>
</html>
