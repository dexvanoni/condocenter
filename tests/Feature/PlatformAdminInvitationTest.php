<?php

namespace Tests\Feature;

use App\Mail\PlatformAdminInvitationMail;
use App\Models\Condominium;
use App\Models\User;
use App\Services\PlatformAdminInvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PlatformAdminInvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Síndico', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Morador', 'guard_name' => 'web']);
    }

    public function test_dashboard_lists_platform_admins_and_invite_controls(): void
    {
        $admin = $this->makeAdmin('Ana Admin', 'ana@sindcon.test');
        $other = $this->makeAdmin('Bruno Admin', 'bruno@sindcon.test');

        $this->actingAs($admin)
            ->get(route('platform.dashboard'))
            ->assertOk()
            ->assertSee('Administradores da plataforma', false)
            ->assertSee('Ana Admin', false)
            ->assertSee('Bruno Admin', false)
            ->assertSee('Enviar convite', false)
            ->assertSee('Digite nome, CPF ou e-mail', false);
    }

    public function test_sindico_cannot_open_dashboard_or_search_admins(): void
    {
        $condominium = Condominium::factory()->create(['saas_complimentary' => true]);
        $sindico = User::factory()->create([
            'condominium_id' => $condominium->id,
            'senha_temporaria' => false,
        ]);
        $sindico->assignRole('Síndico');

        $this->actingAs($sindico)
            ->get(route('platform.dashboard'))
            ->assertForbidden();

        $this->actingAs($sindico)
            ->getJson(route('platform.admins.search', ['term' => 'ana']))
            ->assertForbidden();
    }

    public function test_search_finds_users_from_any_condominium_and_excludes_admins(): void
    {
        $admin = $this->makeAdmin();
        $condoA = Condominium::factory()->create(['saas_complimentary' => true]);
        $condoB = Condominium::factory()->create(['saas_complimentary' => true]);

        $morador = User::factory()->create([
            'condominium_id' => $condoA->id,
            'name' => 'Maria Clara',
            'email' => 'maria.clara@example.com',
            'cpf' => '123.456.789-00',
        ]);
        $morador->assignRole('Morador');

        $sindico = User::factory()->create([
            'condominium_id' => $condoB->id,
            'name' => 'Pedro Souza',
            'email' => 'pedro.souza@example.com',
            'cpf' => '987.654.321-00',
        ]);
        $sindico->assignRole('Síndico');

        $this->actingAs($admin)
            ->getJson(route('platform.admins.search', ['term' => 'Maria']))
            ->assertOk()
            ->assertJsonFragment(['email' => 'maria.clara@example.com'])
            ->assertJsonMissing(['email' => $admin->email]);

        $this->actingAs($admin)
            ->getJson(route('platform.admins.search', ['term' => '98765432100']))
            ->assertOk()
            ->assertJsonFragment(['email' => 'pedro.souza@example.com']);

        $this->actingAs($admin)
            ->getJson(route('platform.admins.search', ['term' => 'pedro.souza@example.com']))
            ->assertOk()
            ->assertJsonFragment(['id' => $sindico->id]);
    }

    public function test_invite_by_email_sends_registration_link_and_guest_can_register(): void
    {
        Mail::fake();
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('platform.admins.invite'), [
                'email' => 'novo.admin@sindcon.test',
            ])
            ->assertRedirect();

        $inviteUrl = null;
        Mail::assertSent(PlatformAdminInvitationMail::class, function (PlatformAdminInvitationMail $mail) use (&$inviteUrl) {
            $inviteUrl = $mail->inviteUrl;

            return $mail->hasTo('novo.admin@sindcon.test')
                && $mail->existingUser === null;
        });

        $this->assertNotEmpty($inviteUrl);

        $this->flushSession();
        $this->app['auth']->forgetGuards();

        $this->get($this->relativeUrl($inviteUrl))
            ->assertOk()
            ->assertSee('Conclua o cadastro', false)
            ->assertSee('novo.admin@sindcon.test', false);

        $this->post($this->relativeUrl($inviteUrl), [
            'name' => 'Novo Administrador',
            'password' => 'password1',
            'password_confirmation' => 'password1',
        ])->assertRedirect(route('platform.dashboard'));

        $created = User::query()->where('email', 'novo.admin@sindcon.test')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->hasAssignedRole('Administrador'));
        $this->assertTrue($created->is_active);
        $this->assertAuthenticatedAs($created);
    }

    public function test_invite_existing_user_assigns_admin_role_on_accept_without_removing_other_roles(): void
    {
        Mail::fake();
        $admin = $this->makeAdmin();
        $condominium = Condominium::factory()->create(['saas_complimentary' => true]);
        $morador = User::factory()->create([
            'condominium_id' => $condominium->id,
            'name' => 'Carla Moradora',
            'email' => 'carla@example.com',
            'cpf' => '444.555.666-77',
            'senha_temporaria' => false,
        ]);
        $morador->assignRole('Morador');

        $this->actingAs($admin)
            ->post(route('platform.admins.invite'), [
                'user_id' => $morador->id,
            ])
            ->assertRedirect();

        $inviteUrl = null;
        Mail::assertSent(PlatformAdminInvitationMail::class, function (PlatformAdminInvitationMail $mail) use (&$inviteUrl, $morador) {
            $inviteUrl = $mail->inviteUrl;

            return $mail->hasTo('carla@example.com')
                && $mail->existingUser?->is($morador);
        });

        $this->flushSession();
        $this->app['auth']->forgetGuards();

        $this->post($this->relativeUrl($inviteUrl))
            ->assertRedirect(route('login'));

        $morador->refresh();
        $this->assertTrue($morador->hasAssignedRole('Administrador'));
        $this->assertTrue($morador->hasAssignedRole('Morador'));
    }

    public function test_existing_user_can_accept_invite_while_another_admin_is_logged_in(): void
    {
        Mail::fake();
        $admin = $this->makeAdmin();
        $condominium = Condominium::factory()->create(['saas_complimentary' => true]);
        $morador = User::factory()->create([
            'condominium_id' => $condominium->id,
            'name' => 'Diego Morador',
            'email' => 'diego@example.com',
            'senha_temporaria' => false,
        ]);
        $morador->assignRole('Morador');

        $this->actingAs($admin)
            ->post(route('platform.admins.invite'), [
                'user_id' => $morador->id,
            ])
            ->assertRedirect();

        $inviteUrl = null;
        Mail::assertSent(PlatformAdminInvitationMail::class, function (PlatformAdminInvitationMail $mail) use (&$inviteUrl) {
            $inviteUrl = $mail->inviteUrl;

            return $mail->hasTo('diego@example.com');
        });

        $this->actingAs($admin)
            ->post($this->relativeUrl($inviteUrl))
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $morador->refresh();
        $this->assertTrue($morador->hasAssignedRole('Administrador'));
        $this->assertTrue($morador->hasAssignedRole('Morador'));
    }

    public function test_cannot_invite_user_who_is_already_platform_admin(): void
    {
        Mail::fake();
        $admin = $this->makeAdmin();
        $other = $this->makeAdmin('Outro', 'outro@sindcon.test');

        $this->actingAs($admin)
            ->from(route('platform.dashboard'))
            ->post(route('platform.admins.invite'), [
                'user_id' => $other->id,
            ])
            ->assertRedirect(route('platform.dashboard'))
            ->assertSessionHasErrors('user_id');

        Mail::assertNothingSent();
    }

    public function test_unsigned_invite_url_is_rejected(): void
    {
        $this->get(route('platform.admins.invite.show', [
            'email' => 'x@y.com',
            'user' => 0,
        ]))->assertForbidden();
    }

    public function test_invite_expire_days_constant_matches_mail(): void
    {
        $this->assertSame(7, PlatformAdminInvitationService::EXPIRE_DAYS);
    }

    private function makeAdmin(string $name = 'Admin Plataforma', string $email = 'admin@sindcon.test'): User
    {
        $admin = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'condominium_id' => null,
            'senha_temporaria' => false,
        ]);
        $admin->assignRole('Administrador');

        return $admin;
    }

    private function relativeUrl(string $absolute): string
    {
        $parts = parse_url($absolute);

        return ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '');
    }
}
