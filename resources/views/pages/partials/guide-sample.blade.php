{{-- Reusable "sample exercise draft" callout. Expects $title and $body strings. --}}
<div class="rounded-2xl border border-dashed border-purple-200 bg-white p-5 mb-6">
    <div class="flex items-center gap-2 mb-2">
        <span class="w-7 h-7 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center flex-shrink-0">
            <i data-lucide="lightbulb" class="w-4 h-4"></i>
        </span>
        <p class="text-sm font-bold text-gray-900">{{ $title }}</p>
    </div>
    <p class="text-sm text-gray-600 leading-relaxed">{{ $body }}</p>
</div>
