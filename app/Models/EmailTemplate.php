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
        'category', 'variables', 'is_active', 'translations',
    ];

    protected $casts = [
        'variables' => 'array',
        'translations' => 'array',
        'is_active' => 'boolean',
    ];

    public const CATEGORIES = ['marketing', 'transactional'];

    /** Locales the system templates are translated into (en is the base). */
    public const LOCALES = ['en', 'es', 'de', 'fr', 'pt', 'tr', 'it'];

    /**
     * Subject / preheader / html_body for a recipient locale, falling back to
     * the English base columns when there is no translation (or locale is en).
     *
     * @return array{subject: string, preheader: ?string, html_body: string}
     */
    public function localized(string $locale): array
    {
        $t = $this->translations[$locale] ?? null;

        return [
            'subject' => $t['subject'] ?? $this->subject,
            'preheader' => $t['preheader'] ?? $this->preheader,
            'html_body' => $t['html_body'] ?? $this->html_body,
        ];
    }

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
