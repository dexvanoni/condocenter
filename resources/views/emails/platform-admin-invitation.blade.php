<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convite de Administrador — {{ config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#eef2fb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef2fb;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background-color:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td align="center" style="background-color:#0a1b67;background-image:linear-gradient(135deg,#0a1b67 0%,#3866d2 100%);padding:36px 28px 28px;">
                            <img src="{{ $logoUrl }}" alt="SindCON" width="200" style="display:block;margin:0 auto;width:200px;max-width:200px;height:auto;border:0;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 32px 8px;">
                            <p style="margin:0 0 8px;font-size:13px;letter-spacing:0.08em;text-transform:uppercase;color:#3866d2;font-weight:bold;">Convite</p>
                            <h1 style="margin:0 0 16px;font-size:24px;line-height:1.25;color:#0a1b67;">
                                @if($existingUser)
                                    Olá, {{ $firstName }}. Você foi convidado para administrar a plataforma.
                                @else
                                    Você foi convidado para ser Administrador da plataforma SindCON.
                                @endif
                            </h1>
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#374151;">
                                <strong>{{ $invitedByName }}</strong> enviou um convite para o e-mail
                                <strong>{{ $email }}</strong> com o perfil <strong>Administrador da plataforma</strong>.
                            </p>
                            @if($existingUser)
                                <p style="margin:0 0 20px;font-size:16px;line-height:1.6;color:#374151;">
                                    Sua conta já existe. Ao aceitar, o perfil Administrador é adicionado e você passa a acessar o painel SaaS em <strong>/platform</strong>. Os demais perfis (síndico, morador etc.) permanecem.
                                </p>
                            @else
                                <p style="margin:0 0 20px;font-size:16px;line-height:1.6;color:#374151;">
                                    O link abaixo abre o cadastro. Informe seu nome, crie uma senha e entre no painel da plataforma.
                                </p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:0 32px 8px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="background-color:#3866d2;border-radius:10px;">
                                        <a href="{{ $inviteUrl }}" style="display:inline-block;padding:14px 28px;color:#ffffff;text-decoration:none;font-size:16px;font-weight:bold;">
                                            @if($existingUser)
                                                Aceitar convite
                                            @else
                                                Concluir cadastro
                                            @endif
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 32px 28px;">
                            <p style="margin:0 0 8px;font-size:13px;line-height:1.5;color:#6b7280;">
                                Este convite vale por <strong>{{ $expireDays }} dias</strong>. Se o prazo passar, peça um novo link a um administrador da plataforma.
                            </p>
                            <p style="margin:0;font-size:13px;line-height:1.5;color:#6b7280;">
                                Se você não esperava este e-mail, ignore-o. Nada é alterado na sua conta sem o clique no convite.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="background-color:#0a1b67;padding:22px 24px;">
                            <p style="margin:0 0 4px;color:#ffffff;font-size:14px;font-weight:bold;">SindCON</p>
                            <p style="margin:0;color:#dbe4ff;font-size:12px;line-height:1.5;">Gestão condominial inteligente<br><a href="{{ $loginUrl }}" style="color:#dbe4ff;text-decoration:underline;">Acessar o SindCON</a></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
