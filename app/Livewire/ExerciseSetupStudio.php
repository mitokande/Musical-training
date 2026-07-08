<?php

namespace App\Livewire;

use App\Models\ExerciseSetupTemplate;
use App\Models\UserPractice;
use App\Services\AiUsageLogger;
use Livewire\Component;
use OpenAI;

class ExerciseSetupStudio extends Component
{
    public $templates = [];

    public $isPremium = false;

    public $savedPlansLimit = 3;

    public $aiRecommendation = null;

    public $aiLoading = false;

    public $aiError = null;

    public function mount()
    {
        $user = auth()->user();
        $this->isPremium = $user?->isPremium() ?? false;
        $this->savedPlansLimit = $user ? $user->getPlanLimit('saved_plans_limit') : 3;
        $this->loadTemplates();
    }

    public function loadTemplates(): void
    {
        $user = auth()->user();
        $this->templates = $user
            ? $user->exerciseSetupTemplates()
                ->orderByDesc('is_favorite')
                ->orderByDesc('updated_at')
                ->get()
                ->toArray()
            : [];
    }

    public function getAiRecommendation(): void
    {
        $user = auth()->user();

        if (! $user->isPremium()) {
            $this->aiError = __('app.exercises.ai_premium_only');

            return;
        }

        $apiKey = config('services.openai.key');
        if (! $apiKey) {
            $this->aiError = __('app.exercises.ai_not_configured');

            return;
        }

        $this->aiLoading = true;
        $this->aiError = null;
        $this->aiRecommendation = null;

        $model = 'gpt-4.1-mini';
        $start = microtime(true);

        try {
            $context = $this->buildContext($user);
            $client = OpenAI::client($apiKey);

            $response = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => "You are an ear-training coach for musicians. Based on the user's profile, recent practice data, and weak points, recommend personalized exercise settings. Respond in {$this->aiResponseLanguage()}. Return JSON only."],
                    ['role' => 'user', 'content' => $context],
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
                'max_tokens' => 800,
                'temperature' => 0.6,
            ]);

            AiUsageLogger::logSuccess('exercise_setup_studio_recommend', $model, $response->usage, $user->id, [], (int) ((microtime(true) - $start) * 1000));

            $this->aiRecommendation = json_decode($response->choices[0]->message->content, true);
        } catch (\Exception $e) {
            AiUsageLogger::logError('exercise_setup_studio_recommend', $model, $e->getMessage(), $user->id, [], (int) ((microtime(true) - $start) * 1000));
            $this->aiError = __('app.exercises.ai_failed');
            \Log::error('ExerciseSetupStudio AI error: '.$e->getMessage());
        } finally {
            $this->aiLoading = false;
        }
    }

    public function deletePlan(int $templateId): void
    {
        $template = ExerciseSetupTemplate::find($templateId);
        if ($template && $template->user_id === auth()->id()) {
            $template->delete();
            $this->loadTemplates();
        }
    }

    public function toggleFavorite(int $templateId): void
    {
        $template = ExerciseSetupTemplate::find($templateId);
        if ($template && $template->user_id === auth()->id()) {
            $template->update(['is_favorite' => ! $template->is_favorite]);
            $this->loadTemplates();
        }
    }

    public function render()
    {
        return view('livewire.exercise-setup-studio');
    }

    private function buildContext($user): string
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

            return "- {$up->practice->name}: {$accuracy}% accuracy";
        })->implode("\n");

        $level = $profile?->musical_level ?? 'unknown';
        $instrument = $profile?->primary_instrument ?? 'unspecified';
        $weeklyHours = $profile?->weekly_practice_hours ?? 0;

        return "User info:\n- Musical level: {$level}\n- Primary instrument: {$instrument}\n- Weekly practice time: {$weeklyHours} hours\n\nRecent practice performance:\n{$practiceLines}\n\nBased on this data, recommend a personalized exercise setup for the area the user most needs to improve. Available exercise types: melodic-intervals, harmonic-intervals, intervals-direction, intervals-construction, interval-comparison, chords, scales, rhythm, melodic-dictation.";
    }

    private function aiResponseLanguage(): string
    {
        return [
            'en' => 'English', 'tr' => 'Turkish', 'es' => 'Spanish', 'de' => 'German',
            'fr' => 'French', 'pt' => 'Portuguese', 'it' => 'Italian', 'ar' => 'Arabic',
            'ja' => 'Japanese', 'ko' => 'Korean', 'nl' => 'Dutch', 'pl' => 'Polish',
            'ru' => 'Russian', 'sv' => 'Swedish', 'zh' => 'Chinese',
        ][app()->getLocale()] ?? 'English';
    }
}
