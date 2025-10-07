<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .alert {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .details-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .details-row:last-child {
            border-bottom: none;
        }
        .details-label {
            font-weight: bold;
            width: 150px;
            color: #666;
        }
        .details-value {
            color: #333;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="icon">❌</div>
        <h1>{{ $isForSindico ? 'Cancelamento de Reserva' : 'Sua Reserva Foi Cancelada' }}</h1>
    </div>
    
    <div class="content">
        @if($isForSindico)
            <p>Olá, <strong>Síndico</strong>,</p>
            <p>Informamos que uma reserva foi cancelada pelo morador:</p>
        @else
            <p>Olá, <strong>{{ $morador }}</strong>,</p>
            <p>Informamos que sua reserva foi cancelada.</p>
        @endif
        
        <div class="alert">
            <strong>⚠️ Reserva Cancelada</strong>
            <p style="margin: 10px 0 0 0;">
                @if($isForSindico)
                    Esta reserva foi cancelada pelo próprio morador.
                @else
                    Esta reserva foi cancelada pela administração do condomínio.
                @endif
            </p>
        </div>
        
        <div class="details">
            <h3 style="margin-top: 0; color: #dc3545;">📋 Detalhes da Reserva Cancelada</h3>
            
            <div class="details-row">
                <div class="details-label">🏢 Espaço:</div>
                <div class="details-value">{{ $spaceName }}</div>
            </div>
            
            <div class="details-row">
                <div class="details-label">📅 Data:</div>
                <div class="details-value">{{ $reservationDate }}</div>
            </div>
            
            <div class="details-row">
                <div class="details-label">⏰ Horário:</div>
                <div class="details-value">{{ $startTime }} às {{ $endTime }}</div>
            </div>
            
            @if($isForSindico)
            <div class="details-row">
                <div class="details-label">👤 Morador:</div>
                <div class="details-value">{{ $morador }}</div>
            </div>
            @endif
            
            <div class="details-row">
                <div class="details-label">🚫 Cancelado por:</div>
                <div class="details-value">{{ $cancelledByName }}</div>
            </div>
            
            <div class="details-row">
                <div class="details-label">📆 Cancelado em:</div>
                <div class="details-value">{{ $reservation->cancelled_at->format('d/m/Y H:i') }}</div>
            </div>
            
            @if($reservation->cancellation_reason)
            <div class="details-row">
                <div class="details-label">📝 Motivo:</div>
                <div class="details-value">{{ $reservation->cancellation_reason }}</div>
            </div>
            @endif
        </div>
        
        @if(!$isForSindico)
            <p>Se você deseja fazer uma nova reserva, acesse o sistema e escolha outra data disponível.</p>
        @endif
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ url('/reservations') }}" style="display: inline-block; padding: 12px 30px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                {{ $isForSindico ? 'Ver Todas as Reservas' : 'Fazer Nova Reserva' }}
            </a>
        </div>
        
        @if(!$isForSindico)
        <p style="margin-top: 30px; padding: 15px; background: #e7f3ff; border-left: 4px solid #0d6efd; border-radius: 4px;">
            <strong>💡 Dica:</strong> Para evitar cancelamentos, lembre-se de verificar sua disponibilidade antes de fazer a reserva.
        </p>
        @endif
    </div>
    
    <div class="footer">
        <p><strong>{{ config('app.name') }}</strong></p>
        <p>Este é um e-mail automático. Por favor, não responda.</p>
        <p>{{ $reservation->space->condominium->name }}</p>
    </div>
</body>
</html>

