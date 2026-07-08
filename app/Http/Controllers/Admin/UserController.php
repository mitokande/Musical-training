<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Models\AiCoachingSession;
use App\Models\DailyExerciseCount;
use App\Models\FeedItem;
use App\Models\Follow;
use App\Models\GameScore;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->when($request->role, fn ($q, $role) => $q->where('role', $role))
            ->when($request->plan, fn ($q, $plan) => $q->where('plan', $plan))
            ->when($request->status === 'active', fn ($q) => $q->where('last_active_at', '>=', now()->subDays(7)))
            ->when($request->status === 'inactive', function ($q) {
                $q->where(function ($q) {
                    $q->where('last_active_at', '<', now()->subDays(30))
                        ->orWhereNull('last_active_at');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

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
            'crmNotes' => fn ($q) => $q->latest(),
        ]);

        $recentMessages = $user->receivedMessages()
            ->with('sender')
            ->latest()
            ->take(10)
            ->get();

        $activityStats = [
            'total_exercises' => (int) DailyExerciseCount::where('user_id', $user->id)->sum('count'),
            'game_plays' => GameScore::where('user_id', $user->id)->count(),
            'best_game_score' => (int) GameScore::where('user_id', $user->id)->max('score'),
            'feed_posts' => FeedItem::where('user_id', $user->id)->where('type', 'post')->count(),
            'followers' => Follow::where('followed_id', $user->id)->count(),
            'following' => Follow::where('follower_id', $user->id)->count(),
            'ai_sessions' => AiCoachingSession::where('user_id', $user->id)->count(),
        ];

        $exerciseTrend = DailyExerciseCount::where('user_id', $user->id)
            ->where('date', '>=', now()->subDays(30))
            ->select('date', DB::raw('SUM(count) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($r) => ['date' => (string) $r->date, 'total' => (int) $r->total])
            ->values();

        return view('admin.users.show', compact('user', 'recentMessages', 'activityStats', 'exerciseTrend'));
    }

    /**
     * Apply a bulk action to the selected users.
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:set_plan_free,set_plan_premium,set_role_user,set_role_teacher,set_role_school,delete',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        // Never let a bulk action touch the current admin or other admins
        $users = User::whereIn('id', $validated['user_ids'])
            ->where('id', '!=', auth()->id())
            ->where('role', '!=', 'admin')
            ->get();

        $count = $users->count();

        foreach ($users as $user) {
            match ($validated['action']) {
                'set_plan_free' => tap($user->update(['plan' => 'free']), fn () => $this->syncTeacherTierWithPlan($user)),
                'set_plan_premium' => tap($user->update(['plan' => 'premium']), fn () => $this->syncTeacherTierWithPlan($user)),
                'set_role_user' => $user->update(['role' => 'user']),
                'set_role_teacher' => $user->update(['role' => 'teacher']),
                'set_role_school' => $user->update(['role' => 'school']),
                'delete' => $user->delete(),
            };
        }

        $skipped = count($validated['user_ids']) - $count;
        $message = "Bulk action applied to {$count} member(s).";
        if ($skipped > 0) {
            $message .= " {$skipped} skipped (admins and your own account are protected).";
        }

        return redirect()->route('admin.users.index')->with('success', $message);
    }

    /**
     * Log in as the given user (impersonation).
     */
    public function impersonate(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You are already logged in as this user.');
        }
        if ($user->role === 'admin') {
            return back()->with('error', 'Admins cannot be impersonated.');
        }

        session(['impersonator_id' => auth()->id()]);
        Auth::login($user);

        return redirect()->route('dashboard');
    }

    /**
     * Return to the admin account after impersonating.
     */
    public function leaveImpersonation()
    {
        $adminId = session()->pull('impersonator_id');
        abort_unless($adminId, 403);

        $admin = User::findOrFail($adminId);
        Auth::login($admin);

        return redirect()->route('admin.users.index')->with('success', 'Returned to your admin account.');
    }

    /**
     * Email a password reset link to the user.
     */
    public function sendPasswordReset(User $user)
    {
        $status = Password::sendResetLink(['email' => $user->email]);

        return back()->with(
            $status === Password::RESET_LINK_SENT ? 'success' : 'error',
            __($status)
        );
    }

    /**
     * Show user segments overview.
     */
    public function segments()
    {
        $segments = [
            'free' => User::where('plan', 'free')->count(),
            'premium' => User::where('plan', 'premium')->count(),
            'students' => User::where('role', 'user')->count(),
            'teachers' => User::where('role', 'teacher')->count(),
            'schools' => User::where('role', 'school')->count(),
            'active' => User::where('last_active_at', '>=', now()->subDays(7))->count(),
            'inactive' => User::where(function ($q) {
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
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string|in:user,teacher,school,admin',
            'plan' => 'required|string|in:free,premium',
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
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|string|in:user,teacher,school,admin',
            'plan' => 'required|string|in:free,premium',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->plan = $validated['plan'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $this->syncTeacherTierWithPlan($user);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Keep a teacher account's premium capabilities in sync with the plan set
     * from the member-management screens. Teacher premium features are gated on
     * TeacherProfile->tier (see TeacherCapabilityService), which is otherwise
     * disconnected from User->plan — so a "premium" teacher would stay locked
     * unless we mirror the plan onto the tier here. A profile is created on the
     * fly for teachers who don't have one yet so the tier can be applied.
     */
    private function syncTeacherTierWithPlan(User $user): void
    {
        if (! $user->hasTeacherAccount()) {
            return;
        }

        $desiredTier = $user->plan === 'premium'
            ? TeacherProfile::TIER_PREMIUM
            : TeacherProfile::TIER_BASIC;

        $profile = $user->teacherProfile()->firstOrCreate([], [
            'tier' => $desiredTier,
            'status' => TeacherProfile::STATUS_DRAFT,
        ]);

        if ($profile->tier === $desiredTier) {
            return;
        }

        $from = $profile->tier;
        $profile->update(['tier' => $desiredTier]);

        activity('teacher')
            ->causedBy(auth()->user())
            ->performedOn($profile)
            ->withProperties(['from' => $from, 'to' => $desiredTier, 'via' => 'plan_sync'])
            ->log('teacher_tier_changed');
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
     * Toggle restriction on/off for a user.
     */
    public function toggleRestriction(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.edit', $user)
                ->with('error', 'Kendi hesabınızı kısıtlayamazsınız.');
        }

        $user->is_restricted = ! $user->is_restricted;
        $user->save();

        $message = $user->is_restricted
            ? 'Kullanıcı kısıtlandı. Artık yalnızca ana sayfayı görebilir.'
            : 'Kullanıcı kısıtlaması kaldırıldı.';

        return redirect()->route('admin.users.edit', $user)->with('success', $message);
    }

    /**
     * Export users to Excel.
     */
    public function export(Request $request)
    {
        return Excel::download(new UsersExport($request), 'users.xlsx');
    }
}
