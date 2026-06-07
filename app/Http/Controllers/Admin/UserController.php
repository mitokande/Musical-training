<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\UsersExport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index()
    {
        $users = User::latest()->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show user detail with related data.
     */
    public function show(User $user)
    {
        $user->load([
            'profile',
            'teacherProfile',
            'school',
            'userPractices.practice',
            'questionnaireResponses',
            'crmNotes' => fn($q) => $q->latest(),
        ]);

        $recentMessages = $user->receivedMessages()
            ->with('sender')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.users.show', compact('user', 'recentMessages'));
    }

    /**
     * Show user segments overview.
     */
    public function segments()
    {
        $segments = [
            'free'      => User::where('plan', 'free')->count(),
            'premium'   => User::where('plan', 'premium')->count(),
            'students'  => User::where('role', 'user')->count(),
            'teachers'  => User::where('role', 'teacher')->count(),
            'schools'   => User::where('role', 'school')->count(),
            'active'    => User::where('last_active_at', '>=', now()->subDays(7))->count(),
            'inactive'  => User::where(function ($q) {
                $q->where('last_active_at', '<', now()->subDays(30))
                  ->orWhereNull('last_active_at');
            })->count(),
        ];

        return view('admin.users.segments', compact('segments'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email'    => 'required|string|email|max:255|unique:users',
            'role'     => 'required|string|in:user,teacher,school,admin',
            'plan'     => 'required|string|in:free,premium',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'     => 'required|string|in:user,teacher,school,admin',
            'plan'     => 'required|string|in:free,premium',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->plan = $validated['plan'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Export users to Excel.
     */
    public function export(Request $request)
    {
        return Excel::download(new UsersExport($request), 'users.xlsx');
    }
}
