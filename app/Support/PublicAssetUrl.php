<?php

namespace App\Support;

class PublicAssetUrl
{
    public static function storage(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        return '/storage/'.$normalized;
    }
}
