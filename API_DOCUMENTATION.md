# 📚 CondoManager - Documentação da API

## Base URL

```
Desenvolvimento: http://localhost:8000/api
Produção: https://seudominio.com/api
```

## Autenticação

A API usa **Laravel Sanctum** para autenticação.

### Login Web (obter cookies de sessão)

```http
POST /login
Content-Type: application/x-www-form-urlencoded

email=sindico@vistaverde.com&password=password
```

### Obter Informações do Usuário

```http
GET /api/user
Authorization: Bearer {token}
```

---

## 📊 Endpoints Financeiros

### Listar Transações

```http
GET /api/transactions?type=expense&status=paid&per_page=15
```

**Query Parameters:**
- `type` - income | expense
- `status` - pending | paid | overdue | cancelled
- `category` - string
- `start_date` - YYYY-MM-DD
- `end_date` - YYYY-MM-DD
- `per_page` - número (padrão: 15)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "type": "expense",
      "category": "Manutenção",
      "description": "Compra de materiais",
      "amount": "500.00",
      "transaction_date": "2025-10-07",
      "status": "paid",
      "receipts": []
    }
  ],
  "current_page": 1,
  "total": 10
}
```

### Criar Transação

```http
POST /api/transactions
Content-Type: application/json

{
  "type": "expense",
  "category": "Limpeza",
  "description": "Produtos de limpeza",
  "amount": 350.00,
  "transaction_date": "2025-10-07",
  "status": "paid",
  "payment_method": "pix"
}
```

### Upload de Comprovante

```http
POST /api/transactions/{id}/receipts
Content-Type: multipart/form-data

file: (arquivo PDF/JPG/PNG, máx 5MB)
description: "Nota fiscal"
```

---

## 💰 Endpoints de Cobranças

### Listar Cobranças

```http
GET /api/charges?status=pending&unit_id=1
```

### Criar Cobrança

```http
POST /api/charges

{
  "unit_id": 1,
  "title": "Taxa Condominial - Nov/2025",
  "amount": 450.00,
  "due_date": "2025-11-10",
  "type": "regular"
}
```

### Criar Cobranças em Lote

```http
POST /api/charges/bulk-create

{
  "title": "Taxa Condominial - Nov/2025",
  "amount": 450.00,
  "due_date": "2025-11-10",
  "type": "regular",
  "apply_to_all_units": true
}
```

### Gerar Pagamento no Asaas

```http
POST /api/charges/{id}/generate-asaas
```

---

## 📅 Endpoints de Reservas

### Listar Reservas

```http
GET /api/reservations?space_id=1&status=approved
```

### Criar Reserva

```http
POST /api/reservations

{
  "space_id": 1,
  "reservation_date": "2025-10-15",
  "start_time": "14:00",
  "end_time": "18:00",
  "notes": "Festa de aniversário"
}
```

### Aprovar Reserva

```http
POST /api/reservations/{id}/approve
```

### Rejeitar Reserva

```http
POST /api/reservations/{id}/reject

{
  "rejection_reason": "Espaço em manutenção"
}
```

---

## 📦 Endpoints de Encomendas

### Registrar Encomenda

```http
POST /api/packages

{
  "unit_id": 1,
  "sender": "Correios",
  "tracking_code": "BR123456789BR",
  "description": "Caixa média"
}
```

**Comportamento:** Envia notificação automática para os moradores da unidade.

### Registrar Retirada

```http
POST /api/packages/{id}/collect

{
  "collected_by": 2,
  "notes": "Retirado pelo próprio morador"
}
```

---

## 🛒 Endpoints de Marketplace

### Listar Anúncios

```http
GET /api/marketplace?category=products&search=bicicleta
```

### Criar Anúncio

```http
POST /api/marketplace
Content-Type: multipart/form-data

title: "Bicicleta Mountain Bike"
description: "Seminova, ótimo estado"
price: 800.00
category: "products"
condition: "used"
images[]: (arquivos de imagem, máx 3)
```

---

## 🚪 Endpoints de Portaria

### Registrar Entrada

```http
POST /api/entries

{
  "unit_id": 1,
  "type": "visitor",
  "visitor_name": "João Silva",
  "visitor_document": "123.456.789-00",
  "vehicle_plate": "ABC-1234",
  "authorized": true
}
```

**Tipos:** resident | visitor | service_provider | delivery

---

## 🗳️ Endpoints de Assembleias

### Criar Assembleia

```http
POST /api/assemblies

{
  "title": "Assembleia Geral Ordinária",
  "description": "Aprovação de contas",
  "agenda": [
    "Aprovação das contas",
    "Eleição do síndico",
    "Reforma da fachada"
  ],
  "scheduled_at": "2025-11-15 19:00:00",
  "duration_minutes": 120,
  "voting_type": "open",
  "allow_delegation": false
}
```

### Votar

```http
POST /api/assemblies/{id}/vote

{
  "agenda_item": "Aprovação das contas",
  "vote": "yes"
}
```

**Opções de voto:** yes | no | abstain

---

## 📧 Endpoints de Notificações

### Listar Notificações

```http
GET /api/notifications?is_read=false
```

### Marcar como Lida

```http
POST /api/notifications/{id}/read
```

### Marcar Todas como Lidas

```http
POST /api/notifications/mark-all-read
```

### Contador de Não Lidas

```http
GET /api/notifications/unread-count

Response: {"count": 5}
```

---

## 📊 Endpoints de Relatórios

### Relatório Financeiro

```http
GET /api/reports/financial?start_date=2025-10-01&end_date=2025-10-31&format=pdf
```

**Parameters:**
- `start_date` - YYYY-MM-DD
- `end_date` - YYYY-MM-DD
- `format` - json | pdf

### Relatório de Inadimplência

```http
GET /api/reports/defaulters
```

### Balancete

```http
GET /api/reports/balance?month=10&year=2025
```

### Fluxo de Caixa

```http
GET /api/reports/cash-flow?months=6
```

---

## 🏥 Health Check

```http
GET /api/health

Response:
{
  "status": "healthy",
  "timestamp": "2025-10-07T01:00:00.000000Z",
  "checks": {
    "database": {"status": "ok"},
    "cache": {"status": "ok"},
    "storage": {"status": "ok"}
  },
  "info": {
    "laravel_version": "12.x",
    "php_version": "8.3.16"
  }
}
```

---

## ❌ Códigos de Erro

| Código | Descrição |
|--------|-----------|
| 200 | Sucesso |
| 201 | Criado com sucesso |
| 400 | Requisição inválida |
| 401 | Não autenticado |
| 403 | Não autorizado |
| 404 | Não encontrado |
| 422 | Erro de validação |
| 500 | Erro interno do servidor |

### Exemplo de Erro de Validação

```json
{
  "errors": {
    "email": ["O campo email é obrigatório"],
    "amount": ["O valor deve ser maior que zero"]
  }
}
```

---

## 🔐 Segurança

### Headers Obrigatórios

```
X-CSRF-TOKEN: {token}
Accept: application/json
Content-Type: application/json
```

### Rate Limiting

- **Webhooks:** Sem limite
- **API pública:** 60 requisições/minuto
- **API autenticada:** 120 requisições/minuto

---

## 📝 Notas Importantes

1. **Multi-tenant:** Todos os dados são isolados por `condominium_id`
2. **Soft Deletes:** Maioria dos recursos usa soft delete
3. **Auditoria:** Operações financeiras são auditadas automaticamente
4. **Jobs:** Notificações e pagamentos são processados de forma assíncrona
5. **Validação:** Todos os endpoints validam dados de entrada

---

## 🧪 Testando a API

### Com Postman

Importe o arquivo `postman_collection.json` incluído no projeto.

### Com cURL

```bash
# Login
curl -X POST http://localhost:8000/login \
  -d "email=sindico@vistaverde.com&password=password" \
  -c cookies.txt

# Usar API
curl -X GET http://localhost:8000/api/transactions \
  -b cookies.txt \
  -H "Accept: application/json"
```

---

**Versão da API:** 1.0.0  
**Última atualização:** Outubro 2025

