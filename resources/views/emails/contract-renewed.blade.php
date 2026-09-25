<!DOCTYPE html>
<html lang="pt-BR">
<body style="font-family: Arial, sans-serif; color: #1f2937;">
    <p>Olá, {{ $recipientName }}.</p>
    <p>O contrato SindCON de <strong>{{ $clientName }}</strong>@if($planName) (plano {{ $planName }})@endif foi renovado automaticamente pelo mesmo período.</p>
    <p>Nova vigência: <strong>{{ $startsAt }}</strong> a <strong>{{ $endsAt }}</strong>.</p>
    <p>As cobranças da recorrência continuam sendo geradas no mesmo ciclo, sem interrupção.</p>
    <p>Equipe SindCON</p>
</body>
</html>
