# PRD — SindCON (CondoCenter)

**Product Requirements Document**

| Campo | Valor |
|-------|-------|
| **Produto** | SindCON — Plataforma SaaS de Gestão Condominial |
| **Repositório** | CondoCenter |
| **Versão do documento** | 1.0 |
| **Data** | 08/09/2026 |
| **Status** | Em produção / evolução contínua |
| **Stack** | Laravel 12, PHP 8.3+, MySQL, Bootstrap 5, Vue 3, Sanctum, Spatie Permission |

---

## Sumário

1. [Visão do produto](#1-visão-do-produto)
2. [Problema e oportunidade](#2-problema-e-oportunidade)
3. [Objetivos e metas de sucesso](#3-objetivos-e-metas-de-sucesso)
4. [Personas e papéis](#4-personas-e-papéis)
5. [Escopo do produto](#5-escopo-do-produto)
6. [Arquitetura e modelo multi-tenant](#6-arquitetura-e-modelo-multi-tenant)
7. [Requisitos funcionais por módulo](#7-requisitos-funcionais-por-módulo)
8. [Regras de negócio globais](#8-regras-de-negócio-globais)
9. [Permissões e controle de acesso](#9-permissões-e-controle-de-acesso)
10. [Integrações externas](#10-integrações-externas)
11. [Canais de entrega](#11-canais-de-entrega)
12. [Modelo comercial (SaaS)](#12-modelo-comercial-saas)
13. [Requisitos não funcionais](#13-requisitos-não-funcionais)
14. [Jornadas principais](#14-jornadas-principais)
15. [Fora de escopo e roadmap](#15-fora-de-escopo-e-roadmap)
16. [Glossário](#16-glossário)
17. [Referências](#17-referências)

---

## 1. Visão do produto

### 1.1 Declaração de visão

O **SindCON** é uma plataforma SaaS multi-condomínio que centraliza a gestão operacional, financeira, de comunicação e de segurança de condomínios residenciais, oferecendo transparência aos moradores, eficiência à administração e monetização recorrente à operadora da plataforma.

### 1.2 Proposta de valor

| Stakeholder | Valor entregue |
|-------------|----------------|
| **Operadora da plataforma** | Receita recorrente por assinatura, gestão centralizada de múltiplos condomínios |
| **Síndico / administração** | Ferramentas integradas para finanças, comunicação, reservas, portaria e governança |
| **Morador** | Autosserviço (pagamentos, reservas, marketplace), transparência financeira e canal de emergência |
| **Portaria** | Registro de acessos, encomendas e liberações com rastreabilidade |
| **Conselho fiscal** | Visibilidade financeira e participação em assembleias |

### 1.3 Posicionamento

- **Segmento:** condomínios residenciais (pequenos a grandes) e administradoras
- **Modelo:** B2B2C — plataforma vende ao condomínio; moradores são usuários finais
- **Diferencial:** ecossistema integrado (financeiro + operacional + comunicação + pânico) com WhatsApp nativo e app mobile de emergência

---

## 2. Problema e oportunidade

### 2.1 Problemas atuais no mercado

- Gestão fragmentada (planilhas, grupos de WhatsApp, sistemas isolados)
- Baixa transparência financeira para moradores
- Comunicação informal sem registro ou auditoria
- Dificuldade de cobrança e controle de inadimplência
- Ausência de canal formal e rastreável para emergências no condomínio
- Portaria com processos manuais e pouco integrados

### 2.2 Oportunidade

Digitalizar o ciclo completo da vida condominial — do cadastro de moradores ao pagamento de taxas, reserva de áreas comuns, controle de visitantes, assembleias virtuais e alertas de pânico — em uma única plataforma com isolamento por condomínio e modelo SaaS escalável.

---

## 3. Objetivos e metas de sucesso

### 3.1 Objetivos de produto

1. Reduzir tempo operacional do síndico em tarefas administrativas repetitivas
2. Aumentar taxa de adimplência via cobrança digital e lembretes automatizados
3. Proporcionar transparência financeira total aos moradores e conselho fiscal
4. Garantir comunicação rastreável e canais sigilosos quando necessário
5. Oferecer resposta rápida a emergências via alerta de pânico
6. Monetizar via assinatura SaaS com planos flexíveis

### 3.2 KPIs sugeridos

| KPI | Descrição |
|-----|-----------|
| **Taxa de adimplência** | % de cobranças pagas no prazo por condomínio |
| **Adoção de moradores** | % de unidades com pelo menos 1 usuário ativo |
| **Tempo médio de resolução de OS** | Da abertura à conclusão |
| **Engajamento em assembleias** | % de participação em votações |
| **Tempo de resposta ao pânico** | Da confirmação à resolução pelo síndico |
| **Churn de assinatura SaaS** | Condomínios que cancelam assinatura |
| **NPS / satisfação** | Pesquisa periódica com síndicos e moradores |

---

## 4. Personas e papéis

### 4.1 Personas

#### P1 — Administrador da plataforma
- **Quem:** equipe SindCON / operadora do SaaS
- **Necessidades:** gerenciar condomínios, planos, assinaturas, configurações globais (Asaas, WhatsApp)
- **Acesso:** painel de plataforma (`platform.*`)

#### P2 — Síndico
- **Quem:** gestor eleito ou profissional do condomínio
- **Necessidades:** finanças, usuários, reservas, comunicação, moderação, relatórios
- **Pode atuar em múltiplos condomínios** (seletor de condomínio ativo)

#### P3 — Morador (responsável pela unidade)
- **Quem:** proprietário ou inquilino principal da unidade
- **Necessidades:** pagar taxas, reservar espaços, marketplace, pets, votar, acionar pânico
- **Regra:** uma unidade possui um morador responsável

#### P4 — Agregado
- **Quem:** dependente vinculado ao morador (cônjuge, filho, empregada etc.)
- **Necessidades:** acesso limitado conforme permissões configuradas pelo síndico/morador
- **Regra:** vinculado via `morador_vinculado_id`; permissões granulares por módulo

#### P5 — Porteiro
- **Quem:** equipe de portaria / controle de acesso
- **Necessidades:** registrar entradas, encomendas, processar liberações, verificar pets

#### P6 — Conselho Fiscal
- **Quem:** conselheiros eleitos
- **Necessidades:** transparência financeira, relatórios, participação em assembleias

#### P7 — Secretaria
- **Quem:** apoio administrativo do condomínio
- **Necessidades:** visualização operacional, envio de avisos, comunicação

### 4.2 Matriz resumida papel × escopo

| Papel | Plataforma SaaS | Condomínio | Financeiro completo | Pânico |
|-------|-----------------|------------|----------------------|--------|
| Administrador | ✅ Total | ✅ Total | ✅ | ✅ Gestão |
| Síndico | ❌ | ✅ Total | ✅ | ✅ Gestão |
| Morador | ❌ | ✅ Uso | ✅ Visualização | ✅ Acionar |
| Agregado | ❌ | ⚙️ Configurável | ⚙️ Configurável | ✅ Acionar |
| Porteiro | ❌ | ✅ Portaria | ❌ | ❌ |
| Conselho Fiscal | ❌ | ✅ Fiscalização | ✅ Visualização | ❌ |
| Secretaria | ❌ | ✅ Operacional | ⚙️ Parcial | ❌ |

---

## 5. Escopo do produto

### 5.1 Dentro do escopo (implementado)

- Plataforma SaaS multi-condomínio
- Gestão de unidades e usuários (CRUD, aprovação, histórico)
- Módulo financeiro (modo simplificado e completo)
- Cobranças e pagamentos via Asaas
- Reservas de espaços com calendário e créditos
- Marketplace interno
- Caronas entre moradores
- Cadastro de pets com QR Code
- Ordens de serviço
- Controle de acesso / portaria
- Encomendas
- Assembleias e votação
- Comunicação (mensagens, conversas, avisos, Fale com o Síndico)
- Livro de Ocorrências (sigiloso + exposição pública opcional)
- Regimento interno versionado
- Alerta de pânico (web + app mobile)
- Landing page pública por condomínio
- API REST (Sanctum)
- Notificações (database, e-mail, WhatsApp, push mobile)
- Auditoria de operações críticas

### 5.2 Fora do escopo atual

- Integração com Stripe (pagamentos exclusivamente via Asaas)
- App mobile completo (apenas módulo de pânico)
- API para ordens de serviço, livro de ocorrências e regimento interno
- Gestão de obras / reformas estruturais
- Integração com elevadores, CFTV ou IoT

---

## 6. Arquitetura e modelo multi-tenant

### 6.1 Hierarquia de dados

```
Plataforma SindCON
 └── Condomínio (tenant)
      ├── Unidades
      │    └── Morador responsável + Agregados
      ├── Usuários (condominium_id, unit_id)
      ├── Assinatura SaaS
      ├── Configurações (financeiro, WhatsApp, inadimplentes)
      └── Dados operacionais isolados por condominium_id
```

### 6.2 Isolamento de tenant

- Todo dado operacional possui `condominium_id`
- Middleware `require.condominium` exige condomínio ativo na sessão
- Middleware `ensure.saas.subscription` bloqueia módulos se assinatura inativa
- Síndicos multi-condomínio: pivot `condominium_user` + seletor `condominium.switch`

### 6.3 Perfil ativo em sessão

- Usuário pode ter **múltiplos papéis** (ex.: Síndico + Morador)
- Permissões avaliadas pelo **perfil ativo** (`HasActiveProfileRole`)
- Troca de perfil via `profile.switch`

### 6.4 Ambientes financeiros

| Modo | Descrição |
|------|-----------|
| **Simplificado** | Prestação de contas por upload; cobranças e taxas básicas |
| **Completo** | Caixa do condomínio, transações, conciliação bancária, DRE, contas bancárias |

Controlado por `financial_mode` no condomínio; middleware `ensure.full.financial` restringe módulos avançados.

### 6.5 Modos de recebimento (Asaas)

| Modo | Descrição |
|------|-----------|
| **Plataforma** | Credenciais SindCON; split/repasse configurável |
| **Próprio** | Credenciais Asaas do condomínio |

---

## 7. Requisitos funcionais por módulo

### 7.1 Plataforma SaaS (Administrador)

| ID | Requisito | Prioridade |
|----|-----------|------------|
| PLT-01 | CRUD de condomínios (ativar/desativar, código de registro) | Must |
| PLT-02 | Gestão de planos de assinatura | Must |
| PLT-03 | Gestão de assinaturas por condomínio (ativar, suspender, cancelar) | Must |
| PLT-04 | Sincronização de cobranças SaaS com Asaas | Must |
| PLT-05 | Configuração global Asaas e WhatsApp (Evolution API) | Must |
| PLT-06 | Novidades/comunicados globais da plataforma | Should |
| PLT-07 | Dashboard consolidado da operação SaaS | Should |

### 7.2 Gestão de unidades e usuários

| ID | Requisito | Prioridade |
|----|-----------|------------|
| USR-01 | CRUD de unidades com morador responsável | Must |
| USR-02 | CRUD de usuários com múltiplos papéis | Must |
| USR-03 | Auto-cadastro com código do condomínio + aprovação do síndico | Must |
| USR-04 | Ativar/desativar usuários | Must |
| USR-05 | Histórico completo do usuário (PDF/Excel) | Should |
| USR-06 | Permissões granulares para agregados por módulo (`view` / `crud`) | Must |
| USR-07 | Apenas administradores podem atribuir/remover perfil Administrador | Must |
| USR-08 | Síndicos podem atribuir perfis Síndico e Conselho Fiscal | Must |

### 7.3 Financeiro

| ID | Requisito | Prioridade |
|----|-----------|------------|
| FIN-01 | CRUD de transações (receitas/despesas) com comprovantes | Must |
| FIN-02 | Configuração e geração de taxas/cobranças em lote | Must |
| FIN-03 | Cobrança individual por unidade (boleto, PIX, cartão via Asaas) | Must |
| FIN-04 | Extrato e pagamento online para moradores ("Minhas Cobranças") | Must |
| FIN-05 | Multas com emissão e cancelamento | Should |
| FIN-06 | Conciliação bancária (upload CSV/OFX + matching) | Should |
| FIN-07 | Prestação de contas (relatório oficial PDF/Excel) | Must |
| FIN-08 | Painel de adimplência e inadimplência | Must |
| FIN-09 | Transparência financeira total para moradores e conselho fiscal | Must |
| FIN-10 | Webhooks Asaas para confirmação automática de pagamentos | Must |
| FIN-11 | Lembretes automáticos de vencimento | Should |
| FIN-12 | Baixa manual e revogação de folha de pagamento | Should |

### 7.4 Reservas

| ID | Requisito | Prioridade |
|----|-----------|------------|
| RES-01 | Calendário interativo de reservas (Vue) | Must |
| RES-02 | CRUD de espaços reserváveis pelo síndico | Must |
| RES-03 | Aprovação manual ou automática de reservas | Must |
| RES-04 | Reservas recorrentes / bloqueios periódicos | Should |
| RES-05 | Créditos de usuário (`UserCredit`) | Should |
| RES-06 | Pagamento online condicionado a configuração do condomínio | Should |
| RES-07 | Limites por mês/hora e detecção de conflitos | Must |
| RES-08 | Gestão administrativa (aprovação em lote, edição) | Must |

### 7.5 Marketplace

| ID | Requisito | Prioridade |
|----|-----------|------------|
| MKT-01 | Publicação de anúncios por categoria (produtos, serviços, empregos etc.) | Must |
| MKT-02 | Até 3 imagens por anúncio (upload ou câmera) | Must |
| MKT-03 | Edição com sincronização de imagens (manter/remover/adicionar) | Must |
| MKT-04 | Moderação pelo síndico | Should |
| MKT-05 | Bloqueio para inadimplentes (quando configurado) | Must |
| MKT-06 | Toggle de acesso para agregados por condomínio | Should |
| MKT-07 | Contato via WhatsApp do anunciante | Must |

### 7.6 Caronas

| ID | Requisito | Prioridade |
|----|-----------|------------|
| CAR-01 | Oferta de carona entre moradores | Should |
| CAR-02 | Reserva de vaga na carona | Should |
| CAR-03 | Notificações WhatsApp (publicação, reserva, cancelamento) | Should |

### 7.7 Pets

| ID | Requisito | Prioridade |
|----|-----------|------------|
| PET-01 | CRUD de pets com foto | Must |
| PET-02 | QR Code público para verificação na portaria | Must |
| PET-03 | Impressão/download de tag | Should |
| PET-04 | Verificação na portaria via QR | Must |

### 7.8 Ordens de serviço

| ID | Requisito | Prioridade |
|----|-----------|------------|
| OS-01 | Morador abre solicitação | Must |
| OS-02 | Síndico gerencia status, mensagens e itens | Must |
| OS-03 | Geração de cobrança a partir da OS | Should |
| OS-04 | Bloqueio para inadimplentes (quando configurado) | Must |

### 7.9 Controle de acesso / Portaria

| ID | Requisito | Prioridade |
|----|-----------|------------|
| ACC-01 | Painel do porteiro para processar entradas | Must |
| ACC-02 | Morador/agregado cria liberações prévias | Must |
| ACC-03 | Listas de autorização e prestadores de serviço | Should |
| ACC-04 | Relatório de movimentações com export PDF | Should |
| ACC-05 | Registro de proibições de acesso | Should |

### 7.10 Encomendas

| ID | Requisito | Prioridade |
|----|-----------|------------|
| PKG-01 | Portaria registra chegada de encomenda | Must |
| PKG-02 | Notificação ao morador (WhatsApp/database) | Must |
| PKG-03 | Confirmação de retirada com código | Must |
| PKG-04 | Barra de progresso visual no fluxo de registro/retirada | Should |

### 7.11 Assembleias

| ID | Requisito | Prioridade |
|----|-----------|------------|
| ASM-01 | Criação de assembleia com pauta | Must |
| ASM-02 | Votação (sim/não/abstenção) com opção secreta | Must |
| ASM-03 | Delegação de voto | Should |
| ASM-04 | Ciclo de vida: iniciar, concluir, cancelar, reabrir | Must |
| ASM-05 | Exportação de ata | Should |
| ASM-06 | Bloqueio de voto para inadimplentes (quando configurado) | Must |

### 7.12 Comunicação

| ID | Requisito | Prioridade |
|----|-----------|------------|
| COM-01 | Mensagens (mural, privadas, prioridades) | Must |
| COM-02 | Conversas (avisos, diretas, anexos, reuniões) | Must |
| COM-03 | Fale com o Síndico (canal sigiloso) | Must |
| COM-04 | Envio de avisos em broadcast | Must |
| COM-05 | Centro de notificações com contador | Must |
| COM-06 | Export CSV/PDF de conversas | Should |

### 7.13 Livro de Ocorrências

| ID | Requisito | Prioridade |
|----|-----------|------------|
| OCC-01 | Morador registra ocorrência/crítica/sugestão ao síndico | Must |
| OCC-02 | Canal sigiloso — admin plataforma sem perfil síndico não acessa | Must |
| OCC-03 | Síndico gerencia, registra ciência e comenta | Must |
| OCC-04 | Exposição pública opcional (sem identificar autor) | Should |
| OCC-05 | Foto opcional na criação (visível síndico/autor; não no livro público) | Should |
| OCC-06 | Export Excel/PDF | Should |
| OCC-07 | Notificação WhatsApp opcional | Should |

### 7.14 Regimento interno

| ID | Requisito | Prioridade |
|----|-----------|------------|
| REG-01 | Publicação e versionamento do regimento | Must |
| REG-02 | Histórico de alterações | Must |
| REG-03 | Visualização, PDF e impressão | Must |

### 7.15 Alerta de pânico

| ID | Requisito | Prioridade |
|----|-----------|------------|
| PAN-01 | 7 tipos de emergência (incêndio, criança perdida, enchente, roubo, polícia, violência doméstica, ambulância) | Must |
| PAN-02 | Confirmação por slide-to-confirm | Must |
| PAN-03 | Notificação imediata a todos moradores + síndico/admin | Must |
| PAN-04 | Canais: database, e-mail, WhatsApp, push (mobile) | Must |
| PAN-05 | Registro de IP, user-agent, data/hora e informações adicionais | Must |
| PAN-06 | Gestão de alertas pelo síndico (resolver/confirmar) | Must |
| PAN-07 | App mobile Expo/React Native (`celular/CondoCenterMobile`) | Must |

### 7.16 Landing page pública

| ID | Requisito | Prioridade |
|----|-----------|------------|
| LND-01 | Página pública por condomínio (`/c/{slug}`) | Should |
| LND-02 | QR Code, galeria e informações do condomínio | Should |

### 7.17 Dashboard

| ID | Requisito | Prioridade |
|----|-----------|------------|
| DSH-01 | Dashboard personalizado por perfil | Must |
| DSH-02 | KPIs financeiros, inadimplência, reservas, encomendas | Must |
| DSH-03 | Atalhos rápidos para morador (liberar visitante, OS, ocorrências) | Should |
| DSH-04 | Pendências do síndico (livro de ocorrências, OS etc.) | Should |

---

## 8. Regras de negócio globais

| # | Regra |
|---|-------|
| RN-01 | Uma unidade possui **um morador responsável** e **N agregados** vinculados |
| RN-02 | Morador e Agregado **não podem** ser selecionados simultaneamente no mesmo usuário |
| RN-03 | Agregados devem ter `morador_vinculado_id` obrigatório |
| RN-04 | Perfis Administrador e Porteiro **não exigem** unidade vinculada |
| RN-05 | Demais perfis **exigem** unidade vinculada |
| RN-06 | Apenas **Administrador** pode atribuir ou remover perfil Administrador |
| RN-07 | **Síndico** pode atribuir perfis Síndico e Conselho Fiscal |
| RN-08 | Assinatura SaaS **ativa** é obrigatória para acessar módulos do condomínio |
| RN-09 | Restrição de inadimplentes (`restrict_defaulters`) bloqueia marketplace, reservas, OS e votação em assembleias |
| RN-10 | Moradores e Conselho Fiscal têm **transparência financeira total** (visualização e exportação) |
| RN-11 | Cadastro de moradores: auto-registro com código + **aprovação do síndico** |
| RN-12 | Marketplace: máximo **3 imagens** por anúncio; JPG, PNG ou WEBP até 5 MB |
| RN-13 | Livro de Ocorrências é **sigiloso**; exposição pública não identifica o autor |
| RN-14 | Operações críticas são registradas via **Laravel Auditing** |
| RN-15 | Pagamentos processados exclusivamente via **Asaas** (sem Stripe) |

---

## 9. Permissões e controle de acesso

### 9.1 Sistema de permissões

- **Spatie Laravel Permission** com ~60 permissões granulares
- Papéis: Administrador, Síndico, Morador, Porteiro, Conselho Fiscal, Secretaria, Agregado
- Permissões agrupadas por domínio: condomínios, usuários, financeiro, reservas, marketplace, portaria, assembleias, comunicação, pânico

### 9.2 Middlewares de segurança

| Middleware | Função |
|------------|--------|
| `auth` | Autenticação obrigatória |
| `check.password` | Troca de senha obrigatória quando configurada |
| `require.condominium` | Exige condomínio ativo |
| `ensure.saas.subscription` | Valida assinatura SaaS |
| `ensure.full.financial` | Restringe módulos financeiros avançados |
| `restrict.defaulters` | Bloqueia ações para inadimplentes |

### 9.3 Agregados

- Permissões via tabela `agregado_permissions` (módulo + nível `view` / `crud`)
- Validação adicional em `SidebarHelper::canAccessModule()`
- Marketplace para agregados controlado por flag `marketplace_allow_agregados` no condomínio

---

## 10. Integrações externas

| Integração | Finalidade | Configuração |
|------------|------------|--------------|
| **Asaas** | Cobranças condominiais + assinatura SaaS | `config/services.php`; credenciais por condomínio ou plataforma |
| **WhatsApp (Evolution API)** | Notificações por grupo e individual | `config/whatsapp.php`; instância global + por condomínio |
| **Laravel Sanctum** | Autenticação API REST | `config/sanctum.php` |
| **DomPDF** | Relatórios, multas, histórico | — |
| **Maatwebsite Excel** | Exportações financeiras e livro de ocorrências | — |
| **SimpleSoftwareIO QRCode** | Moradores, pets, landing page | — |
| **Intervention Image** | Processamento de imagens | — |
| **Firebase** | Push notifications no app mobile | `celular/CondoCenterMobile` |
| **Redis (Predis)** | Cache e filas (opcional) | — |

### 10.1 Grupos WhatsApp configuráveis

Acesso, pânico, encomendas, reservas, cobranças, conversas, caronas, ordens de serviço, livro de ocorrências, assinatura SaaS, cadastro, assembleias, avisos gerais.

### 10.2 Webhooks

| Endpoint | Finalidade |
|----------|------------|
| `/webhooks/asaas` | Pagamentos gerais |
| `/webhooks/asaas/platform` | Assinatura SaaS |
| `/webhooks/asaas/condominium/{id}` | Pagamentos por condomínio |

---

## 11. Canais de entrega

### 11.1 Web (Blade + Bootstrap 5)

- Interface principal para todos os perfis
- Sidebar desktop + menu mobile responsivo
- Componentes Vue 3 / Alpine.js para interatividade
- DataTables (Yajra) para listagens server-side

### 11.2 API REST (`/api`)

- Autenticação Sanctum
- Middlewares: `require.condominium`, `ensure.saas.subscription`
- Módulos com API: cobranças, transações, reservas, encomendas, marketplace, assembleias, mensagens, conversas, notificações, espaços, pets, caronas, controle de acesso
- Health check: `GET /api/health`

### 11.3 App mobile (Expo/React Native)

- **Escopo atual:** autenticação + alerta de pânico + push (Firebase)
- Consome API Laravel
- Documentação: `README_MOBILE.md`

### 11.4 Matriz módulo × canal

| Módulo | Web | API | Mobile |
|--------|-----|-----|--------|
| Financeiro | ✅ | ✅ Parcial | ❌ |
| Reservas | ✅ | ✅ | ❌ |
| Marketplace | ✅ | ✅ | ❌ |
| Encomendas | ✅ | ✅ | ❌ |
| Controle de acesso | ✅ | ✅ | ❌ |
| Assembleias | ✅ | ✅ | ❌ |
| Comunicação | ✅ | ✅ | ❌ |
| Pets | ✅ | ✅ | ❌ |
| Caronas | ✅ | ✅ | ❌ |
| Pânico | ✅ | Parcial | ✅ |
| Ordens de serviço | ✅ | ❌ | ❌ |
| Livro de ocorrências | ✅ | ❌ | ❌ |
| Regimento interno | ✅ | ❌ | ❌ |
| Plataforma SaaS | ✅ | ❌ | ❌ |

---

## 12. Modelo comercial (SaaS)

### 12.1 Planos disponíveis

| Plano | Métrica | Ciclo | Preço referência |
|-------|---------|-------|------------------|
| Essencial — por unidade | Unidade | Mensal | R$ 4,90/unidade |
| Profissional — por unidade | Unidade | Trimestral | R$ 4,50/unidade |
| Corporativo — por usuário | Usuário ativo | Anual | R$ 2,90/usuário |
| Fixo — Mensal | Valor fixo | Mensal | R$ 299,90 |
| Fixo — Trimestral | Valor fixo | Trimestral | R$ 849,90 |
| Fixo — Anual | Valor fixo | Anual | R$ 3.199,90 |

### 12.2 Período de trial

- Essencial: 14 dias
- Profissional / Fixo mensal e trimestral: 7 dias
- Fixo anual: 14 dias
- Corporativo: sem trial

### 12.3 Formas de pagamento SaaS

- Boleto (planos Essencial, Profissional, Fixo mensal/trimestral)
- Cartão de crédito (Corporativo, Fixo anual)

### 12.4 Impacto da assinatura

- Assinatura **inativa** → middleware bloqueia acesso aos módulos do condomínio
- Síndico acessa área de gestão da assinatura para regularizar pagamento (PIX, boleto)

---

## 13. Requisitos não funcionais

### 13.1 Performance

- Listagens com paginação server-side (DataTables)
- Jobs assíncronos para conciliação bancária e processamento de extratos
- Cache Redis opcional para sessões e filas

### 13.2 Segurança

- Autenticação com verificação de e-mail
- Senhas com hash bcrypt
- Credenciais Asaas criptografadas no banco
- CSRF em formulários web
- Policies Laravel por recurso sensível
- Auditoria em models críticos
- Registro de IP/user-agent em alertas de pânico

### 13.3 Disponibilidade e ambientes

| Ambiente | Uso |
|----------|-----|
| `dev` | Desenvolvimento local (Laragon) |
| `test` | Testes automatizados e QA |
| `prod` | Produção |

### 13.4 Conformidade

- Dados pessoais (CPF, telefone, e-mail) tratados conforme LGPD
- Canais sigilosos (Fale com o Síndico, Livro de Ocorrências) com controle de acesso rigoroso
- Alterações de banco exigem aprovação formal, backup e migrações reversíveis

### 13.5 Usabilidade

- Interface responsiva (desktop + mobile web)
- Menu lateral adaptado por perfil
- Feedback visual em ações assíncronas (barras de progresso, spinners)
- Confirmação deliberada para ações críticas (slide-to-confirm no pânico)

### 13.6 Manutenibilidade

- Padrão Laravel com Services, Requests, Policies e Resources
- Documentação extensa em `DOCUMENTAÇÃO/` (~70 arquivos)
- Seeders para ambiente demo (Residencial Vista Verde)

---

## 14. Jornadas principais

### 14.1 Jornada: Morador paga taxa condominial

1. Morador acessa "Minhas Cobranças"
2. Visualiza cobranças pendentes/atrasadas
3. Seleciona cobrança e escolhe forma de pagamento (PIX/boleto/cartão)
4. Sistema gera cobrança via Asaas
5. Webhook confirma pagamento → status atualizado
6. Morador recebe notificação de confirmação

### 14.2 Jornada: Síndico gerencia inadimplência

1. Síndico acessa painel de adimplência
2. Identifica unidades em atraso
3. Envia lembretes (WhatsApp/e-mail)
4. Ativa restrição de inadimplentes (se configurado)
5. Inadimplente perde acesso a marketplace, reservas, OS e votação

### 14.3 Jornada: Morador aciona pânico

1. Morador clica em "Alerta de Pânico" (sidebar ou app)
2. Seleciona tipo de emergência
3. Adiciona informações opcionais
4. Desliza para confirmar (slide-to-confirm)
5. Sistema notifica todos moradores + síndico (database, e-mail, WhatsApp, push)
6. Síndico gerencia alerta até resolução

### 14.4 Jornada: Porteiro registra encomenda

1. Porteiro acessa módulo de encomendas
2. Registra chegada (unidade, remetente, descrição)
3. Morador recebe notificação
4. Morador retira presencialmente
5. Porteiro confirma retirada com código
6. Barra de progresso indica conclusão do fluxo

### 14.5 Jornada: Auto-cadastro de morador

1. Morador acessa página de registro
2. Informa código do condomínio
3. Seleciona unidade disponível
4. Preenche dados pessoais
5. Aguarda aprovação do síndico
6. Síndico aprova → morador recebe acesso completo

### 14.6 Jornada: Morador registra ocorrência

1. Morador acessa "Minhas Ocorrências"
2. Cria registro (tipo, descrição, foto opcional)
3. Síndico recebe notificação
4. Síndico registra ciência e adiciona comentário
5. Morador é notificado da ciência
6. Síndico pode ativar livro público (sem identificar autor)

---

## 15. Fora de escopo e roadmap

### 15.1 Gaps conhecidos (prioridade sugerida)

| Item | Prioridade | Descrição |
|------|------------|-----------|
| API para Ordens de Serviço | Alta | Paridade web/API para mobile futuro |
| API para Livro de Ocorrências | Alta | Permite app mobile de ocorrências |
| API para Regimento Interno | Média | Consulta mobile do regimento |
| App mobile completo | Média | Expandir além do pânico |
| Unificação web/API comunicação | Média | Reduzir duplicação de lógica |
| Integração Stripe | Baixa | Asaas atende mercado BR |
| IoT / CFTV / elevadores | Baixa | Fora do core atual |

### 15.2 Evoluções sugeridas

- Matriz formal papel × módulo × canal (web/API/mobile)
- Documentação de estados da assinatura SaaS e impacto em UX
- Dashboard analítico consolidado para administrador da plataforma
- Onboarding guiado para novos condomínios
- Pesquisa de satisfação (NPS) integrada

---

## 16. Glossário

| Termo | Definição |
|-------|-----------|
| **Tenant** | Condomínio isolado na plataforma multi-condomínio |
| **Morador responsável** | Usuário principal vinculado à unidade |
| **Agregado** | Dependente vinculado ao morador responsável |
| **Perfil ativo** | Papel (role) em uso na sessão atual |
| **Modo financeiro simplificado** | Prestação por upload, sem caixa completo |
| **Modo financeiro completo** | Caixa, conciliação, DRE, contas bancárias |
| **Inadimplente** | Unidade/usuário com cobranças vencidas não pagas |
| **Asaas** | Gateway de pagamento brasileiro (boleto, PIX, cartão) |
| **Evolution API** | Servidor de integração WhatsApp |
| **Slide-to-confirm** | Gestão de confirmação deslizando botão (evita acionamento acidental) |
| **Livro sigiloso** | Registro acessível apenas autor e síndico |

---

## 17. Referências

### Documentação interna

| Documento | Conteúdo |
|-----------|----------|
| [README.md](README.md) | Índice geral da documentação |
| [PROJETO_SUMMARY.md](PROJETO_SUMMARY.md) | Resumo técnico do projeto |
| [FUNCIONALIDADES.md](FUNCIONALIDADES.md) | Lista detalhada de funcionalidades |
| [API_DOCUMENTATION.md](API_DOCUMENTATION.md) | Documentação da API REST |
| [MENU_POR_PERFIL.md](MENU_POR_PERFIL.md) | Menus por perfil de usuário |
| [SIDEBAR_PERMISSIONS.md](SIDEBAR_PERMISSIONS.md) | Permissões do sidebar |
| [PERMISSOES_FINANCEIRAS.md](PERMISSOES_FINANCEIRAS.md) | Transparência financeira |
| [README_MOBILE.md](README_MOBILE.md) | App mobile de pânico |
| [REGRAS_PROJETO.md](REGRAS_PROJETO.md) | Governança de alterações em banco |
| [SETUP.md](SETUP.md) | Configuração inicial |
| [DEPLOY.md](DEPLOY.md) | Deploy em produção |

### Módulos específicos

| Documento | Módulo |
|-----------|--------|
| [SISTEMA_RESERVAS.md](SISTEMA_RESERVAS.md) | Reservas |
| [SISTEMA_PETS.md](SISTEMA_PETS.md) | Pets |
| [SISTEMA_ENCOMENDAS.md](SISTEMA_ENCOMENDAS.md) | Encomendas |
| [SISTEMA_REGIMENTO_INTERNO.md](SISTEMA_REGIMENTO_INTERNO.md) | Regimento |
| [SISTEMA_VERIFICACAO_QR_CODE_PETS.md](SISTEMA_VERIFICACAO_QR_CODE_PETS.md) | QR Code pets |
| [IMPLEMENTACAO_UNIDADES_USUARIOS.md](IMPLEMENTACAO_UNIDADES_USUARIOS.md) | Unidades e usuários |
| [MORADOR_RESPONSAVEL_UNIDADE.md](MORADOR_RESPONSAVEL_UNIDADE.md) | Morador responsável |
| [RESTRICAO_EDICAO_PERFIL.md](RESTRICAO_EDICAO_PERFIL.md) | Restrições de perfil |

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

*Documento gerado com base no estado atual do repositório CondoCenter. Para alterações de escopo, abrir revisão deste PRD com stakeholders.*
