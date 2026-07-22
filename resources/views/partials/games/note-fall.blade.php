{{-- Note Fall game partial — 5-level edition --}}

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
    .nf-white-key { transition: background 0.07s ease, box-shadow 0.07s ease; }
    .nf-black-key { transition: background 0.07s ease, box-shadow 0.07s ease; }
    .nf-white-key:not(.nf-key-active):hover {
        background: linear-gradient(to bottom, #fef9c3 0%, #fffde7 100%) !important;
    }
    .nf-black-key:not(.nf-key-active):hover {
        background: linear-gradient(to bottom, #374151 0%, #4b5563 100%) !important;
    }
    @keyframes nfGlowGreen {
        0%   { box-shadow: 0 0 10px 4px rgba(52,211,153,0.7); }
        40%  { box-shadow: 0 0 28px 10px rgba(52,211,153,1), 0 0 50px 16px rgba(52,211,153,0.35); }
        100% { box-shadow: 0 0 10px 4px rgba(52,211,153,0.7); }
    }
    @keyframes nfGlowRed {
        0%   { box-shadow: 0 0 10px 4px rgba(248,113,113,0.7); }
        40%  { box-shadow: 0 0 28px 10px rgba(248,113,113,1), 0 0 50px 16px rgba(248,113,113,0.35); }
        100% { box-shadow: 0 0 10px 4px rgba(248,113,113,0.7); }
    }
    @keyframes nfGlowAmber {
        0%   { box-shadow: 0 0 10px 4px rgba(251,191,36,0.7); }
        40%  { box-shadow: 0 0 28px 10px rgba(251,191,36,1), 0 0 50px 16px rgba(251,191,36,0.35); }
        100% { box-shadow: 0 0 10px 4px rgba(251,191,36,0.7); }
    }
    .nf-glow-green { animation: nfGlowGreen 0.28s ease-out; }
    .nf-glow-red   { animation: nfGlowRed   0.28s ease-out; }
    .nf-glow-amber { animation: nfGlowAmber 0.28s ease-out; }
    @keyframes nfNoteExplode {
        0%   { transform: scale(1);   opacity: 1; }
        40%  { transform: scale(2.0); opacity: 0.9; }
        100% { transform: scale(3.0); opacity: 0; }
    }
    .nf-note-explode { animation: nfNoteExplode 0.38s ease-out forwards; pointer-events: none; }
    @keyframes nfLevelIn {
        0%   { opacity:0; transform:scale(0.7); }
        60%  { opacity:1; transform:scale(1.06); }
        100% { opacity:1; transform:scale(1); }
    }
    .nf-level-in { animation: nfLevelIn 0.4s ease-out forwards; }
</style>

<div x-data="noteFallGame()" x-init="onInit()" class="game-surface rounded-2xl overflow-hidden">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-emerald-500/20 to-teal-600/20 border-b border-white/10 py-4 px-5 sm:p-5">
        <div class="relative flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('games.index') }}" class="w-10 h-10 rounded-xl bg-white border border-white/20 flex items-center justify-center text-red-600 font-bold hover:bg-white/90 transition-all flex-shrink-0">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <div class="text-white font-bold text-sm">{{ __('app.games.note_fall.title') }}</div>
                    <div class="text-white/40 text-xs"
                         x-text="gameState==='idle' ? str.ready : gameState==='playing' ? (str.level+' '+currentLevel) : gameState==='level_pass' ? str.levelPass : str.gameOver"></div>
                </div>
            </div>

            {{-- Centred: note progress during play --}}
            <div class="absolute left-1/2 -translate-x-1/2 text-center pointer-events-none"
                 x-show="gameState==='playing' || gameState==='level_pass'">
                <div class="font-black text-3xl tabular-nums leading-none flex items-center gap-0.5">
                    <span class="text-emerald-400" x-text="correctInLevel"></span>
                    <span class="text-white/40 font-normal">/</span>
                    <span class="text-white" x-text="resolvedInLevel"></span>
                </div>
            </div>

            <div class="flex flex-col items-end gap-1">
                <div class="text-white font-black text-xl tabular-nums leading-none" x-text="score"></div>
                <div class="flex items-center gap-0.5">
                    <template x-for="i in [1,2,3]" :key="i">
                        <span :class="i <= lives ? 'text-red-400' : 'text-white/20'"
                              class="text-xl leading-none transition-colors duration-300">♥</span>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Idle --}}
    <div x-show="gameState==='idle'" class="flex flex-col items-center justify-center gap-5 p-8" style="min-height:460px;">

        {{-- Nota ikonları açıklama metninin iki yanında --}}
        <div class="flex items-center gap-2 w-full max-w-xs">
            <span class="text-white/10 text-4xl font-black select-none flex-shrink-0 leading-none">♩</span>
            <p class="text-white/40 text-sm text-center flex-1 min-w-0" x-text="str.noteFallDesc"></p>
            <span class="text-white/10 text-4xl font-black select-none flex-shrink-0 leading-none">♫</span>
        </div>

        {{-- Level kutuları — mobil: 3+2 sıra, sm+: tek sıra --}}
        <div class="sm:hidden flex flex-col items-center gap-2">
            <div class="flex gap-2">
                <template x-for="lvl in [1,2,3]" :key="lvl">
                    <div class="flex flex-col items-center px-3 py-2 rounded-xl border"
                         :class="lvl===1 ? 'border-emerald-400/60 bg-emerald-400/8' : 'border-white/8 opacity-40'">
                        <span class="text-xs font-black text-white/70" x-text="str.level+' '+lvl"></span>
                        <span class="text-[9px] text-white/35 mt-0.5" x-text="NF_LEVEL_SHORT[lvl-1]"></span>
                    </div>
                </template>
            </div>
            <div class="flex gap-2">
                <template x-for="lvl in [4,5]" :key="lvl">
                    <div class="flex flex-col items-center px-3 py-2 rounded-xl border border-white/8 opacity-40">
                        <span class="text-xs font-black text-white/70" x-text="str.level+' '+lvl"></span>
                        <span class="text-[9px] text-white/35 mt-0.5" x-text="NF_LEVEL_SHORT[lvl-1]"></span>
                    </div>
                </template>
            </div>
        </div>
        <div class="hidden sm:flex gap-2 justify-center">
            <template x-for="lvl in [1,2,3,4,5]" :key="lvl">
                <div class="flex flex-col items-center px-3 py-2 rounded-xl border"
                     :class="lvl===1 ? 'border-emerald-400/60 bg-emerald-400/8' : 'border-white/8 opacity-40'">
                    <span class="text-xs font-black text-white/70" x-text="str.level+' '+lvl"></span>
                    <span class="text-[9px] text-white/35 mt-0.5" x-text="NF_LEVEL_SHORT[lvl-1]"></span>
                </div>
            </template>
        </div>
        <p class="text-white/25 text-xs text-center max-w-[240px]" x-text="str.levelHint"></p>

        {{-- Mode selector --}}
        <div class="flex gap-3">
            <button @click="mode='staff'"
                    :class="mode==='staff'?'border-emerald-400 text-emerald-300 bg-emerald-400/10':'border-white/10 text-white/40'"
                    class="flex flex-col items-center gap-2 px-5 py-4 rounded-xl border transition-all">
                <svg viewBox="0 0 40 52" width="40" height="52" xmlns="http://www.w3.org/2000/svg">
                    <line x1="2" y1="10" x2="38" y2="10" stroke="currentColor" stroke-width="1.2" opacity="0.6"/>
                    <line x1="2" y1="17" x2="38" y2="17" stroke="currentColor" stroke-width="1.2" opacity="0.6"/>
                    <line x1="2" y1="24" x2="38" y2="24" stroke="currentColor" stroke-width="1.2" opacity="0.6"/>
                    <line x1="2" y1="31" x2="38" y2="31" stroke="currentColor" stroke-width="1.2" opacity="0.6"/>
                    <line x1="2" y1="38" x2="38" y2="38" stroke="currentColor" stroke-width="1.2" opacity="0.6"/>
                    <ellipse cx="20" cy="24" rx="6" ry="4.5" fill="currentColor"/>
                    <line x1="26" y1="21" x2="26" y2="7" stroke="currentColor" stroke-width="1.5"/>
                </svg>
                <span class="text-xs font-semibold">{{ __('app.games.note_fall.mode_staff') }}</span>
            </button>
            <button @click="mode='letters'"
                    :class="mode==='letters'?'border-emerald-400 text-emerald-300 bg-emerald-400/10':'border-white/10 text-white/40'"
                    class="flex flex-col items-center gap-2 px-5 py-4 rounded-xl border transition-all">
                <span class="text-3xl font-black leading-none">G</span>
                <span class="text-xs font-semibold">{{ __('app.games.note_fall.mode_letters') }}</span>
            </button>
        </div>

        @if($personalBest > 0)
        <div class="text-white/30 text-sm flex items-center gap-1.5">
            <i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i>
            <span x-text="str.personalBest"></span>
            <span class="text-white font-bold ml-1">{{ number_format($personalBest) }}</span>
        </div>
        @endif

        <button @click="startGame()"
                class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-emerald-400 to-teal-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform">
            <span class="flex items-center gap-2">
                <i data-lucide="play" class="w-4 h-4 fill-current"></i>
                <span x-text="str.startGame"></span>
            </span>
        </button>
    </div>

    {{-- Playing / Level Pass / Game Over --}}
    <div x-show="gameState==='playing' || gameState==='level_pass' || gameState==='gameover'">
        <div class="sm:mx-[5%]">

        {{-- Fall Field --}}
        <div class="relative select-none overflow-hidden" style="height:410px;background:#05080f;" x-ref="field">

            {{-- Column guides --}}
            <div class="absolute inset-0"
                 style="background-image:repeating-linear-gradient(90deg,rgba(255,255,255,0.025) 0,rgba(255,255,255,0.025) 1px,transparent 1px,transparent calc(100%/7));pointer-events:none;"></div>

            {{-- Catch-zone bar (visual "piano top" line) --}}
            <div class="absolute left-0 right-0" style="bottom:2px;height:2px;background:rgba(16,185,129,0.12);pointer-events:none;"></div>

            {{-- Falling notes --}}
            <template x-for="item in fallingNotes" :key="item.id">
                <div class="absolute"
                     :style="`left:${item.x}px;top:${item.y}px;`"
                     :class="[
                         item.state==='correct' ? 'text-emerald-400' : item.state==='wrong' ? 'text-red-400' : 'text-white',
                         item.state==='wrong' ? 'nf-note-explode' : ''
                     ]">

                    {{-- Staff mode --}}
                    <div x-show="mode==='staff'" x-html="getItemSVG(item)" class="flex justify-center"></div>

                    {{-- Letter mode — single note --}}
                    <template x-if="mode==='letters' && item.seqLen===1">
                        <div :class="item.state==='correct'
                                 ? 'bg-emerald-500/50 border-emerald-400'
                                 : item.state==='wrong'
                                 ? 'bg-red-500/50 border-red-400'
                                 : 'bg-emerald-500/25 border-emerald-400/50'"
                             class="w-12 h-12 rounded-xl border flex items-center justify-center">
                            <span class="text-white font-black text-lg" x-text="window.HarmonivaNotation.toDisplaySymbol(item.notes[0].pc)"></span>
                        </div>
                    </template>

                    {{-- Letter mode — sequence --}}
                    <template x-if="mode==='letters' && item.seqLen>1">
                        <div class="flex flex-col gap-1 items-center">
                            <template x-for="(note, idx) in item.notes" :key="idx">
                                <div :class="idx < item.currentNoteIndex
                                         ? 'bg-emerald-500/60 border-emerald-400 opacity-70'
                                         : idx === item.currentNoteIndex
                                         ? (item.state==='wrong' ? 'bg-red-500/50 border-red-400' : 'bg-emerald-500/25 border-emerald-400/70')
                                         : 'bg-white/5 border-white/15'"
                                     class="w-12 h-9 rounded-lg border flex items-center justify-center transition-all">
                                    <span :class="idx < item.currentNoteIndex ? 'text-emerald-300' : idx===item.currentNoteIndex ? 'text-white' : 'text-white/30'"
                                          class="font-black text-sm" x-text="note.requiresNatural ? window.HarmonivaNotation.toDisplaySymbol(note.pc)+'♮' : window.HarmonivaNotation.toDisplaySymbol(note.pc)"></span>
                                </div>
                            </template>
                            <div class="text-white/40 text-[9px] mt-0.5"
                                 x-text="(item.currentNoteIndex+1)+'/'+item.seqLen"></div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Level Pass overlay --}}
            <div x-show="gameState==='level_pass'"
                 class="absolute inset-0 bg-black/80 flex flex-col items-center justify-center gap-4 z-20">
                <div class="nf-level-in text-center">
                    <div class="text-4xl mb-3">🎉</div>
                    <div class="text-emerald-400 font-black text-2xl mb-1" x-text="str.level+' '+currentLevel+' '+str.complete+'!'"></div>
                    <div class="text-white/60 text-sm mb-3"
                         x-text="'✓ '+correctInLevel+' / '+NOTES_PER_LEVEL"></div>
                    <template x-if="currentLevel < 5">
                        <div class="text-white/40 text-xs" x-text="str.nextLevel+': '+str.level+' '+(currentLevel+1)"></div>
                    </template>
                    <template x-if="currentLevel >= 5">
                        <div class="text-yellow-400 text-xs font-bold" x-text="str.allComplete"></div>
                    </template>
                </div>
            </div>

            {{-- Game Over overlay --}}
            <div x-show="gameState==='gameover'"
                 class="absolute inset-0 bg-black/75 flex flex-col items-center justify-center gap-4 z-20">
                <div :class="allLevelsComplete ? 'text-5xl' : 'text-4xl'"
                     x-text="allLevelsComplete ? '🏆' : correctInLevel >= PASS_THRESHOLD ? '🎹' : '😔'"></div>
                <div class="text-center">
                    <div class="text-white/40 text-sm mb-1"
                         x-text="allLevelsComplete ? str.allComplete : (correctInLevel < PASS_THRESHOLD ? str.levelFailed : str.finalScore)"></div>
                    <div class="text-5xl font-black text-white tabular-nums" x-text="score"></div>
                </div>
                <template x-if="!allLevelsComplete && correctInLevel < PASS_THRESHOLD">
                    <div class="text-white/40 text-sm text-center">
                        <span x-text="'✓ '+correctInLevel+' / '+NOTES_PER_LEVEL"></span>
                        <span class="text-white/25 ml-2" x-text="'('+str.needScore+' '+PASS_THRESHOLD+')'"></span>
                    </div>
                </template>
                <div class="flex items-center gap-5 text-sm text-white/50">
                    <span><span x-text="str.streak"></span>: <span x-text="maxStreak"></span></span>
                    <span x-text="'✓ '+correctCount"></span>
                    <span class="flex items-center gap-1">
                        <i data-lucide="layers" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span x-text="str.level+' '+maxLevelReached"></span>
                    </span>
                </div>
                <div x-show="isNewBest"
                     class="px-4 py-2 rounded-full bg-yellow-400/20 border border-yellow-400/30 text-yellow-300 text-sm font-bold"
                     x-text="str.newBest+' 🏆'"></div>
                <button @click="resetGame()"
                        class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-emerald-400 to-teal-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform"
                        x-text="limitReached?'{{ __('app.games.daily_limit_title') }} →':str.playAgain">
                </button>
            </div>
        </div>

        {{-- Piano keyboard --}}
        <div class="relative select-none"
             style="height:118px;background:#08080e;border-top:2px solid rgba(255,255,255,0.08);">

            {{-- White keys --}}
            <div class="flex h-full px-1 gap-px">
                <template x-for="wk in whiteKeys" :key="wk.keyId">
                    <button @click="pressKey([wk.pc])"
                            :disabled="gameState!=='playing'"
                            @mousedown.prevent
                            :id="'nf-key-'+wk.keyId"
                            :class="['nf-white-key flex-1 rounded-b-xl cursor-pointer relative z-0 disabled:cursor-default',
                                     keyStates[wk.keyId] ? 'nf-key-active' : '',
                                     keyGlowClass(wk.keyId)]"
                            :style="getWhiteKeyStyle(wk.keyId)"
                            style="min-width:0;border:1px solid rgba(0,0,0,0.13);border-top:none;">
                    </button>
                </template>
            </div>

            {{-- Black keys --}}
            <template x-for="bk in blackKeys" :key="bk.keyId">
                <button @click.stop="pressKey(bk.pcs)"
                        :disabled="gameState!=='playing'"
                        @mousedown.prevent
                        :id="'nf-key-'+bk.keyId"
                        :class="['nf-black-key absolute top-0 rounded-b-lg cursor-pointer z-10 disabled:cursor-default',
                                 keyStates[bk.keyId] ? 'nf-key-active' : '',
                                 keyGlowClass(bk.keyId)]"
                        :style="'left:'+bk.leftPct+'%;width:9%;height:64%;'+getBlackKeyStyle(bk.keyId)"
                        style="border:1px solid rgba(255,255,255,0.06);border-top:none;box-shadow:inset 0 -4px 8px rgba(0,0,0,0.4),0 4px 12px rgba(0,0,0,0.6);">
                </button>
            </template>
        </div>

        {{-- Sequence hint bar (L4/L5 only) --}}
        <div x-show="currentLevel>=4 && gameState==='playing'"
             class="px-4 py-1.5 bg-black/20 border-t border-white/5 flex items-center justify-center gap-2">
            <i data-lucide="music" class="w-3 h-3 text-white/30"></i>
            <span class="text-white/30 text-xs"
                  x-text="currentLevel===4 ? str.seqHint2 : str.seqHint3"></span>
        </div>
        </div>{{-- /sm:mx-[5%] --}}
    </div>

</div>

<script>
(function () {

// ── Field geometry ─────────────────────────────────────────────────────────
const FIELD_H    = 410;   // fall field height (px)
const NOTE_SVG_W = 90;    // single-note SVG width
const NOTE_SVG_H = 115;   // single-note SVG height

// A note becomes answerable when its TOP is this far into the field (px).
// Prevents matching notes that haven't visually appeared yet.
const VISIBLE_Y = 0;

// A note is "missed" and removed when its TOP reaches this y-value.
// Chosen so the average note HEAD (~staffY≈70) reaches the piano area (y=430).
const CATCH_Y   = 324;   // maintains 86px bottom margin: 410 - 86

// ── Canonical note database (G3–D5, treble clef) ──────────────────────────
// Staff lines top→bottom: y = 28,39,50,61,72  ↔  F5,D5,B4,G4,E4
const NOTE_DB = {
    'G3':  {pc:'G',  oct:3, acc:null, staffY:100, ledgerYs:[83,94]},
    'G#3': {pc:'G#', oct:3, acc:'#',  staffY:100, ledgerYs:[83,94]},
    'Ab3': {pc:'Ab', oct:3, acc:'b',  staffY:94,  ledgerYs:[83,94]},
    'A3':  {pc:'A',  oct:3, acc:null, staffY:94,  ledgerYs:[83,94]},
    'A#3': {pc:'A#', oct:3, acc:'#',  staffY:94,  ledgerYs:[83,94]},
    'Bb3': {pc:'Bb', oct:3, acc:'b',  staffY:89,  ledgerYs:[83]},
    'B3':  {pc:'B',  oct:3, acc:null, staffY:89,  ledgerYs:[83]},
    'C4':  {pc:'C',  oct:4, acc:null, staffY:83,  ledgerYs:[83]},
    'C#4': {pc:'C#', oct:4, acc:'#',  staffY:83,  ledgerYs:[83]},
    'Db4': {pc:'Db', oct:4, acc:'b',  staffY:78,  ledgerYs:[]},
    'D4':  {pc:'D',  oct:4, acc:null, staffY:78,  ledgerYs:[]},
    'D#4': {pc:'D#', oct:4, acc:'#',  staffY:78,  ledgerYs:[]},
    'Eb4': {pc:'Eb', oct:4, acc:'b',  staffY:72,  ledgerYs:[]},
    'E4':  {pc:'E',  oct:4, acc:null, staffY:72,  ledgerYs:[]},
    'F4':  {pc:'F',  oct:4, acc:null, staffY:67,  ledgerYs:[]},
    'F#4': {pc:'F#', oct:4, acc:'#',  staffY:67,  ledgerYs:[]},
    'Gb4': {pc:'Gb', oct:4, acc:'b',  staffY:61,  ledgerYs:[]},
    'G4':  {pc:'G',  oct:4, acc:null, staffY:61,  ledgerYs:[]},
    'G#4': {pc:'G#', oct:4, acc:'#',  staffY:61,  ledgerYs:[]},
    'Ab4': {pc:'Ab', oct:4, acc:'b',  staffY:56,  ledgerYs:[]},
    'A4':  {pc:'A',  oct:4, acc:null, staffY:56,  ledgerYs:[]},
    'A#4': {pc:'A#', oct:4, acc:'#',  staffY:56,  ledgerYs:[]},
    'Bb4': {pc:'Bb', oct:4, acc:'b',  staffY:50,  ledgerYs:[]},
    'B4':  {pc:'B',  oct:4, acc:null, staffY:50,  ledgerYs:[]},
    'C5':  {pc:'C',  oct:5, acc:null, staffY:44,  ledgerYs:[]},
    'C#5': {pc:'C#', oct:5, acc:'#',  staffY:44,  ledgerYs:[]},
    'Db5': {pc:'Db', oct:5, acc:'b',  staffY:39,  ledgerYs:[]},
    'D5':  {pc:'D',  oct:5, acc:null, staffY:39,  ledgerYs:[]},
};

const LEVEL_POOLS = {
    1: ['C4','D4','E4','F4','G4','A4','B4'],
    2: ['G3','A3','B3','C4','D4','E4','F4','G4','A4','B4','C5','D5'],
};
const CHROMATIC_POOL = Object.keys(NOTE_DB);
LEVEL_POOLS[3] = CHROMATIC_POOL;
LEVEL_POOLS[4] = CHROMATIC_POOL;
LEVEL_POOLS[5] = CHROMATIC_POOL;

// Semitone ladder G3=0 … D5=19, each slot lists possible spellings
const SEMITONE_NOTES = [
    ['G3'],['G#3','Ab3'],['A3'],['A#3','Bb3'],['B3'],
    ['C4'],['C#4','Db4'],['D4'],['D#4','Eb4'],['E4'],
    ['F4'],['F#4','Gb4'],['G4'],['G#4','Ab4'],['A4'],
    ['A#4','Bb4'],['B4'],['C5'],['C#5','Db5'],['D5'],
];

// Pitch class → canonical key ID for keyStates / glow
const PC_TO_KEYID = {
    C:'C', D:'D', E:'E', F:'F', G:'G', A:'A', B:'B',
    'C#':'Cs','Db':'Cs','D#':'Ds','Eb':'Ds',
    'F#':'Fs','Gb':'Fs','G#':'Gs','Ab':'Gs','A#':'As','Bb':'As',
};

// ── SVG builder ─────────────────────────────────────────────────────────────
const STAFF_LINES = [28, 39, 50, 61, 72];

function _staffLines(W) {
    return STAFF_LINES.map(y =>
        `<line x1="5" y1="${y}" x2="${W-5}" y2="${y}" stroke="rgba(255,255,255,0.68)" stroke-width="1.4"/>`
    ).join('');
}

function _noteGlyph(cx, cy, acc, ledgerYs, fill) {
    let s = '';
    ledgerYs.forEach(ly =>
        s += `<line x1="${cx-14}" y1="${ly}" x2="${cx+14}" y2="${ly}" stroke="${fill==='currentColor'?'rgba(255,255,255,0.82)':fill}" stroke-width="1.5"/>`
    );
    if (acc === '#') s += `<text x="${cx-23}" y="${cy+5}" font-size="19" fill="#f97316" font-family="serif" font-weight="bold">♯</text>`;
    if (acc === 'b') s += `<text x="${cx-21}" y="${cy+3}" font-size="19" fill="#f97316" font-family="serif" font-weight="bold">♭</text>`;
    s += `<ellipse cx="${cx}" cy="${cy}" rx="7.7" ry="5.4" fill="${fill}"/>`;
    if (cy >= 50) { const st = Math.max(10, cy-32); s += `<line x1="${cx+8}" y1="${cy-3}" x2="${cx+8}" y2="${st}" stroke="${fill}" stroke-width="2"/>`; }
    else          { const sb = Math.min(108, cy+32); s += `<line x1="${cx-8}" y1="${cy+3}" x2="${cx-8}" y2="${sb}" stroke="${fill}" stroke-width="2"/>`; }
    return s;
}

// Precompute single-note SVGs
const NOTE_SVGS = {};
Object.keys(NOTE_DB).forEach(k => {
    const n = NOTE_DB[k];
    NOTE_SVGS[k] = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${NOTE_SVG_W} ${NOTE_SVG_H}" width="${NOTE_SVG_W}" height="${NOTE_SVG_H}">`
        + _staffLines(NOTE_SVG_W)
        + _noteGlyph(47, n.staffY, n.acc, n.ledgerYs, 'currentColor')
        + `</svg>`;
});

function buildSequenceSVG(notes, currentNoteIndex) {
    const count = notes.length;
    const W = count === 2 ? 155 : 205;
    const CXS = count === 2 ? [42, 113] : [36, 103, 170];
    let s = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${NOTE_SVG_H}" width="${W}" height="${NOTE_SVG_H}">`
          + _staffLines(W);
    notes.forEach((note, i) => {
        const n = NOTE_DB[note.key]; if (!n) return;
        const cx   = CXS[i];
        const done = i < currentNoteIndex;
        const active = i === currentNoteIndex;
        const fill = done ? 'rgba(52,211,153,0.9)' : active ? 'rgba(255,255,255,0.95)' : 'rgba(255,255,255,0.22)';
        // Ledger lines with context-aware colour
        n.ledgerYs.forEach(ly =>
            s += `<line x1="${cx-14}" y1="${ly}" x2="${cx+14}" y2="${ly}" stroke="${done?'rgba(52,211,153,0.55)':'rgba(255,255,255,0.55)'}" stroke-width="1.4"/>`
        );
        // requiresNatural overrides the pitch accidental for display only (answer key unchanged)
        const dispAcc = note.requiresNatural ? 'natural' : n.acc;
        if (dispAcc==='#')       s += `<text x="${cx-23}" y="${n.staffY+5}" font-size="16" fill="#f97316" font-family="serif" font-weight="bold">♯</text>`;
        if (dispAcc==='b')       s += `<text x="${cx-21}" y="${n.staffY+3}" font-size="16" fill="#f97316" font-family="serif" font-weight="bold">♭</text>`;
        if (dispAcc==='natural') s += `<text x="${cx-22}" y="${n.staffY+5}" font-size="16" fill="#f97316" font-family="serif" font-weight="bold">♮</text>`;
        s += `<ellipse cx="${cx}" cy="${n.staffY}" rx="7.7" ry="5.4" fill="${fill}"/>`;
        if (n.staffY>=50) { const st=Math.max(10,n.staffY-32); s+=`<line x1="${cx+8}" y1="${n.staffY-3}" x2="${cx+8}" y2="${st}" stroke="${fill}" stroke-width="2"/>`; }
        else              { const sb=Math.min(108,n.staffY+32); s+=`<line x1="${cx-8}" y1="${n.staffY+3}" x2="${cx-8}" y2="${sb}" stroke="${fill}" stroke-width="2"/>`; }
        if (done) s += `<text x="${cx-4}" y="${Math.min(n.staffY-14,22)}" font-size="9" fill="rgba(52,211,153,0.8)">✓</text>`;
    });
    s += `<text x="${W/2}" y="${NOTE_SVG_H-4}" text-anchor="middle" font-size="8" fill="rgba(255,255,255,0.28)" font-family="sans-serif">${currentNoteIndex+1} / ${count}</text>`;
    return s + `</svg>`;
}

// ── Alpine component ─────────────────────────────────────────────────────────
window.noteFallGame = function () {
    const PERSONAL_BEST = {{ (int)$personalBest }};
    const SCORE_URL     = @json($scoreUrl);
    const STR           = window.GAME_STRINGS || {};

    const NF_LEVEL_SHORT = ['C4–B4','G3–D5','♯&♭',@json(__('app.games.note_fall.level_short_2note')),@json(__('app.games.note_fall.level_short_3note'))];

    return {
        str: Object.assign({
            ready: @json(__('app.games.note_fall.ready')),
            playing: @json(__('app.games.note_fall.playing')),
            gameOver: @json(__('app.games.status_game_over')),
            level: @json(__('app.games.note_fall.level')),
            streak: @json(__('app.games.note_fall.streak')),
            startGame: @json(__('app.games.note_fall.start_game')),
            playAgain: @json(__('app.games.play_again')),
            finalScore: @json(__('app.games.final_score')),
            personalBest: @json(__('app.games.note_fall.personal_best')),
            newBest: @json(__('app.games.note_fall.new_best')),
            noteFallDesc: @json(__('app.games.note_fall.description')),
            levelHint: @json(__('app.games.note_fall.level_hint')),
            levelPass: @json(__('app.games.note_fall.level_pass')),
            complete: @json(__('app.games.note_fall.complete')),
            nextLevel: @json(__('app.games.note_fall.next_level')),
            allComplete: @json(__('app.games.note_fall.all_complete')),
            levelFailed: @json(__('app.games.note_fall.level_failed')),
            needScore: @json(__('app.games.note_fall.need_score')),
            seqHint2: @json(__('app.games.note_fall.seq_hint_2')),
            seqHint3: @json(__('app.games.note_fall.seq_hint_3')),
        }, STR),

        NOTE_SVGS,
        NF_LEVEL_SHORT,

        // ── Game state ──────────────────────────────────────────────────────
        gameState: 'idle',   // idle | playing | level_pass | gameover
        allLevelsComplete: false,
        mode: 'staff',
        currentLevel: 1,
        maxLevelReached: 1,
        limitReached: false,

        // ── Scores / streaks ────────────────────────────────────────────────
        score: 0, streak: 0, maxStreak: 0,
        correctCount: 0,     // total correct across all levels this run

        // ── Per-level counters ──────────────────────────────────────────────
        NOTES_PER_LEVEL: 20,
        PASS_THRESHOLD:  15,
        spawnedInLevel:  0,    // notes spawned in current level
        resolvedInLevel: 0,    // notes resolved (correct + missed + wrong)
        correctInLevel:  0,    // correct answers in current level
        levelEvaluating: false,

        // ── Lives ────────────────────────────────────────────────────────────
        lives: 3, consecutiveErrors: 0,

        // ── Fall engine ─────────────────────────────────────────────────────
        fallingNotes: [], nextId: 0,
        loopId: null, spawnTimer: null, levelPassTimer: null,
        recentNoteKeys: [], recentSemitones: [],

        // ── Best ─────────────────────────────────────────────────────────────
        isNewBest: false, personalBest: PERSONAL_BEST,

        // ── Key visuals ──────────────────────────────────────────────────────
        keyStates: { C:null,D:null,E:null,F:null,G:null,A:null,B:null, Cs:null,Ds:null,Fs:null,Gs:null,As:null },
        whiteKeys: [{pc:'C',keyId:'C'},{pc:'D',keyId:'D'},{pc:'E',keyId:'E'},{pc:'F',keyId:'F'},{pc:'G',keyId:'G'},{pc:'A',keyId:'A'},{pc:'B',keyId:'B'}],
        blackKeys: [
            {pcs:['C#','Db'],keyId:'Cs',label:'C#/Db',leftPct:10.4},
            {pcs:['D#','Eb'],keyId:'Ds',label:'D#/Eb',leftPct:24.2},
            {pcs:['F#','Gb'],keyId:'Fs',label:'F#/Gb',leftPct:52.6},
            {pcs:['G#','Ab'],keyId:'Gs',label:'G#/Ab',leftPct:66.5},
            {pcs:['A#','Bb'],keyId:'As',label:'A#/Bb',leftPct:80.3},
        ],

        // ── Computed ─────────────────────────────────────────────────────────
        get seqLen()    { return [1,1,1,2,3][this.currentLevel-1]; },
        // Speed increases 30% every 10 notes spawned (phases 0–2)
        get phase()     { return Math.min(2, Math.floor(this.spawnedInLevel / 10)); },
        get fallSpeed() { return 2.2 * Math.pow(1.3, this.phase); },
        get spawnMs()   {
            const seqPause = [1.0, 1.0, 1.0, 1.8, 2.2][this.currentLevel-1];
            return Math.round(1000 * seqPause / Math.pow(1.3, this.phase));
        },
        get itemWidth() { return [NOTE_SVG_W, NOTE_SVG_W, NOTE_SVG_W, 155, 205][this.currentLevel-1]; },

        // ── Init ─────────────────────────────────────────────────────────────
        onInit() { lucide.createIcons(); },

        // ── Key styling ──────────────────────────────────────────────────────
        getWhiteKeyStyle(id) {
            const s = this.keyStates[id];
            if (s==='correct') return 'background:linear-gradient(to bottom,#6ee7b7,#34d399);';
            if (s==='wrong')   return 'background:linear-gradient(to bottom,#fca5a5,#f87171);';
            if (s==='hint')    return 'background:linear-gradient(to bottom,#fde68a,#fbbf24);';
            return 'background:linear-gradient(to bottom,#e8e8e8 0%,#ffffff 40%,#f0f0f0 100%);';
        },
        getBlackKeyStyle(id) {
            const s = this.keyStates[id];
            if (s==='correct') return 'background:linear-gradient(to bottom,#059669,#047857);';
            if (s==='wrong')   return 'background:linear-gradient(to bottom,#dc2626,#b91c1c);';
            if (s==='hint')    return 'background:linear-gradient(to bottom,#d97706,#b45309);';
            return 'background:linear-gradient(to bottom,#2d2d2d 0%,#1a1a1a 60%,#0f0f0f 100%);';
        },
        keyGlowClass(id) {
            const s = this.keyStates[id];
            if (s==='correct') return 'nf-glow-green';
            if (s==='wrong')   return 'nf-glow-red';
            if (s==='hint')    return 'nf-glow-amber';
            return '';
        },

        // ── SVG retrieval (reactive for sequences) ────────────────────────────
        getItemSVG(item) {
            if (item.seqLen === 1) return NOTE_SVGS[item.notes[0].key] || '';
            return buildSequenceSVG(item.notes, item.currentNoteIndex);
        },

        // ── Note generation ───────────────────────────────────────────────────
        pickNoteFromPool(pool, recentKeys) {
            const last2 = recentKeys.slice(-2);
            let pool2 = pool.filter(n => !last2.includes(n));
            if (!pool2.length) pool2 = pool.filter(n => n !== recentKeys[recentKeys.length-1]);
            if (!pool2.length) pool2 = pool;
            return pool2[Math.floor(Math.random() * pool2.length)];
        },

        generateMelodicSequence(length) {
            const STEP_W = [0, 30, 28, 18, 12, 7, 5];
            const TOTAL_W = STEP_W.reduce((a,b) => a+b, 0);
            const recent = this.recentSemitones.slice(-3);
            let pool = [];
            for (let i=5; i<=14; i++) { if (!recent.includes(i)) pool.push(i); }
            if (!pool.length) pool = [5,7,9,12,14];
            let st = pool[Math.floor(Math.random()*pool.length)];
            const keys = [];
            this.recentSemitones.push(st);
            for (let i=0; i<length; i++) {
                const choices = SEMITONE_NOTES[st];
                keys.push(choices[Math.floor(Math.random()*choices.length)]);
                if (i < length-1) {
                    let r = Math.random()*TOTAL_W, step=1, acc2=0;
                    for (let j=1; j<STEP_W.length; j++) { acc2+=STEP_W[j]; if(r<acc2){step=j;break;} }
                    const canUp=st+step<=19, canDown=st-step>=0;
                    const dir = !canUp ? -1 : !canDown ? 1 : Math.random()<0.5 ? 1 : -1;
                    st = Math.max(0, Math.min(19, st+dir*step));
                    this.recentSemitones.push(st);
                }
            }
            if (this.recentSemitones.length > 8) this.recentSemitones = this.recentSemitones.slice(-8);
            return keys;
        },

        buildNoteObjects(keys) {
            const notes = keys.map(k => { const n=NOTE_DB[k]||{}; return {key:k, pc:n.pc||k, acc:n.acc||null, requiresNatural:false}; });
            // L4/L5 notation rule: when a natural note follows an accidentalled note with the
            // same diatonic letter (e.g. F#→F, Bb→B), mark it for ♮ display only.
            // Pitch, answer key, and validation logic are NOT mutated.
            for (let i = 1; i < notes.length; i++) {
                const prev = notes[i-1], curr = notes[i];
                if ((prev.acc === '#' || prev.acc === 'b') && curr.acc === null && prev.pc[0] === curr.pc[0])
                    notes[i].requiresNatural = true;
            }
            return notes;
        },

        // ── Game start ────────────────────────────────────────────────────────
        startGame() {
            if (window.HarmonivaAudio) HarmonivaAudio.warmup();
            this.score=0; this.streak=0; this.maxStreak=0; this.correctCount=0;
            this.currentLevel=1; this.maxLevelReached=1;
            this.lives=3; this.consecutiveErrors=0;
            this.isNewBest=false; this.allLevelsComplete=false;
            this.fallingNotes=[]; this.nextId=0;
            this._resetLevelCounters();
            this.gameState='playing';
            this._startLoop();
            this._scheduleSpawn();
        },

        _resetLevelCounters() {
            this.spawnedInLevel=0; this.resolvedInLevel=0; this.correctInLevel=0;
            this.levelEvaluating=false; this.consecutiveErrors=0;
            this.recentNoteKeys=[]; this.recentSemitones=[];
        },

        // ── Fall loop ─────────────────────────────────────────────────────────
        _startLoop() {
            const tick = () => {
                if (this.gameState !== 'playing') return;
                const toRemove = [];

                this.fallingNotes.forEach(n => {
                    if (n.state === 'wrong') return; // already exploding via timeout
                    if (n.fastFall) {
                        n.y += n.vy;
                        if (n.y > FIELD_H + 20) toRemove.push(n.id);
                    } else {
                        n.y += this.fallSpeed;
                        if (n.y >= CATCH_Y) {
                            // Note reached piano without being answered → miss
                            toRemove.push(n.id);
                            this.streak = 0;
                            if (this.lives > 0) {
                                this.consecutiveErrors++;
                                if (this.consecutiveErrors >= 2) {
                                    this.lives--;
                                    this.consecutiveErrors = 0;
                                }
                            }
                            if (this.resolvedInLevel < this.NOTES_PER_LEVEL) {
                                this.resolvedInLevel++;
                                if (this.lives <= 0) {
                                    setTimeout(() => this._endGame(), 50);
                                } else {
                                    this._checkLevelComplete();
                                }
                            }
                        }
                    }
                });

                if (toRemove.length) {
                    this.fallingNotes = this.fallingNotes.filter(n => !toRemove.includes(n.id));
                }
                this.loopId = requestAnimationFrame(tick);
            };
            this.loopId = requestAnimationFrame(tick);
        },

        _scheduleSpawn() {
            if (this.gameState !== 'playing') return;
            if (this.spawnedInLevel >= this.NOTES_PER_LEVEL) return;

            const active = this.fallingNotes.filter(n => !n.fastFall && n.state !== 'wrong');
            const lowestY = active.length > 0 ? Math.max(...active.map(n => n.y)) : Infinity;

            // Spawn the next note only when the current note is near the piano (gate),
            // so the new note enters from the top as the old one exits at the bottom.
            // Gate = CATCH_Y - NOTE_SVG_H: ensures ~1 note-height of travel-time overlap.
            const SPAWN_GATE_Y = CATCH_Y - NOTE_SVG_H; // 305 - 115 = 190

            if (active.length >= 2 || (active.length === 1 && lowestY < SPAWN_GATE_Y)) {
                this.spawnTimer = setTimeout(() => this._scheduleSpawn(), 100);
                return;
            }

            this._spawnNote();
            this.spawnTimer = setTimeout(() => this._scheduleSpawn(), 100);
        },

        _spawnNote() {
            if (this.spawnedInLevel >= this.NOTES_PER_LEVEL) return;
            this.spawnedInLevel++;

            const seqLen = this.seqLen;
            let keys;
            if (seqLen === 1) {
                const pool = LEVEL_POOLS[this.currentLevel];
                const key  = this.pickNoteFromPool(pool, this.recentNoteKeys);
                keys = [key];
                this.recentNoteKeys.push(key);
                if (this.recentNoteKeys.length > 4) this.recentNoteKeys.shift();
            } else {
                keys = this.generateMelodicSequence(seqLen);
            }

            const w      = this.itemWidth;
            const fieldW = this.$refs.field?.offsetWidth || 600;
            const margin = 14;
            const x      = margin + Math.random() * (fieldW - w - margin * 2);

            this.fallingNotes.push({
                id: this.nextId++,
                notes: this.buildNoteObjects(keys),
                seqLen,
                currentNoteIndex: 0,
                x, y: -(NOTE_SVG_H + 4),   // fully off-screen above
                state: null, fastFall: false, vy: 0,
            });
        },

        // ── Key press — ONLY the lowest visible note responds ─────────────────
        pressKey(pcs) {
            if (this.gameState !== 'playing') return;

            // Candidate notes: visible (y >= VISIBLE_Y) AND not yet at piano (y < CATCH_Y)
            const candidates = this.fallingNotes.filter(n =>
                !n.fastFall &&
                n.state !== 'wrong' &&
                n.y >= VISIBLE_Y &&
                n.y < CATCH_Y
            );

            // ► Only the LOWEST note (highest y) is active
            const activeItem = candidates.length > 0
                ? candidates.reduce((a, b) => b.y > a.y ? b : a)
                : null;

            // Play sound at the active note's octave when the pressed key matches,
            // so Level 2+ notes (G3, C5, etc.) sound at the correct pitch.
            if (window.HarmonivaAudio) {
                if (activeItem) {
                    const targetNote = activeItem.notes[activeItem.currentNoteIndex];
                    const noteToPlay = pcs.includes(targetNote.pc) ? targetNote.key : (pcs[0] + '4');
                    HarmonivaAudio.playNote(noteToPlay, 0.4);
                } else {
                    HarmonivaAudio.playNote(pcs[0] + '4', 0.4);
                }
            }

            if (!activeItem) {
                // Nothing on screen to answer — flash red and return
                this._flashKey(PC_TO_KEYID[pcs[0]] || pcs[0], 'wrong', 180);
                return;
            }

            const targetPc   = activeItem.notes[activeItem.currentNoteIndex].pc;
            const hitKeyId   = PC_TO_KEYID[targetPc] || targetPc;
            const pressedId  = PC_TO_KEYID[pcs[0]] || pcs[0];

            if (pcs.includes(targetPc)) {
                // ✓ CORRECT
                const idx  = this.fallingNotes.findIndex(n => n.id === activeItem.id);
                const item = this.fallingNotes[idx];
                item.currentNoteIndex++;

                if (item.currentNoteIndex >= item.seqLen) {
                    // Sequence / single note complete
                    item.state    = 'correct';
                    item.fastFall = true;
                    item.vy       = this.fallSpeed * 4;

                    this.streak++;
                    this.maxStreak    = Math.max(this.maxStreak, this.streak);
                    this.correctCount++;
                    this.correctInLevel++;
                    this.consecutiveErrors = 0;

                    if (this.resolvedInLevel < this.NOTES_PER_LEVEL) {
                        this.resolvedInLevel++;
                    }

                    const basePoints = [100, 110, 125, 175, 230][this.currentLevel - 1];
                    this.score += basePoints + this.streak * 15 + this.phase * 25;

                    this._flashKey(hitKeyId, 'correct', 280);
                    this._checkLevelComplete();
                } else {
                    // Partial sequence — flash correct key, keep item falling
                    this._flashKey(hitKeyId, 'correct', 200);
                    // Trigger Alpine reactivity for sequence SVG update
                    this.fallingNotes[idx] = { ...item };
                }
            } else {
                // ✗ WRONG — the active (lowest) note fails
                this.streak = 0;
                this.consecutiveErrors++;
                if (this.consecutiveErrors >= 2) {
                    this.lives--;
                    this.consecutiveErrors = 0;
                }
                this._flashKey(pressedId, 'wrong', 280);

                const idx = this.fallingNotes.findIndex(n => n.id === activeItem.id);
                if (idx !== -1) {
                    const wid = this.fallingNotes[idx].id;
                    this.fallingNotes[idx] = { ...this.fallingNotes[idx], state: 'wrong', currentNoteIndex: 0 };

                    if (this.resolvedInLevel < this.NOTES_PER_LEVEL) {
                        this.resolvedInLevel++;
                    }

                    setTimeout(() => {
                        this.fallingNotes = this.fallingNotes.filter(n => n.id !== wid);
                    }, 420);

                    if (this.lives <= 0) {
                        setTimeout(() => this._endGame(), 50);
                    } else {
                        this._checkLevelComplete();
                    }
                }
            }
        },

        _flashKey(keyId, state, ms) {
            this.keyStates[keyId] = state;
            setTimeout(() => { this.keyStates[keyId] = null; }, ms);
        },

        // ── Level completion logic ─────────────────────────────────────────────
        _checkLevelComplete() {
            if (this.levelEvaluating) return;
            if (this.resolvedInLevel < this.NOTES_PER_LEVEL) return;
            if (this.spawnedInLevel < this.NOTES_PER_LEVEL) return; // shouldn't happen
            this.levelEvaluating = true;
            // Small delay to let the last animation finish
            setTimeout(() => this._evaluateLevel(), 700);
        },

        _evaluateLevel() {
            if (this.gameState !== 'playing') return;
            cancelAnimationFrame(this.loopId);
            clearTimeout(this.spawnTimer);
            this.fallingNotes = []; // clear remaining notes

            if (this.correctInLevel >= this.PASS_THRESHOLD) {
                // ✓ Passed
                if (this.currentLevel >= 5) {
                    this.allLevelsComplete = true;
                    this._endGame();
                } else {
                    this.gameState = 'level_pass';
                    this.levelPassTimer = setTimeout(() => this._startNextLevel(), 2500);
                }
            } else {
                // ✗ Failed
                this._endGame();
            }
        },

        _startNextLevel() {
            this.currentLevel++;
            this.maxLevelReached = Math.max(this.maxLevelReached, this.currentLevel);
            this.lives = 3;
            this._resetLevelCounters();
            this.fallingNotes = [];
            this.gameState = 'playing';
            this._startLoop();
            this._scheduleSpawn();
        },

        // ── End game (failed level or all levels complete) ─────────────────────
        _endGame() {
            if (this.gameState === 'gameover') return;
            cancelAnimationFrame(this.loopId);
            clearTimeout(this.spawnTimer);
            clearTimeout(this.levelPassTimer);
            this.gameState = 'gameover';
            this.isNewBest = this.score > this.personalBest;
            if (this.isNewBest) this.personalBest = this.score;

            this.$nextTick(() => {
                if (this.allLevelsComplete || this.correctInLevel >= this.PASS_THRESHOLD) {
                    this.launchConfetti(this.score);
                    this.playApplause(this.score);
                }
                lucide.createIcons();
            });

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            fetch(SCORE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({
                    score: this.score,
                    max_streak: this.maxStreak,
                    level_reached: this.maxLevelReached,
                    metadata: { correct: this.correctCount, mode: this.mode, allComplete: this.allLevelsComplete },
                }),
            })
            .then(r => r.json())
            .then(d => { if (d.can_play_again === false) this.limitReached = true; })
            .catch(() => {});
        },

        resetGame() {
            cancelAnimationFrame(this.loopId);
            clearTimeout(this.spawnTimer);
            clearTimeout(this.levelPassTimer);
            this.fallingNotes = [];
            if (this.limitReached) { window.location.reload(); return; }
            this.gameState = 'idle';
        },

        // ── Confetti ──────────────────────────────────────────────────────────
        launchConfetti(score) {
            const DURATION=3000, FADE_FROM=2200;
            const count=Math.min(280, Math.max(70, Math.floor(score/3)));
            const canvas=document.createElement('canvas');
            canvas.style.cssText='position:fixed;top:0;left:0;width:100%;height:100%;z-index:99999;pointer-events:none;';
            canvas.width=window.innerWidth; canvas.height=window.innerHeight;
            document.body.appendChild(canvas);
            const COLORS=['#fbbf24','#34d399','#60a5fa','#f472b6','#a78bfa','#fb923c','#f87171','#38bdf8','#4ade80','#facc15','#e879f9'];
            const ps=Array.from({length:count},(_,i)=>{
                const b=i<count*0.35;
                return {x:b?canvas.width/2+(Math.random()-0.5)*160:Math.random()*canvas.width,
                        y:b?canvas.height*0.38+(Math.random()-0.5)*60:-10-Math.random()*180,
                        vx:b?(Math.random()-0.5)*16:(Math.random()-0.5)*3.5,
                        vy:b?-9+Math.random()*7:1.8+Math.random()*4.5,
                        w:6+Math.random()*9,h:3+Math.random()*5,
                        color:COLORS[Math.floor(Math.random()*COLORS.length)],
                        rot:Math.random()*360,rs:(Math.random()-0.5)*8,alpha:1};
            });
            const ctx=canvas.getContext('2d'); const t0=performance.now();
            const frame=now=>{
                const el=now-t0; ctx.clearRect(0,0,canvas.width,canvas.height); let alive=0;
                ps.forEach(p=>{p.x+=p.vx;p.y+=p.vy;p.vy+=0.13;p.rot+=p.rs;
                    if(el>FADE_FROM)p.alpha-=0.012; if(p.alpha<=0)return; alive++;
                    ctx.save();ctx.globalAlpha=p.alpha;ctx.translate(p.x,p.y);ctx.rotate(p.rot*Math.PI/180);
                    ctx.fillStyle=p.color;ctx.fillRect(-p.w/2,-p.h/2,p.w,p.h);ctx.restore();});
                if(el<DURATION&&alive>0)requestAnimationFrame(frame);
                else if(canvas.parentNode)canvas.parentNode.removeChild(canvas);
            };
            requestAnimationFrame(frame);
        },

        // ── Applause ──────────────────────────────────────────────────────────
        playApplause(score) {
            try {
                const AC=window.AudioContext||window.webkitAudioContext; if(!AC)return;
                const ctx=new AC(), dur=Math.min(4.0,2.2+score/220), cnt=Math.floor(18+score/55);
                const master=ctx.createGain();
                master.gain.setValueAtTime(0,ctx.currentTime);master.gain.linearRampToValueAtTime(0.9,ctx.currentTime+0.35);
                master.gain.setValueAtTime(0.9,ctx.currentTime+dur-0.6);master.gain.linearRampToValueAtTime(0,ctx.currentTime+dur);
                master.connect(ctx.destination);
                for(let i=0;i<cnt;i++){
                    const t=ctx.currentTime+Math.random()*(dur*0.88),bd=0.04+Math.random()*0.1;
                    const bufLen=Math.ceil(ctx.sampleRate*(bd+0.12)),buf=ctx.createBuffer(2,bufLen,ctx.sampleRate);
                    for(let ch=0;ch<2;ch++){const d=buf.getChannelData(ch);for(let j=0;j<bufLen;j++)d[j]=Math.random()*2-1;}
                    const src=ctx.createBufferSource();src.buffer=buf;
                    const hp=ctx.createBiquadFilter();hp.type='highpass';hp.frequency.value=900+Math.random()*600;
                    const pk=ctx.createBiquadFilter();pk.type='peaking';pk.frequency.value=2000+Math.random()*1200;pk.gain.value=7;
                    const env=ctx.createGain();env.gain.setValueAtTime(0,t);env.gain.linearRampToValueAtTime(0.28,t+0.006);env.gain.exponentialRampToValueAtTime(0.0005,t+bd);
                    src.connect(hp);hp.connect(pk);pk.connect(env);env.connect(master);src.start(t);src.stop(t+bd+0.05);
                }
                if(window.HarmonivaAudio&&score>60){
                    const notes=score>500?['C4','E4','G4','C5','E5']:score>200?['C4','E4','G4','C5']:['C4','E4','G4'];
                    setTimeout(()=>HarmonivaAudio.playSequence(notes,115,0.85),180);
                }
            } catch(e){}
        },
    };
};

})();
</script>
@endif
