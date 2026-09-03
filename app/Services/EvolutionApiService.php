<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    public function __construct(
        private PlatformSettingsService $platformSettings,
        private CondominiumWhatsAppSettingsService $condominiumSettings,
    ) {}

    public function getConfig(?int $condominiumId = null): array
    {
        if ($condominiumId) {
            $condominium = \App\Models\Condominium::query()->find($condominiumId);

            if ($condominium) {
                return $this->condominiumSettings->getConfig($condominium);
            }
        }

        return $this->platformSettings->getWhatsAppConfig();
    }

    public function isConfigured(?int $condominiumId = null): bool
    {
        $config = $this->getConfig($condominiumId);

        return filled($config['api_url'])
            && filled($config['api_key'])
            && filled($config['instance']);
    }

    public function connectionState(?int $condominiumId = null): array
    {
        if (!$this->isConfigured($condominiumId)) {
            return [
                'ok' => false,
                'state' => null,
                'message' => 'Evolution API não configurada.',
            ];
        }

        $config = $this->getConfig($condominiumId);
        $url = $this->buildEndpoint($config, "/instance/connectionState/{$config['instance']}");

        try {
            $response = $this->client($config)->get($url);

            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'state' => null,
                    'message' => 'HTTP ' . $response->status() . ': ' . ($response->json('message') ?? $response->body()),
                ];
            }

            $state = $response->json('instance.state')
                ?? $response->json('state')
                ?? $response->json('status');

            $connected = in_array(strtolower((string) $state), ['open', 'connected'], true);

            return [
                'ok' => $connected,
                'state' => $state,
                'message' => $connected
                    ? 'Instância conectada ao WhatsApp.'
                    : 'Instância não conectada (estado: ' . ($state ?: 'desconhecido') . ').',
            ];
        } catch (\Throwable $e) {
            Log::warning('Evolution API connectionState failed: ' . $e->getMessage(), [
                'condominium_id' => $condominiumId,
            ]);

            return [
                'ok' => false,
                'state' => null,
                'message' => 'Falha ao consultar instância: ' . $e->getMessage(),
            ];
        }
    }

    public function sendText(string $phone, string $text, ?int $condominiumId = null): array
    {
        if (!$this->isConfigured($condominiumId)) {
            return ['ok' => false, 'message' => 'Evolution API não configurada.'];
        }

        $config = $this->getConfig($condominiumId);

        if (str_contains((string) $phone, '@')) {
            $number = trim($phone);
        } else {
            $number = $this->normalizePhone($phone, $config['default_country_code']);
        }

        if (!$number) {
            return ['ok' => false, 'message' => 'Telefone inválido para WhatsApp.'];
        }

        return $this->postTextMessage($config, $number, $text, $condominiumId);
    }

    public function sendTextToGroup(string $groupJid, string $text, int $condominiumId): array
    {
        if (!$this->isConfigured($condominiumId)) {
            return ['ok' => false, 'message' => 'Evolution API não configurada.'];
        }

        $jid = trim($groupJid);
        if ($jid === '' || !str_contains($jid, '@')) {
            return ['ok' => false, 'message' => 'ID do grupo WhatsApp inválido.'];
        }

        $config = $this->getConfig($condominiumId);

        return $this->postTextMessage($config, $jid, $text, $condominiumId);
    }

    /**
     * Lista grupos WhatsApp da instância Evolution (número precisa participar do grupo).
     *
     * @param  array{api_url?: string, api_key?: string, instance?: string}  $configOverride
     */
    public function fetchAllGroups(?int $condominiumId = null, array $configOverride = [], bool $getParticipants = false): array
    {
        $config = $this->resolveConfig($condominiumId, $configOverride);

        if (!filled($config['api_url']) || !filled($config['api_key']) || !filled($config['instance'])) {
            return [
                'ok' => false,
                'groups' => [],
                'message' => 'Informe URL, instância e API Key da Evolution API.',
            ];
        }

        $url = $this->buildEndpoint($config, "/group/fetchAllGroups/{$config['instance']}");

        try {
            $response = $this->client($config)->get($url, [
                'getParticipants' => $getParticipants ? 'true' : 'false',
            ]);

            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'groups' => [],
                    'message' => 'HTTP ' . $response->status() . ': ' . ($response->json('message') ?? $response->body()),
                ];
            }

            $groups = $this->normalizeGroupsResponse($response->json());

            return [
                'ok' => true,
                'groups' => $groups,
                'message' => $groups === []
                    ? 'Nenhum grupo encontrado. Adicione o número da instância ao grupo no WhatsApp.'
                    : count($groups) . ' grupo(s) encontrado(s).',
            ];
        } catch (\Throwable $e) {
            Log::warning('Evolution API fetchAllGroups failed: ' . $e->getMessage(), [
                'condominium_id' => $condominiumId,
            ]);

            return [
                'ok' => false,
                'groups' => [],
                'message' => 'Falha ao listar grupos: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * @param  array{api_url?: string, api_key?: string, instance?: string}  $override
     */
    protected function resolveConfig(?int $condominiumId, array $override = []): array
    {
        $config = $this->getConfig($condominiumId);

        foreach (['api_url', 'api_key', 'instance'] as $key) {
            if (filled($override[$key] ?? null)) {
                $config[$key] = $override[$key];
            }
        }

        return $config;
    }

    /**
     * @return list<array{id: string, name: string, participants: int|null, owner: string|null}>
     */
    protected function normalizeGroupsResponse(mixed $body): array
    {
        if (!is_array($body)) {
            return [];
        }

        $list = $body;
        if (!array_is_list($body)) {
            if (isset($body['groups']) && is_array($body['groups'])) {
                $list = $body['groups'];
            } elseif (isset($body['data']) && is_array($body['data'])) {
                $list = $body['data'];
            } else {
                $list = [];
            }
        }

        return collect($list)
            ->filter(fn ($group) => is_array($group))
            ->map(function (array $group) {
                $id = $group['id'] ?? $group['jid'] ?? $group['groupJid'] ?? null;
                $id = trim((string) $id);

                if ($id !== '' && !str_contains($id, '@')) {
                    $id .= '@g.us';
                }

                $participants = $group['size'] ?? null;
                if ($participants === null && isset($group['participants']) && is_array($group['participants'])) {
                    $participants = count($group['participants']);
                }

                return [
                    'id' => $id !== '' ? $id : null,
                    'name' => trim((string) ($group['subject'] ?? $group['name'] ?? 'Sem nome')),
                    'participants' => is_numeric($participants) ? (int) $participants : null,
                    'owner' => isset($group['owner']) ? (string) $group['owner'] : null,
                ];
            })
            ->filter(fn (array $group) => filled($group['id']))
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    protected function postTextMessage(array $config, string $number, string $text, ?int $condominiumId): array
    {
        $url = $this->buildEndpoint($config, "/message/sendText/{$config['instance']}");

        try {
            $response = $this->client($config)->post($url, [
                'number' => $number,
                'text' => $text,
            ]);

            if ($response->successful()) {
                return [
                    'ok' => true,
                    'message' => 'Mensagem enviada.',
                    'response' => $response->json(),
                ];
            }

            return [
                'ok' => false,
                'message' => 'HTTP ' . $response->status() . ': ' . ($response->json('message') ?? $response->body()),
            ];
        } catch (\Throwable $e) {
            Log::warning('Evolution API sendText failed: ' . $e->getMessage(), [
                'number' => $number,
                'condominium_id' => $condominiumId,
            ]);

            return [
                'ok' => false,
                'message' => 'Falha ao enviar: ' . $e->getMessage(),
            ];
        }
    }

    public function normalizePhone(?string $phone, ?string $countryCode = null): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        $countryCode = preg_replace('/\D+/', '', (string) ($countryCode ?: config('whatsapp.default_country_code', '55'))) ?? '55';

        if (str_starts_with($digits, '0')) {
            $digits = ltrim($digits, '0');
        }

        if (!str_starts_with($digits, $countryCode) && strlen($digits) <= 11) {
            $digits = $countryCode . $digits;
        }

        return strlen($digits) >= 12 ? $digits : null;
    }

    protected function buildEndpoint(array $config, string $path): string
    {
        $base = rtrim($config['api_url'] ?? '', '/');

        return $base . $path;
    }

    protected function client(array $config)
    {
        return Http::withHeaders([
            'apikey' => $config['api_key'],
            'Content-Type' => 'application/json',
        ])->timeout((int) ($config['timeout'] ?? 15));
    }
}
