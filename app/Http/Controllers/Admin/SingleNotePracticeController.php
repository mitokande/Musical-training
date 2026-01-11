<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Practice;
use App\Models\SingleNotePractice;
use Illuminate\Http\Request;

class SingleNotePracticeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $practices = SingleNotePractice::latest()->paginate(15);
        $settings = Practice::where('slug', 'single-note-practice')->first();
        return view('admin.single-note.index', compact('practices', 'settings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.single-note.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'target' => 'required|string|max:10',
            'target_type' => 'required|string|max:50',
            'other_options' => 'required|string|max:255',
            'octave' => 'required|string|in:2,3,4,5,6',
        ]);

        SingleNotePractice::create($validated);

        return redirect()->route('admin.single-note.index')
            ->with('success', 'Single Note Practice created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SingleNotePractice $single_note)
    {
        return view('admin.single-note.edit', ['practice' => $single_note]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SingleNotePractice $single_note)
    {
        $validated = $request->validate([
            'target' => 'required|string|max:10',
            'target_type' => 'required|string|max:50',
            'other_options' => 'required|string|max:255',
            'octave' => 'required|string|in:2,3,4,5,6',
        ]);

        $single_note->update($validated);

        return redirect()->route('admin.single-note.index')
            ->with('success', 'Single Note Practice updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SingleNotePractice $single_note)
    {
        $single_note->delete();

        return redirect()->route('admin.single-note.index')
            ->with('success', 'Single Note Practice deleted successfully.');
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

        $practice = Practice::where('slug', 'single-note-practice')->first();
        
        if ($practice) {
            $practice->update($validated);
        }

        return redirect()->route('admin.single-note.index')
            ->with('success', 'Practice settings updated successfully.');
    }
}
