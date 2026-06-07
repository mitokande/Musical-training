@guest
<style>[x-cloak]{display:none!important}</style>
{{--
    Guest 5-minute timer popup.
    After 5 minutes of browsing as a guest, a blocking modal prompts sign-up.
    The start timestamp is stored in localStorage so page refreshes don't reset the clock.
    Pass $timerKey (e.g. 'piano-studio' or 'music-games') to namespace the localStorage entry.
--}}
<div
    x-data="guestTimer('{{ $timerKey ?? 'harmoniva' }}')"
    x-init="init()"
>
    {{-- Progress bar at top of viewport --}}
    <div
        x-show="!expired"
        class="fixed top-0 left-0 right-0 z-50 h-0.5 bg-white/5"
    >
        <div
            class="h-full bg-gradient-to-r from-purple-500 to-pink-500 transition-all duration-1000"
            :style="'width:' + progressPct + '%'"
        ></div>
    </div>

    {{-- Blocking modal --}}
    <div
        x-show="expired"
        x-cloak
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
        style="background: rgba(0,0,0,0.85); backdrop-filter: blur(8px);"
    >
        <div
            class="relative w-full max-w-md rounded-3xl p-8 text-center"
            style="background: linear-gradient(145deg, #1a0f33 0%, #0f0a1e 100%); border: 1px solid rgba(255,255,255,0.12); box-shadow: 0 40px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.05);"
        >
            {{-- Icon --}}
            <div class="mx-auto w-16 h-16 rounded-2xl flex items-center justify-center mb-5"
                 style="background: linear-gradient(135deg, rgba(168,85,247,0.3) 0%, rgba(236,72,153,0.3) 100%); border: 1px solid rgba(168,85,247,0.4);">
                <i data-lucide="music-2" class="w-8 h-8 text-purple-300"></i>
            </div>

            {{-- Heading --}}
            <h2 class="text-white font-extrabold text-2xl mb-2 leading-tight">
                {{ __('app.popup.heading') }}
            </h2>

            <p class="text-white/50 text-sm mb-7 leading-relaxed">
                {{ __('app.popup.trial') }}<br>
                {{ __('app.popup.desc') }}
            </p>

            {{-- Perks --}}
            <div class="grid grid-cols-3 gap-3 mb-7">
                <div class="rounded-xl p-3" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
                    <i data-lucide="infinity" class="w-5 h-5 text-purple-400 mx-auto mb-1.5"></i>
                    <p class="text-white/70 text-xs font-medium">{{ __('app.popup.unlimited') }}</p>
                </div>
                <div class="rounded-xl p-3" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
                    <i data-lucide="trophy" class="w-5 h-5 text-amber-400 mx-auto mb-1.5"></i>
                    <p class="text-white/70 text-xs font-medium">{{ __('app.popup.scores') }}</p>
                </div>
                <div class="rounded-xl p-3" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
                    <i data-lucide="bar-chart-2" class="w-5 h-5 text-sky-400 mx-auto mb-1.5"></i>
                    <p class="text-white/70 text-xs font-medium">{{ __('app.popup.track') }}</p>
                </div>
            </div>

            {{-- CTA buttons --}}
            <a
                href="{{ route('register') }}"
                class="block w-full py-3.5 rounded-2xl font-bold text-white text-base mb-3 transition-all duration-200 hover:opacity-90 active:scale-[0.98]"
                style="background: linear-gradient(135deg, #9333ea 0%, #ec4899 100%); box-shadow: 0 8px 24px rgba(147,51,234,0.4);"
            >
                {{ __('app.popup.sign_up') }}
            </a>
            <a
                href="{{ route('login') }}"
                class="block w-full py-3 rounded-2xl font-semibold text-white/60 text-sm border transition-all duration-200 hover:text-white hover:border-white/30"
                style="border-color: rgba(255,255,255,0.12); background: rgba(255,255,255,0.04);"
            >
                {{ __('app.popup.login') }}
            </a>
        </div>
    </div>
</div>

<script>
function guestTimer(key) {
    const DURATION = 5 * 60; // 300 seconds
    const STORAGE_KEY = 'harmoniva_guest_start_' + key;

    return {
        expired: false,
        progressPct: 100,
        _interval: null,

        init() {
            let start = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);
            const now = Math.floor(Date.now() / 1000);

            if (!start || isNaN(start)) {
                start = now;
                localStorage.setItem(STORAGE_KEY, start);
            }

            const elapsed = now - start;

            if (elapsed >= DURATION) {
                this.expired = true;
                this.progressPct = 0;
                this.$nextTick(() => lucide.createIcons());
                return;
            }

            this.progressPct = Math.max(0, ((DURATION - elapsed) / DURATION) * 100);

            this._interval = setInterval(() => {
                const e = Math.floor(Date.now() / 1000) - start;
                this.progressPct = Math.max(0, ((DURATION - e) / DURATION) * 100);

                if (e >= DURATION) {
                    clearInterval(this._interval);
                    this.expired = true;
                    this.$nextTick(() => lucide.createIcons());
                }
            }, 1000);
        },
    };
}
</script>
@endguest
