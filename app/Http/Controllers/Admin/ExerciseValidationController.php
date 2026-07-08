<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HarmonicIntervalPractice;
use App\Models\IntervalComparisonPractice;
use App\Models\IntervalConstructionPractice;
use App\Models\IntervalDirectionPractice;
use App\Models\MelodicIntervalPractice;
use App\Services\MusicTheoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ExerciseValidationController extends Controller
{
    private const PRACTICE_TYPES = [
        'melodic-interval-practice' => MelodicIntervalPractice::class,
        'harmonic-interval-practice' => HarmonicIntervalPractice::class,
        'interval-direction-practice' => IntervalDirectionPractice::class,
        'interval-construction-practice' => IntervalConstructionPractice::class,
        'interval-comparison-practice' => IntervalComparisonPractice::class,
    ];

    public function __construct(private MusicTheoryService $music) {}

    public function index()
    {
        $summary = [];

        foreach (self::PRACTICE_TYPES as $type => $modelClass) {
            $total = $modelClass::count();
            $valid = $invalid = $needsReview = 0;

            foreach ($modelClass::all() as $q) {
                $result = $this->music->validateQuestionConsistency($q->toArray(), $type);
                match ($result['status']) {
                    'valid' => $valid++,
                    'needs_review' => $needsReview++,
                    default => $invalid++,
                };
            }

            $summary[$type] = compact('total', 'valid', 'invalid', 'needsReview');
        }

        return view('admin.exercises.validate', compact('summary'));
    }

    public function repair(Request $request)
    {
        $dryRun = $request->boolean('dry_run', false);
        $type = $request->input('type');

        $exitCode = Artisan::call('exercises:repair-questions', array_filter([
            '--type' => $type ?: null,
            '--dry-run' => $dryRun,
        ]));

        $output = Artisan::output();

        if ($request->wantsJson()) {
            return response()->json(['output' => $output, 'exit_code' => $exitCode]);
        }

        return redirect()->route('admin.exercises.validate')
            ->with('flash_message', $dryRun ? '[Dry run] '.$output : $output);
    }
}
