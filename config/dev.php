<?php

return [
    /*
    | Token secreto da documentação interna. Vazio = rota desligada (404).
    */
    'docs_token' => env('DEV_DOCS_TOKEN', ''),

    /*
    | URL completa para abrir o tutorial da VPS (apenas o desenvolvedor).
    */
    'docs_url' => env('DEV_DOCS_URL', ''),
];
