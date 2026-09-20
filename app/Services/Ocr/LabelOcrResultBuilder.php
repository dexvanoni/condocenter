<?php

namespace App\Services\Ocr;

use App\DTO\OcrResult;
use App\Services\Packages\PackageSenderDetector;
use App\Support\TextNormalizer;

class LabelOcrResultBuilder
{
    public function __construct(
        private readonly PackageSenderDetector $senderDetector
    ) {
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    public function fromRawText(string $rawText, ?float $confidence, array $extra = []): OcrResult
    {
        $rawText = trim($rawText);
        $unitParts = TextNormalizer::extractBlockAndUnit($rawText);

        return new OcrResult(
            rawText: $rawText,
            confidence: $confidence,
            trackingCode: TextNormalizer::extractTrackingCode($rawText),
            possibleName: TextNormalizer::extractPossibleName($rawText),
            possibleAddress: TextNormalizer::extractPossibleAddress($rawText),
            possibleUnit: $unitParts['number'],
            possibleBlock: $unitParts['block'],
            possibleSender: $this->senderDetector->detect($rawText),
            extra: $extra,
        );
    }
}
