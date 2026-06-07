<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, LogsActivity;

    protected $fillable = [
        'name',
        'surname',
        'username',
        'email',
        'password',
        'role',
        'plan',
        'locale',
        'google_id',
        'avatar_url',
        'phone',
        'country',
        'city',
        'date_of_birth',
        'suspended_at',
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
        ];
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

    public function isSchool(): bool
    {
        return $this->role === 'school';
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
        return !is_null($this->suspended_at);
    }

    public function hasPassword(): bool
    {
        return !is_null($this->password);
    }

    public function canAccess(string $feature): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $value = config("plans.{$this->role}.{$this->plan}.{$feature}");

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
        return config("plans.{$this->role}.{$this->plan}.{$feature}");
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
        $filled = collect($fields)->filter(fn ($f) => !empty($this->$f))->count();

        $profile = $this->profile;
        $profileFields = ['primary_instrument', 'musical_level', 'education_status', 'bio'];
        $profileFilled = 0;
        if ($profile) {
            $profileFilled = collect($profileFields)->filter(fn ($f) => !empty($profile->$f))->count();
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
            'phone'      => __('app.profile.missing_phone'),
            'country'    => __('app.profile.missing_country'),
            'city'       => __('app.profile.missing_city'),
        ];

        foreach ($labels as $field => $label) {
            if (empty($this->$field)) {
                $missing[] = $label;
            }
        }

        $profile = $this->profile;
        $profileLabels = [
            'primary_instrument' => __('app.profile.missing_instrument'),
            'musical_level'      => __('app.profile.missing_level'),
            'education_status'   => __('app.profile.missing_education'),
            'bio'                => __('app.profile.missing_bio'),
        ];

        foreach ($profileLabels as $field => $label) {
            if (!$profile || empty($profile->$field)) {
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
            return asset('storage/' . $this->avatar_url);
        }

        return '';
    }

    public function hasAvatar(): bool
    {
        return !empty($this->avatar_url);
    }
}
