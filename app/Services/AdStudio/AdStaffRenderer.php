<?php

namespace App\Services\AdStudio;

/**
 * Draws a note on the round frames' treble staff.
 *
 * The staff is authored in a fixed SVG user space shared by every round so it
 * never jumps size between cuts:
 *
 *   viewBox "0 -70 900 300"; the five staff lines sit at y = 20, 60, 100, 140,
 *   180 (F5, D5, B4, G4, E4 top-to-bottom). One diatonic step is 20 units, so a
 *   space is a half-step of that grid. y = -20 is the first ledger line above
 *   the staff (A5); y = 200 is the first below (C4).
 *
 * This is deliberately a tiny, self-contained renderer rather than a reuse of
 * MusicTheoryService: the app's theory service answers musical questions, while
 * this answers a typographic one — where does the glyph go in THIS artboard.
 */
class AdStaffRenderer
{
    /** Diatonic letters in ascending order within an octave. */
    private const LETTERS = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];

    /** E4 is the bottom staff line at y=180; everything else is derived from it. */
    private const E4_INDEX = 30;

    private const E4_Y = 180;

    private const STEP = 20;

    /** Below this y the note sits under the middle line, so its stem runs up. */
    private const MIDDLE_Y = 100;

    /**
     * Vertical position of a spelled note (e.g. "A4", "D#5", "Bb3").
     *
     * The accidental never changes the position — that is the entire point of
     * staff notation, and it is why D#5 and D5 share a line.
     */
    public function y(string $note): int
    {
        [$letter, , $octave] = $this->parse($note);

        $index = ($octave * 7) + array_search($letter, self::LETTERS, true);

        return self::E4_Y - self::STEP * ($index - self::E4_INDEX);
    }

    /**
     * The full <g> for one note: ledger lines if it sits off the staff, the
     * accidental if it has one, a stem in the correct direction, and the head.
     *
     * @param  string  $id  Element id the frame timeline targets.
     * @param  int  $cx  Horizontal centre of the note head in user space.
     */
    public function note(string $id, string $note, int $cx): string
    {
        [, $accidental] = $this->parse($note);
        $y = $this->y($note);

        $parts = [];

        // A ledger has to overhang the head clearly on both sides or it reads as
        // two stubs poking out from behind it. The head is 54 units wide (rx 27),
        // so ±46 gives it roughly a third of a head of overhang each side —
        // which is what engraved notation does.
        foreach ($this->ledgerLines($y) as $ledgerY) {
            $parts[] = sprintf(
                '<line class="ledger" x1="%d" y1="%d" x2="%d" y2="%d" />',
                $cx - 46, $ledgerY, $cx + 46, $ledgerY
            );
        }

        if ($accidental !== '') {
            $parts[] = $this->accidental($accidental, $cx - 58, $y);
        }

        // Stem up for notes below the middle line, down for notes on or above
        // it — the standard rule, and it keeps the stem inside the artboard.
        if ($y > self::MIDDLE_Y) {
            $stemX = $cx + 26;
            $parts[] = sprintf('<line class="notestem" x1="%d" y1="%d" x2="%d" y2="%d" />', $stemX, $y, $stemX, $y - 96);
        } else {
            $stemX = $cx - 26;
            $parts[] = sprintf('<line class="notestem" x1="%d" y1="%d" x2="%d" y2="%d" />', $stemX, $y, $stemX, $y + 96);
        }

        $parts[] = sprintf(
            '<ellipse class="notehead" cx="%d" cy="%d" rx="27" ry="20" transform="rotate(-18 %d %d)" />',
            $cx, $y, $cx, $y
        );

        $indent = str_repeat(' ', 10);

        return $indent.'<g class="noteg" id="'.$id.'">'."\n"
            .collect($parts)->map(fn ($p) => $indent.'  '.$p)->implode("\n")."\n"
            .$indent.'</g>';
    }

    /**
     * Ledger lines between the staff and a note that sits outside it.
     *
     * Line positions continue the 40-unit line grid in both directions; spaces
     * (A5 at y=-20 is a line, G5 at y=0 is a space) get no ledger of their own.
     *
     * @return list<int>
     */
    public function ledgerLines(int $y): array
    {
        $lines = [];

        // Above the staff: -20, -60, -100 …
        for ($ly = -20; $ly >= $y; $ly -= 40) {
            $lines[] = $ly;
        }

        // Below the staff: 220, 260 … (200 is the C4 ledger, 40 units below E4).
        for ($ly = 220; $ly <= $y; $ly += 40) {
            $lines[] = $ly;
        }

        return $lines;
    }

    /**
     * Accidentals are DRAWN, not set as ♯ / ♭ characters: the IBM Plex Mono
     * latin subset shipped in assets/fonts carries neither glyph, and a missing
     * glyph renders as a tofu box in the final MP4.
     */
    private function accidental(string $kind, int $cx, int $y): string
    {
        if ($kind === '#') {
            return implode('', [
                sprintf('<line class="sharp" x1="%d" y1="%d" x2="%d" y2="%d" />', $cx - 10, $y - 30, $cx - 10, $y + 26),
                sprintf('<line class="sharp" x1="%d" y1="%d" x2="%d" y2="%d" />', $cx + 12, $y - 36, $cx + 12, $y + 20),
                sprintf('<line class="sharp" x1="%d" y1="%d" x2="%d" y2="%d" />', $cx - 22, $y - 6, $cx + 24, $y - 14),
                sprintf('<line class="sharp" x1="%d" y1="%d" x2="%d" y2="%d" />', $cx - 22, $y + 14, $cx + 24, $y + 6),
            ]);
        }

        // Flat: a stem with a bowl hanging off its lower half.
        return implode('', [
            sprintf('<line class="flat" x1="%d" y1="%d" x2="%d" y2="%d" />', $cx - 8, $y - 44, $cx - 8, $y + 18),
            sprintf('<path class="flat" d="M %d %d Q %d %d %d %d Q %d %d %d %d" />',
                $cx - 8, $y + 18,
                $cx + 22, $y - 6,
                $cx + 14, $y + 2,
                $cx + 2, $y + 10,
                $cx - 8, $y + 4),
        ]);
    }

    /** @return array{0: string, 1: string, 2: int} letter, accidental, octave */
    private function parse(string $note): array
    {
        if (! preg_match('/^([A-G])([#b]?)(-?\d)$/', trim($note), $m)) {
            throw new AdStudioException("Cannot place the note [$note] on the staff — expected a spelling like A4, D#5 or Bb3.");
        }

        return [$m[1], $m[2], (int) $m[3]];
    }

    /**
     * Screen-reader label for the whole staff. The MP4 does not carry it, but
     * `hyperframes check` gates on SVG role/label and the preview is a web page.
     */
    public function aria(string $lower, string $upper): string
    {
        return sprintf('Two notes on a treble staff: %s and %s', $this->spoken($lower), $this->spoken($upper));
    }

    private function spoken(string $note): string
    {
        [$letter, $accidental, $octave] = $this->parse($note);

        return $letter.match ($accidental) {
            '#' => ' sharp ',
            'b' => ' flat ',
            default => ' ',
        }.$octave;
    }
}
