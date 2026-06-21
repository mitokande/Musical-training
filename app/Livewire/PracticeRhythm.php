<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPracticeData;
use App\Models\LearningPathExercise;
use App\Models\Practice;
use App\Models\RhythmPractice;
use App\Models\UserPractice;
use App\Services\LearningPathQuestionGenerator;
use Livewire\Component;

class PracticeRhythm extends Component
{
    use HandlesPracticeData;

    public $currentPracticeIndex = 0;

    public $settings = [];

    public $replayLimit = null;

    public $feedbackMode = 'immediate';

    public $timeLimitSeconds = 0;

    public $metronome = true;

    public $rhythmMode = 'dictation';

    public function mount($practices)
    {
        $settings = session('exercise_settings', []);
        session()->forget('exercise_settings');

        if (! empty($settings)) {
            $this->settings = $settings;
            $this->replayLimit = $settings['replay_limit'] ?? null;
            $this->feedbackMode = $settings['feedback_mode'] ?? 'immediate';
            $this->timeLimitSeconds = (int) ($settings['time_limit_seconds'] ?? 0);
            $this->metronome = isset($settings['metronome']) ? (bool) $settings['metronome'] : true;
            $this->rhythmMode = $settings['rhythm_mode'] ?? 'dictation';

            $count = (int) ($settings['question_count'] ?? 10);
            $timeSig = $settings['time_signature'] ?? '4/4';
            $tempo = (int) ($settings['tempo'] ?? 80);

            $generator = app(LearningPathQuestionGenerator::class);
            $includeRests = ! empty($settings['rhythm_rests']);

            if ($this->rhythmMode === 'build') {
                $rhythmDifficulty = $this->mapRhythmDifficulty($settings['difficulty'] ?? 'intermediate');
                $userNoteVals = ! empty($settings['note_values']) ? $settings['note_values'] : null;

                // Builder palette: convert triplet-eighth → 'triplet' button (builder JS expands
                // 'triplet' to 3× triplet-eighth); drop triplet-quarter (no builder button for it).
                if ($userNoteVals) {
                    $palette = array_values(array_unique(array_filter(
                        array_map(fn ($v) => $v === 'triplet-eighth' ? 'triplet' : ($v === 'triplet-quarter' ? null : $v), $userNoteVals),
                        fn ($v) => $v !== null
                    )));
                    // Sort by canonical display order: notes long→short, triplet, rests long→short.
                    $order = array_flip(['whole', 'half', 'dotted-half', 'quarter', 'dotted-quarter', 'eighth', 'dotted-eighth', 'sixteenth', 'triplet', 'whole_rest', 'half_rest', 'quarter_rest', 'eighth_rest']);
                    usort($palette, fn ($a, $b) => ($order[$a] ?? 99) <=> ($order[$b] ?? 99));
                } else {
                    $palette = $this->rhythmPaletteForDifficulty($rhythmDifficulty);
                }

                // Generator filter: strip rests (no rest cells in generator) and triplet-quarter.
                // Rests are injected post-assembly via the include_rests flag instead.
                $generatorAllowed = $userNoteVals
                    ? array_values(array_filter($userNoteVals, fn ($v) => ! str_contains($v, '_rest') && $v !== 'triplet-quarter'))
                    : null;

                $exercise = new LearningPathExercise(['config_json' => [
                    'practice_type' => 'rhythm-practice',
                    'time_signatures' => [$timeSig],
                    'tempo_range' => [$tempo, $tempo],
                    'rhythm_difficulty' => $rhythmDifficulty,
                    'allowed_note_values' => ! empty($generatorAllowed) ? $generatorAllowed : null,
                    'bars' => 1,
                    'include_rests' => $includeRests,
                ]]);
                $generated = $generator->generate($exercise, $count)->values()
                    ->map(function ($q, $i) use ($palette) {
                        $q->id = $i + 1;
                        $q->allowed_values = $palette;

                        return $q;
                    });
            } else {
                $noteVals = ! empty($settings['note_values']) ? $settings['note_values'] : ['quarter', 'half'];
                // Strip rests and triplet-quarter — not cell tokens in the generator.
                // Rests are injected post-assembly via the include_rests flag instead.
                $generatorNoteVals = array_values(array_filter($noteVals, fn ($v) => ! str_contains($v, '_rest') && $v !== 'triplet-quarter'));
                $exercise = new LearningPathExercise(['config_json' => [
                    'practice_type' => 'rhythm-practice',
                    'time_signatures' => [$timeSig],
                    'allowed_note_values' => ! empty($generatorNoteVals) ? $generatorNoteVals : null,
                    'tempo_range' => [$tempo, $tempo],
                    'bars' => 1,
                    'include_rests' => $includeRests,
                ]]);
                $generated = $generator->generate($exercise, $count)->values()
                    ->map(function ($q, $i) {
                        $q->id = $i + 1;

                        return $q;
                    });
            }

            session(['exercise_practice_session' => [
                'practice_type' => 'rhythm-practice',
                'question_count' => $generated->count(),
                'questions' => $generator->serializeForSession($generated),
            ]]);

            $this->practiceDataArray = $this->serializePractices($generated->all());
        } else {
            $this->practiceDataArray = $this->serializePractices($practices);
        }
    }

    public function render()
    {
        $currentPractice = $this->buildModelFromData(RhythmPractice::class, $this->getCurrentPracticeData());

        return view('livewire.practice-rhythm', [
            'practices' => $this->practiceDataArray,
            'currentPractice' => $currentPractice,
            'currentPracticeIndex' => $this->currentPracticeIndex,
            'metronome' => $this->metronome,
            'rhythmMode' => $this->rhythmMode,
        ]);
    }

    public function getNextPractice()
    {
        $this->currentPracticeIndex++;
        $this->dispatch('practice-updated');
    }

    public function answerPractice($answer)
    {
        $practiceId = Practice::where('slug', 'rhythm-practice')->value('id');
        $userPractice = UserPractice::firstOrCreate(
            ['user_id' => auth()->id(), 'practice_id' => $practiceId],
            ['total_questions' => 0, 'correct_answers' => 0, 'incorrect_answers' => 0, 'score' => 0]
        );

        $data = $this->getCurrentPracticeData();
        $noteValues = $data['note_values'] ?? [];
        if (is_string($noteValues)) {
            $noteValues = json_decode($noteValues, true) ?? [];
        }
        $correctSequence = implode(',', $noteValues);
        $isCorrect = trim($answer) === trim($correctSequence);

        $userPractice->total_questions++;
        if ($isCorrect) {
            $userPractice->correct_answers++;
        } else {
            $userPractice->incorrect_answers++;
        }
        $userPractice->score = $userPractice->total_questions > 0
            ? ($userPractice->correct_answers / $userPractice->total_questions) * 100
            : 0;
        $userPractice->save();

        return $isCorrect;
    }

    /**
     * Map the exercise-setup difficulty (beginner/intermediate/advanced/adaptive) to the
     * rhythm cell-pool difficulty (easy/medium/hard) used by the question generator.
     */
    private function mapRhythmDifficulty(string $difficulty): string
    {
        return match ($difficulty) {
            'beginner' => 'easy',
            'advanced' => 'hard',
            default => 'medium', // intermediate + adaptive
        };
    }

    /**
     * Note-button palette offered by the builder at a given difficulty (mirrors
     * AIController::rhythmPaletteForDifficulty). Rests are always available; hard adds triplets.
     */
    private function rhythmPaletteForDifficulty(string $difficulty): array
    {
        $rests = ['whole_rest', 'half_rest', 'quarter_rest', 'eighth_rest'];

        return match ($difficulty) {
            'easy' => array_merge(['whole', 'half', 'dotted-half', 'quarter', 'eighth'], $rests),
            'hard' => array_merge([
                'whole', 'half', 'dotted-half', 'quarter', 'dotted-quarter',
                'eighth', 'dotted-eighth', 'sixteenth', 'triplet',
            ], $rests),
            // medium + adaptive
            default => array_merge([
                'whole', 'half', 'dotted-half', 'quarter', 'dotted-quarter',
                'eighth', 'dotted-eighth', 'sixteenth',
            ], $rests),
        };
    }
}
