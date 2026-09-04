<?php

namespace App\Http\Middleware;

use App\Models\CondominiumLandingPage;
use App\Services\CondominiumLandingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveCondominiumLandingDomain
{
    public function __construct(
        private readonly CondominiumLandingService $landingService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (!$host || !$appHost || $host === strtolower($appHost)) {
            return $next($request);
        }

        $landing = CondominiumLandingPage::query()
            ->where('custom_domain', $host)
            ->where('is_published', true)
            ->whereHas('condominium', fn ($q) => $q->where('is_active', true))
            ->first();

        if (!$landing) {
            return $next($request);
        }

        if (in_array($request->path(), ['', '/'], true) || $request->path() === '/') {
            $payload = $this->landingService->buildPublicPayload($landing);

            return response()->view('landing.show', $payload);
        }

        if ($request->path() === 'login') {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
