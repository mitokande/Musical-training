<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\SchoolTeacherRelationship;
use App\Models\User;
use App\Services\School\SchoolTeacherService;
use App\Services\Teacher\TeacherCapabilityService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class SchoolTeacherRelationshipController extends Controller
{
    public function __construct(
        private TeacherCapabilityService $capabilities,
        private SchoolTeacherService $teachers,
    ) {}

    /** Search existing users to send a membership request (JSON). */
    public function search(Request $request)
    {
        abort_unless($this->capabilities->canManageTeachers($request->user()), 403);

        $request->validate(['q' => 'required|string|min:3|max:100']);

        $q = trim($request->q);
        $schoolId = $request->user()->id;

        $users = User::query()
            ->where('id', '!=', $schoolId)
            ->where('role', '!=', 'school')
            ->whereNull('suspended_at')
            ->where(function ($query) use ($q) {
                $query->where('email', strtolower($q))
                    ->orWhere('name', 'like', $q.'%')
                    ->orWhere('surname', 'like', $q.'%');
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'surname', 'email']);

        $existing = SchoolTeacherRelationship::where('school_id', $schoolId)
            ->whereIn('teacher_id', $users->pluck('id'))
            ->pluck('status', 'teacher_id');

        return response()->json($users->map(fn (User $u) => [
            'id' => $u->id,
            'name' => trim($u->name.' '.$u->surname),
            // Only reveal enough of the email to confirm identity.
            'email_hint' => $this->maskEmail($u->email),
            'relationship_status' => $existing[$u->id] ?? null,
        ]));
    }

    /** Send a membership request to an existing user. */
    public function store(Request $request)
    {
        abort_unless($this->capabilities->canManageTeachers($request->user()), 403);

        $request->validate(['user_id' => 'required|integer|exists:users,id']);

        $teacher = User::findOrFail($request->integer('user_id'));

        try {
            $this->teachers->requestExistingTeacher($request->user(), $teacher);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['user_id' => $e->getMessage()]);
        }

        return back()->with('status', 'teacher-relationship-requested');
    }

    /** School removes a member teacher. */
    public function destroy(Request $request, SchoolTeacherRelationship $relationship)
    {
        abort_unless($this->capabilities->canManageTeachers($request->user()), 403);
        abort_unless($relationship->school_id === $request->user()->id, 403);

        $this->teachers->revokeBySchool($relationship);

        return redirect()->route('school.teachers.index')->with('status', 'teacher-relationship-revoked');
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $visible = mb_substr($local, 0, 2);

        return $visible.str_repeat('*', max(1, mb_strlen($local) - 2)).'@'.$domain;
    }
}
