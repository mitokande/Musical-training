<?php

namespace App\Http\Controllers;

use App\Models\SchoolTeacherInvitation;
use App\Services\School\SchoolTeacherService;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Teacher-facing invitation landing. The route is auth-protected, so guests
 * are sent through login/registration first and return here via the
 * intended-URL redirect (mirrors TeacherInvitationAcceptController).
 */
class SchoolInvitationAcceptController extends Controller
{
    public function __construct(private SchoolTeacherService $schools) {}

    public function show(Request $request, string $token)
    {
        $invitation = SchoolTeacherInvitation::where('token', $token)->firstOrFail();

        return view('school-invitation', [
            'invitation' => $invitation,
            'usable' => $invitation->isUsable() && $invitation->school_id !== $request->user()->id,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $invitation = SchoolTeacherInvitation::where('token', $token)->firstOrFail();

        try {
            $this->schools->acceptInvitation($request->user(), $invitation);
        } catch (InvalidArgumentException $e) {
            return redirect()->route('my-schools.index')->withErrors(['invitation' => $e->getMessage()]);
        }

        return redirect()->route('my-schools.index')->with('status', 'school-joined');
    }
}
