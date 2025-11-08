<?php

namespace Tests\Feature;

use App\Models\Assembly;
use App\Models\AssemblyItem;
use App\Models\AssemblyVote;
use App\Models\Condominium;
use App\Models\User;
use App\Services\Assembly\AssemblyService;
use App\Services\Assembly\AssemblyVotingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AssemblyWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_sindico_cria_assembleia_com_itens_e_anexos(): void
    {
        Storage::fake('public');

        $condominium = Condominium::factory()->create();

        $this->seedRoles(['Síndico', 'Morador']);
        $this->seedPermission('create_assemblies', 'Síndico');

        $sindico = User::factory()
            ->for($condominium)
            ->create();
        $sindico->assignRole('Síndico');

        Sanctum::actingAs($sindico);

        $scheduledAt = now()->addDays(2)->seconds(0);
        $votingCloses = $scheduledAt->copy()->addDay();

        $payload = [
            'title' => 'Assembleia Geral Extraordinária',
            'description' => 'Deliberação sobre obras e prestação de contas.',
            'urgency' => 'high',
            'scheduled_at' => $scheduledAt->format('Y-m-d\TH:i'),
            'voting_opens_at' => $scheduledAt->format('Y-m-d\TH:i'),
            'voting_closes_at' => $votingCloses->format('Y-m-d\TH:i'),
            'duration_minutes' => 150,
            'voting_type' => 'open',
            'results_visibility' => 'real_time',
            'allow_delegation' => true,
            'allow_comments' => true,
            'allowed_roles' => ['Morador', 'Síndico'],
            'items' => [
                [
                    'title' => 'Prestação de contas 2025',
                    'description' => 'Análise do balancete anual.',
                    'options' => ['aprovar', 'rejeitar', 'abstenção'],
                    'opens_at' => $scheduledAt->format('Y-m-d\TH:i'),
                ],
                [
                    'title' => 'Obra da fachada',
                    'description' => 'Aprovação da contratação da empresa vencedora.',
                    'options' => ['aprovar', 'rejeitar'],
                ],
            ],
            'attachments' => [
                UploadedFile::fake()->create('editais.pdf', 120, 'application/pdf'),
                UploadedFile::fake()->image('fachada.jpg'),
            ],
        ];

        $response = $this->post('/api/assemblies', $payload, ['Accept' => 'application/json']);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'message',
                'assembly' => [
                    'id',
                    'title',
                    'items',
                    'attachments',
                    'allowed_roles',
                ],
            ]);

        $assemblyId = $response->json('assembly.id');

        $this->assertDatabaseHas('assemblies', [
            'id' => $assemblyId,
            'title' => 'Assembleia Geral Extraordinária',
            'urgency' => 'high',
            'allow_delegation' => true,
            'allow_comments' => true,
            'results_visibility' => 'real_time',
        ]);

        $this->assertDatabaseCount('assembly_items', 2);
        $this->assertDatabaseCount('assembly_attachments', 2);

        $paths = collect($response->json('assembly.attachments'))->pluck('path');
        $paths->each(fn ($path) => Storage::disk('public')->assertExists($path));
    }

    public function test_morador_registra_voto_em_item_aberto(): void
    {
        $condominium = Condominium::factory()->create();

        $this->seedRoles(['Síndico', 'Morador']);
        $this->seedPermission('create_assemblies', 'Síndico');

        $sindico = User::factory()->for($condominium)->create();
        $sindico->assignRole('Síndico');

        $morador = User::factory()->for($condominium)->create();
        $morador->assignRole('Morador');

        /** @var AssemblyService $assemblyService */
        $assemblyService = app(AssemblyService::class);

        $assembly = $assemblyService->createAssembly([
            'condominium_id' => $condominium->id,
            'title' => 'Assembleia Ordinária',
            'description' => 'Pauta mensal.',
            'urgency' => 'normal',
            'scheduled_at' => now()->addHour(),
            'voting_opens_at' => now()->subMinutes(5),
            'voting_closes_at' => now()->addHours(2),
            'duration_minutes' => 90,
            'voting_type' => 'open',
            'results_visibility' => 'real_time',
            'allow_comments' => true,
            'allowed_role_ids' => [Role::where('name', 'Morador')->first()->id],
            'items' => [
                [
                    'title' => 'Aprovação das contas',
                    'description' => 'Votação do balancete mensal.',
                    'options' => ['sim', 'não', 'abstenção'],
                    'status' => 'open',
                ],
            ],
        ], $sindico);

        $item = $assembly->items()->first();

        Sanctum::actingAs($morador);

        $response = $this->postJson("/api/assemblies/{$assembly->id}/items/{$item->id}/vote", [
            'choice' => 'sim',
            'comment' => 'Apoiado.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('vote.choice', 'sim');

        $this->assertDatabaseHas('assembly_votes', [
            'assembly_id' => $assembly->id,
            'assembly_item_id' => $item->id,
            'voter_id' => $morador->id,
            'choice' => 'sim',
            'comment' => 'Apoiado.',
        ]);
    }

    public function test_conclusao_de_assembleia_secreta_gera_ata_publica(): void
    {
        $condominium = Condominium::factory()->create();

        $this->seedRoles(['Síndico', 'Morador']);
        $this->seedPermission('create_assemblies', 'Síndico');

        $sindico = User::factory()->for($condominium)->create();
        $sindico->assignRole('Síndico');

        $morador = User::factory()->for($condominium)->create();
        $morador->assignRole('Morador');

        /** @var AssemblyService $assemblyService */
        $assemblyService = app(AssemblyService::class);
        /** @var AssemblyVotingService $votingService */
        $votingService = app(AssemblyVotingService::class);

        $assembly = $assemblyService->createAssembly([
            'condominium_id' => $condominium->id,
            'title' => 'Assembleia Secreta',
            'urgency' => 'critical',
            'scheduled_at' => now()->subHour(),
            'voting_opens_at' => now()->subMinutes(30),
            'voting_closes_at' => now()->addHour(),
            'duration_minutes' => 60,
            'voting_type' => 'secret',
            'results_visibility' => 'final_only',
            'allow_comments' => false,
            'allowed_role_ids' => [Role::where('name', 'Morador')->first()->id],
            'items' => [
                [
                    'title' => 'Votação confidencial',
                    'status' => 'open',
                    'options' => ['sim', 'não'],
                ],
            ],
        ], $sindico);

        $item = $assembly->items()->first();

        $votingService->recordVote(
            $assembly->fresh(),
            $item->fresh(),
            $morador,
            'sim'
        );

        Sanctum::actingAs($sindico);

        $this->postJson("/api/assemblies/{$assembly->id}/complete")
            ->assertOk()
            ->assertJsonPath('assembly.status', 'completed');

        /** @var Assembly $refreshed */
        $refreshed = $assembly->fresh();

        $this->assertNotNull($refreshed->minutes);
        $this->assertStringContainsString('Votante confidencial', $refreshed->minutes);
        $this->assertStringNotContainsString($morador->name, $refreshed->minutes);

        $this->assertDatabaseHas('assembly_status_logs', [
            'assembly_id' => $assembly->id,
            'to_status' => 'completed',
        ]);
    }

    private function seedRoles(array $roles): void
    {
        collect($roles)->each(fn (string $role) => Role::firstOrCreate(['name' => $role]));
    }

    private function seedPermission(string $permission, string $role): void
    {
        $perm = Permission::firstOrCreate(['name' => $permission]);
        Role::where('name', $role)->first()->givePermissionTo($perm);
    }
}

