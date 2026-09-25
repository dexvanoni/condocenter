<?php

namespace Tests\Feature;

use App\Mail\ClientWelcomeMail;
use App\Mail\ContractRenewedMail;
use App\Services\SubscriptionAutoRenewService;
use App\Models\Condominium;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\SubscriptionPlan;
use App\Models\Term;
use App\Models\TermVersion;
use App\Models\User;
use App\Services\ActiveCondominiumService;
use App\Services\CondominiumSubscriptionService;
use App\Services\DirectClientOnboardingService;
use App\Services\LgpdConsentService;
use App\Services\OrganizationProvisioningService;
use App\Services\OrganizationSubscriptionService;
use App\Models\PlatformSetting;
use App\Services\PlatformSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MultiTenantOrganizationTest extends TestCase
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

    public function test_condominium_factory_creates_direct_organization(): void
    {
        $condominium = Condominium::factory()->create();

        $this->assertNotNull($condominium->fresh()->organization_id);
        $this->assertTrue($condominium->organization->isDirectCondominium());
    }

    public function test_sindico_cannot_access_other_condominium(): void
    {
        $condoA = Condominium::factory()->create();
        $condoB = Condominium::factory()->create();

        $sindico = User::factory()->create(['condominium_id' => $condoA->id]);
        $sindico->assignRole('Síndico');

        $service = app(ActiveCondominiumService::class);

        $this->assertTrue($service->userCanAccessCondominium($sindico, (int) $condoA->id));
        $this->assertFalse($service->userCanAccessCondominium($sindico, (int) $condoB->id));
        $this->assertSame((int) $condoA->id, $service->getActiveCondominiumId($sindico));
    }

    public function test_management_company_member_cannot_access_other_organization_condo(): void
    {
        $orgA = Organization::factory()->managementCompany()->create();
        $orgB = Organization::factory()->managementCompany()->create();

        $condoA = Condominium::factory()->create(['organization_id' => $orgA->id]);
        $condoB = Condominium::factory()->create(['organization_id' => $orgB->id]);

        $owner = User::factory()->create(['condominium_id' => null]);
        app(OrganizationProvisioningService::class)->attachUser($orgA, (int) $owner->id, Organization::ROLE_OWNER);

        $service = app(ActiveCondominiumService::class);

        $this->assertTrue($service->userCanAccessCondominium($owner, (int) $condoA->id));
        $this->assertFalse($service->userCanAccessCondominium($owner, (int) $condoB->id));
        $this->assertFalse($service->userCanAccessOrganization($owner, (int) $orgB->id));
    }

    public function test_admin_can_access_all_condominiums(): void
    {
        $condoA = Condominium::factory()->create();
        $condoB = Condominium::factory()->create();

        $admin = User::factory()->create(['condominium_id' => null]);
        $admin->assignRole('Administrador');

        $service = app(ActiveCondominiumService::class);
        $this->assertTrue($service->userCanAccessCondominium($admin, (int) $condoA->id));
        $this->assertTrue($service->userCanAccessCondominium($admin, (int) $condoB->id));
    }

    public function test_non_admin_cannot_assign_administrador_or_sindico(): void
    {
        $condo = Condominium::factory()->create();
        $sindico = User::factory()->create(['condominium_id' => $condo->id]);
        $sindico->assignRole('Síndico');

        $policy = app(\App\Policies\UserPolicy::class);

        $this->assertFalse($policy->assignRole($sindico, 'Administrador'));
        $this->assertFalse($policy->assignRole($sindico, 'Síndico'));
        $this->assertTrue($policy->assignRole($sindico, 'Morador'));
    }

    public function test_direct_onboarding_creates_org_condo_and_sindico(): void
    {
        Mail::fake();

        $result = app(DirectClientOnboardingService::class)->onboardDirect([
            'condominium' => [
                'name' => 'Residencial Teste',
                'city' => 'São Paulo',
                'state' => 'SP',
            ],
            'user' => [
                'name' => 'Síndico Teste',
                'email' => 'sindico.teste@example.com',
            ],
        ]);

        $this->assertTrue($result['organization']->isDirectCondominium());
        $this->assertSame($result['organization']->id, $result['condominium']->organization_id);
        $this->assertTrue($result['user']->hasRole('Síndico'));
        $this->assertSame($result['condominium']->id, $result['user']->condominium_id);

        Mail::assertSent(ClientWelcomeMail::class, function (ClientWelcomeMail $mail) {
            $html = $mail->render();

            return $mail->hasTo('sindico.teste@example.com')
                && $mail->audience === ClientWelcomeMail::AUDIENCE_SINDICO
                && str_contains($mail->subject ?? '', 'SindCON')
                && str_contains($html, 'Residencial Teste')
                && str_contains($html, 'Criar minha senha e entrar')
                && ! str_contains($html, 'Reset Password');
        });
    }

    public function test_management_onboarding_creates_org_and_owner(): void
    {
        Mail::fake();

        $plan = SubscriptionPlan::query()->create([
            'name' => 'Plano Admin',
            'slug' => 'plano-admin',
            'audience' => SubscriptionPlan::AUDIENCE_MANAGEMENT_COMPANY,
            'billing_metric' => 'unit',
            'unit_price' => 4.90,
            'fixed_price' => 199,
            'billing_cycle' => 'monthly',
            'payment_method' => 'boleto',
            'is_active' => true,
        ]);

        $result = app(DirectClientOnboardingService::class)->onboardManagement([
            'organization' => [
                'legal_name' => 'Administradora ABC LTDA',
                'trade_name' => 'ABC Condos',
                'document' => '12.345.678/0001-90',
            ],
            'user' => [
                'name' => 'Owner ABC',
                'email' => 'owner.abc@example.com',
            ],
            'subscription_plan_id' => $plan->id,
        ]);

        $this->assertTrue($result['organization']->isManagementCompany());
        $this->assertSame(Organization::ROLE_OWNER, $result['user']->organizationRoleFor((int) $result['organization']->id));
        $this->assertNotNull($result['subscription']);
        $this->assertSame($plan->id, $result['subscription']->subscription_plan_id);

        Mail::assertSent(ClientWelcomeMail::class, function (ClientWelcomeMail $mail) {
            $html = $mail->render();

            return $mail->hasTo('owner.abc@example.com')
                && $mail->audience === ClientWelcomeMail::AUDIENCE_ADMINISTRADORA
                && str_contains($html, 'ABC Condos');
        });
    }

    public function test_admin_can_update_organization_profile(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Administrador');

        $organization = Organization::factory()->managementCompany()->create([
            'legal_name' => 'Administradora Antiga LTDA',
            'trade_name' => 'Antiga',
            'status' => Organization::STATUS_ACTIVE,
        ]);

        $this->actingAs($admin)
            ->get(route('platform.organizations.edit', $organization))
            ->assertOk()
            ->assertSee('Editar organização')
            ->assertSee('Administradora Antiga LTDA');

        $this->actingAs($admin)
            ->put(route('platform.organizations.update', $organization), [
                'legal_name' => 'Administradora Nova LTDA',
                'trade_name' => 'Nova',
                'document' => '11.222.333/0001-44',
                'email' => 'contato@nova.test',
                'phone' => '11999990000',
                'address' => 'Rua das Flores, 10',
                'neighborhood' => 'Centro',
                'city' => 'Campinas',
                'state' => 'sp',
                'zip_code' => '13010-000',
                'notes' => 'Atualizado pelo admin',
            ])
            ->assertRedirect(route('platform.organizations.show', $organization));

        $organization->refresh();
        $this->assertSame('Administradora Nova LTDA', $organization->legal_name);
        $this->assertSame('Nova', $organization->trade_name);
        $this->assertSame('SP', $organization->state);
        $this->assertSame(Organization::STATUS_ACTIVE, $organization->status);
        $this->assertSame(Organization::TYPE_MANAGEMENT_COMPANY, $organization->type);
    }

    public function test_non_admin_cannot_edit_organization(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Morador');
        $organization = Organization::factory()->create();

        $this->actingAs($user)
            ->get(route('platform.organizations.edit', $organization))
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('platform.organizations.update', $organization), [
                'legal_name' => 'Tentativa',
            ])
            ->assertForbidden();
    }

    public function test_platform_organizations_index_requires_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Morador');

        $this->actingAs($user)
            ->get(route('platform.organizations.index'))
            ->assertForbidden();
    }

    public function test_direct_sindico_does_not_see_management_company_panel(): void
    {
        Mail::fake();

        $result = app(DirectClientOnboardingService::class)->onboardDirect([
            'condominium' => [
                'name' => 'Residencial Menu',
                'city' => 'São Paulo',
                'state' => 'SP',
            ],
            'user' => [
                'name' => 'Síndico Menu',
                'email' => 'sindico.menu@example.com',
            ],
        ]);

        $sindico = $result['user'];
        $sindico->forceFill([
            'email_verified_at' => now(),
            'senha_temporaria' => false,
        ])->save();

        $this->assertTrue($sindico->isOrganizationMember());
        $this->assertFalse($sindico->isManagementCompanyMember());

        $this->actingAs($sindico)
            ->get(route('privacy.index'))
            ->assertOk()
            ->assertDontSee('Painel da Administradora', false);

        $this->actingAs($sindico)
            ->get(route('organization.dashboard'))
            ->assertForbidden();
    }

    public function test_management_company_member_sees_management_company_panel(): void
    {
        $org = Organization::factory()->managementCompany()->create();
        $owner = User::factory()->create(['condominium_id' => null]);
        app(OrganizationProvisioningService::class)->attachUser($org, (int) $owner->id, Organization::ROLE_OWNER);

        $this->assertTrue($owner->isManagementCompanyMember());

        $this->actingAs($owner)
            ->get(route('organization.dashboard'))
            ->assertOk()
            ->assertSee('Painel da Administradora', false)
            ->assertDontSee('ALERTA DE PÂNICO', false)
            ->assertDontSee('> PÂNICO', false);

        $this->actingAs($owner)
            ->postJson(route('panic.send'), ['alert_type' => 'fire'])
            ->assertForbidden();
    }

    public function test_management_owner_opens_admin_panel_and_respects_contract_limits(): void
    {
        $organization = Organization::factory()->managementCompany()->create();
        $owner = User::factory()->create(['condominium_id' => null, 'senha_temporaria' => false]);
        app(OrganizationProvisioningService::class)->attachUser($organization, (int) $owner->id, Organization::ROLE_OWNER);

        OrganizationSubscription::query()->create([
            'organization_id' => $organization->id,
            'billing_metric' => 'unit',
            'status' => OrganizationSubscription::STATUS_ACTIVE,
            'max_condominiums' => 1,
            'max_units' => 3,
        ]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertRedirect(route('organization.dashboard'));

        $this->actingAs($owner)
            ->get(route('organization.dashboard'))
            ->assertOk()
            ->assertSee('Contrato SindCON', false);

        OrganizationSubscription::query()->create([
            'organization_id' => $organization->id,
            'billing_metric' => 'unit',
            'status' => OrganizationSubscription::STATUS_ACTIVE,
            'recurring_amount' => 199.90,
            'max_condominiums' => 1,
            'max_units' => 3,
        ]);

        $this->actingAs($owner)
            ->get(route('organization.contract.show'))
            ->assertOk()
            ->assertSee('199,90', false)
            ->assertSee('Cobranças da assinatura', false);

        $payload = [
            'name' => 'Residencial da Administradora',
            'address' => 'Rua Um, 10',
            'city' => 'Campinas',
            'state' => 'SP',
            'zip_code' => '13000-000',
            'financial_mode' => 'full',
            'units_limit' => 3,
            'syndic_name' => 'Síndico do Residencial',
            'syndic_email' => 'sindico.residencial@example.com',
        ];

        $this->actingAs($owner)
            ->post(route('organization.condominiums.store'), $payload)
            ->assertRedirect(route('organization.dashboard'));

        $condominium = Condominium::query()->where('organization_id', $organization->id)->first();
        $this->assertNotNull($condominium);
        $this->assertSame(3, (int) $condominium->units_limit);
        $this->assertSame(Organization::TYPE_MANAGEMENT_COMPANY, $condominium->organization->type);
        $syndic = User::query()->where('email', 'sindico.residencial@example.com')->first();
        $this->assertNotNull($syndic);
        $this->assertSame($condominium->id, $syndic->managedCondominiums()->first()->id);
        $this->assertNull($syndic->condominium_id);
        $this->assertTrue($syndic->hasRole('Síndico'));
        $this->assertNull($owner->condominium_id);
        $this->assertNotSame($owner->id, $syndic->id);

        $second = Condominium::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Condominio Profissional Dois',
        ]);
        $second->syndics()->syncWithoutDetaching([$syndic->id]);
        $syndic->forceFill(['senha_temporaria' => false, 'email_verified_at' => now()])->save();

        $this->actingAs($syndic)
            ->get(route('dashboard'))
            ->assertRedirect(route('syndic.condominiums.index'));

        $this->actingAs($syndic)
            ->get(route('syndic.condominiums.index'))
            ->assertOk()
            ->assertSee('Residencial da Administradora', false)
            ->assertSee('Condominio Profissional Dois', false);

        $this->actingAs($syndic)
            ->post(route('syndic.condominiums.enter'), ['condominium_id' => $second->id])
            ->assertRedirect(route('dashboard'));

        $this->assertSame($second->id, app(\App\Services\ActiveCondominiumService::class)->getActiveCondominiumId($syndic));

        $home = Condominium::factory()->create();
        $resident = User::factory()->create([
            'condominium_id' => $home->id,
            'unit_id' => \App\Models\Unit::factory()->create(['condominium_id' => $home->id])->id,
            'email' => 'morador.sindico@example.com',
            'senha_temporaria' => false,
        ]);
        $resident->assignRole('Morador');
        $withoutSyndic = Condominium::factory()->create(['organization_id' => $organization->id]);

        $this->actingAs($owner)
            ->post(route('organization.condominiums.syndic', $withoutSyndic), [
                'syndic_name' => $resident->name,
                'syndic_email' => $resident->email,
            ])
            ->assertRedirect();

        $resident->refresh();
        $this->assertSame($home->id, $resident->condominium_id);
        $this->assertTrue($resident->managedCondominiums()->whereKey($withoutSyndic->id)->exists());
        $this->assertTrue($resident->hasRole('Síndico'));

        $this->actingAs($owner)
            ->from(route('organization.condominiums.create'))
            ->post(route('organization.condominiums.store'), [
                ...$payload,
                'name' => 'Segundo condomínio',
                'syndic_email' => 'sindico.segundo@example.com',
            ])
            ->assertRedirect(route('organization.condominiums.create'))
            ->assertSessionHas('error');

        $owner->refresh();
        $this->assertTrue($owner->hasRole('Síndico'));
    }

    public function test_management_owner_can_switch_back_to_administradora_profile(): void
    {
        $organization = Organization::factory()->managementCompany()->create();
        $owner = User::factory()->create(['condominium_id' => null, 'senha_temporaria' => false]);
        $owner->assignRole('Síndico');
        app(OrganizationProvisioningService::class)->attachUser($organization, (int) $owner->id, Organization::ROLE_OWNER);
        $condominium = Condominium::factory()->create(['organization_id' => $organization->id]);

        $this->actingAs($owner)
            ->withSession(['active_role' => 'Síndico', 'active_condominium_id' => $condominium->id])
            ->get(route('organization.dashboard'))
            ->assertOk()
            ->assertSee('Administradora', false)
            ->assertSee('Trocar Perfil', false);

        $this->actingAs($owner)
            ->withSession([
                'active_role' => 'Síndico',
                'active_condominium_id' => $condominium->id,
            ])
            ->postJson(route('profile.switch'), ['role' => User::PROFILE_ADMINISTRADORA])
            ->assertOk()
            ->assertJsonPath('role', User::PROFILE_ADMINISTRADORA);

        $this->actingAs($owner)
            ->withSession(['active_role' => User::PROFILE_ADMINISTRADORA])
            ->get(route('organization.dashboard'))
            ->assertOk();

        $this->assertSame(User::PROFILE_ADMINISTRADORA, $owner->getActiveRoleName());
        $this->assertNull(app(\App\Services\ActiveCondominiumService::class)->getActiveCondominiumId($owner));

        $this->actingAs($owner)
            ->withSession(['active_role' => User::PROFILE_ADMINISTRADORA])
            ->postJson(route('profile.switch'), ['role' => 'Síndico'])
            ->assertOk()
            ->assertJsonPath('role', 'Síndico');
    }

    public function test_organization_dashboard_forbidden_for_other_org(): void
    {
        $orgA = Organization::factory()->managementCompany()->create();
        $orgB = Organization::factory()->managementCompany()->create();

        $owner = User::factory()->create();
        app(OrganizationProvisioningService::class)->attachUser($orgA, (int) $owner->id, Organization::ROLE_OWNER);

        $this->actingAs($owner)
            ->get(route('organization.dashboard'))
            ->assertOk();

        $this->actingAs($owner)
            ->post(route('organization.condominiums.enter'), [
                'condominium_id' => Condominium::factory()->create(['organization_id' => $orgB->id])->id,
            ])
            ->assertStatus(403);
    }

    public function test_lgpd_term_versions_are_not_overwritten(): void
    {
        $service = app(LgpdConsentService::class);
        $service->ensureDefaultTerms();

        $term = Term::query()->where('type', Term::TYPE_TERMS_OF_USE)->firstOrFail();
        $v1 = $service->publishVersion($term, '1.0', 'Termos v1', 'Conteúdo um');
        $v2 = $service->publishVersion($term, '2.0', 'Termos v2', 'Conteúdo dois');

        $this->assertFalse($v1->fresh()->is_active);
        $this->assertTrue($v2->fresh()->is_active);
        $this->assertSame(2, $term->versions()->count());
        $this->assertSame('Conteúdo um', $v1->fresh()->content);
    }

    public function test_platform_webhook_updates_organization_subscription(): void
    {
        $org = Organization::factory()->managementCompany()->create();
        $subscription = $org->subscription()->create([
            'status' => 'past_due',
            'asaas_customer_id' => 'cus_org_1',
            'billing_metric' => 'fixed',
            'fixed_price' => 100,
            'recurring_amount' => 100,
            'payment_method' => 'boleto',
        ]);

        $handled = app(CondominiumSubscriptionService::class)->handlePlatformWebhook([
            'event' => 'PAYMENT_RECEIVED',
            'payment' => [
                'customer' => 'cus_org_1',
                'id' => 'pay_1',
            ],
        ]);

        $this->assertTrue($handled);
        $this->assertSame('active', $subscription->fresh()->status);
    }

    public function test_catalog_plans_link_to_the_matching_organization_contract(): void
    {
        $admin = User::factory()->create(['condominium_id' => null]);
        $admin->assignRole('Administrador');

        $sindicoPlan = SubscriptionPlan::query()->create([
            'name' => 'Plano Síndico Catálogo',
            'slug' => 'plano-sindico-catalogo',
            'audience' => SubscriptionPlan::AUDIENCE_CONDOMINIUM,
            'billing_metric' => 'unit',
            'unit_price' => 4.90,
            'billing_cycle' => 'monthly',
            'payment_method' => 'boleto',
            'is_active' => true,
        ]);
        $adminPlan = SubscriptionPlan::query()->create([
            'name' => 'Plano Administradora Catálogo',
            'slug' => 'plano-administradora-catalogo',
            'audience' => SubscriptionPlan::AUDIENCE_MANAGEMENT_COMPANY,
            'billing_metric' => 'unit',
            'unit_price' => 3.50,
            'billing_cycle' => 'monthly',
            'payment_method' => 'boleto',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('platform.plans.index'))
            ->assertOk()
            ->assertSee('Público do plano', false)
            ->assertSee('Plano Síndico Catálogo', false)
            ->assertSee('Administradora', false);

        $management = Organization::factory()->managementCompany()->create();

        $this->actingAs($admin)
            ->get(route('platform.organizations.subscription.edit', $management))
            ->assertOk()
            ->assertSee('Plano Administradora Catálogo', false)
            ->assertDontSee('Plano Síndico Catálogo', false);

        $this->actingAs($admin)
            ->post(route('platform.organizations.subscription.store', $management), [
                'subscription_plan_id' => $adminPlan->id,
                'billing_metric' => 'unit',
                'unit_price' => 3.50,
                'billing_cycle' => 'monthly',
                'payment_method' => 'boleto',
            ])
            ->assertRedirect(route('platform.organizations.subscription.edit', $management));

        $this->assertSame($adminPlan->id, $management->fresh()->subscription->subscription_plan_id);

        Mail::fake();
        $direct = app(DirectClientOnboardingService::class)->onboardDirect([
            'condominium' => ['name' => 'Condo Contrato', 'city' => 'São Paulo', 'state' => 'SP'],
            'user' => ['name' => 'Síndico Contrato', 'email' => 'sindico.contrato@example.com'],
        ]);

        $this->actingAs($admin)
            ->get(route('platform.organizations.show', $direct['organization']))
            ->assertOk()
            ->assertSee('Gerenciar contrato e cobranças', false);

        $this->actingAs($admin)
            ->get(route('platform.organizations.subscription.edit', $direct['organization']))
            ->assertRedirect(route('platform.subscriptions.edit', [
                'condominium' => $direct['condominium'],
                'from_organization' => $direct['organization']->id,
            ]));

        $this->actingAs($admin)
            ->get(route('platform.subscriptions.edit', [
                'condominium' => $direct['condominium'],
                'from_organization' => $direct['organization']->id,
            ]))
            ->assertOk()
            ->assertSee('Plano Síndico Catálogo', false)
            ->assertDontSee('Plano Administradora Catálogo', false)
            ->assertSee('Organização', false);
    }

    public function test_direct_sindico_contract_applies_units_limit(): void
    {
        Mail::fake();

        $plan = SubscriptionPlan::query()->create([
            'name' => 'Plano 40 unidades',
            'slug' => 'plano-40-unidades',
            'audience' => SubscriptionPlan::AUDIENCE_CONDOMINIUM,
            'billing_metric' => 'unit',
            'unit_price' => 2,
            'billing_cycle' => 'monthly',
            'payment_method' => 'boleto',
            'max_units' => 40,
            'is_active' => true,
        ]);

        $result = app(DirectClientOnboardingService::class)->onboardDirect([
            'condominium' => [
                'name' => 'Condo Limite',
                'city' => 'São Paulo',
                'state' => 'SP',
            ],
            'user' => [
                'name' => 'Síndico Limite',
                'email' => 'sindico.limite@example.com',
            ],
            'subscription_plan_id' => $plan->id,
        ]);

        $this->assertSame(40, $result['condominium']->fresh()->units_limit);

        $admin = User::factory()->create(['condominium_id' => null]);
        $admin->assignRole('Administrador');

        $this->actingAs($admin)
            ->get(route('platform.subscriptions.edit', $result['condominium']))
            ->assertOk()
            ->assertSee('Limite de unidades', false)
            ->assertSee('value="40"', false);

        $this->actingAs($admin)
            ->post(route('platform.subscriptions.store', $result['condominium']), [
                'subscription_plan_id' => $plan->id,
                'billing_metric' => 'unit',
                'unit_price' => 2,
                'billing_cycle' => 'monthly',
                'payment_method' => 'boleto',
                'units_limit' => 25,
            ])
            ->assertRedirect(route('platform.subscriptions.edit', $result['condominium']));

        $this->assertSame(25, $result['condominium']->fresh()->units_limit);
    }

    public function test_activate_organization_subscription_explains_asaas_customer_error(): void
    {
        PlatformSetting::setValue(PlatformSettingsService::KEY_ASAAS_API_KEY, 'test-asaas-key', encrypt: true);
        PlatformSetting::setValue(PlatformSettingsService::KEY_ASAAS_SANDBOX, '1');

        $admin = User::factory()->create(['condominium_id' => null]);
        $admin->assignRole('Administrador');

        $organization = Organization::factory()->managementCompany()->create([
            'document' => null,
            'email' => null,
        ]);
        $organization->subscription()->create([
            'status' => 'draft',
            'billing_metric' => 'fixed',
            'fixed_price' => 100,
            'recurring_amount' => 100,
            'billing_cycle' => 'monthly',
            'payment_method' => 'boleto',
        ]);

        $this->actingAs($admin)
            ->post(route('platform.organizations.subscription.activate', $organization))
            ->assertRedirect()
            ->assertSessionHasErrors('asaas');

        $this->assertStringContainsString(
            'CNPJ de faturamento',
            session('errors')->first('asaas')
        );

        $organization->update([
            'document' => '12.345.678/0001-90',
            'email' => 'financeiro@abc.test',
        ]);

        Http::fake([
            'https://sandbox.asaas.com/api/v3/customers' => Http::response([
                'errors' => [
                    ['code' => 'invalid_object', 'description' => 'O CPF/CNPJ informado é inválido.'],
                ],
            ], 400),
        ]);

        $this->actingAs($admin)
            ->from(route('platform.organizations.subscription.edit', $organization))
            ->post(route('platform.organizations.subscription.activate', $organization))
            ->assertRedirect()
            ->assertSessionHasErrors('asaas');

        $this->assertStringContainsString(
            'O CPF/CNPJ informado é inválido.',
            session('errors')->first('asaas')
        );
    }

    public function test_management_company_can_keep_two_contracts(): void
    {
        $admin = User::factory()->create(['condominium_id' => null]);
        $admin->assignRole('Administrador');
        $organization = Organization::factory()->managementCompany()->create();

        $first = app(OrganizationSubscriptionService::class)->upsert($organization, [
            'billing_metric' => 'unit',
            'unit_price' => 1,
            'billing_cycle' => 'monthly',
            'payment_method' => 'bank_deposit',
        ], $admin);

        $second = app(OrganizationSubscriptionService::class)->upsert($organization, [
            'billing_metric' => 'unit',
            'unit_price' => 2,
            'billing_cycle' => 'monthly',
            'payment_method' => 'bank_deposit',
        ], $admin);

        $this->assertNotSame($first->id, $second->id);
        $this->assertCount(2, $organization->subscriptions()->get());

        $this->actingAs($admin)
            ->get(route('platform.organizations.subscription.edit', $organization))
            ->assertOk()
            ->assertSee('Novo contrato', false)
            ->assertSee('Gerenciar', false);
    }

    public function test_auto_renew_extends_the_same_period_and_emails_the_client(): void
    {
        Mail::fake();

        $organization = Organization::factory()->managementCompany()->create([
            'email' => 'financeiro@admin.test',
        ]);
        $subscription = $organization->subscription()->create([
            'status' => 'active',
            'auto_renew' => true,
            'billing_metric' => 'fixed',
            'fixed_price' => 100,
            'recurring_amount' => 100,
            'billing_cycle' => 'monthly',
            'payment_method' => 'boleto',
            'contract_starts_at' => '2026-01-01',
            'contract_ends_at' => '2026-01-31',
            'asaas_subscription_id' => 'sub_keep',
            'financial_contact_email' => 'financeiro@admin.test',
        ]);

        $open = $organization->subscription()->create([
            'status' => 'active',
            'auto_renew' => true,
            'billing_metric' => 'fixed',
            'fixed_price' => 50,
            'recurring_amount' => 50,
            'billing_cycle' => 'monthly',
            'payment_method' => 'boleto',
            'contract_starts_at' => now()->toDateString(),
            'contract_ends_at' => now()->addMonth()->toDateString(),
        ]);

        $count = app(SubscriptionAutoRenewService::class)->renewDue();

        $subscription->refresh();
        $this->assertSame(1, $count);
        $this->assertSame('2026-02-01', $subscription->contract_starts_at->toDateString());
        $this->assertSame('2026-03-03', $subscription->contract_ends_at->toDateString());
        $this->assertSame('sub_keep', $subscription->asaas_subscription_id);
        $this->assertTrue($open->fresh()->contract_ends_at->isFuture());

        Mail::assertSent(ContractRenewedMail::class, function (ContractRenewedMail $mail) {
            return $mail->hasTo('financeiro@admin.test')
                && str_contains($mail->render(), '01/02/2026');
        });
    }

    public function test_admin_can_edit_management_company_user_without_condominium(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['condominium_id' => null]);
        $admin->assignRole('Administrador');
        $organization = Organization::factory()->managementCompany()->create();
        $member = User::factory()->create([
            'condominium_id' => null,
            'name' => 'Dono Antigo',
            'is_active' => true,
        ]);
        app(OrganizationProvisioningService::class)->attachUser($organization, (int) $member->id, Organization::ROLE_OWNER);

        $this->actingAs($admin)
            ->get(route('platform.organizations.show', $organization))
            ->assertOk()
            ->assertSee('Editar', false)
            ->assertSee('Novo usuário', false);

        $this->actingAs($admin)
            ->put(route('platform.organizations.members.update', [$organization, $member]), [
                'name' => 'Dono Novo',
                'email' => $member->email,
                'role' => Organization::ROLE_ADMIN,
                'is_active' => '1',
            ])
            ->assertRedirect(route('platform.organizations.show', $organization));

        $member->refresh();
        $this->assertSame('Dono Novo', $member->name);
        $this->assertNull($member->condominium_id);
        $this->assertTrue($member->is_active);
        $this->assertSame(Organization::ROLE_ADMIN, $member->organizationRoleFor((int) $organization->id));
    }

    public function test_mass_assignment_cannot_set_organization_id_via_user_update_payload_fields(): void
    {
        $user = User::factory()->create();
        $this->assertFalse(in_array('organization_id', $user->getFillable(), true));
        $this->assertFalse(in_array('is_super_admin', $user->getFillable(), true));
    }
}
