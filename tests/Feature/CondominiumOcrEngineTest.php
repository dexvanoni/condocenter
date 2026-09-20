<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\User;
use App\Services\Ocr\OcrEngineResolver;
use App\Support\CondominiumModules;
use App\Support\OcrEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CondominiumOcrEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_sindico_can_update_label_ocr_engine(): void
    {
        [$condominium, $sindico] = $this->createSindicoWithCondominium();

        $this->actingAs($sindico)
            ->put(route('condominiums.settings.ocr.update', $condominium), [
                'label_ocr_engine' => OcrEngine::PADDLE,
            ])
            ->assertRedirect(route('condominiums.show', $condominium));

        $this->assertSame(OcrEngine::PADDLE, $condominium->fresh()->label_ocr_engine);
    }

    public function test_resolver_uses_condominium_configured_engine(): void
    {
        $condominium = Condominium::factory()->create([
            'label_ocr_engine' => OcrEngine::PADDLE,
            'enabled_modules' => CondominiumModules::keys(),
        ]);

        $resolver = app(OcrEngineResolver::class);

        $this->assertSame(OcrEngine::PADDLE, $resolver->engineKeyForCondominium($condominium->id));
    }

    public function test_morador_cannot_update_ocr_engine(): void
    {
        $condominium = Condominium::factory()->create([
            'enabled_modules' => CondominiumModules::keys(),
        ]);

        $role = Role::firstOrCreate(['name' => 'Morador', 'guard_name' => 'web']);
        $morador = User::factory()->create(['condominium_id' => $condominium->id]);
        $morador->assignRole($role);

        $this->actingAs($morador)
            ->put(route('condominiums.settings.ocr.update', $condominium), [
                'label_ocr_engine' => OcrEngine::PADDLE,
            ])
            ->assertForbidden();
    }

    /**
     * @return array{0: Condominium, 1: User}
     */
    private function createSindicoWithCondominium(): array
    {
        $condominium = Condominium::factory()->create([
            'enabled_modules' => CondominiumModules::keys(),
            'label_ocr_engine' => OcrEngine::TESSERACT,
        ]);

        $role = Role::firstOrCreate(['name' => 'Síndico', 'guard_name' => 'web']);
        $sindico = User::factory()->create(['condominium_id' => $condominium->id]);
        $sindico->assignRole($role);

        return [$condominium, $sindico];
    }
}
