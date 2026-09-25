<?php

namespace Tests\Feature;

use App\Models\Charge;
use App\Models\Condominium;
use App\Models\Fine;
use App\Models\Organization;
use App\Models\Unit;
use App\Models\User;
use App\Services\OrganizationCondominiumInsightsService;
use App\Services\OrganizationProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class OrganizationDashboardInsightsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::create(['name' => 'Administrador']);
        Role::create(['name' => 'Síndico']);
        Role::create(['name' => 'Morador']);
    }

    public function test_dashboard_shows_fines_financial_health_and_users_per_condominium(): void
    {
        $organization = Organization::factory()->managementCompany()->create();
        $owner = User::factory()->create(['condominium_id' => null, 'senha_temporaria' => false]);
        app(OrganizationProvisioningService::class)->attachUser($organization, (int) $owner->id, Organization::ROLE_OWNER);

        $critical = $this->condominium($organization, 'Residencial Aurora', 'Campinas', 'SP');
        $healthy = $this->condominium($organization, 'Edificio Estavel', 'Santos', 'SP');
        $other = Condominium::factory()->create(['name' => 'Fora da Carteira']);

        $this->seedUsers($critical, 3);
        $criticalUnits = Unit::factory()->count(2)->create(['condominium_id' => $critical->id]);
        $this->issueFine($critical, $owner, 250.50);
        Charge::create([
            'condominium_id' => $critical->id,
            'unit_id' => $criticalUnits->first()->id,
            'title' => 'Taxa em atraso',
            'amount' => 400,
            'due_date' => now()->subDays(3)->toDateString(),
            'status' => 'pending',
            'type' => 'regular',
        ]);

        $this->seedUsers($healthy, 1);
        Unit::factory()->count(2)->create(['condominium_id' => $healthy->id]);
        $cancelled = $this->issueFine($healthy, $owner, 80);
        $cancelled->update(['status' => 'cancelled']);

        $this->seedUsers($other, 4);

        $this->actingAs($owner)
            ->get(route('organization.dashboard'))
            ->assertOk()
            ->assertSee('Condomínios sob sua responsabilidade', false)
            ->assertSee('Residencial Aurora', false)
            ->assertSee('Edificio Estavel', false)
            ->assertSee('Crítica', false)
            ->assertSee('50,0%', false)
            ->assertSee('250,50', false)
            ->assertSee('Saudável', false)
            ->assertSee('100,0%', false)
            ->assertSee('Nenhuma', false)
            ->assertDontSee('Fora da Carteira', false);
    }

    public function test_health_bands_follow_compliance_thresholds(): void
    {
        $service = app(OrganizationCondominiumInsightsService::class);

        $this->assertSame('empty', $service->classify(0, 0)['key']);
        $this->assertSame('healthy', $service->classify(10, 1)['key']);
        $this->assertSame('attention', $service->classify(10, 2)['key']);
        $this->assertSame('critical', $service->classify(10, 4)['key']);
    }

    private function condominium(Organization $organization, string $name, string $city, string $state): Condominium
    {
        return Condominium::factory()->create([
            'organization_id' => $organization->id,
            'name' => $name,
            'city' => $city,
            'state' => $state,
            'is_active' => true,
        ]);
    }

    private function seedUsers(Condominium $condominium, int $count): void
    {
        User::factory()->count($count)->create([
            'condominium_id' => $condominium->id,
        ]);
    }

    private function issueFine(Condominium $condominium, User $appliedBy, float $amount): Fine
    {
        return Fine::create([
            'condominium_id' => $condominium->id,
            'reference' => 'MULTA-'.$condominium->id.'-'.str_replace('.', '', (string) $amount),
            'motivo' => 'Descumprimento do regimento.',
            'enquadramento' => 'Art. 12',
            'amount' => $amount,
            'due_date' => now()->addDays(10)->toDateString(),
            'applied_at' => now(),
            'applied_by' => $appliedBy->id,
            'status' => 'issued',
        ]);
    }
}
