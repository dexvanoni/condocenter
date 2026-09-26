<?php

namespace App\Services\Finance\Ai;

use App\Support\FinanceAiProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiProvider implements AiProviderInterface
{
    public function providerKey(): string
    {
        return FinanceAiProvider::GEMINI;
    }

    public function generate(string $systemPrompt, string $userPrompt, array $options = []): AiResponse
    {
        $apiKey = config('services.gemini.api_key');
        $defaultModel = (string) config('services.gemini.model', 'gemini-3.8-flash');
        $timeout = (int) config('services.gemini.timeout', 30);
        $model = isset($options['model']) && is_string($options['model']) && $options['model'] !== ''
            ? $options['model']
            : $defaultModel;
        $maxTokens = isset($options['max_tokens']) && is_numeric($options['max_tokens'])
            ? (int) $options['max_tokens']
            : (int) config('finance_ai.max_output_tokens', 2000);

        if (! $apiKey) {
            throw new RuntimeException('GEMINI_API_KEY ausente.');
        }

        $started = microtime(true);
        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
            rawurlencode($model)
        );

        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [['text' => $userPrompt]],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.3,
                'responseMimeType' => 'application/json',
            ],
        ];

        if ($maxTokens > 0) {
            $payload['generationConfig']['maxOutputTokens'] = $maxTokens;
        }

        try {
            $response = Http::withHeaders([
                'x-goog-api-key' => $apiKey,
            ])
                ->timeout($timeout)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload)
                ->throw();
        } catch (ConnectionException $e) {
            Log::warning('finance_ai.gemini_timeout', [
                'provider' => $this->providerKey(),
                'model' => $model,
                'message' => $e->getMessage(),
            ]);
            throw new RuntimeException('Timeout ao contactar a API de IA.', 0, $e);
        } catch (RequestException $e) {
            $status = $e->response?->status();
            $body = $this->safeBody($e->response?->body());

            Log::warning('finance_ai.gemini_http_error', [
                'provider' => $this->providerKey(),
                'model' => $model,
                'status' => $status,
                'body' => $body,
            ]);

            throw new RuntimeException($this->userFacingHttpError($status, $body), 0, $e);
        }

        $json = $response->json();
        $content = $this->extractText($json);

        if ($content === null || trim($content) === '') {
            throw new RuntimeException('Resposta vazia da API de IA.');
        }

        $inputTokens = data_get($json, 'usageMetadata.promptTokenCount');
        $outputTokens = data_get($json, 'usageMetadata.candidatesTokenCount');
        $totalTokens = data_get($json, 'usageMetadata.totalTokenCount');
        $finishReason = data_get($json, 'candidates.0.finishReason');

        return new AiResponse(
            content: $content,
            provider: $this->providerKey(),
            model: $model,
            inputTokens: is_numeric($inputTokens) ? (int) $inputTokens : null,
            outputTokens: is_numeric($outputTokens) ? (int) $outputTokens : null,
            totalTokens: is_numeric($totalTokens) ? (int) $totalTokens : null,
            estimatedCost: null,
            responseTimeMs: (int) round((microtime(true) - $started) * 1000),
            finishReason: is_string($finishReason) ? $finishReason : null,
        );
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    protected function extractText(?array $json): ?string
    {
        if ($json === null) {
            return null;
        }

        $parts = data_get($json, 'candidates.0.content.parts');
        if (! is_array($parts)) {
            return null;
        }

        $chunks = [];
        foreach ($parts as $part) {
            if (is_array($part) && isset($part['text']) && is_string($part['text'])) {
                $chunks[] = $part['text'];
            }
        }

        if ($chunks === []) {
            return null;
        }

        return implode('', $chunks);
    }

    protected function safeBody(?string $body): ?string
    {
        if ($body === null) {
            return null;
        }

        $trimmed = mb_substr($body, 0, 500);

        return str_ireplace((string) config('services.gemini.api_key'), '[redacted]', $trimmed);
    }

    protected function userFacingHttpError(?int $status, ?string $body): string
    {
        $message = null;
        if (is_string($body) && $body !== '') {
            $decoded = json_decode($body, true);
            if (is_array($decoded)) {
                $message = data_get($decoded, 'error.message');
            }
        }

        if ($status === 429) {
            return 'A conta Gemini atingiu o limite de uso. Aguarde ou verifique a cota em Google AI Studio / Cloud.';
        }

        if ($status === 503 || (is_string($message) && str_contains(mb_strtolower($message), 'high demand'))) {
            return 'O modelo Gemini está temporariamente sobrecarregado. Tente novamente em instantes ou troque o modelo do condomínio (ex.: gemini-3.1-flash-lite) em Condomínios → Consultor Financeiro.';
        }

        if ($status === 401 || $status === 403) {
            return 'A chave GEMINI_API_KEY é inválida ou sem permissão. Verifique o .env.';
        }

        if ($status === 404) {
            return 'Modelo Gemini não encontrado. Verifique GEMINI_MODEL no .env (ex.: gemini-3.8-flash).';
        }

        if ($status === 400) {
            return 'Requisição inválida ao Gemini. Verifique o modelo e o prompt.';
        }

        if ($status !== null && $status >= 500) {
            return 'Serviço Gemini temporariamente indisponível. Tente novamente em instantes.';
        }

        return 'Falha HTTP na API de IA.';
    }
}
