{{-- Note Catcher game partial — 5-level edition --}}

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
            <a href="{{ route('checkout.show') }}"
               class="inline-block px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white font-semibold text-sm">
                {{ __('app.games.upgrade_premium') }}
            </a>
            @endif
        @endguest
    </div>
@else

<style>
    @keyframes ncLevelIn {
        0%   { opacity:0; transform:scale(0.7); }
        60%  { opacity:1; transform:scale(1.06); }
        100% { opacity:1; transform:scale(1); }
    }
    .nc-level-in { animation: ncLevelIn 0.4s ease-out forwards; }
</style>

<div x-data="noteCatcherGame()" x-init="onInit()" class="game-surface rounded-2xl overflow-hidden sm:max-w-[85%] sm:mx-auto lg:max-w-[80%] lg:mr-0"
     @keydown.arrow-left.window="moveLeft()" @keydown.arrow-right.window="moveRight()">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-violet-500/20 to-purple-600/20 border-b border-white/10 py-3 px-4 sm:p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('games.index') }}" class="w-10 h-10 rounded-xl bg-white border border-white/20 flex items-center justify-center text-red-600 font-bold hover:bg-white/90 transition-all flex-shrink-0">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div class="hidden sm:flex w-10 h-10 rounded-xl bg-gradient-to-br from-violet-400 to-purple-600 items-center justify-center flex-shrink-0">
                    <i data-lucide="move-horizontal" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <div class="text-white font-bold text-sm">{{ __('app.games.note_catcher.title') }}</div>
                    <div class="text-white/40 text-xs"
                         x-text="gameState==='idle'?str.ready:(gameState==='gameover'?(allLevelsComplete?str.allComplete:str.gameOver):str.level+' '+currentLevel)"></div>
                </div>
            </div>

            {{-- Right cluster: hearts (lives) + Score correct/total --}}
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="flex items-center gap-0.5">
                    <template x-for="i in 3" :key="i">
                        <span class="text-lg sm:text-2xl" :class="i<=lives?'text-red-500':'opacity-40 text-rose-800'">❤</span>
                    </template>
                </div>
                <div class="text-center">
                    <div class="text-white/40 text-[10px] sm:text-xs">{{ __('app.games.note_catcher.score') }}</div>
                    <div class="font-black text-base sm:text-xl tabular-nums leading-none">
                        <span class="text-emerald-400" x-text="correctInLevel"></span><span
                            class="text-white/35">/</span><span class="text-white" x-text="resolvedInLevel"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Idle --}}
    <div x-show="gameState==='idle'" class="flex flex-col items-center justify-center gap-5 p-8" style="min-height:380px;">
        <div class="text-white/10 text-5xl font-black select-none">◄ ♩ ►</div>
        <p class="text-white/40 text-sm text-center max-w-xs" x-text="str.noteCatcherDesc"></p>

        {{-- Level map --}}
        <div class="flex gap-2 justify-center flex-wrap">
            <template x-for="lvl in [1,2,3,4,5]" :key="lvl">
                <div class="flex flex-col items-center px-3 py-2 rounded-xl border"
                     :class="lvl===1 ? 'border-violet-400/60 bg-violet-400/10' : 'border-white/8 opacity-40'">
                    <span class="text-xs font-black text-white/70" x-text="str.level+' '+lvl"></span>
                    <span class="text-[9px] text-white/35 mt-0.5" x-text="NC_LEVEL_SHORT[lvl-1]"></span>
                </div>
            </template>
        </div>
        <p class="text-white/25 text-xs text-center max-w-[260px]" x-text="str.levelHint"></p>

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
                <span class="text-xs font-semibold">{{ __('app.games.note_catcher.mode_staff') }}</span>
            </button>
            <button @click="mode='letters'"
                    :class="mode==='letters'?'border-violet-400 text-violet-300 bg-violet-400/10':'border-white/10 text-white/40'"
                    class="flex flex-col items-center gap-2 px-5 py-4 rounded-xl border transition-all">
                <span class="text-3xl font-black leading-none">E</span>
                <span class="text-xs font-semibold">{{ __('app.games.note_catcher.mode_letters') }}</span>
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

    {{-- Playing / Level end / Game over --}}
    <div x-show="gameState==='playing' || gameState==='levelend' || gameState==='gameover'">

        {{-- Main area --}}
        <div style="background:#05080f;">

            {{-- Game field --}}
            <div class="relative overflow-hidden select-none h-[340px] lg:h-[400px]" x-ref="field">
                {{-- Column guides (7 white-key columns) --}}
                <template x-for="(pct,i) in colPct" :key="i">
                    <div class="absolute top-0 bottom-0 w-px bg-white/[0.04]"
                         :style="`left:calc(${pct}% - 0.5px)`"></div>
                </template>

                {{-- Active lane highlight --}}
                <div class="absolute top-0 bottom-0 transition-[left] duration-100 bg-violet-500/10"
                     x-show="gameState==='playing'"
                     :style="`left:calc(${LANE_X[currentLane]}% - 18px);width:36px;`"></div>

                {{-- Catch zone --}}
                <div class="absolute left-0 right-0 h-px bg-violet-500/30" style="bottom:0;"></div>

                {{-- Clef / key signature badge (L4 & L5) --}}
                <div x-show="currentLevel>=4 && gameState==='playing' && currentNote"
                     class="absolute top-2 left-2 flex items-center gap-2 px-2.5 py-1 rounded-lg bg-black/45 border border-white/10 z-10">
                    <span class="text-violet-300 font-bold text-xs" x-text="currentClefLabel"></span>
                    <span x-show="currentLevel===5" class="text-amber-300 font-semibold text-xs" x-text="currentKeyLabel"></span>
                </div>

                {{-- Wrong-answer reveal --}}
                <div x-show="revealName"
                     class="absolute left-1/2 -translate-x-1/2 top-3 px-3 py-1.5 rounded-lg bg-red-500/20 border border-red-400/40 text-red-200 text-sm font-bold z-20 whitespace-nowrap"
                     x-text="str.correctWas+': '+window.HarmonivaNotation.toDisplaySymbol(revealName)"></div>

                {{-- Falling note (only one at a time) --}}
                <template x-if="currentNote">
                    <div class="absolute transition-[left] duration-100"
                         :style="`left:calc(${LANE_X[currentLane]}% - 46px); top:${noteY}px; width:92px; z-index:5;`">

                        {{-- Flash overlay --}}
                        <div x-show="flashResult==='correct'" class="absolute inset-0 rounded-xl bg-green-400/40 z-10"></div>
                        <div x-show="flashResult==='wrong'"   class="absolute inset-0 rounded-xl bg-red-400/40 z-10"></div>

                        {{-- Staff mode --}}
                        <div x-show="mode==='staff'" x-html="noteSVG(currentNote, currentLevel)"
                             class="w-full flex justify-center opacity-95"></div>

                        {{-- Letters mode --}}
                        <div x-show="mode==='letters'" class="w-full flex justify-center">
                            <div class="w-12 h-12 rounded-xl bg-violet-500/20 border border-violet-400/40 flex items-center justify-center">
                                <span class="text-white font-black text-xl" x-text="window.HarmonivaNotation.toDisplaySymbol(currentNote.display)"></span>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Level-end overlay (passed, more levels remain) --}}
                <div x-show="gameState==='levelend'"
                     class="absolute inset-0 bg-black/85 flex flex-col items-center justify-center gap-3 z-30 px-6">
                    <div class="nc-level-in flex flex-col items-center gap-3">
                        <div class="text-4xl">🎉</div>
                        <div class="text-violet-300 font-black text-xl" x-text="str.level+' '+currentLevel+' '+str.complete+'!'"></div>
                        <div class="grid grid-cols-2 gap-x-8 gap-y-1.5 text-sm">
                            <span class="text-white/50" x-text="str.correctAnswers"></span>
                            <span class="text-white font-bold text-right" x-text="lastResult.correct+' / '+NOTES_PER_LEVEL"></span>
                            <span class="text-white/50" x-text="str.accuracy"></span>
                            <span class="text-emerald-400 font-bold text-right" x-text="lastResult.accuracy+'%'"></span>
                            <span class="text-white/50" x-text="str.bestStreak"></span>
                            <span class="text-amber-300 font-bold text-right" x-text="lastResult.bestStreak"></span>
                        </div>
                        <div class="text-emerald-400 text-sm font-bold flex items-center gap-1.5">
                            <i data-lucide="unlock" class="w-4 h-4"></i>
                            <span x-text="str.unlocked+' '+str.level+' '+(currentLevel+1)"></span>
                        </div>
                        <button @click="nextLevel()"
                                class="mt-1 px-7 py-3 rounded-xl bg-gradient-to-r from-violet-400 to-purple-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform"
                                x-text="str.nextLevel"></button>
                    </div>
                </div>

                {{-- Game over overlay (failed level, lives out, or all complete) --}}
                <div x-show="gameState==='gameover'"
                     class="absolute inset-0 bg-black/80 flex flex-col items-center justify-center gap-3 z-30 px-6">
                    <div :class="allLevelsComplete ? 'text-5xl' : 'text-4xl'"
                         x-text="allLevelsComplete ? '🏆' : (lastResult.passed ? '🎹' : '😔')"></div>
                    <div class="text-center">
                        <div class="text-white/45 text-sm mb-1"
                             x-text="allLevelsComplete ? str.allComplete : (lastResult.passed ? str.finalScore : str.levelFailed)"></div>
                        <div class="text-4xl font-black text-white tabular-nums" x-text="points"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-x-8 gap-y-1.5 text-sm">
                        <span class="text-white/50" x-text="str.correctAnswers"></span>
                        <span class="text-white font-bold text-right" x-text="lastResult.correct+' / '+lastResult.total"></span>
                        <span class="text-white/50" x-text="str.accuracy"></span>
                        <span class="text-emerald-400 font-bold text-right" x-text="lastResult.accuracy+'%'"></span>
                        <span class="text-white/50" x-text="str.bestStreak"></span>
                        <span class="text-amber-300 font-bold text-right" x-text="lastResult.bestStreak"></span>
                        <span class="text-white/50" x-text="str.level"></span>
                        <span class="text-violet-300 font-bold text-right" x-text="maxLevelReached"></span>
                    </div>
                    <div x-show="!allLevelsComplete && !lastResult.passed" class="text-white/30 text-xs"
                         x-text="str.needCorrect+' '+PASS_THRESHOLD+' / '+NOTES_PER_LEVEL"></div>
                    <div x-show="isNewBest" class="px-4 py-2 rounded-full bg-yellow-400/20 border border-yellow-400/30 text-yellow-300 text-sm font-bold" x-text="str.newBest+' 🏆'"></div>
                    <button @click="resetGame()"
                            class="px-8 py-3 rounded-xl bg-gradient-to-r from-violet-400 to-purple-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform"
                            x-text="limitReached ? '{{ __('app.games.daily_limit_title') }} →' : str.playAgain">
                    </button>
                </div>
            </div>

        </div>

        {{-- Piano keyboard --}}
        <div class="relative" style="height:98px;background:#0a0d14;border-top:1px solid rgba(255,255,255,0.06);">
            <div class="flex h-full">
                <template x-for="(wk,i) in whiteKeys" :key="wk.lane">
                    <div class="flex-1 relative flex flex-col items-center justify-end pb-1.5 border-r border-black/30 rounded-b-lg border-b border-black/20 transition-colors"
                         :class="currentLane===wk.lane && gameState==='playing' ? 'bg-violet-200' : 'bg-white'"
                         :style="activeLanes.includes(wk.lane) ? '' : 'opacity:0.35;'"
                         style="min-width:0;">
                        <span class="text-gray-600 text-xs font-bold pointer-events-none"
                              x-show="mode==='staff'"
                              :style="`opacity:${labelOpacity}`"
                              x-text="wk.label"></span>
                    </div>
                </template>
            </div>
            <template x-for="bk in blackKeys" :key="bk.lane">
                <div class="absolute top-0 rounded-b-md z-10 border border-white/5 flex items-end justify-center pb-1 transition-colors"
                     :class="currentLane===bk.lane && gameState==='playing' ? 'bg-violet-500' : 'bg-gray-900'"
                     :style="'left:'+bk.leftPct+'%;top:0;width:8.5%;height:56%;'+(activeLanes.includes(bk.lane)?'':'opacity:0.3;')">
                    <span class="text-white/60 text-[7px] font-bold leading-none pointer-events-none text-center"
                          x-show="mode==='staff' && currentLevel>=2"
                          :style="`opacity:${labelOpacity}`"
                          x-text="bk.label"></span>
                </div>
            </template>
        </div>

        {{-- Navigation buttons below piano --}}
        <div class="flex" style="gap:5px;padding:5px;background:#0a0d14;">
            <button @click="moveLeft()" :disabled="gameState!=='playing'"
                    class="flex-1 flex items-center justify-center text-white hover:brightness-110 active:scale-95 transition-all disabled:opacity-20 disabled:cursor-default"
                    style="min-height:65px;touch-action:manipulation;border-radius:10px;background:linear-gradient(to right,rgba(139,92,246,0.32),rgba(109,40,217,0.24));border:1px solid rgba(139,92,246,0.30);box-shadow:0 2px 8px rgba(139,92,246,0.15),inset 0 1px 0 rgba(255,255,255,0.08);">
                <i data-lucide="chevron-left" class="w-9 h-9" style="stroke-width:2.5;"></i>
            </button>
            <button @click="moveRight()" :disabled="gameState!=='playing'"
                    class="flex-1 flex items-center justify-center text-white hover:brightness-110 active:scale-95 transition-all disabled:opacity-20 disabled:cursor-default"
                    style="min-height:65px;touch-action:manipulation;border-radius:10px;background:linear-gradient(to right,rgba(109,40,217,0.24),rgba(139,92,246,0.32));border:1px solid rgba(139,92,246,0.30);box-shadow:0 2px 8px rgba(139,92,246,0.15),inset 0 1px 0 rgba(255,255,255,0.08);">
                <i data-lucide="chevron-right" class="w-9 h-9" style="stroke-width:2.5;"></i>
            </button>
        </div>
    </div>

</div>

@if($perTypeLimit !== null && $perTypeLimit !== -1)
<div class="pt-2 pb-1 text-center text-white/25 text-xs">
    {{ __('app.games.plays_used', ['used' => $dailyPlaysUsed, 'limit' => $perTypeLimit]) }}
    @if($totalLimit !== null && $totalLimit !== -1)
     · {{ __('app.games.plays_used_total', ['used' => $totalPlaysUsed, 'total' => $totalLimit]) }}
    @endif
</div>
@endif

<script>
function noteCatcherGame() {
    const PERSONAL_BEST = {{ (int)$personalBest }};
    const SCORE_URL = @json($scoreUrl);
    const STR = window.GAME_STRINGS || {};

    // ── Geometry ───────────────────────────────────────────────────────────
    const FIELD_H = 340;
    const LAND_Y  = 290;            // note "lands" when its container top reaches this

    // White-key column centres (7 equal columns)
    const COL_PCT = [7.143, 21.429, 35.714, 50.0, 64.286, 78.571, 92.857];

    // 12 chromatic lane centres (white + black) keyed by pitch-class index 0..11
    //  0 C  1 C# 2 D  3 D# 4 E  5 F  6 F# 7 G  8 G# 9 A 10 A# 11 B
    const LANE_X = [7.143, 14.75, 21.429, 28.75, 35.714, 50.0, 57.25, 64.286, 71.25, 78.571, 85.25, 92.857];

    const LETTER_IDX = { C:0, D:1, E:2, F:3, G:4, A:5, B:6 };
    const PC_TO_LANE = {
        C:0, 'C#':1, Db:1, D:2, 'D#':3, Eb:3, E:4, F:5, 'F#':6, Gb:6,
        G:7, 'G#':8, Ab:8, A:9, 'A#':10, Bb:10, B:11,
    };
    const WHITE_LANE = [0, 2, 4, 5, 7, 9, 11];

    const KEY_SIGS = {
        C:  { name:'C / Am',  sharps:[],          flats:[] },
        G:  { name:'G / Em',  sharps:['F'],       flats:[] },
        F:  { name:'F / Dm',  sharps:[],          flats:['B'] },
        D:  { name:'D / Bm',  sharps:['F','C'],   flats:[] },
        Bb: { name:'B♭ / Gm', sharps:[],          flats:['B','E'] },
    };

    // Note pools (written spellings) per range
    const L2_POOL = ['C4','D4','E4','F4','G4','A4','B4',
                     'C#4','Db4','D#4','Eb4','F#4','Gb4','G#4','Ab4','A#4','Bb4'];
    const TREBLE_POOL = ['G3','A3','B3','C4','D4','E4','F4','G4','A4','B4','C5','D5',
                         'G#3','Ab3','A#3','Bb3','C#4','Db4','D#4','Eb4','F#4','Gb4',
                         'G#4','Ab4','A#4','Bb4','C#5','Db5'];
    const BASS_POOL = ['G2','A2','B2','C3','D3','E3','F3','G3','A3','B3','C4',
                       'G#2','Ab2','A#2','Bb2','C#3','Db3','D#3','Eb3','F#3','Gb3',
                       'G#3','Ab3','A#3','Bb3'];

    // ── Geometry helpers ───────────────────────────────────────────────────
    function diatonicY(written, clef) {
        const m = written.match(/^([A-G])([#b]?)(-?\d)$/);
        const ds = parseInt(m[3],10) * 7 + LETTER_IDX[m[1]];
        // spacing=11, bottom staff line y=72; treble: E4(ds=30)=72, bass: G2(ds=18)=72
        return Math.round(clef === 'bass' ? 72 - (ds - 18) * 5.5 : 72 - (ds - 30) * 5.5);
    }
    function ledgersFor(staffY) {
        const out = [];
        for (let ly = 17; ly >= staffY - 1; ly -= 11) out.push(ly);  // above (spacing=11)
        for (let ly = 83; ly <= staffY + 1; ly += 11) out.push(ly);  // below
        return out;
    }
    function makeNote(written, clef, keySig) {
        const m = written.match(/^([A-G])([#b]?)(-?\d)$/);
        const letter = m[1], accW = m[2] || null, oct = parseInt(m[3],10);
        let pc, accDraw;
        if (keySig) {
            const ks = KEY_SIGS[keySig];
            if      (ks.sharps.includes(letter)) pc = letter + '#';
            else if (ks.flats.includes(letter))  pc = letter + 'b';
            else                                 pc = letter;
            accDraw = null;                 // accidental implied by the key signature
        } else {
            pc = letter + (accW || '');
            accDraw = accW;
        }
        const staffY = diatonicY(written, clef);
        return {
            written, letter, oct, accDraw, pc, display: pc,
            audio: pc + oct, lane: PC_TO_LANE[pc],
            clef, keySig: keySig || null,
            staffY, ledgers: ledgersFor(staffY),
        };
    }

    // ── SVG builders ───────────────────────────────────────────────────────
    function drawKeySig(keySig, clef) {
        const ks = KEY_SIGS[keySig]; if (!ks) return '';
        const repT = { F:'F5', C:'C5', G:'G5', D:'D5', A:'A4', E:'E5', B:'B4' };
        const repB = { F:'F3', C:'C3', G:'G3', D:'D3', A:'A3', E:'E3', B:'B2' };
        const rep = clef === 'bass' ? repB : repT;
        let s = '', i = 0;
        ks.sharps.forEach(l => { const y = diatonicY(rep[l], clef); s += `<text x="${26+i*7}" y="${y+4}" font-size="12" fill="rgba(255,255,255,0.92)" font-family="serif" font-weight="bold">♯</text>`; i++; });
        ks.flats.forEach(l  => { const y = diatonicY(rep[l], clef); s += `<text x="${26+i*7}" y="${y+4}" font-size="12" fill="rgba(255,255,255,0.92)" font-family="serif">♭</text>`; i++; });
        return s;
    }
    function noteSVG(note, level = 1) {
        if (!note) return '';
        // 108×115'den %15 daralttık → 92×115; desktop clef daha büyük
        const desktop  = window.innerWidth >= 1024;
        // desktop: clef üst kenar staff top (y=28) - 5px = y=23'te olsun
        const clefFS   = desktop ? 75 : 42;
        const clefY    = desktop ? 75 : 78;
        const bassFS   = desktop ? 53 : 30;
        const bassY    = desktop ? 56 : 62;
        const cx = 70;  // 5px sola kaydırıldı
        let s = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 92 115" width="92" height="115">`;
        for (const y of [28,39,50,61,72]) s += `<line x1="5" y1="${y}" x2="87" y2="${y}" stroke="rgba(255,255,255,0.5)" stroke-width="1.1"/>`;
        if (note.clef === 'bass') s += `<text x="4" y="${bassY}" font-size="${bassFS}" fill="rgba(255,255,255,0.9)" font-family="serif">𝄢</text>`;
        else                      s += `<text x="4" y="${clefY}" font-size="${clefFS}" fill="rgba(255,255,255,0.9)" font-family="serif">𝄞</text>`;
        if (note.keySig) s += drawKeySig(note.keySig, note.clef);
        for (const ly of note.ledgers) s += `<line x1="${cx-12}" y1="${ly}" x2="${cx+12}" y2="${ly}" stroke="rgba(255,255,255,0.7)" stroke-width="1.1"/>`;
        const accSize  = (level === 2) ? 19 : 14;
        const accColor = (level === 2) ? '#FFDD00' : '#fff';
        const accOff   = (level === 2) ? 24 : 20;
        if (note.accDraw === '#') s += `<text x="${cx-accOff}" y="${note.staffY+5}" font-size="${accSize}" fill="${accColor}" font-family="serif" font-weight="bold">♯</text>`;
        if (note.accDraw === 'b') s += `<text x="${cx-accOff+1}" y="${note.staffY+6}" font-size="${accSize}" fill="${accColor}" font-family="serif">♭</text>`;
        s += `<ellipse cx="${cx}" cy="${note.staffY}" rx="7" ry="5" fill="#fff"/>`;
        if (note.staffY >= 50) { const st = Math.max(8, note.staffY-32);  s += `<line x1="${cx+7}" y1="${note.staffY-3}" x2="${cx+7}" y2="${st}" stroke="#fff" stroke-width="1.8"/>`; }
        else                   { const sb = Math.min(110, note.staffY+32); s += `<line x1="${cx-7}" y1="${note.staffY+3}" x2="${cx-7}" y2="${sb}" stroke="#fff" stroke-width="1.8"/>`; }
        return s + `</svg>`;
    }

    // Short error buzz via WebAudio (kept separate from the note sampler)
    let _ctx = null;
    function errorBuzz() {
        try {
            const AC = window.AudioContext || window.webkitAudioContext; if (!AC) return;
            _ctx = _ctx || new AC();
            const t = _ctx.currentTime;
            const osc = _ctx.createOscillator(), g = _ctx.createGain();
            osc.type = 'square'; osc.frequency.setValueAtTime(200, t);
            osc.frequency.exponentialRampToValueAtTime(110, t + 0.18);
            g.gain.setValueAtTime(0.0001, t);
            g.gain.exponentialRampToValueAtTime(0.18, t + 0.02);
            g.gain.exponentialRampToValueAtTime(0.0001, t + 0.22);
            osc.connect(g); g.connect(_ctx.destination);
            osc.start(t); osc.stop(t + 0.24);
        } catch (e) {}
    }

    const NC_LEVEL_SHORT = [
        @json(__('app.games.note_catcher.level_short_naturals')),
        '♯ & ♭',
        @json(__('app.games.note_catcher.level_short_wide_range')),
        @json(__('app.games.note_catcher.level_short_clef_switch')),
        @json(__('app.games.note_catcher.level_short_key_sig')),
    ];

    return {
        str: Object.assign({
            ready: @json(__('app.games.status_ready')),
            playing: @json(__('app.games.status_playing')),
            gameOver: @json(__('app.games.status_game_over')),
            level: @json(__('app.games.status_level')),
            streak: @json(__('app.games.note_catcher.streak')),
            startGame: @json(__('app.games.start_game')),
            playAgain: @json(__('app.games.play_again')),
            finalScore: @json(__('app.games.final_score')),
            personalBest: @json(__('app.games.personal_best')),
            newBest: @json(__('app.games.new_personal_best')),
            noteCatcherDesc: @json(__('app.games.note_catcher.description')),
            levelHint: @json(__('app.games.note_catcher.level_hint')),
            complete: @json(__('app.games.note_catcher.complete')),
            correctAnswers: @json(__('app.games.note_catcher.correct_answers')),
            accuracy: @json(__('app.games.note_catcher.accuracy')),
            bestStreak: @json(__('app.games.note_catcher.best_streak')),
            unlocked: @json(__('app.games.note_catcher.unlocked')),
            nextLevel: @json(__('app.games.note_catcher.next_level')),
            levelFailed: @json(__('app.games.note_catcher.level_failed')),
            allComplete: @json(__('app.games.note_catcher.all_complete')),
            needCorrect: @json(__('app.games.note_catcher.need_correct')),
            correctWas: @json(__('app.games.note_catcher.correct_was')),
            treble: @json(__('app.exercises.treble_short')),
            bass: @json(__('app.exercises.bass_short')),
        }, STR),

        NC_LEVEL_SHORT, LANE_X, colPct: COL_PCT,
        NOTES_PER_LEVEL: 20, PASS_THRESHOLD: 15,

        limitReached: false,
        gameState: 'idle', mode: 'staff',
        allLevelsComplete: false,

        // run-wide
        lives: 3, consecErrors: 0,
        points: 0, streak: 0, maxStreak: 0,
        currentLevel: 1, maxLevelReached: 1, correctCount: 0,

        // per-level
        notesInLevel: 0, resolvedInLevel: 0, correctInLevel: 0, levelBestStreak: 0,
        lastResult: { correct:0, total:0, accuracy:0, bestStreak:0, passed:false },

        // falling note
        currentNote: null, currentLane: 5, noteY: -90, landed: false,
        flashResult: null, revealName: null,
        recentKeys: [],

        isNewBest: false, personalBest: PERSONAL_BEST, loopId: null,

        whiteKeys: [
            {lane:0,label:'C'},{lane:2,label:'D'},{lane:4,label:'E'},
            {lane:5,label:'F'},{lane:7,label:'G'},{lane:9,label:'A'},{lane:11,label:'B'},
        ],
        blackKeys: [
            {lane:1, label:'C♯',leftPct:10.5},{lane:3, label:'D♯',leftPct:24.5},
            {lane:6, label:'F♯',leftPct:53.0},{lane:8, label:'G♯',leftPct:67.0},{lane:10,label:'A♯',leftPct:81.0},
        ],

        onInit() { lucide.createIcons(); },

        // ── Computed ──────────────────────────────────────────────────────
        get block()      { return Math.min(3, Math.floor(Math.max(0, this.notesInLevel - 1) / 5)); },
        get speedMul()   { return [1.0, 1.15, 1.2, 1.25][this.block]; },
        get fallSpeed()  { return [2.1, 2.1, 2.45, 2.45, 2.87][this.currentLevel-1] * this.speedMul; },
        get activeLanes() {
            if (this.currentLevel === 1) return this.block >= 1 ? WHITE_LANE : [0,2,4,5,7];
            return [0,1,2,3,4,5,6,7,8,9,10,11];
        },
        get labelOpacity() {
            if (this.mode !== 'staff') return 0;
            // fade across quarters of the level: 100% → 75% → 50% → 25%
            const q = Math.min(3, Math.floor(Math.max(0, this.notesInLevel - 1) / (this.NOTES_PER_LEVEL / 4)));
            let o = [1, 0.75, 0.5, 0.25][q];
            if (this.currentLevel >= 4 && q >= 3) o = 0;   // hidden in the last quarter on L4/L5
            return o;
        },
        get currentClefLabel() {
            if (!this.currentNote) return '';
            return this.currentNote.clef === 'bass' ? this.str.bass : this.str.treble;
        },
        get currentKeyLabel() {
            if (!this.currentNote || !this.currentNote.keySig) return '';
            return (KEY_SIGS[this.currentNote.keySig] || {}).name || '';
        },

        noteSVG,

        // ── Lifecycle ─────────────────────────────────────────────────────
        startGame() {
            this.lives = 3; this.consecErrors = 0;
            this.points = 0; this.streak = 0; this.maxStreak = 0;
            this.currentLevel = 1; this.maxLevelReached = 1; this.correctCount = 0;
            this.allLevelsComplete = false; this.isNewBest = false;
            this._resetLevel();
            this.gameState = 'playing';
            this.spawnNote();
            this.startLoop();
        },

        _resetLevel() {
            this.notesInLevel = 0; this.resolvedInLevel = 0; this.correctInLevel = 0;
            this.levelBestStreak = 0; this.streak = 0; this.consecErrors = 0;
            this.recentKeys = []; this.flashResult = null; this.revealName = null;
            this.landed = false;
        },

        startLoop() {
            const fieldH = (this.$refs.field && this.$refs.field.offsetHeight) || FIELD_H;
            const landY  = fieldH - 75;
            const tick = () => {
                if (this.gameState !== 'playing') return;
                if (this.currentNote && !this.landed) {
                    this.noteY += this.fallSpeed;
                    if (this.noteY >= landY) { this.landed = true; this.handleLanding(); }
                }
                this.loopId = requestAnimationFrame(tick);
            };
            this.loopId = requestAnimationFrame(tick);
        },

        // ── Note generation ───────────────────────────────────────────────
        _pick(pool) {
            const last = this.recentKeys.slice(-2);
            let p = pool.filter(k => !last.includes(k));
            if (!p.length) p = pool;
            const k = p[Math.floor(Math.random() * p.length)];
            this.recentKeys.push(k);
            if (this.recentKeys.length > 4) this.recentKeys.shift();
            return k;
        },

        _centerSpawnLane(lanes) {
            const n = lanes.length;
            const mid = (n - 1) / 2;
            const weights = lanes.map((_, i) => {
                const d = Math.abs(i - mid) / (n / 2);
                return Math.exp(-d * d * 2.5);  // Gaussian; kenar ~12× daha nadir
            });
            const total = weights.reduce((a, b) => a + b, 0);
            let r = Math.random() * total;
            for (let i = 0; i < n; i++) {
                r -= weights[i];
                if (r <= 0) return lanes[i];
            }
            return lanes[n - 1];
        },

        spawnNote() {
            this.notesInLevel++;
            const lvl = this.currentLevel, b = this.block;
            let note;
            if (lvl === 1) {
                const pool = b >= 1 ? ['C4','D4','E4','F4','G4','A4','B4'] : ['C4','D4','E4','F4','G4'];
                note = makeNote(this._pick(pool), 'treble', null);
            } else if (lvl === 2) {
                note = makeNote(this._pick(L2_POOL), 'treble', null);
            } else if (lvl === 3) {
                note = makeNote(this._pick(TREBLE_POOL), 'treble', null);
            } else if (lvl === 4) {
                const clef = ['treble','bass','treble','bass'][b];
                note = makeNote(this._pick(clef === 'treble' ? TREBLE_POOL : BASS_POOL), clef, null);
            } else {
                const key  = ['G','D','F','Bb'][b];
                const clef = (b % 2 === 0) ? 'treble' : 'bass';
                const oct  = clef === 'treble' ? 4 : 3;
                const letter = this._pick(['A','B','C','D','E','F','G']).slice(0,1);
                note = makeNote(letter + oct, clef, key);
            }

            this.currentNote = note;
            const lanes = this.activeLanes;
            this.currentLane = this._centerSpawnLane(lanes);
            this.noteY = -90;
            this.landed = false;

            if (window.HarmonivaAudio) HarmonivaAudio.playNote(note.audio, 0.55);
        },

        handleLanding() {
            const note = this.currentNote;
            const correct = note.lane === this.currentLane;
            this.resolvedInLevel++;

            if (correct) {
                this.flashResult = 'correct';
                this.streak++;
                this.maxStreak = Math.max(this.maxStreak, this.streak);
                this.levelBestStreak = Math.max(this.levelBestStreak, this.streak);
                this.correctCount++; this.correctInLevel++;
                this.consecErrors = 0;
                this.points += 120 + this.streak * 15 + this.block * 20 + this.currentLevel * 10;
                if (window.HarmonivaAudio) HarmonivaAudio.playNote(note.audio, 0.3);
            } else {
                this.flashResult = 'wrong';
                this.streak = 0;
                this.consecErrors++;
                this.revealName = note.display;
                errorBuzz();
                if (this.consecErrors >= 2) { this.lives--; this.consecErrors = 0; }
            }

            // Out of lives → game over immediately
            if (this.lives <= 0) {
                setTimeout(() => this._finishLevel(false), 550);
                return;
            }
            // Level finished (all 40 notes resolved)
            if (this.notesInLevel >= this.NOTES_PER_LEVEL) {
                setTimeout(() => this._finishLevel(this.correctInLevel >= this.PASS_THRESHOLD), 650);
                return;
            }
            // Otherwise spawn the next note
            setTimeout(() => {
                this.flashResult = null; this.revealName = null;
                if (this.gameState === 'playing') this.spawnNote();
            }, correct ? 380 : 640);
        },

        moveLeft() {
            if (this.gameState !== 'playing') return;
            const a = this.activeLanes; let i = a.indexOf(this.currentLane);
            if (i < 0) i = 0; if (i > 0) this.currentLane = a[i-1];
        },
        moveRight() {
            if (this.gameState !== 'playing') return;
            const a = this.activeLanes; let i = a.indexOf(this.currentLane);
            if (i < 0) i = a.length-1; if (i < a.length-1) this.currentLane = a[i+1];
        },

        // ── Level resolution ──────────────────────────────────────────────
        _finishLevel(passed) {
            cancelAnimationFrame(this.loopId);
            this.currentNote = null; this.flashResult = null; this.revealName = null;
            this.lastResult = {
                correct: this.correctInLevel,
                total: this.resolvedInLevel,
                accuracy: Math.round(this.correctInLevel / Math.max(1, this.resolvedInLevel) * 100),
                bestStreak: this.levelBestStreak,
                passed,
            };

            if (passed && this.currentLevel < 5) {
                this.gameState = 'levelend';
                this.$nextTick(() => { lucide.createIcons(); this.launchConfetti(this.points, false); });
            } else if (passed && this.currentLevel >= 5) {
                this.allLevelsComplete = true;
                this._endGame();
            } else {
                this._endGame();
            }
        },

        nextLevel() {
            this.currentLevel++;
            this.maxLevelReached = Math.max(this.maxLevelReached, this.currentLevel);
            this._resetLevel();
            this.gameState = 'playing';
            this.spawnNote();
            this.startLoop();
        },

        _endGame() {
            cancelAnimationFrame(this.loopId);
            this.currentNote = null;
            this.gameState = 'gameover';
            this.isNewBest = this.points > this.personalBest;
            if (this.isNewBest) this.personalBest = this.points;

            this.$nextTick(() => {
                if (this.allLevelsComplete || this.lastResult.passed) this.launchConfetti(this.points, true);
                lucide.createIcons();
            });

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            fetch(SCORE_URL, {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
                body: JSON.stringify({
                    score: this.points,
                    max_streak: this.maxStreak,
                    level_reached: this.maxLevelReached,
                    metadata: { correct: this.correctCount, mode: this.mode, allComplete: this.allLevelsComplete },
                }),
            })
            .then(r => r.json())
            .then(data => { if (data.can_play_again === false) this.limitReached = true; })
            .catch(() => {});
        },

        resetGame() {
            cancelAnimationFrame(this.loopId);
            if (this.limitReached) { window.location.reload(); return; }
            this.currentNote = null; this.flashResult = null; this.revealName = null;
            this.gameState = 'idle';
        },

        // ── Confetti ──────────────────────────────────────────────────────
        launchConfetti(score, big) {
            const DURATION = big ? 3000 : 1600, FADE_FROM = big ? 2200 : 1100;
            const count = big ? Math.min(280, Math.max(80, Math.floor(score/3))) : 90;
            const canvas = document.createElement('canvas');
            canvas.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;z-index:99999;pointer-events:none;';
            canvas.width = window.innerWidth; canvas.height = window.innerHeight;
            document.body.appendChild(canvas);
            const COLORS = ['#a78bfa','#34d399','#60a5fa','#f472b6','#fbbf24','#fb923c','#f87171','#38bdf8','#4ade80','#e879f9'];
            const ps = Array.from({length:count}, () => ({
                x: Math.random()*canvas.width, y: -10 - Math.random()*180,
                vx: (Math.random()-0.5)*4, vy: 1.8 + Math.random()*4.5,
                w: 6+Math.random()*9, h: 3+Math.random()*5,
                color: COLORS[Math.floor(Math.random()*COLORS.length)],
                rot: Math.random()*360, rs: (Math.random()-0.5)*8, alpha: 1,
            }));
            const ctx = canvas.getContext('2d'); const t0 = performance.now();
            const frame = now => {
                const el = now - t0; ctx.clearRect(0,0,canvas.width,canvas.height); let alive = 0;
                ps.forEach(p => { p.x+=p.vx; p.y+=p.vy; p.vy+=0.13; p.rot+=p.rs;
                    if (el>FADE_FROM) p.alpha-=0.012; if (p.alpha<=0) return; alive++;
                    ctx.save(); ctx.globalAlpha=p.alpha; ctx.translate(p.x,p.y); ctx.rotate(p.rot*Math.PI/180);
                    ctx.fillStyle=p.color; ctx.fillRect(-p.w/2,-p.h/2,p.w,p.h); ctx.restore(); });
                if (el<DURATION && alive>0) requestAnimationFrame(frame);
                else if (canvas.parentNode) canvas.parentNode.removeChild(canvas);
            };
            requestAnimationFrame(frame);
        },
    };
}
</script>
@endif
