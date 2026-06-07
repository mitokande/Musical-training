<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class School extends Model
{
    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'long_description',
        'logo_url', 'address', 'city', 'country', 'phone', 'email',
        'website_url', 'social_links', 'payment_link', 'programs',
        'founded_year', 'student_capacity', 'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'programs' => 'array',
            'is_verified' => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
