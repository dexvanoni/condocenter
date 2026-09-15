<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\Package;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PackageReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_sindico_can_view_package_reports(): void
    {
        [$sindico, $unit] = $this->createSindicoWithUnit();

        Package::create([
            'condominium_id' => $sindico->condominium_id,
            'unit_id' => $unit->id,
            'registered_by' => $sindico->id,
            'type' => Package::TYPE_LEVE,
            'status' => Package::STATUS_PENDING,
            'received_at' => now(),
            'notification_sent' => false,
            'identification_method' => Package::METHOD_MANUAL,
        ]);

        $this->actingAs($sindico)
            ->get(route('packages.reports'))
            ->assertOk()
            ->assertSee('Movimentações de Encomendas');
    }

    public function test_porteiro_is_redirected_from_index_to_operational_panel(): void
    {
        [$porteiro] = $this->createPorteiroWithUnit();

        $this->actingAs($porteiro)
            ->get(route('packages.index'))
            ->assertOk()
            ->assertSee('Portaria');
    }

    public function test_sindico_index_redirects_to_reports(): void
    {
        [$sindico] = $this->createSindicoWithUnit();

        $this->actingAs($sindico)
            ->get(route('packages.index'))
            ->assertRedirect(route('packages.reports'));
    }

    public function test_sindico_can_export_package_reports_pdf(): void
    {
        [$sindico, $unit] = $this->createSindicoWithUnit();

        Package::create([
            'condominium_id' => $sindico->condominium_id,
            'unit_id' => $unit->id,
            'registered_by' => $sindico->id,
            'type' => Package::TYPE_LEVE,
            'status' => Package::STATUS_COLLECTED,
            'received_at' => now()->subDay(),
            'collected_at' => now(),
            'collected_by' => $sindico->id,
            'notification_sent' => true,
            'identification_method' => Package::METHOD_OCR,
        ]);

        $this->actingAs($sindico)
            ->get(route('packages.reports.pdf', [
                'from' => now()->subDays(7)->toDateString(),
                'to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_user_without_view_packages_cannot_access_reports(): void
    {
        $condominium = Condominium::factory()->create();
        $user = User::factory()->for($condominium)->create();

        $this->actingAs($user)
            ->get(route('packages.reports'))
            ->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Unit}
     */
    protected function createSindicoWithUnit(): array
    {
        $condominium = Condominium::factory()->create();
        $unit = Unit::factory()->create(['condominium_id' => $condominium->id]);

        $viewPackages = Permission::firstOrCreate(['name' => 'view_packages', 'guard_name' => 'web']);
        $exportReports = Permission::firstOrCreate(['name' => 'export_packages_reports', 'guard_name' => 'web']);

        $role = Role::firstOrCreate(['name' => 'Síndico', 'guard_name' => 'web']);
        $role->syncPermissions([$viewPackages, $exportReports]);

        $sindico = User::factory()->create(['condominium_id' => $condominium->id]);
        $sindico->assignRole($role);

        return [$sindico, $unit];
    }

    /**
     * @return array{0: User, 1: Unit}
     */
    protected function createPorteiroWithUnit(): array
    {
        $condominium = Condominium::factory()->create();
        $unit = Unit::factory()->create(['condominium_id' => $condominium->id]);

        $register = Permission::firstOrCreate(['name' => 'register_packages', 'guard_name' => 'web']);
        $view = Permission::firstOrCreate(['name' => 'view_packages', 'guard_name' => 'web']);

        $role = Role::firstOrCreate(['name' => 'Porteiro', 'guard_name' => 'web']);
        $role->syncPermissions([$register, $view]);

        $porteiro = User::factory()->create(['condominium_id' => $condominium->id]);
        $porteiro->assignRole($role);

        return [$porteiro, $unit];
    }
}
