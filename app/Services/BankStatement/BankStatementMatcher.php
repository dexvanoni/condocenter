<?php

namespace App\Services\BankStatement;

use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\BankStatementLine;
use App\Models\CondominiumAccount;
use App\Models\Transaction;
use App\Services\BankAccountRoutingService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BankStatementMatcher
{
    private const DATE_TOLERANCE_DAYS = 3;

    private const AMOUNT_TOLERANCE = 0.50;

    public function __construct(
        private readonly BankAccountRoutingService $bankAccountRoutingService,
    ) {
    }

    public function match(BankStatement $statement): void
    {
        $statement->loadMissing(['lines', 'bankAccount']);
        $account = $statement->bankAccount;

        if (!$account) {
            return;
        }

        $candidates = $this->eligibleCandidates(
            (int) $statement->condominium_id,
            $account,
            $statement->period_start?->copy()->subDays(self::DATE_TOLERANCE_DAYS),
            $statement->period_end?->copy()->addDays(self::DATE_TOLERANCE_DAYS),
        );

        $usedKeys = $this->alreadyMatchedKeys($account->id);

        DB::transaction(function () use ($statement, $candidates, &$usedKeys) {
            foreach ($statement->lines as $line) {
                if ($line->isLinked() || $line->status === BankStatementLine::STATUS_IGNORED) {
                    continue;
                }

                $this->matchLine($line, $candidates, $usedKeys);
            }

            $this->refreshStatementCounters($statement);
        });
    }

    /**
     * @param  Collection<int, array{key: string, source_type: string, source_id: int, direction: string, amount: float, date: Carbon, label: string}>  $candidates
     * @param  array<string, true>  $usedKeys
     */
    protected function matchLine(BankStatementLine $line, Collection $candidates, array &$usedKeys): void
    {
        $direction = $line->direction();
        $amount = $line->absoluteAmount();
        $date = $line->posted_at->copy()->startOfDay();

        $available = $candidates->filter(function (array $candidate) use ($direction, $usedKeys) {
            return $candidate['direction'] === $direction
                && ! isset($usedKeys[$candidate['key']]);
        });

        $exact = $available->filter(function (array $candidate) use ($amount, $date) {
            return $this->amountsEqual($candidate['amount'], $amount)
                && $candidate['date']->equalTo($date);
        })->values();

        if ($exact->count() === 1) {
            $this->linkLine($line, $exact->first(), BankStatementLine::STATUS_AUTO_MATCHED);
            $usedKeys[$exact->first()['key']] = true;

            return;
        }

        if ($exact->count() > 1) {
            $suggestion = $exact->first();
            $this->suggestLine($line, $suggestion, [
                'reason' => 'ambiguous_exact',
                'candidates_count' => $exact->count(),
            ]);

            return;
        }

        $fuzzy = $available->filter(function (array $candidate) use ($amount, $date) {
            $sameAmount = $this->amountsEqual($candidate['amount'], $amount);
            $days = abs($candidate['date']->diffInDays($date));
            $amountDiff = abs($candidate['amount'] - $amount);

            $nearDateSameAmount = $sameAmount && $days <= self::DATE_TOLERANCE_DAYS;
            $sameDateNearAmount = $candidate['date']->equalTo($date)
                && $amountDiff <= self::AMOUNT_TOLERANCE
                && ! $sameAmount;

            return $nearDateSameAmount || $sameDateNearAmount;
        })->sortBy(function (array $candidate) use ($amount, $date) {
            $days = abs($candidate['date']->diffInDays($date));
            $amountDiff = abs($candidate['amount'] - $amount);

            return ($days * 1000) + ($amountDiff * 100);
        })->values();

        if ($fuzzy->isNotEmpty()) {
            $this->suggestLine($line, $fuzzy->first(), [
                'reason' => 'fuzzy',
                'candidates_count' => $fuzzy->count(),
            ]);

            return;
        }

        $line->forceFill([
            'status' => BankStatementLine::STATUS_UNMATCHED,
            'matched_source_type' => null,
            'matched_source_id' => null,
            'suggested_source_type' => null,
            'suggested_source_id' => null,
            'suggestion_meta' => null,
        ])->save();
    }

    /**
     * @param  array{key: string, source_type: string, source_id: int, direction: string, amount: float, date: Carbon, label: string}  $candidate
     */
    public function confirm(BankStatementLine $line, string $sourceType, int $sourceId): BankStatementLine
    {
        $candidate = $this->findCandidateBySource(
            (int) $line->statement->condominium_id,
            $line->bankAccount,
            $sourceType,
            $sourceId
        );

        if ($candidate === null) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'match' => 'Lançamento do sistema não está elegível para vínculo.',
            ]);
        }

        if ($candidate['direction'] !== $line->direction()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'match' => 'A direção do lançamento não corresponde à linha do extrato.',
            ]);
        }

        $this->ensureSourceNotLinked($line->bank_account_id, $sourceType, $sourceId, $line->id);

        $this->linkLine($line, $candidate, BankStatementLine::STATUS_CONFIRMED);
        $this->refreshStatementCounters($line->statement);

        return $line->fresh();
    }

    public function acceptSuggestion(BankStatementLine $line): BankStatementLine
    {
        if ($line->status !== BankStatementLine::STATUS_SUGGESTED
            || ! $line->suggested_source_type
            || ! $line->suggested_source_id) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'match' => 'Esta linha não possui sugestão para confirmar.',
            ]);
        }

        return $this->confirm($line, $line->suggested_source_type, (int) $line->suggested_source_id);
    }

    public function unlink(BankStatementLine $line): BankStatementLine
    {
        $line->forceFill([
            'status' => BankStatementLine::STATUS_UNMATCHED,
            'matched_source_type' => null,
            'matched_source_id' => null,
            'suggested_source_type' => null,
            'suggested_source_id' => null,
            'suggestion_meta' => null,
        ])->save();

        $this->match(BankStatement::findOrFail($line->bank_statement_id));

        return $line->fresh();
    }

    public function ignore(BankStatementLine $line): BankStatementLine
    {
        $line->forceFill([
            'status' => BankStatementLine::STATUS_IGNORED,
            'matched_source_type' => null,
            'matched_source_id' => null,
            'suggested_source_type' => null,
            'suggested_source_id' => null,
            'suggestion_meta' => null,
        ])->save();

        $this->refreshStatementCounters($line->statement);

        return $line->fresh();
    }

    public function markCreated(BankStatementLine $line, CondominiumAccount $entry): BankStatementLine
    {
        $line->forceFill([
            'status' => BankStatementLine::STATUS_CREATED,
            'matched_source_type' => BankStatementLine::SOURCE_CONDOMINIUM_ACCOUNT,
            'matched_source_id' => $entry->id,
            'suggested_source_type' => null,
            'suggested_source_id' => null,
            'suggestion_meta' => null,
        ])->save();

        $this->refreshStatementCounters($line->statement);

        return $line->fresh();
    }

    /**
     * @return Collection<int, array{key: string, source_type: string, source_id: int, direction: string, amount: float, date: Carbon, label: string}>
     */
    public function eligibleCandidates(
        int $condominiumId,
        BankAccount $account,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
    ): Collection {
        $transactions = Transaction::query()
            ->where('condominium_id', $condominiumId)
            ->whereNull('reconciliation_id')
            ->where('status', 'paid')
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('transaction_date', [$startDate, $endDate]))
            ->get()
            ->filter(fn (Transaction $t) => (int) $this->bankAccountRoutingService->resolveForTransaction($t) === (int) $account->id)
            ->map(fn (Transaction $t) => [
                'key' => 'transaction:'.$t->id,
                'source_type' => BankStatementLine::SOURCE_TRANSACTION,
                'source_id' => $t->id,
                'direction' => $t->type,
                'amount' => (float) $t->amount,
                'date' => $t->transaction_date->copy()->startOfDay(),
                'label' => $t->description ?: 'Transação #'.$t->id,
            ]);

        $accounts = CondominiumAccount::query()
            ->where('condominium_id', $condominiumId)
            ->whereNull('reconciliation_id')
            ->countsInBalance()
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('transaction_date', [$startDate, $endDate]))
            ->get()
            ->filter(fn (CondominiumAccount $e) => (int) $this->bankAccountRoutingService->resolveForCondominiumAccount($e) === (int) $account->id)
            ->map(fn (CondominiumAccount $e) => [
                'key' => 'condominium_account:'.$e->id,
                'source_type' => BankStatementLine::SOURCE_CONDOMINIUM_ACCOUNT,
                'source_id' => $e->id,
                'direction' => $e->type,
                'amount' => (float) $e->amount,
                'date' => $e->transaction_date->copy()->startOfDay(),
                'label' => $e->description ?: 'Caixa #'.$e->id,
            ]);

        return $transactions->concat($accounts)->values();
    }

    /**
     * @return list<array{source_type: string, source_id: int}>
     */
    public function linkedSourcePairs(int $bankAccountId, ?Carbon $start = null, ?Carbon $end = null): array
    {
        return BankStatementLine::query()
            ->where('bank_account_id', $bankAccountId)
            ->linked()
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->whereBetween('posted_at', [$start->toDateString(), $end->toDateString()]);
            })
            ->get(['matched_source_type', 'matched_source_id'])
            ->map(fn (BankStatementLine $line) => [
                'source_type' => $line->matched_source_type,
                'source_id' => (int) $line->matched_source_id,
            ])
            ->all();
    }

    public function refreshStatementCounters(BankStatement $statement): void
    {
        $statement->refresh();
        $lines = $statement->lines()->get();
        $linked = $lines->filter(fn (BankStatementLine $line) => $line->isLinked())->count();

        $statement->forceFill([
            'total_transactions' => $lines->count(),
            'reconciled_transactions' => $linked,
            'status' => BankStatement::STATUS_READY,
        ])->save();
    }

    /**
     * @param  array{key: string, source_type: string, source_id: int, direction: string, amount: float, date: Carbon, label: string}  $candidate
     */
    protected function linkLine(BankStatementLine $line, array $candidate, string $status): void
    {
        $line->forceFill([
            'status' => $status,
            'matched_source_type' => $candidate['source_type'],
            'matched_source_id' => $candidate['source_id'],
            'suggested_source_type' => null,
            'suggested_source_id' => null,
            'suggestion_meta' => null,
        ])->save();
    }

    /**
     * @param  array{key: string, source_type: string, source_id: int, direction: string, amount: float, date: Carbon, label: string}  $candidate
     * @param  array<string, mixed>  $meta
     */
    protected function suggestLine(BankStatementLine $line, array $candidate, array $meta): void
    {
        $line->forceFill([
            'status' => BankStatementLine::STATUS_SUGGESTED,
            'matched_source_type' => null,
            'matched_source_id' => null,
            'suggested_source_type' => $candidate['source_type'],
            'suggested_source_id' => $candidate['source_id'],
            'suggestion_meta' => array_merge($meta, [
                'label' => $candidate['label'],
                'amount' => $candidate['amount'],
                'date' => $candidate['date']->toDateString(),
            ]),
        ])->save();
    }

    /**
     * @return array{key: string, source_type: string, source_id: int, direction: string, amount: float, date: Carbon, label: string}|null
     */
    protected function findCandidateBySource(
        int $condominiumId,
        BankAccount $account,
        string $sourceType,
        int $sourceId,
    ): ?array {
        return $this->eligibleCandidates($condominiumId, $account)
            ->first(fn (array $c) => $c['source_type'] === $sourceType && $c['source_id'] === $sourceId);
    }

    protected function ensureSourceNotLinked(int $bankAccountId, string $sourceType, int $sourceId, ?int $exceptLineId = null): void
    {
        $exists = BankStatementLine::query()
            ->where('bank_account_id', $bankAccountId)
            ->linked()
            ->where('matched_source_type', $sourceType)
            ->where('matched_source_id', $sourceId)
            ->when($exceptLineId, fn ($q) => $q->where('id', '!=', $exceptLineId))
            ->exists();

        if ($exists) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'match' => 'Este lançamento já está vinculado a outra linha do extrato.',
            ]);
        }
    }

    /**
     * @return array<string, true>
     */
    protected function alreadyMatchedKeys(int $bankAccountId): array
    {
        $keys = [];
        foreach ($this->linkedSourcePairs($bankAccountId) as $pair) {
            $keys[$pair['source_type'].':'.$pair['source_id']] = true;
        }

        return $keys;
    }

    protected function amountsEqual(float $a, float $b): bool
    {
        return abs($a - $b) < 0.005;
    }
}
