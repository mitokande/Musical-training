<?php

namespace App\Services;

use App\Models\AiUsageLog;
use OpenAI\Responses\Chat\CreateResponseUsage;

/**
 * Single entry point for recording OpenAI chat-completion usage across every
 * call site (AIController, AiChatController, AiCoachController,
 * ExerciseSetupController, ExerciseSetupStudio, TeacherAiHomeworkService).
 * Never throws — a logging failure must not break the underlying AI feature.
 */
class AiUsageLogger
{
    public static function logSuccess(
        string $feature,
        string $model,
        CreateResponseUsage $usage,
        ?int $userId = null,
        array $meta = [],
        ?int $durationMs = null
    ): void {
        self::write([
            'user_id' => $userId,
            'feature' => $feature,
            'model' => $model,
            'prompt_tokens' => $usage->promptTokens,
            'completion_tokens' => $usage->completionTokens,
            'total_tokens' => $usage->totalTokens,
            'cost_usd' => self::estimateCost($model, $usage->promptTokens, $usage->completionTokens ?? 0),
            'status' => 'success',
            'duration_ms' => $durationMs,
            'meta' => $meta ?: null,
        ]);
    }

    public static function logError(
        string $feature,
        string $model,
        string $errorMessage,
        ?int $userId = null,
        array $meta = [],
        ?int $durationMs = null
    ): void {
        self::write([
            'user_id' => $userId,
            'feature' => $feature,
            'model' => $model,
            'status' => 'error',
            'error_message' => mb_substr($errorMessage, 0, 2000),
            'duration_ms' => $durationMs,
            'meta' => $meta ?: null,
        ]);
    }

    private static function estimateCost(string $model, int $promptTokens, int $completionTokens): ?float
    {
        $rates = config('ai_pricing')[$model] ?? null;
        if (! $rates) {
            return null;
        }

        return round(
            ($promptTokens / 1_000_000) * $rates['input']
            + ($completionTokens / 1_000_000) * $rates['output'],
            6
        );
    }

    private static function write(array $attributes): void
    {
        try {
            AiUsageLog::create($attributes);
        } catch (\Exception $e) {
            \Log::error('Failed to write AI usage log: '.$e->getMessage());
        }
    }
}
