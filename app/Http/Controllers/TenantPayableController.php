<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesActiveCondominium;
use App\Models\Charge;
use App\Models\Condominium;
use App\Models\Fine;
use App\Services\UnitOccupancyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantPayableController extends Controller
{
    use ResolvesActiveCondominium;

    public function __construct(
        private readonly UnitOccupancyService $unitOccupancyService,
    ) {
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $condominiumId = $this->activeCondominiumId($user);

        $fines = Fine::with(['recipients.charge'])
            ->byCondominium($condominiumId)
            ->whereHas('recipients', fn ($q) => $q->where('user_id', $user->id))
            ->orderByDesc('applied_at')
            ->paginate(15);

        $charges = Charge::with(['unit'])
            ->where('condominium_id', $condominiumId)
            ->where('unit_id', $user->unit_id)
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date')
            ->get()
            ->filter(fn (Charge $charge) => $this->unitOccupancyService->isMoradorResponsibleCharge($charge));

        $condominium = Condominium::query()->find($condominiumId);
        $onlinePaymentsEnabled = $condominium?->acceptsOnlinePayments() ?? false;

        return view('tenant-payables.index', compact('fines', 'charges', 'onlinePaymentsEnabled'));
    }
}
