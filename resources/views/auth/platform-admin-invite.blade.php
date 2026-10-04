<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convite de Administrador - {{ config('app.name', 'SindCON') }}</title>
    <x-sindcon-favicon />
    @include('partials.sindcon-brand-styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(10,27,103,0.15);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);
            border: none;
        }
        .btn-primary:hover {
            filter: brightness(1.05);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="login-header">
                        <x-sindcon-logo variant="auth" />
                        <h3 class="mb-1"><i class="bi bi-shield-lock"></i> Administrador da plataforma</h3>
                        <p class="mb-0 opacity-75">
                            @if($existingUser)
                                Aceite o convite para o perfil Administrador
                            @else
                                Conclua o cadastro do convite
                            @endif
                        </p>
                    </div>
                    <div class="p-4">
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <p class="text-muted small mb-3">
                            Convite para <strong>{{ $email }}</strong>. Válido por {{ $expireDays }} dias.
                        </p>

                        <form method="POST" action="{{ $formAction }}">
                            @csrf

                            @if($existingUser)
                                <p class="mb-4">
                                    Olá, <strong>{{ $existingUser->name }}</strong>. Ao aceitar, o perfil
                                    <strong>Administrador</strong> é adicionado à sua conta. Os demais perfis permanecem.
                                </p>
                                <button type="submit" class="btn btn-primary w-100 py-2">
                                    <i class="bi bi-check-circle"></i> Aceitar convite
                                </button>
                            @else
                                <div class="mb-3">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="email" value="{{ $email }}" disabled>
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome completo</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                           name="name" value="{{ old('name') }}" required autofocus>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Senha</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" required minlength="8">
                                    <small class="text-muted">Mínimo de 8 caracteres.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmar senha</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                           name="password_confirmation" required minlength="8">
                                </div>
                                <button type="submit" class="btn btn-primary w-100 py-2">
                                    <i class="bi bi-person-plus"></i> Criar conta e entrar
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
