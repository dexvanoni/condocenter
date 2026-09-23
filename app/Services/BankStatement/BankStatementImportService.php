<?php

namespace App\Services\BankStatement;

use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\BankStatementLine;
use App\Models\CondominiumAccount;
use App\Models\User;
use App\Support\ExpenseCategories;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BankStatementImportService
{
    public function __construct(
        private readonly OfxStatementParser $ofxParser,
        private readonly CsvStatementParser $csvParser,
        private readonly BankStatementMatcher $matcher,
    ) {
    }

    /**
     * @param  array{date?: int|string, description?: int|string, amount?: int|string, debit?: int|string, credit?: int|string}|null  $csvMapping
     * @return array{statement: BankStatement, needs_mapping: bool, headers: ?array}
     */
    public function import(
        User $user,
        BankAccount $account,
        UploadedFile $file,
        ?array $csvMapping = null,
    ): array {
        $extension = strtolower($file->getClientOriginalExtension() ?: '');
        $contents = file_get_contents($file->getRealPath());

        if ($contents === false || trim($contents) === '') {
            throw ValidationException::withMessages([
                'file' => 'Não foi possível ler o arquivo enviado.',
            ]);
        }

        $format = $this->detectFormat($extension, $contents);
        $hash = hash('sha256', $contents);

        $duplicate = BankStatement::query()
            ->where('bank_account_id', $account->id)
            ->where('file_hash', $hash)
            ->whereNull('deleted_at')
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'file' => 'Este extrato já foi importado para esta conta.',
            ]);
        }

        $parsed = $format === BankStatement::FORMAT_OFX
            ? $this->ofxParser->parse($contents)
            : $this->csvParser->parse($contents, $csvMapping);

        if ($parsed->needsMapping) {
            $tempPath = $file->storeAs(
                'bank-statements/pending/'.$account->id,
                $hash.'.csv',
                'local'
            );

            return [
                'statement' => null,
                'needs_mapping' => true,
                'headers' => $parsed->detectedHeaders,
                'temp_path' => $tempPath,
                'file_hash' => $hash,
                'original_filename' => $file->getClientOriginalName(),
            ];
        }

        return [
            'statement' => $this->persistParsed(
                $user,
                $account,
                $file->getClientOriginalName(),
                $contents,
                $format,
                $hash,
                $parsed
            ),
            'needs_mapping' => false,
            'headers' => $parsed->detectedHeaders,
        ];
    }

    /**
     * @param  array{date?: int|string, description?: int|string, amount?: int|string, debit?: int|string, credit?: int|string}  $csvMapping
     */
    public function importFromStoredPath(
        User $user,
        BankAccount $account,
        string $tempPath,
        string $originalFilename,
        string $fileHash,
        array $csvMapping,
    ): BankStatement {
        if (! Storage::disk('local')->exists($tempPath)) {
            throw ValidationException::withMessages([
                'file' => 'Arquivo temporário expirado. Envie o CSV novamente.',
            ]);
        }

        $contents = Storage::disk('local')->get($tempPath);
        $parsed = $this->csvParser->parse($contents, $csvMapping);

        if ($parsed->needsMapping || $parsed->lines === []) {
            throw ValidationException::withMessages([
                'mapping' => 'Não foi possível aplicar o mapeamento informado.',
            ]);
        }

        $statement = $this->persistParsed(
            $user,
            $account,
            $originalFilename,
            $contents,
            BankStatement::FORMAT_CSV,
            $fileHash,
            $parsed
        );

        Storage::disk('local')->delete($tempPath);

        return $statement;
    }

    public function createEntryFromLine(
        User $user,
        BankStatementLine $line,
        string $type,
        ?string $description = null,
        ?string $paymentMethod = null,
        ?string $notes = null,
        ?string $category = null,
    ): CondominiumAccount {
        if ($line->isLinked() || $line->status === BankStatementLine::STATUS_IGNORED) {
            throw ValidationException::withMessages([
                'line' => 'Esta linha já foi tratada.',
            ]);
        }

        if (! in_array($type, ['income', 'expense'], true)) {
            throw ValidationException::withMessages([
                'type' => 'Informe se é receita ou despesa.',
            ]);
        }

        $expectedDirection = $line->direction();
        if ($type !== $expectedDirection) {
            throw ValidationException::withMessages([
                'type' => sprintf(
                    'A linha do extrato é uma %s. Selecione o tipo correspondente.',
                    $expectedDirection === 'income' ? 'entrada' : 'saída'
                ),
            ]);
        }

        if ($type === 'expense' && ($category === null || $category === '')) {
            throw ValidationException::withMessages([
                'category' => 'Informe a categoria da despesa para o dashboard e as previsões.',
            ]);
        }

        if ($category !== null && $category !== '' && ! ExpenseCategories::isValid($category)) {
            throw ValidationException::withMessages([
                'category' => 'Categoria inválida.',
            ]);
        }

        return DB::transaction(function () use ($user, $line, $type, $description, $paymentMethod, $notes, $category) {
            $entry = CondominiumAccount::create([
                'condominium_id' => $line->statement->condominium_id,
                'bank_account_id' => $line->bank_account_id,
                'type' => $type,
                'status' => CondominiumAccount::STATUS_ACTIVE,
                'source_type' => 'bank_statement_line',
                'source_id' => $line->id,
                'description' => $description ?: ($line->description ?: 'Lançamento do extrato'),
                'amount' => $line->absoluteAmount(),
                'transaction_date' => $line->posted_at->toDateString(),
                'payment_method' => $paymentMethod ?: 'bank_transfer',
                'category' => $type === 'expense' ? $category : null,
                'notes' => $notes,
                'created_by' => $user->id,
            ]);

            $this->matcher->markCreated($line, $entry);

            return $entry;
        });
    }

    protected function persistParsed(
        User $user,
        BankAccount $account,
        string $originalFilename,
        string $contents,
        string $format,
        string $hash,
        ParsedStatement $parsed,
    ): BankStatement {
        $this->assertNoDuplicateFitids($account->id, $parsed->lines);

        return DB::transaction(function () use ($user, $account, $originalFilename, $contents, $format, $hash, $parsed) {
            $path = sprintf(
                'bank-statements/%d/%s.%s',
                $account->id,
                $hash,
                $format === BankStatement::FORMAT_OFX ? 'ofx' : 'csv'
            );

            Storage::disk('local')->put($path, $contents);

            $statement = BankStatement::create([
                'condominium_id' => $account->condominium_id,
                'bank_account_id' => $account->id,
                'uploaded_by' => $user->id,
                'original_filename' => $originalFilename,
                'storage_path' => $path,
                'format' => $format,
                'file_hash' => $hash,
                'statement_date' => now()->toDateString(),
                'period_start' => $parsed->periodStart?->toDateString(),
                'period_end' => $parsed->periodEnd?->toDateString(),
                'opening_balance' => $parsed->openingBalance,
                'closing_balance' => $parsed->closingBalance,
                'status' => BankStatement::STATUS_PROCESSING,
                'total_transactions' => count($parsed->lines),
                'reconciled_transactions' => 0,
            ]);

            $rows = [];
            foreach ($parsed->lines as $index => $line) {
                $rows[] = [
                    'bank_statement_id' => $statement->id,
                    'bank_account_id' => $account->id,
                    'line_order' => $index,
                    'posted_at' => $line['posted_at']->toDateString(),
                    'amount' => $line['amount'],
                    'description' => $line['description'],
                    'fitid' => $line['fitid'],
                    'trn_type' => $line['trn_type'],
                    'status' => BankStatementLine::STATUS_UNMATCHED,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            BankStatementLine::insert($rows);

            $this->matcher->match($statement->fresh('lines'));

            return $statement->fresh(['lines', 'bankAccount']);
        });
    }

    /**
     * @param  list<array{fitid: ?string}>  $lines
     */
    protected function assertNoDuplicateFitids(int $bankAccountId, array $lines): void
    {
        $fitids = collect($lines)
            ->pluck('fitid')
            ->filter()
            ->values();

        if ($fitids->isEmpty()) {
            return;
        }

        $duplicatesInFile = $fitids->duplicates()->unique()->values();
        if ($duplicatesInFile->isNotEmpty()) {
            throw ValidationException::withMessages([
                'file' => 'O extrato contém FITIDs duplicados: '.$duplicatesInFile->implode(', '),
            ]);
        }

        $existing = BankStatementLine::query()
            ->where('bank_account_id', $bankAccountId)
            ->whereIn('fitid', $fitids->all())
            ->pluck('fitid');

        if ($existing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'file' => 'Algumas movimentações deste extrato já foram importadas (FITID).',
            ]);
        }
    }

    protected function detectFormat(string $extension, string $contents): string
    {
        if (in_array($extension, ['ofx', 'qfx'], true)) {
            return BankStatement::FORMAT_OFX;
        }

        if (in_array($extension, ['csv', 'txt'], true)) {
            return BankStatement::FORMAT_CSV;
        }

        if (stripos($contents, '<OFX') !== false || stripos($contents, 'OFXHEADER') !== false) {
            return BankStatement::FORMAT_OFX;
        }

        return BankStatement::FORMAT_CSV;
    }
}
