# Sistema de Regimento Interno

## Visão Geral

Sistema completo para gerenciamento do Regimento Interno do condomínio, com funcionalidades de visualização para todos os usuários e gestão restrita para administradores e síndicos.

## Data de Criação
**31 de Outubro de 2025**

---

## Funcionalidades Implementadas

### 1. Visualização do Regimento (Todos os Usuários)
- ✅ Acesso via sidebar em "Documentos > Regimento Interno"
- ✅ Visualização elegante e navegável do texto completo
- ✅ Informações de metadados (versão, data de aprovação, assembleia)
- ✅ Histórico de últimas alterações
- ✅ Botões de ação: PDF, Imprimir, Histórico

### 2. Gestão do Regimento (Administrador/Síndico)
- ✅ Criar regimento inicial
- ✅ Editar regimento existente
- ✅ Controle automático de versionamento
- ✅ Registro de autor das alterações
- ✅ Resumo de mudanças opcion al
- ✅ Data e detalhes de assembleia

### 3. Histórico de Alterações
- ✅ Visualização completa de todas as versões
- ✅ Registro automático de cada alteração
- ✅ Exibição de: versão, data, autor, resumo
- ✅ Visualização de versões anteriores completas
- ✅ Indicador visual da versão atual

### 4. Exportação e Impressão
- ✅ Exportação para PDF formatado
- ✅ Visualização para impressão otimizada
- ✅ Layout profissional e organizado
- ✅ Metadados incluídos nos documentos

---

## Estrutura Técnica

### Banco de Dados

#### Tabela: `internal_regulations`
```sql
- id
- condominium_id (FK)
- content (text)
- assembly_date (date, nullable)
- assembly_details (string, nullable)
- version (integer, default 1)
- is_active (boolean, default true)
- updated_by (FK users, nullable)
- created_at, updated_at, deleted_at
```

#### Tabela: `internal_regulation_history`
```sql
- id
- internal_regulation_id (FK)
- condominium_id (FK)
- content (text)
- changes_summary (text, nullable)
- assembly_date (date, nullable)
- assembly_details (string, nullable)
- version (integer)
- updated_by (FK users, nullable)
- changed_at (timestamp)
- created_at, updated_at
```

### Models

#### InternalRegulation
- **Traits**: HasFactory, SoftDeletes, Auditable
- **Relacionamentos**:
  - `condominium()`: BelongsTo
  - `updatedBy()`: BelongsTo (User)
  - `history()`: HasMany (InternalRegulationHistory)
- **Funcionalidade Especial**: Ao atualizar, cria automaticamente histórico e incrementa versão

#### InternalRegulationHistory
- **Traits**: HasFactory
- **Relacionamentos**:
  - `internalRegulation()`: BelongsTo
  - `condominium()`: BelongsTo
  - `updatedBy()`: BelongsTo (User)

### Controller: InternalRegulationController

#### Rotas Públicas (Todos os usuários)
- `GET /internal-regulations` - index()
- `GET /internal-regulations/history` - history()
- `GET /internal-regulations/history/{id}` - showHistory()
- `GET /internal-regulations/export-pdf` - exportPdf()
- `GET /internal-regulations/print` - print()

#### Rotas Administrativas (Admin/Síndico)
- `GET /internal-regulations/create` - create()
- `POST /internal-regulations` - store()
- `GET /internal-regulations/edit` - edit()
- `PUT /internal-regulations` - update()

### Views

1. **index.blade.php** - Visualização principal do regimento
2. **create.blade.php** - Criação inicial do regimento
3. **edit.blade.php** - Edição do regimento (versão nova automática)
4. **history.blade.php** - Lista completa de versões
5. **show-history.blade.php** - Visualização de versão específica
6. **pdf.blade.php** - Template para exportação PDF
7. **print.blade.php** - Template para impressão

---

## Fluxo de Utilização

### Para Moradores
1. Acessar "Documentos > Regimento Interno" no sidebar
2. Visualizar o regimento completo
3. Exportar para PDF ou imprimir, se necessário
4. Consultar histórico de alterações

### Para Administrador/Síndico
1. Acessar "Documentos > Regimento Interno" no sidebar
2. Clicar em "Editar" (aparece apenas para admin/síndico)
3. Modificar o texto conforme necessário
4. Adicionar resumo das mudanças (opcional mas recomendado)
5. Informar data e detalhes de assembleia
6. Salvar - uma nova versão será criada automaticamente
7. A versão anterior fica no histórico

---

## Características Técnicas

### Versionamento Automático
- Sempre que o conteúdo é alterado, a versão anterior é salva no histórico
- O número de versão é incrementado automaticamente
- Não é possível perder histórico de alterações

### Segurança
- Apenas administradores e síndicos podem criar/editar
- Todos os usuários podem visualizar
- Registro de auditoria com OwenIt\Auditing
- Log de atividades do usuário

### Performance
- Soft Deletes para recuperação de dados
- Índices otimizados nas tabelas
- Eager Loading nos relacionamentos

### Escalabilidade
- Suporta múltiplos condomínios (multi-tenancy)
- Histórico ilimitado de versões
- Estrutura preparada para futuras expansões

---

## Instalação/Migração

### Comandos Executados
```bash
php artisan migrate
php artisan db:seed --class=InternalRegulationSeeder
```

### Arquivos Criados
- 2 Migrations
- 2 Models
- 1 Controller
- 7 Views
- 1 Seeder
- Rotas adicionadas
- Items no sidebar (desktop + mobile)

---

## Dependências

- **Laravel**: Framework principal
- **barryvdh/laravel-dompdf**: Geração de PDFs
- **OwenIt/Auditing**: Sistema de auditoria
- **Bootstrap 5**: Interface visual
- **Bootstrap Icons**: Ícones

---

## Melhorias Futuras Sugeridas

1. **Editor Rich Text**: Implementar editor WYSIWYG para formatação mais rica
2. **Comparação de Versões**: Sistema de diff visual entre versões
3. **Notificações**: Avisar usuários quando houver nova versão do regimento
4. **Assinatura Digital**: Implementar assinatura eletrônica para assembleias
5. **Busca no Texto**: Campo de busca para localizar artigos específicos
6. **Índice Automático**: Gerar índice clicável dos capítulos
7. **Comentários**: Permitir que síndicos adicionem notas explicativas
8. **Multilíngua**: Suporte para regimentos em outros idiomas
9. **Anexos**: Permitir anexar documentos complementares
10. **API**: Endpoint para integração com outros sistemas

---

## Manutenção

### Como Atualizar o Regimento
1. Login como administrador ou síndico
2. Acessar Regimento Interno
3. Clicar em "Editar"
4. Fazer as alterações necessárias
5. Preencher data de assembleia e detalhes
6. Adicionar resumo das mudanças
7. Salvar

### Como Consultar Histórico
1. Acessar Regimento Interno
2. Clicar em "Histórico"
3. Ver lista de todas as versões
4. Clicar em "Visualizar" para ver versão específica

### Como Exportar/Imprimir
1. Acessar Regimento Interno
2. Clicar em "PDF" para baixar
3. Ou clicar em "Imprimir" para imprimir diretamente

---

## Seeder Inicial

O sistema inclui um seeder com o Regimento Interno do CHAS (Condomínio Habitacional Augusto Severo), que pode ser usado como exemplo ou substituído conforme necessidade.

**Arquivo**: `database/seeders/InternalRegulationSeeder.php`
**Conteúdo**: `database/seeders/regimento-content.txt`

Para executar:
```bash
php artisan db:seed --class=InternalRegulationSeeder
```

---

## Contato para Suporte

Para dúvidas ou sugestões sobre este sistema, consulte a documentação do Laravel ou entre em contato com o desenvolvedor responsável.

---

## Licença

Este sistema foi desenvolvido como parte do projeto CondoCenter e segue as mesmas diretrizes de licenciamento do projeto principal.

---

**Desenvolvido com ❤️ para o CondoCenter**
**Data: 31/10/2025**

