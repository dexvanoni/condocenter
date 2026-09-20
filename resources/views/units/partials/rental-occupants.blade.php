@php
    use App\Services\SyndicConversationService;

    $morador = $unit->morador ?? $unit->users->first(fn ($u) => $u->hasAssignedRole('Morador'));
    $owner = $unit->owner;
    $syndicConversationService = app(SyndicConversationService::class);

    $contactCard = function ($person, string $roleLabel) use ($unit, $syndicConversationService, $canContactResidents) {
        if (!$person) {
            return null;
        }

        $whatsappDigits = preg_replace('/\D+/', '', (string) ($person->whatsappPhone() ?? ''));
        if ($whatsappDigits !== '' && !str_starts_with($whatsappDigits, '55')) {
            $whatsappDigits = '55' . $whatsappDigits;
        }
        $whatsappUrl = $whatsappDigits !== ''
            ? 'https://wa.me/' . $whatsappDigits . '?text=' . rawurlencode('Olá, ' . $person->name . '! Sou o síndico do ' . ($unit->condominium->name ?? 'condomínio') . '.')
            : null;

        $syndicChatUrl = null;
        if ($canContactResidents && Route::has('syndic-conversations.manage')) {
            $conversation = $syndicConversationService->findConversationForResidentOnUnit($person, $unit);
            $syndicChatUrl = $conversation
                ? route('syndic-conversations.manage', ['open' => $conversation->id])
                : route('syndic-conversations.manage');
        }

        return compact('person', 'roleLabel', 'whatsappUrl', 'syndicChatUrl');
    };

    $ownerCard = $contactCard($owner, 'Proprietário');
    $tenantCard = $contactCard($morador, 'Morador (inquilino)');
@endphp

<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-key"></i> Aluguel — ocupação</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach(array_filter([$ownerCard, $tenantCard]) as $card)
            <div class="col-md-6">
                <div class="border rounded p-3 h-100 bg-light">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-dark">{{ $card['roleLabel'] }}</span>
                            <h6 class="mt-2 mb-0">
                                @can('view_users')
                                <a href="{{ route('users.show', $card['person']) }}">{{ $card['person']->name }}</a>
                                @else
                                {{ $card['person']->name }}
                                @endcan
                            </h6>
                        </div>
                        @if($card['person']->is_active)
                            <span class="badge bg-success">Ativo</span>
                        @else
                            <span class="badge bg-secondary">Inativo</span>
                        @endif
                    </div>
                    <ul class="list-unstyled small text-muted mb-3">
                        @if($card['person']->email)
                        <li><i class="bi bi-envelope"></i> {{ $card['person']->email }}</li>
                        @endif
                        @if($card['person']->phone ?? $card['person']->telefone_celular)
                        <li><i class="bi bi-telephone"></i> {{ $card['person']->phone ?? $card['person']->telefone_celular }}</li>
                        @endif
                        @if($card['person']->cpf)
                        <li><i class="bi bi-person-vcard"></i> {{ $card['person']->cpf }}</li>
                        @endif
                    </ul>
                    @if($canContactResidents)
                    <div class="d-flex flex-wrap gap-2">
                        @if($card['whatsappUrl'])
                        <a href="{{ $card['whatsappUrl'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-success">
                            <i class="bi bi-whatsapp"></i> WhatsApp
                        </a>
                        @endif
                        @if($card['person']->email)
                        <a href="mailto:{{ $card['person']->email }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-envelope"></i> E-mail
                        </a>
                        @endif
                        @if($card['syndicChatUrl'])
                        <a href="{{ $card['syndicChatUrl'] }}" class="btn btn-sm btn-outline-dark">
                            <i class="bi bi-chat-dots"></i> Mensagem SindCON
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @if(!$ownerCard && !$tenantCard)
        <p class="text-muted mb-0">Cadastre proprietário e morador (inquilino) na edição da unidade.</p>
        @endif
    </div>
</div>
