<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherStudentRelationship;
use App\Models\User;
use App\Services\Teacher\TeacherCapabilityService;
use App\Services\Teacher\TeacherStudentService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class TeacherStudentRelationshipController extends Controller
{
    public function __construct(
        private TeacherCapabilityService $capabilities,
        private TeacherStudentService $students,
    ) {}

    /** Search existing users to send a relationship request (JSON). */
    public function search(Request $request)
    {
        abort_unless($this->capabilities->canManageStudents($request->user()), 403);

        $request->validate(['q' => 'required|string|min:3|max:100']);

        $q = trim($request->q);
        $teacherId = $request->user()->id;

        $users = User::query()
            ->where('id', '!=', $teacherId)
            ->whereNull('suspended_at')
            ->where(function ($query) use ($q) {
                $query->where('email', strtolower($q))
                    ->orWhere('name', 'like', $q.'%')
                    ->orWhere('surname', 'like', $q.'%');
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'surname', 'email']);

        $existing = TeacherStudentRelationship::where('teacher_id', $teacherId)
            ->whereIn('student_id', $users->pluck('id'))
            ->pluck('status', 'student_id');

        return response()->json($users->map(fn (User $u) => [
            'id' => $u->id,
            'name' => trim($u->name.' '.$u->surname),
            // Only reveal enough of the email to confirm identity.
            'email_hint' => $this->maskEmail($u->email),
            'relationship_status' => $existing[$u->id] ?? null,
        ]));
    }

    /** Send a relationship request to an existing user. */
    public function store(Request $request)
    {
        abort_unless($this->capabilities->canManageStudents($request->user()), 403);

        $request->validate(['user_id' => 'required|integer|exists:users,id']);

        $student = User::findOrFail($request->integer('user_id'));

        try {
            $this->students->requestExistingUser($request->user(), $student);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['user_id' => $e->getMessage()]);
        }

        return back()->with('status', 'relationship-requested');
    }

    /** Teacher revokes (or archives) a relationship. */
    public function destroy(Request $request, TeacherStudentRelationship $relationship)
    {
        abort_unless($this->capabilities->canManageStudents($request->user()), 403);
        abort_unless($relationship->teacher_id === $request->user()->id, 403);

        $this->students->revokeByTeacher($relationship);

        return redirect()->route('teacher.students.index')->with('status', 'relationship-revoked');
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $visible = mb_substr($local, 0, 2);

        return $visible.str_repeat('*', max(1, mb_strlen($local) - 2)).'@'.$domain;
    }
}
