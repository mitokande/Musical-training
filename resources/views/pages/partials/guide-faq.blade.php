{{-- Reusable per-section FAQ accordion. Expects $faqs = [['q'=>..,'a'=>..], ...]; optional $label overrides the heading. --}}
<div class="mt-8">
    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
        <i data-lucide="help-circle" class="w-4 h-4"></i> {{ $label ?? 'Frequently asked' }}
    </p>
    <div class="space-y-2.5">
        @foreach($faqs as $faq)
        <div x-data="{ open: false }" class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <button @click="open = !open" class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left" :aria-expanded="open">
                <span class="font-semibold text-gray-800 text-sm">{{ $faq['q'] }}</span>
                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-purple-50 flex items-center justify-center transition-transform duration-200" :class="open ? 'rotate-45' : ''">
                    <i data-lucide="plus" class="w-3.5 h-3.5 text-purple-600"></i>
                </span>
            </button>
            <div x-show="open" x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                class="px-5 pb-5">
                <div class="h-px bg-gray-100 mb-4"></div>
                <p class="text-gray-600 text-sm leading-relaxed">{{ $faq['a'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>
