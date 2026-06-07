<?php

namespace App\Models;

use App\Services\MusicTheoryService;
use Illuminate\Database\Eloquent\Model;

class ScalePractice extends Model
{
    protected $fillable = [
        'scale_type', 'root_note', 'direction', 'octave', 'other_options',
    ];

    protected $casts = [
        'other_options' => 'array',
    ];

    public static function schema(): array
    {
        return [
            'name' => 'ScalePractice',
            'strict' => true,
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'scale_type' => ['type' => 'string', 'description' => 'Scale name e.g. Major, Natural Minor, Harmonic Minor, Dorian, Phrygian, Lydian, Mixolydian, Pentatonic, Blues'],
                    'root_note' => ['type' => 'string', 'description' => 'Root note e.g. C, D, E, F, G, A, B'],
                    'direction' => ['type' => 'string', 'enum' => ['ascending', 'descending', 'both']],
                    'octave' => ['type' => 'string', 'description' => 'Starting octave e.g. 3, 4, 5'],
                    'other_options' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => '3 wrong scale type answers'],
                ],
                'required' => ['scale_type', 'root_note', 'direction', 'octave', 'other_options'],
                'additionalProperties' => false,
            ],
        ];
    }

    public static function scaleIntervals(): array
    {
        return [
            'Major' => [0, 2, 4, 5, 7, 9, 11, 12],
            'Natural Minor' => [0, 2, 3, 5, 7, 8, 10, 12],
            'Harmonic Minor' => [0, 2, 3, 5, 7, 8, 11, 12],
            'Melodic Minor' => [0, 2, 3, 5, 7, 9, 11, 12],
            'Dorian' => [0, 2, 3, 5, 7, 9, 10, 12],
            'Phrygian' => [0, 1, 3, 5, 7, 8, 10, 12],
            'Lydian' => [0, 2, 4, 6, 7, 9, 11, 12],
            'Mixolydian' => [0, 2, 4, 5, 7, 9, 10, 12],
            'Aeolian' => [0, 2, 3, 5, 7, 8, 10, 12],
            'Locrian' => [0, 1, 3, 5, 6, 8, 10, 12],
            'Pentatonic' => [0, 2, 4, 7, 9, 12],
            'Blues' => [0, 3, 5, 6, 7, 10, 12],
        ];
    }

    /**
     * Diatonic letter-step for each scale degree (how many letter-names above the root).
     * 7-note scales advance one letter per degree (default below); the non-heptatonic
     * scales need an explicit map so they're spelled correctly (e.g. the blues scale's
     * b5 and 5 share a letter → Gb then G).
     */
    public static function scaleLetterSteps(): array
    {
        return [
            'Pentatonic' => [0, 1, 2, 4, 5, 7],     // major pentatonic: 1 2 3 5 6 8
            'Blues' => [0, 2, 3, 4, 4, 6, 7],   // minor blues: 1 b3 4 b5 5 b7 8
        ];
    }

    public function getNoteArrayAttribute(): array
    {
        $music = app(MusicTheoryService::class);
        $intervals = self::scaleIntervals()[$this->scale_type] ?? self::scaleIntervals()['Major'];
        $steps = self::scaleLetterSteps()[$this->scale_type] ?? range(0, count($intervals) - 1);
        $octave = (int) $this->octave;
        $root = $this->root_note;

        $noteArray = [];
        foreach ($intervals as $i => $semitone) {
            $spelled = $music->spellNote($root, $octave, $steps[$i] ?? $i, $semitone);
            if ($spelled !== null) {
                $noteArray[] = $spelled['note'].$spelled['octave'];
            }
        }

        if ($this->direction === 'descending') {
            return array_reverse($noteArray);
        }

        return $noteArray;
    }
}
