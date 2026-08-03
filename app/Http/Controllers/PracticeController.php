<?php

namespace App\Http\Controllers;

use App\Models\ChordPractice;
use App\Models\HarmonicIntervalPractice;
use App\Models\IntervalComparisonPractice;
use App\Models\IntervalConstructionPractice;
use App\Models\IntervalDirectionPractice;
use App\Models\MelodicDictationPractice;
use App\Models\MelodicIntervalPractice;
use App\Models\Practice;
use App\Models\RhythmPractice;
use App\Models\ScalePractice;
use App\Models\SingleNotePractice;
use App\Models\TeacherAssignmentAttempt;
use App\Models\UserPractice;
use App\Services\MusicTheoryService;
use App\Services\Practice\PracticeAnswerGrader;
use App\Services\Practice\PracticeCatalog;
use App\Services\Practice\PracticeProgressRecorder;
use App\Services\Teacher\TeacherAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PracticeController extends Controller
{
    public function __construct(
        private readonly PracticeCatalog $catalog,
        private readonly PracticeAnswerGrader $grader,
        private readonly PracticeProgressRecorder $recorder,
    ) {}

    protected static array $practiceModels = [
        1 => SingleNotePractice::class,
        2 => IntervalDirectionPractice::class,
        3 => IntervalComparisonPractice::class,
        4 => MelodicIntervalPractice::class,
        5 => HarmonicIntervalPractice::class,
        6 => IntervalConstructionPractice::class,
    ];

    protected static array $targetFields = [
        1 => 'target',
        2 => 'direction',
        3 => 'target',
        4 => 'interval',
        5 => 'interval',
        6 => 'note2',
    ];

    protected static array $slugToPracticeId = [
        'single-note-practice' => 1,
        'interval-direction-practice' => 2,
        'interval-comparison-practice' => 3,
        'melodic-interval-practice' => 4,
        'harmonic-interval-practice' => 5,
        'interval-construction-practice' => 6,
    ];

    public function getSingleNotePractices()
    {
        $singleNotePractices = SingleNotePractice::all();

        return response()->json($singleNotePractices);
    }

    // Slug-based routing for new practice types
    protected static array $slugModels = [
        'chord-practice' => ChordPractice::class,
        'scale-practice' => ScalePractice::class,
        'rhythm-practice' => RhythmPractice::class,
        'melodic-dictation' => MelodicDictationPractice::class,
    ];

    protected static array $slugTargetFields = [
        'chord-practice' => 'chord_type',
        'scale-practice' => 'scale_type',
        'rhythm-practice' => 'note_values',
        'melodic-dictation' => 'notes',
    ];

    public function checkAnswer(Request $request)
    {
        // Teacher assignment session — highest priority: the student answers
        // against the assignment's immutable question snapshot.
        $ta = session('teacher_assignment_session');
        if ($ta) {
            $qid = (int) $request->input('question_id', 0);
            if ($qid > 0 && isset($ta['questions'][$qid - 1])) {
                return $this->checkTeacherAssignmentAnswer($request, $ta, $qid - 1);
            }
        }

        // Teacher preview ("solve as a student") — grade against the snapshot
        // but never record an attempt or any stat.
        $tp = session('teacher_assignment_preview_session');
        if ($tp) {
            $qid = (int) $request->input('question_id', 0);
            if ($qid > 0 && isset($tp['questions'][$qid - 1])) {
                return $this->checkTeacherAssignmentPreview($request, $tp, $qid - 1);
            }
        }

        // Exercise-setup free practice (generated questions, no DB IDs) — check first
        // so a stale learning_path_session cannot override the current exercise.
        $ep = session('exercise_practice_session');
        if ($ep) {
            $qid = (int) $request->input('question_id', 0);
            if ($qid > 0 && isset($ep['questions'][$qid - 1])) {
                return $this->checkExercisePracticeAnswer($request, $ep, $qid - 1);
            }
        }

        // LP Mode: if a learning path session is active, intercept before DB lookup
        $lp = session('learning_path_session');
        if ($lp) {
            $qid = (int) $request->input('question_id', 0);
            if ($qid > 0 && isset($lp['questions'][$qid - 1])) {
                return $this->checkLPAnswer($request, $lp, $qid - 1);
            }
        }

        $slug = $request->input('slug');

        // New practice types use slug-based routing
        if ($slug && isset(self::$slugModels[$slug])) {
            return $this->checkAnswerBySlug($request, $slug);
        }

        $validated = $request->validate([
            'practice_id' => 'required|integer|in:1,2,3,4,5,6',
            'question_id' => 'required|integer',
            'answer' => 'required|string|max:255',
        ]);

        $practiceId = $validated['practice_id'];
        $questionId = $validated['question_id'];
        $answer = $validated['answer'];

        $modelClass = self::$practiceModels[$practiceId];
        $question = $modelClass::find($questionId);

        if (! $question) {
            return response()->json(['error' => 'Question not found'], 404);
        }

        $targetField = self::$targetFields[$practiceId];
        $target = $question->{$targetField};

        // For interval direction: derive from actual note pitches, not stored label
        if ($practiceId === 2) {
            $octave1 = (int) ($question->octave ?? 4);
            $octave2 = (int) ($question->note2_octave ?? $octave1);
            $target = app(MusicTheoryService::class)->getDirection($question->note1, $octave1, $question->note2, $octave2);
        }

        // Note-answer types (single note = 1, interval construction = 6):
        // accept enharmonic equivalents — the piano answer keyboard emits
        // sharp names while questions may store flat spellings.
        if (in_array($practiceId, [1, 6], true)) {
            $music = app(MusicTheoryService::class);
            $isCorrect = strtolower(trim($answer)) === strtolower(trim($target))
                || $music->notesAreEnharmonic(trim($answer), trim($target));
        } else {
            $isCorrect = strtolower(trim($answer)) === strtolower(trim($target));
        }

        // The legacy DB path is the only one that counts toward the daily
        // exercise quota and therefore the streak — preserved deliberately.
        $totals = $this->recorder->recordPracticeAnswer(
            auth()->id(),
            $practiceId,
            $isCorrect,
            countsTowardDaily: true,
        );

        $this->recordIntervalStat($practiceId, $question->getAttributes(), $isCorrect);

        return response()->json([
            'is_correct' => $isCorrect,
            'correctAnswer' => $target,
            'xp' => $totals['xp'],
            'correctCount' => $totals['correct_count'],
            'totalCount' => $totals['total_count'],
        ]);
    }

    /**
     * Record per-interval accuracy for a single answered question.
     * No-op for non-interval practice types (resolver returns null).
     */
    protected function recordIntervalStat(?int $practiceId, array $data, bool $isCorrect): void
    {
        $this->recorder->recordIntervalStat(auth()->id(), $practiceId, $data, $isCorrect);
    }

    protected function checkLPAnswer(Request $request, array $lp, int $idx): JsonResponse
    {
        $request->validate(['answer' => 'required|string|max:1000']);

        $questionData = $lp['questions'][$idx];
        $practiceType = $lp['practice_type'];

        ['correct' => $correct, 'is_correct' => $isCorrect] = $this->grader->grade(
            $questionData,
            $practiceType,
            (string) $request->answer,
        );

        $isLast = ($idx + 1) >= $lp['question_count'];
        if ($isLast) {
            session()->forget('learning_path_session');
        }

        $progress = $this->recorder->recordLearningPathAnswer(
            (int) auth()->id(),
            (int) $lp['exercise_id'],
            (int) $lp['question_count'],
            $isCorrect,
            $isLast,
        );

        $this->recordIntervalStat(self::$slugToPracticeId[$practiceType] ?? null, $questionData, $isCorrect);

        return response()->json([
            'is_correct' => $isCorrect,
            'correctAnswer' => $correct,
            'xp' => $progress->correct_answers * 10,
            'correctCount' => $progress->correct_answers,
            'totalCount' => $progress->total_questions,
            'completed' => $isLast,
            'score' => $progress->score,
        ]);
    }

    /**
     * Answer check for teacher assignment sessions. Evaluation reads the
     * immutable snapshot stored at send time via the same canonical
     * getAnswerFromSessionQuestion() used by LP and Exercise Setup — the
     * interval-direction rule and enharmonic handling are shared, not
     * reimplemented.
     */
    protected function checkTeacherAssignmentAnswer(Request $request, array $ta, int $idx): JsonResponse
    {
        $request->validate(['answer' => 'required|string|max:1000']);

        $questionData = $ta['questions'][$idx];
        $practiceType = $ta['practice_type'];
        $answer = trim($request->answer);

        ['correct' => $correct, 'is_correct' => $isCorrect] = $this->grader->grade(
            $questionData,
            $practiceType,
            $answer,
        );

        $service = app(TeacherAssignmentService::class);
        $completed = $service->recordAnswer((int) $ta['attempt_id'], $idx, $answer, $correct, $isCorrect);

        if ($completed) {
            session()->forget('teacher_assignment_session');
        }

        $this->recordIntervalStat(self::$slugToPracticeId[$practiceType] ?? null, $questionData, $isCorrect);

        $attempt = TeacherAssignmentAttempt::find($ta['attempt_id']);

        return response()->json([
            'is_correct' => $isCorrect,
            'correctAnswer' => $correct,
            'xp' => ($attempt?->correct_count ?? 0) * 10,
            'correctCount' => $attempt?->correct_count ?? 0,
            'totalCount' => $idx + 1,
            'completed' => $completed,
            'score' => $attempt?->score !== null ? (float) $attempt->score : null,
        ]);
    }

    /**
     * Answer check for a teacher previewing their own assignment. Grades with
     * the same canonical answer resolution as the real student flow, but writes
     * nothing — no attempt, no recipient, no user/interval stats. The preview
     * session is cleared after the last question.
     */
    protected function checkTeacherAssignmentPreview(Request $request, array $tp, int $idx): JsonResponse
    {
        $request->validate(['answer' => 'required|string|max:1000']);

        $questionData = $tp['questions'][$idx];
        $practiceType = $tp['practice_type'];

        ['correct' => $correct, 'is_correct' => $isCorrect] = $this->grader->grade(
            $questionData,
            $practiceType,
            (string) $request->answer,
        );

        $total = (int) ($tp['question_count'] ?? count($tp['questions']));
        $isLast = ($idx + 1) >= $total;

        // Track the running correct count across the preview run in the session.
        $correctSoFar = (int) ($tp['correct_so_far'] ?? 0) + ($isCorrect ? 1 : 0);
        $tp['correct_so_far'] = $correctSoFar;
        session(['teacher_assignment_preview_session' => $tp]);

        if ($isLast) {
            session()->forget('teacher_assignment_preview_session');
        }

        return response()->json([
            'is_correct' => $isCorrect,
            'correctAnswer' => $correct,
            'xp' => $correctSoFar * 10,
            'correctCount' => $correctSoFar,
            'totalCount' => $idx + 1,
            'completed' => $isLast,
            'score' => $total > 0 ? round($correctSoFar / $total * 100, 2) : null,
            'preview' => true,
        ]);
    }

    protected function checkExercisePracticeAnswer(Request $request, array $ep, int $idx): JsonResponse
    {
        $request->validate(['answer' => 'required|string|max:1000']);

        $questionData = $ep['questions'][$idx];
        $practiceType = $ep['practice_type'];

        ['correct' => $correct, 'is_correct' => $isCorrect] = $this->grader->grade(
            $questionData,
            $practiceType,
            (string) $request->answer,
        );

        // Exercise-setup runs roll into UserPractice but never into the daily
        // exercise count — matching the behaviour this branch has always had.
        $totals = $this->recorder->recordPracticeAnswer(
            auth()->id(),
            Practice::where('slug', $practiceType)->value('id'),
            $isCorrect,
        );

        $this->recordIntervalStat(self::$slugToPracticeId[$practiceType] ?? null, $questionData, $isCorrect);

        // Clear the session once the final question is answered so a finished
        // exercise-setup run cannot linger and hijack later answer checks
        // (this branch is evaluated before LP/DB lookup in checkAnswer()).
        if (($idx + 1) >= ($ep['question_count'] ?? count($ep['questions']))) {
            session()->forget('exercise_practice_session');
        }

        return response()->json([
            'is_correct' => $isCorrect,
            'correctAnswer' => $correct,
            'xp' => $totals['xp'],
            'correctCount' => $totals['correct_count'],
            'totalCount' => $totals['total_count'],
        ]);
    }

    protected function checkAnswerBySlug(Request $request, string $slug)
    {
        $request->validate([
            'question_id' => 'required|integer',
            'answer' => 'required|string|max:1000',
        ]);

        $modelClass = self::$slugModels[$slug];
        $question = $modelClass::find($request->question_id);

        if (! $question) {
            return response()->json(['error' => 'Question not found'], 404);
        }

        $targetField = self::$slugTargetFields[$slug];
        $raw = $question->{$targetField};

        // Normalize target: arrays (note_values, notes) → comma-joined string
        $target = is_array($raw) ? implode(',', $raw) : $raw;

        $answer = trim($request->answer);

        // For rhythm/dictation: normalize whitespace and case
        $isCorrect = strtolower(preg_replace('/\s+/', '', $answer))
            === strtolower(preg_replace('/\s+/', '', $target));

        $totals = $this->recorder->recordPracticeAnswer(
            auth()->id(),
            Practice::where('slug', $slug)->value('id'),
            $isCorrect,
        );

        return response()->json([
            'is_correct' => $isCorrect,
            'correctAnswer' => $target,
            'xp' => $totals['xp'],
            'correctCount' => $totals['correct_count'],
            'totalCount' => $totals['total_count'],
        ]);
    }

    public static function getProgressByPracticeId(int $practiceId)
    {
        $userId = auth()->user()->id;

        return UserPractice::where('user_id', $userId)
            ->where('practice_id', $practiceId)
            ->get();
    }

    public static function getIntervalDirectionProgress()
    {
        return self::getProgressByPracticeId(2);
    }

    public static function getSingleNoteProgress()
    {
        return self::getProgressByPracticeId(1);
    }

    public static function getIntervalComparisonProgress()
    {
        return self::getProgressByPracticeId(3);
    }

    public static function getMelodicIntervalProgress()
    {
        return self::getProgressByPracticeId(4);
    }

    public static function getHarmonicIntervalProgress()
    {
        return self::getProgressByPracticeId(5);
    }

    public static function getIntervalConstructionProgress()
    {
        return self::getProgressByPracticeId(6);
    }

    public static function getPracticeProgressByUser($slug)
    {
        $practiceId = self::$slugToPracticeId[$slug] ?? null;
        if (! $practiceId) {
            return 0;
        }

        $modelClass = self::$practiceModels[$practiceId];
        $userP = self::getProgressByPracticeId($practiceId);

        $solved = 0;
        if (count($userP) > 0) {
            $solved = $userP[0]->total_questions;
        }

        $totalCount = $modelClass::count();
        if ($totalCount === 0) {
            return 0;
        }

        $progress = min(($solved / $totalCount) * 100, 100);

        return round($progress, 1);
    }
}
