<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherConversationAttachment extends Model
{
    protected $fillable = ['message_id', 'disk', 'path', 'original_name', 'mime_type', 'size'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(TeacherConversationMessage::class, 'message_id');
    }
}
