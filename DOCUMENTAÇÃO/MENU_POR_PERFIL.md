# 📋 Menu Lateral por Perfil - CondoManager

Este documento mostra exatamente quais itens do menu cada perfil visualiza.

---

## 👑 ADMINISTRADOR (Acesso Total)

```
Dashboard
├── GESTÃO
│   ├── Unidades
│   └── Usuários
├── FINANCEIRO
│   ├── Transações
│   └── Cobranças
├── RESERVAS
│   ├── Agendar
│   ├── Gerenciar Espaços
│   ├── Gerenciar Reservas
│   └── Reservas Recorrentes
├── COMUNIDADE
│   ├── Marketplace
│   ├── Pets
│   └── Assembleias
├── PORTARIA
│   ├── Controle de Acesso
│   └── Encomendas
├── COMUNICAÇÃO
│   └── Mensagens
└── 🚨 ALERTA DE PÂNICO
```

**Total de itens:** 16 + PÂNICO

---

## 🏛️ SÍNDICO (Tudo exceto Conselho Fiscal)

```
Dashboard
├── GESTÃO
│   ├── Unidades
│   └── Usuários
├── FINANCEIRO
│   ├── Transações
│   └── Cobranças
├── RESERVAS
│   ├── Agendar
│   ├── Gerenciar Espaços
│   ├── Gerenciar Reservas
│   └── Reservas Recorrentes
├── COMUNIDADE
│   ├── Marketplace
│   ├── Pets
│   └── Assembleias
├── PORTARIA (Visualização)
│   ├── Controle de Acesso
│   └── Encomendas
├── COMUNICAÇÃO
│   └── Mensagens
└── (Sem PÂNICO - Síndico já tem autoridade)
```

**Total de itens:** 15

**Diferenças do Admin:**
- ❌ Não pode criar/editar perfis "Síndico" e "Conselho Fiscal"
- ❌ Não tem botão de PÂNICO (já tem autoridade no local)

---

## 🏠 MORADOR (Acesso Geral - Sem Admin)

```
Dashboard
├── (Sem GESTÃO - não vê Unidades nem Usuários)
├── FINANCEIRO
│   ├── Transações (apenas suas)
│   └── Cobranças (apenas suas)
├── RESERVAS
│   └── Agendar (pode fazer reservas)
├── COMUNIDADE
│   ├── Marketplace (pode criar e ver)
│   ├── Pets (pode cadastrar)
│   └── Assembleias (pode votar)
├── COMUNICAÇÃO
│   └── Mensagens (pode enviar ao síndico)
└── 🚨 ALERTA DE PÂNICO
```

**Total de itens:** 8 + PÂNICO

**O que Morador PODE fazer:**
- ✅ Ver suas próprias cobranças e transações
- ✅ Fazer reservas de espaços
- ✅ Cadastrar pets
- ✅ Criar anúncios no marketplace
- ✅ Votar em assembleias
- ✅ Enviar mensagens ao síndico
- ✅ Enviar alerta de pânico

**O que Morador NÃO pode fazer:**
- ❌ Gerenciar unidades ou usuários
- ❌ Ver dados financeiros de outros
- ❌ Aprovar reservas
- ❌ Gerenciar espaços
- ❌ Acessar portaria

---

## 👨‍👩‍👧‍👦 AGREGADO (Acesso Muito Limitado)

```
Dashboard
├── (Sem GESTÃO)
├── (Sem FINANCEIRO) ❌
├── (Sem RESERVAS) ❌
├── COMUNIDADE
│   ├── Marketplace (apenas visualização)
│   ├── Pets (apenas visualização)
│   └── Assembleias (apenas visualização)
├── COMUNICAÇÃO
│   └── Mensagens (apenas receber)
└── (Sem PÂNICO) ❌
```

**Total de itens:** 5 itens

**O que Agregado PODE fazer:**
- ✅ Ver marketplace (não pode criar)
- ✅ Ver pets
- ✅ Ver assembleias (não pode votar)
- ✅ Ver notificações
- ✅ Ver mensagens recebidas

**O que Agregado NÃO pode fazer:**
- ❌ Acessar qualquer coisa financeira
- ❌ Fazer reservas
- ❌ Enviar mensagens ao síndico
- ❌ Criar pets ou anúncios
- ❌ Votar em assembleias
- ❌ Enviar pânico

**Restrições Especiais:**
- Deve estar vinculado a um Morador
- Herda a unidade do morador vinculado
- Não aparece no cadastro de reservas

---

## 🚪 PORTEIRO (Apenas Portaria)

```
Dashboard
├── (Sem GESTÃO)
├── (Sem FINANCEIRO)
├── (Sem RESERVAS)
├── COMUNIDADE
│   └── Pets (apenas visualização - para identificar)
├── PORTARIA
│   ├── Controle de Acesso
│   └── Encomendas
└── COMUNICAÇÃO
    └── Mensagens (receber apenas)
```

**Total de itens:** 5 itens

**O que Porteiro PODE fazer:**
- ✅ Registrar entradas/visitantes
- ✅ Registrar encomendas
- ✅ Ver lista de pets (para identificação)
- ✅ Visualizar notificações

**O que Porteiro NÃO pode fazer:**
- ❌ Qualquer coisa fora de Portaria
- ❌ Acessar financeiro
- ❌ Ver unidades ou usuários completos
- ❌ Fazer reservas

**Observação:** Porteiro não precisa de unidade vinculada.

---

## 💰 CONSELHO FISCAL (Fiscalização Financeira)

```
Dashboard
├── GESTÃO (Visualização)
│   ├── Unidades (apenas visualizar)
│   └── Usuários (apenas visualizar)
├── FINANCEIRO (Total)
│   ├── Transações (todos)
│   ├── Cobranças (todos)
│   └── Extratos Bancários (gerenciar)
├── (Sem RESERVAS)
├── COMUNIDADE
│   └── Assembleias (participar)
└── COMUNICAÇÃO
    └── Mensagens
```

**Total de itens:** 8 itens

**O que Conselho PODE fazer:**
- ✅ Ver TODAS as transações financeiras
- ✅ Ver TODAS as cobranças
- ✅ Gerenciar extratos bancários
- ✅ Ver relatórios financeiros
- ✅ Ver assembleias
- ✅ Ver unidades e usuários (apenas visualização)

**O que Conselho NÃO pode fazer:**
- ❌ Editar unidades ou usuários
- ❌ Fazer/aprovar reservas
- ❌ Gerenciar espaços
- ❌ Acessar portaria

**Importante:** Conselho Fiscal tem acesso TOTAL ao financeiro, mas não pode editar nada administrativo.

---

## 📝 SECRETARIA (Visualização Geral)

```
Dashboard
├── GESTÃO (Visualização)
│   ├── Unidades (apenas visualizar)
│   └── Usuários (apenas visualizar)
├── FINANCEIRO (Visualização)
│   ├── Transações (apenas visualizar)
│   └── Cobranças (apenas visualizar)
├── RESERVAS (Visualização)
│   └── Agendar (apenas visualizar)
├── COMUNIDADE
│   ├── Marketplace
│   ├── Pets
│   └── Assembleias
├── PORTARIA (Visualização)
│   ├── Controle de Acesso (visualizar)
│   └── Encomendas (visualizar)
└── COMUNICAÇÃO
    └── Mensagens
```

**Total de itens:** 12 itens

**Perfil de apoio:** Pode ver quase tudo, mas não pode editar/criar.

---

## 🎯 COMPARAÇÃO VISUAL POR SEÇÃO

### GESTÃO (Unidades + Usuários)
| Perfil | Unidades | Usuários |
|--------|----------|----------|
| Administrador | ✅ CRUD Completo | ✅ CRUD Completo |
| Síndico | ✅ CRUD Completo | ✅ CRUD (exceto Síndico/Conselho) |
| Conselho Fiscal | ✅ Apenas Ver | ✅ Apenas Ver |
| Secretaria | ✅ Apenas Ver | ✅ Apenas Ver |
| Morador | ❌ Sem Acesso | ❌ Sem Acesso |
| Agregado | ❌ Sem Acesso | ❌ Sem Acesso |
| Porteiro | ❌ Sem Acesso | ❌ Sem Acesso |

### FINANCEIRO
| Perfil | Transações | Cobranças | Extratos |
|--------|-----------|-----------|----------|
| Administrador | ✅ Todas | ✅ Todas | ✅ Gerenciar |
| Síndico | ✅ Todas | ✅ Todas | ✅ Gerenciar |
| Conselho Fiscal | ✅ Todas | ✅ Todas | ✅ Gerenciar |
| Morador | ✅ Apenas Suas | ✅ Apenas Suas | ❌ |
| Secretaria | ✅ Apenas Ver | ✅ Apenas Ver | ❌ |
| Agregado | ❌ Bloqueado | ❌ Bloqueado | ❌ |
| Porteiro | ❌ Bloqueado | ❌ Bloqueado | ❌ |

### RESERVAS
| Perfil | Agendar | Gerenciar | Aprovar |
|--------|---------|-----------|---------|
| Administrador | ✅ | ✅ | ✅ |
| Síndico | ✅ | ✅ | ✅ |
| Morador | ✅ | ❌ | ❌ |
| Secretaria | Ver | ❌ | ❌ |
| Agregado | ❌ Bloqueado | ❌ | ❌ |
| Conselho Fiscal | ❌ | ❌ | ❌ |
| Porteiro | ❌ | ❌ | ❌ |

### COMUNIDADE
| Perfil | Marketplace | Pets | Assembleias |
|--------|-------------|------|-------------|
| Administrador | ✅ CRUD | ✅ CRUD | ✅ Gerenciar |
| Síndico | ✅ Gerenciar | ✅ Ver Todos | ✅ Criar/Gerenciar |
| Morador | ✅ Criar/Ver | ✅ Cadastrar | ✅ Votar |
| Agregado | ✅ Apenas Ver | ✅ Apenas Ver | ✅ Apenas Ver |
| Secretaria | ✅ Ver | ✅ Ver | ✅ Ver |
| Conselho Fiscal | ❌ | ❌ | ✅ Ver |
| Porteiro | ❌ | ✅ Ver (identificar) | ❌ |

### PORTARIA
| Perfil | Controle Acesso | Encomendas |
|--------|-----------------|------------|
| Porteiro | ✅ Registrar | ✅ Registrar |
| Administrador | ✅ Ver | ✅ Ver |
| Síndico | ✅ Ver | ✅ Ver |
| Outros | ❌ | ❌ |

### COMUNICAÇÃO
| Perfil | Mensagens | Enviar p/ Síndico |
|--------|-----------|-------------------|
| Administrador | ✅ Todas | ✅ |
| Síndico | ✅ Todas | - |
| Morador | ✅ Suas + Enviar | ✅ |
| Agregado | ✅ Receber | ❌ Bloqueado |
| Todos Outros | ✅ | ✅ |

### EMERGÊNCIA
| Perfil | Botão PÂNICO |
|--------|--------------|
| Morador | ✅ |
| Secretaria | ✅ |
| Agregado | ❌ Bloqueado |
| Síndico | ❌ (já tem autoridade) |
| Admin | ❌ (já tem autoridade) |
| Porteiro | ✅ |
| Conselho | ✅ |

---

## 🎨 HIERARQUIA VISUAL DO MENU

### Seções (em ordem)
1. **Dashboard** - Sempre visível para todos
2. **GESTÃO** - Apenas Admin, Síndico, Conselho Fiscal, Secretaria
3. **FINANCEIRO** - Admin, Síndico, Conselho, Morador (limitado)
4. **RESERVAS** - Admin, Síndico, Morador
5. **COMUNIDADE** - Todos (com restrições)
6. **PORTARIA** - Porteiro (registro), Admin/Síndico (visualização)
7. **COMUNICAÇÃO** - Todos
8. **EMERGÊNCIA** - Morador, Porteiro, Secretaria, Conselho

### Características Visuais
- ✅ Seções com labels em maiúsculas
- ✅ Espaçamento entre seções (`mt-3`)
- ✅ Ícones intuitivos para cada item
- ✅ Destaque visual para item ativo
- ✅ Badge de contador em Mensagens
- ✅ Botão de PÂNICO em vermelho destacado
- ✅ Dropdown de perfil no topo (se múltiplos)

---

## 🔄 COMPORTAMENTO DO DROPDOWN DE PERFIS

### Quando aparece
- ✅ Apenas se o usuário tiver 2+ perfis
- ✅ Mostra o perfil atualmente ativo
- ✅ Lista todos os perfis do usuário

### Ao trocar de perfil
- ✅ Faz requisição AJAX
- ✅ Atualiza sessão sem logout
- ✅ Recarrega página com novo menu
- ✅ Permissões mudam automaticamente
- ✅ Registra a troca no histórico

### Exemplo: Usuário com perfis "Síndico" + "Morador"
- Se selecionar **Síndico**: Vê GESTÃO, pode gerenciar reservas
- Se selecionar **Morador**: Não vê GESTÃO, só pode agendar

---

## 📊 ESTATÍSTICAS DE ACESSO

### Quantidade de Itens por Perfil
| Perfil | Itens Menu | Seções |
|--------|------------|--------|
| Administrador | 16 | 7 |
| Síndico | 15 | 6 |
| Secretaria | 12 | 6 |
| Morador | 8 | 4 |
| Conselho Fiscal | 8 | 4 |
| Porteiro | 5 | 3 |
| Agregado | 5 | 2 |

---

## 🎯 CASOS ESPECIAIS

### Agregado vinculado a Morador
- **Menu:** Apenas Comunidade (ver) + Mensagens (receber)
- **Bloqueios:** Financeiro, Reservas, Pânico, Enviar Mensagens
- **Não pode:** Criar, editar ou deletar nada
- **Pode:** Apenas visualizar conteúdo permitido

### Porteiro sem Unidade
- **Menu:** Apenas Portaria + Pets (identificação)
- **Bloqueios:** Tudo exceto controle de acesso e encomendas
- **Não precisa:** Unidade vinculada

### Conselho Fiscal
- **Menu:** FINANCEIRO completo + Assembleias
- **Bloqueios:** Não pode editar unidades/usuários
- **Foco:** Apenas fiscalização financeira
- **Diferença do Síndico:** Não gerencia nada, apenas fiscaliza

---

## 🚀 NAVEGAÇÃO ADAPTATIVA

### Baseado no Perfil Ativo
```javascript
// O menu se adapta automaticamente ao perfil selecionado
// Exemplo:

Usuário: João Silva
Perfis: [Morador, Síndico]

┌─────────────────────────┐
│ Perfil Ativo: Síndico ▼ │ ← Dropdown
└─────────────────────────┘

Menu mostra: GESTÃO + FINANCEIRO + RESERVAS + etc (15 itens)

[Usuário troca para "Morador"]

┌─────────────────────────┐
│ Perfil Ativo: Morador ▼ │
└─────────────────────────┘

Menu mostra: Apenas FINANCEIRO (próprio) + RESERVAS + COMUNIDADE (8 itens)
```

---

## ✨ MELHORIAS IMPLEMENTADAS NO SIDEBAR

### Organização
- ✅ Agrupamento lógico por funcionalidade
- ✅ Separadores visuais entre seções
- ✅ Labels de seção em maiúsculas
- ✅ Ordem do mais importante ao menos importante

### UX
- ✅ Ícones consistentes e intuitivos
- ✅ Contador de mensagens não lidas
- ✅ Destaque para botão de PÂNICO
- ✅ Dropdown de perfil integrado
- ✅ Nome do condomínio visível

### Performance
- ✅ Apenas carrega itens que o usuário pode acessar
- ✅ Usa `@canany` para otimizar verificações
- ✅ Cache de permissões do Spatie

---

## 🎨 CÓDIGO DO DROPDOWN DE PERFIL

```php
@if(Auth::user()->hasMultipleRoles())
<div class="dropdown me-3">
    <button class="btn btn-sm btn-outline-primary dropdown-toggle" 
            type="button" id="profileDropdown" data-bs-toggle="dropdown">
        <i class="bi bi-person-badge"></i> 
        {{ session('active_role', Auth::user()->roles->first()->name) }}
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        @foreach(Auth::user()->roles as $role)
        <li>
            <a class="dropdown-item {{ session('active_role') === $role->name ? 'active' : '' }}" 
               href="#" onclick="switchProfile('{{ $role->name }}'); return false;">
                {{ $role->name }}
            </a>
        </li>
        @endforeach
    </ul>
</div>
@else
<span class="text-muted me-3">
    <i class="bi bi-person-circle"></i>
    {{ Auth::user()->roles->pluck('name')->join(', ') }}
</span>
@endif
```

---

## 🔐 BLOQUEIOS AUTOMÁTICOS

### Middleware `CheckActiveProfile`
- Força seleção de perfil se usuário tiver múltiplos
- Valida se perfil selecionado ainda pertence ao usuário
- Bloqueia acesso sem perfil ativo

### Middleware `CheckPasswordChange`
- Bloqueia acesso se senha for temporária
- Permite apenas: troca de senha e logout
- Redireciona para `/password/change`

### Resultado
- Menu sempre reflete exatamente o que o usuário PODE fazer
- Impossível acessar algo sem permissão
- UX clara sobre o que está disponível

---

**✨ Menu totalmente adaptável, organizado e seguro!**

