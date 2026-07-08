<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherMedia extends Model
{
    public const KIND_PHOTO = 'photo';

    public const KIND_DOCUMENT = 'document';

    public const VISIBILITY_PUBLIC = 'public';    // Herkese — shown on the public profile

    public const VISIBILITY_SHARED = 'shared';    // Paylaştıklarım — approved students only

    public const VISIBILITY_PRIVATE = 'private';  // Sadece bana — owner/admin only

    protected $table = 'teacher_media';

    protected $fillable = [
        'teacher_profile_id', 'kind', 'disk', 'path', 'original_name',
        'mime_type', 'size', 'title', 'visibility', 'sort_order',
    ];

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class);
    }

    public function isPublic(): bool
    {
        return $this->visibility === self::VISIBILITY_PUBLIC;
    }

    public function isShared(): bool
    {
        return $this->visibility === self::VISIBILITY_SHARED;
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', self::VISIBILITY_PUBLIC);
    }

    /** Public URL — only valid for media stored on the public disk. */
    public function publicUrl(): ?string
    {
        return $this->isPublic() ? asset($this->path) : null;
    }

    /**
     * Who may download this file. Owner and admins always; "shared" files are
     * open to the teacher's approved/active students; and any file (regardless
     * of visibility) is downloadable by a student it was attached to via a sent
     * homework assignment.
     */
    public function canBeDownloadedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $teacherUserId = $this->teacherProfile->user_id;

        if ($user->id === $teacherUserId || $user->isAdmin()) {
            return true;
        }

        if ($this->isShared() && TeacherStudentRelationship::query()
            ->active()
            ->where('teacher_id', $teacherUserId)
            ->where('student_id', $user->id)
            ->exists()) {
            return true;
        }

        return TeacherAssignmentRecipient::forStudent($user->id)
            ->whereHas('assignment.media', fn ($q) => $q->whereKey($this->getKey()))
            ->exists();
    }
}
