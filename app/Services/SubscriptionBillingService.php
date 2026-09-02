<?php

namespace App\Services;

use App\Models\CondominiumSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionBillingService
{
    public function __construct(
        private PlatformAsaasService $asaas,
        private PlatformSettingsService $platformSettings,
    ) {}

    public function getBillingReport(CondominiumSubscription $subscription, array $filters = []): array
    {
        if ($subscription->payment_method === CondominiumSubscription::PAYMENT_BANK_DEPOSIT) {
            return [
                'charges' => [],
                'summary' => $this->emptySummary(),
                'source' => 'manual',
                'message' => 'Este contrato utiliza depósito bancário manual. Cobranças não são geradas automaticamente no Asaas.',
            ];
        }

        if (!$this->platformSettings->isAsaasConfigured()) {
            return [
                'charges' => [],
                'summary' => $this->emptySummary(),
                'source' => 'unconfigured',
                'message' => 'Configure a API do Asaas em Plataforma → Asaas (SaaS) para consultar cobranças.',
            ];
        }

        if (!$subscription->asaas_customer_id && !$subscription->asaas_subscription_id) {
            return [
                'charges' => [],
                'summary' => $this->emptySummary(),
                'source' => 'pending_sync',
                'message' => 'Nenhuma cobrança no Asaas ainda. Ative a assinatura ou sincronize com o Asaas.',
            ];
        }

        $rawPayments = $this->fetchRawPayments($subscription);
        $charges = collect($rawPayments)
            ->map(fn (array $payment) => $this->normalizeRawPayment($payment))
            ->sortByDesc(fn (array $charge) => $charge['due_date']?->timestamp ?? 0)
            ->values();

        $allCharges = $charges->all();
        $filtered = $this->applyFilters($charges, $filters);

        return [
            'charges' => $filtered->values()->all(),
            'summary' => $this->summarize($allCharges),
            'filtered_summary' => $this->summarize($filtered->all()),
            'source' => 'asaas',
            'message' => null,
            'total_fetched' => count($allCharges),
        ];
    }

    public function filtersFromRequest(Request $request): array
    {
        return array_filter([
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'status' => $request->input('status'),
        ], fn ($value) => filled($value));
    }

    public function exportCsv(CondominiumSubscription $subscription, array $filters, string $filenamePrefix): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $report = $this->getBillingReport($subscription, $filters);
        $condominiumName = $subscription->condominium?->name ?? 'condominio';
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($condominiumName));
        $filename = trim($filenamePrefix . '-' . $slug . '-' . now()->format('Y-m-d'), '-') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($report, $subscription) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, ['Condomínio', $subscription->condominium?->name ?? '']);
            fputcsv($output, ['Contrato', $subscription->statusLabel()]);
            fputcsv($output, ['Valor recorrente', number_format((float) $subscription->recurring_amount, 2, ',', '.')]);
            fputcsv($output, []);

            fputcsv($output, [
                'ID Asaas',
                'Vencimento',
                'Pagamento',
                'Valor (R$)',
                'Status',
                'Forma',
                'Descrição',
                'Link fatura',
            ]);

            foreach ($report['charges'] as $charge) {
                fputcsv($output, [
                    $charge['id'],
                    $charge['due_date']?->format('d/m/Y') ?? '',
                    $charge['payment_date']?->format('d/m/Y') ?? '',
                    number_format($charge['value'], 2, ',', '.'),
                    $charge['status_label'],
                    $charge['billing_type_label'],
                    $charge['description'],
                    $charge['invoice_url'] ?? $charge['bank_slip_url'] ?? '',
                ]);
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function fetchRawPayments(CondominiumSubscription $subscription): array
    {
        if ($subscription->asaas_customer_id) {
            return $this->asaas->fetchAllCustomerPayments($subscription->asaas_customer_id);
        }

        if ($subscription->asaas_subscription_id) {
            return $this->asaas->fetchAllSubscriptionPayments($subscription->asaas_subscription_id);
        }

        return [];
    }

    public function normalizeRawPayment(array $payment): array
    {
        $status = strtoupper((string) ($payment['status'] ?? ''));
        $group = $this->statusGroup($status);

        return [
            'id' => $payment['id'] ?? null,
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'status_group' => $group,
            'value' => (float) ($payment['value'] ?? 0),
            'due_date' => !empty($payment['dueDate']) ? Carbon::parse($payment['dueDate']) : null,
            'payment_date' => !empty($payment['paymentDate']) ? Carbon::parse($payment['paymentDate']) : null,
            'description' => $payment['description'] ?? 'Assinatura SindCON',
            'invoice_url' => $payment['invoiceUrl'] ?? null,
            'bank_slip_url' => $payment['bankSlipUrl'] ?? null,
            'billing_type' => $payment['billingType'] ?? null,
            'billing_type_label' => $this->billingTypeLabel($payment['billingType'] ?? null),
        ];
    }

    protected function normalizePayment(array $payment): array
    {
        return $this->normalizeRawPayment($payment);
    }

    protected function applyFilters($charges, array $filters)
    {
        $filtered = collect($charges);

        if (!empty($filters['date_from'])) {
            $from = Carbon::parse($filters['date_from'])->startOfDay();
            $filtered = $filtered->filter(fn (array $charge) => $charge['due_date'] && $charge['due_date']->gte($from));
        }

        if (!empty($filters['date_to'])) {
            $to = Carbon::parse($filters['date_to'])->endOfDay();
            $filtered = $filtered->filter(fn (array $charge) => $charge['due_date'] && $charge['due_date']->lte($to));
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $filtered = $filtered->filter(fn (array $charge) => $charge['status_group'] === $filters['status']);
        }

        return $filtered;
    }

    protected function summarize(array $charges): array
    {
        $collection = collect($charges);

        return [
            'total' => $collection->count(),
            'total_amount' => $collection->sum('value'),
            'pending' => $collection->where('status_group', 'pending')->count(),
            'pending_amount' => $collection->where('status_group', 'pending')->sum('value'),
            'paid' => $collection->where('status_group', 'paid')->count(),
            'paid_amount' => $collection->where('status_group', 'paid')->sum('value'),
            'overdue' => $collection->where('status_group', 'overdue')->count(),
            'overdue_amount' => $collection->where('status_group', 'overdue')->sum('value'),
        ];
    }

    protected function emptySummary(): array
    {
        return [
            'total' => 0,
            'total_amount' => 0,
            'pending' => 0,
            'pending_amount' => 0,
            'paid' => 0,
            'paid_amount' => 0,
            'overdue' => 0,
            'overdue_amount' => 0,
        ];
    }

    public function statusLabel(string $status): string
    {
        return match (strtoupper($status)) {
            'PENDING' => 'Pendente',
            'RECEIVED' => 'Recebido',
            'CONFIRMED' => 'Confirmado',
            'OVERDUE' => 'Vencido',
            'REFUNDED' => 'Estornado',
            'RECEIVED_IN_CASH' => 'Recebido em dinheiro',
            'REFUND_REQUESTED' => 'Estorno solicitado',
            'CHARGEBACK_REQUESTED' => 'Chargeback',
            'AWAITING_RISK_ANALYSIS' => 'Análise de risco',
            'DUNNING_REQUESTED' => 'Negativação solicitada',
            'DUNNING_RECEIVED' => 'Recuperado',
            default => $status ?: '—',
        };
    }

    public function statusGroup(string $status): string
    {
        return match (strtoupper($status)) {
            'PENDING', 'AWAITING_RISK_ANALYSIS' => 'pending',
            'RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH', 'DUNNING_RECEIVED' => 'paid',
            'OVERDUE', 'DUNNING_REQUESTED' => 'overdue',
            default => 'other',
        };
    }

    protected function billingTypeLabel(?string $type): string
    {
        return match (strtoupper((string) $type)) {
            'BOLETO' => 'Boleto',
            'CREDIT_CARD' => 'Cartão',
            'PIX' => 'PIX',
            'UNDEFINED' => 'Indefinido',
            default => $type ?: '—',
        };
    }
}
