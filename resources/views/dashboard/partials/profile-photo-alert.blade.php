@php
    $currentUser = auth()->user();
@endphp

@if($currentUser && empty($currentUser->photo))
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning border-0 shadow-sm mb-0">
            <div class="d-flex align-items-start flex-wrap gap-3">
                <i class="bi bi-person-bounding-box fs-3 text-warning"></i>
                <div class="flex-grow-1">
                    <h6 class="alert-heading mb-1">Cadastre sua foto de perfil</h6>
                    <p class="mb-0">
                        Sua conta ainda não possui foto de identificação.
                        Cadastre uma selfie para facilitar o reconhecimento na portaria e nos acessos do condomínio.
                    </p>
                </div>
                <a href="{{ route('users.edit', $currentUser) }}#profile-photo" class="btn btn-warning align-self-center">
                    <i class="bi bi-camera me-1"></i> Cadastrar foto
                </a>
            </div>
        </div>
    </div>
</div>
@endif
