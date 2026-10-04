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
        Role::firstOrCreate(['name' => 'Conselho Fiscal', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Agregado', 'guard_name' => 'web']);
    }

    public function test_syndic_can_assign_porteiro_but_not_administrador(): void
    {
        $scope = app(UserScopeService::class);
        $syndic = User::factory()->create();
        $syndic->assignRole('Síndico');
        session(['active_role' => 'Síndico']);

        $this->assertTrue($scope->canAssignRole($syndic, 'Porteiro'));
        $this->assertTrue($scope->canAssignRole($syndic, 'Síndico'));
        $this->assertTrue($scope->canAssignRole($syndic, 'Conselho Fiscal'));
        $this->assertTrue($scope->canAssignRole($syndic, 'Agregado'));
        $this->assertFalse($scope->canAssignRole($syndic, 'Administrador'));
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

    public function test_platform_admin_sees_himself_in_condominium_users_and_edits_own_roles(): void
    {
        $condominium = Condominium::factory()->create(['saas_complimentary' => true]);
        \App\Models\Unit::factory()->create(['condominium_id' => $condominium->id]);
        $admin = User::factory()->create([
            'name' => 'Denis Plataforma',
            'condominium_id' => null,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole(['Administrador', 'Síndico']);
        $viewUsers = Permission::firstOrCreate(['name' => 'view_users', 'guard_name' => 'web']);
        $manageUsers = Permission::firstOrCreate(['name' => 'manage_users', 'guard_name' => 'web']);
        Role::findByName('Administrador', 'web')->givePermissionTo([$viewUsers, $manageUsers]);
        $condominium->syndics()->attach($admin->id);

        session(['active_role' => 'Administrador', 'active_condominium_id' => $condominium->id]);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Denis Plataforma', false);

        $this->actingAs($admin)
            ->get(route('users.edit', $admin))
            ->assertOk()
            ->assertViewIs('users.edit')
            ->assertSee('value="Morador"', false);
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

    public function test_syndic_can_assign_sindico_role_to_resident(): void
    {
        $condominium = Condominium::factory()->create(['saas_complimentary' => true]);
        $unit = \App\Models\Unit::factory()->create(['condominium_id' => $condominium->id]);

        $manageUsers = Permission::firstOrCreate(['name' => 'manage_users', 'guard_name' => 'web']);
        $viewUsers = Permission::firstOrCreate(['name' => 'view_users', 'guard_name' => 'web']);
        $sindicoRole = Role::findByName('Síndico', 'web');
        $sindicoRole->givePermissionTo([$manageUsers, $viewUsers]);

        $syndic = User::factory()->create([
            'condominium_id' => $condominium->id,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $syndic->assignRole('Síndico');

        $resident = User::factory()->create([
            'condominium_id' => $condominium->id,
            'unit_id' => $unit->id,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $resident->assignRole('Morador');

        session(['active_role' => 'Síndico', 'active_condominium_id' => $condominium->id]);

        $this->actingAs($syndic)
            ->get(route('users.edit', $resident))
            ->assertOk()
            ->assertSee('value="Síndico"', false);

        $this->actingAs($syndic)
            ->put(route('users.update', $resident), [
                'name' => $resident->name,
                'email' => $resident->email,
                'unit_id' => $unit->id,
                'roles' => ['Morador', 'Síndico'],
                'is_active' => 1,
            ])
            ->assertRedirect(route('users.show', $resident));

        $resident->refresh();
        $this->assertTrue($resident->hasAssignedRole('Síndico'));
        $this->assertTrue($resident->hasAssignedRole('Morador'));
        $this->assertTrue($resident->managedCondominiums()->whereKey($condominium->id)->exists());
    }

    public function test_syndic_create_form_lists_sindico_role(): void
    {
        $condominium = Condominium::factory()->create(['saas_complimentary' => true]);
        $manageUsers = Permission::firstOrCreate(['name' => 'manage_users', 'guard_name' => 'web']);
        $viewUsers = Permission::firstOrCreate(['name' => 'view_users', 'guard_name' => 'web']);
        Role::findByName('Síndico', 'web')->givePermissionTo([$manageUsers, $viewUsers]);

        $syndic = User::factory()->create([
            'condominium_id' => $condominium->id,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $syndic->assignRole('Síndico');

        session(['active_role' => 'Síndico', 'active_condominium_id' => $condominium->id]);

        $this->actingAs($syndic)
            ->get(route('users.create'))
            ->assertOk()
            ->assertSee('value="Síndico"', false)
            ->assertDontSee('value="Administrador"', false);
    }

    public function test_syndic_cannot_strip_administrador_when_editing_user(): void
    {
        $scope = app(UserScopeService::class);
        $condominium = Condominium::factory()->create();
        $syndic = User::factory()->create(['condominium_id' => $condominium->id]);
        $syndic->assignRole('Síndico');
        session(['active_role' => 'Síndico']);

        $target = User::factory()->create(['condominium_id' => $condominium->id]);
        $target->assignRole(['Administrador', 'Morador']);

        $merged = $scope->mergeUnassignableExistingRoles($syndic, $target, ['Morador', 'Síndico']);

        $this->assertContains('Administrador', $merged);
        $this->assertContains('Síndico', $merged);
        $this->assertContains('Morador', $merged);
    }

    public function test_pivot_syndic_can_manage_condominium(): void
    {
        $condominium = Condominium::factory()->create();
        $syndic = User::factory()->create(['condominium_id' => null]);
        $syndic->assignRole('Síndico');
        $condominium->syndics()->attach($syndic->id);
        session(['active_role' => 'Síndico']);

        $policy = app(\App\Policies\CondominiumPolicy::class);
        $this->assertTrue($policy->view($syndic, $condominium));
        $this->assertTrue($policy->update($syndic, $condominium));
    }

    public function test_roles_for_user_form_includes_assignable_sindico_for_syndic_actor(): void
    {
        $scope = app(UserScopeService::class);
        $condominium = Condominium::factory()->create(['saas_complimentary' => true]);
        $manageUsers = Permission::firstOrCreate(['name' => 'manage_users', 'guard_name' => 'web']);
        Role::findByName('Síndico', 'web')->givePermissionTo($manageUsers);

        $syndic = User::factory()->create([
            'condominium_id' => $condominium->id,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $syndic->assignRole('Síndico');
        session(['active_role' => 'Síndico', 'active_condominium_id' => $condominium->id]);

        $names = $scope->rolesForUserForm($syndic)->pluck('name')->all();

        $this->assertContains('Síndico', $names);
        $this->assertContains('Conselho Fiscal', $names);
        $this->assertNotContains('Administrador', $names);
    }
}
