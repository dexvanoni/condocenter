<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendRideNotification;
use App\Models\Ride;
use App\Models\User;
use App\Services\RideBookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class RideController extends Controller
{
    public function __construct(
        private RideBookingService $bookingService
    ) {}

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->tenantCondominiumId()) {
            return response()->json(['error' => 'Usuário sem condomínio vinculado'], 403);
        }

        $query = Ride::query()
            ->with([
                'driver:id,name,phone',
                'activeBookings.passenger:id,name',
            ])
            ->byCondominium((int) $user->tenantCondominiumId())
            ->where('departure_at', '>=', now()->subHours(6));

        if ($request->get('scope') === 'mine') {
            $query->where('driver_id', $user->id);
        } elseif ($request->get('scope') === 'bookings') {
            $query->whereHas('activeBookings', fn ($q) => $q->where('passenger_id', $user->id));
        } elseif ($request->get('available_only') === '1') {
            $query->available();
        } else {
            $query->whereIn('status', [Ride::STATUS_OPEN, Ride::STATUS_FULL]);
        }

        $rides = $query->orderBy('departure_at')->paginate(20);

        return response()->json($rides);
    }

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->can('create_rides')) {
            return response()->json(['error' => 'Sem permissão para oferecer carona'], 403);
        }

        if (!$user->tenantCondominiumId()) {
            return response()->json(['error' => 'Usuário sem condomínio vinculado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'destination' => ['required', 'string', 'max:255'],
            'departure_at' => ['required', 'date', 'after:now'],
            'seats_total' => ['required', 'integer', 'min:1', 'max:8'],
            'has_return' => ['nullable', 'boolean'],
            'return_at' => ['nullable', 'date', 'after:departure_at'],
            'is_free' => ['required', 'boolean'],
            'price_per_seat' => ['nullable', 'numeric', 'min:0', 'required_if:is_free,0,false'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $seats = (int) $request->input('seats_total');
        $isFree = $request->boolean('is_free');

        $ride = Ride::create([
            'condominium_id' => $user->tenantCondominiumId(),
            'driver_id' => $user->id,
            'destination' => $request->input('destination'),
            'departure_at' => $request->input('departure_at'),
            'seats_total' => $seats,
            'seats_available' => $seats,
            'has_return' => $request->boolean('has_return'),
            'return_at' => $request->boolean('has_return') ? $request->input('return_at') : null,
            'is_free' => $isFree,
            'price_per_seat' => $isFree ? null : $request->input('price_per_seat'),
            'notes' => $request->input('notes'),
            'status' => Ride::STATUS_OPEN,
        ]);

        SendRideNotification::dispatchSync($ride, 'ride_published');

        return response()->json($ride->load('driver:id,name'), 201);
    }

    public function show(Ride $ride)
    {
        $this->authorizeRide($ride);

        $ride->load([
            'driver:id,name,phone',
            'activeBookings.passenger:id,name',
        ]);

        return response()->json($ride);
    }

    public function destroy(Ride $ride)
    {
        /** @var User $user */
        $user = Auth::user();
        $this->authorizeRide($ride);

        if ((int) $ride->driver_id !== (int) $user->id && !$user->can('manage_rides')) {
            return response()->json(['error' => 'Sem permissão para cancelar esta carona'], 403);
        }

        try {
            $this->bookingService->cancelRide($ride, $user);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Carona cancelada com sucesso.']);
    }

    public function book(Request $request, Ride $ride)
    {
        /** @var User $user */
        $user = Auth::user();
        $this->authorizeRide($ride);

        if (!$user->can('book_rides')) {
            return response()->json(['error' => 'Sem permissão para reservar carona'], 403);
        }

        $validator = Validator::make($request->all(), [
            'seats_booked' => ['required', 'integer', 'min:1', 'max:8'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $booking = $this->bookingService->book(
                $ride,
                $user,
                (int) $request->input('seats_booked')
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Reserva confirmada!',
            'booking' => $booking,
            'ride' => $ride->fresh()->load('driver:id,name', 'activeBookings.passenger:id,name'),
        ], 201);
    }

    private function authorizeRide(Ride $ride): void
    {
        /** @var User $user */
        $user = Auth::user();

        if ((int) $ride->condominium_id !== (int) $user->tenantCondominiumId()) {
            abort(403, 'Carona de outro condomínio');
        }
    }
}
