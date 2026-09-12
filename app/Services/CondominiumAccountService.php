<?php

namespace App\Services;

use App\Models\CondominiumAccount;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CondominiumAccountService
{
    public function cancelManualExpense(CondominiumAccount $account, User $user, string $reason): CondominiumAccount
    {
        if ($account->condominium_id !== $user->tenantCondominiumId()) {
            throw ValidationException::withMessages([
                'expense' => 'Este pagamento não pertence ao seu condomínio.',
            ]);
        }

        if ($account->reconciliation_id !== null) {
            throw ValidationException::withMessages([
                'expense' => 'Este pagamento já foi conciliado. Cancele a conciliação bancária antes de cancelar o lançamento.',
            ]);
        }

        if (! $account->isCancellableManualExpense()) {
            throw ValidationException::withMessages([
                'expense' => 'Este pagamento não pode ser cancelado.',
            ]);
        }

        $account->update([
            'status' => CondominiumAccount::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by' => $user->id,
            'cancellation_reason' => $reason,
        ]);

        return $account->fresh(['creator', 'cancelledBy']);
    }
}
