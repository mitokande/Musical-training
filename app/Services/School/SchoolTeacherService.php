<?php

namespace App\Services\School;

use App\Mail\SchoolTeacherInvitationMail;
use App\Models\SchoolTeacherInvitation;
use App\Models\SchoolTeacherRelationship;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Notifications\School\SchoolTeacherRelationshipAccepted;
use App\Notifications\School\SchoolTeacherRelationshipDeclined;
use App\Notifications\School\SchoolTeacherRelationshipRequested;
use App\Notifications\School\SchoolTeacherRelationshipRevoked;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

/**
 * All school ↔ member-teacher membership and invitation state changes go
 * through this service. Mirrors TeacherStudentService: request/approve/
 * decline/revoke lifecycle plus email/link invitations, with the school
 * plan's max_teachers limit enforced on every path that adds a member.
 */
class SchoolTeacherService
{
    public const EMAIL_INVITATION_TTL_DAYS = 14;

    /** Flow A: school sends a membership request to an existing user. */
    public function requestExistingTeacher(User $school, User $teacher): SchoolTeacherRelationship
    {
        $this->assertCanTarget($school, $teacher);
        $this->assertUnderTeacherLimit($school);

        $relationship = $this->freshRelationship(
            $school,
            $teacher,
            SchoolTeacherRelationship::STATUS_PENDING_TEACHER_APPROVAL
        );

        $teacher->notify(new SchoolTeacherRelationshipRequested($school));

        return $relationship;
    }

    /** Flow B: school invites a teacher by email (registered or not). */
    public function inviteByEmail(User $school, string $email, ?string $name = null): SchoolTeacherInvitation
    {
        $email = strtolower(trim($email));

        if (strtolower($school->email) === $email) {
            throw new InvalidArgumentException(__('school.teachers.error_self'));
        }

        if ($existing = User::where('email', $email)->first()) {
            $this->assertCanTarget($school, $existing);
        }

        $duplicate = SchoolTeacherInvitation::pending()
            ->where('school_id', $school->id)
            ->where('type', SchoolTeacherInvitation::TYPE_EMAIL)
            ->where('email', $email)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();

        if ($duplicate) {
            throw new InvalidArgumentException(__('school.teachers.error_duplicate_invitation'));
        }

        $this->assertUnderTeacherLimit($school);

        $invitation = SchoolTeacherInvitation::create([
            'school_id' => $school->id,
            'type' => SchoolTeacherInvitation::TYPE_EMAIL,
            'email' => $email,
            'name' => $name,
            'token' => SchoolTeacherInvitation::generateToken(),
            'expires_at' => now()->addDays(self::EMAIL_INVITATION_TTL_DAYS),
        ]);

        // The invitee is not a registered user yet, so their language is
        // unknown — invitations always go out in English.
        Mail::to($email)->locale('en')->queue(new SchoolTeacherInvitationMail($invitation));

        return $invitation;
    }

    /** Flow C: shareable multi-use invitation link. */
    public function createLinkInvitation(User $school, ?\DateTimeInterface $expiresAt = null): SchoolTeacherInvitation
    {
        return SchoolTeacherInvitation::create([
            'school_id' => $school->id,
            'type' => SchoolTeacherInvitation::TYPE_LINK,
            'token' => SchoolTeacherInvitation::generateToken(),
            'expires_at' => $expiresAt,
        ]);
    }

    public function revokeInvitation(SchoolTeacherInvitation $invitation): void
    {
        $invitation->update(['status' => SchoolTeacherInvitation::STATUS_REVOKED]);
    }

    /**
     * Teacher opens an invitation while authenticated. Accepting is the
     * teacher's approval, so the membership becomes active directly. Teachers
     * without a teacher account get a draft profile so the full CRM opens up.
     */
    public function acceptInvitation(User $teacher, SchoolTeacherInvitation $invitation): SchoolTeacherRelationship
    {
        if (! $invitation->isUsable()) {
            throw new InvalidArgumentException(__('teacher.invitations.error_unusable'));
        }

        $school = $invitation->school;
        $this->assertCanTarget($school, $teacher);
        $this->assertUnderTeacherLimit($school);

        $relationship = DB::transaction(function () use ($school, $teacher, $invitation) {
            $relationship = $this->freshRelationship(
                $school,
                $teacher,
                SchoolTeacherRelationship::STATUS_ACTIVE,
                approvedAt: now()
            );

            if (! $teacher->teacherProfile()->exists()) {
                TeacherProfile::createDraftFor($teacher);
            }

            // Single-use for email invitations; shareable links stay usable
            // until they expire or the school revokes them.
            if ($invitation->type === SchoolTeacherInvitation::TYPE_EMAIL) {
                $invitation->update([
                    'status' => SchoolTeacherInvitation::STATUS_ACCEPTED,
                    'accepted_by' => $teacher->id,
                    'accepted_at' => now(),
                ]);
            }

            return $relationship;
        });

        $school->notify(new SchoolTeacherRelationshipAccepted($teacher));

        return $relationship;
    }

    /** Teacher approves a pending membership request from a school. */
    public function approve(SchoolTeacherRelationship $relationship): void
    {
        if ($relationship->status !== SchoolTeacherRelationship::STATUS_PENDING_TEACHER_APPROVAL) {
            throw new InvalidArgumentException(__('teacher.invitations.error_not_pending'));
        }

        $this->assertUnderTeacherLimit($relationship->school, ignorePending: $relationship);

        $relationship->update([
            'status' => SchoolTeacherRelationship::STATUS_ACTIVE,
            'approved_at' => now(),
            'revoked_at' => null,
        ]);

        // Member teachers need a teacher account to use the CRM the school
        // manages them through.
        if (! $relationship->teacher->teacherProfile()->exists()) {
            TeacherProfile::createDraftFor($relationship->teacher);
        }

        $relationship->school->notify(new SchoolTeacherRelationshipAccepted($relationship->teacher));
    }

    public function decline(SchoolTeacherRelationship $relationship): void
    {
        if ($relationship->status !== SchoolTeacherRelationship::STATUS_PENDING_TEACHER_APPROVAL) {
            throw new InvalidArgumentException(__('teacher.invitations.error_not_pending'));
        }

        $relationship->update(['status' => SchoolTeacherRelationship::STATUS_DECLINED]);

        $relationship->school->notify(new SchoolTeacherRelationshipDeclined($relationship->teacher));
    }

    public function revokeBySchool(SchoolTeacherRelationship $relationship): void
    {
        $this->revoke($relationship, SchoolTeacherRelationship::STATUS_REVOKED_BY_SCHOOL);
        $relationship->teacher->notify(new SchoolTeacherRelationshipRevoked($relationship->school, bySchool: true));
    }

    public function revokeByTeacher(SchoolTeacherRelationship $relationship): void
    {
        $this->revoke($relationship, SchoolTeacherRelationship::STATUS_REVOKED_BY_TEACHER);
        $relationship->school->notify(new SchoolTeacherRelationshipRevoked($relationship->teacher, bySchool: false));
    }

    private function revoke(SchoolTeacherRelationship $relationship, string $status): void
    {
        $relationship->update([
            'status' => $status,
            'revoked_at' => now(),
        ]);
    }

    /**
     * Create or reset the membership row (the pair is unique). Declined or
     * revoked pairs may be re-invited; active or pending pairs may not.
     */
    private function freshRelationship(User $school, User $teacher, string $status, ?\DateTimeInterface $approvedAt = null): SchoolTeacherRelationship
    {
        $relationship = SchoolTeacherRelationship::firstOrNew([
            'school_id' => $school->id,
            'teacher_id' => $teacher->id,
        ]);

        $relationship->fill([
            'status' => $status,
            'approved_at' => $approvedAt,
            'revoked_at' => null,
        ])->save();

        return $relationship;
    }

    private function assertCanTarget(User $school, User $teacher): void
    {
        if ($school->id === $teacher->id) {
            throw new InvalidArgumentException(__('school.teachers.error_self'));
        }

        if ($teacher->isSchool() || ($teacher->teacherProfile?->isSchoolEntity() ?? false)) {
            throw new InvalidArgumentException(__('school.teachers.error_target_school'));
        }

        $existing = SchoolTeacherRelationship::where('school_id', $school->id)
            ->where('teacher_id', $teacher->id)
            ->first();

        if ($existing && in_array($existing->status, [
            SchoolTeacherRelationship::STATUS_ACTIVE,
            SchoolTeacherRelationship::STATUS_PENDING_TEACHER_APPROVAL,
        ], true)) {
            throw new InvalidArgumentException(__('school.teachers.error_already_related'));
        }
    }

    /**
     * config/plans.php school.{free|premium}.max_teachers; -1 means unlimited.
     * Active + pending memberships and pending email invitations all count,
     * so a basic school cannot queue up more members than its plan allows.
     */
    private function assertUnderTeacherLimit(User $school, ?SchoolTeacherRelationship $ignorePending = null): void
    {
        // Premium plan or an earned free-period benefit lifts the cap.
        if ($school->isEffectivelyPremium()) {
            return;
        }

        $planKey = $school->teacherProfile?->planKey() ?? 'free';
        $limit = (int) config("plans.school.{$planKey}.max_teachers", 2);

        if ($limit === -1) {
            return;
        }

        $memberCount = SchoolTeacherRelationship::where('school_id', $school->id)
            ->whereIn('status', [
                SchoolTeacherRelationship::STATUS_ACTIVE,
                SchoolTeacherRelationship::STATUS_PENDING_TEACHER_APPROVAL,
            ])
            ->when($ignorePending, fn ($q) => $q->where('id', '!=', $ignorePending->id))
            ->count();

        $pendingInvitations = SchoolTeacherInvitation::pending()
            ->where('school_id', $school->id)
            ->where('type', SchoolTeacherInvitation::TYPE_EMAIL)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->count();

        if ($memberCount + $pendingInvitations >= $limit) {
            throw new InvalidArgumentException(__('school.teachers.error_limit_reached', ['limit' => $limit]));
        }
    }
}
