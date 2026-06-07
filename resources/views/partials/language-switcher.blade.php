@php
$languages = [
    'en' => ['flag' => '🇬🇧', 'name' => 'English'],
    'es' => ['flag' => '🇪🇸', 'name' => 'Español'],
    'de' => ['flag' => '🇩🇪', 'name' => 'Deutsch'],
    'fr' => ['flag' => '🇫🇷', 'name' => 'Français'],
    'pt' => ['flag' => '🇧🇷', 'name' => 'Português'],
    'tr' => ['flag' => '🇹🇷', 'name' => 'Türkçe'],
    'it' => ['flag' => '🇮🇹', 'name' => 'Italiano'],
];
$currentLocale = app()->getLocale();
$current = $languages[$currentLocale] ?? $languages['tr'];
@endphp

<div x-data="{ open: false }" @click.outside="open = false" class="relative">
    <button @click="open = !open"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition font-medium">
        <span class="text-base leading-none">{{ $current['flag'] }}</span>
        <span class="hidden sm:inline">{{ strtoupper($currentLocale) }}</span>
        <i data-lucide="chevron-up" class="w-3 h-3 text-gray-400" x-show="!open"></i>
        <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400" x-show="open" x-cloak></i>
    </button>

    <div x-show="open" x-cloak
         @click.outside="open = false"
         class="absolute bottom-full mb-2 right-0 w-52 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50"
         style="display: none;">
        <div class="grid grid-cols-1 max-h-72 overflow-y-auto">
            @foreach($languages as $code => $lang)
                <form method="POST" action="{{ route('language.switch') }}">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $code }}">
                    <button type="submit"
                            class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-left transition hover:bg-gray-50 {{ $currentLocale === $code ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-700' }}">
                        <span class="text-base leading-none">{{ $lang['flag'] }}</span>
                        <span>{{ $lang['name'] }}</span>
                        @if($currentLocale === $code)
                            <i data-lucide="check" class="w-3.5 h-3.5 ml-auto text-primary-600"></i>
                        @endif
                    </button>
                </form>
            @endforeach
        </div>
    </div>
</div>
