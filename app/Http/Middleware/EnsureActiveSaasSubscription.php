<?php

namespace App\Http\Middleware;

use App\Services\ActiveCondominiumService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSaasSubscription
{
    public function __construct(private ActiveCondominiumService $activeCondominiumService) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!config('saas.enforce_subscription', true)) {
            return $next($request);
        }

        $user = $request->user();

        if (!$user || $user->isAdmin()) {
            return $next($request);
        }

        if ($request->routeIs(
            'syndic-subscription.*',
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
            // Membro de administradora sem condomínio selecionado: valida assinatura da organização.
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

        if ($condominium->isSaasComplimentary()) {
            return $next($request);
        }

        // Condomínio sob administradora: valida assinatura da organização quando existir.
        if ($condominium->organization_id) {
            $organization = $condominium->organization;
            if ($organization?->isManagementCompany()) {
                if (!app(\App\Services\OrganizationSubscriptionService::class)->organizationAccessAllowed($organization)) {
                    if ($request->expectsJson()) {
                        return response()->json(['error' => 'Assinatura da administradora inativa.'], 402);
                    }

                    return redirect()
                        ->route('organization.dashboard')
                        ->with('error', 'O acesso está suspenso. Regularize a assinatura da administradora.');
                }

                return $next($request);
            }
        }

        $subscriptions = $condominium->subscriptions()->get();
        $subscription = $subscriptions->first();

        if ($subscriptions->isEmpty()) {
            return $next($request);
        }

        if ($subscriptions->contains(fn ($item) => $item->isAccessAllowed())) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Assinatura do condomínio inativa. Entre em contato com a administração ou regularize o pagamento.',
                'subscription_status' => $subscription->status,
            ], 402);
        }

        return redirect()
            ->route('syndic-subscription.show')
            ->with('error', 'O acesso ao sistema está suspenso. Regularize a assinatura do condomínio para continuar.');
    }
}
