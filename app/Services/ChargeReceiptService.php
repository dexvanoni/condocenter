<?php

namespace App\Services;

use App\Models\Charge;
use App\Support\CondominiumDocuments;
use App\Support\PaymentMethods;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ChargeReceiptService
{
    public function download(Charge $charge): Response
    {
        if ($charge->status !== 'paid') {
            throw ValidationException::withMessages([
                'charge' => 'Comprovante disponível apenas para cobranças pagas.',
            ]);
        }

        $charge->load([
            'condominium',
            'unit.morador',
            'fee:id,name,billing_type',
            'payments' => fn ($query) => $query->orderBy('payment_date')->orderBy('id'),
            'payments.user:id,name',
        ]);

        $data = $this->buildReceiptData($charge);
        $filename = sprintf(
            'comprovante_cobranca_%d_%s.pdf',
            $charge->id,
            now()->format('Y-m-d')
        );

        $pdf = Pdf::loadView('charges.pdf.receipt', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    public function buildReceiptData(Charge $charge): array
    {
        $condominium = $charge->condominium;
        $unit = $charge->unit;
        $sindico = CondominiumDocuments::resolveSindico((int) $charge->condominium_id);

        $payments = $charge->payments->map(fn ($payment) => [
            'id' => $payment->id,
            'date' => optional($payment->payment_date)->format('d/m/Y'),
            'datetime' => optional($payment->created_at)->format('d/m/Y H:i'),
            'method' => PaymentMethods::label($payment->payment_method),
            'amount' => (float) $payment->amount_paid,
            'amount_formatted' => $this->formatMoney($payment->amount_paid),
            'notes' => $payment->notes,
            'registered_by' => $payment->user?->name,
            'transaction_id' => $payment->transaction_id ?? $payment->asaas_payment_id,
        ]);

        $totalPaid = (float) $charge->payments->sum('amount_paid');

        return [
            'condominium' => CondominiumDocuments::presentCondominium($condominium),
            'sindico' => [
                'name' => $sindico?->name,
            ],
            'charge' => [
                'id' => $charge->id,
                'title' => $charge->title,
                'description' => $charge->description,
                'amount' => (float) $charge->amount,
                'amount_formatted' => $this->formatMoney($charge->amount),
                'due_date' => optional($charge->due_date)->format('d/m/Y'),
                'paid_at' => optional($charge->paid_at)->format('d/m/Y H:i'),
                'status_label' => 'Pago',
                'fee_name' => $charge->fee?->name,
            ],
            'unit' => [
                'identifier' => $unit?->full_identifier,
                'morador' => $unit?->morador?->name,
            ],
            'payments' => $payments,
            'total_paid' => $totalPaid,
            'total_paid_formatted' => $this->formatMoney($totalPaid),
            'generated_at' => now()->format('d/m/Y H:i:s'),
            'app_name' => config('app.name', 'SindCON'),
        ];
    }

    protected function formatMoney($value): string
    {
        return 'R$ ' . number_format((float) $value, 2, ',', '.');
    }
}
