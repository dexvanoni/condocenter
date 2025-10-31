<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir - Regimento Interno</title>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            
            @page {
                margin: 2cm;
            }
        }
        
        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.8;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #0d6efd;
            font-size: 24pt;
            margin: 0 0 10px 0;
            text-transform: uppercase;
        }
        
        .header h2 {
            font-size: 16pt;
            color: #666;
            font-weight: normal;
            margin: 5px 0;
        }
        
        .metadata {
            background-color: #f8f9fa;
            padding: 20px;
            margin-bottom: 40px;
            border-left: 5px solid #0d6efd;
        }
        
        .metadata table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .metadata td {
            padding: 8px 0;
            font-size: 11pt;
        }
        
        .metadata td:first-child {
            font-weight: bold;
            color: #0d6efd;
            width: 30%;
        }
        
        .content {
            text-align: justify;
            white-space: pre-wrap;
            font-size: 12pt;
            line-height: 1.8;
        }
        
        .print-button-container {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #e9ecef;
        }
        
        .print-button {
            background-color: #0d6efd;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 5px;
            font-size: 14pt;
            cursor: pointer;
            margin: 0 10px;
        }
        
        .print-button:hover {
            background-color: #0b5ed7;
        }
        
        .back-button {
            background-color: #6c757d;
        }
        
        .back-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="print-button-container no-print">
        <button onclick="window.print()" class="print-button">
            🖨️ Imprimir Documento
        </button>
        <button onclick="window.close()" class="back-button print-button">
            ← Voltar
        </button>
    </div>

    <div class="header">
        <h1>Regimento Interno</h1>
        <h2>{{ $regulation->condominium->name ?? 'Condomínio' }}</h2>
    </div>
    
    <div class="metadata">
        <table>
            <tr>
                <td>Versão:</td>
                <td>{{ $regulation->version }}</td>
            </tr>
            @if($regulation->assembly_date)
            <tr>
                <td>Data de Aprovação:</td>
                <td>{{ $regulation->formatted_assembly_date }}</td>
            </tr>
            @endif
            @if($regulation->assembly_details)
            <tr>
                <td>Assembleia:</td>
                <td>{{ $regulation->assembly_details }}</td>
            </tr>
            @endif
            <tr>
                <td>Última Atualização:</td>
                <td>{{ $regulation->updated_at->format('d/m/Y H:i') }}</td>
            </tr>
            @if($regulation->updatedBy)
            <tr>
                <td>Atualizado por:</td>
                <td>{{ $regulation->updatedBy->name }}</td>
            </tr>
            @endif
        </table>
    </div>
    
    <div class="content">
        {{ $regulation->content }}
    </div>
    
    <script>
        // Auto-abre a janela de impressão ao carregar (opcional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>

