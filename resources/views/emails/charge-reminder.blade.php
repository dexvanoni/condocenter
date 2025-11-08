@component('mail::message')
# {{ $context === 'due_today' ? 'Cobrança vence hoje' : 'Lembrete de cobrança' }}

Olá! 👋

@if($context === 'due_today')
Apenas lembrando que a cobrança **{{ $charge->title }}** vence **hoje ({{ $dueDate->format('d/m/Y') }})**.
@else
Falta apenas **1 dia** para o vencimento da cobrança **{{ $charge->title }}** ({{ $dueDate->format('d/m/Y') }}).
@endif

- Valor: **R$ {{ number_format($charge->amount, 2, ',', '.') }}**
- Unidade: **{{ optional($charge->unit)->full_identifier ?? '—' }}**
- Competência: **{{ $charge->recurrence_period ?? '—' }}**

Caso o pagamento já tenha sido realizado, desconsidere este aviso. 😉

@component('mail::panel')
Mantenha suas contribuições em dia para garantir o bom funcionamento do condomínio.
@endcomponent

@component('mail::button', ['url' => config('app.url') . '/charges'])
Ver minhas cobranças
@endcomponent

Obrigado,<br>
{{ config('app.name') }}
@endcomponent

