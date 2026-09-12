<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supabase — leads da landing de vendas SindCON
    |--------------------------------------------------------------------------
    |
    | A listagem usa a RPC list_leads_with_token (SECURITY DEFINER) no Postgres.
    | A chave publishable/anon é suficiente no servidor Laravel.
    |
    */

    'url' => rtrim((string) env('SUPABASE_URL', ''), '/'),

    'key' => env('SUPABASE_KEY'),

    'leads_admin_token' => env('SUPABASE_LEADS_ADMIN_TOKEN'),

    'leads_rpc' => env('SUPABASE_LEADS_RPC', 'list_leads_with_token'),

];
