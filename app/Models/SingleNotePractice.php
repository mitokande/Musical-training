<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SingleNotePractice extends Model
{
    //
    use HasFactory;


    protected $fillable = [
        'target',
        'target_type',
        'other_options',
        'octave',
    ];

    public static function schema() {
        return [
                'type' => 'object',
                'properties' => [
                    'target' => [
                        'type' => 'string',
                        'description' => 'The note to play (e.g., C, D#, Eb). This should be a string.'
                    ],
                    'target_type' => [
                        'type' => 'string',
                        'description' => 'The type of target (e.g., note, chord). This should be a string.'
                    ],
                    'other_options' => [
                        'type' => 'string',
                        'description' => 'The options for the question inclueded the target (e.g., C, D#, Eb). This should be a comma separated list of notes.'
                    ],
                    'octave' => [
                        'type' => 'string',
                        'description' => 'The octave number for the note. This should be a number between 2 and 6.'
                    ],
                    'type' => [
                        'type' => 'string',
                        'enum' => ['single-note'],
                        'description' => 'The type of practice'
                    ],
                ],
                'required' => ['target', 'target_type', 'other_options', 'octave', 'type'],
                'additionalProperties' => false
        ];
    }
}
