{{-- Shared Footer Partial --}}
{{-- Usage: @include('partials.footer') --}}

@php
$footerLocale = app()->getLocale();
$footerLanguages = [
    'en' => ['flag' => '🇺🇸', 'name' => 'English'],
    'es' => ['flag' => '🇪🇸', 'name' => 'Español'],
    'de' => ['flag' => '🇩🇪', 'name' => 'Deutsch'],
    'fr' => ['flag' => '🇫🇷', 'name' => 'Français'],
    'pt' => ['flag' => '🇧🇷', 'name' => 'Português'],
    'tr' => ['flag' => '🇹🇷', 'name' => 'Türkçe'],
    'it' => ['flag' => '🇮🇹', 'name' => 'Italiano'],
];
$footerCurrent = $footerLanguages[$footerLocale] ?? $footerLanguages['en'];
@endphp

<style>
    .ft-link { transition: color 0.15s ease; }
    .ft-link:hover { color: #fff; }
    .ft-social { width: 34px; height: 34px; border-radius: 8px; background: rgba(255,255,255,0.07); display: flex; align-items: center; justify-content: center; transition: background 0.15s ease; }
    .ft-social:hover { background: rgba(255,255,255,0.14); }
</style>

<footer class="bg-gray-950 text-gray-400 mt-0 border-t border-gray-800/60">

    {{-- ── Main Navigation Grid ── --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-10">
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-6 gap-x-8 gap-y-10">

            {{-- A. Brand Column --}}
            <div class="col-span-2 sm:col-span-2 lg:col-span-2">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-white flex items-center justify-center shadow-sm shrink-0">
                        <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5">
                            <defs>
                                <linearGradient id="ft-logo-g" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#9333ea"/>
                                    <stop offset="100%" stop-color="#fb923c"/>
                                </linearGradient>
                            </defs>
                            <rect x="2" y="3" width="5.5" height="22" rx="2" fill="url(#ft-logo-g)"/>
                            <rect x="20.5" y="3" width="5.5" height="22" rx="2" fill="url(#ft-logo-g)"/>
                            <path d="M7.5 14 Q11 9 14 14 Q17 19 20.5 14" stroke="url(#ft-logo-g)" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span class="font-bold text-2xl text-white tracking-tight leading-none">
                        <span style="background:linear-gradient(135deg,#c084fc,#fb923c);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">H</span>armoniva
                    </span>
                </div>

                <p class="text-sm leading-relaxed text-gray-400 mb-6 max-w-xs">
                    {{ __('app.footer.brand_description') }}
                </p>

                {{-- Social Icons --}}
                <div class="flex items-center gap-2">
                    <a href="#" class="ft-social" aria-label="YouTube">
                        <i data-lucide="youtube" class="w-4 h-4"></i>
                    </a>
                    <a href="#" class="ft-social" aria-label="Instagram">
                        <i data-lucide="instagram" class="w-4 h-4"></i>
                    </a>
                    <a href="#" class="ft-social" aria-label="Twitter / X">
                        <i data-lucide="twitter" class="w-4 h-4"></i>
                    </a>
                    <a href="#" class="ft-social" aria-label="Facebook">
                        <i data-lucide="facebook" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            {{-- B. Product --}}
            <div class="col-span-1">
                <h4 class="text-xs font-bold uppercase tracking-widest text-gray-300 mb-4">{{ __('app.footer.product') }}</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="/"               class="ft-link">{{ __('app.footer.features') }}</a></li>
                    <li><a href="/learn"           class="ft-link">{{ __('app.footer.learning_path') }}</a></li>
                    <li><a href="/exercise-setup"  class="ft-link">{{ __('app.footer.exercise_setup') }}</a></li>
                    <li><a href="/ai-exercises"    class="ft-link">{{ __('app.footer.ai_exercises') }}</a></li>
                    <li><a href="/piano-studio"    class="ft-link">{{ __('app.footer.piano_studio') }}</a></li>
                    <li><a href="/games"           class="ft-link">{{ __('app.footer.music_games') }}</a></li>
                    <li><a href="/progress"        class="ft-link">{{ __('app.footer.progress_tracking') }}</a></li>
                    <li><a href="/pricing"         class="ft-link">{{ __('app.footer.pricing') }}</a></li>
                </ul>
            </div>

            {{-- C. Solutions --}}
            <div class="col-span-1">
                <h4 class="text-xs font-bold uppercase tracking-widest text-gray-300 mb-4">{{ __('app.footer.solutions') }}</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="/students"      class="ft-link">{{ __('app.footer.for_students') }}</a></li>
                    <li><a href="/teachers"      class="ft-link">{{ __('app.footer.for_teachers') }}</a></li>
                    <li><a href="/schools"       class="ft-link">{{ __('app.footer.for_music_schools') }}</a></li>
                    <li><a href="/piano-learners" class="ft-link">{{ __('app.footer.for_piano_learners') }}</a></li>
                    <li><a href="/request-demo"  class="ft-link">{{ __('app.footer.request_demo') }}</a></li>
                </ul>
            </div>

            {{-- D. Resources --}}
            <div class="col-span-1">
                <h4 class="text-xs font-bold uppercase tracking-widest text-gray-300 mb-4">{{ __('app.footer.resources') }}</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="/help"               class="ft-link">{{ __('app.footer.help_center') }}</a></li>
                    <li><a href="/faq"                class="ft-link">{{ __('app.footer.faq') }}</a></li>
                    <li><a href="/blog"               class="ft-link">{{ __('app.footer.articles') }}</a></li>
                    <li><a href="/ear-training-guide" class="ft-link">{{ __('app.footer.ear_training_guide') }}</a></li>
                    <li><a href="/music-theory-basics" class="ft-link">{{ __('app.footer.music_theory_basics') }}</a></li>
                    <li><a href="/contact"            class="ft-link">{{ __('app.footer.contact_support') }}</a></li>
                </ul>
            </div>

            {{-- E. Company --}}
            <div class="col-span-1">
                <h4 class="text-xs font-bold uppercase tracking-widest text-gray-300 mb-4">{{ __('app.footer.company') }}</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="/about"    class="ft-link">{{ __('app.footer.about_harmoniva') }}</a></li>
                    <li><a href="/contact"  class="ft-link">{{ __('app.footer.contact') }}</a></li>
                    <li><a href="/press"    class="ft-link">{{ __('app.footer.press') }}</a></li>
                    <li><a href="/partners" class="ft-link">{{ __('app.footer.partners') }}</a></li>
                </ul>
            </div>

        </div>
    </div>

    {{-- ── App Download · Address · Language ── --}}
    <div class="border-t border-gray-800/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

                {{-- LEFT: Mobile Apps --}}
                <div class="flex-shrink-0">
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-500 mb-3">
                        {{ __('app.footer.mobile_apps_coming_soon') }}
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-800/60 rounded-xl border border-gray-700/60 opacity-60 cursor-not-allowed select-none">
                            <i data-lucide="apple" class="w-5 h-5 text-gray-300"></i>
                            <div class="text-left">
                                <p class="text-[10px] text-gray-500">{{ __('app.footer.download_appstore') }}</p>
                                <p class="text-sm font-semibold text-gray-300">{{ __('app.footer.appstore') }}</p>
                            </div>
                        </div>
                        <div class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-800/60 rounded-xl border border-gray-700/60 opacity-60 cursor-not-allowed select-none">
                            <i data-lucide="smartphone" class="w-5 h-5 text-gray-300"></i>
                            <div class="text-left">
                                <p class="text-[10px] text-gray-500">{{ __('app.footer.get_it_on') }}</p>
                                <p class="text-sm font-semibold text-gray-300">{{ __('app.footer.google_play') }}</p>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 mt-2">{{ __('app.footer.web_version_note') }}</p>

                    {{-- Address on MOBILE (below subtitle, left-aligned) --}}
                    <div class="lg:hidden mt-4 space-y-1.5 text-xs text-gray-500">
                        <div class="flex items-start gap-2">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 shrink-0 mt-0.5 text-gray-600"></i>
                            <span>Harmoniva, H&amp;P LLC · 8 The Green STE B · Dover, DE 19901 US</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-3.5 h-3.5 shrink-0 text-gray-600"></i>
                            <a href="mailto:support@harmoniva.app" class="hover:text-white transition-colors">support@harmoniva.app</a>
                        </div>
                    </div>
                </div>

                {{-- CENTER: Address on DESKTOP only --}}
                <div class="hidden lg:flex flex-col items-center gap-1.5 text-xs text-gray-500 text-center">
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 shrink-0 text-gray-600"></i>
                        <span>Harmoniva, H&amp;P LLC</span>
                    </div>
                    <span>8 The Green STE B · Dover, DE 19901 US</span>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <i data-lucide="mail" class="w-3.5 h-3.5 shrink-0 text-gray-600"></i>
                        <a href="mailto:support@harmoniva.app" class="hover:text-white transition-colors">support@harmoniva.app</a>
                    </div>
                </div>

                {{-- RIGHT: Language Switcher — always right-aligned --}}
                <div class="flex justify-end lg:flex-shrink-0">
                    <div x-data="{ langOpen: false }" @click.outside="langOpen = false" class="relative">

                        <button @click="langOpen = !langOpen"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg hover:bg-gray-700 transition-colors text-sm text-gray-200 font-medium">
                            <i data-lucide="globe" class="w-4 h-4 text-gray-400"></i>
                            <span class="text-base leading-none">{{ $footerCurrent['flag'] }}</span>
                            <span>{{ $footerCurrent['name'] }}</span>
                            <i data-lucide="chevron-up"   class="w-3.5 h-3.5 text-gray-400" x-show="!langOpen"></i>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400" x-show="langOpen" style="display:none"></i>
                        </button>

                        {{-- Dropdown: opens upward, right-aligned (safe since button is flush right) --}}
                        <div x-show="langOpen"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             class="absolute bottom-full mb-2 right-0 w-52 bg-gray-900 border border-gray-700 rounded-xl shadow-2xl z-[999] overflow-hidden"
                             style="display:none; max-height:260px; overflow-y:auto;">
                            @foreach($footerLanguages as $code => $lang)
                                <form method="POST" action="{{ route('language.switch') }}">
                                    @csrf
                                    <input type="hidden" name="locale" value="{{ $code }}">
                                    <button type="submit"
                                            class="w-full flex items-center gap-2.5 px-3 py-2.5 text-sm text-left transition-colors
                                                   {{ $footerLocale === $code
                                                       ? 'bg-gray-800 text-white font-semibold'
                                                       : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <span class="text-base leading-none">{{ $lang['flag'] }}</span>
                                        <span>{{ $lang['name'] }}</span>
                                        @if($footerLocale === $code)
                                            <i data-lucide="check" class="w-3.5 h-3.5 ml-auto text-purple-400"></i>
                                        @endif
                                    </button>
                                </form>
                            @endforeach
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Copyright Bar ── --}}
    <div class="border-t border-gray-800/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-5 pb-[100px] sm:pb-6">
            {{--
                Mobile : legal links on TOP, copyright below  (flex-col-reverse)
                Desktop: copyright on left, legal links right (sm:flex-row)
            --}}
            <div class="flex flex-col-reverse sm:flex-row items-center sm:justify-between gap-3">
                <p class="text-xs text-gray-600">&copy; 2026 Harmoniva. {{ __('app.footer.copyright') }}</p>
                <div class="flex items-center gap-5 text-xs text-gray-500">
                    <a href="/privacy-policy"   class="hover:text-white transition-colors">{{ __('app.footer.privacy_policy') }}</a>
                    <a href="/terms-of-service" class="hover:text-white transition-colors">{{ __('app.footer.terms_of_service') }}</a>
                    <a href="/cookie-policy"    class="hover:text-white transition-colors">{{ __('app.footer.cookie_policy') }}</a>
                </div>
            </div>
        </div>
    </div>

</footer>
