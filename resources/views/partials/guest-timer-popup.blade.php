@guest
<style>[x-cloak]{display:none!important}</style>
{{--
    Guest signup-nudge popup (non-blocking, dismissible).

    After $initialSeconds (default plans.guest.piano_popup_initial_seconds,
    180s) of activity the popup appears; closing it lets the guest keep
    using the page, and it re-appears every $repeatSeconds (default 60s).
    It NEVER blocks the underlying feature. The elapsed-time state is kept in
    localStorage namespaced by $timerKey AND the current date, so it resets
    daily together with all other guest limits.
--}}
@php
    $initialSeconds = (int) ($initialSeconds ?? config('plans.guest.piano_popup_initial_seconds', 180));
    $repeatSeconds = (int) ($repeatSeconds ?? config('plans.guest.piano_popup_repeat_seconds', 60));
@endphp
<div
    x-data="guestNudge('{{ $timerKey ?? 'harmoniva' }}', {{ $initialSeconds }}, {{ $repeatSeconds }})"
    x-init="init()"
>
    <div
        x-show="visible"
        x-cloak
        x-transition.opacity.duration.300ms
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
        style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);"
        @click.self="dismiss()"
    >
        <div
            class="relative w-full max-w-md rounded-3xl p-8 text-center"
            style="background: linear-gradient(145deg, #1a0f33 0%, #0f0a1e 100%); border: 1px solid rgba(255,255,255,0.12); box-shadow: 0 40px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.05);"
        >
            {{-- Close (always available — the popup never blocks usage) --}}
            <button
                type="button"
                @click="dismiss()"
                class="absolute top-4 right-4 w-8 h-8 rounded-full flex items-center justify-center text-white/50 hover:text-white transition-colors"
                style="background: rgba(255,255,255,0.08);"
                aria-label="{{ __('app.popup.close') }}"
            >
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>

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

            <button
                type="button"
                @click="dismiss()"
                class="mt-4 text-white/40 text-xs hover:text-white/70 transition-colors"
            >
                {{ __('app.popup.continue_free') }}
            </button>
        </div>
    </div>
</div>

<script>
function guestNudge(key, initialSeconds, repeatSeconds) {
    // Date-scoped storage: elapsed time (and therefore the nudge cadence)
    // resets every day, in line with the daily guest limits.
    var today = new Date().toISOString().slice(0, 10);
    var STORAGE_KEY = 'harmoniva_guest_nudge_' + key + '_' + today;

    return {
        visible: false,
        _interval: null,

        init() {
            var start = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);
            var now = Math.floor(Date.now() / 1000);

            if (!start || isNaN(start)) {
                start = now;
                localStorage.setItem(STORAGE_KEY, start);
            }

            var self = this;
            var nextAt = start + initialSeconds;

            // If the guest already passed the first threshold today, schedule
            // the next repeat instead of firing instantly on page load.
            while (nextAt <= now) {
                nextAt += repeatSeconds;
            }

            this._interval = setInterval(function () {
                var t = Math.floor(Date.now() / 1000);
                if (!self.visible && t >= nextAt) {
                    self.visible = true;
                    nextAt = t + repeatSeconds;
                    self.$nextTick(function () { if (window.lucide) lucide.createIcons(); });
                }
            }, 1000);
        },

        dismiss() {
            this.visible = false;
        },
    };
}
</script>
@endguest
