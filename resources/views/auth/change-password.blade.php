@extends('layouts.guest')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="sindcon-logo-wrap--light mb-3">
                            <x-sindcon-logo variant="inline" class="mb-0" />
                        </div>
                        <h3 class="mb-0">
                            <i class="bi bi-shield-lock"></i>
                            Alterar Senha
                        </h3>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Por segurança, você precisa alterar sua senha temporária antes de continuar.
                    </div>

                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Senha Atual <span class="text-danger">*</span></label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nova Senha <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            <small class="text-muted">Mínimo de 8 caracteres</small>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmar Nova Senha <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-3">
                            <i class="bi bi-check-circle"></i> Alterar Senha
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

