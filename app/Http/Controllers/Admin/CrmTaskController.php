<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmTask;
use App\Models\User;
use Illuminate\Http\Request;

class CrmTaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = CrmTask::with(['admin', 'user'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->priority, fn($q, $p) => $q->where('priority', $p))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->admin_id, fn($q, $id) => $q->where('admin_id', $id))
            ->latest()
            ->paginate(20);

        $admins = User::where('role', 'admin')->get();

        return view('admin.tasks.index', compact('tasks', 'admins'));
    }

    public function create()
    {
        $users = User::select('id', 'name', 'email')->get();
        $admins = User::where('role', 'admin')->get();

        return view('admin.tasks.create', compact('users', 'admins'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|string|in:call,email,follow_up,meeting,other',
            'priority'    => 'required|string|in:low,normal,high,urgent',
            'status'      => 'required|string|in:pending,in_progress,completed,cancelled',
            'user_id'     => 'nullable|exists:users,id',
            'admin_id'    => 'required|exists:users,id',
            'due_date'    => 'nullable|date',
        ]);

        CrmTask::create($validated);

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task created successfully.');
    }

    public function show(CrmTask $crmTask)
    {
        $crmTask->load(['admin', 'user']);

        return view('admin.tasks.show', compact('crmTask'));
    }

    public function edit(CrmTask $crmTask)
    {
        $users = User::select('id', 'name', 'email')->get();
        $admins = User::where('role', 'admin')->get();

        return view('admin.tasks.edit', compact('crmTask', 'users', 'admins'));
    }

    public function update(Request $request, CrmTask $crmTask)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|string|in:call,email,follow_up,meeting,other',
            'priority'    => 'required|string|in:low,normal,high,urgent',
            'status'      => 'required|string|in:pending,in_progress,completed,cancelled',
            'user_id'     => 'nullable|exists:users,id',
            'admin_id'    => 'required|exists:users,id',
            'due_date'    => 'nullable|date',
        ]);

        if ($validated['status'] === 'completed' && $crmTask->status !== 'completed') {
            $validated['completed_at'] = now();
        }

        $crmTask->update($validated);

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(CrmTask $crmTask)
    {
        $crmTask->delete();

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task deleted successfully.');
    }
}
