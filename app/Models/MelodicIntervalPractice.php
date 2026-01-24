<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MelodicIntervalPractice extends Model
{
    protected $fillable = ['interval', 'note1', 'note2', 'octave'];
}
