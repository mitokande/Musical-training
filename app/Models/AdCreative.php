<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An ad creative authored in the admin Ad Studio.
 *
 * The row is the intent; the deliverable is a generated HyperFrames project on
 * disk (see AdProjectBuilder). A creative can always be rebuilt from `config`,
 * so the project directory is disposable and the row is not.
 */
class AdCreative extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_VOICING = 'voicing';

    public const STATUS_BUILT = 'built';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_RENDERING = 'rendering';

    public const STATUS_RENDERED = 'rendered';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'name',
        'slug',
        'template',
        'status',
        'auto_render',
        'config',
        'vo_manifest',
        'timings',
        'project_dir',
        'render_path',
        'render_bytes',
        'duration_seconds',
        'error',
        'queued_at',
        'render_started_at',
        'rendered_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'auto_render' => 'boolean',
            'vo_manifest' => 'array',
            'timings' => 'array',
            'render_bytes' => 'integer',
            'duration_seconds' => 'float',
            'queued_at' => 'datetime',
            'render_started_at' => 'datetime',
            'rendered_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Statuses where a background process owns the row. The editor locks its
     * form on these so an operator cannot mutate config mid-render.
     */
    public function isBusy(): bool
    {
        return in_array($this->status, [self::STATUS_VOICING, self::STATUS_QUEUED, self::STATUS_RENDERING], true);
    }

    public function isRenderable(): bool
    {
        return in_array($this->status, [self::STATUS_BUILT, self::STATUS_RENDERED, self::STATUS_FAILED], true);
    }

    public function hasRender(): bool
    {
        return $this->render_path !== null && is_file($this->absoluteRenderPath());
    }

    /** Project paths are stored relative to base_path() so the row survives a move. */
    public function absoluteProjectDir(): string
    {
        return base_path($this->project_dir ?? '');
    }

    public function absoluteRenderPath(): string
    {
        return base_path($this->render_path ?? '');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_VOICING => 'Generating voiceover…',
            self::STATUS_BUILT => 'Ready to render',
            self::STATUS_QUEUED => 'Queued',
            self::STATUS_RENDERING => 'Rendering',
            self::STATUS_RENDERED => 'Rendered',
            self::STATUS_FAILED => 'Failed',
            default => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_RENDERED => 'green',
            self::STATUS_FAILED => 'red',
            self::STATUS_BUILT => 'blue',
            self::STATUS_QUEUED, self::STATUS_RENDERING, self::STATUS_VOICING => 'amber',
            default => 'gray',
        };
    }

    /** Script lines as [key => text], the shape the voiceover service wants. */
    public function scriptLines(): array
    {
        return (array) ($this->config['lines'] ?? []);
    }

    public function option(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, "options.$key", $default);
    }
}
