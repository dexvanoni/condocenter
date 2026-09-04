<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\BankAccountRoutingRule;
use App\Models\Charge;
use App\Models\CondominiumAccount;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BankAccountRoutingService
{
    public function rulesForCondominium(int $condominiumId): Collection
    {
        return BankAccountRoutingRule::query()
            ->with('bankAccount')
            ->where('condominium_id', $condominiumId)
            ->orderBy('source_key')
            ->get()
            ->keyBy('source_key');
    }

    public function accountsForCondominium(int $condominiumId): Collection
    {
        return BankAccount::query()
            ->where('condominium_id', $condominiumId)
            ->where('active', true)
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get();
    }

    public function primaryAccount(int $condominiumId): ?BankAccount
    {
        return BankAccount::query()
            ->where('condominium_id', $condominiumId)
            ->where('active', true)
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->first();
    }

    public function syncRules(int $condominiumId, array $rules): void
    {
        $validKeys = array_keys(BankAccountRoutingRule::SOURCE_KEYS);
        $accountIds = BankAccount::query()
            ->where('condominium_id', $condominiumId)
            ->pluck('id')
            ->all();

        foreach ($rules as $sourceKey => $bankAccountId) {
            if (!in_array($sourceKey, $validKeys, true)) {
                continue;
            }

            if (empty($bankAccountId)) {
                BankAccountRoutingRule::query()
                    ->where('condominium_id', $condominiumId)
                    ->where('source_key', $sourceKey)
                    ->delete();
                continue;
            }

            if (!in_array((int) $bankAccountId, $accountIds, true)) {
                throw ValidationException::withMessages([
                    "rules.{$sourceKey}" => 'Conta bancária inválida para este condomínio.',
                ]);
            }

            BankAccountRoutingRule::updateOrCreate(
                [
                    'condominium_id' => $condominiumId,
                    'source_key' => $sourceKey,
                ],
                [
                    'bank_account_id' => (int) $bankAccountId,
                ]
            );
        }
    }

    public function resolveForCharge(Charge $charge): ?int
    {
        $charge->loadMissing('fee');

        if ($charge->service_order_id) {
            return $this->resolveByKey($charge->condominium_id, 'service_order');
        }

        if ($charge->generated_by === 'fine' || !empty($charge->metadata['fine_id'])) {
            return $this->resolveByKey($charge->condominium_id, 'fine');
        }

        if ($charge->generated_by === 'reservation' || !empty($charge->metadata['reservation_id'])) {
            return $this->resolveByKey($charge->condominium_id, 'reservation');
        }

        if ($charge->fee_id && $charge->fee?->bank_account_id) {
            return (int) $charge->fee->bank_account_id;
        }

        return $this->resolveByKey($charge->condominium_id, 'condominium_fee');
    }

    public function resolveForCondominiumAccount(CondominiumAccount $entry): ?int
    {
        if ($entry->bank_account_id) {
            return (int) $entry->bank_account_id;
        }

        if ($entry->source_type === 'charge' && $entry->source_id) {
            $charge = Charge::with('fee')->find($entry->source_id);

            return $charge ? $this->resolveForCharge($charge) : null;
        }

        if ($entry->type === 'income') {
            return $this->resolveByKey($entry->condominium_id, 'manual_income');
        }

        return $this->resolveByKey($entry->condominium_id, 'expense');
    }

    public function resolveForTransaction(Transaction $transaction): ?int
    {
        if ($transaction->bank_account_id) {
            return (int) $transaction->bank_account_id;
        }

        $key = $transaction->type === 'income' ? 'transaction_income' : 'transaction_expense';

        return $this->resolveByKey($transaction->condominium_id, $key);
    }

    public function resolveByKey(int $condominiumId, string $sourceKey): ?int
    {
        $rule = BankAccountRoutingRule::query()
            ->where('condominium_id', $condominiumId)
            ->where('source_key', $sourceKey)
            ->value('bank_account_id');

        if ($rule) {
            return (int) $rule;
        }

        return $this->primaryAccount($condominiumId)?->id;
    }
}
