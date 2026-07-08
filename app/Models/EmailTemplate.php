<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EmailTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'subject', 'preheader', 'html_body', 'text_body',
        'category', 'variables', 'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public const CATEGORIES = ['marketing', 'transactional'];

    protected static function booted(): void
    {
        static::creating(function (self $template) {
            $template->slug = $template->slug ?: Str::slug($template->name);
        });
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class, 'template_id');
    }

    public function automations(): HasMany
    {
        return $this->hasMany(EmailAutomation::class, 'template_id');
    }

    public function isMarketing(): bool
    {
        return $this->category === 'marketing';
    }
}
