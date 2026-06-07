<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MusicTheoryService;
use Illuminate\Http\Request;

class MusicTheoryApiController extends Controller
{
    public function __construct(private MusicTheoryService $music) {}

    /**
     * Recalculate interval data when admin changes note1/note2/octave in the edit form.
     * Returns direction, interval name, and suggested answer options.
     *
     * GET /admin/api/recalculate-interval
     *   ?note1=C&note1_octave=4&note2=E&note2_octave=4
     */
    public function recalculate(Request $request)
    {
        $note1   = $request->input('note1', 'C');
        $octave1 = (int) $request->input('note1_octave', 4);
        $note2   = $request->input('note2', 'E');
        $octave2 = (int) $request->input('note2_octave', $octave1);

        if (!isset(MusicTheoryService::NOTE_SEMITONES[$note1]) || !isset(MusicTheoryService::NOTE_SEMITONES[$note2])) {
            return response()->json(['error' => 'Unknown note name'], 422);
        }

        $direction     = $this->music->getDirection($note1, $octave1, $note2, $octave2);
        $semitones     = abs($this->music->semitonesBetween($note1, $octave1, $note2, $octave2));
        $intervalName  = $this->music->intervalNameFromSemitones($semitones);

        return response()->json([
            'direction'      => $direction,
            'semitones'      => $semitones,
            'interval_name'  => $intervalName,
            'note2_octave'   => $octave2,
        ]);
    }

    /**
     * Validate a single question for consistency.
     * POST /admin/api/validate-question
     *   { type, ...question_fields }
     */
    public function validateQuestion(Request $request)
    {
        $type     = $request->input('type', '');
        $question = $request->except('type');

        $result = $this->music->validateQuestionConsistency($question, $type);

        return response()->json($result);
    }

    /**
     * Validate all questions for a practice type and return a summary.
     * GET /admin/api/validate-all?type=interval-direction-practice
     */
    public function validateAll(Request $request)
    {
        $type = $request->input('type', '');

        $modelMap = [
            'melodic-interval-practice'      => \App\Models\MelodicIntervalPractice::class,
            'harmonic-interval-practice'     => \App\Models\HarmonicIntervalPractice::class,
            'interval-direction-practice'    => \App\Models\IntervalDirectionPractice::class,
            'interval-construction-practice' => \App\Models\IntervalConstructionPractice::class,
            'interval-comparison-practice'   => \App\Models\IntervalComparisonPractice::class,
        ];

        if (!isset($modelMap[$type])) {
            return response()->json(['error' => 'Unknown practice type'], 422);
        }

        $questions = $modelMap[$type]::all();
        $results   = ['total' => $questions->count(), 'valid' => 0, 'invalid' => 0, 'needs_review' => 0, 'details' => []];

        foreach ($questions as $q) {
            $check = $this->music->validateQuestionConsistency($q->toArray(), $type);
            $results[$check['status']]++;
            if ($check['status'] !== 'valid') {
                $results['details'][] = ['id' => $q->id, 'status' => $check['status'], 'issues' => $check['issues']];
            }
        }

        return response()->json($results);
    }
}
