<?php

namespace App\Services\BankStatement;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class OfxStatementParser
{
    public function parse(string $contents): ParsedStatement
    {
        $contents = $this->normalizeEncoding($contents);

        if (stripos($contents, '<OFX') === false && stripos($contents, '<STMTTRN') === false) {
            throw ValidationException::withMessages([
                'file' => 'O arquivo OFX não contém transações reconhecíveis.',
            ]);
        }

        $lines = [];
        $order = 0;

        if (preg_match_all('/<STMTTRN>(.*?)<\/STMTTRN>/is', $contents, $matches)) {
            foreach ($matches[1] as $block) {
                $line = $this->parseTransactionBlock($block);
                if ($line !== null) {
                    $lines[] = $line;
                    $order++;
                }
            }
        } else {
            // OFX 1 SGML sem fechamento de STMTTRN
            if (preg_match_all('/<STMTTRN>(.*?)(?=<STMTTRN>|<\/BANKTRANLIST>|$)/is', $contents, $sgmlMatches)) {
                foreach ($sgmlMatches[1] as $block) {
                    $line = $this->parseTransactionBlock($block);
                    if ($line !== null) {
                        $lines[] = $line;
                    }
                }
            }
        }

        if ($lines === []) {
            throw ValidationException::withMessages([
                'file' => 'Nenhuma movimentação encontrada no arquivo OFX.',
            ]);
        }

        $dates = collect($lines)->pluck('posted_at');
        $closingBalance = $this->extractBalance($contents, 'LEDGERBAL')
            ?? $this->extractBalance($contents, 'BALAMT');

        return new ParsedStatement(
            lines: $lines,
            periodStart: $dates->min()?->copy(),
            periodEnd: $dates->max()?->copy(),
            openingBalance: null,
            closingBalance: $closingBalance,
        );
    }

    protected function parseTransactionBlock(string $block): ?array
    {
        $amountRaw = $this->tagValue($block, 'TRNAMT');
        $dateRaw = $this->tagValue($block, 'DTPOSTED');

        if ($amountRaw === null || $dateRaw === null) {
            return null;
        }

        $amount = $this->parseAmount($amountRaw);
        $postedAt = $this->parseOfxDate($dateRaw);

        if ($postedAt === null) {
            return null;
        }

        $description = $this->tagValue($block, 'MEMO')
            ?? $this->tagValue($block, 'NAME')
            ?? $this->tagValue($block, 'PAYEE')
            ?? 'Movimentação bancária';

        $fitid = $this->tagValue($block, 'FITID');
        $trnType = $this->tagValue($block, 'TRNTYPE');

        return [
            'posted_at' => $postedAt,
            'amount' => $amount,
            'description' => trim($description),
            'fitid' => $fitid !== null && $fitid !== '' ? $fitid : null,
            'trn_type' => $trnType,
        ];
    }

    protected function tagValue(string $block, string $tag): ?string
    {
        if (preg_match('/<'.$tag.'>([^<\r\n]+)/i', $block, $match)) {
            return trim(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        if (preg_match('/<'.$tag.'>\s*<!\[CDATA\[(.*?)\]\]>/is', $block, $cdata)) {
            return trim($cdata[1]);
        }

        return null;
    }

    protected function parseOfxDate(string $raw): ?Carbon
    {
        $raw = trim($raw);
        $digits = preg_replace('/[^0-9].*$/', '', $raw) ?? '';

        if (strlen($digits) >= 8) {
            try {
                return Carbon::createFromFormat('Ymd', substr($digits, 0, 8))->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    protected function parseAmount(string $raw): float
    {
        $raw = trim(str_replace([' ', "\t"], '', $raw));
        $raw = str_replace(',', '.', $raw);

        return round((float) $raw, 2);
    }

    protected function extractBalance(string $contents, string $tag): ?float
    {
        if ($tag === 'LEDGERBAL') {
            if (preg_match('/<LEDGERBAL>(.*?)<\/LEDGERBAL>/is', $contents, $block)
                || preg_match('/<LEDGERBAL>(.*?)(?=<|$)/is', $contents, $block)) {
                $amount = $this->tagValue($block[1], 'BALAMT');
                if ($amount !== null) {
                    return $this->parseAmount($amount);
                }
            }
        }

        $direct = $this->tagValue($contents, $tag);
        if ($direct !== null && is_numeric(str_replace(',', '.', $direct))) {
            return $this->parseAmount($direct);
        }

        return null;
    }

    protected function normalizeEncoding(string $contents): string
    {
        if (! mb_check_encoding($contents, 'UTF-8')) {
            $converted = @mb_convert_encoding($contents, 'UTF-8', 'Windows-1252');
            if ($converted !== false) {
                return $converted;
            }
        }

        return $contents;
    }
}
