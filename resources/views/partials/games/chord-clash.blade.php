{{-- Chord Clash game partial --}}

@if(!$canPlay)
    <div class="game-surface rounded-2xl p-10 text-center">
        <div class="w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center mx-auto mb-5">
            <i data-lucide="lock" class="w-8 h-8 text-amber-400"></i>
        </div>
        <h2 class="text-white text-xl font-bold mb-2">{{ __('app.games.daily_limit_title') }}</h2>
        <p class="text-white/40 text-sm max-w-xs mx-auto mb-6">
            @guest
                {{ __('app.games.daily_limit_guest_desc') }}
            @else
                {{ __('app.games.daily_limit_desc', ['limit' => $perTypeLimit]) }}
            @endguest
        </p>
        @guest
            <a href="{{ route('register') }}"
               class="inline-block px-6 py-3 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold text-sm">
                {{ __('app.popup.sign_up') }}
            </a>
        @else
            @if(auth()->user()->plan === 'free')
            <a href="{{ route('profile.edit') }}"
               class="inline-block px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white font-semibold text-sm">
                {{ __('app.games.upgrade_premium') }}
            </a>
            @endif
        @endguest
    </div>
@else

<div
    id="chord-clash-root"
    x-data="chordClashGame"
    x-init="init()"
    class="game-surface rounded-2xl overflow-hidden"
>
    {{-- Header --}}
    <div class="bg-gradient-to-r from-rose-500/20 to-pink-600/20 border-b border-white/10 p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('games.index') }}"
                   class="w-10 h-10 rounded-xl bg-white border border-white/20 flex items-center justify-center text-red-600 font-bold hover:bg-white/90 transition-all flex-shrink-0">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-400 to-pink-600 flex items-center justify-center shrink-0">
                    <i data-lucide="layers" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <div class="text-white font-bold text-sm">{{ __('app.games.chord_clash.title') }}</div>
                    <div class="text-white/40 text-xs"
                         x-text="gameState === 'idle' ? '{{ __('app.games.status_ready') }}' : (gameState === 'playing' || gameState === 'levelup' ? '{{ __('app.games.status_level') }} ' + currentLevel + ' · ' + levelName : '{{ __('app.games.status_game_over') }}')"></div>
                </div>
            </div>
            <div class="flex items-center gap-3 sm:gap-5">
                {{-- Lives --}}
                <div class="flex items-center gap-0.5" x-show="gameState !== 'idle'">
                    <template x-for="i in maxLives" :key="i">
                        <span class="text-base sm:text-lg transition-all"
                              :class="i <= lives ? 'text-rose-400' : 'opacity-20 grayscale'">❤</span>
                    </template>
                </div>
                <div class="text-center" x-show="gameState !== 'idle'">
                    <div class="text-white/40 text-xs">{{ __('app.games.points') }}</div>
                    <div class="text-white font-black text-lg sm:text-xl tabular-nums" x-text="score.toLocaleString()"></div>
                </div>
                <div class="text-center" x-show="gameState !== 'idle'">
                    <div class="text-white/40 text-xs">{{ __('app.games.streak') }}</div>
                    <div class="font-black text-lg sm:text-xl tabular-nums"
                         :class="streak > 0 ? 'text-rose-400' : 'text-white/30'"
                         x-text="streak"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-5 sm:p-8 min-h-72">

        {{-- ── IDLE ── --}}
        <div x-show="gameState === 'idle'" class="flex flex-col items-center justify-center gap-5 py-6">
            <div class="text-center">
                <div class="text-white/8 text-4xl font-black mb-3 select-none">{{ __('app.games.chord_clash.decorative_qualities') }}</div>
                <p class="text-white/50 text-sm text-center max-w-sm">
                    {{ __('app.games.how_chord_idle_desc') }}
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs text-white/30 w-full max-w-xs">
                <div class="bg-white/4 rounded-xl p-3 border border-white/8">
                    <div class="text-white/60 font-semibold mb-1">{{ __('app.games.cc_levels_1_4') }}</div>
                    {{ __('app.games.cc_levels_1_4_desc') }}
                </div>
                <div class="bg-white/4 rounded-xl p-3 border border-white/8">
                    <div class="text-white/60 font-semibold mb-1">{{ __('app.games.cc_level_5') }}</div>
                    {{ __('app.games.cc_level_5_desc') }}
                </div>
            </div>

            @if($personalBest > 0)
            <div class="flex items-center gap-1.5 text-white/30 text-sm">
                <i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i>
                {{ __('app.games.personal_best') }} <span class="text-white font-bold ml-1">{{ number_format($personalBest) }}</span>
            </div>
            @endif

            <button @click="startGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-rose-400 to-pink-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform">
                <span class="flex items-center gap-2">
                    <i data-lucide="play" class="w-4 h-4 fill-current"></i>
                    {{ __('app.games.start_game') }}
                </span>
            </button>
        </div>

        {{-- ── PLAYING ── --}}
        <div x-show="gameState === 'playing'" class="flex flex-col gap-5">

            {{-- Progress bar (levels 1-4) --}}
            <div x-show="currentLevel < 5" class="space-y-1.5">
                <div class="flex justify-between text-xs text-white/40">
                    <span x-text="'{{ __('app.games.status_level') }} ' + currentLevel + ': ' + correctCount + ' / 20'"></span>
                    <span class="text-rose-400/80" x-show="errorCount > 0"
                          x-text="'⚠ ' + errorCount + ' / 3'"></span>
                </div>
                <div class="w-full bg-white/8 rounded-full h-2">
                    <div class="bg-gradient-to-r from-rose-400 to-pink-500 h-2 rounded-full transition-all duration-500"
                         :style="'width:' + (correctCount / 20 * 100) + '%'"></div>
                </div>
            </div>
            <div x-show="currentLevel === 5" class="flex justify-between text-xs text-white/40">
                <span x-text="'{{ __('app.games.status_level') }} 5: ' + correctCount + ' {{ __('app.games.correct_label') }}'"></span>
                <span class="text-rose-400/80" x-show="errorCount > 0"
                      x-text="'⚠ ' + errorCount + ' / 3'"></span>
            </div>

            {{-- Question prompt --}}
            <div class="text-center">
                <p class="text-white/50 text-xs uppercase tracking-wider mb-1">{{ __('app.games.what_chord_quality') }}</p>
            </div>

            {{-- Play button --}}
            <div class="flex justify-center">
                <button @click="playCurrentChord()"
                        :disabled="isPlaying"
                        class="flex items-center gap-2.5 px-7 py-3.5 rounded-2xl border transition-all"
                        :class="isPlaying
                            ? 'bg-rose-500/30 border-rose-400/50 text-rose-300 cursor-wait scale-95'
                            : 'bg-rose-500/15 border-rose-400/30 text-white hover:bg-rose-500/25 hover:border-rose-400/50 active:scale-95'">
                    <i data-lucide="volume-2" class="w-5 h-5" :class="isPlaying ? 'animate-pulse' : ''"></i>
                    <span class="font-semibold text-sm"
                          x-text="isPlaying ? '{{ __('app.games.playing') }}…' : (showFeedback ? '{{ __('app.games.replay_melody') }}' : '{{ __('app.games.listen_carefully') }}')"></span>
                </button>
            </div>

            {{-- Feedback overlay (shown after answering) --}}
            <div x-show="showFeedback" class="rounded-2xl border p-4 text-center"
                 :class="lastAnswerCorrect ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-rose-500/10 border-rose-500/30'">
                <div class="font-bold text-base mb-0.5"
                     :class="lastAnswerCorrect ? 'text-emerald-400' : 'text-rose-400'"
                     x-text="lastAnswerCorrect ? '✓ {{ __('app.games.status_correct') }}' : '✗ {{ __('app.games.incorrect') }}'"></div>
                <div class="text-white font-semibold text-sm mb-1"
                     x-text="correctAnswerLabel"></div>
                <div class="text-white/50 text-xs" x-text="correctAnswerFeedback"></div>
            </div>

            {{-- Answer buttons --}}
            <div class="grid grid-cols-2 gap-3 w-full max-w-sm mx-auto">
                <template x-for="(option, idx) in answerOptions" :key="idx">
                    <button @click="answer(idx)"
                            :disabled="showFeedback"
                            class="py-4 px-3 rounded-2xl font-bold text-sm transition-all border"
                            :class="getAnswerButtonClass(idx)">
                        <span x-text="option.label"></span>
                    </button>
                </template>
            </div>

        </div>

        {{-- ── LEVEL UP ── --}}
        <div x-show="gameState === 'levelup'" class="flex flex-col items-center justify-center gap-5 py-6 text-center">
            <div class="text-5xl">🎯</div>
            <div>
                <div class="text-emerald-400 font-bold text-lg mb-1"
                     x-text="'{{ __('app.games.status_level') }} ' + (currentLevel - 1) + ' {{ __('app.games.cc_complete') }}'"></div>
                <div class="text-white text-2xl font-black">+500!</div>
            </div>
            <div class="bg-white/5 rounded-2xl border border-white/10 px-6 py-4 w-full max-w-xs">
                <div class="text-white/40 text-xs mb-2">{{ __('app.games.cc_next_up') }}</div>
                <div class="text-white font-bold text-base" x-text="'{{ __('app.games.status_level') }} ' + currentLevel + ' · ' + levelName"></div>
                <div class="text-white/40 text-xs mt-1" x-text="nextLevelDesc"></div>
            </div>
            <button @click="continueToNextLevel()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-emerald-400 to-teal-500 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform">
                {{ __('app.games.cc_continue') }}
            </button>
        </div>

        {{-- ── GAME OVER ── --}}
        <div x-show="gameState === 'gameover'" class="flex flex-col items-center justify-center gap-5 py-4">
            <div class="text-5xl">🎹</div>
            <div class="text-center">
                <div class="text-white/40 text-sm mb-1">{{ __('app.games.final_score') }}</div>
                <div class="text-5xl font-black text-white tabular-nums" x-text="score.toLocaleString()"></div>
            </div>
            <div class="grid grid-cols-3 gap-3 w-full max-w-sm text-center text-sm">
                <div class="bg-white/5 rounded-xl border border-white/10 p-3">
                    <div class="text-white font-bold text-lg" x-text="totalCorrect"></div>
                    <div class="text-white/40 text-xs">{{ __('app.games.status_correct') }}</div>
                </div>
                <div class="bg-white/5 rounded-xl border border-white/10 p-3">
                    <div class="text-white font-bold text-lg" x-text="totalWrong"></div>
                    <div class="text-white/40 text-xs">{{ __('app.games.incorrect') }}</div>
                </div>
                <div class="bg-white/5 rounded-xl border border-white/10 p-3">
                    <div class="text-white font-bold text-lg" x-text="maxStreak"></div>
                    <div class="text-white/40 text-xs">{{ __('app.games.best_streak') }}</div>
                </div>
            </div>
            <div class="bg-white/5 rounded-xl border border-white/10 p-4 w-full max-w-sm">
                <div class="text-white/40 text-xs mb-2 uppercase tracking-wider">{{ __('app.games.cc_highest_level') }}</div>
                <div class="text-white font-bold" x-text="'{{ __('app.games.status_level') }} ' + highestLevel + ' · ' + getLevelName(highestLevel)"></div>
            </div>
            <div x-show="weakAreas.length > 0" class="bg-white/5 rounded-xl border border-white/10 p-4 w-full max-w-sm">
                <div class="text-white/40 text-xs mb-2 uppercase tracking-wider">{{ __('app.games.cc_focus_next') }}</div>
                <template x-for="area in weakAreas" :key="area">
                    <div class="text-white/70 text-sm" x-text="'• ' + getChordLabel(area)"></div>
                </template>
            </div>
            <div x-show="isNewBest" class="px-4 py-2 rounded-full bg-yellow-400/20 border border-yellow-400/30 text-yellow-300 text-sm font-bold">
                🏆 {{ __('app.games.new_personal_best') }}
            </div>
            <button @click="limitReached ? window.location.reload() : resetGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-rose-400 to-pink-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform mt-1"
                    x-text="limitReached ? '{{ __('app.games.daily_limit_title') }} →' : '{{ __('app.games.play_again') }}'">
            </button>
        </div>

    </div>

    @if($perTypeLimit !== null && $perTypeLimit !== -1)
    <div class="px-6 pb-4 text-center text-white/20 text-xs">
        {{ __('app.games.plays_used', ['used' => $dailyPlaysUsed, 'limit' => $perTypeLimit]) }}
        @if($totalLimit !== null && $totalLimit !== -1)
         · {{ __('app.games.plays_used_total', ['used' => $totalPlaysUsed, 'total' => $totalLimit]) }}
        @endif
    </div>
    @endif
</div>

<script>
(function() {
    const PERSONAL_BEST = {{ (int)$personalBest }};
    const SCORE_URL = @json($scoreUrl);
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    function saveScore(score, maxStreak, levelReached, metadata) {
        return fetch(SCORE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ score, max_streak: maxStreak, level_reached: levelReached, metadata })
        })
        .then(r => r.json())
        .catch(() => ({ can_play_again: true }));
    }

    // ── Chord definitions ─────────────────────────────────────────────────────
    const CHORD_DEFS = {
        major_triad:             { intervals:[0,4,7],     label:@json(__('app.games.chord_clash.quality_major')),              feedback:@json(__('app.games.chord_clash.feedback_major')) },
        minor_triad:             { intervals:[0,3,7],     label:@json(__('app.games.chord_clash.quality_minor')),              feedback:@json(__('app.games.chord_clash.feedback_minor')) },
        diminished_triad:        { intervals:[0,3,6],     label:@json(__('app.games.chord_clash.quality_diminished')),         feedback:@json(__('app.games.chord_clash.feedback_diminished')) },
        augmented_triad:         { intervals:[0,4,8],     label:@json(__('app.games.chord_clash.quality_augmented')),          feedback:@json(__('app.games.chord_clash.feedback_augmented')) },
        major_seventh:           { intervals:[0,4,7,11],  label:@json(__('app.games.chord_clash.quality_major_seventh')),      feedback:@json(__('app.games.chord_clash.feedback_major_seventh')) },
        minor_seventh:           { intervals:[0,3,7,10],  label:@json(__('app.games.chord_clash.quality_minor_seventh')),      feedback:@json(__('app.games.chord_clash.feedback_minor_seventh')) },
        dominant_seventh:        { intervals:[0,4,7,10],  label:@json(__('app.games.chord_clash.quality_dominant_seventh')),   feedback:@json(__('app.games.chord_clash.feedback_dominant_seventh')) },
        diminished_seventh:      { intervals:[0,3,6,9],   label:@json(__('app.games.chord_clash.quality_diminished_seventh')), feedback:@json(__('app.games.chord_clash.feedback_diminished_seventh')) },
        half_diminished_seventh: { intervals:[0,3,6,10],  label:@json(__('app.games.chord_clash.quality_half_diminished_seventh')), feedback:@json(__('app.games.chord_clash.feedback_half_diminished_seventh')) },
        augmented_major_seventh: { intervals:[0,4,8,11],  label:@json(__('app.games.chord_clash.quality_augmented_major_seventh')), feedback:@json(__('app.games.chord_clash.feedback_augmented_major_seventh')) },
    };

    // ── Confusion pairs (distractor selection) ────────────────────────────────
    const CONFUSION_PAIRS = {
        major_triad:             ['minor_triad', 'augmented_triad'],
        minor_triad:             ['major_triad', 'diminished_triad'],
        diminished_triad:        ['minor_triad', 'augmented_triad'],
        augmented_triad:         ['major_triad', 'diminished_triad'],
        major_seventh:           ['dominant_seventh', 'minor_seventh', 'augmented_major_seventh'],
        minor_seventh:           ['dominant_seventh', 'major_seventh', 'half_diminished_seventh'],
        dominant_seventh:        ['major_seventh', 'minor_seventh', 'half_diminished_seventh'],
        diminished_seventh:      ['half_diminished_seventh', 'diminished_triad'],
        half_diminished_seventh: ['diminished_seventh', 'minor_seventh', 'dominant_seventh'],
        augmented_major_seventh: ['major_seventh', 'augmented_triad'],
    };

    // ── Level definitions ─────────────────────────────────────────────────────
    const LEVELS = [
        {
            number: 1,
            name: @json(__('app.games.chord_clash.level_1_name')),
            desc: @json(__('app.games.chord_clash.level_1_desc')),
            pool: ['major_triad','minor_triad'],
            correctToAdvance: 20,
        },
        {
            number: 2,
            name: @json(__('app.games.chord_clash.level_2_name')),
            desc: @json(__('app.games.chord_clash.level_2_desc')),
            pool: ['major_triad','minor_triad','diminished_triad','augmented_triad'],
            correctToAdvance: 20,
        },
        {
            number: 3,
            name: @json(__('app.games.chord_clash.level_3_name')),
            desc: @json(__('app.games.chord_clash.level_3_desc')),
            pool: ['major_seventh','minor_seventh','dominant_seventh'],
            correctToAdvance: 20,
        },
        {
            number: 4,
            name: @json(__('app.games.chord_clash.level_4_name')),
            desc: @json(__('app.games.chord_clash.level_4_desc')),
            pool: ['major_seventh','minor_seventh','dominant_seventh','diminished_seventh','half_diminished_seventh','augmented_major_seventh'],
            correctToAdvance: 20,
        },
        {
            number: 5,
            name: @json(__('app.games.chord_clash.level_5_name')),
            desc: @json(__('app.games.chord_clash.level_5_desc')),
            pool: ['major_triad','minor_triad','diminished_triad','augmented_triad','major_seventh','minor_seventh','dominant_seventh','diminished_seventh','half_diminished_seventh','augmented_major_seventh'],
            correctToAdvance: null, // endless
        },
    ];

    // ── MIDI / audio helpers ──────────────────────────────────────────────────
    const NOTE_NAMES = {C:0,'C#':1,D:2,'D#':3,E:4,F:5,'F#':6,G:7,'G#':8,A:9,'A#':10,Bb:10,B:11};
    const MIDI_NAMES = ['C','C#','D','D#','E','F','F#','G','G#','A','A#','B'];
    const ROOTS = ['C3','D3','E3','F3','G3','A3','B3','C4','D4','E4','F4','G4','A4'];

    function noteToMidi(note) {
        const m = note.match(/([A-G][b#]?)(\d)/);
        return NOTE_NAMES[m[1]] + (parseInt(m[2]) + 1) * 12;
    }
    function midiToNote(midi) {
        return MIDI_NAMES[midi % 12] + (Math.floor(midi / 12) - 1);
    }
    function buildChord(root, intervals) {
        const base = noteToMidi(root);
        return intervals.map(i => midiToNote(base + i));
    }

    // ── Distractor selection ──────────────────────────────────────────────────
    function pickDistractor(correctQuality, pool, confusionCounts) {
        const preferred = (CONFUSION_PAIRS[correctQuality] || []).filter(q => pool.includes(q));
        if (preferred.length > 0) {
            if (confusionCounts) {
                let best = null, bestCount = -1;
                preferred.forEach(q => {
                    const key = correctQuality + '->' + q;
                    const c = confusionCounts[key] || 0;
                    if (c > bestCount) { bestCount = c; best = q; }
                });
                if (bestCount > 0 && Math.random() < 0.6) return best;
            }
            return preferred[Math.floor(Math.random() * preferred.length)];
        }
        const others = pool.filter(q => q !== correctQuality);
        return others[Math.floor(Math.random() * others.length)];
    }

    // ── Alpine component ──────────────────────────────────────────────────────
    document.addEventListener('alpine:init', function () {
        Alpine.data('chordClashGame', function () {
            return {
                gameState: 'idle',

                currentLevel: 1,
                lives: 3,
                maxLives: 3,
                score: 0,
                streak: 0,
                maxStreak: 0,
                correctCount: 0,
                errorCount: 0,
                totalCorrect: 0,
                totalWrong: 0,
                highestLevel: 1,
                isNewBest: false,
                personalBest: PERSONAL_BEST,
                limitReached: false,

                currentQuestion: null,
                answerOptions: [],
                showFeedback: false,
                selectedIdx: null,
                lastAnswerCorrect: false,
                correctAnswerLabel: '',
                correctAnswerFeedback: '',
                isPlaying: false,

                confusionCounts: {},
                qualityWrongCounts: {},
                nextLevelDesc: '',

                get levelName() {
                    return LEVELS[this.currentLevel - 1]?.name ?? '';
                },

                getLevelName(n) {
                    return LEVELS[n - 1]?.name ?? '';
                },

                getChordLabel(quality) {
                    return CHORD_DEFS[quality]?.label ?? quality;
                },

                get weakAreas() {
                    return Object.entries(this.qualityWrongCounts)
                        .sort((a, b) => b[1] - a[1])
                        .slice(0, 3)
                        .filter(([, v]) => v > 0)
                        .map(([k]) => k);
                },

                init() {
                    lucide.createIcons();
                },

                startGame() {
                    this.currentLevel = 1;
                    this.lives = 3;
                    this.maxLives = 3;
                    this.score = 0;
                    this.streak = 0;
                    this.maxStreak = 0;
                    this.correctCount = 0;
                    this.errorCount = 0;
                    this.totalCorrect = 0;
                    this.totalWrong = 0;
                    this.highestLevel = 1;
                    this.isNewBest = false;
                    this.limitReached = false;
                    this.confusionCounts = {};
                    this.qualityWrongCounts = {};
                    this.gameState = 'playing';
                    this.nextQuestion();
                },

                nextQuestion() {
                    this.showFeedback = false;
                    this.selectedIdx = null;
                    this.currentQuestion = this.generateQuestion();
                    this.answerOptions = this.currentQuestion.answerOptions;
                    this.$nextTick(() => {
                        lucide.createIcons();
                        this.playCurrentChord();
                    });
                },

                generateQuestion() {
                    const levelDef = LEVELS[this.currentLevel - 1];
                    const pool = levelDef.pool;

                    const correctQuality = pool[Math.floor(Math.random() * pool.length)];
                    const distractorQuality = pickDistractor(
                        correctQuality, pool,
                        this.currentLevel === 5 ? this.confusionCounts : null
                    );

                    const root = ROOTS[Math.floor(Math.random() * ROOTS.length)];
                    const notes = buildChord(root, CHORD_DEFS[correctQuality].intervals);

                    const correctIdx = Math.random() < 0.5 ? 0 : 1;
                    const options = correctIdx === 0
                        ? [
                            { quality: correctQuality,    label: CHORD_DEFS[correctQuality].label },
                            { quality: distractorQuality, label: CHORD_DEFS[distractorQuality].label },
                          ]
                        : [
                            { quality: distractorQuality, label: CHORD_DEFS[distractorQuality].label },
                            { quality: correctQuality,    label: CHORD_DEFS[correctQuality].label },
                          ];

                    return {
                        root,
                        quality: correctQuality,
                        notes,
                        correctIdx,
                        answerOptions: options,
                    };
                },

                playCurrentChord() {
                    if (!window.HarmonivaAudio || !this.currentQuestion) return;
                    this.isPlaying = true;
                    HarmonivaAudio.playChord(this.currentQuestion.notes, 2);
                    setTimeout(() => { this.isPlaying = false; }, 1400);
                },

                answer(idx) {
                    if (this.showFeedback || this.gameState !== 'playing') return;

                    this.selectedIdx = idx;
                    const isCorrect = (idx === this.currentQuestion.correctIdx);
                    const quality = this.currentQuestion.quality;

                    this.correctAnswerLabel = CHORD_DEFS[quality].label;
                    this.correctAnswerFeedback = CHORD_DEFS[quality].feedback;
                    this.lastAnswerCorrect = isCorrect;
                    this.showFeedback = true;

                    if (isCorrect) {
                        this.streak++;
                        this.maxStreak = Math.max(this.maxStreak, this.streak);
                        this.score += 100 + (this.streak - 1) * 10 + (this.currentLevel - 1) * 20;
                        this.correctCount++;
                        this.totalCorrect++;
                    } else {
                        const distractor = this.currentQuestion.answerOptions[idx].quality;
                        const key = quality + '->' + distractor;
                        this.confusionCounts[key] = (this.confusionCounts[key] || 0) + 1;
                        this.qualityWrongCounts[quality] = (this.qualityWrongCounts[quality] || 0) + 1;

                        this.streak = 0;
                        this.errorCount++;
                        this.totalWrong++;

                        if (this.errorCount >= 3) {
                            this.lives--;
                            this.errorCount = 0;
                            if (this.lives <= 0) {
                                setTimeout(() => this.endGame(), 1400);
                                return;
                            }
                        }
                    }

                    const levelDef = LEVELS[this.currentLevel - 1];
                    const shouldAdvance = levelDef.correctToAdvance !== null
                        && this.correctCount >= levelDef.correctToAdvance;

                    if (shouldAdvance) {
                        setTimeout(() => this.triggerLevelUp(), 1400);
                    } else {
                        setTimeout(() => {
                            if (this.gameState === 'playing') this.nextQuestion();
                        }, 1400);
                    }
                },

                getAnswerButtonClass(idx) {
                    if (!this.showFeedback) {
                        return 'bg-white/6 border-white/12 text-white hover:bg-white/12 hover:border-white/25 active:scale-95';
                    }
                    if (idx === this.currentQuestion.correctIdx) {
                        return 'bg-emerald-500/20 border-emerald-400/50 text-emerald-300 scale-95';
                    }
                    if (idx === this.selectedIdx) {
                        return 'bg-rose-500/20 border-rose-400/50 text-rose-300 scale-95';
                    }
                    return 'bg-white/3 border-white/6 text-white/30';
                },

                triggerLevelUp() {
                    this.score += 500;
                    this.highestLevel = Math.max(this.highestLevel, this.currentLevel);
                    this.currentLevel++;
                    this.correctCount = 0;
                    this.nextLevelDesc = LEVELS[this.currentLevel - 1]?.desc ?? '';
                    this.gameState = 'levelup';
                    this.$nextTick(() => lucide.createIcons());
                },

                continueToNextLevel() {
                    this.gameState = 'playing';
                    this.nextQuestion();
                },

                endGame() {
                    this.highestLevel = Math.max(this.highestLevel, this.currentLevel);
                    this.gameState = 'gameover';
                    this.isNewBest = this.score > this.personalBest;
                    if (this.isNewBest) this.personalBest = this.score;

                    saveScore(this.score, this.maxStreak, this.highestLevel, {
                        totalCorrect: this.totalCorrect,
                        totalWrong: this.totalWrong,
                        highestLevel: this.highestLevel,
                        confusions: this.confusionCounts,
                    }).then(data => {
                        if (data && data.can_play_again === false) {
                            this.limitReached = true;
                        }
                    });

                    this.$nextTick(() => lucide.createIcons());
                },

                resetGame() {
                    this.gameState = 'idle';
                    this.$nextTick(() => lucide.createIcons());
                },
            };
        });
    });
})();
</script>

@endif
