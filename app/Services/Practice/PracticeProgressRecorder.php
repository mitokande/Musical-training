<?php

namespace App\Services\Practice;

use App\Models\DailyExerciseCount;
use App\Models\FeedItem;
use App\Models\User;
use App\Models\UserIntervalStat;
use App\Models\UserLearningPathProgress;
use App\Models\UserPractice;
use App\Services\MusicTheoryService;

/**
 * Writes practice progress. Extracted from PracticeController so the web flows
 * and the mobile API record identically.
 *
 * Note the $countsTowardDaily flag: historically only the legacy DB answer path
 * incremented DailyExerciseCount (and therefore the streak). The exercise-setup
 * and slug paths never did. That asymmetry is preserved deliberately — the flag
 * makes it explicit instead of implicit.
 */
class PracticeProgressRecorder
{
    public function __construct(
        private readonly PracticeCatalog $catalog,
        private readonly MusicTheoryService $music,
    ) {}

    /**
     * Roll a single answered question into the user's UserPractice aggregate.
     *
     * @return array{correct_count: int, total_count: int, xp: int}
     */
    public function recordPracticeAnswer(
        ?int $userId,
        ?int $practiceId,
        bool $isCorrect,
        bool $countsTowardDaily = false,
    ): array {
        if (! $userId || ! $practiceId) {
            return ['correct_count' => 0, 'total_count' => 0, 'xp' => 0];
        }

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
            ? ($userPractice->correct_answers / $userPractice->total_questions) * 100
            : 0;
        $userPractice->save();

        if ($countsTowardDaily) {
            $daily = DailyExerciseCount::incrementCount($userId, $practiceId);
            // On the first activity of a new day, check for a streak milestone.
            if ($daily->wasRecentlyCreated && ($actor = User::find($userId))) {
                FeedItem::checkStreakAchievement($actor);
            }
        }

        return [
            'correct_count' => $userPractice->correct_answers,
            'total_count' => $userPractice->total_questions,
            'xp' => $userPractice->correct_answers * 10,
        ];
    }

    /**
     * Record per-interval accuracy for a single answered question.
     * No-op for non-interval practice types (the resolver returns null).
     */
    public function recordIntervalStat(?int $userId, ?int $practiceId, array $questionData, bool $isCorrect): void
    {
        if (! $userId || ! $practiceId) {
            return;
        }

        $slug = $this->catalog->slugForPracticeId($practiceId);
        if ($slug === null) {
            return;
        }

        $interval = $this->music->intervalForStats($questionData, $slug);
        if ($interval === null) {
            return;
        }

        UserIntervalStat::record($userId, $practiceId, $interval, $isCorrect);
    }

    /**
     * Roll an answer into the user's Learning Path progress row.
     */
    public function recordLearningPathAnswer(
        int $userId,
        int $exerciseId,
        int $questionCount,
        bool $isCorrect,
        bool $isLast,
    ): UserLearningPathProgress {
        $progress = UserLearningPathProgress::firstOrCreate(
            [
                'user_id' => $userId,
                'learning_path_exercise_id' => $exerciseId,
            ],
            [
                'question_count_attempted' => $questionCount,
                'total_questions' => 0,
                'correct_answers' => 0,
                'score' => 0,
                'completed' => false,
            ]
        );

        $progress->total_questions++;
        if ($isCorrect) {
            $progress->correct_answers++;
        }
        $progress->question_count_attempted = $questionCount;
        $progress->score = $progress->total_questions > 0
            ? round(($progress->correct_answers / $progress->total_questions) * 100, 2)
            : 0;

        if ($isLast) {
            $progress->completed = true;
            $progress->completed_at = now();
        }

        $progress->save();

        return $progress;
    }
}
