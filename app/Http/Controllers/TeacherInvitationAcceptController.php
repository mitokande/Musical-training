<?php

namespace App\Http\Controllers;

use App\Models\TeacherStudentInvitation;
use App\Services\Teacher\TeacherStudentService;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Student-facing invitation landing. The route is auth-protected, so guests
 * are sent through login/registration first and return here via the
 * intended-URL redirect (login, registration, and email verification all
 * honor url.intended).
 */
class TeacherInvitationAcceptController extends Controller
{
    public function __construct(private TeacherStudentService $students) {}

    public function show(Request $request, string $token)
    {
        $invitation = TeacherStudentInvitation::where('token', $token)->firstOrFail();

        return view('teacher-invitation', [
            'invitation' => $invitation,
            'usable' => $invitation->isUsable() && $invitation->teacher_id !== $request->user()->id,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $invitation = TeacherStudentInvitation::where('token', $token)->firstOrFail();

        try {
            $this->students->acceptInvitation($request->user(), $invitation);
        } catch (InvalidArgumentException $e) {
            return redirect()->route('my-teachers.index')->withErrors(['invitation' => $e->getMessage()]);
        }

        return redirect()->route('my-teachers.index')->with('status', 'teacher-connected');
    }
}
