<?php

namespace App\Http\Middleware;

use App\Models\TeacherProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SchoolMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user || ! in_array($user->role, ['school', 'admin'])) {
            abort(403, __('app.messages.unauthorized_school'));
        }

        // The school panel runs on the shared CRM engine, which is keyed on a
        // TeacherProfile row. Provision the school draft (entity_type=school)
        // on the school's first panel visit — mirrors TeacherAccountController.
        if ($user->isSchool() && ! $user->teacherProfile()->exists()) {
            TeacherProfile::createDraftFor($user, TeacherProfile::ENTITY_SCHOOL);
            $user->unsetRelation('teacherProfile');

            activity('school')
                ->causedBy($user)
                ->performedOn($user)
                ->log('school_account_created');
        }

        return $next($request);
    }
}
