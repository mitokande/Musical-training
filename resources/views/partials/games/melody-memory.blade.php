{{-- Melody Memory — v2: 3-level edition with reference note, octave-aware keyboard, 10-streak progression --}}

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
            <a href="{{ route('profile.edit') }}"
               class="inline-block px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white font-semibold text-sm">
                {{ __('app.games.upgrade_premium') }}
            </a>
            @endif
        @endguest
    </div>
@else

<style>
    .mm-white-key { transition: background 0.07s ease, box-shadow 0.07s ease; }
    .mm-black-key { transition: background 0.07s ease, box-shadow 0.07s ease; }
    .mm-white-key:not(.mm-active):hover {
        background: linear-gradient(to bottom, #ede9fe 0%, #f5f3ff 100%) !important;
    }
    .mm-black-key:not(.mm-active):hover {
        background: linear-gradient(to bottom, #4c1d95 0%, #5b21b6 100%) !important;
    }
    @keyframes mmGlowPurple {
        0%,100% { box-shadow: 0 0 10px 4px rgba(167,139,250,0.7); }
        40%      { box-shadow: 0 0 28px 10px rgba(167,139,250,1), 0 0 50px 16px rgba(167,139,250,0.3); }
    }
    @keyframes mmGlowRed {
        0%,100% { box-shadow: 0 0 10px 4px rgba(248,113,113,0.7); }
        40%      { box-shadow: 0 0 28px 10px rgba(248,113,113,1), 0 0 50px 16px rgba(248,113,113,0.3); }
    }
    @keyframes mmGlowAmber {
        0%,100% { box-shadow: 0 0 10px 4px rgba(251,191,36,0.7); }
        40%      { box-shadow: 0 0 28px 10px rgba(251,191,36,1), 0 0 50px 16px rgba(251,191,36,0.3); }
    }
    .mm-glow-purple { animation: mmGlowPurple 0.3s ease-out; }
    .mm-glow-red    { animation: mmGlowRed    0.3s ease-out; }
    .mm-glow-amber  { animation: mmGlowAmber  0.3s ease-out; }
    @keyframes mmUnlockPop {
        0%   { opacity:0; transform:scale(0.7); }
        60%  { opacity:1; transform:scale(1.05); }
        100% { opacity:1; transform:scale(1); }
    }
    .mm-unlock-pop { animation: mmUnlockPop 0.45s ease-out forwards; }
</style>

<div x-data="melodyMemoryGame()" x-init="onInit()" class="game-surface rounded-2xl overflow-hidden">

    {{-- ─── Header ───────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-r from-purple-600/20 to-indigo-600/20 border-b border-white/10 py-4 px-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('games.index') }}"
                   class="w-10 h-10 rounded-xl bg-white border border-white/20 flex items-center justify-center text-red-600 font-bold hover:bg-white/90 transition-all flex-shrink-0">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center">
                    <i data-lucide="music" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <div class="text-white font-bold text-sm">{{ __('app.games.melody_memory.title') }}</div>
                    <div class="text-white/40 text-xs" x-text="headerStatus"></div>
                </div>
            </div>
            <div class="flex items-center gap-4" x-show="phase !== 'idle'">
                <div class="text-center">
                    <div class="text-white/40 text-xs">{{ __('app.games.status_level') }}</div>
                    <div class="text-white font-black text-xl tabular-nums" x-text="currentLevel"></div>
                </div>
                <div class="text-center">
                    <div class="text-white/40 text-xs">{{ __('app.games.melody_memory.score') }}</div>
                    <div class="text-white font-black text-xl tabular-nums" x-text="sessionScore"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── IDLE: Level selection ─────────────────────────────────────────────── --}}
    <div x-show="phase === 'idle'" class="flex flex-col items-center justify-center gap-5 p-8" style="min-height:420px;">

        {{-- Description --}}
        <div class="flex items-center gap-3 w-full max-w-sm">
            <span class="text-white/10 text-4xl font-black select-none flex-shrink-0 leading-none">♩</span>
            <p class="text-white/40 text-sm text-center flex-1">{{ __('app.games.melody_memory.instructions_intro') }}<br>
               {{ __('app.games.melody_memory.instructions_get') }} <span class="text-purple-400 font-semibold">{{ __('app.games.melody_memory.instructions_streak_count') }}</span> {{ __('app.games.melody_memory.instructions_unlock_next') }} <span class="text-red-400 font-semibold">{{ __('app.games.melody_memory.instructions_lives_count') }}</span>.</p>
            <span class="text-white/10 text-4xl font-black select-none flex-shrink-0 leading-none">♫</span>
        </div>

        {{-- Level info cards (tıklanabilir, açık olanlar) --}}
        <div class="flex gap-2 justify-center flex-wrap">
            <template x-for="lvl in [1,2,3]" :key="lvl">
                <button @click="isLevelUnlocked(lvl) && startLevel(lvl)"
                        :disabled="!isLevelUnlocked(lvl)"
                        class="flex flex-col items-center px-4 py-2.5 rounded-xl border transition-all"
                        :class="lvl === recommendedLevel
                            ? 'border-purple-400/60 bg-purple-400/8 hover:bg-purple-400/14 cursor-pointer'
                            : isLevelUnlocked(lvl)
                                ? 'border-white/10 opacity-70 hover:opacity-100 hover:border-white/20 cursor-pointer'
                                : 'border-white/8 opacity-35 cursor-not-allowed'">
                    <div class="flex items-center gap-1.5 mb-0.5">
                        <span class="text-xs font-black text-white/70" x-text="'{{ __('app.games.status_level') }}' + ' ' + lvl"></span>
                        <i x-show="!isLevelUnlocked(lvl)" data-lucide="lock" class="w-2.5 h-2.5 text-white/30"></i>
                    </div>
                    <span class="text-[9px] text-white/35" x-text="['C4 – B4','G3 – G5','C4–B4 ♯♭'][lvl-1]"></span>
                </button>
            </template>
        </div>

        @if($personalBest > 0)
        <div class="flex items-center gap-1.5 text-white/30 text-sm">
            <i data-lucide="trophy" class="w-4 h-4 text-amber-400"></i>
            {{ __('app.games.melody_memory.best_label') }} <span class="text-white font-bold ml-1">{{ number_format($personalBest) }} {{ __('app.games.melody_memory.pts_suffix') }}</span>
        </div>
        @endif

        {{-- Start button --}}
        <button @click="startLevel(recommendedLevel)"
                class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform">
            <span class="flex items-center gap-2">
                <i data-lucide="play" class="w-4 h-4 fill-current"></i>
                <span x-text="'{{ __('app.games.melody_memory.start_level') }}' + ' ' + recommendedLevel"></span>
            </span>
        </button>

        <p class="text-white/20 text-xs -mt-2" x-show="maxUnlockedLevel > 1">
            {{ __('app.games.melody_memory.click_level_hint') }}
        </p>
    </div>

    {{-- ─── PLAYING PHASES (reference / listening / input / correct / wrong) ─── --}}
    <div x-show="['reference','listening','input','correct','wrong'].includes(phase)" class="p-4 sm:p-5 sm:mx-[10%]">

        {{-- Reference note banner --}}
        <div class="flex items-center justify-between mb-3 px-3 py-2.5 rounded-xl transition-all"
             :class="phase==='reference' ? 'bg-purple-500/15 border border-purple-500/35' : 'bg-white/4 border border-white/8'">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                     :class="phase==='reference' ? 'bg-purple-500/30' : 'bg-white/8'">
                    <i data-lucide="music-2" class="w-4 h-4 text-purple-400"></i>
                </div>
                <div>
                    <div class="text-white/40 text-[10px] uppercase tracking-wider leading-tight">{{ __('app.games.melody_memory.reference_note_label') }}</div>
                    <div class="text-white font-black text-lg leading-tight tracking-wide"
                         x-text="referenceNote ? window.HarmonivaNotation.toDisplaySymbol(referenceNote.noteName) : '—'"></div>
                </div>
            </div>
            <button @click="replayReferenceNote()"
                    :disabled="phase === 'listening'"
                    class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-purple-400 border border-purple-500/30 hover:bg-purple-500/15 transition text-xs disabled:opacity-30 disabled:cursor-not-allowed">
                <i data-lucide="volume-2" class="w-3.5 h-3.5"></i>
                <span class="hidden sm:inline">{{ __('app.games.melody_memory.play_button') }}</span>
            </button>
        </div>

        {{-- Lives + Streak row --}}
        <div class="flex items-center justify-between mb-3">
            {{-- Lives --}}
            <div class="flex items-center gap-1.5">
                <span class="text-white/35 text-[10px] uppercase tracking-wider">{{ __('app.games.melody_memory.lives_label') }}</span>
                <div class="flex gap-0.5">
                    <template x-for="i in [1,2,3]" :key="i">
                        <span class="text-base leading-none transition-all duration-200"
                              :class="i <= lives ? 'text-red-400' : 'text-white/12'">♥</span>
                    </template>
                </div>
            </div>
            {{-- Streak --}}
            <div class="flex items-center gap-1.5">
                <div class="flex gap-0.5">
                    <template x-for="dot in streakDots" :key="dot">
                        <div class="w-2 h-2 rounded-full transition-all duration-200"
                             :class="dot <= consecutiveStreak ? 'bg-purple-400 scale-110' : 'bg-white/12'"></div>
                    </template>
                </div>
                <span class="text-white/50 text-xs font-bold tabular-nums"
                      x-text="consecutiveStreak + ' / 8'"></span>
            </div>
        </div>

        {{-- Melody dots --}}
        <div class="flex justify-center gap-2 mb-3 min-h-[14px]">
            <template x-for="(note, i) in sequence" :key="i">
                <div class="w-3 h-3 rounded-full transition-all duration-150"
                     :class="{
                         'bg-purple-400 scale-125': phase === 'listening' && highlightIndex === i,
                         'bg-green-400': ['input','correct'].includes(phase) && userInput.length > i,
                         'bg-white/15': !(phase === 'listening' && highlightIndex === i) && !((['input','correct'].includes(phase)) && userInput.length > i),
                     }">
                </div>
            </template>
        </div>

        {{-- User input chips --}}
        <div class="flex justify-center gap-1.5 mb-3 min-h-[26px] flex-wrap">
            <template x-for="(item, i) in userInput" :key="i">
                <div class="px-2.5 py-0.5 rounded-md bg-green-500/20 border border-green-500/40 text-green-300 text-xs font-bold"
                     x-text="window.HarmonivaNotation.toDisplaySymbol(item.noteName)"></div>
            </template>
            {{-- Show expected note on wrong --}}
            <template x-if="phase === 'wrong' && wrongNoteInfo">
                <div class="flex items-center gap-1.5">
                    <span class="px-2.5 py-0.5 rounded-md bg-red-500/20 border border-red-500/40 text-red-300 text-xs font-bold"
                          x-text="window.HarmonivaNotation.toDisplaySymbol(wrongNoteInfo.got)"></span>
                    <span class="text-white/30 text-xs">→</span>
                    <span class="px-2.5 py-0.5 rounded-md bg-amber-500/20 border border-amber-500/40 text-amber-300 text-xs font-bold"
                          x-text="window.HarmonivaNotation.toDisplaySymbol(wrongNoteInfo.expected)"></span>
                </div>
            </template>
        </div>

        {{-- Status text --}}
        <div class="text-center mb-3 h-6 flex items-center justify-center">
            <span x-show="phase === 'reference'" class="text-purple-300 text-sm font-medium flex items-center gap-1.5">
                <i data-lucide="headphones" class="w-4 h-4"></i> {{ __('app.games.melody_memory.listen_reference_note') }}
            </span>
            <span x-show="phase === 'listening'" class="text-purple-300 text-sm font-medium flex items-center gap-1.5">
                <i data-lucide="ear" class="w-4 h-4"></i> {{ __('app.games.listen_carefully') }}
            </span>
            <span x-show="phase === 'input'" class="text-white/60 text-sm flex items-center gap-1.5">
                <i data-lucide="music" class="w-4 h-4 text-purple-400"></i>
                {{ __('app.games.status_your_turn') }}&nbsp;(<span x-text="userInput.length"></span>/<span x-text="sequence.length"></span>)
            </span>
            <span x-show="phase === 'correct'" class="text-green-400 text-sm font-bold flex items-center gap-1.5">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                {{ __('app.games.status_correct') }} <span x-show="consecutiveStreak > 1" x-text="consecutiveStreak + ' {{ __('app.games.melody_memory.in_a_row_suffix') }}'"></span>
            </span>
            <span x-show="phase === 'wrong'" class="text-red-400 text-sm flex items-center gap-1.5">
                <i data-lucide="x-circle" class="w-4 h-4"></i>
                <span x-text="lives > 0 ? '{{ __('app.games.melody_memory.wrong_prefix') }} ' + lives + ' {{ __('app.games.melody_memory.lives_left_suffix') }}' : '{{ __('app.games.melody_memory.wrong_prefix') }} {{ __('app.games.melody_memory.no_lives_left') }}'"></span>
            </span>
        </div>

        {{-- ─── Piano keyboard ─────────────────────────────────────────────── --}}
        {{-- NOTE: :style (string) overrides static style in Alpine v3, so use :class for opacity/pointer-events --}}
        <div class="relative select-none transition-opacity duration-150"
             style="height:118px;background:#08080e;border-top:2px solid rgba(255,255,255,0.08);border-radius:0 0 12px 12px;"
             :class="['reference','listening','correct'].includes(phase) ? 'opacity-40 pointer-events-none' : (phase==='wrong' ? 'opacity-50 pointer-events-none' : '')"
        >

            {{-- White keys --}}
            <div class="flex h-full px-1 gap-px">
                <template x-for="wk in whiteKeys" :key="wk.keyId">
                    <button @click="pressKey(wk.pc)"
                            :disabled="phase !== 'input'"
                            @mousedown.prevent
                            :class="['mm-white-key flex-1 rounded-b-xl cursor-pointer relative z-0 disabled:cursor-default',
                                     keyStates[wk.keyId] ? 'mm-active' : '',
                                     keyGlowClass(wk.keyId)]"
                            :style="getWhiteKeyStyle(wk.keyId)"
                            style="min-width:0;border:1px solid rgba(0,0,0,0.13);border-top:none;">
                        <span class="absolute bottom-1.5 left-0 right-0 text-center text-[11px] font-bold pointer-events-none select-none"
                              :class="keyStates[wk.keyId]==='correct'?'text-purple-800':keyStates[wk.keyId]==='wrong'?'text-red-900':'text-black/30'"
                              x-text="wk.pc"></span>
                    </button>
                </template>
            </div>

            {{-- Black keys --}}
            <template x-for="bk in visibleBlackKeys" :key="bk.keyId">
                <button @click.stop="pressKey(bk.pcs[0])"
                        :disabled="phase !== 'input'"
                        @mousedown.prevent
                        :class="['mm-black-key absolute top-0 rounded-b-lg cursor-pointer z-10 disabled:cursor-default',
                                 keyStates[bk.keyId] ? 'mm-active' : '',
                                 keyGlowClass(bk.keyId)]"
                        :style="'left:'+bk.leftPct+'%;width:9%;height:64%;'+getBlackKeyStyle(bk.keyId)"
                        style="border:1px solid rgba(255,255,255,0.06);border-top:none;box-shadow:inset 0 -4px 8px rgba(0,0,0,0.4),0 4px 12px rgba(0,0,0,0.6);">
                    <span class="absolute bottom-1 left-0 right-0 text-center text-[7px] font-bold pointer-events-none select-none leading-tight"
                          :class="keyStates[bk.keyId]==='correct'?'text-purple-300':keyStates[bk.keyId]==='wrong'?'text-red-300':'text-white/35'"
                          x-text="window.HarmonivaNotation.toDisplaySymbol(bk.label)"></span>
                </button>
            </template>
        </div>

        {{-- Action buttons --}}
        <div class="flex items-center justify-center gap-3 mt-3">
            <button @click="replayMelody()"
                    :disabled="['listening','reference'].includes(phase)"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/50 hover:text-white hover:bg-white/10 transition text-xs disabled:opacity-30 disabled:cursor-not-allowed">
                <i data-lucide="repeat" class="w-3.5 h-3.5"></i> {{ __('app.games.replay_melody') }}
            </button>
            <button @click="endSession()"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/40 hover:text-white hover:bg-white/10 transition text-xs">
                <i data-lucide="square" class="w-3.5 h-3.5"></i> {{ __('app.games.melody_memory.end_button') }}
            </button>
        </div>
    </div>

    {{-- ─── UNLOCK SCREEN ────────────────────────────────────────────────────── --}}
    <div x-show="phase === 'unlock'"
         class="p-6 sm:mx-[10%] flex flex-col items-center gap-5 text-center"
         style="min-height:300px;justify-content:center;">
        <div class="mm-unlock-pop">
            <div class="text-5xl mb-3">🎉</div>
            <div class="text-white/50 text-sm mb-1">{{ __('app.games.status_level') }} <span x-text="currentLevel"></span> {{ __('app.games.melody_memory.complete_suffix') }}</div>
            <div class="text-3xl font-black text-purple-300 mb-1">
                {{ __('app.games.status_level') }} <span x-text="currentLevel + 1"></span> {{ __('app.games.melody_memory.unlocked_suffix') }}
            </div>
            <div class="text-white/40 text-sm mb-5">{{ __('app.games.melody_memory.unlock_congrats') }}</div>
            <div class="flex items-center justify-center gap-3">
                <button @click="continueToNextLevel()"
                        class="px-6 py-3 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform flex items-center gap-2">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    {{ __('app.games.melody_memory.continue_to_level') }} <span x-text="currentLevel + 1"></span>
                </button>
                <button @click="goToIdle()"
                        class="px-6 py-3 rounded-xl border border-white/15 text-white/50 font-semibold text-sm hover:bg-white/5 transition">
                    {{ __('app.games.melody_memory.levels_button') }}
                </button>
            </div>
        </div>
    </div>

    {{-- ─── GAME OVER / SESSION END ──────────────────────────────────────────── --}}
    <div x-show="phase === 'gameover'"
         class="p-6 sm:mx-[10%] flex flex-col items-center gap-4 text-center"
         style="min-height:300px;justify-content:center;">
        <div class="text-5xl" x-text="livesOut ? '💔' : '🎶'"></div>
        <div>
            <div class="text-white/40 text-sm mb-1"
                 x-text="(livesOut ? '{{ __('app.games.melody_memory.no_lives_left_level_prefix') }} ' : '{{ __('app.games.melody_memory.session_over_level_prefix') }} ') + currentLevel"></div>
            <div class="text-5xl font-black text-white tabular-nums" x-text="sessionScore"></div>
            <div class="text-white/30 text-xs mt-1">{{ __('app.games.points') }}</div>
        </div>
        <div class="flex items-center gap-5 text-sm text-white/50">
            <span><span class="text-green-400 font-bold" x-text="totalCorrect"></span> {{ __('app.games.correct_label') }}</span>
            <span><span class="text-red-400 font-bold" x-text="totalWrong"></span> {{ __('app.games.melody_memory.wrong_label') }}</span>
            <span>{{ __('app.games.best_streak') }} <span class="text-purple-400 font-bold" x-text="bestStreak"></span></span>
        </div>
        <div x-show="isNewBest"
             class="px-4 py-2 rounded-full bg-yellow-400/20 border border-yellow-400/30 text-yellow-300 text-sm font-bold">
            {{ __('app.games.new_personal_best') }} 🏆
        </div>
        <div class="flex items-center gap-3">
            <button @click="limitReached ? window.location.reload() : startLevel(currentLevel)"
                    class="px-6 py-3 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-bold text-sm hover:scale-105 active:scale-95 transition-transform flex items-center gap-2">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span x-text="limitReached ? '{{ __('app.games.daily_limit_title') }} →' : '{{ __('app.games.play_again') }}'"></span>
            </button>
            <button @click="goToIdle()"
                    class="px-6 py-3 rounded-xl border border-white/15 text-white/50 font-semibold text-sm hover:bg-white/5 transition">
                {{ __('app.games.melody_memory.levels_button') }}
            </button>
        </div>
    </div>

    {{-- Daily plays footer --}}
    @if($perTypeLimit !== null && $perTypeLimit !== -1)
    <div class="px-6 pb-4 text-center text-white/20 text-xs">
        {{ __('app.games.plays_used', ['used' => $dailyPlaysUsed, 'limit' => $perTypeLimit]) }}
        @if($totalLimit !== null && $totalLimit !== -1)
         · {{ __('app.games.plays_used_total', ['used' => $totalPlaysUsed, 'total' => $totalLimit]) }}
        @endif
    </div>
    @endif
</div>

<script>
function melodyMemoryGame() {
    const PERSONAL_BEST       = {{ (int)$personalBest }};
    const SCORE_URL           = @json($scoreUrl);
    const MAX_UNLOCKED_LEVEL  = {{ (int)($maxUnlockedLevel ?? 1) }};

    // ── Level configuration ───────────────────────────────────────────────────
    const LEVEL_CFG = {
        1: {
            pool:         ['C4','D4','E4','F4','G4','A4','B4'],
            kbMode:       'natural-7',
            streakNeeded: 8,
        },
        2: {
            pool:         ['G3','A3','B3','C4','D4','E4','F4','G4','A4','B4','C5','D5','E5','F5','G5'],
            kbMode:       'natural-7-octave-aware',
            streakNeeded: 8,
        },
        3: {
            pool:         ['C4','C#4','D4','D#4','E4','F4','F#4','G4','G#4','A4','A#4','B4'],
            kbMode:       'chromatic-12',
            streakNeeded: 8,
        },
    };

    // ── Canonical note data ───────────────────────────────────────────────────
    const NOTE_DB = {
        'G3':  { noteName:'G',   octave:3, acc:null, midi:55 },
        'A3':  { noteName:'A',   octave:3, acc:null, midi:57 },
        'B3':  { noteName:'B',   octave:3, acc:null, midi:59 },
        'C4':  { noteName:'C',   octave:4, acc:null, midi:60 },
        'C#4': { noteName:'C#',  octave:4, acc:'#',  midi:61 },
        'D4':  { noteName:'D',   octave:4, acc:null, midi:62 },
        'D#4': { noteName:'D#',  octave:4, acc:'#',  midi:63 },
        'E4':  { noteName:'E',   octave:4, acc:null, midi:64 },
        'F4':  { noteName:'F',   octave:4, acc:null, midi:65 },
        'F#4': { noteName:'F#',  octave:4, acc:'#',  midi:66 },
        'G4':  { noteName:'G',   octave:4, acc:null, midi:67 },
        'G#4': { noteName:'G#',  octave:4, acc:'#',  midi:68 },
        'A4':  { noteName:'A',   octave:4, acc:null, midi:69 },
        'A#4': { noteName:'A#',  octave:4, acc:'#',  midi:70 },
        'B4':  { noteName:'B',   octave:4, acc:null, midi:71 },
        'C5':  { noteName:'C',   octave:5, acc:null, midi:72 },
        'D5':  { noteName:'D',   octave:5, acc:null, midi:74 },
        'E5':  { noteName:'E',   octave:5, acc:null, midi:76 },
        'F5':  { noteName:'F',   octave:5, acc:null, midi:77 },
        'G5':  { noteName:'G',   octave:5, acc:null, midi:79 },
    };

    // ── PC → keyId map (for keyboard glow) ───────────────────────────────────
    const PC_TO_KEYID = {
        'C':'C','D':'D','E':'E','F':'F','G':'G','A':'A','B':'B',
        'C#':'Cs','Db':'Cs','D#':'Ds','Eb':'Ds',
        'F#':'Fs','Gb':'Fs','G#':'Gs','Ab':'Gs','A#':'As','Bb':'As',
    };

    const WHITE_KEYS = [
        {pc:'C',keyId:'C'},{pc:'D',keyId:'D'},{pc:'E',keyId:'E'},
        {pc:'F',keyId:'F'},{pc:'G',keyId:'G'},{pc:'A',keyId:'A'},{pc:'B',keyId:'B'},
    ];
    const BLACK_KEYS = [
        {pcs:['C#','Db'], keyId:'Cs', label:'C#', leftPct:10.4},
        {pcs:['D#','Eb'], keyId:'Ds', label:'D#', leftPct:24.2},
        {pcs:['F#','Gb'], keyId:'Fs', label:'F#', leftPct:52.6},
        {pcs:['G#','Ab'], keyId:'Gs', label:'G#', leftPct:66.5},
        {pcs:['A#','Bb'], keyId:'As', label:'A#', leftPct:80.3},
    ];

    return {
        // ── Localized display strings ───────────────────────────────────────────
        str: {
            ready:         @json(__('app.games.status_ready')),
            referenceNote: @json(__('app.games.melody_memory.reference_note_label')),
            listening:     @json(__('app.games.status_listening')),
            yourTurn:      @json(__('app.games.status_your_turn')),
            correct:       @json(__('app.games.status_correct')),
            wrong:         @json(__('app.games.melody_memory.wrong_status')),
            levelUnlocked: @json(__('app.games.melody_memory.level_unlocked_status')),
            sessionOver:   @json(__('app.games.melody_memory.session_over_status')),
        },

        // ── Persistent state ──────────────────────────────────────────────────
        maxUnlockedLevel: MAX_UNLOCKED_LEVEL,
        personalBest:     PERSONAL_BEST,

        // ── Game phase ────────────────────────────────────────────────────────
        // idle | reference | listening | input | correct | wrong | unlock | gameover
        phase: 'idle',
        currentLevel: 1,
        limitReached: false,
        isNewBest: false,

        // ── Session stats ─────────────────────────────────────────────────────
        sessionScore:      0,
        consecutiveStreak: 0,
        bestStreak:        0,
        totalCorrect:      0,
        totalWrong:        0,

        // ── Lives ─────────────────────────────────────────────────────────────
        lives:    3,     // remaining lives; 0 = game over
        livesOut: false, // true when game ended due to 0 lives

        // ── Simon Says sequence (grows by 1 note each correct round) ──────────
        sequence:      [],   // [{noteName, octave, acc, midi, key}] — the growing melody
        round:         0,    // = sequence.length after _nextRound() runs
        referenceNote: null, // fixed for the level session
        showRefNote:   true, // play reference note before the next melody?

        // ── Input state ───────────────────────────────────────────────────────
        userInput:      [],  // [{noteName, acc, key}]
        highlightIndex: -1,
        wrongNoteInfo:  null,
        _playingFlag:   false,

        // ── Keyboard state ────────────────────────────────────────────────────
        keyStates: {C:null,D:null,E:null,F:null,G:null,A:null,B:null,Cs:null,Ds:null,Fs:null,Gs:null,As:null},
        whiteKeys: WHITE_KEYS,
        blackKeys: BLACK_KEYS,

        // ── Computed getters ──────────────────────────────────────────────────
        get streakNeeded()     { return LEVEL_CFG[this.currentLevel]?.streakNeeded ?? null; },
        get kbMode()           { return LEVEL_CFG[this.currentLevel]?.kbMode ?? 'natural-7'; },
        get recommendedLevel() { return Math.min(3, this.maxUnlockedLevel); },
        get streakDots() {
            const n = this.streakNeeded;
            return n ? Array.from({length:n}, (_,i) => i+1) : [];
        },
        get visibleBlackKeys() { return BLACK_KEYS; },
        get headerStatus() {
            const map = {
                idle: this.str.ready, reference: this.str.referenceNote, listening: this.str.listening,
                input: this.str.yourTurn, correct: this.str.correct, wrong: this.str.wrong,
                unlock: this.str.levelUnlocked, gameover: this.str.sessionOver,
            };
            return map[this.phase] || '';
        },

        // ── Init ──────────────────────────────────────────────────────────────
        onInit() { lucide.createIcons(); },

        isLevelUnlocked(lvl) { return lvl === 1 || this.maxUnlockedLevel >= lvl; },

        // ── Start a level ─────────────────────────────────────────────────────
        startLevel(level) {
            if (!this.isLevelUnlocked(level)) return;
            if (window.HarmonivaAudio) HarmonivaAudio.warmup();
            this.currentLevel      = level;
            this.consecutiveStreak = 0;
            this.bestStreak        = 0;
            this.sessionScore      = 0;
            this.totalCorrect      = 0;
            this.totalWrong        = 0;
            this.isNewBest         = false;
            this.limitReached      = false;
            this.lives             = 3;
            this.livesOut          = false;
            this.sequence          = [];
            this.round             = 0;
            this.showRefNote       = true;
            this.referenceNote     = this._pickRefNoteObj(LEVEL_CFG[level].pool);
            this._nextRound();
        },

        // ── Simon Says: add one note to the growing sequence ──────────────────
        _nextRound() {
            this.round++;
            this.userInput      = [];
            this.wrongNoteInfo  = null;
            this.highlightIndex = -1;
            this._playingFlag   = false;

            // Pick next note — avoid immediate repeat
            const pool      = LEVEL_CFG[this.currentLevel].pool;
            const lastKey   = this.sequence.length > 0 ? this.sequence[this.sequence.length - 1].key : null;
            const from      = pool.filter(k => k !== lastKey);
            const newKey    = (from.length > 0 ? from : pool)[Math.floor(Math.random() * (from.length || pool.length))];
            this.sequence   = [...this.sequence, { ...NOTE_DB[newKey], key: newKey }];

            if (this.showRefNote) {
                this.showRefNote = false;
                this.phase = 'reference';
                setTimeout(() => this._playReferenceAndMelody(), 350);
            } else {
                setTimeout(() => this._playMelodySeq(), 350);
            }
        },

        async _playReferenceAndMelody() {
            if (this.phase !== 'reference' || this._playingFlag) return;
            this._playingFlag = true;
            if (window.HarmonivaAudio && this.referenceNote) {
                HarmonivaAudio.playNote(this.referenceNote.key, 0.9);
            }
            await this._wait(1300);
            if (this.phase !== 'reference') { this._playingFlag = false; return; }
            await this._playMelodySeq();
        },

        async _playMelodySeq() {
            this.phase = 'listening';
            await this._wait(200);
            for (let i = 0; i < this.sequence.length; i++) {
                if (this.phase !== 'listening') break;
                this.highlightIndex = i;
                if (window.HarmonivaAudio) HarmonivaAudio.playNote(this.sequence[i].key, 0.8);
                await this._wait(750);
            }
            this.highlightIndex = -1;
            this._playingFlag   = false;
            if (this.phase === 'listening') {
                this.phase = 'input';
                this.$nextTick(() => lucide.createIcons());
            }
        },

        replayReferenceNote() {
            if (!this.referenceNote) return;
            if (window.HarmonivaAudio) HarmonivaAudio.playNote(this.referenceNote.key, 0.8);
        },

        replayMelody() {
            if (['listening','reference'].includes(this.phase)) return;
            this.userInput     = [];
            this.wrongNoteInfo = null;
            this._playingFlag  = false;
            this._playMelodySeq();
        },

        // ── Key press ─────────────────────────────────────────────────────────
        pressKey(pressedPc) {
            if (this.phase !== 'input') return;
            const posIdx = this.userInput.length;
            if (posIdx >= this.sequence.length) return;

            const target = this.sequence[posIdx];

            // Audio: octave-aware for Level 2
            let audioKey;
            if (this.kbMode === 'natural-7-octave-aware') {
                audioKey = pressedPc + target.octave;
            } else {
                audioKey = (pressedPc === target.noteName) ? target.key : pressedPc + '4';
            }
            if (window.HarmonivaAudio) HarmonivaAudio.playNote(audioKey, 0.65);

            const isCorrect    = (pressedPc === target.noteName);
            const pressedKeyId = PC_TO_KEYID[pressedPc] || pressedPc;

            if (isCorrect) {
                this.userInput.push({ noteName: target.noteName, acc: target.acc, key: target.key });
                this._flashKey(pressedKeyId, 'correct', 380);
                if (this.userInput.length === this.sequence.length) {
                    this._onCorrect();
                }
            } else {
                this.wrongNoteInfo = { expected: target.noteName, got: pressedPc };
                this._flashKey(pressedKeyId, 'wrong', 500);
                const targetKeyId = PC_TO_KEYID[target.noteName] || target.noteName;
                if (targetKeyId !== pressedKeyId) this._flashKey(targetKeyId, 'hint', 700);
                this._onWrong();
            }
        },

        _onCorrect() {
            this.consecutiveStreak++;
            this.bestStreak    = Math.max(this.bestStreak, this.consecutiveStreak);
            this.totalCorrect++;
            this.sessionScore += this.round * 100 + this.consecutiveStreak * 10;
            this.phase = 'correct';

            const needed = this.streakNeeded;
            if (needed && this.consecutiveStreak >= needed) {
                setTimeout(() => this._onLevelComplete(), 1000);
            } else {
                setTimeout(() => this._nextRound(), 1300);
            }
        },

        _onWrong() {
            this.consecutiveStreak = 0;
            this.totalWrong++;
            this.lives--;
            this.phase       = 'wrong';
            // Reset sequence — next attempt starts from 1 note
            this.sequence    = [];
            this.round       = 0;
            this.showRefNote = true;

            if (this.lives <= 0) {
                this.livesOut = true;
                setTimeout(() => {
                    this._saveScore(false, this.currentLevel);
                    this.isNewBest = this.sessionScore > this.personalBest;
                    this.phase = 'gameover';
                    this.$nextTick(() => lucide.createIcons());
                }, 2300);
            } else {
                setTimeout(() => {
                    if (this.phase === 'wrong') this._nextRound();
                }, 2300);
            }
        },

        _onLevelComplete() {
            const next = this.currentLevel + 1;
            if (next <= 3) {
                this.maxUnlockedLevel = Math.max(this.maxUnlockedLevel, next);
                this._saveScore(true, next);
                this.phase = 'unlock';
                this.$nextTick(() => lucide.createIcons());
            } else {
                this._saveScore(false, 3);
                this.isNewBest = this.sessionScore > this.personalBest;
                this.phase = 'gameover';
                this.$nextTick(() => lucide.createIcons());
            }
        },

        continueToNextLevel() { this.startLevel(this.currentLevel + 1); },

        endSession() {
            this._saveScore(false, this.currentLevel);
            this.isNewBest = this.sessionScore > this.personalBest;
            this.phase = 'gameover';
            this.$nextTick(() => lucide.createIcons());
        },

        goToIdle() {
            this._playingFlag = false;
            this.phase = 'idle';
            this.$nextTick(() => lucide.createIcons());
        },

        // ── Keyboard styling ──────────────────────────────────────────────────
        _flashKey(keyId, state, ms) {
            if (!(keyId in this.keyStates)) return;
            this.keyStates[keyId] = state;
            setTimeout(() => { if (this.keyStates[keyId] === state) this.keyStates[keyId] = null; }, ms);
        },

        getWhiteKeyStyle(keyId) {
            const s = this.keyStates[keyId];
            if (s === 'correct') return 'background:linear-gradient(to bottom,#c4b5fd,#a78bfa);';
            if (s === 'wrong')   return 'background:linear-gradient(to bottom,#fca5a5,#f87171);';
            if (s === 'hint')    return 'background:linear-gradient(to bottom,#fde68a,#fbbf24);';
            return 'background:linear-gradient(to bottom,#e8e8e8 0%,#ffffff 40%,#f0f0f0 100%);';
        },

        getBlackKeyStyle(keyId) {
            const s = this.keyStates[keyId];
            if (s === 'correct') return 'background:linear-gradient(to bottom,#7c3aed,#5b21b6);';
            if (s === 'wrong')   return 'background:linear-gradient(to bottom,#dc2626,#b91c1c);';
            if (s === 'hint')    return 'background:linear-gradient(to bottom,#d97706,#b45309);';
            return 'background:linear-gradient(to bottom,#2d2d2d 0%,#1a1a1a 60%,#0f0f0f 100%);';
        },

        keyGlowClass(keyId) {
            const s = this.keyStates[keyId];
            if (s === 'correct') return 'mm-glow-purple';
            if (s === 'wrong')   return 'mm-glow-red';
            if (s === 'hint')    return 'mm-glow-amber';
            return '';
        },

        // ── Reference note picker ─────────────────────────────────────────────
        _pickRefNoteObj(pool) {
            const prefs = ['C4','G4','C5','C3','F4'];
            for (const p of prefs) { if (pool.includes(p)) return { ...NOTE_DB[p], key: p }; }
            return { ...NOTE_DB[pool[0]], key: pool[0] };
        },

        // ── Score saving ──────────────────────────────────────────────────────
        _saveScore(isLevelUnlock, levelReached) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const meta = { correct: this.totalCorrect, wrong: this.totalWrong, game_v: 2 };
            if (isLevelUnlock) { meta.level_unlock = 1; meta.unlocked_level = levelReached; }
            fetch(SCORE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({
                    score:         this.sessionScore,
                    max_streak:    this.bestStreak,
                    level_reached: levelReached,
                    metadata:      meta,
                }),
            })
            .then(r => r.json())
            .then(d => {
                if (d.can_play_again === false) this.limitReached = true;
                if (d.is_new_best) { this.isNewBest = true; this.personalBest = this.sessionScore; }
            })
            .catch(() => {});
        },

        // ── Utility ───────────────────────────────────────────────────────────
        _wait(ms) { return new Promise(r => setTimeout(r, ms)); },
    };
}
</script>

@endif
