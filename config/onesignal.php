<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Controle geral
    |--------------------------------------------------------------------------
    |
    | Ative o envio de notificações do OneSignal definindo ONESIGNAL_ENABLED=true.
    | Todos os métodos do serviço validarão este flag antes de tentar disparar
    | qualquer requisição ao provedor.
    */
    'enabled' => (bool) env('ONESIGNAL_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Credenciais da aplicação
    |--------------------------------------------------------------------------
    */
    'app_id' => env('ONESIGNAL_APP_ID'),
    'rest_api_key' => env('ONESIGNAL_REST_API_KEY'),
    'api_url' => env('ONESIGNAL_API_URL', 'https://onesignal.com/api/v1/notifications'),

    /*
    |--------------------------------------------------------------------------
    | Opções padrão
    |--------------------------------------------------------------------------
    */
    'default_url' => env('ONESIGNAL_DEFAULT_URL', env('APP_URL')),
    'max_recipients_per_request' => 2000,

    /*
    |--------------------------------------------------------------------------
    | Timeout das requisições
    |--------------------------------------------------------------------------
    */
    'timeout' => env('ONESIGNAL_TIMEOUT', 10),
    'connect_timeout' => env('ONESIGNAL_CONNECT_TIMEOUT', 5),

    /*
    |--------------------------------------------------------------------------
    | Mapas específicos por evento
    |--------------------------------------------------------------------------
    */
    'events' => [
        'panic_alert' => [
            'heading' => '🚨 Alerta de Emergência',
            'url' => '/panic-alerts',
        ],
        'panic_resolved' => [
            'heading' => '✅ Alerta Resolvido',
            'url' => '/panic-alerts',
        ],
        'package_arrived' => [
            'heading' => '📦 Encomenda Recebida',
            'url' => '/packages',
        ],
        'package_collected' => [
            'heading' => '📦 Encomenda Retirada',
            'url' => '/packages',
        ],
        'sindico_message' => [
            'heading' => '📢 Mensagem do Síndico',
            'url' => '/messages',
        ],
        'assembly_status' => [
            'heading' => '👥 Atualização de Assembleia',
            'url' => '/assemblies',
        ],
        'reservation_update' => [
            'heading' => '📅 Atualização de Reserva',
            'url' => '/reservations',
        ],
        'payment_received' => [
            'heading' => '💰 Novo Pagamento Recebido',
            'url' => '/financial/accounts',
        ],
    ],
];

