<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\Notification;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderMessage;
use App\Models\User;

class ServiceOrderNotificationService
{
    public function notifyCreated(ServiceOrder $order): void
    {
        if (!$order->whatsapp_notify) {
            return;
        }

        $managers = User::query()
            ->where('condominium_id', $order->condominium_id)
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Síndico', 'Administrador', 'Secretaria']))
            ->get();

        foreach ($managers as $manager) {
            $this->createNotification(
                $order,
                $manager,
                'service_order_created',
                'Nova ordem de serviço',
                "Nova OS {$order->protocol}: {$order->title} ({$order->urgency_label})",
                [
                    'service_order_id' => $order->id,
                    'protocol' => $order->protocol,
                    'urgency' => $order->urgency,
                ]
            );
        }
    }

    public function notifyStatusChanged(ServiceOrder $order, User $actor, string $previousStatus): void
    {
        if (!$order->whatsapp_notify) {
            return;
        }

        $requester = $order->requester;
        if (!$requester) {
            return;
        }

        $this->createNotification(
            $order,
            $requester,
            'service_order_status_updated',
            'Atualização da sua OS',
            "A OS {$order->protocol} mudou de " . (ServiceOrder::STATUSES[$previousStatus] ?? $previousStatus)
                . " para {$order->status_label}.",
            [
                'service_order_id' => $order->id,
                'protocol' => $order->protocol,
                'previous_status' => $previousStatus,
                'new_status' => $order->status,
                'updated_by' => $actor->id,
            ]
        );
    }

    public function notifyNewMessage(ServiceOrder $order, ServiceOrderMessage $message, User $author): void
    {
        if (!$order->whatsapp_notify || $message->is_internal) {
            return;
        }

        $recipients = collect();

        if ($author->can('manage_service_orders')) {
            if ($order->requester && $order->requester->id !== $author->id) {
                $recipients->push($order->requester);
            }
        } elseif ($order->requester?->id === $author->id) {
            $recipients = User::query()
                ->where('condominium_id', $order->condominium_id)
                ->where('is_active', true)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Síndico', 'Administrador', 'Secretaria']))
                ->get();
        }

        foreach ($recipients->unique('id') as $recipient) {
            if ($recipient->id === $author->id) {
                continue;
            }

            $this->createNotification(
                $order,
                $recipient,
                'service_order_message',
                'Nova mensagem na OS ' . $order->protocol,
                "{$author->name}: " . \Illuminate\Support\Str::limit($message->message, 180),
                [
                    'service_order_id' => $order->id,
                    'protocol' => $order->protocol,
                    'message_id' => $message->id,
                    'author_id' => $author->id,
                ]
            );
        }
    }

    public function notifyChargeCreated(ServiceOrder $order, Charge $charge, bool $isAdditional = false): void
    {
        $requester = $order->requester;
        if (!$requester || !$order->whatsapp_notify) {
            return;
        }

        $title = $isAdditional
            ? 'Nova cobrança adicional na OS ' . $order->protocol
            : 'Cobrança de ressarcimento gerada';

        $this->createNotification(
            $order,
            $requester,
            'service_order_charge_created',
            $title,
            "Foi gerada cobrança de R$ " . number_format((float) $charge->amount, 2, ',', '.')
                . " referente à OS {$order->protocol}. Vencimento: "
                . $charge->due_date?->format('d/m/Y') . '.',
            [
                'service_order_id' => $order->id,
                'protocol' => $order->protocol,
                'charge_id' => $charge->id,
                'amount' => (float) $charge->amount,
                'is_additional' => $isAdditional,
            ]
        );
    }

    public function notifyItemAdded(ServiceOrder $order, ServiceOrderItem $item, User $actor): void
    {
        if (!$order->whatsapp_notify) {
            return;
        }

        $requester = $order->requester;
        if (!$requester || $requester->id === $actor->id) {
            return;
        }

        $hasCharges = $order->charges->isNotEmpty();

        $this->createNotification(
            $order,
            $requester,
            'service_order_items_added',
            $hasCharges ? 'Novo item de ressarcimento na OS ' . $order->protocol : 'Item registrado na OS ' . $order->protocol,
            "Foi adicionado à OS {$order->protocol}: {$item->type_label} — {$item->description} "
                . "(R$ " . number_format((float) $item->total, 2, ',', '.') . ")."
                . ($hasCharges ? ' Uma nova cobrança poderá ser gerada pela administração.' : ''),
            [
                'service_order_id' => $order->id,
                'protocol' => $order->protocol,
                'item_id' => $item->id,
                'item_total' => (float) $item->total,
                'has_existing_charges' => $hasCharges,
            ]
        );
    }

    protected function createNotification(
        ServiceOrder $order,
        User $recipient,
        string $type,
        string $title,
        string $message,
        array $data = [],
    ): void {
        Notification::create([
            'condominium_id' => $order->condominium_id,
            'user_id' => $recipient->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => array_merge($data, [
                'service_order_id' => $order->id,
                'protocol' => $order->protocol,
            ]),
            'channel' => 'database',
            'sent' => true,
            'sent_at' => now(),
        ]);
    }
}
