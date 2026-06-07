<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MelodicDictationPractice extends Model
{
    protected $fillable = [
        'notes', 'bars', 'clef', 'key_signature', 'tempo', 'include_rhythm',
    ];

    protected $casts = [
        'notes' => 'array',
        'include_rhythm' => 'boolean',
    ];

    public static function schema(): array
    {
        return [
            'name' => 'MelodicDictationPractice',
            'strict' => true,
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'notes' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'description' => 'Array of notes with octave e.g. ["C4", "E4", "G4"]',
                    ],
                    'bars' => ['type' => 'integer', 'description' => '2 or 4 bars'],
                    'clef' => ['type' => 'string', 'enum' => ['treble', 'bass', 'alto']],
                    'key_signature' => ['type' => 'string', 'description' => 'Key e.g. C, G, F, D'],
                    'tempo' => ['type' => 'integer', 'description' => 'BPM e.g. 60-100'],
                    'include_rhythm' => ['type' => 'boolean'],
                ],
                'required' => ['notes', 'bars', 'clef', 'key_signature', 'tempo', 'include_rhythm'],
                'additionalProperties' => false,
            ],
        ];
    }
}
