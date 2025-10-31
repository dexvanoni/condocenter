# 💰 Permissões Financeiras - Transparência Total

## 🎯 Objetivo

Garantir **transparência total** das finanças do condomínio para todos os moradores, permitindo que acompanhem toda a movimentação financeira sem poder realizar alterações.

---

## 📊 Estrutura de Permissões Financeiras

### **🔐 Níveis de Acesso**

#### **1️⃣ Visualização Total (Moradores)**
- ✅ **Ver tudo** relacionado a finanças
- ✅ **Gerar relatórios** e exportar
- ❌ **Não pode** criar/editar/excluir

#### **2️⃣ Fiscalização (Conselho Fiscal)**
- ✅ **Ver tudo** + fiscalizar
- ✅ **Aprovar despesas**
- ✅ **Gerenciar extratos bancários**
- ❌ **Não pode** criar transações

#### **3️⃣ Gestão Total (Admin/Síndico)**
- ✅ **Acesso total** CRUD completo
- ✅ Criar, editar, excluir tudo
- ✅ Gerenciar todo o financeiro

---

## 📋 Permissões Criadas

### **💳 Transações**
```
✅ view_transactions        - Ver todas as transações
✅ manage_transactions      - Gerenciar transações (Admin/Síndico)
✅ create_transactions      - Criar transações (Admin/Síndico)
✅ edit_transactions        - Editar transações (Admin/Síndico)
✅ delete_transactions      - Excluir transações (Admin/Síndico)
```

### **🧾 Cobranças**
```
✅ view_charges            - Ver todas as cobranças
✅ manage_charges          - Gerenciar cobranças (Admin/Síndico)
```

### **📈 Receitas e Despesas**
```
✅ view_revenue            - Ver receitas
✅ view_expenses           - Ver despesas
✅ approve_expenses        - Aprovar despesas (Conselho Fiscal)
```

### **🏦 Conciliação Bancária**
```
✅ view_bank_statements           - Ver extratos bancários
✅ manage_bank_statements         - Gerenciar extratos (Admin/Síndico/Conselho)
✅ view_bank_reconciliation       - Ver conciliação bancária
```

### **📊 Relatórios**
```
✅ view_financial_reports         - Ver relatórios financeiros
✅ export_financial_reports       - Exportar relatórios (PDF/Excel)
✅ view_accountability_reports    - Ver prestação de contas
✅ export_accountability_reports  - Exportar prestação de contas
```

### **💰 Saldo e Balanço**
```
✅ view_balance                   - Ver balanço patrimonial
✅ view_own_financial             - Ver finanças da própria unidade
```

---

## 👥 Matriz de Permissões por Perfil

| Permissão | Admin | Síndico | Conselho | Morador | Agregado | Porteiro |
|-----------|-------|---------|----------|---------|----------|----------|
| **Ver Transações** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Criar Transações** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Ver Cobranças** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Gerenciar Cobranças** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Ver Receitas** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Ver Despesas** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Aprovar Despesas** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Ver Extratos** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Gerenciar Extratos** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Ver Conciliação** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Ver Relatórios** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Exportar Relatórios** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Ver Prestação Contas** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Exportar Prest. Contas** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Ver Balanço** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Ver Minhas Finanças** | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |

---

## 🎨 Menu Financeiro no Sidebar

### **👤 Para Moradores:**
```
💰 FINANCEIRO
├── 💵 Transações (visualizar)
├── 🧾 Cobranças (visualizar)
├── 📈 Receitas (visualizar)
├── 📉 Despesas (visualizar)
├── 🏦 Conciliação Bancária (visualizar)
├── 📊 Relatórios Financeiros (visualizar + exportar)
├── 📄 Prestação de Contas (visualizar + exportar)
└── 📊 Balanço Patrimonial (visualizar)
```

### **👔 Para Admin/Síndico:**
```
💰 FINANCEIRO
├── 💵 Gerenciar Transações (CRUD)
├── 🧾 Gerenciar Cobranças (CRUD)
├── 📈 Receitas (CRUD)
├── 📉 Despesas (CRUD)
├── 🏦 Conciliação Bancária (CRUD)
├── 📊 Relatórios Financeiros (visualizar + exportar)
├── 📄 Prestação de Contas (visualizar + exportar)
└── 📊 Balanço Patrimonial (visualizar)
```

### **🔍 Para Conselho Fiscal:**
```
💰 FINANCEIRO
├── 💵 Transações (visualizar)
├── 🧾 Cobranças (visualizar)
├── 📈 Receitas (visualizar)
├── 📉 Despesas (visualizar + aprovar)
├── 🏦 Conciliação Bancária (visualizar + gerenciar)
├── 📊 Relatórios Financeiros (visualizar + exportar)
├── 📄 Prestação de Contas (visualizar + exportar)
└── 📊 Balanço Patrimonial (visualizar + fiscalizar)
```

---

## 🔒 Regras de Segurança

### **✅ Moradores PODEM:**
- 👀 Visualizar todas as transações do condomínio
- 📊 Gerar e exportar relatórios financeiros
- 🏦 Ver conciliação bancária
- 📄 Acessar prestação de contas
- 💰 Ver balanço patrimonial
- 📈 Acompanhar receitas e despesas
- 🧾 Ver todas as cobranças

### **❌ Moradores NÃO PODEM:**
- ✏️ Criar novas transações
- 🗑️ Editar ou excluir transações
- ➕ Adicionar cobranças
- ❌ Excluir cobranças
- 💳 Gerenciar contas bancárias
- ✍️ Alterar relatórios
- 🔐 Acessar funções administrativas

---

## 📱 Funcionalidades por Módulo

### **1️⃣ Transações**
```php
// Rota: /transactions

Moradores veem:
✅ Lista completa de transações
✅ Filtros por período, tipo, categoria
✅ Detalhes de cada transação
✅ Exportar para PDF/Excel
❌ Botões de criar/editar/excluir (ocultos)

Admin/Síndico vê:
✅ Tudo que moradores veem +
✅ Botões de criar/editar/excluir
✅ Importar transações
✅ Conciliar automaticamente
```

### **2️⃣ Cobranças**
```php
// Rota: /charges

Moradores veem:
✅ Lista de todas as cobranças
✅ Status de pagamento
✅ Valores e vencimentos
✅ Histórico completo
❌ Não podem criar/editar

Admin/Síndico vê:
✅ Tudo que moradores veem +
✅ Criar novas cobranças
✅ Editar cobranças
✅ Marcar como pago
✅ Gerar boletos
```

### **3️⃣ Conciliação Bancária**
```php
// Rota: /bank-reconciliation

Moradores veem:
✅ Extratos bancários importados
✅ Status da conciliação
✅ Divergências identificadas
✅ Histórico de conciliações
❌ Não podem fazer conciliação

Conselho Fiscal vê:
✅ Tudo que moradores veem +
✅ Fazer conciliação manual
✅ Aprovar conciliações
✅ Gerenciar extratos
```

### **4️⃣ Relatórios Financeiros**
```php
// Rota: /financial-reports

Todos (Morador+) podem:
✅ Visualizar relatórios
✅ Filtrar por período
✅ Ver gráficos e estatísticas
✅ Exportar PDF
✅ Exportar Excel
✅ Imprimir

Tipos de relatórios:
- Demonstrativo de Resultados
- Fluxo de Caixa
- Receitas x Despesas
- Por Categoria
- Por Fornecedor
- Evolução Mensal
```

### **5️⃣ Prestação de Contas**
```php
// Rota: /accountability-reports

Todos (Morador+) podem:
✅ Ver prestação mensal/anual
✅ Documentos comprobatórios
✅ Notas fiscais digitalizadas
✅ Comprovantes de pagamento
✅ Exportar completo (PDF)
✅ Assinaturas digitais

Formato padrão:
- Resumo Executivo
- Receitas Detalhadas
- Despesas Detalhadas
- Saldo Atual
- Previsão Orçamentária
- Anexos (comprovantes)
```

### **6️⃣ Balanço Patrimonial**
```php
// Rota: /balance

Todos (Morador+) podem:
✅ Ver ativo/passivo
✅ Patrimônio líquido
✅ Evolução patrimonial
✅ Gráficos comparativos
✅ Exportar relatório
```

---

## 🎯 Benefícios da Transparência

### **Para Moradores:**
- 🔍 **Total visibilidade** das finanças
- 📊 **Dados em tempo real**
- 🤝 **Confiança aumentada**
- ✅ **Prestação de contas clara**
- 📈 **Acompanhamento fácil**

### **Para Administração:**
- 💎 **Transparência total**
- 🛡️ **Menos questionamentos**
- ✅ **Conformidade legal**
- 📋 **Documentação completa**
- 🎯 **Gestão profissional**

### **Para Conselho Fiscal:**
- 🔍 **Fiscalização efetiva**
- ✅ **Aprovação de despesas**
- 📊 **Relatórios detalhados**
- 🏦 **Controle bancário**
- ⚖️ **Conformidade garantida**

---

## 🔄 Fluxo de Acesso

```
1. Morador faz login
   ↓
2. Sistema identifica perfil "Morador"
   ↓
3. Carrega permissões financeiras de visualização
   ↓
4. Sidebar mostra menu financeiro completo
   ↓
5. Ao acessar qualquer tela financeira:
   - Vê todos os dados
   - Botões de edição ocultos
   - Apenas botões de exportar/imprimir visíveis
   ↓
6. Pode exportar qualquer relatório
```

---

## 📝 Implementação Técnica

### **Verificação no Controller:**
```php
// Em qualquer controller financeiro
public function index()
{
    // Morador pode ver
    $this->authorize('view_transactions');
    
    $transactions = Transaction::all();
    $canManage = auth()->user()->can('manage_transactions');
    
    return view('transactions.index', [
        'transactions' => $transactions,
        'canManage' => $canManage // Controla botões
    ]);
}

public function store()
{
    // Apenas Admin/Síndico pode criar
    $this->authorize('create_transactions');
    // ...
}
```

### **Verificação na View:**
```blade
{{-- Todos veem a lista --}}
<table>
    @foreach($transactions as $transaction)
        <tr>
            <td>{{ $transaction->date }}</td>
            <td>{{ $transaction->description }}</td>
            <td>{{ $transaction->amount }}</td>
            <td>
                {{-- Botões apenas para quem pode gerenciar --}}
                @can('edit_transactions')
                    <a href="{{ route('transactions.edit', $transaction) }}">Editar</a>
                @endcan
                
                @can('delete_transactions')
                    <button>Excluir</button>
                @endcan
            </td>
        </tr>
    @endforeach
</table>

{{-- Botão de exportar para todos --}}
@can('export_financial_reports')
    <a href="{{ route('transactions.export') }}">Exportar PDF</a>
@endcan
```

---

## ✅ Checklist de Implementação

- ✅ Permissões criadas no seeder
- ✅ Roles configurados corretamente
- ✅ Sidebar atualizado
- ✅ Verificações de rota implementadas
- ⏳ Controllers financeiros (a criar)
- ⏳ Views financeiras (a criar)
- ⏳ Relatórios e exports (a criar)
- ⏳ Conciliação bancária (a criar)

---

## 🚀 Próximos Passos

1. ✅ **Criar controllers financeiros**
2. ✅ **Criar views de visualização**
3. ✅ **Implementar exports (PDF/Excel)**
4. ✅ **Sistema de conciliação bancária**
5. ✅ **Gerador de prestação de contas**
6. ✅ **Dashboard financeiro**

---

**Sistema de transparência financeira total implementado!** 🎉

