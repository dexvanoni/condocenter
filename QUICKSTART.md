# 🚀 CondoManager - Guia de Início Rápido

## Primeiros Passos (5 minutos)

### 1. Criar arquivo .env

Crie um arquivo `.env` na raiz do projeto com este conteúdo mínimo:

```env
APP_NAME="CondoManager"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=condocenter
DB_USERNAME=root
DB_PASSWORD=

ASAAS_API_KEY=sua_chave_aqui
ASAAS_SANDBOX=true

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database

MAIL_MAILER=log
```

### 2. Executar Comandos de Setup

```bash
# Gerar chave da aplicação
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan key:generate

# Criar banco de dados no MySQL
# mysql -u root -p
# CREATE DATABASE condocenter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Executar migrations
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan migrate

# Popular banco com dados demo
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan db:seed

# Criar link simbólico
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan storage:link

# Compilar assets
npm run build
```

### 3. Iniciar Servidor

```bash
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan serve
```

Acesse: **http://localhost:8000**

## 👤 Logins de Teste

| Email | Senha | Perfil |
|-------|-------|--------|
| `admin@condomanager.com` | `password` | Administrador |
| `sindico@vistaverde.com` | `password` | Síndico |
| `morador1@example.com` | `password` | Morador |
| `porteiro@vistaverde.com` | `password` | Porteiro |

## 🎯 Funcionalidades Testáveis

### Dashboard do Síndico
- ✅ KPIs financeiros (receitas, despesas, saldo)
- ✅ Inadimplência
- ✅ Últimas transações
- ✅ Próximas reservas
- ✅ Encomendas pendentes

### Sistema Multi-tenant
- ✅ Isolamento por condomínio
- ✅ Usuários vinculados a unidades
- ✅ QR Code único por morador

### Integração Asaas (via API)
- ✅ Service pronto para criar cobranças
- ✅ Webhook configurado
- ✅ Suporte a boleto, PIX e cartão

## 🔧 Comandos Úteis

```bash
# Ver rotas
php artisan route:list

# Limpar caches
php artisan optimize:clear

# Recriar banco
php artisan migrate:fresh --seed

# Processar filas
php artisan queue:work

# Ver logs em tempo real
php artisan pail
```

## 📚 Documentação Completa

- **[README.md](README.md)** - Visão geral do projeto
- **[SETUP.md](SETUP.md)** - Guia detalhado de configuração
- **[PROJETO_SUMMARY.md](PROJETO_SUMMARY.md)** - Status do desenvolvimento

## ⚠️ Troubleshooting Rápido

### Erro: "No application encryption key has been specified"
```bash
php artisan key:generate
```

### Erro: "SQLSTATE[HY000] [1049] Unknown database"
Crie o banco de dados no MySQL:
```sql
CREATE DATABASE condocenter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Assets não carregam
```bash
npm install
npm run build
php artisan view:clear
```

### Permissões no Windows
Se tiver problemas com storage, execute como administrador:
```bash
icacls "storage" /grant Everyone:(OI)(CI)F /T
icacls "bootstrap\cache" /grant Everyone:(OI)(CI)F /T
```

## 🎨 Próximos Passos

1. **Testar o Dashboard** - Faça login com diferentes usuários
2. **Explorar as Migrations** - Veja a estrutura do banco
3. **Ler o SETUP.md** - Configure Asaas e email
4. **Implementar Controllers** - Comece pelo módulo financeiro
5. **Criar Views** - Use o layout já pronto

## 📞 Precisa de Ajuda?

1. Consulte o **[SETUP.md](SETUP.md)** para configurações detalhadas
2. Veja **[PROJETO_SUMMARY.md](PROJETO_SUMMARY.md)** para entender o que está pronto
3. Verifique os **logs** em `storage/logs/laravel.log`

---

**Dica:** Use o comando `php artisan tinker` para testar os models interativamente!

Exemplo:
```php
User::with('condominium', 'unit')->find(1);
Condominium::with('units')->first();
```

