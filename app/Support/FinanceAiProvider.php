<?php

namespace App\Support;

/**
 * Provedores LLM do Consultor Financeiro (chaves de API globais no .env).
 */
final class FinanceAiProvider
{
    public const OPENAI = 'openai';

    public const GEMINI = 'gemini';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return [self::OPENAI, self::GEMINI];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::OPENAI => 'OpenAI',
            self::GEMINI => 'Google Gemini',
        ];
    }

    public static function normalize(?string $provider): string
    {
        $value = mb_strtolower(trim((string) $provider));

        return in_array($value, self::values(), true) ? $value : self::OPENAI;
    }

    public static function label(string $provider): string
    {
        return self::labels()[self::normalize($provider)] ?? $provider;
    }

    /**
     * Modelos disponíveis para o provider (config + env).
     *
     * @return list<string>
     */
    public static function modelsFor(string $provider): array
    {
        $provider = self::normalize($provider);
        $configured = config("finance_ai.providers.{$provider}.models", []);

        if (! is_array($configured) || $configured === []) {
            $fallback = $provider === self::GEMINI
                ? [(string) config('services.gemini.model', 'gemini-3.8-flash')]
                : [(string) config('services.openai.model', 'gpt-5.6-luna')];

            return array_values(array_unique(array_filter($fallback)));
        }

        return array_values(array_unique(array_filter(array_map('strval', $configured))));
    }

    public static function defaultModel(string $provider): string
    {
        $models = self::modelsFor($provider);

        return $models[0] ?? ($provider === self::GEMINI ? 'gemini-3.8-flash' : 'gpt-5.6-luna');
    }

    public static function isApiKeyConfigured(string $provider): bool
    {
        $provider = self::normalize($provider);
        $key = $provider === self::GEMINI
            ? config('services.gemini.api_key')
            : config('services.openai.api_key');

        return is_string($key) && trim($key) !== '';
    }

    public static function missingKeyMessage(string $provider): string
    {
        return match (self::normalize($provider)) {
            self::GEMINI => 'Gemini não está configurado no ambiente da plataforma. Configure GEMINI_API_KEY no ambiente da aplicação.',
            default => 'OpenAI não está configurada no ambiente da plataforma. Configure OPENAI_API_KEY no ambiente da aplicação.',
        };
    }

    /**
     * Resolve provider/modelo efetivos do condomínio (fallback OpenAI global).
     *
     * @return array{provider: string, model: string}
     */
    public static function resolveForCondominium(?\App\Models\Condominium $condominium): array
    {
        $provider = self::normalize($condominium?->ai_provider);
        $model = trim((string) ($condominium?->ai_model ?? ''));
        $allowed = self::modelsFor($provider);

        if ($model === '' || ! in_array($model, $allowed, true)) {
            $model = self::defaultModel($provider);
        }

        return [
            'provider' => $provider,
            'model' => $model,
        ];
    }
}
