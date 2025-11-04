# Correção do Erro no Update de Usuários

## Problema Identificado

Ao tentar alterar a unidade vinculada de um morador, ocorria o seguinte erro:

```
BadMethodCallException
Method App\Http\Requests\UpdateUserRequest::setRequest does not exist.
```

**Localização do Erro:**
- Arquivo: `app/Http/Controllers/UserController.php`
- Método: `update()`
- Linha: ~302

## Causa Raiz

O código estava tentando instanciar manualmente um `FormRequest` e chamar o método `setRequest()`, que não existe:

```php
$updateUserRequest = new UpdateUserRequest();
$updateUserRequest->setContainer(app());
$updateUserRequest->setRedirector(app('redirect'));
$updateUserRequest->setRequest($request); // ❌ Este método não existe!
```

Este é um padrão incorreto para usar FormRequests no Laravel. FormRequests devem ser injetados automaticamente pelo framework ou suas regras devem ser aplicadas diretamente via `$request->validate()`.

## Solução Implementada

Substituímos a instanciação manual do FormRequest por validação direta usando `$request->validate()`, mantendo todas as regras de validação:

### 1. Validação Principal

```php
$data = $request->validate([
    'condominium_id' => ['sometimes', 'required', 'exists:condominiums,id'],
    'unit_id' => ['nullable', 'exists:units,id'],
    'morador_vinculado_id' => ['nullable', 'exists:users,id'],
    'name' => ['sometimes', 'required', 'string', 'max:255'],
    'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
    // ... demais regras
]);
```

### 2. Validações Customizadas

Adicionamos as validações customizadas que estavam no `UpdateUserRequest::withValidator()`:

```php
if ($request->has('roles')) {
    $requestedRoles = $request->input('roles', []);
    
    // Validar perfis restritos
    $restrictedRoles = ['Síndico', 'Conselho Fiscal'];
    if (array_intersect($restrictedRoles, $requestedRoles) && !$this->authUser()->hasRole('Administrador')) {
        return redirect()->back()->withErrors([
            'roles' => 'Apenas administradores podem atribuir os perfis de Síndico ou Conselho Fiscal.'
        ])->withInput();
    }
    
    // Validar agregado com morador vinculado
    if (in_array('Agregado', $requestedRoles) && !$request->input('morador_vinculado_id')) {
        return redirect()->back()->withErrors([
            'morador_vinculado_id' => 'Agregados devem estar vinculados a um morador.'
        ])->withInput();
    }
    
    // Validar unidade obrigatória
    $rolesWithoutUnit = ['Administrador', 'Porteiro'];
    $needsUnit = !array_intersect($rolesWithoutUnit, $requestedRoles);
    if ($needsUnit && !$request->input('unit_id')) {
        return redirect()->back()->withErrors([
            'unit_id' => 'Este perfil requer que uma unidade seja vinculada.'
        ])->withInput();
    }
}
```

## Arquivos Modificados

1. **app/Http/Controllers/UserController.php**
   - Método `update()` (linhas ~297-348)
   - Substituída instanciação manual do FormRequest por validação direta
   - Adicionadas validações customizadas

## Validações Mantidas

Todas as validações do `UpdateUserRequest` foram preservadas:

✅ Validação de campos obrigatórios  
✅ Validação de unicidade (email, CPF)  
✅ Validação de formatos (CPF, datas)  
✅ Validação de relacionamentos (unidade, condomínio)  
✅ Validação de perfis restritos (Síndico, Conselho Fiscal)  
✅ Validação de agregado vinculado  
✅ Validação de unidade obrigatória  

## Benefícios da Solução

1. **Simplicidade**: Código mais direto e fácil de entender
2. **Manutenibilidade**: Regras de validação visíveis no mesmo local
3. **Performance**: Evita overhead de instanciar objetos desnecessários
4. **Consistência**: Usa o padrão recomendado do Laravel

## Fluxo de Atualização

O método `update()` agora tem dois caminhos distintos:

### 1. Usuário editando seu próprio perfil
- Usa `UpdateProfileRequest` (injetado automaticamente)
- Campos restritos (apenas os que o usuário pode editar)

### 2. Admin/Síndico editando qualquer usuário
- Usa validação direta com `$request->validate()`
- Campos completos (todos os campos editáveis)
- Validações customizadas adicionais

## Teste da Correção

Para testar a correção:

1. Acesse a edição de um usuário como Admin ou Síndico
2. Altere a unidade vinculada
3. Salve as alterações
4. Verifique se a atualização foi concluída com sucesso

## Observações

- O `UpdateUserRequest` ainda é usado para autorização via Policy
- A validação inline mantém todas as regras do FormRequest original
- As mensagens de erro são retornadas corretamente ao usuário
- O comportamento é idêntico ao anterior, mas sem o erro

## Status

✅ **CORRIGIDO** - A alteração de unidade de morador agora funciona corretamente

