<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TeacherProfile extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted_for_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SUBMITTED,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_SUSPENDED,
        self::STATUS_ARCHIVED,
    ];

    public const TIER_BASIC = 'basic';

    public const TIER_PREMIUM = 'premium';

    protected $fillable = [
        'user_id', 'tier', 'slug', 'status', 'admin_forced_private',
        // legacy fields kept for backward compatibility
        'title', 'short_bio', 'long_bio', 'specializations',
        'teaching_subjects', 'education_background', 'experience_years',
        'hourly_rate', 'currency', 'lesson_format', 'website_url',
        'social_links', 'payment_link', 'location', 'languages',
        'accepts_students', 'max_students', 'public_profile',
        // general information
        'headline', 'expertise', 'cover_image_path', 'about', 'teaching_methodology',
        'teaching_formats', 'lesson_types', 'country', 'city',
        'public_email', 'show_email', 'public_phone', 'show_phone',
        // music profile
        'primary_instrument', 'education_status', 'certificates', 'workshops',
        'masterclasses', 'teaching_experience', 'genres', 'expertise_areas',
        'age_groups', 'skill_levels', 'teaching_languages',
        // seo
        'seo_title', 'seo_description',
        // lifecycle
        'submitted_at', 'approved_at', 'published_at', 'rejected_at', 'suspended_at',
        'rejection_reason', 'view_count',
    ];

    protected function casts(): array
    {
        return [
            'specializations' => 'array',
            'teaching_subjects' => 'array',
            'social_links' => 'array',
            'languages' => 'array',
            'teaching_formats' => 'array',
            'lesson_types' => 'array',
            'genres' => 'array',
            'expertise_areas' => 'array',
            'age_groups' => 'array',
            'skill_levels' => 'array',
            'teaching_languages' => 'array',
            'accepts_students' => 'boolean',
            'public_profile' => 'boolean',
            'admin_forced_private' => 'boolean',
            'show_email' => 'boolean',
            'show_phone' => 'boolean',
            'hourly_rate' => 'decimal:2',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
            'rejected_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(TeacherEducation::class)->orderBy('sort_order');
    }

    public function instruments(): HasMany
    {
        return $this->hasMany(TeacherInstrument::class)->orderBy('sort_order');
    }

    public function services(): HasMany
    {
        return $this->hasMany(TeacherService::class)->orderBy('sort_order');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(TeacherVideo::class)->orderBy('sort_order');
    }

    public function media(): HasMany
    {
        return $this->hasMany(TeacherMedia::class)->orderBy('sort_order');
    }

    public function paymentLinks(): HasMany
    {
        return $this->hasMany(TeacherPaymentLink::class)->orderBy('sort_order');
    }

    public function views(): HasMany
    {
        return $this->hasMany(TeacherProfileView::class);
    }

    public function moderationLogs(): HasMany
    {
        return $this->hasMany(TeacherProfileModerationLog::class)->latest();
    }

    // --- Tier helpers ---

    public function isPremiumTier(): bool
    {
        return $this->tier === self::TIER_PREMIUM;
    }

    public function isBasicTier(): bool
    {
        return $this->tier === self::TIER_BASIC;
    }

    // --- Status helpers ---

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Whether the profile may be shown on its public URL and indexed.
     */
    public function isPubliclyVisible(): bool
    {
        return $this->isApproved() && ! $this->admin_forced_private;
    }

    public function canBeSubmitted(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED], true);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->where('admin_forced_private', false);
    }

    /**
     * Create the initial Basic draft profile for a user. Single entry point
     * for teacher-account creation — used by the become-teacher flow and by
     * the CRM for role-based users (teacher/school/admin) without a profile.
     */
    public static function createDraftFor(User $user): self
    {
        return static::create([
            'user_id' => $user->id,
            'tier' => self::TIER_BASIC,
            'status' => self::STATUS_DRAFT,
            'slug' => self::generateUniqueSlug(
                trim($user->name.' '.($user->surname ?? '')) ?: ($user->username ?? 'teacher')
            ),
            'country' => $user->country,
            'city' => $user->city,
        ]);
    }

    // --- Slug ---

    public static function generateUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base) ?: 'teacher';
        $candidate = $slug;
        $i = 2;

        while (static::where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = "{$slug}-{$i}";
            $i++;
        }

        return $candidate;
    }

    // --- Public display helpers ---

    public function displayName(): string
    {
        return trim($this->user->name.' '.($this->user->surname ?? ''));
    }

    public function publicUrl(): string
    {
        return route('teachers.show', $this->slug);
    }

    public function coverImageUrl(): ?string
    {
        return $this->cover_image_path ? asset($this->cover_image_path) : null;
    }
}
