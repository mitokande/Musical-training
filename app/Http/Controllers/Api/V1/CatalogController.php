<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Api\ApiException;
use App\Http\Controllers\Controller;
use App\Models\DailyExerciseCount;
use App\Models\ExerciseCategory;
use App\Models\LearningPathExercise;
use App\Models\Practice;
use App\Models\UserLearningPathProgress;
use App\Models\UserPractice;
use App\Services\Practice\PracticeCatalog;
use App\Services\Practice\QuestionPresenter;
use App\Services\Practice\StudioConfigMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(
        private readonly PracticeCatalog $catalog,
        private readonly StudioConfigMapper $mapper,
        private readonly QuestionPresenter $presenter,
    ) {}

    /**
     * Every practice type with its option contract, the user's running
     * accuracy, and today's remaining allowance.
     */
    public function practiceTypes(Request $request): JsonResponse
    {
        $user = $request->user();

        $practices = Practice::whereIn('slug', $this->catalog->slugs())->get()->keyBy('slug');
        $stats = UserPractice::where('user_id', $user->id)->get()->keyBy('practice_id');
        $dailyLimit = (int) ($user->getPlanLimit('daily_exercises_per_type') ?? -1);

        $data = [];

        foreach ($this->catalog->slugs() as $slug) {
            $practiceId = $this->catalog->practiceIdForSlug($slug);
            $practice = $practices->get($slug);
            $stat = $practiceId ? $stats->get($practiceId) : null;

            $used = $practiceId
                ? (int) (DailyExerciseCount::where('user_id', $user->id)
                    ->where('practice_id', $practiceId)
                    ->where('date', now()->toDateString())
                    ->value('count') ?? 0)
                : 0;

            $data[] = [
                'slug' => $slug,
                'practice_id' => $practiceId,
                'name' => $practice?->name ?? $this->humanize($slug),
                'description' => $practice?->description,
                'is_premium' => (bool) ($practice?->is_premium ?? false),
                'answer_mode' => $this->presenter->answerMode($slug),
                'config_schema' => $this->mapper->configSchema($slug),
                'user_progress' => [
                    'total_questions' => (int) ($stat->total_questions ?? 0),
                    'correct_answers' => (int) ($stat->correct_answers ?? 0),
                    'accuracy' => $stat && $stat->total_questions > 0
                        ? round($stat->correct_answers / $stat->total_questions * 100, 1)
                        : 0.0,
                ],
                'daily' => [
                    'limit' => $dailyLimit,
                    'unlimited' => $dailyLimit === -1,
                    'used' => $used,
                    'remaining' => $dailyLimit === -1 ? null : max(0, $dailyLimit - $used),
                ],
            ];
        }

        return response()->json(['data' => $data]);
    }

    /**
     * Learning Path categories, each with their lessons and the user's
     * progress on them.
     */
    public function learningPath(Request $request): JsonResponse
    {
        $user = $request->user();

        $progress = UserLearningPathProgress::where('user_id', $user->id)
            ->get()
            ->keyBy('learning_path_exercise_id');

        $categories = ExerciseCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $exercises = LearningPathExercise::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category_id');

        $data = $categories->map(fn (ExerciseCategory $category) => [
            'id' => $category->id,
            'slug' => $category->slug,
            'name' => $category->name,
            'description' => $category->description,
            'icon' => $category->icon,
            'is_premium' => (bool) $category->is_premium,
            'exercises' => ($exercises->get($category->id) ?? collect())
                ->map(fn (LearningPathExercise $e) => $this->exercisePayload($e, $progress->get($e->id)))
                ->values(),
        ])->values();

        return response()->json(['data' => $data]);
    }

    public function learningPathExercise(Request $request, string $slug): JsonResponse
    {
        $exercise = LearningPathExercise::where('slug', $slug)->where('is_active', true)->first();

        if (! $exercise) {
            throw ApiException::notFound(__('Lesson not found.'));
        }

        $progress = UserLearningPathProgress::where('user_id', $request->user()->id)
            ->where('learning_path_exercise_id', $exercise->id)
            ->first();

        $siblings = LearningPathExercise::where('is_active', true)
            ->where('category_id', $exercise->category_id)
            ->orderBy('sort_order')
            ->get();

        $position = $siblings->search(fn ($e) => $e->id === $exercise->id);

        return response()->json([
            'data' => $this->exercisePayload($exercise, $progress) + [
                'previous_slug' => $position > 0 ? $siblings[$position - 1]->slug : null,
                'next_slug' => $position !== false && isset($siblings[$position + 1])
                    ? $siblings[$position + 1]->slug
                    : null,
            ],
        ]);
    }

    private function exercisePayload(LearningPathExercise $e, ?UserLearningPathProgress $progress): array
    {
        return [
            'id' => $e->id,
            'slug' => $e->slug,
            'title' => $e->getLocalizedTitle(),
            'description' => $e->description,
            'level' => $e->level,
            'sort_order' => $e->sort_order,
            'practice_type' => $e->config_json['practice_type'] ?? null,
            'estimated_duration_minutes' => $e->estimated_duration_minutes,
            'tags' => $e->tags,
            'skills_trained' => $e->skills_trained,
            'question_count_options' => [
                ['count' => 5, 'premium' => $e->isPremiumVariant(5)],
                ['count' => 10, 'premium' => $e->isPremiumVariant(10)],
                ['count' => 15, 'premium' => $e->isPremiumVariant(15)],
            ],
            'progress' => [
                'total_questions' => (int) ($progress->total_questions ?? 0),
                'correct_answers' => (int) ($progress->correct_answers ?? 0),
                'score' => (float) ($progress->score ?? 0),
                'completed' => (bool) ($progress->completed ?? false),
                'completed_at' => $progress?->completed_at?->toIso8601String(),
            ],
        ];
    }

    private function humanize(string $slug): string
    {
        return ucwords(str_replace('-', ' ', $slug));
    }
}
