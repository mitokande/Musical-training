<?php

namespace App\Console\Commands;

use App\Services\MusicTheoryService;
use Illuminate\Console\Command;

class RepairQuestions extends Command
{
    protected $signature = 'exercises:repair-questions
                            {--type= : Limit to one practice type slug}
                            {--dry-run : Show what would be changed without writing to the DB}';

    protected $description = 'Repair interval practice questions where direction or answer mismatches the actual note data';

    private const PRACTICE_TYPES = [
        'melodic-interval-practice'      => \App\Models\MelodicIntervalPractice::class,
        'harmonic-interval-practice'     => \App\Models\HarmonicIntervalPractice::class,
        'interval-direction-practice'    => \App\Models\IntervalDirectionPractice::class,
        'interval-construction-practice' => \App\Models\IntervalConstructionPractice::class,
        'interval-comparison-practice'   => \App\Models\IntervalComparisonPractice::class,
    ];

    public function handle(MusicTheoryService $music): int
    {
        $filterType = $this->option('type');
        $dryRun     = $this->option('dry-run');

        $types = $filterType
            ? (isset(self::PRACTICE_TYPES[$filterType]) ? [$filterType => self::PRACTICE_TYPES[$filterType]] : [])
            : self::PRACTICE_TYPES;

        if (empty($types)) {
            $this->error("Unknown practice type: {$filterType}");
            return Command::FAILURE;
        }

        if ($dryRun) {
            $this->warn('[DRY RUN] No changes will be written to the database.');
        }

        $repaired     = 0;
        $skipped      = 0;
        $markedReview = 0;

        foreach ($types as $type => $modelClass) {
            $questions = $modelClass::all();

            foreach ($questions as $q) {
                $check = $music->validateQuestionConsistency($q->toArray(), $type);

                if ($check['status'] === 'valid') {
                    continue;
                }

                if ($check['status'] === 'needs_review') {
                    // Mark ambiguous questions but do not change their data
                    $this->line("  [needs_review] {$type} ID={$q->id} issues=" . implode(',', $check['issues']));
                    if (!$dryRun) {
                        $q->needs_review      = true;
                        $q->validation_status = 'needs_review';
                        $q->save();
                    }
                    $markedReview++;
                    continue;
                }

                // Attempt repair
                $fixes = $this->buildFixes($music, $q->toArray(), $type, $check['issues']);

                if (empty($fixes)) {
                    $this->warn("  [skip] {$type} ID={$q->id} — could not determine fix for issues: " . implode(',', $check['issues']));
                    $skipped++;
                    continue;
                }

                $this->line("  [repair] {$type} ID={$q->id} fixes=" . json_encode($fixes));

                if (!$dryRun) {
                    // Back up original data before modifying
                    if (empty($q->backup_data)) {
                        $q->backup_data = $q->toArray();
                    }

                    foreach ($fixes as $field => $value) {
                        $q->{$field} = $value;
                    }
                    $q->needs_review      = false;
                    $q->validation_status = 'valid';
                    $q->save();
                }

                $repaired++;
            }
        }

        $this->newLine();
        $this->info("Repaired: {$repaired}  |  Marked needs_review: {$markedReview}  |  Skipped (ambiguous): {$skipped}");

        if ($dryRun) {
            $this->warn('[DRY RUN] No changes were written.');
        }

        return Command::SUCCESS;
    }

    private function buildFixes(MusicTheoryService $music, array $q, string $type, array $issues): array
    {
        $fixes = [];
        $note1  = $q['note1'] ?? null;
        $octave = isset($q['octave']) ? (int) $q['octave'] : null;
        $note2  = $q['note2'] ?? null;

        if (!$note1 || !$octave || !$note2) {
            return [];
        }

        switch ($type) {
            case 'interval-direction-practice':
                // Fix: recalculate note2_octave (default to same octave for existing records)
                // and re-derive direction from actual pitches.
                $note2Octave = isset($q['note2_octave']) ? (int) $q['note2_octave'] : $octave;
                $direction   = $music->getDirection($note1, $octave, $note2, $note2Octave);

                if (in_array('direction_mismatch', $issues)) {
                    $fixes['direction']    = $direction;
                    $fixes['note2_octave'] = $note2Octave;
                }
                if (in_array('octave_mismatch', $issues) || !isset($q['note2_octave'])) {
                    $fixes['note2_octave'] = $note2Octave;
                }
                $fixes['validation_status'] = 'valid';
                break;

            case 'melodic-interval-practice':
            case 'harmonic-interval-practice':
                // Fix: recalculate note2 and note2_octave from note1+interval
                $intervalName = $q['interval'] ?? null;
                if (!$intervalName) break;

                $expected = $music->noteAboveByInterval($note1, $octave, $intervalName);
                if ($expected) {
                    if (in_array('answer_mismatch', $issues)) {
                        $fixes['note2']       = $expected['note'];
                        $fixes['note2_octave']= $expected['octave'];
                    } elseif (!isset($q['note2_octave'])) {
                        $fixes['note2_octave'] = $expected['octave'];
                    }
                    $fixes['validation_status'] = 'valid';
                }
                break;

            case 'interval-construction-practice':
                $intervalName = $q['interval'] ?? null;
                if (!$intervalName) break;

                $expected = $music->noteAboveByInterval($note1, $octave, $intervalName);
                if ($expected) {
                    if (in_array('answer_mismatch', $issues)) {
                        $fixes['note2']       = $expected['note'];
                        $fixes['note2_octave']= $expected['octave'];
                    } elseif (!isset($q['note2_octave'])) {
                        $fixes['note2_octave'] = $expected['octave'];
                    }
                    $fixes['validation_status'] = 'valid';
                }
                break;
        }

        return $fixes;
    }
}
