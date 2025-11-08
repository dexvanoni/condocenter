<?php

namespace Tests\Feature;

use App\Models\AgregadoPermission;
use App\Models\Condominium;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MarketplaceModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_admin_can_toggle_agregado_setting(): void
    {
        $condominium = Condominium::factory()->create([
            'marketplace_allow_agregados' => false,
        ]);

        $manageMarketplace = Permission::create(['name' => 'manage_marketplace']);
        $adminRole = Role::create(['name' => 'Administrador']);
        $adminRole->givePermissionTo($manageMarketplace);

        $admin = User::factory()
            ->for($condominium)
            ->create();

        $admin->assignRole($adminRole);

        $response = $this->actingAs($admin)
            ->post(route('marketplace.admin.settings.toggle'), [
                'marketplace_allow_agregados' => true,
            ]);

        $response->assertRedirect(route('marketplace.admin.index'));

        $this->assertTrue($condominium->fresh()->marketplace_allow_agregados);
    }

    public function test_agregado_creation_respects_toggle_and_permissions(): void
    {
        $condominium = Condominium::factory()->create([
            'marketplace_allow_agregados' => false,
        ]);

        $unit = Unit::factory()->create([
            'condominium_id' => $condominium->id,
        ]);

        $agregadoRole = Role::create(['name' => 'Agregado']);
        Permission::create(['name' => 'view_marketplace']);
        $agregadoRole->givePermissionTo('view_marketplace');

        $agregado = User::factory()
            ->for($condominium)
            ->create([
                'unit_id' => $unit->id,
            ]);

        $agregado->assignRole($agregadoRole);

        Sanctum::actingAs($agregado);

        $payload = [
            'title' => 'Bicicleta Nova',
            'description' => 'Bicicleta em ótimo estado, pouco uso.',
            'price' => 800.00,
            'category' => 'products',
            'condition' => 'used',
            'whatsapp' => '11987654321',
        ];

        // Toggle desativado: deve bloquear mesmo com permissão de agregado
        AgregadoPermission::create([
            'user_id' => $agregado->id,
            'granted_by' => $agregado->id,
            'permission_key' => 'marketplace',
            'permission_level' => 'crud',
            'is_granted' => true,
        ]);

        $this->postJson('/api/marketplace', $payload)->assertStatus(403);

        // Toggle ativado, porém sem permissão CRUD: ainda deve bloquear
        $condominium->update(['marketplace_allow_agregados' => true]);
        AgregadoPermission::query()->update(['is_granted' => false]);

        $this->postJson('/api/marketplace', $payload)->assertStatus(403);

        // Toggle ativado e permissão concedida: requisição deve ser aceita
        AgregadoPermission::query()->update(['is_granted' => true]);

        $this->postJson('/api/marketplace', $payload)
            ->assertCreated()
            ->assertJsonStructure(['message', 'item' => ['id', 'title']]);
    }
}

