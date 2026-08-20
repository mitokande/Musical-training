<?php

namespace App\Services\Ai;

use App\Exceptions\Api\ApiException;

/**
 * The one seam between the AI features and the model vendor.
 *
 * The web controllers (AiChatController, AiCoachController) call
 * `OpenAI::client()` inline, which is why neither has a test: there is nowhere
 * to stand between the controller and the network. The mobile surface goes
 * through this interface instead, bound to OpenAiChatCompletionClient in
 * AppServiceProvider and swapped for a fake in tests.
 *
 * It is also where a second provider would be added, should the admin model
 * switch ever need to offer one.
 */
interface ChatCompletionClient
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options  feature, model, max_tokens, temperature, response_format
     *
     * @throws ApiException when the model is unreachable or refuses
     */
    public function complete(array $messages, array $options): ChatCompletion;
}
