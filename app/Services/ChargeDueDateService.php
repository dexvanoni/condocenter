<?php

namespace App\Services;

use App\Models\Charge;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

class ChargeDueDateService
{
    public function __construct(
        private readonly ChargePaymentService $chargePaymentService,
    ) {
    }

    /**
     * Altera o vencimento de uma cobrança pendente/em atraso.
     *
     * O pagamento Asaas pendente (boleto/PIX antigo) é cancelado, pois deixa
     * de valer com a nova data; um novo é gerado quando o morador for pagar.
     */
    public function updateDueDate(Charge $charge, CarbonInterface $newDueDate): Charge
    {
        if (! in_array($charge->status, ['pending', 'overdue'], true)) {
            throw ValidationException::withMessages([
                'due_date' => 'Só é possível alterar o vencimento de cobranças pendentes ou em atraso.',
            ]);
        }

        $this->chargePaymentService->resetPendingAsaasPayment($charge);

        $charge->update([
            'due_date' => $newDueDate->toDateString(),
            'status' => 'pending',
        ]);

        return $charge->fresh();
    }
}
