<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ExerciseSession;
use App\Models\GameScore;
use App\Models\TeacherAssignmentRecipient;
use App\Models\TeacherClass;
use App\Models\TeacherStudentInvitation;
use App\Models\TeacherStudentNote;
use App\Models\TeacherStudentRelationship;
use App\Models\TeacherStudentReward;
use App\Models\TeacherStudentTag;
use App\Models\User;
use App\Models\UserLearningPathProgress;
use App\Models\UserPractice;
use App\Notifications\Teacher\StudentRewardReceived;
use App\Services\Teacher\TeacherCapabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeacherStudentController extends Controller
{
    public function __construct(private TeacherCapabilityService $capabilities) {}

    public function index(Request $request)
    {
        abort_unless($this->capabilities->canManageStudents($request->user()), 403);

        $teacher = $request->user();

        $relationships = TeacherStudentRelationship::with('student')
            ->where('teacher_id', $teacher->id)
            ->whereIn('status', [
                TeacherStudentRelationship::STATUS_ACTIVE,
                TeacherStudentRelationship::STATUS_PENDING_STUDENT_APPROVAL,
            ])
            ->orderByDesc('approved_at')
            ->get();

        // Optional filters: class + tag.
        $classId = $request->integer('class');
        $tagId = $request->integer('tag');

        if ($classId) {
            $classStudentIds = TeacherClass::where('teacher_id', $teacher->id)
                ->where('id', $classId)->first()?->students()->pluck('users.id') ?? collect();
            $relationships = $relationships->whereIn('student_id', $classStudentIds->all());
        }

        if ($tagId) {
            $tagStudentIds = TeacherStudentTag::where('teacher_id', $teacher->id)
                ->where('id', $tagId)->first()?->students()->pluck('users.id') ?? collect();
            $relationships = $relationships->whereIn('student_id', $tagStudentIds->all());
        }

        $invitations = TeacherStudentInvitation::with('teacherClass')
            ->where('teacher_id', $teacher->id)
            ->pending()
            ->orderByDesc('created_at')
            ->get();

        return view('teacher.students.index', [
            'relationships' => $relationships,
            'invitations' => $invitations,
            'classes' => TeacherClass::active()->where('teacher_id', $teacher->id)->orderBy('name')->get(),
            'tags' => TeacherStudentTag::where('teacher_id', $teacher->id)->orderBy('name')->get(),
            'filterClass' => $classId,
            'filterTag' => $tagId,
        ]);
    }

    public function show(Request $request, User $student)
    {
        Gate::authorize('view-student-data', $student);

        $teacher = $request->user();

        $relationship = TeacherStudentRelationship::where('teacher_id', $teacher->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $practiceStats = UserPractice::where('user_id', $student->id)->get();
        $totalQuestions = (int) $practiceStats->sum('total_questions');
        $totalCorrect = (int) $practiceStats->sum('correct_answers');

        $assignmentRecipients = TeacherAssignmentRecipient::with('assignment')
            ->where('student_id', $student->id)
            ->whereHas('assignment', fn ($q) => $q->where('teacher_id', $teacher->id))
            ->orderByDesc('created_at')
            ->get();

        $completedAssignments = $assignmentRecipients->where('status', TeacherAssignmentRecipient::STATUS_COMPLETED);

        return view('teacher.students.show', [
            'student' => $student,
            'relationship' => $relationship,
            'stats' => [
                'total_questions' => $totalQuestions,
                'accuracy' => $totalQuestions > 0 ? round($totalCorrect / $totalQuestions * 100, 1) : null,
                'exercise_sessions' => ExerciseSession::where('user_id', $student->id)->count(),
                'lp_completed' => UserLearningPathProgress::where('user_id', $student->id)->where('completed', true)->count(),
                'game_best' => GameScore::where('user_id', $student->id)->max('score'),
            ],
            'recentSessions' => ExerciseSession::where('user_id', $student->id)
                ->orderByDesc('created_at')->limit(10)->get(),
            'lpProgress' => UserLearningPathProgress::with('exercise')
                ->where('user_id', $student->id)->orderByDesc('updated_at')->limit(10)->get(),
            'recentGames' => GameScore::where('user_id', $student->id)
                ->orderByDesc('created_at')->limit(5)->get(),
            'assignmentRecipients' => $assignmentRecipients,
            'assignmentStats' => [
                'assigned' => $assignmentRecipients->count(),
                'completed' => $completedAssignments->count(),
                'overdue' => $assignmentRecipients->filter(fn ($r) => $r->isOverdue())->count(),
                'average_score' => $completedAssignments->whereNotNull('best_score')->avg('best_score'),
            ],
            'notes' => TeacherStudentNote::where('teacher_id', $teacher->id)
                ->where('student_id', $student->id)->orderByDesc('created_at')->get(),
            'tags' => TeacherStudentTag::where('teacher_id', $teacher->id)->orderBy('name')->get(),
            'studentTagIds' => TeacherStudentTag::where('teacher_id', $teacher->id)
                ->whereHas('students', fn ($q) => $q->where('users.id', $student->id))
                ->pluck('id')->all(),
            'rewards' => TeacherStudentReward::where('teacher_id', $teacher->id)
                ->where('student_id', $student->id)->orderByDesc('created_at')->get(),
            'classes' => TeacherClass::active()->where('teacher_id', $teacher->id)->orderBy('name')->get(),
            'studentClassIds' => TeacherClass::where('teacher_id', $teacher->id)
                ->whereHas('students', fn ($q) => $q->where('users.id', $student->id))
                ->pluck('id')->all(),
        ]);
    }

    public function storeNote(Request $request, User $student)
    {
        Gate::authorize('view-student-data', $student);

        $request->validate(['body' => 'required|string|max:5000']);

        TeacherStudentNote::create([
            'teacher_id' => $request->user()->id,
            'student_id' => $student->id,
            'body' => $request->body,
        ]);

        return back()->with('status', 'note-added');
    }

    public function destroyNote(Request $request, TeacherStudentNote $note)
    {
        abort_unless($note->teacher_id === $request->user()->id, 403);

        $note->delete();

        return back()->with('status', 'note-deleted');
    }

    public function storeTag(Request $request)
    {
        abort_unless($this->capabilities->canManageStudents($request->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        TeacherStudentTag::firstOrCreate(
            ['teacher_id' => $request->user()->id, 'name' => trim($validated['name'])],
            ['color' => $validated['color'] ?? null],
        );

        return back()->with('status', 'tag-created');
    }

    public function syncTags(Request $request, User $student)
    {
        Gate::authorize('view-student-data', $student);

        $validated = $request->validate([
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer',
        ]);

        $teacherTagIds = TeacherStudentTag::where('teacher_id', $request->user()->id)->pluck('id');
        $selected = collect($validated['tag_ids'] ?? [])->map(fn ($id) => (int) $id)
            ->intersect($teacherTagIds);

        foreach ($teacherTagIds as $tagId) {
            $tag = TeacherStudentTag::find($tagId);
            if ($selected->contains($tagId)) {
                $tag->students()->syncWithoutDetaching([$student->id]);
            } else {
                $tag->students()->detach($student->id);
            }
        }

        return back()->with('status', 'tags-updated');
    }

    public function storeReward(Request $request, User $student)
    {
        Gate::authorize('view-student-data', $student);

        $validated = $request->validate([
            'type' => 'required|in:sticker,badge,label,milestone',
            'label' => 'required|string|max:100',
            'icon' => 'nullable|string|max:50',
            'note' => 'nullable|string|max:500',
        ]);

        $reward = TeacherStudentReward::create([
            'teacher_id' => $request->user()->id,
            'student_id' => $student->id,
            'type' => $validated['type'],
            'label' => $validated['label'],
            'icon' => $validated['icon'] ?? null,
            'note' => $validated['note'] ?? null,
        ]);

        $student->notify(new StudentRewardReceived($request->user(), $reward));

        return back()->with('status', 'reward-given');
    }
}
