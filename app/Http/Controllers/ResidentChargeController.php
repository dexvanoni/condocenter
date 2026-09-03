<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesActiveCondominium;
use App\Models\Condominium;
use App\Models\User;
use App\Services\ChargeResidentReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResidentChargeController extends Controller
{
    use ResolvesActiveCondominium;

    public function __construct(
        private readonly ChargeResidentReportService $reportService,
    ) {}

    public function index()
    {
        $user = Auth::user();
        $this->ensureResidentAccess($user);

        $condominium = Condominium::query()->find($this->activeCondominiumId($user));

        return view('charges.my-charges', [
            'onlinePaymentsEnabled' => $condominium?->acceptsOnlinePayments() ?? false,
            'unitLabel' => $user->unit?->full_identifier,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $this->ensureResidentAccess($user);

        return $this->reportService->download($user, $request);
    }

    protected function ensureResidentAccess(User $user): void
    {
        abort_unless(
            $user->can('view_charges')
                && filled($user->unit_id)
                && $user->isMorador()
                && !$user->isSindico()
                && !$user->isAdmin(),
            403,
            'Esta área é exclusiva para moradores.'
        );
    }
}
