<?php

namespace App\Http\Controllers;

use App\Helpers\SidebarHelper;
use App\Models\Condominium;
use App\Services\UserCreditService;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function index()
    {
        return view('reservations.index');
    }

    public function myReservations()
    {
        $user = Auth::user();

        abort_unless(SidebarHelper::canViewReservations($user), 403);

        $condominium = Condominium::query()->find($user->tenantCondominiumId());
        $initialUserCredits = app(UserCreditService::class)
            ->getAvailableTotal($user, $user->tenantCondominiumId());

        return view('reservations.my', [
            'onlinePaymentsEnabled' => $condominium?->acceptsOnlinePayments() ?? false,
            'initialUserCredits' => $initialUserCredits,
            'canMakeReservations' => SidebarHelper::canMakeReservations($user),
        ]);
    }
}
