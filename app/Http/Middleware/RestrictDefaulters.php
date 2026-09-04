<?php

namespace App\Http\Middleware;

use App\Services\DefaulterRestrictionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictDefaulters
{
    public function __construct(
        private readonly DefaulterRestrictionService $defaulterRestrictionService,
    ) {
    }

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if (!$user || !$this->defaulterRestrictionService->blocksFeature($user, $feature)) {
            return $next($request);
        }

        $message = $this->defaulterRestrictionService->denialMessage($feature);
        $regularizeUrl = $this->defaulterRestrictionService->getContextForUser($user)['regularize_url'];

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => $message,
                'restricted' => true,
                'regularize_url' => $regularizeUrl,
            ], 403);
        }

        return redirect()
            ->route('dashboard')
            ->with('error', $message);
    }
}
