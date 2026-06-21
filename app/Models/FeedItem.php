<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedItem extends Model
{
    /** Consecutive-day streak milestones that earn an achievement feed item. */
    public const STREAK_MILESTONES = [3, 7, 14, 30, 100];

    protected $fillable = ['user_id', 'type', 'body', 'subject_id', 'metadata'];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(FeedLike::class);
    }

    // --- Generation helpers (single source of truth for creating feed items) ---

    public static function recordNewMember(User $user): self
    {
        return static::create([
            'user_id' => $user->id,
            'type' => 'new_member',
        ]);
    }

    public static function recordFollow(User $follower, User $followed): self
    {
        return static::create([
            'user_id' => $follower->id,
            'type' => 'follow',
            'subject_id' => $followed->id,
        ]);
    }

    public static function recordPost(User $user, string $body): self
    {
        return static::create([
            'user_id' => $user->id,
            'type' => 'post',
            'body' => trim($body),
        ]);
    }

    public static function recordAchievement(User $user, string $body, array $metadata = []): self
    {
        return static::create([
            'user_id' => $user->id,
            'type' => 'achievement',
            'body' => $body,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Record a "new personal best" achievement for a game, once per (user, game, score).
     */
    public static function recordGameHighScore(User $user, string $gameSlug, string $gameName, int $score): ?self
    {
        $exists = static::where('user_id', $user->id)
            ->where('type', 'achievement')
            ->where('metadata->kind', 'game_high_score')
            ->where('metadata->game', $gameSlug)
            ->where('metadata->score', $score)
            ->exists();

        if ($exists) {
            return null;
        }

        return static::recordAchievement(
            $user,
            __('app.social.achievement_highscore', ['game' => $gameName, 'score' => $score]),
            ['kind' => 'game_high_score', 'game' => $gameSlug, 'game_name' => $gameName, 'score' => $score],
        );
    }

    /**
     * Check the user's consecutive-day practice streak and record an achievement
     * the first time a milestone is reached. Deduped per (user, milestone).
     */
    public static function checkStreakAchievement(User $user): ?self
    {
        $streak = static::currentStreakForUser($user->id);

        $milestone = collect(self::STREAK_MILESTONES)
            ->filter(fn ($m) => $streak >= $m)
            ->max();

        if (! $milestone) {
            return null;
        }

        $exists = static::where('user_id', $user->id)
            ->where('type', 'achievement')
            ->where('metadata->kind', 'streak')
            ->where('metadata->milestone', $milestone)
            ->exists();

        if ($exists) {
            return null;
        }

        return static::recordAchievement(
            $user,
            __('app.social.achievement_streak', ['days' => $milestone]),
            ['kind' => 'streak', 'milestone' => $milestone],
        );
    }

    /**
     * Consecutive practice-day streak (ending today or yesterday) derived from
     * DailyExerciseCount records.
     */
    public static function currentStreakForUser(int $userId): int
    {
        $dates = DailyExerciseCount::where('user_id', $userId)
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->sort()
            ->reverse()
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $today = Carbon::today()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        if ($dates->first() !== $today && $dates->first() !== $yesterday) {
            return 0;
        }

        $streak = 0;
        $cursor = Carbon::parse($dates->first());

        foreach ($dates as $date) {
            $practiceDate = Carbon::parse($date);
            if ($cursor->diffInDays($practiceDate) <= 1) {
                $streak++;
                $cursor = $practiceDate;
            } else {
                break;
            }
        }

        return $streak;
    }
}
