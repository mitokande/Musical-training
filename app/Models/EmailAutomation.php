<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailAutomation extends Model
{
    protected $fillable = [
        'key', 'name', 'description', 'template_id', 'enabled', 'config',
        'send_count', 'last_run_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'config' => 'array',
        'last_run_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(EmailMessage::class, 'automation_id');
    }

    public function configValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }
}
