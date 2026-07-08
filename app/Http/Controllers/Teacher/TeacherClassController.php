<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherAssignmentRecipient;
use App\Models\TeacherClass;
use App\Models\TeacherStudentRelationship;
use App\Models\User;
use App\Services\Teacher\TeacherCapabilityService;
use Illuminate\Http\Request;

class TeacherClassController extends Controller
{
    public function __construct(private TeacherCapabilityService $capabilities) {}

    public function index(Request $request)
    {
        abort_unless($this->capabilities->canCreateClasses($request->user()), 403);

        $classes = TeacherClass::withCount('students')
            ->where('teacher_id', $request->user()->id)
            ->orderByRaw('archived_at is null desc')
            ->orderBy('name')
            ->get();

        return view('teacher.classes.index', ['classes' => $classes]);
    }

    public function store(Request $request)
    {
        abort_unless($this->capabilities->canCreateClasses($request->user()), 403);

        $validated = $this->validateClass($request);

        TeacherClass::create($validated + ['teacher_id' => $request->user()->id]);

        return back()->with('status', 'class-created');
    }

    public function show(Request $request, TeacherClass $class)
    {
        $this->authorizeClass($request, $class);

        $students = $class->students()->orderBy('name')->get();

        // Class performance summary from this teacher's assignment results.
        $recipients = TeacherAssignmentRecipient::with('assignment')
            ->where('teacher_class_id', $class->id)
            ->get();

        $completed = $recipients->where('status', TeacherAssignmentRecipient::STATUS_COMPLETED);

        $activeStudentIds = TeacherStudentRelationship::active()
            ->where('teacher_id', $request->user()->id)
            ->pluck('student_id');

        $addableStudents = User::whereIn('id', $activeStudentIds)
            ->whereNotIn('id', $students->pluck('id'))
            ->orderBy('name')
            ->get(['id', 'name', 'surname']);

        return view('teacher.classes.show', [
            'class' => $class,
            'students' => $students,
            'addableStudents' => $addableStudents,
            'summary' => [
                'assignments' => $recipients->pluck('teacher_assignment_id')->unique()->count(),
                'completed' => $completed->count(),
                'average_score' => $completed->whereNotNull('best_score')->avg('best_score'),
            ],
        ]);
    }

    public function update(Request $request, TeacherClass $class)
    {
        $this->authorizeClass($request, $class);

        $class->update($this->validateClass($request));

        return back()->with('status', 'class-updated');
    }

    public function addStudent(Request $request, TeacherClass $class)
    {
        $this->authorizeClass($request, $class);
        abort_if($class->isArchived(), 403);

        $request->validate(['student_id' => 'required|integer|exists:users,id']);

        $studentId = $request->integer('student_id');

        // Only students with an active approved relationship may join a class.
        abort_unless($request->user()->hasActiveStudent($studentId), 403);

        $class->students()->syncWithoutDetaching([$studentId]);

        return back()->with('status', 'student-added');
    }

    public function removeStudent(Request $request, TeacherClass $class, User $student)
    {
        $this->authorizeClass($request, $class);

        $class->students()->detach($student->id);

        return back()->with('status', 'student-removed');
    }

    public function archive(Request $request, TeacherClass $class)
    {
        $this->authorizeClass($request, $class);

        $class->update(['archived_at' => $class->isArchived() ? null : now()]);

        return back()->with('status', $class->isArchived() ? 'class-archived' : 'class-restored');
    }

    private function validateClass(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:2000',
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'instrument_focus' => 'nullable|string|max:100',
        ]);
    }

    private function authorizeClass(Request $request, TeacherClass $class): void
    {
        abort_unless($this->capabilities->canCreateClasses($request->user()), 403);
        abort_unless($class->teacher_id === $request->user()->id, 403);
    }
}
