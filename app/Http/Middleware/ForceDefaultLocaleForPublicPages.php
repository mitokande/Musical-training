<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Forces English (the x-default) on the UN-prefixed form of a localized public
 * page — the counterpart to ForceLocaleFromUrl.
 *
 * SetLocale resolves the locale from the user/session/browser/IP; on a bare URL
 * like /contact (as opposed to /de/contact) an IP-geolocation guess would make
 * the English x-default page render German content, declare <html lang="de">,
 * and — via the canonical/hreflang partials that read app()->getLocale() —
 * canonicalise itself to /de/contact. That tells Google not to index the
 * English URL at all. Every /{locale} variant already carries the German (etc.)
 * content, so the un-prefixed path must always be the English x-default.
 *
 * Localized visitors are unaffected: navbar/footer links are built with
 * locale_url(), so a German user browses /de/contact, never the bare /contact.
 */
class ForceDefaultLocaleForPublicPages
{
    public function handle(Request $request, Closure $next): Response
    {
        $segments = $request->segments();

        // A /{locale}/... URL is handled by ForceLocaleFromUrl — leave it alone.
        if (isset($segments[0]) && in_array($segments[0], config('locales.prefixed'), true)) {
            return $next($request);
        }

        $basePath = rtrim('/'.implode('/', $segments), '/') ?: '/';

        if (array_key_exists($basePath, (array) config('locales.public_pages'))) {
            app()->setLocale('en');
        }

        return $next($request);
    }
}
