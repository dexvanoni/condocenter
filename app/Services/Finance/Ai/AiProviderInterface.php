<?php

namespace App\Services\Finance\Ai;

interface AiProviderInterface
{
    /**
     * @param  array{model?: string, max_tokens?: int}  $options
     */
    public function generate(string $systemPrompt, string $userPrompt, array $options = []): AiResponse;

    public function providerKey(): string;
}
