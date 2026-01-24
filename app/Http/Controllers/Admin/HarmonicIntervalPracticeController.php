<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HarmonicIntervalPractice;
use App\Models\Practice;
use Illuminate\Http\Request;

class HarmonicIntervalPracticeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $practices = HarmonicIntervalPractice::latest()->paginate(15);
        $settings = Practice::where('slug', 'harmonic-interval-practice')->first();
        return view('admin.harmonic-interval.index', compact('practices', 'settings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.harmonic-interval.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'interval' => 'required|string|max:50',
            'note1' => 'required|string|max:10',
            'note2' => 'required|string|max:10',
            'octave' => 'required|string|in:2,3,4,5,6',
        ]);

        HarmonicIntervalPractice::create($validated);

        return redirect()->route('admin.harmonic-interval.index')
            ->with('success', 'Harmonic Interval Practice created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HarmonicIntervalPractice $harmonic_interval)
    {
        return view('admin.harmonic-interval.edit', ['practice' => $harmonic_interval]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HarmonicIntervalPractice $harmonic_interval)
    {
        $validated = $request->validate([
            'interval' => 'required|string|max:50',
            'note1' => 'required|string|max:10',
            'note2' => 'required|string|max:10',
            'octave' => 'required|string|in:2,3,4,5,6',
        ]);

        $harmonic_interval->update($validated);

        return redirect()->route('admin.harmonic-interval.index')
            ->with('success', 'Harmonic Interval Practice updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HarmonicIntervalPractice $harmonic_interval)
    {
        $harmonic_interval->delete();

        return redirect()->route('admin.harmonic-interval.index')
            ->with('success', 'Harmonic Interval Practice deleted successfully.');
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

        $practice = Practice::where('slug', 'harmonic-interval-practice')->first();
        
        if ($practice) {
            $practice->update($validated);
        }

        return redirect()->route('admin.harmonic-interval.index')
            ->with('success', 'Practice settings updated successfully.');
    }
}
