<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Generic usage counter behind UsageQuotaService. One row per
 * subject (user or guest) + feature + period ('total' or a Y-m-d date).
 * Never query this model directly from controllers — go through the service.
 */
class UsageCounter extends Model
{
    protected $fillable = [
        'subject_type',
        'subject_id',
        'feature',
        'period',
        'count',
    ];
}
