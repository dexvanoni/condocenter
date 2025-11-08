<?php

namespace App\Jobs;

use App\Mail\ChargeReminderMail;
use App\Models\Charge;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendChargeReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->notifyChargesDueTomorrow();
            $this->notifyChargesDueToday();
        } catch (\Throwable $exception) {
            Log::error('Erro ao enviar lembretes de cobranças', [
                'message' => $exception->getMessage(),
                'trace' => Str::limit($exception->getTraceAsString(), 1000),
            ]);

            throw $exception;
        }
    }

    protected function notifyChargesDueTomorrow(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        $charges = Charge::with(['unit.morador', 'unit.users'])
            ->whereNotNull('unit_id')
            ->where('status', 'pending')
            ->whereDate('due_date', $tomorrow)
            ->whereNull('first_reminder_sent_at')
            ->get();

        foreach ($charges as $charge) {
            if ($this->shouldSkipCharge($charge)) {
                continue;
            }

            $targets = $this->resolveTargets($charge);
            if ($targets->isEmpty()) {
                continue;
            }

            foreach ($targets as $user) {
                $this->dispatchReminder($user, $charge, 'due_tomorrow');
            }

            $charge->forceFill([
                'first_reminder_sent_at' => now(),
            ])->saveQuietly();
        }
    }

    protected function notifyChargesDueToday(): void
    {
        $today = now()->toDateString();

        $charges = Charge::with(['unit.morador', 'unit.users'])
            ->whereNotNull('unit_id')
            ->where('status', 'pending')
            ->whereDate('due_date', $today)
            ->whereNull('second_reminder_sent_at')
            ->get();

        foreach ($charges as $charge) {
            if ($this->shouldSkipCharge($charge)) {
                continue;
            }

            $targets = $this->resolveTargets($charge);
            if ($targets->isEmpty()) {
                continue;
            }

            foreach ($targets as $user) {
                $this->dispatchReminder($user, $charge, 'due_today');
            }

            $charge->forceFill([
                'second_reminder_sent_at' => now(),
            ])->saveQuietly();
        }
    }

    protected function shouldSkipCharge(Charge $charge): bool
    {
        $channel = data_get($charge->metadata, 'payment_channel');

        if ($channel === 'payroll') {
            return true;
        }

        if (!$charge->unit || !$charge->unit->is_active) {
            return true;
        }

        return false;
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\User>
     */
    protected function resolveTargets(Charge $charge)
    {
        $resident = $charge->unit?->morador;
        if ($resident && $resident->is_active) {
            return collect([$resident]);
        }

        return $charge->unit?->users
            ->filter(fn (User $user) => $user->is_active)
            ->unique('id')
            ?? collect();
    }

    protected function dispatchReminder(User $user, Charge $charge, string $notificationType): void
    {
        Notification::create([
            'condominium_id' => $charge->condominium_id,
            'user_id' => $user->id,
            'type' => 'charge_' . $notificationType,
            'title' => $notificationType === 'due_tomorrow'
                ? 'Cobrança vence amanhã'
                : 'Cobrança vence hoje',
            'message' => $this->buildNotificationMessage($charge, $notificationType),
            'data' => [
                'charge_id' => $charge->id,
                'due_date' => $charge->due_date,
                'amount' => $charge->amount,
            ],
            'channel' => 'database',
            'sent' => true,
            'sent_at' => now(),
        ]);

        if (!empty($user->email)) {
            Mail::to($user->email)->queue(new ChargeReminderMail($charge, $notificationType));
        }
    }

    protected function buildNotificationMessage(Charge $charge, string $notificationType): string
    {
        $amount = number_format($charge->amount, 2, ',', '.');
        $dueDate = $charge->due_date?->format('d/m/Y');

        return match ($notificationType) {
            'due_today' => "A cobrança '{$charge->title}' vence hoje ({$dueDate}). Valor: R$ {$amount}.",
            default => "Lembrete: a cobrança '{$charge->title}' vence em {$dueDate}. Valor: R$ {$amount}.",
        };
    }
}

