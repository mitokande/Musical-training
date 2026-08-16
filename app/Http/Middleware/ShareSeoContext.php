<?php

namespace App\Http\Middleware;

use App\Services\Seo\PublicPageSeo;
use Closure;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shares the canonical / hreflang / <html lang> facts for the current URL with
 * every view, so public templates render one consistent set of SEO tags.
 *
 * This runs as middleware rather than a view composer because a child view's
 * section bodies are captured BEFORE its layout renders — a composer bound to
 * layouts.standalone would come too late for a page that builds JSON-LD out of
 * $seoCanonical. It also reaches the handful of public pages that hand-roll
 * their own <head> instead of extending the layout.
 *
 * Sharing per request (not once at provider boot) keeps the values tied to the
 * URL actually being served, which matters wherever one booted application
 * handles several requests: the test suite, and Octane-style workers.
 */
class ShareSeoContext
{
    public function handle($request, Closure $next): Response
    {
        View::share(app(PublicPageSeo::class)->forRequest($request));

        return $next($request);
    }
}
