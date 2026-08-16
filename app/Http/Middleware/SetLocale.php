<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Long-lived guest language preference.
     *
     * A guest's chosen language used to live only in the session (120 min) while
     * their usage quotas live in the one-year `harmoniva_guest_id` cookie
     * (UsageQuotaService). When the session lapsed the quota survived but the
     * language did not, so the next page — typically the "daily limit reached"
     * screen, which replaces the whole feature area — came back in the browser's
     * language instead of the one the visitor was reading the site in.
     *
     * Kept unencrypted (see `encryptCookies(except:)` in bootstrap/app.php) so it
     * costs ~20 bytes per request instead of ~300: it holds a language code, not
     * a secret, and the value is validated against $supported on every read.
     */
    public const LOCALE_COOKIE = 'harmoniva_locale';

    protected const COOKIE_MINUTES = 60 * 24 * 365;

    protected array $supported = ['en', 'es', 'de', 'fr', 'pt', 'tr', 'it'];

    public function handle(Request $request, Closure $next): Response
    {
        [$locale, $explicit] = $this->resolveLocale($request);

        app()->setLocale($locale);

        // Record whether this locale came from an explicit human signal (a saved
        // account preference, a manually chosen language, a stored guest cookie,
        // or the browser's Accept-Language) as opposed to no signal at all. The
        // `/` route reads this to decide whether to redirect to a locale-prefixed
        // landing: it must NOT redirect without one, because search-engine and AI
        // crawlers send no Accept-Language and would otherwise turn `/` into a
        // permanent "page with redirect", keeping the homepage out of the index.
        $request->attributes->set('locale_explicit', $explicit);

        // Persist for subsequent guest requests — but ONLY when the locale came
        // from an explicit signal, so a signal-less visitor (crawler, prefetch,
        // in-app webview) never gets English locked in.
        //
        // Written to BOTH stores on purpose: the session answers fast within a
        // browsing session, the cookie outlives it. Without the cookie half, a
        // guest's language silently reverted to their browser's every time the
        // session expired while their cookie-backed usage quotas carried on.
        if (! Auth::check() && $explicit) {
            if (! session()->has('locale')) {
                session(['locale' => $locale]);
            }

            if ($request->cookie(self::LOCALE_COOKIE) !== $locale) {
                Cookie::queue(Cookie::make(self::LOCALE_COOKIE, $locale, self::COOKIE_MINUTES));
            }
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
        // this flag, so their session locale counts as a deliberate choice. A
        // session locale written by handle() from Accept-Language does not, and
        // neither does one left over from the retired IP-geolocation step — those
        // still resolve, they just must not trigger the `/` locale redirect.
        $chosen = (bool) session('locale_selected');

        // 2. Session locale (previously detected or user-selected for guests)
        if (session()->has('locale')) {
            $locale = session('locale');
            if (in_array($locale, $this->supported)) {
                return [$locale, $chosen];
            }
        }

        // 3. Long-lived guest cookie — the same choice the session carried, kept
        //    past the session's 120-minute lifetime. Always an explicit signal:
        //    it is only ever written from one (see handle() / LanguageController).
        $cookieLocale = $request->cookie(self::LOCALE_COOKIE);
        if (is_string($cookieLocale) && in_array($cookieLocale, $this->supported)) {
            return [$cookieLocale, true];
        }

        // 4. Browser Accept-Language header — an explicit signal from the client.
        $browserLocale = $this->detectFromBrowser($request);
        if ($browserLocale) {
            return [$browserLocale, true];
        }

        // 5. No language signal at all — serve the site's source language rather
        //    than guessing. IP geolocation used to sit here, but a country is not
        //    a language: it mislabelled expats, travellers, VPN users and anyone
        //    on a foreign SIM, and it cost every signal-less request an outbound
        //    ip-api.com lookup. Undetectable now means English, by design.
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
}
