<?php

namespace App\Services\Teacher;

use App\Models\TeacherConversation;
use App\Models\TeacherConversationAttachment;
use App\Models\TeacherConversationMessage;
use App\Models\TeacherStudentRelationship;
use App\Models\User;
use App\Notifications\Teacher\TeacherMessageReceived;
use App\Services\UsageQuotaService;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

/**
 * One-to-one teacher-student messaging. Every send goes through here so
 * relationship authorization and tier rules stay in one place:
 * - messages flow only inside an active approved relationship;
 * - a Basic-tier teacher may receive but never send (premium capability).
 */
class TeacherMessagingService
{
    private const ATTACHMENT_MIMES = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

    private const MAX_ATTACHMENT_KB = 10240;

    public function __construct(
        private TeacherCapabilityService $capabilities,
        private CrmQuotaService $quota,
        private UsageQuotaService $usage,
    ) {}

    /** Find-or-create the conversation between an active teacher-student pair. */
    public function conversationBetween(User $teacher, User $student): TeacherConversation
    {
        $active = TeacherStudentRelationship::active()
            ->where('teacher_id', $teacher->id)
            ->where('student_id', $student->id)
            ->exists();

        if (! $active) {
            throw new InvalidArgumentException(__('teacher.messaging.error_no_relationship'));
        }

        return TeacherConversation::firstOrCreate([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
        ]);
    }

    /**
     * @param  UploadedFile[]  $attachments
     */
    public function send(TeacherConversation $conversation, User $sender, string $body, array $attachments = []): TeacherConversationMessage
    {
        if (! $conversation->involves($sender)) {
            throw new InvalidArgumentException(__('teacher.messaging.error_not_participant'));
        }

        $isTeacherSide = $sender->id === $conversation->teacher_id;

        if ($isTeacherSide && ! $this->capabilities->canReplyToMessages($sender)) {
            throw new InvalidArgumentException(__('teacher.messaging.error_basic_cannot_reply'));
        }

        // Free (basic-tier) accounts: daily sent-message cap.
        if ($isTeacherSide) {
            $limit = $this->quota->limit($sender, 'daily_teacher_messages');
            if ($limit !== -1
                && $this->usage->userUsed($sender, UsageQuotaService::FEATURE_TEACHER_MESSAGES) >= $limit) {
                throw new InvalidArgumentException(__('teacher.limits.messages_reached', ['limit' => $limit]));
            }
        }

        // Both directions require the relationship to still be active.
        $active = TeacherStudentRelationship::active()
            ->where('teacher_id', $conversation->teacher_id)
            ->where('student_id', $conversation->student_id)
            ->exists();

        if (! $active) {
            throw new InvalidArgumentException(__('teacher.messaging.error_no_relationship'));
        }

        $message = TeacherConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'body' => $body,
        ]);

        foreach ($attachments as $file) {
            $this->storeAttachment($message, $file);
        }

        if ($isTeacherSide) {
            $this->usage->userIncrement($sender, UsageQuotaService::FEATURE_TEACHER_MESSAGES);
        }

        $conversation->update(['last_message_at' => now()]);

        $conversation->otherParty($sender)->notify(new TeacherMessageReceived($conversation, $sender));

        return $message;
    }

    public function markRead(TeacherConversation $conversation, User $reader): void
    {
        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $reader->id)
            ->update(['read_at' => now()]);
    }

    public function unreadTotalFor(User $user): int
    {
        return TeacherConversationMessage::whereNull('read_at')
            ->where('sender_id', '!=', $user->id)
            ->whereHas('conversation', fn ($q) => $q
                ->where('teacher_id', $user->id)
                ->orWhere('student_id', $user->id))
            ->count();
    }

    private function storeAttachment(TeacherConversationMessage $message, UploadedFile $file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, self::ATTACHMENT_MIMES, true)) {
            throw new InvalidArgumentException(__('teacher.messaging.error_attachment_type'));
        }

        if ($file->getSize() > self::MAX_ATTACHMENT_KB * 1024) {
            throw new InvalidArgumentException(__('teacher.messaging.error_attachment_size'));
        }

        $path = $file->store('teacher-message-attachments/'.$message->conversation_id, 'local');

        TeacherConversationAttachment::create([
            'message_id' => $message->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize(),
        ]);
    }
}
