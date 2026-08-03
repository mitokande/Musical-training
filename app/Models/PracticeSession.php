<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A practice run started from the mobile API.
 *
 * questions_json holds the generator's serializeForSession() output including
 * the correct answers, so it is hidden and never serialized to a response —
 * everything client-facing goes through QuestionPresenter.
 */
class PracticeSession extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_ABANDONED = 'abandoned';

    public const STATUS_EXPIRED = 'expired';

    public const SOURCE_STUDIO = 'studio';

    public const SOURCE_LEARNING_PATH = 'learning_path';

    protected $fillable = [
        'uuid', 'user_id', 'source', 'practice_type', 'learning_path_exercise_id',
        'exercise_session_id', 'config_json', 'questions_json', 'answers_json',
        'question_count', 'current_index', 'answered_count', 'correct_count',
        'score', 'status', 'started_at', 'last_activity_at', 'completed_at', 'expires_at',
    ];

    protected $hidden = ['id', 'user_id', 'questions_json', 'answers_json'];

    protected $casts = [
        'config_json' => 'array',
        'questions_json' => 'array',
        'answers_json' => 'array',
        'score' => 'float',
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(LearningPathExercise::class, 'learning_path_exercise_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function hasExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function questionAt(int $index): ?array
    {
        return $this->questions_json[$index] ?? null;
    }

    /** The answer already recorded for a question index, if any. */
    public function answerAt(int $index): ?array
    {
        foreach ($this->answers_json ?? [] as $entry) {
            if (($entry['index'] ?? null) === $index) {
                return $entry;
            }
        }

        return null;
    }
}
