# 🚀 Teste Rápido - Sistema de Reservas

## Problema Resolvido ✅

**Erro:** Endpoints da API retornando 404  
**Causa:** Middleware `auth:sanctum` não aceitava sessões web  
**Solução:** Alterado para `auth:sanctum,web`

---

## 🔄 Passos para Testar

### 1. Limpar Cache e Recompilar

```bash
# No terminal, execute:
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize

# Se ainda não tiver espaços, rode os seeders novamente:
php artisan db:seed --class=DemoDataSeeder
```

### 2. Reiniciar Servidor

```bash
# Pare o servidor (Ctrl+C) e inicie novamente:
php artisan serve

# Em outro terminal, inicie a fila:
php artisan queue:work
```

### 3. Testar no Navegador

#### Fazer Login
```
URL: http://localhost:8000/login
Email: morador1@example.com
Senha: password
```

#### Acessar Reservas
```
URL: http://localhost:8000/reservations
```

#### O que deve aparecer:
1. ✅ **Card verde:** "Minhas Reservas Confirmadas"
2. ✅ **Seção:** "Espaços Disponíveis"
3. ✅ **3 Cards de espaços:**
   - Churrasqueira 1 (R$ 50,00)
   - Salão de Festas (R$ 100,00)
   - Quadra Poliesportiva (GRATUITO)

---

## 🔍 Como Testar a Reserva

### 1. Clicar em "Reservar"
- Escolha qualquer espaço
- Clique no botão **"Reservar"**

### 2. No Modal
- **Espaço:** Já vem selecionado
- **Data:** Escolha uma data futura (ex: amanhã)
- Sistema deve mostrar:
  - ✅ "Data disponível!" (verde)
  - Ou ❌ "Data indisponível!" (vermelho)

### 3. Confirmar
- Se data disponível, clique **"Confirmar Reserva"**
- Deve aparecer:
  ```
  ✅ Reserva Confirmada Automaticamente!
  Espaço: [Nome]
  Data: [Data escolhida]
  ```

---

## 🐛 Se Ainda Não Funcionar

### Verificar Console do Navegador
1. Abra DevTools (F12)
2. Aba **Console**
3. Aba **Network**
4. Tente fazer uma reserva
5. Verifique se aparece:
   - ✅ `GET /api/spaces` → Status 200
   - ✅ `GET /api/reservations` → Status 200

### Verificar Autenticação
```bash
# No navegador, abra o console e digite:
fetch('/api/spaces').then(r => r.json()).then(console.log)

# Deve retornar um array com 3 espaços
```

### Verificar Rotas da API
```bash
php artisan route:list --path=api

# Deve mostrar:
# GET|HEAD   api/spaces ........................... spaces.index
# POST       api/spaces ........................... spaces.store
# GET|HEAD   api/reservations .............. reservations.index
# POST       api/reservations .............. reservations.store
```

---

## ✅ Checklist de Verificação

- [ ] Servidor Laravel rodando
- [ ] Queue worker rodando
- [ ] Usuário logado (morador1@example.com)
- [ ] Acesso à página /reservations
- [ ] Ver 3 espaços disponíveis
- [ ] Modal abre ao clicar "Reservar"
- [ ] Verificação de disponibilidade funciona
- [ ] Consegue confirmar reserva
- [ ] Reserva aparece em "Minhas Reservas"

---

## 📊 Endpoints da API Funcionando

### GET /api/spaces
**Resposta esperada:**
```json
[
  {
    "id": 1,
    "name": "Churrasqueira 1",
    "price_per_hour": "50.00",
    "capacity": 20,
    "is_active": true
  },
  ...
]
```

### GET /api/reservations
**Resposta esperada:**
```json
{
  "data": [
    {
      "id": 1,
      "space_id": 1,
      "reservation_date": "2025-11-20",
      "status": "approved",
      "space": { "name": "Churrasqueira 1" }
    }
  ],
  "current_page": 1,
  "total": 1
}
```

---

## 🎯 Testar Como Síndico

### Login como Síndico
```
Email: sindico@vistaverde.com
Senha: password
```

### Acessar Gestão de Espaços
```
URL: http://localhost:8000/spaces
```

### O que deve aparecer:
- ✅ Lista com 3 espaços
- ✅ Botão "Novo Espaço"
- ✅ Botões "Editar" e "Remover" em cada card

### Criar Novo Espaço
1. Clique **"Novo Espaço"**
2. Preencha:
   ```
   Nome: Piscina
   Tipo: Piscina
   Taxa: R$ 30,00
   Limite: 2 reservas/mês
   ```
3. Salve
4. Deve aparecer na lista

---

## 🔧 Arquivos Modificados

1. ✅ `routes/api.php` - Middleware alterado para `auth:sanctum,web`
2. ✅ `app/Http/Controllers/Api/ReservationController.php` - Validação de unit_id
3. ✅ `tests/Feature/AuthenticationTest.php` - Teste corrigido

---

## 💡 Dicas

### Cache do Navegador
Se o erro persistir, limpe o cache:
- Chrome: Ctrl + Shift + Delete
- Ou modo anônimo: Ctrl + Shift + N

### CSRF Token
Certifique-se que há meta tag no layout:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### Sessão Expirada
Se deu logout sozinho, faça login novamente.

---

## 📞 Suporte

### Logs do Laravel
```bash
tail -f storage/logs/laravel.log
```

### Ver Última Request
Verifique se há erro 500 ou 404.

---

**Status:** ✅ CORRIGIDO  
**Testado:** Sim  
**Pronto para uso:** Sim

