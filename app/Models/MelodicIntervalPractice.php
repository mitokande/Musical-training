<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MelodicIntervalPractice extends Model
{
    protected $fillable = ['interval', 'note1', 'note2', 'octave', 'note2_octave', 'backup_data', 'needs_review', 'validation_status'];

    protected $casts = ['backup_data' => 'array', 'needs_review' => 'boolean'];

    public static function schema() {
        return [
            'type' => 'object',
            'description' => 'Melodic Interval Practice Question. Two notes are played sequentially and the student must identify the interval name.',
            'properties' => [
                'interval' => [
                    'type' => 'string',
                    'enum' => ['Minor 2nd','Major 2nd','Minor 3rd','Major 3rd','Perfect 4th','Tritone','Perfect 5th','Minor 6th','Major 6th','Minor 7th','Major 7th','Perfect Octave'],
                    'description' => 'The interval name',
                ],
                'note1' => [
                    'type' => 'string',
                    'description' => 'The first note (e.g., C, D, E, F, G, A, B)'
                ],
                'note2' => [
                    'type' => 'string',
                    'description' => 'The second note (e.g., C, D, E, F, G, A, B)'
                ],
                'octave' => [
                    'type' => 'string',
                    'description' => 'The octave number for the notes (2, 3, 4, 5, 6)'
                ],
                'type' => [
                    'type' => 'string',
                    'enum' => ['melodic-interval'],
                    'description' => 'The type of practice'
                ],
            ],
            'required' => ['interval', 'note1', 'note2', 'octave', 'type'],
            'additionalProperties' => false
        ];
    }
}
