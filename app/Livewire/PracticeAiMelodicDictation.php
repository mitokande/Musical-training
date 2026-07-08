<?php

namespace App\Livewire;

use App\Models\MelodicDictationPractice;
use App\Services\LearningPathQuestionGenerator;
use App\Services\TonalMelodyGenerator;

/**
 * AI Assisted Exercises variant of Melodic Dictation.
 *
 * Inherits the full Exercise Setup dictation engine (beat-pattern rhythm
 * generation, TonalMelodyGenerator pitches, answer checking) unchanged.
 * The only difference: instead of user-picked setup settings, the session
 * settings (key, time signature, tempo, note-value pool) are auto-derived
 * from the AI page's question count + difficulty selection.
 */
class PracticeAiMelodicDictation extends PracticeMelodicDictation
{
    public function mount($practices = [])
    {
        $settings = session('ai_dictation_settings', []);
        session()->forget('ai_dictation_settings');

        if (empty($settings)) {
            // Page refresh / direct visit without an AI session: nothing to show,
            // the blade renders its "No exercises found" card linking back.
            $this->practiceDataArray = $this->serializePractices(is_array($practices) ? $practices : []);

            return;
        }

        // AI difficulties (easy/medium/hard/adaptive) → melody generator levels
        $this->difficulty = match ($settings['difficulty'] ?? 'adaptive') {
            'easy' => 'beginner',
            'hard' => 'advanced',
            default => 'intermediate', // medium + adaptive
        };

        $auto = $this->autoSettingsForLevel($this->difficulty);

        $this->replayLimit = null;
        $this->feedbackMode = 'immediate';
        $this->timeLimitSeconds = 0;
        $this->dictationTimeSignature = $auto['time_signature'];
        $this->dictationKeySignature = $auto['key_signature'];
        $this->dictationTempo = $auto['tempo'];
        $this->dictationMetronome = true;
        $this->dictationMode = $auto['mode'];
        $this->dictationNoteValues = $auto['note_values'];

        $count = max(1, (int) ($settings['question_count'] ?? 10));
        $bars = 2;
        $clef = 'treble';
        $timeSig = $this->dictationTimeSignature;

        $majorKeyRoot = $this->parseMajorRoot($this->dictationKeySignature);
        $minorKeyRoot = $this->parseMinorRoot($this->dictationKeySignature);
        $tonicRoot = $this->dictationMode === 'minor' ? $minorKeyRoot : $majorKeyRoot;

        $melodyGenerator = app(TonalMelodyGenerator::class);
        $context = $melodyGenerator->contextForKey($majorKeyRoot, $this->dictationMode, $clef);

        $generator = app(LearningPathQuestionGenerator::class);

        $rhythmValues = array_values(array_filter($auto['note_values'], fn ($v) => isset(self::BEAT16[$v]) && ! $this->isRest($v)));

        if (empty($rhythmValues)) {
            $rhythmValues = ['quarter'];
        }

        // Same rule as Exercise Setup's Rests toggle: when the pool contains
        // rest values, exactly one rest is injected into each melody.
        $addRests = count($rhythmValues) < count(array_filter($auto['note_values'], fn ($v) => isset(self::BEAT16[$v])));

        $generated = collect();
        for ($qi = 0; $qi < $count; $qi++) {
            $noteValues = $this->generateBeatPatternRhythm($bars, $timeSig, $rhythmValues);
            $noteCount = count($noteValues);
            $notes = $melodyGenerator->generateMelody($noteCount, $context, $this->difficulty);
            $notes = $melodyGenerator->applyAccidentals($notes, $majorKeyRoot, $this->dictationMode, $this->difficulty);

            if ($addRests) {
                [$notes, $noteValues] = $this->injectOneRestIntoMelody($notes, $noteValues);
            }

            $q = new MelodicDictationPractice;
            $q->id = $qi + 1;
            $q->notes = $notes;
            $q->note_values = $noteValues;
            $q->time_signature = $timeSig;
            $q->key_signature = $majorKeyRoot;
            $q->tonic = $tonicRoot;
            $q->bars = $bars;
            $q->clef = $clef;
            $q->tempo = $this->dictationTempo;
            $generated->push($q);
        }

        session(['exercise_practice_session' => [
            'practice_type' => 'melodic-dictation',
            'question_count' => $generated->count(),
            'questions' => $generator->serializeForSession($generated),
        ]]);

        $this->practiceDataArray = $this->serializePractices($generated->all());
    }

    public function render()
    {
        $currentPractice = $this->buildModelFromData(MelodicDictationPractice::class, $this->getCurrentPracticeData());

        return view('livewire.practice-ai-melodic-dictation', [
            'practices' => $this->practiceDataArray,
            'currentPractice' => $currentPractice,
            'currentPracticeIndex' => $this->currentPracticeIndex,
            'dictationTimeSignature' => $this->dictationTimeSignature,
            'dictationKeySignature' => $this->dictationKeySignature,
            'dictationTempo' => $this->dictationTempo,
            'dictationMetronome' => $this->dictationMetronome,
            'dictationNoteValues' => $this->dictationNoteValues,
            'dictationMode' => $this->dictationMode,
        ]);
    }

    /**
     * Auto-derived session settings per melody level. Pools widen with level
     * the same way the Exercise Setup defaults do; one key / time signature /
     * tempo / mode is fixed for the whole session, matching the setup flow.
     * Advanced additionally engages the optional Setup rules the same way it
     * already does for dotted values: rests (one injected per melody) and the
     * minor mode (minor tonic + minor accidental handling).
     *
     * @return array{key_signature: string, time_signature: string, tempo: int, note_values: string[], mode: string}
     */
    protected function autoSettingsForLevel(string $level): array
    {
        [$keys, $timeSigs, $tempo, $noteValues, $modes] = match ($level) {
            'beginner' => [
                ['C Major / A minor', 'G Major / E minor'],
                ['4/4'],
                50,
                ['quarter', 'half'],
                ['major'],
            ],
            'advanced' => [
                ['C Major / A minor', 'G Major / E minor', 'D Major / B minor', 'F Major / D minor', 'Bb Major / G minor'],
                ['4/4', '3/4', '6/8'],
                70,
                ['quarter', 'eighth', 'sixteenth', 'half', 'dotted-quarter', 'dotted-eighth', 'dotted-half', 'half_rest', 'quarter_rest', 'eighth_rest'],
                ['major', 'minor'],
            ],
            default => [ // intermediate
                ['C Major / A minor', 'G Major / E minor', 'F Major / D minor', 'D Major / B minor'],
                ['4/4', '3/4'],
                60,
                ['quarter', 'eighth', 'half'],
                ['major'],
            ],
        };

        return [
            'key_signature' => $keys[array_rand($keys)],
            'time_signature' => $timeSigs[array_rand($timeSigs)],
            'tempo' => $tempo,
            'note_values' => $noteValues,
            'mode' => $modes[array_rand($modes)],
        ];
    }
}
