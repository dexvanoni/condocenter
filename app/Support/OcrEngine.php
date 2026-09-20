<?php

namespace App\Support;

class OcrEngine
{
    public const TESSERACT = 'tesseract';

    public const PADDLE = 'paddle';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return [
            self::TESSERACT,
            self::PADDLE,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::TESSERACT => 'Tesseract OCR',
            self::PADDLE => 'PaddleOCR',
        ];
    }

    public static function isValid(?string $engine): bool
    {
        return $engine !== null && in_array($engine, self::values(), true);
    }

    public static function normalize(?string $engine): string
    {
        return self::isValid($engine) ? $engine : self::TESSERACT;
    }
}
