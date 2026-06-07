<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyExerciseCount extends Model
{
    protected $fillable = [
        'user_id',
        'practice_id',
        'date',
        'count',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function incrementCount(int $userId, int $practiceId): self
    {
        $record = static::firstOrCreate(
            [
                'user_id' => $userId,
                'practice_id' => $practiceId,
                'date' => now()->toDateString(),
            ],
            ['count' => 0]
        );

        $record->increment('count');

        return $record;
    }

    public static function getRemainingForUser(int $userId, int $practiceId, int $limit): int
    {
        if ($limit === -1) {
            return PHP_INT_MAX;
        }

        $used = static::where('user_id', $userId)
            ->where('practice_id', $practiceId)
            ->where('date', now()->toDateString())
            ->value('count') ?? 0;

        return max(0, $limit - $used);
    }
}
