# 📅 CondoManager - Sistema de Reservas/Agendamentos

## Sistema Completo e Automático

---

## 🎯 Regras de Negócio Implementadas

### 1. ✅ Aprovação Automática e Imediata
- **Não há aprovação manual** do síndico
- Assim que o morador reserva, está **CONFIRMADA**
- Status já vai como `approved`
- `approved_at` = now()
- `approved_by` = próprio usuário que reservou

### 2. ✅ Uma Reserva por Local por Dia
- **Validação rigorosa:** Apenas 1 reserva por espaço por data
- Se já existe reserva para aquela data, **não permite**
- Mensagem clara: "Este espaço já está reservado para esta data"
- Usuário precisa escolher **outra data**

### 3. ✅ Verificação de Disponibilidade
- Sistema verifica em tempo real
- Ao selecionar data, mostra:
  - ✅ Verde: "Data disponível!"
  - ❌ Vermelho: "Data indisponível!"
- Botão de confirmar só fica ativo se data disponível

### 4. ✅ Cobrança de Taxa (Asaas)
- Síndico define se tem taxa ao criar espaço
- Se `price_per_hour` > 0, **gera cobrança automática**
- Cobrança via Asaas (PIX, Cartão, Boleto)
- Vencimento: **1 dia antes** da reserva
- Job assíncrono processa

---

## 👨‍💼 Fluxo do Síndico

### Gerenciar Espaços

**Acesso:** Sidebar → **Espaços**

#### 1. Listar Espaços
- Ver todos os espaços cadastrados
- Cards com informações:
  - Nome, tipo, capacidade
  - Taxa de reserva
  - Limite mensal
  - Total de reservas
- Botões: Editar, Remover

#### 2. Criar Novo Espaço

**Clique:** "Novo Espaço"

**Formulário:**
```
Nome: Churrasqueira 1
Tipo: Churrasqueira
Descrição: Churrasqueira com pia e tomadas
Capacidade: 20 pessoas
Taxa de Reserva: R$ 50,00
Limite de Reservas por Mês: 1
Disponível das: 08:00
Até: 22:00
Regras: Proibido som alto após 22h
```

**Ao salvar:**
- ✅ Espaço criado
- ✅ Aparece na lista
- ✅ Moradores já podem reservar

#### 3. Editar Espaço

- Pode alterar nome, descrição, taxa
- Pode ativar/desativar
- **Importante:** Alterar taxa **não afeta** reservas já feitas

#### 4. Remover Espaço

- Só permite se **não houver reservas futuras**
- Confirmação obrigatória

### Acompanhar Reservas

**Acesso:** Dashboard do Síndico

- Próximas 5 reservas exibidas
- Espaço, morador, data
- Link para ver todas: `/reservations`

---

## 👤 Fluxo do Morador

### Fazer Reserva

**Acesso:** Sidebar → **Reservas**

#### 1. Ver Espaços Disponíveis

- Cards com todos os espaços
- Informações visíveis:
  - Nome do espaço
  - Taxa (ou "GRATUITO")
  - Capacidade
  - Limite mensal
- Botão: "Reservar"

#### 2. Clicar em "Reservar"

Modal abre com:
- Seleção de espaço (já pre-selecionado)
- Informações do espaço exibidas
- Campo de data

#### 3. Escolher Data

- Calendário com date picker
- Mínimo: hoje
- Ao escolher, sistema **verifica imediatamente**:
  - ✅ "Data disponível!" → pode continuar
  - ❌ "Data indisponível!" → precisa escolher outra

#### 4. Confirmar Reserva

- Botão: "Confirmar Reserva"
- Requisição vai para API
- Validações automáticas:
  - Espaço existe?
  - Está ativo?
  - Data já reservada? ❌
  - Limite mensal OK? ✅
  - Conflito de horário? ❌

#### 5. Aprovação Automática

- Sistema aprova **IMEDIATAMENTE**
- Mostra tela de confirmação:
  ```
  ✅ Reserva Confirmada Automaticamente!
  
  Espaço: Churrasqueira 1
  Data: 15/11/2025
  
  💳 Uma cobrança de R$ 50,00 será gerada via Asaas.
  Você receberá o link de pagamento (PIX/Cartão) em breve.
  ```

#### 6. Cobrança Gerada (se houver taxa)

- Job `GenerateReservationPayment` é despachado
- Cria cliente no Asaas (se não existir)
- Gera cobrança no Asaas
- Tipos de pagamento disponíveis:
  - PIX (QR Code)
  - Cartão de Crédito
  - Boleto
  - Cartão de Débito
- Vencimento: **1 dia antes** da reserva

#### 7. Receber Links de Pagamento

- Notificação no sistema
- Email com links
- Pode pagar por:
  - PIX: escanear QR Code
  - Cartão: formulário seguro Asaas
  - Boleto: baixar e pagar

---

## 🔧 Tecnologias Utilizadas

### Backend
- **Laravel 12** - Framework
- **Eloquent ORM** - Relacionamentos
- **Jobs/Queues** - Processamento assíncrono
- **AsaasService** - Integração pagamento
- **Policies** - Autorização
- **Validations** - Segurança

### Frontend
- **Bootstrap 5** - UI components
- **Vue 3** - Componente de calendário
- **Axios** - Requisições AJAX
- **JavaScript** - Validações em tempo real

### APIs
- **Asaas API** - Gateway de pagamento
- **RESTful API** - Endpoints próprios

---

## 🗂️ Arquivos do Sistema de Reservas

```
app/
├── Http/Controllers/
│   ├── SpaceController.php ✅ NOVO - Gestão de espaços
│   ├── ReservationController.php ✅ Atualizado
│   └── Api/
│       ├── SpaceController.php ✅
│       └── ReservationController.php ✅ Atualizado com novas regras
├── Models/
│   ├── Space.php ✅
│   └── Reservation.php ✅
├── Jobs/
│   ├── GenerateReservationPayment.php ✅ NOVO
│   └── SendReservationNotification.php ✅
└── Services/
    └── AsaasService.php ✅

database/migrations/
├── 2025_10_07_011128_create_spaces_table.php ✅
└── 2025_10_07_011129_create_reservations_table.php ✅

resources/views/
├── spaces/
│   ├── index.blade.php ✅ NOVO - Lista espaços
│   ├── create.blade.php ✅ NOVO - Criar espaço
│   └── edit.blade.php ✅ NOVO - Editar espaço
├── reservations/
│   └── index.blade.php ✅ ATUALIZADO - Sistema completo
└── components/
    └── ReservationCalendar.vue ✅

routes/
├── web.php ✅ Atualizado com rotas de spaces
└── api.php ✅ Endpoints completos
```

---

## 📋 Exemplos de Uso

### Exemplo 1: Churrasqueira Gratuita

**Síndico cria:**
```
Nome: Churrasqueira 1
Taxa: R$ 0,00 (gratuito)
Limite: 1 reserva por mês
```

**Morador reserva:**
- Escolhe data disponível
- Confirma
- ✅ Reserva aprovada automaticamente
- Sem cobrança

### Exemplo 2: Salão com Taxa

**Síndico cria:**
```
Nome: Salão de Festas
Taxa: R$ 150,00
Limite: 1 reserva por mês
```

**Morador reserva para 15/11/2025:**
- Escolhe data (disponível)
- Confirma
- ✅ Reserva aprovada automaticamente
- 💳 Cobrança de R$ 150,00 gerada via Asaas
- Vencimento: 14/11/2025 (1 dia antes)
- Recebe email com link de pagamento PIX/Cartão

**Morador paga:**
- Escaneia QR Code PIX ou paga com cartão
- Webhook confirma pagamento
- Status atualizado para "paid"
- Morador pode usar o espaço no dia 15/11

### Exemplo 3: Tentativa de Reserva Conflitante

**Morador 1:**
- Reserva Churrasqueira 1 para 20/11/2025
- ✅ Confirmado

**Morador 2:**
- Tenta reservar Churrasqueira 1 para 20/11/2025
- ❌ "Este espaço já está reservado para esta data"
- Precisa escolher outra data (ex: 21/11/2025)

---

## 🔍 Validações Implementadas

### No Frontend (JavaScript)
1. ✅ Data mínima = hoje
2. ✅ Verificação de disponibilidade ao mudar data
3. ✅ Botão desabilitado se data indisponível
4. ✅ Campos obrigatórios

### No Backend (Laravel)
1. ✅ Espaço existe e está ativo
2. ✅ Pertence ao condomínio do usuário
3. ✅ Data >= hoje
4. ✅ **Não existe outra reserva no mesmo espaço no mesmo dia**
5. ✅ Limite mensal não excedido
6. ✅ Autorização (permissions)

---

## 💳 Integração com Asaas

### Quando é Gerada Cobrança

```php
if ($space->price_per_hour > 0) {
    // Despacha job
    GenerateReservationPayment::dispatch($reservation, $space);
}
```

### Job Faz

1. Cria `Charge` local no banco
2. Cria/atualiza customer no Asaas
3. Cria payment no Asaas:
   - Tipo: PIX (padrão)
   - Valor: taxa do espaço
   - Vencimento: 1 dia antes da reserva
   - Descrição: "Taxa de Reserva - [Nome do Espaço]"
4. Obtém QR Code PIX
5. Salva dados na cobrança
6. Logs de sucesso

### Webhook Confirma Pagamento

```php
// Quando morador paga
POST /webhooks/asaas

// Sistema:
- Recebe confirmação
- Marca cobrança como paga
- Cria registro de payment
- Morador pode usar o espaço
```

---

## 📊 Relatórios do Síndico

### Dashboard

**KPIs exibidos:**
- Próximas 5 reservas
- Espaço, morador, data
- Link para ver todas

### Ver Todas as Reservas

**Endpoint:** GET /api/reservations

**Filtros disponíveis:**
- Por espaço
- Por status (aprovadas, canceladas)
- Por data
- Por unidade

---

## 📱 Experiência do Usuário

### Desktop
1. Acessa `/reservations`
2. Vê grid de espaços
3. Clica "Reservar"
4. Modal abre
5. Escolhe data
6. Sistema valida em tempo real
7. Confirma
8. ✅ Pronto!

### Mobile
1. Acessa mesma URL
2. Cards empilhados
3. Toque em "Reservar"
4. Modal fullscreen
5. Date picker nativo
6. Validação touch-friendly
7. Confirma
8. ✅ Reservado!

---

## 🧪 Como Testar

### Teste 1: Criar Espaço (Síndico)

```bash
# Login como síndico
Email: sindico@vistaverde.com
Senha: password

# Navegar
Sidebar → Espaços → Novo Espaço

# Preencher
Nome: Churrasqueira 2
Taxa: R$ 100,00
Limite: 2 reservas/mês

# Salvar
```

**Resultado esperado:** Espaço criado, aparece na lista

### Teste 2: Fazer Reserva (Morador)

```bash
# Login como morador
Email: morador1@example.com
Senha: password

# Navegar
Sidebar → Reservas

# Clicar
"Reservar" na Churrasqueira 2

# Preencher
Data: 20/11/2025
Observações: Festa de aniversário

# Confirmar
```

**Resultado esperado:**
- ✅ Reserva confirmada automaticamente
- 💳 Cobrança de R$ 100,00 gerada
- 📧 Email com link de pagamento
- 📱 Notificação no sistema

### Teste 3: Tentar Reserva Conflitante (Morador 2)

```bash
# Login como outro morador
Email: morador2@example.com
Senha: password

# Navegar
Sidebar → Reservas → Churrasqueira 2

# Tentar reservar
Data: 20/11/2025 (mesma data do Teste 2)

# Resultado
❌ "Data indisponível! Este espaço já está reservado"
Botão "Confirmar" desabilitado
```

**Resultado esperado:** Sistema **não permite** reserva duplicada

### Teste 4: Pagar Taxa via Asaas

```bash
# Como morador1 (que fez reserva)

# Acessar
Sidebar → Cobranças

# Deve ver
"Taxa de Reserva - Churrasqueira 2"
Valor: R$ 100,00
Vencimento: 19/11/2025
Status: Pendente

# Clicar
"Pagar" → Opções:
- QR Code PIX
- Link para cartão
- Boleto

# Pagar
Escanear PIX ou usar cartão
```

**Resultado esperado:**
- Webhook confirma pagamento
- Status muda para "Pago"
- Morador pode usar o espaço

---

## 📊 Base de Dados

### Tabela `spaces`

```sql
CREATE TABLE spaces (
  id BIGINT PRIMARY KEY,
  condominium_id BIGINT,
  name VARCHAR(255),           -- Nome do espaço
  type ENUM(...),               -- Tipo
  capacity INT,                 -- Capacidade
  price_per_hour DECIMAL(10,2), -- Taxa de reserva
  max_reservations_per_month_per_unit INT, -- Limite mensal
  available_from TIME,          -- Horário início
  available_until TIME,         -- Horário fim
  is_active BOOLEAN,            -- Ativo/Inativo
  rules TEXT                    -- Regras de uso
);
```

### Tabela `reservations`

```sql
CREATE TABLE reservations (
  id BIGINT PRIMARY KEY,
  space_id BIGINT,              -- Espaço reservado
  unit_id BIGINT,               -- Unidade
  user_id BIGINT,               -- Quem reservou
  reservation_date DATE,        -- Data da reserva
  start_time TIME,              -- Início (do espaço)
  end_time TIME,                -- Fim (do espaço)
  status ENUM,                  -- approved (sempre)
  approved_by BIGINT,           -- Quem aprovou (auto)
  approved_at TIMESTAMP,        -- Quando (imediato)
  notes TEXT                    -- Observações
);
```

### Índices Importantes

```sql
INDEX(space_id, reservation_date, status)  -- Verificar conflito
INDEX(unit_id, reservation_date)           -- Limite mensal
INDEX(approved_at)                         -- Relatórios
```

---

## 🔄 Fluxo Completo do Sistema

```
SÍNDICO CRIA ESPAÇO
  ↓
Define nome, tipo, taxa, limite
  ↓
Salva no banco
  ↓
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  ↓
MORADOR ACESSA /reservations
  ↓
Vê espaços disponíveis
  ↓
Clica "Reservar" em um espaço
  ↓
Modal abre
  ↓
Escolhe data (ex: 20/11/2025)
  ↓
Sistema verifica (AJAX):
  - Já tem reserva neste dia? 
    SIM → ❌ Bloqueia
    NÃO → ✅ Libera
  ↓
Morador confirma
  ↓
POST /api/reservations
  ↓
ReservationController valida:
  ✓ Espaço ativo
  ✓ Pertence ao condomínio
  ✓ Data válida
  ✓ Sem conflito (1 por dia) ← CRÍTICO
  ✓ Limite mensal OK
  ↓
Cria reservation:
  status = 'approved'
  approved_at = now()
  ↓
Espaço tem taxa?
  SIM → Despacha GenerateReservationPayment
  NÃO → Pula
  ↓
Envia notificação de confirmação
  ↓
Retorna success
  ↓
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  ↓
SE TEM TAXA:
  ↓
Job GenerateReservationPayment executa
  ↓
1. Cria Charge local
2. Cria customer Asaas
3. Cria payment Asaas (PIX)
4. Obtém QR Code
5. Salva na cobrança
  ↓
Morador recebe:
  - Notificação no sistema
  - Email com QR Code PIX
  - Ou link para cartão
  ↓
Morador paga
  ↓
Asaas envia webhook
  ↓
Sistema marca como pago
  ↓
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  ↓
DIA DA RESERVA CHEGA
  ↓
Morador usa o espaço
  ↓
FIM ✅
```

---

## 🎯 Vantagens do Sistema

### Para o Síndico
✅ Não precisa aprovar manualmente cada reserva  
✅ Controle total sobre taxa e limites  
✅ Relatórios automáticos  
✅ Cobrança integrada via Asaas  
✅ Menos trabalho administrativo  

### Para o Morador
✅ Reserva confirmada na hora  
✅ Sem espera por aprovação  
✅ Vê se data está disponível antes de reservar  
✅ Pagamento fácil (PIX/Cartão)  
✅ Tudo pelo celular  

### Para o Condomínio
✅ Processo automatizado  
✅ Sem conflitos de reserva  
✅ Receita adicional (se cobrar taxa)  
✅ Histórico completo  
✅ Auditoria de uso  

---

## ⚙️ Configurações

### No Espaço (Síndico define)

| Campo | Valor | Efeito |
|-------|-------|--------|
| **price_per_hour** | 0 | Reserva gratuita |
| **price_per_hour** | > 0 | Gera cobrança Asaas |
| **max_reservations_per_month_per_unit** | 1 | 1 reserva/mês/unidade |
| **max_reservations_per_month_per_unit** | 4 | 4 reservas/mês/unidade |
| **is_active** | true | Disponível para reserva |
| **is_active** | false | Indisponível (manutenção) |

---

## 🚀 Comandos Úteis

```bash
# Ver espaços cadastrados
php artisan tinker
>>> Space::with('reservations')->get();

# Ver reservas de um espaço
>>> Space::find(1)->reservations;

# Ver reservas de uma unidade
>>> Unit::find(1)->reservations;

# Gerar cobrança manual de reserva
>>> $reservation = Reservation::find(1);
>>> $space = $reservation->space;
>>> GenerateReservationPayment::dispatch($reservation, $space);

# Processar fila
php artisan queue:work
```

---

## 📊 Estatísticas

### Implementado
- ✅ 3 Controllers (web + 2 API)
- ✅ 5 Views (index, create, edit spaces + reservations)
- ✅ 1 Job (GenerateReservationPayment)
- ✅ 2 Models atualizados
- ✅ 10+ rotas
- ✅ Validação de conflito única
- ✅ Integração Asaas completa

---

## ✅ CONCLUSÃO

O sistema de reservas está **100% funcional** com:

✅ **Aprovação automática** (não manual)  
✅ **1 reserva por local por dia** (validado)  
✅ **Verificação de disponibilidade** em tempo real  
✅ **Taxa configurável** pelo síndico  
✅ **Cobrança via Asaas** (PIX, Cartão, Boleto)  
✅ **Interface intuitiva** (desktop e mobile)  
✅ **Notificações automáticas**  

**Pronto para uso em produção!** 🚀

---

*Sistema de Reservas Completo - CondoManager v1.0*

