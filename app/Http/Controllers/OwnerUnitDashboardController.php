<?php

namespace App\Http\Controllers;

use App\Helpers\SidebarHelper;
use App\Models\Unit;
use App\Models\User;
use App\Services\DirectConversationService;
use App\Services\OwnerTenantReportService;
use App\Services\UnitOccupancyService;
use Illuminate\Http\Request;

class OwnerUnitDashboardController extends Controller
{
    public function __construct(
        private readonly UnitOccupancyService $occupancy,
        private readonly DirectConversationService $directConversations,
        private readonly OwnerTenantReportService $tenantReport,
    ) {
    }

    public function exportTenantReportPdf(Request $request, Unit $unit)
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($this->occupancy->userOwnsUnit($user, $unit), 403);

        return $this->tenantReport->downloadPdf($user, $unit, $request);
    }

    public function startMoradorConversation(Request $request, Unit $unit)
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($this->occupancy->userOwnsUnit($user, $unit), 403);

        abort_unless(
            SidebarHelper::canSendMessages($user) || $user->can('view_messages'),
            403,
            'Você não tem permissão para mensagens.'
        );

        $unit->loadMissing('morador');
        $morador = $unit->morador;
        abort_unless($morador, 404, 'Esta unidade não possui morador vinculado.');

        $condominiumId = (int) $user->tenantCondominiumId();
        $conversation = $this->directConversations->findOrCreatePeerConversation($user, $morador, $condominiumId);

        return redirect()->route('messages.index', ['open' => $conversation->id]);
    }
}
