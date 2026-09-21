<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CondominiumUnitsLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_sindico_cannot_create_unit_when_limit_reached(): void
    {
        $condominium = Condominium::factory()->create(['units_limit' => 1]);
        Unit::factory()->create(['condominium_id' => $condominium->id, 'number' => '101']);

        $sindico = $this->makeSindicoWithCreateUnits($condominium);

        $this->actingAs($sindico)
            ->post(route('units.store'), $this->validUnitPayload())
            ->assertSessionHasErrors('number');

        $this->assertSame(1, $condominium->units()->count());
    }

    public function test_sindico_sees_limit_message_on_create_form(): void
    {
        $condominium = Condominium::factory()->create(['units_limit' => 1]);
        Unit::factory()->create(['condominium_id' => $condominium->id]);

        $sindico = $this->makeSindicoWithCreateUnits($condominium);

        $this->actingAs($sindico)
            ->get(route('units.create'))
            ->assertOk()
            ->assertSee('Limite de unidades atingido', false);
    }

    public function test_admin_can_set_units_limit_on_create(): void
    {
        $admin = $this->makePlatformAdmin();

        $this->actingAs($admin)
            ->post(route('condominiums.store'), [
                'name' => 'Residencial Teste',
                'zip_code' => '60000-000',
                'address' => 'Rua A',
                'city' => 'Fortaleza',
                'state' => 'CE',
                'financial_mode' => 'full',
                'units_limit' => 120,
                'is_active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('condominiums', [
            'name' => 'Residencial Teste',
            'units_limit' => 120,
        ]);
    }

    public function test_admin_cannot_lower_limit_below_existing_units(): void
    {
        $condominium = Condominium::factory()->create(['units_limit' => 10]);
        Unit::factory()->count(3)->create(['condominium_id' => $condominium->id]);

        $admin = $this->makePlatformAdmin();

        $this->actingAs($admin)
            ->put(route('condominiums.update', $condominium), [
                'name' => $condominium->name,
                'zip_code' => $condominium->zip_code,
                'address' => $condominium->address,
                'city' => $condominium->city,
                'state' => $condominium->state,
                'financial_mode' => 'full',
                'units_limit' => 2,
            ])
            ->assertSessionHasErrors('units_limit');
    }

    public function test_sindico_cannot_change_units_limit_via_update(): void
    {
        $condominium = Condominium::factory()->create(['units_limit' => 5]);
        $sindico = User::factory()->create(['condominium_id' => $condominium->id]);
        $sindico->assignRole(Role::firstOrCreate(['name' => 'Síndico', 'guard_name' => 'web']));

        $this->actingAs($sindico)
            ->put(route('condominiums.update', $condominium), [
                'name' => $condominium->name,
                'zip_code' => $condominium->zip_code,
                'address' => $condominium->address,
                'city' => $condominium->city,
                'state' => $condominium->state,
                'units_limit' => 999,
            ])
            ->assertRedirect(route('condominiums.show', $condominium));

        $this->assertSame(5, $condominium->fresh()->units_limit);
    }

    private function makeSindicoWithCreateUnits(Condominium $condominium): User
    {
        $permission = Permission::firstOrCreate(['name' => 'create_units', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'Síndico', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $sindico = User::factory()->create(['condominium_id' => $condominium->id]);
        $sindico->assignRole($role);

        return $sindico;
    }

    private function makePlatformAdmin(): User
    {
        Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $admin = User::factory()->create(['condominium_id' => null]);
        $admin->assignRole('Administrador');

        return $admin;
    }

    /**
     * @return array<string, mixed>
     */
    private function validUnitPayload(): array
    {
        return [
            'number' => '202',
            'block' => 'B',
            'type' => 'residential',
            'unit_model' => 'apartamento',
            'situacao' => 'habitado',
            'occupancy_regime' => 'particular',
        ];
    }
}
