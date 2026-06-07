<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class GameScore extends Model
{
    protected $fillable = [
        'user_id',
        'game_slug',
        'score',
        'max_streak',
        'level_reached',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function personalBest(int $userId, string $gameSlug): ?self
    {
        return static::where('user_id', $userId)
            ->where('game_slug', $gameSlug)
            ->orderByDesc('score')
            ->first();
    }

    public static function weeklyLeaderboard(string $gameSlug, int $limit = 10): \Illuminate\Support\Collection
    {
        return static::selectRaw('user_id, MAX(score) as best_score, MAX(max_streak) as best_streak')
            ->where('game_slug', $gameSlug)
            ->where('created_at', '>=', now()->startOfWeek())
            ->groupBy('user_id')
            ->orderByDesc('best_score')
            ->limit($limit)
            ->with('user:id,name,country')
            ->get();
    }

    public static function allTimeLeaderboard(string $gameSlug, int $limit = 10): \Illuminate\Support\Collection
    {
        return static::selectRaw('user_id, MAX(score) as best_score, MAX(max_streak) as best_streak')
            ->where('game_slug', $gameSlug)
            ->groupBy('user_id')
            ->orderByDesc('best_score')
            ->limit($limit)
            ->with('user:id,name,country')
            ->get();
    }

    public static function globalLeaderboard(int $limit = 20): \Illuminate\Support\Collection
    {
        $perGame = static::select('user_id', 'game_slug', DB::raw('MAX(score) as max_score'))
            ->groupBy('user_id', 'game_slug');

        return DB::query()
            ->fromSub($perGame, 'pg')
            ->select('user_id', DB::raw('SUM(max_score) as total_score'), DB::raw('COUNT(DISTINCT game_slug) as games_played'))
            ->groupBy('user_id')
            ->orderByDesc('total_score')
            ->limit($limit)
            ->get()
            ->each(fn($r) => $r->user = User::select(['id', 'name', 'country'])->find($r->user_id));
    }

    public static function userRank(int $userId, string $gameSlug, bool $weekly = true): int
    {
        $personalBest = static::where('user_id', $userId)
            ->where('game_slug', $gameSlug)
            ->max('score') ?? 0;

        $query = static::selectRaw('user_id, MAX(score) as best_score')
            ->where('game_slug', $gameSlug)
            ->groupBy('user_id')
            ->havingRaw('MAX(score) > ?', [$personalBest]);

        if ($weekly) {
            $query->where('created_at', '>=', now()->startOfWeek());
        }

        return $query->count() + 1;
    }
}
