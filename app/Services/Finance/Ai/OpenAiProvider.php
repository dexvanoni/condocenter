<?php

namespace App\Services\Finance\Ai;

use App\Services\Finance\OpenAiChatClient;
use App\Support\FinanceAiProvider;

/**
 * Encapsula o cliente OpenAI já existente — comportamento preservado.
 */
class OpenAiProvider implements AiProviderInterface
{
    public function __construct(
        private OpenAiChatClient $client,
    ) {}

    public function providerKey(): string
    {
        return FinanceAiProvider::OPENAI;
    }

    public function generate(string $systemPrompt, string $userPrompt, array $options = []): AiResponse
    {
        $started = microtime(true);
        $model = isset($options['model']) && is_string($options['model']) && $options['model'] !== ''
            ? $options['model']
            : null;
        $maxTokens = isset($options['max_tokens']) && is_numeric($options['max_tokens'])
            ? (int) $options['max_tokens']
            : null;

        $raw = $this->client->chat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ], $maxTokens, $model);

        return new AiResponse(
            content: $raw['content'],
            provider: $this->providerKey(),
            model: $raw['model'],
            inputTokens: $raw['input_tokens'],
            outputTokens: $raw['output_tokens'],
            totalTokens: $raw['total_tokens'],
            estimatedCost: $raw['estimated_cost'],
            responseTimeMs: (int) round((microtime(true) - $started) * 1000),
            finishReason: $raw['finish_reason'] ?? null,
        );
    }
}
