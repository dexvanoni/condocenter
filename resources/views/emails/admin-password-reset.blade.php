@component('mail::message')
# Redefinição de senha

Olá, **{{ $user->name }}**!

A administração do condomínio ({{ $initiatedByName }}) solicitou a redefinição da sua senha no **{{ config('app.name') }}**.

@component('mail::button', ['url' => $resetUrl])
Redefinir minha senha
@endcomponent

Este link é válido por **{{ $expireMinutes }} minutos**. Se você não solicitou esta alteração, ignore este e-mail ou contate a administração.

@component('mail::panel')
Por segurança, escolha uma senha forte com pelo menos 8 caracteres.
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent
