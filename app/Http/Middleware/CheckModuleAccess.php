<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\SidebarHelper;
use App\Services\DefaulterRestrictionService;

class CheckModuleAccess
{
    public function __construct(
        private readonly DefaulterRestrictionService $defaulterRestrictionService,
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string $module The module to check access for
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Acesso não autorizado.');
        }

        // Verificar se o usuário pode acessar o módulo
        if (!SidebarHelper::canAccessModule($user, $module)) {
            abort(403, "Você não tem permissão para acessar o módulo {$module}.");
        }

        if ($this->defaulterRestrictionService->blocksModuleAccess($user, $module)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => $this->defaulterRestrictionService->denialMessage(),
                    'restricted' => true,
                    'regularize_url' => $this->defaulterRestrictionService->getContextForUser($user)['regularize_url'],
                ], 403);
            }

            return redirect()
                ->route('dashboard')
                ->with('error', $this->defaulterRestrictionService->denialMessage());
        }

        return $next($request);
    }
}
