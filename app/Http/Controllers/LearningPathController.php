<?php

namespace App\Http\Controllers;

use App\Models\LearningPathExercise;
use App\Models\UserLearningPathProgress;
use App\Services\LearningPathQuestionGenerator;
use Illuminate\Http\Request;

class LearningPathController extends Controller
{
    public function __construct(private LearningPathQuestionGenerator $generator) {}

    public function show(string $slug)
    {
        $exercise = LearningPathExercise::where('slug', $slug)
            ->where('is_active', true)
            ->with('category')
            ->firstOrFail();

        $progress = null;
        if (auth()->check()) {
            $progress = UserLearningPathProgress::where('user_id', auth()->id())
                ->where('learning_path_exercise_id', $exercise->id)
                ->first();
        }

        $category = $exercise->category;

        $prev = LearningPathExercise::where('category_id', $exercise->category_id)
            ->where('sort_order', $exercise->sort_order - 1)
            ->where('is_active', true)
            ->first();

        $next = LearningPathExercise::where('category_id', $exercise->category_id)
            ->where('sort_order', $exercise->sort_order + 1)
            ->where('is_active', true)
            ->first();

        return view('learning-path-exercise', compact('exercise', 'progress', 'category', 'prev', 'next'));
    }

    public function start(Request $request, string $slug)
    {
        $request->validate([
            'question_count' => 'required|integer|in:5,10,15',
        ]);

        $exercise = LearningPathExercise::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $questionCount = (int) $request->question_count;

        if ($exercise->isPremiumVariant($questionCount) && !auth()->user()->isPremium()) {
            return redirect()->route('learning-path.show', $slug)
                ->with('error', 'This question count requires a Premium subscription.');
        }

        // Reset any prior progress so this session starts from zero
        UserLearningPathProgress::where('user_id', auth()->id())
            ->where('learning_path_exercise_id', $exercise->id)
            ->update([
                'total_questions'  => 0,
                'correct_answers'  => 0,
                'completed'        => false,
                'score'            => 0,
                'completed_at'     => null,
            ]);

        $questions = $this->generator->generate($exercise, $questionCount);

        if ($questions->isEmpty()) {
            return redirect()->route('learning-path.show', $slug)
                ->with('error', 'Could not generate questions for this exercise. Please try again.');
        }

        $practiceType = $exercise->config_json['practice_type'];

        $serializedQuestions = $this->generator->serializeForSession($questions);

        session([
            'learning_path_session' => [
                'exercise_id'    => $exercise->id,
                'exercise_slug'  => $slug,
                'question_count' => count($serializedQuestions), // use actual count, not requested
                'questions'      => $serializedQuestions,
                'practice_type'  => $practiceType,
            ],
        ]);

        return redirect("/practice/{$practiceType}");
    }

    public function checkAnswer(Request $request)
    {
        $request->validate([
            'question_index'       => 'required|integer|min:0',
            'answer'               => 'required|string|max:1000',
            'exercise_id'          => 'required|integer',
            'is_last_question'     => 'sometimes|boolean',
        ]);

        $lp = session('learning_path_session');

        if (!$lp || ($lp['exercise_id'] ?? null) !== (int) $request->exercise_id) {
            return response()->json(['error' => 'Session expired or invalid.'], 422);
        }

        $idx = (int) $request->question_index;
        $questionData = $lp['questions'][$idx] ?? null;

        if ($questionData === null) {
            return response()->json(['error' => 'Question not found in session.'], 404);
        }

        $practiceType = $lp['practice_type'];
        $correct      = $this->generator->getAnswerFromSessionQuestion($questionData, $practiceType);
        $answer       = trim($request->answer);

        $isCorrect = strtolower(preg_replace('/\s+/', '', $answer))
            === strtolower(preg_replace('/\s+/', '', $correct));

        $exercise = LearningPathExercise::find($request->exercise_id);

        $progress = UserLearningPathProgress::firstOrCreate(
            [
                'user_id'                    => auth()->id(),
                'learning_path_exercise_id'  => $request->exercise_id,
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

        $isLastQuestion = $request->boolean('is_last_question', false)
            || ($idx + 1) >= $lp['question_count'];

        if ($isLastQuestion) {
            $progress->completed    = true;
            $progress->completed_at = now();
            session()->forget('learning_path_session');
        }

        $progress->save();

        return response()->json([
            'is_correct'    => $isCorrect,
            'correctAnswer' => $correct,
            'xp'            => $progress->correct_answers * 10,
            'correctCount'  => $progress->correct_answers,
            'totalCount'    => $progress->total_questions,
            'completed'     => $isLastQuestion,
            'score'         => $progress->score,
        ]);
    }
}
