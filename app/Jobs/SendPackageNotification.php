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

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 120];

    public $package;
    public $type;

    /** Senha em texto claro apenas para a mensagem; não é persistida. */
    public ?string $pickupCode;

    public function __construct(Package $package, string $type = 'arrived', ?string $pickupCode = null)
    {
        $this->package = $package;
        $this->type = $type;
        $this->pickupCode = $pickupCode;
    }

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
                Notification::create([
                    'condominium_id' => $this->package->condominium_id,
                    'user_id' => $resident->id,
                    'type' => 'package_' . $this->type,
                    'title' => $this->type === 'arrived' ? 'Nova Encomenda Chegou!' : 'Encomenda Retirada',
                    'message' => $this->getMessageText($resident),
                    'data' => [
                        'package_id' => $this->package->id,
                        'type' => $this->package->type,
                        'type_label' => $this->package->type_label,
                        'received_at' => $this->package->received_at,
                        'collected_at' => $this->package->collected_at,
                        'sender' => $this->package->sender,
                        'has_pickup_code' => $this->type === 'arrived' && !empty($this->pickupCode),
                    ],
                    'channel' => 'database',
                    'sent' => true,
                    'sent_at' => now(),
                ]);

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

            $this->package->update([
                'notification_sent' => true,
            ]);

            Log::info('Notificação interna de encomenda criada; envio WhatsApp será processado separadamente.', [
                'package_id' => $this->package->id,
                'unit_id' => $this->package->unit_id,
                'type' => $this->type,
            ]);
        } catch (\Exception $e) {
            $this->package->update([
                'whatsapp_delivery_status' => Package::WHATSAPP_FAILED,
            ]);

            Log::error('Erro ao enviar notificação de encomenda: ' . $e->getMessage());
            throw $e;
        }
    }

    public function failed(?\Throwable $exception): void
    {
        try {
            $this->package->update([
                'whatsapp_delivery_status' => Package::WHATSAPP_FAILED,
            ]);
        } catch (\Throwable) {
            // ignore
        }

        Log::error('SendPackageNotification failed permanently', [
            'package_id' => $this->package->id ?? null,
            'error' => $exception?->getMessage(),
        ]);
    }

    protected function getMessageText(?User $resident = null): string
    {
        $unit = $this->package->unit;
        $block = $unit->block ?? '';
        $number = $unit->number ?? '';
        $unitLabel = $unit->full_identifier ?? "Unidade {$number}";
        $sender = $this->package->sender ?: 'não identificado';
        $name = $resident?->name ? explode(' ', $resident->name)[0] : 'morador';

        if ($this->type === 'arrived') {
            $lines = [
                '📦 NOVA ENCOMENDA',
                '',
                "Olá, {$name}!",
                '',
                'Uma encomenda destinada à sua unidade foi recebida pela portaria.',
                '',
                "🏢 Bloco {$block}",
                "🚪 Apartamento {$number}",
                '',
                "📦 Remetente: {$sender}",
            ];

            if ($this->pickupCode) {
                $lines[] = '';
                $lines[] = "🔐 Senha de retirada: {$this->pickupCode}";
                $lines[] = 'Informe esta senha ao porteiro para retirar.';
            } else {
                $lines[] = '';
                $lines[] = "A encomenda ({$this->package->type_label}) para {$unitLabel} está disponível na portaria.";
            }

            return implode("\n", $lines);
        }

        $collectedAt = optional($this->package->collected_at)->format('d/m/Y H:i');

        return sprintf(
            'A encomenda (%s) foi retirada em %s.',
            $this->package->type_label,
            $collectedAt ?? 'horário não informado'
        );
    }
}
