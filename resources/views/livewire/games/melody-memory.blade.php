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
    id="melody-memory-root"
    x-data="melodyMemoryGame"
    x-init="init()"
    class="game-surface rounded-2xl overflow-hidden"
>
    {{-- Header --}}
    <div class="bg-gradient-to-r from-purple-600/20 to-indigo-600/20 border-b border-white/10 p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center">
                    <i data-lucide="music" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <div class="text-white font-bold text-sm">Melody Memory</div>
                    <div class="text-white/40 text-xs"
                         x-text="phase === 'idle' ? 'Ready' : (phase === 'listening' ? 'Listen...' : (phase === 'input' ? 'Your turn' : (phase === 'gameover' ? 'Game Over' : 'Correct!')))"></div>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-center">
                    <div class="text-white/40 text-xs">Round</div>
                    <div class="text-white font-black text-xl tabular-nums" x-text="round"></div>
                </div>
                <div class="text-center">
                    <div class="text-white/40 text-xs">Score</div>
                    <div class="text-white font-black text-xl tabular-nums" x-text="score"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-5 sm:p-6">

        {{-- Idle --}}
        <div x-show="phase === 'idle'" class="flex flex-col items-center justify-center h-48 gap-5">
            <div class="text-white/10 text-5xl">♩♪♫♬</div>
            <p class="text-white/40 text-sm text-center">Listen to the melody, then repeat it on the piano.<br>Each round adds one more note.</p>
            @if($personalBest > 0)
            <div class="flex items-center gap-1.5 text-white/30 text-sm">
                <i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i>
                Best: Round <span class="text-white font-bold ml-1">{{ $personalBest }}</span>
            </div>
            @endif
            <button @click="startGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform">
                <span class="flex items-center gap-2">
                    <i data-lucide="play" class="w-4 h-4 fill-current"></i>
                    Start Game
                </span>
            </button>
        </div>

        {{-- Playing --}}
        <div x-show="phase !== 'idle' && phase !== 'gameover'">

            {{-- Sequence dots --}}
            <div class="flex justify-center gap-2 mb-5">
                <template x-for="(note, i) in sequence" :key="i">
                    <div class="w-3 h-3 rounded-full transition-all duration-200"
                         :class="{
                             'bg-purple-400 scale-125': (phase === 'listening' && highlightIndex === i) || (phase === 'input' && userInput.length === i),
                             'bg-green-400': phase === 'input' && userInput.length > i,
                             'bg-white/20': true
                         }">
                    </div>
                </template>
            </div>

            {{-- Status --}}
            <div class="text-center mb-5 h-8 flex items-center justify-center">
                <div x-show="phase === 'listening'" class="flex items-center gap-2 text-purple-300 text-sm font-medium">
                    <i data-lucide="ear" class="w-4 h-4"></i> Listen carefully...
                </div>
                <div x-show="phase === 'input'" class="flex items-center gap-2 text-white/60 text-sm">
                    <i data-lucide="music" class="w-4 h-4 text-purple-400"></i>
                    Your turn
                    <span class="text-white/30">(<span x-text="userInput.length"></span>/<span x-text="sequence.length"></span>)</span>
                </div>
                <div x-show="phase === 'correct'" class="flex items-center gap-2 text-green-400 text-sm font-bold">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Correct! Next round...
                </div>
            </div>

            {{-- Piano --}}
            <div class="relative flex justify-center" style="height:90px;"
                 :style="phase !== 'input' ? 'opacity:0.4;pointer-events:none' : ''">
                <div class="flex gap-1 relative">
                    <template x-for="(wk, i) in whiteKeys" :key="wk.note">
                        <button @click="pressKey(wk.note)"
                                :class="pressedKey === wk.note ? 'bg-purple-200' : 'bg-white/90 hover:bg-white'"
                                class="rounded-b-lg border border-black/10 cursor-pointer transition-colors"
                                style="width:36px;height:90px;">
                        </button>
                    </template>
                    <template x-for="bk in blackKeys" :key="bk.note">
                        <button @click.stop="pressKey(bk.note)"
                                :class="pressedKey === bk.note ? 'bg-indigo-600' : 'bg-gray-900 hover:bg-gray-700'"
                                class="absolute rounded-b-md cursor-pointer z-10 border border-white/5 transition-colors"
                                :style="'left:' + bk.left + 'px;top:0;width:24px;height:56px'">
                        </button>
                    </template>
                </div>
            </div>

            <div class="flex justify-center gap-1 mt-1">
                <template x-for="wk in whiteKeys" :key="wk.note">
                    <div class="text-white/20 text-xs text-center" style="width:36px;" x-text="wk.label"></div>
                </template>
            </div>

            <div class="flex justify-center mt-4">
                <button @click="playSequence()" :disabled="phase === 'listening'"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/5 border border-white/10 text-white/50 hover:text-white hover:bg-white/10 transition text-xs disabled:opacity-40">
                    <i data-lucide="repeat" class="w-3.5 h-3.5"></i> Replay Melody
                </button>
            </div>
        </div>

        {{-- Game Over --}}
        <div x-show="phase === 'gameover'" class="flex flex-col items-center justify-center gap-5 py-6">
            <div class="text-5xl">🎶</div>
            <div class="text-center">
                <div class="text-white/40 text-sm mb-1">You reached</div>
                <div class="text-5xl font-black text-white tabular-nums" x-text="'Round ' + round"></div>
                <div class="text-white/40 text-sm mt-1" x-text="score + ' points'"></div>
            </div>
            <div x-show="isNewBest" class="px-4 py-2 rounded-full bg-yellow-400/20 border border-yellow-400/30 text-yellow-300 text-sm font-bold">
                New Personal Best!
            </div>
            <p class="text-white/30 text-xs" x-show="wrongNote" x-text="wrongNote ? 'Wrong: expected ' + wrongNote.expected + ', played ' + wrongNote.got : ''"></p>
            <button @click="resetGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform">
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
    const SCORE_URL = '{{ route('games.score', 'melody-memory') }}';
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    function saveScore(score, maxStreak, levelReached, metadata) {
        fetch(SCORE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ score, max_streak: maxStreak, level_reached: levelReached, metadata })
        }).catch(() => {});
    }

    document.addEventListener('alpine:init', function() {
        Alpine.data('melodyMemoryGame', function() {
            return {
                phase: 'idle',
                round: 0,
                score: 0,
                sequence: [],
                userInput: [],
                highlightIndex: -1,
                pressedKey: null,
                isNewBest: false,
                personalBest: PERSONAL_BEST,
                wrongNote: null,

                notePool: ['C4','D4','E4','F4','G4','A4','B4','C5','D5','E5'],
                whiteKeys: [
                    {note:'C4',label:'C'},{note:'D4',label:'D'},{note:'E4',label:'E'},
                    {note:'F4',label:'F'},{note:'G4',label:'G'},{note:'A4',label:'A'},
                    {note:'B4',label:'B'},{note:'C5',label:'C'},{note:'D5',label:'D'},
                    {note:'E5',label:'E'},
                ],
                blackKeys: [
                    {note:'C#4',left:25},{note:'D#4',left:62},{note:'F#4',left:136},
                    {note:'G#4',left:173},{note:'A#4',left:210},{note:'C#5',left:284},
                    {note:'D#5',left:321},
                ],

                init() { lucide.createIcons(); },

                startGame() {
                    this.sequence = []; this.score = 0; this.round = 0;
                    this.isNewBest = false; this.wrongNote = null;
                    this.nextRound();
                },

                nextRound() {
                    this.round++;
                    this.userInput = [];
                    const pool = this.round > 5 ? this.notePool : this.notePool.slice(0,7);
                    this.sequence = [...this.sequence, pool[Math.floor(Math.random()*pool.length)]];
                    setTimeout(() => this.playSequence(), 600);
                },

                async playSequence() {
                    this.phase = 'listening';
                    for (let i = 0; i < this.sequence.length; i++) {
                        this.highlightIndex = i;
                        if (window.HarmonivaAudio) HarmonivaAudio.playNote(this.sequence[i], 0.8);
                        await new Promise(r => setTimeout(r, 700));
                    }
                    this.highlightIndex = -1;
                    this.phase = 'input';
                    this.$nextTick(() => lucide.createIcons());
                },

                pressKey(note) {
                    if (this.phase !== 'input') return;
                    this.pressedKey = note;
                    if (window.HarmonivaAudio) HarmonivaAudio.playNote(note, 0.6);
                    setTimeout(() => { this.pressedKey = null; }, 200);

                    const expected = this.sequence[this.userInput.length];
                    if (note !== expected) {
                        this.wrongNote = { expected, got: note };
                        this.endGame();
                        return;
                    }
                    this.userInput.push(note);
                    if (this.userInput.length === this.sequence.length) {
                        this.score += this.round * 100;
                        this.phase = 'correct';
                        setTimeout(() => this.nextRound(), 1200);
                    }
                },

                endGame() {
                    this.phase = 'gameover';
                    this.isNewBest = this.score > this.personalBest;
                    if (this.isNewBest) this.personalBest = this.score;
                    saveScore(this.score, this.round - 1, this.round, { rounds_completed: this.round - 1 });
                    this.$nextTick(() => lucide.createIcons());
                },

                resetGame() { this.phase = 'idle'; this.round = 0; this.sequence = []; }
            };
        });
    });
})();
</script>

@endif
</div>
