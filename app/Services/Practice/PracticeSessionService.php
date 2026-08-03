<?php

namespace App\Services\Practice;

use App\Exceptions\Api\ApiException;
use App\Models\ExerciseSession;
use App\Models\LearningPathExercise;
use App\Models\PracticeSession;
use App\Models\User;
use App\Services\LearningPathQuestionGenerator;
use App\Services\UsageQuotaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Creates, resumes and grades API practice runs.
 *
 * This is the stateless replacement for the web's PHP session keys — the
 * generated questions live in a practice_sessions row instead of
 * session('learning_path_session') / session('exercise_practice_session').
 */
class PracticeSessionService
{
    /** A run left untouched for this long can no longer be answered. */
    private const TTL_HOURS = 6;

    public function __construct(
        private readonly LearningPathQuestionGenerator $generator,
        private readonly StudioConfigMapper $mapper,
        private readonly QuestionPresenter $presenter,
        private readonly PracticeAnswerGrader $grader,
        private readonly PracticeProgressRecorder $recorder,
        private readonly PracticeCatalog $catalog,
        private readonly UsageQuotaService $usage,
    ) {}

    /**
     * Start a free-practice ("studio") run for a practice type.
     */
    public function createStudioSession(User $user, string $practiceType, int $questionCount, array $config): PracticeSession
    {
        if (! $this->catalog->isKnownSlug($practiceType)) {
            throw ApiException::notFound(__('Unknown practice type.'));
        }

        $this->assertQuota($user, UsageQuotaService::FEATURE_STUDIO_SESSIONS, 'studio_daily_sessions');

        $questionCount = $this->clampQuestionCount($user, $questionCount);
        $configJson = $this->mapper->map($practiceType, $config);

        $questions = $this->generate($configJson, $questionCount, $practiceType);

        $session = $this->persist($user, [
            'source' => PracticeSession::SOURCE_STUDIO,
            'practice_type' => $practiceType,
            'config_json' => $configJson,
            'questions_json' => $questions,
            'question_count' => count($questions),
        ]);

        // Keep the web "recent sessions" list truthful about mobile runs too.
        $session->exercise_session_id = ExerciseSession::create([
            'user_id' => $user->id,
            'exercise_type' => $practiceType,
            'difficulty' => $config['difficulty'] ?? 'intermediate',
            'question_count' => count($questions),
            'ai_mode' => false,
            'settings_json' => $config,
            'started_at' => now(),
        ])->id;
        $session->save();

        $this->consumeQuota($user, UsageQuotaService::FEATURE_STUDIO_SESSIONS, 'studio_daily_sessions');

        return $session;
    }

    /**
     * Start a Learning Path lesson run.
     */
    public function createLearningPathSession(User $user, string $exerciseSlug, int $questionCount): PracticeSession
    {
        $exercise = LearningPathExercise::where('slug', $exerciseSlug)->where('is_active', true)->first();

        if (! $exercise) {
            throw ApiException::notFound(__('Lesson not found.'));
        }

        if ($exercise->isPremiumVariant($questionCount) && ! $user->isEffectivelyPremium() && ! $user->isAdmin()) {
            throw ApiException::premiumRequired(
                __('Longer sessions are a Premium feature.'),
                ['question_count' => $questionCount],
            );
        }

        $this->assertQuota($user, UsageQuotaService::FEATURE_LP_SESSIONS, 'learning_path_daily_sessions');

        $questionCount = $this->clampQuestionCount($user, $questionCount);
        $practiceType = $exercise->config_json['practice_type'] ?? null;

        if (! $this->catalog->isKnownSlug($practiceType)) {
            throw ApiException::generationFailed(__('This lesson is not playable yet.'));
        }

        $questions = $this->generateFromExercise($exercise, $questionCount, $practiceType);

        $session = $this->persist($user, [
            'source' => PracticeSession::SOURCE_LEARNING_PATH,
            'practice_type' => $practiceType,
            'learning_path_exercise_id' => $exercise->id,
            'config_json' => $exercise->config_json,
            'questions_json' => $questions,
            'question_count' => count($questions),
        ]);

        $this->consumeQuota($user, UsageQuotaService::FEATURE_LP_SESSIONS, 'learning_path_daily_sessions');

        return $session;
    }

    /**
     * Grade one answer. Idempotent: re-submitting an index that was already
     * answered replays the stored result rather than double-counting, which
     * matters on a mobile network.
     *
     * @return array<string,mixed>
     */
    public function submitAnswer(PracticeSession $session, int $index, string $answer, ?int $elapsedMs = null): array
    {
        $existing = $session->answerAt($index);
        if ($existing !== null) {
            return $this->answerResponse($session, $existing);
        }

        if ($session->hasExpired() && $session->isActive()) {
            $session->update(['status' => PracticeSession::STATUS_EXPIRED]);
        }

        if (! $session->isActive()) {
            throw ApiException::conflict(__('This session is no longer active.'), [
                'status' => $session->status,
                'current_index' => $session->current_index,
            ]);
        }

        if ($index !== $session->current_index) {
            throw ApiException::conflict(__('Unexpected question index.'), [
                'status' => $session->status,
                'current_index' => $session->current_index,
            ]);
        }

        $questionData = $session->questionAt($index);
        if ($questionData === null) {
            throw ApiException::notFound(__('Question not found in this session.'));
        }

        $practiceType = $session->practice_type;

        ['correct' => $correct, 'is_correct' => $isCorrect] = $this->grader->grade(
            $questionData,
            $practiceType,
            $answer,
        );

        $entry = [
            'index' => $index,
            'answer' => $answer,
            'correct_answer' => $correct,
            'is_correct' => $isCorrect,
            'elapsed_ms' => $elapsedMs,
            'answered_at' => now()->toIso8601String(),
        ];

        DB::transaction(function () use ($session, $entry, $isCorrect, $practiceType, $questionData) {
            $locked = PracticeSession::whereKey($session->getKey())->lockForUpdate()->first();

            $answers = $locked->answers_json ?? [];
            $answers[] = $entry;

            $locked->answers_json = $answers;
            $locked->answered_count = count($answers);
            $locked->correct_count += $isCorrect ? 1 : 0;
            $locked->current_index = $locked->current_index + 1;
            $locked->score = round($locked->correct_count / max(1, $locked->answered_count) * 100, 2);
            $locked->last_activity_at = now();

            if ($locked->current_index >= $locked->question_count) {
                $locked->status = PracticeSession::STATUS_COMPLETED;
                $locked->completed_at = now();
            }

            $locked->save();
            $session->setRawAttributes($locked->getAttributes(), true);

            $practiceId = $this->catalog->practiceIdForSlug($practiceType);

            // Mobile practice does build the streak, unlike the web
            // exercise-setup flow — the app is the daily-habit surface.
            $this->recorder->recordPracticeAnswer(
                $locked->user_id,
                $practiceId,
                $isCorrect,
                countsTowardDaily: true,
            );

            $this->recorder->recordIntervalStat($locked->user_id, $practiceId, $questionData, $isCorrect);

            if ($locked->source === PracticeSession::SOURCE_LEARNING_PATH && $locked->learning_path_exercise_id) {
                $this->recorder->recordLearningPathAnswer(
                    $locked->user_id,
                    $locked->learning_path_exercise_id,
                    $locked->question_count,
                    $isCorrect,
                    $locked->status === PracticeSession::STATUS_COMPLETED,
                );
            }

            if ($locked->status === PracticeSession::STATUS_COMPLETED) {
                $this->closeExerciseSession($locked);
            }
        });

        return $this->answerResponse($session, $entry);
    }

    /**
     * End a run early. Already-completed sessions are returned untouched.
     */
    public function complete(PracticeSession $session, string $status = PracticeSession::STATUS_COMPLETED): PracticeSession
    {
        if ($session->isActive()) {
            $session->status = $status;
            $session->completed_at = now();
            $session->score = round($session->correct_count / max(1, $session->answered_count) * 100, 2);
            $session->save();

            $this->closeExerciseSession($session);
        }

        return $session;
    }

    /**
     * Per-question review, only meaningful once a run has finished.
     */
    public function review(PracticeSession $session): array
    {
        return array_map(fn ($a) => [
            'index' => $a['index'],
            'answer' => $a['answer'],
            'correct_answer' => $a['correct_answer'],
            'is_correct' => $a['is_correct'],
        ], $session->answers_json ?? []);
    }

    /**
     * The client-facing question list for a session.
     */
    public function questions(PracticeSession $session): array
    {
        $out = [];
        foreach ($session->questions_json as $i => $q) {
            $out[] = $this->presenter->present($q, $session->practice_type, $i);
        }

        return $out;
    }

    private function answerResponse(PracticeSession $session, array $entry): array
    {
        return [
            'index' => $entry['index'],
            'is_correct' => $entry['is_correct'],
            'correct_answer' => $entry['correct_answer'],
            'correct_count' => $session->correct_count,
            'answered_count' => $session->answered_count,
            'question_count' => $session->question_count,
            'score' => $session->score,
            'completed' => $session->status === PracticeSession::STATUS_COMPLETED,
        ];
    }

    /**
     * Generate + normalize questions from a raw config_json.
     */
    private function generate(array $configJson, int $questionCount, string $practiceType): array
    {
        return $this->generateFromExercise(
            new LearningPathExercise(['config_json' => $configJson]),
            $questionCount,
            $practiceType,
        );
    }

    private function generateFromExercise(LearningPathExercise $exercise, int $questionCount, string $practiceType): array
    {
        $generated = $this->generator->generate($exercise, $questionCount)
            ->values()
            ->map(function ($q, $i) {
                $q->id = $i + 1;

                return $q;
            });

        if ($generated->isEmpty()) {
            throw ApiException::generationFailed(__('Could not build questions for this configuration.'));
        }

        $serialized = $this->generator->serializeForSession($generated);

        // Freeze the answer-option order now so it survives resume.
        return array_map(
            fn (array $q) => $this->presenter->prepare($q, $practiceType),
            $serialized,
        );
    }

    private function persist(User $user, array $attributes): PracticeSession
    {
        return PracticeSession::create($attributes + [
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'status' => PracticeSession::STATUS_ACTIVE,
            'current_index' => 0,
            'answered_count' => 0,
            'correct_count' => 0,
            'answers_json' => [],
            'started_at' => now(),
            'last_activity_at' => now(),
            'expires_at' => now()->addHours(self::TTL_HOURS),
        ]);
    }

    private function closeExerciseSession(PracticeSession $session): void
    {
        if (! $session->exercise_session_id) {
            return;
        }

        ExerciseSession::whereKey($session->exercise_session_id)->update([
            'score' => $session->correct_count,
            'accuracy' => $session->score,
            'duration_seconds' => $session->started_at?->diffInSeconds(now()),
            'completed_at' => now(),
        ]);
    }

    /**
     * Free plans cap how many questions a single run may contain. This clamps
     * rather than erroring — the client shows "Free plan: 5 questions".
     */
    public function clampQuestionCount(User $user, int $requested): int
    {
        $cap = (int) ($user->getPlanLimit('session_question_cap') ?? -1);

        if ($cap === -1 || $user->isAdmin()) {
            return $requested;
        }

        return min($requested, $cap);
    }

    private function assertQuota(User $user, string $feature, string $limitKey): void
    {
        if ($user->isAdmin() || $user->isEffectivelyPremium()) {
            return;
        }

        $limit = (int) ($user->getPlanLimit($limitKey) ?? -1);
        if ($limit === -1) {
            return;
        }

        $used = $this->usage->userUsed($user, $feature);
        if ($used >= $limit) {
            throw ApiException::quotaExceeded($feature, $limit, $used);
        }
    }

    private function consumeQuota(User $user, string $feature, string $limitKey): void
    {
        if ($user->isAdmin() || $user->isEffectivelyPremium()) {
            return;
        }

        if ((int) ($user->getPlanLimit($limitKey) ?? -1) === -1) {
            return;
        }

        $this->usage->userIncrement($user, $feature);
    }
}
