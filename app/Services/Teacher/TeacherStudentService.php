<?php

namespace App\Services\Teacher;

use App\Mail\TeacherStudentInvitationMail;
use App\Models\TeacherClass;
use App\Models\TeacherStudentInvitation;
use App\Models\TeacherStudentRelationship;
use App\Models\User;
use App\Notifications\Teacher\TeacherRelationshipAccepted;
use App\Notifications\Teacher\TeacherRelationshipDeclined;
use App\Notifications\Teacher\TeacherRelationshipRequested;
use App\Notifications\Teacher\TeacherRelationshipRevoked;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

/**
 * All teacher-student relationship and invitation state changes go through
 * this service so side effects (class membership cleanup, benefit
 * recalculation, notifications) stay consistent.
 */
class TeacherStudentService
{
    public const EMAIL_INVITATION_TTL_DAYS = 14;

    public function __construct(
        private TeacherSubscriptionBenefitService $benefits,
        private CrmQuotaService $quota,
    ) {}

    /** Flow A: teacher sends a relationship request to an existing user. */
    public function requestExistingUser(User $teacher, User $student): TeacherStudentRelationship
    {
        $this->assertCanTarget($teacher, $student);
        $this->assertUnderStudentLimit($teacher, $student);

        $relationship = $this->freshRelationship(
            $teacher,
            $student,
            TeacherStudentRelationship::STATUS_PENDING_STUDENT_APPROVAL
        );

        $student->notify(new TeacherRelationshipRequested($teacher));

        return $relationship;
    }

    /** Flow B: teacher invites a student by email (registered or not). */
    public function inviteByEmail(User $teacher, string $email, ?string $name = null, ?int $classId = null): TeacherStudentInvitation
    {
        $email = strtolower(trim($email));

        if (strtolower($teacher->email) === $email) {
            throw new InvalidArgumentException(__('teacher.students.error_self'));
        }

        if ($existing = User::where('email', $email)->first()) {
            $this->assertCanTarget($teacher, $existing);
            $this->assertUnderStudentLimit($teacher, $existing);
        } else {
            // Unregistered invitee: plan unknown, so the cap is re-checked
            // (with the real plan) at accept time. Block early only when even
            // a free student could no longer be added.
            $this->assertUnderStudentLimit($teacher, null);
        }

        $duplicate = TeacherStudentInvitation::pending()
            ->where('teacher_id', $teacher->id)
            ->where('type', TeacherStudentInvitation::TYPE_EMAIL)
            ->where('email', $email)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();

        if ($duplicate) {
            throw new InvalidArgumentException(__('teacher.students.error_duplicate_invitation'));
        }

        $invitation = TeacherStudentInvitation::create([
            'teacher_id' => $teacher->id,
            'type' => TeacherStudentInvitation::TYPE_EMAIL,
            'email' => $email,
            'name' => $name,
            'token' => TeacherStudentInvitation::generateToken(),
            'teacher_class_id' => $this->validClassId($teacher, $classId),
            'expires_at' => now()->addDays(self::EMAIL_INVITATION_TTL_DAYS),
        ]);

        Mail::to($email)->queue(new TeacherStudentInvitationMail($invitation));

        return $invitation;
    }

    /** Flow C: shareable multi-use invitation link. */
    public function createLinkInvitation(User $teacher, ?\DateTimeInterface $expiresAt = null, ?int $classId = null): TeacherStudentInvitation
    {
        return TeacherStudentInvitation::create([
            'teacher_id' => $teacher->id,
            'type' => TeacherStudentInvitation::TYPE_LINK,
            'token' => TeacherStudentInvitation::generateToken(),
            'teacher_class_id' => $this->validClassId($teacher, $classId),
            'expires_at' => $expiresAt,
        ]);
    }

    public function revokeInvitation(TeacherStudentInvitation $invitation): void
    {
        $invitation->update(['status' => TeacherStudentInvitation::STATUS_REVOKED]);
    }

    /**
     * Student opens an invitation while authenticated. Accepting an invitation
     * is the student's approval, so the relationship becomes active directly.
     */
    public function acceptInvitation(User $student, TeacherStudentInvitation $invitation): TeacherStudentRelationship
    {
        if (! $invitation->isUsable()) {
            throw new InvalidArgumentException(__('teacher.invitations.error_unusable'));
        }

        $teacher = $invitation->teacher;
        $this->assertCanTarget($teacher, $student);
        $this->assertUnderStudentLimit($teacher, $student);

        $relationship = DB::transaction(function () use ($teacher, $student, $invitation) {
            $relationship = $this->freshRelationship(
                $teacher,
                $student,
                TeacherStudentRelationship::STATUS_ACTIVE,
                approvedAt: now()
            );

            // Single-use for email invitations; shareable links stay usable
            // until they expire or the teacher revokes them.
            if ($invitation->type === TeacherStudentInvitation::TYPE_EMAIL) {
                $invitation->update([
                    'status' => TeacherStudentInvitation::STATUS_ACCEPTED,
                    'accepted_by' => $student->id,
                    'accepted_at' => now(),
                ]);
            }

            if ($invitation->teacher_class_id) {
                $class = TeacherClass::find($invitation->teacher_class_id);
                if ($class && $class->teacher_id === $teacher->id && ! $class->isArchived()) {
                    $class->students()->syncWithoutDetaching([$student->id]);
                }
            }

            return $relationship;
        });

        $teacher->notify(new TeacherRelationshipAccepted($student));
        $this->benefits->recalculate($teacher);

        return $relationship;
    }

    /** Student approves a pending relationship request from a teacher. */
    public function approve(TeacherStudentRelationship $relationship): void
    {
        if ($relationship->status !== TeacherStudentRelationship::STATUS_PENDING_STUDENT_APPROVAL) {
            throw new InvalidArgumentException(__('teacher.invitations.error_not_pending'));
        }

        $relationship->update([
            'status' => TeacherStudentRelationship::STATUS_ACTIVE,
            'approved_at' => now(),
            'revoked_at' => null,
        ]);

        $relationship->teacher->notify(new TeacherRelationshipAccepted($relationship->student));
        $this->benefits->recalculate($relationship->teacher);
    }

    public function decline(TeacherStudentRelationship $relationship): void
    {
        if ($relationship->status !== TeacherStudentRelationship::STATUS_PENDING_STUDENT_APPROVAL) {
            throw new InvalidArgumentException(__('teacher.invitations.error_not_pending'));
        }

        $relationship->update(['status' => TeacherStudentRelationship::STATUS_DECLINED]);

        $relationship->teacher->notify(new TeacherRelationshipDeclined($relationship->student));
    }

    public function revokeByTeacher(TeacherStudentRelationship $relationship): void
    {
        $this->revoke($relationship, TeacherStudentRelationship::STATUS_REVOKED_BY_TEACHER);
        $relationship->student->notify(new TeacherRelationshipRevoked($relationship->teacher, byTeacher: true));
    }

    public function revokeByStudent(TeacherStudentRelationship $relationship): void
    {
        $this->revoke($relationship, TeacherStudentRelationship::STATUS_REVOKED_BY_STUDENT);
        $relationship->teacher->notify(new TeacherRelationshipRevoked($relationship->student, byTeacher: false));
    }

    private function revoke(TeacherStudentRelationship $relationship, string $status): void
    {
        DB::transaction(function () use ($relationship, $status) {
            $relationship->update([
                'status' => $status,
                'revoked_at' => now(),
            ]);

            // Remove the student from all of this teacher's classes.
            DB::table('teacher_class_students')
                ->where('student_id', $relationship->student_id)
                ->whereIn('teacher_class_id', TeacherClass::where('teacher_id', $relationship->teacher_id)->pluck('id'))
                ->delete();
        });

        $this->benefits->recalculate($relationship->teacher);
    }

    /**
     * Create or reset the relationship row (the pair is unique). Declined or
     * revoked pairs may be re-invited; active or pending pairs may not.
     */
    private function freshRelationship(User $teacher, User $student, string $status, ?\DateTimeInterface $approvedAt = null): TeacherStudentRelationship
    {
        $relationship = TeacherStudentRelationship::firstOrNew([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
        ]);

        $relationship->fill([
            'status' => $status,
            'approved_at' => $approvedAt,
            'revoked_at' => null,
        ])->save();

        return $relationship;
    }

    private function assertCanTarget(User $teacher, User $student): void
    {
        if ($teacher->id === $student->id) {
            throw new InvalidArgumentException(__('teacher.students.error_self'));
        }

        $existing = TeacherStudentRelationship::where('teacher_id', $teacher->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing && in_array($existing->status, [
            TeacherStudentRelationship::STATUS_ACTIVE,
            TeacherStudentRelationship::STATUS_PENDING_STUDENT_APPROVAL,
            TeacherStudentRelationship::STATUS_PENDING_TEACHER_REQUEST,
        ], true)) {
            throw new InvalidArgumentException(__('teacher.students.error_already_related'));
        }
    }

    /**
     * Free-tier accounts may hold at most max_free_students free-plan
     * students (active + pending). Premium-plan students never count and are
     * unlimited — the premium-student incentive depends on that.
     */
    private function assertUnderStudentLimit(User $teacher, ?User $student): void
    {
        if (! $this->quota->canAddStudent($teacher, $student)) {
            throw new InvalidArgumentException(__('teacher.limits.students_reached', [
                'limit' => $this->quota->limit($teacher, 'max_free_students'),
            ]));
        }
    }

    private function validClassId(User $teacher, ?int $classId): ?int
    {
        if ($classId === null) {
            return null;
        }

        $owns = TeacherClass::where('id', $classId)->where('teacher_id', $teacher->id)->exists();

        return $owns ? $classId : null;
    }
}
