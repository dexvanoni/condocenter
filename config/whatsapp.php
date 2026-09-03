<?php

return [
    'enabled' => (bool) env('WHATSAPP_ENABLED', false),

    'api_url' => env('EVOLUTION_API_URL', 'http://localhost:8080'),
    'api_key' => env('EVOLUTION_API_KEY'),
    'instance' => env('EVOLUTION_INSTANCE'),

    'timeout' => (int) env('EVOLUTION_TIMEOUT', 15),
    'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '55'),

    /*
    | Mensagens individuais no WhatsApp são enviadas somente a usuários ativos
    | (is_active = true) e não excluídos (soft delete). Ver WhatsAppNotificationService.
    |
    | Grupos enviados pela instância global da plataforma (ex.: cobrança SaaS).
    | Demais grupos são configurados por condomínio, cada um com sua instância Evolution.
    */
    'platform_only_groups' => ['subscription'],

    /*
    | Grupos de notificação habilitáveis pelo administrador da plataforma.
    | Cada grupo cobre um ou mais tipos (`type`) gravados em `notifications`.
    */
    'groups' => [
        'access' => [
            'label' => 'Controle de acesso',
            'description' => 'Entradas, negações e alertas críticos de proibição na portaria.',
            'recipients' => 'Morador indicado para aviso, quem autorizou a visita (morador/agregado) e morador titular da unidade.',
            'types' => ['access_entered', 'access_denied', 'access_prohibition_critical'],
        ],
        'panic' => [
            'label' => 'Alerta de pânico',
            'description' => 'Botão de emergência acionado e resolução do alerta.',
            'recipients' => 'Todos os usuários ativos do condomínio (alerta de emergência).',
            'types' => ['panic_alert', 'panic_resolved'],
        ],
        'packages' => [
            'label' => 'Encomendas',
            'description' => 'Chegada e retirada de encomendas na portaria.',
            'recipients' => 'Moradores e agregados da unidade da encomenda.',
            'types' => ['package_arrived', 'package_collected'],
        ],
        'reservations' => [
            'label' => 'Reservas de espaços',
            'description' => 'Aprovações, rejeições, cancelamentos e cobranças de reserva.',
            'recipients' => 'Síndicos (reserva pendente); solicitante da reserva (aprovada, rejeitada, cancelada ou cobrança gerada).',
            'types' => [
                'reservation_approved',
                'reservation_rejected',
                'reservation_pending_approval',
                'reservation_cancelled',
                'reservation_charge_created',
            ],
        ],
        'charges' => [
            'label' => 'Cobranças e financeiro',
            'description' => 'Lembretes de vencimento, inadimplência, pagamentos e taxas invalidadas.',
            'recipients' => 'Morador titular da unidade ou usuários ativos da unidade (lembretes e inadimplência); moradores/agregados da unidade (taxa invalidada).',
            'types' => [
                'charge_due_tomorrow',
                'charge_due_today',
                'payment_overdue',
                'payment_received',
                'fee_invalidated',
            ],
        ],
        'conversations' => [
            'label' => 'Mensagens e conversas',
            'description' => 'Mensagens do síndico e conversas com prioridade alta.',
            'recipients' => 'Participantes da conversa ou destinatários do aviso (todos, perfil ou usuário), exceto quem enviou.',
            'types' => ['conversation_message', 'sindico_message'],
        ],
        'rides' => [
            'label' => 'Caronas',
            'description' => 'Publicação, reservas e cancelamentos de caronas.',
            'recipients' => 'Moradores com acesso a caronas (nova oferta); motorista (reservas e lotação); passageiro (cancelamento da carona).',
            'types' => [
                'ride_published',
                'ride_booking_created',
                'ride_booking_cancelled',
                'ride_cancelled',
                'ride_full',
            ],
        ],
        'subscription' => [
            'label' => 'Assinatura SaaS',
            'description' => 'Cobranças da plataforma para condomínios contratantes.',
            'recipients' => 'Responsável financeiro da assinatura e síndicos do condomínio.',
            'types' => [
                'saas_payment_created',
                'saas_payment_overdue',
                'saas_payment_received',
                'saas_payment_update',
            ],
        ],
        'registration' => [
            'label' => 'Cadastro de moradores',
            'description' => 'Solicitações pendentes e aprovação/rejeição de cadastro.',
            'recipients' => 'Síndicos e administradores (cadastro pendente); próprio solicitante (aprovado ou rejeitado).',
            'types' => ['registration_pending', 'registration_approved', 'registration_rejected'],
        ],
        'assemblies' => [
            'label' => 'Assembleias',
            'description' => 'Atualizações de status de assembleias (prefixo assembly_).',
            'recipients' => 'Usuários elegíveis à votação conforme perfis permitidos na assembleia (padrão: moradores e síndico).',
            'types' => ['assembly_*'],
        ],
        'general' => [
            'label' => 'Avisos gerais (grupo WhatsApp)',
            'description' => 'Encaminha alertas de pânico, avisos do síndico e notificações gerais para o grupo de avisos do condomínio.',
            'recipients' => 'Grupo de avisos configurado pelo síndico/administrador (além dos destinatários individuais).',
            'types' => [
                'panic_alert',
                'panic_resolved',
                'conversation_message',
                'sindico_message',
            ],
            'uses_announcements_group' => true,
        ],
    ],

    /*
    | Tipos que também disparam mensagem no grupo de avisos do condomínio.
    */
    'announcements_group_types' => [
        'panic_alert',
        'panic_resolved',
        'conversation_message',
        'sindico_message',
    ],
];
