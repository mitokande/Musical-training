<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Api\ApiException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Ai\AiChatQuota;
use App\Services\Ai\CoachPlanService;
use App\Services\Ai\MusicChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The app's two AI surfaces: the weekly Coach plan and the music assistant.
 *
 * Both mirror features that already exist on harmoniva.app, and both draw on
 * the same shared machinery — the plan matrix for entitlement and the daily
 * allowance, the SystemSetting model switch, and the ai_usage_logs ledger — so
 * the website and the app are metered, priced and tuned as one product rather
 * than two.
 *
 * The two features are gated differently, exactly as they are on the web:
 * AI Coach is premium-only (`plan:ai_coach` there, canAccess() here), while the
 * chat is open to everyone under the `ask_ai_daily` allowance.
 */
class AiController extends Controller
{
    public function __construct(
        private readonly CoachPlanService $coach,
        private readonly MusicChatService $chat,
        private readonly AiChatQuota $quota,
    ) {}

    /**
     * The stored plan, or null when there is no recent one.
     *
     * Reading and generating are deliberately different verbs. A screen that
     * generated on open would bill a seven-day plan every time someone tapped
     * through to look at Tuesday, and would make the screen wait on the model
     * before it could paint anything at all.
     */
    public function coachPlan(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->assertCoachAccess($user);

        return response()->json(['data' => $this->coach->latestFor($user)]);
    }

    /** Generates a new plan, replacing whatever the screen was showing. */
    public function generateCoachPlan(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->assertCoachAccess($user);

        return response()->json(['data' => $this->coach->generate($user)]);
    }

    /** The badge the chat screen shows before the learner has spent anything. */
    public function chatQuota(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->quota->snapshot($request->user())]);
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
            // The thread lives on the device, so it arrives with the request.
            // Bounding it here is what stops a modified client from sending an
            // arbitrarily long — and arbitrarily expensive — context window.
            'history' => ['sometimes', 'array', 'max:'.MusicChatService::HISTORY_LIMIT],
            'history.*.role' => ['required', 'string', 'in:user,assistant'],
            'history.*.content' => ['required', 'string', 'max:2000'],
        ]);

        $user = $request->user();

        if ($this->quota->isExhausted($user)) {
            throw ApiException::quotaExceeded(
                AiChatQuota::FEATURE,
                $this->quota->limitFor($user),
                $this->quota->used($user),
                __('You have reached your daily question limit. Upgrade to Premium for more.'),
            );
        }

        $reply = $this->chat->reply($user, $validated['message'], $validated['history'] ?? []);

        // Only a delivered answer is charged for: an upstream failure throws
        // above this line, so a 503 never costs the learner one of their day's
        // questions.
        $this->quota->increment($user);

        return response()->json([
            'data' => [
                'reply' => $reply,
                'quota' => $this->quota->snapshot($user),
            ],
        ]);
    }

    /**
     * AI Coach is premium-only on the website — routes/web.php gates it with
     * `plan:ai_coach`, and canAccess() reads free plans' 'limited' as a no. The
     * app must not be the cheap way in: a seven-day plan is the most expensive
     * single call either surface makes.
     *
     * Reading is gated as well as generating, because on the web the whole
     * screen is behind the middleware. The premium_required envelope carries
     * `upgrade_url`, so the app has what it needs to paint the paywall.
     */
    private function assertCoachAccess(User $user): void
    {
        if (! $user->canAccess('ai_coach')) {
            throw ApiException::premiumRequired(
                __('app.limits.feature_ai_coach'),
                ['feature' => 'ai_coach'],
            );
        }
    }
}
