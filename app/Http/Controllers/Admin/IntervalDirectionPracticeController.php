<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntervalDirectionPractice;
use Illuminate\Http\Request;

class IntervalDirectionPracticeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $practices = IntervalDirectionPractice::latest()->paginate(15);
        return view('admin.interval-direction.index', compact('practices'));
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
}

