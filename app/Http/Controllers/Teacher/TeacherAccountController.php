<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeacherAccountController extends Controller
{
    /**
     * Create a Basic teacher account for the authenticated user.
     * Any registered user may become a teacher; the profile starts as a
     * private draft until submitted and approved.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->teacherProfile()->exists()) {
            return redirect()->route('teacher.profile.edit');
        }

        TeacherProfile::createDraftFor($user);

        activity('teacher')
            ->causedBy($user)
            ->performedOn($user)
            ->log('teacher_account_created');

        return redirect()->route('teacher.profile.edit')
            ->with('status', 'teacher-account-created');
    }
}
