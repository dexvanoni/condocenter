<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\Ride;
use App\Models\RideBooking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRideNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Ride $ride,
        public string $type,
        public ?RideBooking $booking = null
    ) {}

    public function handle(): void
    {
        $this->ride->loadMissing('driver:id,name');
        $this->booking?->loadMissing('passenger:id,name');

        $messages = $this->buildMessages();
        if (!$messages) {
            return;
        }

        foreach ($messages as $payload) {
            Notification::create([
                'condominium_id' => $this->ride->condominium_id,
                'user_id' => $payload['user_id'],
                'type' => $payload['type'],
                'title' => $payload['title'],
                'message' => $payload['message'],
                'data' => array_merge([
                    'ride_id' => $this->ride->id,
                    'destination' => $this->ride->destination,
                    'departure_at' => $this->ride->departure_at?->toIso8601String(),
                ], $payload['data'] ?? []),
                'channel' => 'database',
                'sent' => true,
                'sent_at' => now(),
            ]);
        }
    }

    private function buildMessages(): array
    {
        $destination = $this->ride->destination;
        $departure = $this->ride->departure_at?->format('d/m/Y H:i') ?? '';

        return match ($this->type) {
            'booking_created' => [[
                'user_id' => $this->ride->driver_id,
                'type' => 'ride_booking_created',
                'title' => 'Nova reserva na sua carona',
                'message' => sprintf(
                    '%s reservou %d lugar(es) para %s (partida %s). Restam %d vaga(s).',
                    $this->booking?->passenger?->name ?? 'Um morador',
                    $this->booking?->seats_booked ?? 1,
                    $destination,
                    $departure,
                    $this->ride->seats_available
                ),
                'data' => [
                    'booking_id' => $this->booking?->id,
                    'seats_booked' => $this->booking?->seats_booked,
                ],
            ]],
            'ride_full' => [[
                'user_id' => $this->ride->driver_id,
                'type' => 'ride_full',
                'title' => 'Carona lotada',
                'message' => "Sua carona para {$destination} ({$departure}) está lotada e foi marcada como indisponível.",
                'data' => [],
            ]],
            'booking_cancelled' => [[
                'user_id' => $this->ride->driver_id,
                'type' => 'ride_booking_cancelled',
                'title' => 'Reserva cancelada',
                'message' => sprintf(
                    'Uma reserva de %d lugar(es) na carona para %s foi cancelada. Agora há %d vaga(s) disponível(is).',
                    $this->booking?->seats_booked ?? 1,
                    $destination,
                    $this->ride->seats_available
                ),
                'data' => [
                    'booking_id' => $this->booking?->id,
                ],
            ]],
            'ride_cancelled_passenger' => [[
                'user_id' => $this->booking?->passenger_id,
                'type' => 'ride_cancelled',
                'title' => 'Carona cancelada',
                'message' => "A carona para {$destination} ({$departure}) foi cancelada pelo motorista.",
                'data' => [
                    'booking_id' => $this->booking?->id,
                ],
            ]],
            'ride_published' => $this->buildPublishedMessages($destination, $departure),
            default => [],
        };
    }

    private function buildPublishedMessages(string $destination, string $departure): array
    {
        $driverName = $this->ride->driver?->name ?? 'Um morador';
        $seats = $this->ride->seats_available;

        return User::query()
            ->where('condominium_id', $this->ride->condominium_id)
            ->where('id', '!=', $this->ride->driver_id)
            ->active()
            ->where(function ($query) {
                $query->where('registration_status', 'approved')
                    ->orWhereNull('registration_status');
            })
            ->get(['id'])
            ->filter(fn (User $user) => $user->can('view_rides'))
            ->map(fn (User $user) => [
                'user_id' => $user->id,
                'type' => 'ride_published',
                'title' => 'Nova carona disponível!',
                'message' => sprintf(
                    '%s oferece carona para %s com partida em %s. %d %s disponível(is).',
                    $driverName,
                    $destination,
                    $departure,
                    $seats,
                    $seats === 1 ? 'vaga' : 'vagas'
                ),
                'data' => [
                    'driver_name' => $driverName,
                ],
            ])
            ->values()
            ->all();
    }
}
