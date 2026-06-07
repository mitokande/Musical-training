<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RhythmPractice extends Model
{
    protected $fillable = [
        'time_signature', 'note_values', 'other_options', 'tempo', 'bars', 'allowed_values',
    ];

    protected $casts = [
        'note_values' => 'array',
        'other_options' => 'array',
        'allowed_values' => 'array',
    ];

    public static function schema(): array
    {
        return [
            'name' => 'RhythmPractice',
            'strict' => true,
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'time_signature' => ['type' => 'string', 'enum' => ['2/4', '3/4', '4/4', '6/8']],
                    'note_values' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'description' => 'Sequence of note durations: quarter, eighth, sixteenth, half, whole, quarter_rest, eighth_rest',
                    ],
                    'other_options' => [
                        'type' => 'array',
                        'items' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'description' => '3 alternative wrong rhythm sequences',
                    ],
                    'tempo' => ['type' => 'integer', 'description' => 'BPM e.g. 60-120'],
                    'bars' => ['type' => 'integer', 'description' => 'Number of bars: 1 or 2'],
                ],
                'required' => ['time_signature', 'note_values', 'other_options', 'tempo', 'bars'],
                'additionalProperties' => false,
            ],
        ];
    }

    public static function noteDurations(): array
    {
        return [
            'whole' => 4,
            'half' => 2,
            'quarter' => 1,
            'eighth' => 0.5,
            'sixteenth' => 0.25,
            'quarter_rest' => 1,
            'eighth_rest' => 0.5,
        ];
    }
}
