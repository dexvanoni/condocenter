<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Str;

class DevDocumentationController extends Controller
{
    public function vpsGuide(string $token): Response
    {
        $expected = (string) config('dev.docs_token');

        if ($expected === '' || strlen($expected) < 32 || ! hash_equals($expected, $token)) {
            abort(404);
        }

        $path = base_path('DOCUMENTAÇÃO/INSTALACAO_VPS.md');

        if (! is_readable($path)) {
            abort(404);
        }

        $html = Str::markdown(file_get_contents($path), [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return response()
            ->view('dev.vps-guide', ['content' => $html])
            ->header('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }
}
