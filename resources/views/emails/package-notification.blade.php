<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .info-box {
            background: white;
            padding: 15px;
            border-left: 4px solid #667eea;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 {{ $type === 'arrived' ? 'Nova Encomenda!' : 'Encomenda Retirada' }}</h1>
        </div>
        <div class="content">
            @if($type === 'arrived')
                <p>Olá!</p>
                <p>Uma encomenda chegou para a <strong>Unidade {{ $package->unit->full_identifier }}</strong>.</p>
                
                <div class="info-box">
                    <h3 style="margin-top: 0;">Detalhes da Encomenda</h3>
                    
                    @if($package->sender)
                    <p><strong>Remetente:</strong> {{ $package->sender }}</p>
                    @endif
                    
                    @if($package->tracking_code)
                    <p><strong>Código de Rastreio:</strong> {{ $package->tracking_code }}</p>
                    @endif
                    
                    @if($package->description)
                    <p><strong>Descrição:</strong> {{ $package->description }}</p>
                    @endif
                    
                    <p><strong>Data/Hora de Chegada:</strong> {{ $package->received_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Registrado por:</strong> {{ $package->registeredBy->name }}</p>
                </div>
                
                <p style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
                    <strong>⚠️ Importante:</strong> Retire sua encomenda na portaria o quanto antes.
                </p>
            @else
                <p>Olá!</p>
                <p>Informamos que a encomenda da <strong>Unidade {{ $package->unit->full_identifier }}</strong> foi retirada.</p>
                
                <div class="info-box">
                    <h3 style="margin-top: 0;">Detalhes da Retirada</h3>
                    <p><strong>Data/Hora:</strong> {{ $package->collected_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Retirado por:</strong> {{ $package->collectedBy->name ?? 'Não informado' }}</p>
                </div>
            @endif
            
            <p style="text-align: center; margin-top: 30px;">
                <a href="{{ config('app.url') }}/dashboard" style="background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                    Acessar o Sistema
                </a>
            </p>
        </div>
        <div class="footer">
            <p>Este é um e-mail automático. Não responda.</p>
            <p>&copy; {{ date('Y') }} CondoManager - Gestão de Condomínios</p>
        </div>
    </div>
</body>
</html>

