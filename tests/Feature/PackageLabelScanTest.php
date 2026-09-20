<?php

namespace Tests\Feature;

use App\Contracts\OcrServiceInterface;
use App\Jobs\SendPackageNotification;
use App\Models\Condominium;
use App\Models\Package;
use App\Models\Unit;
use App\Models\User;
use App\Services\Ocr\FakeOcrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PackageLabelScanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Storage::fake('public');
    }

    public function test_porteiro_can_preview_label_and_match_resident(): void
    {
        [$porteiro, $unit] = $this->createPorteiroWithUnit('B', '203');
        $resident = $this->createResidentForUnit($unit, 'João da Silva');

        $this->app->instance(OcrServiceInterface::class, new FakeOcrService(
            "JOAO DA SILVA\nBLOCO B\nAP 203\nMERCADO LIVRE",
            0.95
        ));

        Sanctum::actingAs($porteiro);

        $response = $this->post('/api/packages/label/preview', [
            'image' => UploadedFile::fake()->image('label.jpg', 800, 600),
        ], ['Accept' => 'application/json']);

        $response->assertOk()
            ->assertJsonPath('match.level', 'high')
            ->assertJsonPath('match.candidates.0.resident_id', $resident->id)
            ->assertJsonPath('sender', 'MERCADO LIVRE');

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $porteiro->id,
            'action' => 'package_label_preview',
            'module' => 'packages',
        ]);
    }

    public function test_porteiro_can_match_label_text_from_client_ocr(): void
    {
        [$porteiro, $unit] = $this->createPorteiroWithUnit('B', '203');
        $resident = $this->createResidentForUnit($unit, 'João da Silva');

        Sanctum::actingAs($porteiro);

        $this->postJson('/api/packages/label/match-text', [
            'ocr_text' => "DESTINATARIO\nJOAO DA SILVA\nBLOCO B AP 203",
            'ocr_confidence' => 0.91,
            'ocr_engine' => 'paddle-js-v6',
        ])
            ->assertOk()
            ->assertJsonPath('match.level', 'high')
            ->assertJsonPath('match.candidates.0.resident_id', $resident->id)
            ->assertJsonPath('ocr_engine', 'paddle-js-v6');
    }

    public function test_confirm_label_registers_package_and_dispatches_notification(): void
    {
        Queue::fake();

        [$porteiro, $unit] = $this->createPorteiroWithUnit('B', '203');
        $resident = $this->createResidentForUnit($unit, 'João da Silva');

        Sanctum::actingAs($porteiro);

        $response = $this->postJson('/api/packages/label/confirm', [
            'unit_id' => $unit->id,
            'resident_id' => $resident->id,
            'type' => Package::TYPE_LEVE,
            'sender' => 'MERCADO LIVRE',
            'tracking_code' => 'AB123456789BR',
            'identification_method' => Package::METHOD_OCR,
            'identification_confidence' => 0.97,
            'ocr_text' => 'JOAO DA SILVA BLOCO B AP 203',
            'ocr_confidence' => 0.9,
        ]);

        $response->assertCreated()
            ->assertJsonPath('package.unit_id', $unit->id)
            ->assertJsonPath('package.sender', 'MERCADO LIVRE')
            ->assertJsonPath('whatsapp_status', Package::WHATSAPP_PENDING);

        $this->assertDatabaseHas('packages', [
            'unit_id' => $unit->id,
            'identified_resident_id' => $resident->id,
            'identification_method' => Package::METHOD_OCR,
            'tracking_code' => 'AB123456789BR',
        ]);

        $package = Package::first();
        $this->assertNotEmpty($package->pickup_code_hash);

        Queue::assertPushed(SendPackageNotification::class, function (SendPackageNotification $job) {
            return $job->type === 'arrived' && !empty($job->pickupCode);
        });

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $porteiro->id,
            'action' => 'package_registered_label',
            'module' => 'packages',
        ]);
    }

    public function test_preview_respects_condominium_scope(): void
    {
        [$porteiro, $unit] = $this->createPorteiroWithUnit('A', '101');
        $this->createResidentForUnit($unit, 'Carlos Lima');

        $otherCondo = Condominium::factory()->create();
        $otherUnit = Unit::factory()->create([
            'condominium_id' => $otherCondo->id,
            'block' => 'A',
            'number' => '101',
        ]);
        $this->createResidentForUnit($otherUnit, 'Carlos Lima');

        $this->app->instance(OcrServiceInterface::class, new FakeOcrService(
            "CARLOS LIMA\nBLOCO A\nAP 101",
            0.95
        ));

        Sanctum::actingAs($porteiro);

        $response = $this->post('/api/packages/label/preview', [
            'image' => UploadedFile::fake()->image('label.jpg'),
        ], ['Accept' => 'application/json']);

        $response->assertOk();
        $candidates = $response->json('match.candidates');
        foreach ($candidates as $candidate) {
            $this->assertSame($unit->id, $candidate['unit_id']);
        }
    }

    public function test_user_without_permission_cannot_preview(): void
    {
        $condominium = Condominium::factory()->create();
        $user = User::factory()->create(['condominium_id' => $condominium->id]);

        Sanctum::actingAs($user);

        $this->post('/api/packages/label/preview', [
            'image' => UploadedFile::fake()->image('label.jpg'),
        ], ['Accept' => 'application/json'])
            ->assertForbidden();
    }

    public function test_collect_requires_correct_pickup_code(): void
    {
        Queue::fake();

        [$porteiro, $unit] = $this->createPorteiroWithUnit();
        $this->createResidentForUnit($unit);

        $package = Package::create([
            'condominium_id' => $porteiro->condominium_id,
            'unit_id' => $unit->id,
            'registered_by' => $porteiro->id,
            'type' => Package::TYPE_LEVE,
            'status' => Package::STATUS_PENDING,
            'received_at' => now(),
            'notification_sent' => false,
            'pickup_code_hash' => Hash::make('4821'),
            'identification_method' => Package::METHOD_MANUAL,
        ]);

        Sanctum::actingAs($porteiro);

        $this->postJson("/api/packages/{$package->id}/collect", [
            'pickup_code' => '0001',
        ])->assertStatus(422);

        $this->postJson("/api/packages/{$package->id}/collect", [
            'pickup_code' => '4821',
        ])->assertOk()
            ->assertJsonPath('package.status', Package::STATUS_COLLECTED);
    }

    public function test_porteiro_finds_pending_package_by_pickup_code_and_records_pickup_name(): void
    {
        Queue::fake();

        [$porteiro, $unit] = $this->createPorteiroWithUnit();
        $resident = $this->createResidentForUnit($unit, 'Tayna Fernandes');

        $package = Package::create([
            'condominium_id' => $porteiro->condominium_id,
            'unit_id' => $unit->id,
            'registered_by' => $porteiro->id,
            'type' => Package::TYPE_LEVE,
            'status' => Package::STATUS_PENDING,
            'received_at' => now(),
            'notification_sent' => true,
            'pickup_code_hash' => Hash::make('4821'),
            'identified_resident_id' => $resident->id,
        ]);

        Sanctum::actingAs($porteiro);

        $this->postJson('/api/packages/pickup/find', [
            'pickup_code' => '4821',
        ])->assertOk()
            ->assertJsonPath('package.id', $package->id)
            ->assertJsonPath('package.residents.0.name', 'Tayna Fernandes');

        $this->postJson("/api/packages/{$package->id}/collect", [
            'pickup_code' => '4821',
            'picked_up_by_name' => 'Carlos Fernandes',
        ])->assertOk();

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => Package::STATUS_COLLECTED,
            'collected_by' => $porteiro->id,
            'picked_up_by_name' => 'Carlos Fernandes',
        ]);

        $this->assertNotNull($package->fresh()->pickup_verified_at);
        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $porteiro->id,
            'action' => 'package_collected',
            'module' => 'packages',
        ]);
    }

    public function test_pickup_code_search_never_returns_package_from_other_condominium(): void
    {
        [$porteiro] = $this->createPorteiroWithUnit();
        $otherCondominium = Condominium::factory()->create();
        $otherUnit = Unit::factory()->create(['condominium_id' => $otherCondominium->id]);
        $otherUser = User::factory()->create(['condominium_id' => $otherCondominium->id]);

        Package::create([
            'condominium_id' => $otherCondominium->id,
            'unit_id' => $otherUnit->id,
            'registered_by' => $otherUser->id,
            'type' => Package::TYPE_LEVE,
            'status' => Package::STATUS_PENDING,
            'received_at' => now(),
            'pickup_code_hash' => Hash::make('4821'),
        ]);

        Sanctum::actingAs($porteiro);

        $this->postJson('/api/packages/pickup/find', [
            'pickup_code' => '4821',
        ])->assertUnprocessable();
    }

    public function test_legacy_package_without_pickup_code_can_be_collected(): void
    {
        Queue::fake();

        [$porteiro, $unit] = $this->createPorteiroWithUnit();

        $package = Package::create([
            'condominium_id' => $porteiro->condominium_id,
            'unit_id' => $unit->id,
            'registered_by' => $porteiro->id,
            'type' => Package::TYPE_PESADO,
            'status' => Package::STATUS_PENDING,
            'received_at' => now(),
            'notification_sent' => false,
            'pickup_code_hash' => null,
        ]);

        Sanctum::actingAs($porteiro);

        $this->postJson("/api/packages/{$package->id}/collect")
            ->assertOk()
            ->assertJsonPath('package.status', Package::STATUS_COLLECTED);
    }

    public function test_notification_job_failure_does_not_remove_package(): void
    {
        Queue::fake();

        [$porteiro, $unit] = $this->createPorteiroWithUnit();
        $this->createResidentForUnit($unit);

        Sanctum::actingAs($porteiro);

        $this->postJson('/api/packages', [
            'unit_id' => $unit->id,
            'type' => Package::TYPE_LEVE,
        ])->assertCreated();

        $this->assertDatabaseCount('packages', 1);

        // Simula falha do job sem apagar a encomenda
        $package = Package::first();
        $job = new SendPackageNotification($package, 'arrived', '1234');
        $job->failed(new \RuntimeException('Evolution API down'));

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'whatsapp_delivery_status' => Package::WHATSAPP_FAILED,
        ]);
    }

    public function test_manual_registration_still_works_with_pickup_code(): void
    {
        Queue::fake();

        [$porteiro, $unit] = $this->createPorteiroWithUnit();
        $this->createResidentForUnit($unit);

        Sanctum::actingAs($porteiro);

        $response = $this->postJson('/api/packages', [
            'unit_id' => $unit->id,
            'type' => Package::TYPE_FRAGIL,
            'sender' => 'Amazon',
        ]);

        $response->assertCreated();

        $package = Package::first();
        $this->assertNotNull($package->pickup_code_hash);
        $this->assertSame(Package::METHOD_MANUAL, $package->identification_method);
        $this->assertSame('Amazon', $package->sender);
    }

    public function test_preview_identifies_resident_when_name_shares_line_with_unit(): void
    {
        [$porteiro, $unit] = $this->createPorteiroWithUnit('B', '203');
        $resident = $this->createResidentForUnit($unit, 'João da Silva');

        $this->app->instance(OcrServiceInterface::class, new FakeOcrService(
            "MERCADO LIVRE\nJOAO DA SILVA AP 203 BLOCO B\nCEP 65000000",
            0.72
        ));

        Sanctum::actingAs($porteiro);

        $this->post('/api/packages/label/preview', [
            'image' => UploadedFile::fake()->image('label.jpg', 800, 600),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('match.level', 'high')
            ->assertJsonPath('match.candidates.0.resident_id', $resident->id);
    }

    public function test_intake_page_is_accessible_for_porteiro(): void
    {
        $this->withoutVite();

        [$porteiro] = $this->createPorteiroWithUnit();

        $this->actingAs($porteiro)
            ->get(route('packages.register'))
            ->assertOk();
    }

    protected function createPorteiroWithUnit(string $block = 'A', string $number = '101'): array
    {
        $condominium = Condominium::factory()->create();
        $unit = Unit::factory()->create([
            'condominium_id' => $condominium->id,
            'block' => $block,
            'number' => $number,
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

    protected function createResidentForUnit(Unit $unit, string $name = 'Morador Teste'): User
    {
        $viewPackages = Permission::firstOrCreate(
            ['name' => 'view_packages', 'guard_name' => 'web']
        );

        $moradorRole = Role::firstOrCreate(['name' => 'Morador', 'guard_name' => 'web']);
        if (!$moradorRole->hasPermissionTo($viewPackages)) {
            $moradorRole->givePermissionTo($viewPackages);
        }

        $resident = User::factory()->create([
            'name' => $name,
            'condominium_id' => $unit->condominium_id,
            'unit_id' => $unit->id,
        ]);

        $resident->assignRole($moradorRole);

        return $resident;
    }
}
