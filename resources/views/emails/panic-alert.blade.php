<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .alert-header {
            background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 5px 5px 0 0;
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        .alert-icon {
            font-size: 60px;
            margin-bottom: 10px;
        }
        .content {
            background: white;
            padding: 30px;
            border-radius: 0 0 5px 5px;
            border-left: 5px solid #0a1b67;
            border-right: 5px solid #0a1b67;
            border-bottom: 5px solid #0a1b67;
        }
        .emergency-box {
            background: rgba(10, 27, 103, 0.08);
            border-left: 4px solid #3866d2;
            padding: 20px;
            margin: 20px 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        .info-item {
            background: #f9f9f9;
            padding: 12px;
            border-radius: 5px;
        }
        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
        }
        .info-value {
            color: #333;
            font-size: 16px;
            margin-top: 5px;
        }
        .urgent-notice {
            background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
            font-size: 18px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="alert-header">
            <div class="alert-icon">🚨</div>
            <h1 style="margin: 0; font-size: 32px;">ALERTA DE EMERGÊNCIA</h1>
            <h2 style="margin: 10px 0 0 0;">{{ $alertData['alert_title'] }}</h2>
        </div>
        
        <div class="content">
            <div class="urgent-notice">
                ⚠️ ATENÇÃO: SITUAÇÃO DE EMERGÊNCIA NO CONDOMÍNIO ⚠️
            </div>

            <div class="emergency-box">
                <strong>🏢 Condomínio:</strong> {{ $alertData['condominium_name'] }}<br>
                <strong>📅 Data/Hora:</strong> {{ $alertData['timestamp'] }}
            </div>

            <h3 style="color: #0a1b67;">Informações do Alerta:</h3>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">👤 Enviado por</div>
                    <div class="info-value">{{ $alertData['user_name'] }}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">🏠 Unidade</div>
                    <div class="info-value">{{ $alertData['user_unit'] }}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">📱 Telefone</div>
                    <div class="info-value">{{ $alertData['user_phone'] }}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">🕐 Horário</div>
                    <div class="info-value">{{ $alertData['timestamp'] }}</div>
                </div>
            </div>

            @if(!empty($alertData['additional_info']))
            <div class="emergency-box">
                <strong>📝 Informações Adicionais:</strong><br>
                {{ $alertData['additional_info'] }}
            </div>
            @endif

            <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <strong>🔍 Dados Técnicos (para registro):</strong><br>
                <small>
                    IP do Dispositivo: {{ $alertData['ip_address'] }}<br>
                    User Agent: {{ Str::limit($alertData['user_agent'], 100) }}
                </small>
            </div>

            <div class="urgent-notice">
                TOME AS MEDIDAS NECESSÁRIAS IMEDIATAMENTE!
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <p style="color: #666; margin-bottom: 10px;">Responda rapidamente:</p>
                <div style="display: inline-block;">
                    <a href="tel:{{ $alertData['user_phone'] }}" 
                       style="background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; font-weight: bold;">
                        📞 Ligar para {{ $alertData['user_name'] }}
                    </a>
                </div>
                <div style="display: inline-block;">
                    <a href="{{ config('app.url') }}/dashboard" 
                       style="background: linear-gradient(135deg, #0a1b67 0%, #3866d2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; font-weight: bold;">
                        🖥️ Acessar o Sistema
                    </a>
                </div>
            </div>

            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-top: 20px;">
                <strong>⚠️ Orientações Gerais:</strong>
                <ul style="margin: 10px 0;">
                    @if($alertData['alert_type'] === 'fire')
                    <li><strong>INCÊNDIO:</strong> Acione o alarme, evacue o prédio, chame os bombeiros (193)</li>
                    @elseif($alertData['alert_type'] === 'lost_child')
                    <li><strong>CRIANÇA PERDIDA:</strong> Organize busca, verifique áreas comuns, acione portaria</li>
                    @elseif($alertData['alert_type'] === 'flood')
                    <li><strong>ENCHENTE:</strong> Desligue energia, evacue áreas alagadas, chame defesa civil</li>
                    @elseif($alertData['alert_type'] === 'robbery')
                    <li><strong>ROUBO/FURTO:</strong> Não confronte, chame polícia (190), preserve local</li>
                    @elseif($alertData['alert_type'] === 'police')
                    <li><strong>POLÍCIA:</strong> Ligue 190, mantenha-se seguro, aguarde</li>
                    @elseif($alertData['alert_type'] === 'domestic_violence')
                    <li><strong>VIOLÊNCIA DOMÉSTICA:</strong> Ligue 190 ou 180, ofereça suporte à vítima</li>
                    @elseif($alertData['alert_type'] === 'ambulance')
                    <li><strong>AMBULÂNCIA:</strong> Ligue 192 (SAMU), preste primeiros socorros se souber</li>
                    @endif
                    <li>Mantenha a calma e aja com responsabilidade</li>
                    <li>Coordene com síndico e administração</li>
                    <li>Documente tudo para registro</li>
                </ul>
            </div>
        </div>

        <div class="footer">
            <p><strong>⚠️ ESTE É UM EMAIL URGENTE DE EMERGÊNCIA</strong></p>
            <p>Enviado automaticamente pelo sistema CondoManager</p>
            <p>{{ $alertData['condominium_name'] }}</p>
            <p>&copy; {{ date('Y') }} CondoManager - Sistema de Gestão de Condomínios</p>
        </div>
    </div>
</body>
</html>

