@php
    /** @var \App\Models\Condominium $condominium */
    $syndics = $syndics ?? collect();
    $canManageSyndics = auth()->user()?->isAdmin() ?? false;
    $whatsappIntro = 'Olá! Aqui é da gestão SindCON, sobre o condomínio '.$condominium->name.'.';
@endphp

<div class="card shadow-sm mt-4">
    <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0"><i class="bi bi-person-badge"></i> Síndico do condomínio</h5>
        @if($canManageSyndics)
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#syndicAttachModal">
                <i class="bi bi-person-plus"></i> Incluir síndico
            </button>
        @endif
    </div>
    <div class="card-body">
        @if($syndics->isEmpty())
            <p class="text-muted mb-0">
                Nenhum síndico vinculado a <strong>{{ $condominium->name }}</strong>.
                @if($canManageSyndics)
                    Use <strong>Incluir síndico</strong> para cadastrar ou vincular uma pessoa com perfil Síndico.
                @endif
            </p>
        @else
            <div class="row g-3">
                @foreach($syndics as $syndic)
                    @php
                        $syndicWhatsappUrl = $syndic->whatsappChatUrl(
                            'Olá, '.$syndic->name.'! '.$whatsappIntro
                        );
                    @endphp
                    <div class="col-12">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                <div class="d-flex align-items-start gap-3 min-w-0">
                                    <span class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:2.5rem;height:2.5rem;">
                                        <i class="bi bi-person-badge"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="fw-semibold">{{ $syndic->name }}</div>
                                        <div class="small text-muted text-break">
                                            <i class="bi bi-envelope me-1"></i>
                                            @if($syndic->email)
                                                <a href="mailto:{{ $syndic->email }}">{{ $syndic->email }}</a>
                                            @else
                                                —
                                            @endif
                                        </div>
                                        <div class="small text-muted">
                                            <i class="bi bi-telephone me-1"></i>
                                            {{ $syndic->phone ?: 'Telefone não informado' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @if($syndicWhatsappUrl)
                                        <a href="{{ $syndicWhatsappUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-success" title="WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    @endif
                                    @if($canManageSyndics)
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary syndic-edit-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#syndicEditModal"
                                                data-user-id="{{ $syndic->id }}"
                                                data-name="{{ $syndic->name }}"
                                                data-email="{{ $syndic->email }}"
                                                data-phone="{{ $syndic->phone }}"
                                                data-update-url="{{ route('condominiums.syndics.update', [$condominium, $syndic]) }}">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        <form method="POST"
                                              action="{{ route('condominiums.syndics.destroy', [$condominium, $syndic]) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Desvincular {{ $syndic->name }} deste condomínio? A conta do usuário permanece no sistema.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-person-x"></i> Excluir vínculo
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@if($canManageSyndics)
    <div class="modal fade" id="syndicAttachModal" tabindex="-1" aria-labelledby="syndicAttachModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('condominiums.syndics.store', $condominium) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="syndicAttachModalLabel">Incluir síndico</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">Informe os dados de quem atuará como síndico em <strong>{{ $condominium->name }}</strong>. Se o e-mail já existir, o usuário será vinculado; caso contrário, enviamos um link para definir a senha.</p>
                        <div class="mb-3">
                            <label class="form-label" for="syndic_name">Nome *</label>
                            <input type="text" name="syndic_name" id="syndic_name" class="form-control @error('syndic_name') is-invalid @enderror" value="{{ old('syndic_name') }}" required>
                            @error('syndic_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="syndic_email">E-mail *</label>
                            <input type="email" name="syndic_email" id="syndic_email" class="form-control @error('syndic_email') is-invalid @enderror" value="{{ old('syndic_email') }}" required>
                            @error('syndic_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="syndic_phone">Telefone</label>
                            <input type="text" name="syndic_phone" id="syndic_phone" class="form-control" value="{{ old('syndic_phone') }}" placeholder="Opcional — usado para WhatsApp">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-check2"></i> Vincular</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="syndicEditModal" tabindex="-1" aria-labelledby="syndicEditModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="syndicEditForm" action="#">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="syndicEditModalLabel">Editar síndico</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="syndic_edit_name">Nome *</label>
                            <input type="text" name="name" id="syndic_edit_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="syndic_edit_email">E-mail *</label>
                            <input type="email" name="email" id="syndic_edit_email" class="form-control" required>
                        </div>
                        <div class="mb-0">
                            <label class="form-label" for="syndic_edit_phone">Telefone</label>
                            <input type="text" name="phone" id="syndic_edit_phone" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.querySelectorAll('.syndic-edit-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var form = document.getElementById('syndicEditForm');
                form.action = btn.getAttribute('data-update-url') || '#';
                document.getElementById('syndic_edit_name').value = btn.getAttribute('data-name') || '';
                document.getElementById('syndic_edit_email').value = btn.getAttribute('data-email') || '';
                document.getElementById('syndic_edit_phone').value = btn.getAttribute('data-phone') || '';
            });
        });
        @if($errors->has('syndic_name') || $errors->has('syndic_email'))
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('syndicAttachModal');
            if (el && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(el).show();
            }
        });
        @endif
    </script>
    @endpush
@endif
