<?php

namespace App\Services\BankStatement;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class CsvStatementParser
{
    /** @var list<string> */
    private const DATE_HEADERS = [
        'data', 'date', 'dt', 'data lancamento', 'data lançamento', 'data movimento',
        'posted', 'data_postagem', 'dtposted', 'data contábil', 'data contabil',
    ];

    /** @var list<string> */
    private const DESCRIPTION_HEADERS = [
        'descricao', 'descrição', 'description', 'historico', 'histórico', 'memo',
        'detalhe', 'lançamento', 'lancamento', 'nome', 'name', 'payee',
    ];

    /** @var list<string> */
    private const AMOUNT_HEADERS = [
        'valor', 'amount', 'value', 'vlr', 'montante',
    ];

    /** @var list<string> */
    private const DEBIT_HEADERS = [
        'debito', 'débito', 'debit', 'saida', 'saída', 'withdrawal',
    ];

    /** @var list<string> */
    private const CREDIT_HEADERS = [
        'credito', 'crédito', 'credit', 'entrada', 'deposit',
    ];

    /**
     * @param  array{date?: int|string, description?: int|string, amount?: int|string, debit?: int|string, credit?: int|string}|null  $mapping
     */
    public function parse(string $contents, ?array $mapping = null): ParsedStatement
    {
        $contents = $this->normalizeEncoding($contents);
        $delimiter = $this->detectDelimiter($contents);
        $rows = $this->readRows($contents, $delimiter);

        if (count($rows) < 2) {
            throw ValidationException::withMessages([
                'file' => 'O CSV precisa de cabeçalho e ao menos uma linha de movimentação.',
            ]);
        }

        $header = array_map(fn ($cell) => $this->normalizeHeader((string) $cell), $rows[0]);
        $resolved = $mapping !== null
            ? $this->resolveManualMapping($header, $mapping)
            : $this->detectMapping($header);

        if ($resolved === null) {
            return new ParsedStatement(
                lines: [],
                detectedHeaders: $rows[0],
                needsMapping: true,
            );
        }

        $lines = [];
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $line = $this->mapRow($row, $resolved);
            if ($line !== null) {
                $lines[] = $line;
            }
        }

        if ($lines === []) {
            throw ValidationException::withMessages([
                'file' => 'Nenhuma movimentação válida encontrada no CSV.',
            ]);
        }

        $dates = collect($lines)->pluck('posted_at');

        return new ParsedStatement(
            lines: $lines,
            periodStart: $dates->min()?->copy(),
            periodEnd: $dates->max()?->copy(),
            detectedHeaders: $rows[0],
        );
    }

    /**
     * @param  list<string>  $header
     * @return array{date: int, description: int, amount: ?int, debit: ?int, credit: ?int}|null
     */
    protected function detectMapping(array $header): ?array
    {
        $date = $this->findHeaderIndex($header, self::DATE_HEADERS);
        $description = $this->findHeaderIndex($header, self::DESCRIPTION_HEADERS);
        $amount = $this->findHeaderIndex($header, self::AMOUNT_HEADERS);
        $debit = $this->findHeaderIndex($header, self::DEBIT_HEADERS);
        $credit = $this->findHeaderIndex($header, self::CREDIT_HEADERS);

        if ($date !== null && $description !== null && ($amount !== null || ($debit !== null && $credit !== null))) {
            return [
                'date' => $date,
                'description' => $description,
                'amount' => $amount,
                'debit' => $debit,
                'credit' => $credit,
            ];
        }

        // Fallback posicional clássico: data, descrição, valor
        if (count($header) >= 3) {
            return [
                'date' => 0,
                'description' => 1,
                'amount' => 2,
                'debit' => null,
                'credit' => null,
            ];
        }

        return null;
    }

    /**
     * @param  list<string>  $header
     * @param  array{date?: int|string, description?: int|string, amount?: int|string, debit?: int|string, credit?: int|string}  $mapping
     * @return array{date: int, description: int, amount: ?int, debit: ?int, credit: ?int}
     */
    protected function resolveManualMapping(array $header, array $mapping): array
    {
        $date = isset($mapping['date']) ? (int) $mapping['date'] : null;
        $description = isset($mapping['description']) ? (int) $mapping['description'] : null;
        $amount = array_key_exists('amount', $mapping) && $mapping['amount'] !== '' && $mapping['amount'] !== null
            ? (int) $mapping['amount']
            : null;
        $debit = array_key_exists('debit', $mapping) && $mapping['debit'] !== '' && $mapping['debit'] !== null
            ? (int) $mapping['debit']
            : null;
        $credit = array_key_exists('credit', $mapping) && $mapping['credit'] !== '' && $mapping['credit'] !== null
            ? (int) $mapping['credit']
            : null;

        if ($date === null || $description === null) {
            throw ValidationException::withMessages([
                'mapping' => 'Informe as colunas de data e descrição.',
            ]);
        }

        if ($amount === null && ($debit === null || $credit === null)) {
            throw ValidationException::withMessages([
                'mapping' => 'Informe a coluna de valor ou as colunas de débito e crédito.',
            ]);
        }

        $max = count($header) - 1;
        foreach (['date' => $date, 'description' => $description, 'amount' => $amount, 'debit' => $debit, 'credit' => $credit] as $key => $index) {
            if ($index !== null && ($index < 0 || $index > $max)) {
                throw ValidationException::withMessages([
                    'mapping' => "Índice de coluna inválido para {$key}.",
                ]);
            }
        }

        return [
            'date' => $date,
            'description' => $description,
            'amount' => $amount,
            'debit' => $debit,
            'credit' => $credit,
        ];
    }

    /**
     * @param  list<string|null>  $row
     * @param  array{date: int, description: int, amount: ?int, debit: ?int, credit: ?int}  $mapping
     */
    protected function mapRow(array $row, array $mapping): ?array
    {
        $dateRaw = $row[$mapping['date']] ?? null;
        $description = trim((string) ($row[$mapping['description']] ?? ''));

        if ($dateRaw === null || trim((string) $dateRaw) === '') {
            return null;
        }

        $postedAt = $this->parseDate(trim((string) $dateRaw));
        if ($postedAt === null) {
            return null;
        }

        if ($mapping['amount'] !== null) {
            $amount = $this->parseAmount((string) ($row[$mapping['amount']] ?? '0'));
        } else {
            $debit = $this->parseAmount((string) ($row[$mapping['debit']] ?? '0'));
            $credit = $this->parseAmount((string) ($row[$mapping['credit']] ?? '0'));
            $amount = round($credit - abs($debit), 2);
        }

        if ($amount == 0.0 && $description === '') {
            return null;
        }

        return [
            'posted_at' => $postedAt,
            'amount' => $amount,
            'description' => $description !== '' ? $description : 'Movimentação bancária',
            'fitid' => null,
            'trn_type' => $amount >= 0 ? 'CREDIT' : 'DEBIT',
        ];
    }

    protected function parseDate(string $raw): ?Carbon
    {
        $raw = trim($raw);
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'd/m/y', 'Y/m/d', 'm/d/Y'];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $raw);
                if ($date !== false) {
                    return $date->startOfDay();
                }
            } catch (\Throwable) {
                // try next
            }
        }

        try {
            return Carbon::parse($raw)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function parseAmount(string $raw): float
    {
        $raw = trim($raw);
        if ($raw === '' || $raw === '-') {
            return 0.0;
        }

        $negative = false;
        if (str_starts_with($raw, '(') && str_ends_with($raw, ')')) {
            $negative = true;
            $raw = substr($raw, 1, -1);
        }

        $raw = str_replace(['R$', ' ', "\xc2\xa0"], '', $raw);

        if (str_contains($raw, ',') && str_contains($raw, '.')) {
            if (strrpos($raw, ',') > strrpos($raw, '.')) {
                $raw = str_replace('.', '', $raw);
                $raw = str_replace(',', '.', $raw);
            } else {
                $raw = str_replace(',', '', $raw);
            }
        } elseif (str_contains($raw, ',')) {
            $raw = str_replace(',', '.', $raw);
        }

        $value = (float) $raw;
        if ($negative) {
            $value = -abs($value);
        }

        return round($value, 2);
    }

    protected function detectDelimiter(string $contents): string
    {
        $firstLine = strtok($contents, "\r\n") ?: '';
        $semicolons = substr_count($firstLine, ';');
        $commas = substr_count($firstLine, ',');

        return $semicolons > $commas ? ';' : ',';
    }

    /**
     * @return list<list<string|null>>
     */
    protected function readRows(string $contents, string $delimiter): array
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $contents);
        rewind($handle);

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    protected function normalizeHeader(string $header): string
    {
        $header = mb_strtolower(trim($header));
        $header = str_replace(['_', '-'], ' ', $header);
        $header = preg_replace('/\s+/', ' ', $header) ?? $header;

        return $header;
    }

    /**
     * @param  list<string>  $header
     * @param  list<string>  $candidates
     */
    protected function findHeaderIndex(array $header, array $candidates): ?int
    {
        foreach ($header as $index => $name) {
            if (in_array($name, $candidates, true)) {
                return $index;
            }
        }

        return null;
    }

    /**
     * @param  list<string|null>  $row
     */
    protected function rowIsEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function normalizeEncoding(string $contents): string
    {
        if (! mb_check_encoding($contents, 'UTF-8')) {
            $converted = @mb_convert_encoding($contents, 'UTF-8', 'Windows-1252');
            if ($converted !== false) {
                return $converted;
            }
        }

        // Remove BOM
        return preg_replace('/^\xEF\xBB\xBF/', '', $contents) ?? $contents;
    }
}
