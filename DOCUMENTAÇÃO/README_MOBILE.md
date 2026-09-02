# SindCON Mobile - App de Pânico

Este é o aplicativo móvel do SindCON, focado nas funcionalidades de **alerta de pânico** e **autenticação**. O app se comunica com o sistema web existente através da API Laravel.

## 🚀 Funcionalidades

- **Autenticação**: Login seguro com token JWT
- **Alerta de Pânico**: Botão de emergência que notifica todos os moradores
- **Notificações Push**: Recebimento de alertas com som de sirene
- **Interface Intuitiva**: Design similar ao sistema web
- **Resolução de Alertas**: Capacidade de marcar alertas como resolvidos

## 📱 Tipos de Emergência Suportados

- 🔥 Incêndio
- 👶 Criança Perdida
- 🌊 Enchente
- 🚨 Roubo/Furto
- 🚓 Chamem a Polícia
- ⚠️ Violência Doméstica
- 🚑 Chamem uma Ambulância

## 🛠️ Instalação e Configuração

### Pré-requisitos

- Node.js 16+ 
- npm ou yarn
- Expo CLI (`npm install -g @expo/cli`)
- Android Studio (para build do APK)
- Conta Expo (para builds)

### 1. Instalar Dependências

```bash
cd celular/SindCONMobile
npm install
```

### 2. Configurar Firebase

1. Crie um projeto no [Firebase Console](https://console.firebase.google.com/)
2. Ative o Firebase Cloud Messaging
3. Baixe o arquivo `google-services.json` para Android
4. Atualize as configurações em `src/config/index.ts`:

```typescript
export const FIREBASE_CONFIG = {
  API_KEY: 'sua-api-key',
  AUTH_DOMAIN: 'seu-projeto.firebaseapp.com',
  PROJECT_ID: 'seu-projeto-id',
  STORAGE_BUCKET: 'seu-projeto.appspot.com',
  MESSAGING_SENDER_ID: 'seu-sender-id',
  APP_ID: 'seu-app-id',
};
```

### 3. Configurar API

Atualize a URL da API em `src/config/index.ts`:

```typescript
export const API_CONFIG = {
  BASE_URL: 'https://seu-dominio.com/api', // URL do seu servidor Laravel
  TIMEOUT: 10000,
  RETRY_ATTEMPTS: 3,
};
```

### 4. Configurar Permissões

O app precisa das seguintes permissões no Android:

- `RECORD_AUDIO` - Para reproduzir sons de alerta
- `MODIFY_AUDIO_SETTINGS` - Para controlar volume
- `VIBRATE` - Para vibrar em alertas
- `WAKE_LOCK` - Para manter tela ligada durante alertas

## 🏗️ Build do APK

### Desenvolvimento Local

```bash
# Executar em modo desenvolvimento
npm run android

# Ou usar Expo Go
npm start
```

### Build de Produção

1. **Instalar EAS CLI**:
```bash
npm install -g eas-cli
```

2. **Login no Expo**:
```bash
eas login
```

3. **Configurar projeto**:
```bash
eas build:configure
```

4. **Build do APK**:
```bash
# Build de preview (APK para testes)
eas build --platform android --profile preview

# Build de produção (APK final)
eas build --platform android --profile production
```

## 🔧 Configuração do Servidor Laravel

### 1. Adicionar Rota de Pânico na API

Adicione em `routes/api.php`:

```php
Route::middleware(['auth:sanctum'])->group(function () {
    // Rota para enviar alerta de pânico
    Route::post('/panic-alert', [\App\Http\Controllers\PanicAlertController::class, 'send']);
    
    // Rota para verificar alertas ativos
    Route::get('/panic-alerts/active', [\App\Http\Controllers\PanicAlertController::class, 'checkActiveAlerts']);
    
    // Rota para resolver alerta
    Route::post('/panic-alerts/{id}/resolve', [\App\Http\Controllers\PanicAlertController::class, 'resolve']);
});
```

### 2. Configurar CORS

Adicione em `config/cors.php`:

```php
'allowed_origins' => [
    'http://localhost:3000', // Expo dev server
    'exp://192.168.1.100:8081', // Expo local
],
```

### 3. Configurar Firebase no Laravel

Certifique-se de que o Firebase está configurado no Laravel conforme os arquivos existentes.

## 📱 Como Usar

### Para Moradores

1. **Login**: Use suas credenciais do sistema web
2. **Alerta de Pânico**: Toque no botão vermelho em caso de emergência
3. **Selecionar Tipo**: Escolha o tipo de emergência
4. **Informações Adicionais**: Adicione detalhes se necessário
5. **Confirmar**: Confirme o envio do alerta

### Para Administração

- Todos os alertas são enviados automaticamente para:
  - Todos os moradores via notificação push
  - Síndicos, administradores, porteiros e secretaria via email
  - Sistema de mensagens interno

## 🔔 Notificações

### Recebimento de Alertas

- **Som de Sirene**: Reproduzido automaticamente
- **Vibração**: Dispositivo vibra durante o alerta
- **Notificação Visual**: Aparece na tela do dispositivo
- **Duração**: Som e vibração por 10 segundos

### Resolução de Alertas

- Qualquer usuário pode marcar um alerta como resolvido
- Notificação de resolução é enviada para todos
- Som de sirene para automaticamente

## 🐛 Troubleshooting

### Problemas Comuns

1. **Erro de Conexão**:
   - Verifique se o servidor Laravel está rodando
   - Confirme a URL da API em `src/config/index.ts`

2. **Notificações não funcionam**:
   - Verifique permissões do dispositivo
   - Confirme configuração do Firebase
   - Teste em dispositivo físico (não funciona no emulador)

3. **Som não toca**:
   - Verifique se o volume está ligado
   - Confirme permissões de áudio
   - Teste em dispositivo físico

### Logs de Debug

Para ver logs detalhados:

```bash
# Android
adb logcat | grep SindCON

# Expo
npx expo logs
```

## 📋 Checklist de Deploy

- [ ] Firebase configurado e testado
- [ ] API Laravel funcionando
- [ ] CORS configurado
- [ ] Permissões do Android configuradas
- [ ] Teste em dispositivo físico
- [ ] Build do APK gerado
- [ ] Instalação testada em diferentes dispositivos

## 🔒 Segurança

- Tokens JWT para autenticação
- HTTPS obrigatório em produção
- Validação de dados no servidor
- Rate limiting para prevenir spam
- Logs de auditoria para todos os alertas

## 📞 Suporte

Para suporte técnico ou dúvidas sobre o app, consulte a documentação do sistema web ou entre em contato com a administração do condomínio.
