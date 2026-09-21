<?php

namespace Tests\Feature;

use App\Models\Charge;
use App\Models\Condominium;
use App\Models\Unit;
use App\Models\User;
use App\Support\UnitOccupancyRegimes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OwnerTenantReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_owner_can_download_tenant_report_pdf(): void
    {
        $condominium = Condominium::factory()->create();
        $owner = User::factory()->create(['condominium_id' => $condominium->id]);
        Role::firstOrCreate(['name' => 'Proprietário', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Morador', 'guard_name' => 'web']);
        $owner->assignRole('Proprietário');

        $unit = Unit::factory()->create([
            'condominium_id' => $condominium->id,
            'owner_user_id' => $owner->id,
            'occupancy_regime' => UnitOccupancyRegimes::ALUGUEL,
        ]);

        $morador = User::factory()->create([
            'condominium_id' => $condominium->id,
            'unit_id' => $unit->id,
        ]);
        $morador->assignRole('Morador');

        Charge::create([
            'condominium_id' => $condominium->id,
            'unit_id' => $unit->id,
            'title' => 'Multa convertida',
            'amount' => 80,
            'due_date' => now()->subDays(5),
            'status' => 'overdue',
            'type' => 'extra',
            'generated_by' => 'fine',
            'metadata' => ['fine_id' => 1],
        ]);

        $response = $this->actingAs($owner)
            ->get(route('owner.tenant-report.pdf', $unit));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_non_owner_cannot_download_tenant_report(): void
    {
        $condominium = Condominium::factory()->create();
        $owner = User::factory()->create(['condominium_id' => $condominium->id]);
        $stranger = User::factory()->create(['condominium_id' => $condominium->id]);

        $unit = Unit::factory()->create([
            'condominium_id' => $condominium->id,
            'owner_user_id' => $owner->id,
        ]);

        $this->actingAs($stranger)
            ->get(route('owner.tenant-report.pdf', $unit))
            ->assertForbidden();
    }
}
