<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\CondominiumSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PlatformSaasBillingAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_admin_can_open_global_billing_page(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('platform.billing.index'))
            ->assertOk()
            ->assertSee('Cobranças da plataforma', false);
    }

    public function test_sindico_cannot_open_global_billing_page(): void
    {
        $condominium = Condominium::factory()->create();
        Role::firstOrCreate(['name' => 'Síndico', 'guard_name' => 'web']);
        $sindico = User::factory()->create(['condominium_id' => $condominium->id]);
        $sindico->assignRole('Síndico');

        $this->actingAs($sindico)
            ->get(route('platform.billing.index'))
            ->assertForbidden();
    }

    public function test_admin_can_reset_subscription_to_draft(): void
    {
        $admin = $this->makeAdmin();
        $condominium = Condominium::factory()->create();
        $subscription = CondominiumSubscription::query()->create([
            'condominium_id' => $condominium->id,
            'created_by' => $admin->id,
            'billing_metric' => CondominiumSubscription::METRIC_FIXED,
            'fixed_price' => 100,
            'billing_cycle' => CondominiumSubscription::CYCLE_MONTHLY,
            'payment_method' => CondominiumSubscription::PAYMENT_BANK_DEPOSIT,
            'status' => CondominiumSubscription::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('platform.subscriptions.reset-contract', $condominium), [
                'notes' => 'Novo contrato',
            ])
            ->assertRedirect();

        $subscription->refresh();
        $this->assertSame(CondominiumSubscription::STATUS_DRAFT, $subscription->status);
        $this->assertNull($subscription->cancelled_at);
    }

    private function makeAdmin(): User
    {
        Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $admin = User::factory()->create(['condominium_id' => null]);
        $admin->assignRole('Administrador');

        return $admin;
    }
}
