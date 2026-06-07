<div>
@if(!$canPlay && $dailyLimit !== -1)
    <div class="game-surface rounded-2xl p-10 text-center">
        <div class="w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center mx-auto mb-5">
            <i data-lucide="lock" class="w-8 h-8 text-amber-400"></i>
        </div>
        <h2 class="text-white text-xl font-bold mb-2">Daily limit reached</h2>
        <p class="text-white/40 text-sm max-w-xs mx-auto mb-6">
            You've used all {{ $dailyLimit }} free plays today. Upgrade to Premium for unlimited games.
        </p>
        <a href="{{ route('profile.edit') }}"
           class="inline-block px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white font-semibold text-sm">
            Upgrade to Premium
        </a>
    </div>
@else

<div
    id="interval-blitz-root"
    x-data="intervalBlitzGame"
    x-init="init()"
    class="game-surface rounded-2xl overflow-hidden"
>
    {{-- Header --}}
    <div class="bg-gradient-to-r from-sky-500/20 to-blue-600/20 border-b border-white/10 p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center">
                    <i data-lucide="timer" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <div class="text-white font-bold text-sm">Interval Blitz</div>
                    <div class="text-white/40 text-xs"
                         x-text="gameState === 'idle' ? 'Ready' : (gameState === 'playing' ? 'Level ' + level : 'Game Over')"></div>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1">
                    <template x-for="i in 3" :key="i">
                        <span class="text-lg transition-all"
                              :class="i <= lives ? 'text-rose-400' : 'opacity-25 text-rose-900'">❤</span>
                    </template>
                </div>
                <div class="text-center">
                    <div class="text-white/40 text-xs">Score</div>
                    <div class="text-white font-black text-xl tabular-nums" x-text="score"></div>
                </div>
                <div class="text-center">
                    <div class="text-white/40 text-xs">Streak</div>
                    <div class="font-black text-xl tabular-nums"
                         :class="streak > 0 ? 'text-sky-400' : 'text-white/30'"
                         x-text="streak"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Timer bar --}}
    <div class="h-2 bg-white/5">
        <div class="h-full transition-all rounded-full"
             :class="timeLeft <= 3 ? 'bg-gradient-to-r from-red-400 to-rose-500' : 'bg-gradient-to-r from-sky-400 to-blue-500'"
             :style="'width:' + (timeLeft / 10 * 100) + '%'"></div>
    </div>

    <div class="p-6 sm:p-8 min-h-64">

        {{-- Idle --}}
        <div x-show="gameState === 'idle'" class="flex flex-col items-center justify-center h-48 gap-5">
            <div class="text-white/10 text-5xl font-black">P4 m3</div>
            <p class="text-white/40 text-sm text-center">Identify intervals before the timer runs out.<br>3 lives. 5-streak earns a bonus life!</p>
            <div class="flex flex-wrap gap-2 justify-center">
                <button @click="difficulty='easy'"
                        :class="difficulty==='easy' ? 'border-sky-400 text-sky-300 bg-sky-400/10' : 'border-white/10 text-white/40'"
                        class="px-4 py-2 rounded-xl border text-sm font-medium transition-all">Easy (4 intervals)</button>
                <button @click="difficulty='medium'"
                        :class="difficulty==='medium' ? 'border-sky-400 text-sky-300 bg-sky-400/10' : 'border-white/10 text-white/40'"
                        class="px-4 py-2 rounded-xl border text-sm font-medium transition-all">Medium (8)</button>
                <button @click="difficulty='hard'"
                        :class="difficulty==='hard' ? 'border-sky-400 text-sky-300 bg-sky-400/10' : 'border-white/10 text-white/40'"
                        class="px-4 py-2 rounded-xl border text-sm font-medium transition-all">Hard (all 12)</button>
            </div>
            @if($personalBest > 0)
            <div class="flex items-center gap-1.5 text-white/30 text-sm">
                <i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i>
                Personal best: <span class="text-white font-bold ml-1">{{ number_format($personalBest) }}</span>
            </div>
            @endif
            <button @click="startGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-sky-400 to-blue-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform">
                <span class="flex items-center gap-2">
                    <i data-lucide="play" class="w-4 h-4 fill-current"></i>
                    Start Game
                </span>
            </button>
        </div>

        {{-- Playing --}}
        <div x-show="gameState === 'playing'" class="flex flex-col items-center gap-5">
            <div class="flex items-center gap-5">
                <div class="text-center w-12">
                    <div class="font-black text-3xl tabular-nums"
                         :class="timeLeft <= 3 ? 'text-red-400' : 'text-white/60'"
                         x-text="timeLeft"></div>
                    <div class="text-white/30 text-xs">sec</div>
                </div>
                <button @click="playInterval()"
                        class="w-20 h-20 rounded-2xl bg-gradient-to-br from-sky-400 to-blue-600 shadow-xl flex items-center justify-center hover:scale-105 active:scale-95 transition-transform">
                    <i data-lucide="volume-2" class="w-9 h-9 text-white"></i>
                </button>
                <div class="w-12"></div>
            </div>

            <p class="text-white/40 text-xs">What interval is this?</p>

            <div class="grid grid-cols-2 gap-2.5 w-full max-w-xs">
                <template x-for="(opt, i) in options" :key="i">
                    <button @click="answer(opt)"
                            :disabled="answered"
                            :class="{
                                'correct': answered && opt === currentInterval.name,
                                'wrong': answered && selectedAnswer === opt && opt !== currentInterval.name,
                                'disabled-neutral': answered && opt !== currentInterval.name && selectedAnswer !== opt
                            }"
                            class="answer-btn text-white font-semibold text-sm py-3.5 rounded-xl">
                        <span x-text="opt"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- Game Over --}}
        <div x-show="gameState === 'gameover'" class="flex flex-col items-center justify-center gap-5 py-4">
            <div class="text-5xl">🎵</div>
            <div class="text-center">
                <div class="text-white/40 text-sm mb-1">Final Score</div>
                <div class="text-5xl font-black text-white tabular-nums" x-text="score"></div>
            </div>
            <div class="flex items-center gap-5 text-sm text-white/50">
                <span x-text="correctCount + ' correct'"></span>
                <span>Best streak: <span x-text="maxStreak"></span></span>
                <span>Level <span x-text="level"></span></span>
            </div>
            <div x-show="isNewBest" class="px-4 py-2 rounded-full bg-yellow-400/20 border border-yellow-400/30 text-yellow-300 text-sm font-bold">
                New Personal Best!
            </div>
            <button @click="resetGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-sky-400 to-blue-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform mt-2">
                Play Again
            </button>
        </div>

    </div>

    @if($dailyLimit !== -1)
    <div class="px-6 pb-4 text-center">
        <span class="text-white/20 text-xs">{{ $dailyPlaysUsed }}/{{ $dailyLimit }} plays used today</span>
    </div>
    @endif
</div>

<script>
(function() {
    const PERSONAL_BEST = {{ $personalBest }};
    const SCORE_URL = '{{ route('games.score', 'interval-blitz') }}';
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    function saveScore(score, maxStreak, levelReached, metadata) {
        fetch(SCORE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ score, max_streak: maxStreak, level_reached: levelReached, metadata })
        }).catch(() => {});
    }

    const ALL_INTERVALS = [
        {name:'Minor 2nd', semitones:1}, {name:'Major 2nd', semitones:2},
        {name:'Minor 3rd', semitones:3}, {name:'Major 3rd', semitones:4},
        {name:'Perfect 4th', semitones:5}, {name:'Tritone', semitones:6},
        {name:'Perfect 5th', semitones:7}, {name:'Minor 6th', semitones:8},
        {name:'Major 6th', semitones:9}, {name:'Minor 7th', semitones:10},
        {name:'Major 7th', semitones:11}, {name:'Octave', semitones:12},
    ];

    function noteToMidi(note) {
        const n = {'C':0,'C#':1,'D':2,'D#':3,'E':4,'F':5,'F#':6,'G':7,'G#':8,'A':9,'A#':10,'B':11};
        const m = note.match(/([A-G]#?)(\d)/);
        return n[m[1]] + (parseInt(m[2]) + 1) * 12;
    }
    function midiToNote(midi) {
        const names = ['C','C#','D','D#','E','F','F#','G','G#','A','A#','B'];
        return names[midi % 12] + (Math.floor(midi / 12) - 1);
    }

    document.addEventListener('alpine:init', function() {
        Alpine.data('intervalBlitzGame', function() {
            return {
                gameState: 'idle',
                difficulty: 'medium',
                lives: 3,
                score: 0,
                streak: 0,
                maxStreak: 0,
                correctCount: 0,
                level: 1,
                currentInterval: null,
                options: [],
                answered: false,
                selectedAnswer: null,
                timeLeft: 10,
                timer: null,
                isNewBest: false,
                personalBest: PERSONAL_BEST,

                get intervalPool() {
                    if (this.difficulty === 'easy')   return ALL_INTERVALS.slice(0, 4);
                    if (this.difficulty === 'medium') return ALL_INTERVALS.slice(0, 8);
                    return ALL_INTERVALS;
                },

                init() { lucide.createIcons(); },

                startGame() {
                    this.lives = 3; this.score = 0; this.streak = 0;
                    this.maxStreak = 0; this.correctCount = 0; this.level = 1;
                    this.isNewBest = false;
                    this.gameState = 'playing';
                    this.nextQuestion();
                },

                nextQuestion() {
                    this.answered = false; this.selectedAnswer = null;
                    const pool = this.intervalPool;
                    this.currentInterval = pool[Math.floor(Math.random() * pool.length)];
                    const distractors = pool.filter(i => i.name !== this.currentInterval.name)
                        .sort(() => Math.random()-0.5).slice(0,3).map(i => i.name);
                    this.options = [...distractors, this.currentInterval.name].sort(() => Math.random()-0.5);
                    this.timeLeft = Math.max(5, 10 - Math.floor(this.level / 5));
                    this.$nextTick(() => { lucide.createIcons(); this.playInterval(); });
                    this.startTimer();
                },

                startTimer() {
                    clearInterval(this.timer);
                    this.timer = setInterval(() => {
                        this.timeLeft--;
                        if (this.timeLeft <= 0) this.timeout();
                    }, 1000);
                },

                playInterval() {
                    if (!this.currentInterval || !window.HarmonivaAudio) return;
                    const roots = ['C4','D4','E4','F4','G4','A4'];
                    const root = roots[Math.floor(Math.random() * roots.length)];
                    const top = midiToNote(noteToMidi(root) + this.currentInterval.semitones);
                    HarmonivaAudio.playSequence([root, top], 600, 1);
                },

                answer(chosen) {
                    if (this.answered || this.gameState !== 'playing') return;
                    clearInterval(this.timer);
                    this.answered = true; this.selectedAnswer = chosen;
                    if (chosen === this.currentInterval.name) {
                        this.streak++;
                        this.maxStreak = Math.max(this.maxStreak, this.streak);
                        this.correctCount++;
                        this.score += 100 + (this.streak > 1 ? (this.streak-1)*20 : 0) + (this.timeLeft*10);
                        this.level++;
                        if (this.streak % 5 === 0) this.lives = Math.min(this.lives + 1, 5);
                    } else {
                        this.streak = 0; this.lives--;
                        if (this.lives <= 0) { setTimeout(() => this.endGame(), 800); return; }
                    }
                    setTimeout(() => { if (this.gameState === 'playing') this.nextQuestion(); }, 900);
                },

                timeout() {
                    clearInterval(this.timer);
                    this.answered = true; this.selectedAnswer = '__timeout__';
                    this.streak = 0; this.lives--;
                    if (this.lives <= 0) { setTimeout(() => this.endGame(), 600); return; }
                    setTimeout(() => { if (this.gameState === 'playing') this.nextQuestion(); }, 900);
                },

                endGame() {
                    clearInterval(this.timer);
                    this.gameState = 'gameover';
                    this.isNewBest = this.score > this.personalBest;
                    if (this.isNewBest) this.personalBest = this.score;
                    saveScore(this.score, this.maxStreak, this.level, { correct: this.correctCount, difficulty: this.difficulty });
                    this.$nextTick(() => lucide.createIcons());
                },

                resetGame() { this.gameState = 'idle'; }
            };
        });
    });
})();
</script>

@endif
</div>
