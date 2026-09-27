<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN', false);
        $response->headers->set('X-Content-Type-Options', 'nosniff', false);
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', false);
        $response->headers->set('X-XSS-Protection', '0', false);

        if (!$response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy(), false);
        }

        return $response;
    }

    protected function contentSecurityPolicy(): string
    {
        $connectSrc = [
            "'self'",
            'https://cdn.jsdelivr.net',
            'https://cdn.datatables.net',
        ];

        if (app()->environment('local')) {
            $connectSrc[] = 'ws:';
            $connectSrc[] = 'wss:';
            $connectSrc[] = 'http://localhost:*';
            $connectSrc[] = 'http://127.0.0.1:*';

            $appUrl = config('app.url');
            if (is_string($appUrl) && $appUrl !== '') {
                $scheme = parse_url($appUrl, PHP_URL_SCHEME);
                $host = parse_url($appUrl, PHP_URL_HOST);
                if (is_string($scheme) && is_string($host) && $host !== '' && ! in_array($host, ['localhost', '127.0.0.1'], true)) {
                    $connectSrc[] = $scheme.'://'.$host;
                }
            }
        }

        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://code.jquery.com https://cdn.datatables.net",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.bunny.net https://cdn.datatables.net",
            "font-src 'self' data: https://cdn.jsdelivr.net https://fonts.bunny.net",
            "img-src 'self' data: blob: https:",
            'connect-src '.implode(' ', $connectSrc),
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        return implode('; ', $directives);
    }
}
