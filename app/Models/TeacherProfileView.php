<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherProfileView extends Model
{
    protected $fillable = [
        'teacher_profile_id', 'viewer_id', 'ip_hash', 'viewed_on',
    ];

    protected function casts(): array
    {
        return [
            'viewed_on' => 'date',
        ];
    }

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class);
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }

    /**
     * Record today's view for this visitor unless already counted.
     * Returns true when a new view row was created.
     */
    public static function firstOrCreateForToday(TeacherProfile $profile, ?int $viewerId, string $ipHash): bool
    {
        $view = static::firstOrCreate(
            [
                'teacher_profile_id' => $profile->id,
                'ip_hash' => $ipHash,
                'viewed_on' => now()->toDateString(),
            ],
            ['viewer_id' => $viewerId],
        );

        return $view->wasRecentlyCreated;
    }
}
