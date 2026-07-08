{{-- Note Rush game partial --}}
{{-- Variables: $personalBest, $canPlay, $perTypeLimit, $totalLimit, $dailyPlaysUsed, $totalPlaysUsed, $scoreUrl, $slug --}}

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

<div x-data="noteRushGame()" x-init="onInit()" class="game-surface rounded-2xl overflow-hidden">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-yellow-500/20 to-orange-500/20 border-b border-white/10 py-4 px-5 sm:p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('games.index') }}" class="w-10 h-10 rounded-xl bg-white border border-white/20 flex items-center justify-center text-red-600 font-bold hover:bg-white/90 transition-all flex-shrink-0">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center">
                    <i data-lucide="zap" class="w-5 h-5 text-white fill-current"></i>
                </div>
                <div>
                    <div class="text-white font-bold text-sm">{{ __('app.games.note_rush.title') }}</div>
                    <div class="text-white/40 text-xs" x-text="gameState==='idle' ? str.ready : (gameState==='playing' || gameState==='reference' ? str.playing : str.gameOver)"></div>
                </div>
            </div>
            <div class="flex items-center gap-4">
                {{-- Level indicator --}}
                <div class="text-center" x-show="gameState==='playing' || gameState==='reference'">
                    <div class="text-white/40 text-xs">{{ __('app.games.status_level') }}</div>
                    <div class="text-white font-black text-xl tabular-nums" x-text="currentLevel + '/3'"></div>
                </div>
                {{-- Score (shown in gameover / idle) --}}
                <div class="text-center" x-show="gameState==='idle' || gameState==='gameover' || gameState==='levelcomplete' || gameState==='allcomplete'">
                    <div class="text-white/40 text-xs" x-text="str.scoreLabel">{{ __('app.games.note_rush.score') }}</div>
                    <div class="text-white font-black text-xl tabular-nums" x-text="score"></div>
                </div>
                {{-- Lives (hearts) --}}
                <div class="text-center" x-show="gameState==='playing' || gameState==='reference'">
                    <div class="text-white/40 text-xs">{{ __('app.games.note_rush.lives') }}</div>
                    <div class="flex items-center gap-0.5 justify-center">
                        <template x-for="i in [1,2,3]" :key="i">
                            <span :class="i <= lives ? 'text-red-400' : 'text-white/20'"
                                  class="text-xl leading-none transition-colors duration-300">♥</span>
                        </template>
                    </div>
                </div>
                {{-- Streak counter --}}
                <div class="text-center" x-show="gameState==='playing'">
                    <div class="text-white/40 text-xs">{{ __('app.games.note_rush.streak') }}</div>
                    <div class="font-black text-xl tabular-nums" :class="consecutiveCorrect>0?'text-orange-400':'text-white/30'" x-text="consecutiveCorrect+'/10'"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-6 sm:p-8 min-h-72">

        {{-- Idle --}}
        <div x-show="gameState==='idle'" class="flex flex-col items-center justify-center h-96 gap-5">
            <div class="text-5xl font-black text-white/10">♩</div>
            <p class="text-white/40 text-sm text-center max-w-xs" x-text="str.noteRushDesc"></p>
            @if($personalBest > 0)
            <div class="flex items-center gap-1.5 text-white/30 text-sm">
                <i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i>
                <span x-text="str.personalBest"></span> <span class="text-white font-bold ml-1">{{ number_format($personalBest) }}</span>
            </div>
            @endif
            {{-- Level badges --}}
            <div class="flex gap-2 mt-1">
                <template x-for="lvl in [1,2,3]" :key="lvl">
                    <div class="flex flex-col items-center gap-1">
                        <div :class="highestUnlockedLevel >= lvl
                                ? 'bg-gradient-to-br from-yellow-400 to-orange-500 text-white'
                                : 'bg-white/5 text-white/20 border border-white/10'"
                             class="w-12 h-12 rounded-xl flex items-center justify-center font-black text-lg transition-all">
                            <span x-text="lvl"></span>
                        </div>
                        <span class="text-xs" :class="highestUnlockedLevel >= lvl ? 'text-yellow-400' : 'text-white/20'" x-text="highestUnlockedLevel >= lvl ? '✓' : '🔒'"></span>
                    </div>
                </template>
            </div>
            <button @click="startGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white font-bold text-sm shadow-lg hover:scale-105 active:scale-95 transition-transform">
                <span class="flex items-center gap-2">
                    <i data-lucide="play" class="w-4 h-4 fill-current"></i>
                    <span x-text="str.startGame"></span>
                </span>
            </button>
        </div>

        {{-- Reference Note Intro --}}
        <div x-show="gameState==='reference'" class="flex flex-col items-center justify-center gap-5 py-6">
            <div class="text-white/40 text-xs uppercase tracking-wider">{{ __('app.games.note_rush.reference_note') }}</div>
            <div class="w-28 h-28 rounded-2xl bg-gradient-to-br from-yellow-400 to-orange-500 shadow-xl flex flex-col items-center justify-center gap-1">
                <span class="text-white font-black text-4xl">C</span>
                <span class="text-white/70 text-xs">C4</span>
            </div>
            <p class="text-white/50 text-sm text-center max-w-xs">{{ __('app.games.note_rush.reference_note_desc') }}</p>
            <div class="flex items-center gap-2 text-yellow-400 text-sm font-semibold">
                <i data-lucide="music" class="w-4 h-4"></i>
                <span x-text="referenceCountdown > 0 ? (str.startingIn + ' ' + referenceCountdown + '...') : str.starting"></span>
            </div>
        </div>

        {{-- Playing --}}
        <div x-show="gameState==='playing'" class="flex flex-col items-center gap-6">

            {{-- Level badge --}}
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full bg-yellow-400/20 border border-yellow-400/30 text-yellow-300 text-xs font-bold" x-text="str.level + ' ' + currentLevel + ' ' + str.of3"></span>
                <span class="text-white/40 text-xs" x-text="str.streakLabel + ': ' + consecutiveCorrect + ' / 10'"></span>
            </div>

            <div class="text-center">
                <p class="text-white/40 text-xs uppercase tracking-wider mb-2" x-text="str.whatNote"></p>
                <button @click="playCurrentNote()"
                        class="w-24 h-24 rounded-2xl bg-gradient-to-br from-yellow-400 to-orange-500 shadow-xl flex items-center justify-center hover:scale-105 active:scale-95 transition-transform mx-auto">
                    <i data-lucide="volume-2" class="w-10 h-10 text-white"></i>
                </button>
                <p class="text-white/30 text-xs mt-2" x-text="str.tapReplay"></p>
            </div>

            {{-- Answer buttons --}}
            <div class="grid grid-cols-2 gap-3 w-full max-w-sm">
                <template x-for="(opt,idx) in options" :key="idx">
                    <button @click="answer(opt)" :disabled="answered"
                            :class="{
                                'correct': answered && opt===currentQuestion.expectedAnswer,
                                'wrong': answered && selectedAnswer===opt && opt!==currentQuestion.expectedAnswer,
                                'disabled-neutral': answered && opt!==currentQuestion.expectedAnswer && selectedAnswer!==opt
                            }"
                            class="answer-btn text-white font-bold text-lg py-4 rounded-xl">
                        <span x-text="window.HarmonivaNotation.toDisplaySymbol(opt)"></span>
                    </button>
                </template>
            </div>

            {{-- Streak flame --}}
            <div x-show="consecutiveCorrect>=3" class="flex items-center gap-1.5 text-orange-400 text-sm font-semibold">
                <i data-lucide="flame" class="w-4 h-4"></i>
                <span x-text="consecutiveCorrect + ' ' + str.inARow"></span>
            </div>
        </div>

        {{-- Level Complete --}}
        <div x-show="gameState==='levelcomplete'" class="flex flex-col items-center justify-center gap-5 py-6">
            <div class="text-5xl" x-text="currentLevel < 3 ? '🎉' : ''"></div>
            <div class="text-center">
                <div class="text-white font-black text-2xl mb-1"
                     x-text="currentLevel === 1 ? str.level1Complete : str.level2Complete"></div>
                <div class="text-white/40 text-sm" x-text="str.scoreLabel + ': ' + score"></div>
            </div>
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center shadow-xl">
                <i data-lucide="unlock" class="w-8 h-8 text-white"></i>
            </div>
            <p class="text-white/50 text-sm text-center">{{ __('app.games.note_rush.get_ready_for_level') }} <span x-text="currentLevel + 1"></span>...</p>
        </div>

        {{-- All Complete --}}
        <div x-show="gameState==='allcomplete'" class="flex flex-col items-center justify-center gap-5 py-4">
            <div class="text-5xl">🏆</div>
            <div class="text-center">
                <div class="text-white font-black text-2xl mb-1">{{ __('app.games.note_rush.congratulations') }}</div>
                <div class="text-white/60 text-sm mb-1">{{ __('app.games.note_rush.all_levels_complete_desc') }}</div>
                <div class="text-white/40 text-xs" x-text="str.finalScore + ': ' + score"></div>
            </div>
            <div class="flex items-center gap-5 text-sm text-white/50">
                <span class="flex items-center gap-1.5">
                    <i data-lucide="target" class="w-4 h-4 text-green-400"></i>
                    <span x-text="correctCount + ' ' + str.correctLabel"></span>
                </span>
            </div>
            <div x-show="isNewBest" class="px-4 py-2 rounded-full bg-yellow-400/20 border border-yellow-400/30 text-yellow-300 text-sm font-bold" x-text="str.newBest+' 🏆'">
            </div>
            <button @click="resetGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform"
                    x-text="str.playAgain">
            </button>
        </div>

        {{-- Game Over (lost all lives) --}}
        <div x-show="gameState==='gameover'" class="flex flex-col items-center gap-5 py-4">
            <div class="text-5xl">💔</div>
            <div class="text-center">
                <div class="text-white/40 text-sm mb-1" x-text="str.finalScore"></div>
                <div class="text-5xl font-black text-white tabular-nums" x-text="score"></div>
            </div>
            <div class="text-white/40 text-sm" x-text="str.reachedLevel + ' ' + currentLevel + ' ' + str.of3"></div>
            <div class="flex items-center gap-5 text-sm text-white/50">
                <span class="flex items-center gap-1.5">
                    <i data-lucide="target" class="w-4 h-4 text-green-400"></i>
                    <span x-text="correctCount+' '+str.correctLabel"></span>
                </span>
                <span class="flex items-center gap-1.5">
                    <i data-lucide="flame" class="w-4 h-4 text-orange-400"></i>
                    <span x-text="maxStreak+' '+str.bestStreak"></span>
                </span>
            </div>
            <div x-show="isNewBest" class="px-4 py-2 rounded-full bg-yellow-400/20 border border-yellow-400/30 text-yellow-300 text-sm font-bold" x-text="str.newBest+' 🏆'">
            </div>
            <button @click="resetGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform"
                    x-text="limitReached ? '{{ __('app.games.daily_limit_title') }} →' : str.playAgain">
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
function noteRushGame() {
    const PERSONAL_BEST = {{ (int)$personalBest }};
    const SCORE_URL = @json($scoreUrl);
    const STR = window.GAME_STRINGS || {};

    // ── Canonical level configuration ──────────────────────────────────────
    const LEVELS = {
        1: {
            requiredConsecutive: 10,
            allowedNotes: [
                { noteName: 'C', octave: 4 },
                { noteName: 'D', octave: 4 },
                { noteName: 'E', octave: 4 },
                { noteName: 'F', octave: 4 },
                { noteName: 'G', octave: 4 },
                { noteName: 'A', octave: 4 },
                { noteName: 'B', octave: 4 },
            ],
        },
        2: {
            requiredConsecutive: 10,
            allowedNotes: [
                { noteName: 'G', octave: 3 },
                { noteName: 'A', octave: 3 },
                { noteName: 'B', octave: 3 },
                { noteName: 'C', octave: 4 },
                { noteName: 'D', octave: 4 },
                { noteName: 'E', octave: 4 },
                { noteName: 'F', octave: 4 },
                { noteName: 'G', octave: 4 },
                { noteName: 'A', octave: 4 },
                { noteName: 'B', octave: 4 },
                { noteName: 'C', octave: 5 },
                { noteName: 'D', octave: 5 },
                { noteName: 'E', octave: 5 },
                { noteName: 'F', octave: 5 },
                { noteName: 'G', octave: 5 },
            ],
        },
        3: {
            requiredConsecutive: 10,
            allowedNotes: [
                { noteName: 'C',  octave: 4, displayLabel: 'C'  },
                { noteName: 'C#', octave: 4, displayLabel: 'C#' },
                { noteName: 'D',  octave: 4, displayLabel: 'D'  },
                { noteName: 'D#', octave: 4, displayLabel: 'D#' },
                { noteName: 'E',  octave: 4, displayLabel: 'E'  },
                { noteName: 'F',  octave: 4, displayLabel: 'F'  },
                { noteName: 'F#', octave: 4, displayLabel: 'F#' },
                { noteName: 'G',  octave: 4, displayLabel: 'G'  },
                { noteName: 'G#', octave: 4, displayLabel: 'G#' },
                { noteName: 'A',  octave: 4, displayLabel: 'A'  },
                { noteName: 'A#', octave: 4, displayLabel: 'A#' },
                { noteName: 'B',  octave: 4, displayLabel: 'B'  },
            ],
        },
    };

    // Build a canonical question object from a note entry
    function buildQuestion(levelNum, noteEntry) {
        const label = noteEntry.displayLabel || noteEntry.noteName;
        const pitchId = noteEntry.noteName + noteEntry.octave;
        return {
            level: levelNum,
            noteName: noteEntry.noteName,
            octave: noteEntry.octave,
            pitchId: pitchId,
            displayLabel: label,
            audioPitch: pitchId,
            expectedAnswer: label,
        };
    }

    // Pick unique distractor options from the level pool
    function buildOptions(q, pool, levelNum) {
        const opts = new Set([q.expectedAnswer]);
        const labels = pool.map(n => n.displayLabel || n.noteName);
        // Avoid infinite loop if pool is tiny
        let attempts = 0;
        while (opts.size < Math.min(4, labels.length) && attempts < 200) {
            opts.add(labels[Math.floor(Math.random() * labels.length)]);
            attempts++;
        }
        return [...opts].sort(() => Math.random() - 0.5);
    }

    return {
        str: Object.assign({
            scoreLabel: @json(__('app.games.note_rush.score')),
            streakLabel: @json(__('app.games.note_rush.streak')),
            noteRushDesc: @json(__('app.games.note_rush_desc')),
            personalBest: @json(__('app.games.personal_best')),
            startGame: @json(__('app.games.start_game')),
            whatNote: @json(__('app.games.what_note')),
            tapReplay: @json(__('app.games.tap_replay')),
            finalScore: @json(__('app.games.final_score')),
            correctLabel: @json(__('app.games.correct_label')),
            bestStreak: @json(__('app.games.best_streak')),
            newBest: @json(__('app.games.new_personal_best')),
            playAgain: @json(__('app.games.play_again')),
            playing: @json(__('app.games.status_playing')),
            ready: @json(__('app.games.status_ready')),
            gameOver: @json(__('app.games.status_game_over')),
            points: @json(__('app.games.points')),
            level: @json(__('app.games.status_level')),
            of3: @json(__('app.games.note_rush.of_3')),
            reachedLevel: @json(__('app.games.note_rush.reached_level')),
            inARow: @json(__('app.games.note_rush.in_a_row')),
            startingIn: @json(__('app.games.note_rush.starting_in')),
            starting: @json(__('app.games.note_rush.starting')),
            level1Complete: @json(__('app.games.note_rush.level_1_complete')),
            level2Complete: @json(__('app.games.note_rush.level_2_complete'))
        }, STR),

        limitReached: false,
        gameState: 'idle',
        score: 0,
        maxStreak: 0,
        correctCount: 0,
        isNewBest: false,
        personalBest: PERSONAL_BEST,

        // Level state
        currentLevel: 1,
        highestUnlockedLevel: 1,
        consecutiveCorrect: 0,
        lives: 3,

        // Current question (canonical)
        currentQuestion: null,
        options: [],
        answered: false,
        selectedAnswer: null,

        // Reference note
        referenceCountdown: 3,
        _referenceTimer: null,
        _transitionTimer: null,

        onInit() { lucide.createIcons(); },

        startGame() {
            this.score = 0;
            this.maxStreak = 0;
            this.correctCount = 0;
            this.isNewBest = false;
            this.currentLevel = 1;
            this.consecutiveCorrect = 0;
            this.lives = 3;
            this.answered = false;
            this.selectedAnswer = null;
            this.currentQuestion = null;
            this._clearTimers();
            this._playReferenceNote();
        },

        _playReferenceNote() {
            this.gameState = 'reference';
            this.referenceCountdown = 3;
            this.$nextTick(() => { lucide.createIcons(); });
            if (window.HarmonivaAudio) {
                HarmonivaAudio.playNote('C4', 1.5);
            }
            this._referenceTimer = setInterval(() => {
                this.referenceCountdown--;
                if (this.referenceCountdown <= 0) {
                    clearInterval(this._referenceTimer);
                    this._referenceTimer = null;
                    this._startLevel(this.currentLevel);
                }
            }, 1000);
        },

        _startLevel(levelNum) {
            this.currentLevel = levelNum;
            this.consecutiveCorrect = 0;
            this.answered = false;
            this.selectedAnswer = null;
            this.currentQuestion = null;
            this.gameState = 'playing';
            this.$nextTick(() => { lucide.createIcons(); this.nextQuestion(); });
        },

        nextQuestion() {
            if (this.gameState !== 'playing') return;
            this.answered = false;
            this.selectedAnswer = null;
            const levelConfig = LEVELS[this.currentLevel];
            const pool = levelConfig.allowedNotes;
            const noteEntry = pool[Math.floor(Math.random() * pool.length)];
            this.currentQuestion = buildQuestion(this.currentLevel, noteEntry);
            this.options = buildOptions(this.currentQuestion, pool, this.currentLevel);
            this.$nextTick(() => { lucide.createIcons(); this.playCurrentNote(); });
        },

        playCurrentNote() {
            if (!this.currentQuestion || !window.HarmonivaAudio) return;
            HarmonivaAudio.playNote(this.currentQuestion.audioPitch, 1.2);
        },

        answer(chosen) {
            if (this.answered || this.gameState !== 'playing') return;
            this.answered = true;
            this.selectedAnswer = chosen;

            if (chosen === this.currentQuestion.expectedAnswer) {
                this.consecutiveCorrect++;
                this.maxStreak = Math.max(this.maxStreak, this.consecutiveCorrect);
                this.score += 100;
                this.correctCount++;

                if (this.consecutiveCorrect >= LEVELS[this.currentLevel].requiredConsecutive) {
                    // Level complete
                    this._transitionTimer = setTimeout(() => this._completeLevel(), 900);
                    return;
                }
            } else {
                this.consecutiveCorrect = 0;
                this.lives--;
                if (this.lives <= 0) {
                    this._transitionTimer = setTimeout(() => this._endGame(), 900);
                    return;
                }
            }

            this._transitionTimer = setTimeout(() => {
                if (this.gameState === 'playing') this.nextQuestion();
            }, 700);
        },

        _completeLevel() {
            this._clearTimers();
            if (this.currentLevel >= 3) {
                this._allComplete();
                return;
            }
            this.gameState = 'levelcomplete';
            if (this.currentLevel + 1 > this.highestUnlockedLevel) {
                this.highestUnlockedLevel = this.currentLevel + 1;
            }
            this.$nextTick(() => { lucide.createIcons(); });
            this._transitionTimer = setTimeout(() => {
                const nextLevel = this.currentLevel + 1;
                this.currentLevel = nextLevel;
                this.lives = 3;
                this._startLevel(nextLevel);
            }, 2500);
        },

        _allComplete() {
            this.gameState = 'allcomplete';
            this.isNewBest = this.score > this.personalBest;
            if (this.isNewBest) this.personalBest = this.score;
            this._saveScore(3);
            this.$nextTick(() => { lucide.createIcons(); });
        },

        _endGame() {
            this._clearTimers();
            this.gameState = 'gameover';
            this.isNewBest = this.score > this.personalBest;
            if (this.isNewBest) this.personalBest = this.score;
            this._saveScore(this.currentLevel);
            this.$nextTick(() => { lucide.createIcons(); });
        },

        _saveScore(levelReached) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            fetch(SCORE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({
                    score: this.score,
                    max_streak: this.maxStreak,
                    level_reached: levelReached,
                    metadata: { correct: this.correctCount }
                })
            })
            .then(r => r.json())
            .then(data => { if (data.can_play_again === false) this.limitReached = true; })
            .catch(() => {});
        },

        resetGame() {
            this._clearTimers();
            if (this.limitReached) { window.location.reload(); return; }
            this.gameState = 'idle';
            this.$nextTick(() => { lucide.createIcons(); });
        },

        _clearTimers() {
            if (this._referenceTimer) { clearInterval(this._referenceTimer); this._referenceTimer = null; }
            if (this._transitionTimer) { clearTimeout(this._transitionTimer); this._transitionTimer = null; }
        },
    };
}
</script>
@endif
