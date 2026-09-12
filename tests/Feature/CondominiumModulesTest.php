<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\User;
use App\Support\CondominiumModules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CondominiumModulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_null_enabled_modules_keeps_all_catalog_modules_on(): void
    {
        $condominium = Condominium::factory()->create([
            'enabled_modules' => null,
        ]);

        foreach (CondominiumModules::keys() as $key) {
            $this->assertTrue($condominium->hasModule($key));
        }
    }

    public function test_sindico_can_save_enabled_modules(): void
    {
        $condominium = Condominium::factory()->create([
            'enabled_modules' => CondominiumModules::keys(),
        ]);

        Role::create(['name' => 'Síndico']);

        $sindico = User::factory()->create([
            'condominium_id' => $condominium->id,
        ]);
        $sindico->assignRole('Síndico');

        $response = $this->actingAs($sindico)->put(
            route('condominiums.settings.modules.update', $condominium),
            ['modules' => ['financial', 'packages']]
        );

        $response->assertRedirect(route('condominiums.show', $condominium));

        $this->assertSame(
            ['financial', 'packages'],
            $condominium->fresh()->enabled_modules
        );
    }

    public function test_marketplace_web_is_forbidden_when_module_is_off(): void
    {
        $condominium = Condominium::factory()->create([
            'enabled_modules' => ['financial'],
        ]);

        $permission = Permission::create(['name' => 'view_marketplace']);
        $role = Role::create(['name' => 'Morador']);
        $role->givePermissionTo($permission);

        $morador = User::factory()->create([
            'condominium_id' => $condominium->id,
        ]);
        $morador->assignRole($role);

        $this->actingAs($morador)
            ->get(route('marketplace.index'))
            ->assertForbidden();
    }

    public function test_marketplace_api_returns_403_when_module_is_off(): void
    {
        $condominium = Condominium::factory()->create([
            'enabled_modules' => ['packages'],
        ]);

        $permission = Permission::create(['name' => 'view_marketplace']);
        $role = Role::create(['name' => 'Morador']);
        $role->givePermissionTo($permission);

        $morador = User::factory()->create([
            'condominium_id' => $condominium->id,
        ]);
        $morador->assignRole($role);

        Sanctum::actingAs($morador);

        $this->getJson('/api/marketplace')
            ->assertForbidden()
            ->assertJsonFragment([
                'error' => 'Este módulo não está habilitado neste condomínio.',
            ]);
    }

    public function test_financial_web_redirects_when_module_is_off(): void
    {
        $condominium = Condominium::factory()->create([
            'enabled_modules' => ['marketplace'],
        ]);

        $permission = Permission::create(['name' => 'view_charges']);
        $role = Role::create(['name' => 'Síndico']);
        $role->givePermissionTo($permission);

        $sindico = User::factory()->create([
            'condominium_id' => $condominium->id,
        ]);
        $sindico->assignRole($role);

        $this->actingAs($sindico)
            ->get(route('charges.index'))
            ->assertRedirect(route('dashboard'));
    }
}
