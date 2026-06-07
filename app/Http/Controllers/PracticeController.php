<?php

namespace App\Http\Controllers;

use App\Models\ChordPractice;
use App\Models\DailyExerciseCount;
use App\Models\IntervalComparisonPractice;
use App\Models\IntervalDirectionPractice;
use App\Models\MelodicDictationPractice;
use App\Models\MelodicIntervalPractice;
use App\Models\HarmonicIntervalPractice;
use App\Models\IntervalConstructionPractice;
use App\Models\Practice;
use App\Models\RhythmPractice;
use App\Models\ScalePractice;
use App\Models\SingleNotePractice;
use App\Models\UserIntervalStat;
use App\Models\UserLearningPathProgress;
use App\Models\UserPractice;
use App\Services\LearningPathQuestionGenerator;
use App\Services\MusicTheoryService;
use Illuminate\Http\Request;

class PracticeController extends Controller
{
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

    public function getSingleNotePractices() {
        $singleNotePractices = SingleNotePractice::all();
        return response()->json($singleNotePractices);
    }

    // Slug-based routing for new practice types
    protected static array $slugModels = [
        'chord-practice'    => ChordPractice::class,
        'scale-practice'    => ScalePractice::class,
        'rhythm-practice'   => RhythmPractice::class,
        'melodic-dictation' => MelodicDictationPractice::class,
    ];

    protected static array $slugTargetFields = [
        'chord-practice'    => 'chord_type',
        'scale-practice'    => 'scale_type',
        'rhythm-practice'   => 'note_values',
        'melodic-dictation' => 'notes',
    ];

    public function checkAnswer(Request $request) {
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

        if (!$question) {
            return response()->json(['error' => 'Question not found'], 404);
        }

        $targetField = self::$targetFields[$practiceId];
        $target = $question->{$targetField};

        // For interval direction: derive from actual note pitches, not stored label
        if ($practiceId === 2) {
            $octave1 = (int) ($question->octave ?? 4);
            $octave2 = (int) ($question->note2_octave ?? $octave1);
            $target  = app(MusicTheoryService::class)->getDirection($question->note1, $octave1, $question->note2, $octave2);
        }

        // For interval construction: accept enharmonic equivalents
        if ($practiceId === 6) {
            $music = app(MusicTheoryService::class);
            $isCorrect = strtolower(trim($answer)) === strtolower(trim($target))
                || $music->notesAreEnharmonic(trim($answer), trim($target));
        } else {
            $isCorrect = strtolower(trim($answer)) === strtolower(trim($target));
        }

        $correctCount = 0;
        $totalCount = 0;
        if ($userId = auth()->id()) {
            $userPractice = UserPractice::firstOrCreate(
                ['user_id' => $userId, 'practice_id' => $practiceId],
                ['total_questions' => 0, 'correct_answers' => 0, 'incorrect_answers' => 0, 'score' => 0]
            );
            $userPractice->total_questions++;
            if ($isCorrect) { $userPractice->correct_answers++; }
            else { $userPractice->incorrect_answers++; }
            $userPractice->score = $userPractice->total_questions > 0
                ? ($userPractice->correct_answers / $userPractice->total_questions) * 100 : 0;
            $userPractice->save();
            DailyExerciseCount::incrementCount($userId, $practiceId);
            $correctCount = $userPractice->correct_answers;
            $totalCount   = $userPractice->total_questions;
        }

        $this->recordIntervalStat($practiceId, $question->getAttributes(), $isCorrect);

        return response()->json([
            'is_correct' => $isCorrect,
            'correctAnswer' => $target,
            'xp' => $correctCount * 10,
            'correctCount' => $correctCount,
            'totalCount' => $totalCount,
        ]);
    }

    /**
     * Record per-interval accuracy for a single answered question.
     * No-op for non-interval practice types (resolver returns null).
     */
    protected function recordIntervalStat(?int $practiceId, array $data, bool $isCorrect): void
    {
        if (!$practiceId) {
            return;
        }

        $slug = array_search($practiceId, self::$slugToPracticeId, true);
        if ($slug === false) {
            return;
        }

        $interval = app(MusicTheoryService::class)->intervalForStats($data, $slug);
        if ($interval === null) {
            return;
        }

        if ($userId = auth()->id()) {
            UserIntervalStat::record($userId, $practiceId, $interval, $isCorrect);
        }
    }

    protected function checkLPAnswer(Request $request, array $lp, int $idx): \Illuminate\Http\JsonResponse
    {
        $request->validate(['answer' => 'required|string|max:1000']);

        $questionData = $lp['questions'][$idx];
        $practiceType = $lp['practice_type'];

        /** @var LearningPathQuestionGenerator $generator */
        $generator = app(LearningPathQuestionGenerator::class);
        $correct   = $generator->getAnswerFromSessionQuestion($questionData, $practiceType);
        $answer    = trim($request->answer);

        $normalizedAnswer  = strtolower(preg_replace('/\s+/', '', $answer));
        $normalizedCorrect = strtolower(preg_replace('/\s+/', '', $correct));
        $isCorrect = $normalizedAnswer === $normalizedCorrect;

        if (!$isCorrect && $practiceType === 'interval-construction-practice') {
            $isCorrect = app(MusicTheoryService::class)->notesAreEnharmonic($answer, $correct);
        }

        $progress = UserLearningPathProgress::firstOrCreate(
            [
                'user_id'                   => auth()->id(),
                'learning_path_exercise_id' => $lp['exercise_id'],
            ],
            [
                'question_count_attempted' => $lp['question_count'],
                'total_questions'          => 0,
                'correct_answers'          => 0,
                'score'                    => 0,
                'completed'                => false,
            ]
        );

        $progress->total_questions++;
        if ($isCorrect) {
            $progress->correct_answers++;
        }
        $progress->question_count_attempted = $lp['question_count'];
        $progress->score = $progress->total_questions > 0
            ? round(($progress->correct_answers / $progress->total_questions) * 100, 2)
            : 0;

        $isLast = ($idx + 1) >= $lp['question_count'];
        if ($isLast) {
            $progress->completed    = true;
            $progress->completed_at = now();
            session()->forget('learning_path_session');
        }

        $progress->save();

        $this->recordIntervalStat(self::$slugToPracticeId[$practiceType] ?? null, $questionData, $isCorrect);

        return response()->json([
            'is_correct'    => $isCorrect,
            'correctAnswer' => $correct,
            'xp'            => $progress->correct_answers * 10,
            'correctCount'  => $progress->correct_answers,
            'totalCount'    => $progress->total_questions,
            'completed'     => $isLast,
            'score'         => $progress->score,
        ]);
    }

    protected function checkExercisePracticeAnswer(Request $request, array $ep, int $idx): \Illuminate\Http\JsonResponse
    {
        $request->validate(['answer' => 'required|string|max:1000']);

        $questionData = $ep['questions'][$idx];
        $practiceType = $ep['practice_type'];

        $generator = app(LearningPathQuestionGenerator::class);
        $correct   = $generator->getAnswerFromSessionQuestion($questionData, $practiceType);
        $answer    = trim($request->answer);

        $normalizedAnswer  = strtolower(preg_replace('/\s+/', '', $answer));
        $normalizedCorrect = strtolower(preg_replace('/\s+/', '', $correct));
        $isCorrect = $normalizedAnswer === $normalizedCorrect;

        // For interval construction: also accept enharmonic equivalents
        if (!$isCorrect && $practiceType === 'interval-construction-practice') {
            $isCorrect = app(MusicTheoryService::class)->notesAreEnharmonic($answer, $correct);
        }

        $correctCount = 0;
        $totalCount = 0;
        if ($userId = auth()->id()) {
            $practiceId   = Practice::where('slug', $practiceType)->value('id');
            $userPractice = UserPractice::firstOrCreate(
                ['user_id' => $userId, 'practice_id' => $practiceId],
                ['total_questions' => 0, 'correct_answers' => 0, 'incorrect_answers' => 0, 'score' => 0]
            );
            $userPractice->total_questions++;
            if ($isCorrect) {
                $userPractice->correct_answers++;
            } else {
                $userPractice->incorrect_answers++;
            }
            $userPractice->score = $userPractice->total_questions > 0
                ? ($userPractice->correct_answers / $userPractice->total_questions) * 100 : 0;
            $userPractice->save();
            $correctCount = $userPractice->correct_answers;
            $totalCount   = $userPractice->total_questions;
        }

        $this->recordIntervalStat(self::$slugToPracticeId[$practiceType] ?? null, $questionData, $isCorrect);

        return response()->json([
            'is_correct'    => $isCorrect,
            'correctAnswer' => $correct,
            'xp'            => $correctCount * 10,
            'correctCount'  => $correctCount,
            'totalCount'    => $totalCount,
        ]);
    }

    protected function checkAnswerBySlug(Request $request, string $slug)
    {
        $request->validate([
            'question_id' => 'required|integer',
            'answer'      => 'required|string|max:1000',
        ]);

        $modelClass = self::$slugModels[$slug];
        $question   = $modelClass::find($request->question_id);

        if (!$question) {
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

        $correctCount = 0;
        $totalCount = 0;
        if ($userId = auth()->id()) {
            $practice = Practice::where('slug', $slug)->first();
            if ($practice) {
                $userPractice = UserPractice::firstOrCreate(
                    ['user_id' => $userId, 'practice_id' => $practice->id],
                    ['total_questions' => 0, 'correct_answers' => 0, 'incorrect_answers' => 0, 'score' => 0]
                );
                $userPractice->total_questions++;
                if ($isCorrect) { $userPractice->correct_answers++; }
                else { $userPractice->incorrect_answers++; }
                $userPractice->score = $userPractice->total_questions > 0
                    ? ($userPractice->correct_answers / $userPractice->total_questions) * 100 : 0;
                $userPractice->save();
                $correctCount = $userPractice->correct_answers;
                $totalCount   = $userPractice->total_questions;
            }
        }

        return response()->json([
            'is_correct'   => $isCorrect,
            'correctAnswer'=> $target,
            'xp'           => $correctCount * 10,
            'correctCount' => $correctCount,
            'totalCount'   => $totalCount,
        ]);
    }

    public static function getProgressByPracticeId(int $practiceId) {
        $userId = auth()->user()->id;
        return UserPractice::where('user_id', $userId)
            ->where('practice_id', $practiceId)
            ->get();
    }

    public static function getIntervalDirectionProgress() {
        return self::getProgressByPracticeId(2);
    }

    public static function getSingleNoteProgress() {
        return self::getProgressByPracticeId(1);
    }

    public static function getIntervalComparisonProgress() {
        return self::getProgressByPracticeId(3);
    }

    public static function getMelodicIntervalProgress() {
        return self::getProgressByPracticeId(4);
    }

    public static function getHarmonicIntervalProgress() {
        return self::getProgressByPracticeId(5);
    }

    public static function getIntervalConstructionProgress() {
        return self::getProgressByPracticeId(6);
    }

    public static function getPracticeProgressByUser($slug) {
        $practiceId = self::$slugToPracticeId[$slug] ?? null;
        if (!$practiceId) {
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
