<?php

namespace App\Http\Controllers;

use App\Helpers\SidebarHelper;
use App\Http\Controllers\Concerns\ResolvesActiveCondominium;
use App\Models\Condominium;
use App\Models\Unit;
use App\Models\User;
use App\Services\ChargeResidentReportService;
use App\Services\UnitOccupancyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResidentChargeController extends Controller
{
    use ResolvesActiveCondominium;

    public function __construct(
        private readonly ChargeResidentReportService $reportService,
        private readonly UnitOccupancyService $unitOccupancyService,
    ) {}

    public function index()
    {
        $user = Auth::user();
        $this->ensureMyChargesAccess($user);

        $condominium = Condominium::query()->find($this->activeCondominiumId($user));

        return view('charges.my-charges', [
            'onlinePaymentsEnabled' => $condominium?->acceptsOnlinePayments() ?? false,
            'unitLabel' => $this->resolveUnitLabel($user),
            'isOwnerViewer' => $user->isProprietario() && !$user->isSindico() && !$user->isAdmin(),
        ]);
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $this->ensureMyChargesAccess($user);

        return $this->reportService->download($user, $request);
    }

    protected function ensureMyChargesAccess(User $user): void
    {
        abort_unless(
            SidebarHelper::canAccessMyChargesIndex($user),
            403,
            'Você não tem permissão para acessar esta área de cobranças.'
        );
    }

    protected function resolveUnitLabel(User $user): ?string
    {
        if ($user->isProprietario() && !$user->isSindico() && !$user->isAdmin()) {
            $condominiumId = $this->activeCondominiumId($user);
            $ownedIds = $this->unitOccupancyService->ownedUnitIds($user, $condominiumId);

            if ($ownedIds === []) {
                return null;
            }

            return Unit::query()
                ->whereIn('id', $ownedIds)
                ->orderBy('block')
                ->orderBy('number')
                ->get()
                ->pluck('full_identifier')
                ->join(', ');
        }

        return $user->unit?->full_identifier;
    }
}
