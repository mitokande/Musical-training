<?php

namespace App\Services\Ai;

use App\Exceptions\Api\ApiException;
use App\Services\AiUsageLogger;
use OpenAI;

/**
 * The production ChatCompletionClient: OpenAI chat completions, with every call
 * written to the same ai_usage_logs ledger the web features use.
 *
 * Usage logging lives here rather than in the calling service so a third AI
 * feature cannot forget to meter itself — the only way to reach the model is
 * through this class, and this class always logs. `$options['feature']` is the
 * label the row carries; `surface: mobile` in the meta separates these rows
 * from the website's in the admin report.
 */
class OpenAiChatCompletionClient implements ChatCompletionClient
{
    public function complete(array $messages, array $options): ChatCompletion
    {
        $apiKey = config('services.openai.key');
        $model = $options['model'] ?? 'gpt-4.1-mini';
        $feature = $options['feature'] ?? 'mobile_ai';

        if (! $apiKey) {
            // Deliberately not generation_failed: nothing the learner did caused
            // this, and retrying will not help until someone sets the key.
            throw $this->unavailable();
        }

        $started = microtime(true);

        try {
            $response = OpenAI::client($apiKey)->chat()->create(array_filter([
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => $options['max_tokens'] ?? null,
                'temperature' => $options['temperature'] ?? null,
                'response_format' => $options['response_format'] ?? null,
            ], fn ($value) => $value !== null));
        } catch (\Throwable $e) {
            AiUsageLogger::logError(
                $feature,
                $model,
                $e->getMessage(),
                auth()->id(),
                ['surface' => 'mobile'],
                $this->elapsed($started),
            );

            throw $this->unavailable();
        }

        AiUsageLogger::logSuccess(
            $feature,
            $model,
            $response->usage,
            auth()->id(),
            ['surface' => 'mobile'],
            $this->elapsed($started),
        );

        return new ChatCompletion(
            content: $response->choices[0]->message->content ?? '',
            promptTokens: $response->usage->promptTokens,
            completionTokens: $response->usage->completionTokens ?? 0,
            totalTokens: $response->usage->totalTokens,
        );
    }

    private function unavailable(): ApiException
    {
        return new ApiException(
            'ai_unavailable',
            __('The AI assistant is not available right now. Please try again in a moment.'),
            503,
        );
    }

    private function elapsed(float $started): int
    {
        return (int) ((microtime(true) - $started) * 1000);
    }
}
