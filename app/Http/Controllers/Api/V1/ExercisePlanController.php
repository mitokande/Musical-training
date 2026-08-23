<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Api\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ExercisePlanResource;
use App\Models\ExerciseSetupTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Saved practice plans — the Exercise Setup Studio's "saved plans", over the
 * mobile API.
 *
 * The website has had these since the Studio shipped, but only as web routes
 * behind a session cookie, so the app has had no way to reach them. Everything
 * here is the same table, the same ownership rule and the same plan allowance;
 * only the envelope is v1's.
 *
 * `settings` is stored as sent. Nothing server-side reads a plan's settings —
 * they come back to the client, the client rebuilds its setup screen from them
 * and starts a session the ordinary way — so the shape stays the client's
 * business, and a phone and a browser can each keep the keys they understand.
 */
class ExercisePlanController extends Controller
{
    /** Favourites first, then most recently touched — the website's order. */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $plans = $user->exerciseSetupTemplates()
            ->orderByDesc('is_favorite')
            ->orderByDesc('updated_at')
            ->get();

        $limit = $this->limitFor($request);

        return response()->json([
            'data' => ExercisePlanResource::collection($plans),
            'meta' => [
                // -1 is unlimited, and the app paints its "3 of 3 saved" line
                // from these two rather than counting a page it may not have.
                'limit' => $limit,
                'used' => $plans->count(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:50'],
            'exercise_type' => ['required', 'string', 'max:50'],
            'settings' => ['required', 'array'],
            'is_ai_generated' => ['sometimes', 'boolean'],
            'is_favorite' => ['sometimes', 'boolean'],
        ]);

        $user = $request->user();
        $limit = $this->limitFor($request);
        $used = $user->exerciseSetupTemplates()->count();

        if ($limit !== -1 && $used >= $limit) {
            throw ApiException::premiumRequired(
                __('You have reached the number of saved plans your plan allows.'),
                ['feature' => 'saved_plans_limit', 'limit' => $limit, 'used' => $used],
            );
        }

        $plan = $user->exerciseSetupTemplates()->create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'exercise_type' => $validated['exercise_type'],
            'settings_json' => $validated['settings'],
            'is_ai_generated' => $validated['is_ai_generated'] ?? false,
            'is_favorite' => $validated['is_favorite'] ?? false,
        ]);

        return response()->json(['data' => new ExercisePlanResource($plan)], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'settings' => ['sometimes', 'array'],
            'is_favorite' => ['sometimes', 'boolean'],
        ]);

        $plan = $this->ownedPlan($request, $id);

        $plan->fill(array_filter([
            'name' => $validated['name'] ?? null,
            'settings_json' => $validated['settings'] ?? null,
        ], fn ($value) => $value !== null));

        if (array_key_exists('is_favorite', $validated)) {
            $plan->is_favorite = $validated['is_favorite'];
        }

        $plan->save();

        return response()->json(['data' => new ExercisePlanResource($plan)]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->ownedPlan($request, $id)->delete();

        return response()->json(['data' => ['status' => 'deleted']]);
    }

    /**
     * Someone else's plan is reported as missing rather than as forbidden: a
     * 403 would confirm that the id exists, and the caller has no business
     * knowing either way.
     */
    private function ownedPlan(Request $request, int $id): ExerciseSetupTemplate
    {
        $plan = $request->user()->exerciseSetupTemplates()->find($id);

        if (! $plan) {
            throw ApiException::notFound(__('This saved plan no longer exists.'));
        }

        return $plan;
    }

    /**
     * How many plans this account may keep, -1 for unlimited.
     *
     * A role the plan matrix says nothing about — teacher, school, admin — has
     * no allowance to read, and the website's `$limit !== -1 && $count >= $limit`
     * turns that silence into a hard zero: a teacher cannot save a single plan.
     * Silence here means no limit, which is the only reading that does not
     * punish an account for the shape of its plan config.
     */
    private function limitFor(Request $request): int
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return -1;
        }

        $limit = $user->getPlanLimit('saved_plans_limit');

        return $limit === null ? -1 : (int) $limit;
    }
}
