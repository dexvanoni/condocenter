<?php

namespace Tests\Feature;

use App\Helpers\QRCodeHelper;
use App\Jobs\SendAccessNotification;
use App\Models\AccessAuthorization;
use App\Models\Condominium;
use App\Models\Unit;
use App\Models\User;
use App\Support\CondominiumModules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class VisitorAccessCredentialTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_named_visitor_authorization_requires_valid_until_and_generates_credentials(): void
    {
        [$morador, $unit] = $this->createMoradorWithUnit();

        Sanctum::actingAs($morador);

        $scheduledAt = now()->addHour();
        $validUntil = now()->addHours(4);

        $response = $this->postJson('/api/access-control/authorizations', [
            'visitor_name' => 'João Visitante',
            'visitor_preset_key' => AccessAuthorization::PRESET_OTHER,
            'authorization_type' => 'allow',
            'scheduled_at' => $scheduledAt->toDateTimeString(),
            'valid_until' => $validUntil->toDateTimeString(),
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('has_digital_pass', true)
            ->assertJsonPath('authorization.visitor_preset_key', AccessAuthorization::PRESET_OTHER);

        $authorization = AccessAuthorization::first();
        $this->assertNotNull($authorization->access_pin_hash);
        $this->assertNotNull($authorization->qr_token);
        $this->assertTrue($authorization->hasDigitalPass());

        $this->assertDatabaseHas('notifications', [
            'user_id' => $morador->id,
            'type' => 'access_visitor_credential',
        ]);
    }

    public function test_named_visitor_authorization_fails_without_valid_until(): void
    {
        [$morador] = $this->createMoradorWithUnit();

        Sanctum::actingAs($morador);

        $this->postJson('/api/access-control/authorizations', [
            'visitor_name' => 'João Visitante',
            'visitor_preset_key' => AccessAuthorization::PRESET_OTHER,
            'authorization_type' => 'allow',
            'scheduled_at' => now()->addHour()->toDateTimeString(),
        ])->assertStatus(422);
    }

    public function test_porteiro_can_check_in_by_pin_and_keep_credentials_active(): void
    {
        Queue::fake();

        [$morador, $unit] = $this->createMoradorWithUnit();
        $porteiro = $this->createPorteiro($morador->condominium_id);
        $plainPin = '4821';

        $authorization = AccessAuthorization::create([
            'condominium_id' => $morador->condominium_id,
            'unit_id' => $unit->id,
            'authorized_by' => $morador->id,
            'notify_user_id' => $morador->id,
            'visitor_name' => 'Maria Visitante',
            'visitor_preset_key' => AccessAuthorization::PRESET_OTHER,
            'authorization_type' => AccessAuthorization::TYPE_ALLOW,
            'scheduled_at' => now()->subMinutes(5),
            'valid_until' => now()->addHours(2),
            'expires_at' => now()->addHours(2),
            'status' => AccessAuthorization::STATUS_PENDING,
            'access_pin_hash' => Hash::make($plainPin),
            'qr_token' => 'test-qr-token-abc123',
        ]);

        Sanctum::actingAs($porteiro);

        $response = $this->postJson('/api/access-control/check-in/pin', [
            'access_pin' => $plainPin,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('authorization.status', AccessAuthorization::STATUS_PENDING)
            ->assertJsonPath('authorization.visitor_name', 'Maria Visitante');

        $this->assertDatabaseHas('access_authorizations', [
            'id' => $authorization->id,
            'status' => AccessAuthorization::STATUS_PENDING,
            'processed_by' => $porteiro->id,
        ]);

        Queue::assertPushed(SendAccessNotification::class);
    }

    public function test_credentials_allow_multiple_check_ins_until_expiry(): void
    {
        Queue::fake();

        [$morador, $unit] = $this->createMoradorWithUnit();
        $porteiro = $this->createPorteiro($morador->condominium_id);
        $plainPin = '4821';

        AccessAuthorization::create([
            'condominium_id' => $morador->condominium_id,
            'unit_id' => $unit->id,
            'authorized_by' => $morador->id,
            'notify_user_id' => $morador->id,
            'visitor_name' => 'Visitante Recorrente',
            'visitor_preset_key' => AccessAuthorization::PRESET_OTHER,
            'authorization_type' => AccessAuthorization::TYPE_ALLOW,
            'scheduled_at' => now()->subMinutes(5),
            'valid_until' => now()->addHours(2),
            'expires_at' => now()->addHours(2),
            'status' => AccessAuthorization::STATUS_PENDING,
            'access_pin_hash' => Hash::make($plainPin),
            'qr_token' => 'multi-entry-token',
        ]);

        Sanctum::actingAs($porteiro);

        $this->postJson('/api/access-control/check-in/pin', ['access_pin' => $plainPin])->assertOk();
        $this->postJson('/api/access-control/check-in/pin', ['access_pin' => $plainPin])->assertOk();

        $this->assertDatabaseCount('access_movements', 2);
        $this->assertDatabaseHas('access_authorizations', [
            'visitor_name' => 'Visitante Recorrente',
            'status' => AccessAuthorization::STATUS_PENDING,
        ]);

        Queue::assertPushed(SendAccessNotification::class, 2);
    }

    public function test_porteiro_can_check_in_by_qr_code(): void
    {
        Queue::fake();

        [$morador, $unit] = $this->createMoradorWithUnit();
        $porteiro = $this->createPorteiro($morador->condominium_id);
        $token = 'visitor-token-xyz789';

        AccessAuthorization::create([
            'condominium_id' => $morador->condominium_id,
            'unit_id' => $unit->id,
            'authorized_by' => $morador->id,
            'notify_user_id' => $morador->id,
            'visitor_name' => 'Carlos Visitante',
            'visitor_preset_key' => AccessAuthorization::PRESET_OTHER,
            'authorization_type' => AccessAuthorization::TYPE_ALLOW,
            'scheduled_at' => now()->subMinutes(5),
            'valid_until' => now()->addHours(2),
            'expires_at' => now()->addHours(2),
            'status' => AccessAuthorization::STATUS_PENDING,
            'access_pin_hash' => Hash::make('1234'),
            'qr_token' => $token,
        ]);

        Sanctum::actingAs($porteiro);

        $qrData = json_encode([
            'type' => 'visitor_access',
            'token' => $token,
        ]);

        $this->postJson('/api/access-control/check-in/qr', [
            'qr_data' => $qrData,
        ])
            ->assertOk()
            ->assertJsonPath('authorization.visitor_name', 'Carlos Visitante')
            ->assertJsonPath('authorization.status', AccessAuthorization::STATUS_PENDING);

        Queue::assertPushed(SendAccessNotification::class);
    }

    public function test_expired_named_visitor_cannot_check_in(): void
    {
        [$morador, $unit] = $this->createMoradorWithUnit();
        $porteiro = $this->createPorteiro($morador->condominium_id);
        $plainPin = '7391';

        AccessAuthorization::create([
            'condominium_id' => $morador->condominium_id,
            'unit_id' => $unit->id,
            'authorized_by' => $morador->id,
            'notify_user_id' => $morador->id,
            'visitor_name' => 'Visitante Expirado',
            'visitor_preset_key' => AccessAuthorization::PRESET_OTHER,
            'authorization_type' => AccessAuthorization::TYPE_ALLOW,
            'scheduled_at' => now()->subHours(3),
            'valid_until' => now()->subHour(),
            'expires_at' => now()->subHour(),
            'status' => AccessAuthorization::STATUS_PENDING,
            'access_pin_hash' => Hash::make($plainPin),
            'qr_token' => 'expired-token',
        ]);

        Sanctum::actingAs($porteiro);

        $this->postJson('/api/access-control/check-in/pin', [
            'access_pin' => $plainPin,
        ])->assertStatus(422);
    }

    public function test_digital_pass_authorizations_appear_in_porteiro_panel(): void
    {
        [$morador, $unit] = $this->createMoradorWithUnit();
        $porteiro = $this->createPorteiro($morador->condominium_id);

        AccessAuthorization::create([
            'condominium_id' => $morador->condominium_id,
            'unit_id' => $unit->id,
            'authorized_by' => $morador->id,
            'notify_user_id' => $morador->id,
            'visitor_name' => 'Visitante Painel',
            'visitor_preset_key' => AccessAuthorization::PRESET_OTHER,
            'authorization_type' => AccessAuthorization::TYPE_ALLOW,
            'scheduled_at' => now()->addHour(),
            'valid_until' => now()->addHours(3),
            'expires_at' => now()->addHours(3),
            'status' => AccessAuthorization::STATUS_PENDING,
            'access_pin_hash' => Hash::make('5678'),
            'qr_token' => 'panel-token',
        ]);

        Sanctum::actingAs($porteiro);

        $this->getJson('/api/access-control/porteiro/panel')
            ->assertOk()
            ->assertJsonPath('authorizations.0.visitor_name', 'Visitante Painel')
            ->assertJsonPath('authorizations.0.has_digital_pass', true);
    }

    public function test_morador_can_download_pdf_for_active_named_visitor(): void
    {
        [$morador, $unit] = $this->createMoradorWithUnit();

        $authorization = AccessAuthorization::create([
            'condominium_id' => $morador->condominium_id,
            'unit_id' => $unit->id,
            'authorized_by' => $morador->id,
            'notify_user_id' => $morador->id,
            'visitor_name' => 'PDF Visitante',
            'visitor_preset_key' => AccessAuthorization::PRESET_OTHER,
            'authorization_type' => AccessAuthorization::TYPE_ALLOW,
            'scheduled_at' => now()->addHour(),
            'valid_until' => now()->addHours(3),
            'expires_at' => now()->addHours(3),
            'status' => AccessAuthorization::STATUS_PENDING,
            'access_pin_hash' => Hash::make('5678'),
            'qr_token' => 'download-token',
        ]);

        Sanctum::actingAs($morador);

        $response = $this->get("/api/access-control/authorizations/{$authorization->id}/pdf");

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertGreaterThan(5000, strlen($response->getContent()));
    }

    public function test_png_qr_generation_produces_binary_image(): void
    {
        [$morador, $unit] = $this->createMoradorWithUnit();

        $authorization = AccessAuthorization::make([
            'qr_token' => 'png-test-token',
            'visitor_name' => 'Teste PNG',
        ]);

        $png = QRCodeHelper::generateForVisitorAccessPngBinary($authorization);
        $this->assertStringStartsWith("\x89PNG", $png);
    }

    public function test_qr_helper_parses_visitor_access_payload(): void
    {
        $token = QRCodeHelper::parseVisitorAccessToken(json_encode([
            'type' => 'visitor_access',
            'token' => 'abc123',
        ]));

        $this->assertSame('abc123', $token);
    }

    protected function createMoradorWithUnit(): array
    {
        $condominium = Condominium::factory()->create([
            'enabled_modules' => CondominiumModules::keys(),
        ]);

        $unit = Unit::factory()->create([
            'condominium_id' => $condominium->id,
            'block' => 'A',
            'number' => '101',
        ]);

        $permission = Permission::firstOrCreate([
            'name' => 'create_access_authorizations',
            'guard_name' => 'web',
        ]);

        $role = Role::firstOrCreate(['name' => 'Morador', 'guard_name' => 'web']);
        $role->syncPermissions([$permission]);

        $morador = User::factory()->create([
            'condominium_id' => $condominium->id,
            'unit_id' => $unit->id,
        ]);
        $morador->assignRole($role);

        return [$morador, $unit];
    }

    protected function createPorteiro(int $condominiumId): User
    {
        $permission = Permission::firstOrCreate([
            'name' => 'process_access',
            'guard_name' => 'web',
        ]);

        $role = Role::firstOrCreate(['name' => 'Porteiro', 'guard_name' => 'web']);
        $role->syncPermissions([$permission]);

        $porteiro = User::factory()->create([
            'condominium_id' => $condominiumId,
            'unit_id' => null,
        ]);
        $porteiro->assignRole($role);

        return $porteiro;
    }
}
