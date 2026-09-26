<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Organization;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\UserScopeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserScopePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Síndico', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Morador', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Porteiro', 'guard_name' => 'web']);
    }

    public function test_syndic_can_assign_porteiro_but_not_administrador(): void
    {
        $scope = app(UserScopeService::class);
        $syndic = User::factory()->create();
        $syndic->assignRole('Síndico');
        session(['active_role' => 'Síndico']);

        $this->assertTrue($scope->canAssignRole($syndic, 'Porteiro'));
        $this->assertFalse($scope->canAssignRole($syndic, 'Administrador'));
        $this->assertFalse($scope->canAssignRole($syndic, 'Síndico'));
    }

    public function test_management_company_profile_can_assign_syndic_not_platform_admin(): void
    {
        $scope = app(UserScopeService::class);
        $org = Organization::factory()->create(['type' => Organization::TYPE_MANAGEMENT_COMPANY]);
        $owner = User::factory()->create(['condominium_id' => null]);
        $org->users()->attach($owner->id, ['role' => Organization::ROLE_OWNER]);
        $owner->assignRole('Síndico');
        session(['active_role' => User::PROFILE_ADMINISTRADORA]);

        $this->assertTrue($scope->canAssignRole($owner, 'Síndico'));
        $this->assertFalse($scope->canAssignRole($owner, 'Administrador'));
    }

    public function test_cannot_reset_own_password_via_policy(): void
    {
        $policy = app(UserPolicy::class);
        $user = User::factory()->create();
        $user->assignRole('Síndico');
        session(['active_role' => 'Síndico']);

        $this->assertFalse($policy->resetPassword($user, $user));
    }

    public function test_syndic_can_reset_password_for_user_in_same_condominium(): void
    {
        $condominium = Condominium::factory()->create(['saas_complimentary' => true]);
        $syndic = User::factory()->create(['condominium_id' => $condominium->id]);
        $syndic->assignRole('Síndico');
        Permission::firstOrCreate(['name' => 'manage_users', 'guard_name' => 'web']);
        $syndic->givePermissionTo('manage_users');
        $morador = User::factory()->create(['condominium_id' => $condominium->id]);
        $morador->assignRole('Morador');

        session(['active_role' => 'Síndico', 'active_condominium_id' => $condominium->id]);

        $policy = app(UserPolicy::class);
        $this->assertTrue($policy->resetPassword($syndic, $morador));
    }

    public function test_professional_syndic_can_edit_own_profile_without_condominium_on_record(): void
    {
        $first = Condominium::factory()->create(['saas_complimentary' => true]);
        $second = Condominium::factory()->create(['saas_complimentary' => true]);
        $syndic = User::factory()->create([
            'condominium_id' => null,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $syndic->assignRole('Síndico');
        $first->syndics()->attach($syndic->id);
        $second->syndics()->attach($syndic->id);

        session(['active_role' => 'Síndico', 'active_condominium_id' => $second->id]);

        $this->actingAs($syndic)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertViewIs('users.profile-edit');

        $this->actingAs($syndic)
            ->get(route('users.edit', $syndic))
            ->assertOk()
            ->assertViewIs('users.profile-edit');
    }

    public function test_syndic_sees_particular_condominium_via_home_condominium_id_in_managed_list(): void
    {
        $orgCondo = Condominium::factory()->create(['saas_complimentary' => true]);
        $particular = Condominium::factory()->create(['saas_complimentary' => true, 'name' => 'Particular Alpha']);
        $syndic = User::factory()->create([
            'condominium_id' => $particular->id,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $syndic->assignRole('Síndico');
        $orgCondo->syndics()->syncWithoutDetaching([$syndic->id]);
        $secondOrg = Condominium::factory()->create(['saas_complimentary' => true]);
        $secondOrg->syndics()->syncWithoutDetaching([$syndic->id]);

        session(['active_role' => 'Síndico']);

        $ids = app(\App\Services\ActiveCondominiumService::class)
            ->accessibleCondominiumIds($syndic)
            ->all();

        $this->assertCount(3, $ids);
        $this->assertContains($particular->id, $ids);

        $this->actingAs($syndic)
            ->get(route('syndic.condominiums.index'))
            ->assertOk()
            ->assertSee('Particular Alpha', false);
    }

    public function test_professional_syndic_can_open_profile_without_active_condominium(): void
    {
        $condominium = Condominium::factory()->create(['saas_complimentary' => true]);
        $syndic = User::factory()->create([
            'condominium_id' => null,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $syndic->assignRole('Síndico');
        $condominium->syndics()->attach($syndic->id);

        session(['active_role' => 'Síndico']);

        $this->actingAs($syndic)
            ->get(route('profile.edit'))
            ->assertOk();
    }
}
