<div>
@if(!$canPlay && $dailyLimit !== -1)
    {{-- Paywall --}}
    <div class="game-surface rounded-2xl p-10 text-center">
        <div class="w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center mx-auto mb-5">
            <i data-lucide="lock" class="w-8 h-8 text-amber-400"></i>
        </div>
        <h2 class="text-white text-xl font-bold mb-2">Daily limit reached</h2>
        <p class="text-white/40 text-sm max-w-xs mx-auto mb-6">
            You've used all {{ $dailyLimit }} free plays today. Upgrade to Premium for unlimited games every day.
        </p>
        <a href="{{ route('profile.edit') }}"
           class="inline-block px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white font-semibold text-sm">
            Upgrade to Premium
        </a>
    </div>
@else

<div
    id="note-rush-root"
    x-data="noteRushGame"
    x-init="init()"
    class="game-surface rounded-2xl overflow-hidden"
>
    {{-- Header --}}
    <div class="bg-gradient-to-r from-yellow-500/20 to-orange-500/20 border-b border-white/10 p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center">
                    <i data-lucide="zap" class="w-5 h-5 text-white fill-current"></i>
                </div>
                <div>
                    <div class="text-white font-bold text-sm">Note Rush</div>
                    <div class="text-white/40 text-xs"
                         x-text="gameState === 'idle' ? 'Ready' : (gameState === 'playing' ? 'In progress' : 'Game over')"></div>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-center">
                    <div class="text-white/40 text-xs">Score</div>
                    <div class="text-white font-black text-xl tabular-nums" x-text="score"></div>
                </div>
                <div class="text-center">
                    <div class="text-white/40 text-xs">Streak</div>
                    <div class="font-black text-xl tabular-nums"
                         :class="streak > 0 ? 'text-orange-400' : 'text-white/30'" x-text="streak"></div>
                </div>
                <div class="text-center">
                    <div class="text-white/40 text-xs">Mult.</div>
                    <div class="font-black text-xl"
                         :class="multiplier > 1 ? 'text-yellow-400' : 'text-white/30'"
                         x-text="multiplier + 'x'"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Timer bar --}}
    <div class="h-1.5 bg-white/5">
        <div class="h-full bg-gradient-to-r transition-all"
             :class="timeLeft <= 10 ? 'from-red-400 to-rose-500' : 'from-yellow-400 to-orange-500'"
             :style="'width:' + ((timeLeft / 60) * 100) + '%'"></div>
    </div>

    <div class="p-6 sm:p-8 min-h-64">

        {{-- Idle --}}
        <div x-show="gameState === 'idle'" class="flex flex-col items-center justify-center h-48 gap-5">
            <div class="text-5xl font-black text-white/10 tabular-nums">60</div>
            <p class="text-white/40 text-sm text-center">Identify as many notes as possible<br>in 60 seconds. Build streaks for bonus points!</p>
            @if($personalBest > 0)
            <div class="flex items-center gap-1.5 text-white/30 text-sm">
                <i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i>
                Personal best: <span class="text-white font-bold ml-1">{{ number_format($personalBest) }}</span>
            </div>
            @endif
            <button @click="startGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white font-bold text-sm shadow-lg hover:scale-105 active:scale-95 transition-transform">
                <span class="flex items-center gap-2">
                    <i data-lucide="play" class="w-4 h-4 fill-current"></i>
                    Start Game
                </span>
            </button>
        </div>

        {{-- Playing --}}
        <div x-show="gameState === 'playing'" class="flex flex-col items-center gap-6">
            <div class="flex items-center gap-2">
                <i data-lucide="timer" class="w-4 h-4" :class="timeLeft <= 10 ? 'text-red-400' : 'text-white/40'"></i>
                <span class="tabular-nums font-bold text-2xl"
                      :class="timeLeft <= 10 ? 'text-red-400' : 'text-white/60'"
                      x-text="timeLeft + 's'"></span>
            </div>

            <div class="text-center">
                <p class="text-white/40 text-xs uppercase tracking-wider mb-2">What note is this?</p>
                <button @click="playCurrentNote()"
                        class="w-24 h-24 rounded-2xl bg-gradient-to-br from-yellow-400 to-orange-500 shadow-xl flex items-center justify-center hover:scale-105 active:scale-95 transition-transform mx-auto">
                    <i data-lucide="volume-2" class="w-10 h-10 text-white"></i>
                </button>
                <p class="text-white/30 text-xs mt-2">Tap to replay</p>
            </div>

            <div class="grid grid-cols-2 gap-3 w-full max-w-sm">
                <template x-for="(opt, idx) in options" :key="idx">
                    <button
                        @click="answer(opt)"
                        :disabled="answered"
                        :class="{
                            'correct': answered && opt === currentNote,
                            'wrong': answered && selectedAnswer === opt && opt !== currentNote,
                            'disabled-neutral': answered && opt !== currentNote && selectedAnswer !== opt
                        }"
                        class="answer-btn text-white font-bold text-lg py-4 rounded-xl">
                        <span x-text="opt"></span>
                    </button>
                </template>
            </div>

            <div x-show="streak >= 3" class="flex items-center gap-1.5 text-orange-400 text-sm font-semibold">
                <i data-lucide="flame" class="w-4 h-4"></i>
                <span x-text="streak + ' streak! ' + multiplier + 'x points'"></span>
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
                <span class="flex items-center gap-1.5">
                    <i data-lucide="target" class="w-4 h-4 text-green-400"></i>
                    <span x-text="correctCount + ' correct'"></span>
                </span>
                <span class="flex items-center gap-1.5">
                    <i data-lucide="flame" class="w-4 h-4 text-orange-400"></i>
                    <span x-text="maxStreak + ' best streak'"></span>
                </span>
            </div>
            <div x-show="isNewBest" class="px-4 py-2 rounded-full bg-yellow-400/20 border border-yellow-400/30 text-yellow-300 text-sm font-bold">
                New Personal Best!
            </div>
            <button @click="resetGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-yellow-400 to-orange-500 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform mt-2">
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
    const GAME_SLUG = 'note-rush';
    const PERSONAL_BEST = {{ $personalBest }};
    const SCORE_URL = '{{ route('games.score', 'note-rush') }}';
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    function saveScore(score, maxStreak, levelReached, metadata) {
        fetch(SCORE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ score, max_streak: maxStreak, level_reached: levelReached, metadata })
        }).catch(() => {});
    }

    document.addEventListener('alpine:init', function() {
        Alpine.data('noteRushGame', function() {
            return {
                gameState: 'idle',
                timeLeft: 60,
                score: 0,
                streak: 0,
                maxStreak: 0,
                multiplier: 1,
                correctCount: 0,
                currentNote: null,
                currentOctave: 4,
                options: [],
                answered: false,
                selectedAnswer: null,
                isNewBest: false,
                personalBest: PERSONAL_BEST,
                timer: null,

                allNotes: ['C','C#','D','D#','E','F','F#','G','G#','A','A#','B'],
                easyNotes: ['C','D','E','F','G','A','B'],

                init() { lucide.createIcons(); },

                startGame() {
                    this.score = 0; this.streak = 0; this.maxStreak = 0;
                    this.multiplier = 1; this.correctCount = 0;
                    this.isNewBest = false; this.timeLeft = 60;
                    this.gameState = 'playing';
                    this.timer = setInterval(() => {
                        this.timeLeft--;
                        if (this.timeLeft <= 0) this.endGame();
                    }, 1000);
                    this.nextQuestion();
                },

                nextQuestion() {
                    this.answered = false;
                    this.selectedAnswer = null;
                    const level = Math.floor((60 - this.timeLeft) / 15);
                    const pool = level < 2 ? this.easyNotes : this.allNotes;
                    this.currentNote = pool[Math.floor(Math.random() * pool.length)];
                    this.currentOctave = level < 1 ? 4 : [3,4,5][Math.floor(Math.random()*3)];
                    const opts = new Set([this.currentNote]);
                    while (opts.size < 4) opts.add(pool[Math.floor(Math.random()*pool.length)]);
                    this.options = [...opts].sort(() => Math.random() - 0.5);
                    this.$nextTick(() => {
                        lucide.createIcons();
                        this.playCurrentNote();
                    });
                },

                playCurrentNote() {
                    if (!this.currentNote || !window.HarmonivaAudio) return;
                    HarmonivaAudio.playNote(this.currentNote + this.currentOctave, 1.2);
                },

                answer(chosen) {
                    if (this.answered || this.gameState !== 'playing') return;
                    this.answered = true;
                    this.selectedAnswer = chosen;
                    if (chosen === this.currentNote) {
                        this.streak++;
                        this.maxStreak = Math.max(this.maxStreak, this.streak);
                        this.multiplier = this.streak >= 10 ? 3 : this.streak >= 5 ? 2 : 1;
                        this.score += 100 * this.multiplier;
                        this.correctCount++;
                    } else {
                        this.streak = 0; this.multiplier = 1;
                    }
                    setTimeout(() => { if (this.gameState === 'playing') this.nextQuestion(); }, 700);
                },

                endGame() {
                    clearInterval(this.timer);
                    this.gameState = 'gameover';
                    this.isNewBest = this.score > this.personalBest;
                    if (this.isNewBest) this.personalBest = this.score;
                    const level = Math.floor(this.maxStreak / 5) + 1;
                    saveScore(this.score, this.maxStreak, level, { correct: this.correctCount, duration: 60 });
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
