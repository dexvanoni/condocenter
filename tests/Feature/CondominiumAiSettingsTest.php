<?php

namespace Tests\Feature;

use App\Models\Condominium;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CondominiumAiSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Condominium $condominium;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Síndico', 'guard_name' => 'web']);

        $this->admin = User::factory()->create([
            'condominium_id' => null,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $this->admin->assignRole('Administrador');

        $this->condominium = Condominium::factory()->create([
            'financial_mode' => 'full',
            'saas_complimentary' => true,
            'ai_provider' => 'openai',
            'ai_model' => null,
        ]);
    }

    public function test_admin_can_set_gemini_provider_when_key_configured(): void
    {
        config([
            'services.gemini.api_key' => 'gemini-test-key',
            'services.gemini.model' => 'gemini-3.8-flash',
            'finance_ai.providers.gemini.models' => ['gemini-3.8-flash'],
        ]);

        $this->actingAs($this->admin)
            ->put(route('condominiums.settings.ai.update', $this->condominium), [
                'ai_provider' => 'gemini',
                'ai_model' => 'gemini-3.8-flash',
            ])
            ->assertRedirect(route('condominiums.show', $this->condominium));

        $this->assertDatabaseHas('condominiums', [
            'id' => $this->condominium->id,
            'ai_provider' => 'gemini',
            'ai_model' => 'gemini-3.8-flash',
        ]);
    }

    public function test_admin_can_set_openai_provider_when_key_configured(): void
    {
        config([
            'services.openai.api_key' => 'openai-test-key',
            'services.openai.model' => 'gpt-5.6-luna',
            'finance_ai.providers.openai.models' => ['gpt-5.6-luna'],
        ]);

        $this->condominium->update(['ai_provider' => 'gemini', 'ai_model' => 'gemini-3.8-flash']);

        $this->actingAs($this->admin)
            ->put(route('condominiums.settings.ai.update', $this->condominium), [
                'ai_provider' => 'openai',
                'ai_model' => 'gpt-5.6-luna',
            ])
            ->assertRedirect();

        $this->assertSame('openai', $this->condominium->fresh()->ai_provider);
        $this->assertSame('gpt-5.6-luna', $this->condominium->fresh()->ai_model);
    }

    public function test_admin_cannot_save_gemini_without_api_key(): void
    {
        config(['services.gemini.api_key' => null]);

        $this->actingAs($this->admin)
            ->from(route('condominiums.show', $this->condominium))
            ->put(route('condominiums.settings.ai.update', $this->condominium), [
                'ai_provider' => 'gemini',
                'ai_model' => 'gemini-3.8-flash',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('ai_provider');

        $this->assertSame('openai', $this->condominium->fresh()->ai_provider);
    }

    public function test_rejects_openai_model_when_provider_is_gemini(): void
    {
        config([
            'services.gemini.api_key' => 'gemini-test-key',
            'finance_ai.providers.gemini.models' => ['gemini-3.8-flash'],
            'finance_ai.providers.openai.models' => ['gpt-5.6-luna'],
        ]);

        $this->actingAs($this->admin)
            ->from(route('condominiums.show', $this->condominium))
            ->put(route('condominiums.settings.ai.update', $this->condominium), [
                'ai_provider' => 'gemini',
                'ai_model' => 'gpt-5.6-luna',
            ])
            ->assertSessionHasErrors('ai_model');
    }

    public function test_syndic_cannot_update_ai_settings(): void
    {
        $syndic = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $syndic->assignRole('Síndico');

        config([
            'services.gemini.api_key' => 'gemini-test-key',
            'finance_ai.providers.gemini.models' => ['gemini-3.8-flash'],
        ]);

        $this->actingAs($syndic)
            ->putJson(route('condominiums.settings.ai.update', $this->condominium), [
                'ai_provider' => 'gemini',
                'ai_model' => 'gemini-3.8-flash',
            ])
            ->assertForbidden();

        $this->assertSame('openai', $this->condominium->fresh()->ai_provider);
    }

    public function test_show_page_includes_ai_settings_for_admin(): void
    {
        config([
            'services.openai.api_key' => 'openai-test-key',
            'services.gemini.api_key' => 'gemini-test-key',
        ]);

        $this->actingAs($this->admin)
            ->get(route('condominiums.show', $this->condominium))
            ->assertOk()
            ->assertSee('Consultor Financeiro — Inteligência Artificial', false)
            ->assertSee('ai_provider', false)
            ->assertSee('ai_model', false);
    }
}
