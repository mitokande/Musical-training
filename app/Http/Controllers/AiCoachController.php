<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use OpenAI;

class AiCoachController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $profile = $user->profile;
        $responses = $user->questionnaireResponses()->with('question')->get();
        $practiceHistory = $user->userPractices()
            ->with('practice')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('ai-coach.index', compact('user', 'profile', 'responses', 'practiceHistory'));
    }

    public function generate(Request $request): JsonResponse
    {
        $apiKey = config('services.openai.key');
        if (!$apiKey) {
            return response()->json(['error' => 'OpenAI API anahtarı yapılandırılmamış.'], 500);
        }

        $user = $request->user();
        $context = $this->buildContext($user);

        try {
            $client = OpenAI::client($apiKey);

            $response = $client->chat()->create([
                'model' => 'gpt-4.1-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => implode("\n", [
                            'You are an encouraging ear training coach for Harmoniva.',
                            'Create a personalized 7-day weekly practice plan based on the provided user profile, survey answers, weak areas, and recent practice history.',
                            '',
                            'Return ONLY valid JSON. Do not include markdown, comments, or any extra text.',
                            '',
                            'Rules that MUST be followed:',
                            '- Base the plan only on the provided user context.',
                            '- Do not invent progress history, weak areas, scores, or features not available in the app.',
                            '- Use only these Harmoniva exercise types: Single Note Practice, Interval Direction Practice, Interval Comparison Practice, Melodic Interval Practice, Harmonic Interval Practice, Interval Construction Practice, Chords, Scales & Modes, Rhythm, Melodic Dictation.',
                            '- Prioritize the user\'s weak areas, but include some review and confidence-building exercises.',
                            '- If recent practice history is limited or missing, create a balanced beginner-friendly plan based on profile and survey answers only.',
                            '- Keep daily practice realistic and sustainable (10–40 minutes per day).',
                            '- Include lighter review days when appropriate.',
                            '- Make exercises specific — for example: "Interval Direction Practice: descending intervals" instead of just "Intervals".',
                            '- Do not include medical, psychological, or unrelated advice.',
                            '- Respond in the language specified by the user\'s preferred_language field. If not provided, use English.',
                            '- Keep the tone supportive, practical, and teacher-like.',
                            '',
                            'JSON structure (return exactly this):',
                            '{"weekly_plan":[{"day":"...","exercises":["..."],"duration_minutes":30}],"focus_areas":["...","...","..."],"tips":["...","...","...","..."],"motivation":"..."}',
                            '',
                            'Requirements:',
                            '- weekly_plan must contain exactly 7 days.',
                            '- focus_areas must contain exactly 3 items.',
                            '- tips must contain exactly 4 items.',
                            '- motivation must be one short encouraging message.',
                        ]),
                    ],
                    [
                        'role' => 'user',
                        'content' => $context,
                    ],
                ],
                'max_tokens' => 2000,
                'temperature' => 0.5,
            ]);

            $content = $response->choices[0]->message->content;

            // Strip markdown code fences if present
            $content = preg_replace('/^```(?:json)?\s*/i', '', trim($content));
            $content = preg_replace('/\s*```$/', '', $content);

            $plan = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['error' => 'AI yanıtı işlenemedi. Lütfen tekrar deneyin.'], 422);
            }

            return response()->json(['plan' => $plan]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Bir hata oluştu: ' . $e->getMessage()], 500);
        }
    }

    private function buildContext(User $user): string
    {
        $localeMap = [
            'tr' => 'Turkish',  'en' => 'English',    'de' => 'German',
            'fr' => 'French',   'es' => 'Spanish',    'it' => 'Italian',
            'pt' => 'Portuguese', 'ru' => 'Russian',  'nl' => 'Dutch',
            'pl' => 'Polish',   'ar' => 'Arabic',     'ja' => 'Japanese',
            'ko' => 'Korean',   'zh' => 'Chinese',    'sv' => 'Swedish',
        ];

        $profile  = $user->profile;
        $levelMap = ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'];
        $eduMap   = ['self_taught' => 'Self-taught', 'private_lessons' => 'Private lessons', 'music_school' => 'Music school', 'professional' => 'Professional'];
        $locale   = $user->locale ?? 'en';
        $language = $localeMap[$locale] ?? 'English';

        // --- User profile block ---
        $profileLines = [
            "- name: {$user->name}",
            "- preferred_language: {$language} ({$locale})",
            '- instrument: '       . ($profile?->primary_instrument ?? 'not specified'),
            '- level: '            . ($levelMap[$profile?->musical_level ?? ''] ?? 'unknown'),
            '- education_status: ' . ($eduMap[$profile?->education_status ?? ''] ?? 'unknown'),
            '- weekly_practice_hours: ' . ($profile?->weekly_practice_hours ?? 0),
        ];
        if (!empty($profile?->interests)) {
            $interests = is_array($profile->interests) ? implode(', ', $profile->interests) : $profile->interests;
            $profileLines[] = "- interests: {$interests}";
        }
        if ($profile?->bio) {
            $profileLines[] = "- bio: {$profile->bio}";
        }

        // --- Survey answers block ---
        $surveyLines = [];
        $responses = $user->questionnaireResponses()->with('question')->get();
        foreach ($responses as $response) {
            if ($response->question) {
                $surveyLines[] = "- {$response->question->question_text}: {$response->answer_value}";
            }
        }

        // --- Practice history block ---
        $practiceHistory = $user->userPractices()
            ->with('practice')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $historyLines  = [];
        $weakPractices = [];
        foreach ($practiceHistory as $up) {
            $name    = $up->practice?->name ?? 'Unknown';
            $details = array_filter([
                isset($up->score)           ? "score: {$up->score}"            : null,
                isset($up->correct_answers) ? "correct: {$up->correct_answers}" : null,
                isset($up->total_questions) ? "total: {$up->total_questions}"   : null,
            ]);
            $historyLines[] = "- {$name}" . (count($details) ? ' (' . implode(', ', $details) . ')' : '');

            if (isset($up->total_questions) && $up->total_questions > 0 && isset($up->correct_answers)) {
                if (($up->correct_answers / $up->total_questions) < 0.6) {
                    $weakPractices[] = $name;
                }
            }
        }
        $weakPractices = array_unique($weakPractices);

        // --- Assemble ---
        $parts = [
            'Create a weekly practice plan for this Harmoniva user.',
            '',
            'User profile:',
            implode("\n", $profileLines),
        ];

        if (!empty($surveyLines)) {
            $parts[] = '';
            $parts[] = 'Survey answers:';
            $parts[] = implode("\n", $surveyLines);
        }

        if (!empty($historyLines)) {
            $parts[] = '';
            $parts[] = 'Recent practice history (last 20 sessions):';
            $parts[] = implode("\n", $historyLines);
        }

        if (!empty($weakPractices)) {
            $parts[] = '';
            $parts[] = 'Detected weak areas (accuracy < 60%):';
            $parts[] = implode(', ', $weakPractices);
        }

        $parts[] = '';
        $parts[] = 'App constraints:';
        $parts[] = '- Available exercise types: Single Note Practice, Interval Direction Practice, Interval Comparison Practice, Melodic Interval Practice, Harmonic Interval Practice, Interval Construction Practice, Chords, Scales & Modes, Rhythm, Melodic Dictation.';
        $parts[] = '- Keep the plan realistic and suitable for the user\'s level.';

        return implode("\n", $parts);
    }
}
