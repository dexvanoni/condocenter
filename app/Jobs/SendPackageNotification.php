<?php

namespace App\Jobs;

use App\Models\Package;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
            // Buscar moradores da unidade
            $residents = $this->package->unit->users;

            foreach ($residents as $resident) {
                // Criar notificação no banco
                $notification = Notification::create([
                    'condominium_id' => $this->package->condominium_id,
                    'user_id' => $resident->id,
                    'type' => 'package_' . $this->type,
                    'title' => $this->type === 'arrived' ? 'Nova Encomenda Chegou!' : 'Encomenda Retirada',
                    'message' => $this->getMessageText(),
                    'data' => [
                        'package_id' => $this->package->id,
                        'sender' => $this->package->sender,
                        'tracking_code' => $this->package->tracking_code,
                        'received_at' => $this->package->received_at,
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
            $msg = "Uma encomenda chegou para a unidade {$this->package->unit->full_identifier}. ";
            
            if ($this->package->sender) {
                $msg .= "Remetente: {$this->package->sender}. ";
            }
            
            if ($this->package->tracking_code) {
                $msg .= "Código de rastreio: {$this->package->tracking_code}. ";
            }
            
            $msg .= "Retire na portaria.";
            
            return $msg;
        }

        return "Sua encomenda foi retirada em {$this->package->collected_at->format('d/m/Y H:i')}.";
    }
}
