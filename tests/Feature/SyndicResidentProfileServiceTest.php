<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Unit;
use App\Models\User;
use App\Services\SyndicResidentProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SyndicResidentProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'Síndico', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Morador', 'guard_name' => 'web']);
    }

    public function test_syndic_can_add_morador_role_with_unit(): void
    {
        $condominium = Condominium::factory()->create();
        $unit = Unit::factory()->create(['condominium_id' => $condominium->id]);

        $syndic = User::factory()->create(['condominium_id' => $condominium->id]);
        $syndic->assignRole('Síndico');

        $service = app(SyndicResidentProfileService::class);
        $service->syncOwnMoradorProfile($syndic, true, $unit->id);

        $syndic->refresh();
        $this->assertTrue($syndic->hasAssignedRole('Morador'));
        $this->assertTrue($syndic->hasAssignedRole('Síndico'));
        $this->assertSame((int) $unit->id, (int) $syndic->unit_id);
        $this->assertTrue($syndic->hasProfileSwitcher());
    }

    public function test_pivot_syndic_can_add_morador_on_the_active_condominium(): void
    {
        $condominium = Condominium::factory()->create();
        $unit = Unit::factory()->create(['condominium_id' => $condominium->id]);
        $syndic = User::factory()->create(['condominium_id' => null]);
        $syndic->assignRole('Síndico');
        $condominium->syndics()->attach($syndic->id);

        session(['active_condominium_id' => $condominium->id]);

        $service = app(SyndicResidentProfileService::class);
        $this->assertTrue($service->canManageOwnMoradorProfile($syndic));

        $service->syncOwnMoradorProfile($syndic, true, $unit->id);

        $syndic->refresh();
        $this->assertTrue($syndic->hasAssignedRole('Morador'));
        $this->assertSame((int) $condominium->id, (int) $syndic->condominium_id);
        $this->assertSame((int) $unit->id, (int) $syndic->unit_id);
    }

    public function test_syndic_can_remove_morador_role(): void
    {
        $condominium = Condominium::factory()->create();
        $unit = Unit::factory()->create(['condominium_id' => $condominium->id]);

        $syndic = User::factory()->create([
            'condominium_id' => $condominium->id,
            'unit_id' => $unit->id,
        ]);
        $syndic->assignRole(['Síndico', 'Morador']);

        $service = app(SyndicResidentProfileService::class);
        $service->syncOwnMoradorProfile($syndic, false, null);

        $syndic->refresh();
        $this->assertFalse($syndic->hasAssignedRole('Morador'));
        $this->assertTrue($syndic->hasAssignedRole('Síndico'));
        $this->assertNull($syndic->unit_id);
    }
}
