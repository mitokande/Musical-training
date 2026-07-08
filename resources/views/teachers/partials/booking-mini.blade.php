{{-- Compact weekly booking widget for the public profile right column.
     Expects: $slug, $bookingEnabled (bool), $bookingDays (consecutive-day grid). --}}
@php $enabled = ($bookingEnabled ?? false); @endphp

<div x-data="{
        days: @js($bookingDays ?? []),
        page: 0,
        per: 2,
        rows: 6,
        symbolic: ['10.00', '11.00', '14.00', '15.00', '16.00', '17.00'],
        enabled: {{ $enabled ? 'true' : 'false' }},
        bookingUrl: @js(route('teachers.booking', $slug)),
        get pages() { return Math.max(1, Math.ceil(this.days.length / this.per)); },
        get shown() {
            let s = this.days.slice(this.page * this.per, this.page * this.per + this.per);
            while (s.length < this.per) s.push(null);
            return s;
        },
        prev() { if (this.page > 0) this.page--; },
        next() { if ((this.page + 1) < this.pages) this.page++; },
     }"
     x-init="$nextTick(() => window.lucide && lucide.createIcons())">

    <div class="grid grid-cols-2 gap-3">
        <template x-for="(d, ci) in shown" :key="ci">
            <div class="min-w-0">
                <p class="text-center text-[15px] font-bold capitalize truncate" :class="d ? 'text-gray-800' : 'text-gray-300'" x-text="d ? d.weekday : '—'"></p>
                <p class="text-center text-xs mb-2" :class="d ? 'text-gray-400' : 'text-gray-300'" x-text="d ? (d.day + ' ' + d.month) : ' '"></p>

                {{-- Always 6 rows: real slot in dark where available, faded symbolic time otherwise --}}
                <div class="space-y-2">
                    <template x-for="i in rows" :key="'s' + i">
                        <a x-show="enabled && d && d.slots[i - 1]" :href="bookingUrl"
                           class="block text-center py-2 rounded-xl border border-primary-200 bg-primary-50 text-primary-700 font-semibold text-[15px] hover:bg-primary-100 transition"
                           x-text="(d && d.slots[i - 1]) ? d.slots[i - 1].label.replace(':', '.') : ''"></a>
                    </template>
                    <template x-for="i in rows" :key="'p' + i">
                        <div x-show="!(enabled && d && d.slots[i - 1])"
                             class="text-center py-2 rounded-xl border border-gray-200 bg-gray-50 text-gray-300 font-semibold text-[15px] cursor-default select-none"
                             x-text="symbolic[i - 1]"></div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    {{-- Day navigation: always present (later days may be available) --}}
    <div class="flex items-center justify-between gap-3 mt-4">
        <button type="button" @click="prev" :disabled="page === 0"
                :class="page === 0 ? 'text-gray-300 cursor-default' : 'text-gray-600 hover:bg-gray-200'"
                class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center transition shrink-0">
            <i data-lucide="chevron-left" class="w-5 h-5"></i>
        </button>
        <a href="{{ route('teachers.booking', $slug) }}"
           class="flex-1 text-center py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition">
            {{ __('teacher.booking.request') }}
        </a>
        <button type="button" @click="next" :disabled="(page + 1) >= pages"
                :class="(page + 1) >= pages ? 'text-gray-300 cursor-default' : 'text-gray-600 hover:bg-gray-200'"
                class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center transition shrink-0">
            <i data-lucide="chevron-right" class="w-5 h-5"></i>
        </button>
    </div>

</div>
