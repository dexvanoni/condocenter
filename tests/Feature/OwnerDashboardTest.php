<?php

namespace Tests\Feature;

use App\Models\Charge;
use App\Models\Condominium;
use App\Models\Unit;
use App\Models\User;
use App\Services\OwnerDashboardService;
use App\Support\UnitOccupancyRegimes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OwnerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_panorama_lists_owned_units_and_owner_charges(): void
    {
        $condominium = Condominium::factory()->create();
        $owner = User::factory()->create(['condominium_id' => $condominium->id]);
        Role::firstOrCreate(['name' => 'Proprietário', 'guard_name' => 'web']);
        $owner->assignRole('Proprietário');

        $unit = Unit::factory()->create([
            'condominium_id' => $condominium->id,
            'owner_user_id' => $owner->id,
            'occupancy_regime' => UnitOccupancyRegimes::PARTICULAR,
            'number' => '101',
            'block' => 'A',
        ]);

        Charge::create([
            'condominium_id' => $condominium->id,
            'unit_id' => $unit->id,
            'title' => 'Taxa condominial',
            'amount' => 150.00,
            'due_date' => now()->addDays(10),
            'status' => 'pending',
            'type' => 'regular',
        ]);

        $data = app(OwnerDashboardService::class)->panorama($owner, $condominium->id);

        $this->assertSame(1, $data['summary']['units_count']);
        $this->assertSame(1, $data['summary']['open_owner_charges']);
        $this->assertSame('101', $data['units']->first()['unit']->number);
    }

    public function test_proprietario_dashboard_page_renders_panorama(): void
    {
        $condominium = Condominium::factory()->create();
        $owner = User::factory()->create(['condominium_id' => $condominium->id]);
        Role::firstOrCreate(['name' => 'Proprietário', 'guard_name' => 'web']);
        $owner->assignRole('Proprietário');

        Unit::factory()->create([
            'condominium_id' => $condominium->id,
            'owner_user_id' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Panorama das unidades', false)
            ->assertSee('Relatório do inquilino', false);
    }
}
