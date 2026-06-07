{{-- Chord Clash game partial --}}

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

<div x-data="chordClashGame()" x-init="onInit()" class="game-surface rounded-2xl overflow-hidden">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-rose-500/20 to-pink-600/20 border-b border-white/10 py-4 px-5 sm:p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('games.index') }}" class="w-10 h-10 rounded-xl bg-white border border-white/20 flex items-center justify-center text-red-600 font-bold hover:bg-white/90 transition-all flex-shrink-0">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-400 to-pink-600 flex items-center justify-center">
                    <i data-lucide="layers" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <div class="text-white font-bold text-sm">Chord Clash</div>
                    <div class="text-white/40 text-xs"
                         x-text="gameState==='idle'?str.ready:(gameState==='playing'?str.round+' '+round:str.gameOver)"></div>
                </div>
            </div>
            <div class="flex items-center gap-3 sm:gap-4">
                {{-- Hearts: desktop only (inline) --}}
                <div class="hidden sm:flex items-center gap-0.5">
                    <template x-for="i in 3" :key="i">
                        <span class="text-2xl" :class="i<=lives?'text-red-500':'opacity-40 text-rose-800'">❤</span>
                    </template>
                </div>
                <div class="text-center">
                    <div class="text-white/40 text-xs">Score</div>
                    <div class="text-white font-black text-xl tabular-nums" x-text="score"></div>
                    {{-- Hearts: mobile only (below score) --}}
                    <div class="flex items-center justify-center gap-0.5 mt-0.5 sm:hidden">
                        <template x-for="i in 3" :key="'m'+i">
                            <span class="text-lg" :class="i<=lives?'text-red-500':'opacity-40 text-rose-800'">❤</span>
                        </template>
                    </div>
                </div>
                <div class="text-center"><div class="text-white/40 text-xs" x-text="str.streak">Streak</div><div class="font-black text-xl tabular-nums" :class="streak>0?'text-rose-400':'text-white/30'" x-text="streak"></div></div>
            </div>
        </div>
    </div>

    <div class="p-6 sm:p-8 min-h-72">

        {{-- Idle --}}
        <div x-show="gameState==='idle'" class="flex flex-col items-center justify-center h-56 gap-5">
            <div class="text-white/10 text-5xl font-black">Cmaj Amin</div>
            <p class="text-white/40 text-sm text-center max-w-xs" x-text="str.chordDesc"></p>
            @if($personalBest > 0)
            <div class="text-white/30 text-sm flex items-center gap-1.5">
                <i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i>
                <span x-text="str.personalBest"></span> <span class="text-white font-bold ml-1">{{ number_format($personalBest) }}</span>
            </div>
            @endif
            <button @click="startGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-rose-400 to-pink-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform">
                <span class="flex items-center gap-2"><i data-lucide="play" class="w-4 h-4 fill-current"></i> <span x-text="str.startGame"></span></span>
            </button>
        </div>

        {{-- Playing --}}
        <div x-show="gameState==='playing'" class="flex flex-col items-center gap-5">
            <div class="text-center">
                <p class="text-white/40 text-xs uppercase tracking-wider mb-2" x-text="str.findChord">Find this chord:</p>
                <div class="inline-block px-6 py-3 rounded-2xl bg-white/8 border border-white/15">
                    <span class="text-white font-black text-2xl" x-text="targetChordLabel"></span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 w-full max-w-sm">
                <template x-for="(chord,idx) in chordOptions" :key="idx">
                    <div class="flex flex-col gap-2">
                        <button @click="playChordOption(idx)"
                                class="w-full aspect-square rounded-2xl bg-white/6 border border-white/12 flex flex-col items-center justify-center gap-2 hover:bg-white/12 active:scale-95 transition-all">
                            <i data-lucide="volume-2" class="w-8 h-8 text-white/60"></i>
                            <span class="text-white/40 text-xs"><span x-text="str.chordNum"></span> <span x-text="idx+1"></span></span>
                        </button>
                        <button @click="answer(idx)" :disabled="answered"
                                :class="{
                                    'correct': answered && idx===correctIndex,
                                    'wrong': answered && idx!==correctIndex && selectedIndex===idx,
                                    'disabled-neutral': answered && idx!==correctIndex && selectedIndex!==idx
                                }"
                                class="answer-btn w-full py-3 rounded-xl text-white font-semibold text-sm"
                                x-text="str.thisOne">
                        </button>
                    </div>
                </template>
            </div>

            <button @click="playBoth()" :disabled="answered"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/5 border border-white/10 text-white/50 hover:text-white hover:bg-white/10 transition text-xs disabled:opacity-40">
                <i data-lucide="repeat" class="w-3.5 h-3.5"></i> <span x-text="str.playBoth">Play both again</span>
            </button>
        </div>

        {{-- Game Over --}}
        <div x-show="gameState==='gameover'" class="flex flex-col items-center gap-5 py-4">
            <div class="text-5xl">🎹</div>
            <div class="text-center">
                <div class="text-white/40 text-sm mb-1" x-text="str.finalScore">Final Score</div>
                <div class="text-5xl font-black text-white tabular-nums" x-text="score"></div>
            </div>
            <div class="flex items-center gap-5 text-sm text-white/50">
                <span x-text="(round-1)+' '+str.rounds"></span>
                <span><span x-text="str.streak"></span>: <span x-text="maxStreak"></span></span>
            </div>
            <div x-show="isNewBest" class="px-4 py-2 rounded-full bg-yellow-400/20 border border-yellow-400/30 text-yellow-300 text-sm font-bold" x-text="str.newBest+' 🏆'"></div>
            <button @click="resetGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-rose-400 to-pink-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform"
                    x-text="limitReached ? '{{ __('app.games.daily_limit_title') }} →' : str.playAgain">
            </button>
        </div>

    </div>

    @if($dailyLimit !== -1)
    <div class="px-6 pb-4 text-center text-white/20 text-xs">{{ __('app.games.plays_used', ['used' => $dailyPlaysUsed, 'limit' => $dailyLimit]) }}</div>
    @endif
</div>

<script>
function chordClashGame() {
    const PERSONAL_BEST = {{ (int)$personalBest }};
    const SCORE_URL = @json(route('games.score', 'chord-clash'));
    const STR = window.GAME_STRINGS || {};

    const TIERS=[
        [{name:'Major',i:[0,4,7]},{name:'Minor',i:[0,3,7]}],
        [{name:'Major',i:[0,4,7]},{name:'Minor',i:[0,3,7]},{name:'Diminished',i:[0,3,6]},{name:'Augmented',i:[0,4,8]}],
        [{name:'Major',i:[0,4,7]},{name:'Minor',i:[0,3,7]},{name:'Dom 7',i:[0,4,7,10]},{name:'Maj 7',i:[0,4,7,11]},{name:'Min 7',i:[0,3,7,10]}],
        [{name:'Major',i:[0,4,7]},{name:'Minor',i:[0,3,7]},{name:'Dom 7',i:[0,4,7,10]},{name:'Maj 7',i:[0,4,7,11]},{name:'Min 7',i:[0,3,7,10]},{name:'Half-dim 7',i:[0,3,6,10]},{name:'Dim 7',i:[0,3,6,9]}],
    ];
    const ROOTS=['C4','D4','E4','F4','G4','A4'];
    const NM={'C':0,'C#':1,'D':2,'D#':3,'E':4,'F':5,'F#':6,'G':7,'G#':8,'A':9,'A#':10,'Bb':10,'B':11};
    const MN=['C','C#','D','D#','E','F','F#','G','G#','A','A#','B'];
    function n2m(n){const m=n.match(/([A-G][b#]?)(\d)/);return NM[m[1]]+(parseInt(m[2])+1)*12;}
    function m2n(m){return MN[m%12]+(Math.floor(m/12)-1);}
    function buildChord(root,intervals){const b=n2m(root);return intervals.map(i=>m2n(b+i));}

    return {
        str: Object.assign({ ready: 'Ready', round: 'Round', gameOver: 'Game Over', streak: 'Streak', startGame: 'Start Game', playAgain: 'Play Again', finalScore: 'Final Score', personalBest: 'Personal best:', newBest: 'New Personal Best!', chordDesc: 'Two chords play — pick the one matching the name. 3 lives.', findChord: 'Find this chord:', chordNum: 'Chord', thisOne: 'This one!', playBoth: 'Play both again', rounds: 'rounds' }, STR),
        limitReached: false,
        gameState:'idle', lives:3, score:0, streak:0, maxStreak:0, round:1,
        targetChordLabel:'', chordOptions:[], correctIndex:0,
        answered:false, selectedIndex:null, isNewBest:false, personalBest:PERSONAL_BEST,

        get currentTier(){return this.round<=5?0:this.round<=12?1:this.round<=20?2:3;},

        onInit(){lucide.createIcons();},

        startGame(){
            this.lives=3;this.score=0;this.streak=0;this.maxStreak=0;
            this.round=1;this.isNewBest=false;
            this.gameState='playing'; this.nextQuestion();
        },

        nextQuestion(){
            this.answered=false;this.selectedIndex=null;
            const pool=TIERS[this.currentTier];
            const tType=pool[Math.floor(Math.random()*pool.length)];
            let dType;
            do{dType=pool[Math.floor(Math.random()*pool.length)];}while(dType.name===tType.name);
            const rootA=ROOTS[Math.floor(Math.random()*ROOTS.length)];
            let rootB;do{rootB=ROOTS[Math.floor(Math.random()*ROOTS.length)];}while(rootB===rootA);
            const tNotes=buildChord(rootA,tType.i);
            const dNotes=buildChord(rootB,dType.i);
            this.correctIndex=Math.random()<0.5?0:1;
            this.chordOptions=this.correctIndex===0
                ?[{label:tType.name,notes:tNotes},{label:dType.name,notes:dNotes}]
                :[{label:dType.name,notes:dNotes},{label:tType.name,notes:tNotes}];
            this.targetChordLabel=rootA.replace('4','')+' '+tType.name;
            this.$nextTick(()=>{lucide.createIcons();this.playBoth();});
        },

        playChordOption(idx){if(window.HarmonivaAudio)HarmonivaAudio.playChord(this.chordOptions[idx].notes,2);},

        playBoth(){
            if(!window.HarmonivaAudio)return;
            HarmonivaAudio.playChord(this.chordOptions[0].notes,1.5);
            setTimeout(()=>HarmonivaAudio.playChord(this.chordOptions[1].notes,1.5),1800);
        },

        answer(idx){
            if(this.answered||this.gameState!=='playing')return;
            this.answered=true;this.selectedIndex=idx;
            if(idx===this.correctIndex){
                this.streak++;this.maxStreak=Math.max(this.maxStreak,this.streak);
                this.score+=150+this.streak*25+this.currentTier*50;
                this.round++;
            }else{
                this.streak=0;this.lives--;
                if(this.lives<=0){setTimeout(()=>this.endGame(),900);return;}
            }
            setTimeout(()=>{if(this.gameState==='playing')this.nextQuestion();},1000);
        },

        endGame(){
            this.gameState='gameover';
            this.isNewBest=this.score>this.personalBest;
            if(this.isNewBest)this.personalBest=this.score;
            const csrf=document.querySelector('meta[name="csrf-token"]')?.content??'';
            fetch(SCORE_URL,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
                body:JSON.stringify({score:this.score,max_streak:this.maxStreak,level_reached:this.currentTier+1,metadata:{rounds:this.round-1}})
            })
            .then(r=>r.json())
            .then(data=>{ if(data.can_play_again===false) this.limitReached=true; })
            .catch(()=>{});
            this.$nextTick(()=>lucide.createIcons());
        },

        resetGame(){
            if(this.limitReached){window.location.reload();return;}
            this.gameState='idle';
        }
    };
}
</script>
@endif
