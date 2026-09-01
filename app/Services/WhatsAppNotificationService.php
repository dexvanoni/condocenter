<?php

namespace App\Services;

use App\Models\Condominium;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    public function __construct(
        private PlatformSettingsService $platformSettings,
        private CondominiumWhatsAppSettingsService $condominiumSettings,
        private EvolutionApiService $evolution,
    ) {}

    public function isPlatformTypeEnabled(string $type): bool
    {
        if (!$this->platformSettings->isWhatsAppEnabled() || !$this->evolution->isConfigured()) {
            return false;
        }

        $group = $this->platformSettings->resolveWhatsAppGroupForType($type);

        return $group
            && $this->condominiumSettings->isPlatformOnlyGroup($group)
            && $this->platformSettings->isWhatsAppGroupEnabled($group);
    }

    public function isCondominiumTypeEnabled(Condominium $condominium, string $type): bool
    {
        if (!$this->condominiumSettings->isConfigured($condominium) || !$this->condominiumSettings->isEnabled($condominium)) {
            return false;
        }

        return $this->condominiumSettings->isTypeEnabled($condominium, $type);
    }

    public function isTypeEnabledForNotification(Notification $notification): bool
    {
        $group = $this->platformSettings->resolveWhatsAppGroupForType($notification->type);

        if (!$group) {
            return false;
        }

        if ($this->condominiumSettings->isPlatformOnlyGroup($group)) {
            return $this->isPlatformTypeEnabled($notification->type);
        }

        if (!$notification->condominium_id) {
            return false;
        }

        $condominium = $notification->condominium ?? Condominium::query()->find($notification->condominium_id);

        if (!$condominium) {
            return false;
        }

        return $this->isCondominiumTypeEnabled($condominium, $notification->type);
    }

    public function sendFromNotification(Notification $notification): bool
    {
        if ($notification->channel !== 'database') {
            return false;
        }

        if (!$this->isTypeEnabledForNotification($notification)) {
            Log::info('WhatsApp skip: envio desabilitado para este tipo ou condomínio.', [
                'notification_id' => $notification->id,
                'type' => $notification->type,
                'condominium_id' => $notification->condominium_id,
                'user_id' => $notification->user_id,
            ]);

            return false;
        }

        $user = $notification->user;

        if (!$user) {
            Log::info('WhatsApp skip: notificação sem usuário vinculado.', [
                'notification_id' => $notification->id,
                'type' => $notification->type,
            ]);

            return false;
        }

        $group = $this->platformSettings->resolveWhatsAppGroupForType($notification->type);
        $condominiumId = $this->condominiumSettings->isPlatformOnlyGroup((string) $group)
            ? null
            : $notification->condominium_id;

        return $this->sendToUser(
            $user,
            $notification->title,
            $notification->message,
            $notification->type,
            $condominiumId
        );
    }

    public function sendToUser(
        User $user,
        string $title,
        string $message,
        ?string $type = null,
        ?int $condominiumId = null
    ): bool {
        if ($type) {
            $group = $this->platformSettings->resolveWhatsAppGroupForType($type);
            $usePlatform = $group && $this->condominiumSettings->isPlatformOnlyGroup($group);

            if ($usePlatform) {
                if (!$this->isPlatformTypeEnabled($type)) {
                    return false;
                }
                $condominiumId = null;
            } elseif ($condominiumId) {
                $condominium = Condominium::query()->find($condominiumId);
                if (!$condominium || !$this->isCondominiumTypeEnabled($condominium, $type)) {
                    return false;
                }
            } else {
                return false;
            }
        } elseif ($condominiumId) {
            $condominium = Condominium::query()->find($condominiumId);
            if (!$condominium || !$this->condominiumSettings->isEnabled($condominium) || !$this->condominiumSettings->isConfigured($condominium)) {
                return false;
            }
        } elseif (!$this->platformSettings->isWhatsAppEnabled() || !$this->evolution->isConfigured()) {
            return false;
        }

        $phone = $user->whatsappPhone();

        if (!$phone) {
            Log::info('WhatsApp skip: usuário sem telefone cadastrado.', [
                'user_id' => $user->id,
                'type' => $type,
                'condominium_id' => $condominiumId,
            ]);

            return false;
        }

        $text = $this->formatMessage($title, $message);
        $result = $this->evolution->sendText($phone, $text, $condominiumId);

        if (!$result['ok']) {
            Log::warning('WhatsApp notification failed', [
                'user_id' => $user->id,
                'type' => $type,
                'condominium_id' => $condominiumId,
                'phone' => $phone,
                'message' => $result['message'] ?? 'unknown',
            ]);

            return false;
        }

        Log::info('WhatsApp notification sent', [
            'user_id' => $user->id,
            'type' => $type,
            'condominium_id' => $condominiumId,
        ]);

        return true;
    }

    public function formatMessage(string $title, string $message): string
    {
        $appName = config('app.name', 'CondoCenter');

        return "*{$title}*\n\n{$message}\n\n_{$appName}_";
    }

    public function platformGroupsForUi(): array
    {
        $groups = config('whatsapp.groups', []);
        $enabled = $this->platformSettings->getWhatsAppEnabledGroups();
        $keys = config('whatsapp.platform_only_groups', ['subscription']);

        return collect($keys)
            ->map(function (string $key) use ($groups, $enabled) {
                $group = $groups[$key] ?? [];

                return array_merge($group, [
                    'key' => $key,
                    'enabled' => (bool) ($enabled[$key] ?? false),
                ]);
            })
            ->values()
            ->all();
    }

    public function groupsForUi(): array
    {
        return $this->platformGroupsForUi();
    }
}
