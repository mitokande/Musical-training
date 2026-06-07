<?php

namespace App\Console\Commands;

use App\Services\MusicTheoryService;
use Illuminate\Console\Command;

class ValidateQuestions extends Command
{
    protected $signature = 'exercises:validate-questions
                            {--type= : Limit to one practice type slug}
                            {--show-issues : Print per-question issue details}';

    protected $description = 'Validate all interval practice questions for data consistency';

    private const PRACTICE_TYPES = [
        'melodic-interval-practice'      => \App\Models\MelodicIntervalPractice::class,
        'harmonic-interval-practice'     => \App\Models\HarmonicIntervalPractice::class,
        'interval-direction-practice'    => \App\Models\IntervalDirectionPractice::class,
        'interval-construction-practice' => \App\Models\IntervalConstructionPractice::class,
        'interval-comparison-practice'   => \App\Models\IntervalComparisonPractice::class,
    ];

    public function handle(MusicTheoryService $music): int
    {
        $filterType  = $this->option('type');
        $showIssues  = $this->option('show-issues');

        $types = $filterType
            ? (isset(self::PRACTICE_TYPES[$filterType]) ? [$filterType => self::PRACTICE_TYPES[$filterType]] : [])
            : self::PRACTICE_TYPES;

        if (empty($types)) {
            $this->error("Unknown practice type: {$filterType}");
            return Command::FAILURE;
        }

        $grandTotal   = 0;
        $grandValid   = 0;
        $grandInvalid = 0;
        $grandReview  = 0;

        foreach ($types as $type => $modelClass) {
            $questions = $modelClass::all();
            $total = $questions->count();
            $valid = $invalid = $needsReview = 0;

            foreach ($questions as $q) {
                $result = $music->validateQuestionConsistency($q->toArray(), $type);
                match ($result['status']) {
                    'valid'        => $valid++,
                    'needs_review' => $needsReview++,
                    default        => $invalid++,
                };

                if ($showIssues && $result['status'] !== 'valid') {
                    $this->line("  [{$type}] ID={$q->id} status={$result['status']} issues=" . implode(',', $result['issues']));
                }
            }

            $this->line(sprintf(
                '%-44s total=%-4d valid=%-4d invalid=%-4d needs_review=%d',
                $type, $total, $valid, $invalid, $needsReview
            ));

            $grandTotal   += $total;
            $grandValid   += $valid;
            $grandInvalid += $invalid;
            $grandReview  += $needsReview;
        }

        $this->newLine();
        $this->line(sprintf(
            'TOTAL                                        total=%-4d valid=%-4d invalid=%-4d needs_review=%d',
            $grandTotal, $grandValid, $grandInvalid, $grandReview
        ));

        return $grandInvalid > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
