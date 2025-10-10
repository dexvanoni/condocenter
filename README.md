# CondoManager - Sistema de Gestão de Condomínios

Sistema SaaS profissional para administração de pequenos e médios condomínios com funcionalidades completas de gestão financeira, reservas, marketplace interno, controle de portaria, assembleias online e muito mais.

## 🚀 Tecnologias

- **Backend:** Laravel 12
- **Database:** MySQL
- **Frontend:** Blade Templates + Bootstrap 5
- **JavaScript:** Vue 3 + Alpine.js
- **Autenticação:** Laravel Sanctum
- **Permissions:** Spatie Laravel Permission
- **Payments:** Integração Asaas (Sandbox e Produção)
- **PDF:** DomPDF
- **Excel:** Maatwebsite Excel
- **Images:** Intervention Image
- **QRCode:** SimpleSoftwareIO QRCode
- **Auditing:** Laravel Auditing

## ✨ Funcionalidades Principais

### 🔐 Autenticação e Autorização
- Login/Logout/Reset Password
- 6 perfis de usuário: Administrador, Síndico, Morador, Porteiro, Conselho Fiscal, Secretaria
- Suporte a múltiplos perfis por usuário
- Permissões granulares com Spatie Permission

### 🏢 Multi-tenant por Condomínio
- Cada condomínio tem seu cadastro independente
- Usuários vinculados a unidades
- Isolamento completo de dados por condomínio

### 💰 Gestão Financeira Completa
- Lançamento de despesas e receitas
- Upload obrigatório de comprovantes (PDF/Imagem)
- Categorização e subcategorização
- Lançamentos recorrentes
- Geração de cobranças (boleto, PIX, cartão)
- Integração com Asaas (sandbox e produção)
- Conciliação bancária com upload de extrato (CSV/OFX)
- Relatórios: balancete, razão, DRE, fluxo de caixa
- Controle de inadimplência
- Auditoria imutável de todas operações

### 📅 Sistema de Reservas
- Cadastro de espaços (churrasqueira, salão, quadra, piscina)
- Calendário visual
- Regras de uso e bloqueio
- Aprovação automática ou manual
- Notificações

### 🛒 Marketplace Interno
- Anúncios de produtos e serviços
- Upload de até 3 imagens por anúncio
- Categorização
- Mensageria entre comprador e vendedor
- Dashboard de vendas

### 🚪 Controle de Portaria
- Registro de entradas e saídas
- Cadastro de visitantes
- QR Code único por morador
- Registro de encomendas com notificação automática
- Histórico detalhado

### 🐾 Cadastro de Animais
- Registro de pets por unidade
- Fotos e informações completas
- Controle de vacinação

### 🗳️ Assembleias Online
- Criação de assembleias com pauta
- Votação segura (aberta ou secreta)
- Delegação de voto (opcional)
- Geração automática de ata (PDF)

### 📢 Comunicação
- Mural de avisos
- "Fale com o Síndico"
- Botão PÂNICO com alerta para todos
- Notificações por email e push

## 📋 Requisitos

- PHP 8.3+
- MySQL 8.0+
- Composer 2.x
- Node.js 18+ e NPM
- Redis (opcional, para produção)

## 🛠️ Instalação

### 1. Clone o repositório

```bash
cd C:\laragon\www\condocenter
# O projeto já está no diretório
```

### 2. Instale as dependências

```bash
# Dependências PHP
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe C:\laragon\bin\composer\composer.phar install

# Dependências JavaScript
npm install
```

### 3. Configure o ambiente

Copie o arquivo de ambiente de exemplo (crie manualmente):

```env
APP_NAME="CondoManager"
APP_ENV=local
APP_DEBUG=true
APP_TIMEZONE=America/Sao_Paulo
APP_URL=http://localhost

APP_LOCALE=pt_BR

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=condocenter
DB_USERNAME=root
DB_PASSWORD=

# Asaas Payment Gateway
ASAAS_API_KEY=your_asaas_api_key_here
ASAAS_SANDBOX=true
ASAAS_WEBHOOK_EMAIL=admin@condomanager.com

QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@condomanager.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Gere a chave da aplicação

```bash
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan key:generate
```

### 5. Execute as migrations e seeders

```bash
# Criar banco de dados
# No MySQL: CREATE DATABASE condocenter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Executar migrations
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan migrate

# Popular com dados demo
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan db:seed
```

### 6. Crie o link simbólico para storage

```bash
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan storage:link
```

### 7. Compile os assets

```bash
# Desenvolvimento
npm run dev

# Produção
npm run build
```

### 8. Inicie o servidor

```bash
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan serve
```

Acesse: `http://localhost:8000`

## 👥 Usuários Demo

Após rodar o seeder, você terá acesso aos seguintes usuários:

| Email | Senha | Perfil |
|-------|-------|--------|
| admin@condomanager.com | password | Administrador |
| sindico@vistaverde.com | password | Síndico |
| porteiro@vistaverde.com | password | Porteiro |
| morador1@example.com | password | Morador |
| morador2@example.com | password | Morador |
| morador3@example.com | password | Morador |
| morador4@example.com | password | Morador |
| conselho@vistaverde.com | password | Conselho Fiscal |

## 🔧 Configuração do Asaas

1. Crie uma conta no Asaas: https://www.asaas.com/
2. Acesse o Painel > Integrações > API
3. Copie sua chave de API
4. Configure no `.env`:
   - `ASAAS_API_KEY`: Sua chave de API
   - `ASAAS_SANDBOX`: `true` para testes, `false` para produção
   - `ASAAS_WEBHOOK_EMAIL`: Email para receber notificações

### Webhook

Configure o webhook no Asaas apontando para:
```
https://seudominio.com/api/webhooks/asaas
```

## 🚀 Deploy na Hostinger

### 1. Requisitos

- Plano de hospedagem com PHP 8.3+
- MySQL 8.0+
- Acesso SSH (recomendado)

### 2. Passos

1. **Upload dos arquivos**
   - Faça upload de todos os arquivos para o diretório `public_html` ou equivalente

2. **Configure o .env**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://seudominio.com
   
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_DATABASE=seu_database
   DB_USERNAME=seu_usuario
   DB_PASSWORD=sua_senha
   
   ASAAS_SANDBOX=false
   ```

3. **Execute via SSH**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   npm run build
   ```

4. **Configure permissões**
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

5. **Configure o .htaccess**
   
   Certifique-se de que o arquivo `.htaccess` está configurado corretamente no diretório `public`.

## 📊 Filas e Jobs

O sistema utiliza filas para processamento assíncrono de:
- Envio de emails
- Geração de boletos/PIX
- Processamento de webhooks
- Geração de relatórios PDF
- Upload e processamento de extratos bancários

### Configuração

Para desenvolvimento (Windows):
```env
QUEUE_CONNECTION=database
```

Para processar as filas:
```bash
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan queue:work
```

Para produção (Linux):
Configure um supervisor ou cron job para manter o worker ativo.

## 🔒 Segurança

- ✅ CSRF Protection habilitado
- ✅ Validação forte em todos os inputs
- ✅ Upload seguro de arquivos (validação de MIME type)
- ✅ Rate limiting em rotas críticas
- ✅ Logs de auditoria em operações financeiras
- ✅ Soft deletes em registros sensíveis
- ✅ Criptografia de senhas com bcrypt
- ✅ Sanitização de dados

## 📝 Estrutura do Projeto

```
condocenter/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Controllers REST
│   │   ├── Middleware/      # Middlewares customizados
│   │   └── Requests/        # Form Requests
│   ├── Models/              # Eloquent Models
│   ├── Services/            # Services (Asaas, etc)
│   ├── Jobs/                # Jobs assíncronos
│   └── Policies/            # Authorization Policies
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── resources/
│   ├── views/               # Blade templates
│   ├── js/                  # JavaScript/Vue
│   └── css/                 # Estilos
├── routes/
│   ├── web.php              # Rotas web
│   └── api.php              # Rotas API
└── storage/
    ├── app/                 # Uploads
    └── logs/                # Logs da aplicação
```

## 🧪 Testes

```bash
# Executar todos os testes
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan test

# Testes com coverage
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan test --coverage
```

## 📖 Documentação Completa

Para mais informações detalhadas sobre o projeto, consulte nossa **[documentação completa](docs/README.md)** que inclui:

- 📘 **[Guia de Início Rápido](docs/QUICKSTART.md)** - Comece a usar rapidamente
- 📗 **[Setup Detalhado](docs/SETUP.md)** - Configuração completa do ambiente
- 📙 **[Funcionalidades](docs/FUNCIONALIDADES.md)** - Lista completa de recursos
- 📕 **[API Documentation](docs/API_DOCUMENTATION.md)** - Documentação da API REST
- 🔐 **[Sistema de Permissões](docs/SIDEBAR_PERMISSIONS.md)** - Como funcionam as permissões
- 💰 **[Transparência Financeira](docs/PERMISSOES_FINANCEIRAS.md)** - Sistema financeiro
- 🚀 **[Deploy](docs/DEPLOY.md)** - Guia de deploy em produção

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT.

## 🆘 Suporte

Para suporte, envie um email para suporte@condomanager.com ou abra uma issue no GitHub.

## 🎯 Roadmap

- [ ] App Mobile (React Native)
- [ ] Integração com WhatsApp Business API
- [ ] Integração com mais gateways de pagamento
- [ ] Sistema de notificações push (PWA)
- [ ] BI/Dashboard avançado
- [ ] Integração com contabilidade
- [ ] Sistema de manutenção preventiva
- [ ] Controle de acesso por biometria

---

Desenvolvido com para facilitar a gestão de condomínios no Brasil.
