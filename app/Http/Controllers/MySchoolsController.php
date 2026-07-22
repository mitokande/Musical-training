<?php

namespace App\Http\Controllers;

use App\Models\SchoolTeacherRelationship;
use App\Services\School\SchoolTeacherService;
use Illuminate\Http\Request;
use InvalidArgumentException;

/** Teacher-facing view of their school memberships. */
class MySchoolsController extends Controller
{
    public function __construct(private SchoolTeacherService $schools) {}

    public function index(Request $request)
    {
        $relationships = SchoolTeacherRelationship::with(['school.teacherProfile', 'school.school'])
            ->where('teacher_id', $request->user()->id)
            ->whereIn('status', [
                SchoolTeacherRelationship::STATUS_ACTIVE,
                SchoolTeacherRelationship::STATUS_PENDING_TEACHER_APPROVAL,
            ])
            ->orderByRaw("status = 'pending_teacher_approval' desc")
            ->orderByDesc('approved_at')
            ->get();

        return view('my-schools', ['relationships' => $relationships]);
    }

    public function approve(Request $request, SchoolTeacherRelationship $relationship)
    {
        abort_unless($relationship->teacher_id === $request->user()->id, 403);

        try {
            $this->schools->approve($relationship);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['relationship' => $e->getMessage()]);
        }

        return back()->with('status', 'school-approved');
    }

    public function decline(Request $request, SchoolTeacherRelationship $relationship)
    {
        abort_unless($relationship->teacher_id === $request->user()->id, 403);

        try {
            $this->schools->decline($relationship);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['relationship' => $e->getMessage()]);
        }

        return back()->with('status', 'school-declined');
    }

    public function destroy(Request $request, SchoolTeacherRelationship $relationship)
    {
        abort_unless($relationship->teacher_id === $request->user()->id, 403);
        abort_unless($relationship->isActive(), 403);

        $this->schools->revokeByTeacher($relationship);

        return back()->with('status', 'school-left');
    }
}
