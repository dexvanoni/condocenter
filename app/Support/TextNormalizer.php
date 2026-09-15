<?php

namespace App\Support;

use Illuminate\Support\Str;

class TextNormalizer
{
    private const ABBREVIATIONS = [
        'AP' => 'APARTAMENTO',
        'APT' => 'APARTAMENTO',
        'APTO' => 'APARTAMENTO',
        'APART' => 'APARTAMENTO',
        'BL' => 'BLOCO',
        'BLC' => 'BLOCO',
        'QD' => 'QUADRA',
        'LT' => 'LOTE',
        'N' => 'NUMERO',
        'NR' => 'NUMERO',
        'NRO' => 'NUMERO',
        'AV' => 'AVENIDA',
        'R' => 'RUA',
    ];

    public static function normalizeText(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        $text = Str::ascii($text);
        $text = mb_strtoupper($text, 'UTF-8');
        $text = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $text);
        $text = preg_replace('/[^A-Z0-9\s\/\-]/', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }

    public static function normalizeName(?string $name): string
    {
        $normalized = self::normalizeText($name);

        $stopWords = ['DE', 'DA', 'DO', 'DAS', 'DOS', 'E'];
        $parts = array_values(array_filter(
            explode(' ', $normalized),
            fn (string $part) => $part !== '' && !in_array($part, $stopWords, true)
        ));

        return implode(' ', $parts);
    }

    public static function normalizeAddress(?string $address): string
    {
        $normalized = self::normalizeText($address);

        foreach (self::ABBREVIATIONS as $abbr => $full) {
            $normalized = preg_replace(
                '/\b' . preg_quote($abbr, '/') . '\b/',
                $full,
                $normalized
            ) ?? $normalized;
        }

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);
    }

    public static function normalizeUnitReference(?string $unit): string
    {
        $normalized = self::normalizeAddress($unit);
        $normalized = preg_replace('/\b(APARTAMENTO|BLOCO|UNIDADE)\b/', ' ', $normalized) ?? $normalized;

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);
    }

    /**
     * Extrai bloco e número de apartamento a partir de texto OCR.
     *
     * @return array{block: ?string, number: ?string}
     */
    public static function extractBlockAndUnit(?string $text): array
    {
        $normalized = self::normalizeAddress($text);
        $block = null;
        $number = null;

        if (preg_match('/\bBLOCO\s*([A-Z0-9]+)\b/', $normalized, $matches)) {
            $block = $matches[1];
        } elseif (preg_match('/\bBL\s*([A-Z0-9]+)\b/', self::normalizeText($text), $matches)) {
            $block = $matches[1];
        }

        if (preg_match('/\bAPARTAMENTO\s*([0-9]+[A-Z]?)\b/', $normalized, $matches)) {
            $number = $matches[1];
        } elseif (preg_match('/\b(?:AP|APT|APTO)\s*\.?\s*([0-9]+[A-Z]?)\b/i', $text ?? '', $matches)) {
            $number = mb_strtoupper($matches[1], 'UTF-8');
        } elseif (preg_match('/\bUNIDADE\s*([0-9]+[A-Z]?)\b/', $normalized, $matches)) {
            $number = $matches[1];
        }

        return [
            'block' => $block,
            'number' => $number,
        ];
    }

    /**
     * Extrai possível código de rastreamento (Correios e padrões genéricos).
     */
    public static function extractTrackingCode(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return null;
        }

        $upper = mb_strtoupper($text, 'UTF-8');

        // Correios: AA123456789BR
        if (preg_match('/\b([A-Z]{2})[\s\-]*(\d(?:[\s\-]*\d){8})[\s\-]*([A-Z]{2})\b/', $upper, $matches)) {
            return $matches[1] . preg_replace('/\D/', '', $matches[2]) . $matches[3];
        }

        $lines = preg_split('/\r\n|\r|\n/', $upper) ?: [];
        foreach ($lines as $line) {
            $normalized = self::normalizeText($line);

            // Identificadores explicitamente rotulados têm precedência.
            if (preg_match(
                '/\b(?:RASTREIO|RASTREAMENTO|TRACKING|PEDIDO|ORDER|VENDA)\s*[:#\-]?\s*([A-Z0-9\-]{8,30})\b/',
                $normalized,
                $matches
            )) {
                return str_replace('-', '', $matches[1]);
            }
        }

        // Código de barras puramente numérico: normalmente 10–20 dígitos.
        if (preg_match_all('/(?<!\d)(\d{10,20})(?!\d)/', $upper, $matches)) {
            foreach ((array) ($matches[1] ?? []) as $candidate) {
                // CEP, CNPJ e datas curtas não entram nesta faixa mínima.
                return $candidate;
            }
        }

        // Padrão alfanumérico genérico exige tamanho e densidade suficientes.
        if (preg_match_all('/\b([A-Z0-9]{10,24})\b/', $upper, $matches)) {
            foreach ((array) ($matches[1] ?? []) as $candidate) {
                $digits = preg_match_all('/\d/', $candidate);
                $letters = preg_match_all('/[A-Z]/', $candidate);
                if ($digits >= 4 && $letters >= 2) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    /**
     * Extrai o destinatário de uma etiqueta.
     *
     * Etiquetas normalmente exibem o nome imediatamente antes de endereço,
     * CEP ou uma versão concatenada do próprio nome. Por isso não basta
     * escolher a maior linha alfabética: campos como "Complemento" podem
     * sofrer OCR e parecer um nome.
     */
    public static function extractPossibleName(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return null;
        }

        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $best = null;
        $bestScore = PHP_INT_MIN;

        foreach ($lines as $index => $line) {
            $normalized = self::normalizeName($line);
            if ($normalized === '' || preg_match('/\d/', $normalized)) {
                continue;
            }

            $words = explode(' ', $normalized);
            $wordCount = count($words);

            if ($wordCount < 2 || $wordCount > 7 || self::looksLikeNonNameLine($normalized)) {
                continue;
            }

            $score = $wordCount * 2;
            $followingLines = implode(' ', array_slice($lines, $index + 1, 3));
            $followingNormalized = self::normalizeText($followingLines);

            // É o padrão mais confiável desta classe de etiquetas.
            if (preg_match('/\b(ENDERECO|CEP|CIDADE\s+DE\s+DESTINO)\b/', $followingNormalized)) {
                $score += 12;
            }

            // Algumas etiquetas imprimem na sequência o nome concatenado.
            if (preg_match('/^\s*\([A-Z\s]+\)/u', $lines[$index + 1] ?? '')) {
                $score += 8;
            }

            // Nomes humanos tendem a possuir mais de uma palavra com 3+ letras.
            $meaningfulWords = count(array_filter(
                $words,
                fn (string $word) => mb_strlen($word) >= 3
            ));
            $score += $meaningfulWords * 2;

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $normalized;
            }
        }

        return $best;
    }

    private static function looksLikeNonNameLine(string $normalized): bool
    {
        $blockedWords = [
            'MERCADO', 'LIVRE', 'SHOPEE', 'AMAZON', 'CORREIOS', 'JADLOG', 'LOGGI',
            'EXPRESS', 'TRANSPORTADORA', 'DESTINATARIO', 'REMETENTE', 'ENDERECO',
            'CEP', 'CIDADE', 'DESTINO', 'COMPLEMENTO', 'REFERENCIA', 'LAVA', 'JATO',
            'RUA', 'AVENIDA', 'VENDA', 'SKU', 'AGENCIA', 'FAMPLEMNTO',
        ];

        $words = explode(' ', $normalized);
        foreach ($words as $word) {
            if (in_array($word, $blockedWords, true)) {
                return true;
            }

            // Corrige ruídos comuns do OCR em cabeçalhos, ex.: FAMPLEMNTO.
            foreach (['COMPLEMENTO', 'ENDERECO', 'REFERENCIA'] as $header) {
                if (mb_strlen($word) >= 7 && levenshtein($word, $header) <= 4) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function similarity(string $a, string $b): float
    {
        $a = self::normalizeName($a);
        $b = self::normalizeName($b);

        if ($a === '' || $b === '') {
            return 0.0;
        }

        if ($a === $b) {
            return 1.0;
        }

        similar_text($a, $b, $percent);
        $similar = $percent / 100;

        $maxLen = max(strlen($a), strlen($b));
        $lev = $maxLen > 0 ? 1 - (levenshtein($a, $b) / $maxLen) : 0.0;
        $lev = max(0.0, min(1.0, $lev));

        return round(($similar * 0.6) + ($lev * 0.4), 4);
    }

    /**
     * Similaridade de nomes com cobertura de tokens.
     * Ex.: "Tayna Fernandes" vs "Tayna Karine da Silva Fernandes Elk" → alta.
     */
    public static function nameSimilarity(string $a, string $b): float
    {
        $tokensA = array_values(array_filter(explode(' ', self::normalizeName($a))));
        $tokensB = array_values(array_filter(explode(' ', self::normalizeName($b))));

        if ($tokensA === [] || $tokensB === []) {
            return 0.0;
        }

        if ($tokensA === $tokensB) {
            return 1.0;
        }

        $shorter = count($tokensA) <= count($tokensB) ? $tokensA : $tokensB;
        $longer = count($tokensA) <= count($tokensB) ? $tokensB : $tokensA;

        $matched = 0;
        foreach ($shorter as $token) {
            if (self::tokenInList($token, $longer)) {
                $matched++;
            }
        }

        $coverage = $matched / count($shorter);
        $firstMatch = self::tokensClose($shorter[0], $longer[0]);
        $stringScore = self::similarity(implode(' ', $tokensA), implode(' ', $tokensB));

        // Nome cadastrado curto contido no nome da etiqueta (caso típico de portaria)
        if ($firstMatch && $coverage >= 1.0 && count($shorter) >= 2) {
            return round(max(0.94, $stringScore), 4);
        }

        if ($firstMatch && $coverage >= 0.66 && count($shorter) >= 2) {
            return round(max(0.82, ($coverage * 0.7) + ($stringScore * 0.3)), 4);
        }

        if ($coverage >= 0.5 && $firstMatch) {
            return round(max(0.65, ($coverage * 0.55) + ($stringScore * 0.45)), 4);
        }

        return round(($coverage * 0.45) + ($stringScore * 0.55), 4);
    }

    /**
     * @return list<string>
     */
    public static function significantNameTokens(?string $name): array
    {
        $tokens = array_values(array_filter(
            explode(' ', self::normalizeName($name)),
            fn (string $token) => mb_strlen($token) >= 3
        ));

        if ($tokens === []) {
            return [];
        }

        $first = array_shift($tokens);
        usort($tokens, fn (string $a, string $b) => mb_strlen($b) <=> mb_strlen($a));

        return array_values(array_unique(array_filter([
            $first,
            ...array_slice($tokens, 0, 3),
        ])));
    }

    /**
     * @param  list<string>  $list
     */
    private static function tokenInList(string $token, array $list): bool
    {
        foreach ($list as $candidate) {
            if (self::tokensClose($token, $candidate)) {
                return true;
            }
        }

        return false;
    }

    private static function tokensClose(string $a, string $b): bool
    {
        if ($a === $b) {
            return true;
        }

        $maxLen = max(mb_strlen($a), mb_strlen($b));
        if ($maxLen <= 3) {
            return false;
        }

        $distance = levenshtein($a, $b);

        return $distance <= 1 || ($distance / $maxLen) <= 0.2;
    }
}
