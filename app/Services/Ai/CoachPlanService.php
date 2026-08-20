<?php

namespace App\Services\Ai;

use App\Exceptions\Api\ApiException;
use App\Models\AiCoachingSession;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Practice\PracticeCatalog;

/**
 * The weekly practice plan behind the app's AI Coach.
 *
 * The context is the same one AiCoachController builds for the website —
 * profile, questionnaire answers, the last 20 UserPractice rows and the drills
 * scoring under 60% — because the mobile drill screens write those same rows
 * through PracticeSessionService. What is new is that each plan item carries a
 * `practice_type` slug validated against PracticeCatalog, so a day in the plan
 * is something the app can open rather than a sentence about an exercise.
 *
 * Plans are persisted to ai_coaching_sessions, a table four admin screens
 * already read and nothing has ever written to. That also makes the screen
 * cheap to reopen: a plan is generated once a week, not once per visit.
 *
 * Access is premium-only, same as the website's `plan:ai_coach` route gate.
 * The check lives in AiController, next to the quota check for the chat.
 */
class CoachPlanService
{
    /** A weekly plan is stale once its week is up. */
    public const FRESH_DAYS = 7;

    /**
     * The drills the model may name, and the slug each one maps to.
     *
     * Kept here rather than read from the practices table because these strings
     * are prompt input, not UI: they must stay in English and stay stable even
     * as the localized display names change. AiApiTest asserts this list
     * matches PracticeCatalog exactly, so adding a drill family to the app
     * fails the suite until the coach learns about it too.
     */
    public const DRILL_LABELS = [
        'single-note-practice' => 'Single Note',
        'interval-direction-practice' => 'Interval Direction',
        'interval-comparison-practice' => 'Interval Comparison',
        'melodic-interval-practice' => 'Melodic Intervals',
        'harmonic-interval-practice' => 'Harmonic Intervals',
        'interval-construction-practice' => 'Interval Construction',
        'chord-practice' => 'Chords',
        'scale-practice' => 'Scales & Modes',
        'rhythm-practice' => 'Rhythm',
        'melodic-dictation' => 'Melodic Dictation',
    ];

    private const LOCALES = [
        'tr' => 'Turkish', 'en' => 'English', 'de' => 'German',
        'fr' => 'French', 'es' => 'Spanish', 'it' => 'Italian',
        'pt' => 'Portuguese', 'ru' => 'Russian', 'nl' => 'Dutch',
        'pl' => 'Polish', 'ar' => 'Arabic', 'ja' => 'Japanese',
        'ko' => 'Korean', 'zh' => 'Chinese', 'sv' => 'Swedish',
    ];

    public function __construct(
        private readonly ChatCompletionClient $client,
        private readonly PracticeCatalog $catalog,
    ) {}

    /**
     * The most recent plan, if one was generated inside the freshness window.
     *
     * @return array{plan: array, generated_at: string}|null
     */
    public function latestFor(User $user): ?array
    {
        $session = AiCoachingSession::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(self::FRESH_DAYS))
            ->latest('id')
            ->first();

        if (! $session || ! is_array($session->session_data)) {
            return null;
        }

        return [
            'plan' => $session->session_data,
            'generated_at' => $session->created_at->toIso8601String(),
        ];
    }

    /**
     * @return array{plan: array, generated_at: string}
     *
     * @throws ApiException
     */
    public function generate(User $user): array
    {
        $model = (string) SystemSetting::get('ai_model', 'gpt-4.1-mini');

        $completion = $this->client->complete(
            [
                ['role' => 'system', 'content' => $this->systemPrompt()],
                ['role' => 'user', 'content' => $this->contextFor($user)],
            ],
            [
                'feature' => 'mobile_coach_plan',
                'model' => $model,
                'max_tokens' => (int) SystemSetting::get('ai_max_tokens', 2000),
                'temperature' => (float) SystemSetting::get('ai_temperature', 0.5),
                // Belt to the fence-stripping braces in decode(): asking for a
                // JSON object is what stops the model prefacing it with prose.
                'response_format' => ['type' => 'json_object'],
            ],
        );

        $plan = $this->normalise($this->decode($completion->content));

        $this->persist($user, $plan, $model, $completion->totalTokens);

        return ['plan' => $plan, 'generated_at' => now()->toIso8601String()];
    }

    // --- prompt ------------------------------------------------------------

    private function systemPrompt(): string
    {
        $drills = collect(self::DRILL_LABELS)
            ->map(fn (string $label, string $slug) => "  - \"{$slug}\" — {$label}")
            ->implode("\n");

        return implode("\n", [
            'You are an encouraging ear training coach for Harmoniva.',
            'Create a personalized 7-day practice plan from the supplied profile, survey answers, weak areas and recent practice history.',
            '',
            'Return ONLY a valid JSON object. No markdown, no commentary.',
            '',
            'Every exercise must name one of these drills, using the slug verbatim in `practice_type`:',
            $drills,
            '',
            'Rules that MUST be followed:',
            '- Base the plan only on the provided user context. Never invent progress, scores or features.',
            '- Prioritize the weak areas, but include review and confidence-building work too.',
            '- If the practice history is thin or missing, build a balanced beginner plan from the profile alone.',
            '- Keep each day realistic and sustainable: 10-40 minutes.',
            '- Include lighter review days where it makes sense.',
            '- `title` must be specific — "Descending intervals by ear", not "Intervals".',
            '- `practice_type` must be one of the slugs above and must match the title.',
            '- Respond in the language named by preferred_language. Default to English.',
            '- No medical, psychological or otherwise unrelated advice.',
            '- Keep the tone supportive, practical and teacher-like.',
            '',
            'Return exactly this shape:',
            '{"weekly_plan":[{"day":"Monday","duration_minutes":25,"exercises":[{"title":"...","practice_type":"melodic-interval-practice"}]}],"focus_areas":["...","...","..."],"tips":["...","...","...","..."],"motivation":"..."}',
            '',
            'Requirements: exactly 7 days; 1-3 exercises per day; exactly 3 focus_areas; exactly 4 tips; one short motivation line.',
        ]);
    }

    /** The website's context builder, verbatim in substance. */
    private function contextFor(User $user): string
    {
        $profile = $user->profile;
        $levels = ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'];
        $education = [
            'self_taught' => 'Self-taught', 'private_lessons' => 'Private lessons',
            'music_school' => 'Music school', 'professional' => 'Professional',
        ];

        $locale = $user->locale ?? 'en';

        $lines = [
            "- name: {$user->name}",
            '- preferred_language: '.(self::LOCALES[$locale] ?? 'English')." ({$locale})",
            '- instrument: '.($profile?->primary_instrument ?? 'not specified'),
            '- level: '.($levels[$profile?->musical_level ?? ''] ?? 'unknown'),
            '- education_status: '.($education[$profile?->education_status ?? ''] ?? 'unknown'),
            '- weekly_practice_hours: '.($profile?->weekly_practice_hours ?? 0),
        ];

        if (! empty($profile?->interests)) {
            $interests = is_array($profile->interests) ? implode(', ', $profile->interests) : $profile->interests;
            $lines[] = "- interests: {$interests}";
        }

        if ($profile?->bio) {
            $lines[] = "- bio: {$profile->bio}";
        }

        $parts = ['Create a weekly practice plan for this Harmoniva user.', '', 'User profile:', implode("\n", $lines)];

        $survey = $user->questionnaireResponses()->with('question')->get()
            ->filter(fn ($response) => (bool) $response->question)
            ->map(fn ($response) => "- {$response->question->question_text}: {$response->answer_value}");

        if ($survey->isNotEmpty()) {
            $parts[] = '';
            $parts[] = 'Survey answers:';
            $parts[] = $survey->implode("\n");
        }

        $history = $user->userPractices()->with('practice')->latest()->take(20)->get();
        $weak = [];
        $historyLines = [];

        foreach ($history as $entry) {
            $name = $entry->practice?->name ?? 'Unknown';
            $details = array_filter([
                isset($entry->score) ? "score: {$entry->score}" : null,
                isset($entry->correct_answers) ? "correct: {$entry->correct_answers}" : null,
                isset($entry->total_questions) ? "total: {$entry->total_questions}" : null,
            ]);

            $historyLines[] = "- {$name}".($details ? ' ('.implode(', ', $details).')' : '');

            if (($entry->total_questions ?? 0) > 0
                && isset($entry->correct_answers)
                && ($entry->correct_answers / $entry->total_questions) < 0.6) {
                $weak[] = $name;
            }
        }

        if ($historyLines) {
            $parts[] = '';
            $parts[] = 'Recent practice history (last 20 sessions):';
            $parts[] = implode("\n", $historyLines);
        }

        if ($weak) {
            $parts[] = '';
            $parts[] = 'Detected weak areas (accuracy below 60%):';
            $parts[] = implode(', ', array_unique($weak));
        }

        return implode("\n", $parts);
    }

    // --- response handling --------------------------------------------------

    private function decode(string $content): array
    {
        $content = trim($content);
        $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
        $content = preg_replace('/\s*```$/', '', (string) $content);

        $decoded = json_decode((string) $content, true);

        if (! is_array($decoded)) {
            throw ApiException::generationFailed(
                __('The coach could not put a plan together. Please try again.')
            );
        }

        return $decoded;
    }

    /**
     * Coerces model output into the shape the app renders.
     *
     * The client must never have to defend itself against a malformed plan, so
     * anything unexpected is dropped here rather than shipped: an unrecognised
     * `practice_type` becomes null (the item stays, it just isn't tappable),
     * days beyond the seventh are cut, and a plan with no usable day at all is
     * a generation failure rather than an empty screen.
     */
    private function normalise(array $raw): array
    {
        $days = [];

        foreach (array_slice($this->arrayOf($raw, 'weekly_plan'), 0, 7) as $index => $day) {
            if (! is_array($day)) {
                continue;
            }

            $exercises = [];

            foreach (array_slice($this->arrayOf($day, 'exercises'), 0, 4) as $exercise) {
                // The model occasionally answers with a bare string despite the
                // schema; that is still a usable item, just an unlinked one.
                $title = is_string($exercise)
                    ? $exercise
                    : (is_array($exercise) ? (string) ($exercise['title'] ?? '') : '');

                $title = trim($title);

                if ($title === '') {
                    continue;
                }

                $slug = is_array($exercise) ? ($exercise['practice_type'] ?? null) : null;
                $slug = is_string($slug) ? $slug : null;

                $exercises[] = [
                    'title' => mb_substr($title, 0, 160),
                    'practice_type' => $this->catalog->isKnownSlug($slug) ? $slug : null,
                ];
            }

            if (! $exercises) {
                continue;
            }

            $minutes = (int) ($day['duration_minutes'] ?? 0);

            $days[] = [
                'day' => trim((string) ($day['day'] ?? '')) ?: 'Day '.($index + 1),
                'duration_minutes' => $minutes > 0 ? max(5, min(90, $minutes)) : null,
                'exercises' => $exercises,
            ];
        }

        if (! $days) {
            throw ApiException::generationFailed(
                __('The coach could not put a plan together. Please try again.')
            );
        }

        return [
            'weekly_plan' => $days,
            'focus_areas' => $this->strings($raw, 'focus_areas', 4),
            'tips' => $this->strings($raw, 'tips', 6),
            'motivation' => mb_substr(trim((string) ($raw['motivation'] ?? '')), 0, 400) ?: null,
        ];
    }

    private function arrayOf(array $source, string $key): array
    {
        return is_array($source[$key] ?? null) ? $source[$key] : [];
    }

    /** @return array<int, string> */
    private function strings(array $source, string $key, int $max): array
    {
        return collect($this->arrayOf($source, $key))
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->map(fn (string $value) => mb_substr(trim($value), 0, 240))
            ->take($max)
            ->values()
            ->all();
    }

    private function persist(User $user, array $plan, string $model, int $tokens): void
    {
        try {
            AiCoachingSession::create([
                'user_id' => $user->id,
                'session_data' => $plan,
                'model_used' => $model,
                'tokens_used' => $tokens,
            ]);
        } catch (\Throwable $e) {
            // A plan the learner can read beats a plan the admin report can
            // count — same rule AiUsageLogger follows.
            \Log::error('Failed to persist AI coaching session: '.$e->getMessage());
        }
    }
}
