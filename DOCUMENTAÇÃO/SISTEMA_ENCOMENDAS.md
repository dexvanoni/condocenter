# Sistema de Encomendas

## Visão Geral
- Painel interativo para porteiros com todas as unidades do condomínio e contagem em tempo real de encomendas pendentes.
- Registro simplificado de chegadas: basta selecionar a unidade e o tipo (`Leve`, `Pesado`, `Caixa Grande`, `Frágil`).
- Retirada confirmada em um clique com registro automático de horário e responsável.
- Notificações internas instantâneas para moradores e agregados vinculados à unidade.

## Modelagem
- Tabela `packages`
  - Campos principais: `condominium_id`, `unit_id`, `registered_by`, `type`, `status`, `received_at`, `collected_at`, `collected_by`, `notification_sent`.
  - Tipos suportados: `leve`, `pesado`, `caixa_grande`, `fragil`.
  - Status: `pending` (pendente), `collected` (retirada).
- Scopes auxiliares (`Package`):
  - `pending()`, `byCondominium($id)`, `forUnit($id)`.
  - Atributos computados `type_label` e `status_label` expostos via API.

## Fluxo das Notificações
- Job `SendPackageNotification` fila mensagens para moradores/agregados (`roles`: `Morador`, `Agregado`) da unidade.
- Eventos disparados:
  - `arrived`: registra chegada e envia alerta de retirada pendente.
  - `collected`: informa horário da retirada.
- Canal padrão: `database` (sem FCM).

## Endpoints REST
| Método | Rota | Descrição | Permissões |
| --- | --- | --- | --- |
| `GET` | `/api/packages` | Lista encomendas (filtros por status, tipo, unidade, busca) | Autenticado |
| `POST` | `/api/packages` | Registra nova encomenda | `register_packages` |
| `POST` | `/api/packages/{package}/collect` | Marca como retirada | `register_packages` |
| `GET` | `/api/packages/summary/units` | Painel de unidades (pendências, moradores) | `register_packages` |
| `GET` | `/api/packages/residents/search` | Busca por nome/CPF para filtragem rápida | `register_packages` |

## Interface do Porteiro (`/packages`)
- Busca dinâmica por unidade, morador ou CPF com sugestões instantâneas.
- Cartões por unidade exibindo:
  - Código (Bloco • Número) e moradores vinculados.
  - Contador e chips das encomendas pendentes com horário de chegada.
  - Ações: “Registrar chegada” (modal com seleção do tipo) e “Confirmar retirada”.
- Atualização em tempo real após cada ação e indicador de última sincronização.

## Dashboard do Porteiro
- Cards atualizados com total de encomendas do dia e pendências.
- Seção dedicada às pendências com atalho direto para o painel completo.
- Encomendas do dia destacam tipo e horário de registro.

## Dashboards de Morador e Agregado
- Listagem de encomendas pendentes com tipo e horário, destacando a necessidade de retirada na portaria.
- Remoção de botões de ação (registro realizado exclusivamente pela portaria).

## Testes Automatizados
- Classe `PackageManagementTest` valida:
  - Registro de encomenda por porteiro com disparo de job.
  - Fluxo de retirada.
  - Restrições de acesso ao resumo.
  - Retorno estruturado do resumo de unidades.
- Suite completa ainda depende de ajustes prévios nas migrations legacy (`condominiums`), registrar falha ao executar `phpunit`.

## Observações
- Todas as novas permissões utilizam `Spatie\Permission`.
- Views mantêm Bootstrap e seguem padrão visual do sistema.
- Job utiliza apenas notificações internas (sem dependência de FCM).
- Novas migrations: campo `type` em `packages`.

