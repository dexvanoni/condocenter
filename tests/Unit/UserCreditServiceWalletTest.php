<?php

namespace Tests\Unit;

use App\Models\Condominium;
use App\Models\Reservation;
use App\Models\Space;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserCredit;
use App\Services\UserCreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCreditServiceWalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_details_returns_available_and_usage_history(): void
    {
        $condominium = Condominium::factory()->create();
        $user = User::factory()->create(['condominium_id' => $condominium->id]);
        $unit = Unit::factory()->create(['condominium_id' => $condominium->id]);
        $space = Space::create([
            'condominium_id' => $condominium->id,
            'name' => 'Salão de Festas',
            'description' => 'Espaço para eventos',
            'capacity' => 50,
            'price_per_hour' => 100,
            'is_active' => true,
        ]);

        $reservation = Reservation::create([
            'space_id' => $space->id,
            'unit_id' => $unit->id,
            'user_id' => $user->id,
            'reservation_date' => now()->addDays(3),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'status' => 'approved',
        ]);

        UserCredit::create([
            'condominium_id' => $condominium->id,
            'user_id' => $user->id,
            'amount' => 25.00,
            'type' => 'refund',
            'description' => 'Estorno de reserva cancelada',
            'status' => 'available',
            'expires_at' => now()->addMonths(12),
        ]);

        UserCredit::create([
            'condominium_id' => $condominium->id,
            'user_id' => $user->id,
            'amount' => 10.00,
            'type' => 'refund',
            'description' => 'Crédito aplicado na reserva',
            'status' => 'used',
            'used_in_reservation_id' => $reservation->id,
            'used_at' => now(),
        ]);

        $wallet = app(UserCreditService::class)->getWalletDetails($user, $condominium->id);

        $this->assertSame(25.0, $wallet['total']);
        $this->assertCount(1, $wallet['credits']);
        $this->assertSame(25.0, $wallet['credits'][0]['amount']);
        $this->assertCount(1, $wallet['usage_history']);
        $this->assertSame(10.0, $wallet['usage_history'][0]['amount']);
        $this->assertSame($space->name, $wallet['usage_history'][0]['reservation']['space_name']);
    }
}
