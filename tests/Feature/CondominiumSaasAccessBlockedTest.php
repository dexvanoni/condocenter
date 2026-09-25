<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\CondominiumSubscription;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CondominiumSaasAccessBlockedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'Síndico', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Morador', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Porteiro', 'guard_name' => 'web']);
    }

    public function test_user_without_active_contract_is_redirected_to_blocked_page(): void
    {
        $condominium = Condominium::factory()->create();
        $syndic = User::factory()->create([
            'condominium_id' => $condominium->id,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $syndic->assignRole('Síndico');

        $this->actingAs($syndic)
            ->get(route('dashboard'))
            ->assertRedirect(route('saas.access-blocked'));

        $this->actingAs($syndic)
            ->get(route('saas.access-blocked'))
            ->assertOk()
            ->assertSee('Administração do SindCON', false)
            ->assertSee($condominium->name, false)
            ->assertSee('Nenhum contrato de assinatura', false);
    }

    public function test_morador_and_porteiro_are_blocked_when_contract_is_cancelled(): void
    {
        $condominium = Condominium::factory()->create();
        $unit = Unit::factory()->create(['condominium_id' => $condominium->id]);

        CondominiumSubscription::query()->create([
            'condominium_id' => $condominium->id,
            'billing_metric' => CondominiumSubscription::METRIC_FIXED,
            'fixed_price' => 249,
            'billing_cycle' => CondominiumSubscription::CYCLE_MONTHLY,
            'payment_method' => CondominiumSubscription::PAYMENT_BANK_DEPOSIT,
            'status' => CondominiumSubscription::STATUS_CANCELLED,
        ]);

        $morador = User::factory()->create([
            'condominium_id' => $condominium->id,
            'unit_id' => $unit->id,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $morador->assignRole('Morador');

        $porteiro = User::factory()->create([
            'condominium_id' => $condominium->id,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $porteiro->assignRole('Porteiro');

        $this->actingAs($morador)->get(route('dashboard'))->assertRedirect(route('saas.access-blocked'));
        $this->actingAs($porteiro)->get(route('dashboard'))->assertRedirect(route('saas.access-blocked'));
    }

    public function test_complimentary_condominium_allows_access_without_active_contract(): void
    {
        $condominium = Condominium::factory()->create([
            'saas_complimentary' => true,
        ]);
        $syndic = User::factory()->create([
            'condominium_id' => $condominium->id,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $syndic->assignRole('Síndico');

        CondominiumSubscription::query()->create([
            'condominium_id' => $condominium->id,
            'billing_metric' => CondominiumSubscription::METRIC_FIXED,
            'fixed_price' => 599,
            'billing_cycle' => CondominiumSubscription::CYCLE_MONTHLY,
            'payment_method' => CondominiumSubscription::PAYMENT_PIX_RECURRING,
            'status' => CondominiumSubscription::STATUS_DRAFT,
        ]);

        $this->actingAs($syndic)
            ->get(route('dashboard'))
            ->assertOk();

        $this->actingAs($syndic)
            ->get(route('saas.access-blocked'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_syndic_sees_complimentary_message_on_my_subscription_page(): void
    {
        $condominium = Condominium::factory()->create([
            'saas_complimentary' => true,
            'saas_complimentary_notes' => 'Parceria piloto',
        ]);
        $syndic = User::factory()->create([
            'condominium_id' => $condominium->id,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $syndic->assignRole('Síndico');

        CondominiumSubscription::query()->create([
            'condominium_id' => $condominium->id,
            'billing_metric' => CondominiumSubscription::METRIC_FIXED,
            'fixed_price' => 599,
            'billing_cycle' => CondominiumSubscription::CYCLE_MONTHLY,
            'payment_method' => CondominiumSubscription::PAYMENT_PIX_RECURRING,
            'status' => CondominiumSubscription::STATUS_DRAFT,
        ]);

        $this->actingAs($syndic)
            ->get(route('syndic-subscription.show'))
            ->assertOk()
            ->assertSee('Uso gratuito da plataforma', false)
            ->assertSee('Plataforma gratuita', false)
            ->assertSee('Parceria piloto', false)
            ->assertDontSee('Forma de pagamento', false)
            ->assertDontSee('Cobranças da assinatura', false);
    }

    public function test_active_contract_allows_dashboard_access(): void
    {
        $condominium = Condominium::factory()->create();
        $syndic = User::factory()->create([
            'condominium_id' => $condominium->id,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $syndic->assignRole('Síndico');

        CondominiumSubscription::query()->create([
            'condominium_id' => $condominium->id,
            'billing_metric' => CondominiumSubscription::METRIC_FIXED,
            'fixed_price' => 249,
            'billing_cycle' => CondominiumSubscription::CYCLE_MONTHLY,
            'payment_method' => CondominiumSubscription::PAYMENT_BANK_DEPOSIT,
            'status' => CondominiumSubscription::STATUS_ACTIVE,
        ]);

        $this->actingAs($syndic)
            ->get(route('dashboard'))
            ->assertOk();
    }
}
