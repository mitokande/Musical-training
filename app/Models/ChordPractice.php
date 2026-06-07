<?php

namespace App\Models;

use App\Services\MusicTheoryService;
use Illuminate\Database\Eloquent\Model;

class ChordPractice extends Model
{
    protected $fillable = [
        'chord_type', 'root_note', 'voicing', 'inversion', 'octave', 'other_options',
    ];

    protected $casts = [
        'other_options' => 'array',
    ];

    public static function schema(): array
    {
        return [
            'name' => 'ChordPractice',
            'strict' => true,
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'chord_type' => ['type' => 'string', 'description' => 'Chord type e.g. Major, Minor, Diminished, Augmented, Dominant 7th, Major 7th, Minor 7th'],
                    'root_note' => ['type' => 'string', 'description' => 'Root note e.g. C, D, E, F, G, A, B'],
                    'voicing' => ['type' => 'string', 'enum' => ['block', 'arpeggiated']],
                    'inversion' => ['type' => 'integer', 'description' => '0 = root position, 1 = first inversion, 2 = second inversion'],
                    'octave' => ['type' => 'string', 'description' => 'Octave number e.g. 3, 4, 5'],
                    'other_options' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => '3 wrong chord type answers'],
                ],
                'required' => ['chord_type', 'root_note', 'voicing', 'inversion', 'octave', 'other_options'],
                'additionalProperties' => false,
            ],
        ];
    }

    public static function chordIntervals(): array
    {
        return [
            'Major' => [0, 4, 7],
            'Minor' => [0, 3, 7],
            'Diminished' => [0, 3, 6],
            'Augmented' => [0, 4, 8],
            'Dominant 7th' => [0, 4, 7, 10],
            'Major 7th' => [0, 4, 7, 11],
            'Minor 7th' => [0, 3, 7, 10],
            'Half Diminished' => [0, 3, 6, 10],
            'Diminished 7th' => [0, 3, 6, 9],
            'Augmented 7th' => [0, 4, 8, 10],
        ];
    }

    public function getNoteArrayAttribute(): array
    {
        $music = app(MusicTheoryService::class);
        $intervals = self::chordIntervals()[$this->chord_type] ?? self::chordIntervals()['Major'];
        $octave = (int) $this->octave;
        $root = $this->root_note;

        $noteArray = [];
        foreach ($intervals as $i => $semitones) {
            // Chord tones are stacked thirds, so each tone is two letter-names apart
            // (root, 3rd, 5th, 7th → letter steps 0, 2, 4, 6). This spells e.g. Eb major
            // as Eb-G-Bb and C diminished as C-Eb-Gb, not with sharps.
            $spelled = $music->spellNote($root, $octave, $i * 2, $semitones);
            if ($spelled !== null) {
                $noteArray[] = $spelled['note'].$spelled['octave'];
            }
        }

        return $noteArray;
    }
}
