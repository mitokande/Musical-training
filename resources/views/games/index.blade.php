<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('app.nav.games') }} — {{ config('app.name', 'Harmoniva') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800,900" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] } } }
        }
    </script>

    <style>
        :root {
            --bg-base: #0c0918;
            --bg-surface: rgba(255,255,255,0.045);
            --bg-surface-hover: rgba(255,255,255,0.07);
            --border: rgba(255,255,255,0.09);
            --border-hover: rgba(255,255,255,0.18);
        }
        body { background: var(--bg-base); }

        .page-bg {
            background:
                radial-gradient(ellipse 70% 40% at 60% -10%, rgba(110,30,180,0.28) 0%, transparent 60%),
                radial-gradient(ellipse 50% 30% at 15% 70%, rgba(30,80,200,0.12) 0%, transparent 55%),
                var(--bg-base);
        }

        .note-float {
            position: absolute; font-size: 1.4rem;
            opacity: 0.07; user-select: none; pointer-events: none;
            animation: floatNote 7s ease-in-out infinite;
        }
        .note-float.d1 { animation-delay: 0s; }
        .note-float.d2 { animation-delay: 2.2s; }
        .note-float.d3 { animation-delay: 4.4s; }
        @keyframes floatNote {
            0%,100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .surface {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            backdrop-filter: blur(10px);
        }

        .game-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .game-card:hover {
            transform: translateY(-3px);
            border-color: var(--border-hover);
        }
        .game-card-note-rush:hover     { box-shadow: 0 16px 48px -8px rgba(251,191,36,0.18), 0 0 0 1px rgba(251,191,36,0.1); }
        .game-card-melody-memory:hover { box-shadow: 0 16px 48px -8px rgba(139,92,246,0.18), 0 0 0 1px rgba(139,92,246,0.1); }
        .game-card-interval-blitz:hover{ box-shadow: 0 16px 48px -8px rgba(56,189,248,0.18), 0 0 0 1px rgba(56,189,248,0.1); }
        .game-card-chord-clash:hover   { box-shadow: 0 16px 48px -8px rgba(244,63,94,0.18),  0 0 0 1px rgba(244,63,94,0.1); }
        .game-card-note-fall:hover     { box-shadow: 0 16px 48px -8px rgba(16,185,129,0.18), 0 0 0 1px rgba(16,185,129,0.1); }
        .game-card-note-catcher:hover  { box-shadow: 0 16px 48px -8px rgba(124,58,237,0.18), 0 0 0 1px rgba(124,58,237,0.1); }

        .play-btn { transition: opacity 0.15s ease, transform 0.15s ease; }
        .play-btn:hover  { opacity: 0.9; transform: scale(1.02); }
        .play-btn:active { transform: scale(0.97); }

        .skill-focus { font-size: 0.72rem; color: rgba(255,255,255,0.3); letter-spacing: 0.02em; line-height: 1.5; }

        .podium-gold   { background: linear-gradient(180deg, rgba(251,191,36,0.18) 0%, rgba(251,191,36,0.04) 100%); border-color: rgba(251,191,36,0.25); }
        .podium-silver { background: linear-gradient(180deg, rgba(148,163,184,0.13) 0%, rgba(148,163,184,0.03) 100%); border-color: rgba(148,163,184,0.2); }
        .podium-plat   { background: linear-gradient(180deg, rgba(167,139,250,0.13) 0%, rgba(167,139,250,0.03) 100%); border-color: rgba(167,139,250,0.2); }

        .challenge-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            border-left: 3px solid #f59e0b;
        }

        .cta-section {
            background: linear-gradient(135deg, rgba(88,28,135,0.25) 0%, rgba(30,27,75,0.3) 100%);
            border: 1px solid rgba(139,92,246,0.2);
        }
    </style>
</head>
<body class="font-sans antialiased page-bg min-h-screen">

    @include('partials.navbar', ['active' => 'games'])

    {{-- ─── HERO ─────────────────────────────────────────────────────────── --}}
    <section class="relative overflow-hidden">
        <span class="note-float d1" style="top:18%;left:3%;">♩</span>
        <span class="note-float d2" style="top:55%;left:1.5%;">♪</span>
        <span class="note-float d3" style="top:28%;right:4%;">♫</span>
        <span class="note-float d1" style="top:65%;right:2%;">♬</span>
        <span class="note-float d2" style="top:10%;right:14%;">𝄞</span>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-10 text-center">

            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/5 border border-white/10 text-white/55 text-xs font-medium mb-6">
                <i data-lucide="gamepad-2" class="w-3.5 h-3.5 text-purple-400 flex-shrink-0"></i>
                {{ __('app.games.hero_badge') }}
            </div>

            {{-- Heading --}}
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-[1.1] mb-5">
                {{ __('app.games.hero_title_1') }}<br>
                <span class="bg-gradient-to-r from-violet-400 via-fuchsia-400 to-rose-400 bg-clip-text text-transparent">{{ __('app.games.hero_title_2') }}</span>
            </h1>

            <p class="text-white/45 text-base lg:text-lg mb-8 leading-relaxed">
                {{ __('app.games.hero_desc') }}
            </p>

            {{-- Free plan notice (only for users who can upgrade) --}}
            @auth
                @if($dailyLimit !== -1 && auth()->user()->plan === 'free')
                <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-500/8 border border-amber-500/18 text-amber-300/80 text-xs font-medium">
                    <i data-lucide="clock" class="w-3.5 h-3.5 flex-shrink-0"></i>
                    {{ __('app.games.free_plan_limit', ['limit' => $dailyLimit]) }}
                    <span class="text-white/30">·</span>
                    <a href="{{ route('profile.edit') }}" class="underline decoration-dotted hover:text-amber-200 transition-colors">{{ __('app.games.upgrade_unlimited') }}</a>
                </div>
                @endif
            @endauth

        </div>
    </section>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-24">

        {{-- ─── GAME GRID ──────────────────────────────────────────────────── --}}
        @php
        $meta = [
            'note-rush' => [
                'icon'        => 'zap',
                'accent_rgb'  => '251,191,36',
                'accent_cls'  => 'text-amber-400',
                'btn_from'    => '#d97706',
                'btn_to'      => '#f59e0b',
                'cat'         => __('app.games.cat_notes'),
                'type'        => __('app.games.type_speed'),
                'desc'        => __('app.games.nr_idx_desc'),
                'skill'       => __('app.games.nr_idx_skill'),
            ],
            'melody-memory' => [
                'icon'        => 'music',
                'accent_rgb'  => '139,92,246',
                'accent_cls'  => 'text-violet-400',
                'btn_from'    => '#7c3aed',
                'btn_to'      => '#8b5cf6',
                'cat'         => __('app.games.cat_melody'),
                'type'        => __('app.games.type_memory'),
                'desc'        => __('app.games.mm_idx_desc'),
                'skill'       => __('app.games.mm_idx_skill'),
            ],
            'interval-blitz' => [
                'icon'        => 'timer',
                'accent_rgb'  => '56,189,248',
                'accent_cls'  => 'text-sky-400',
                'btn_from'    => '#0284c7',
                'btn_to'      => '#38bdf8',
                'cat'         => __('app.games.cat_intervals'),
                'type'        => __('app.games.type_challenge'),
                'desc'        => __('app.games.ib_idx_desc'),
                'skill'       => __('app.games.ib_idx_skill'),
            ],
            'chord-clash' => [
                'icon'        => 'layers',
                'accent_rgb'  => '244,63,94',
                'accent_cls'  => 'text-rose-400',
                'btn_from'    => '#e11d48',
                'btn_to'      => '#f43f5e',
                'cat'         => __('app.games.cat_chords'),
                'type'        => __('app.games.type_harmony'),
                'desc'        => __('app.games.cc_idx_desc'),
                'skill'       => __('app.games.cc_idx_skill'),
            ],
            'note-fall' => [
                'icon'        => 'arrow-down-to-line',
                'accent_rgb'  => '16,185,129',
                'accent_cls'  => 'text-emerald-400',
                'btn_from'    => '#059669',
                'btn_to'      => '#10b981',
                'cat'         => __('app.games.cat_notes'),
                'type'        => __('app.games.type_reflex'),
                'desc'        => __('app.games.nf_idx_desc'),
                'skill'       => __('app.games.nf_idx_skill'),
            ],
            'note-catcher' => [
                'icon'        => 'move-horizontal',
                'accent_rgb'  => '124,58,237',
                'accent_cls'  => 'text-purple-400',
                'btn_from'    => '#6d28d9',
                'btn_to'      => '#7c3aed',
                'cat'         => __('app.games.cat_notes'),
                'type'        => __('app.games.type_arcade'),
                'desc'        => __('app.games.nc_idx_desc'),
                'skill'       => __('app.games.nc_idx_skill'),
            ],
        ];

        $diffLabels = [
            'Beginner'     => ['dot' => '#22c55e', 'text' => 'text-emerald-400/70'],
            'Intermediate' => ['dot' => '#f59e0b', 'text' => 'text-amber-400/70'],
            'Advanced'     => ['dot' => '#f43f5e', 'text' => 'text-rose-400/70'],
        ];
        @endphp

        <div id="games" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-16">
            @foreach(\App\Http\Controllers\GameController::GAMES as $slug => $game)
            @php
                $m    = $meta[$slug];
                $best = $scores[$slug] ?? null;
                $diff = $game['difficulty'];
                $diffStyle = $diffLabels[$diff] ?? ['dot' => '#94a3b8', 'text' => 'text-slate-400/70'];
            @endphp

            <a href="{{ route('games.show', $slug) }}"
               class="game-card game-card-{{ $slug }} rounded-2xl p-5 flex flex-col gap-4 no-underline group"
               aria-label="{{ $game['name'] }}">

                {{-- Top row: icon + badges --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background: rgba({{ $m['accent_rgb'] }},0.12); border: 1px solid rgba({{ $m['accent_rgb'] }},0.2);">
                        <i data-lucide="{{ $m['icon'] }}" class="w-5 h-5 {{ $m['accent_cls'] }}"></i>
                    </div>
                    <div class="flex flex-wrap gap-1.5 justify-end">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                              style="background: rgba({{ $m['accent_rgb'] }},0.1); color: rgba({{ $m['accent_rgb'] }},1); border: 1px solid rgba({{ $m['accent_rgb'] }},0.18);">
                            {{ $m['cat'] }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                              style="background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.5); border: 1px solid rgba(255,255,255,0.09);">
                            {{ $m['type'] }}
                        </span>
                    </div>
                </div>

                {{-- Title + description --}}
                <div class="flex-1">
                    <h2 class="text-white font-bold text-base leading-tight mb-2">{{ $game['name'] }}</h2>
                    <p class="text-white/45 text-sm leading-relaxed">{{ $m['desc'] }}</p>
                </div>

                {{-- Skill focus --}}
                <div class="flex items-start gap-1.5">
                    <i data-lucide="activity" class="w-3 h-3 mt-0.5 flex-shrink-0 {{ $m['accent_cls'] }} opacity-60"></i>
                    <span class="skill-focus">{{ $m['skill'] }}</span>
                </div>

                {{-- Divider --}}
                <div style="border-top: 1px solid rgba(255,255,255,0.07);"></div>

                {{-- Footer: difficulty + score --}}
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background: {{ $diffStyle['dot'] }};"></span>
                        <span class="text-xs {{ $diffStyle['text'] }}">{{ $diff }}</span>
                    </span>
                    @if($best)
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="trophy" class="w-3.5 h-3.5 text-amber-400/80"></i>
                        <span class="text-white/60 text-xs font-semibold">{{ number_format($best->score) }}</span>
                    </div>
                    @else
                    <span class="text-white/20 text-xs">{{ __('app.games.no_plays_yet') }}</span>
                    @endif
                </div>

                {{-- Play button --}}
                <button class="play-btn w-full flex items-center justify-center gap-2 py-3 rounded-xl text-white font-semibold text-sm"
                        style="background: linear-gradient(135deg, {{ $m['btn_from'] }}, {{ $m['btn_to'] }}); box-shadow: 0 4px 16px -4px rgba({{ $m['accent_rgb'] }},0.35);">
                    <i data-lucide="play" class="w-4 h-4 fill-current"></i>
                    {{ __('app.games.play_now') }}
                </button>

            </a>
            @endforeach
        </div>

        {{-- ─── HALL OF FAME ───────────────────────────────────────────────── --}}
        @if($globalLeaderboard->count() > 0)
        @php $top3 = $globalLeaderboard->take(3); @endphp

        <div class="mb-4 flex items-center gap-2">
            <i data-lucide="trophy" class="w-5 h-5 text-amber-400"></i>
            <h2 class="text-white font-bold text-lg">{{ __('app.games.hall_of_fame') }}</h2>
            <span class="text-white/30 text-xs">— {{ __('app.games.hall_of_fame_sub') }}</span>
        </div>

        <div class="surface rounded-2xl p-6 mb-6">
            @php
            $second = $top3->get(1);
            $first  = $top3->get(0);
            $third  = $top3->get(2);
            @endphp
            <div class="flex items-end justify-center gap-3" style="height:180px;">

                {{-- 2nd --}}
                <div class="flex flex-col items-center flex-1 max-w-[130px]">
                    <div class="text-2xl mb-1">🥈</div>
                    @if($second)
                    <div class="text-white font-semibold text-xs truncate w-full text-center mb-1">{{ $second->user?->name ?? '—' }}</div>
                    <div class="text-slate-300 font-black text-base">{{ number_format($second->total_score) }}</div>
                    @else
                    <div class="text-white/15 text-sm">—</div>
                    @endif
                    <div class="podium-silver border rounded-t-xl w-full flex items-center justify-center font-black text-slate-400 text-xl mt-2" style="height:88px;">2</div>
                </div>

                {{-- 1st --}}
                <div class="flex flex-col items-center flex-1 max-w-[130px]">
                    <div class="text-3xl mb-1">🥇</div>
                    @if($first)
                    <div class="text-white font-semibold text-xs truncate w-full text-center mb-0.5">{{ $first->user?->name ?? '—' }}</div>
                    <div class="text-amber-300 font-black text-lg">{{ number_format($first->total_score) }}</div>
                    @else
                    <div class="text-white/15 text-sm">—</div>
                    @endif
                    <div class="podium-gold border rounded-t-xl w-full flex items-center justify-center font-black text-amber-400 text-xl mt-2" style="height:115px;">1</div>
                </div>

                {{-- 3rd --}}
                <div class="flex flex-col items-center flex-1 max-w-[130px]">
                    <div class="text-2xl mb-1">🏅</div>
                    @if($third)
                    <div class="text-white font-semibold text-xs truncate w-full text-center mb-1">{{ $third->user?->name ?? '—' }}</div>
                    <div class="text-violet-300 font-black text-base">{{ number_format($third->total_score) }}</div>
                    @else
                    <div class="text-white/15 text-sm">—</div>
                    @endif
                    <div class="podium-plat border rounded-t-xl w-full flex items-center justify-center font-black text-violet-400 text-xl mt-2" style="height:68px;">3</div>
                </div>

            </div>
        </div>

        {{-- ─── GLOBAL TOP 20 + SIDEBAR ────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-12">

            {{-- Global Top 20 list --}}
            <div class="lg:col-span-2 surface rounded-2xl p-6">
                <div class="flex items-center gap-2 mb-4">
                    <i data-lucide="list-ordered" class="w-4 h-4 text-violet-400/70"></i>
                    <p class="text-white/35 text-xs font-semibold uppercase tracking-wider">{{ __('app.games.global_top20') }}</p>
                    <span class="text-white/20 text-xs">— {{ __('app.games.global_top20_sub') }}</span>
                </div>

                <div class="space-y-0.5">
                    @foreach($globalLeaderboard as $i => $entry)
                    @php
                    $rankColors = ['text-amber-400','text-slate-400','text-violet-400'];
                    $rc = $rankColors[$i] ?? 'text-white/25';
                    $country = $entry->user?->country;
                    @endphp
                    <div class="flex items-center gap-3 px-2.5 py-2 rounded-xl {{ $i < 3 ? 'bg-white/4' : 'hover:bg-white/3' }} transition-colors">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-black flex-shrink-0 {{ $i < 3 ? 'bg-white/8' : '' }} {{ $rc }}">{{ $i + 1 }}</span>
                        <span class="text-white text-sm font-medium flex-1 truncate">{{ $entry->user?->name ?? 'Unknown' }}</span>
                        @if($country)
                        <span class="text-white/35 text-xs flex-shrink-0">{{ $country }}</span>
                        @endif
                        <span class="text-white/30 text-xs mr-1.5 flex-shrink-0">{{ $entry->games_played }} {{ __('app.games.games_played') }}</span>
                        <span class="font-bold text-sm {{ $rc }} flex-shrink-0">{{ number_format($entry->total_score) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Sidebar: daily challenge + mini stats --}}
            <div class="flex flex-col gap-4">

                {{-- Daily Challenge --}}
                <div class="challenge-card rounded-2xl p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="flame" class="w-4 h-4 text-amber-400"></i>
                        <span class="text-amber-300 text-xs font-semibold uppercase tracking-wider">{{ __('app.games.daily_challenge') }}</span>
                    </div>
                    <p class="text-white font-bold text-base leading-tight mb-1">Score 500+ in Note Rush</p>
                    <p class="text-white/40 text-sm mb-4">{{ __('app.games.skill_focus') }}: Staff reading speed</p>
                    <a href="{{ route('games.show', 'note-rush') }}"
                       class="block w-full text-center py-2.5 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 border border-amber-500/25 text-amber-300 text-sm font-semibold transition-all">
                        {{ __('app.games.challenge_cta') }} →
                    </a>
                </div>

                {{-- Mini stats --}}
                <div class="surface rounded-2xl px-5 py-4">
                    <div class="grid grid-cols-3 divide-x divide-white/8 text-center">
                        <div class="px-2">
                            <div class="text-white font-bold text-lg">6</div>
                            <div class="text-white/35 text-xs mt-0.5">games</div>
                        </div>
                        <div class="px-2">
                            <div class="text-white font-bold text-lg">
                                <i data-lucide="volume-2" class="w-4 h-4 inline text-violet-400"></i>
                            </div>
                            <div class="text-white/35 text-xs mt-0.5">audio</div>
                        </div>
                        <div class="px-2">
                            <div class="text-white font-bold text-lg">
                                <i data-lucide="smartphone" class="w-4 h-4 inline text-emerald-400"></i>
                            </div>
                            <div class="text-white/35 text-xs mt-0.5">mobile</div>
                        </div>
                    </div>
                </div>

                {{-- Audio note --}}
                <div class="surface rounded-2xl px-5 py-4">
                    <div class="flex items-start gap-3">
                        <i data-lucide="volume-2" class="w-4 h-4 text-violet-400/70 flex-shrink-0 mt-0.5"></i>
                        <p class="text-white/35 text-xs leading-relaxed">{{ __('app.games.audio_note') }}</p>
                    </div>
                </div>

            </div>

        </div>

        @endif

        {{-- ─── ACCOUNT CTA (guests only) ─────────────────────────────────── --}}
        @guest
        <div class="cta-section rounded-2xl p-8 mb-12 text-center">
            <div class="w-12 h-12 rounded-2xl bg-violet-500/20 border border-violet-500/25 flex items-center justify-center mx-auto mb-5">
                <i data-lucide="music-2" class="w-6 h-6 text-violet-300"></i>
            </div>
            <h2 class="text-white font-extrabold text-2xl mb-3">{{ __('app.games.cta_heading') }}</h2>
            <p class="text-white/45 text-sm max-w-md mx-auto mb-7 leading-relaxed">
                {{ __('app.games.cta_desc') }}
            </p>

            <div class="flex flex-wrap justify-center gap-6 mb-8">
                <div class="flex items-center gap-2 text-white/60 text-sm">
                    <i data-lucide="infinity" class="w-4 h-4 text-violet-400"></i>
                    {{ __('app.popup.unlimited') }}
                </div>
                <div class="flex items-center gap-2 text-white/60 text-sm">
                    <i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i>
                    {{ __('app.games.save_score_login') }}
                </div>
                <div class="flex items-center gap-2 text-white/60 text-sm">
                    <i data-lucide="bar-chart-2" class="w-4 h-4 text-sky-400"></i>
                    {{ __('app.popup.track') }}
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl font-bold text-white text-sm transition-all hover:opacity-90 active:scale-[0.98]"
                   style="background: linear-gradient(135deg, #7c3aed, #a855f7); box-shadow: 0 8px 24px rgba(124,58,237,0.35);">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    {{ __('app.popup.sign_up') }}
                </a>
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl font-semibold text-white/55 text-sm border border-white/10 hover:text-white hover:border-white/25 transition-all">
                    {{ __('app.games.log_in') }}
                </a>
            </div>
        </div>
        @endguest

        {{-- ─── BRIDGE LINK ────────────────────────────────────────────────── --}}
        <div class="text-center py-4">
            <span class="text-white/25 text-sm">{{ __('app.games.structured_prompt') }}</span>
            <a href="{{ route('learn') }}" class="text-violet-400 hover:text-violet-300 text-sm ml-2 transition-colors">
                {{ __('app.games.back_practice') }} →
            </a>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
    </script>

    @include('partials.footer')

    @include('partials.guest-timer-popup', ['timerKey' => 'music-games'])
</body>
</html>
