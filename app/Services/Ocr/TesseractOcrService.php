<?php

namespace App\Services\Ocr;

use App\Contracts\OcrServiceInterface;
use App\DTO\OcrResult;
use App\Services\Packages\PackageSenderDetector;
use App\Support\TextNormalizer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

/**
 * OCR via binário Tesseract do sistema (sem dependência Composer pesada).
 * Compatível com VPS Ubuntu e Laragon Windows.
 */
class TesseractOcrService implements OcrServiceInterface
{
    public function __construct(
        private readonly PackageSenderDetector $senderDetector
    ) {
    }

    public function isAvailable(): bool
    {
        if (!config('ocr.enabled', true)) {
            return false;
        }

        try {
            $binary = $this->binary();
            if ($binary === '' || ($this->isAbsolutePath($binary) && !is_file($binary))) {
                return false;
            }

            $result = Process::timeout(10)->run([$binary, '--version']);

            return $result->successful();
        } catch (\Throwable $e) {
            Log::warning('Tesseract OCR unavailable', [
                'error' => $this->safeText($e->getMessage()),
            ]);

            return false;
        }
    }

    public function extract(string $imagePath): OcrResult
    {
        if (!is_file($imagePath)) {
            return new OcrResult(extra: ['error' => 'Imagem não encontrada']);
        }

        if (!config('ocr.enabled', true)) {
            return new OcrResult(rawText: '', confidence: null, extra: [
                'error' => 'OCR desabilitado. Use o registro manual.',
            ]);
        }

        $binary = $this->binary();
        if ($binary === '' || ($this->isAbsolutePath($binary) && !is_file($binary))) {
            return new OcrResult(rawText: '', confidence: null, extra: [
                'error' => 'Tesseract não encontrado. Configure TESSERACT_PATH no .env ou use o registro manual.',
            ]);
        }

        try {
            $lang = config('ocr.lang', 'por');
            $timeout = (int) config('ocr.timeout', 30);
            $passes = [];

            foreach ([6, 11, 3] as $psm) {
                $pass = $this->runPass($binary, $imagePath, $lang, $timeout, $psm);
                if ($pass['text'] !== '') {
                    $passes[] = $pass;
                }
            }

            if ($passes === []) {
                return new OcrResult(
                    rawText: '',
                    confidence: null,
                    extra: ['error' => 'O Tesseract não encontrou texto legível na etiqueta.'],
                );
            }

            usort($passes, fn (array $a, array $b) => $this->passScore($b) <=> $this->passScore($a));
            $best = $passes[0];
            $rawText = $best['text'];
            $confidence = $best['confidence'];
            $unitParts = TextNormalizer::extractBlockAndUnit($rawText);

            return new OcrResult(
                rawText: $rawText,
                confidence: $confidence,
                trackingCode: TextNormalizer::extractTrackingCode($rawText),
                possibleName: TextNormalizer::extractPossibleName($rawText),
                possibleAddress: $this->extractAddressLine($rawText),
                possibleUnit: $unitParts['number'],
                possibleBlock: $unitParts['block'],
                possibleSender: $this->senderDetector->detect($rawText),
                extra: [
                    'psm' => $best['psm'],
                    'passes' => count($passes),
                ],
            );
        } catch (\Throwable $e) {
            Log::error('OCR extraction failed', [
                'error' => $this->safeText($e->getMessage()),
                'image' => basename($imagePath),
                'binary' => $binary,
            ]);

            return new OcrResult(
                rawText: '',
                confidence: null,
                extra: [
                    'error' => 'Falha ao processar a etiqueta. Tente novamente ou registre manualmente.',
                ],
            );
        }
    }

    /**
     * @return array{text: string, confidence: float, psm: int}
     */
    private function runPass(string $binary, string $imagePath, string $lang, int $timeout, int $psm): array
    {
        $outputBase = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ocr_out_' . uniqid('', true);
        $result = Process::timeout($timeout)->run([
            $binary,
            $imagePath,
            $outputBase,
            '-l',
            $lang,
            '--oem',
            '1',
            '--psm',
            (string) $psm,
            '-c',
            'preserve_interword_spaces=1',
        ]);

        $txtFile = $outputBase . '.txt';
        $text = is_file($txtFile) ? (string) file_get_contents($txtFile) : '';
        @unlink($txtFile);
        $text = $this->safeText($text);

        if (!$result->successful()) {
            Log::warning('OCR pass failed', [
                'psm' => $psm,
                'exit' => $result->exitCode(),
                'error' => $this->safeText($result->errorOutput()),
            ]);
        }

        return [
            'text' => trim($text),
            'confidence' => $this->estimateConfidence($text) ?? 0.0,
            'psm' => $psm,
        ];
    }

    private function passScore(array $pass): float
    {
        $score = (float) $pass['confidence'];
        $text = $pass['text'];

        if (TextNormalizer::extractPossibleName($text)) {
            $score += 1.0;
        }
        if (TextNormalizer::extractTrackingCode($text)) {
            $score += 0.4;
        }
        if (preg_match('/\b(ENDERECO|CEP|DESTINATARIO|RECEBEDOR)\b/i', TextNormalizer::normalizeText($text))) {
            $score += 0.3;
        }

        return $score;
    }

    private function binary(): string
    {
        $configured = trim((string) config('ocr.tesseract_path', ''));
        if ($configured !== '') {
            return $configured;
        }

        // Fallbacks comuns no Windows (Laragon)
        $windowsDefaults = [
            'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
            'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe',
        ];

        foreach ($windowsDefaults as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return 'tesseract';
    }

    private function isAbsolutePath(string $path): bool
    {
        return (bool) preg_match('/^(?:[A-Za-z]:[\\\\\\/]|\\\\|\\/)/', $path);
    }

    private function friendlyFailureMessage(string $stderr): string
    {
        $lower = mb_strtolower($stderr, 'UTF-8');

        if (
            str_contains($lower, 'nao e reconhecido')
            || str_contains($lower, 'não é reconhecido')
            || str_contains($lower, 'not recognized')
            || str_contains($lower, 'not found')
        ) {
            return 'Tesseract não encontrado no PATH. Defina TESSERACT_PATH no .env e limpe o cache de config.';
        }

        return 'Falha no OCR. Tente novamente ou use o registro manual.';
    }

    /**
     * Garante string UTF-8 válida para respostas JSON.
     */
    private function safeText(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $converted = @mb_convert_encoding($value, 'UTF-8', 'Windows-1252, ISO-8859-1, UTF-8');
        if (is_string($converted) && mb_check_encoding($converted, 'UTF-8')) {
            return $converted;
        }

        return preg_replace('/[^\x20-\x7E\n\r\t]/', '?', $value) ?? '';
    }

    private function estimateConfidence(string $rawText): ?float
    {
        $trimmed = trim($rawText);
        if ($trimmed === '') {
            return 0.0;
        }

        $length = mb_strlen($trimmed);
        $alphaNum = preg_match_all('/[A-Za-z0-9]/', $trimmed) ?: 0;
        $ratio = $length > 0 ? $alphaNum / $length : 0;
        $lengthScore = min(1.0, $length / 120);
        $confidence = ($ratio * 0.5) + ($lengthScore * 0.5);

        return round(max(0.1, min(0.99, $confidence)), 4);
    }

    private function extractAddressLine(string $text): ?string
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        foreach ($lines as $line) {
            $normalized = TextNormalizer::normalizeAddress($line);
            if (
                str_contains($normalized, 'RUA')
                || str_contains($normalized, 'AVENIDA')
                || str_contains($normalized, 'BLOCO')
                || str_contains($normalized, 'APARTAMENTO')
            ) {
                return trim($line);
            }
        }

        return null;
    }
}
