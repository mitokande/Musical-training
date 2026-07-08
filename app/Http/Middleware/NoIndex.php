<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoIndex
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Controllers may opt a page into indexing (currently only approved
        // public teacher profiles do this); everything else stays noindex.
        if ($request->attributes->get('allow_indexing') === true) {
            return $response;
        }

        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
