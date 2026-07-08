{{-- Interval Blitz game partial — 3-level progression system --}}

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

<div x-data="intervalBlitzGame()" x-init="onInit()" class="game-surface rounded-2xl overflow-hidden">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-sky-500/20 to-blue-600/20 border-b border-white/10 py-4 px-5 sm:p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('games.index') }}" class="w-10 h-10 rounded-xl bg-white border border-white/20 flex items-center justify-center text-red-600 font-bold hover:bg-white/90 transition-all flex-shrink-0">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center">
                    <i data-lucide="timer" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <div class="text-white font-bold text-sm">{{ __('app.games.interval_blitz.title') }}</div>
                    <div class="text-white/40 text-xs" x-text="headerSubtitle"></div>
                </div>
            </div>
            <div class="flex items-center gap-3 sm:gap-4">
                {{-- Hearts: desktop only --}}
                <div class="hidden sm:flex items-center gap-0.5">
                    <template x-for="i in maxLives" :key="i">
                        <span class="text-2xl transition-all" :class="i<=lives?'text-red-500':'opacity-40 text-rose-800'">❤</span>
                    </template>
                </div>
                <div class="text-center">
                    <div class="text-white/40 text-xs">{{ __('app.games.interval_blitz.score') }}</div>
                    <div class="text-white font-black text-xl tabular-nums" x-text="score"></div>
                    {{-- Hearts: mobile only --}}
                    <div class="flex items-center justify-center gap-0.5 mt-0.5 sm:hidden">
                        <template x-for="i in maxLives" :key="'m'+i">
                            <span class="text-lg transition-all" :class="i<=lives?'text-red-500':'opacity-40 text-rose-800'">❤</span>
                        </template>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-white/40 text-xs" x-text="str.streak">{{ __('app.games.interval_blitz.streak') }}</div>
                    <div class="font-black text-xl tabular-nums" :class="streak>0?'text-sky-400':'text-white/30'" x-text="streak"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Progress bar: timer during play, level progress when level-complete --}}
    <div class="h-2 bg-white/5">
        <div class="h-full transition-all duration-1000 rounded-full"
             :class="gameState==='playing'
                ? (timeLeft<=3 ? 'bg-gradient-to-r from-red-400 to-rose-500' : 'bg-gradient-to-r from-sky-400 to-blue-500')
                : 'bg-gradient-to-r from-emerald-400 to-teal-500'"
             :style="'width:' + (gameState==='playing' ? (timeLeft/10*100) : (gameState==='level-complete' ? 100 : 0)) + '%'"></div>
    </div>

    <div class="p-6 sm:p-8 min-h-72">

        {{-- ── IDLE ── --}}
        <div x-show="gameState==='idle'" class="flex flex-col items-center gap-5">
            <div class="text-white/10 text-4xl font-black tracking-tight">P4 m3 M6</div>
            <p class="text-white/40 text-sm text-center max-w-xs">
                {{ __('app.games.interval_blitz.idle_description') }}
            </p>

            {{-- Level cards --}}
            <div class="w-full max-w-sm space-y-2">
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-sky-400/10 border border-sky-400/20">
                    <span class="w-7 h-7 rounded-lg bg-sky-400/20 flex items-center justify-center text-sky-300 text-xs font-black">1</span>
                    <div class="flex-1">
                        <div class="text-sky-200 text-sm font-semibold">{{ __('app.games.interval_blitz.melodic_intervals_title') }}</div>
                        <div class="text-white/30 text-xs">{{ __('app.games.interval_blitz.answer_20_to_advance') }}</div>
                    </div>
                    <i data-lucide="music" class="w-4 h-4 text-sky-400/60"></i>
                </div>
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-violet-400/10 border border-violet-400/20">
                    <span class="w-7 h-7 rounded-lg bg-violet-400/20 flex items-center justify-center text-violet-300 text-xs font-black">2</span>
                    <div class="flex-1">
                        <div class="text-violet-200 text-sm font-semibold">{{ __('app.games.interval_blitz.harmonic_intervals_title') }}</div>
                        <div class="text-white/30 text-xs">{{ __('app.games.interval_blitz.answer_20_to_advance') }}</div>
                    </div>
                    <i data-lucide="layers" class="w-4 h-4 text-violet-400/60"></i>
                </div>
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-amber-400/10 border border-amber-400/20">
                    <span class="w-7 h-7 rounded-lg bg-amber-400/20 flex items-center justify-center text-amber-300 text-xs font-black">3</span>
                    <div class="flex-1">
                        <div class="text-amber-200 text-sm font-semibold">{{ __('app.games.interval_blitz.mixed_challenge_title') }}</div>
                        <div class="text-white/30 text-xs">{{ __('app.games.interval_blitz.mixed_challenge_desc') }}</div>
                    </div>
                    <i data-lucide="zap" class="w-4 h-4 text-amber-400/60"></i>
                </div>
            </div>

            <p class="text-white/25 text-xs text-center">{{ __('app.games.interval_blitz.idle_rules') }}</p>

            @if($personalBest > 0)
            <div class="text-white/30 text-sm flex items-center gap-1.5">
                <i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i>
                <span x-text="str.personalBest"></span> <span class="text-white font-bold ml-1">{{ number_format($personalBest) }}</span>
            </div>
            @endif

            <button @click="startGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-sky-400 to-blue-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform">
                <span class="flex items-center gap-2">
                    <i data-lucide="play" class="w-4 h-4 fill-current"></i>
                    <span x-text="str.startGame"></span>
                </span>
            </button>
        </div>

        {{-- ── PLAYING ── --}}
        <div x-show="gameState==='playing'" class="flex flex-col items-center gap-5">

            {{-- Level badge + question progress --}}
            <div class="flex items-center justify-between w-full max-w-xs">
                <span class="px-3 py-1 rounded-full text-xs font-bold"
                      :class="{
                          'bg-sky-400/15 text-sky-300 border border-sky-400/30': currentLevel===1,
                          'bg-violet-400/15 text-violet-300 border border-violet-400/30': currentLevel===2,
                          'bg-amber-400/15 text-amber-300 border border-amber-400/30': currentLevel===3
                      }"
                      x-text="str.level + ' ' + currentLevel"></span>
                <span class="text-white/40 text-xs" x-text="questionProgressLabel"></span>
            </div>

            {{-- Level 1&2: progress dots --}}
            <div x-show="currentLevel < 3" class="flex gap-1 flex-wrap justify-center max-w-xs">
                <template x-for="i in 20" :key="i">
                    <span class="w-2 h-2 rounded-full transition-all"
                          :class="i <= levelQuestions ? 'bg-emerald-400' : 'bg-white/10'"></span>
                </template>
            </div>

            {{-- Timer + play button --}}
            <div class="flex items-center gap-5">
                <div class="text-center w-12">
                    <div class="font-black text-3xl tabular-nums"
                         :class="timeLeft<=3?'text-red-400':'text-white/60'"
                         x-text="timeLeft"></div>
                    <div class="text-white/30 text-xs" x-text="str.sec">{{ __('app.games.interval_blitz.sec') }}</div>
                </div>
                <button @click="playQuestion()"
                        class="w-20 h-20 rounded-2xl shadow-xl flex items-center justify-center hover:scale-105 active:scale-95 transition-transform"
                        :class="{
                            'bg-gradient-to-br from-sky-400 to-blue-600': currentLevel===1,
                            'bg-gradient-to-br from-violet-500 to-purple-700': currentLevel===2,
                            'bg-gradient-to-br from-amber-400 to-orange-600': currentLevel===3
                        }">
                    <i data-lucide="volume-2" class="w-9 h-9 text-white"></i>
                </button>
                <div class="w-12 text-center">
                    <div class="text-white/20 text-xs leading-tight" x-text="currentQuestion && currentQuestion.direction === 'simultaneous' ? '≡' : (currentQuestion && currentQuestion.direction === 'ascending' ? '↑' : (currentQuestion && currentQuestion.direction === 'descending' ? '↓' : ''))"></div>
                </div>
            </div>

            <p class="text-white/40 text-xs" x-text="str.whatInterval">{{ __('app.games.interval_blitz.what_interval') }}</p>

            <div class="grid grid-cols-2 gap-2.5 w-full max-w-xs">
                <template x-for="(opt,i) in options" :key="i">
                    <button @click="answer(opt)" :disabled="answered"
                            :class="{
                                'correct': answered && opt===currentQuestion.correctAnswer,
                                'wrong': answered && selectedAnswer===opt && opt!==currentQuestion.correctAnswer,
                                'disabled-neutral': answered && opt!==currentQuestion.correctAnswer && selectedAnswer!==opt
                            }"
                            class="answer-btn text-white font-semibold text-sm py-3.5 rounded-xl" x-text="opt"></button>
                </template>
            </div>
        </div>

        {{-- ── LEVEL COMPLETE ── --}}
        <div x-show="gameState==='level-complete'" class="flex flex-col items-center justify-center gap-4 py-6">
            <div class="text-5xl animate-bounce">🎉</div>
            <div class="text-center">
                <div class="text-white font-black text-2xl">{{ __('app.games.status_level') }} <span x-text="currentLevel"></span> {{ __('app.games.interval_blitz.complete') }}</div>
                <div class="text-white/40 text-sm mt-1">{{ __('app.games.interval_blitz.advancing_to_level') }} <span x-text="currentLevel + 1"></span>…</div>
            </div>
            <div class="flex items-center gap-4 text-sm text-white/50">
                <span x-text="'{{ __('app.games.interval_blitz.score') }}: ' + score"></span>
                <span x-text="'{{ __('app.games.interval_blitz.streak') }}: ' + streak"></span>
                <span class="flex items-center gap-0.5">
                    <template x-for="i in maxLives" :key="i">
                        <span :class="i<=lives?'text-red-400':'text-white/20'">❤</span>
                    </template>
                </span>
            </div>
            <div class="w-full max-w-xs bg-white/5 rounded-full h-1.5 overflow-hidden mt-2">
                <div class="h-full bg-emerald-400 rounded-full animate-pulse" style="width:100%"></div>
            </div>
        </div>

        {{-- ── GAME OVER ── --}}
        <div x-show="gameState==='gameover'" class="flex flex-col items-center gap-5 py-4">
            <div class="text-5xl">🎵</div>
            <div class="text-center">
                <div class="text-white/40 text-sm mb-1" x-text="str.finalScore">{{ __('app.games.final_score') }}</div>
                <div class="text-5xl font-black text-white tabular-nums" x-text="score"></div>
            </div>
            <div class="grid grid-cols-3 gap-4 text-center text-sm w-full max-w-xs">
                <div>
                    <div class="text-white/30 text-xs mb-0.5">{{ __('app.games.interval_blitz.level_reached') }}</div>
                    <div class="text-white font-bold" x-text="currentLevel"></div>
                </div>
                <div>
                    <div class="text-white/30 text-xs mb-0.5">{{ __('app.games.interval_blitz.correct') }}</div>
                    <div class="text-white font-bold" x-text="totalCorrect + '/' + totalQuestions"></div>
                </div>
                <div>
                    <div class="text-white/30 text-xs mb-0.5">{{ __('app.games.interval_blitz.accuracy') }}</div>
                    <div class="text-white font-bold" x-text="(totalQuestions > 0 ? Math.round(totalCorrect/totalQuestions*100) : 0) + '%'"></div>
                </div>
            </div>
            <div class="flex items-center gap-5 text-sm text-white/50">
                <span>{{ __('app.games.best_streak') }}: <span x-text="maxStreak"></span></span>
            </div>
            <div x-show="isNewBest" class="px-4 py-2 rounded-full bg-yellow-400/20 border border-yellow-400/30 text-yellow-300 text-sm font-bold" x-text="str.newBest+' 🏆'"></div>
            <button @click="resetGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-sky-400 to-blue-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform"
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
function intervalBlitzGame() {
    const PERSONAL_BEST = {{ (int)$personalBest }};
    const SCORE_URL = @json($scoreUrl);
    const STR = window.GAME_STRINGS || {};
    const QUESTION_TIME = 10;

    // All simple intervals P1 through P8
    const ALL_INTERVALS = [
        {name:'Perfect Unison', semitones:0},
        {name:'Minor 2nd',      semitones:1},
        {name:'Major 2nd',      semitones:2},
        {name:'Minor 3rd',      semitones:3},
        {name:'Major 3rd',      semitones:4},
        {name:'Perfect 4th',    semitones:5},
        {name:'Tritone',        semitones:6},
        {name:'Perfect 5th',    semitones:7},
        {name:'Minor 6th',      semitones:8},
        {name:'Major 6th',      semitones:9},
        {name:'Minor 7th',      semitones:10},
        {name:'Major 7th',      semitones:11},
        {name:'Perfect Octave', semitones:12},
    ];

    // G3 = MIDI 55, G5 = MIDI 79
    const MIDI_MIN = 55;
    const MIDI_MAX = 79;
    const MIDI_NAMES = ['C','C#','D','D#','E','F','F#','G','G#','A','A#','B'];

    function midiToName(m) {
        return MIDI_NAMES[m % 12] + (Math.floor(m / 12) - 1);
    }

    function generateQuestion(lvl) {
        const interval = ALL_INTERVALS[Math.floor(Math.random() * ALL_INTERVALS.length)];

        // Determine playback type per level
        let playbackType;
        if (lvl === 1) playbackType = 'melodic';
        else if (lvl === 2) playbackType = 'harmonic';
        else playbackType = Math.random() < 0.5 ? 'melodic' : 'harmonic';

        let direction, firstMidi, secondMidi;

        if (interval.semitones === 0) {
            // Perfect Unison — same note for both
            direction = playbackType === 'harmonic' ? 'simultaneous' : 'unison';
            firstMidi = MIDI_MIN + Math.floor(Math.random() * (MIDI_MAX - MIDI_MIN + 1));
            secondMidi = firstMidi;
        } else if (playbackType === 'harmonic') {
            direction = 'simultaneous';
            const maxLower = MIDI_MAX - interval.semitones;
            if (maxLower < MIDI_MIN) return generateQuestion(lvl);
            firstMidi = MIDI_MIN + Math.floor(Math.random() * (maxLower - MIDI_MIN + 1));
            secondMidi = firstMidi + interval.semitones;
        } else {
            // Melodic: randomly ascending or descending
            direction = Math.random() < 0.5 ? 'ascending' : 'descending';
            const maxFirst = MIDI_MAX - interval.semitones;
            const minFirst = MIDI_MIN + interval.semitones;
            // Fallback if chosen direction has no valid notes in range
            if (direction === 'ascending' && maxFirst < MIDI_MIN) direction = 'descending';
            if (direction === 'descending' && minFirst > MIDI_MAX) direction = 'ascending';
            if (direction === 'ascending') {
                firstMidi = MIDI_MIN + Math.floor(Math.random() * (maxFirst - MIDI_MIN + 1));
                secondMidi = firstMidi + interval.semitones;
            } else {
                firstMidi = minFirst + Math.floor(Math.random() * (MIDI_MAX - minFirst + 1));
                secondMidi = firstMidi - interval.semitones;
            }
        }

        return {
            level: lvl,
            playbackType,
            firstNote: midiToName(firstMidi),
            secondNote: midiToName(secondMidi),
            firstMidi,
            secondMidi,
            interval,
            direction,
            correctAnswer: interval.name,
        };
    }

    function buildOptions(question) {
        const correct = question.correctAnswer;
        const distractors = ALL_INTERVALS
            .filter(i => i.name !== correct)
            .sort(() => Math.random() - 0.5)
            .slice(0, 3)
            .map(i => i.name);
        return [...distractors, correct].sort(() => Math.random() - 0.5);
    }

    return {
        str: Object.assign({
            ready: @json(__('app.games.status_ready')), playing: @json(__('app.games.status_playing')), gameOver: @json(__('app.games.status_game_over')),
            level: @json(__('app.games.status_level')), streak: @json(__('app.games.interval_blitz.streak')), startGame: @json(__('app.games.start_game')),
            playAgain: @json(__('app.games.play_again')), finalScore: @json(__('app.games.final_score')),
            personalBest: @json(__('app.games.personal_best')), correctLabel: @json(__('app.games.correct_label')),
            newBest: @json(__('app.games.new_personal_best')), whatInterval: @json(__('app.games.what_interval')),
            sec: @json(__('app.games.sec')), questionAbbr: @json(__('app.games.interval_blitz.question_abbr')),
        }, STR),

        limitReached: false,
        gameState: 'idle',   // 'idle' | 'playing' | 'level-complete' | 'gameover'
        currentLevel: 1,
        levelQuestions: 0,   // correct answers in current level (levels 1&2: target 20)
        totalQuestions: 0,   // total questions attempted this session
        totalCorrect: 0,     // total correct answers this session
        maxLives: 3,
        lives: 3,
        score: 0,
        streak: 0,
        maxStreak: 0,
        currentQuestion: null,
        options: [],
        answered: false,
        selectedAnswer: null,
        timeLeft: QUESTION_TIME,
        timer: null,
        isNewBest: false,
        personalBest: PERSONAL_BEST,

        get headerSubtitle() {
            if (this.gameState === 'idle') return this.str.ready;
            if (this.gameState === 'gameover') return this.str.gameOver;
            return this.str.level + ' ' + this.currentLevel;
        },

        get questionProgressLabel() {
            if (this.currentLevel < 3) {
                return this.str.questionAbbr + ' ' + (this.levelQuestions + 1) + ' / 20';
            }
            return this.str.questionAbbr + ' ' + (this.totalQuestions + 1);
        },

        onInit() { lucide.createIcons(); },

        startGame() {
            this.lives = 3;
            this.score = 0;
            this.streak = 0;
            this.maxStreak = 0;
            this.totalCorrect = 0;
            this.totalQuestions = 0;
            this.currentLevel = 1;
            this.levelQuestions = 0;
            this.isNewBest = false;
            this.gameState = 'playing';
            this.nextQuestion();
        },

        nextQuestion() {
            this.answered = false;
            this.selectedAnswer = null;
            this.currentQuestion = generateQuestion(this.currentLevel);
            this.options = buildOptions(this.currentQuestion);
            this.timeLeft = QUESTION_TIME;
            this.$nextTick(() => { lucide.createIcons(); this.playQuestion(); });
            clearInterval(this.timer);
            this.timer = setInterval(() => {
                this.timeLeft--;
                if (this.timeLeft <= 0) this.timeout();
            }, 1000);
        },

        playQuestion() {
            if (!this.currentQuestion || !window.HarmonivaAudio) return;
            const q = this.currentQuestion;
            if (q.playbackType === 'harmonic') {
                HarmonivaAudio.playChord([q.firstNote, q.secondNote], 2);
            } else {
                HarmonivaAudio.playSequence([q.firstNote, q.secondNote], 700, 1);
            }
        },

        answer(chosen) {
            if (this.answered || this.gameState !== 'playing') return;
            clearInterval(this.timer);
            this.answered = true;
            this.selectedAnswer = chosen;
            this.totalQuestions++;

            const correct = chosen === this.currentQuestion.correctAnswer;
            if (correct) {
                this.streak++;
                this.maxStreak = Math.max(this.maxStreak, this.streak);
                this.totalCorrect++;
                this.levelQuestions++;
                this.score += 100 + (this.streak > 1 ? (this.streak - 1) * 20 : 0) + (this.timeLeft * 10);
                if (this.streak % 5 === 0) this.lives = Math.min(this.lives + 1, this.maxLives + 2);

                // Level completion check (levels 1 & 2 only)
                if (this.currentLevel < 3 && this.levelQuestions >= 20) {
                    setTimeout(() => this.advanceLevel(), 900);
                    return;
                }
            } else {
                this.streak = 0;
                this.lives--;
                if (this.lives <= 0) {
                    setTimeout(() => this.endGame(), 800);
                    return;
                }
            }
            setTimeout(() => { if (this.gameState === 'playing') this.nextQuestion(); }, 900);
        },

        timeout() {
            clearInterval(this.timer);
            this.answered = true;
            this.selectedAnswer = '__timeout__';
            this.totalQuestions++;
            this.streak = 0;
            this.lives--;
            if (this.lives <= 0) {
                setTimeout(() => this.endGame(), 600);
                return;
            }
            setTimeout(() => { if (this.gameState === 'playing') this.nextQuestion(); }, 900);
        },

        advanceLevel() {
            clearInterval(this.timer);
            this.gameState = 'level-complete';
            this.$nextTick(() => lucide.createIcons());
            // Auto-advance to next level after 2.5 seconds
            setTimeout(() => {
                this.currentLevel++;
                this.levelQuestions = 0;
                this.gameState = 'playing';
                this.nextQuestion();
            }, 2500);
        },

        endGame() {
            clearInterval(this.timer);
            this.gameState = 'gameover';
            this.isNewBest = this.score > this.personalBest;
            if (this.isNewBest) this.personalBest = this.score;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            fetch(SCORE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({
                    score: this.score,
                    max_streak: this.maxStreak,
                    level_reached: this.currentLevel,
                    metadata: {
                        correct: this.totalCorrect,
                        total: this.totalQuestions,
                        accuracy: this.totalQuestions > 0 ? Math.round(this.totalCorrect / this.totalQuestions * 100) : 0,
                    }
                })
            })
            .then(r => r.json())
            .then(data => { if (data.can_play_again === false) this.limitReached = true; })
            .catch(() => {});
            this.$nextTick(() => lucide.createIcons());
        },

        resetGame() {
            clearInterval(this.timer);
            if (this.limitReached) { window.location.reload(); return; }
            this.gameState = 'idle';
        },
    };
}
</script>
@endif
