<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Practice;
use App\Models\QuestionnaireQuestion;
use App\Models\QuestionnaireResponse;
use App\Models\UserPractice;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $profile = $user->profile ?? new UserProfile();
        $questions = QuestionnaireQuestion::active()->get();
        $responses = $user->questionnaireResponses()->pluck('answer_value', 'question_id');
        $tab = $request->get('tab', 'general');

        $userPractices = UserPractice::where('user_id', $user->id)
            ->with('practice')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalSessions   = $userPractices->count();
        $totalQuestions  = $userPractices->sum('total_questions');
        $totalCorrect    = $userPractices->sum('correct_answers');
        $totalIncorrect  = $userPractices->sum('incorrect_answers');
        $totalTime       = $userPractices->sum('total_time');
        $overallAccuracy = $totalQuestions > 0
            ? round(($totalCorrect / $totalQuestions) * 100, 1) : 0;
        $streak          = $this->calcStreak($userPractices);
        $formattedTime   = $this->calcFormattedTime($totalTime);
        $practiceBreakdown = $this->calcPracticeBreakdown($userPractices);
        $recentActivity  = $userPractices->take(5);

        return view('profile.edit', compact(
            'user', 'profile', 'questions', 'responses', 'tab',
            'totalSessions', 'totalQuestions', 'totalCorrect', 'totalIncorrect',
            'overallAccuracy', 'streak', 'formattedTime',
            'practiceBreakdown', 'recentActivity'
        ));
    }

    private function calcStreak($userPractices): int
    {
        if ($userPractices->isEmpty()) return 0;

        $dates = $userPractices->pluck('created_at')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->unique()->sort()->reverse()->values();

        if ($dates->isEmpty()) return 0;

        $today     = Carbon::today()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        if ($dates->first() !== $today && $dates->first() !== $yesterday) return 0;

        $streak = 0;
        $current = Carbon::parse($dates->first());

        foreach ($dates as $date) {
            $d = Carbon::parse($date);
            if ($current->diffInDays($d) <= 1) { $streak++; $current = $d; }
            else break;
        }

        return $streak;
    }

    private function calcFormattedTime(int $seconds): string
    {
        if ($seconds < 60) return $seconds . 's';
        $minutes = floor($seconds / 60);
        $remaining = $seconds % 60;
        if ($minutes < 60) return $minutes . 'm ' . $remaining . 's';
        $hours = floor($minutes / 60);
        return $hours . 'h ' . ($minutes % 60) . 'm';
    }

    private function calcPracticeBreakdown($userPractices): array
    {
        $practices = Practice::all();
        $breakdown = [];

        foreach ($practices as $practice) {
            $tp = $userPractices->where('practice_id', $practice->id);
            $tq = $tp->sum('total_questions');
            $tc = $tp->sum('correct_answers');
            $tt = $tp->sum('total_time');

            $breakdown[] = [
                'id'              => $practice->id,
                'name'            => $practice->name,
                'slug'            => $practice->slug,
                'sessions'        => $tp->count(),
                'total_questions' => $tq,
                'correct_answers' => $tc,
                'accuracy'        => $tq > 0 ? round(($tc / $tq) * 100, 1) : 0,
                'total_time'      => $tt,
                'avg_time'        => $tq > 0 ? round($tt / $tq, 1) : 0,
            ];
        }

        return $breakdown;
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'country' => $validated['country'] ?? null,
            'city' => $validated['city'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit', ['tab' => 'general'])->with('status', 'profile-updated');
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        // Delete old avatar file if it was stored directly in public/
        if ($user->avatar_url && str_starts_with($user->avatar_url, 'pub:')) {
            $oldPath = public_path(substr($user->avatar_url, 4));
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        } elseif ($user->avatar_url && !str_starts_with($user->avatar_url, 'http')) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        // Store directly in public/images/avatars/ to avoid symlink issues
        $filename = $request->file('avatar')->hashName();
        $destDir  = public_path('images/avatars');
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $request->file('avatar')->move($destDir, $filename);

        $user->update(['avatar_url' => 'pub:images/avatars/' . $filename]);

        return Redirect::route('profile.edit', ['tab' => 'general'])->with('status', 'avatar-updated');
    }

    public function toggleSuspend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isSuspended()) {
            $user->update(['suspended_at' => null]);
            return Redirect::route('profile.edit')->with('status', 'account-activated');
        }

        $user->update(['suspended_at' => now()]);
        return Redirect::route('profile.edit')->with('status', 'account-suspended');
    }

    public function editExtendedProfile(Request $request): View
    {
        $user = $request->user();
        $profile = $user->profile ?? new UserProfile();

        return view('profile.extended-profile', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    public function updateExtendedProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'primary_instrument' => ['nullable', 'string', 'max:100'],
            'secondary_instruments' => ['nullable', 'array'],
            'secondary_instruments.*' => ['string', 'max:100'],
            'musical_level' => ['nullable', 'in:beginner,intermediate,advanced'],
            'education_status' => ['nullable', 'in:self_taught,private_lessons,music_school,professional'],
            'weekly_practice_hours' => ['nullable', 'integer', 'min:0', 'max:168'],
            'learning_goals' => ['nullable', 'array'],
            'learning_goals.*' => ['string', 'max:255'],
            'interests' => ['nullable', 'array'],
            'interests.*' => ['string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->user()->update([
            'phone' => $validated['phone'] ?? null,
            'country' => $validated['country'] ?? null,
            'city' => $validated['city'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
        ]);

        $request->user()->profile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'primary_instrument' => $validated['primary_instrument'] ?? null,
                'secondary_instruments' => $validated['secondary_instruments'] ?? null,
                'musical_level' => $validated['musical_level'] ?? null,
                'education_status' => $validated['education_status'] ?? null,
                'weekly_practice_hours' => $validated['weekly_practice_hours'] ?? null,
                'learning_goals' => $validated['learning_goals'] ?? null,
                'interests' => $validated['interests'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ]
        );

        return Redirect::route('profile.edit', ['tab' => 'music'])->with('status', 'profile-updated');
    }

    public function showQuestionnaire(Request $request): View
    {
        $questions = QuestionnaireQuestion::active()->get();
        $responses = $request->user()->questionnaireResponses()
            ->pluck('answer_value', 'question_id');

        return view('profile.questionnaire', [
            'questions' => $questions,
            'responses' => $responses,
        ]);
    }

    public function storeQuestionnaire(Request $request): RedirectResponse
    {
        $questions = QuestionnaireQuestion::active()->get();

        foreach ($questions as $question) {
            $key = "answers.{$question->id}";
            $value = $request->input($key);

            if (is_null($value) && $question->is_required) {
                continue;
            }

            if (is_array($value)) {
                $value = json_encode($value);
            }

            if (!is_null($value)) {
                QuestionnaireResponse::updateOrCreate(
                    [
                        'user_id' => $request->user()->id,
                        'question_id' => $question->id,
                    ],
                    ['answer_value' => $value]
                );
            }
        }

        return Redirect::route('profile.edit', ['tab' => 'questionnaire'])->with('status', 'questionnaire-saved');
    }
}
