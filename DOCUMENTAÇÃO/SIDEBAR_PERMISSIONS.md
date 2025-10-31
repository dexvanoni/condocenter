# 📋 Documentação - Sistema de Permissões do Sidebar

## 🎯 Visão Geral

Este documento detalha como o sistema de permissões do sidebar funciona, especialmente para **Agregados** com permissões granulares.

---

## 🔐 Hierarquia de Perfis

### **1️⃣ Administrador**
- ✅ **Acesso Total** a todas as funcionalidades
- ✅ Pode gerenciar: Unidades, Usuários, Condomínios
- ✅ Pode administrar: Espaços, Reservas, Assembleias
- ✅ Acesso total ao financeiro

### **2️⃣ Síndico**
- ✅ **Acesso Administrativo** (exceto Conselho Fiscal)
- ✅ Pode gerenciar: Unidades, Usuários (exceto criar Síndico/Conselho)
- ✅ Pode administrar: Espaços, Reservas, Assembleias
- ✅ Acesso total ao financeiro

### **3️⃣ Conselho Fiscal**
- ✅ **Acesso Financeiro Total**
- ✅ Visualização de transações e cobranças
- ✅ Fiscalização de prestação de contas
- ❌ Não gerencia espaços ou usuários

### **4️⃣ Morador**
- ✅ **Acesso de Usuário Final**
- ✅ Fazer reservas, criar anúncios, cadastrar pets
- ✅ Ver assembleias, participar
- ✅ Enviar mensagens
- ✅ Ver suas próprias finanças
- ❌ Não administra nada

### **5️⃣ Porteiro**
- ✅ **Acesso de Portaria**
- ✅ Controle de acesso (entradas/saídas)
- ✅ Registrar encomendas
- ❌ Não tem acesso a outras áreas

### **6️⃣ Agregado** ⭐ (Permissões Granulares)
- 🔹 **Permissões Personalizadas** por módulo
- 🔹 Cada módulo pode ter nível: **Visualização** ou **Acesso Completo**
- ❌ **NUNCA** pode administrar (criar espaços, aprovar reservas, gerenciar usuários)

---

## 📦 Módulos com Permissões Granulares (Agregados)

### **1. Espaços (spaces)**

#### **Apenas Visualização:**
```
✓ Ver calendário de reservas
✓ Ver detalhes dos espaços
✗ Não pode fazer reservas
```

#### **Acesso Completo:**
```
✓ Ver calendário de reservas
✓ Ver detalhes dos espaços
✓ Fazer novas reservas
✓ Cancelar suas reservas
✗ Não pode criar/editar espaços (admin)
✗ Não pode aprovar reservas (admin)
```

**Sidebar exibido:**
- 📅 Ver Reservas (visualização)
- 📅 Fazer Reserva (acesso completo)
- 📅 Minhas Reservas (acesso completo)

---

### **2. Marketplace (marketplace)**

#### **Apenas Visualização:**
```
✓ Ver anúncios
✓ Filtrar e pesquisar
✗ Não pode criar anúncios
```

#### **Acesso Completo:**
```
✓ Ver anúncios
✓ Filtrar e pesquisar
✓ Criar novos anúncios
✓ Editar seus anúncios
✓ Excluir seus anúncios
```

**Sidebar exibido:**
- 🛍️ Ver Anúncios (ambos)
- ➕ Meus Anúncios (acesso completo)

---

### **3. Pets (pets)**

#### **Apenas Visualização:**
```
✓ Ver lista de pets do condomínio
✓ Ver detalhes dos pets
✗ Não pode cadastrar pets
```

#### **Acesso Completo:**
```
✓ Ver lista de pets do condomínio
✓ Ver detalhes dos pets
✓ Cadastrar novos pets
✓ Editar seus pets
✓ Excluir seus pets
```

**Sidebar exibido:**
- 🐾 Ver Pets (ambos)
- ➕ Meus Pets (acesso completo)

---

### **4. Notificações (notifications)**

```
✓ Receber notificações do sistema
✓ Marcar como lido
✓ Ver histórico
```

**Sidebar exibido:**
- 🔔 Notificações

---

### **5. Encomendas (packages)**

#### **Apenas Visualização:**
```
✓ Ver suas encomendas
✓ Notificações de chegada
✗ Não pode registrar (portaria)
```

#### **Acesso Completo:**
```
✓ Ver suas encomendas
✓ Notificações de chegada
✓ Marcar como retirada
```

**Sidebar exibido:**
- 📦 Minhas Encomendas

---

### **6. Mensagens (messages)**

#### **Apenas Visualização:**
```
✓ Ver mensagens recebidas
✗ Não pode enviar mensagens
```

#### **Acesso Completo:**
```
✓ Ver mensagens recebidas
✓ Enviar novas mensagens
✓ Responder mensagens
```

**Sidebar exibido:**
- 💬 Mensagens (ambos)
- ➕ Nova Mensagem (acesso completo)

---

### **7. Financeiro (financial)**

```
✓ Ver apenas informações limitadas
✓ Ver cobranças da sua unidade
✗ Nunca acessa financeiro completo
```

**Sidebar:** Não exibido para agregados

---

## 🚫 Restrições Absolutas para Agregados

### **NUNCA podem:**
- ❌ Gerenciar espaços (criar, editar, excluir espaços)
- ❌ Aprovar reservas de outros usuários
- ❌ Criar reservas recorrentes
- ❌ Acessar gestão de usuários
- ❌ Acessar gestão de unidades
- ❌ Acessar assembleias
- ❌ Registrar encomendas (função do porteiro)
- ❌ Controle de acesso (função do porteiro)
- ❌ Acessar financeiro completo
- ❌ Administrar qualquer módulo

---

## 🔧 Como Funciona o SidebarHelper

### **Métodos Principais:**

```php
// Verifica se pode acessar um módulo
SidebarHelper::canAccessModule($user, 'spaces')

// Verifica se pode fazer CRUD em um módulo
SidebarHelper::canCrudModule($user, 'spaces')

// Verificações específicas
SidebarHelper::canMakeReservations($user)
SidebarHelper::canViewReservations($user)
SidebarHelper::canManageSpaces($user) // Sempre false para agregados
SidebarHelper::canApproveReservations($user) // Sempre false para agregados
SidebarHelper::canCreateMarketplace($user)
SidebarHelper::canManagePets($user)
SidebarHelper::canSendMessages($user)
```

---

## 📊 Matriz de Permissões

| Módulo | Admin/Síndico | Morador | Agregado (View) | Agregado (CRUD) | Porteiro |
|--------|---------------|---------|-----------------|-----------------|----------|
| **Gestão** | ✅ Total | ❌ | ❌ | ❌ | ❌ |
| **Financeiro** | ✅ Total | ✅ Próprio | ❌ | ❌ | ❌ |
| **Criar Espaços** | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Aprovar Reservas** | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Fazer Reservas** | ✅ | ✅ | ❌ | ✅ | ❌ |
| **Ver Reservas** | ✅ | ✅ | ✅ | ✅ | ❌ |
| **Criar Anúncios** | ✅ | ✅ | ❌ | ✅ | ❌ |
| **Ver Anúncios** | ✅ | ✅ | ✅ | ✅ | ❌ |
| **Cadastrar Pets** | ✅ | ✅ | ❌ | ✅ | ❌ |
| **Ver Pets** | ✅ | ✅ | ✅ | ✅ | ❌ |
| **Assembleias** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Enviar Mensagens** | ✅ | ✅ | ❌ | ✅ | ❌ |
| **Ver Encomendas** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Registrar Encomendas** | ✅ | ❌ | ❌ | ❌ | ✅ |
| **Controle Acesso** | ✅ | ❌ | ❌ | ❌ | ✅ |

---

## 🎨 Estrutura do Sidebar

### **Seções:**

1. **Dashboard** (Todos)
2. **Gestão** (Admin/Síndico)
   - Unidades
   - Usuários
   - Condomínios

3. **Financeiro** (Admin/Síndico/Conselho/Morador)
   - Transações
   - Cobranças
   - Minhas Finanças

4. **Espaços** (Dinâmico por permissão)
   - Fazer Reserva
   - Minhas Reservas / Ver Reservas
   - [Separador]
   - Gerenciar Espaços (Admin/Síndico)
   - Aprovar Reservas (Admin/Síndico)
   - Reservas Recorrentes (Admin/Síndico)

5. **Marketplace** (Dinâmico por permissão)
   - Ver Anúncios
   - Meus Anúncios (se CRUD)

6. **Pets** (Dinâmico por permissão)
   - Ver Pets
   - Meus Pets (se CRUD)

7. **Assembleias** (Exceto Agregados)
   - Ver Assembleias
   - Nova Assembleia (Admin/Síndico)

8. **Encomendas** (Dinâmico por perfil)
   - Registrar Encomenda (Porteiro)
   - Todas Encomendas / Minhas Encomendas

9. **Portaria** (Apenas Porteiro)
   - Controle de Acesso

10. **Comunicação** (Todos)
    - Mensagens
    - Nova Mensagem (se permitido)
    - Notificações

11. **Alerta de Pânico** (Conforme permissão)

---

## ✅ Validação de Consistência

### **Checklist de Segurança:**

- ✅ Agregados nunca podem acessar gestão administrativa
- ✅ Agregados nunca podem aprovar/gerenciar reservas de outros
- ✅ Agregados nunca podem acessar assembleias
- ✅ Agregados nunca podem acessar financeiro completo
- ✅ Porteiros só veem portaria e encomendas
- ✅ Permissões granulares são verificadas em duas camadas (Model + Helper)
- ✅ Sidebar adapta-se dinamicamente às permissões
- ✅ Botões de ação rápida também respeitam permissões

---

## 🔄 Fluxo de Verificação

```
1. Usuário faz login
   ↓
2. Sistema identifica perfil(is)
   ↓
3. Se Agregado → Carrega permissões granulares
   ↓
4. SidebarHelper verifica cada módulo
   ↓
5. Sidebar renderiza apenas itens permitidos
   ↓
6. Middleware protege rotas (check.agregado.permission)
```

---

## 📝 Exemplo de Uso no Blade

```blade
{{-- Verificar acesso ao módulo --}}
@if(SidebarHelper::canAccessModule($user, 'spaces'))
    <li class="nav-item">
        <a href="{{ route('reservations.index') }}">Ver Reservas</a>
    </li>
@endif

{{-- Verificar CRUD --}}
@if(SidebarHelper::canMakeReservations($user))
    <li class="nav-item">
        <a href="{{ route('reservations.create') }}">Fazer Reserva</a>
    </li>
@endif

{{-- Garantir que agregados não vejam admin --}}
@if(SidebarHelper::canManageSpaces($user))
    <li class="nav-item">
        <a href="{{ route('spaces.index') }}">Gerenciar Espaços</a>
    </li>
@endif
```

---

## 🎯 Resultado Final

✅ **Sidebar 100% dinâmico e seguro**
✅ **Zero inconsistências de permissão**
✅ **Agregados com controle granular perfeito**
✅ **Cada perfil vê apenas o que pode acessar**
✅ **Administração protegida de usuários comuns**

---

**Sistema implementado e documentado! 🚀**

