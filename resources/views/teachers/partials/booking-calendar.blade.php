{{-- Inline month calendar + slot picker for the public booking panel. Expects $slug. --}}
<div x-data="bookingCalendar()" x-init="init()">
    {{-- Month header --}}
    <div class="flex items-center justify-between mb-3">
        <button type="button" @click="prevMonth" :disabled="!canGoPrev" :class="canGoPrev ? 'text-gray-600 hover:bg-gray-100' : 'text-gray-200 cursor-default'" class="p-2 rounded-lg transition">
            <i data-lucide="chevron-left" class="w-4 h-4"></i>
        </button>
        <p class="text-[15px] font-bold text-gray-900" x-text="monthLabel"></p>
        <button type="button" @click="nextMonth" class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition">
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
        </button>
    </div>

    {{-- Weekday headings --}}
    <div class="grid grid-cols-7 gap-1 mb-1 text-center">
        <template x-for="d in weekdays" :key="d">
            <span class="text-[11px] font-semibold text-gray-400 uppercase" x-text="d"></span>
        </template>
    </div>

    {{-- Day grid --}}
    <div class="grid grid-cols-7 gap-1 mb-4">
        <template x-for="(cell, i) in cells" :key="i">
            <button type="button"
                    @click="cell.date && !cell.disabled && selectDate(cell.date)"
                    :disabled="!cell.date || cell.disabled"
                    :class="{
                        'invisible': !cell.date,
                        'text-gray-300 cursor-default': cell.disabled,
                        'bg-primary-600 text-white font-bold': cell.date === date,
                        'hover:bg-primary-50 text-gray-700': cell.date && !cell.disabled && cell.date !== date,
                        'ring-1 ring-primary-300': cell.isToday && cell.date !== date,
                    }"
                    class="h-9 rounded-lg text-sm transition"
                    x-text="cell.day"></button>
        </template>
    </div>

    {{-- Slots --}}
    <div x-show="loading" class="text-sm text-gray-400 mb-2">…</div>
    <div x-show="!loading && date && slots.length === 0" class="text-sm text-gray-400 mb-2">{{ $trans('booking.no_slots') }}</div>

    <div x-show="slots.length > 0" x-cloak class="mb-3">
        <p class="text-sm font-semibold text-gray-600 mb-2">{{ $trans('booking.pick_slot') }}</p>
        <div class="flex flex-wrap gap-1.5 mb-1.5">
            <template x-for="slot in slots" :key="slot.value">
                <button type="button" @click="selected = slot.value"
                        :class="selected === slot.value ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-primary-50'"
                        class="px-3 py-2 rounded-lg text-sm font-semibold transition" x-text="slot.label"></button>
            </template>
        </div>
        <p class="text-xs text-gray-400" x-show="timezone" x-text="'{{ $trans('booking.times_in_timezone', ['tz' => '']) }}' + timezone"></p>
    </div>

    <form x-show="selected" x-cloak method="POST" action="{{ route('teachers.book', $slug) }}" class="space-y-2">
        @csrf
        <input type="hidden" name="starts_at" :value="selected">
        <input type="text" name="topic" maxlength="255" placeholder="{{ $trans('booking.topic') }}" class="w-full rounded-xl border-gray-300 text-[15px]">
        <button class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white text-[15px] font-semibold rounded-xl transition">
            {{ $trans('booking.request') }}
        </button>
    </form>
    @if ($errors->has('booking'))
        <p class="text-sm text-red-600 mt-2">{{ $errors->first('booking') }}</p>
    @endif
</div>

<script>
function bookingCalendar() {
    const today = new Date();
    const pad = n => String(n).padStart(2, '0');
    const iso = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

    return {
        year: today.getFullYear(),
        month: today.getMonth(), // 0-based
        date: '', slots: [], selected: null, loading: false, timezone: '',
        weekdays: @js(collect(range(0, 6))->map(fn ($i) => now()->startOfWeek(\Carbon\CarbonInterface::MONDAY)->addDays($i)->translatedFormat('D'))->all()),
        cells: [],
        get monthLabel() {
            return new Date(this.year, this.month, 1).toLocaleDateString(@js(str_replace('_', '-', app()->getLocale())), { month: 'long', year: 'numeric' });
        },
        get canGoPrev() {
            return this.year > today.getFullYear() || (this.year === today.getFullYear() && this.month > today.getMonth());
        },
        init() { this.buildCells(); },
        buildCells() {
            const first = new Date(this.year, this.month, 1);
            const daysInMonth = new Date(this.year, this.month + 1, 0).getDate();
            // Monday-first offset.
            const offset = (first.getDay() + 6) % 7;
            const cells = Array.from({ length: offset }, () => ({ date: null }));
            const todayIso = iso(today);
            for (let day = 1; day <= daysInMonth; day++) {
                const d = new Date(this.year, this.month, day);
                const dIso = iso(d);
                cells.push({ date: dIso, day, disabled: dIso < todayIso, isToday: dIso === todayIso });
            }
            this.cells = cells;
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },
        prevMonth() {
            if (!this.canGoPrev) return;
            this.month === 0 ? (this.month = 11, this.year--) : this.month--;
            this.buildCells();
        },
        nextMonth() {
            this.month === 11 ? (this.month = 0, this.year++) : this.month++;
            this.buildCells();
        },
        async selectDate(d) {
            this.date = d; this.selected = null; this.loading = true;
            try {
                const res = await fetch(`{{ route('teachers.slots', $slug) }}?date=${d}`, { headers: { 'Accept': 'application/json' } });
                const data = res.ok ? await res.json() : { slots: [] };
                this.slots = data.slots || [];
                this.timezone = data.timezone || '';
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
