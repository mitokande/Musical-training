<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherConversation extends Model
{
    protected $fillable = ['teacher_id', 'student_id', 'last_message_at'];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TeacherConversationMessage::class, 'conversation_id');
    }

    public function otherParty(User $user): User
    {
        return $user->id === $this->teacher_id ? $this->student : $this->teacher;
    }

    public function involves(User $user): bool
    {
        return in_array($user->id, [$this->teacher_id, $this->student_id], true);
    }

    public function unreadCountFor(User $user): int
    {
        return $this->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $user->id)
            ->count();
    }
}
