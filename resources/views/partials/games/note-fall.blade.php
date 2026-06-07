{{-- Note Fall game partial --}}

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

<style>
    /* Base key transitions */
    .nf-white-key { transition: background 0.07s ease, box-shadow 0.07s ease; }
    .nf-black-key { transition: background 0.07s ease, box-shadow 0.07s ease; }

    /* Hover only when key is idle (no active state) */
    .nf-white-key:not(.nf-key-active):hover {
        background: linear-gradient(to bottom, #fef9c3 0%, #fffde7 100%) !important;
    }
    .nf-black-key:not(.nf-key-active):hover {
        background: linear-gradient(to bottom, #374151 0%, #4b5563 100%) !important;
    }

    /* Glow pulse animations */
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
</style>

<div x-data="noteFallGame()" x-init="onInit()" class="game-surface rounded-2xl overflow-hidden">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-emerald-500/20 to-teal-600/20 border-b border-white/10 py-4 px-5 sm:p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('games.index') }}" class="w-10 h-10 rounded-xl bg-white border border-white/20 flex items-center justify-center text-red-600 font-bold hover:bg-white/90 transition-all flex-shrink-0">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center">
                    <i data-lucide="arrow-down-to-line" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <div class="text-white font-bold text-sm">Note Fall</div>
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
                <div class="text-center">
                    <div class="text-white/40 text-xs" x-text="str.streak">Streak</div>
                    <div class="font-black text-xl tabular-nums"
                         :class="streak>0?'text-emerald-400':'text-white/30'" x-text="streak"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Idle --}}
    <div x-show="gameState==='idle'" class="flex flex-col items-center justify-center gap-5 p-8" style="min-height:460px;">
        <div class="text-white/10 text-5xl font-black select-none">♩♪♫</div>
        <p class="text-white/40 text-sm text-center max-w-xs" x-text="str.noteFallDesc"></p>

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
                <span class="text-xs font-semibold">Staff</span>
            </button>
            <button @click="mode='letters'"
                    :class="mode==='letters'?'border-emerald-400 text-emerald-300 bg-emerald-400/10':'border-white/10 text-white/40'"
                    class="flex flex-col items-center gap-2 px-5 py-4 rounded-xl border transition-all">
                <span class="text-3xl font-black leading-none">G</span>
                <span class="text-xs font-semibold">Letters</span>
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

    {{-- Playing / Game Over --}}
    <div x-show="gameState==='playing' || gameState==='gameover'">

        {{-- Fall Field --}}
        <div class="relative select-none overflow-hidden" style="height:340px;background:#05080f;" x-ref="field">

            {{-- Subtle column guides --}}
            <div class="absolute inset-0"
                 style="background-image:repeating-linear-gradient(90deg,rgba(255,255,255,0.025) 0,rgba(255,255,255,0.025) 1px,transparent 1px,transparent calc(100%/7));pointer-events:none;"></div>

            {{-- Catch zone bar --}}
            <div class="absolute left-0 right-0" style="bottom:2px;height:2px;background:rgba(16,185,129,0.12);pointer-events:none;"></div>

            {{-- Falling notes --}}
            <template x-for="note in fallingNotes" :key="note.id">
                <div class="absolute" :style="`left:${note.x}px;top:${note.y}px;`"
                     :class="note.sliding ? 'transition-none' : ''">
                    <div x-show="mode==='staff'" x-html="NOTE_SVGS[note.note]"
                         class="w-full flex justify-center"></div>
                    <div x-show="mode==='letters'"
                         class="w-11 h-11 rounded-xl bg-emerald-500/25 border border-emerald-400/50 flex items-center justify-center">
                        <span class="text-white font-black text-xl"
                              x-text="NOTE_LABELS[note.note]||note.note"></span>
                    </div>
                </div>
            </template>

            {{-- Game Over overlay --}}
            <div x-show="gameState==='gameover'"
                 class="absolute inset-0 bg-black/75 flex flex-col items-center justify-center gap-4 z-20">
                <div class="text-5xl">🎹</div>
                <div class="text-center">
                    <div class="text-white/40 text-sm mb-1" x-text="str.finalScore"></div>
                    <div class="text-5xl font-black text-white tabular-nums" x-text="score"></div>
                </div>
                <div class="flex items-center gap-5 text-sm text-white/50">
                    <span x-text="str.level+' '+level"></span>
                    <span><span x-text="str.streak"></span>: <span x-text="maxStreak"></span></span>
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
                <template x-for="wk in whiteKeys" :key="wk.note">
                    <button @click="pressKey(wk.note)"
                            :disabled="gameState==='gameover'"
                            @mousedown.prevent
                            :id="'nf-key-'+wk.note"
                            :class="['nf-white-key flex-1 rounded-b-xl cursor-pointer relative z-0 disabled:cursor-default',
                                     keyStates[wk.note] ? 'nf-key-active' : '',
                                     keyGlowClass(wk.note, false)]"
                            :style="getWhiteKeyStyle(wk.note)"
                            style="min-width:0;border:1px solid rgba(0,0,0,0.13);border-top:none;">
                    </button>
                </template>
            </div>

            {{-- Black keys --}}
            <template x-for="bk in blackKeys" :key="bk.note">
                <button @click.stop="pressKey(bk.note)"
                        :disabled="gameState==='gameover'"
                        @mousedown.prevent
                        :id="'nf-key-'+bk.note"
                        :class="['nf-black-key absolute top-0 rounded-b-lg cursor-pointer z-10 disabled:cursor-default',
                                 keyStates[bk.note] ? 'nf-key-active' : '',
                                 keyGlowClass(bk.note, true)]"
                        :style="'left:'+bk.leftPct+'%;width:9%;height:64%;'+getBlackKeyStyle(bk.note)"
                        style="border:1px solid rgba(255,255,255,0.06);border-top:none;box-shadow:inset 0 -4px 8px rgba(0,0,0,0.4),0 4px 12px rgba(0,0,0,0.6);">
                </button>
            </template>
        </div>
    </div>

    @if($dailyLimit !== -1)
    <div class="px-6 pb-4 text-center text-white/20 text-xs">
        {{ __('app.games.plays_used', ['used' => $dailyPlaysUsed, 'limit' => $dailyLimit]) }}
    </div>
    @endif
</div>

<script>
(function () {

// ── Pre-compute note SVGs (treble clef, C4 octave) ─────────────────────────
// Staff lines (top→bottom in SVG y-coords): y = 18,25,32,39,46
// Treble clef: line 1(bottom)=E4, line2=G4, line3=B4; C4 on ledger below
const STAFF_LINES_Y = [18, 25, 32, 39, 46];
const STAFF_NOTE_CY = { C4:58, D4:53, E4:46, F4:42, G4:39, A4:35, B4:32 };

function buildNoteSVG(name) {
    const cy = STAFF_NOTE_CY[name] ?? 39;
    const cx = 24;
    let s = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 74" width="52" height="74">`;
    STAFF_LINES_Y.forEach(y => {
        s += `<line x1="3" y1="${y}" x2="49" y2="${y}" stroke="rgba(255,255,255,0.68)" stroke-width="1.4"/>`;
    });
    // Ledger line for C4 (one line below staff)
    if (name === 'C4') {
        s += `<line x1="14" y1="${cy}" x2="34" y2="${cy}" stroke="rgba(255,255,255,0.82)" stroke-width="1.5"/>`;
    }
    s += `<ellipse cx="${cx}" cy="${cy}" rx="8.5" ry="6" fill="white"/>`;
    const stemTop = Math.max(8, cy - 30);
    s += `<line x1="${cx+8}" y1="${cy-2}" x2="${cx+8}" y2="${stemTop}" stroke="white" stroke-width="2"/>`;
    s += `</svg>`;
    return s;
}

const _NOTE_SVGS   = {};
const _NOTE_LABELS = { C4:'C', D4:'D', E4:'E', F4:'F', G4:'G', A4:'A', B4:'B' };
['C4','D4','E4','F4','G4','A4','B4'].forEach(n => { _NOTE_SVGS[n] = buildNoteSVG(n); });

const WHITE_KEY_INDEX = { C4:0, D4:1, E4:2, F4:3, G4:4, A4:5, B4:6 };
const PLAYABLE_NOTES  = ['C4','D4','E4','F4','G4','A4','B4'];
const FIELD_H         = 340;
const NOTE_W          = 52;
// Step distance between notes (for variety scoring)
const NOTE_STEP = { C4:0, D4:1, E4:2, F4:3, G4:4, A4:5, B4:6 };

// ── Alpine component ────────────────────────────────────────────────────────
window.noteFallGame = function () {
    const PERSONAL_BEST = {{ (int)$personalBest }};
    const SCORE_URL     = @json(route('games.score', 'note-fall'));
    const STR           = window.GAME_STRINGS || {};

    return {
        str: Object.assign({
            ready:'Ready', playing:'In progress', gameOver:'Game Over',
            level:'Level', streak:'Streak', startGame:'Start Game',
            playAgain:'Play Again', finalScore:'Final Score',
            personalBest:'Personal best:', newBest:'New Personal Best!',
            noteFallDesc:'Notes fall from above. Press the matching piano key before they hit the bottom!'
        }, STR),

        NOTE_SVGS:   _NOTE_SVGS,
        NOTE_LABELS: _NOTE_LABELS,

        limitReached: false,
        gameState: 'idle', mode: 'staff',
        lives: 3, score: 0, streak: 0, maxStreak: 0, level: 1, correctCount: 0,
        fallingNotes: [], nextId: 0,
        isNewBest: false, personalBest: PERSONAL_BEST,
        loopId: null, spawnTimer: null,
        recentNotes: [], // last spawned notes for variety control

        // Per-key state: null | 'correct' | 'wrong' | 'hint'
        keyStates: {
            C4:null, D4:null, E4:null, F4:null, G4:null, A4:null, B4:null,
            'C#4':null, 'D#4':null, 'F#4':null, 'G#4':null, 'A#4':null,
        },

        whiteKeys: [
            {note:'C4'},{note:'D4'},{note:'E4'},
            {note:'F4'},{note:'G4'},{note:'A4'},{note:'B4'},
        ],
        blackKeys: [
            {note:'C#4',leftPct:10.4},
            {note:'D#4',leftPct:24.2},
            {note:'F#4',leftPct:52.6},
            {note:'G#4',leftPct:66.5},
            {note:'A#4',leftPct:80.3},
        ],

        onInit() { lucide.createIcons(); },

        // ── Key visual helpers ──────────────────────────────────────────────
        getWhiteKeyStyle(note) {
            const s = this.keyStates[note];
            if (s === 'correct') return 'background:linear-gradient(to bottom,#6ee7b7,#34d399);';
            if (s === 'wrong')   return 'background:linear-gradient(to bottom,#fca5a5,#f87171);';
            if (s === 'hint')    return 'background:linear-gradient(to bottom,#fde68a,#fbbf24);';
            return 'background:linear-gradient(to bottom,#e8e8e8 0%,#ffffff 40%,#f0f0f0 100%);';
        },

        getBlackKeyStyle(note) {
            const s = this.keyStates[note];
            if (s === 'correct') return 'background:linear-gradient(to bottom,#059669,#047857);';
            if (s === 'wrong')   return 'background:linear-gradient(to bottom,#dc2626,#b91c1c);';
            if (s === 'hint')    return 'background:linear-gradient(to bottom,#d97706,#b45309);';
            return 'background:linear-gradient(to bottom,#2d2d2d 0%,#1a1a1a 60%,#0f0f0f 100%);';
        },

        // Returns the glow CSS class for the current key state
        keyGlowClass(note, isBlack) {
            const s = this.keyStates[note];
            if (s === 'correct') return 'nf-glow-green';
            if (s === 'wrong')   return 'nf-glow-red';
            if (s === 'hint')    return 'nf-glow-amber';
            return '';
        },

        // ── Game speed ─────────────────────────────────────────────────────
        get fallSpeed() { return 0.85 + (this.level - 1) * 0.22; },
        get spawnMs()   { return Math.max(1100, 2600 - (this.level - 1) * 180); },

        // ── Start / Loop ───────────────────────────────────────────────────
        startGame() {
            this.lives = 3; this.score = 0; this.streak = 0; this.maxStreak = 0;
            this.level = 1; this.correctCount = 0; this.isNewBest = false;
            this.fallingNotes = []; this.nextId = 0; this.recentNotes = [];
            this.gameState = 'playing';
            this.startLoop();
            this.scheduleSpawn();
        },

        startLoop() {
            const tick = () => {
                if (this.gameState !== 'playing') return;
                const toRemove = [];

                this.fallingNotes.forEach(n => {
                    if (n.fastFall) {
                        // Fall at 4× normal speed — silently exit (already scored)
                        n.y += n.vy;
                        if (n.y > FIELD_H + 14) toRemove.push(n.id);
                    } else {
                        n.y += this.fallSpeed;
                        if (n.y > FIELD_H + 12) {
                            toRemove.push(n.id);
                            this.streak = 0;
                            this.lives--;
                            if (this.lives <= 0) { setTimeout(() => this.endGame(), 200); }
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

        scheduleSpawn() {
            if (this.gameState !== 'playing') return;
            this.spawnNote();
            this.spawnTimer = setTimeout(() => this.scheduleSpawn(), this.spawnMs);
        },

        spawnNote() {
            // Smart selection: avoid repeating notes within last 2 spawns,
            // and prefer notes ≥3 steps away from the most recent note.
            const recent = this.recentNotes.slice(-2);
            const lastStep = recent.length ? NOTE_STEP[recent[recent.length - 1]] : -99;

            // Candidates at least 3 steps from last note (wide interval variety)
            let pool = PLAYABLE_NOTES.filter(n =>
                Math.abs(NOTE_STEP[n] - lastStep) >= 3 && !recent.includes(n)
            );
            // Fallback: at least 2 steps away and not identical to last
            if (pool.length < 2) {
                pool = PLAYABLE_NOTES.filter(n =>
                    Math.abs(NOTE_STEP[n] - lastStep) >= 2 && n !== recent[recent.length - 1]
                );
            }
            // Last resort: anything except the very last note
            if (pool.length === 0) {
                pool = PLAYABLE_NOTES.filter(n => n !== recent[recent.length - 1]);
            }

            const note = pool[Math.floor(Math.random() * pool.length)] || PLAYABLE_NOTES[0];
            this.recentNotes.push(note);
            if (this.recentNotes.length > 4) this.recentNotes.shift();

            const fieldW = this.$refs.field?.offsetWidth || 600;
            const margin = 12;
            const x = margin + Math.random() * (fieldW - NOTE_W - margin * 2);
            this.fallingNotes.push({ id: this.nextId++, note, y: -74, x, fastFall: false, vy: 0 });
        },

        // ── Key press ──────────────────────────────────────────────────────
        pressKey(note) {
            if (this.gameState !== 'playing') return;
            if (window.HarmonivaAudio) HarmonivaAudio.playNote(note, 0.5);

            // Match any active (non-fast-falling) note with this name on screen
            const idx = this.fallingNotes.findIndex(
                n => n.note === note && !n.fastFall
            );

            if (idx !== -1) {
                // ✓ Correct — trigger 4× speed fall, exits silently (already scored)
                const n = this.fallingNotes[idx];
                n.fastFall = true;
                n.vy = this.fallSpeed * 4;

                this.streak++;
                this.maxStreak = Math.max(this.maxStreak, this.streak);
                this.correctCount++;
                this.score += 100 + this.streak * 15 + this.level * 10;
                if (this.correctCount % 8 === 0) this.level++;

                this._flashKey(note, 'correct', 280);
            } else {
                // ✗ Wrong — flash red + hint the closest active note's key
                this._flashKey(note, 'wrong', 280);

                const active = this.fallingNotes.filter(n => !n.fastFall);
                if (active.length > 0) {
                    const closest = active.reduce(
                        (b, n) => n.y > b.y ? n : b,
                        { y: -Infinity, note: null }
                    );
                    if (closest.note && closest.note !== note) {
                        this._flashKey(closest.note, 'hint', 300);
                    }
                }
            }
        },

        _flashKey(note, state, ms) {
            this.keyStates[note] = state;
            setTimeout(() => { this.keyStates[note] = null; }, ms);
        },

        // ── End game ───────────────────────────────────────────────────────
        endGame() {
            cancelAnimationFrame(this.loopId);
            clearTimeout(this.spawnTimer);
            this.gameState = 'gameover';
            this.isNewBest = this.score > this.personalBest;
            if (this.isNewBest) this.personalBest = this.score;

            this.$nextTick(() => {
                this.launchConfetti(this.score);
                this.playApplause(this.score);
                lucide.createIcons();
            });

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            fetch(SCORE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({
                    score: this.score, max_streak: this.maxStreak,
                    level_reached: this.level,
                    metadata: { correct: this.correctCount, mode: this.mode },
                }),
            })
            .then(r => r.json())
            .then(d => { if (d.can_play_again === false) this.limitReached = true; })
            .catch(() => {});
        },

        resetGame() {
            cancelAnimationFrame(this.loopId);
            clearTimeout(this.spawnTimer);
            this.fallingNotes = [];
            if (this.limitReached) { window.location.reload(); return; }
            this.gameState = 'idle';
        },

        // ── Full-screen confetti (position:fixed) ──────────────────────────
        launchConfetti(score) {
            const DURATION  = 3000; // ms
            const FADE_FROM = 2200; // ms — start fading
            const count = Math.min(280, Math.max(70, Math.floor(score / 3)));

            const canvas = document.createElement('canvas');
            canvas.style.cssText =
                'position:fixed;top:0;left:0;width:100%;height:100%;' +
                'z-index:99999;pointer-events:none;';
            canvas.width  = window.innerWidth;
            canvas.height = window.innerHeight;
            document.body.appendChild(canvas);

            const COLORS = [
                '#fbbf24','#34d399','#60a5fa','#f472b6',
                '#a78bfa','#fb923c','#f87171','#38bdf8',
                '#4ade80','#facc15','#e879f9',
            ];

            // Mix of burst-from-center and rain-from-top
            const particles = Array.from({ length: count }, (_, i) => {
                const burst = i < count * 0.35;
                return {
                    x:     burst ? canvas.width / 2 + (Math.random() - 0.5) * 160
                                 : Math.random() * canvas.width,
                    y:     burst ? canvas.height * 0.38 + (Math.random() - 0.5) * 60
                                 : -10 - Math.random() * 180,
                    vx:    burst ? (Math.random() - 0.5) * 16 : (Math.random() - 0.5) * 3.5,
                    vy:    burst ? -9 + Math.random() * 7    : 1.8 + Math.random() * 4.5,
                    w:     6 + Math.random() * 9,
                    h:     3 + Math.random() * 5,
                    color: COLORS[Math.floor(Math.random() * COLORS.length)],
                    rot:   Math.random() * 360,
                    rs:    (Math.random() - 0.5) * 8,
                    alpha: 1,
                };
            });

            const ctx = canvas.getContext('2d');
            const t0  = performance.now();

            const frame = (now) => {
                const elapsed = now - t0;
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                let alive = 0;
                particles.forEach(p => {
                    p.x  += p.vx;
                    p.y  += p.vy;
                    p.vy += 0.13; // gravity
                    p.rot += p.rs;
                    if (elapsed > FADE_FROM) {
                        p.alpha -= 0.012;
                    }
                    if (p.alpha <= 0) return;
                    alive++;
                    ctx.save();
                    ctx.globalAlpha = p.alpha;
                    ctx.translate(p.x, p.y);
                    ctx.rotate(p.rot * Math.PI / 180);
                    ctx.fillStyle = p.color;
                    ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
                    ctx.restore();
                });

                if (elapsed < DURATION && alive > 0) {
                    requestAnimationFrame(frame);
                } else {
                    if (canvas.parentNode) canvas.parentNode.removeChild(canvas);
                }
            };
            requestAnimationFrame(frame);
        },

        // ── Applause (realistic clap bursts) ──────────────────────────────
        playApplause(score) {
            try {
                const AC = window.AudioContext || window.webkitAudioContext;
                if (!AC) return;
                const ctx = new AC();

                const applaUseDuration = Math.min(4.0, 2.2 + score / 220);
                // Density grows with score: more people clapping
                const clapCount = Math.floor(18 + score / 55);

                // Master envelope
                const master = ctx.createGain();
                master.gain.setValueAtTime(0, ctx.currentTime);
                master.gain.linearRampToValueAtTime(0.9, ctx.currentTime + 0.35);
                master.gain.setValueAtTime(0.9, ctx.currentTime + applaUseDuration - 0.6);
                master.gain.linearRampToValueAtTime(0, ctx.currentTime + applaUseDuration);
                master.connect(ctx.destination);

                // Create individual hand-clap noise bursts
                for (let i = 0; i < clapCount; i++) {
                    const t  = ctx.currentTime + Math.random() * (applaUseDuration * 0.88);
                    const bd = 0.04 + Math.random() * 0.1; // burst duration 40-140 ms

                    const bufLen = Math.ceil(ctx.sampleRate * (bd + 0.12));
                    const buf = ctx.createBuffer(2, bufLen, ctx.sampleRate);
                    for (let ch = 0; ch < 2; ch++) {
                        const d = buf.getChannelData(ch);
                        for (let j = 0; j < bufLen; j++) d[j] = Math.random() * 2 - 1;
                    }

                    const src = ctx.createBufferSource();
                    src.buffer = buf;

                    // High-pass: remove sub-clap rumble
                    const hp = ctx.createBiquadFilter();
                    hp.type = 'highpass';
                    hp.frequency.value = 900 + Math.random() * 600;

                    // Peak at hand-clap fundamental (~2-3 kHz)
                    const pk = ctx.createBiquadFilter();
                    pk.type = 'peaking';
                    pk.frequency.value = 2000 + Math.random() * 1200;
                    pk.gain.value = 7;

                    // Per-clap envelope: very fast attack, exponential decay
                    const env = ctx.createGain();
                    env.gain.setValueAtTime(0, t);
                    env.gain.linearRampToValueAtTime(0.28, t + 0.006);
                    env.gain.exponentialRampToValueAtTime(0.0005, t + bd);

                    src.connect(hp);
                    hp.connect(pk);
                    pk.connect(env);
                    env.connect(master);
                    src.start(t);
                    src.stop(t + bd + 0.05);
                }

                // Celebratory arpeggio on top
                if (window.HarmonivaAudio && score > 60) {
                    const notes = score > 500 ? ['C4','E4','G4','C5','E5']
                                : score > 200 ? ['C4','E4','G4','C5']
                                :               ['C4','E4','G4'];
                    setTimeout(() => HarmonivaAudio.playSequence(notes, 115, 0.85), 180);
                }
            } catch (e) {}
        },
    };
};

})();
</script>
@endif
