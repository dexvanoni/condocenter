# ✅ Deploy – Lembretes Financeiros e Painel de Contas

Este documento descreve os passos necessários para colocar em produção os novos serviços financeiros: lembretes automáticos de cobranças, painel de contas do condomínio e prestação de contas exportável.

---

## 1. Pré-requisitos

- Código atualizado com as migrations executadas (`php artisan migrate`).
- Variáveis de ambiente de e-mail/SMTP configuradas (para envio opcional dos lembretes por e-mail).
- Serviço de filas operando (Redis ou database queue).
- Scheduler (cron ou agendador) disponível na máquina/servidor.

---

## 2. Scheduler – `charges:send-reminders`

O comando `charges:send-reminders` deve rodar diariamente às 08:00 para notificar:

- morador responsável via notificação interna;
- envio de e-mail (quando houver endereço cadastrado);
- marcação de lembretes já enviados, evitando duplicidades.

### Configuração Recomendada

**Linux / Servidor em nuvem**  
Adicionar ao cron (`crontab -e`), executando `schedule:run` a cada minuto:

```bash
* * * * * cd /caminho/para/condocenter && php artisan schedule:run >> /var/log/laravel-schedule.log 2>&1
```

O Laravel internamente dispara `charges:send-reminders` apenas no horário configurado (08:00), junto com as demais tasks do agendador.

**Windows (Laragon / IIS / Windows Server)**  
Usar o “Agendador de Tarefas”:

1. Criar tarefa → “Executar se o usuário estiver conectado ou não”.
2. Ação:
   - Programa/script: `C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe`
   - Argumentos: `artisan schedule:run`
   - Iniciar em: `C:\laragon\www\condocenter`
3. Gatilho: a cada 1 minuto (ou pelo menos diariamente antes das 08:00).  
   O scheduler do Laravel cuida de executar `charges:send-reminders` apenas às 08h.

---

## 3. Worker de filas (`queue:work`)

Os jobs de lembretes e os e-mails ficam na fila. É obrigatório ter ao menos um worker ativo.

### Desenvolvimento / homologação

```powershell
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan queue:work --tries=3
```

Deixar o terminal aberto enquanto precisa processar jobs.

### Produção

- **Linux**: usar Supervisor  
  Arquivo exemplo `/etc/supervisor/conf.d/queue-worker.conf`:

  ```
  [program:condocenter-queue]
  process_name=%(program_name)s_%(process_num)02d
  command=/usr/bin/php /var/www/condocenter/artisan queue:work --tries=3 --timeout=120
  autostart=true
  autorestart=true
  user=www-data
  numprocs=1
  redirect_stderr=true
  stdout_logfile=/var/log/condocenter-queue.log
  ```

  `supervisorctl reread && supervisorctl update`

- **Windows**:  
  - Usar NSSM para registrar o comando `php artisan queue:work` como serviço; ou  
  - Agendar tarefa recorrente com a opção “Repetir indefinidamente” e “Executar se o usuário estiver conectado ou não”.

---

## 4. Verificações pós-deploy

1. Garantir que o scheduler está rodando (ver logs `schedule:run`).
2. Validar que o worker de fila está processando jobs (log de filas, tabela `jobs` vazia).
3. Conferir no painel financeiro (menu “Financeiro”) se:
   - Painel de adimplência abre e exibe dados;
   - Contas do condomínio listam entradas/saídas;
   - Prestação de contas exporta PDF/planilha/impressão sem erro.
4. Testar lembretes de cobrança (criar cobrança com vencimento próximo e aguardar horário configurado; verificar notificação/e-mail).

---

## 5. Recovery / rollback

- Em caso de necessidade de rollback, rever migrations executadas recentemente.
- Desativar temporariamente o agendador (cron/Tarefa Agendada) se houver falhas.
- Parar o serviço de filas, aplicar correções, reativar worker e scheduler.

---

> **Importante:** manter backups do banco antes de rodar migrations e configurar logs centralizados para os comandos de agendamento e filas.

