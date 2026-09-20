<?php

namespace App\Services\Ocr;

use App\Contracts\OcrServiceInterface;
use App\DTO\OcrResult;
use App\Services\Packages\PackageSenderDetector;
use App\Support\TextNormalizer;

/**
 * Implementação fake para testes / quando Tesseract não está disponível.
 */
class FakeOcrService implements OcrServiceInterface
{
    private ?string $forcedText;
    private ?float $forcedConfidence;
    private PackageSenderDetector $senderDetector;

    public function __construct(
        ?string $forcedText = null,
        ?float $forcedConfidence = 0.9,
        ?PackageSenderDetector $senderDetector = null,
    ) {
        $this->forcedText = $forcedText;
        $this->forcedConfidence = $forcedConfidence;
        $this->senderDetector = $senderDetector ?? new PackageSenderDetector();
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function extract(string $imagePath, array $hints = []): OcrResult
    {
        $rawText = $this->forcedText ?? '';

        if ($rawText === '' && is_file($imagePath)) {
            $sidecar = preg_replace('/\.\w+$/', '.txt', $imagePath);
            if (is_string($sidecar) && is_file($sidecar)) {
                $rawText = (string) file_get_contents($sidecar);
            }
        }

        $unitParts = TextNormalizer::extractBlockAndUnit($rawText);

        return new OcrResult(
            rawText: $rawText,
            confidence: $this->forcedConfidence,
            trackingCode: TextNormalizer::extractTrackingCode($rawText),
            possibleName: TextNormalizer::extractPossibleName($rawText),
            possibleAddress: TextNormalizer::extractPossibleAddress($rawText),
            possibleUnit: $unitParts['number'],
            possibleBlock: $unitParts['block'],
            possibleSender: $this->senderDetector->detect($rawText),
        );
    }
}
