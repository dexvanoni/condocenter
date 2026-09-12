# PRD — SindCON (CondoCenter)

**Product Requirements Document**

| Campo | Valor |
|-------|-------|
| **Produto** | SindCON — Plataforma SaaS de Gestão Condominial |
| **Repositório** | CondoCenter |
| **Versão do documento** | 2.0 |
| **Data** | 12/09/2026 |
| **Status** | Em produção / evolução contínua |
| **Stack** | Laravel 12, PHP 8.3+, MySQL, Bootstrap 5, Vue 3, Vite, Sanctum, Spatie Permission |
| **Integrações** | Asaas (pagamentos), Evolution API (WhatsApp), Firebase (push mobile) |

---

## Sumário

1. [Visão do produto](#1-visão-do-produto)
2. [Problema e oportunidade](#2-problema-e-oportunidade)
3. [Objetivos e metas de sucesso](#3-objetivos-e-metas-de-sucesso)
4. [Personas e papéis](#4-personas-e-papéis)
5. [Escopo do produto](#5-escopo-do-produto)
6. [Arquitetura e modelo multi-tenant](#6-arquitetura-e-modelo-multi-tenant)
7. [Módulos habilitáveis por condomínio](#7-módulos-habilitáveis-por-condomínio)
8. [Requisitos funcionais por módulo](#8-requisitos-funcionais-por-módulo)
9. [Landing page pública](#9-landing-page-pública)
10. [Regras de negócio globais](#10-regras-de-negócio-globais)
11. [Permissões e controle de acesso](#11-permissões-e-controle-de-acesso)
12. [Integrações externas](#12-integrações-externas)
13. [Canais de entrega (Web, API, Mobile)](#13-canais-de-entrega-web-api-mobile)
14. [Modelo comercial (SaaS)](#14-modelo-comercial-saas)
15. [Operação em produção](#15-operação-em-produção)
16. [Modelo de dados (visão geral)](#16-modelo-de-dados-visão-geral)
17. [Requisitos não funcionais](#17-requisitos-não-funcionais)
18. [Jornadas principais](#18-jornadas-principais)
19. [Cobertura de testes e qualidade](#19-cobertura-de-testes-e-qualidade)
20. [Fora de escopo e roadmap](#20-fora-de-escopo-e-roadmap)
21. [Glossário](#21-glossário)
22. [Referências](#22-referências)

---

## 1. Visão do produto

### 1.1 Declaração de visão

O **SindCON** é uma plataforma SaaS multi-condomínio que centraliza gestão operacional, financeira, de comunicação e de segurança de condomínios residenciais. Oferece transparência aos moradores, eficiência à administração e monetização recorrente à operadora da plataforma, com isolamento rigoroso por condomínio (tenant) e ativação modular de funcionalidades.

### 1.2 Proposta de valor

| Stakeholder | Valor entregue |
|-------------|----------------|
| **Operadora da plataforma** | Receita recorrente por assinatura, gestão centralizada de múltiplos condomínios, configuração global de Asaas e WhatsApp |
| **Síndico / administração** | Finanças (modo completo ou simplificado), usuários, reservas, portaria, assembleias, landing page, fechamento mensal, toggles de módulos |
| **Morador** | Autosserviço (pagamentos, reservas, marketplace, pets), transparência financeira, canal de emergência, auto-cadastro com aprovação |
| **Porteiro** | Registro de acessos, encomendas, liberações, verificação de pets via QR |
| **Conselho fiscal** | Visibilidade financeira, exportações, participação em assembleias |
| **Visitante / público** | Landing page do condomínio com avisos, eventos, galeria e QR Code |

### 1.3 Posicionamento

- **Segmento:** condomínios residenciais (pequenos a grandes) e administradoras
- **Modelo:** B2B2C — plataforma vende ao condomínio; moradores são usuários finais
- **Diferenciais:** ecossistema integrado (financeiro + operacional + comunicação + pânico), WhatsApp nativo (Evolution API), marketplace e caronas internas, livro de ocorrências sigiloso, fechamento mensal guiado, landing page com domínio próprio e templates visuais

### 1.4 Componentes do repositório

| Componente | Caminho | Descrição |
|------------|---------|-----------|
| Backend / Web | Raiz do repo | Laravel — interface principal e API |
| App mobile | `celular/CondoCenterMobile` | Expo/React Native — pânico + push (Firebase) |
| Documentação | `DOCUMENTAÇÃO/` | PRD, VPS, módulos, API, regras |
| Assets frontend | `resources/js`, `resources/css` | Vite — app, reservas Vue, landing classic/connect |

---

## 2. Problema e oportunidade

### 2.1 Problemas atuais no mercado

- Gestão fragmentada (planilhas, grupos de WhatsApp, sistemas isolados)
- Baixa transparência financeira para moradores e conselho
- Comunicação informal sem registro ou auditoria
- Dificuldade de cobrança e controle de inadimplência
- Ausência de canal formal e rastreável para emergências
- Portaria com processos manuais e pouco integrados
- Falta de presença digital profissional do condomínio (site/portal público)

### 2.2 Oportunidade

Digitalizar o ciclo completo da vida condominial — do cadastro de moradores ao pagamento de taxas, reserva de áreas comuns, controle de visitantes, assembleias virtuais, alertas de pânico e portal público — em uma única plataforma com isolamento por condomínio, modelo SaaS escalável e módulos ativáveis conforme o perfil de cada cliente.

---

## 3. Objetivos e metas de sucesso

### 3.1 Objetivos de produto

1. Reduzir tempo operacional do síndico em tarefas administrativas repetitivas
2. Aumentar taxa de adimplência via cobrança digital e lembretes automatizados
3. Proporcionar transparência financeira total aos moradores e conselho fiscal
4. Garantir comunicação rastreável e canais sigilosos quando necessário
5. Oferecer resposta rápida a emergências via alerta de pânico (web + mobile)
6. Monetizar via assinatura SaaS com planos flexíveis
7. Permitir que cada condomínio ative apenas os módulos relevantes
8. Oferecer presença digital pública via landing page configurável

### 3.2 KPIs sugeridos

| KPI | Descrição |
|-----|-----------|
| **Taxa de adimplência** | % de cobranças pagas no prazo por condomínio |
| **Adoção de moradores** | % de unidades com pelo menos 1 usuário ativo |
| **Tempo médio de resolução de OS** | Da abertura à conclusão |
| **Engajamento em assembleias** | % de participação em votações |
| **Tempo de resposta ao pânico** | Da confirmação à resolução pelo síndico |
| **Churn de assinatura SaaS** | Condomínios que cancelam assinatura |
| **Módulos ativos por condomínio** | Adoção média dos 11 módulos configuráveis |
| **Visitas à landing page** | Tráfego público por slug/domínio customizado |
| **NPS / satisfação** | Pesquisa periódica com síndicos e moradores |

---

## 4. Personas e papéis

### 4.1 Personas

#### P1 — Administrador da plataforma
- **Quem:** equipe SindCON / operadora do SaaS
- **Necessidades:** gerenciar condomínios, planos, assinaturas, Asaas/WhatsApp globais, novidades da plataforma
- **Acesso:** painel `platform.*` (sem condomínio ativo obrigatório)
- **Dashboard:** `dashboard/admin.blade.php`

#### P2 — Síndico
- **Quem:** gestor eleito ou profissional do condomínio
- **Necessidades:** finanças, usuários, reservas, comunicação, moderação, relatórios, landing page, fechamento mensal
- **Pode atuar em múltiplos condomínios** via pivot `condominium_user` + seletor `condominium.switch`
- **Dashboard:** `dashboard/sindico.blade.php`

#### P3 — Morador (responsável pela unidade)
- **Quem:** proprietário ou inquilino principal da unidade
- **Necessidades:** pagar taxas, reservar espaços, marketplace, pets, votar, acionar pânico, registrar ocorrências
- **Regra:** uma unidade possui um morador responsável
- **Dashboard:** `dashboard/morador.blade.php`

#### P4 — Agregado
- **Quem:** dependente vinculado ao morador (cônjuge, filho, empregada etc.)
- **Necessidades:** acesso limitado conforme `AgregadoPermission` (view/crud por módulo)
- **Regra:** vinculado via `morador_vinculado_id`
- **Dashboard:** `dashboard/agregado.blade.php`

#### P5 — Porteiro
- **Quem:** equipe de portaria / controle de acesso
- **Necessidades:** registrar entradas, encomendas, processar liberações, verificar pets
- **Dashboard:** `dashboard/porteiro.blade.php`

#### P6 — Conselho Fiscal
- **Quem:** conselheiros eleitos
- **Necessidades:** transparência financeira, relatórios, assembleias
- **Dashboard:** `dashboard/conselho.blade.php`

#### P7 — Secretaria
- **Quem:** apoio administrativo do condomínio
- **Necessidades:** visualização operacional, envio de avisos, comunicação
- **Dashboard:** `dashboard/default.blade.php` (fallback)

### 4.2 Matriz resumida papel × escopo

| Papel | Plataforma SaaS | Condomínio | Financeiro completo | Pânico | Landing admin |
|-------|-----------------|------------|----------------------|--------|---------------|
| Administrador | ✅ Total | ✅ Total | ✅ | ✅ Gestão | ❌ |
| Síndico | ❌ | ✅ Total | ✅ | ✅ Gestão | ✅ |
| Morador | ❌ | ✅ Uso | ✅ Visualização | ✅ Acionar | ❌ |
| Agregado | ❌ | ⚙️ Configurável | ⚙️ Configurável | ✅ Acionar | ❌ |
| Porteiro | ❌ | ✅ Portaria | ❌ | ❌ | ❌ |
| Conselho Fiscal | ❌ | ✅ Fiscalização | ✅ Visualização | ❌ | ❌ |
| Secretaria | ❌ | ✅ Operacional | ⚙️ Parcial | ❌ | ❌ |

### 4.3 Perfil ativo e multi-papel

- Usuário pode possuir **múltiplos papéis** (ex.: Síndico + Morador)
- Permissões avaliadas pelo **perfil ativo** em sessão (`HasActiveProfileRole`, `active_role`)
- Troca via `ProfileSelectorController` → `/profile/select`, `/profile/switch`
- Sidebar e dashboard renderizados conforme perfil ativo + módulos habilitados

---

## 5. Escopo do produto

### 5.1 Dentro do escopo (implementado)

#### Plataforma e tenancy
- SaaS multi-condomínio com assinatura, planos, trial e bloqueio por inadimplência com a plataforma
- Admin plataforma: condomínios, planos, assinaturas, Asaas/WhatsApp globais, novidades (`PlatformAnnouncement`)
- Seletor de condomínio ativo para administradores e síndicos multi-condomínio

#### Gestão de pessoas
- CRUD de unidades e usuários, histórico exportável (PDF/Excel)
- Auto-cadastro com código do condomínio + aprovação do síndico
- Permissões granulares para agregados (`AgregadoPermission`)
- Onboarding: e-mail verificado, troca de senha obrigatória, seleção de perfil

#### Financeiro
- Modo **completo** (caixa, contas bancárias, conciliação, funcionários, DRE) e **simplificado** (upload de prestação)
- Taxas recorrentes, cobranças em lote, multas, baixa manual, desconto em folha
- Pagamentos Asaas: PIX, boleto, cartão; webhooks por plataforma e por condomínio
- Fechamento mensal com checklist de 9 etapas
- Transparência financeira para morador e conselho fiscal

#### Operacional
- Reservas (calendário Vue, recorrentes, créditos, cobrança online)
- Marketplace interno (até 3 imagens, moderação, WhatsApp do anunciante)
- Caronas entre moradores
- Pets com QR Code público
- Ordens de serviço (chat, itens, cobrança de ressarcimento)
- Controle de acesso / portaria (liberações, listas, prestadores, movimentos, proibições)
- Encomendas (registro, notificação, retirada com código)
- Assembleias (pauta, votação secreta/opcional, delegação, ata exportável)
- Regimento interno versionado

#### Comunicação
- Mensagens, conversas (avisos, diretas, reuniões, anexos)
- Fale com o Síndico (canal sigiloso)
- Livro de Ocorrências (sigiloso + exposição pública opcional)
- Notificações in-app + e-mail + WhatsApp + push (pânico)
- Landing page pública (2 templates, domínio customizado, QR Code)

#### Segurança
- Alerta de pânico (7 tipos, slide-to-confirm, web + app mobile)
- Auditoria Laravel Auditing em models críticos

#### Canais
- Web (Blade + Bootstrap 5 + Vue 3 parcial)
- API REST (Sanctum, stateful + token)
- App mobile (Expo — escopo atual: autenticação + pânico)

### 5.2 Fora do escopo atual

- Integração Stripe (pagamentos exclusivamente via Asaas)
- App mobile completo (demais módulos além do pânico)
- API para ordens de serviço, livro de ocorrências e regimento interno
- Integração com elevadores, CFTV ou IoT
- ERP contábil externo (integração bidirecional)

---

## 6. Arquitetura e modelo multi-tenant

### 6.1 Hierarquia de dados

```
Plataforma SindCON
 ├── SubscriptionPlan, PlatformSetting, PlatformAnnouncement
 └── Condomínio (tenant)
      ├── enabled_modules (JSON, 11 slugs)
      ├── financial_mode (full | simplified)
      ├── payment_receiving_mode (manual | platform)
      ├── Assinatura SaaS (CondominiumSubscription)
      ├── Unidades → Morador responsável + Agregados
      ├── Usuários (condominium_id, unit_id, roles Spatie)
      ├── CondominiumLandingPage (1:1) → CondominiumLandingItem[]
      └── Dados operacionais isolados por condominium_id
```

### 6.2 Isolamento de tenant

- Todo dado operacional possui `condominium_id` (ou deriva dele)
- Middleware `require.condominium` exige condomínio ativo (`ActiveCondominiumService`)
- Middleware `ensure.saas.subscription` bloqueia módulos se assinatura inativa (`config/saas.php`)
- Síndicos multi-condomínio: pivot `condominium_user` + rota `condominium.switch`
- Admin plataforma alterna contexto via `active_condominium_id` na sessão

### 6.3 Camadas de autorização (ordem típica)

| Camada | Middleware / mecanismo | Função |
|--------|------------------------|--------|
| 1 | `auth`, `verified`, `check.password`, `check.profile` | Autenticação e onboarding |
| 2 | `require.condominium` | Condomínio ativo na sessão |
| 3 | `ensure.saas.subscription` | Assinatura SaaS válida |
| 4 | `condominium.module:{slug}` | Módulo habilitado no condomínio |
| 5 | `can:{permission}` (Spatie) | Permissão do perfil ativo |
| 6 | `check.module.access:{slug}` | Módulo + permissão + inadimplência |
| 7 | `restrict.defaulters` | Bloqueio para inadimplentes (rotas específicas) |
| 8 | `ensure.full.financial` | Modo financeiro completo |

### 6.4 Ambientes financeiros

| Modo | Valor | Descrição |
|------|-------|-----------|
| **Completo** | `full` | Caixa, transações, conciliação, funcionários, fechamento mensal, prestação gerada |
| **Simplificado** | `simplified` | Upload de prestação de contas; rotas avançadas bloqueadas por `ensure.full.financial` |

### 6.5 Modos de recebimento (Asaas)

| Modo | Descrição |
|------|-----------|
| **Plataforma** | Credenciais SindCON; split/repasse configurável |
| **Próprio** | Credenciais Asaas do condomínio (`CondominiumAsaasSettingsService`) |

### 6.6 Camada de serviços

Regras de negócio concentradas em `app/Services/` (~50 classes), incluindo:

- **Financeiro:** `FeeService`, `ChargePaymentService`, `ChargeSettlementService`, `MonthlyClosingService`, `BankReconciliationService`, `AccountabilityReportService`, `FineService`, `DefaulterRestrictionService`
- **Integrações:** `AsaasService`, `PlatformAsaasService`, `EvolutionApiService`, `WhatsAppNotificationService`
- **Operacional:** `AccessControlService`, `PackageService`, `RideBookingService`, `ServiceOrderService`, `OccurrenceBookService`
- **Assembleias:** namespace `App\Services\Assembly\*`
- **Plataforma:** `CondominiumSubscriptionService`, `CondominiumLandingService`, `ActiveCondominiumService`

Controllers permanecem finos; validação em Form Requests; autorização em Policies.

---

## 7. Módulos habilitáveis por condomínio

### 7.1 Catálogo oficial

Fonte: `app/Support/CondominiumModules.php` — coluna `condominiums.enabled_modules` (JSON).

| Slug | Label | Descrição resumida |
|------|-------|-------------------|
| `financial` | Financeiro | Taxas, cobranças, caixa, multas, prestação de contas |
| `spaces` | Espaços e reservas | Áreas comuns, reservas, recorrentes |
| `marketplace` | Marketplace | Classificados entre moradores |
| `rides` | Caronas | Oferta e reserva de caronas |
| `pets` | Pets | Cadastro e QR Code |
| `service_orders` | Ordens de serviço | Manutenção e OS |
| `assemblies` | Assembleias | Convocação, votação, atas |
| `documents` | Documentos | Regimento interno |
| `packages` | Encomendas | Registro e retirada |
| `access_control` | Controle de acesso | Portaria, liberações, relatórios |
| `communication` | Comunicação | Mensagens, ocorrências, fale com síndico, landing |

### 7.2 Módulos sempre ativos (não configuráveis)

- **Gestão** (unidades, usuários)
- **Dashboard** (por perfil)
- **Alerta de pânico**
- **Assinatura SaaS** (área do síndico)
- **Notificações** (centro in-app)

### 7.3 Comportamento

- `enabled_modules = null` → **todos os módulos ligados** (retrocompatibilidade)
- Configuração: `PUT /condominiums/{id}/settings/modules` (`CondominiumModulesSettingsController`)
- UI: Gestão → Meu Condomínio → Módulos
- Módulo desligado → rotas retornam 403; item some do menu (`SidebarHelper`)

### 7.4 Aliases de módulo

- `messages` e `notifications` → mapeiam para slug `communication`

---

## 8. Requisitos funcionais por módulo

### 8.1 Plataforma SaaS (Administrador)

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| PLT-01 | CRUD de condomínios (ativar/desativar, código de registro) | Must | `CondominiumController`, `condominiums/*` |
| PLT-02 | Gestão de planos de assinatura | Must | `SubscriptionPlanController` |
| PLT-03 | Gestão de assinaturas por condomínio (ativar, suspender, cancelar, sync Asaas) | Must | `CondominiumSubscriptionController` |
| PLT-04 | Sincronização de cobranças SaaS com Asaas | Must | `PlatformAsaasService`, webhooks |
| PLT-05 | Configuração global Asaas e WhatsApp (Evolution API) | Must | `PlatformSettingsController` |
| PLT-06 | Novidades/comunicados globais da plataforma | Should | `PlatformAnnouncement` → feed landing |
| PLT-07 | Dashboard consolidado da operação SaaS | Should | `platform/dashboard` |
| PLT-08 | Documentação VPS servida por token | Should | `/dev/docs/{token}` |
| PLT-09 | Teste de integração Asaas/WhatsApp global | Should | `PlatformIntegrationTestService` |

### 8.2 Gestão de unidades e usuários

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| USR-01 | CRUD de unidades com morador responsável | Must | `UnitController` |
| USR-02 | CRUD de usuários com múltiplos papéis Spatie | Must | `UserController` |
| USR-03 | Auto-cadastro com código + aprovação do síndico | Must | `SelfRegistrationController` |
| USR-04 | Ativar/desativar usuários | Must | `UserController` |
| USR-05 | Histórico completo do usuário (PDF/Excel) | Should | `UserHistoryController` |
| USR-06 | Permissões granulares agregados (`view`/`crud` por módulo) | Must | `AgregadoPermission` |
| USR-07 | Apenas Admin atribui/remove perfil Administrador | Must | `UserController` + policies |
| USR-08 | Síndico atribui Síndico e Conselho Fiscal | Must | Idem |
| USR-09 | Seleção de perfil ativo para multi-papel | Must | `ProfileSelectorController` |
| USR-10 | Exportação de unidades (PDF/Excel/CSV) | Should | `UnitController@export` |

### 8.3 Financeiro

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| FIN-01 | CRUD de transações com comprovantes | Must | `IncomeExpenseController`, API `TransactionController` |
| FIN-02 | Configuração e geração de taxas/cobranças em lote | Must | `FeeController`, `FeeService`, cron `fees:generate-upcoming` |
| FIN-03 | Cobrança por unidade (boleto, PIX, cartão via Asaas) | Must | `ChargePaymentService`, `GenerateAsaasPayment` job |
| FIN-04 | Extrato e pagamento online ("Minhas Cobranças") | Must | `ResidentChargeController`, `my-charges` |
| FIN-05 | Multas com PDF de notificação | Should | `FineController`, `FineNoticeService` |
| FIN-06 | Conciliação bancária (CSV/OFX + matching) | Should | `BankReconciliationController` |
| FIN-07 | Prestação de contas (PDF/Excel/impressão/ZIP) | Must | `AccountabilityReportController` |
| FIN-08 | Painel de adimplência e inadimplência | Must | `FinancialStatusController` |
| FIN-09 | Transparência total morador/conselho | Must | Permissões + policies |
| FIN-10 | Webhooks Asaas (plataforma + condomínio) | Must | `WebhookController` |
| FIN-11 | Lembretes automáticos de vencimento | Should | `charges:send-reminders`, `charges:check-overdue` |
| FIN-12 | Baixa manual e liquidação folha | Should | `ChargeSettlementController`, `charges:settle-payroll` |
| FIN-13 | Fechamento mensal com checklist | Should | `MonthlyClosingController`, 9 passos |
| FIN-14 | Contas bancárias e regras de roteamento | Should | `BankAccountController` |
| FIN-15 | Funcionários e lançamentos de folha | Should | `EmployeeController` |
| FIN-16 | Créditos de usuário para reservas | Should | `UserCreditService` |
| FIN-17 | Modo simplificado (upload prestação) | Must | `AccountabilityReportUploadController` |
| FIN-18 | Auditoria de cobertura de taxas por unidade | Should | `FeeChargeCoverageService` |

#### Passos do fechamento mensal (`MonthlyClosingSteps`)

1. Geração de taxas  
2. Cobertura de unidades  
3. Cobranças  
4. Multas  
5. Reservas  
6. Funcionários  
7. Caixa manual  
8. Conciliação bancária  
9. Prestação de contas  

### 8.4 Reservas

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| RES-01 | Calendário interativo (Vue) | Must | `ReservationController`, views `reservations/` |
| RES-02 | CRUD de espaços reserváveis | Must | `SpaceController` |
| RES-03 | Aprovação manual ou automática | Must | `ReservationManagementController` |
| RES-04 | Reservas recorrentes / bloqueios | Should | `RecurringReservationController` |
| RES-05 | Créditos de usuário | Should | `UserCreditService` |
| RES-06 | Pagamento online condicionado | Should | `ReservationChargeService` |
| RES-07 | Limites e detecção de conflitos | Must | `ReservationController` |
| RES-08 | Gestão administrativa em lote | Must | `AdminReservationController` |
| RES-09 | Cancelamento de pré-reservas expiradas | Should | Cron `reservations:cancel-expired-prereservations` |

### 8.5 Marketplace

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| MKT-01 | Anúncios por categoria (produtos, serviços, empregos, imóveis, veículos, outros) | Must | `MarketplaceController` |
| MKT-02 | Até 3 imagens (upload ou câmera) | Must | `FileUploadService` |
| MKT-03 | Edição com sync de imagens | Must | `MarketplaceController@update` |
| MKT-04 | Moderação pelo síndico | Should | `MarketplaceAdminController` |
| MKT-05 | Bloqueio inadimplentes | Must | `restrict.defaulters` |
| MKT-06 | Toggle agregados (`marketplace_allow_agregados`) | Should | Flag no condomínio |
| MKT-07 | Contato WhatsApp do anunciante | Must | Link direto |
| MKT-08 | Feed na landing page (opcional) | Should | `CondominiumLandingService` |

### 8.6 Caronas

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| CAR-01 | Oferta de carona | Should | `Api\RideController` |
| CAR-02 | Reserva de vaga | Should | `RideBookingService` |
| CAR-03 | Notificações WhatsApp | Should | Jobs + `WhatsAppNotificationService` |
| CAR-04 | Feed na landing page | Should | `show_rides_feed` |

### 8.7 Pets

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| PET-01 | CRUD com foto | Must | `PetController` |
| PET-02 | QR Code público (`/pets/qr/{code}`) | Must | Rota pública |
| PET-03 | Impressão/download tag | Should | Views `pets/` |
| PET-04 | Verificação na portaria | Must | `PetController@verify` |

### 8.8 Ordens de serviço

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| OS-01 | Morador abre solicitação | Must | `ServiceOrderController` |
| OS-02 | Síndico gerencia status, mensagens, itens | Must | `ServiceOrderManagementController` |
| OS-03 | Geração de cobrança de ressarcimento | Should | `ServiceOrderService` |
| OS-04 | Bloqueio inadimplentes | Must | Middleware |

### 8.9 Controle de acesso / Portaria

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| ACC-01 | Painel do porteiro | Must | `AccessControlWebController`, `access-control/porteiro` |
| ACC-02 | Liberações prévias morador/agregado | Must | `access-control/resident` |
| ACC-03 | Listas de autorização e prestadores | Should | API `access-control/*` |
| ACC-04 | Relatório movimentações (PDF) | Should | `access-control/reports` |
| ACC-05 | Proibições de acesso | Should | `AccessControlService` |
| ACC-06 | Alertas de acesso no dashboard | Should | `AccessAlertService` |

### 8.10 Encomendas

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| PKG-01 | Portaria registra chegada | Must | `PackageService`, SPA `packages/` |
| PKG-02 | Notificação morador (WhatsApp/database) | Must | Jobs |
| PKG-03 | Retirada com código | Must | API `packages/{id}/collect` |
| PKG-04 | UI com barra de progresso | Should | Frontend packages |

### 8.11 Assembleias

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| ASM-01 | Criação com pauta e anexos | Must | `AssemblyService` |
| ASM-02 | Votação sim/não/abstenção, opção secreta | Must | `AssemblyVotingService` |
| ASM-03 | Delegação de voto | Should | API |
| ASM-04 | Ciclo: iniciar, concluir, cancelar, reabrir | Must | Transições de status |
| ASM-05 | Exportação de ata (PDF/markdown) | Should | `AssemblyMinutesService` |
| ASM-06 | Bloqueio inadimplentes | Must | Middleware |

### 8.12 Comunicação

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| COM-01 | Mensagens (mural, privadas, prioridades) | Must | API `MessageController` |
| COM-02 | Conversas (avisos, diretas, anexos, reuniões) | Must | `ConversationWebController` |
| COM-03 | Fale com o Síndico (sigiloso) | Must | `SyndicConversationWebController` |
| COM-04 | Broadcast de avisos | Must | `conversations/announcement` |
| COM-05 | Centro de notificações + contador | Must | `NotificationController` |
| COM-06 | Export CSV/PDF de conversas | Should | API |
| COM-07 | Deep links de notificações | Should | `NotificationRedirectService` |
| COM-08 | Landing page admin (ver seção 9) | Should | `CondominiumLandingAdminController` |

### 8.13 Livro de Ocorrências

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| OCC-01 | Morador registra ocorrência/crítica/sugestão | Must | `OccurrenceBookController` |
| OCC-02 | Sigiloso — admin plataforma sem perfil síndico não acessa | Must | Policies |
| OCC-03 | Síndico: ciência, comentários, gestão | Must | `OccurrenceBookService` |
| OCC-04 | Exposição pública opcional (sem autor) | Should | Livro público |
| OCC-05 | Foto opcional na criação | Should | Upload |
| OCC-06 | Export Excel/PDF | Should | Views export |
| OCC-07 | WhatsApp opcional | Should | `OccurrenceBookNotificationService` |

### 8.14 Regimento interno

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| REG-01 | Publicação e versionamento | Must | `InternalRegulationController` |
| REG-02 | Histórico de alterações | Must | `InternalRegulationHistory` |
| REG-03 | Visualização, PDF e impressão | Must | Views `internal-regulations/` |

### 8.15 Alerta de pânico

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| PAN-01 | 7 tipos (incêndio, criança perdida, enchente, roubo, polícia, violência doméstica, ambulância) | Must | `PanicAlertController` |
| PAN-02 | Confirmação slide-to-confirm | Must | Frontend |
| PAN-03 | Notificação moradores + síndico/admin | Must | `SendPanicAlert` job |
| PAN-04 | Canais: database, e-mail, WhatsApp, push | Must | Multi-canal |
| PAN-05 | Registro IP, user-agent, timestamp | Must | Model `PanicAlert` |
| PAN-06 | Gestão síndico (resolver/confirmar) | Must | `panic-alerts/` |
| PAN-07 | App mobile Expo | Must | `celular/CondoCenterMobile` |

### 8.16 Dashboard

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| DSH-01 | Dashboard personalizado por perfil | Must | `DashboardController` |
| DSH-02 | KPIs financeiros, inadimplência, reservas, encomendas | Must | Partials por perfil |
| DSH-03 | Atalhos morador (liberar visitante, OS, ocorrências) | Should | `morador-quick-actions` |
| DSH-04 | Pendências síndico (ocorrências, OS, fechamento) | Should | `sindico-*` partials |
| DSH-05 | Card restrição inadimplente | Should | `defaulter-restriction-card` |

### 8.17 Assinatura SaaS (síndico)

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| SUB-01 | Visualizar status da assinatura do condomínio | Must | `SyndicSubscriptionController` |
| SUB-02 | Pagar via PIX/boleto/cartão | Must | `SyndicSubscriptionPaymentService` |
| SUB-03 | Alterar forma de pagamento | Should | Views `syndic-subscription/` |
| SUB-04 | Período de trial conforme plano | Must | `CondominiumSubscriptionService` |
| SUB-05 | Bloqueio de módulos se inadimplente com plataforma | Must | `ensure.saas.subscription` |

---

## 9. Landing page pública

### 9.1 Visão geral

Cada condomínio pode publicar uma **landing page** acessível sem login, servindo como portal de informações para moradores, visitantes e prospects. Gerenciada pelo síndico na área autenticada (módulo `communication`, permissão `manage_landing_page`).

### 9.2 URLs de acesso

| Modo | URL | Middleware |
|------|-----|------------|
| Slug padrão | `/c/{slug}` | Rota `condominium.landing` |
| Domínio customizado | `https://meucondominio.com.br/` (raiz) | `ResolveCondominiumLandingDomain` |

### 9.3 Templates visuais

| Template | Slug | View | Descrição |
|----------|------|------|-----------|
| **Clássica** | `classic` | `landing.show` | Hero panorâmico, carrosséis, seções editoriais modulares |
| **Connect** | `connect` | `landing.connect` | Card hero, grids, visual inspirado no Condo Connect Hub |

- Seleção: Landing Page → aba **Geral** → **Modelo visual**
- Coluna DB: `condominium_landing_pages.template` (default `classic`)
- Mesmos campos de conteúdo servem ambos os templates

### 9.4 Configurações editáveis pelo síndico

**Aba Geral**
- Título, subtítulo, tagline, cor de destaque
- Imagem principal e galeria do hero
- Sobre (título + texto)
- Contato (telefone, e-mail, WhatsApp)
- Slug da URL, publicação (rascunho/publicado)
- Template visual

**Aba Conteúdos** — itens ordenáveis (`CondominiumLandingItem`):

| Tipo | Slug | Uso |
|------|------|-----|
| Aviso | `notice` | Comunicados importantes |
| Notícia | `news` | Novidades do condomínio |
| Evento | `event` | Agenda com data/hora |
| Obra | `construction` | Fases de obras (metadata: fase, progresso %) |
| Galeria | `gallery` | Fotos (múltiplas imagens) |
| Popup | `popup` | Modal na entrada (período configurável) |
| Bloco custom | `custom` | Seção livre |

**Aba Integrações** (feeds opcionais)
- Caronas recentes (`show_rides_feed`)
- Marketplace recente (`show_marketplace_feed`)
- Novidades da plataforma SindCon (`show_platform_news`)
- Comunicados oficiais (`show_announcements_feed`)

**Aba Domínio & QR**
- Domínio customizado (`custom_domain`)
- QR Code (visualizar e baixar PNG/SVG)

### 9.5 Recursos técnicos

- Serviço: `CondominiumLandingService::buildPublicPayload()` + `enrichPublicViewData()`
- Imagens servidas via URL relativa `/storage/...` (`PublicAssetUrl`) — compatível com domínio customizado
- Hero: fallback automático para fotos da galeria quando hero vazio
- Modais: notícias (lightbox de conteúdo), galeria (navegação entre fotos)
- Popups: controle por localStorage (não repetir após fechar)
- Assets Vite: `landing.css/js` (classic), `landing-connect.css/js` (connect)

### 9.6 Requisitos funcionais landing

| ID | Requisito | Prioridade |
|----|-----------|------------|
| LND-01 | Página pública por slug | Must |
| LND-02 | Dois templates selecionáveis | Must |
| LND-03 | CRUD de conteúdos por tipo | Must |
| LND-04 | Domínio customizado | Should |
| LND-05 | QR Code download | Should |
| LND-06 | Feeds integrados (caronas, marketplace, SindCon) | Should |
| LND-07 | Popups configuráveis | Should |
| LND-08 | CTA login moradores + footer contato | Must |
| LND-09 | Responsivo mobile | Must |
| LND-10 | Deep link para login com redirect | Should |

---

## 10. Regras de negócio globais

| # | Regra |
|---|-------|
| RN-01 | Uma unidade possui **um morador responsável** e **N agregados** |
| RN-02 | Morador e Agregado **não coexistem** no mesmo usuário |
| RN-03 | Agregados exigem `morador_vinculado_id` |
| RN-04 | Administrador e Porteiro **não exigem** unidade |
| RN-05 | Demais perfis **exigem** unidade vinculada |
| RN-06 | Apenas **Administrador** atribui/remove perfil Administrador |
| RN-07 | **Síndico** atribui Síndico e Conselho Fiscal |
| RN-08 | Assinatura SaaS **ativa** obrigatória para módulos do condomínio |
| RN-09 | `restrict_defaulters` bloqueia marketplace, reservas, OS, caronas e voto em assembleias |
| RN-10 | Moradores e Conselho Fiscal têm **transparência financeira total** |
| RN-11 | Auto-cadastro: código + **aprovação síndico** → `is_active = true` |
| RN-12 | Marketplace: máx. **3 imagens**; JPG/PNG/WEBP até 5 MB |
| RN-13 | Livro de Ocorrências **sigiloso**; público não identifica autor |
| RN-14 | Operações críticas auditadas (**Laravel Auditing**) |
| RN-15 | Pagamentos via **Asaas** exclusivamente |
| RN-16 | Módulo desligado em `enabled_modules` → **403** em rotas e ocultação no menu |
| RN-17 | `enabled_modules = null` → todos os módulos **ligados** |
| RN-18 | Permissões avaliadas pelo **perfil ativo**, não pela união de todos os papéis |
| RN-19 | Landing publicada (`is_published`) obrigatória para URL pública e QR |
| RN-20 | URLs de mídia pública usam caminho relativo `/storage/` para funcionar em qualquer host |

---

## 11. Permissões e controle de acesso

### 11.1 Sistema Spatie

- **~60 permissões** granulares em `RolesAndPermissionsSeeder`
- **8 papéis:** Administrador, Síndico, Morador, Porteiro, Conselho Fiscal, Secretaria, Agregado (+ perfil ativo)
- Agrupamento: condomínios, usuários, financeiro, reservas, marketplace, portaria, assembleias, comunicação, pânico, landing

### 11.2 Policies registradas

`ChargePolicy`, `FinePolicy`, `TransactionPolicy`, `ReservationPolicy`, `RecurringReservationPolicy`, `UnitPolicy`, `UserPolicy`, `PetPolicy`, `ServiceOrderPolicy`, `OccurrenceBookEntryPolicy`, `CondominiumPolicy`

### 11.3 Agregados

- Tabela `agregado_permissions`: módulo + nível (`view` | `crud`)
- Chaves: `spaces`, `marketplace`, `pets`, `notifications`, `packages`, `messages`, `rides`, `financial`
- Validação adicional: `SidebarHelper::canAccessModule()`
- Marketplace agregados: flag `marketplace_allow_agregados` no condomínio

### 11.4 Middlewares

| Middleware | Função |
|------------|--------|
| `auth`, `verified` | Autenticação |
| `check.password` | Troca de senha obrigatória |
| `check.profile` | Perfil ativo selecionado |
| `require.condominium` | Condomínio ativo |
| `ensure.saas.subscription` | Assinatura SaaS |
| `condominium.module:{slug}` | Módulo habilitado |
| `check.module.access:{slug}` | Módulo + permissão + inadimplência |
| `ensure.full.financial` | Modo financeiro completo |
| `restrict.defaulters` | Bloqueio inadimplentes |
| `ResolveCondominiumLandingDomain` | Landing em domínio customizado |

---

## 12. Integrações externas

### 12.1 Asaas (único gateway de pagamento)

| Escopo | Serviço | Webhook |
|--------|---------|---------|
| Plataforma SaaS | `PlatformAsaasService` | `POST /webhooks/asaas/platform` |
| Condomínio (legado) | `AsaasService` | `POST /webhooks/asaas` |
| Condomínio (por ID) | `AsaasService` | `POST /webhooks/asaas/condominium/{id}` |

**Métodos:** boleto, PIX, cartão de crédito  
**Usos:** taxas condominiais, reservas pagas, multas, OS, assinatura SaaS  
**Config:** painel plataforma + `condominiums/{id}/settings/receiving`  
**Env fallback:** `ASAAS_API_KEY`, `ASAAS_SANDBOX`, `ASAAS_WEBHOOK_*`

### 12.2 WhatsApp — Evolution API

| Escopo | Configuração |
|--------|--------------|
| Plataforma | `config/whatsapp.php`, painel Plataforma |
| Condomínio | Instância própria (`evolution_api_url`, `key`, `instance`) |

**Grupos configuráveis:** access, panic, packages, reservations, charges, conversations, rides, service_orders, occurrence_book, subscription, registration, assemblies, general

**Env fallback:** `WHATSAPP_ENABLED`, `EVOLUTION_*`, `WHATSAPP_DEFAULT_COUNTRY_CODE`

### 12.3 Outras integrações

| Integração | Finalidade |
|------------|------------|
| **Laravel Sanctum** | API REST (stateful + token) |
| **DomPDF** | PDFs (multas, recibos, relatórios, histórico) |
| **Maatwebsite Excel** | Exportações financeiras e ocorrências |
| **SimpleSoftwareIO QRCode** | QR pets, landing, moradores |
| **Intervention Image** | Processamento de imagens |
| **Firebase** | Push mobile (pânico) |
| **ViaCEP** | Autocomplete de endereço |
| **OwenIt Auditing** | Trilha de auditoria |
| **AWS S3** | Storage opcional (`FILESYSTEM_DISK`) |
| **Redis/Predis** | Cache e filas (opcional) |

**Stripe:** não implementado.

---

## 13. Canais de entrega (Web, API, Mobile)

### 13.1 Web (Blade + Bootstrap 5 + Vite)

- Interface principal para todos os perfis
- Sidebar dinâmica (`SidebarHelper`) por perfil + módulos + permissões
- SPAs parciais (Vue): reservas, encomendas, assembleias, mensagens, notificações
- DataTables (Yajra) para listagens server-side
- Landing pages públicas (classic + connect)

### 13.2 API REST (`/api`)

- Autenticação: Sanctum (`auth:sanctum`)
- Middlewares: `require.condominium`, `ensure.saas.subscription`
- Health: `GET /api/health`
- Laravel health: `GET /up`

#### Cobertura API por módulo

| Módulo | Endpoints principais | Gate |
|--------|---------------------|------|
| Cobranças | CRUD, bulk, generate-asaas | `financial` |
| Transações/Relatórios | CRUD, receipts, financial/defaulters/balance/cash-flow | `financial` + full |
| Reservas | availability, CRUD, approve/reject, confirm-payment | `spaces` |
| Encomendas | summary, search, CRUD, collect | `packages` |
| Marketplace | CRUD | `marketplace` |
| Assembleias | CRUD, vote, lifecycle, minutes export | `assemblies` |
| Mensagens/Conversas | CRUD, syndic, export | `communication` |
| Notificações | index, read, unread-count | — |
| Espaços | CRUD | `spaces` |
| Pets | CRUD | `pets` |
| Caronas | rides + bookings | `rides` |
| Controle acesso | porteiro, authorizations, lists, providers, movements | `access_control` |
| Usuários | search | — |
| Créditos | GET /user/credits | — |

#### Somente Web (sem API dedicada)

Multas, taxas (FeeController), fechamento mensal, contas bancárias/conciliação (parcial), funcionários, prestação de contas upload, ordens de serviço, livro de ocorrências, regimento interno, landing admin, gestão plataforma, auto-cadastro, checkout web, reservas recorrentes, histórico usuário, configurações condomínio.

### 13.3 App mobile (Expo/React Native)

- **Escopo atual:** autenticação Sanctum + alerta de pânico + push Firebase
- **Caminho:** `celular/CondoCenterMobile`
- **Documentação:** `DOCUMENTAÇÃO/README_MOBILE.md`

### 13.4 Matriz módulo × canal

| Módulo | Web | API | Mobile |
|--------|-----|-----|--------|
| Financeiro (cobranças) | ✅ | ✅ | ❌ |
| Financeiro (taxas/multas/fechamento) | ✅ | ❌/Parcial | ❌ |
| Reservas | ✅ | ✅ | ❌ |
| Marketplace | ✅ | ✅ | ❌ |
| Caronas | ✅ | ✅ | ❌ |
| Encomendas | ✅ | ✅ | ❌ |
| Controle de acesso | ✅ | ✅ | ❌ |
| Assembleias | ✅ | ✅ | ❌ |
| Comunicação | ✅ | ✅ | ❌ |
| Pets | ✅ | ✅ | ❌ |
| Pânico | ✅ | Parcial | ✅ |
| Ordens de serviço | ✅ | ❌ | ❌ |
| Livro de ocorrências | ✅ | ❌ | ❌ |
| Regimento interno | ✅ | ❌ | ❌ |
| Landing page | ✅ Pública | ❌ | ❌ |
| Plataforma SaaS | ✅ | ❌ | ❌ |
| Módulos toggle | ✅ | ❌ | ❌ |

---

## 14. Modelo comercial (SaaS)

### 14.1 Planos disponíveis

| Plano | Métrica | Ciclo | Preço referência |
|-------|---------|-------|------------------|
| Essencial — por unidade | Unidade | Mensal | R$ 4,90/unidade |
| Profissional — por unidade | Unidade | Trimestral | R$ 4,50/unidade |
| Corporativo — por usuário | Usuário ativo | Anual | R$ 2,90/usuário |
| Fixo — Mensal | Valor fixo | Mensal | R$ 299,90 |
| Fixo — Trimestral | Valor fixo | Trimestral | R$ 849,90 |
| Fixo — Anual | Valor fixo | Anual | R$ 3.199,90 |

### 14.2 Período de trial

| Plano | Trial |
|-------|-------|
| Essencial | 14 dias |
| Profissional / Fixo mensal e trimestral | 7 dias |
| Fixo anual | 14 dias |
| Corporativo | Sem trial |

### 14.3 Formas de pagamento SaaS

- Boleto (Essencial, Profissional, Fixo mensal/trimestral)
- Cartão de crédito (Corporativo, Fixo anual)
- PIX (via fluxo síndico `SyndicSubscriptionPaymentService`)

### 14.4 Impacto da assinatura

- Inativa → `ensure.saas.subscription` bloqueia módulos
- Grace period configurável: `SAAS_GRACE_DAYS` (`config/saas.php`)
- Síndico regulariza em `/minha-assinatura/*`

---

## 15. Operação em produção

Referência canônica: `DOCUMENTAÇÃO/INSTALACAO_VPS.md`

### 15.1 Requisitos de infraestrutura

| Componente | Obrigatório | Função |
|------------|-------------|--------|
| PHP 8.3+ | Sim | Runtime |
| MySQL | Sim | Banco principal |
| Nginx/Apache | Sim | Web server |
| Cron | Sim | `php artisan schedule:run` |
| Queue worker | Sim | Jobs assíncronos (WhatsApp, Asaas, lembretes) |
| Supervisor | Recomendado | Manter worker ativo |
| `storage:link` | Sim | Arquivos públicos (`/storage`) |
| SSL | Sim | HTTPS produção |

### 15.2 Scheduler (`routes/console.php`)

| Horário | Comando | Função |
|---------|---------|--------|
| Diário 05:00 | `fees:generate-upcoming` | Gera cobranças de taxas |
| Diário 06:30 | `charges:settle-payroll` | Liquida cobranças folha |
| Diário 07:00 | `charges:mark-overdue` | Marca vencidas |
| Diário 08:00 | `charges:send-reminders` | Lembretes pré-vencimento |
| Diário 09:00 | `charges:check-overdue` | Inadimplência + notificações |
| Mensal dia 1, 08:00 | `reports:generate-monthly` | Relatórios mensais |
| Hourly | `reservations:cancel-expired-prereservations` | Cancela pré-reservas |
| Semanal | Closure | Limpa notificações lidas > 30 dias |

### 15.3 Jobs assíncronos relevantes

`GenerateAsaasPayment`, `GenerateReservationPayment`, `SendWhatsAppNotification`, `SendPanicAlert`, `SendAccessNotification`, `SendChargeReminders`, `SendOverdueReminders`, `SendSubscriptionBillingNotification`

**Fila padrão:** `QUEUE_CONNECTION=database`

### 15.4 Variáveis de ambiente (produção)

| Variável | Uso |
|----------|-----|
| `APP_*` | Core Laravel |
| `DB_*` | Banco de dados |
| `QUEUE_CONNECTION` | Fila (database ou redis) |
| `SESSION_DRIVER` | Sessões |
| `MAIL_*` | E-mail transacional |
| `ASAAS_*` | Fallback gateway plataforma |
| `WHATSAPP_*`, `EVOLUTION_*` | Fallback WhatsApp plataforma |
| `SAAS_ENFORCE_SUBSCRIPTION`, `SAAS_GRACE_DAYS` | Bloqueio assinatura |
| `SAAS_WEBHOOK_BASE_URL` | URL base webhooks |
| `DEV_DOCS_TOKEN` | Tutorial VPS (vazio = desligado) |
| `AWS_*` | S3 opcional |
| `TRUSTED_PROXIES` | Behind reverse proxy |

**Nota:** credenciais Asaas/WhatsApp por condomínio ficam no **banco/painel**, não apenas no `.env`.

---

## 16. Modelo de dados (visão geral)

```
Plataforma
├── SubscriptionPlan
├── PlatformSetting / PlatformAnnouncement
└── Condominium
    ├── Unit (1:N) → User (morador/agregado)
    ├── Users ↔ syndics (N:N condominium_user)
    ├── enabled_modules, financial_mode, payment_receiving_mode
    ├── CondominiumSubscription → logs, documents
    ├── CondominiumLandingPage → CondominiumLandingItem[]
    │
    ├── Financeiro
    │   ├── Fee → Charge → Payment
    │   ├── Fine → FineRecipient
    │   ├── Transaction → Receipt
    │   ├── BankAccount → Reconciliation
    │   ├── Employee → EmployeeFinancialEntry
    │   ├── MonthlyClosing → StepConfirmation
    │   └── AccountabilityReportUpload
    │
    ├── Espaços: Space → Reservation, RecurringReservation, UserCredit
    │
    ├── Comunicação: Message, Conversation, Notification, OccurrenceBookEntry
    │
    ├── Operacional
    │   ├── Package, AccessAuthorization, AccessMovement, ServiceProvider
    │   ├── ServiceOrder → Items, Messages
    │   ├── Pet, PanicAlert
    │   ├── MarketplaceItem, Ride → RideBooking
    │   ├── Assembly → Items, Votes, Attachments
    │   └── InternalRegulation → History
    │
    └── Auxiliar: AgregadoPermission, UserActivityLog
```

**Conceitos transversais:** soft deletes, auditing (OwenIt), scoping por `condominium_id`, uploads em `storage/app/public/`.

---

## 17. Requisitos não funcionais

### 17.1 Performance

- Paginação server-side (DataTables)
- Jobs assíncronos para conciliação, WhatsApp, Asaas
- Cache Redis opcional

### 17.2 Segurança

- E-mail verificado, senhas bcrypt
- Credenciais Asaas criptografadas
- CSRF em formulários web
- Policies por recurso sensível
- Auditoria em models críticos
- IP/user-agent em alertas de pânico
- Canais sigilosos com policy rigorosa

### 17.3 Disponibilidade

| Ambiente | Uso |
|----------|-----|
| `local` | Desenvolvimento (Laragon) |
| `testing` | PHPUnit |
| `production` | VPS |

### 17.4 Conformidade (LGPD)

- Dados pessoais (CPF, telefone, e-mail) com controle de acesso
- Canais sigilosos documentados
- Alterações de schema: aprovação, backup, migrations reversíveis (`REGRAS_PROJETO.md`)

### 17.5 Usabilidade

- Interface responsiva (desktop + mobile web)
- Menu lateral por perfil
- Feedback visual (spinners, barras de progresso encomendas)
- Slide-to-confirm no pânico
- Dashboard contextual por papel

### 17.6 Manutenibilidade

- Padrão Laravel: Services, Requests, Policies, Resources
- Módulos desacopláveis via `enabled_modules`
- Documentação extensa em `DOCUMENTAÇÃO/`
- Seeders demo: Residencial Vista Verde

---

## 18. Jornadas principais

### 18.1 Morador paga taxa condominial

1. Acessa "Minhas Cobranças" (`/minhas-cobrancas`)
2. Visualiza pendentes/atrasadas
3. Escolhe PIX, boleto ou cartão
4. `ChargePaymentService` gera pagamento Asaas
5. Webhook confirma → status atualizado
6. Notificação de confirmação (in-app/e-mail/WhatsApp)

### 18.2 Síndico gerencia inadimplência

1. Painel adimplência (`finance/status`)
2. Identifica unidades em atraso
3. Envia lembretes (cron + manual)
4. Ativa `restrict_defaulters` se desejado
5. Inadimplente perde marketplace, reservas, OS, caronas, voto

### 18.3 Síndico configura módulos do condomínio

1. Gestão → Meu Condomínio → Módulos
2. Desliga módulos não utilizados (ex.: caronas)
3. Menu e rotas refletem imediatamente
4. Moradores recebem 403 ao acessar módulo desligado

### 18.4 Síndico publica landing page

1. Comunicação → Landing Page
2. Escolhe template (Clássica ou Connect)
3. Preenche hero, sobre, contato
4. Adiciona conteúdos (avisos, notícias, galeria)
5. Ativa integrações (caronas, marketplace, novidades SindCon)
6. Publica → `/c/{slug}` ou domínio customizado
7. Baixa QR Code para impressão

### 18.5 Morador aciona pânico

1. Clica "Alerta de Pânico" (web ou app)
2. Seleciona tipo (7 opções)
3. Slide-to-confirm
4. Notificação multi-canal
5. Síndico gerencia até resolução

### 18.6 Porteiro registra encomenda

1. Módulo Encomendas
2. Registra chegada (unidade, remetente)
3. Morador notificado
4. Retirada presencial com código
5. Status concluído

### 18.7 Auto-cadastro de morador

1. `/register` → código do condomínio
2. Escolhe Morador ou Agregado
3. Preenche dados + unidade
4. Status `pending` até aprovação
5. Síndico aprova → acesso liberado
6. WhatsApp opcional ao síndico

### 18.8 Síndico executa fechamento mensal

1. Financeiro → Fechamento mensal
2. Percorre checklist (9 passos)
3. Confirma cada etapa
4. Encerra competência ou reabre se necessário

### 18.9 Síndico regulariza assinatura SaaS

1. Middleware bloqueia módulos
2. Acessa `/minha-assinatura`
3. Paga via PIX/boleto/cartão
4. Webhook plataforma confirma
5. Acesso restaurado

### 18.10 Morador registra ocorrência

1. Livro de Ocorrências → nova
2. Tipo, descrição, foto opcional
3. Síndico notificado
4. Ciência e comentário
5. Exposição pública opcional (sem autor)

---

## 19. Cobertura de testes e qualidade

### 19.1 Suites existentes (`tests/`)

| Arquivo | Cobertura |
|---------|-----------|
| `FeeServiceTest.php` | Taxas, cobranças, folha, artisan |
| `FeeChargeCoverageTest.php` | Cobertura unidades + fechamento |
| `ChargeDueDateTest.php` | Vencimentos |
| `AssemblyWorkflowTest.php` | Assembleias end-to-end |
| `CondominiumModulesTest.php` | Toggle módulos, 403 |
| `PackageManagementTest.php` | Encomendas |
| `MarketplaceModuleTest.php` | Agregados marketplace |
| `TransactionTest.php` | Transações + isolamento tenant |
| `AuthenticationTest.php` | Login/logout |
| `NotificationRedirectTest.php` | Deep links |

### 19.2 Lacunas de QA (documentar no roadmap)

Sem testes automatizados dedicados para: WhatsApp/Evolution, webhooks Asaas, fechamento mensal E2E, controle de acesso, caronas, landing page, auto-cadastro, assinatura SaaS, exportações PDF, porteiro, multitenancy admin.

---

## 20. Fora de escopo e roadmap

### 20.1 Gaps conhecidos (prioridade)

| Item | Prioridade | Descrição |
|------|------------|-----------|
| API Ordens de Serviço | Alta | Paridade web/API |
| API Livro de Ocorrências | Alta | App mobile futuro |
| API Regimento Interno | Média | Consulta mobile |
| API Taxas/Multas/Fechamento | Média | Financeiro mobile |
| App mobile completo | Média | Além do pânico |
| Unificação web/API comunicação | Média | Reduzir duplicação |
| Testes integração Asaas/WhatsApp | Alta | Confiabilidade produção |
| Preview visual templates landing | Baixa | UX síndico |
| Integração Stripe | Baixa | Asaas atende BR |
| IoT / CFTV | Baixa | Fora do core |

### 20.2 Evoluções sugeridas

- Matriz formal **papel × módulo × canal** mantida automaticamente
- Onboarding guiado novos condomínios
- Dashboard analítico admin plataforma
- NPS integrado
- Paridade API ≥ 80% dos módulos web
- Documentação OpenAPI/Swagger da API

---

## 21. Glossário

| Termo | Definição |
|-------|-----------|
| **Tenant** | Condomínio isolado na plataforma |
| **Morador responsável** | Usuário principal da unidade |
| **Agregado** | Dependente vinculado ao morador |
| **Perfil ativo** | Papel em uso na sessão (`active_role`) |
| **Módulo habilitável** | Feature flag por condomínio (`enabled_modules`) |
| **Modo financeiro simplificado** | Upload prestação, sem caixa completo |
| **Modo financeiro completo** | Caixa, conciliação, fechamento mensal |
| **Inadimplente** | Unidade com cobranças vencidas não pagas |
| **Asaas** | Gateway BR (boleto, PIX, cartão) |
| **Evolution API** | Integração WhatsApp |
| **Template landing** | `classic` ou `connect` |
| **Slide-to-confirm** | Confirmação deslizante (pânico) |
| **Livro sigiloso** | Ocorrência visível só autor + síndico |
| **Fechamento mensal** | Checklist de encerramento contábil |
| **PlatformAnnouncement** | Novidade global SindCon (feed landing) |

---

## 22. Referências

### Documentação interna

| Documento | Conteúdo |
|-----------|----------|
| [INSTALACAO_VPS.md](INSTALACAO_VPS.md) | Instalação, cron, deploy, changelog |
| [README.md](README.md) | Índice geral |
| [PROJETO_SUMMARY.md](PROJETO_SUMMARY.md) | Resumo técnico |
| [FUNCIONALIDADES.md](FUNCIONALIDADES.md) | Lista detalhada |
| [API_DOCUMENTATION.md](API_DOCUMENTATION.md) | API REST |
| [MENU_POR_PERFIL.md](MENU_POR_PERFIL.md) | Menus por perfil |
| [SIDEBAR_PERMISSIONS.md](SIDEBAR_PERMISSIONS.md) | Permissões sidebar |
| [PERMISSOES_FINANCEIRAS.md](PERMISSOES_FINANCEIRAS.md) | Transparência financeira |
| [README_MOBILE.md](README_MOBILE.md) | App mobile |
| [REGRAS_PROJETO.md](REGRAS_PROJETO.md) | Governança de banco |

### Módulos específicos

| Documento | Módulo |
|-----------|--------|
| [SISTEMA_RESERVAS.md](SISTEMA_RESERVAS.md) | Reservas |
| [SISTEMA_PETS.md](SISTEMA_PETS.md) | Pets |
| [SISTEMA_ENCOMENDAS.md](SISTEMA_ENCOMENDAS.md) | Encomendas |
| [SISTEMA_REGIMENTO_INTERNO.md](SISTEMA_REGIMENTO_INTERNO.md) | Regimento |
| [SISTEMA_VERIFICACAO_QR_CODE_PETS.md](SISTEMA_VERIFICACAO_QR_CODE_PETS.md) | QR pets |
| [IMPLEMENTACAO_UNIDADES_USUARIOS.md](IMPLEMENTACAO_UNIDADES_USUARIOS.md) | Unidades/usuários |

### Código-fonte canônico

| Área | Caminho |
|------|---------|
| Módulos habilitáveis | `app/Support/CondominiumModules.php` |
| Permissões | `database/seeders/RolesAndPermissionsSeeder.php` |
| Rotas web | `routes/web.php` |
| Rotas API | `routes/api.php` |
| Scheduler | `routes/console.php` |
| Landing | `app/Services/CondominiumLandingService.php` |
| SaaS config | `config/saas.php` |

### Ambiente demo

| Campo | Valor |
|-------|-------|
| Condomínio | Residencial Vista Verde |
| Admin plataforma | `admin@condomanager.com` |
| Síndico | `sindico@vistaverde.com` |
| Morador | `morador1@example.com` |
| Porteiro | `porteiro@vistaverde.com` |
| Senha padrão | `password` |

---

*Documento v2.0 — atualizado com base no estado do repositório CondoCenter em 12/09/2026. Reflete módulos habilitáveis, landing dual-template, fechamento mensal, camada de serviços e matriz de canais. Para alterações de escopo, revisar com stakeholders e incrementar a versão deste PRD.*
