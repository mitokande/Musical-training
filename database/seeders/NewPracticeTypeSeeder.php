<?php

namespace Database\Seeders;

use App\Models\Practice;
use App\Models\ChordPractice;
use App\Models\ScalePractice;
use App\Models\RhythmPractice;
use App\Models\MelodicDictationPractice;
use Illuminate\Database\Seeder;

class NewPracticeTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Add new practice types to practices table
        $newPractices = [
            [
                'name' => 'Chords',
                'slug' => 'chord-practice',
                'description' => 'Identify chord types by ear — major, minor, augmented, diminished, and seventh chords.',
                'type' => 'Recognition',
                'is_premium' => false,
            ],
            [
                'name' => 'Scales & Modes',
                'slug' => 'scale-practice',
                'description' => 'Recognize major, minor, and modal scales by their unique sound quality.',
                'type' => 'Recognition',
                'is_premium' => false,
            ],
            [
                'name' => 'Rhythm',
                'slug' => 'rhythm-practice',
                'description' => 'Listen to rhythmic patterns and identify the correct note value sequence.',
                'type' => 'Rhythm',
                'is_premium' => false,
            ],
            [
                'name' => 'Melodic Dictation',
                'slug' => 'melodic-dictation',
                'description' => 'Listen to a melody and write down the notes you hear.',
                'type' => 'Dictation',
                'is_premium' => false,
            ],
        ];

        foreach ($newPractices as $practice) {
            Practice::updateOrCreate(['slug' => $practice['slug']], $practice);
        }

        // Seed chord practices
        $roots = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];
        $chordTypes = [
            ['chord_type' => 'major',      'voicing' => 'block',       'inversion' => 0, 'other_options' => ['minor', 'augmented', 'diminished']],
            ['chord_type' => 'minor',      'voicing' => 'block',       'inversion' => 0, 'other_options' => ['major', 'diminished', 'augmented']],
            ['chord_type' => 'augmented',  'voicing' => 'block',       'inversion' => 0, 'other_options' => ['major', 'minor', 'diminished']],
            ['chord_type' => 'diminished', 'voicing' => 'block',       'inversion' => 0, 'other_options' => ['minor', 'major', 'augmented']],
            ['chord_type' => 'major7',     'voicing' => 'arpeggiated', 'inversion' => 0, 'other_options' => ['minor7', 'dominant7', 'minor']],
            ['chord_type' => 'minor7',     'voicing' => 'arpeggiated', 'inversion' => 0, 'other_options' => ['major7', 'dominant7', 'minor']],
            ['chord_type' => 'dominant7',  'voicing' => 'block',       'inversion' => 0, 'other_options' => ['major7', 'major', 'minor7']],
            ['chord_type' => 'major',      'voicing' => 'arpeggiated', 'inversion' => 1, 'other_options' => ['major', 'minor', 'major7']],
            ['chord_type' => 'minor',      'voicing' => 'arpeggiated', 'inversion' => 1, 'other_options' => ['major', 'minor7', 'diminished']],
            ['chord_type' => 'major',      'voicing' => 'block',       'inversion' => 2, 'other_options' => ['minor', 'augmented', 'major7']],
        ];

        $rootsForChords = ['C', 'D', 'E', 'F', 'G', 'A'];
        foreach ($chordTypes as $i => $chord) {
            ChordPractice::create([
                'chord_type'   => $chord['chord_type'],
                'root_note'    => $rootsForChords[$i % count($rootsForChords)],
                'voicing'      => $chord['voicing'],
                'inversion'    => $chord['inversion'],
                'octave'       => '4',
                'other_options'=> $chord['other_options'],
            ]);
        }

        // Seed scale practices
        $scaleData = [
            ['scale_type' => 'major',          'root_note' => 'C', 'direction' => 'ascending',  'other_options' => ['natural-minor', 'dorian', 'mixolydian']],
            ['scale_type' => 'natural-minor',  'root_note' => 'A', 'direction' => 'ascending',  'other_options' => ['major', 'dorian', 'phrygian']],
            ['scale_type' => 'harmonic-minor', 'root_note' => 'D', 'direction' => 'ascending',  'other_options' => ['natural-minor', 'major', 'melodic-minor']],
            ['scale_type' => 'melodic-minor',  'root_note' => 'G', 'direction' => 'ascending',  'other_options' => ['harmonic-minor', 'natural-minor', 'dorian']],
            ['scale_type' => 'dorian',         'root_note' => 'D', 'direction' => 'ascending',  'other_options' => ['natural-minor', 'major', 'phrygian']],
            ['scale_type' => 'phrygian',       'root_note' => 'E', 'direction' => 'ascending',  'other_options' => ['natural-minor', 'dorian', 'locrian']],
            ['scale_type' => 'lydian',         'root_note' => 'F', 'direction' => 'ascending',  'other_options' => ['major', 'mixolydian', 'dorian']],
            ['scale_type' => 'mixolydian',     'root_note' => 'G', 'direction' => 'ascending',  'other_options' => ['major', 'dorian', 'lydian']],
            ['scale_type' => 'major',          'root_note' => 'G', 'direction' => 'descending', 'other_options' => ['natural-minor', 'mixolydian', 'dorian']],
            ['scale_type' => 'pentatonic-major','root_note' => 'C', 'direction' => 'ascending', 'other_options' => ['pentatonic-minor', 'major', 'natural-minor']],
        ];

        foreach ($scaleData as $scale) {
            ScalePractice::create([
                'scale_type'   => $scale['scale_type'],
                'root_note'    => $scale['root_note'],
                'direction'    => $scale['direction'],
                'octave'       => '4',
                'other_options'=> $scale['other_options'],
            ]);
        }

        // Seed rhythm practices
        $rhythmData = [
            ['time_signature' => '4/4', 'note_values' => ['quarter','quarter','quarter','quarter'],                     'tempo' => 80,  'bars' => 1, 'other_options' => [['quarter','half','quarter'],['half','half'],['eighth','eighth','quarter','half']]],
            ['time_signature' => '4/4', 'note_values' => ['quarter','eighth','eighth','quarter','quarter'],              'tempo' => 80,  'bars' => 1, 'other_options' => [['quarter','quarter','quarter','quarter'],['half','quarter','quarter'],['eighth','eighth','eighth','eighth','quarter','quarter']]],
            ['time_signature' => '4/4', 'note_values' => ['half','quarter','quarter'],                                  'tempo' => 72,  'bars' => 1, 'other_options' => [['quarter','quarter','quarter','quarter'],['quarter','half','quarter'],['whole']]],
            ['time_signature' => '3/4', 'note_values' => ['quarter','quarter','quarter'],                               'tempo' => 90,  'bars' => 1, 'other_options' => [['dotted-half'],['quarter','half'],['eighth','eighth','quarter','quarter']]],
            ['time_signature' => '3/4', 'note_values' => ['dotted-half'],                                               'tempo' => 80,  'bars' => 1, 'other_options' => [['quarter','quarter','quarter'],['quarter','half'],['half','quarter']]],
            ['time_signature' => '2/4', 'note_values' => ['quarter','quarter'],                                         'tempo' => 100, 'bars' => 1, 'other_options' => [['half'],['eighth','eighth','quarter'],['eighth','eighth','eighth','eighth']]],
            ['time_signature' => '4/4', 'note_values' => ['eighth','eighth','eighth','eighth','eighth','eighth','eighth','eighth'], 'tempo' => 80, 'bars' => 1, 'other_options' => [['quarter','quarter','quarter','quarter'],['quarter','eighth','eighth','quarter','quarter'],['half','quarter','quarter']]],
            ['time_signature' => '4/4', 'note_values' => ['quarter','quarter_rest','quarter','quarter'],                'tempo' => 80,  'bars' => 1, 'other_options' => [['quarter','quarter','quarter','quarter'],['quarter','eighth','eighth','quarter_rest','quarter'],['half','quarter_rest','quarter']]],
            ['time_signature' => '6/8', 'note_values' => ['eighth','eighth','eighth','eighth','eighth','eighth'],       'tempo' => 120, 'bars' => 1, 'other_options' => [['quarter','eighth','quarter','eighth'],['dotted-quarter','eighth','dotted-quarter','eighth'],['eighth','eighth','eighth','eighth_rest','eighth','eighth']]],
            ['time_signature' => '4/4', 'note_values' => ['quarter','quarter','half'],                                  'tempo' => 76,  'bars' => 1, 'other_options' => [['quarter','quarter','quarter','quarter'],['half','half'],['quarter','eighth','eighth','half']]],
        ];

        foreach ($rhythmData as $rhythm) {
            RhythmPractice::create([
                'time_signature' => $rhythm['time_signature'],
                'note_values'    => $rhythm['note_values'],
                'other_options'  => $rhythm['other_options'],
                'tempo'          => $rhythm['tempo'],
                'bars'           => $rhythm['bars'],
            ]);
        }

        // Seed melodic dictation practices
        $dictationData = [
            ['notes' => ['C4','D4','E4','F4','G4'],            'bars' => 1, 'clef' => 'treble', 'key_signature' => 'C', 'tempo' => 60],
            ['notes' => ['G4','E4','C4','E4','G4'],            'bars' => 1, 'clef' => 'treble', 'key_signature' => 'C', 'tempo' => 60],
            ['notes' => ['C4','E4','G4','E4','C4'],            'bars' => 1, 'clef' => 'treble', 'key_signature' => 'C', 'tempo' => 60],
            ['notes' => ['D4','F4','A4','F4','D4'],            'bars' => 1, 'clef' => 'treble', 'key_signature' => 'D', 'tempo' => 60],
            ['notes' => ['E4','G4','B4','G4','E4'],            'bars' => 1, 'clef' => 'treble', 'key_signature' => 'C', 'tempo' => 55],
            ['notes' => ['C4','D4','E4','G4','E4','D4','C4'],  'bars' => 2, 'clef' => 'treble', 'key_signature' => 'C', 'tempo' => 52],
            ['notes' => ['G4','A4','B4','C5'],                 'bars' => 1, 'clef' => 'treble', 'key_signature' => 'G', 'tempo' => 60],
            ['notes' => ['A4','G4','F4','E4','D4'],            'bars' => 1, 'clef' => 'treble', 'key_signature' => 'C', 'tempo' => 60],
            ['notes' => ['C4','E4','G4','C5','G4','E4','C4'], 'bars' => 2, 'clef' => 'treble', 'key_signature' => 'C', 'tempo' => 52],
            ['notes' => ['F4','A4','C5','A4','F4'],            'bars' => 1, 'clef' => 'treble', 'key_signature' => 'F', 'tempo' => 58],
        ];

        foreach ($dictationData as $dictation) {
            MelodicDictationPractice::create([
                'notes'          => $dictation['notes'],
                'bars'           => $dictation['bars'],
                'clef'           => $dictation['clef'],
                'key_signature'  => $dictation['key_signature'],
                'tempo'          => $dictation['tempo'],
                'include_rhythm' => false,
            ]);
        }
    }
}
