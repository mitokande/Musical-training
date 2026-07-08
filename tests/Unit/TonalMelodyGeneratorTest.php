<?php

namespace Tests\Unit;

use App\Services\TonalMelodyGenerator;
use PHPUnit\Framework\TestCase;

class TonalMelodyGeneratorTest extends TestCase
{
    private TonalMelodyGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new TonalMelodyGenerator;
    }

    /** Re-derive the interval list independently of the service. */
    private function intervals(array $melody): array
    {
        $midis = array_map(fn ($n) => $this->generator->noteToMidi($n), $melody);
        $out = [];
        for ($i = 1; $i < count($midis); $i++) {
            $out[] = $midis[$i] - $midis[$i - 1];
        }

        return $out;
    }

    private function assertMelodyFollowsRules(array $melody, string $difficulty, string $label): void
    {
        $deltas = $this->intervals($melody);
        $abs = array_map('abs', $deltas);
        $total = count($abs);
        $this->assertGreaterThan(0, $total, $label);

        $maxLeap = ['beginner' => 2, 'intermediate' => 7, 'advanced' => 11][$difficulty];
        $maxLeapCount = ['beginner' => 0, 'intermediate' => 2, 'advanced' => 3][$difficulty];
        $maxRange = ['beginner' => 9, 'intermediate' => 12, 'advanced' => 16][$difficulty];

        // ≥70% steps/thirds (beginner is steps-only via the leap cap below)
        $conjunct = count(array_filter($abs, fn ($d) => $d <= 4));
        $this->assertGreaterThanOrEqual(0.70, $conjunct / $total, "$label: too much disjunct motion");

        // 5ths must stay rare at intermediate
        if ($difficulty === 'intermediate') {
            $fifths = count(array_filter($abs, fn ($d) => $d === 7));
            $this->assertLessThanOrEqual(1, $fifths, "$label: more than one 5th leap");
        }

        $leaps = 0;
        foreach ($abs as $i => $d) {
            $this->assertLessThanOrEqual($maxLeap, $d, "$label: leap of $d semitones exceeds level cap");
            $this->assertNotSame(6, $d, "$label: tritone leap (aug/dim needs explicit config)");
            if ($d >= 5) {
                $leaps++;
                if ($i > 0) {
                    $this->assertLessThan(5, $abs[$i - 1], "$label: two consecutive leaps");
                }
                if ($i < $total - 1) {
                    $contrary = ($deltas[$i + 1] <=> 0) === -($deltas[$i] <=> 0);
                    $ok = $difficulty === 'beginner'
                        ? ($contrary && $abs[$i + 1] <= 4)
                        : ($contrary || $abs[$i + 1] <= 2);
                    $this->assertTrue($ok, "$label: leap not resolved by contrary motion");
                }
            }
        }
        $this->assertLessThanOrEqual($maxLeapCount, $leaps, "$label: too many leaps");

        $midis = array_map(fn ($n) => $this->generator->noteToMidi($n), $melody);
        $this->assertLessThanOrEqual($maxRange, max($midis) - min($midis), "$label: range exceeds level cap");
    }

    public function test_melodies_follow_level_rules_across_keys_and_difficulties(): void
    {
        foreach (['C', 'G', 'F', 'Bb', 'A'] as $key) {
            foreach (['major', 'minor'] as $mode) {
                foreach (['beginner', 'intermediate', 'advanced'] as $difficulty) {
                    $context = $this->generator->contextForKey($key, $mode, 'treble');
                    for ($i = 0; $i < 20; $i++) {
                        $melody = $this->generator->generateMelody(8, $context, $difficulty);
                        $label = "$key $mode $difficulty #$i (".implode(',', $melody).')';
                        $this->assertCount(8, $melody, $label);
                        $this->assertMelodyFollowsRules($melody, $difficulty, $label);
                    }
                }
            }
        }
    }

    public function test_melodies_are_diatonic_to_the_key(): void
    {
        $context = $this->generator->contextForKey('D', 'major', 'treble');
        $allowed = ['D', 'E', 'F#', 'G', 'A', 'B', 'C#'];

        for ($i = 0; $i < 20; $i++) {
            $melody = $this->generator->generateMelody(8, $context, 'intermediate');
            foreach ($melody as $note) {
                $name = preg_replace('/\d+$/', '', $note);
                $this->assertContains($name, $allowed, "Chromatic note $note in D major melody");
            }
        }
    }

    public function test_beginner_melodies_end_on_tonic(): void
    {
        foreach ([['C', 'major', 'C'], ['G', 'major', 'G'], ['C', 'minor', 'A'], ['F', 'minor', 'D']] as [$key, $mode, $tonic]) {
            $context = $this->generator->contextForKey($key, $mode, 'treble');
            for ($i = 0; $i < 10; $i++) {
                $melody = $this->generator->generateMelody(8, $context, 'beginner');
                $last = preg_replace('/\d+$/', '', end($melody));
                $this->assertSame($tonic, $last, "$key $mode beginner melody must end on tonic, got $last");
            }
        }
    }

    public function test_melodies_start_on_a_tonic_triad_degree(): void
    {
        $context = $this->generator->contextForKey('C', 'major', 'treble');
        foreach (range(1, 20) as $i) {
            $melody = $this->generator->generateMelody(8, $context, 'beginner');
            $first = preg_replace('/\d+$/', '', $melody[0]);
            $this->assertContains($first, ['C', 'E', 'G'], "Melody starts on $first, not a C-triad degree");
        }
    }

    public function test_long_melodies_in_bass_clef_stay_valid(): void
    {
        $context = $this->generator->contextForKey('Eb', 'major', 'bass');
        for ($i = 0; $i < 10; $i++) {
            $melody = $this->generator->generateMelody(16, $context, 'beginner');
            $this->assertCount(16, $melody);
            $this->assertMelodyFollowsRules($melody, 'beginner', 'Eb bass 16-note #'.$i);
        }
    }

    public function test_context_from_custom_pool_anchors_on_tonic(): void
    {
        $pool = ['C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'B4', 'C5'];
        $context = $this->generator->contextFromPool($pool, 'C');

        $this->assertSame($pool, $context['pool']);
        $this->assertContains('C4', $context['triad']);
        $this->assertContains('E4', $context['triad']);
        $this->assertContains('G4', $context['triad']);
        $this->assertNotEmpty($context['tonics']);
        $this->assertNotEmpty($context['dominants']);

        for ($i = 0; $i < 10; $i++) {
            $melody = $this->generator->generateMelody(6, $context, 'beginner');
            $this->assertCount(6, $melody);
            $this->assertMelodyFollowsRules($melody, 'beginner', 'custom pool #'.$i);
        }
    }

    // ── applyAccidentals ─────────────────────────────────────────────────────

    /** Strip the octave digit to get just the note name (e.g. 'G#4' → 'G#'). */
    private function nameOnly(string $noteWithOctave): string
    {
        return preg_replace('/\d+$/', '', $noteWithOctave);
    }

    /** Return the set of distinct non-diatonic note names in a melody. */
    private function extraAccidentals(array $melody, array $diatonicNames): array
    {
        $extras = [];
        foreach ($melody as $note) {
            $name = $this->nameOnly($note);
            if (! in_array($name, $diatonicNames, true)) {
                $extras[] = $name;
            }
        }

        return array_unique($extras);
    }

    public function test_major_beginner_stays_fully_diatonic(): void
    {
        $scale = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];
        $context = $this->generator->contextForKey('C', 'major', 'treble');

        for ($i = 0; $i < 30; $i++) {
            $melody = $this->generator->generateMelody(8, $context, 'beginner');
            $result = $this->generator->applyAccidentals($melody, 'C', 'major', 'beginner');
            $extras = $this->extraAccidentals($result, $scale);
            $this->assertEmpty($extras, 'Beginner C major must have no extra accidentals, got: '.implode(',', $extras));
        }
    }

    public function test_minor_beginner_includes_leading_tone_sometimes_but_not_always(): void
    {
        // A minor: natural 7th = G, leading tone = G#
        $context = $this->generator->contextForKey('C', 'minor', 'treble');
        $naturalMinorNames = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

        $withLeadingTone = 0;
        // Large sample: the leading tone is applied probabilistically, so a
        // small sample can legitimately contain zero occurrences and flake.
        $total = 300;

        for ($i = 0; $i < $total; $i++) {
            $melody = $this->generator->generateMelody(8, $context, 'beginner');
            $result = $this->generator->applyAccidentals($melody, 'C', 'minor', 'beginner');
            if (in_array('G#', array_map([$this, 'nameOnly'], $result), true)) {
                $withLeadingTone++;
            }
        }

        $this->assertGreaterThan(0, $withLeadingTone,
            'Beginner A minor: leading tone should appear in some questions (got 0 out of '.$total.')');
        $this->assertLessThan($total, $withLeadingTone,
            'Beginner A minor: leading tone should not appear in every question');
    }

    public function test_minor_beginner_never_uses_raised_sixth(): void
    {
        $context = $this->generator->contextForKey('C', 'minor', 'treble');

        for ($i = 0; $i < 40; $i++) {
            $melody = $this->generator->generateMelody(8, $context, 'beginner');
            $result = $this->generator->applyAccidentals($melody, 'C', 'minor', 'beginner');
            $names = array_map([$this, 'nameOnly'], $result);
            $this->assertNotContains('F#', $names,
                'Beginner A minor: F# (raised 6th) must not appear. Melody: '.implode(',', $result));
        }
    }

    public function test_minor_beginner_avoids_aug_second_before_leading_tone(): void
    {
        // Craft a melody where the natural 7th is preceded by the natural 6th.
        // The leading tone must NOT be applied here (aug 2nd: F → G# in A minor).
        $melody = ['A4', 'F4', 'G4', 'A4']; // F → G → A; G preceded by F
        $result = $this->generator->applyAccidentals($melody, 'C', 'minor', 'beginner');

        // Force 35% chance path by running many times; G# must never appear when
        // preceded by F even across random seeds.
        $raised = false;
        for ($i = 0; $i < 50; $i++) {
            $r = $this->generator->applyAccidentals($melody, 'C', 'minor', 'beginner');
            if (in_array('G#4', $r, true)) {
                $raised = true;
                break;
            }
        }

        $this->assertFalse($raised,
            'Beginner A minor: G# must not appear when preceded by F (aug 2nd). '.
            'Melody: '.implode(',', $melody));
    }

    public function test_minor_intermediate_raises_7th_before_tonic(): void
    {
        // G4 → A4 in A minor context must become G#4 → A4 at intermediate.
        $melody = ['A4', 'B4', 'G4', 'A4'];

        $raised = false;
        for ($i = 0; $i < 50; $i++) {
            $r = $this->generator->applyAccidentals($melody, 'C', 'minor', 'intermediate');
            if (in_array('G#4', $r, true)) {
                $raised = true;
                break;
            }
        }

        $this->assertTrue($raised,
            'Intermediate A minor: G4→A4 must be raised to G#4→A4 at least once in 50 tries. '.
            'Melody: '.implode(',', $melody));
    }

    public function test_minor_intermediate_melodic_minor_ascending(): void
    {
        // F4→G4→A4 ascending in A minor → should sometimes become F#4→G#4→A4.
        $melody = ['E4', 'F4', 'G4', 'A4'];

        $foundMelodicMinor = false;
        for ($i = 0; $i < 80; $i++) {
            $r = $this->generator->applyAccidentals($melody, 'C', 'minor', 'intermediate');
            $names = array_map([$this, 'nameOnly'], $r);
            if (in_array('F#', $names, true) && in_array('G#', $names, true)) {
                $foundMelodicMinor = true;
                break;
            }
        }

        $this->assertTrue($foundMelodicMinor,
            'Intermediate A minor: F→G→A ascending should sometimes produce F#→G#→A (melodic minor). '.
            'Melody: '.implode(',', $melody));
    }

    public function test_minor_intermediate_does_not_raise_descending_sixth_seventh(): void
    {
        // Descending A → G → F: the 6th (F) must not be raised when descending.
        $melody = ['A4', 'G4', 'F4', 'E4', 'A4'];
        $diatonic = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

        for ($i = 0; $i < 50; $i++) {
            $r = $this->generator->applyAccidentals($melody, 'C', 'minor', 'intermediate');
            $extras = $this->extraAccidentals($r, $diatonic);
            // F# must not appear in this descending passage (only G# is allowed: leading-tone at end)
            $this->assertNotContains('F#', $extras,
                'Intermediate A minor descending A-G-F: F# must not appear. Result: '.implode(',', $r));
        }
    }

    public function test_apply_accidentals_never_introduces_tritone(): void
    {
        foreach (['C', 'G', 'F', 'Bb', 'A'] as $key) {
            foreach (['major', 'minor'] as $mode) {
                foreach (['beginner', 'intermediate', 'advanced'] as $difficulty) {
                    $context = $this->generator->contextForKey($key, $mode, 'treble');
                    for ($i = 0; $i < 20; $i++) {
                        $melody = $this->generator->generateMelody(8, $context, $difficulty);
                        $result = $this->generator->applyAccidentals($melody, $key, $mode, $difficulty);
                        $intervals = $this->intervals($result);
                        foreach ($intervals as $d) {
                            $this->assertNotSame(6, abs($d),
                                "$key $mode $difficulty: tritone (6 semitones) found in ".implode(',', $result));
                        }
                    }
                }
            }
        }
    }

    public function test_minor_advanced_allows_up_to_two_distinct_accidentals(): void
    {
        $context = $this->generator->contextForKey('C', 'minor', 'treble');
        $diatonic = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

        for ($i = 0; $i < 40; $i++) {
            $melody = $this->generator->generateMelody(8, $context, 'advanced');
            $result = $this->generator->applyAccidentals($melody, 'C', 'minor', 'advanced');
            $extras = $this->extraAccidentals($result, $diatonic);
            $this->assertLessThanOrEqual(2, count($extras),
                'Advanced A minor: at most 2 distinct additional accidentals allowed. '.
                'Got: '.implode(',', $extras).' in melody '.implode(',', $result));
        }
    }

    public function test_major_intermediate_allows_at_most_one_chromatic_approach(): void
    {
        $context = $this->generator->contextForKey('C', 'major', 'treble');
        $scale = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];

        for ($i = 0; $i < 40; $i++) {
            $melody = $this->generator->generateMelody(8, $context, 'intermediate');
            $result = $this->generator->applyAccidentals($melody, 'C', 'major', 'intermediate');
            $extras = $this->extraAccidentals($result, $scale);
            $this->assertLessThanOrEqual(1, count($extras),
                'Intermediate C major: at most 1 distinct additional accidental allowed. '.
                'Got: '.implode(',', $extras).' in melody '.implode(',', $result));
        }
    }

    public function test_major_advanced_allows_at_most_two_chromatic_approaches(): void
    {
        $context = $this->generator->contextForKey('C', 'major', 'treble');
        $scale = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];

        for ($i = 0; $i < 40; $i++) {
            $melody = $this->generator->generateMelody(8, $context, 'advanced');
            $result = $this->generator->applyAccidentals($melody, 'C', 'major', 'advanced');
            $extras = $this->extraAccidentals($result, $scale);
            $this->assertLessThanOrEqual(2, count($extras),
                'Advanced C major: at most 2 distinct additional accidentals allowed. '.
                'Got: '.implode(',', $extras).' in melody '.implode(',', $result));
        }
    }

    public function test_leading_tone_resolves_ascending_to_tonic(): void
    {
        // G#4 must always be followed by A4 (ascending), never be raised going down.
        $context = $this->generator->contextForKey('C', 'minor', 'treble');

        for ($i = 0; $i < 40; $i++) {
            $melody = $this->generator->generateMelody(8, $context, 'intermediate');
            $result = $this->generator->applyAccidentals($melody, 'C', 'minor', 'intermediate');

            for ($j = 0; $j < count($result) - 1; $j++) {
                if ($this->nameOnly($result[$j]) === 'G#') {
                    $nextMidi = $this->generator->noteToMidi($result[$j + 1]);
                    $currMidi = $this->generator->noteToMidi($result[$j]);
                    $this->assertGreaterThan($currMidi, $nextMidi,
                        'G# (leading tone) must be followed by a higher note (ascending resolution). '.
                        'Melody: '.implode(',', $result));
                }
            }
        }
    }

    public function test_validate_melody_rejects_rule_violations(): void
    {
        // Random disconnected line (the old generator's failure mode)
        $this->assertFalse($this->generator->validateMelody(['C4', 'B4', 'D4', 'A4', 'E4', 'G5'], 'beginner'));
        // Two consecutive leaps
        $this->assertFalse($this->generator->validateMelody(['C4', 'F4', 'C5', 'B4', 'A4', 'G4', 'F4', 'E4'], 'intermediate'));
        // Octave leap at beginner level
        $this->assertFalse($this->generator->validateMelody(['C4', 'C5', 'B4', 'A4', 'G4', 'F4', 'E4', 'C4'], 'beginner'));
        // Octave leaps need explicit config even at advanced level
        $this->assertFalse($this->generator->validateMelody(['C4', 'C5', 'B4', 'A4', 'G4', 'F4', 'E4', 'D4'], 'advanced'));
        // Tritone leaps need explicit config at every level
        $this->assertFalse($this->generator->validateMelody(['C4', 'D4', 'E4', 'F4', 'B4', 'A4', 'G4', 'F4'], 'advanced'));
        // Range beyond a 6th at beginner level
        $this->assertFalse($this->generator->validateMelody(['C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'B4', 'C5'], 'beginner'));
        // Beginner is steps-only: even a single 3rd fails…
        $this->assertFalse($this->generator->validateMelody(['C4', 'D4', 'E4', 'G4', 'F4', 'E4', 'D4', 'C4'], 'beginner'));
        // …but the same line is fine at intermediate
        $this->assertTrue($this->generator->validateMelody(['C4', 'D4', 'E4', 'G4', 'F4', 'E4', 'D4', 'C4'], 'intermediate'));
        // Mechanical zigzag between two notes reads as random, not melodic
        $this->assertFalse($this->generator->validateMelody(['C4', 'D4', 'C4', 'D4', 'C4', 'D4', 'C4', 'D4'], 'beginner'));
        // A stepwise singable wave passes beginner
        $this->assertTrue($this->generator->validateMelody(['C4', 'D4', 'E4', 'F4', 'G4', 'F4', 'E4', 'D4'], 'beginner'));
    }
}
