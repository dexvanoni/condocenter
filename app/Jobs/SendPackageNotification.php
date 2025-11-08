<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\Package;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPackageNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $package;
    public $type;

    /**
     * Create a new job instance.
     */
    public function __construct(Package $package, string $type = 'arrived')
    {
        $this->package = $package;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->package->loadMissing('unit');

            if (!$this->package->unit) {
                Log::warning('Encomenda sem unidade associada ao enviar notificação', [
                    'package_id' => $this->package->id,
                ]);
                return;
            }

            $residents = User::query()
                ->select('id', 'email', 'name')
                ->byCondominium($this->package->condominium_id)
                ->where('unit_id', $this->package->unit_id)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Morador', 'Agregado']))
                ->get();

            foreach ($residents as $resident) {
                // Criar notificação no banco
                Notification::create([
                    'condominium_id' => $this->package->condominium_id,
                    'user_id' => $resident->id,
                    'type' => 'package_' . $this->type,
                    'title' => $this->type === 'arrived' ? 'Nova Encomenda Chegou!' : 'Encomenda Retirada',
                    'message' => $this->getMessageText(),
                    'data' => [
                        'package_id' => $this->package->id,
                        'type' => $this->package->type,
                        'type_label' => $this->package->type_label,
                        'received_at' => $this->package->received_at,
                        'collected_at' => $this->package->collected_at,
                    ],
                    'channel' => 'database',
                    'sent' => true,
                    'sent_at' => now(),
                ]);

                // Enviar email (se configurado)
                if (config('mail.default') !== 'log') {
                    try {
                        Mail::to($resident->email)->send(
                            new \App\Mail\PackageNotification($this->package, $this->type)
                        );
                    } catch (\Exception $e) {
                        Log::warning('Erro ao enviar email de encomenda: ' . $e->getMessage());
                    }
                }
            }

            // Marcar notificação como enviada
            $this->package->update(['notification_sent' => true]);

            Log::info("Notificação de encomenda enviada", [
                'package_id' => $this->package->id,
                'unit_id' => $this->package->unit_id,
                'type' => $this->type,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação de encomenda: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function getMessageText(): string
    {
        if ($this->type === 'arrived') {
            $unitLabel = $this->package->unit->full_identifier ?? "Unidade {$this->package->unit->number}";

            return sprintf(
                'Chegou uma encomenda (%s) para %s. Retire na portaria.',
                $this->package->type_label,
                $unitLabel
            );
        }

        $collectedAt = optional($this->package->collected_at)->format('d/m/Y H:i');
            
        return sprintf(
            'A encomenda (%s) foi retirada em %s.',
            $this->package->type_label,
            $collectedAt ?? 'horário não informado'
        );
    }
}
