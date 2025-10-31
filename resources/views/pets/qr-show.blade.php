<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Encontrado - {{ $pet->name }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .pet-card {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .pet-photo {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        .pet-info {
            padding: 30px;
        }
        .pet-name {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        .info-row {
            margin-bottom: 15px;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .contact-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .whatsapp-btn {
            background: #25D366;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4);
        }
        .whatsapp-btn:hover {
            background: #20ba5a;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.6);
            color: white;
        }
        .badge-custom {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="pet-card">
        <img src="{{ $pet->photo_url }}" alt="{{ $pet->name }}" class="pet-photo">
        
        <div class="pet-info">
            <div class="text-center mb-4">
                <div class="pet-name">{{ $pet->name }}</div>
                <span class="badge bg-primary badge-custom">{{ $pet->type_label }}</span>
                <span class="badge bg-info badge-custom">{{ $pet->size_label }}</span>
            </div>

            <div class="alert alert-success text-center">
                <i class="bi bi-check-circle-fill"></i>
                <strong>Pet Encontrado!</strong>
                <p class="mb-0 small">Use as informações abaixo para contatar o dono.</p>
            </div>

            <div class="info-row">
                <div class="info-label"><i class="bi bi-info-circle"></i> Informações do Pet</div>
            </div>

            @if($pet->breed)
            <div class="info-row">
                <span class="info-label">Raça:</span>
                <span class="info-value">{{ $pet->breed }}</span>
            </div>
            @endif

            @if($pet->color)
            <div class="info-row">
                <span class="info-label">Cor:</span>
                <span class="info-value">{{ $pet->color }}</span>
            </div>
            @endif

            @if($pet->observations)
            <div class="info-row">
                <span class="info-label">Observações:</span>
                <p class="info-value mb-0">{{ $pet->observations }}</p>
            </div>
            @endif

            <hr>

            <div class="contact-section">
                <div class="info-row">
                    <div class="info-label mb-3">
                        <i class="bi bi-person-circle"></i> Informações do Dono
                    </div>
                </div>

                <div class="info-row">
                    <span class="info-label">Nome:</span>
                    <span class="info-value">{{ $pet->owner->name }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Telefone:</span>
                    <span class="info-value">{{ $pet->owner->phone }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Unidade:</span>
                    <span class="info-value">{{ $pet->unit->full_identifier }}</span>
                </div>

                @if($pet->condominium)
                <div class="info-row">
                    <span class="info-label">Condomínio:</span>
                    <span class="info-value">{{ $pet->condominium->name }}</span>
                </div>
                @endif

                <div class="text-center mt-4">
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $pet->owner->phone) }}?text=Olá! Encontrei seu pet {{ $pet->name }} através do QR Code da coleira!" 
                       class="whatsapp-btn" target="_blank">
                        <i class="bi bi-whatsapp"></i>
                        Contatar Dono pelo WhatsApp
                    </a>
                </div>
            </div>

            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="bi bi-shield-check"></i>
                    Sistema de Identificação de Pets - CondoCenter
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

