<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCondominiumWhatsAppSettingsRequest;
use App\Models\Condominium;
use App\Services\CondominiumWhatsAppSettingsService;
use App\Services\EvolutionApiService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;

class CondominiumWhatsAppSettingsController extends Controller
{
    public function __construct(
        private CondominiumWhatsAppSettingsService $settings,
        private EvolutionApiService $evolution,
        private WhatsAppNotificationService $whatsapp,
    ) {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            $condominium = $request->route('condominium');

            if ($user?->isAdmin()) {
                return $next($request);
            }

            if ($user?->isSindico()
                && $condominium instanceof Condominium
                && (int) $user->tenantCondominiumId() === (int) $condominium->id) {
                return $next($request);
            }

            abort(403, 'Acesso negado. Somente administradores ou síndicos podem configurar o WhatsApp.');
        });
    }

    public function index(Condominium $condominium)
    {
        $config = $this->settings->getConfig($condominium);
        $maskedKey = $config['api_key']
            ? str_repeat('•', max(strlen($config['api_key']) - 4, 8)) . substr($config['api_key'], -4)
            : null;

        return view('settings.whatsapp', [
            'condominium' => $condominium,
            'config' => $config,
            'maskedKey' => $maskedKey,
            'groups' => $this->settings->groupsForUi($condominium),
        ]);
    }

    public function update(UpdateCondominiumWhatsAppSettingsRequest $request, Condominium $condominium)
    {
        $this->settings->updateSettings($condominium, [
            'enabled' => $request->boolean('enabled'),
            'api_url' => $request->input('api_url'),
            'api_key' => $request->input('api_key'),
            'instance' => $request->input('instance'),
            'notify_groups' => $request->input('notify_groups', []),
            'announcements_group' => $request->input('announcements_group'),
        ]);

        return redirect()
            ->route('condominiums.settings.whatsapp', $condominium)
            ->with('success', 'Configurações do WhatsApp salvas com sucesso.');
    }

    public function test(Request $request, Condominium $condominium)
    {
        $request->validate([
            'test_phone' => ['nullable', 'string', 'max:30'],
            'announcements_group' => ['nullable', 'string', 'max:120'],
        ]);

        $connection = $this->evolution->connectionState($condominium->id);

        $testPhone = $request->input('test_phone') ?: $request->user()?->phone;
        $testSend = null;
        $testGroupSend = null;

        $announcementsGroup = trim((string) (
            $request->input('announcements_group')
            ?: $condominium->whatsapp_announcements_group
            ?: ''
        ));

        if ($connection['ok']) {
            if (filled($testPhone)) {
                $message = $this->whatsapp->formatMessage(
                    'Teste SindCON',
                    "Esta é uma mensagem de teste da integração WhatsApp do condomínio {$condominium->name}."
                );
                $testSend = $this->evolution->sendText($testPhone, $message, $condominium->id);
            }

            if ($announcementsGroup !== '') {
                $groupMessage = $this->whatsapp->formatMessage(
                    'Teste SindCON — Avisos gerais',
                    "Mensagem de teste no grupo de avisos gerais do condomínio {$condominium->name}. Se você recebeu esta mensagem, o envio para o grupo está funcionando."
                );
                $testGroupSend = $this->evolution->sendTextToGroup(
                    $announcementsGroup,
                    $groupMessage,
                    $condominium->id
                );
            } else {
                $testGroupSend = [
                    'ok' => false,
                    'skipped' => true,
                    'message' => 'Nenhum grupo de avisos informado. Preencha o ID do grupo ou use "Listar grupos".',
                ];
            }
        }

        return response()->json([
            'connection' => $connection,
            'test_send' => $testSend,
            'test_group_send' => $testGroupSend,
            'announcements_group' => $announcementsGroup !== '' ? $announcementsGroup : null,
            'configured' => $this->settings->isConfigured($condominium),
            'enabled' => $this->settings->isEnabled($condominium),
        ]);
    }

    public function listGroups(Request $request, Condominium $condominium)
    {
        $request->validate([
            'api_url' => ['nullable', 'string', 'max:500'],
            'instance' => ['nullable', 'string', 'max:120'],
            'api_key' => ['nullable', 'string', 'max:500'],
        ]);

        $override = array_filter([
            'api_url' => $request->input('api_url'),
            'api_key' => $request->input('api_key'),
            'instance' => $request->input('instance'),
        ], fn ($value) => filled($value));

        if (!isset($override['api_key'])) {
            $saved = $this->settings->getConfig($condominium);
            if (filled($saved['api_key'])) {
                $override['api_key'] = $saved['api_key'];
            }
        }

        $result = $this->evolution->fetchAllGroups($condominium->id, $override);

        return response()->json($result);
    }
}
