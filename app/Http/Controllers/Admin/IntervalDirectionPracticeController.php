<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntervalDirectionPractice;
use App\Models\Practice;
use Illuminate\Http\Request;

class IntervalDirectionPracticeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $practices = IntervalDirectionPractice::latest()->paginate(15);
        $settings = Practice::where('slug', 'interval-direction-practice')->first();
        return view('admin.interval-direction.index', compact('practices', 'settings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.interval-direction.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'clef' => 'required|string|in:treble,alto,bass',
            'note1' => 'required|string|max:10',
            'note2' => 'required|string|max:10',
            'direction' => 'required|string|in:ascending,descending',
            'octave' => 'required|string|in:2,3,4,5,6',
        ]);

        IntervalDirectionPractice::create($validated);

        return redirect()->route('admin.interval-direction.index')
            ->with('success', 'Interval Direction Practice created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(IntervalDirectionPractice $interval_direction)
    {
        return view('admin.interval-direction.edit', ['practice' => $interval_direction]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IntervalDirectionPractice $interval_direction)
    {
        $validated = $request->validate([
            'clef' => 'required|string|in:treble,alto,bass',
            'note1' => 'required|string|max:10',
            'note2' => 'required|string|max:10',
            'direction' => 'required|string|in:ascending,descending',
            'octave' => 'required|string|in:2,3,4,5,6',
        ]);

        $interval_direction->update($validated);

        return redirect()->route('admin.interval-direction.index')
            ->with('success', 'Interval Direction Practice updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IntervalDirectionPractice $interval_direction)
    {
        $interval_direction->delete();

        return redirect()->route('admin.interval-direction.index')
            ->with('success', 'Interval Direction Practice deleted successfully.');
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

        $practice = Practice::where('slug', 'interval-direction-practice')->first();
        
        if ($practice) {
            $practice->update($validated);
        }

        return redirect()->route('admin.interval-direction.index')
            ->with('success', 'Practice settings updated successfully.');
    }
}
