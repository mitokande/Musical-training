<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntervalDirectionPractice extends Model
{
    protected $fillable = [
        'clef', 'note1', 'note2', 'direction', 'octave', 'note2_octave',
        'backup_data', 'needs_review', 'validation_status',
    ];

    protected $casts = ['backup_data' => 'array', 'needs_review' => 'boolean'];

    public static function schema() {
        return [
                'type' => 'object',
                'properties' => [
                    'clef' => [
                        'type' => 'string',
                        'enum' => ['treble', 'alto', 'bass'],
                        'description' => 'The clef for displaying the notes'
                    ],
                    'note1' => [
                        'type' => 'string',
                        'description' => 'The first note (e.g., C, D#, Eb)'
                    ],
                    'note2' => [
                        'type' => 'string',
                        'description' => 'The second note (e.g., C, D#, Eb)'
                    ],
                    'direction' => [
                        'type' => 'string',
                        'enum' => ['ascending', 'descending'],
                        'description' => 'Whether the interval goes up (ascending) or down (descending)'
                    ],
                    'octave' => [
                        'type' => 'string',
                        'enum' => ['2', '3', '4', '5', '6'],
                        'description' => 'The octave number for the notes'
                    ],
                    'type' => [
                        'type' => 'string',
                        'enum' => ['interval-direction'],
                        'description' => 'The type of practice'
                    ],
                ],
                'required' => ['clef', 'note1', 'note2', 'direction', 'octave', 'type'],
                'additionalProperties' => false
        ];
    }
}
