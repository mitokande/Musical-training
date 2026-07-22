<?php

namespace App\Http\Controllers;

use App\Models\ExerciseSession;
use App\Models\ExerciseSetupTemplate;
use App\Models\UserPractice;
use App\Services\AiUsageLogger;
use App\Services\UsageQuotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use OpenAI;

class ExerciseSetupController extends Controller
{
    private const EXERCISE_SLUGS = [
        'melodic-intervals' => 'melodic-interval-practice',
        'harmonic-intervals' => 'harmonic-interval-practice',
        'intervals-direction' => 'interval-direction-practice',
        'intervals-construction' => 'interval-construction-practice',
        'interval-comparison' => 'interval-comparison-practice',
        'single-note' => 'single-note-practice',
        'chords' => 'chord-practice',
        'scales' => 'scale-practice',
        'rhythm' => 'rhythm-practice',
        'melodic-dictation' => 'melodic-dictation',
        'piano-practice' => 'piano-studio',
    ];

    public function index(Request $request): View
    {
        $user = $request->user();
        $templates = $user
            ? $user->exerciseSetupTemplates()->orderByDesc('is_favorite')->orderByDesc('updated_at')->get()
            : collect();
        $recentSessions = $user
            ? $user->exerciseSessions()->orderByDesc('started_at')->take(5)->get()
            : collect();
        $isPremium = $user?->isEffectivelyPremium() ?? false;
        $savedPlansLimit = $user ? $user->getPlanLimit('saved_plans_limit') : 0;
        $preselectedType = $request->query('type');

        // Daily studio session quota for the limit badge / CTA.
        $usage = app(UsageQuotaService::class);
        if (! $user) {
            $studioQuota = [
                'limit' => (int) config('plans.guest.studio_daily_sessions', 2),
                'used' => $usage->guestUsed($request, UsageQuotaService::FEATURE_STUDIO_SESSIONS),
                'guest' => true,
            ];
        } elseif ($user->isAdmin() || $isPremium) {
            $studioQuota = ['limit' => -1, 'used' => 0, 'guest' => false];
        } else {
            $limit = (int) ($user->getPlanLimit('studio_daily_sessions') ?? -1);
            $studioQuota = [
                'limit' => $limit,
                'used' => $limit === -1 ? 0 : $usage->userUsed($user, UsageQuotaService::FEATURE_STUDIO_SESSIONS),
                'guest' => false,
            ];
        }

        return view('exercise-setup', compact(
            'templates', 'recentSessions', 'isPremium', 'savedPlansLimit', 'preselectedType', 'studioQuota'
        ));
    }

    public function launch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'exercise_type' => 'required|string',
            'question_count' => 'required|integer|min:5|max:20',
            'ai_mode' => 'boolean',
            'settings' => 'required|string',
            'template_id' => 'nullable|integer',
        ]);

        // Difficulty selection was removed from the setup UI; a fixed neutral
        // value is stored for the session payload and the audit record.
        $difficulty = 'intermediate';

        $user = $request->user();
        $settings = json_decode($validated['settings'], true) ?? [];
        $aiMode = ($validated['ai_mode'] ?? false) && ($user?->isEffectivelyPremium() ?? false);

        $slug = self::EXERCISE_SLUGS[$validated['exercise_type']] ?? null;
        if (! $slug) {
            return back()->withErrors(['exercise_type' => 'Geçersiz egzersiz türü.']);
        }

        $usage = app(UsageQuotaService::class);
        $questionCount = (int) $validated['question_count'];

        // Session-size cap: guests and free plans launch 5-question sessions.
        $cap = $user
            ? (int) ($user->getPlanLimit('session_question_cap') ?? -1)
            : (int) config('plans.guest.session_question_cap', 5);
        if (! ($user?->isAdmin()) && $cap !== -1) {
            $questionCount = min($questionCount, $cap);
        }

        // Daily studio session quota. Guests: plans.guest.studio_daily_sessions
        // per day (server-side guest id); free plans: studio_daily_sessions/day.
        if (! $user) {
            if ($usage->guestRemaining($request, UsageQuotaService::FEATURE_STUDIO_SESSIONS, 'studio_daily_sessions') <= 0) {
                return back()->with('studio_limit_reached', 'guest');
            }
        } elseif (! $user->isAdmin() && ! $user->isEffectivelyPremium()) {
            if ($usage->userRemaining($user, UsageQuotaService::FEATURE_STUDIO_SESSIONS, 'studio_daily_sessions') <= 0) {
                return back()->with('studio_limit_reached', 'free');
            }
        }

        $sessionData = array_merge($settings, [
            'exercise_type' => $validated['exercise_type'],
            'difficulty' => $difficulty,
            'question_count' => $questionCount,
            'ai_mode' => $aiMode,
        ]);

        // Store settings in session for the practice component to consume.
        // Clear any stale LP session so it cannot intercept answer checks.
        session(['exercise_settings' => $sessionData]);
        session(['exercise_back_url' => '/exercise-setup']);
        session()->forget('learning_path_session');

        // Consume one session from the daily quota (limited accounts only).
        if (! $user) {
            $usage->guestIncrement($request, UsageQuotaService::FEATURE_STUDIO_SESSIONS);
        } elseif (! $user->isAdmin() && ! $user->isEffectivelyPremium()) {
            $usage->userIncrement($user, UsageQuotaService::FEATURE_STUDIO_SESSIONS);
        }

        // Create audit record only for authenticated users
        if ($user) {
            ExerciseSession::create([
                'user_id' => $user->id,
                'template_id' => $validated['template_id'] ?? null,
                'exercise_type' => $validated['exercise_type'],
                'difficulty' => $difficulty,
                'question_count' => $questionCount,
                'ai_mode' => $aiMode,
                'settings_json' => $sessionData,
            ]);
        }

        if ($slug === 'piano-studio') {
            return redirect()->route('piano-studio');
        }

        return redirect("/practice/{$slug}");
    }

    public function saveTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'exercise_type' => 'required|string|max:50',
            'settings_json' => 'required|array',
            'is_ai_generated' => 'boolean',
        ]);

        $user = $request->user();
        $limit = $user->getPlanLimit('saved_plans_limit');

        if ($limit !== -1 && $user->exerciseSetupTemplates()->count() >= $limit) {
            return response()->json([
                'success' => false,
                'message' => "Ücretsiz planda maksimum {$limit} plan kaydedebilirsiniz. Sınırsız plan için Premium'a geçin.",
            ], 403);
        }

        $template = ExerciseSetupTemplate::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'category' => $validated['category'],
            'exercise_type' => $validated['exercise_type'],
            'settings_json' => $validated['settings_json'],
            'is_ai_generated' => $validated['is_ai_generated'] ?? false,
        ]);

        return response()->json(['success' => true, 'template' => $template]);
    }

    public function deleteTemplate(Request $request, ExerciseSetupTemplate $template): JsonResponse
    {
        if ($template->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.'], 403);
        }

        $template->delete();

        return response()->json(['success' => true]);
    }

    public function toggleFavorite(Request $request, ExerciseSetupTemplate $template): JsonResponse
    {
        if ($template->user_id !== $request->user()->id) {
            return response()->json(['success' => false], 403);
        }

        $template->update(['is_favorite' => ! $template->is_favorite]);

        return response()->json(['success' => true, 'is_favorite' => $template->is_favorite]);
    }

    public function aiRecommend(Request $request): JsonResponse
    {
        $apiKey = config('services.openai.key');
        if (! $apiKey) {
            return response()->json(['error' => 'OpenAI API anahtarı yapılandırılmamış.'], 500);
        }

        $user = $request->user();
        $context = $this->buildRecommendationContext($user);
        $model = 'gpt-4.1-mini';
        $start = microtime(true);

        try {
            $client = OpenAI::client($apiKey);

            $response = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Sen bir müzik kulağı eğitimi koçusun. Kullanıcının profili, geçmiş pratik verileri ve zayıf noktaları doğrultusunda kişiselleştirilmiş egzersiz ayarları öner. Türkçe yanıt ver. Yanıtını YALNIZCA geçerli JSON formatında ver, başka hiçbir metin ekleme.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $context,
                    ],
                ],
                'response_format' => [
                    'type' => 'json_schema',
                    'json_schema' => [
                        'name' => 'ExerciseRecommendation',
                        'strict' => true,
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'exercise_type' => ['type' => 'string'],
                                'category' => ['type' => 'string'],
                                'settings' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'difficulty' => ['type' => 'string'],
                                        'question_count' => ['type' => 'integer'],
                                        'direction' => ['type' => 'string'],
                                        'interval_pool' => ['type' => 'array', 'items' => ['type' => 'string']],
                                        'replay_limit' => ['type' => 'string'],
                                        'clef' => ['type' => 'string'],
                                        'chord_types' => ['type' => 'array', 'items' => ['type' => 'string']],
                                        'scale_types' => ['type' => 'array', 'items' => ['type' => 'string']],
                                        'time_signature' => ['type' => 'string'],
                                    ],
                                    'required' => ['difficulty', 'question_count'],
                                    'additionalProperties' => false,
                                ],
                                'explanation' => ['type' => 'string'],
                            ],
                            'required' => ['exercise_type', 'category', 'settings', 'explanation'],
                            'additionalProperties' => false,
                        ],
                    ],
                ],
                'max_tokens' => 1000,
                'temperature' => 0.6,
            ]);

            AiUsageLogger::logSuccess('exercise_setup_recommend', $model, $response->usage, $user->id, [], (int) ((microtime(true) - $start) * 1000));

            $result = json_decode($response->choices[0]->message->content, true);

            return response()->json(['success' => true, 'recommendation' => $result]);
        } catch (\Exception $e) {
            AiUsageLogger::logError('exercise_setup_recommend', $model, $e->getMessage(), $user->id, [], (int) ((microtime(true) - $start) * 1000));
            \Log::error('ExerciseSetup AI recommend error: '.$e->getMessage());

            return response()->json(['error' => 'AI önerisi alınamadı. Lütfen tekrar deneyin.'], 500);
        }
    }

    private function buildRecommendationContext($user): string
    {
        $profile = $user->profile;
        $recentPractices = UserPractice::where('user_id', $user->id)
            ->with('practice')
            ->orderByDesc('updated_at')
            ->take(10)
            ->get();

        $practiceLines = $recentPractices->map(function ($up) {
            $total = $up->total_questions > 0 ? $up->total_questions : 1;
            $accuracy = round(($up->correct_answers / $total) * 100);

            return "- {$up->practice->name}: %{$accuracy} doğruluk";
        })->implode("\n");

        $level = $profile?->musical_level ?? 'bilinmiyor';
        $instrument = $profile?->primary_instrument ?? 'belirtilmemiş';
        $weeklyHours = $profile?->weekly_practice_hours ?? 0;

        return "Kullanıcı bilgileri:\n"
            ."- Müzik seviyesi: {$level}\n"
            ."- Ana enstrüman: {$instrument}\n"
            ."- Haftalık pratik süresi: {$weeklyHours} saat\n\n"
            ."Son pratik performansları:\n{$practiceLines}\n\n"
            .'Bu verilere dayanarak, kullanıcının en çok gelişmeye ihtiyaç duyduğu alanda kişiselleştirilmiş bir egzersiz ayarı öner. '
            .'Mevcut egzersiz tipleri: melodic-intervals, harmonic-intervals, intervals-direction, intervals-construction, interval-comparison, chords, scales, rhythm, melodic-dictation. '
            .'Kategori adları: Melodic Intervals, Harmonic Intervals, Interval Direction, Interval Construction, Interval Comparison, Chords, Scales, Rhythm, Melodic Dictation.';
    }

    private function slugToPracticeId(string $slug): ?int
    {
        $map = [
            'single-note-practice' => 1,
            'interval-direction-practice' => 2,
            'interval-comparison-practice' => 3,
            'melodic-interval-practice' => 4,
            'harmonic-interval-practice' => 5,
            'interval-construction-practice' => 6,
        ];

        return $map[$slug] ?? null;
    }
}
