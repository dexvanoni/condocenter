<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalNotificationService
{
    protected array $config;
    protected bool $enabled;

    public function __construct()
    {
        $this->config = config('onesignal', []);
        $this->enabled = (bool) ($this->config['enabled'] ?? false);
    }

    public function isEnabled(): bool
    {
        return $this->enabled
            && !empty($this->config['app_id'])
            && !empty($this->config['rest_api_key']);
    }

    /**
     * Envia notificação genérica para uma lista de usuários (external user ids).
     *
     * @param  array<int, int|string>  $userIds
     */
    public function sendToUsers(
        array $userIds,
        string $content,
        ?string $heading = null,
        array $data = [],
        array $overrides = []
    ): int {
        if (!$this->isEnabled()) {
            return 0;
        }

        $ids = array_values(array_unique(array_filter(array_map('strval', $userIds))));
        if (empty($ids)) {
            return 0;
        }

        $headingText = $heading ?? 'Notificação';
        $chunks = array_chunk($ids, $this->config['max_recipients_per_request'] ?? 2000);
        $sent = 0;

        foreach ($chunks as $chunk) {
            $payload = array_merge($this->basePayload($headingText, $content, $data), [
                'include_external_user_ids' => $chunk,
                'channel_for_external_user_ids' => 'push',
            ], $overrides);

            if ($this->dispatch($payload)) {
                $sent += count($chunk);
            }
        }

        return $sent;
    }

    /**
     * Envia notificação para todos os assinantes (fallback / campanhas).
     */
    public function sendBroadcast(string $content, ?string $heading = null, array $data = [], array $overrides = []): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $payload = array_merge($this->basePayload($heading ?? 'Notificação', $content, $data), [
            'included_segments' => ['Subscribed Users'],
        ], $overrides);

        return $this->dispatch($payload);
    }

    /**
     * Envia alerta de pânico.
     *
     * @param  array<int, int|string>  $userIds
     */
    public function sendPanicAlert(array $userIds, array $alertData): int
    {
        $event = Arr::get($this->config, 'events.panic_alert', []);
        $content = sprintf(
            '%s reportou %s na unidade %s.',
            Arr::get($alertData, 'user_name', 'Um morador'),
            Arr::get($alertData, 'alert_title', 'uma emergência'),
            Arr::get($alertData, 'user_unit', 'do condomínio')
        );

        $data = array_merge($alertData, [
            'type' => 'panic_alert',
            'url' => $this->buildUrl(Arr::get($event, 'url')),
        ]);

        return $this->sendToUsers(
            $userIds,
            $content,
            Arr::get($event, 'heading', '🚨 Alerta de Emergência'),
            $data
        );
    }

    /**
     * Envia notificação de alerta de pânico resolvido.
     *
     * @param  array<int, int|string>  $userIds
     */
    public function sendPanicResolved(array $userIds, array $alertData): int
    {
        $event = Arr::get($this->config, 'events.panic_resolved', []);
        $content = sprintf(
            'O alerta de %s foi resolvido por %s.',
            Arr::get($alertData, 'alert_title', 'emergência'),
            Arr::get($alertData, 'resolved_by', 'um responsável')
        );

        $data = array_merge($alertData, [
            'type' => 'panic_resolved',
            'url' => $this->buildUrl(Arr::get($event, 'url')),
        ]);

        return $this->sendToUsers(
            $userIds,
            $content,
            Arr::get($event, 'heading', '✅ Alerta Resolvido'),
            $data
        );
    }

    /**
     * Notificações sobre encomendas.
     *
     * @param  array<int, int|string>  $userIds
     */
    public function sendPackageNotification(array $userIds, string $type, array $payload): int
    {
        $eventKey = $type === 'collected' ? 'events.package_collected' : 'events.package_arrived';
        $event = Arr::get($this->config, $eventKey, []);

        $content = $type === 'collected'
            ? sprintf(
                'A encomenda (%s) foi retirada em %s.',
                Arr::get($payload, 'type_label', 'encomenda'),
                Arr::get($payload, 'collected_at', 'horário não informado')
            )
            : sprintf(
                'Chegou uma encomenda (%s) para a unidade %s.',
                Arr::get($payload, 'type_label', 'encomenda'),
                Arr::get($payload, 'unit_label', 'do condomínio')
            );

        $data = array_merge($payload, [
            'type' => "package_{$type}",
            'url' => $this->buildUrl(Arr::get($event, 'url')),
        ]);

        return $this->sendToUsers(
            $userIds,
            $content,
            Arr::get($event, 'heading', '📦 Atualização de Encomenda'),
            $data
        );
    }

    /**
     * Mensagens enviadas por síndicos/administradores.
     *
     * @param  array<int, int|string>  $userIds
     */
    public function sendSindicoMessage(array $userIds, array $messageData): int
    {
        $event = Arr::get($this->config, 'events.sindico_message', []);
        $content = Arr::get($messageData, 'excerpt')
            ?? mb_strimwidth(Arr::get($messageData, 'message', 'Nova mensagem do síndico.'), 0, 140, '...');

        $data = array_merge($messageData, [
            'type' => 'sindico_message',
            'url' => $this->buildUrl(Arr::get($event, 'url')),
        ]);

        return $this->sendToUsers(
            $userIds,
            $content,
            Arr::get($event, 'heading', '📢 Mensagem do Síndico'),
            $data
        );
    }

    /**
     * Assembleias (status ou lembretes).
     *
     * @param  array<int, int|string>  $userIds
     */
    public function sendAssemblyNotification(array $userIds, string $status, array $assemblyData): int
    {
        $event = Arr::get($this->config, 'events.assembly_status', []);

        $statusLabels = [
            'scheduled' => 'Agendada',
            'in_progress' => 'Em andamento',
            'completed' => 'Encerrada',
        ];

        $content = sprintf(
            'Assembleia "%s" está %s (%s).',
            Arr::get($assemblyData, 'title', 'Assembleia'),
            $statusLabels[$status] ?? $status,
            Arr::get($assemblyData, 'scheduled_at_label', '')
        );

        $data = array_merge($assemblyData, [
            'type' => 'assembly_' . $status,
            'url' => $this->buildUrl(Arr::get($event, 'url')),
        ]);

        return $this->sendToUsers(
            $userIds,
            $content,
            Arr::get($event, 'heading', '👥 Assembleia'),
            $data
        );
    }

    /**
     * Reservas de espaços.
     *
     * @param  array<int, int|string>  $userIds
     */
    public function sendReservationNotification(array $userIds, string $type, array $reservationData): int
    {
        $event = Arr::get($this->config, 'events.reservation_update', []);
        $titles = [
            'approved' => '✅ Reserva Aprovada',
            'rejected' => '❌ Reserva Rejeitada',
            'pending_approval' => '⏳ Reserva Pendente',
            'cancelled' => '❌ Reserva Cancelada',
        ];

        $content = Arr::get($reservationData, 'message') ?? sprintf(
            'Reserva do espaço %s atualizada (%s).',
            Arr::get($reservationData, 'space_name', 'comum'),
            $type
        );

        $data = array_merge($reservationData, [
            'type' => 'reservation_' . $type,
            'url' => $this->buildUrl(Arr::get($event, 'url')),
        ]);

        return $this->sendToUsers(
            $userIds,
            $content,
            $titles[$type] ?? Arr::get($event, 'heading', '📅 Reserva'),
            $data
        );
    }

    /**
     * Pagamentos recebidos (admins/síndicos).
     *
     * @param  array<int, int|string>  $userIds
     */
    public function sendPaymentReceived(array $userIds, array $paymentData): int
    {
        $event = Arr::get($this->config, 'events.payment_received', []);

        $content = sprintf(
            'Pagamento de %s recebido para a cobrança "%s".',
            Arr::get($paymentData, 'amount_label', 'R$ 0,00'),
            Arr::get($paymentData, 'charge_title', 'Cobrança')
        );

        $data = array_merge($paymentData, [
            'type' => 'payment_received',
            'url' => $this->buildUrl(Arr::get($event, 'url')),
        ]);

        return $this->sendToUsers(
            $userIds,
            $content,
            Arr::get($event, 'heading', '💰 Pagamento Recebido'),
            $data
        );
    }

    /**
     * Monta o payload base com heading, conteúdo e metadados.
     */
    protected function basePayload(string $heading, string $content, array $data = []): array
    {
        return [
            'app_id' => $this->config['app_id'],
            'headings' => [
                'pt' => $heading,
                'en' => $heading,
            ],
            'contents' => [
                'pt' => $content,
                'en' => $content,
            ],
            'data' => array_merge(['sent_at' => now()->toIso8601String()], $data),
            'url' => $this->buildUrl(Arr::get($data, 'url')),
        ];
    }

    /**
     * Envia requisição HTTP para o OneSignal.
     */
    protected function dispatch(array $payload): bool
    {
        try {
            $response = Http::timeout($this->config['timeout'] ?? 10)
                ->connectTimeout($this->config['connect_timeout'] ?? 5)
                ->withHeaders([
                    'Authorization' => 'Basic ' . $this->config['rest_api_key'],
                    'Content-Type' => 'application/json',
                ])
                ->post($this->config['api_url'], $payload);

            if ($response->successful()) {
                Log::info('[OneSignal] Notificação enviada com sucesso', [
                    'payload' => $payload,
                    'response' => $response->json(),
                ]);
                return true;
            }

            Log::warning('[OneSignal] Falha no envio de notificação', [
                'status' => $response->status(),
                'payload' => $payload,
                'response' => $response->body(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('[OneSignal] Erro ao enviar notificação', [
                'error' => $exception->getMessage(),
                'payload' => $payload,
            ]);
        }

        return false;
    }

    /**
     * Constrói a URL final usando a configuração padrão.
     */
    protected function buildUrl(?string $path): string
    {
        $base = rtrim($this->config['default_url'] ?? config('app.url'), '/');
        if (empty($path)) {
            return $base;
        }

        return $base . '/' . ltrim($path, '/');
    }
}

