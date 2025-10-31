# Sistema de Cadastro de Pets - CondoCenter

## Visão Geral

Sistema completo para cadastro e gerenciamento de pets no condomínio, com geração automática de QR Code para identificação e localização de animais perdidos.

## Funcionalidades Implementadas

### 1. Cadastro de Pets

#### Dados do Pet
- **Nome**: Nome do animal
- **Tipo**: Cachorro, Gato, Pássaro ou Outro
- **Raça**: Raça do animal (opcional)
- **Cor**: Cor predominante (opcional)
- **Porte**: Pequeno, Médio ou Grande
- **Descrição**: Observações adicionais (opcional)
- **Foto**: Imagem do pet (JPG, PNG, GIF - máx 2MB)
- **QR Code**: Gerado automaticamente no cadastro

#### Vínculos Obrigatórios
- **Unidade**: Pet deve estar vinculado a uma unidade
- **Dono (Morador)**: Pet deve ter um dono morador (não agregado)

### 2. Listagem de Pets (Index)

#### Visualização
- Cards organizados com foto, informações e ações
- Todos os usuários podem visualizar todos os pets
- Filtros disponíveis:
  - Busca por nome, raça, cor ou dono
  - Tipo de animal
  - Porte

#### Ações nos Cards
- **Chamar o Dono**: Botão verde que redireciona para WhatsApp do dono
- **Download QR Code**: Botão para baixar o QR Code do pet
- **Editar**: Disponível para dono e administrador
- **Excluir**: Disponível para dono e administrador

### 3. Sistema de QR Code

#### Geração Automática
- QR Code único gerado ao cadastrar o pet
- Formato: `PET-XXXXXXXXXX-timestamp`
- Pode ser impresso e fixado na coleira

#### Leitura de QR Code
- **Botão no Dashboard**: Disponível para todos os usuários
- **Leitor com Câmera**: Abre câmera do celular/computador
- **Seleção de Câmera**: Opção de escolher câmera frontal ou traseira
- **Resultado da Leitura**: Exibe:
  - Foto do pet
  - Informações do animal (nome, tipo, raça, cor, porte)
  - Dados do dono (nome, telefone, unidade)
  - Botão para contatar dono via WhatsApp

#### URL Pública
- Rota pública acessível sem login: `/pets/qr/{qrCode}`
- Design responsivo e atraente
- Informações completas do pet e dono
- Botão direto para WhatsApp

### 4. Controle de Acesso

#### Permissões
- **Visualizar**: Todos podem ver todos os pets
- **Cadastrar**: Apenas moradores (não agregados), administradores e síndico
- **Editar**: Dono do pet, administrador ou síndico
- **Excluir**: Dono do pet, administrador ou síndico

#### Validações
- Agregados não podem ser donos de pets
- Dono deve pertencer à unidade selecionada
- Apenas moradores da unidade podem ser selecionados como donos

## Estrutura Técnica

### Model: Pet
**Localização**: `app/Models/Pet.php`

**Campos**:
```php
- id
- condominium_id
- unit_id
- owner_id
- name
- type (enum: cachorro, gato, passaro, outro)
- breed (nullable)
- color (nullable)
- size (enum: pequeno, medio, grande)
- description (nullable)
- photo (nullable)
- qr_code (unique)
- is_active
- timestamps
- soft_deletes
```

**Relacionamentos**:
- `belongsTo`: Condominium, Unit, User (owner)

**Scopes**:
- `active()`: Pets ativos
- `byCondominium($id)`: Filtrar por condomínio
- `byOwner($id)`: Filtrar por dono
- `byUnit($id)`: Filtrar por unidade
- `search($term)`: Busca em nome, raça, cor e dono

### Controller: PetController
**Localização**: `app/Http/Controllers/PetController.php`

**Métodos**:
- `index()`: Lista todos os pets com filtros
- `create()`: Form de cadastro
- `store()`: Salva novo pet
- `show()`: Exibe detalhes do pet
- `edit()`: Form de edição
- `update()`: Atualiza pet
- `destroy()`: Remove pet
- `getOwnersByUnit($unitId)`: AJAX - retorna moradores da unidade
- `showQrCode($qrCode)`: Exibe página pública do QR Code
- `downloadQrCode($pet)`: Faz download do QR Code em PNG
- `verifyQrCode(Request)`: AJAX - verifica QR Code e retorna dados

### Policy: PetPolicy
**Localização**: `app/Policies/PetPolicy.php`

**Regras**:
- `viewAny()`: Todos podem visualizar
- `view()`: Todos podem ver detalhes
- `create()`: Moradores, admin e síndico
- `update()`: Dono, admin ou síndico
- `delete()`: Dono, admin ou síndico

### Views

#### Index
**Localização**: `resources/views/pets/index.blade.php`
- Grid de cards responsivo
- Filtros de busca
- Botões de ação por pet

#### Create
**Localização**: `resources/views/pets/create.blade.php`
- Form completo de cadastro
- Select dinâmico de donos (AJAX)
- Upload de foto

#### Edit
**Localização**: `resources/views/pets/edit.blade.php`
- Form de edição
- Preview da foto atual
- Exibição do QR Code

#### QR Show (Público)
**Localização**: `resources/views/pets/qr-show.blade.php`
- Página standalone (sem layout)
- Design premium com gradiente
- Informações completas
- Botão WhatsApp destacado

#### Componente Leitor QR
**Localização**: `resources/views/components/pet-qr-reader.blade.php`
- Modal com leitor de QR Code
- Usa biblioteca html5-qrcode
- Seleção de câmera
- Resultado em tempo real

## Rotas

### Autenticadas
```php
Route::resource('pets', PetController::class);
Route::get('/pets/owners/{unit}', 'getOwnersByUnit');
Route::get('/pets/{pet}/download-qr', 'downloadQrCode');
Route::post('/pets/verify-qr', 'verifyQrCode');
```

### Públicas
```php
Route::get('/pets/qr/{qrCode}', 'showQrCode');
```

## Integração com Dashboard

O componente de leitura de QR Code foi integrado aos dashboards:
- Dashboard Default: `resources/views/dashboard/default.blade.php`

**Card de destaque** com:
- Título chamativo
- Descrição clara
- Botão grande para abrir leitor

## QRCodeHelper

**Localização**: `app/Helpers/QRCodeHelper.php`

Novo método adicionado:
```php
public static function generateForPet($pet)
```

Gera QR Code de 400x400px com alta correção de erro (H) contendo a URL pública do pet.

## Fluxo de Uso

### Cadastro de Pet
1. Morador acessa "Pets" no menu
2. Clica em "Cadastrar Pet"
3. Seleciona unidade
4. Sistema carrega moradores da unidade (AJAX)
5. Seleciona dono (morador)
6. Preenche dados do pet
7. Faz upload da foto
8. Salva
9. Sistema gera QR Code automaticamente

### Download e Impressão do QR Code
1. Na listagem ou na edição do pet
2. Clica no botão de QR Code
3. Faz download do arquivo PNG
4. Imprime e fixa na coleira

### Encontrar Pet Perdido
1. Qualquer pessoa acessa o sistema
2. No dashboard, clica em "Escanear QR Code"
3. Autoriza acesso à câmera
4. Seleciona câmera (frontal ou traseira)
5. Aponta para o QR Code da coleira
6. Sistema mostra informações do pet e dono
7. Clica em "Contatar Dono" (WhatsApp)

### Acesso Direto (sem scanner)
1. Alguém acessa a URL do QR Code manualmente
2. Sistema exibe página pública com todas as informações
3. Botão direto para WhatsApp do dono

## Bibliotecas Utilizadas

### Frontend
- **html5-qrcode**: Scanner de QR Code via webcam
- **Bootstrap 5**: Framework CSS
- **jQuery**: AJAX e manipulação DOM

### Backend
- **SimpleSoftwareIO/simple-qrcode**: Geração de QR Codes
- **Laravel Storage**: Armazenamento de fotos

## Validações e Segurança

### No Backend
- Validação de tipos de arquivo (imagens)
- Limite de tamanho (2MB)
- Verificação de que o dono pertence à unidade
- Verificação de que o dono não é agregado
- Soft delete para manter histórico

### CSRF
- Todas as requisições POST protegidas com token CSRF

### Sanitização
- Dados escapados nas views
- Validação de entrada no controller

## Melhorias Futuras Sugeridas

1. **Notificações Push**: Avisar dono quando QR Code for escaneado
2. **Histórico de Leituras**: Registrar quando e onde o QR foi lido
3. **Múltiplos Donos**: Permitir co-proprietários de pets
4. **Vacinas e Documentos**: Registro de carteira de vacinação
5. **Galeria de Fotos**: Múltiplas fotos por pet
6. **Chip e Registro**: Campos para número de chip e RGA
7. **Exportação**: Relatório PDF com dados e QR Code
8. **API para Mobile**: Endpoints REST para app mobile nativo

## Considerações de Escalabilidade

### Performance
- Índices em `qr_code`, `owner_id`, `unit_id`
- Eager loading de relacionamentos (with)
- Paginação na listagem (atualmente todos, sugestão: 12 por página)

### Armazenamento
- Fotos armazenadas em `storage/app/public/pets`
- QR Codes gerados on-the-fly (não salvos em disco)
- Considerar CDN para fotos em produção

### Manutenibilidade
- Código bem documentado
- Separação de responsabilidades
- Policy para controle de acesso
- Validações centralizadas no controller

## Testes Recomendados

1. **Cadastro**:
   - Cadastrar pet com todos os campos
   - Cadastrar pet com campos mínimos
   - Tentar cadastrar sem foto
   - Tentar cadastrar com agregado como dono

2. **Edição**:
   - Editar como dono
   - Editar como admin
   - Tentar editar como outro morador

3. **QR Code**:
   - Gerar e fazer download
   - Escanear com câmera do celular
   - Acessar URL diretamente
   - Testar com QR Code inválido

4. **Permissões**:
   - Acessar como morador
   - Acessar como agregado
   - Acessar como admin
   - Acessar como visitante (URL pública)

5. **Responsividade**:
   - Desktop
   - Tablet
   - Mobile

## Documentação de API

### POST /pets/verify-qr
**Autenticação**: Requerida

**Request**:
```json
{
  "qr_code": "PET-ABC123XYZ-1234567890"
}
```

**Response (Sucesso)**:
```json
{
  "success": true,
  "pet": {
    "id": 1,
    "name": "Rex",
    "type": "Cachorro",
    "breed": "Labrador",
    "color": "Dourado",
    "size": "Grande",
    "photo": "http://...",
    "description": "...",
    "owner": {
      "name": "João Silva",
      "phone": "(11) 98765-4321",
      "whatsapp_link": "https://wa.me/5511987654321"
    },
    "unit": {
      "identifier": "Bloco A - Apt 101"
    },
    "condominium": {
      "name": "Condomínio Exemplo"
    }
  }
}
```

**Response (Erro)**:
```json
{
  "success": false,
  "message": "Pet não encontrado"
}
```

## Contato e Suporte

Para dúvidas ou sugestões sobre o sistema de pets:
- Consultar documentação técnica em `DOCUMENTAÇÃO/SISTEMA_PETS.md`
- Verificar código fonte em `app/Models/Pet.php` e `app/Http/Controllers/PetController.php`

---

**Data de Implementação**: 31/10/2025  
**Versão**: 1.0  
**Status**: ✅ Implementado e Testado

