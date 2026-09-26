<?php

namespace App\Services\Finance;

use App\Models\AiFinancialConsultation;
use App\Models\Condominium;
use App\Models\User;
use App\Services\Finance\Ai\AiProviderManager;
use App\Support\FinanceAiProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class FinanceAiAdvisorService
{
    public function __construct(
        private FinancialAnalysisService $analysis,
        private AiProviderManager $providers,
        private FinanceAiQuotaService $quota,
    ) {}

    /**
     * @return array{
     *   ok: bool,
     *   from_cache: bool,
     *   question_key: string,
     *   question_title: string,
     *   analysis: array<string, mixed>|null,
     *   message: string|null,
     *   quota: array<string, mixed>|null
     * }
     */
    public function analyze(User $user, int $condominiumId, string $questionKey): array
    {
        $question = config("finance_ai.questions.{$questionKey}");
        $title = is_array($question) ? (string) ($question['title'] ?? $questionKey) : $questionKey;
        $friendly = (string) config('finance_ai.friendly_error');
        $started = microtime(true);
        $quotaInfo = $this->publicQuota($this->quota->quotaForCondominium($condominiumId));

        $condominium = Condominium::query()->find($condominiumId);
        $resolved = FinanceAiProvider::resolveForCondominium($condominium);
        $providerKey = $resolved['provider'];
        $modelKey = $resolved['model'];

        try {
            $snapshot = $this->analysis->buildSnapshot($condominiumId, $questionKey);
            $hash = $this->analysis->indicatorsHash($snapshot);
            $cacheKey = "finance_ai:{$condominiumId}:{$questionKey}:{$providerKey}:{$modelKey}:{$hash}";
            $ttl = (int) config('finance_ai.cache_ttl', 3600);

            $cached = Cache::get($cacheKey);
            if (is_array($cached) && ($cached['ok'] ?? false)) {
                $this->persistConsultation($user, $condominiumId, $questionKey, $hash, [
                    'provider' => $cached['provider'] ?? $providerKey,
                    'model' => $cached['model'] ?? $modelKey,
                    'status' => 'cache_hit',
                    'response_time_ms' => (int) round((microtime(true) - $started) * 1000),
                    'input_tokens' => null,
                    'output_tokens' => null,
                    'total_tokens' => null,
                    'estimated_cost' => null,
                ]);

                return [
                    'ok' => true,
                    'from_cache' => true,
                    'question_key' => $questionKey,
                    'question_title' => $title,
                    'analysis' => $cached['analysis'],
                    'message' => null,
                    'quota' => $quotaInfo,
                ];
            }

            $liveQuota = $this->quota->quotaForCondominium($condominiumId);
            if (! $liveQuota['allowed']) {
                return [
                    'ok' => false,
                    'from_cache' => false,
                    'question_key' => $questionKey,
                    'question_title' => $title,
                    'analysis' => null,
                    'message' => $liveQuota['message'] ?? 'Limite mensal de consultas atingido.',
                    'quota' => $this->publicQuota($liveQuota),
                ];
            }

            $resolvedProvider = $this->providers->resolve($condominium);
            $systemPrompt = (string) config('finance_ai.system_prompt');
            $userPrompt = $this->buildUserPrompt($title, $snapshot);

            $ai = $resolvedProvider['client']->generate($systemPrompt, $userPrompt, [
                'model' => $resolvedProvider['model'],
            ])->toArray();

            $analysis = $this->parseAndValidate($ai['content']);
            $elapsedMs = (int) round((microtime(true) - $started) * 1000);

            if ($analysis === null) {
                $this->persistConsultation($user, $condominiumId, $questionKey, $hash, [
                    'provider' => $ai['provider'],
                    'model' => $ai['model'],
                    'status' => 'invalid_response',
                    'response_time_ms' => $elapsedMs,
                    'input_tokens' => $ai['input_tokens'],
                    'output_tokens' => $ai['output_tokens'],
                    'total_tokens' => $ai['total_tokens'],
                    'estimated_cost' => $ai['estimated_cost'],
                ]);

                Log::warning('finance_ai.invalid_json', [
                    'condominium_id' => $condominiumId,
                    'user_id' => $user->id,
                    'question' => $questionKey,
                    'provider' => $ai['provider'],
                    'model' => $ai['model'],
                ]);

                return [
                    'ok' => false,
                    'from_cache' => false,
                    'question_key' => $questionKey,
                    'question_title' => $title,
                    'analysis' => null,
                    'message' => $friendly,
                    'quota' => $this->publicQuota($this->quota->quotaForCondominium($condominiumId)),
                ];
            }

            Cache::put($cacheKey, [
                'ok' => true,
                'analysis' => $analysis,
                'provider' => $ai['provider'],
                'model' => $ai['model'],
            ], $ttl);

            $this->persistConsultation($user, $condominiumId, $questionKey, $hash, [
                'provider' => $ai['provider'],
                'model' => $ai['model'],
                'status' => 'success',
                'response_time_ms' => $elapsedMs,
                'input_tokens' => $ai['input_tokens'],
                'output_tokens' => $ai['output_tokens'],
                'total_tokens' => $ai['total_tokens'],
                'estimated_cost' => $ai['estimated_cost'],
            ]);

            Log::info('finance_ai.success', [
                'condominium_id' => $condominiumId,
                'user_id' => $user->id,
                'question' => $questionKey,
                'provider' => $ai['provider'],
                'model' => $ai['model'],
                'response_time_ms' => $elapsedMs,
                'total_tokens' => $ai['total_tokens'],
            ]);

            return [
                'ok' => true,
                'from_cache' => false,
                'question_key' => $questionKey,
                'question_title' => $title,
                'analysis' => $analysis,
                'message' => null,
                'quota' => $this->publicQuota($this->quota->quotaForCondominium($condominiumId)),
            ];
        } catch (Throwable $e) {
            $elapsedMs = (int) round((microtime(true) - $started) * 1000);

            Log::warning('finance_ai.failure', [
                'condominium_id' => $condominiumId,
                'user_id' => $user->id,
                'question' => $questionKey,
                'provider' => $providerKey,
                'model' => $modelKey,
                'error' => $e->getMessage(),
                'response_time_ms' => $elapsedMs,
            ]);

            try {
                $this->persistConsultation($user, $condominiumId, $questionKey, null, [
                    'provider' => $providerKey,
                    'model' => $modelKey,
                    'status' => 'error',
                    'response_time_ms' => $elapsedMs,
                    'input_tokens' => null,
                    'output_tokens' => null,
                    'total_tokens' => null,
                    'estimated_cost' => null,
                ]);
            } catch (Throwable) {
                // não mascarar o erro original
            }

            return [
                'ok' => false,
                'from_cache' => false,
                'question_key' => $questionKey,
                'question_title' => $title,
                'analysis' => null,
                'message' => $this->publicFailureMessage($e, $friendly),
                'quota' => $quotaInfo,
            ];
        }
    }

    protected function publicFailureMessage(Throwable $e, string $friendly): string
    {
        $message = trim($e->getMessage());

        // Mensagens já sanitizadas pelos providers (créditos, chave, modelo).
        if (
            str_contains($message, 'OpenAI')
            || str_contains($message, 'OPENAI_')
            || str_contains($message, 'Gemini')
            || str_contains($message, 'GEMINI_')
            || str_starts_with($message, 'Timeout ao contactar')
        ) {
            return $message;
        }

        return $friendly;
    }

    /**
     * @param  array<string, mixed>  $quota
     * @return array{limit: int|null, used: int, remaining: int|null, shared: bool, period_label: string, allowed: bool}
     */
    protected function publicQuota(array $quota): array
    {
        return [
            'limit' => $quota['limit'],
            'used' => $quota['used'],
            'remaining' => $quota['remaining'],
            'shared' => $quota['shared'],
            'period_label' => $quota['period_label'],
            'allowed' => $quota['allowed'],
        ];
    }

    /**
     * @param  array<string, mixed>  $snapshot
     */
    protected function buildUserPrompt(string $questionTitle, array $snapshot): string
    {
        $json = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return "Pergunta do síndico: {$questionTitle}\n\n"
            ."Indicadores financeiros calculados pelo SindCON (use somente estes dados):\n"
            .$json;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function parseAndValidate(string $content): ?array
    {
        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            if (preg_match('/\{.*\}/s', $content, $matches)) {
                $decoded = json_decode($matches[0], true);
            }
        }

        if (! is_array($decoded)) {
            return null;
        }

        $titulo = $decoded['titulo'] ?? null;
        $resumo = $decoded['resumo'] ?? null;

        if (! is_string($titulo) || ! is_string($resumo)) {
            return null;
        }

        $pontos = $decoded['pontos_atencao'] ?? [];
        $recomendacoes = $decoded['recomendacoes'] ?? [];
        $observacoes = $decoded['observacoes'] ?? [];

        if (! is_array($pontos) || ! is_array($recomendacoes) || ! is_array($observacoes)) {
            return null;
        }

        $normalizedRecs = [];
        foreach ($recomendacoes as $rec) {
            if (! is_array($rec)) {
                continue;
            }
            $normalizedRecs[] = [
                'titulo' => (string) ($rec['titulo'] ?? ''),
                'acao' => (string) ($rec['acao'] ?? ''),
                'motivo' => (string) ($rec['motivo'] ?? ''),
                'impacto' => (string) ($rec['impacto'] ?? ''),
                'prioridade' => $this->normalizePriority($rec['prioridade'] ?? 'media'),
            ];
        }

        return [
            'titulo' => $titulo,
            'resumo' => $resumo,
            'pontos_atencao' => array_values(array_map('strval', $pontos)),
            'recomendacoes' => $normalizedRecs,
            'observacoes' => array_values(array_map('strval', $observacoes)),
        ];
    }

    protected function normalizePriority(mixed $value): string
    {
        $v = mb_strtolower(trim((string) $value));

        return match ($v) {
            'alta', 'high' => 'alta',
            'baixa', 'low' => 'baixa',
            default => 'media',
        };
    }

    /**
     * @param  array{
     *   provider?: mixed,
     *   model: mixed,
     *   status: string,
     *   response_time_ms: int,
     *   input_tokens: int|null,
     *   output_tokens: int|null,
     *   total_tokens: int|null,
     *   estimated_cost: float|null
     * }  $meta
     */
    protected function persistConsultation(
        User $user,
        int $condominiumId,
        string $questionKey,
        ?string $hash,
        array $meta
    ): void {
        AiFinancialConsultation::query()->create([
            'condominium_id' => $condominiumId,
            'user_id' => $user->id,
            'question_key' => $questionKey,
            'indicators_hash' => $hash,
            'provider' => isset($meta['provider']) && $meta['provider'] !== null
                ? (string) $meta['provider']
                : null,
            'model' => $meta['model'] ? (string) $meta['model'] : null,
            'input_tokens' => $meta['input_tokens'],
            'output_tokens' => $meta['output_tokens'],
            'total_tokens' => $meta['total_tokens'],
            'estimated_cost' => $meta['estimated_cost'],
            'response_time_ms' => $meta['response_time_ms'],
            'status' => $meta['status'],
        ]);
    }
}
