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
        $supported = config('locales.supported', ['en']);

        // The header wins, and the account's column is only the fallback.
        //
        // This is the opposite of the web twin, deliberately. SetLocale serves a
        // browser, where the saved preference is the best evidence of what the
        // reader wants. This serves one app, which knows what language it is
        // currently rendering and says so on every request — and it says so
        // explicitly rather than letting the platform fill the header in,
        // precisely because a device-derived language once had the AI coach
        // answering a Turkish handset in Turkish inside an English app.
        //
        // Deferring to the column would reintroduce that: the app ships with
        // its language picker behind __DEV__, so a member who signed up on the
        // Turkish website would read Turkish answer options and Turkish
        // validation errors under English chrome. The column is still the right
        // answer for a caller that offers nothing.
        $locale = $this->fromHeader($request, $supported);

        if (! $locale) {
            // The guard is named rather than left to the default. A bare user()
            // does happen to work on the authenticated routes — middleware
            // priority hoists Authenticate ahead of this group, and it calls
            // shouldUse('sanctum'). But that is a property of the priority
            // table, not of this file, and it does not hold on the anonymous
            // routes, where the default guard is the session-backed `web` one.
            $account = $request->user('sanctum')?->locale;

            $locale = in_array($account, $supported, true)
                ? $account
                : config('app.locale');
        }

        app()->setLocale($locale);

        $response = $next($request);

        // Tell the client which language it actually got. The app cannot infer
        // this: it asks with Accept-Language but the account's saved locale
        // outranks the header, so a phone set to English belonging to a member
        // who chose Turkish is answered in Turkish. Without this the app has no
        // way to know its own request header lost, and no way to tell a real
        // translation apart from an English fallback.
        $response->headers->set('Content-Language', $locale);

        return $response;
    }

    /**
     * The best-weighted supported language in Accept-Language, or null.
     *
     * Reading the first two characters is not enough. A client is entitled to
     * send its list in any order so long as it weights it, so `en-US;q=0.5, tr`
     * asks for Turkish, and a leading space or a bare `*` derails a substr.
     */
    private function fromHeader(Request $request, array $supported): ?string
    {
        $candidates = [];

        foreach (explode(',', (string) $request->header('Accept-Language')) as $entry) {
            $parts = explode(';', trim($entry));
            $tag = strtolower(trim($parts[0]));

            if ($tag === '' || $tag === '*') {
                continue;
            }

            $quality = 1.0;

            foreach (array_slice($parts, 1) as $parameter) {
                $parameter = trim($parameter);

                if (str_starts_with($parameter, 'q=')) {
                    $quality = (float) substr($parameter, 2);
                }
            }

            // `tr-TR` and `tr` are the same offer as far as we are concerned.
            $language = explode('-', $tag)[0];

            if (in_array($language, $supported, true)) {
                // First mention wins at equal weight, so the header's own order
                // still means what it looks like it means.
                $candidates[$language] ??= $quality;
            }
        }

        if ($candidates === []) {
            return null;
        }

        // arsort is stable as of PHP 8.0, so equal weights keep header order.
        arsort($candidates);

        return array_key_first($candidates);
    }
}
