<?php

namespace Tests\Feature;

use App\Models\Charge;
use App\Models\Condominium;
use App\Models\Fine;
use App\Models\FineRecipient;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\Space;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ChargeDueDateTest extends TestCase
{
    use RefreshDatabase;

    protected Condominium $condominium;
    protected User $sindico;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->condominium = Condominium::factory()->create();

        $role = Role::create(['name' => 'Síndico']);
        foreach (['manage_fines', 'view_fines', 'manage_reservations'] as $permission) {
            Permission::create(['name' => $permission]);
        }
        $role->givePermissionTo(['manage_fines', 'view_fines', 'manage_reservations']);

        $this->sindico = User::factory()->create([
            'condominium_id' => $this->condominium->id,
        ]);
        $this->sindico->assignRole($role);
    }

    protected function makeFineWithCharge(string $chargeStatus = 'pending'): array
    {
        $unit = Unit::factory()->create(['condominium_id' => $this->condominium->id]);

        $morador = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'unit_id' => $unit->id,
        ]);

        $fine = Fine::create([
            'condominium_id' => $this->condominium->id,
            'reference' => 'MULTA-2026-0001',
            'motivo' => 'Barulho após o horário permitido.',
            'enquadramento' => 'Regimento interno art. 12',
            'amount' => 150,
            'due_date' => now()->addDays(5)->toDateString(),
            'applied_at' => now(),
            'applied_by' => $this->sindico->id,
            'status' => 'issued',
        ]);

        $charge = Charge::create([
            'condominium_id' => $this->condominium->id,
            'unit_id' => $unit->id,
            'title' => 'Multa — Regimento interno art. 12',
            'description' => 'Cobrança da multa',
            'amount' => 150,
            'due_date' => now()->addDays(5)->toDateString(),
            'type' => 'extra',
            'status' => $chargeStatus,
            'generated_by' => 'fine',
            'metadata' => ['fine_id' => $fine->id],
        ]);

        FineRecipient::create([
            'fine_id' => $fine->id,
            'user_id' => $morador->id,
            'unit_id' => $unit->id,
            'notified_user_id' => $morador->id,
            'charge_id' => $charge->id,
        ]);

        return [$fine, $charge, $morador];
    }

    public function test_sindico_updates_fine_due_date_and_charge_follows(): void
    {
        [$fine, $charge, $morador] = $this->makeFineWithCharge('overdue');

        $newDate = now()->addDays(15)->toDateString();

        $response = $this->actingAs($this->sindico)->put(
            route('fines.due-date.update', $fine),
            ['due_date' => $newDate]
        );

        $response->assertRedirect(route('fines.show', $fine));

        $this->assertSame($newDate, $fine->fresh()->due_date->toDateString());

        $charge = $charge->fresh();
        $this->assertSame($newDate, $charge->due_date->toDateString());
        $this->assertSame('pending', $charge->status);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $morador->id,
            'type' => 'fine_due_date_updated',
        ]);
    }

    public function test_fine_due_date_cannot_change_when_charge_is_paid(): void
    {
        [$fine, $charge] = $this->makeFineWithCharge('paid');
        $originalDate = $charge->due_date->toDateString();

        $response = $this->actingAs($this->sindico)
            ->from(route('fines.show', $fine))
            ->put(route('fines.due-date.update', $fine), [
                'due_date' => now()->addDays(15)->toDateString(),
            ]);

        $response->assertSessionHasErrors('due_date');
        $this->assertSame($originalDate, $charge->fresh()->due_date->toDateString());
    }

    public function test_fine_due_date_cannot_be_in_the_past(): void
    {
        [$fine] = $this->makeFineWithCharge();

        $this->actingAs($this->sindico)
            ->from(route('fines.show', $fine))
            ->put(route('fines.due-date.update', $fine), [
                'due_date' => now()->subDay()->toDateString(),
            ])
            ->assertSessionHasErrors('due_date');
    }

    public function test_sindico_updates_reservation_charge_due_date_via_manage(): void
    {
        $unit = Unit::factory()->create(['condominium_id' => $this->condominium->id]);
        $morador = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'unit_id' => $unit->id,
        ]);

        $space = Space::create([
            'condominium_id' => $this->condominium->id,
            'name' => 'Salão de Festas',
            'description' => 'Espaço para eventos',
            'capacity' => 50,
            'price_per_hour' => 100,
            'is_active' => true,
        ]);

        $reservation = Reservation::create([
            'space_id' => $space->id,
            'user_id' => $morador->id,
            'unit_id' => $unit->id,
            'reservation_date' => now()->addDays(10)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'status' => 'approved',
        ]);

        $charge = Charge::create([
            'condominium_id' => $this->condominium->id,
            'unit_id' => $unit->id,
            'title' => 'Taxa de Reserva — Salão de Festas',
            'description' => 'Cobrança da reserva',
            'amount' => 200,
            'due_date' => now()->addDays(2)->toDateString(),
            'type' => 'extra',
            'status' => 'pending',
            'generated_by' => 'reservation',
            'metadata' => ['reservation_id' => $reservation->id, 'space_id' => $space->id],
        ]);

        $newDate = now()->addDays(8)->toDateString();

        $response = $this->actingAs($this->sindico)->put(
            "/reservations/manage/{$reservation->id}",
            [
                'space_id' => $space->id,
                'reservation_date' => $reservation->reservation_date->toDateString(),
                'start_time' => '10:00',
                'end_time' => '12:00',
                'status' => 'approved',
                'notes' => null,
                'admin_reason' => null,
                'charge_due_date' => $newDate,
            ]
        );

        $response->assertOk()->assertJson(['success' => true]);

        $charge = $charge->fresh();
        $this->assertSame($newDate, $charge->due_date->toDateString());
        $this->assertSame('pending', $charge->status);
    }
}
