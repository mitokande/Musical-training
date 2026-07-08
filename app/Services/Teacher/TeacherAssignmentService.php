<?php

namespace App\Services\Teacher;

use App\Models\LearningPathExercise;
use App\Models\TeacherAssignment;
use App\Models\TeacherAssignmentAttempt;
use App\Models\TeacherAssignmentQuestion;
use App\Models\TeacherAssignmentRecipient;
use App\Models\TeacherClass;
use App\Models\TeacherStudentReward;
use App\Models\User;
use App\Notifications\Teacher\StudentAssignmentCompleted;
use App\Notifications\Teacher\StudentAssignmentReceived;
use App\Notifications\Teacher\StudentRewardReceived;
use App\Services\LearningPathQuestionGenerator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Assignment lifecycle: draft → question generation (canonical pipeline) →
 * immutable snapshots → send to recipients → student attempts → results.
 *
 * Questions always come from LearningPathQuestionGenerator. Snapshots are
 * serialized with serializeForSession() and never mutated after send —
 * playback, staff rendering, options, and evaluation all read the snapshot.
 */
class TeacherAssignmentService
{
    public function __construct(
        private LearningPathQuestionGenerator $generator,
        private TeacherAssignmentConfigFactory $configFactory,
    ) {}

    /**
     * (Re)generate the full question set for a draft assignment and store
     * snapshots. Fails loudly when the config cannot produce questions.
     */
    public function generateQuestions(TeacherAssignment $assignment): void
    {
        if ($assignment->questionsLocked()) {
            throw new InvalidArgumentException(__('teacher.assignments.error_locked'));
        }

        $questions = $this->generator->generate($this->exerciseFor($assignment), $assignment->question_count);

        if ($questions->isEmpty()) {
            throw new InvalidArgumentException(__('teacher.assignments.error_generation_failed'));
        }

        $serialized = $this->generator->serializeForSession($questions);

        DB::transaction(function () use ($assignment, $serialized) {
            $assignment->questions()->delete();

            foreach (array_values($serialized) as $i => $data) {
                TeacherAssignmentQuestion::create([
                    'teacher_assignment_id' => $assignment->id,
                    'position' => $i + 1,
                    'question_data' => $data,
                ]);
            }

            $assignment->update(['question_count' => count($serialized)]);
        });
    }

    /** Regenerate a single question in a draft, keeping the rest untouched. */
    public function regenerateQuestion(TeacherAssignment $assignment, TeacherAssignmentQuestion $question): void
    {
        if ($assignment->questionsLocked()) {
            throw new InvalidArgumentException(__('teacher.assignments.error_locked'));
        }

        $fresh = $this->generator->generate($this->exerciseFor($assignment), 1);

        if ($fresh->isEmpty()) {
            throw new InvalidArgumentException(__('teacher.assignments.error_generation_failed'));
        }

        $serialized = $this->generator->serializeForSession($fresh);

        $question->update(['question_data' => array_values($serialized)[0]]);
    }

    /** Remove a single question from a draft and close the position gap. */
    public function removeQuestion(TeacherAssignment $assignment, TeacherAssignmentQuestion $question): void
    {
        if ($assignment->questionsLocked()) {
            throw new InvalidArgumentException(__('teacher.assignments.error_locked'));
        }

        DB::transaction(function () use ($assignment, $question) {
            $question->delete();

            $assignment->questions()->get()->values()->each(
                fn (TeacherAssignmentQuestion $q, int $i) => $q->update(['position' => $i + 1])
            );

            $assignment->update(['question_count' => $assignment->questions()->count()]);
        });
    }

    /**
     * Send the assignment to students and/or whole classes. Only students
     * with an active approved relationship become recipients. Snapshots are
     * frozen from this point on.
     */
    public function send(TeacherAssignment $assignment, User $teacher, array $studentIds, array $classIds): int
    {
        if ($assignment->isSent()) {
            throw new InvalidArgumentException(__('teacher.assignments.error_already_sent'));
        }

        if ($assignment->type !== TeacherAssignment::TYPE_PRACTICE_GOAL && $assignment->questions()->count() === 0) {
            throw new InvalidArgumentException(__('teacher.assignments.error_no_questions'));
        }

        // student_id → class_id (null for directly selected students)
        $recipients = collect($studentIds)->mapWithKeys(fn ($id) => [(int) $id => null]);

        foreach ($classIds as $classId) {
            $class = TeacherClass::where('teacher_id', $teacher->id)->find($classId);
            if (! $class) {
                continue;
            }
            foreach ($class->students()->pluck('users.id') as $studentId) {
                if (! $recipients->has((int) $studentId)) {
                    $recipients->put((int) $studentId, $class->id);
                }
            }
        }

        // Only active approved relationships may receive private assignments.
        $activeIds = $teacher->studentRelationships()->active()->pluck('student_id')->map(fn ($id) => (int) $id);
        $recipients = $recipients->only($activeIds->all());

        if ($recipients->isEmpty()) {
            throw new InvalidArgumentException(__('teacher.assignments.error_no_recipients'));
        }

        DB::transaction(function () use ($assignment, $recipients) {
            foreach ($recipients as $studentId => $classId) {
                TeacherAssignmentRecipient::firstOrCreate(
                    ['teacher_assignment_id' => $assignment->id, 'student_id' => $studentId],
                    ['teacher_class_id' => $classId],
                );
            }

            $assignment->update([
                'status' => TeacherAssignment::STATUS_SENT,
                'sent_at' => now(),
            ]);
        });

        foreach (User::whereIn('id', $recipients->keys())->get() as $student) {
            $student->notify(new StudentAssignmentReceived($assignment));
        }

        return $recipients->count();
    }

    /**
     * Start (or resume) an attempt and build the practice session payload.
     * The caller puts the payload into session('teacher_assignment_session').
     */
    public function startAttempt(TeacherAssignmentRecipient $recipient): array
    {
        $assignment = $recipient->assignment;

        if (! $assignment->isSent()) {
            throw new InvalidArgumentException(__('teacher.assignments.error_not_available'));
        }

        if ($assignment->starts_at !== null && $assignment->starts_at->isFuture()) {
            throw new InvalidArgumentException(__('teacher.assignments.error_not_started_yet'));
        }

        if (! $recipient->canAttempt()) {
            throw new InvalidArgumentException(__('teacher.assignments.error_no_attempts_left'));
        }

        $questions = $assignment->questions->sortBy('position')->pluck('question_data')->values()->all();

        if ($questions === []) {
            throw new InvalidArgumentException(__('teacher.assignments.error_no_questions'));
        }

        $attempt = DB::transaction(function () use ($recipient) {
            $recipient->increment('attempts_count');

            if ($recipient->status === TeacherAssignmentRecipient::STATUS_ASSIGNED) {
                $recipient->update([
                    'status' => TeacherAssignmentRecipient::STATUS_STARTED,
                    'started_at' => $recipient->started_at ?? now(),
                ]);
            }

            return TeacherAssignmentAttempt::create([
                'recipient_id' => $recipient->id,
                'attempt_number' => $recipient->attempts_count,
                'question_count' => count($recipient->assignment->questions),
                'started_at' => now(),
                'answers' => [],
            ]);
        });

        return [
            'assignment_id' => $assignment->id,
            'recipient_id' => $recipient->id,
            'attempt_id' => $attempt->id,
            'practice_type' => $assignment->practice_type,
            'question_count' => count($questions),
            'questions' => $questions,
        ];
    }

    /**
     * Record one answered question on the attempt; finalize when it was the
     * last question. Returns true when the attempt is now complete.
     */
    public function recordAnswer(int $attemptId, int $index, string $given, string $correct, bool $isCorrect): bool
    {
        $attempt = TeacherAssignmentAttempt::with('recipient.assignment.teacher')->findOrFail($attemptId);

        $answers = $attempt->answers ?? [];
        $answers[$index] = [
            'given' => $given,
            'correct' => $correct,
            'is_correct' => $isCorrect,
            'answered_at' => now()->toIso8601String(),
        ];

        $correctCount = collect($answers)->where('is_correct', true)->count();
        $isLast = count($answers) >= $attempt->question_count;

        $attempt->fill([
            'answers' => $answers,
            'correct_count' => $correctCount,
        ]);

        if ($isLast) {
            $score = $attempt->question_count > 0
                ? round($correctCount / $attempt->question_count * 100, 2)
                : 0;

            $attempt->fill([
                'score' => $score,
                'completed_at' => now(),
                'duration_seconds' => $attempt->started_at ? (int) abs(now()->diffInSeconds($attempt->started_at)) : null,
            ]);
        }

        $attempt->save();

        if ($isLast) {
            $this->finalizeAttempt($attempt);
        }

        return $isLast;
    }

    private function finalizeAttempt(TeacherAssignmentAttempt $attempt): void
    {
        $recipient = $attempt->recipient;
        $assignment = $recipient->assignment;

        $firstCompletion = $recipient->status !== TeacherAssignmentRecipient::STATUS_COMPLETED;

        $recipient->update([
            'status' => TeacherAssignmentRecipient::STATUS_COMPLETED,
            'completed_at' => $recipient->completed_at ?? now(),
            'best_score' => max((float) ($recipient->best_score ?? 0), (float) $attempt->score),
        ]);

        $assignment->teacher->notify(new StudentAssignmentCompleted($assignment, $recipient, $attempt));

        // Optional reward attached to the assignment — granted on first completion.
        if ($firstCompletion && $assignment->reward_label) {
            $reward = TeacherStudentReward::create([
                'teacher_id' => $assignment->teacher_id,
                'student_id' => $recipient->student_id,
                'teacher_assignment_id' => $assignment->id,
                'type' => TeacherStudentReward::TYPE_STICKER,
                'label' => $assignment->reward_label,
            ]);

            $recipient->student->notify(new StudentRewardReceived($assignment->teacher, $reward));
        }

        // Mark the whole assignment completed when every recipient finished.
        $unfinished = $assignment->recipients()
            ->where('status', '!=', TeacherAssignmentRecipient::STATUS_COMPLETED)
            ->exists();

        if (! $unfinished && $assignment->status === TeacherAssignment::STATUS_SENT) {
            $assignment->update(['status' => TeacherAssignment::STATUS_COMPLETED]);
        }
    }

    /**
     * Ad-hoc LearningPathExercise wrapper so the canonical generator can run
     * from an assignment's stored config (same pattern as Exercise Setup).
     */
    private function exerciseFor(TeacherAssignment $assignment): LearningPathExercise
    {
        if ($assignment->type === TeacherAssignment::TYPE_LEARNING_PATH && $assignment->learning_path_exercise_id) {
            $exercise = LearningPathExercise::find($assignment->learning_path_exercise_id);
            if ($exercise) {
                return $exercise;
            }
        }

        $config = $assignment->config_json;

        if (empty($config)) {
            $config = $this->configFactory->build(
                $assignment->practice_type,
                $assignment->difficulty ?? 'beginner',
            );
        }

        $exercise = new LearningPathExercise;
        $exercise->config_json = $config;

        return $exercise;
    }
}
