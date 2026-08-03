<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PracticeSessionResource;
use App\Models\DailyExerciseCount;
use App\Models\FeedItem;
use App\Models\LearningPathExercise;
use App\Models\Practice;
use App\Models\PracticeSession;
use App\Models\UserIntervalStat;
use App\Models\UserLearningPathProgress;
use App\Models\UserPractice;
use App\Services\Practice\PracticeCatalog;
use App\Services\UsageQuotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function __construct(
        private readonly PracticeCatalog $catalog,
        private readonly UsageQuotaService $usage,
    ) {}

    /**
     * The app's home screen. Mirrors DashboardController::userDashboard(),
     * including its streak source (FeedItem::currentStreakForUser) — the one
     * implementation tied to the same DailyExerciseCount rows the recorder
     * writes.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $practices = UserPractice::where('user_id', $user->id)->get();
        $totalQuestions = (int) $practices->sum('total_questions');
        $totalCorrect = (int) $practices->sum('correct_answers');

        $resume = PracticeSession::forUser($user->id)
            ->active()
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest()
            ->first();

        return response()->json([
            'data' => [
                'total_sessions' => $practices->count(),
                'total_questions' => $totalQuestions,
                'total_correct' => $totalCorrect,
                'accuracy' => $totalQuestions > 0 ? round($totalCorrect / $totalQuestions * 100) : 0,
                'streak' => FeedItem::currentStreakForUser($user->id),
                'resume_session' => $resume ? new PracticeSessionResource($resume) : null,
                'popular_exercises' => LearningPathExercise::where('is_active', true)
                    ->orderBy('sort_order')
                    ->limit(5)
                    ->get()
                    ->map(fn ($e) => [
                        'slug' => $e->slug,
                        'title' => $e->getLocalizedTitle(),
                        'level' => $e->level,
                        'practice_type' => $e->config_json['practice_type'] ?? null,
                    ]),
            ],
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $detailed = (bool) $user->getPlanLimit('detailed_charts');

        $practices = UserPractice::where('user_id', $user->id)->get()->keyBy('practice_id');
        $names = Practice::whereIn('slug', $this->catalog->slugs())->pluck('name', 'slug');

        $perPractice = [];
        foreach ($this->catalog->slugs() as $slug) {
            $id = $this->catalog->practiceIdForSlug($slug);
            $stat = $id ? $practices->get($id) : null;
            $total = (int) ($stat->total_questions ?? 0);

            $perPractice[] = [
                'slug' => $slug,
                'name' => $names[$slug] ?? ucwords(str_replace('-', ' ', $slug)),
                'total_questions' => $total,
                'correct_answers' => (int) ($stat->correct_answers ?? 0),
                'accuracy' => $total > 0 ? round(($stat->correct_answers / $total) * 100, 1) : 0.0,
            ];
        }

        $lpTotal = LearningPathExercise::where('is_active', true)->count();
        $lpCompleted = UserLearningPathProgress::where('user_id', $user->id)
            ->where('completed', true)
            ->count();

        return response()->json([
            'data' => [
                'per_practice' => $perPractice,
                'daily' => DailyExerciseCount::where('user_id', $user->id)
                    ->where('date', '>=', now()->subDays(29)->toDateString())
                    ->selectRaw('date, SUM(count) as total')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->map(fn ($r) => ['date' => (string) $r->date, 'count' => (int) $r->total]),
                'learning_path' => [
                    'completed' => $lpCompleted,
                    'total' => $lpTotal,
                ],
                // Per-interval mastery is a Premium chart; free users get a
                // teaser flag rather than a 403 so the app can upsell in place.
                'intervals' => $detailed
                    ? UserIntervalStat::where('user_id', $user->id)->get()->map(fn ($s) => [
                        'interval' => $s->interval_name,
                        'total_questions' => $s->total_questions,
                        'correct_answers' => $s->correct_answers,
                        'accuracy' => $s->accuracy,
                    ])
                    : [],
                'intervals_locked' => ! $detailed,
            ],
        ]);
    }

    /**
     * Plan limits and today's usage. `-1` becomes null + unlimited:true so the
     * client never has to know the sentinel convention.
     */
    public function plan(Request $request): JsonResponse
    {
        $user = $request->user();

        $limitKeys = [
            'session_question_cap',
            'learning_path_daily_sessions',
            'studio_daily_sessions',
            'daily_exercises_per_type',
            'detailed_charts',
            'ai_exercises',
        ];

        $limits = [];
        foreach ($limitKeys as $key) {
            $limits[$key] = $this->normalizeLimit($user->getPlanLimit($key));
        }

        return response()->json([
            'data' => [
                'plan' => $user->effectivePlanKey(),
                'is_premium' => $user->isEffectivelyPremium(),
                'expires_at' => $user->plan_expires_at?->toIso8601String(),
                'trial' => [
                    'active' => $user->onTrial(),
                    'ends_at' => $user->trial_ends_at?->toIso8601String(),
                    'can_start' => $user->canStartTrial(),
                ],
                'limits' => $limits,
                'usage' => [
                    'learning_path_sessions' => $this->usageFor($user, UsageQuotaService::FEATURE_LP_SESSIONS, 'learning_path_daily_sessions'),
                    'studio_sessions' => $this->usageFor($user, UsageQuotaService::FEATURE_STUDIO_SESSIONS, 'studio_daily_sessions'),
                ],
                'upgrade_url' => url('/checkout'),
            ],
        ]);
    }

    private function usageFor($user, string $feature, string $limitKey): array
    {
        $limit = (int) ($user->getPlanLimit($limitKey) ?? -1);
        $unlimited = $limit === -1 || $user->isAdmin() || $user->isEffectivelyPremium();
        $used = $this->usage->userUsed($user, $feature);

        return [
            'limit' => $unlimited ? null : $limit,
            'unlimited' => $unlimited,
            'used' => $used,
            'remaining' => $unlimited ? null : max(0, $limit - $used),
        ];
    }

    private function normalizeLimit(mixed $value): mixed
    {
        if ($value === -1) {
            return ['value' => null, 'unlimited' => true];
        }

        return ['value' => $value, 'unlimited' => false];
    }
}
