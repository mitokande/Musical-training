<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * An ad-hoc lesson on Derslik. Open until the teacher ends it, or until
 * services.derslik.lesson_hours pass (derslik:close-stale-lessons then deletes
 * the Derslik room).
 */
class LiveLesson extends Model
{
    protected $fillable = [
        'teacher_id', 'title', 'derslik_room_id', 'teacher_url', 'student_url', 'share_token', 'ended_at',
    ];

    protected $hidden = ['teacher_url', 'student_url', 'derslik_room_id'];

    protected function casts(): array
    {
        return [
            // Derslik's teacher URL contains the room's teacher key.
            'teacher_url' => 'encrypted',
            'ended_at' => 'datetime',
        ];
    }

    public static function newShareToken(): string
    {
        return Str::random(24);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /** Not ended and still inside the lesson window. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('ended_at')
            ->where('created_at', '>=', now()->subHours(self::lessonHours()));
    }

    /** Not ended, but past the lesson window — what the hourly cleanup closes. */
    public function scopeStale(Builder $query): Builder
    {
        return $query->whereNull('ended_at')
            ->where('created_at', '<', now()->subHours(self::lessonHours()));
    }

    public function isOpen(): bool
    {
        return $this->ended_at === null
            && $this->created_at !== null
            && $this->created_at->gte(now()->subHours(self::lessonHours()));
    }

    /** The link the teacher shares — a harmoniva.app page that embeds the room. */
    public function joinUrl(): string
    {
        return route('live-lessons.join', $this->share_token);
    }

    public function roomUrl(): string
    {
        return route('live-lessons.room', $this);
    }

    public static function lessonHours(): int
    {
        return max(1, (int) config('services.derslik.lesson_hours', 12));
    }
}
