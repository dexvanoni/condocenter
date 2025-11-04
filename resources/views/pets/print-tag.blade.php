<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tag Pet - {{ $pet->name }}</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 20px;
        }

        /* Container da tag - 3x4cm */
        .pet-tag {
            width: 3cm;
            height: 4cm;
            background: white;
            border: 2px solid #333;
            border-radius: 8px;
            padding: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Container do QR Code */
        .qr-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 5px 0;
            min-height: 0;
        }

        .qr-container svg {
            width: 100%;
            height: auto;
            max-width: 2.5cm;
            max-height: 2.5cm;
            display: block;
        }

        /* Informações do dono */
        .owner-info {
            text-align: center;
            width: 100%;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            flex-shrink: 0;
        }

        .owner-label {
            font-size: 7px;
            color: #666;
            margin-bottom: 3px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .owner-phone {
            font-size: 9px;
            color: #000;
            font-weight: bold;
            word-break: break-all;
            line-height: 1.3;
            display: block;
        }

        /* Estilos para impressão */
        @media print {
            body {
                padding: 0;
                margin: 0;
                background: white;
            }

            .pet-tag {
                border: 1.5px solid #000;
                page-break-inside: avoid;
                break-inside: avoid;
                box-shadow: none;
            }

            @page {
                size: auto;
                margin: 0.5cm;
            }

            /* Permite múltiplas tags na mesma página */
            .pet-tag {
                margin-bottom: 0.5cm;
            }

            /* Impede quebra de página dentro da tag */
            .pet-tag * {
                page-break-inside: avoid;
            }
        }

        /* Para visualização em tela */
        @media screen {
            .pet-tag {
                margin: 20px auto;
            }
            
            /* Instruções de impressão */
            .print-instructions {
                position: fixed;
                top: 20px;
                right: 20px;
                background: #4CAF50;
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                font-size: 14px;
                max-width: 300px;
            }
            
            .print-instructions strong {
                display: block;
                margin-bottom: 8px;
                font-size: 16px;
            }
            
            .print-instructions button {
                margin-top: 10px;
                background: white;
                color: #4CAF50;
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
                width: 100%;
            }
            
            .print-instructions button:hover {
                background: #f0f0f0;
            }
        }
        
        @media print {
            .print-instructions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-instructions">
        <strong>📌 Pronto para imprimir!</strong>
        <p>Pressione <strong>Ctrl+P</strong> (ou Cmd+P no Mac) para imprimir a tag.</p>
        <p style="font-size: 12px; margin-top: 8px; opacity: 0.9;">Tamanho: 3x4cm</p>
        <button onclick="window.print()">🖨️ Imprimir Agora</button>
    </div>

    <div class="pet-tag">
        <div class="qr-container">
            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->format('svg')->errorCorrection('H')->generate(url('/pets/qr/' . $pet->qr_code)) !!}
        </div>
        
        <div class="owner-info">
            <div class="owner-phone">{{ $pet->owner->phone }}</div>
            <div class="owner-phone">{{ $pet->name }}</div>
        </div>
    </div>
</body>
</html>
