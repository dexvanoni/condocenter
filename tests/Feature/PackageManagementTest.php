<?php

namespace Tests\Feature;

use App\Jobs\SendPackageNotification;
use App\Models\Condominium;
use App\Models\Package;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PackageManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_porteiro_can_register_package_and_dispatch_notification(): void
    {
        Queue::fake();

        [$porteiro, $unit] = $this->createPorteiroWithUnit();
        $this->createResidentForUnit($unit);

        Sanctum::actingAs($porteiro);

        $response = $this->postJson('/api/packages', [
            'unit_id' => $unit->id,
            'type' => Package::TYPE_FRAGIL,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('package.type', Package::TYPE_FRAGIL)
            ->assertJsonPath('package.status', Package::STATUS_PENDING);

        $this->assertDatabaseHas('packages', [
            'unit_id' => $unit->id,
            'type' => Package::TYPE_FRAGIL,
            'status' => Package::STATUS_PENDING,
        ]);

        Queue::assertPushed(SendPackageNotification::class, function (SendPackageNotification $job) use ($unit) {
            return $job->type === 'arrived'
                && $job->package->unit_id === $unit->id;
        });
    }

    public function test_porteiro_can_mark_package_as_collected(): void
    {
        Queue::fake();

        [$porteiro, $unit] = $this->createPorteiroWithUnit();
        $this->createResidentForUnit($unit);

        $package = Package::create([
            'condominium_id' => $porteiro->condominium_id,
            'unit_id' => $unit->id,
            'registered_by' => $porteiro->id,
            'type' => Package::TYPE_PESADO,
            'status' => Package::STATUS_PENDING,
            'received_at' => now()->subHour(),
            'notification_sent' => false,
        ]);

        Sanctum::actingAs($porteiro);

        $response = $this->postJson("/api/packages/{$package->id}/collect");

        $response
            ->assertOk()
            ->assertJsonPath('package.status', Package::STATUS_COLLECTED);

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => Package::STATUS_COLLECTED,
            'collected_by' => $porteiro->id,
        ]);

        Queue::assertPushed(SendPackageNotification::class, function (SendPackageNotification $job) use ($package) {
            return $job->type === 'collected'
                && $job->package->id === $package->id;
        });
    }

    public function test_summary_requires_register_permission(): void
    {
        $condominium = Condominium::factory()->create();
        $unit = Unit::factory()->create([
            'condominium_id' => $condominium->id,
        ]);

        $user = User::factory()->for($condominium)->create([
            'unit_id' => $unit->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/packages/summary/units')
            ->assertForbidden();
    }

    public function test_summary_returns_units_with_pending_packages(): void
    {
        [$porteiro, $unit] = $this->createPorteiroWithUnit();
        $this->createResidentForUnit($unit);

        $otherUnit = Unit::factory()->create([
            'condominium_id' => $porteiro->condominium_id,
            'block' => 'B',
            'number' => '203',
        ]);

        Package::create([
            'condominium_id' => $porteiro->condominium_id,
            'unit_id' => $unit->id,
            'registered_by' => $porteiro->id,
            'type' => Package::TYPE_LEVE,
            'status' => Package::STATUS_PENDING,
            'received_at' => now()->subMinutes(10),
            'notification_sent' => false,
        ]);

        Package::create([
            'condominium_id' => $porteiro->condominium_id,
            'unit_id' => $otherUnit->id,
            'registered_by' => $porteiro->id,
            'type' => Package::TYPE_CAIXA_GRANDE,
            'status' => Package::STATUS_COLLECTED,
            'received_at' => now()->subDay(),
            'notification_sent' => true,
        ]);

        Sanctum::actingAs($porteiro);

        $response = $this->getJson('/api/packages/summary/units');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'block',
                        'number',
                        'pending_packages_count',
                        'pending_packages',
                        'residents',
                    ],
                ],
            ])
            ->assertJsonPath('data.0.pending_packages_count', 1);
    }

    protected function createPorteiroWithUnit(): array
    {
        $condominium = Condominium::factory()->create();
        $unit = Unit::factory()->create([
            'condominium_id' => $condominium->id,
            'block' => 'A',
            'number' => '101',
        ]);

        $registerPackages = Permission::firstOrCreate(
            ['name' => 'register_packages', 'guard_name' => 'web']
        );
        $viewPackages = Permission::firstOrCreate(
            ['name' => 'view_packages', 'guard_name' => 'web']
        );

        $porteiroRole = Role::firstOrCreate(['name' => 'Porteiro', 'guard_name' => 'web']);
        $porteiroRole->syncPermissions([$registerPackages, $viewPackages]);

        $porteiro = User::factory()->create([
            'condominium_id' => $condominium->id,
            'unit_id' => null,
        ]);

        $porteiro->assignRole($porteiroRole);

        return [$porteiro, $unit];
    }

    protected function createResidentForUnit(Unit $unit): User
    {
        $viewPackages = Permission::firstOrCreate(
            ['name' => 'view_packages', 'guard_name' => 'web']
        );

        $moradorRole = Role::firstOrCreate(['name' => 'Morador', 'guard_name' => 'web']);
        if (!$moradorRole->hasPermissionTo($viewPackages)) {
            $moradorRole->givePermissionTo($viewPackages);
        }

        $resident = User::factory()->create([
            'condominium_id' => $unit->condominium_id,
            'unit_id' => $unit->id,
        ]);

        $resident->assignRole($moradorRole);

        return $resident;
    }
}

