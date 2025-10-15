# 🔔 Firebase Cloud Messaging (FCM) - Configuração

## 📋 Visão Geral

O sistema CondoCenter agora inclui suporte completo a notificações push usando Firebase Cloud Messaging (FCM). Esta funcionalidade pode ser facilmente habilitada/desabilitada sem afetar o sistema existente.

## ⚙️ Configuração

### 1. Configurações do .env

Adicione as seguintes configurações ao seu arquivo `.env`:

```env
# Habilitar/Desabilitar FCM (true/false)
FCM_ENABLED=false

# Configurações específicas do Firebase
FCM_SERVER_KEY=your_firebase_server_key_here
FCM_SENDER_ID=your_firebase_sender_id_here
FCM_PROJECT_ID=your_firebase_project_id_here

# Configurações do cliente Firebase (para JavaScript)
FCM_API_KEY=your_firebase_api_key_here
FCM_AUTH_DOMAIN=your_project_id.firebaseapp.com
FCM_STORAGE_BUCKET=your_project_id.appspot.com
FCM_APP_ID=your_firebase_app_id_here
FCM_VAPID_KEY=your_firebase_vapid_key_here

# Configurações específicas de notificações
FCM_PANIC_NOTIFICATIONS=true
FCM_GENERAL_NOTIFICATIONS=true
```

### 2. Configuração do Firebase Console

1. **Acesse** https://console.firebase.google.com/
2. **Crie um novo projeto** ou selecione um existente
3. **Vá em "Project Settings" > "Cloud Messaging"**
   - Copie o "Server Key" para `FCM_SERVER_KEY`
   - Copie o "Sender ID" para `FCM_SENDER_ID`
4. **Vá em "Project Settings" > "General"**
   - Copie o "Project ID" para `FCM_PROJECT_ID`
   - Copie os valores para as configurações do cliente
5. **Para VAPID Key**: Vá em "Project Settings" > "Cloud Messaging" > "Web Push certificates"
   - Gere um novo par de chaves
   - Copie a chave pública para `FCM_VAPID_KEY`

### 3. Ativar FCM

Após configurar todas as chaves, defina:
```env
FCM_ENABLED=true
```

## 🎯 Funcionalidades

### ✅ Alertas de Pânico
- Notificações push instantâneas para todos os usuários
- Notificações de resolução de alertas
- Vibração e som especiais para alertas críticos

### ✅ Notificações Gerais
- Aprovação/cancelamento de reservas
- Lembretes de pagamento
- Notificações de encomendas
- Lembretes de assembleias

### ✅ Controle Granular
- Usuários podem habilitar/desabilitar notificações
- Tópicos específicos (pânico, reservas, financeiro, etc.)
- Configuração por tipo de notificação

## 🔧 Como Usar

### Para Administradores/Síndicos:

1. **Testar FCM**: Use `window.testFCM()` no console do navegador
2. **Configurar FCM**: Use `window.setupFCM()` no console do navegador
3. **Verificar Status**: Acesse `/api/fcm/status`

### Para Usuários:

1. **Permissão**: O navegador solicitará permissão para notificações
2. **Token**: Automaticamente registrado no servidor
3. **Controle**: Podem habilitar/desabilitar via API

## 📱 Suporte de Dispositivos

### ✅ Desktop
- Chrome, Firefox, Safari, Edge
- Windows, macOS, Linux

### ✅ Mobile
- Android (Chrome, Firefox)
- iOS (Safari, Chrome)
- PWA (Progressive Web App)

## 🛡️ Segurança

- Tokens FCM são criptografados e armazenados com segurança
- Permissões baseadas em roles (Admin/Síndico)
- Logs detalhados para auditoria
- Rate limiting automático

## 🔄 Desabilitar FCM

Para desabilitar completamente:

```env
FCM_ENABLED=false
```

Ou via configuração:
```php
// config/firebase.php
'enabled' => false,
```

## 📊 Monitoramento

### Logs Disponíveis:
- Registro de tokens FCM
- Envio de notificações
- Erros e falhas
- Estatísticas de entrega

### Métricas:
- Usuários com FCM habilitado
- Taxa de entrega de notificações
- Tipos de notificações mais enviadas

## 🚀 Benefícios

1. **Não Intrusivo**: Não afeta funcionalidades existentes
2. **Configurável**: Fácil de habilitar/desabilitar
3. **Escalável**: Suporta milhares de usuários
4. **Gratuito**: Até 1 milhão de mensagens/mês
5. **Real-time**: Notificações instantâneas
6. **Cross-platform**: Funciona em todos os dispositivos

## 🔧 Troubleshooting

### FCM não funciona:
1. Verifique se `FCM_ENABLED=true`
2. Confirme as chaves do Firebase
3. Verifique logs em `storage/logs/laravel.log`
4. Teste com `window.testFCM()`

### Notificações não aparecem:
1. Verifique permissões do navegador
2. Confirme se o usuário tem token FCM
3. Teste em modo incógnito
4. Verifique se o service worker está ativo

### Erro de configuração:
1. Valide todas as chaves do Firebase
2. Verifique se o projeto está ativo
3. Confirme as URLs do domínio
4. Teste a conectividade com FCM

## 📞 Suporte

Para problemas específicos:
1. Verifique os logs do sistema
2. Teste as configurações do Firebase
3. Consulte a documentação oficial do FCM
4. Entre em contato com o suporte técnico
