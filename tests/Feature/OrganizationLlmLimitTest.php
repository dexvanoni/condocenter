<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OrganizationLlmLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
    }

    public function test_platform_admin_can_set_llm_monthly_limit_on_organization(): void
    {
        $admin = User::factory()->create([
            'condominium_id' => null,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('Administrador');

        $org = Organization::factory()->managementCompany()->create([
            'llm_monthly_limit' => null,
        ]);

        $this->actingAs($admin)
            ->patch(route('platform.organizations.update-llm-limit', $org), [
                'llm_monthly_limit' => 40,
            ])
            ->assertRedirect();

        $this->assertSame(40, $org->fresh()->llm_monthly_limit);

        $this->actingAs($admin)
            ->get(route('platform.organizations.show', $org))
            ->assertOk()
            ->assertSee('Consultor Financeiro (LLM)', false)
            ->assertSee('compartilhado', false);
    }
}
