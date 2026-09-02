# Implementação: Morador Responsável por Unidade

## Contexto

Cada unidade pode ter múltiplos usuários vinculados, mas apenas **um usuário com o perfil "Morador"** é considerado o responsável pela unidade. Os demais devem ter o perfil "Agregado".

## Regra de Negócio

- ✅ **Uma unidade** = **Um morador responsável** + **N agregados**
- ✅ O morador é o responsável legal pela unidade
- ✅ Agregados são dependentes vinculados ao morador

## Implementação

### 1. Modelo Unit (`app/Models/Unit.php`)

Adicionado um relacionamento específico para buscar o morador responsável:

```php
/**
 * Retorna o morador responsável pela unidade
 */
public function morador()
{
    return $this->hasOne(User::class)->whereHas('roles', function($query) {
        $query->where('name', 'Morador');
    });
}
```

**Características:**
- Usa `hasOne()` pois só pode haver um morador por unidade
- Filtra usuários com a role "Morador"
- Retorna `null` se não houver morador vinculado

### 2. Controller (`app/Http/Controllers/UnitController.php`)

Modificado o método `index()` para carregar o morador junto com a consulta:

```php
$query = Unit::with(['condominium', 'users', 'morador'])
    ->byCondominium($user->condominium_id);
```

**Benefícios:**
- Eager Loading evita problema N+1
- Performance otimizada
- Uma única consulta para múltiplos relacionamentos

### 3. View (`resources/views/units/index.blade.php`)

#### Estrutura da Tabela Atualizada

**Colunas:**
1. Ap/Casa
2. Bloco
3. Tipo
4. Situação
5. **Responsável** ← NOVA COLUNA
6. **Total** (total de pessoas na unidade)
7. Status (dívidas)
8. Ações

#### Exibição do Responsável

```blade
<td>
    @if($unit->morador)
        <span class="text-primary fw-bold">
            <i class="bi bi-person-badge"></i> {{ $unit->morador->name }}
        </span>
    @else
        <span class="text-muted">
            <i class="bi bi-dash-circle"></i> Sem responsável
        </span>
    @endif
</td>
```

**Estados Possíveis:**
- ✅ **Com Morador**: Nome em azul com ícone de badge
- ⚠️ **Sem Morador**: Texto cinza "Sem responsável"

## Visualização da Tabela

| Ap/Casa | Bloco | Tipo | Situação | Responsável | Total | Status | Ações |
|---------|-------|------|----------|-------------|-------|--------|-------|
| 101 | A | Residencial | Habitado | 👤 João Silva | 3 | Em dia | 👁️ ✏️ 🗑️ |
| 102 | A | Residencial | Fechado | - Sem responsável | 0 | Em dia | 👁️ ✏️ 🗑️ |
| 201 | B | Comercial | Habitado | 👤 Maria Santos | 1 | Com dívidas | 👁️ ✏️ 🗑️ |

## Casos de Uso

### Caso 1: Unidade com Morador e Agregados
```
Unidade 101
├── João Silva (Morador) ← Responsável
├── Maria Silva (Agregado)
└── Pedro Silva (Agregado)

Exibe: "👤 João Silva" | Total: 3
```

### Caso 2: Unidade sem Morador
```
Unidade 102
└── (vazia)

Exibe: "- Sem responsável" | Total: 0
```

### Caso 3: Unidade só com Agregados (situação irregular)
```
Unidade 103
├── Carlos Souza (Agregado)
└── Ana Souza (Agregado)

Exibe: "- Sem responsável" | Total: 2
⚠️ Situação irregular: agregados sem morador responsável
```

## Validações Relacionadas

Para garantir a integridade da regra de negócio, o sistema valida:

### No UserController

1. **Agregado deve ter morador vinculado:**
```php
if (in_array('Agregado', $requestedRoles) && !$request->input('morador_vinculado_id')) {
    return redirect()->back()->withErrors([
        'morador_vinculado_id' => 'Agregados devem estar vinculados a um morador.'
    ]);
}
```

2. **Morador deve ter unidade vinculada:**
```php
$rolesWithoutUnit = ['Administrador', 'Porteiro'];
$needsUnit = !array_intersect($rolesWithoutUnit, $requestedRoles);
if ($needsUnit && !$request->input('unit_id')) {
    return redirect()->back()->withErrors([
        'unit_id' => 'Este perfil requer que uma unidade seja vinculada.'
    ]);
}
```

## Melhorias Futuras Sugeridas

### 1. Validação de Morador Único
Adicionar validação para impedir que duas pessoas com perfil "Morador" sejam vinculadas à mesma unidade:

```php
// No UpdateUserRequest ou UserController
$existingMorador = User::where('unit_id', $request->unit_id)
    ->whereHas('roles', function($q) {
        $q->where('name', 'Morador');
    })
    ->where('id', '!=', $user->id)
    ->exists();

if ($existingMorador && in_array('Morador', $requestedRoles)) {
    return redirect()->back()->withErrors([
        'roles' => 'Esta unidade já possui um morador responsável.'
    ]);
}
```

### 2. Alerta Visual
Adicionar alerta quando uma unidade tiver agregados mas nenhum morador:

```blade
@if($unit->users->count() > 0 && !$unit->morador)
    <span class="badge bg-warning">
        <i class="bi bi-exclamation-triangle"></i> Sem responsável
    </span>
@endif
```

### 3. Filtro de Unidades sem Responsável
Adicionar filtro específico para listar unidades sem morador:

```php
<select name="tem_responsavel" class="form-select">
    <option value="">Com/Sem responsável</option>
    <option value="1">Com responsável</option>
    <option value="0">Sem responsável</option>
</select>
```

### 4. Método Helper no Model
Adicionar método para verificar situação irregular:

```php
public function hasSituacaoIrregular(): bool
{
    return $this->users()->count() > 0 && !$this->morador;
}
```

## Benefícios da Implementação

✅ **Clareza**: Fácil identificar o responsável pela unidade  
✅ **Performance**: Eager loading otimizado  
✅ **UX**: Ícones e cores facilitam identificação visual  
✅ **Manutenibilidade**: Código limpo e bem documentado  
✅ **Escalabilidade**: Relacionamento preparado para consultas complexas  

## Testes Recomendados

1. ✅ Listar unidades com morador
2. ✅ Listar unidades sem morador
3. ✅ Listar unidades com agregados mas sem morador
4. ✅ Verificar performance com muitas unidades
5. ✅ Testar filtros combinados

## Arquivos Modificados

1. `app/Models/Unit.php` - Adicionado relacionamento `morador()`
2. `app/Http/Controllers/UnitController.php` - Eager loading do morador
3. `resources/views/units/index.blade.php` - Nova coluna "Responsável"

## Status

✅ **IMPLEMENTADO** - A coluna "Responsável" exibe corretamente o morador de cada unidade

