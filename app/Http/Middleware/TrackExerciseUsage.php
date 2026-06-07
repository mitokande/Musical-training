<?php

namespace App\Http\Middleware;

use App\Models\DailyExerciseCount;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackExerciseUsage
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || $user->isPremium() || $user->isAdmin()) {
            return $next($request);
        }

        $slug = $request->route('slug');
        $practiceId = $this->resolvePracticeId($slug);

        if (!$practiceId) {
            return $next($request);
        }

        $limit = config("plans.{$user->role}.free.daily_exercises_per_type", 3);

        if ($limit === -1) {
            return $next($request);
        }

        $todayCount = DailyExerciseCount::where('user_id', $user->id)
            ->where('practice_id', $practiceId)
            ->where('date', now()->toDateString())
            ->value('count') ?? 0;

        if ($todayCount >= $limit) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'daily_limit_reached',
                    'message' => 'You have reached your daily exercise limit for this type. Upgrade to Premium for unlimited access.',
                    'limit' => $limit,
                    'used' => $todayCount,
                ], 429);
            }

            return redirect()->route('dashboard')->with('error',
                "Bu egzersiz tipi için günlük {$limit} hakkınızı kullandınız. Premium'a yükselterek sınırsız erişim kazanın."
            );
        }

        return $next($request);
    }

    private function resolvePracticeId(?string $slug): ?int
    {
        $map = [
            'single-note-practice' => 1,
            'interval-direction-practice' => 2,
            'interval-comparison-practice' => 3,
            'melodic-interval-practice' => 4,
            'harmonic-interval-practice' => 5,
            'interval-construction-practice' => 6,
        ];

        return $map[$slug] ?? null;
    }
}
