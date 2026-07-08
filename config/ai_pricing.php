<?php

// Estimated OpenAI chat-completion pricing in USD per 1,000,000 tokens.
// These are published list prices, not actual billing data (the API never
// returns real cost) — check platform.openai.com/pricing and update this
// file if OpenAI reprices. Models not listed here are logged with tokens
// but a null estimated cost.
return [
    'gpt-4.1-mini' => ['input' => 0.40, 'output' => 1.60],
    'gpt-4.1' => ['input' => 2.00, 'output' => 8.00],
    'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
    'gpt-4o' => ['input' => 2.50, 'output' => 10.00],
    'gpt-3.5-turbo' => ['input' => 0.50, 'output' => 1.50],
];
