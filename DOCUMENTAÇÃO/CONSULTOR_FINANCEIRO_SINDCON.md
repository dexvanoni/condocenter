# Consultor Financeiro SindCON

Ferramenta de apoio à decisão do síndico no **modo financeiro completo**. O Laravel calcula indicadores agregados; a LLM apenas interpreta o snapshot — **nunca** consulta o banco.

## Limite mensal por organização

O Administrador da plataforma define `organizations.llm_monthly_limit` na tela da organização.

| Tipo de organização | Comportamento |
|---------------------|---------------|
| Síndico / Condomínio | Limite do condomínio da organização |
| Administradora | Limite **compartilhado** por todos os condomínios da carteira |

- Sem limite configurado (`null`): novas consultas são bloqueadas até o admin definir um número.
- Conta apenas consultas com status `success` no mês civil atual.
- Respostas em cache **não** consomem cota.
- Rota admin: `PATCH /platform/organizations/{organization}/llm-limit`.

## Provedores LLM (OpenAI + Gemini)

As API keys são **globais** da plataforma (`.env`). O administrador escolhe **por condomínio** qual provider/modelo o Consultor usa. O síndico **não** escolhe o provider.

| Provider | Variáveis | Modelo padrão |
|----------|-----------|---------------|
| OpenAI | `OPENAI_API_KEY`, `OPENAI_MODEL`, `OPENAI_TIMEOUT` | valor de `OPENAI_MODEL` |
| Gemini | `GEMINI_API_KEY`, `GEMINI_MODEL`, `GEMINI_TIMEOUT` | `gemini-3.8-flash` |

Configuração admin: **Condomínios → [condo] → Consultor Financeiro — Inteligência Artificial**  
Rota: `PUT /condominiums/{condominium}/settings/ai` (`condominiums.settings.ai.update`).

Campos: `condominiums.ai_provider`, `condominiums.ai_model` (fallback: OpenAI + modelo do `.env`).

## Arquitetura

```
Síndico → POST /financial/consultor/analisar
  → FinanceAiAdvisorController (tenant + permissão + modo completo)
  → FinanceAiAdvisorService
  → FinancialAnalysisService (CondominiumAccount + Charge)
  → AiProviderManager (config do condomínio)
  → OpenAiProvider | GeminiProvider
  → validação JSON → Blade/JSON
```

Arquivos principais:

| Peça | Caminho |
|------|---------|
| Perguntas / prompt | `config/finance_ai.php` |
| OpenAI / Gemini env | `config/services.php` → `openai`, `gemini` |
| Motor de indicadores | `app/Services/Finance/FinancialAnalysisService.php` |
| Orquestração | `app/Services/Finance/FinanceAiAdvisorService.php` |
| Abstração LLM | `app/Services/Finance/Ai/*` |
| HTTP | `FinanceAiAdvisorController`, rotas `financial.ai-advisor.*` |
| Log de uso | tabela `ai_financial_consultations` (`provider`, `model`, tokens) |

## Variáveis de ambiente

```env
OPENAI_API_KEY=
OPENAI_MODEL=gpt-5.6-luna
OPENAI_TIMEOUT=30
GEMINI_API_KEY=
GEMINI_MODEL=gemini-3.8-flash
GEMINI_TIMEOUT=30
FINANCE_AI_RATE_LIMIT=10
FINANCE_AI_CACHE_TTL=3600
# FINANCE_AI_DEFAULT_PROVIDER=openai
# FINANCE_AI_MAX_OUTPUT_TOKENS=2000
```

- **Nunca** coloque a API key no frontend, Blade, banco ou Git.
- Sem fallback automático entre providers: se o configurado falhar, retorna erro controlado.

## Endpoints

| Método | Rota | Nome | Auth |
|--------|------|------|------|
| GET | `/financial/consultor` | `financial.ai-advisor.index` | síndico + modo completo |
| POST | `/financial/consultor/analisar` | `financial.ai-advisor.analyze` | idem + `throttle:finance-ai-advisor` |

Body do POST:

```json
{ "question": "financial_health" }
```

`condominium_id` no body é **ignorado**. O condomínio vem do contexto autenticado (`TenantContext` / sessão).

## Perguntas (chaves)

Definidas em `config/finance_ai.questions`:

- `where_spending`
- `reduce_energy`
- `expense_attention`
- `increase_revenue`
- `contracts_review`
- `default_analysis`
- `expense_evolution`
- `financial_health`
- `ninety_day_savings`
