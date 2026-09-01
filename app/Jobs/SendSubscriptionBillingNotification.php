<?php

namespace App\Jobs;

use App\Models\CondominiumSubscription;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SendSubscriptionBillingNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public CondominiumSubscription $subscription,
        public string $event,
        public array $payment = [],
    ) {}

    public function handle(): void
    {
        $subscription = $this->subscription->fresh(['condominium', 'financialResponsible']);

        if (!$subscription || !$subscription->condominium) {
            return;
        }

        [$type, $title, $message] = $this->buildContent($subscription);

        foreach ($this->resolveRecipients($subscription) as $recipient) {
            $this->storeNotification($subscription, $recipient, $type, $title, $message);
        }
    }

    protected function buildContent(CondominiumSubscription $subscription): array
    {
        $value = number_format((float) ($this->payment['value'] ?? $subscription->recurring_amount), 2, ',', '.');
        $dueDate = $this->payment['dueDate'] ?? null;
        $dueLabel = $dueDate ? \Carbon\Carbon::parse($dueDate)->format('d/m/Y') : '—';

        return match ($this->event) {
            'PAYMENT_CREATED' => [
                'saas_payment_created',
                'Nova cobrança da assinatura',
                "Cobrança de R$ {$value} com vencimento em {$dueLabel} foi gerada para {$subscription->condominium->name}.",
            ],
            'PAYMENT_OVERDUE' => [
                'saas_payment_overdue',
                'Cobrança da assinatura vencida',
                "A cobrança de R$ {$value} (venc. {$dueLabel}) está vencida. Regularize em Minha Assinatura.",
            ],
            'PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED' => [
                'saas_payment_received',
                'Pagamento da assinatura confirmado',
                "Pagamento de R$ {$value} confirmado para {$subscription->condominium->name}.",
            ],
            default => [
                'saas_payment_update',
                'Atualização da assinatura',
                "Houve uma atualização na cobrança da assinatura de {$subscription->condominium->name}.",
            ],
        };
    }

    protected function resolveRecipients(CondominiumSubscription $subscription): Collection
    {
        $recipients = collect();

        if ($subscription->financialResponsible) {
            $recipients->push($subscription->financialResponsible);
        }

        $syndics = User::query()
            ->where('condominium_id', $subscription->condominium_id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Síndico'))
            ->where('is_active', true)
            ->get();

        foreach ($syndics as $syndic) {
            if (!$recipients->contains('id', $syndic->id)) {
                $recipients->push($syndic);
            }
        }

        return $recipients->unique('id')->values();
    }

    protected function storeNotification(
        CondominiumSubscription $subscription,
        User $recipient,
        string $type,
        string $title,
        string $message,
    ): void {
        Notification::create([
            'condominium_id' => $subscription->condominium_id,
            'user_id' => $recipient->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => [
                'subscription_id' => $subscription->id,
                'payment_id' => $this->payment['id'] ?? null,
                'event' => $this->event,
                'url' => route('syndic-subscription.show'),
            ],
            'channel' => 'database',
            'sent' => true,
            'sent_at' => now(),
        ]);
    }
}
