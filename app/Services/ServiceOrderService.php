<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\ServiceOrderMessage;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ServiceOrderService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly ServiceOrderNotificationService $notificationService,
    ) {
    }

    public function create(User $requester, array $data): ServiceOrder
    {
        return $this->database->transaction(function () use ($requester, $data) {
            $condominiumId = (int) $requester->tenantCondominiumId();
            $unitId = $data['location_type'] === 'unit'
                ? ($data['unit_id'] ?? $requester->unit_id)
                : null;

            if ($data['location_type'] === 'unit' && !$unitId) {
                throw ValidationException::withMessages([
                    'unit_id' => 'Informe a unidade para solicitações na sua própria unidade.',
                ]);
            }

            $order = ServiceOrder::create([
                'condominium_id' => $condominiumId,
                'unit_id' => $unitId,
                'user_id' => $requester->id,
                'protocol' => $this->generateProtocol($condominiumId),
                'type' => $data['type'],
                'location_type' => $data['location_type'],
                'location_detail' => $data['location_detail'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'],
                'urgency' => $data['urgency'],
                'preferred_date' => $data['preferred_date'] ?? null,
                'preferred_time_start' => $data['preferred_time_start'] ?? null,
                'preferred_time_end' => $data['preferred_time_end'] ?? null,
                'availability_notes' => $data['availability_notes'] ?? null,
                'status' => 'open',
                'whatsapp_notify' => (bool) ($data['whatsapp_notify'] ?? true),
                'created_by' => $requester->id,
                'updated_by' => $requester->id,
            ]);

            $this->notificationService->notifyCreated($order);

            return $order->load(['requester', 'unit']);
        });
    }

    public function updateStatus(ServiceOrder $order, User $actor, array $data): ServiceOrder
    {
        return $this->database->transaction(function () use ($order, $actor, $data) {
            $previousStatus = $order->status;
            $status = $data['status'];

            $updates = [
                'status' => $status,
                'resolution_notes' => $data['resolution_notes'] ?? $order->resolution_notes,
                'assigned_to' => $data['assigned_to'] ?? $order->assigned_to,
                'updated_by' => $actor->id,
            ];

            if ($status === 'dispatched' && !$order->dispatched_at) {
                $updates['dispatched_at'] = now();
            }

            if (in_array($status, ['resolved', 'unresolved', 'cancelled'], true)) {
                $updates['resolved_at'] = in_array($status, ['resolved', 'unresolved'], true) ? now() : $order->resolved_at;
                $updates['closed_at'] = now();
            }

            $order->update($updates);

            if ($previousStatus !== $status) {
                $this->notificationService->notifyStatusChanged($order->fresh(), $actor, $previousStatus);
            }

            return $order->fresh(['requester', 'unit', 'assignee', 'charges', 'items']);
        });
    }

    public function addMessage(ServiceOrder $order, User $author, string $message, bool $isInternal = false): ServiceOrderMessage
    {
        if ($isInternal && !$author->can('manage_service_orders')) {
            throw ValidationException::withMessages([
                'message' => 'Apenas a administração pode registrar notas internas.',
            ]);
        }

        $entry = ServiceOrderMessage::create([
            'service_order_id' => $order->id,
            'user_id' => $author->id,
            'message' => $message,
            'is_internal' => $isInternal,
        ]);

        $this->notificationService->notifyNewMessage($order->fresh(), $entry, $author);

        return $entry->load('author');
    }

    public function addItem(ServiceOrder $order, User $actor, array $data): ServiceOrderItem
    {
        $quantity = round((float) $data['quantity'], 2);
        $unitPrice = round((float) $data['unit_price'], 2);
        $total = round($quantity * $unitPrice, 2);

        $item = ServiceOrderItem::create([
            'service_order_id' => $order->id,
            'type' => $data['type'],
            'description' => $data['description'],
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $total,
            'created_by' => $actor->id,
        ]);

        $this->refreshReimbursementTotal($order);
        $this->notificationService->notifyItemAdded($order->fresh(['requester', 'charges']), $item, $actor);

        return $item;
    }

    public function removeItem(ServiceOrderItem $item, User $actor): void
    {
        if ($item->isBilled()) {
            throw ValidationException::withMessages([
                'item' => 'Não é possível remover itens já incluídos em uma cobrança.',
            ]);
        }

        $order = $item->serviceOrder;
        $item->delete();
        $this->refreshReimbursementTotal($order->fresh());
    }

    public function generateReimbursementCharge(ServiceOrder $order, User $actor, array $data): Charge
    {
        return $this->database->transaction(function () use ($order, $actor, $data) {
            $order = $order->fresh(['items', 'requester', 'unit', 'charges']);

            $unbilledItems = $order->items->whereNull('charge_id')->values();
            if ($unbilledItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Não há itens pendentes de cobrança. Adicione materiais ou serviços antes de gerar.',
                ]);
            }

            $unitId = $order->unit_id ?? $order->requester?->unit_id;
            if (!$unitId) {
                throw ValidationException::withMessages([
                    'unit_id' => 'O solicitante não possui unidade vinculada para gerar cobrança.',
                ]);
            }

            $total = round((float) $unbilledItems->sum('total'), 2);
            if ($total <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'O valor total do ressarcimento deve ser maior que zero.',
                ]);
            }

            $chargeSequence = $order->charges->count() + 1;
            $dueDate = $data['due_date'] ?? now()->addDays(10)->toDateString();

            $charge = Charge::create([
                'condominium_id' => $order->condominium_id,
                'unit_id' => $unitId,
                'service_order_id' => $order->id,
                'title' => $chargeSequence > 1
                    ? "Ressarcimento adicional OS {$order->protocol} (#{$chargeSequence})"
                    : "Ressarcimento OS {$order->protocol}",
                'description' => $this->buildChargeDescription($order, $unbilledItems),
                'amount' => $total,
                'due_date' => $dueDate,
                'type' => 'extra',
                'status' => 'pending',
                'generated_by' => 'service_order',
                'fine_percentage' => $data['fine_percentage'] ?? 2.00,
                'interest_rate' => $data['interest_rate'] ?? 1.00,
                'metadata' => [
                    'service_order_id' => $order->id,
                    'service_order_protocol' => $order->protocol,
                    'charge_sequence' => $chargeSequence,
                    'generated_by_user_id' => $actor->id,
                ],
            ]);

            ServiceOrderItem::query()
                ->whereIn('id', $unbilledItems->pluck('id'))
                ->update(['charge_id' => $charge->id]);

            $order->update([
                'charge_id' => $charge->id,
                'reimbursement_total' => round((float) $order->items()->sum('total'), 2),
                'updated_by' => $actor->id,
            ]);

            $this->notificationService->notifyChargeCreated(
                $order->fresh(['requester', 'charges']),
                $charge,
                $chargeSequence > 1
            );

            return $charge;
        });
    }

    public function managersForCondominium(int $condominiumId): Collection
    {
        return User::query()
            ->where('condominium_id', $condominiumId)
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Síndico', 'Administrador', 'Secretaria']))
            ->orderBy('name')
            ->get();
    }

    protected function refreshReimbursementTotal(ServiceOrder $order): void
    {
        $order->update([
            'reimbursement_total' => round((float) $order->items()->sum('total'), 2),
        ]);
    }

    protected function generateProtocol(int $condominiumId): string
    {
        $year = now()->format('Y');
        $prefix = "OS-{$year}-";

        $last = ServiceOrder::query()
            ->where('condominium_id', $condominiumId)
            ->where('protocol', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('protocol');

        $sequence = 1;
        if ($last && preg_match('/OS-\d{4}-(\d+)$/', $last, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    protected function buildChargeDescription(ServiceOrder $order, Collection $items): string
    {
        $lines = [
            "Ordem de serviço {$order->protocol} — {$order->title}",
            "Tipo: {$order->type_label}",
            "Local: {$order->location_type_label}" . ($order->location_detail ? " ({$order->location_detail})" : ''),
            'Itens desta cobrança:',
        ];

        foreach ($items as $item) {
            $lines[] = sprintf(
                '- %s: %s (qtd %s) = R$ %s',
                $item->type_label,
                $item->description,
                number_format((float) $item->quantity, 2, ',', '.'),
                number_format((float) $item->total, 2, ',', '.')
            );
        }

        return implode("\n", $lines);
    }
}
