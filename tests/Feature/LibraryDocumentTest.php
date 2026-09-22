<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\CondominiumLibraryDocument;
use App\Models\InternalRegulation;
use App\Models\User;
use App\Services\LibraryDocumentRegulationSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LibraryDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'Morador', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Síndico', 'guard_name' => 'web']);
    }

    private function condominiumWithDocumentsModule(): Condominium
    {
        return Condominium::factory()->create([
            'enabled_modules' => ['documents'],
        ]);
    }

    public function test_regulation_sync_creates_library_entry(): void
    {
        $condo = $this->condominiumWithDocumentsModule();
        $sindico = User::factory()->create(['condominium_id' => $condo->id]);
        $sindico->assignRole('Síndico');

        $regulation = InternalRegulation::create([
            'condominium_id' => $condo->id,
            'content' => 'Artigo 1 — Uso das áreas comuns.',
            'is_active' => true,
            'version' => 1,
            'updated_by' => $sindico->id,
        ]);

        app(LibraryDocumentRegulationSync::class)->syncFromRegulation($regulation);

        $this->assertDatabaseHas('condominium_library_documents', [
            'condominium_id' => $condo->id,
            'internal_regulation_id' => $regulation->id,
            'title' => 'Regimento Interno',
            'source' => 'regulation',
        ]);
    }

    public function test_morador_can_open_library_and_see_search(): void
    {
        $condo = $this->condominiumWithDocumentsModule();
        $morador = User::factory()->create(['condominium_id' => $condo->id]);
        $morador->assignRole('Morador');

        InternalRegulation::create([
            'condominium_id' => $condo->id,
            'content' => 'Regras sobre animais de estimação.',
            'is_active' => true,
            'version' => 1,
        ]);

        $response = $this->actingAs($morador)
            ->get(route('library-documents.index'));

        $response->assertOk()
            ->assertSee('Pesquisar no documento', false)
            ->assertSee('Regimento Interno', false);

        $this->assertMatchesRegularExpression('/animais/u', $response->getContent());
    }

    public function test_sindico_can_upload_document(): void
    {
        Storage::fake('local');
        $condo = $this->condominiumWithDocumentsModule();
        $sindico = User::factory()->create(['condominium_id' => $condo->id]);
        $sindico->assignRole('Síndico');

        $file = UploadedFile::fake()->create('ata.txt', 50, 'text/plain');
        file_put_contents($file->getRealPath(), 'Conteúdo da ata assembleia garagem');

        $this->actingAs($sindico)
            ->post(route('library-documents.store'), [
                'title' => 'Ata 2025',
                'description' => 'Assembleia geral',
                'file' => $file,
                'sort_order' => 50,
            ])
            ->assertRedirect(route('library-documents.manage'));

        $doc = CondominiumLibraryDocument::query()->where('title', 'Ata 2025')->first();
        $this->assertNotNull($doc);
        $this->assertStringContainsString('garagem', $doc->search_text ?? '');
    }

    public function test_morador_cannot_access_manage(): void
    {
        $condo = $this->condominiumWithDocumentsModule();
        $morador = User::factory()->create(['condominium_id' => $condo->id]);
        $morador->assignRole('Morador');

        $this->actingAs($morador)
            ->get(route('library-documents.manage'))
            ->assertForbidden();
    }
}
