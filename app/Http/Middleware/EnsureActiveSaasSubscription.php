<?php

namespace App\Http\Middleware;

use App\Services\ActiveCondominiumService;
use App\Services\SaasCondominiumAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSaasSubscription
{
    public function __construct(
        private ActiveCondominiumService $activeCondominiumService,
        private SaasCondominiumAccessService $condominiumAccess,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->condominiumAccess->enforcementEnabled()) {
            return $next($request);
        }

        $user = $request->user();

        if (!$user || $user->isAdmin()) {
            return $next($request);
        }

        if ($request->routeIs(
            'syndic-subscription.*',
            'saas.access-blocked',
            'organization.dashboard',
            'organization.condominiums.enter',
            'privacy.*',
            'logout',
            'profile.*',
            'password.*',
            'condominium.switch',
            'condominiums.index',
        )) {
            return $next($request);
        }

        $condominium = $this->activeCondominiumService->getActiveCondominium($user) ?? $user->condominium;

        if (!$condominium) {
            if ($user->isOrganizationMember()) {
                $organizationId = $this->activeCondominiumService->getActiveOrganizationId($user);
                if ($organizationId) {
                    $organization = \App\Models\Organization::query()->find($organizationId);
                    if ($organization && !app(\App\Services\OrganizationSubscriptionService::class)->organizationAccessAllowed($organization)) {
                        if ($request->expectsJson()) {
                            return response()->json(['error' => 'Assinatura da administradora inativa.'], 402);
                        }

                        return redirect()
                            ->route('organization.dashboard')
                            ->with('error', 'O acesso está suspenso. Regularize a assinatura da administradora.');
                    }
                }
            }

            return $next($request);
        }

        if ($this->condominiumAccess->condominiumAllowsResidents($condominium)) {
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
