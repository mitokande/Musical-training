<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Practice extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'is_premium',
    ];

    protected $casts = [
        'is_premium' => 'boolean',
    ];
}
