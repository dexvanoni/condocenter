<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RideBooking;
use App\Models\User;
use App\Services\RideBookingService;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class RideBookingController extends Controller
{
    public function __construct(
        private RideBookingService $bookingService
    ) {}

    public function destroy(RideBooking $rideBooking)
    {
        /** @var User $user */
        $user = Auth::user();

        $rideBooking->load('ride');

        if ((int) $rideBooking->ride->condominium_id !== (int) $user->tenantCondominiumId()) {
            return response()->json(['error' => 'Reserva de outro condomínio'], 403);
        }

        $isPassenger = (int) $rideBooking->passenger_id === (int) $user->id;
        $isDriver = (int) $rideBooking->ride->driver_id === (int) $user->id;
        $canManage = $user->can('manage_rides');

        if (!$isPassenger && !$isDriver && !$canManage) {
            return response()->json(['error' => 'Sem permissão'], 403);
        }

        try {
            $this->bookingService->cancelBooking($rideBooking, $user);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Reserva cancelada com sucesso.']);
    }
}
