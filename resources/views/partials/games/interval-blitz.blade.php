{{-- Interval Blitz game partial --}}

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
                    <div class="text-white font-bold text-sm">Interval Blitz</div>
                    <div class="text-white/40 text-xs"
                         x-text="gameState==='idle'?str.ready:(gameState==='playing'?str.level+' '+level:str.gameOver)"></div>
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
                <div class="text-center"><div class="text-white/40 text-xs" x-text="str.streak">Streak</div><div class="font-black text-xl tabular-nums" :class="streak>0?'text-sky-400':'text-white/30'" x-text="streak"></div></div>
            </div>
        </div>
    </div>

    {{-- Timer bar --}}
    <div class="h-2 bg-white/5">
        <div class="h-full transition-all duration-1000 rounded-full"
             :class="timeLeft<=3?'bg-gradient-to-r from-red-400 to-rose-500':'bg-gradient-to-r from-sky-400 to-blue-500'"
             :style="'width:'+(timeLeft/10*100)+'%'"></div>
    </div>

    <div class="p-6 sm:p-8 min-h-72">

        {{-- Idle --}}
        <div x-show="gameState==='idle'" class="flex flex-col items-center justify-center gap-5 h-56">
            <div class="text-white/10 text-5xl font-black">P4 m3</div>
            <p class="text-white/40 text-sm text-center max-w-xs" x-text="str.intervalDesc"></p>
            <div class="flex flex-wrap gap-2 justify-center">
                <button @click="difficulty='easy'" :class="difficulty==='easy'?'border-sky-400 text-sky-300 bg-sky-400/10':'border-white/10 text-white/40'" class="px-4 py-2 rounded-xl border text-sm font-medium transition-all" x-text="str.diffEasy">Easy (4)</button>
                <button @click="difficulty='medium'" :class="difficulty==='medium'?'border-sky-400 text-sky-300 bg-sky-400/10':'border-white/10 text-white/40'" class="px-4 py-2 rounded-xl border text-sm font-medium transition-all" x-text="str.diffMedium">Medium (8)</button>
                <button @click="difficulty='hard'" :class="difficulty==='hard'?'border-sky-400 text-sky-300 bg-sky-400/10':'border-white/10 text-white/40'" class="px-4 py-2 rounded-xl border text-sm font-medium transition-all" x-text="str.diffHard">Hard (12)</button>
            </div>
            @if($personalBest > 0)
            <div class="text-white/30 text-sm flex items-center gap-1.5">
                <i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i>
                <span x-text="str.personalBest"></span> <span class="text-white font-bold ml-1">{{ number_format($personalBest) }}</span>
            </div>
            @endif
            <button @click="startGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-sky-400 to-blue-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform">
                <span class="flex items-center gap-2"><i data-lucide="play" class="w-4 h-4 fill-current"></i> <span x-text="str.startGame"></span></span>
            </button>
        </div>

        {{-- Playing --}}
        <div x-show="gameState==='playing'" class="flex flex-col items-center gap-5">
            <div class="flex items-center gap-5">
                <div class="text-center w-12">
                    <div class="font-black text-3xl tabular-nums" :class="timeLeft<=3?'text-red-400':'text-white/60'" x-text="timeLeft"></div>
                    <div class="text-white/30 text-xs" x-text="str.sec">sec</div>
                </div>
                <button @click="playInterval()"
                        class="w-20 h-20 rounded-2xl bg-gradient-to-br from-sky-400 to-blue-600 shadow-xl flex items-center justify-center hover:scale-105 active:scale-95 transition-transform">
                    <i data-lucide="volume-2" class="w-9 h-9 text-white"></i>
                </button>
                <div class="w-12"></div>
            </div>
            <p class="text-white/40 text-xs" x-text="str.whatInterval">What interval is this?</p>
            <div class="grid grid-cols-2 gap-2.5 w-full max-w-xs">
                <template x-for="(opt,i) in options" :key="i">
                    <button @click="answer(opt)" :disabled="answered"
                            :class="{
                                'correct': answered && opt===currentInterval.name,
                                'wrong': answered && selectedAnswer===opt && opt!==currentInterval.name,
                                'disabled-neutral': answered && opt!==currentInterval.name && selectedAnswer!==opt
                            }"
                            class="answer-btn text-white font-semibold text-sm py-3.5 rounded-xl" x-text="opt"></button>
                </template>
            </div>
        </div>

        {{-- Game Over --}}
        <div x-show="gameState==='gameover'" class="flex flex-col items-center gap-5 py-4">
            <div class="text-5xl">🎵</div>
            <div class="text-center">
                <div class="text-white/40 text-sm mb-1" x-text="str.finalScore">Final Score</div>
                <div class="text-5xl font-black text-white tabular-nums" x-text="score"></div>
            </div>
            <div class="flex items-center gap-5 text-sm text-white/50">
                <span x-text="correctCount+' '+str.correctLabel"></span>
                <span><span x-text="str.streak"></span>: <span x-text="maxStreak"></span></span>
                <span><span x-text="str.level"></span> <span x-text="level"></span></span>
            </div>
            <div x-show="isNewBest" class="px-4 py-2 rounded-full bg-yellow-400/20 border border-yellow-400/30 text-yellow-300 text-sm font-bold" x-text="str.newBest+' 🏆'"></div>
            <button @click="resetGame()"
                    class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-sky-400 to-blue-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform"
                    x-text="limitReached ? '{{ __('app.games.daily_limit_title') }} →' : str.playAgain">
            </button>
        </div>

    </div>

    @if($dailyLimit !== -1)
    <div class="px-6 pb-4 text-center text-white/20 text-xs">{{ __('app.games.plays_used', ['used' => $dailyPlaysUsed, 'limit' => $dailyLimit]) }}</div>
    @endif
</div>

<script>
function intervalBlitzGame() {
    const PERSONAL_BEST = {{ (int)$personalBest }};
    const SCORE_URL = @json(route('games.score', 'interval-blitz'));
    const STR = window.GAME_STRINGS || {};

    const ALL_INTERVALS = [
        {name:'Minor 2nd',semitones:1},{name:'Major 2nd',semitones:2},
        {name:'Minor 3rd',semitones:3},{name:'Major 3rd',semitones:4},
        {name:'Perfect 4th',semitones:5},{name:'Tritone',semitones:6},
        {name:'Perfect 5th',semitones:7},{name:'Minor 6th',semitones:8},
        {name:'Major 6th',semitones:9},{name:'Minor 7th',semitones:10},
        {name:'Major 7th',semitones:11},{name:'Octave',semitones:12},
    ];
    const NOTE_MAP = {'C':0,'C#':1,'D':2,'D#':3,'E':4,'F':5,'F#':6,'G':7,'G#':8,'A':9,'A#':10,'B':11};
    const MIDI_NAMES = ['C','C#','D','D#','E','F','F#','G','G#','A','A#','B'];
    function n2m(n){const m=n.match(/([A-G]#?)(\d)/);return NOTE_MAP[m[1]]+(parseInt(m[2])+1)*12;}
    function m2n(m){return MIDI_NAMES[m%12]+(Math.floor(m/12)-1);}

    return {
        str: Object.assign({ ready: 'Ready', playing: 'In progress', gameOver: 'Game Over', level: 'Level', streak: 'Streak', startGame: 'Start Game', playAgain: 'Play Again', finalScore: 'Final Score', personalBest: 'Personal best:', correctLabel: 'correct', newBest: 'New Personal Best!', whatInterval: 'What interval is this?', sec: 'sec', diffEasy: 'Easy (4)', diffMedium: 'Medium (8)', diffHard: 'Hard (12)', intervalDesc: 'Identify intervals before the timer runs out.' }, STR),
        limitReached: false,
        gameState:'idle', difficulty:'medium',
        lives:3, score:0, streak:0, maxStreak:0, correctCount:0, level:1,
        currentInterval:null, options:[], answered:false, selectedAnswer:null,
        timeLeft:10, timer:null, isNewBest:false, personalBest:PERSONAL_BEST,

        get intervalPool() {
            if(this.difficulty==='easy')   return ALL_INTERVALS.slice(0,4);
            if(this.difficulty==='medium') return ALL_INTERVALS.slice(0,8);
            return ALL_INTERVALS;
        },

        onInit() { lucide.createIcons(); },

        startGame() {
            this.lives=3;this.score=0;this.streak=0;this.maxStreak=0;
            this.correctCount=0;this.level=1;this.isNewBest=false;
            this.gameState='playing'; this.nextQuestion();
        },

        nextQuestion() {
            this.answered=false; this.selectedAnswer=null;
            const pool = this.intervalPool;
            this.currentInterval = pool[Math.floor(Math.random()*pool.length)];
            const dist = pool.filter(i=>i.name!==this.currentInterval.name)
                .sort(()=>Math.random()-0.5).slice(0,3).map(i=>i.name);
            this.options = [...dist, this.currentInterval.name].sort(()=>Math.random()-0.5);
            this.timeLeft = Math.max(5, 10-Math.floor(this.level/5));
            this.$nextTick(()=>{lucide.createIcons();this.playInterval();});
            clearInterval(this.timer);
            this.timer = setInterval(()=>{this.timeLeft--;if(this.timeLeft<=0)this.timeout();},1000);
        },

        playInterval() {
            if (!this.currentInterval||!window.HarmonivaAudio) return;
            const roots=['C4','D4','E4','F4','G4','A4'];
            const root=roots[Math.floor(Math.random()*roots.length)];
            HarmonivaAudio.playSequence([root, m2n(n2m(root)+this.currentInterval.semitones)], 600, 1);
        },

        answer(chosen) {
            if (this.answered||this.gameState!=='playing') return;
            clearInterval(this.timer);
            this.answered=true; this.selectedAnswer=chosen;
            if (chosen===this.currentInterval.name) {
                this.streak++;this.maxStreak=Math.max(this.maxStreak,this.streak);
                this.correctCount++;
                this.score+=100+(this.streak>1?(this.streak-1)*20:0)+(this.timeLeft*10);
                this.level++;
                if(this.streak%5===0)this.lives=Math.min(this.lives+1,5);
            } else {
                this.streak=0;this.lives--;
                if(this.lives<=0){setTimeout(()=>this.endGame(),800);return;}
            }
            setTimeout(()=>{if(this.gameState==='playing')this.nextQuestion();},900);
        },

        timeout() {
            clearInterval(this.timer);
            this.answered=true;this.selectedAnswer='__timeout__';
            this.streak=0;this.lives--;
            if(this.lives<=0){setTimeout(()=>this.endGame(),600);return;}
            setTimeout(()=>{if(this.gameState==='playing')this.nextQuestion();},900);
        },

        endGame() {
            clearInterval(this.timer);
            this.gameState='gameover';
            this.isNewBest=this.score>this.personalBest;
            if(this.isNewBest)this.personalBest=this.score;
            const csrf=document.querySelector('meta[name="csrf-token"]')?.content??'';
            fetch(SCORE_URL,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
                body:JSON.stringify({score:this.score,max_streak:this.maxStreak,level_reached:this.level,metadata:{correct:this.correctCount,difficulty:this.difficulty}})
            })
            .then(r=>r.json())
            .then(data=>{ if(data.can_play_again===false) this.limitReached=true; })
            .catch(()=>{});
            this.$nextTick(()=>lucide.createIcons());
        },

        resetGame(){
            clearInterval(this.timer);
            if(this.limitReached){window.location.reload();return;}
            this.gameState='idle';
        }
    };
}
</script>
@endif
