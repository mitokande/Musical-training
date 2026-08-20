<?php

namespace App\Services\Ai;

/**
 * One chat-completion answer, decoupled from the vendor response object.
 *
 * The AI services depend on this rather than on OpenAI's own DTO so a test can
 * hand them a canned answer without a network call or an API key — see
 * ChatCompletionClient for why that indirection exists at all.
 */
readonly class ChatCompletion
{
    public function __construct(
        public string $content,
        public int $promptTokens = 0,
        public int $completionTokens = 0,
        public int $totalTokens = 0,
    ) {}
}
