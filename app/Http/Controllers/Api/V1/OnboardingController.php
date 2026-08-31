<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Onboarding\MobileOnboardingProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The mobile app's onboarding answers, on their way into the learner profile
 * and the survey the website keeps.
 *
 * Everything interesting happens in MobileOnboardingProfile; this only decides
 * what a valid set of answers looks like. The vocabulary is the app's own —
 * `theory`, `piano`, `fluent` — because the survey's option strings are rows in
 * this database and mapping them client-side would tie a seeder edit to an app
 * release.
 *
 * `topics` is validated key by key rather than as a free-form map: an answer
 * for a topic the app has stopped asking about, or one the survey has no
 * question for, is not something to accept quietly.
 */
class OnboardingController extends Controller
{
    public function store(Request $request, MobileOnboardingProfile $onboarding): JsonResponse
    {
        $familiarity = ['string', Rule::in(MobileOnboardingProfile::FAMILIARITY)];

        $validated = $request->validate([
            'goals' => ['required', 'array', 'min:1'],
            'goals.*' => ['string', Rule::in(MobileOnboardingProfile::GOALS)],
            'instruments' => ['required', 'array', 'min:1'],
            'instruments.*' => ['string', Rule::in(MobileOnboardingProfile::INSTRUMENTS)],
            'topics' => ['sometimes', 'array:notation,intervals,chords,scales,rhythm'],
            'topics.notation' => $familiarity,
            'topics.intervals' => $familiarity,
            'topics.chords' => $familiarity,
            'topics.scales' => $familiarity,
            'topics.rhythm' => $familiarity,
            // The app offers 5, 10, 15 and 20, but the range is what matters
            // here — a later tier should not need a deploy on this side.
            'minutes_per_day' => ['required', 'integer', 'min:1', 'max:600'],
            'completed_at' => ['sometimes', 'nullable', 'date'],
        ]);

        return response()->json([
            'data' => $onboarding->apply($request->user(), $validated),
        ]);
    }
}
