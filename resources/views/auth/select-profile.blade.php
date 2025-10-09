@extends('layouts.guest')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-5 text-center">
                    <h3 class="mb-4">
                        <i class="bi bi-person-badge"></i><br>
                        Selecione seu Perfil
                    </h3>
                    
                    <p class="text-muted mb-4">
                        Você possui múltiplos perfis. Selecione qual deseja utilizar nesta sessão:
                    </p>

                    <form action="{{ route('profile.set') }}" method="POST">
                        @csrf
                        
                        <div class="row g-3">
                            @foreach($roles as $role)
                            <div class="col-12">
                                <button type="submit" name="role" value="{{ $role->name }}" 
                                        class="btn btn-lg btn-outline-primary w-100 py-3">
                                    <i class="bi bi-person-circle fs-3 d-block mb-2"></i>
                                    <strong>{{ $role->name }}</strong>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

