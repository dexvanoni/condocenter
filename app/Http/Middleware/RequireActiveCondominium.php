<?php

namespace App\Http\Middleware;

use App\Services\ActiveCondominiumService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireActiveCondominium
{
    public function __construct(
        private ActiveCondominiumService $activeCondominiumService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        if (!$this->activeCondominiumService->canUseCondominiumContext($user)) {
            return $next($request);
        }

        if ($this->activeCondominiumService->hasActiveCondominium($user)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Selecione um condomínio para continuar.',
            ], 403);
        }

        return redirect()
            ->route('condominiums.index')
            ->with('info', 'Selecione um condomínio para acessar esta área.');
    }
}
