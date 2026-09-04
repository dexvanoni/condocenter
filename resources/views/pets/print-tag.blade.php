@php
    $unitLabel = $pet->unit?->full_identifier ?? '—';
    $phone = $pet->owner?->phone ?? '—';
    $qrUrl = route('pets.show-qr', $pet->qr_code);
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiqueta — {{ $pet->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 24px;
        }

        /* Etiqueta 3cm × 4cm */
        .pet-tag {
            width: 3cm;
            height: 4cm;
            background: #fff;
            border: 0.5pt solid #1e293b;
            border-radius: 2mm;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            overflow: hidden;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .pet-tag__qr {
            flex: 1 1 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5mm 1mm 1mm;
            min-height: 0;
        }

        .pet-tag__qr svg {
            width: 2.55cm;
            height: 2.55cm;
            display: block;
        }

        .pet-tag__info {
            flex: 0 0 auto;
            border-top: 0.4pt solid #cbd5e1;
            padding: 1.2mm 1.5mm 1.5mm;
            text-align: center;
            line-height: 1.15;
        }

        .pet-tag__unit {
            font-size: 6.5pt;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            margin-bottom: 0.6mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pet-tag__phone {
            font-size: 7.5pt;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.01em;
            word-break: break-all;
        }

        .print-toolbar {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 16px 18px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
            max-width: 280px;
            font-size: 14px;
            color: #334155;
        }

        .print-toolbar strong {
            display: block;
            margin-bottom: 6px;
            color: #0f172a;
        }

        .print-toolbar p {
            margin: 0 0 8px;
            font-size: 13px;
            line-height: 1.45;
        }

        .print-toolbar button {
            width: 100%;
            border: none;
            border-radius: 8px;
            padding: 10px 14px;
            background: #2563eb;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }

        .print-toolbar button:hover {
            background: #1d4ed8;
        }

        .print-toolbar .tag-size {
            font-size: 12px;
            color: #64748b;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
                margin: 0;
                display: block;
            }

            .print-toolbar {
                display: none !important;
            }

            .pet-tag {
                margin: 0;
                border: 0.5pt solid #000;
                box-shadow: none;
                border-radius: 0;
            }

            @page {
                size: auto;
                margin: 5mm;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <strong>Etiqueta do pet</strong>
        <p>Imprima em papel adesivo ou papel comum e recorte no tamanho indicado.</p>
        <p class="tag-size">Tamanho: <strong>3 × 4 cm</strong></p>
        <button type="button" onclick="window.print()">Imprimir etiqueta</button>
    </div>

    <div class="pet-tag" aria-label="Etiqueta QR do pet {{ $pet->name }}">
        <div class="pet-tag__qr">
            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(320)->format('svg')->margin(0)->errorCorrection('H')->generate($qrUrl) !!}
        </div>

        <div class="pet-tag__info">
            <div class="pet-tag__unit">Un. {{ $unitLabel }}</div>
            <div class="pet-tag__phone">{{ $phone }}</div>
        </div>
    </div>

    @if(!empty($autoPrint))
    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 300);
        });
    </script>
    @endif
</body>
</html>
