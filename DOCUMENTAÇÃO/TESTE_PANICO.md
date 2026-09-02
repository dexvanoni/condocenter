# 🚨 Guia de Teste - Sistema de Alerta de PÂNICO

## Como Testar o Sistema de PÂNICO

---

## 🎯 Pré-requisitos

1. Sistema rodando: `php artisan serve`
2. Banco populado: `php artisan db:seed`
3. Queue worker ativo: `php artisan queue:work` (em outro terminal)
4. Logado como morador: `morador1@example.com` / `password`

---

## 📝 Passo a Passo do Teste

### Passo 1: Acessar o Dashboard

```
URL: http://localhost:8000/dashboard
Login: morador1@example.com
Senha: password
```

### Passo 2: Localizar o Botão PÂNICO

- Na **sidebar** (menu lateral esquerdo)
- Botão vermelho piscando
- Texto: "🚨 PÂNICO"

### Passo 3: Clicar no Botão PÂNICO

- Modal grande abre
- Header vermelho: "ALERTA DE EMERGÊNCIA"
- 7 botões grandes de emergência exibidos

### Passo 4: Selecionar Tipo de Emergência

Escolha um dos botões:

| Botão | Cor | Ícone |
|-------|-----|-------|
| **INCÊNDIO** | Vermelho | 🔥 |
| **CRIANÇA PERDIDA** | Amarelo | 👶 |
| **ENCHENTE** | Azul | 🌊 |
| **ROUBO/FURTO** | Preto | 🚨 |
| **CHAMEM A POLÍCIA** | Azul | 🚓 |
| **VIOLÊNCIA DOMÉSTICA** | Cinza | ⚠️ |
| **CHAMEM UMA AMBULÂNCIA** | Verde | 🚑 |

**Exemplo:** Clique em **"INCÊNDIO"**

### Passo 5: Tela de Confirmação

Você verá:
- ✅ Alerta grande: "TEM CERTEZA?"
- ✅ Tipo selecionado exibido: "🔥 INCÊNDIO"
- ✅ Campo de texto para informações adicionais
- ✅ Barra de **Slide to Confirm** (gradiente vermelho → verde)

### Passo 6: Adicionar Informações (Opcional)

No campo "Informações Adicionais", digite algo como:
```
Fumaça saindo da unidade 302, 3º andar
```

### Passo 7: Confirmar com Slide

1. **Clique e segure** o botão circular branco
2. **Arraste para a direita** até o final da barra
3. Sistema detecta automaticamente quando chega em 90%
4. **Confirmação automática** quando soltar

**Alternativa:** Use touch em mobile para melhor experiência

### Passo 8: Enviando...

Você verá:
- Spinner vermelho grande
- Texto: "Enviando Alerta de Emergência..."
- "Notificando todos os moradores e administração"

### Passo 9: Confirmação

- Alert aparece: "✅ Alerta de pânico enviado! Todos os moradores e a administração foram notificados."
- Modal fecha
- Página recarrega

---

## ✅ O que Acontece nos Bastidores

### 1. Registro no Banco de Dados

**Tabela `messages`:**
```sql
INSERT INTO messages (
  condominium_id, 
  from_user_id, 
  to_user_id,  -- NULL (para todos)
  type,         -- 'panic_alert'
  subject,      -- 'ALERTA DE PÂNICO: 🔥 INCÊNDIO'
  message,      -- Mensagem completa
  priority,     -- 'urgent'
  created_at
)
```

### 2. Job Despachado

**SendPanicAlert Job:**
- Busca TODOS usuários do condomínio
- Para cada um:
  - Cria notificação no banco
  - Envia email urgente
  - Registra no log

### 3. Emails Enviados

**Para cada usuário:**
```
Para: morador2@example.com, sindico@vistaverde.com, porteiro@vistaverde.com, etc
Assunto: 🚨 ALERTA DE PÂNICO - 🔥 INCÊNDIO
Corpo: Template HTML profissional com:
  - Header vermelho piscante
  - Informações do alerta
  - Dados de quem enviou
  - IP do dispositivo
  - Orientações específicas (para incêndio: ligar 193, evacuar, etc)
  - Botão para ligar para quem acionou
  - Botão para acessar o sistema
```

### 4. Logs Gerados

**storage/logs/laravel.log:**
```
[CRITICAL] 🚨 ALERTA DE PÂNICO ACIONADO
{
  "alert_type": "fire",
  "alert_title": "🔥 INCÊNDIO",
  "user_name": "Morador 1",
  "user_unit": "A - 2",
  "user_phone": "(11) 90000001",
  "timestamp": "07/10/2025 22:45:30",
  "ip_address": "127.0.0.1",
  "additional_info": "Fumaça saindo da unidade 302"
}
```

---

## 🔍 Como Verificar se Funcionou

### Verificação 1: Notificações no Dashboard

1. Faça logout
2. Faça login com outro usuário: `morador2@example.com` / `password`
3. Veja o **sino de notificações** no header
4. Deve ter badge vermelho com "1"
5. Clique no sino
6. Deve aparecer: "🚨 ALERTA DE PÂNICO: 🔥 INCÊNDIO"

### Verificação 2: Banco de Dados

```sql
-- Verificar mensagem criada
SELECT * FROM messages WHERE type = 'panic_alert' ORDER BY id DESC LIMIT 1;

-- Verificar notificações criadas
SELECT COUNT(*) FROM notifications WHERE type = 'panic_alert';

-- Deve retornar o número de usuários do condomínio
```

### Verificação 3: Logs

```bash
# Ver últimas linhas do log
tail -50 storage/logs/laravel.log

# Deve conter:
# [CRITICAL] 🚨 ALERTA DE PÂNICO ACIONADO
```

### Verificação 4: Emails (se MAIL_MAILER=log)

```bash
# Ver logs de email
tail -100 storage/logs/laravel.log | grep "panic"
```

---

## 🎬 Demonstração Completa

### Cenário de Teste Completo

```bash
# Terminal 1: Servidor
php artisan serve

# Terminal 2: Queue Worker
php artisan queue:work

# Navegador 1: Morador 1 (quem vai acionar)
- Login: morador1@example.com
- Clica em PÂNICO
- Seleciona INCÊNDIO
- Desliza para confirmar

# Navegador 2: Síndico (quem vai receber)
- Login: sindico@vistaverde.com
- Dashboard mostra notificação
- Email recebido
```

---

## 📧 Template de Email de PÂNICO

### Estrutura
```
┌─────────────────────────────────────┐
│  🚨 ALERTA DE EMERGÊNCIA            │ ← Header vermelho piscante
│  🔥 INCÊNDIO                         │
└─────────────────────────────────────┘

⚠️ ATENÇÃO: SITUAÇÃO DE EMERGÊNCIA NO CONDOMÍNIO ⚠️

🏢 Condomínio: Vista Verde
📅 Data/Hora: 07/10/2025 22:45:30

┌─────────────────────────────────────┐
│ 👤 Enviado por: Morador 1           │
│ 🏠 Unidade: A - 2                   │
│ 📱 Telefone: (11) 90000001          │
│ 🕐 Horário: 07/10/2025 22:45:30     │
└─────────────────────────────────────┘

📝 Informações Adicionais:
Fumaça saindo da unidade 302, 3º andar

🔍 Dados Técnicos:
IP: 127.0.0.1
User Agent: Mozilla/5.0...

⚠️ TOME AS MEDIDAS NECESSÁRIAS IMEDIATAMENTE!

[📞 Ligar para Morador 1]  [🖥️ Acessar o Sistema]

⚠️ Orientações para INCÊNDIO:
- Acione o alarme
- Evacue o prédio
- Chame os bombeiros (193)
- Não use elevadores
- Mantenha a calma
```

---

## 🧪 Testes Automatizados

### Testar via Postman

```json
POST /panic-alert
Headers:
  Content-Type: application/json
  X-CSRF-TOKEN: {token}
Body:
{
  "alert_type": "fire",
  "additional_info": "Teste de sistema"
}
```

### Testar via API

```javascript
fetch('/panic-alert', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
  },
  body: JSON.stringify({
    alert_type: 'fire',
    additional_info: 'Teste de sistema'
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

---

## ⚠️ Considerações Importantes

### Em Desenvolvimento
- ✅ Emails vão para `storage/logs/laravel.log` (MAIL_MAILER=log)
- ✅ Notificações aparecem no banco
- ✅ Jobs processam se worker estiver ativo

### Em Produção
- ✅ Configurar MAIL_MAILER=smtp
- ✅ Configurar SMTP real (Gmail, SendGrid, etc)
- ✅ Configurar Supervisor para queue workers
- ✅ Monitorar logs de CRITICAL

### Recomendações de Segurança
- ⚠️ Evitar testes desnecessários (causa alarme real)
- ⚠️ Registrar em logs todo acionamento
- ⚠️ Investigar acionamentos frequentes
- ⚠️ Penalizar uso indevido

---

## 📊 Métricas do Sistema de PÂNICO

### Dados Capturados
1. ✅ ID do usuário
2. ✅ Nome completo
3. ✅ Unidade
4. ✅ Telefone
5. ✅ Email
6. ✅ Tipo de emergência
7. ✅ Timestamp exato
8. ✅ IP do dispositivo
9. ✅ User Agent (navegador/dispositivo)
10. ✅ Informações adicionais
11. ✅ Condomínio ID
12. ✅ Condomínio nome

### Ações Executadas
1. ✅ Registro no banco (tabela messages)
2. ✅ Log CRITICAL no sistema
3. ✅ Notificação para todos (tabela notifications)
4. ✅ Email urgente para todos
5. ✅ Job assíncrono (performance)

---

## 🎓 Aprendizados da Implementação

### Tecnologias Usadas
- JavaScript drag events (mousedown, mousemove, touchstart)
- CSS animations e gradients
- Bootstrap modals com steps
- Laravel Jobs e Queues
- Mailable com templates
- Vue reactive components

### Padrões Aplicados
- Progressive disclosure (3 steps)
- Slide to confirm (anti-acidental)
- Graceful degradation
- Error handling robusto
- Logging estruturado

---

## 🚀 Próximos Passos (Opcional)

### Melhorias Futuras
- [ ] Integração com WhatsApp Business API
- [ ] Web Push Notifications (PWA)
- [ ] Gravação de áudio junto com alerta
- [ ] Foto/vídeo via câmera do celular
- [ ] Geolocalização automática
- [ ] Integração com autoridades (190, 192, 193)
- [ ] Dashboard de estatísticas de alertas
- [ ] Botão físico IoT para idosos

---

## 📞 Suporte

Se tiver dúvidas sobre o sistema de PÂNICO:
1. Leia este guia completo
2. Verifique os logs: `storage/logs/laravel.log`
3. Teste em ambiente controlado primeiro
4. Configure emails antes de produção

---

**IMPORTANTE:** O sistema de PÂNICO é uma funcionalidade crítica de segurança. Teste-o adequadamente antes de colocar em produção e oriente os moradores sobre o uso responsável.

---

✅ **Sistema de PÂNICO 100% Funcional e Testado**

*Um recurso que pode salvar vidas.* 🚨

