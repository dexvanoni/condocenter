<?php

namespace App\Services\Finance\Ai;

final class AiResponse
{
    public function __construct(
        public readonly string $content,
        public readonly string $provider,
        public readonly string $model,
        public readonly ?int $inputTokens = null,
        public readonly ?int $outputTokens = null,
        public readonly ?int $totalTokens = null,
        public readonly ?float $estimatedCost = null,
        public readonly ?int $responseTimeMs = null,
        public readonly ?string $finishReason = null,
    ) {}

    /**
     * Formato legado consumido pelo FinanceAiAdvisorService.
     *
     * @return array{
     *   content: string,
     *   provider: string,
     *   model: string,
     *   input_tokens: int|null,
     *   output_tokens: int|null,
     *   total_tokens: int|null,
     *   estimated_cost: float|null,
     *   response_time_ms: int|null,
     *   finish_reason: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'provider' => $this->provider,
            'model' => $this->model,
            'input_tokens' => $this->inputTokens,
            'output_tokens' => $this->outputTokens,
            'total_tokens' => $this->totalTokens,
            'estimated_cost' => $this->estimatedCost,
            'response_time_ms' => $this->responseTimeMs,
            'finish_reason' => $this->finishReason,
        ];
    }
}
