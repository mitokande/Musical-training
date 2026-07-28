<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Forces the application locale from a /{locale} URL prefix on public pages.
 *
 * SetLocale (the global web middleware) resolves the locale from the user,
 * session, browser, or IP — it does not read the URL. On locale-prefixed
 * public routes the URL is the authoritative signal, so this runs after
 * SetLocale and overrides it, mirroring how the /{locale} landing route
 * forces its own locale. The resolved locale is also persisted to the session
 * so subsequent same-session navigation stays in the chosen language.
 */
class ForceLocaleFromUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if (in_array($locale, config('locales.prefixed'), true)) {
            app()->setLocale($locale);
            session(['locale' => $locale]);
        }

        return $next($request);
    }
}
