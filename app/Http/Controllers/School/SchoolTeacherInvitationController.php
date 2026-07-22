<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\SchoolTeacherInvitation;
use App\Services\School\SchoolTeacherService;
use App\Services\Teacher\TeacherCapabilityService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class SchoolTeacherInvitationController extends Controller
{
    public function __construct(
        private TeacherCapabilityService $capabilities,
        private SchoolTeacherService $teachers,
    ) {}

    /** Invite a teacher by email. */
    public function store(Request $request)
    {
        abort_unless($this->capabilities->canManageTeachers($request->user()), 403);

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:100',
        ]);

        try {
            $this->teachers->inviteByEmail(
                $request->user(),
                $validated['email'],
                $validated['name'] ?? null,
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['email' => $e->getMessage()])->withInput();
        }

        return back()->with('status', 'teacher-invitation-sent');
    }

    /** Create a shareable invitation link. */
    public function storeLink(Request $request)
    {
        abort_unless($this->capabilities->canManageTeachers($request->user()), 403);

        $validated = $request->validate([
            'expires_at' => 'nullable|date|after:now',
        ]);

        $this->teachers->createLinkInvitation(
            $request->user(),
            isset($validated['expires_at']) ? new \DateTimeImmutable($validated['expires_at']) : null,
        );

        return back()->with('status', 'teacher-invitation-link-created');
    }

    /** Revoke a pending invitation. */
    public function destroy(Request $request, SchoolTeacherInvitation $invitation)
    {
        abort_unless($this->capabilities->canManageTeachers($request->user()), 403);
        abort_unless($invitation->school_id === $request->user()->id, 403);

        $this->teachers->revokeInvitation($invitation);

        return back()->with('status', 'teacher-invitation-revoked');
    }
}
