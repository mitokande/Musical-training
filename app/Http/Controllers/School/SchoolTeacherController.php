<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\SchoolTeacherInvitation;
use App\Models\SchoolTeacherRelationship;
use App\Models\TeacherAssignment;
use App\Models\TeacherClass;
use App\Models\TeacherStudentRelationship;
use App\Models\User;
use App\Services\Teacher\TeacherCapabilityService;
use Illuminate\Http\Request;

/** School panel: member teacher roster + per-teacher detail. */
class SchoolTeacherController extends Controller
{
    public function __construct(private TeacherCapabilityService $capabilities) {}

    public function index(Request $request)
    {
        $school = $request->user();
        abort_unless($this->capabilities->canManageTeachers($school), 403);

        $relationships = SchoolTeacherRelationship::with(['teacher.teacherProfile'])
            ->where('school_id', $school->id)
            ->whereIn('status', [
                SchoolTeacherRelationship::STATUS_ACTIVE,
                SchoolTeacherRelationship::STATUS_PENDING_TEACHER_APPROVAL,
            ])
            ->orderByRaw("status = 'pending_teacher_approval' desc")
            ->orderByDesc('approved_at')
            ->get();

        $studentCounts = TeacherStudentRelationship::active()
            ->whereIn('teacher_id', $relationships->pluck('teacher_id'))
            ->selectRaw('teacher_id, count(*) as students')
            ->groupBy('teacher_id')
            ->pluck('students', 'teacher_id');

        $invitations = SchoolTeacherInvitation::pending()
            ->where('school_id', $school->id)
            ->latest()
            ->get();

        return view('school.teachers.index', [
            'relationships' => $relationships,
            'studentCounts' => $studentCounts,
            'invitations' => $invitations,
            'capabilities' => $this->capabilities->capabilities($school),
        ]);
    }

    public function show(Request $request, User $teacher)
    {
        $school = $request->user();
        abort_unless($this->capabilities->canManageTeachers($school), 403);

        $relationship = SchoolTeacherRelationship::where('school_id', $school->id)
            ->where('teacher_id', $teacher->id)
            ->where('status', SchoolTeacherRelationship::STATUS_ACTIVE)
            ->firstOrFail();

        // Deleted student accounts drop off the roster.
        $students = TeacherStudentRelationship::with('student')
            ->whereHas('student')
            ->active()
            ->where('teacher_id', $teacher->id)
            ->orderByDesc('approved_at')
            ->get();

        $stats = [
            'active_students' => $students->count(),
            'classes' => TeacherClass::where('teacher_id', $teacher->id)->whereNull('archived_at')->count(),
            'assignments' => TeacherAssignment::where('teacher_id', $teacher->id)->count(),
        ];

        return view('school.teachers.show', [
            'relationship' => $relationship,
            'teacher' => $teacher->load('teacherProfile'),
            'students' => $students,
            'stats' => $stats,
            'capabilities' => $this->capabilities->capabilities($school),
        ]);
    }
}
