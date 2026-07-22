<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, LogsActivity, Notifiable;

    protected $fillable = [
        'name',
        'surname',
        'username',
        'email',
        'password',
        'role',
        'plan',
        'plan_expires_at',
        'plan_cycle',
        'stripe_customer_id',
        'locale',
        'google_id',
        'avatar_url',
        'phone',
        'country',
        'city',
        'date_of_birth',
        'suspended_at',
        'is_restricted',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'last_active_at' => 'datetime',
            'suspended_at' => 'datetime',
            'is_restricted' => 'boolean',
            'plan_expires_at' => 'datetime',
        ];
    }

    /**
     * Full display name: first name + surname (trimmed). Used wherever a person
     * should be shown by their complete name — feed, articles, public surfaces.
     * Falls back to the first name alone when no surname is set.
     */
    public function fullName(): string
    {
        return trim($this->name.' '.($this->surname ?? '')) ?: (string) $this->name;
    }

    // --- Role helpers ---

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    /**
     * Teacher-ness is orthogonal to role: any user (student, teacher, admin)
     * may hold a teacher account, represented by a TeacherProfile row.
     */
    public function hasTeacherAccount(): bool
    {
        return $this->isTeacher() || $this->teacherProfile()->exists();
    }

    /** 'basic' | 'premium' | null when the user has no teacher account. */
    public function teacherTier(): ?string
    {
        return $this->teacherProfile?->tier;
    }

    public function isTeacherPremium(): bool
    {
        return $this->teacherTier() === TeacherProfile::TIER_PREMIUM;
    }

    /**
     * Route name to land on after login. Teachers go straight to their CRM,
     * schools to the school panel; everyone else keeps the profile landing.
     */
    public function homeRoute(): string
    {
        if ($this->crmNamespace() === 'school') {
            return 'school.dashboard';
        }

        return $this->hasTeacherAccount() ? 'teacher.dashboard' : 'profile.edit';
    }

    public function isSchool(): bool
    {
        return $this->role === 'school';
    }

    // --- CRM namespace (shared teacher/school panel) ---

    /**
     * Which CRM namespace this user's panel lives under. The teacher CRM and
     * the school panel share controllers/blades; only the route prefix and
     * skin differ. Schools are recognised by profile entity_type or role.
     */
    public function crmNamespace(): string
    {
        if ($this->isSchool() || ($this->teacherProfile?->isSchoolEntity() ?? false)) {
            return 'school';
        }

        return 'teacher';
    }

    /** Fully-qualified route name inside the user's CRM namespace. */
    public function crmRouteName(string $suffix): string
    {
        return $this->crmNamespace().'.'.$suffix;
    }

    /** Whether this user is an active member teacher of at least one school. */
    public function hasActiveSchoolMembership(): bool
    {
        return once(fn () => SchoolTeacherRelationship::query()
            ->where('teacher_id', $this->id)
            ->where('status', SchoolTeacherRelationship::STATUS_ACTIVE)
            ->exists());
    }

    /** User ids of this school's active member teachers. */
    public function memberTeacherIds(): array
    {
        return once(fn () => SchoolTeacherRelationship::query()
            ->where('school_id', $this->id)
            ->where('status', SchoolTeacherRelationship::STATUS_ACTIVE)
            ->pluck('teacher_id')
            ->all());
    }

    /**
     * Owner ids whose student relationships this CRM account may see:
     * schools aggregate their member teachers' rosters, teachers only their own.
     */
    public function crmOwnerIds(): array
    {
        if ($this->teacherProfile?->isSchoolEntity() ?? false) {
            return array_values(array_unique([$this->id, ...$this->memberTeacherIds()]));
        }

        return [$this->id];
    }

    // --- Plan helpers ---

    public function isPremium(): bool
    {
        return $this->plan === 'premium';
    }

    public function isFree(): bool
    {
        return $this->plan === 'free';
    }

    public function isSuspended(): bool
    {
        return ! is_null($this->suspended_at);
    }

    public function isRestricted(): bool
    {
        return (bool) $this->is_restricted;
    }

    public function hasPassword(): bool
    {
        return ! is_null($this->password);
    }

    /**
     * Whether this account currently enjoys premium entitlements: either a
     * paid premium plan, or an earned free-period benefit from the teacher /
     * school premium-student incentive (e.g. 10 premium students → the
     * teacher uses Harmoniva completely free with premium rights).
     */
    public function isEffectivelyPremium(): bool
    {
        if ($this->isPremium()) {
            return true;
        }

        return once(fn () => $this->teacherSubscriptionBenefits()
            ->active()
            ->where('type', TeacherSubscriptionBenefit::TYPE_FREE_PERIOD)
            ->exists());
    }

    /** Plan key used for entitlement lookups ('free'|'premium'). */
    public function effectivePlanKey(): string
    {
        return $this->isEffectivelyPremium() ? 'premium' : $this->plan;
    }

    public function canAccess(string $feature): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $value = config("plans.{$this->role}.{$this->effectivePlanKey()}.{$feature}");

        if (is_null($value)) {
            return false;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === -1 || $value > 0;
        }

        return $value !== 'limited';
    }

    public function getPlanLimit(string $feature): mixed
    {
        // Admin-managed limits from the plans table take precedence;
        // config/plans.php remains the fallback for missing keys.
        $plans = cache()->remember('plans.features', 300, function () {
            return Plan::where('is_active', true)
                ->get()
                ->keyBy(fn ($plan) => $plan->role.'.'.$plan->type);
        });

        $planKey = $this->effectivePlanKey();

        $plan = $plans->get("{$this->role}.{$planKey}");
        if ($plan && is_array($plan->features) && array_key_exists($feature, $plan->features)) {
            return $plan->features[$feature];
        }

        return config("plans.{$this->role}.{$planKey}.{$feature}");
    }

    // --- Relationships ---

    public function userPractices(): HasMany
    {
        return $this->hasMany(UserPractice::class);
    }

    public function userIntervalStats(): HasMany
    {
        return $this->hasMany(UserIntervalStat::class);
    }

    /**
     * Accuracy multipliers for every tracked interval across all interval
     * practice types. See UserIntervalStat::accuracyMultipliersForUser().
     */
    public function intervalAccuracyMultipliers(): array
    {
        return UserIntervalStat::accuracyMultipliersForUser($this->id);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function questionnaireResponses(): HasMany
    {
        return $this->hasMany(QuestionnaireResponse::class);
    }

    public function dailyExerciseCounts(): HasMany
    {
        return $this->hasMany(DailyExerciseCount::class);
    }

    public function teacherProfile(): HasOne
    {
        return $this->hasOne(TeacherProfile::class);
    }

    public function school(): HasOne
    {
        return $this->hasOne(School::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    public function crmNotes(): HasMany
    {
        return $this->hasMany(CrmNote::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /** The current in-period, active paid subscription (if any). */
    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->latest('starts_at')
            ->first();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function aiCoachingSessions(): HasMany
    {
        return $this->hasMany(AiCoachingSession::class);
    }

    public function exerciseSetupTemplates(): HasMany
    {
        return $this->hasMany(ExerciseSetupTemplate::class);
    }

    public function exerciseSessions(): HasMany
    {
        return $this->hasMany(ExerciseSession::class);
    }

    // --- Teacher CRM ---

    /** Relationships where this user is the teacher. */
    public function studentRelationships(): HasMany
    {
        return $this->hasMany(TeacherStudentRelationship::class, 'teacher_id');
    }

    /** Relationships where this user is the student. */
    public function teacherRelationships(): HasMany
    {
        return $this->hasMany(TeacherStudentRelationship::class, 'student_id');
    }

    public function teacherSubscriptionBenefits(): HasMany
    {
        return $this->hasMany(TeacherSubscriptionBenefit::class);
    }

    /** Classes this user teaches. */
    public function teacherClasses(): HasMany
    {
        return $this->hasMany(TeacherClass::class, 'teacher_id');
    }

    /** Assignments this user issued as a teacher. */
    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id');
    }

    /** Assignment recipient rows where this user is the student. */
    public function assignmentRecipients(): HasMany
    {
        return $this->hasMany(TeacherAssignmentRecipient::class, 'student_id');
    }

    /** Rewards this user received as a student. */
    public function studentRewards(): HasMany
    {
        return $this->hasMany(TeacherStudentReward::class, 'student_id');
    }

    /**
     * Active, mutually approved relationship between this CRM account and a
     * student. Schools also count their member teachers' active students.
     */
    public function hasActiveStudent(int $studentId): bool
    {
        return TeacherStudentRelationship::query()
            ->whereIn('teacher_id', $this->crmOwnerIds())
            ->where('student_id', $studentId)
            ->where('status', TeacherStudentRelationship::STATUS_ACTIVE)
            ->exists();
    }

    /** Active, mutually approved relationship between this student and a teacher. */
    public function hasActiveTeacher(int $teacherId): bool
    {
        return $this->teacherRelationships()
            ->where('teacher_id', $teacherId)
            ->where('status', TeacherStudentRelationship::STATUS_ACTIVE)
            ->exists();
    }

    // --- Social: following / feed ---

    public static function booted(): void
    {
        static::created(function (User $user) {
            FeedItem::recordNewMember($user);
        });
    }

    /** Users that this user follows. */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id')
            ->withTimestamps();
    }

    /** Users that follow this user. */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id')
            ->withTimestamps();
    }

    public function feedItems(): HasMany
    {
        return $this->hasMany(FeedItem::class);
    }

    public function isFollowing(User $user): bool
    {
        return $this->following()->whereKey($user->id)->exists();
    }

    public function follow(User $user): void
    {
        if ($user->id === $this->id || $this->isFollowing($user)) {
            return;
        }

        $this->following()->attach($user->id);
        FeedItem::recordFollow($this, $user);
    }

    public function unfollow(User $user): void
    {
        $this->following()->detach($user->id);
    }

    public function followersCount(): int
    {
        return $this->followers()->count();
    }

    public function followingCount(): int
    {
        return $this->following()->count();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role', 'plan'])
            ->logOnlyDirty()
            ->useLogName('admin');
    }

    // --- Profile completeness ---

    public function getProfileCompletenessAttribute(): int
    {
        $fields = ['name', 'phone', 'country', 'city', 'avatar_url'];
        $filled = collect($fields)->filter(fn ($f) => ! empty($this->$f))->count();

        $profile = $this->profile;
        $profileFields = ['primary_instrument', 'musical_level', 'education_status', 'bio'];
        $profileFilled = 0;
        if ($profile) {
            $profileFilled = collect($profileFields)->filter(fn ($f) => ! empty($profile->$f))->count();
        }

        $total = count($fields) + count($profileFields);
        $done = $filled + $profileFilled;

        return (int) round(($done / $total) * 100);
    }

    public function getMissingProfileFieldsAttribute(): array
    {
        $missing = [];
        $labels = [
            'avatar_url' => __('app.profile.missing_avatar'),
            'phone' => __('app.profile.missing_phone'),
            'country' => __('app.profile.missing_country'),
            'city' => __('app.profile.missing_city'),
        ];

        foreach ($labels as $field => $label) {
            if (empty($this->$field)) {
                $missing[] = $label;
            }
        }

        $profile = $this->profile;
        $profileLabels = [
            'primary_instrument' => __('app.profile.missing_instrument'),
            'musical_level' => __('app.profile.missing_level'),
            'education_status' => __('app.profile.missing_education'),
            'bio' => __('app.profile.missing_bio'),
        ];

        foreach ($profileLabels as $field => $label) {
            if (! $profile || empty($profile->$field)) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    public function getAvatarAttribute(): string
    {
        if ($this->avatar_url) {
            // External URL (Google avatar etc.)
            if (str_starts_with($this->avatar_url, 'http')) {
                return $this->avatar_url;
            }
            // Directly in public/ (new approach, avoids symlink issues)
            if (str_starts_with($this->avatar_url, 'pub:')) {
                return asset(substr($this->avatar_url, 4));
            }

            // Legacy: storage symlink path
            return asset('storage/'.$this->avatar_url);
        }

        return '';
    }

    public function hasAvatar(): bool
    {
        return ! empty($this->avatar_url);
    }
}
