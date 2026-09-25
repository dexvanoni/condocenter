<?php

namespace App\Http\Middleware;

use App\Services\SaasCondominiumAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCondominiumSaasAccess
{
    public function __construct(private SaasCondominiumAccessService $access) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$this->access->enforcementEnabled() || $user->isAdmin()) {
            return $next($request);
        }

        if ($request->routeIs(
            'saas.access-blocked',
            'logout',
            'profile.switch',
            'profile.selector',
        )) {
            return $next($request);
        }

        if (!$this->access->userBlockedByCondominiumContract($user)) {
            return $next($request);
        }

        if ($user->isManagementCompanyMember() && $request->routeIs('organization.*', 'syndic.condominiums.*')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'O contrato do condomínio não está ativo. Entre em contato com a Administração do SindCON.',
            ], 402);
        }

        return redirect()->route('saas.access-blocked');
    }
}
