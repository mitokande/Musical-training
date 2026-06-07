<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeacherMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['teacher', 'school', 'admin'])) {
            abort(403, 'Unauthorized. Teacher access required.');
        }

        return $next($request);
    }
}
