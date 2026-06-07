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
    id="chord-clash-root"
    x-data="chordClashGame"
    x-init="init()"
    class="game-surface rounded-2xl overflow-hidden"
>
    {{-- Header --}}
    <div class="bg-gradient-to-r from-rose-500/20 to-pink-600/20 border-b border-white/10 p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-400 to-pink-600 flex items-center justify-center">
                    <i data-lucide="layers" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <div class="text-white font-bold text-sm">Chord Clash</div>
                    <div class="text-white/40 text-xs"
                         x-text="gameState === 'idle' ? 'Ready' : (gameState === 'playing' ? 'Round ' + round : 'Game Over')"></div>
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
                         :class="streak > 0 ? 'text-rose-400' : 'text-white/30'"
                         x-text="streak"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-6 sm:p-8 min-h-64">

        {{-- Idle --}}
        <div x-show="gameState === 'idle'" class="flex flex-col items-center justify-center h-48 gap-5">
            <div class="text-white/10 text-5xl font-black">Cmaj Amin</div>
            <p class="text-white/40 text-sm text-center">Two chords play — pick the one that matches the name.<br>3 lives. Difficulty increases each round.</p>
            @if($personalBest > 0)
            <div class="flex items-center gap-1.5 text-white/30 text-sm">
                <i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i>
                Personal best: <span class="text-white font-bold ml-1">{{ number_format($personalBest) }}</span>
            </div>
            @endif
            <button @click="startGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-rose-400 to-pink-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform">
                <span class="flex items-center gap-2">
                    <i data-lucide="play" class="w-4 h-4 fill-current"></i>
                    Start Game
                </span>
            </button>
        </div>

        {{-- Playing --}}
        <div x-show="gameState === 'playing'" class="flex flex-col items-center gap-5">

            {{-- Target label --}}
            <div class="text-center">
                <p class="text-white/40 text-xs uppercase tracking-wider mb-2">Find this chord:</p>
                <div class="inline-block px-6 py-3 rounded-2xl bg-white/8 border border-white/15">
                    <span class="text-white font-black text-2xl" x-text="targetChordLabel"></span>
                </div>
            </div>

            {{-- Two chord buttons --}}
            <div class="grid grid-cols-2 gap-4 w-full max-w-sm">
                <template x-for="(chord, idx) in chordOptions" :key="idx">
                    <div class="flex flex-col gap-2">
                        <button @click="playChordOption(idx)"
                                class="w-full aspect-square rounded-2xl bg-white/6 border border-white/12 flex flex-col items-center justify-center gap-2 hover:bg-white/12 hover:border-white/25 active:scale-95 transition-all">
                            <i data-lucide="volume-2" class="w-8 h-8 text-white/60"></i>
                            <span class="text-white/40 text-xs">Chord <span x-text="idx + 1"></span></span>
                        </button>
                        <button @click="answer(idx)"
                                :disabled="answered"
                                :class="{
                                    'correct': answered && idx === correctIndex,
                                    'wrong': answered && idx !== correctIndex && selectedIndex === idx,
                                    'disabled-neutral': answered && idx !== correctIndex && selectedIndex !== idx
                                }"
                                class="answer-btn w-full py-3 rounded-xl text-white font-semibold text-sm">
                            This one!
                        </button>
                    </div>
                </template>
            </div>

            <button @click="playBoth()" :disabled="answered"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/5 border border-white/10 text-white/50 hover:text-white hover:bg-white/10 transition text-xs disabled:opacity-40">
                <i data-lucide="repeat" class="w-3.5 h-3.5"></i> Play both again
            </button>
        </div>

        {{-- Game Over --}}
        <div x-show="gameState === 'gameover'" class="flex flex-col items-center justify-center gap-5 py-4">
            <div class="text-5xl">🎹</div>
            <div class="text-center">
                <div class="text-white/40 text-sm mb-1">Final Score</div>
                <div class="text-5xl font-black text-white tabular-nums" x-text="score"></div>
            </div>
            <div class="flex items-center gap-5 text-sm text-white/50">
                <span x-text="(round - 1) + ' rounds'"></span>
                <span>Best streak: <span x-text="maxStreak"></span></span>
            </div>
            <div x-show="isNewBest" class="px-4 py-2 rounded-full bg-yellow-400/20 border border-yellow-400/30 text-yellow-300 text-sm font-bold">
                New Personal Best!
            </div>
            <button @click="resetGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-rose-400 to-pink-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform mt-2">
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
    const SCORE_URL = '{{ route('games.score', 'chord-clash') }}';
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    function saveScore(score, maxStreak, levelReached, metadata) {
        fetch(SCORE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ score, max_streak: maxStreak, level_reached: levelReached, metadata })
        }).catch(() => {});
    }

    const TIERS = [
        [{name:'Major',intervals:[0,4,7]},{name:'Minor',intervals:[0,3,7]}],
        [{name:'Major',intervals:[0,4,7]},{name:'Minor',intervals:[0,3,7]},{name:'Diminished',intervals:[0,3,6]},{name:'Augmented',intervals:[0,4,8]}],
        [{name:'Major',intervals:[0,4,7]},{name:'Minor',intervals:[0,3,7]},{name:'Diminished',intervals:[0,3,6]},{name:'Dom 7',intervals:[0,4,7,10]},{name:'Maj 7',intervals:[0,4,7,11]},{name:'Min 7',intervals:[0,3,7,10]}],
        [{name:'Major',intervals:[0,4,7]},{name:'Minor',intervals:[0,3,7]},{name:'Dom 7',intervals:[0,4,7,10]},{name:'Maj 7',intervals:[0,4,7,11]},{name:'Min 7',intervals:[0,3,7,10]},{name:'Half-dim 7',intervals:[0,3,6,10]},{name:'Dim 7',intervals:[0,3,6,9]}],
    ];
    const ROOTS = ['C4','D4','E4','F4','G4','A4'];
    const NOTE_NAMES = {'C':0,'C#':1,'D':2,'D#':3,'E':4,'F':5,'F#':6,'G':7,'G#':8,'A':9,'A#':10,'Bb':10,'B':11};
    const MIDI_NAMES = ['C','C#','D','D#','E','F','F#','G','G#','A','A#','B'];

    function noteToMidi(note) {
        const m = note.match(/([A-G][b#]?)(\d)/);
        return NOTE_NAMES[m[1]] + (parseInt(m[2]) + 1) * 12;
    }
    function midiToNote(midi) {
        return MIDI_NAMES[midi % 12] + (Math.floor(midi/12) - 1);
    }
    function buildChord(root, intervals) {
        const base = noteToMidi(root);
        return intervals.map(i => midiToNote(base + i));
    }

    document.addEventListener('alpine:init', function() {
        Alpine.data('chordClashGame', function() {
            return {
                gameState: 'idle',
                lives: 3,
                score: 0,
                streak: 0,
                maxStreak: 0,
                round: 1,
                targetChordLabel: '',
                chordOptions: [],
                correctIndex: 0,
                answered: false,
                selectedIndex: null,
                isNewBest: false,
                personalBest: PERSONAL_BEST,

                get currentTier() {
                    if (this.round <= 5)  return 0;
                    if (this.round <= 12) return 1;
                    if (this.round <= 20) return 2;
                    return 3;
                },

                init() { lucide.createIcons(); },

                startGame() {
                    this.lives = 3; this.score = 0; this.streak = 0;
                    this.maxStreak = 0; this.round = 1; this.isNewBest = false;
                    this.gameState = 'playing';
                    this.nextQuestion();
                },

                nextQuestion() {
                    this.answered = false; this.selectedIndex = null;
                    const pool = TIERS[this.currentTier];
                    const targetType = pool[Math.floor(Math.random() * pool.length)];
                    let distType;
                    do { distType = pool[Math.floor(Math.random() * pool.length)]; }
                    while (distType.name === targetType.name);

                    const rootA = ROOTS[Math.floor(Math.random() * ROOTS.length)];
                    let rootB;
                    do { rootB = ROOTS[Math.floor(Math.random() * ROOTS.length)]; }
                    while (rootB === rootA);

                    const targetNotes = buildChord(rootA, targetType.intervals);
                    const distNotes = buildChord(rootB, distType.intervals);

                    this.correctIndex = Math.random() < 0.5 ? 0 : 1;
                    this.chordOptions = this.correctIndex === 0
                        ? [{label:targetType.name,notes:targetNotes},{label:distType.name,notes:distNotes}]
                        : [{label:distType.name,notes:distNotes},{label:targetType.name,notes:targetNotes}];

                    const rootLabel = rootA.replace('4','');
                    this.targetChordLabel = rootLabel + ' ' + targetType.name;

                    this.$nextTick(() => {
                        lucide.createIcons();
                        this.playBoth();
                    });
                },

                playChordOption(idx) {
                    if (!window.HarmonivaAudio) return;
                    HarmonivaAudio.playChord(this.chordOptions[idx].notes, 2);
                },

                playBoth() {
                    if (!window.HarmonivaAudio) return;
                    HarmonivaAudio.playChord(this.chordOptions[0].notes, 1.5);
                    setTimeout(() => HarmonivaAudio.playChord(this.chordOptions[1].notes, 1.5), 1800);
                },

                answer(idx) {
                    if (this.answered || this.gameState !== 'playing') return;
                    this.answered = true; this.selectedIndex = idx;
                    if (idx === this.correctIndex) {
                        this.streak++;
                        this.maxStreak = Math.max(this.maxStreak, this.streak);
                        this.score += 150 + this.streak * 25 + this.currentTier * 50;
                        this.round++;
                    } else {
                        this.streak = 0; this.lives--;
                        if (this.lives <= 0) { setTimeout(() => this.endGame(), 900); return; }
                    }
                    setTimeout(() => { if (this.gameState === 'playing') this.nextQuestion(); }, 1000);
                },

                endGame() {
                    this.gameState = 'gameover';
                    this.isNewBest = this.score > this.personalBest;
                    if (this.isNewBest) this.personalBest = this.score;
                    saveScore(this.score, this.maxStreak, this.currentTier + 1, { rounds: this.round - 1 });
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
