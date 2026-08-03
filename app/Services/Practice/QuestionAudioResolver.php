<?php

namespace App\Services\Practice;

use App\Models\ChordPractice;
use App\Models\ScalePractice;
use App\Services\MusicTheoryService;

/**
 * Turns a serialized question into the literal pitches a client should sound.
 *
 * On the web this work happens in Blade JS, which is why the browser has to be
 * handed `chord_type` / `scale_type` / `interval` — i.e. the answer. Resolving
 * it server-side instead means the mobile client can play the question without
 * ever receiving the answer.
 *
 * The playback semantics mirror the existing Blade behaviour exactly:
 *   melodic interval / direction  -> sequential, 700ms apart
 *   harmonic interval             -> simultaneous
 *   interval comparison           -> two sequential groups
 *   interval construction         -> the given note only
 *   chord                         -> arpeggio (400ms) when arpeggiated, else block
 *   scale                         -> sequential at the tempo preset
 *   single note                   -> reference note, then the target(s) at 40bpm
 *   rhythm                        -> unpitched, driven by note_values
 *   melodic dictation             -> sequential, note_values when rhythmic
 */
class QuestionAudioResolver
{
    /** Scale tempo presets, in ms between notes (see practice-scale.blade.php). */
    private const SCALE_TEMPO_MS = ['slow' => 650, 'normal' => 450, 'fast' => 270];

    /** The single-note practice metronome: 40bpm. */
    private const SINGLE_NOTE_GAP_MS = 1500;

    private const INTERVAL_GAP_MS = 700;

    public function __construct(private readonly MusicTheoryService $music) {}

    /**
     * @return array{
     *     playback: string,
     *     notes: array<int,string>,
     *     groups: array<int,array<int,string>>|null,
     *     gap_ms: int|null,
     *     duration_ms: int,
     *     tempo_bpm: int|null,
     *     note_values: array<int,string>|null,
     *     reference_note: string|null
     * }
     */
    public function resolve(string $practiceType, array $q): array
    {
        return match ($practiceType) {
            'single-note-practice' => $this->singleNote($q),
            'melodic-interval-practice' => $this->sequentialPair($q),
            'interval-direction-practice' => $this->sequentialPair($q),
            'harmonic-interval-practice' => $this->simultaneousPair($q),
            'interval-comparison-practice' => $this->comparison($q),
            'interval-construction-practice' => $this->construction($q),
            'chord-practice' => $this->chord($q),
            'scale-practice' => $this->scale($q),
            'rhythm-practice' => $this->rhythm($q),
            'melodic-dictation' => $this->dictation($q),
            default => $this->empty(),
        };
    }

    private function singleNote(array $q): array
    {
        $octave = (int) ($q['octave'] ?? 4);
        $target = $this->pitch($q['target'] ?? null, $octave);
        $reference = isset($q['reference_note'])
            ? $this->pitch($q['reference_note'], $octave)
            : null;

        return $this->payload(
            playback: 'sequential',
            notes: array_values(array_filter([$target])),
            gapMs: self::SINGLE_NOTE_GAP_MS,
            referenceNote: $reference,
        );
    }

    private function sequentialPair(array $q): array
    {
        return $this->payload(
            playback: 'sequential',
            notes: $this->intervalNotes($q),
            gapMs: self::INTERVAL_GAP_MS,
        );
    }

    private function simultaneousPair(array $q): array
    {
        return $this->payload(
            playback: 'simultaneous',
            notes: $this->intervalNotes($q),
        );
    }

    private function comparison(array $q): array
    {
        $octave = (int) ($q['octave'] ?? 4);

        $groups = [];
        foreach (['interval_a', 'interval_b'] as $key) {
            $pair = $q[$key] ?? null;
            if (! is_string($pair) || $pair === '') {
                continue;
            }
            $groups[] = $this->spellPairFromRoot($pair, $octave);
        }

        return $this->payload(
            playback: 'groups',
            notes: array_merge(...($groups ?: [[]])),
            groups: $groups,
            gapMs: self::INTERVAL_GAP_MS,
        );
    }

    private function construction(array $q): array
    {
        // Only the given note sounds — the second note is what the user builds.
        $octave = (int) ($q['octave'] ?? 4);
        $note = $this->pitch($q['note1'] ?? null, $octave);

        return $this->payload(
            playback: 'single',
            notes: array_values(array_filter([$note])),
        );
    }

    private function chord(array $q): array
    {
        $model = new ChordPractice;
        $model->chord_type = $q['chord_type'] ?? 'Major';
        $model->root_note = $q['root_note'] ?? 'C';
        $model->octave = (int) ($q['octave'] ?? 4);
        $model->inversion = (int) ($q['inversion'] ?? 0);

        $arpeggiated = ($q['voicing'] ?? 'block') === 'arpeggiated';

        return $this->payload(
            playback: $arpeggiated ? 'arpeggio' : 'simultaneous',
            notes: $model->note_array,
            gapMs: $arpeggiated ? 400 : null,
        );
    }

    private function scale(array $q): array
    {
        $model = new ScalePractice;
        $model->scale_type = $q['scale_type'] ?? 'Major';
        $model->root_note = $q['root_note'] ?? 'C';
        $model->octave = (int) ($q['octave'] ?? 4);
        $model->direction = $q['direction'] ?? 'ascending';

        $preset = $q['tempo'] ?? 'normal';
        $gap = self::SCALE_TEMPO_MS[$preset] ?? self::SCALE_TEMPO_MS['normal'];

        return $this->payload(
            playback: 'sequential',
            notes: $model->note_array,
            gapMs: $gap,
        );
    }

    private function rhythm(array $q): array
    {
        return $this->payload(
            playback: 'rhythm',
            notes: [],
            tempoBpm: (int) ($q['tempo'] ?? 80),
            noteValues: $this->arrayField($q['note_values'] ?? null),
        );
    }

    private function dictation(array $q): array
    {
        $noteValues = $this->arrayField($q['note_values'] ?? null);

        return $this->payload(
            playback: $noteValues ? 'rhythm-melody' : 'sequential',
            notes: $this->arrayField($q['notes'] ?? null) ?? [],
            gapMs: $noteValues ? null : 600,
            tempoBpm: (int) ($q['tempo'] ?? 72),
            noteValues: $noteValues,
        );
    }

    /**
     * note1/note2 with their octaves, as stored by the generator. note2_octave
     * falls back to octave, matching MusicTheoryService::intervalForStats().
     */
    private function intervalNotes(array $q): array
    {
        $octave1 = (int) ($q['octave'] ?? 4);
        $octave2 = (int) ($q['note2_octave'] ?? $octave1);

        return array_values(array_filter([
            $this->pitch($q['note1'] ?? null, $octave1),
            $this->pitch($q['note2'] ?? null, $octave2),
        ]));
    }

    /**
     * Interval-comparison stores pairs as "C,E" note names with a shared
     * octave; the upper note wraps to the next octave when it sits below the
     * root chromatically.
     */
    private function spellPairFromRoot(string $pair, int $octave): array
    {
        [$low, $high] = array_pad(array_map('trim', explode(',', $pair)), 2, null);

        if ($low === null || $high === null) {
            return [];
        }

        $lowMidi = $this->music->midiNumber($low, $octave);
        $highMidi = $this->music->midiNumber($high, $octave);

        if ($lowMidi === null || $highMidi === null) {
            return array_values(array_filter([
                $this->pitch($low, $octave),
                $this->pitch($high, $octave),
            ]));
        }

        $highOctave = $highMidi <= $lowMidi ? $octave + 1 : $octave;

        return [$low.$octave, $high.$highOctave];
    }

    private function pitch(?string $note, int $octave): ?string
    {
        if ($note === null || $note === '') {
            return null;
        }

        // Already an absolute pitch such as "C4".
        if (preg_match('/\d$/', $note)) {
            return $note;
        }

        return $note.$octave;
    }

    /** Session-serialized array fields arrive either as arrays or JSON strings. */
    private function arrayField(mixed $value): ?array
    {
        if (is_array($value)) {
            return array_values($value);
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return array_values($decoded);
            }

            return array_values(array_map('trim', explode(',', $value)));
        }

        return null;
    }

    private function payload(
        string $playback,
        array $notes,
        ?array $groups = null,
        ?int $gapMs = null,
        ?int $tempoBpm = null,
        ?array $noteValues = null,
        ?string $referenceNote = null,
    ): array {
        return [
            'playback' => $playback,
            'notes' => $notes,
            'groups' => $groups,
            'gap_ms' => $gapMs,
            'tempo_bpm' => $tempoBpm,
            'note_values' => $noteValues,
            'reference_note' => $referenceNote,
        ];
    }

    private function empty(): array
    {
        return $this->payload(playback: 'none', notes: []);
    }
}
