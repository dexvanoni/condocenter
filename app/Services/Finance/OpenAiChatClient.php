<?php

namespace App\Services\Finance;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OpenAiChatClient
{
    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @return array{
     *   content: string,
     *   model: string,
     *   input_tokens: int|null,
     *   output_tokens: int|null,
     *   total_tokens: int|null,
     *   estimated_cost: float|null,
     *   finish_reason: string|null
     * }
     */
    public function chat(array $messages, ?int $maxTokens = null, ?string $modelOverride = null): array
    {
        $apiKey = config('services.openai.api_key');
        $model = $modelOverride !== null && $modelOverride !== ''
            ? $modelOverride
            : (string) config('services.openai.model', 'gpt-5.6-luna');
        $timeout = (int) config('services.openai.timeout', 30);

        if (! $apiKey) {
            throw new RuntimeException('OPENAI_API_KEY ausente.');
        }

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.3,
            'response_format' => ['type' => 'json_object'],
        ];

        $maxTokens ??= (int) config('finance_ai.max_output_tokens', 2000);
        if ($maxTokens > 0) {
            $payload['max_tokens'] = $maxTokens;
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout($timeout)
                ->acceptJson()
                ->asJson()
                ->post('https://api.openai.com/v1/chat/completions', $payload)
                ->throw();
        } catch (ConnectionException $e) {
            Log::warning('finance_ai.openai_timeout', [
                'model' => $model,
                'message' => $e->getMessage(),
            ]);
            throw new RuntimeException('Timeout ao contactar a API de IA.', 0, $e);
        } catch (RequestException $e) {
            $status = $e->response?->status();
            $body = $this->safeBody($e->response?->body());

            Log::warning('finance_ai.openai_http_error', [
                'model' => $model,
                'status' => $status,
                'body' => $body,
            ]);

            throw new RuntimeException($this->userFacingHttpError($status, $body), 0, $e);
        }

        $json = $response->json();
        $content = data_get($json, 'choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException('Resposta vazia da API de IA.');
        }

        $inputTokens = data_get($json, 'usage.prompt_tokens');
        $outputTokens = data_get($json, 'usage.completion_tokens');
        $totalTokens = data_get($json, 'usage.total_tokens');
        $finishReason = data_get($json, 'choices.0.finish_reason');

        return [
            'content' => $content,
            'model' => (string) data_get($json, 'model', $model),
            'input_tokens' => is_numeric($inputTokens) ? (int) $inputTokens : null,
            'output_tokens' => is_numeric($outputTokens) ? (int) $outputTokens : null,
            'total_tokens' => is_numeric($totalTokens) ? (int) $totalTokens : null,
            'estimated_cost' => null,
            'finish_reason' => is_string($finishReason) ? $finishReason : null,
        ];
    }

    protected function safeBody(?string $body): ?string
    {
        if ($body === null) {
            return null;
        }

        $trimmed = mb_substr($body, 0, 500);

        return str_ireplace((string) config('services.openai.api_key'), '[redacted]', $trimmed);
    }

    protected function userFacingHttpError(?int $status, ?string $body): string
    {
        $code = null;
        $type = null;

        if (is_string($body) && $body !== '') {
            $decoded = json_decode($body, true);
            if (is_array($decoded)) {
                $code = data_get($decoded, 'error.code');
                $type = data_get($decoded, 'error.type');
            }
        }

        if (
            $status === 429
            || $code === 'credit_balance_exhausted'
            || $code === 'insufficient_quota'
            || $type === 'insufficient_quota'
        ) {
            return 'A conta OpenAI está sem créditos. Recarregue o saldo em platform.openai.com (Billing) ou use outra OPENAI_API_KEY com créditos.';
        }

        if ($status === 401 || $status === 403) {
            return 'A chave OPENAI_API_KEY é inválida ou sem permissão. Verifique o .env.';
        }

        if ($status === 404) {
            return 'Modelo OpenAI não encontrado. Verifique OPENAI_MODEL no .env (ex.: gpt-4o-mini).';
        }

        return 'Falha HTTP na API de IA.';
    }
}
