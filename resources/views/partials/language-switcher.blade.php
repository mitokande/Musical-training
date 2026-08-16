{{--
    Reusable language switcher.

    Params:
      $variant  'light' (default) — for white/gray page chrome
                'dark'            — for the dark full-bleed pages (games)
      $drop     'up' (default) — menu opens upward, for footers
                'down'         — menu opens downward, for page headers

    The dark variant is styled with inline `style` rather than Tailwind colour
    utilities on purpose: the dark pages compile `resources/css/marketing.css`,
    whose purge pass only keeps classes already used at build time, so a fresh
    colour utility here would silently render unstyled until the next asset
    build. Layout utilities used below all appear elsewhere on those pages.
--}}
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
    // English is the source language, so it is also the fallback here.
    $current = $languages[$currentLocale] ?? $languages['en'];

    $variant = $variant ?? 'light';
    $drop = $drop ?? 'up';
    $isDark = $variant === 'dark';
    $menuPosition = $drop === 'down' ? 'top-full mt-2' : 'bottom-full mb-2';
@endphp

<div x-data="{ open: false }" @click.outside="open = false" class="relative">
    <button type="button" @click="open = !open"
            aria-label="{{ __('app.language.select') }}"
            @class([
                'inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg transition font-medium',
                'text-gray-600 bg-gray-100 hover:bg-gray-200' => ! $isDark,
            ])
            @if($isDark) style="color:rgba(255,255,255,0.75);background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.12);" @endif>
        <span class="text-base leading-none">{{ $current['flag'] }}</span>
        <span class="hidden sm:inline">{{ strtoupper($currentLocale) }}</span>
        <i data-lucide="chevron-down" class="w-3 h-3 {{ $isDark ? '' : 'text-gray-400' }}"></i>
    </button>

    <div x-show="open" x-cloak
         class="absolute {{ $menuPosition }} right-0 w-52 rounded-xl overflow-hidden z-50 {{ $isDark ? '' : 'bg-white shadow-lg border border-gray-100' }}"
         style="display:none;{{ $isDark ? 'background:#140d26;border:1px solid rgba(255,255,255,0.12);box-shadow:0 20px 45px rgba(0,0,0,0.55);' : '' }}">
        <div class="grid grid-cols-1 max-h-72 overflow-y-auto">
            @foreach($languages as $code => $lang)
                <form method="POST" action="{{ route('language.switch') }}">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $code }}">
                    <button type="submit"
                            @class([
                                'w-full flex items-center gap-2.5 px-3 py-2 text-sm text-left transition',
                                'hover:bg-gray-50' => ! $isDark,
                                'bg-primary-50 text-primary-700 font-semibold' => ! $isDark && $currentLocale === $code,
                                'text-gray-700' => ! $isDark && $currentLocale !== $code,
                            ])
                            @if($isDark) style="color:{{ $currentLocale === $code ? '#ffffff' : 'rgba(255,255,255,0.65)' }};{{ $currentLocale === $code ? 'background:rgba(255,255,255,0.08);font-weight:600;' : '' }}" @endif>
                        <span class="text-base leading-none">{{ $lang['flag'] }}</span>
                        <span>{{ $lang['name'] }}</span>
                        @if($currentLocale === $code)
                            <i data-lucide="check" class="w-3.5 h-3.5 ml-auto {{ $isDark ? '' : 'text-primary-600' }}"></i>
                        @endif
                    </button>
                </form>
            @endforeach
        </div>
    </div>
</div>
