<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MelodicIntervalPractice;
use App\Models\Practice;
use App\Services\MusicTheoryService;
use Illuminate\Http\Request;

class MelodicIntervalPracticeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $practices = MelodicIntervalPractice::latest()->paginate(15);
        $settings = Practice::where('slug', 'melodic-interval-practice')->first();
        return view('admin.melodic-interval.index', compact('practices', 'settings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.melodic-interval.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'interval'    => 'required|string|max:50',
            'note1'       => 'required|string|max:10',
            'note2'       => 'required|string|max:10',
            'octave'      => 'required|string|in:2,3,4,5,6',
            'note2_octave'=> 'nullable|integer|min:2|max:8',
        ]);

        // Auto-derive note2_octave if not supplied
        if (empty($validated['note2_octave'])) {
            $music  = app(MusicTheoryService::class);
            $result = $music->noteAboveByInterval($validated['note1'], (int)$validated['octave'], $validated['interval']);
            $validated['note2_octave'] = $result['octave'] ?? $validated['octave'];
        }

        // Stamp validation status
        $validated['validation_status'] = app(MusicTheoryService::class)
            ->validateQuestionConsistency($validated, 'melodic-interval-practice')['status'];

        MelodicIntervalPractice::create($validated);

        return redirect()->route('admin.melodic-interval.index')
            ->with('success', 'Melodic Interval Practice created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MelodicIntervalPractice $melodic_interval)
    {
        return view('admin.melodic-interval.edit', ['practice' => $melodic_interval]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MelodicIntervalPractice $melodic_interval)
    {
        $validated = $request->validate([
            'interval'    => 'required|string|max:50',
            'note1'       => 'required|string|max:10',
            'note2'       => 'required|string|max:10',
            'octave'      => 'required|string|in:2,3,4,5,6',
            'note2_octave'=> 'nullable|integer|min:2|max:8',
        ]);

        // Auto-derive note2_octave if not supplied via hidden field
        if (empty($validated['note2_octave'])) {
            $music  = app(MusicTheoryService::class);
            $result = $music->noteAboveByInterval($validated['note1'], (int)$validated['octave'], $validated['interval']);
            $validated['note2_octave'] = $result['octave'] ?? $validated['octave'];
        }

        // Re-stamp validation status
        $validated['validation_status'] = app(MusicTheoryService::class)
            ->validateQuestionConsistency($validated, 'melodic-interval-practice')['status'];

        $melodic_interval->update($validated);

        return redirect()->route('admin.melodic-interval.index')
            ->with('success', 'Melodic Interval Practice updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MelodicIntervalPractice $melodic_interval)
    {
        $melodic_interval->delete();

        return redirect()->route('admin.melodic-interval.index')
            ->with('success', 'Melodic Interval Practice deleted successfully.');
    }

    /**
     * Update practice settings.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'type' => 'required|string|max:100',
            'is_premium' => 'boolean',
        ]);

        $validated['is_premium'] = $request->has('is_premium');

        $practice = Practice::where('slug', 'melodic-interval-practice')->first();
        
        if ($practice) {
            $practice->update($validated);
        }

        return redirect()->route('admin.melodic-interval.index')
            ->with('success', 'Practice settings updated successfully.');
    }
}
