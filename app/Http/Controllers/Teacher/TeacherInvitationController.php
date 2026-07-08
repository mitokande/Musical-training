<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherStudentInvitation;
use App\Services\Teacher\TeacherCapabilityService;
use App\Services\Teacher\TeacherStudentService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class TeacherInvitationController extends Controller
{
    public function __construct(
        private TeacherCapabilityService $capabilities,
        private TeacherStudentService $students,
    ) {}

    /** Invite a student by email. */
    public function store(Request $request)
    {
        abort_unless($this->capabilities->canManageStudents($request->user()), 403);

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:100',
            'teacher_class_id' => 'nullable|integer',
        ]);

        try {
            $this->students->inviteByEmail(
                $request->user(),
                $validated['email'],
                $validated['name'] ?? null,
                isset($validated['teacher_class_id']) ? (int) $validated['teacher_class_id'] : null,
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['email' => $e->getMessage()])->withInput();
        }

        return back()->with('status', 'invitation-sent');
    }

    /** Create a shareable invitation link. */
    public function storeLink(Request $request)
    {
        abort_unless($this->capabilities->canManageStudents($request->user()), 403);

        $validated = $request->validate([
            'expires_at' => 'nullable|date|after:now',
            'teacher_class_id' => 'nullable|integer',
        ]);

        $this->students->createLinkInvitation(
            $request->user(),
            isset($validated['expires_at']) ? new \DateTimeImmutable($validated['expires_at']) : null,
            isset($validated['teacher_class_id']) ? (int) $validated['teacher_class_id'] : null,
        );

        return back()->with('status', 'invitation-link-created');
    }

    /** Revoke a pending invitation. */
    public function destroy(Request $request, TeacherStudentInvitation $invitation)
    {
        abort_unless($this->capabilities->canManageStudents($request->user()), 403);
        abort_unless($invitation->teacher_id === $request->user()->id, 403);

        $this->students->revokeInvitation($invitation);

        return back()->with('status', 'invitation-revoked');
    }
}
