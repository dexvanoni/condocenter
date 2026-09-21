@php
    $contact = $developerContact ?? config('saas.developer_contact');
@endphp
<div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
    <h5 class="alert-heading">
        <i class="bi bi-exclamation-triangle-fill"></i> Limite de unidades atingido
    </h5>
    <p class="mb-2">
        Seu condomínio já possui o número máximo de unidades permitido pelo plano da plataforma
        @if($condominium?->hasUnitsQuota())
            (<strong>{{ $condominium->unitsQuotaSummary() }}</strong>).
        @else
            .
        @endif
    </p>
    <p class="mb-0">
        Para cadastrar mais unidades, entre em contato com o desenvolvedor
        @if($contact)
            @if(filter_var($contact, FILTER_VALIDATE_EMAIL))
                em <a href="mailto:{{ $contact }}" class="alert-link fw-semibold">{{ $contact }}</a>.
            @else
                : <strong>{{ $contact }}</strong>.
            @endif
        @else
            da plataforma SindCON.
        @endif
    </p>
</div>
