<?php

namespace App\Services\Ocr;

use App\Contracts\OcrServiceInterface;
use App\DTO\OcrResult;
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
        private readonly LabelOcrResultBuilder $resultBuilder
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

    public function extract(string $imagePath, array $hints = []): OcrResult
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

        $userWordsFile = $this->writeUserWords($hints['user_words'] ?? []);

        try {
            $lang = (string) config('ocr.lang', 'por');
            $timeout = (int) config('ocr.timeout', 15);
            $passes = [];

            foreach ([6, 4] as $psm) {
                $pass = $this->runPass($binary, $imagePath, $lang, $timeout, $psm, $userWordsFile);
                if ($pass['text'] !== '') {
                    $passes[] = $pass;
                }
            }

            if ($passes === [] && $userWordsFile) {
                $pass = $this->runPass($binary, $imagePath, $lang, $timeout, 6, null);
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
            $rawText = $this->mergePassTexts($passes);

            return $this->resultBuilder->fromRawText($rawText, $best['confidence'], [
                'psm' => $best['psm'],
                'passes' => count($passes),
                'engine' => 'tesseract',
            ]);
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
        } finally {
            if ($userWordsFile && is_file($userWordsFile)) {
                @unlink($userWordsFile);
            }
        }
    }

    /**
     * @return array{text: string, confidence: float, psm: int}
     */
    private function runPass(
        string $binary,
        string $imagePath,
        string $lang,
        int $timeout,
        int $psm,
        ?string $userWordsFile
    ): array {
        $command = [
            $binary,
            $imagePath,
            'stdout',
            '-l',
            $lang,
            '--oem',
            '1',
            '--psm',
            (string) $psm,
            '--dpi',
            '300',
            '-c',
            'preserve_interword_spaces=1',
        ];

        if ($userWordsFile) {
            $command[] = '--user-words';
            $command[] = $userWordsFile;
        }

        $command[] = 'tsv';

        $result = Process::timeout($timeout)->run($command);
        $parsed = $this->parseTsv($this->safeText($result->output()));

        if (!$result->successful()) {
            Log::warning('OCR pass failed', [
                'psm' => $psm,
                'exit' => $result->exitCode(),
                'error' => $this->safeText($result->errorOutput()),
            ]);
        }

        $text = $parsed['text'];

        return [
            'text' => $text,
            'confidence' => $parsed['confidence'] > 0
                ? $parsed['confidence']
                : ($this->estimateConfidence($text) ?? 0.0),
            'psm' => $psm,
        ];
    }

    /**
     * @param  list<string>  $words
     */
    private function writeUserWords(array $words): ?string
    {
        $tokens = [
            'DESTINATARIO', 'RECEBEDOR', 'BLOCO', 'APTO', 'APARTAMENTO',
            'UNIDADE', 'TORRE', 'CEP', 'ENDERECO',
        ];

        foreach ($words as $word) {
            $normalized = TextNormalizer::normalizeName((string) $word);
            foreach (explode(' ', $normalized) as $token) {
                if (mb_strlen($token) >= 3) {
                    $tokens[] = $token;
                }
            }
        }

        $tokens = array_values(array_unique($tokens));
        if ($tokens === []) {
            return null;
        }

        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ocr_words_' . uniqid('', true) . '.txt';
        file_put_contents($path, implode(PHP_EOL, $tokens));

        return $path;
    }

    /**
     * @param  list<array{text: string, confidence: float, psm: int}>  $passes
     */
    private function mergePassTexts(array $passes): string
    {
        $seen = [];
        $lines = [];

        foreach ($passes as $pass) {
            foreach (preg_split('/\r\n|\r|\n/', $pass['text']) ?: [] as $line) {
                $trimmed = trim($line);
                $key = TextNormalizer::normalizeText($trimmed);
                if ($key === '' || isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $lines[] = $trimmed;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @return array{text: string, confidence: float}
     */
    private function parseTsv(string $tsv): array
    {
        $rows = preg_split('/\r\n|\r|\n/', $tsv) ?: [];
        $wordsByLine = [];
        $confidences = [];
        $headerSkipped = false;

        foreach ($rows as $row) {
            if (!$headerSkipped) {
                $headerSkipped = true;
                continue;
            }

            $cols = explode("\t", $row);
            if (count($cols) < 12) {
                continue;
            }

            if ((int) $cols[0] !== 5) {
                continue;
            }

            $word = trim((string) ($cols[11] ?? ''));
            $conf = (float) $cols[10];
            if ($word === '' || $conf < 0) {
                continue;
            }

            $lineKey = $cols[1] . '-' . $cols[2] . '-' . $cols[3] . '-' . $cols[4];
            $wordsByLine[$lineKey][] = $word;
            $confidences[] = $conf;
        }

        $text = implode("\n", array_map(
            fn (array $words) => implode(' ', $words),
            $wordsByLine
        ));

        $confidence = $confidences === []
            ? 0.0
            : round((array_sum($confidences) / count($confidences)) / 100, 4);

        return [
            'text' => trim($text),
            'confidence' => $confidence,
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

}
