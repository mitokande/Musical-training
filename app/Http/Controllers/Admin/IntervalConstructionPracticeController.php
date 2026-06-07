<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntervalConstructionPractice;
use App\Models\Practice;
use App\Services\MusicTheoryService;
use Illuminate\Http\Request;

class IntervalConstructionPracticeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $practices = IntervalConstructionPractice::latest()->paginate(15);
        $settings = Practice::where('slug', 'interval-construction-practice')->first();
        return view('admin.interval-construction.index', compact('practices', 'settings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.interval-construction.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'interval'     => 'required|string|max:50',
            'note1'        => 'required|string|max:10',
            'note2'        => 'required|string|max:10',
            'octave'       => 'required|string|in:2,3,4,5,6',
            'note2_octave' => 'nullable|integer|min:2|max:8',
        ]);

        if (empty($validated['note2_octave'])) {
            $result = app(MusicTheoryService::class)->noteAboveByInterval($validated['note1'], (int)$validated['octave'], $validated['interval']);
            $validated['note2_octave'] = $result['octave'] ?? $validated['octave'];
        }

        $validated['validation_status'] = app(MusicTheoryService::class)
            ->validateQuestionConsistency($validated, 'interval-construction-practice')['status'];

        IntervalConstructionPractice::create($validated);

        return redirect()->route('admin.interval-construction.index')
            ->with('success', 'Interval Construction Practice created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(IntervalConstructionPractice $interval_construction)
    {
        return view('admin.interval-construction.edit', ['practice' => $interval_construction]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IntervalConstructionPractice $interval_construction)
    {
        $validated = $request->validate([
            'interval'     => 'required|string|max:50',
            'note1'        => 'required|string|max:10',
            'note2'        => 'required|string|max:10',
            'octave'       => 'required|string|in:2,3,4,5,6',
            'note2_octave' => 'nullable|integer|min:2|max:8',
        ]);

        if (empty($validated['note2_octave'])) {
            $result = app(MusicTheoryService::class)->noteAboveByInterval($validated['note1'], (int)$validated['octave'], $validated['interval']);
            $validated['note2_octave'] = $result['octave'] ?? $validated['octave'];
        }

        $validated['validation_status'] = app(MusicTheoryService::class)
            ->validateQuestionConsistency($validated, 'interval-construction-practice')['status'];

        $interval_construction->update($validated);

        return redirect()->route('admin.interval-construction.index')
            ->with('success', 'Interval Construction Practice updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IntervalConstructionPractice $interval_construction)
    {
        $interval_construction->delete();

        return redirect()->route('admin.interval-construction.index')
            ->with('success', 'Interval Construction Practice deleted successfully.');
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

        $practice = Practice::where('slug', 'interval-construction-practice')->first();
        
        if ($practice) {
            $practice->update($validated);
        }

        return redirect()->route('admin.interval-construction.index')
            ->with('success', 'Practice settings updated successfully.');
    }
}
