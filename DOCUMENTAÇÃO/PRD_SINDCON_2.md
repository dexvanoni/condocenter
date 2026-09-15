# PRD — SindCON (CondoCenter)

**Product Requirements Document**

| Campo | Valor |
|-------|-------|
| **Produto** | SindCON — Plataforma SaaS de Gestão Condominial |
| **Repositório** | CondoCenter |
| **Versão do documento** | 2.2 |
| **Data** | 15/09/2026 |
| **Status** | Em produção / evolução contínua |
| **Stack** | Laravel 12, PHP 8.3+, MySQL, Bootstrap 5, Vue 3, Vite, Sanctum, Spatie Permission |
| **Integrações** | Asaas (pagamentos), Evolution API (WhatsApp), Firebase (push mobile), Tesseract OCR (encomendas), BaconQrCode + GD (QR visitante), @zxing/library (scan portaria) |

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
| **Síndico / administração** | Finanças (modo completo ou simplificado), usuários, reservas, portaria, assembleias, landing page, fechamento mensal, toggles de módulos, relatório completo de movimentações de encomendas com exportação |
| **Morador** | Autosserviço (pagamentos, reservas, marketplace, pets), transparência financeira, canal de emergência, auto-cadastro com aprovação |
| **Porteiro** | Registro de acessos, encomendas (OCR + retirada por senha), liberações (presets + **visitante "Outro" via QR/senha**), verificação de pets |
| **Conselho fiscal** | Visibilidade financeira, exportações, participação em assembleias |
| **Visitante / público** | Landing page do condomínio com avisos, eventos, galeria e QR Code |

### 1.3 Posicionamento

- **Segmento:** condomínios residenciais (pequenos a grandes) e administradoras
- **Modelo:** B2B2C — plataforma vende ao condomínio; moradores são usuários finais
- **Diferenciais:** ecossistema integrado (financeiro + operacional + comunicação + pânico), WhatsApp nativo (Evolution API), **encomenda inteligente com OCR de etiqueta e senha de retirada**, **visitante nomeado com PDF/QR e senha reutilizável na portaria**, marketplace e caronas internas, livro de ocorrências sigiloso, fechamento mensal guiado, landing page com domínio próprio e templates visuais

### 1.4 Componentes do repositório

| Componente | Caminho | Descrição |
|------------|---------|-----------|
| Backend / Web | Raiz do repo | Laravel — interface principal e API |
| App mobile | `celular/CondoCenterMobile` | Expo/React Native — pânico + push (Firebase) |
| Documentação | `DOCUMENTAÇÃO/` | PRD, VPS, módulos, API, regras |
| Assets frontend | `resources/js`, `resources/css` | Vite — app, reservas Vue, encomendas (intake/pickup), **acesso porteiro (check-in QR/senha)**, landing classic/connect |

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
| **Tempo médio retirada encomenda** | Horas entre `received_at` e `collected_at` |
| **Taxa OCR com confiança alta** | % de leituras com matching ≥ 90% sem correção manual |
| **Taxa entrega WhatsApp encomendas** | % com `whatsapp_delivery_status = sent` |
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
- **Necessidades:** pagar taxas, reservar espaços, marketplace, pets, votar, acionar pânico, registrar ocorrências, **liberar visitantes** (presets e visitante nomeado com PDF/QR)
- **Regra:** uma unidade possui um morador responsável
- **Dashboard:** `dashboard/morador.blade.php`

#### P4 — Agregado
- **Quem:** dependente vinculado ao morador (cônjuge, filho, empregada etc.)
- **Necessidades:** acesso limitado conforme `AgregadoPermission` (view/crud por módulo)
- **Regra:** vinculado via `morador_vinculado_id`
- **Dashboard:** `dashboard/agregado.blade.php`

#### P5 — Porteiro
- **Quem:** equipe de portaria / controle de acesso
- **Necessidades:** registrar entradas, encomendas (OCR + retirada por senha), liberações (presets manuais + **check-in QR/senha de visitante "Outro"**), verificar pets
- **Interfaces principais:** `dashboard/porteiro.blade.php`, `/access-control/porteiro` (painel + liberação rápida), `/packages`, `/packages/intake`, `/packages/pickup`
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
- Controle de acesso / portaria (liberações, listas, prestadores, movimentos, proibições, **visitante nomeado com PDF/QR + senha reutilizável**)
- Encomendas inteligentes (OCR de etiqueta, matching de destinatário, senha de retirada, WhatsApp, relatório síndico)
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
- **Operacional:** `AccessControlService` (liberações, credencial visitante "Outro", check-in QR/senha), `PackageService`, `RideBookingService`, `ServiceOrderService`, `OccurrenceBookService`
- **Encomendas (OCR/matching):** `OcrServiceInterface`, `TesseractOcrService`, `LabelImagePreprocessor`, `PackageRecipientMatcher`, `PackageSenderDetector`, `TextNormalizer`
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
| `packages` | Encomendas | OCR de etiqueta, registro, senha de retirada, WhatsApp, relatório síndico |
| `access_control` | Controle de acesso | Portaria, liberações, QR/senha visitante, relatórios |
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

O módulo `access_control` cobre liberações na portaria em três modalidades: **presets rápidos** (Uber, iFood, entregador etc.), **visitante nomeado ("Outro")** com credencial digital (PDF + QR Code + senha de 4 dígitos) e **listas/prestadores/proibições**. O visitante "Outro" possui fluxo dedicado de check-in automático na portaria, distinto do registro manual ENTROU/NEGADO dos presets.

#### 8.9.1 Visão geral — tipos de liberação

| Tipo | Quem cria | Validade | Credencial | Check-in porteiro |
|------|-----------|----------|------------|-----------------|
| **Preset** (Uber, iFood…) | Morador/agregado | Opcional (default 24h após entrada prevista) | Nenhuma | Card no painel → ENTROU / NEGADO |
| **Outro** (visitante nomeado) | Morador/agregado | **Obrigatória** (`valid_until`) | Senha WhatsApp + PDF com QR | Liberação rápida (scan ou senha) |
| **Lista de evento** | Morador/agregado | Por evento | Nenhuma | Modal de convidados |
| **Prestador** | Morador/síndico | Contrato | Nenhuma | Card prestador → ENTROU |
| **Proibição** | Morador/porteiro | Configurável | Nenhuma | Alertar morador |

#### 8.9.2 Visitante "Outro" — credencial digital

**Objetivo:** quando o morador libera um visitante com nome próprio (tipo **Outro**), o sistema gera credenciais digitais válidas até a data/hora informada. O visitante pode **entrar e sair quantas vezes quiser** nesse período; após a expiração, senha e QR são invalidados automaticamente.

**Fluxo morador**

1. Acessa **Controle de Acesso** (`/access-control`)
2. Seleciona preset **Outro**, informa nome do visitante
3. Informa **entrada prevista** e **validade da liberação** (obrigatória)
4. Sistema cria `AccessAuthorization` com `visitor_preset_key = other`
5. Gera `access_pin_hash` (bcrypt) e `qr_token` (único)
6. Envia **senha de 4 dígitos** ao morador via WhatsApp (`access_visitor_credential`)
7. Exibe modal com download do **PDF** contendo QR Code
8. Morador encaminha PDF ao visitante

**Fluxo porteiro — liberação rápida**

| Rota | Interface | Ação |
|------|-----------|------|
| `/access-control/porteiro` | `AccessCheckinApp.vue` (Vue 3 + `@zxing/library`) | Botões **Escanear QR** e **Digitar senha** |
| `POST /api/access-control/check-in/qr` | API | Valida token do QR → registra entrada |
| `POST /api/access-control/check-in/pin` | API | Valida senha → registra entrada |

1. Visitante apresenta QR (PDF no celular) ou informa senha ao porteiro
2. Porteiro escaneia ou digita 4 dígitos na seção **Liberação rápida**
3. Sistema valida credencial ativa (não expirada, status `pending`)
4. Registra `AccessMovement` com `action = entered` e metadata `credential_reusable: true`
5. **Não altera** status da liberação para `entered` — credencial permanece ativa até `expires_at`
6. Notifica morador via WhatsApp (`access_entered`)
7. Tela de sucesso: "Portão liberado"

**Reutilização:** cada nova passagem na portaria repete o passo 4–6. A credencial só expira quando `expires_at` passa (`expireStaleRecords` → status `expired`) ou quando o morador cancela.

#### 8.9.3 PDF com QR Code

| Item | Detalhe |
|------|---------|
| Endpoint | `GET /api/access-control/authorizations/{id}/pdf` |
| Permissão | Morador autor, morador notificado, síndico ou admin |
| Conteúdo | Nome, unidade, entrada prevista, liberado por, QR Code, validade |
| Geração QR | `QRCodeHelper::generateForVisitorAccessPngBinary()` via GD + BaconQrCode Encoder |
| Render PDF | DomPDF (`access-control/visitor-credential-pdf.blade.php`) |
| Payload QR | JSON: `{"type":"visitor_access","token":"..."}` |

> **Nota técnica:** PNG via `simple-qrcode` exige Imagick; a implementação usa **GD nativo** para rasterizar a matriz do QR, garantindo compatibilidade com Laragon/VPS sem Imagick.

#### 8.9.4 Painel do porteiro

| Área | Função |
|------|--------|
| **Liberação rápida** (topo) | Scan QR + senha 4 dígitos — fluxo automático |
| **Grid de cards** | Todas as liberações pendentes, incluindo visitantes "Outro" (badge QR/Senha) |
| **Tabs** | Todos, Liberações, Proibidos, Listas, Prestadores |
| **Poll** | Atualização automática a cada 12s |

Visitantes com credencial digital aparecem no grid para visibilidade; ao tocar no card, modal orienta uso da liberação rápida (sem botões ENTROU/NEGADO).

#### 8.9.5 Modelo de dados — campos adicionais `access_authorizations`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `visitor_preset_key` | string | Slug do preset (`uber_99`, `ifood`…) ou `other` |
| `access_pin_hash` | string (hidden) | Hash bcrypt da senha de 4 dígitos |
| `qr_token` | string (hidden, unique) | Token para validação do QR Code |

Campos existentes reutilizados: `scheduled_at`, `valid_until`, `expires_at` (= `valid_until` ou scheduled + 24h), `status`, `processed_by/at` (último check-in).

#### 8.9.6 Notificações WhatsApp (grupo `access`)

| Evento | Tipo | Destinatário | Conteúdo principal |
|--------|------|--------------|-------------------|
| Liberação "Outro" criada | `access_visitor_credential` | Morador notificado + autor | Senha 4 dígitos, validade, link para PDF |
| Visitante entrou (check-in) | `access_entered` | Morador da unidade | Nome do visitante, unidade, horário |
| Acesso negado (preset) | `access_denied` | Morador | Nome, unidade |
| Proibição identificada | `access_prohibition_critical` | Morador | Alerta crítico |

Job: `SendVisitorAccessCredentialNotification` (criação) + `SendAccessNotification` (cada entrada).

#### 8.9.7 Regras de negócio específicas

| ID | Regra |
|----|-------|
| ACC-RN-01 | Preset "Outro" exige `valid_until` posterior à entrada prevista |
| ACC-RN-02 | Senha de 4 dígitos gerada automaticamente; armazenada apenas como hash |
| ACC-RN-03 | Códigos triviais bloqueados (0000, 1111, 1234…) |
| ACC-RN-04 | QR e senha válidos até `expires_at`; múltiplas entradas/saídas permitidas |
| ACC-RN-05 | Check-in por QR/senha **não** consome a liberação (status permanece `pending`) |
| ACC-RN-06 | Após expiração, `expireStaleRecords` marca `expired` — credencial inválida |
| ACC-RN-07 | Cada check-in registra `AccessMovement` e notifica morador |
| ACC-RN-08 | Entrada antecipada (antes de `scheduled_at`) permitida com metadata `early_entry` |
| ACC-RN-09 | Agregado pode criar se morador habilitou `agregado_can_authorize_access` |
| ACC-RN-10 | Matching de senha/QR restrito ao `condominium_id` do porteiro |
| ACC-RN-11 | HTTPS obrigatório em produção para scan de QR na portaria (`getUserMedia`) |

#### 8.9.8 Requisitos funcionais (tabela consolidada)

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| ACC-01 | Painel do porteiro (grid + poll) | Must | `access-control/porteiro` |
| ACC-02 | Liberações prévias morador/agregado | Must | `access-control/resident` |
| ACC-03 | Listas de autorização e prestadores | Should | API `access-control/lists`, `providers` |
| ACC-04 | Relatório movimentações (PDF) | Should | `access-control/reports` |
| ACC-05 | Proibições de acesso | Should | `AccessControlService::createProhibition` |
| ACC-06 | Alertas de acesso no dashboard | Should | `AccessAlertService` |
| ACC-07 | Visitante "Outro" com validade obrigatória | Must | `AccessControlService::createAuthorization` |
| ACC-08 | Senha 4 dígitos via WhatsApp na criação | Must | `SendVisitorAccessCredentialNotification` |
| ACC-09 | PDF com QR Code para download | Must | `generateVisitorCredentialPdf`, rota `/pdf` |
| ACC-10 | Credencial reutilizável até expiração | Must | `processAuthorizationByCredential` |
| ACC-11 | Check-in por QR (câmera) | Must | `AccessCheckinApp.vue`, `check-in/qr` |
| ACC-12 | Check-in por senha 4 dígitos | Must | `AccessCheckinApp.vue`, `check-in/pin` |
| ACC-13 | Notificação WhatsApp a cada entrada | Must | `SendAccessNotification` |
| ACC-14 | Presets rápidos (Uber, iFood…) sem credencial | Must | `INDIVIDUAL_VISITOR_PRESETS` |
| ACC-15 | Cancelamento de liberação pendente | Must | `cancelAuthorization` |
| ACC-16 | Expiração automática de credenciais | Must | `expireStaleRecords` |

#### 8.9.9 Testes automatizados

| Arquivo | Cobertura |
|---------|-----------|
| `VisitorAccessCredentialTest.php` | Criação "Outro", validade obrigatória, check-in PIN/QR, múltiplas entradas, expiração, PDF, painel porteiro |

### 8.10 Encomendas — módulo completo

O módulo de encomendas (`packages`) cobre o ciclo operacional da portaria — da chegada do volume à retirada pelo morador — com três pilares: **registro inteligente por leitura de etiqueta (OCR)**, **controle de retirada por senha de 4 dígitos** e **notificação automática via WhatsApp** (Evolution API). O síndico possui visão analítica completa com filtros e exportação PDF/Excel.

> **Restrição de produto:** o reconhecimento de destinatário **não utiliza IA generativa/LLM**. A identificação é baseada exclusivamente em OCR, normalização de texto, fuzzy matching e dados cadastrais do condomínio.

#### 8.10.1 Visão geral do fluxo

```
PORTEIRO                          SISTEMA                         MORADOR
   │                                 │                               │
   ├─ LER ETIQUETA (câmera) ────────►│ OCR + matching                │
   │                                 │ Confirmação humana            │
   ├─ CHEGOU ENCOMENDA ─────────────►│ Registro transacional         │
   │                                 ├─ Gera senha 4 dígitos        │
   │                                 ├─ Job WhatsApp ───────────────►│ Recebe senha
   │                                 │                               │
   ├─ RETIRADA (senha) ─────────────►│ Valida hash + registra        │
   │                                 │ Auditoria completa            │
   │                                 ├─ Job WhatsApp (retirada) ────►│ Confirmação
   │                                 │                               │
SÍNDICO ────────────────────────────►│ /packages/reports             │
                                      │ Filtros + PDF/Excel           │
```

#### 8.10.2 Encomenda Inteligente — leitura automática de etiqueta

**Objetivo:** permitir que o porteiro fotografe a etiqueta de encomendas (Correios, Shopee, Mercado Livre, Amazon, transportadoras etc.) pelo celular e o sistema sugira o destinatário entre os moradores do condomínio.

**Rotas e interfaces**

| Rota | Nome | Tecnologia | Público |
|------|------|------------|---------|
| `/packages/intake` | `packages.register` | Vue 3 (`PackageIntakeApp.vue`) | Porteiro |
| `POST /api/packages/label/preview` | `api.packages.label.preview` | API JSON | Porteiro |
| `POST /api/packages/label/confirm` | `api.packages.label.confirm` | API JSON | Porteiro |

**Fluxo funcional (5 etapas)**

1. **Entrada** — botões "Ler etiqueta" ou "Registrar manualmente"
2. **Câmera** — `getUserMedia()` com preferência `facingMode: "environment"` (câmera traseira); área visual de enquadramento; captura com crop da região da etiqueta
3. **Processamento** — pré-processamento de imagem + OCR + matching; feedback "Lendo etiqueta..."
4. **Confirmação** — apresenta candidato(s) com nível de confiança; porteiro confirma ou corrige
5. **Sucesso** — encomenda registrada; status de envio WhatsApp exibido

**Camada OCR (abstração)**

| Componente | Caminho | Função |
|------------|---------|--------|
| Interface | `OcrServiceInterface` | Contrato intercambiável (Tesseract, futuros engines) |
| Implementação prod | `TesseractOcrService` | CLI Tesseract com múltiplos PSM, seleção do melhor resultado |
| Implementação teste | `FakeOcrService` | Mock determinístico para testes |
| Pré-processamento | `LabelImagePreprocessor` | Resize, grayscale, sharpen; variantes múltiplas; preserva original |
| Config | `config/ocr.php` | `OCR_ENABLED`, `TESSERACT_PATH`, `OCR_LANG`, `OCR_TIMEOUT`, `OCR_PREPROCESS_ENABLED`, `OCR_MAX_DIMENSION` |

**Saída estruturada do OCR (`OcrResult` DTO)**

| Campo | Descrição |
|-------|-----------|
| `raw_text` | Texto bruto extraído |
| `confidence` | Confiança global (0–1) quando disponível |
| `tracking_code` | Código de rastreamento detectado |
| `possible_name` | Nome provável do destinatário |
| `possible_address` | Endereço parcial |
| `possible_unit` / `possible_block` | Unidade/bloco inferidos |
| `possible_sender` | Remetente (Correios, Shopee, ML etc.) |

**Complemento barcode/QR**

- Biblioteca `@zxing/library` no frontend para leitura opcional a partir do frame capturado
- Código de barras/QR enviado junto ao preview para enriquecer `tracking_code`
- Métodos de identificação registrados: `manual`, `ocr`, `barcode`, `hybrid`

**Normalização de texto (`TextNormalizer`)**

- Uppercase, remoção de acentos, espaços duplicados, abreviações comuns
- Extração inteligente de nome (prioriza linhas antes de endereço/CEP; ignora ruído administrativo)
- Extração de código de rastreamento (Correios BR, transportadoras)
- Similaridade token-based (`nameSimilarity`, `levenshtein`, `similar_text`)

**Algoritmo de matching (`PackageRecipientMatcher`)**

- Busca **exclusivamente** moradores/agregados do `condominium_id` ativo do porteiro
- Pontuação ponderada: nome, unidade, bloco, endereço, código de rastreamento
- Pesos dinâmicos: sem bloco/unidade no OCR → peso maior no nome
- Threshold mínimo de candidato: 0,35

**Níveis de confiança**

| Nível | Faixa | Comportamento na UI |
|-------|-------|---------------------|
| **Alta** | ≥ 90% | "Morador identificado" — confirmação direta |
| **Média** | 60–89% | Lista de candidatos — porteiro escolhe |
| **Baixa** | < 60% | "Não identificado" — tentar novamente ou registro manual |

**Regra de segurança:** nunca registrar automaticamente com confiança baixa. Sempre exige confirmação humana antes de registrar e enviar WhatsApp.

**Detecção de remetente (`PackageSenderDetector`)**

- Identifica palavras-chave: Mercado Livre, Shopee, Amazon, Correios etc.
- Pré-preenche campo `sender` quando detectado com segurança; caso contrário "Remetente não identificado"

#### 8.10.3 Registro manual e painel por unidade

**Rotas**

| Rota | Nome | Descrição |
|------|------|-----------|
| `/packages` | `packages.index` | Painel portaria mobile-first (porteiro) |
| `GET /api/packages/summary/units` | `api.packages.summary` | Grid de unidades com pendências |
| `POST /api/packages` | `api.packages.store` | Registro manual por unidade |

**Painel portaria (`/packages`)**

- Hero com contador de pendências e atalhos: Ler etiqueta, Retirada, Por unidade
- Busca por unidade, morador ou CPF com autocomplete
- Toggle "Só com pendências"
- Cards por unidade com moradores e chips de encomendas pendentes
- Modais de registro (tipo: leve, pesado, caixa grande, frágil) e retirada
- Barras de progresso visual no registro e na retirada

**Redirecionamento por perfil**

- Porteiro (`register_packages`) → painel operacional `/packages`
- Síndico/secretaria (apenas `view_packages`) → redirecionado para `/packages/reports`

#### 8.10.4 Retirada por senha de 4 dígitos

**Objetivo:** o morador recebe uma senha de 4 dígitos no WhatsApp e a informa ao porteiro na retirada. O porteiro valida a senha antes de entregar o volume.

**Geração da senha**

- Gerada automaticamente em **todo** registro de encomenda (manual, OCR ou híbrido)
- Armazenada como `pickup_code_hash` (bcrypt) — nunca em texto claro no banco
- Códigos triviais bloqueados (0000, 1111, 1234, sequências etc.)
- Enviada ao morador **somente** via WhatsApp/e-mail no job de notificação

**Fluxo de retirada rápida (`/packages/pickup`)**

| Etapa | Ação |
|-------|------|
| 1 | Porteiro digita senha de 4 dígitos |
| 2 | Sistema localiza encomenda pendente (`POST /api/packages/pickup/find`) |
| 3 | Exibe unidade, tipo, morador identificado |
| 4 | Porteiro confirma e opcionalmente informa nome de quem retirou |
| 5 | `POST /api/packages/{id}/collect` — registra retirada com auditoria |

**Retirada pelo painel de unidades**

- Modal de retirada no `/packages` com campo de senha quando `requires_pickup_code = true`
- Encomendas legadas (sem `pickup_code_hash`) permitem retirada direta sem senha

**Campos de auditoria da retirada**

| Campo | Descrição |
|-------|-----------|
| `collected_at` | Data/hora da retirada |
| `collected_by` | ID do porteiro que processou |
| `picked_up_by_name` | Nome informado de quem retirou (opcional) |
| `pickup_verified_at` | Timestamp da verificação bem-sucedida da senha |
| `user_activity_logs` | Ação `package_collected` com metadados |

#### 8.10.5 Notificações WhatsApp (Evolution API)

**Integração:** reutiliza `EvolutionApiService` e `WhatsAppNotificationService` existentes — **não há integração paralela**.

**Job principal:** `SendPackageNotification`

| Propriedade | Valor |
|-------------|-------|
| Disparo | Após `DB::commit()` via `->afterCommit()` |
| Tipos | `arrived` (chegada) e `collected` (retirada) |
| Retries | `tries = 3`, `backoff` configurado |
| Falha permanente | `whatsapp_delivery_status = failed` |

**Mensagem de chegada (exemplo)**

```
📦 NOVA ENCOMENDA

Olá, {nome}!
Uma encomenda destinada à sua unidade foi recebida pela portaria.

🏢 Bloco {bloco}
🚪 Apartamento {numero}
📦 Remetente: {remetente}

🔐 Senha para retirada: {senha_4_digitos}

A encomenda está disponível para retirada na portaria.
```

**Status de entrega WhatsApp (`whatsapp_delivery_status`)**

| Valor | Significado |
|-------|-------------|
| `pending` | Job enfileirado ou aguardando envio |
| `sent` | Evolution API confirmou envio |
| `failed` | Falha após retries (encomenda permanece registrada) |

**Regra crítica:** falha do WhatsApp **nunca** desfaz o registro da encomenda. A UI informa: "Encomenda registrada. WhatsApp pendente." O job pode ser reprocessado pela fila.

**Requisito operacional:** em produção, `QUEUE_CONNECTION=database` (ou redis) + `php artisan queue:work` via Supervisor. Com `sync`, retries não funcionam adequadamente.

**Grupo WhatsApp:** `packages` em `config/whatsapp.php` — configurável por condomínio ou plataforma.

#### 8.10.6 Relatório de movimentações (síndico / secretaria)

**Rota:** `/packages/reports` (`packages.reports`)

**Público:** Síndico, Secretaria e demais perfis com `view_packages` (sem `register_packages`)

**Funcionalidades**

- Histórico completo de chegadas e retiradas do condomínio
- Filtros: período (de/até), status (pendente/retirada), unidade, busca livre (morador, remetente, rastreio)
- KPIs: total no período, pendentes, retiradas, tempo médio até retirada (horas)
- Tabela responsiva (desktop) + cards (mobile)
- Paginação server-side (50 registros/página)
- Exportação **PDF** (`packages.reports.pdf`) e **Excel** (`packages.reports.excel`)

**Permissão de exportação:** `export_packages_reports` (Síndico e Secretaria no seeder)

**Controller:** `PackageWebController` — `reports()`, `exportPdf()`, `exportExcel()`

**Service:** `PackageService::listMovements()`, `movementStatistics()`

**Dashboard síndico:** cards e atalhos apontam para `/packages/reports` (não para o painel operacional do porteiro)

#### 8.10.7 Modelo de dados — campos `packages`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `condominium_id` | FK | Tenant obrigatório |
| `unit_id` | FK | Unidade destinatária |
| `registered_by` | FK User | Porteiro que registrou |
| `type` | enum | `leve`, `pesado`, `caixa_grande`, `fragil` |
| `status` | enum | `pending`, `collected` |
| `received_at` | datetime | Chegada na portaria |
| `collected_at` | datetime | Retirada |
| `collected_by` | FK User | Porteiro que confirmou retirada |
| `picked_up_by_name` | string | Nome de quem retirou (informado) |
| `pickup_verified_at` | datetime | Verificação da senha |
| `pickup_code_hash` | string (hidden) | Hash bcrypt da senha |
| `sender` | string | Remetente |
| `tracking_code` | string | Código de rastreamento |
| `label_image_path` | string | Imagem original da etiqueta (auditoria) |
| `ocr_text` | text | Texto bruto OCR |
| `ocr_confidence` | float | Confiança OCR |
| `identification_method` | enum | `manual`, `ocr`, `barcode`, `hybrid` |
| `identification_confidence` | float | Confiança do matching |
| `identified_resident_id` | FK User | Morador sugerido/confirmado |
| `whatsapp_delivery_status` | enum | `pending`, `sent`, `failed` |
| `notification_sent` | boolean | Flag legada de notificação |

#### 8.10.8 Regras de negócio específicas

| ID | Regra |
|----|-------|
| PKG-RN-01 | Todo dado de encomenda respeita `condominium_id` — matching nunca cruza condomínios |
| PKG-RN-02 | Confirmação humana obrigatória antes de registrar e notificar |
| PKG-RN-03 | Confiança baixa (< 60%) nunca auto-seleciona morador |
| PKG-RN-04 | Senha de 4 dígitos gerada em todo registro novo |
| PKG-RN-05 | Senha armazenada apenas como hash — nunca em texto claro no banco |
| PKG-RN-06 | Falha WhatsApp não reverte registro da encomenda |
| PKG-RN-07 | Imagem original da etiqueta preservada para auditoria |
| PKG-RN-08 | OCR desabilitável via `OCR_ENABLED=false` — registro manual permanece |
| PKG-RN-09 | HTTPS obrigatório em produção para acesso à câmera (`/packages/intake`) |
| PKG-RN-10 | Encomendas legadas sem senha: retirada direta permitida |
| PKG-RN-11 | Síndico visualiza movimentações; porteiro opera chegadas/retiradas |
| PKG-RN-12 | Operação de registro executada em transação DB; WhatsApp após commit |

#### 8.10.9 Permissões do módulo

| Permissão | Papéis típicos | Escopo |
|-----------|----------------|--------|
| `register_packages` | Porteiro | Registrar, OCR, retirada, summary/units |
| `view_packages` | Síndico, Secretaria, Porteiro | Visualizar encomendas |
| `export_packages_reports` | Síndico, Secretaria | Exportar PDF/Excel |

**Middleware:** `check.module.access:packages` (web), `condominium.module:packages` (API e relatório)

#### 8.10.10 Requisitos funcionais (tabela consolidada)

| ID | Requisito | Prioridade | Implementação |
|----|-----------|------------|---------------|
| PKG-01 | Portaria registra chegada manual por unidade | Must | `PackageService::register()`, `/packages` |
| PKG-02 | Leitura de etiqueta via câmera mobile (Web) | Must | `PackageIntakeApp.vue`, `/packages/intake` |
| PKG-03 | OCR com Tesseract (múltiplos PSM, melhor resultado) | Must | `TesseractOcrService` |
| PKG-04 | Pré-processamento de imagem para OCR | Must | `LabelImagePreprocessor` |
| PKG-05 | Matching fuzzy de destinatário por condomínio | Must | `PackageRecipientMatcher` |
| PKG-06 | Níveis de confiança alta/média/baixa | Must | `PackageRecipientMatcher` |
| PKG-07 | Confirmação humana obrigatória | Must | `confirmLabel()` |
| PKG-08 | Detecção de remetente por palavras-chave | Should | `PackageSenderDetector` |
| PKG-09 | Leitura barcode/QR complementar | Should | `@zxing/library` no frontend |
| PKG-10 | Geração de senha 4 dígitos (hash) | Must | `PackageService::generatePickupCode()` |
| PKG-11 | Notificação WhatsApp com senha na chegada | Must | `SendPackageNotification` |
| PKG-12 | Retirada com validação de senha | Must | `collect()`, `verifyPickupCode()` |
| PKG-13 | Fluxo rápido de retirada (`/packages/pickup`) | Must | `PackagePickupApp.vue` |
| PKG-14 | Busca de encomenda por senha | Must | `POST /api/packages/pickup/find` |
| PKG-15 | Auditoria: quem retirou, quando, senha verificada | Must | `picked_up_by_name`, `pickup_verified_at`, activity log |
| PKG-16 | Status de entrega WhatsApp com retry | Must | `whatsapp_delivery_status`, job retries |
| PKG-17 | Falha WhatsApp não remove encomenda | Must | Transação + `afterCommit()` |
| PKG-18 | Relatório síndico com filtros e KPIs | Must | `/packages/reports` |
| PKG-19 | Exportação PDF e Excel | Must | `PackageMovementsExport`, DomPDF |
| PKG-20 | Painel portaria mobile-first redesenhado | Must | `packages/index.blade.php` |
| PKG-21 | UI com barra de progresso | Should | Modais register/collect |
| PKG-22 | Fallback registro manual se OCR falhar | Must | Botão "Registrar manualmente" |
| PKG-23 | Encomendas legadas sem senha retiráveis | Must | `requires_pickup_code` |
| PKG-24 | Notificação de retirada ao morador | Should | `SendPackageNotification` tipo `collected` |
| PKG-25 | Armazenar imagem e texto OCR para auditoria | Must | `label_image_path`, `ocr_text` |

#### 8.10.11 Infraestrutura e variáveis de ambiente

| Variável | Descrição | Produção |
|----------|-----------|----------|
| `OCR_ENABLED` | Habilita/desabilita OCR | `true` (requer Tesseract) |
| `TESSERACT_PATH` | Caminho do binário Tesseract | Ex.: `/usr/bin/tesseract` |
| `OCR_LANG` | Idioma OCR | `por` |
| `OCR_TIMEOUT` | Timeout em segundos | `30` |
| `OCR_PREPROCESS_ENABLED` | Pré-processamento de imagem | `true` |
| `OCR_MAX_DIMENSION` | Dimensão máxima antes do OCR | `2000` |
| `QUEUE_CONNECTION` | Fila para jobs WhatsApp | `database` |
| `WHATSAPP_ENABLED` | Habilita WhatsApp global | `true` |
| `EVOLUTION_*` | Credenciais Evolution API | Por condomínio ou plataforma |

**Dependência de SO:** `tesseract-ocr` + `tesseract-ocr-por` (VPS — ver `INSTALACAO_VPS.md`)

#### 8.10.12 Testes automatizados

| Arquivo | Cobertura |
|---------|-----------|
| `PackageManagementTest.php` | Registro manual, retirada, summary, pickup code hash |
| `PackageLabelScanTest.php` | OCR preview, confirm, pickup find, cross-tenant, legado |
| `PackageReportsTest.php` | Relatório síndico, redirect, export PDF, permissões |
| `PackageRecipientMatcherTest.php` | Matching alta/média/baixa, isolamento tenant |
| `PackageSenderDetectorTest.php` | Detecção de remetentes |
| `TextNormalizerTest.php` | Normalização e extração de nomes/códigos |

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
| DSH-06 | Atalhos porteiro: ler etiqueta, retirada, painel unidades | Must | `dashboard/porteiro.blade.php` |
| DSH-07 | Síndico: card encomendas → relatório movimentações | Must | `dashboard/sindico.blade.php` → `packages.reports` |
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
| RN-21 | Encomendas: matching de destinatário **somente** dentro do `condominium_id` ativo |
| RN-22 | Encomendas: senha de retirada armazenada como hash; enviada ao morador via WhatsApp |
| RN-23 | Encomendas: falha WhatsApp não reverte registro; job com retry assíncrono |
| RN-24 | Encomendas: OCR é assistente — confirmação humana obrigatória antes de registrar |
| RN-25 | Visitante "Outro": `valid_until` obrigatório; gera senha hash + `qr_token` |
| RN-26 | Visitante "Outro": senha e QR válidos até `expires_at`; múltiplas entradas/saídas |
| RN-27 | Visitante "Outro": check-in por QR/senha não consome liberação (status `pending` até expirar) |
| RN-28 | Visitante "Outro": senha enviada ao morador via WhatsApp; PDF com QR para encaminhar ao visitante |
| RN-29 | Controle de acesso: matching de credencial restrito ao `condominium_id` do porteiro |

---

## 11. Permissões e controle de acesso

### 11.1 Sistema Spatie

- **~65 permissões** granulares em `RolesAndPermissionsSeeder` (inclui `register_packages`, `view_packages`, `export_packages_reports`)
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

**Uso no módulo de controle de acesso (grupo `access`):**

| Evento | Tipo | Conteúdo principal |
|--------|------|-------------------|
| Liberação "Outro" criada | `access_visitor_credential` | Senha 4 dígitos, validade, instrução para baixar PDF |
| Visitante entrou (check-in QR/senha ou manual) | `access_entered` | Nome, unidade, horário |
| Acesso negado | `access_denied` | Nome, unidade |
| Proibição identificada | `access_prohibition_critical` | Alerta crítico ao morador |

Jobs: `SendVisitorAccessCredentialNotification` (criação), `SendAccessNotification` (cada entrada).

**Uso no módulo de encomendas (grupo `packages`):**

| Evento | Job | Conteúdo principal |
|--------|-----|-------------------|
| Chegada de encomenda | `SendPackageNotification` (`arrived`) | Unidade, remetente, **senha de 4 dígitos** |
| Retirada confirmada | `SendPackageNotification` (`collected`) | Confirmação de entrega na portaria |

- Disparo assíncrono após commit da transação (`->afterCommit()`)
- Status persistido em `packages.whatsapp_delivery_status`
- Observer `NotificationObserver` também despacha `SendWhatsAppNotification` para notificações in-app

**Env fallback:** `WHATSAPP_ENABLED`, `EVOLUTION_*`, `WHATSAPP_DEFAULT_COUNTRY_CODE`

### 12.3 Outras integrações

| Integração | Finalidade |
|------------|------------|
| **Laravel Sanctum** | API REST (stateful + token) |
| **DomPDF** | PDFs (multas, recibos, relatórios, histórico) |
| **Maatwebsite Excel** | Exportações financeiras e ocorrências |
| **SimpleSoftwareIO QRCode** | QR pets, landing, moradores |
| **BaconQrCode + GD** | QR Code PNG em PDF de visitante (`QRCodeHelper`) — sem dependência de Imagick |
| **Intervention Image** | Processamento de imagens (OCR etiquetas, marketplace) |
| **Tesseract OCR** | Leitura de etiquetas de encomenda (binário SO + `TesseractOcrService`) |
| **@zxing/library** | Scan QR na portaria (`AccessCheckinApp.vue`) e encomendas |
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
- SPAs parciais (Vue): reservas, encomendas (intake OCR + retirada por senha), **controle de acesso porteiro (check-in QR/senha)**, assembleias, mensagens, notificações
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
| Encomendas | summary, residents/search, CRUD, collect, label/preview, label/confirm, pickup/find | `packages` |
| Marketplace | CRUD | `marketplace` |
| Assembleias | CRUD, vote, lifecycle, minutes export | `assemblies` |
| Mensagens/Conversas | CRUD, syndic, export | `communication` |
| Notificações | index, read, unread-count | — |
| Espaços | CRUD | `spaces` |
| Pets | CRUD | `pets` |
| Caronas | rides + bookings | `rides` |
| Controle acesso | porteiro/panel, authorizations, authorizations/{id}/pdf, check-in/pin, check-in/qr, lists, providers, movements | `access_control` |
| Usuários | search | — |
| Créditos | GET /user/credits | — |

#### Somente Web (sem API dedicada)

Multas, taxas (FeeController), fechamento mensal, contas bancárias/conciliação (parcial), funcionários, prestação de contas upload, ordens de serviço, livro de ocorrências, regimento interno, landing admin, gestão plataforma, auto-cadastro, checkout web, reservas recorrentes, histórico usuário, configurações condomínio, **relatório de encomendas síndico** (`/packages/reports` — server-rendered).

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

`GenerateAsaasPayment`, `GenerateReservationPayment`, `SendWhatsAppNotification`, `SendPackageNotification`, `SendPanicAlert`, `SendAccessNotification`, `SendVisitorAccessCredentialNotification`, `SendChargeReminders`, `SendOverdueReminders`, `SendSubscriptionBillingNotification`

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
| `OCR_*`, `TESSERACT_PATH` | OCR de etiquetas de encomenda |
| `WHATSAPP_*`, `EVOLUTION_*` | WhatsApp (inclui senha de retirada) |

**Nota:** credenciais Asaas/WhatsApp por condomínio ficam no **banco/painel**, não apenas no `.env`.

**Dependência de SO (encomendas):** pacote `tesseract-ocr` + idioma `tesseract-ocr-por` na VPS.

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
    │   ├── Package (+ OCR, senha retirada, whatsapp_delivery_status)
    │   ├── AccessAuthorization (+ visitor_preset_key, access_pin_hash, qr_token), AccessMovement, ServiceProvider
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

### 18.6 Porteiro registra encomenda via leitura de etiqueta (OCR)

1. Acessa **Ler etiqueta** (`/packages/intake`) ou atalho no dashboard
2. Concede permissão da câmera (HTTPS obrigatório em produção)
3. Enquadra a etiqueta na área guia e captura a foto
4. Sistema processa: pré-processamento → OCR (Tesseract) → matching fuzzy
5. Apresenta destinatário sugerido com nível de confiança (alta/média/baixa)
6. Porteiro confirma morador/unidade ou escolhe entre candidatos / tenta novamente / registra manualmente
7. Clica **CHEGOU ENCOMENDA** → registro transacional + geração de senha hash
8. Job `SendPackageNotification` envia WhatsApp com senha de 4 dígitos
9. Tela de sucesso exibe status do WhatsApp (enviado/pendente/falhou)

### 18.6.1 Porteiro registra encomenda manualmente

1. Acessa **Painel Portaria** (`/packages`) ou **Por unidade** no hero
2. Busca unidade/morador ou navega pelos cards
3. Clica **Registrar chegada** → seleciona tipo (leve, pesado, caixa grande, frágil)
4. Confirma → encomenda criada com senha; moradores notificados via WhatsApp

### 18.6.2 Porteiro confirma retirada por senha

1. Morador informa senha de 4 dígitos recebida no WhatsApp
2. Porteiro acessa **Retirada** (`/packages/pickup`) ou modal no painel de unidades
3. Digita a senha → sistema localiza encomenda pendente e exibe dados
4. Confirma destinatário; opcionalmente informa nome de quem retirou
5. Clica **Retirado!** → `collected_at`, `pickup_verified_at` e auditoria registrados
6. Job notifica moradores da retirada

### 18.6.3 Síndico consulta movimentações de encomendas

1. Acessa **Encomendas → Movimentações** (`/packages/reports`) ou atalho no dashboard
2. Define período, status, unidade ou termo de busca
3. Visualiza KPIs e tabela completa (chegadas, retiradas, porteiro, auditoria)
4. Exporta PDF ou Excel para arquivo/arquivo contábil

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

### 18.11 Morador libera visitante "Outro" (credencial digital)

1. Acessa **Controle de Acesso** (`/access-control`)
2. Seleciona preset **Outro**, informa nome do visitante
3. Define **entrada prevista** e **validade da liberação** (obrigatória)
4. Sistema cria liberação com senha hash e `qr_token` único
5. Recebe **senha de 4 dígitos** no WhatsApp (`access_visitor_credential`)
6. Modal exibe opção de **baixar PDF** com QR Code para encaminhar ao visitante
7. Visitante pode entrar/sair quantas vezes quiser até `valid_until`/`expires_at`
8. Morador pode baixar PDF novamente pelo histórico de liberações

### 18.12 Porteiro faz check-in de visitante "Outro" (QR ou senha)

1. Acessa **Portaria** (`/access-control/porteiro`)
2. Visitante apresenta QR (PDF no celular) ou informa senha de 4 dígitos
3. Na seção **Liberação rápida**, porteiro escaneia QR (`AccessCheckinApp.vue`) ou digita a senha
4. API valida credencial ativa (não expirada, mesmo condomínio, status `pending`)
5. Sistema registra `AccessMovement` (`entered`, `credential_reusable: true`) **sem** consumir a liberação
6. Notifica morador via WhatsApp (`access_entered`) — "Portão liberado" na tela
7. Visitante pode repetir o fluxo em novas passagens até expirar a validade
8. Liberações "Outro" também aparecem no grid com badge QR/Senha (visibilidade operacional)

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
| `PackageManagementTest.php` | Encomendas (registro manual, retirada, summary) |
| `PackageLabelScanTest.php` | OCR, confirm label, pickup code, cross-tenant |
| `PackageReportsTest.php` | Relatório síndico, export PDF, permissões |
| `PackageRecipientMatcherTest.php` | Matching fuzzy destinatário |
| `PackageSenderDetectorTest.php` | Detecção remetente |
| `TextNormalizerTest.php` | Normalização OCR |
| `MarketplaceModuleTest.php` | Agregados marketplace |
| `TransactionTest.php` | Transações + isolamento tenant |
| `AuthenticationTest.php` | Login/logout |
| `NotificationRedirectTest.php` | Deep links |
| `VisitorAccessCredentialTest.php` | Visitante "Outro": criação, validade, check-in PIN/QR reutilizável, expiração, PDF, painel porteiro |

### 19.2 Lacunas de QA (documentar no roadmap)

Sem testes automatizados dedicados para: WhatsApp/Evolution (incl. `access_visitor_credential`), webhooks Asaas, fechamento mensal E2E, presets de liberação manual (ENTROU/NEGADO), caronas, landing page, auto-cadastro, assinatura SaaS, exportações PDF gerais, scan QR em dispositivo real, multitenancy admin.

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
| **Encomenda Inteligente** | Fluxo OCR + matching para registro automático assistido |
| **Senha de retirada** | Código de 4 dígitos (hash no banco) enviado via WhatsApp |
| **OCR** | Reconhecimento óptico de caracteres (Tesseract) — sem IA generativa |
| **Matching fuzzy** | Comparação ponderada nome/unidade/bloco entre OCR e cadastro |
| **Evolution API** | Servidor WhatsApp usado para notificações de encomenda |
| **PSM** | Page Segmentation Mode do Tesseract (múltiplas passagens OCR) |
| **Visitante "Outro"** | Liberação nomeada com credencial digital (PDF + QR + senha reutilizável) |
| **Credencial reutilizável** | Senha/QR válidos até `expires_at`; múltiplas entradas sem consumir liberação |
| **Liberação rápida** | Seção do painel porteiro para scan QR ou senha de visitante "Outro" |
| **qr_token** | Token único embutido no QR (`visitor_access`) para check-in na API |
| **access_pin_hash** | Hash bcrypt da senha de 4 dígitos do visitante "Outro" |

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
| Encomendas | `app/Services/PackageService.php`, `app/Http/Controllers/PackageWebController.php` |
| OCR | `app/Services/Ocr/TesseractOcrService.php`, `config/ocr.php` |
| Matching | `app/Services/Packages/PackageRecipientMatcher.php` |
| Vue intake/pickup | `resources/js/components/packages/PackageIntakeApp.vue`, `PackagePickupApp.vue` |
| Controle de acesso | `app/Services/AccessControlService.php`, `app/Http/Controllers/Api/AccessControlController.php` |
| Credencial visitante | `app/Jobs/SendVisitorAccessCredentialNotification.php`, `app/Helpers/QRCodeHelper.php` |
| Vue check-in portaria | `resources/js/components/access/AccessCheckinApp.vue`, `access-porteiro-checkin.js` |
| Migration credenciais | `database/migrations/2026_09_15_180000_add_visitor_credentials_to_access_authorizations.php` |
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

*Documento v2.2 — atualizado com base no estado do repositório CondoCenter em 15/09/2026. Inclui visitante "Outro" com credencial digital (PDF/QR + senha reutilizável, check-in automático na portaria, WhatsApp ao morador), encomenda inteligente (OCR, matching fuzzy, senha de retirada), módulos habilitáveis, landing dual-template, fechamento mensal e matriz de canais. Deploy: `php artisan migrate --force` (campos `visitor_preset_key`, `access_pin_hash`, `qr_token`). Para alterações de escopo, revisar com stakeholders e incrementar a versão deste PRD.*
