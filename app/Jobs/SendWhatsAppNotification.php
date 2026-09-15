<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\Package;
use App\Services\WhatsAppNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [15, 60];

    public function __construct(public int $notificationId) {}

    public function handle(WhatsAppNotificationService $whatsapp): void
    {
        $notification = Notification::query()
            ->with('user')
            ->find($this->notificationId);

        if (!$notification) {
            return;
        }

        $sent = $whatsapp->sendFromNotification($notification);
        $this->updatePackageDeliveryStatus($notification, $sent);

        if (!$sent) {
            $message = 'WhatsApp não foi aceito pela Evolution API.';
            Log::warning('WhatsApp notification will be retried.', [
                'notification_id' => $notification->id,
                'package_id' => $notification->data['package_id'] ?? null,
                'attempt' => $this->attempts(),
            ]);

            // O driver sync não possui worker para executar retentativas.
            // Marca falha corretamente, sem quebrar o registro da encomenda.
            if (config('queue.default') === 'sync') {
                return;
            }

            throw new \RuntimeException($message);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $notification = Notification::query()->find($this->notificationId);

        if ($notification) {
            $this->updatePackageDeliveryStatus($notification, false);
        }

        Log::error('WhatsApp notification failed permanently.', [
            'notification_id' => $this->notificationId,
            'error' => $exception?->getMessage(),
        ]);
    }

    private function updatePackageDeliveryStatus(Notification $notification, bool $sent): void
    {
        $packageId = $notification->data['package_id'] ?? null;

        if (!$packageId || !str_starts_with($notification->type, 'package_')) {
            return;
        }

        Package::query()
            ->whereKey($packageId)
            ->where('condominium_id', $notification->condominium_id)
            ->update([
                'whatsapp_delivery_status' => $sent
                    ? Package::WHATSAPP_SENT
                    : Package::WHATSAPP_FAILED,
            ]);
    }
}
