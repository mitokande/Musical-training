<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPractice extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'user_id',
        'practice_id',
        'total_questions',
        'correct_answers',
        'incorrect_answers',
        'skipped_answers',
        'total_time',
        'average_time',
    ];

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
