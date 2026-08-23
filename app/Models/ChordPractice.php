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
            // Triads & Sus
            'Major' => [0, 4, 7],
            'Minor' => [0, 3, 7],
            'Diminished' => [0, 3, 6],
            'Augmented' => [0, 4, 8],
            'Sus2' => [0, 2, 7],
            'Sus4' => [0, 5, 7],
            // 7th Chords
            'Major 7th' => [0, 4, 7, 11],
            'Dominant 7th' => [0, 4, 7, 10],
            'Minor 7th' => [0, 3, 7, 10],
            'Minor Major 7th' => [0, 3, 7, 11],
            'Half-Diminished 7th' => [0, 3, 6, 10],
            'Half Diminished' => [0, 3, 6, 10],  // legacy alias
            'Diminished 7th' => [0, 3, 6, 9],
            'Augmented 7th' => [0, 4, 8, 10],
            // Color Chords
            'Major 6th' => [0, 4, 7, 9],
            'Minor 6th' => [0, 3, 7, 9],
            'Add9' => [0, 4, 7, 14],
            'Minor Add9' => [0, 3, 7, 14],
        ];
    }

    /**
     * Custom diatonic letter-step arrays for chord types that are NOT built
     * in stacked thirds (where the default i×2 pattern would mis-spell notes).
     *
     * Key: chord type name  →  Value: letter steps for each chord tone.
     */
    public static function chordLetterSteps(): array
    {
        return [
            'Sus2' => [0, 1, 4],       // root, 2nd, 5th
            'Sus4' => [0, 3, 4],       // root, 4th, 5th
            'Major 6th' => [0, 2, 4, 5],    // root, 3rd, 5th, 6th
            'Minor 6th' => [0, 2, 4, 5],    // root, 3rd, 5th, 6th
            'Add9' => [0, 2, 4, 8],    // root, 3rd, 5th, 9th (D = 8 steps above C)
            'Minor Add9' => [0, 2, 4, 8],    // root, 3rd, 5th, 9th
        ];
    }

    public function getNoteArrayAttribute(): array
    {
        $music = app(MusicTheoryService::class);
        $intervals = self::chordIntervals()[$this->chord_type] ?? self::chordIntervals()['Major'];
        $octave = (int) $this->octave;
        $root = $this->root_note;

        $customSteps = self::chordLetterSteps()[$this->chord_type] ?? null;
        $noteArray = [];
        foreach ($intervals as $i => $semitones) {
            // Most chords are built in stacked thirds (0, 2, 4, 6 letter steps).
            // Sus / 6th / Add9 chords use custom step arrays stored in chordLetterSteps().
            $step = $customSteps !== null ? $customSteps[$i] : $i * 2;
            // `simplify: false` — a chord is read, not typed, so it is spelled
            // the way it is built: B augmented is B-D#-F##, F diminished is
            // F-Ab-Cb, C diminished 7th is C-Eb-Gb-Bbb.
            $spelled = $music->spellNote($root, $octave, $step, $semitones, simplify: false);
            if ($spelled !== null) {
                $noteArray[] = $spelled['note'].$spelled['octave'];
            }
        }

        // Apply inversion: move the lowest $inversion notes up an octave, then
        // rotate them to the top so the array stays in ascending pitch order.
        $inversion = (int) ($this->inversion ?? 0);
        if ($inversion > 0 && count($noteArray) > $inversion) {
            for ($i = 0; $i < $inversion; $i++) {
                if (preg_match('/^([A-Ga-g](?:#{1,2}|b{1,2})?)(\d+)$/', $noteArray[$i], $m)) {
                    $noteArray[$i] = $m[1].((int) $m[2] + 1);
                }
            }
            $noteArray = array_merge(
                array_slice($noteArray, $inversion),
                array_slice($noteArray, 0, $inversion)
            );
        }

        return $noteArray;
    }
}
