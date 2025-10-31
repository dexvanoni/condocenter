# 🔧 Correção - Permissão manage_reservations

## 🐛 Problema

Ao tentar acessar a rota `/reservations/manage` como Administrador, o sistema retornava erro **403 - This action is unauthorized**.

## 🔍 Causa

A rota estava configurada com o middleware `can:manage_reservations`, porém:

1. A permissão `manage_reservations` **não existia** no seeder de permissões
2. Mesmo que existisse, **não estava sendo atribuída** aos perfis Administrador e Síndico

## ✅ Solução Aplicada

### 1. Adicionar Permissão ao Seeder

Arquivo: `database/seeders/RolesAndPermissionsSeeder.php`

```php
// Reservas
'manage_spaces',
'view_spaces',
'make_reservations',
'manage_reservations',  // ← ADICIONADA
'approve_reservations',
'view_reservations',
```

### 2. Atribuir Permissão aos Roles

```php
// Síndico
$sindicoRole->syncPermissions([
    // ...
    'manage_spaces',
    'view_spaces',
    'manage_reservations',  // ← ADICIONADA
    'approve_reservations',
    'view_reservations',
    // ...
]);
```

### 3. Executar Correção no Banco de Dados

```bash
php artisan permission:add-manage-reservations
```

## 📊 Permissões de Reservas

### Hierarquia de Permissões

1. **`view_reservations`** - Visualizar reservas
2. **`make_reservations`** - Fazer suas próprias reservas
3. **`manage_reservations`** - Gerenciar/aprovar reservas de outros
4. **`approve_reservations`** - Aprovar todas as reservas

### Atribuição por Perfil

| Perfil | view_reservations | make_reservations | manage_reservations | approve_reservations |
|--------|-------------------|-------------------|---------------------|---------------------|
| **Administrador** | ✅ | ✅ | ✅ | ✅ |
| **Síndico** | ✅ | ✅ | ✅ | ✅ |
| **Morador** | ✅ | ✅ | ❌ | ❌ |
| **Agregado** | ⚙️ | ⚙️ | ❌ | ❌ |
| **Porteiro** | ❌ | ❌ | ❌ | ❌ |
| **Conselho Fiscal** | ❌ | ❌ | ❌ | ❌ |

**Nota**: Agregados usam `AgregadoPermission` com permissões granulares no módulo `spaces`.

## 🎯 Rotas Afetadas

### Rotas que usam `manage_reservations`:

```php
// Gerenciar Reservas (Síndico/Admin)
Route::middleware(['can:manage_reservations'])->group(function () {
    Route::get('/reservations/manage', function() { 
        return view('reservations.manage'); 
    })->name('reservations.manage');
});
```

### Rotas que usam `approve_reservations`:

```php
// Reservas Recorrentes (Síndico/Admin)
Route::middleware(['can:approve_reservations'])->group(function () {
    Route::resource('recurring-reservations', RecurringReservationController::class);
});

// Administração de Reservas (Síndico/Admin)
Route::middleware(['can:approve_reservations'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/reservations', [AdminReservationController::class, 'index']);
        // ...
    });
});
```

## 🧪 Como Testar

1. Faça login como Administrador
2. Acesse: `http://localhost:8000/reservations/manage`
3. Deve carregar a página sem erro 403

## 📝 Notas Importantes

- Administradores recebem **todas as permissões** automaticamente via `syncPermissions(Permission::all())`
- Ao adicionar novas permissões, sempre execute o seeder novamente
- Limpar cache de permissões após mudanças: `app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions()`

## 🚀 Para Futuras Permissões

Ao criar novas permissões relacionadas a reservas:

1. Adicionar ao array `$permissions` no seeder
2. Atribuir aos roles apropriados no seeder
3. Executar: `php artisan db:seed --class=RolesAndPermissionsSeeder`
4. Limpar cache: `php artisan cache:clear`

---

**Status**: ✅ Corrigido  
**Data**: 2024  
**Versão**: 1.0

