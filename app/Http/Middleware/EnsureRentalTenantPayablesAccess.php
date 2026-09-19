<?php

namespace App\Http\Middleware;

use App\Services\UnitOccupancyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRentalTenantPayablesAccess
{
    public function __construct(
        private readonly UnitOccupancyService $unitOccupancyService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        if ($this->unitOccupancyService->canAccessFinancialModule($user)) {
            return redirect()->route('my-charges.index');
        }

        $isRentalTenant = $this->unitOccupancyService->userLivesInRentalUnit($user)
            || $this->unitOccupancyService->agregadoLinkedToRentalMorador($user);

        if (!$isRentalTenant) {
            abort(403, 'Esta área é exclusiva para moradores de imóveis de aluguel.');
        }

        return $next($request);
    }
}
