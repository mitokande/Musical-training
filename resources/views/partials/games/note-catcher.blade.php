{{-- Note Catcher game partial --}}

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

<div x-data="noteCatcherGame()" x-init="onInit()" class="game-surface rounded-2xl overflow-hidden"
     @keydown.arrow-left.window="moveLeft()" @keydown.arrow-right.window="moveRight()">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-violet-500/20 to-purple-600/20 border-b border-white/10 py-4 px-5 sm:p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('games.index') }}" class="w-10 h-10 rounded-xl bg-white border border-white/20 flex items-center justify-center text-red-600 font-bold hover:bg-white/90 transition-all flex-shrink-0">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-400 to-purple-600 flex items-center justify-center">
                    <i data-lucide="move-horizontal" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <div class="text-white font-bold text-sm">Note Catcher</div>
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
                <div class="text-center"><div class="text-white/40 text-xs" x-text="str.streak">Streak</div><div class="font-black text-xl tabular-nums" :class="streak>0?'text-violet-400':'text-white/30'" x-text="streak"></div></div>
            </div>
        </div>
    </div>

    {{-- Idle --}}
    <div x-show="gameState==='idle'" class="flex flex-col items-center justify-center gap-5 p-8" style="min-height:380px;">
        <div class="text-white/10 text-5xl font-black select-none">◄ ♩ ►</div>
        <p class="text-white/40 text-sm text-center max-w-xs" x-text="str.noteCatcherDesc"></p>

        <div class="flex gap-3">
            <button @click="mode='staff'"
                    :class="mode==='staff'?'border-violet-400 text-violet-300 bg-violet-400/10':'border-white/10 text-white/40'"
                    class="flex flex-col items-center gap-2 px-5 py-4 rounded-xl border transition-all">
                <svg viewBox="0 0 40 52" width="40" height="52" xmlns="http://www.w3.org/2000/svg">
                    <line x1="2" y1="10" x2="38" y2="10" stroke="currentColor" stroke-width="1.2" opacity="0.6"/>
                    <line x1="2" y1="17" x2="38" y2="17" stroke="currentColor" stroke-width="1.2" opacity="0.6"/>
                    <line x1="2" y1="24" x2="38" y2="24" stroke="currentColor" stroke-width="1.2" opacity="0.6"/>
                    <line x1="2" y1="31" x2="38" y2="31" stroke="currentColor" stroke-width="1.2" opacity="0.6"/>
                    <line x1="2" y1="38" x2="38" y2="38" stroke="currentColor" stroke-width="1.2" opacity="0.6"/>
                    <ellipse cx="20" cy="17" rx="6" ry="4.5" fill="currentColor"/>
                    <line x1="26" y1="14" x2="26" y2="4" stroke="currentColor" stroke-width="1.5"/>
                </svg>
                <span class="text-xs font-semibold">Staff</span>
            </button>
            <button @click="mode='letters'"
                    :class="mode==='letters'?'border-violet-400 text-violet-300 bg-violet-400/10':'border-white/10 text-white/40'"
                    class="flex flex-col items-center gap-2 px-5 py-4 rounded-xl border transition-all">
                <span class="text-3xl font-black leading-none">E</span>
                <span class="text-xs font-semibold">Letters</span>
            </button>
        </div>

        @if($personalBest > 0)
        <div class="text-white/30 text-sm flex items-center gap-1.5">
            <i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i>
            <span x-text="str.personalBest"></span> <span class="text-white font-bold ml-1">{{ number_format($personalBest) }}</span>
        </div>
        @endif
        <button @click="startGame()"
                class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-violet-400 to-purple-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform">
            <span class="flex items-center gap-2"><i data-lucide="play" class="w-4 h-4 fill-current"></i> <span x-text="str.startGame"></span></span>
        </button>
    </div>

    {{-- Playing --}}
    <div x-show="gameState==='playing' || gameState==='gameover'">

        {{-- Main area with arrow buttons on sides --}}
        <div class="flex items-stretch" style="background:#05080f;">

            {{-- Left arrow --}}
            <button @click="moveLeft()" :disabled="gameState!=='playing'"
                    class="flex items-center justify-center w-12 sm:w-16 flex-shrink-0 text-white/30 hover:text-white hover:bg-white/5 active:bg-white/10 transition-all disabled:opacity-20 disabled:cursor-default border-r border-white/5"
                    style="touch-action:manipulation;">
                <i data-lucide="chevron-left" class="w-8 h-8"></i>
            </button>

            {{-- Game field --}}
            <div class="relative flex-1 overflow-hidden select-none" style="height:340px;" x-ref="field">
                {{-- Column guides --}}
                <template x-for="(pct,i) in colPct" :key="i">
                    <div class="absolute top-0 bottom-0 w-px"
                         :class="i===currentCol?'bg-violet-500/30':'bg-white/4'"
                         :style="`left:calc(${pct}% - 0.5px)`"></div>
                </template>

                {{-- Active column highlight --}}
                <div class="absolute top-0 bottom-0 transition-all duration-100 bg-violet-500/5"
                     :style="`left:${currentCol*(100/7)}%;width:${100/7}%`"></div>

                {{-- Catch zone --}}
                <div class="absolute left-0 right-0 h-px bg-violet-500/30" style="bottom:0;"></div>

                {{-- Falling note (only one at a time) --}}
                <template x-if="currentNote">
                    <div class="absolute transition-[left] duration-100"
                         :style="`left:calc(${colPct[currentCol]}% - 22px); top:${noteY}px; width:44px; z-index:5;`">

                        {{-- Flash overlay --}}
                        <div x-show="flashResult==='correct'"
                             class="absolute inset-0 rounded-xl bg-green-400/40 z-10"></div>
                        <div x-show="flashResult==='wrong'"
                             class="absolute inset-0 rounded-xl bg-red-400/40 z-10"></div>

                        {{-- Staff mode --}}
                        <div x-show="mode==='staff'" x-html="noteSVG(currentNote)"
                             class="w-full flex justify-center opacity-90"></div>

                        {{-- Letters mode --}}
                        <div x-show="mode==='letters'"
                             class="w-11 h-11 rounded-xl bg-violet-500/20 border border-violet-400/40 flex items-center justify-center">
                            <span class="text-white font-black text-xl" x-text="noteLabel(currentNote)"></span>
                        </div>
                    </div>
                </template>

                {{-- Game Over overlay --}}
                <div x-show="gameState==='gameover'"
                     class="absolute inset-0 bg-black/70 flex flex-col items-center justify-center gap-4 z-20">
                    <div class="text-5xl">🎹</div>
                    <div class="text-center">
                        <div class="text-white/40 text-sm mb-1" x-text="str.finalScore">Final Score</div>
                        <div class="text-5xl font-black text-white tabular-nums" x-text="score"></div>
                    </div>
                    <div class="flex items-center gap-5 text-sm text-white/50">
                        <span x-text="str.level+' '+level"></span>
                        <span><span x-text="str.streak"></span>: <span x-text="maxStreak"></span></span>
                    </div>
                    <div x-show="isNewBest" class="px-4 py-2 rounded-full bg-yellow-400/20 border border-yellow-400/30 text-yellow-300 text-sm font-bold" x-text="str.newBest+' 🏆'"></div>
                    <button @click="resetGame()"
                            class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-violet-400 to-purple-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform"
                            x-text="limitReached ? '{{ __('app.games.daily_limit_title') }} →' : str.playAgain">
                    </button>
                </div>
            </div>

            {{-- Right arrow --}}
            <button @click="moveRight()" :disabled="gameState!=='playing'"
                    class="flex items-center justify-center w-12 sm:w-16 flex-shrink-0 text-white/30 hover:text-white hover:bg-white/5 active:bg-white/10 transition-all disabled:opacity-20 disabled:cursor-default border-l border-white/5"
                    style="touch-action:manipulation;">
                <i data-lucide="chevron-right" class="w-8 h-8"></i>
            </button>
        </div>

        {{-- Piano keyboard --}}
        <div class="relative" style="height:88px;background:#0a0d14;border-top:1px solid rgba(255,255,255,0.06);">
            <div class="flex h-full">
                <template x-for="(wk,i) in whiteKeys" :key="wk.note">
                    <div class="flex-1 relative flex flex-col items-center justify-end pb-1.5 border-r border-black/30"
                         :class="[
                             'rounded-b-lg border-b border-black/20',
                             i===currentCol && gameState==='playing' ? 'bg-violet-200' : 'bg-white/88'
                         ]"
                         style="min-width:0;">
                        <span class="text-gray-600 text-xs font-bold" x-text="wk.label"></span>
                    </div>
                </template>
            </div>
            <template x-for="bk in blackKeys" :key="bk.note">
                <div class="absolute top-0 rounded-b-md z-10 border border-white/5 bg-gray-900"
                     :style="'left:'+bk.leftPct+'%;top:0;width:8.5%;height:56%'"></div>
            </template>
        </div>
    </div>

    @if($dailyLimit !== -1)
    <div class="px-6 pb-4 text-center text-white/20 text-xs">{{ __('app.games.plays_used', ['used' => $dailyPlaysUsed, 'limit' => $dailyLimit]) }}</div>
    @endif
</div>

<script>
function noteCatcherGame() {
    const PERSONAL_BEST = {{ (int)$personalBest }};
    const SCORE_URL = @json(route('games.score', 'note-catcher'));
    const STR = window.GAME_STRINGS || {};

    const NOTES = ['C4','D4','E4','F4','G4','A4','B4'];
    const NOTE_LABELS = {'C4':'C','D4':'D','E4':'E','F4':'F','G4':'G','A4':'A','B4':'B'};
    const NOTE_COL    = {'C4':0,'D4':1,'E4':2,'F4':3,'G4':4,'A4':5,'B4':6};
    const COL_PCT     = [7.14, 21.43, 35.71, 50.0, 64.29, 78.57, 92.86];
    const STAFF_Y     = {'C4':45,'D4':41,'E4':38,'F4':34,'G4':31,'A4':27,'B4':24};

    return {
        str: Object.assign({ ready: 'Ready', playing: 'In progress', gameOver: 'Game Over', level: 'Level', streak: 'Streak', startGame: 'Start Game', playAgain: 'Play Again', finalScore: 'Final Score', personalBest: 'Personal best:', newBest: 'New Personal Best!', noteCatcherDesc: 'Steer the falling note left and right to land it on the correct piano key.' }, STR),
        limitReached: false,
        gameState: 'idle', mode: 'staff',
        lives: 3, score: 0, streak: 0, maxStreak: 0, level: 1, correctCount: 0,
        currentNote: null, currentCol: 3, noteY: -58,
        flashResult: null, isNewBest: false, personalBest: PERSONAL_BEST,
        loopId: null,

        colPct: COL_PCT,

        whiteKeys: [
            {note:'C4',label:'C'},{note:'D4',label:'D'},{note:'E4',label:'E'},
            {note:'F4',label:'F'},{note:'G4',label:'G'},{note:'A4',label:'A'},{note:'B4',label:'B'},
        ],
        blackKeys: [
            {note:'C#4',leftPct:10.5},{note:'D#4',leftPct:24.5},
            {note:'F#4',leftPct:53.0},{note:'G#4',leftPct:67.0},{note:'A#4',leftPct:81.0},
        ],

        onInit() { lucide.createIcons(); },

        get fallSpeed() { return 0.8 + (this.level - 1) * 0.2; },

        startGame() {
            this.lives=3; this.score=0; this.streak=0; this.maxStreak=0;
            this.level=1; this.correctCount=0; this.isNewBest=false;
            this.currentNote=null; this.flashResult=null;
            this.gameState='playing';
            this.spawnNote();
            this.startLoop();
        },

        startLoop() {
            const fieldH = 340;
            const tick = () => {
                if (this.gameState !== 'playing') return;
                if (this.currentNote) {
                    this.noteY += this.fallSpeed;
                    if (this.noteY > fieldH) {
                        this.handleLanding();
                    }
                }
                this.loopId = requestAnimationFrame(tick);
            };
            this.loopId = requestAnimationFrame(tick);
        },

        handleLanding() {
            const correct = NOTE_COL[this.currentNote] === this.currentCol;
            this.flashResult = correct ? 'correct' : 'wrong';
            if (window.HarmonivaAudio) HarmonivaAudio.playNote(this.currentNote, 0.6);

            if (correct) {
                this.streak++;
                this.maxStreak = Math.max(this.maxStreak, this.streak);
                this.correctCount++;
                this.score += 150 + this.streak * 20 + this.level * 15;
                if (this.correctCount % 6 === 0) this.level++;
            } else {
                this.streak = 0;
                this.lives--;
                if (this.lives <= 0) {
                    setTimeout(() => this.endGame(), 400);
                    return;
                }
            }
            setTimeout(() => {
                this.flashResult = null;
                this.spawnNote();
            }, 450);
        },

        spawnNote() {
            this.currentNote = NOTES[Math.floor(Math.random() * NOTES.length)];
            this.currentCol  = Math.floor(Math.random() * 7);
            this.noteY = -58;
        },

        moveLeft()  { if (this.gameState==='playing' && this.currentCol > 0) this.currentCol--; },
        moveRight() { if (this.gameState==='playing' && this.currentCol < 6) this.currentCol++; },

        endGame() {
            cancelAnimationFrame(this.loopId);
            this.currentNote = null;
            this.gameState = 'gameover';
            this.isNewBest = this.score > this.personalBest;
            if (this.isNewBest) this.personalBest = this.score;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            fetch(SCORE_URL, {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
                body: JSON.stringify({score:this.score, max_streak:this.maxStreak, level_reached:this.level, metadata:{correct:this.correctCount,mode:this.mode}})
            })
            .then(r=>r.json())
            .then(data=>{ if(data.can_play_again===false) this.limitReached=true; })
            .catch(()=>{});
            this.$nextTick(() => lucide.createIcons());
        },

        resetGame() {
            cancelAnimationFrame(this.loopId);
            if (this.limitReached) { window.location.reload(); return; }
            this.currentNote = null;
            this.flashResult = null;
            this.gameState = 'idle';
        },

        noteLabel(note) { return NOTE_LABELS[note] || note; },

        noteSVG(noteName) {
            if (!noteName) return '';
            const cy = STAFF_Y[noteName] || 24;
            const cx = 20;
            const needsLedger = noteName === 'C4';
            let s = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 56" width="40" height="56">`;
            for (let i=0;i<5;i++) {
                const y = 10 + i*7;
                s += `<line x1="2" y1="${y}" x2="38" y2="${y}" stroke="rgba(255,255,255,0.55)" stroke-width="1.1"/>`;
            }
            if (needsLedger) {
                s += `<line x1="12" y1="${cy}" x2="28" y2="${cy}" stroke="rgba(255,255,255,0.7)" stroke-width="1.2"/>`;
            }
            s += `<ellipse cx="${cx}" cy="${cy}" rx="6.5" ry="4.8" fill="white"/>`;
            const stemEnd = Math.max(6, cy - 26);
            s += `<line x1="${cx+6}" y1="${cy-4}" x2="${cx+6}" y2="${stemEnd}" stroke="white" stroke-width="1.6"/>`;
            s += `</svg>`;
            return s;
        },
    };
}
</script>
@endif
