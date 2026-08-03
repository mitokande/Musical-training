<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stateless twin of SetLocale. The api middleware group has no session, so the
 * locale is resolved from the authenticated user, then the Accept-Language
 * header, then the app default.
 */
class SetApiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('locales.supported', ['en' => []]));

        $locale = $request->user()?->locale;

        if (! $locale || ! in_array($locale, $supported, true)) {
            $header = substr((string) $request->header('Accept-Language'), 0, 2);
            $locale = in_array($header, $supported, true) ? $header : config('app.locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
