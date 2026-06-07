<?php

namespace App\Livewire;

use App\Models\ExerciseSetupTemplate;
use App\Models\UserPractice;
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

        if (!$user->isPremium()) {
            $this->aiError = 'AI önerileri Premium üyelere özeldir.';
            return;
        }

        $apiKey = config('services.openai.key');
        if (!$apiKey) {
            $this->aiError = 'OpenAI API anahtarı yapılandırılmamış.';
            return;
        }

        $this->aiLoading = true;
        $this->aiError = null;
        $this->aiRecommendation = null;

        try {
            $context = $this->buildContext($user);
            $client = OpenAI::client($apiKey);

            $response = $client->chat()->create([
                'model' => 'gpt-4.1-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'Sen bir müzik kulağı eğitimi koçusun. Kullanıcının profili, geçmiş pratik verileri ve zayıf noktaları doğrultusunda kişiselleştirilmiş egzersiz ayarları öner. Türkçe yanıt ver. Sadece JSON döndür.'],
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

            $this->aiRecommendation = json_decode($response->choices[0]->message->content, true);
        } catch (\Exception $e) {
            $this->aiError = 'AI önerisi alınamadı. Lütfen tekrar deneyin.';
            \Log::error('ExerciseSetupStudio AI error: ' . $e->getMessage());
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
            $template->update(['is_favorite' => !$template->is_favorite]);
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
            return "- {$up->practice->name}: %{$accuracy} doğruluk";
        })->implode("\n");

        $level = $profile?->musical_level ?? 'bilinmiyor';
        $instrument = $profile?->primary_instrument ?? 'belirtilmemiş';
        $weeklyHours = $profile?->weekly_practice_hours ?? 0;

        return "Kullanıcı bilgileri:\n- Müzik seviyesi: {$level}\n- Ana enstrüman: {$instrument}\n- Haftalık pratik süresi: {$weeklyHours} saat\n\nSon pratik performansları:\n{$practiceLines}\n\nBu verilere dayanarak, kullanıcının en çok gelişmeye ihtiyaç duyduğu alanda kişiselleştirilmiş bir egzersiz ayarı öner. Mevcut egzersiz tipleri: melodic-intervals, harmonic-intervals, intervals-direction, intervals-construction, interval-comparison, chords, scales, rhythm, melodic-dictation.";
    }
}
