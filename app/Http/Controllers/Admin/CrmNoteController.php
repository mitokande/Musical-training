<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmNote;
use App\Models\User;
use Illuminate\Http\Request;

class CrmNoteController extends Controller
{
    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'note'           => 'required|string',
            'type'           => 'required|string|in:general,follow_up,complaint,feedback',
            'follow_up_date' => 'nullable|date|after_or_equal:today',
        ]);

        $user->crmNotes()->create([
            'admin_id'       => auth()->id(),
            'note'           => $validated['note'],
            'type'           => $validated['type'],
            'follow_up_date' => $validated['follow_up_date'] ?? null,
        ]);

        return back()->with('success', 'Note added successfully.');
    }

    public function update(Request $request, CrmNote $note)
    {
        $validated = $request->validate([
            'note'           => 'required|string',
            'type'           => 'required|string|in:general,follow_up,complaint,feedback',
            'follow_up_date' => 'nullable|date',
        ]);

        $note->update($validated);

        return back()->with('success', 'Note updated successfully.');
    }

    public function destroy(CrmNote $note)
    {
        $note->delete();

        return back()->with('success', 'Note deleted successfully.');
    }

    public function togglePin(CrmNote $note)
    {
        $note->update(['is_pinned' => !$note->is_pinned]);

        return back()->with('success', 'Note pin status toggled.');
    }
}
