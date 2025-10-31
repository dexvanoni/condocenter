<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regimento Interno - {{ $regulation->condominium->name ?? 'Condomínio' }}</title>
    <style>
        @page {
            margin: 2cm;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #0d6efd;
            font-size: 20pt;
            margin: 0 0 10px 0;
        }
        
        .header h2 {
            font-size: 14pt;
            color: #666;
            font-weight: normal;
            margin: 5px 0;
        }
        
        .metadata {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 30px;
            border-left: 4px solid #0d6efd;
        }
        
        .metadata p {
            margin: 5px 0;
            font-size: 10pt;
        }
        
        .metadata strong {
            color: #0d6efd;
        }
        
        .content {
            text-align: justify;
            white-space: pre-wrap;
            font-size: 11pt;
            line-height: 1.8;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9pt;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REGIMENTO INTERNO</h1>
        <h2>{{ $regulation->condominium->name ?? 'Condomínio' }}</h2>
    </div>
    
    <div class="metadata">
        <p><strong>Versão:</strong> {{ $regulation->version }}</p>
        @if($regulation->assembly_date)
        <p><strong>Data de Aprovação:</strong> {{ $regulation->formatted_assembly_date }}</p>
        @endif
        @if($regulation->assembly_details)
        <p><strong>Assembleia:</strong> {{ $regulation->assembly_details }}</p>
        @endif
        <p><strong>Última Atualização:</strong> {{ $regulation->updated_at->format('d/m/Y H:i') }}</p>
        @if($regulation->updatedBy)
        <p><strong>Atualizado por:</strong> {{ $regulation->updatedBy->name }}</p>
        @endif
    </div>
    
    <div class="content">
        {{ $regulation->content }}
    </div>
    
    <div class="footer">
        <p>Este documento foi gerado eletronicamente em {{ now()->format('d/m/Y H:i') }}</p>
        <p>{{ $regulation->condominium->name ?? 'Condomínio' }} - Versão {{ $regulation->version }}</p>
    </div>
</body>
</html>

