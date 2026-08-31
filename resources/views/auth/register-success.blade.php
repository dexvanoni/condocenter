<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Enviado - CondoManager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .success-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,.15);
            max-width: 520px;
            width: 100%;
            text-align: center;
            padding: 2.5rem 2rem;
        }
        .success-icon {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: #ecfdf5;
            color: #059669;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon"><i class="bi bi-check-lg"></i></div>
        <h2 class="fw-bold mb-3">Cadastro enviado!</h2>
        <p class="text-muted mb-4">
            Obrigado, <strong>{{ session('registered_name') }}</strong>. Sua solicitação foi recebida e está
            <strong>aguardando aprovação</strong> da administração do condomínio.
        </p>
        <div class="alert alert-info text-start">
            <i class="bi bi-info-circle me-2"></i>
            Você receberá acesso ao sistema assim que um administrador aprovar seu cadastro. Depois disso, use o e-mail e a senha que você definiu para entrar.
        </div>
        <a href="{{ route('login') }}" class="btn btn-primary btn-lg w-100">
            <i class="bi bi-box-arrow-in-right me-1"></i> Ir para o login
        </a>
    </div>
</body>
</html>
