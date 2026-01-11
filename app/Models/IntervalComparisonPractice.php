<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntervalComparisonPractice extends Model
{
    //
    protected $fillable = [
        'interval_a',
        'interval_b',
        'target',
        'octave',
        'clef',
    ];

    public static function schema() {
        return [
            'type' => 'object',
            'description' => 'Interval Comparison Practice Question, the user must identify which interval has larger distance. One of the intervals will be larger than the other.',
            'properties' => [
                'interval_a' => [
                    'type' => 'string',
                    'description' => 'The first interval, two notes separated by a comma (e.g. C,E or D,A). Do not include the octave number.'
                ],
                'interval_b' => [
                    'type' => 'string',
                    'description' => 'The second interval, two notes separated by a comma (e.g. C,E or D,A). Do not include the octave number.'
                ],
                'target' => [
                    'type' => 'string',
                    'description' => 'Interval that has larger interval distance. Answer must be either A or B.',
                    'enum' => ['a', 'b']
                ],
                'octave' => [
                    'type' => 'string',
                    'description' => 'The octave number for the intervals (2, 3, 4, 5, 6)'
                ],
                'clef' => [
                    'type' => 'string',
                    'description' => 'The clef for displaying the intervals (treble, alto, bass)'
                ],
            ],
            'required' => ['interval_a', 'interval_b', 'target', 'octave', 'clef'],
            'additionalProperties' => false
        ];
    }   
}
