<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Article extends Model
{
    protected $fillable = [
        'author_id', 'title', 'slug', 'body', 'excerpt', 'featured_image',
        'content_type', 'status', 'visibility', 'video_url', 'audio_file',
        'document_file', 'tags', 'category', 'admin_note', 'published_at',
        'meta_title', 'meta_description', 'meta_keywords', 'og_image',
        'canonical_url', 'is_featured', 'view_count', 'reading_time', 'category_id',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function contentCategory(): BelongsTo
    {
        return $this->belongsTo(ContentCategory::class, 'category_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public static function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $count = static::where('slug', 'LIKE', "{$slug}%")->count();
        return $count ? "{$slug}-{$count}" : $slug;
    }
}
