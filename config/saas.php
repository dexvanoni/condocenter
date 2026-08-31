<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cobrança SaaS — controle de acesso
    |--------------------------------------------------------------------------
    */
    'enforce_subscription' => env('SAAS_ENFORCE_SUBSCRIPTION', true),

    /*
    | Dias extras após vencimento antes de bloquear (além do status past_due).
    */
    'grace_days' => (int) env('SAAS_GRACE_DAYS', 0),

    /*
    | URL pública base para webhooks (útil com ngrok).
    */
    'webhook_base_url' => env('SAAS_WEBHOOK_BASE_URL', env('APP_URL')),
];
