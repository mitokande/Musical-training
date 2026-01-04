<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntervalDirectionPractice extends Model
{
    protected $fillable = [
        'clef',
        'note1',
        'note2',
        'direction',
        'octave',
    ];
}
