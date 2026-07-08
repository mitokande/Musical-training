<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherVideo extends Model
{
    protected $fillable = [
        'teacher_profile_id', 'title', 'youtube_id', 'url', 'description', 'sort_order',
    ];

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class);
    }

    /**
     * Extract a YouTube video id from the common URL formats.
     * Returns null when the URL is not a recognizable YouTube link.
     */
    public static function extractYoutubeId(string $url): ?string
    {
        $patterns = [
            '~^https?://(?:www\.)?youtube\.com/watch\?(?:.*&)?v=([A-Za-z0-9_-]{11})~',
            '~^https?://(?:www\.)?youtube\.com/(?:embed|shorts|live)/([A-Za-z0-9_-]{11})~',
            '~^https?://youtu\.be/([A-Za-z0-9_-]{11})~',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, trim($url), $m)) {
                return $m[1];
            }
        }

        return null;
    }

    public function embedUrl(): string
    {
        return 'https://www.youtube-nocookie.com/embed/'.$this->youtube_id;
    }

    public function thumbnailUrl(): string
    {
        return 'https://i.ytimg.com/vi/'.$this->youtube_id.'/hqdefault.jpg';
    }
}
