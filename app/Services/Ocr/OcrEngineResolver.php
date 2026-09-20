<?php

namespace App\Services\Ocr;

use App\Contracts\OcrServiceInterface;
use App\DTO\OcrResult;
use App\Models\Condominium;
use App\Support\OcrEngine;
use Illuminate\Contracts\Foundation\Application;

class OcrEngineResolver
{
    public function __construct(
        private readonly Application $app,
        private readonly TesseractOcrService $tesseract,
        private readonly PaddleOcrService $paddle,
    ) {
    }

    public function forCondominium(?int $condominiumId): OcrServiceInterface
    {
        if ($this->app->environment('testing')) {
            if ($this->app->bound('ocr.force_tesseract')) {
                return $this->resolveConfiguredEngine($condominiumId);
            }

            if ($this->app->bound(OcrServiceInterface::class)) {
                return $this->app->make(OcrServiceInterface::class);
            }

            return $this->app->make(FakeOcrService::class);
        }

        return $this->resolveConfiguredEngine($condominiumId);
    }

    public function engineKeyForCondominium(?int $condominiumId): string
    {
        return $this->configuredEngineKey($condominiumId);
    }

    /**
     * @return array{tesseract: bool, paddle: bool}
     */
    public function availability(): array
    {
        return [
            OcrEngine::TESSERACT => $this->tesseract->isAvailable(),
            OcrEngine::PADDLE => $this->paddle->isAvailable(),
        ];
    }

    /**
     * @return array{available: bool, python: ?string, error: ?string}
     */
    public function paddleStatus(bool $fresh = false): array
    {
        return $this->paddle->availabilityStatus($fresh);
    }

    public function extractWithFallback(?int $condominiumId, string $path, array $hints = []): OcrResult
    {
        if ($this->app->environment('testing') && $this->app->bound(OcrServiceInterface::class)) {
            return $this->app->make(OcrServiceInterface::class)->extract($path, $hints);
        }

        $preferredKey = $this->configuredEngineKey($condominiumId);
        $preferred = $this->makeEngine($preferredKey);
        $result = $preferred->extract($path, $hints);

        if (!$this->shouldFallbackAfterExtract($result)) {
            return $result;
        }

        $fallbackKey = $preferredKey === OcrEngine::PADDLE
            ? OcrEngine::TESSERACT
            : OcrEngine::PADDLE;
        $fallback = $this->makeEngine($fallbackKey);

        if (!$fallback->isAvailable()) {
            return $result;
        }

        $retry = $fallback->extract($path, $hints);
        if (trim($retry->rawText) === '' && !empty($retry->extra['error'])) {
            return $result;
        }

        return new OcrResult(
            rawText: $retry->rawText,
            confidence: $retry->confidence,
            trackingCode: $retry->trackingCode,
            possibleName: $retry->possibleName,
            possibleAddress: $retry->possibleAddress,
            possibleUnit: $retry->possibleUnit,
            possibleBlock: $retry->possibleBlock,
            possibleSender: $retry->possibleSender,
            extra: array_merge($retry->extra, [
                'engine_fallback_from' => $preferredKey,
            ]),
        );
    }

    private function shouldFallbackAfterExtract(OcrResult $result): bool
    {
        return !empty($result->extra['error']) || trim($result->rawText) === '';
    }

    private function resolveConfiguredEngine(?int $condominiumId): OcrServiceInterface
    {
        $preferred = $this->configuredEngineKey($condominiumId);
        $preferredService = $this->makeEngine($preferred);

        if ($preferredService->isAvailable()) {
            return $preferredService;
        }

        foreach (OcrEngine::values() as $fallbackKey) {
            if ($fallbackKey === $preferred) {
                continue;
            }

            $fallback = $this->makeEngine($fallbackKey);
            if ($fallback->isAvailable()) {
                return $fallback;
            }
        }

        return $preferredService;
    }

    private function configuredEngineKey(?int $condominiumId): string
    {
        if (!$condominiumId) {
            return OcrEngine::TESSERACT;
        }

        $condominium = Condominium::query()
            ->select('id', 'label_ocr_engine')
            ->find($condominiumId);

        return OcrEngine::normalize($condominium?->label_ocr_engine);
    }

    private function makeEngine(string $engine): OcrServiceInterface
    {
        return match ($engine) {
            OcrEngine::PADDLE => $this->paddle,
            default => $this->tesseract,
        };
    }
}
