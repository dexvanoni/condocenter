<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\User;
use App\Services\CondominiumSyndicService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CondominiumSyndicServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'Síndico', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
    }

    public function test_lists_syndic_from_home_condominium_id_and_pivot(): void
    {
        $condominium = Condominium::factory()->create();
        $service = app(CondominiumSyndicService::class);

        $homeSyndic = User::factory()->create(['condominium_id' => $condominium->id]);
        $homeSyndic->assignRole('Síndico');

        $pivotSyndic = User::factory()->create(['condominium_id' => null]);
        $pivotSyndic->assignRole('Síndico');
        $condominium->syndics()->attach($pivotSyndic->id);

        $ids = $service->syndicsFor($condominium)->pluck('id')->all();

        $this->assertContains($homeSyndic->id, $ids);
        $this->assertContains($pivotSyndic->id, $ids);
    }

    public function test_detach_removes_pivot_and_clears_home_condominium_id(): void
    {
        $condominium = Condominium::factory()->create();
        $service = app(CondominiumSyndicService::class);

        $syndic = User::factory()->create(['condominium_id' => $condominium->id]);
        $syndic->assignRole('Síndico');
        $condominium->syndics()->attach($syndic->id);

        $service->detachSyndic($syndic, $condominium);

        $syndic->refresh();
        $this->assertNull($syndic->condominium_id);
        $this->assertFalse($condominium->syndics()->whereKey($syndic->id)->exists());
    }
}
