<?php

namespace Tests\Feature;

use App\Models\AiFinancialConsultation;
use App\Models\Condominium;
use App\Models\CondominiumAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FinanceAiAdvisorTest extends TestCase
{
    use RefreshDatabase;

    protected Condominium $condominium;

    protected User $syndic;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'Síndico', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view_financial_reports', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'manage_transactions', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'view_own_financial', 'guard_name' => 'web']);

        $this->condominium = Condominium::factory()->create([
            'financial_mode' => 'full',
            'saas_complimentary' => true,
            'ai_provider' => 'openai',
            'ai_model' => null,
        ]);
        $this->condominium->organization?->update(['llm_monthly_limit' => 50]);

        $this->syndic = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $this->syndic->assignRole('Síndico');
        $this->syndic->givePermissionTo(['view_financial_reports', 'manage_transactions']);

        session([
            'active_role' => 'Síndico',
            'active_condominium_id' => $this->condominium->id,
        ]);

        CondominiumAccount::create([
            'condominium_id' => $this->condominium->id,
            'type' => 'expense',
            'status' => CondominiumAccount::STATUS_ACTIVE,
            'category' => 'energia',
            'description' => 'Energia',
            'amount' => 1200,
            'transaction_date' => now()->toDateString(),
            'created_by' => null,
        ]);
    }

    public function test_guest_cannot_access_advisor(): void
    {
        $this->get(route('financial.ai-advisor.index'))
            ->assertRedirect(route('login'));

        $this->postJson(route('financial.ai-advisor.analyze'), ['question' => 'financial_health'])
            ->assertUnauthorized();
    }

    public function test_simplified_mode_is_forbidden(): void
    {
        $this->condominium->update(['financial_mode' => 'simplified']);

        $this->actingAs($this->syndic)
            ->get(route('financial.ai-advisor.index'))
            ->assertForbidden();

        $this->actingAs($this->syndic)
            ->postJson(route('financial.ai-advisor.analyze'), ['question' => 'financial_health'])
            ->assertForbidden();
    }

    public function test_user_without_permission_cannot_analyze(): void
    {
        $morador = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        Role::firstOrCreate(['name' => 'Morador', 'guard_name' => 'web']);
        $morador->assignRole('Morador');
        $morador->givePermissionTo('view_own_financial');

        $this->actingAs($morador)
            ->withSession(['active_role' => 'Morador', 'active_condominium_id' => $this->condominium->id])
            ->postJson(route('financial.ai-advisor.analyze'), ['question' => 'financial_health'])
            ->assertForbidden();
    }

    public function test_non_syndic_with_financial_permission_cannot_access(): void
    {
        Role::firstOrCreate(['name' => 'Secretaria', 'guard_name' => 'web']);
        $secretaria = User::factory()->create([
            'condominium_id' => $this->condominium->id,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $secretaria->assignRole('Secretaria');
        $secretaria->givePermissionTo(['view_financial_reports', 'manage_transactions']);

        $this->actingAs($secretaria)
            ->withSession(['active_role' => 'Secretaria', 'active_condominium_id' => $this->condominium->id])
            ->get(route('financial.ai-advisor.index'))
            ->assertForbidden();
    }

    public function test_invalid_question_is_rejected(): void
    {
        $this->actingAs($this->syndic)
            ->postJson(route('financial.ai-advisor.analyze'), ['question' => 'not_a_real_question'])
            ->assertStatus(422);
    }

    public function test_client_condominium_id_is_ignored_and_uses_tenant_context(): void
    {
        $other = Condominium::factory()->create([
            'financial_mode' => 'full',
            'saas_complimentary' => true,
        ]);

        config(['services.openai.api_key' => 'test-key-not-real']);

        Http::fake([
            'api.openai.com/*' => Http::response($this->fakeOpenAiBody(), 200),
        ]);

        $this->actingAs($this->syndic)
            ->postJson(route('financial.ai-advisor.analyze'), [
                'question' => 'financial_health',
                'condominium_id' => $other->id,
            ])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('ai_financial_consultations', [
            'user_id' => $this->syndic->id,
            'condominium_id' => $this->condominium->id,
            'status' => 'success',
            'provider' => 'openai',
        ]);

        $this->assertDatabaseMissing('ai_financial_consultations', [
            'condominium_id' => $other->id,
        ]);
    }

    public function test_successful_analysis_with_mocked_openai(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response($this->fakeOpenAiBody(), 200),
        ]);

        config(['services.openai.api_key' => 'test-key-not-real']);

        $this->actingAs($this->syndic)
            ->get(route('financial.ai-advisor.index'))
            ->assertOk()
            ->assertSee('Consultor Financeiro SindCON', false);

        $response = $this->actingAs($this->syndic)
            ->postJson(route('financial.ai-advisor.analyze'), ['question' => 'where_spending']);

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('analysis.titulo', 'Análise financeira do condomínio')
            ->assertJsonStructure([
                'analysis' => [
                    'titulo',
                    'resumo',
                    'pontos_atencao',
                    'recomendacoes',
                    'observacoes',
                ],
            ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.openai.com')
                && $request->hasHeader('Authorization')
                && ! str_contains(json_encode($request->data()), 'test-key-not-real');
        });
    }

    public function test_successful_analysis_with_mocked_gemini(): void
    {
        $this->condominium->update([
            'ai_provider' => 'gemini',
            'ai_model' => 'gemini-3.8-flash',
        ]);

        config([
            'services.gemini.api_key' => 'gemini-test-key',
            'services.gemini.model' => 'gemini-3.8-flash',
            'services.openai.api_key' => null,
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response($this->fakeGeminiBody(), 200),
            'api.openai.com/*' => Http::response(['error' => 'should not call openai'], 500),
        ]);

        $this->actingAs($this->syndic)
            ->postJson(route('financial.ai-advisor.analyze'), ['question' => 'financial_health'])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('analysis.titulo', 'Análise financeira do condomínio');

        Http::assertSent(fn ($request) => str_contains($request->url(), 'generativelanguage.googleapis.com'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'api.openai.com'));

        $this->assertDatabaseHas('ai_financial_consultations', [
            'condominium_id' => $this->condominium->id,
            'provider' => 'gemini',
            'status' => 'success',
        ]);
    }

    public function test_condo_a_uses_gemini_and_condo_b_uses_openai(): void
    {
        $condoGemini = $this->condominium;
        $condoGemini->update(['ai_provider' => 'gemini', 'ai_model' => 'gemini-3.8-flash']);

        $condoOpenAi = Condominium::factory()->create([
            'financial_mode' => 'full',
            'saas_complimentary' => true,
            'ai_provider' => 'openai',
            'ai_model' => null,
        ]);
        $condoOpenAi->organization?->update(['llm_monthly_limit' => 50]);

        $syndicB = User::factory()->create([
            'condominium_id' => $condoOpenAi->id,
            'senha_temporaria' => false,
            'email_verified_at' => now(),
        ]);
        $syndicB->assignRole('Síndico');
        $syndicB->givePermissionTo(['view_financial_reports', 'manage_transactions']);

        config([
            'services.openai.api_key' => 'openai-test-key',
            'services.gemini.api_key' => 'gemini-test-key',
        ]);

        Http::fake([
            'api.openai.com/*' => Http::response($this->fakeOpenAiBody(), 200),
            'generativelanguage.googleapis.com/*' => Http::response($this->fakeGeminiBody(), 200),
        ]);

        $this->actingAs($this->syndic)
            ->withSession(['active_role' => 'Síndico', 'active_condominium_id' => $condoGemini->id])
            ->postJson(route('financial.ai-advisor.analyze'), ['question' => 'financial_health'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->actingAs($syndicB)
            ->withSession(['active_role' => 'Síndico', 'active_condominium_id' => $condoOpenAi->id])
            ->postJson(route('financial.ai-advisor.analyze'), ['question' => 'financial_health'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('ai_financial_consultations', [
            'condominium_id' => $condoGemini->id,
            'provider' => 'gemini',
            'status' => 'success',
        ]);
        $this->assertDatabaseHas('ai_financial_consultations', [
            'condominium_id' => $condoOpenAi->id,
            'provider' => 'openai',
            'status' => 'success',
        ]);
    }

    public function test_gemini_http_error_returns_clear_message(): void
    {
        $this->condominium->update(['ai_provider' => 'gemini', 'ai_model' => 'gemini-3.8-flash']);
        config(['services.gemini.api_key' => 'gemini-test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['message' => 'quota exceeded'],
            ], 429),
        ]);

        $this->actingAs($this->syndic)
            ->postJson(route('financial.ai-advisor.analyze'), ['question' => 'financial_health'])
            ->assertStatus(503)
            ->assertJsonPath('ok', false)
            ->assertJsonPath(
                'message',
                'A conta Gemini atingiu o limite de uso. Aguarde ou verifique a cota em Google AI Studio / Cloud.'
            );
    }

    public function test_gemini_without_api_key_returns_controlled_error(): void
    {
        $this->condominium->update(['ai_provider' => 'gemini', 'ai_model' => 'gemini-3.8-flash']);
        config(['services.gemini.api_key' => null, 'services.openai.api_key' => 'openai-present']);

        Http::fake();

        $this->actingAs($this->syndic)
            ->postJson(route('financial.ai-advisor.analyze'), ['question' => 'financial_health'])
            ->assertStatus(503)
            ->assertJsonPath('ok', false)
            ->assertJsonPath(
                'message',
                'Gemini não está configurado no ambiente da plataforma. Configure GEMINI_API_KEY no ambiente da aplicação.'
            );

        Http::assertNothingSent();
    }

    public function test_invalid_openai_json_returns_friendly_message(): void
    {
        config(['services.openai.api_key' => 'test-key-not-real']);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'isto não é json']],
                ],
                'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 5, 'total_tokens' => 15],
            ], 200),
        ]);

        $this->actingAs($this->syndic)
            ->postJson(route('financial.ai-advisor.analyze'), ['question' => 'financial_health'])
            ->assertStatus(503)
            ->assertJsonPath('ok', false)
            ->assertJsonPath(
                'message',
                'Não foi possível gerar a análise financeira neste momento. Seus dados financeiros continuam disponíveis normalmente.'
            );
    }

    public function test_openai_http_error_returns_friendly_message(): void
    {
        config(['services.openai.api_key' => 'test-key-not-real']);

        Http::fake([
            'api.openai.com/*' => Http::response(['error' => ['message' => 'boom']], 500),
        ]);

        $this->actingAs($this->syndic)
            ->postJson(route('financial.ai-advisor.analyze'), ['question' => 'financial_health'])
            ->assertStatus(503)
            ->assertJsonPath('ok', false)
            ->assertJsonPath(
                'message',
                'Não foi possível gerar a análise financeira neste momento. Seus dados financeiros continuam disponíveis normalmente.'
            );
    }

    public function test_openai_insufficient_credits_returns_clear_message(): void
    {
        config(['services.openai.api_key' => 'test-key-not-real']);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'error' => [
                    'message' => 'You have no credits remaining.',
                    'type' => 'insufficient_quota',
                    'code' => 'credit_balance_exhausted',
                ],
            ], 429),
        ]);

        $this->actingAs($this->syndic)
            ->postJson(route('financial.ai-advisor.analyze'), ['question' => 'financial_health'])
            ->assertStatus(503)
            ->assertJsonPath('ok', false)
            ->assertJsonPath(
                'message',
                'A conta OpenAI está sem créditos. Recarregue o saldo em platform.openai.com (Billing) ou use outra OPENAI_API_KEY com créditos.'
            );
    }

    public function test_missing_api_key_returns_friendly_message(): void
    {
        config(['services.openai.api_key' => null]);

        $this->actingAs($this->syndic)
            ->postJson(route('financial.ai-advisor.analyze'), ['question' => 'financial_health'])
            ->assertStatus(503)
            ->assertJsonPath('ok', false)
            ->assertJsonPath(
                'message',
                'OpenAI não está configurada no ambiente da plataforma. Configure OPENAI_API_KEY no ambiente da aplicação.'
            );
    }

    public function test_blocks_when_monthly_org_quota_exhausted(): void
    {
        config(['services.openai.api_key' => 'test-key-not-real']);
        Http::fake();

        $this->condominium->organization->update(['llm_monthly_limit' => 1]);

        AiFinancialConsultation::create([
            'condominium_id' => $this->condominium->id,
            'user_id' => $this->syndic->id,
            'question_key' => 'financial_health',
            'status' => 'success',
            'model' => 'gpt-test',
        ]);

        $this->actingAs($this->syndic)
            ->postJson(route('financial.ai-advisor.analyze'), ['question' => 'where_spending'])
            ->assertStatus(503)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('quota.limit', 1)
            ->assertJsonPath('quota.used', 1)
            ->assertJsonPath('quota.allowed', false);

        Http::assertNothingSent();
    }

    public function test_management_company_quota_is_shared_across_condominiums(): void
    {
        config(['services.openai.api_key' => 'test-key-not-real']);
        Http::fake([
            'api.openai.com/*' => Http::response($this->fakeOpenAiBody(), 200),
        ]);

        $org = \App\Models\Organization::factory()->managementCompany()->create([
            'llm_monthly_limit' => 2,
        ]);
        $first = Condominium::factory()->create([
            'organization_id' => $org->id,
            'financial_mode' => 'full',
            'saas_complimentary' => true,
        ]);
        $second = Condominium::factory()->create([
            'organization_id' => $org->id,
            'financial_mode' => 'full',
            'saas_complimentary' => true,
        ]);

        $this->syndic->forceFill(['condominium_id' => $first->id])->save();
        $first->syndics()->syncWithoutDetaching([$this->syndic->id]);
        $second->syndics()->syncWithoutDetaching([$this->syndic->id]);

        AiFinancialConsultation::create([
            'condominium_id' => $second->id,
            'user_id' => $this->syndic->id,
            'question_key' => 'financial_health',
            'status' => 'success',
            'model' => 'gpt-test',
        ]);

        $this->actingAs($this->syndic)
            ->withSession(['active_role' => 'Síndico', 'active_condominium_id' => $first->id])
            ->postJson(route('financial.ai-advisor.analyze'), ['question' => 'financial_health'])
            ->assertOk()
            ->assertJsonPath('quota.shared', true)
            ->assertJsonPath('quota.used', 2)
            ->assertJsonPath('quota.limit', 2);
    }

    /**
     * @return array<string, mixed>
     */
    protected function fakeOpenAiBody(): array
    {
        $content = json_encode([
            'titulo' => 'Análise financeira do condomínio',
            'resumo' => 'Os indicadores sugerem acompanhar as despesas de energia.',
            'pontos_atencao' => ['Energia concentra parte relevante das despesas.'],
            'recomendacoes' => [
                [
                    'titulo' => 'Avaliar o consumo de energia',
                    'acao' => 'Revisar áreas comuns e horários de pico.',
                    'motivo' => 'Categoria com peso relevante no período.',
                    'impacto' => 'Possível redução de despesa, dependendo das condições reais.',
                    'prioridade' => 'alta',
                ],
            ],
            'observacoes' => ['Os dados disponíveis são agregados.'],
        ], JSON_UNESCAPED_UNICODE);

        return [
            'model' => 'gpt-test',
            'choices' => [
                ['message' => ['content' => $content]],
            ],
            'usage' => [
                'prompt_tokens' => 100,
                'completion_tokens' => 80,
                'total_tokens' => 180,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function fakeGeminiBody(): array
    {
        $content = json_encode([
            'titulo' => 'Análise financeira do condomínio',
            'resumo' => 'Os indicadores sugerem acompanhar as despesas de energia.',
            'pontos_atencao' => ['Energia concentra parte relevante das despesas.'],
            'recomendacoes' => [
                [
                    'titulo' => 'Avaliar o consumo de energia',
                    'acao' => 'Revisar áreas comuns e horários de pico.',
                    'motivo' => 'Categoria com peso relevante no período.',
                    'impacto' => 'Possível redução de despesa, dependendo das condições reais.',
                    'prioridade' => 'alta',
                ],
            ],
            'observacoes' => ['Os dados disponíveis são agregados.'],
        ], JSON_UNESCAPED_UNICODE);

        return [
            'candidates' => [
                [
                    'content' => [
                        'parts' => [['text' => $content]],
                    ],
                    'finishReason' => 'STOP',
                ],
            ],
            'usageMetadata' => [
                'promptTokenCount' => 90,
                'candidatesTokenCount' => 70,
                'totalTokenCount' => 160,
            ],
        ];
    }
}
