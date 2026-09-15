<?php

namespace App\DTO;

class OcrResult
{
    public function __construct(
        public readonly string $rawText = '',
        public readonly ?float $confidence = null,
        public readonly ?string $trackingCode = null,
        public readonly ?string $possibleName = null,
        public readonly ?string $possibleAddress = null,
        public readonly ?string $possibleUnit = null,
        public readonly ?string $possibleBlock = null,
        public readonly ?string $possibleSender = null,
        public readonly array $extra = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'raw_text' => $this->rawText,
            'confidence' => $this->confidence,
            'tracking_code' => $this->trackingCode,
            'possible_name' => $this->possibleName,
            'possible_address' => $this->possibleAddress,
            'possible_unit' => $this->possibleUnit,
            'possible_block' => $this->possibleBlock,
            'possible_sender' => $this->possibleSender,
            'extra' => $this->extra,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            rawText: (string) ($data['raw_text'] ?? ''),
            confidence: isset($data['confidence']) ? (float) $data['confidence'] : null,
            trackingCode: $data['tracking_code'] ?? null,
            possibleName: $data['possible_name'] ?? null,
            possibleAddress: $data['possible_address'] ?? null,
            possibleUnit: $data['possible_unit'] ?? null,
            possibleBlock: $data['possible_block'] ?? null,
            possibleSender: $data['possible_sender'] ?? null,
            extra: $data['extra'] ?? [],
        );
    }

    public function isEmpty(): bool
    {
        return trim($this->rawText) === '';
    }
}
