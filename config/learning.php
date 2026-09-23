<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Central de Aprendizagem (síndico)
    |--------------------------------------------------------------------------
    |
    | Vídeos: grave a tela e salve em public/videos/learning/{arquivo}.mp4
    | Referencie o nome do arquivo em "video" no catálogo do tutorial.
    | Também aceita URL absoluta (YouTube/Vimeo) em "video_url".
    |
    */
    'video_disk_path' => 'videos/learning',

    'audiences' => [
        'sindico' => ['Síndico', 'Administrador', 'Conselho Fiscal', 'Secretaria'],
    ],
];
