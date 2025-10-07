<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório Financeiro - {{ $period }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #667eea;
            margin: 0;
        }
        .info-box {
            background: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background: #667eea;
            color: white;
            padding: 10px;
            text-align: left;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-success {
            color: #28a745;
        }
        .text-danger {
            color: #dc3545;
        }
        .summary {
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $condominium->name }}</h1>
        <p>CNPJ: {{ $condominium->cnpj }}</p>
        <h2>Relatório Financeiro - {{ $period }}</h2>
        <p>Gerado em: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info-box">
        <strong>Informações do Condomínio:</strong><br>
        Endereço: {{ $condominium->address }}, {{ $condominium->city }}/{{ $condominium->state }}<br>
        Telefone: {{ $condominium->phone }}<br>
        Email: {{ $condominium->email }}
    </div>

    <h3>Resumo do Período</h3>
    <table>
        <tr>
            <th>Descrição</th>
            <th class="text-right">Valor (R$)</th>
        </tr>
        <tr>
            <td><strong>Total de Receitas</strong></td>
            <td class="text-right text-success"><strong>{{ number_format($totalReceitas, 2, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td><strong>Total de Despesas</strong></td>
            <td class="text-right text-danger"><strong>{{ number_format($totalDespesas, 2, ',', '.') }}</strong></td>
        </tr>
        <tr style="background: #f5f5f5;">
            <td><strong>SALDO DO PERÍODO</strong></td>
            <td class="text-right {{ $saldo >= 0 ? 'text-success' : 'text-danger' }}">
                <strong>{{ number_format($saldo, 2, ',', '.') }}</strong>
            </td>
        </tr>
    </table>

    <h3>Detalhamento de Receitas</h3>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Categoria</th>
                <th>Descrição</th>
                <th class="text-right">Valor (R$)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions->where('type', 'income') as $transaction)
            <tr>
                <td>{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                <td>{{ $transaction->category }}</td>
                <td>{{ $transaction->description }}</td>
                <td class="text-right">{{ number_format($transaction->amount, 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Nenhuma receita no período</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <h3>Detalhamento de Despesas</h3>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Categoria</th>
                <th>Descrição</th>
                <th>Comprovante</th>
                <th class="text-right">Valor (R$)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions->where('type', 'expense') as $transaction)
            <tr>
                <td>{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                <td>{{ $transaction->category }}</td>
                <td>{{ $transaction->description }}</td>
                <td class="text-center">
                    {{ $transaction->receipts->count() > 0 ? '✓' : '✗' }}
                </td>
                <td class="text-right">{{ number_format($transaction->amount, 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Nenhuma despesa no período</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <h4>Resumo por Categoria</h4>
        @php
            $byCategory = $transactions->groupBy('category');
        @endphp
        <table>
            <tr>
                <th>Categoria</th>
                <th class="text-right">Receitas</th>
                <th class="text-right">Despesas</th>
                <th class="text-right">Saldo</th>
            </tr>
            @foreach($byCategory as $category => $items)
            @php
                $catReceitas = $items->where('type', 'income')->sum('amount');
                $catDespesas = $items->where('type', 'expense')->sum('amount');
                $catSaldo = $catReceitas - $catDespesas;
            @endphp
            <tr>
                <td>{{ $category }}</td>
                <td class="text-right text-success">{{ number_format($catReceitas, 2, ',', '.') }}</td>
                <td class="text-right text-danger">{{ number_format($catDespesas, 2, ',', '.') }}</td>
                <td class="text-right {{ $catSaldo >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($catSaldo, 2, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="footer">
        <p>Este relatório foi gerado automaticamente pelo sistema CondoManager</p>
        <p>{{ $condominium->name }} - {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>

