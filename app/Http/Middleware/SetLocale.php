<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    protected array $supported = ['en', 'es', 'de', 'fr', 'pt', 'tr', 'it'];

    // ISO country code → locale mapping for IP geolocation
    protected array $countryToLocale = [
        'TR' => 'tr',
        'US' => 'en', 'GB' => 'en', 'AU' => 'en', 'CA' => 'en', 'NZ' => 'en', 'IE' => 'en', 'ZA' => 'en',
        'DE' => 'de', 'AT' => 'de', 'CH' => 'de', 'LI' => 'de',
        'FR' => 'fr', 'BE' => 'fr', 'LU' => 'fr', 'MC' => 'fr',
        'ES' => 'es', 'MX' => 'es', 'AR' => 'es', 'CO' => 'es', 'CL' => 'es', 'PE' => 'es', 'VE' => 'es',
        'IT' => 'it', 'SM' => 'it', 'VA' => 'it',
        'BR' => 'pt', 'PT' => 'pt', 'AO' => 'pt', 'MZ' => 'pt',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        [$locale, $explicit] = $this->resolveLocale($request);

        app()->setLocale($locale);

        // Record whether this locale came from an explicit human signal (a saved
        // account preference, a manually chosen language, or the browser's
        // Accept-Language) as opposed to an IP-geolocation guess. The `/` route
        // reads this to decide whether to redirect to a locale-prefixed landing:
        // it must NOT redirect on an IP guess, because search-engine and AI
        // crawlers send no Accept-Language and would otherwise turn `/` into a
        // permanent "page with redirect", keeping the homepage out of the index.
        $request->attributes->set('locale_explicit', $explicit);

        // Store resolved locale in session for subsequent requests (guests)
        if (! Auth::check() && ! session()->has('locale')) {
            session(['locale' => $locale]);
        }

        return $next($request);
    }

    /**
     * @return array{0: string, 1: bool} [locale, wasExplicit]
     */
    protected function resolveLocale(Request $request): array
    {
        // 1. Authenticated user's saved locale — an explicit preference.
        if (Auth::check() && Auth::user()->locale) {
            $locale = Auth::user()->locale;
            if (in_array($locale, $this->supported)) {
                return [$locale, true];
            }
        }

        // A guest who manually switched language (via LanguageController) carries
        // this flag, so their session locale counts as an explicit choice — an
        // IP-seeded session locale does not.
        $chosen = (bool) session('locale_selected');

        // 2. Session locale (previously detected or user-selected for guests)
        if (session()->has('locale')) {
            $locale = session('locale');
            if (in_array($locale, $this->supported)) {
                return [$locale, $chosen];
            }
        }

        // 3. Browser Accept-Language header — an explicit signal from the client.
        $browserLocale = $this->detectFromBrowser($request);
        if ($browserLocale) {
            return [$browserLocale, true];
        }

        // 4. IP geolocation (cached per session to avoid repeated API calls) —
        //    a guess, never treated as explicit.
        $ipLocale = $this->detectFromIp($request);
        if ($ipLocale) {
            return [$ipLocale, false];
        }

        // 5. Default: English
        return ['en', false];
    }

    protected function detectFromBrowser(Request $request): ?string
    {
        $header = $request->header('Accept-Language', '');
        if (! $header) {
            return null;
        }

        // Parse "en-US,en;q=0.9,tr;q=0.8" format
        $languages = [];
        foreach (explode(',', $header) as $part) {
            $part = trim($part);
            if (str_contains($part, ';q=')) {
                [$lang, $q] = explode(';q=', $part);
                $languages[trim($lang)] = (float) $q;
            } else {
                $languages[trim($part)] = 1.0;
            }
        }

        arsort($languages);

        foreach (array_keys($languages) as $lang) {
            $lang = strtolower(trim($lang));

            // Exact match (e.g. "tr", "en")
            if (in_array($lang, $this->supported)) {
                if (! session()->has('detected_country')) {
                    session(['detected_country' => $this->getDefaultCountryForLocale($lang)]);
                }

                return $lang;
            }

            // Prefix match (e.g. "zh-CN" → "zh", "pt-BR" → "pt")
            $prefix = substr($lang, 0, 2);
            if (in_array($prefix, $this->supported)) {
                if (! session()->has('detected_country')) {
                    session(['detected_country' => $this->getDefaultCountryForLocale($prefix)]);
                }

                return $prefix;
            }
        }

        return null;
    }

    protected function getDefaultCountryForLocale(string $locale): string
    {
        return [
            'en' => 'US', 'es' => 'ES', 'de' => 'DE', 'fr' => 'FR',
            'pt' => 'BR', 'tr' => 'TR', 'it' => 'IT',
        ][$locale] ?? '';
    }

    protected function detectFromIp(Request $request): ?string
    {
        // Use session cache to avoid API calls on every request
        if (session()->has('ip_locale')) {
            $cached = session('ip_locale');

            return in_array($cached, $this->supported) ? $cached : null;
        }

        $ip = $request->ip();

        // Skip private/local IPs
        if (! $ip || $this->isPrivateIp($ip)) {
            session(['ip_locale' => 'en']);

            return null;
        }

        try {
            $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}?fields=countryCode");

            if ($response->successful()) {
                $countryCode = strtoupper($response->json('countryCode', ''));
                $locale = $this->countryToLocale[$countryCode] ?? 'en';
                session(['ip_locale' => $locale, 'detected_country' => $countryCode]);

                return in_array($locale, $this->supported) ? $locale : null;
            }
        } catch (\Throwable) {
            // Silently fail — network issues should not break the page
        }

        session(['ip_locale' => 'en']);

        return null;
    }

    protected function isPrivateIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
}
