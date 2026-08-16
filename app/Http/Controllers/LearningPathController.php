<?php

namespace App\Http\Controllers;

use App\Models\LearningPathExercise;
use App\Models\UserLearningPathProgress;
use App\Services\LearningPathQuestionGenerator;
use App\Services\Practice\PracticeProgressRecorder;
use App\Services\UsageQuotaService;
use Illuminate\Http\Request;

class LearningPathController extends Controller
{
    public function __construct(
        private LearningPathQuestionGenerator $generator,
        private UsageQuotaService $usage,
        private PracticeProgressRecorder $recorder,
    ) {}

    /**
     * Daily learning-path session quota state for the current visitor.
     * Guests: plans.guest.learning_path_daily_sessions per day (server-side
     * guest id). Free users: learning_path_daily_sessions per day. Premium /
     * admin: unlimited.
     */
    private function sessionQuota(Request $request): array
    {
        $user = $request->user();

        if (! $user) {
            $limit = (int) config('plans.guest.learning_path_daily_sessions', 2);

            return [
                'limit' => $limit,
                'used' => $this->usage->guestUsed($request, UsageQuotaService::FEATURE_LP_SESSIONS),
                'guest' => true,
            ];
        }

        if ($user->isAdmin() || $user->isEffectivelyPremium()) {
            return ['limit' => -1, 'used' => 0, 'guest' => false];
        }

        $limit = (int) ($user->getPlanLimit('learning_path_daily_sessions') ?? -1);

        return [
            'limit' => $limit,
            'used' => $limit === -1 ? 0 : $this->usage->userUsed($user, UsageQuotaService::FEATURE_LP_SESSIONS),
            'guest' => false,
        ];
    }

    public function show(Request $request, string $slug)
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

        $sessionQuota = $this->sessionQuota($request);

        $category = $exercise->category;

        $prev = LearningPathExercise::where('category_id', $exercise->category_id)
            ->where('sort_order', $exercise->sort_order - 1)
            ->where('is_active', true)
            ->first();

        $next = LearningPathExercise::where('category_id', $exercise->category_id)
            ->where('sort_order', $exercise->sort_order + 1)
            ->where('is_active', true)
            ->first();

        return view('learning-path-exercise', compact('exercise', 'progress', 'category', 'prev', 'next', 'sessionQuota'));
    }

    public function start(Request $request, string $slug)
    {
        $request->validate([
            'question_count' => 'required|integer|in:5,10,15',
        ]);

        $exercise = LearningPathExercise::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $user = $request->user();
        $questionCount = (int) $request->question_count;

        // Session-size cap: guests and free plans play 5-question packages only.
        $cap = $user
            ? (int) ($user->getPlanLimit('session_question_cap') ?? -1)
            : (int) config('plans.guest.session_question_cap', 5);
        if (! ($user?->isAdmin()) && $cap !== -1) {
            $questionCount = min($questionCount, $cap);
        }

        if ($exercise->isPremiumVariant($questionCount) && ! ($user?->isEffectivelyPremium())) {
            return redirect()->route('learning-path.show', $slug)
                ->with('error', __('app.limits.premium_question_count'));
        }

        // Daily session quota (guests + free plans). Premium/admin unlimited.
        $quota = $this->sessionQuota($request);
        if ($quota['limit'] !== -1 && $quota['used'] >= $quota['limit']) {
            return redirect()->route('learning-path.show', $slug)
                ->with('lp_limit_reached', $quota['guest'] ? 'guest' : 'free');
        }

        // Reset any prior progress so this session starts from zero
        if ($user) {
            UserLearningPathProgress::where('user_id', $user->id)
                ->where('learning_path_exercise_id', $exercise->id)
                ->update([
                    'total_questions' => 0,
                    'correct_answers' => 0,
                    'completed' => false,
                    'score' => 0,
                    'completed_at' => null,
                ]);
        }

        $questions = $this->generator->generate($exercise, $questionCount);

        if ($questions->isEmpty()) {
            return redirect()->route('learning-path.show', $slug)
                ->with('error', __('app.messages.lp_no_questions'));
        }

        $practiceType = $exercise->config_json['practice_type'];

        $serializedQuestions = $this->generator->serializeForSession($questions);

        session([
            'learning_path_session' => [
                'exercise_id' => $exercise->id,
                'exercise_slug' => $slug,
                'question_count' => count($serializedQuestions), // use actual count, not requested
                'questions' => $serializedQuestions,
                'practice_type' => $practiceType,
            ],
        ]);

        // Consume one session from the daily quota (limited plans only).
        if ($quota['limit'] !== -1) {
            $user
                ? $this->usage->userIncrement($user, UsageQuotaService::FEATURE_LP_SESSIONS)
                : $this->usage->guestIncrement($request, UsageQuotaService::FEATURE_LP_SESSIONS);
        }

        return redirect("/practice/{$practiceType}");
    }

    public function checkAnswer(Request $request)
    {
        $request->validate([
            'question_index' => 'required|integer|min:0',
            'answer' => 'required|string|max:1000',
            'exercise_id' => 'required|integer',
            'is_last_question' => 'sometimes|boolean',
        ]);

        $lp = session('learning_path_session');

        if (! $lp || ($lp['exercise_id'] ?? null) !== (int) $request->exercise_id) {
            return response()->json(['error' => 'Session expired or invalid.'], 422);
        }

        $idx = (int) $request->question_index;
        $questionData = $lp['questions'][$idx] ?? null;

        if ($questionData === null) {
            return response()->json(['error' => 'Question not found in session.'], 404);
        }

        $practiceType = $lp['practice_type'];
        $correct = $this->generator->getAnswerFromSessionQuestion($questionData, $practiceType);
        $answer = trim($request->answer);

        $isCorrect = strtolower(preg_replace('/\s+/', '', $answer))
            === strtolower(preg_replace('/\s+/', '', $correct));

        $isLastQuestion = $request->boolean('is_last_question', false)
            || ($idx + 1) >= $lp['question_count'];

        // Guests: grade the answer but never persist progress (nothing is
        // stored permanently for guest sessions).
        if (! auth()->check()) {
            $guestCorrect = (int) session('lp_guest_correct', 0) + ($isCorrect ? 1 : 0);
            $guestTotal = (int) session('lp_guest_total', 0) + 1;
            session(['lp_guest_correct' => $guestCorrect, 'lp_guest_total' => $guestTotal]);

            if ($isLastQuestion) {
                session()->forget(['learning_path_session', 'lp_guest_correct', 'lp_guest_total']);
            }

            return response()->json([
                'is_correct' => $isCorrect,
                'correctAnswer' => $correct,
                'xp' => $guestCorrect * 10,
                'correctCount' => $guestCorrect,
                'totalCount' => $guestTotal,
                'completed' => $isLastQuestion,
                'score' => $guestTotal > 0 ? round(($guestCorrect / $guestTotal) * 100, 2) : 0,
            ]);
        }

        if ($isLastQuestion) {
            session()->forget('learning_path_session');
        }

        $progress = $this->recorder->recordLearningPathAnswer(
            (int) auth()->id(),
            (int) $request->exercise_id,
            (int) $lp['question_count'],
            $isCorrect,
            $isLastQuestion,
        );

        return response()->json([
            'is_correct' => $isCorrect,
            'correctAnswer' => $correct,
            'xp' => $progress->correct_answers * 10,
            'correctCount' => $progress->correct_answers,
            'totalCount' => $progress->total_questions,
            'completed' => $isLastQuestion,
            'score' => $progress->score,
        ]);
    }
}
