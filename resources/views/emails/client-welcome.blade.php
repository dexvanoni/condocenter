<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo ao SindCON</title>
</head>
<body style="margin:0;padding:0;background-color:#eef2fb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef2fb;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background-color:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td align="center" style="background-color:#0a1b67;background-image:linear-gradient(135deg,#0a1b67 0%,#3866d2 100%);padding:36px 28px 28px;">
                            <img src="{{ $logoUrl }}" alt="SindCON — Gestão condominial inteligente" width="200" style="display:block;margin:0 auto;width:200px;max-width:200px;height:auto;border:0;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 32px 8px;">
                            <p style="margin:0 0 8px;font-size:13px;letter-spacing:0.08em;text-transform:uppercase;color:#3866d2;font-weight:bold;">Boas-vindas</p>
                            <h1 style="margin:0 0 16px;font-size:26px;line-height:1.25;color:#0a1b67;">Olá, {{ $firstName }}. Que bom ter você aqui.</h1>
                            @if($audience === \App\Mail\ClientWelcomeMail::AUDIENCE_ADMINISTRADORA)
                                <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#374151;">
                                    A <strong>{{ $placeName }}</strong> acaba de chegar ao <strong>SindCON</strong>.
                                    A partir de agora, os condomínios da administradora ficam no mesmo painel — com a rotina organizada e o acesso pronto para o time.
                                </p>
                            @else
                                <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#374151;">
                                    O <strong>{{ $placeName }}</strong> ganhou um novo endereço digital: o <strong>SindCON</strong>.
                                    Você entra como síndico e passa a cuidar do condomínio com mais clareza, do financeiro ao dia a dia dos moradores.
                                </p>
                            @endif
                            <p style="margin:0 0 20px;font-size:16px;line-height:1.6;color:#374151;">
                                Falta só um passo: criar a sua senha. O link abaixo é pessoal e abre direto na tela de acesso.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:0 32px 8px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="background-color:#3866d2;border-radius:10px;">
                                        <a href="{{ $setupUrl }}" style="display:inline-block;padding:14px 28px;color:#ffffff;text-decoration:none;font-size:16px;font-weight:bold;">Criar minha senha e entrar</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f7ff;border-radius:12px;border-left:4px solid #3866d2;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <p style="margin:0 0 10px;font-size:14px;font-weight:bold;color:#0a1b67;">Como começar</p>
                                        <p style="margin:0 0 6px;font-size:14px;line-height:1.5;color:#374151;">1. Toque no botão e escolha uma senha com pelo menos 8 caracteres.</p>
                                        <p style="margin:0 0 6px;font-size:14px;line-height:1.5;color:#374151;">2. Entre com o e-mail <strong>{{ $user->email }}</strong>.</p>
                                        <p style="margin:0;font-size:14px;line-height:1.5;color:#374151;">3. Explore o painel. O SindCON já está no seu nome.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 32px 28px;">
                            <p style="margin:0 0 8px;font-size:13px;line-height:1.5;color:#6b7280;">
                                Este convite vale por <strong>{{ $expireMinutes }} minutos</strong>. Se o prazo passar, peça um novo link à equipe SindCON.
                            </p>
                            <p style="margin:0;font-size:13px;line-height:1.5;color:#6b7280;">
                                Se você não esperava este e-mail, pode ignorá-lo. Nenhuma senha é criada sem o seu clique.
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
