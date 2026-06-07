<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SchoolMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['school', 'admin'])) {
            abort(403, 'Unauthorized. Music School access required.');
        }

        return $next($request);
    }
}
