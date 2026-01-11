<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntervalComparisonPractice;
use Illuminate\Http\Request;

class IntervalComparisonPracticeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $practices = IntervalComparisonPractice::latest()->paginate(15);
        return view('admin.interval-comparison.index', compact('practices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.interval-comparison.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'interval_a' => 'required|string|max:20',
            'interval_b' => 'required|string|max:20',
            'target' => 'required|string|in:a,b',
            'octave' => 'required|string|in:2,3,4,5,6',
            'clef' => 'required|string|in:treble,alto,bass',
        ]);

        IntervalComparisonPractice::create($validated);

        return redirect()->route('admin.interval-comparison.index')
            ->with('success', 'Interval Comparison Practice created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(IntervalComparisonPractice $interval_comparison)
    {
        return view('admin.interval-comparison.edit', ['practice' => $interval_comparison]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IntervalComparisonPractice $interval_comparison)
    {
        $validated = $request->validate([
            'interval_a' => 'required|string|max:20',
            'interval_b' => 'required|string|max:20',
            'target' => 'required|string|in:a,b',
            'octave' => 'required|string|in:2,3,4,5,6',
            'clef' => 'required|string|in:treble,alto,bass',
        ]);

        $interval_comparison->update($validated);

        return redirect()->route('admin.interval-comparison.index')
            ->with('success', 'Interval Comparison Practice updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IntervalComparisonPractice $interval_comparison)
    {
        $interval_comparison->delete();

        return redirect()->route('admin.interval-comparison.index')
            ->with('success', 'Interval Comparison Practice deleted successfully.');
    }
}

