<?php

namespace App\Services\Finance\Ai;

use App\Models\Condominium;
use App\Support\FinanceAiProvider;
use InvalidArgumentException;
use RuntimeException;

class AiProviderManager
{
    public function __construct(
        private OpenAiProvider $openAi,
        private GeminiProvider $gemini,
    ) {}

    /**
     * @return array{provider: string, model: string, client: AiProviderInterface}
     */
    public function resolve(?Condominium $condominium): array
    {
        $resolved = FinanceAiProvider::resolveForCondominium($condominium);
        $providerKey = $resolved['provider'];
        $model = $resolved['model'];

        if (! FinanceAiProvider::isApiKeyConfigured($providerKey)) {
            throw new RuntimeException(FinanceAiProvider::missingKeyMessage($providerKey));
        }

        return [
            'provider' => $providerKey,
            'model' => $model,
            'client' => $this->driver($providerKey),
        ];
    }

    public function driver(string $provider): AiProviderInterface
    {
        return match (FinanceAiProvider::normalize($provider)) {
            FinanceAiProvider::GEMINI => $this->gemini,
            FinanceAiProvider::OPENAI => $this->openAi,
            default => throw new InvalidArgumentException("Provider de IA inválido: {$provider}"),
        };
    }
}
