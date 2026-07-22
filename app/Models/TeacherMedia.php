<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TeacherMedia extends Model
{
    public const KIND_PHOTO = 'photo';       // Photos & Certificates gallery — always public

    public const KIND_DOCUMENT = 'document'; // Private document archive (My Documents)

    public const VISIBILITY_PUBLIC = 'public';      // Herkese — shown on the public profile (photos/certificates)

    public const VISIBILITY_STUDENTS = 'students';  // Öğrencilerim — all of the teacher's active students

    public const VISIBILITY_SHARED = 'shared';      // Paylaştıklarım — only specifically-shared students

    public const VISIBILITY_PRIVATE = 'private';    // Sadece bana — owner/admin only

    protected $table = 'teacher_media';

    protected $fillable = [
        'teacher_profile_id', 'kind', 'disk', 'path', 'original_name',
        'mime_type', 'size', 'title', 'visibility', 'sort_order',
    ];

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class);
    }

    /** Students this document has been explicitly shared with ("paylaştıklarım"). */
    public function sharedStudents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teacher_media_shares', 'teacher_media_id', 'user_id')
            ->withTimestamps();
    }

    public function isPublic(): bool
    {
        return $this->visibility === self::VISIBILITY_PUBLIC;
    }

    public function isShared(): bool
    {
        return $this->visibility === self::VISIBILITY_SHARED;
    }

    /** True for image files that can be shown enlarged in a lightbox. */
    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/')
            || in_array(strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true);
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
     * Who may download this file. Owner and admins always. "students" files are
     * open to all of the teacher's active students; any file explicitly shared
     * with a student (teacher_media_shares) is downloadable by that student
     * regardless of its base visibility; and any file is downloadable by a
     * student it was attached to via a sent homework assignment.
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

        // Explicitly shared with this specific student ("paylaştıklarım").
        if ($this->sharedStudents()->whereKey($user->id)->exists()) {
            return true;
        }

        // Open to every active student of the teacher ("öğrencilerim").
        if ($this->visibility === self::VISIBILITY_STUDENTS && TeacherStudentRelationship::query()
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
