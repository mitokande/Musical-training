<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class TeacherMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // School accounts have their own copy of the CRM under /school/* —
        // bounce them off teacher.* pages (to the same page when it exists) so
        // there is never a second live panel under /teacher/*. Shared routes
        // that merely require a teacher account (e.g. articles.*) stay open.
        if ($user && ! $user->isAdmin() && $user->crmNamespace() === 'school') {
            $name = $request->route()?->getName();

            if ($name && str_starts_with($name, 'teacher.')) {
                $schoolName = 'school.'.substr($name, strlen('teacher.'));

                return redirect()->route(
                    Route::has($schoolName) ? $schoolName : 'school.dashboard',
                    $request->route()->parameters()
                );
            }
        }

        // Teacher access is granted by role (legacy teacher/admin) or by
        // holding a teacher account (TeacherProfile), which any user may create.
        if (! $user || (! in_array($user->role, ['teacher', 'admin']) && ! $user->hasTeacherAccount())) {
            abort(403, 'Unauthorized. Teacher access required.');
        }

        return $next($request);
    }
}
