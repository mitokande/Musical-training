<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $game['name'] }} — {{ config('app.name', 'Harmoniva') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800,900" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tone/15.3.5/Tone.js"
            integrity="sha512-F1myjNkIKU5XJtOs1HXRo/zOjiUsABgFEEGKLx/riwK82jRThZFebEnfF2HWo9eeC+iC1Nwwnn9Vj6OGq+r7rQ=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>

    <style>
        body { background: #0f0a1e; }
        .game-surface {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.10);
            backdrop-filter: blur(12px);
        }
        .answer-btn {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            transition: all 0.15s ease;
        }
        .answer-btn:hover:not(:disabled) {
            background: rgba(255,255,255,0.12);
            border-color: rgba(255,255,255,0.3);
            transform: scale(1.03);
        }
        .answer-btn:active:not(:disabled) { transform: scale(0.97); }
        .answer-btn.correct {
            background: rgba(34,197,94,0.2) !important;
            border-color: rgba(34,197,94,0.6) !important;
            color: #4ade80 !important;
        }
        .answer-btn.wrong {
            background: rgba(239,68,68,0.18) !important;
            border-color: rgba(239,68,68,0.5) !important;
            color: #f87171 !important;
        }
        .answer-btn.disabled-neutral {
            background: rgba(255,255,255,0.03) !important;
            border-color: rgba(255,255,255,0.06) !important;
            color: rgba(255,255,255,0.2) !important;
        }
        .answer-btn:disabled { cursor: not-allowed; }
        .leaderboard-row:first-child .rank-badge { background: linear-gradient(135deg,#fbbf24,#f59e0b); }
        .leaderboard-row:nth-child(2) .rank-badge { background: linear-gradient(135deg,#9ca3af,#6b7280); }
        .leaderboard-row:nth-child(3) .rank-badge { background: linear-gradient(135deg,#cd7c3e,#a16207); }

        /* Bottom leaderboard tiers */
        .tier-gold {
            background: linear-gradient(135deg, rgba(251,191,36,0.09) 0%, rgba(245,158,11,0.04) 100%);
            border: 2px solid rgba(251,191,36,0.28);
        }
        .tier-silver {
            background: linear-gradient(135deg, rgba(148,163,184,0.07) 0%, rgba(100,116,139,0.03) 100%);
            border: 1px solid rgba(148,163,184,0.18);
        }
        .tier-bronze {
            background: linear-gradient(135deg, rgba(180,110,50,0.07) 0%, rgba(120,70,30,0.03) 100%);
            border: 1px solid rgba(180,110,50,0.14);
        }
    </style>
</head>
<body class="font-sans antialiased min-h-screen">

    @include('partials.navbar', ['active' => 'games'])

    {{-- Localized strings for Alpine.js game components --}}
    <script>
    window.GAME_STRINGS = {
        ready:        @json(__('app.games.status_ready')),
        playing:      @json(__('app.games.status_playing')),
        gameOver:     @json(__('app.games.status_game_over')),
        listening:    @json(__('app.games.status_listening')),
        yourTurn:     @json(__('app.games.status_your_turn')),
        correct:      @json(__('app.games.status_correct')),
        level:        @json(__('app.games.status_level')),
        round:        @json(__('app.games.status_round')),
        correctLabel: @json(__('app.games.correct_label')),
        bestStreak:   @json(__('app.games.best_streak')),
        points:       @json(__('app.games.points')),
        rounds:       @json(__('app.games.rounds')),
        streak:       @json(__('app.games.streak')),
        sec:          @json(__('app.games.sec')),
        newBest:      @json(__('app.games.new_personal_best')),
        startGame:    @json(__('app.games.start_game')),
        playAgain:    @json(__('app.games.play_again')),
        finalScore:   @json(__('app.games.final_score')),
        personalBest: @json(__('app.games.personal_best')),
        listenCarefully: @json(__('app.games.listen_carefully')),
        replayMelody:    @json(__('app.games.replay_melody')),
        youReached:      @json(__('app.games.you_reached')),
        bestRound:       @json(__('app.games.best_round')),
        whatNote:        @json(__('app.games.what_note')),
        tapReplay:       @json(__('app.games.tap_replay')),
        whatInterval:    @json(__('app.games.what_interval')),
        diff_easy:       @json(__('app.games.diff_easy')),
        diff_medium:     @json(__('app.games.diff_medium')),
        diff_hard:       @json(__('app.games.diff_hard')),
        findChord:       @json(__('app.games.find_chord')),
        chordNum:        @json(__('app.games.chord_num')),
        thisOne:         @json(__('app.games.this_one')),
        playBoth:        @json(__('app.games.play_both')),
        noteRushDesc:    @json(__('app.games.note_rush_desc')),
        melodyDesc:      @json(__('app.games.melody_desc')),
        intervalDesc:    @json(__('app.games.interval_desc')),
        chordDesc:       @json(__('app.games.chord_desc')),
        noteFallDesc:    @json(__('app.games.note_fall_desc')),
        noteCatcherDesc: @json(__('app.games.note_catcher_desc')),
    };
    </script>

    <div class="max-w-7xl mx-auto px-[5px] sm:px-6 lg:px-8 pt-5 sm:pt-[50px] pb-6">

        <div class="grid grid-cols-1 lg:grid-cols-7 gap-6">

            <!-- Game Panel -->
            <div class="lg:col-span-5">
                @include('partials.games.' . $slug, [
                    'personalBest'    => $personalBest,
                    'canPlay'         => $canPlay,
                    'dailyLimit'      => $dailyLimit,
                    'dailyPlaysUsed'  => $dailyPlaysUsed,
                    'slug'            => $slug,
                ])
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-2 space-y-4">

                <!-- How to play -->
                <div class="game-surface rounded-2xl p-5">
                    <h3 class="text-white/50 text-xs font-semibold uppercase tracking-wider mb-3">{{ __('app.games.how_to_play') }}</h3>
                    @switch($slug)
                        @case('note-rush')
                        <ul class="space-y-2 text-white/80 text-sm">
                            <li class="flex gap-2"><i data-lucide="volume-2" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-orange-400"></i> {{ __('app.games.how_note_rush_1') }}</li>
                            <li class="flex gap-2"><i data-lucide="zap" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-orange-400"></i> {{ __('app.games.how_note_rush_2') }}</li>
                            <li class="flex gap-2"><i data-lucide="timer" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-orange-400"></i> {{ __('app.games.how_note_rush_3') }}</li>
                        </ul>
                        @break
                        @case('melody-memory')
                        <ul class="space-y-2 text-white/80 text-sm">
                            <li class="flex gap-2"><i data-lucide="ear" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-purple-400"></i> {{ __('app.games.how_melody_1') }}</li>
                            <li class="flex gap-2"><i data-lucide="mouse-pointer" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-purple-400"></i> {{ __('app.games.how_melody_2') }}</li>
                            <li class="flex gap-2"><i data-lucide="x-circle" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-purple-400"></i> {{ __('app.games.how_melody_3') }}</li>
                        </ul>
                        @break
                        @case('interval-blitz')
                        <ul class="space-y-2 text-white/80 text-sm">
                            <li class="flex gap-2"><i data-lucide="volume-2" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-sky-400"></i> {{ __('app.games.how_interval_1') }}</li>
                            <li class="flex gap-2"><i data-lucide="timer" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-sky-400"></i> {{ __('app.games.how_interval_2') }}</li>
                            <li class="flex gap-2"><i data-lucide="heart" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-sky-400"></i> {{ __('app.games.how_interval_3') }}</li>
                        </ul>
                        @break
                        @case('chord-clash')
                        <ul class="space-y-2 text-white/80 text-sm">
                            <li class="flex gap-2"><i data-lucide="volume-2" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-rose-400"></i> {{ __('app.games.how_chord_1') }}</li>
                            <li class="flex gap-2"><i data-lucide="mouse-pointer" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-rose-400"></i> {{ __('app.games.how_chord_2') }}</li>
                            <li class="flex gap-2"><i data-lucide="trending-up" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-rose-400"></i> {{ __('app.games.how_chord_3') }}</li>
                        </ul>
                        @break
                        @case('note-fall')
                        <ul class="space-y-2 text-white/80 text-sm">
                            <li class="flex gap-2"><i data-lucide="arrow-down" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-emerald-400"></i> {{ __('app.games.how_note_fall_1') }}</li>
                            <li class="flex gap-2"><i data-lucide="piano" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-emerald-400"></i> {{ __('app.games.how_note_fall_2') }}</li>
                            <li class="flex gap-2"><i data-lucide="trending-up" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-emerald-400"></i> {{ __('app.games.how_note_fall_3') }}</li>
                        </ul>
                        @break
                        @case('note-catcher')
                        <ul class="space-y-2 text-white/80 text-sm">
                            <li class="flex gap-2"><i data-lucide="move-horizontal" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-violet-400"></i> {{ __('app.games.how_note_catcher_1') }}</li>
                            <li class="flex gap-2"><i data-lucide="target" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-violet-400"></i> {{ __('app.games.how_note_catcher_2') }}</li>
                            <li class="flex gap-2"><i data-lucide="keyboard" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-violet-400"></i> {{ __('app.games.how_note_catcher_3') }}</li>
                        </ul>
                        @break
                    @endswitch
                </div>

                <!-- Personal Best -->
                <div class="game-surface rounded-2xl p-5">
                    <h3 class="text-white/50 text-xs font-semibold uppercase tracking-wider mb-3">{{ __('app.games.your_best') }}</h3>
                    @guest
                    <p class="text-white/30 text-sm mb-3">{{ __('app.games.save_score_login') }}</p>
                    <a href="{{ route('register') }}" class="text-xs text-purple-400 hover:text-purple-300 font-semibold">{{ __('app.games.sign_up_arrow') }}</a>
                    @else
                    @if($personalBestRecord)
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-black text-white">{{ number_format($personalBestRecord->score) }}</span>
                        <span class="text-white/40 text-sm">pts</span>
                    </div>
                    <div class="flex items-center gap-3 text-white/40 text-xs">
                        <span class="flex items-center gap-1">
                            <i data-lucide="flame" class="w-3.5 h-3.5 text-orange-400"></i>
                            {{ $personalBestRecord->max_streak }} {{ __('app.games.streak') }}
                        </span>
                        <span class="flex items-center gap-1">
                            <i data-lucide="layers" class="w-3.5 h-3.5 text-blue-400"></i>
                            Lv.{{ $personalBestRecord->level_reached }}
                        </span>
                    </div>
                    @else
                    <p class="text-white/30 text-sm">{{ __('app.games.play_first_game') }}</p>
                    @endif
                    @endguest
                </div>

                <!-- Leaderboard -->
                @if($canAccessLeaderboard && $weeklyLeaderboard->isNotEmpty())
                <div class="game-surface rounded-2xl p-5">
                    <div x-data="{ tab: 'weekly' }">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-white/50 text-xs font-semibold uppercase tracking-wider">{{ __('app.games.leaderboard') }}</h3>
                            <div class="flex rounded-lg overflow-hidden border border-white/10">
                                <button @click="tab='weekly'"
                                        :class="tab==='weekly' ? 'bg-white/15 text-white' : 'text-white/40'"
                                        class="text-xs px-3 py-1.5 font-medium transition-colors">{{ __('app.games.this_week') }}</button>
                                <button @click="tab='alltime'"
                                        :class="tab==='alltime' ? 'bg-white/15 text-white' : 'text-white/40'"
                                        class="text-xs px-3 py-1.5 font-medium transition-colors">{{ __('app.games.all_time') }}</button>
                            </div>
                        </div>
                        <div x-show="tab==='weekly'" class="space-y-2">
                            @foreach($weeklyLeaderboard as $i => $entry)
                            <div class="leaderboard-row flex items-center gap-3 py-1.5">
                                <div class="rank-badge w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                     style="background:rgba(255,255,255,0.1)">{{ $i + 1 }}</div>
                                <span class="text-white text-sm font-medium truncate flex-1">{{ $entry->user->name }}</span>
                                <span class="text-white font-bold text-sm">{{ number_format($entry->best_score) }}</span>
                            </div>
                            @endforeach
                            @if($userWeeklyRank)
                            <div class="mt-3 pt-3 border-t border-white/10 text-white/40 text-xs text-center">
                                {{ __('app.games.your_rank') }} <span class="text-white font-bold">#{{ $userWeeklyRank }}</span>
                            </div>
                            @endif
                        </div>
                        <div x-show="tab==='alltime'" class="space-y-2" x-cloak>
                            @foreach($allTimeLeaderboard as $i => $entry)
                            <div class="leaderboard-row flex items-center gap-3 py-1.5">
                                <div class="rank-badge w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                     style="background:rgba(255,255,255,0.1)">{{ $i + 1 }}</div>
                                <span class="text-white text-sm font-medium truncate flex-1">{{ $entry->user->name }}</span>
                                <span class="text-white font-bold text-sm">{{ number_format($entry->best_score) }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @elseif(!$canAccessLeaderboard)
                <div class="game-surface rounded-2xl p-5 text-center">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="trophy" class="w-5 h-5 text-amber-400"></i>
                    </div>
                    <p class="text-white/60 text-sm font-medium mb-1">{{ __('app.games.global_leaderboard') }}</p>
                    @guest
                    <p class="text-white/30 text-xs mb-4">{{ __('app.games.save_rank_desc') }}</p>
                    <a href="{{ route('register') }}"
                       class="inline-block text-xs font-semibold px-4 py-2 rounded-lg bg-gradient-to-r from-purple-500 to-pink-500 text-white">
                        {{ __('app.games.free_sign_up') }}
                    </a>
                    @else
                    <p class="text-white/30 text-xs mb-4">{{ __('app.games.leaderboard_premium') }}</p>
                    <a href="{{ route('profile.edit') }}"
                       class="inline-block text-xs font-semibold px-4 py-2 rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 text-white">
                        {{ __('app.games.upgrade_premium') }}
                    </a>
                    @endguest
                </div>
                @endif


            </div>
        </div>

        {{-- ─── ALL-TIME TOP 30 ────────────────────────────────────────────── --}}
        @if($canAccessLeaderboard && $allTimeLeaderboard->isNotEmpty())
        <div class="mt-8 mb-4">
            <div class="flex items-center gap-2 mb-5">
                <i data-lucide="list-ordered" class="w-5 h-5 text-amber-400"></i>
                <h2 class="text-white font-bold text-base">{{ __('app.games.all_time') }} — Top 30</h2>
                <span class="text-white/30 text-xs">{{ $game['name'] }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                {{-- Box 1: Ranks 1–10 (Gold) --}}
                <div class="tier-gold rounded-2xl p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-xl leading-none">🥇</span>
                        <span class="text-amber-300 font-bold text-sm tracking-wide">Top 1 – 10</span>
                    </div>
                    <div class="space-y-0.5">
                        @foreach($allTimeLeaderboard->slice(0, 10) as $i => $entry)
                        <div class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-white/4 transition-colors">
                            <span class="w-5 text-right text-xs font-black flex-shrink-0 {{ $i < 3 ? 'text-amber-400' : 'text-white/20' }}">{{ $i + 1 }}</span>
                            <span class="text-white text-xs font-medium flex-1 truncate">{{ $entry->user->name }}</span>
                            @if($entry->user->country)
                            <span class="text-white/30 text-xs flex-shrink-0">{{ $entry->user->country }}</span>
                            @endif
                            <span class="text-amber-300 font-bold text-xs flex-shrink-0">{{ number_format($entry->best_score) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Box 2: Ranks 11–20 (Silver) --}}
                <div class="tier-silver rounded-2xl p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-xl leading-none">🥈</span>
                        <span class="text-slate-300 font-bold text-sm tracking-wide">Top 11 – 20</span>
                    </div>
                    <div class="space-y-0.5">
                        @foreach($allTimeLeaderboard->slice(10, 10) as $i => $entry)
                        <div class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-white/4 transition-colors">
                            <span class="w-5 text-right text-xs font-black flex-shrink-0 text-white/20">{{ $i + 1 }}</span>
                            <span class="text-white/75 text-xs font-medium flex-1 truncate">{{ $entry->user->name }}</span>
                            @if($entry->user->country)
                            <span class="text-white/25 text-xs flex-shrink-0">{{ $entry->user->country }}</span>
                            @endif
                            <span class="text-slate-400 font-bold text-xs flex-shrink-0">{{ number_format($entry->best_score) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Box 3: Ranks 21–30 (Bronze) --}}
                <div class="tier-bronze rounded-2xl p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-xl leading-none">🏅</span>
                        <span class="text-orange-400/75 font-bold text-sm tracking-wide">Top 21 – 30</span>
                    </div>
                    <div class="space-y-0.5">
                        @foreach($allTimeLeaderboard->slice(20, 10) as $i => $entry)
                        <div class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-white/4 transition-colors">
                            <span class="w-5 text-right text-xs font-black flex-shrink-0 text-white/15">{{ $i + 1 }}</span>
                            <span class="text-white/55 text-xs font-medium flex-1 truncate">{{ $entry->user->name }}</span>
                            @if($entry->user->country)
                            <span class="text-white/20 text-xs flex-shrink-0">{{ $entry->user->country }}</span>
                            @endif
                            <span class="text-orange-400/55 font-bold text-xs flex-shrink-0">{{ number_format($entry->best_score) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
        @endif

    </div>

    {{-- HarmonivaAudio — Tone.js wrapper --}}
    <script>
    window.HarmonivaAudio = (function () {
        let sampler = null;
        function init() {
            if (sampler) return;
            sampler = new Tone.Sampler({
                urls: {
                    C4: 'C4.mp3', 'D#4': 'Ds4.mp3', 'F#4': 'Fs4.mp3',
                    A4: 'A4.mp3', C5: 'C5.mp3',
                },
                release: 1,
                baseUrl: 'https://tonejs.github.io/audio/salamander/',
            }).toDestination();
        }
        return {
            async playNote(note, duration) {
                await Tone.start(); if (!sampler) init();
                sampler.triggerAttackRelease(note, duration ?? 1);
            },
            async playChord(notes, duration) {
                await Tone.start(); if (!sampler) init();
                const now = Tone.now();
                notes.forEach(n => sampler.triggerAttackRelease(n, duration ?? 2, now));
            },
            async playSequence(notes, intervalMs, duration) {
                await Tone.start(); if (!sampler) init();
                const now = Tone.now();
                notes.forEach((n, i) => sampler.triggerAttackRelease(n, duration ?? 1, now + i * ((intervalMs ?? 600) / 1000)));
            },
            stop() { if (sampler) sampler.releaseAll(); }
        };
    })();
    </script>

    {{-- Alpine.js loaded after all alpine:init listeners are registered --}}
    <script src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js" defer></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
    </script>

    @include('partials.guest-timer-popup', ['timerKey' => 'music-games'])
</body>
</html>
