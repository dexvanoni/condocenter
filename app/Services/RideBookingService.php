<?php

namespace App\Services;

use App\Jobs\SendRideNotification;
use App\Models\Ride;
use App\Models\RideBooking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RideBookingService
{
    public function book(Ride $ride, User $passenger, int $seats): RideBooking
    {
        if ((int) $ride->driver_id === (int) $passenger->id) {
            throw new InvalidArgumentException('Você não pode reservar a própria carona.');
        }

        if (!$ride->isBookable()) {
            throw new InvalidArgumentException('Esta carona não está mais disponível.');
        }

        if ($seats < 1) {
            throw new InvalidArgumentException('Informe pelo menos 1 lugar.');
        }

        return DB::transaction(function () use ($ride, $passenger, $seats) {
            $lockedRide = Ride::query()->lockForUpdate()->findOrFail($ride->id);

            if (!$lockedRide->isBookable()) {
                throw new InvalidArgumentException('Esta carona não está mais disponível.');
            }

            if ($seats > $lockedRide->seats_available) {
                throw new InvalidArgumentException(
                    "Restam apenas {$lockedRide->seats_available} vaga(s) disponível(is)."
                );
            }

            $existing = RideBooking::query()
                ->where('ride_id', $lockedRide->id)
                ->where('passenger_id', $passenger->id)
                ->where('status', RideBooking::STATUS_CONFIRMED)
                ->first();

            if ($existing) {
                throw new InvalidArgumentException('Você já reservou lugares nesta carona. Cancele antes de reservar novamente.');
            }

            $booking = RideBooking::create([
                'ride_id' => $lockedRide->id,
                'passenger_id' => $passenger->id,
                'seats_booked' => $seats,
                'status' => RideBooking::STATUS_CONFIRMED,
            ]);

            $remaining = $lockedRide->seats_available - $seats;
            $lockedRide->update([
                'seats_available' => $remaining,
                'status' => $remaining === 0 ? Ride::STATUS_FULL : Ride::STATUS_OPEN,
            ]);

            $booking->load('passenger:id,name');
            $freshRide = $lockedRide->fresh();

            SendRideNotification::dispatchSync($freshRide, 'booking_created', $booking);

            if ($remaining === 0) {
                SendRideNotification::dispatchSync($freshRide, 'ride_full');
            }

            return $booking->load('passenger:id,name');
        });
    }

    public function cancelBooking(RideBooking $booking, User $actor): void
    {
        if ($booking->status !== RideBooking::STATUS_CONFIRMED) {
            throw new InvalidArgumentException('Esta reserva já foi cancelada.');
        }

        DB::transaction(function () use ($booking, $actor) {
            $lockedBooking = RideBooking::query()->lockForUpdate()->findOrFail($booking->id);
            $lockedRide = Ride::query()->lockForUpdate()->findOrFail($lockedBooking->ride_id);

            if ($lockedBooking->status !== RideBooking::STATUS_CONFIRMED) {
                return;
            }

            $lockedBooking->update([
                'status' => RideBooking::STATUS_CANCELLED,
                'cancelled_by' => $actor->id,
                'cancelled_at' => now(),
            ]);

            $newAvailable = $lockedRide->seats_available + $lockedBooking->seats_booked;

            $lockedRide->update([
                'seats_available' => min($newAvailable, $lockedRide->seats_total),
                'status' => $lockedRide->status === Ride::STATUS_CANCELLED
                    ? Ride::STATUS_CANCELLED
                    : Ride::STATUS_OPEN,
            ]);

            SendRideNotification::dispatchSync($lockedRide->fresh(), 'booking_cancelled', $lockedBooking->fresh());
        });
    }

    public function cancelRide(Ride $ride, User $actor): void
    {
        if ($ride->status === Ride::STATUS_CANCELLED) {
            throw new InvalidArgumentException('Esta carona já foi cancelada.');
        }

        DB::transaction(function () use ($ride, $actor) {
            $lockedRide = Ride::query()->lockForUpdate()->findOrFail($ride->id);

            $lockedRide->activeBookings()->each(function (RideBooking $booking) use ($actor, $lockedRide) {
                $booking->update([
                    'status' => RideBooking::STATUS_CANCELLED,
                    'cancelled_by' => $actor->id,
                    'cancelled_at' => now(),
                ]);

                SendRideNotification::dispatchSync($lockedRide, 'ride_cancelled_passenger', $booking);
            });

            $lockedRide->update([
                'status' => Ride::STATUS_CANCELLED,
                'seats_available' => 0,
                'cancelled_by' => $actor->id,
                'cancelled_at' => now(),
            ]);
        });
    }
}
