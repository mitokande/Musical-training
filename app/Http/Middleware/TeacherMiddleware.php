<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeacherMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Teacher access is granted by role (legacy teacher/school/admin) or by
        // holding a teacher account (TeacherProfile), which any user may create.
        if (! $user || (! in_array($user->role, ['teacher', 'school', 'admin']) && ! $user->hasTeacherAccount())) {
            abort(403, 'Unauthorized. Teacher access required.');
        }

        return $next($request);
    }
}
