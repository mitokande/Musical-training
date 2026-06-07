{{-- Melody Memory game partial --}}

@if(!$canPlay && $dailyLimit !== -1)
    <div class="game-surface rounded-2xl p-10 text-center">
        <div class="w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center mx-auto mb-5">
            <i data-lucide="lock" class="w-8 h-8 text-amber-400"></i>
        </div>
        <h2 class="text-white text-xl font-bold mb-2">{{ __('app.games.daily_limit_title') }}</h2>
        <p class="text-white/40 text-sm max-w-xs mx-auto mb-6">
            @auth
                @if(auth()->user()->plan === 'free')
                    {{ __('app.games.daily_limit_desc', ['limit' => $dailyLimit]) }}
                @else
                    {{ __('app.games.daily_limit_premium_desc', ['limit' => $dailyLimit]) }}
                @endif
            @else
                {{ __('app.games.daily_limit_desc', ['limit' => $dailyLimit]) }}
            @endauth
        </p>
        @auth
            @if(auth()->user()->plan === 'free')
            <a href="{{ route('profile.edit') }}"
               class="inline-block px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white font-semibold text-sm">
                {{ __('app.games.upgrade_premium') }}
            </a>
            @else
            <a href="{{ route('games.index') }}"
               class="inline-block px-6 py-3 rounded-xl bg-white/8 border border-white/12 text-white/70 font-semibold text-sm hover:bg-white/12 transition-all">
                ← {{ __('app.nav.games') }}
            </a>
            @endif
        @else
            <a href="{{ route('register') }}"
               class="inline-block px-6 py-3 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold text-sm">
                {{ __('app.popup.sign_up') }}
            </a>
        @endauth
    </div>
@else

<div x-data="melodyMemoryGame()" x-init="onInit()" class="game-surface rounded-2xl overflow-hidden">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-purple-600/20 to-indigo-600/20 border-b border-white/10 py-4 px-5 sm:p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('games.index') }}" class="w-10 h-10 rounded-xl bg-white border border-white/20 flex items-center justify-center text-red-600 font-bold hover:bg-white/90 transition-all flex-shrink-0">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center">
                    <i data-lucide="music" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <div class="text-white font-bold text-sm">Melody Memory</div>
                    <div class="text-white/40 text-xs"
                         x-text="phase==='idle'?str.ready:(phase==='listening'?str.listening:(phase==='input'?str.yourTurn:(phase==='gameover'?str.gameOver:str.correct)))"></div>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-center"><div class="text-white/40 text-xs" x-text="str.roundLabel">Round</div><div class="text-white font-black text-xl tabular-nums" x-text="round"></div></div>
                <div class="text-center"><div class="text-white/40 text-xs">Score</div><div class="text-white font-black text-xl tabular-nums" x-text="score"></div></div>
            </div>
        </div>
    </div>

    <div class="p-5 sm:p-6">

        {{-- Idle --}}
        <div x-show="phase==='idle'" class="flex flex-col items-center justify-center h-48 gap-5">
            <div class="text-white/10 text-5xl">♩♪♫♬</div>
            <p class="text-white/40 text-sm text-center max-w-xs" x-text="str.melodyDesc"></p>
            @if($personalBest > 0)
            <div class="text-white/30 text-sm flex items-center gap-1.5">
                <i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i>
                <span x-text="str.bestRound"></span> <span class="text-white font-bold ml-1">{{ $personalBest }}</span>
            </div>
            @endif
            <button @click="startGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform">
                <span class="flex items-center gap-2"><i data-lucide="play" class="w-4 h-4 fill-current"></i> <span x-text="str.startGame"></span></span>
            </button>
        </div>

        {{-- Playing --}}
        <div x-show="phase!=='idle' && phase!=='gameover'">

            {{-- Dots --}}
            <div class="flex justify-center gap-2 mb-4 flex-wrap">
                <template x-for="(note,i) in sequence" :key="i">
                    <div class="w-3 h-3 rounded-full transition-all duration-150"
                         :class="highlightIndex===i?'bg-purple-400 scale-125':(userInput.length>i&&phase==='input'?'bg-green-400':'bg-white/15')"></div>
                </template>
            </div>

            {{-- Status --}}
            <div class="text-center mb-4 h-7 flex items-center justify-center">
                <span x-show="phase==='listening'" class="text-purple-300 text-sm font-medium flex items-center gap-2">
                    <i data-lucide="ear" class="w-4 h-4"></i> <span x-text="str.listenCarefully"></span>
                </span>
                <span x-show="phase==='input'" class="text-white/60 text-sm flex items-center gap-2">
                    <i data-lucide="music" class="w-4 h-4 text-purple-400"></i>
                    <span x-text="str.yourTurn"></span> (<span x-text="userInput.length"></span>/<span x-text="sequence.length"></span>)
                </span>
                <span x-show="phase==='correct'" class="text-green-400 text-sm font-bold flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> <span x-text="str.correct"></span>
                </span>
            </div>

            {{-- Piano --}}
            <div class="relative flex justify-center" style="height:90px;"
                 :style="phase!=='input'?'opacity:0.4;pointer-events:none':''">
                <div class="flex gap-1 relative">
                    <template x-for="wk in whiteKeys" :key="wk.note">
                        <button @click="pressKey(wk.note)"
                                :class="pressedKey===wk.note?'bg-purple-200':'bg-white/90 hover:bg-white'"
                                class="rounded-b-lg border border-black/10 cursor-pointer transition-colors"
                                style="width:36px;height:90px;"></button>
                    </template>
                    <template x-for="bk in blackKeys" :key="bk.note">
                        <button @click.stop="pressKey(bk.note)"
                                :class="pressedKey===bk.note?'bg-indigo-600':'bg-gray-900 hover:bg-gray-700'"
                                class="absolute rounded-b-md cursor-pointer z-10 border border-white/5 transition-colors"
                                :style="'left:'+bk.left+'px;top:0;width:24px;height:56px'"></button>
                    </template>
                </div>
            </div>
            <div class="flex justify-center gap-1 mt-1">
                <template x-for="wk in whiteKeys" :key="wk.note">
                    <div class="text-white/20 text-xs text-center" style="width:36px;" x-text="wk.label"></div>
                </template>
            </div>

            <div class="flex justify-center mt-4">
                <button @click="playSequenceManual()" :disabled="phase==='listening'"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/5 border border-white/10 text-white/50 hover:text-white hover:bg-white/10 transition text-xs disabled:opacity-40 disabled:cursor-not-allowed">
                    <i data-lucide="repeat" class="w-3.5 h-3.5"></i> <span x-text="str.replayMelody"></span>
                </button>
            </div>
        </div>

        {{-- Game Over --}}
        <div x-show="phase==='gameover'" class="flex flex-col items-center gap-5 py-6">
            <div class="text-5xl">🎶</div>
            <div class="text-center">
                <div class="text-white/40 text-sm mb-1" x-text="str.youReached"></div>
                <div class="text-5xl font-black text-white tabular-nums" x-text="str.roundLabel+' '+round"></div>
                <div class="text-white/40 text-sm mt-1" x-text="score+' '+str.points"></div>
            </div>
            <div x-show="isNewBest" class="px-4 py-2 rounded-full bg-yellow-400/20 border border-yellow-400/30 text-yellow-300 text-sm font-bold" x-text="str.newBest+' 🏆'">
            </div>
            <p x-show="wrongNote" class="text-white/30 text-xs" x-text="wrongNote?'✗ '+wrongNote.expected+' / '+wrongNote.got:''"></p>
            <button @click="resetGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform"
                    x-text="limitReached ? '{{ __('app.games.daily_limit_title') }} →' : str.playAgain">
            </button>
        </div>

    </div>

    @if($dailyLimit !== -1)
    <div class="px-6 pb-4 text-center text-white/20 text-xs">{{ __('app.games.plays_used', ['used' => $dailyPlaysUsed, 'limit' => $dailyLimit]) }}</div>
    @endif
</div>

<script>
function melodyMemoryGame() {
    const PERSONAL_BEST = {{ (int)$personalBest }};
    const SCORE_URL = @json(route('games.score', 'melody-memory'));
    const STR = window.GAME_STRINGS || {};

    return {
        str: Object.assign({ ready: 'Ready', listening: 'Listen...', yourTurn: 'Your turn', gameOver: 'Game Over', correct: 'Correct! Next round...', roundLabel: 'Round', startGame: 'Start Game', melodyDesc: 'Listen to the melody, then repeat it on the piano. Each round adds one more note.', bestRound: 'Best: Round', listenCarefully: 'Listen carefully...', replayMelody: 'Replay Melody', youReached: 'You reached', points: 'points', newBest: 'New Personal Best!', playAgain: 'Play Again' }, STR, { roundLabel: STR.round || 'Round', correct: (STR.correct || 'Correct!'), melodyDesc: STR.melodyDesc || STR.melody_desc || 'Listen to the melody.', bestRound: STR.bestRound || STR.best_round || 'Best: Round', replayMelody: STR.replayMelody || STR.replay_melody || 'Replay Melody', listenCarefully: STR.listenCarefully || STR.listen_carefully || 'Listen carefully...' }),
        limitReached: false,
        phase: 'idle', round: 0, score: 0,
        sequence: [], userInput: [], highlightIndex: -1,
        pressedKey: null, isNewBest: false, personalBest: PERSONAL_BEST, wrongNote: null,

        notePool: ['C4','D4','E4','F4','G4','A4','B4','C5','D5','E5'],
        whiteKeys: [
            {note:'C4',label:'C'},{note:'D4',label:'D'},{note:'E4',label:'E'},
            {note:'F4',label:'F'},{note:'G4',label:'G'},{note:'A4',label:'A'},
            {note:'B4',label:'B'},{note:'C5',label:'C'},{note:'D5',label:'D'},{note:'E5',label:'E'},
        ],
        blackKeys: [
            {note:'C#4',left:25},{note:'D#4',left:62},{note:'F#4',left:136},
            {note:'G#4',left:173},{note:'A#4',left:210},{note:'C#5',left:284},{note:'D#5',left:321},
        ],

        onInit() { lucide.createIcons(); },

        startGame() {
            this.sequence=[]; this.score=0; this.round=0;
            this.isNewBest=false; this.wrongNote=null;
            this.nextRound();
        },

        nextRound() {
            this.round++;
            this.userInput=[];
            const pool = this.round>5 ? this.notePool : this.notePool.slice(0,7);
            this.sequence = [...this.sequence, pool[Math.floor(Math.random()*pool.length)]];
            setTimeout(() => this.playSequenceAuto(), 600);
        },

        async playSequenceAuto() {
            this.phase='listening';
            for (let i=0; i<this.sequence.length; i++) {
                this.highlightIndex=i;
                if (window.HarmonivaAudio) HarmonivaAudio.playNote(this.sequence[i], 0.8);
                await new Promise(r=>setTimeout(r,700));
            }
            this.highlightIndex=-1;
            this.phase='input';
            this.$nextTick(()=>lucide.createIcons());
        },

        playSequenceManual() {
            if (this.phase==='listening') return;
            this.playSequenceAuto();
        },

        pressKey(note) {
            if (this.phase!=='input') return;
            this.pressedKey=note;
            if (window.HarmonivaAudio) HarmonivaAudio.playNote(note, 0.6);
            setTimeout(()=>{this.pressedKey=null;},200);

            const expected = this.sequence[this.userInput.length];
            if (note!==expected) { this.wrongNote={expected,got:note}; this.endGame(); return; }
            this.userInput.push(note);
            if (this.userInput.length===this.sequence.length) {
                this.score += this.round*100;
                this.phase='correct';
                setTimeout(()=>this.nextRound(), 1200);
            }
        },

        endGame() {
            this.phase='gameover';
            this.isNewBest = this.score > this.personalBest;
            if (this.isNewBest) this.personalBest=this.score;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content??'';
            fetch(SCORE_URL,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
                body:JSON.stringify({score:this.score,max_streak:this.round-1,level_reached:this.round,metadata:{rounds:this.round-1}})
            })
            .then(r=>r.json())
            .then(data=>{ if(data.can_play_again===false) this.limitReached=true; })
            .catch(()=>{});
            this.$nextTick(()=>lucide.createIcons());
        },

        resetGame() {
            if (this.limitReached) { window.location.reload(); return; }
            this.phase='idle'; this.round=0; this.sequence=[];
        }
    };
}
</script>
@endif
