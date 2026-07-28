<?php

namespace App\Http\Controllers;

use App\Models\EmailMessage;
use App\Models\EmailPreference;
use App\Models\User;
use App\Services\EmailCenter\SuppressionService;
use Illuminate\Http\Request;

/**
 * The email-preferences centre: how often and about which topics a user wants
 * marketing mail. Reachable two ways — from a signed link in the footer of
 * every email (token → message → user, no login needed), or from the logged-in
 * account Settings page.
 *
 * Account and booking/lesson notifications are transactional and never pass
 * through the marketing gate, so they are unaffected by anything set here.
 */
class EmailPreferenceController extends Controller
{
    public function __construct(protected SuppressionService $suppressions) {}

    // --- Signed token flow (from email footer, works logged out) ------------

    public function showByToken(string $token)
    {
        $message = EmailMessage::where('tracking_token', $token)->firstOrFail();
        $user = $message->user;
        abort_if(! $user, 404);

        return $this->renderFor($user, token: $token);
    }

    public function updateByToken(Request $request, string $token)
    {
        $message = EmailMessage::where('tracking_token', $token)->firstOrFail();
        $user = $message->user;
        abort_if(! $user, 404);

        $this->persist($request, $user);

        return $this->renderFor($user, token: $token, saved: true);
    }

    // --- Authenticated flow (from account Settings) -------------------------

    public function edit(Request $request)
    {
        return $this->renderFor($request->user(), token: null);
    }

    public function update(Request $request)
    {
        $this->persist($request, $request->user());

        return redirect()->route('email-preferences.edit')->with('status', 'email-prefs-updated');
    }

    // --- Shared -------------------------------------------------------------

    protected function renderFor(User $user, ?string $token, bool $saved = false)
    {
        return view('email.preferences', [
            'user' => $user,
            'preference' => EmailPreference::for($user),
            'unsubscribedAll' => $this->suppressions->isSuppressed($user->email),
            'audience' => $user->emailAudience(),
            'settingsUrl' => $this->settingsUrl($user),
            'token' => $token,
            'authed' => auth()->id() === $user->id,
            'saved' => $saved,
        ]);
    }

    /** Where the "back to settings" link points, per the user's panel. */
    protected function settingsUrl(User $user): string
    {
        return match ($user->emailAudience()) {
            'school' => route('school.settings'),
            'teacher' => route('teacher.settings'),
            default => route('profile.edit', ['tab' => 'settings']),
        };
    }

    protected function persist(Request $request, User $user): void
    {
        $data = $request->validate([
            'frequency' => 'required|in:'.implode(',', EmailPreference::FREQUENCIES),
        ]);

        EmailPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'frequency' => $data['frequency'],
                'topic_tips' => $request->boolean('topic_tips'),
                'topic_progress' => $request->boolean('topic_progress'),
                'topic_offers' => $request->boolean('topic_offers'),
                'topic_product' => $request->boolean('topic_product'),
                'topic_teaching' => $request->boolean('topic_teaching'),
            ]
        );

        // The master "unsubscribe from all marketing" switch maps to the shared
        // suppression list so it stays consistent with the one-click footer.
        if ($request->boolean('unsubscribe_all')) {
            $this->suppressions->suppress($user->email, 'unsubscribe', 'preferences');
        } elseif ($this->suppressions->isSuppressed($user->email)) {
            $this->suppressions->unsuppress($user->email);
        }
    }
}
