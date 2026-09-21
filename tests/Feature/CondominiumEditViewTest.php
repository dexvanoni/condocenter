<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CondominiumEditViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_condominium_edit_page_renders_layout(): void
    {
        Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $admin = User::factory()->create(['condominium_id' => null]);
        $admin->assignRole('Administrador');

        $condominium = Condominium::factory()->create(['units_limit' => 10]);

        $this->actingAs($admin)
            ->get(route('condominiums.edit', $condominium))
            ->assertOk()
            ->assertSee('Editar Condomínio', false);
    }
}
