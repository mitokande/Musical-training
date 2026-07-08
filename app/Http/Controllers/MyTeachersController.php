<?php

namespace App\Http\Controllers;

use App\Models\TeacherStudentRelationship;
use App\Services\Teacher\TeacherStudentService;
use Illuminate\Http\Request;
use InvalidArgumentException;

/** Student-facing view of their teacher relationships. */
class MyTeachersController extends Controller
{
    public function __construct(private TeacherStudentService $students) {}

    public function index(Request $request)
    {
        $relationships = TeacherStudentRelationship::with(['teacher.teacherProfile'])
            ->where('student_id', $request->user()->id)
            ->whereIn('status', [
                TeacherStudentRelationship::STATUS_ACTIVE,
                TeacherStudentRelationship::STATUS_PENDING_STUDENT_APPROVAL,
            ])
            ->orderByRaw("status = 'pending_student_approval' desc")
            ->orderByDesc('approved_at')
            ->get();

        return view('my-teachers', ['relationships' => $relationships]);
    }

    public function approve(Request $request, TeacherStudentRelationship $relationship)
    {
        abort_unless($relationship->student_id === $request->user()->id, 403);

        try {
            $this->students->approve($relationship);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['relationship' => $e->getMessage()]);
        }

        return back()->with('status', 'teacher-approved');
    }

    public function decline(Request $request, TeacherStudentRelationship $relationship)
    {
        abort_unless($relationship->student_id === $request->user()->id, 403);

        try {
            $this->students->decline($relationship);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['relationship' => $e->getMessage()]);
        }

        return back()->with('status', 'teacher-declined');
    }

    public function destroy(Request $request, TeacherStudentRelationship $relationship)
    {
        abort_unless($relationship->student_id === $request->user()->id, 403);
        abort_unless($relationship->isActive(), 403);

        $this->students->revokeByStudent($relationship);

        return back()->with('status', 'teacher-revoked');
    }
}
