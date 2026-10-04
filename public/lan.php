<?php

require __DIR__.'/landing-url.php';

$groupUrl = 'https://chat.whatsapp.com/SEU-LINK-AQUI';

$gains = [
    'Aviso de data e horário da próxima live',
    'O link de acesso, sem precisar procurar depois',
    'O que será demonstrado, antes de entrar',
];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SindCON — Entre no grupo</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root{--navy:#07111f;--ink:#eef6ff;--muted:#9aafc6;--line:rgba(255,255,255,.12)}
  *{box-sizing:border-box}
  body{
    margin:0;min-height:100vh;color:var(--ink);
    font-family:Manrope,ui-sans-serif,system-ui,Segoe UI,sans-serif;
    background:
      radial-gradient(820px 420px at 50% -8%, rgba(26,140,255,.3), transparent 62%),
      radial-gradient(640px 380px at 100% 100%, rgba(33,211,123,.12), transparent 50%),
      var(--navy);
  }
  .page{width:min(760px,calc(100% - 36px));margin:0 auto;padding:36px 0 48px;text-align:center}
  .brand{display:flex;justify-content:center}
  .brand img{height:84px;width:auto;mix-blend-mode:screen}
  .badge{
    display:inline-flex;align-items:center;gap:8px;margin-top:8px;
    padding:8px 14px;border-radius:999px;border:1px solid rgba(33,211,123,.28);
    background:rgba(33,211,123,.08);color:#8af0c0;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;
  }
  .pulse{width:8px;height:8px;border-radius:50%;background:#2ee59a;box-shadow:0 0 12px #2ee59a}
  h1{margin:18px 0 12px;font-size:clamp(40px,6vw,62px);line-height:.96;letter-spacing:-.055em}
  h1 span{background:linear-gradient(100deg,#fff,#7ee7ff);-webkit-background-clip:text;background-clip:text;color:transparent}
  .sub{margin:0 auto;max-width:540px;color:var(--muted);font-size:17px;line-height:1.6}
  .card{
    margin-top:28px;text-align:left;padding:22px;border-radius:28px;
    background:linear-gradient(165deg,rgba(16,40,66,.94),rgba(7,18,32,.94));
    border:1px solid rgba(130,196,255,.18);
    box-shadow:0 28px 70px rgba(0,0,0,.32);
  }
  .inside{display:flex;gap:14px;align-items:center;padding:14px;border-radius:18px;background:rgba(255,255,255,.04);border:1px solid var(--line)}
  .wa{width:48px;height:48px;flex:none;border-radius:16px;display:grid;place-items:center;background:rgba(33,211,123,.14);color:#67f0b4;font-weight:800}
  .inside strong{display:block;font-size:15px}
  .inside small{color:#8ea6be;font-size:12px}
  .gains{margin:16px 0 18px;padding:0;list-style:none}
  .gains li{padding:8px 0 8px 22px;position:relative;color:#d5e4f2;font-size:14px}
  .gains li:before{content:"";position:absolute;left:0;top:14px;width:8px;height:8px;border-radius:50%;background:linear-gradient(135deg,#1a8cff,#2ad4ee)}
  .cta{
    display:block;text-align:center;text-decoration:none;border-radius:16px;padding:18px 20px;
    color:#042014;font-weight:800;font-size:16px;
    background:linear-gradient(100deg,#2ee08a,#14c56d);
    box-shadow:0 16px 34px rgba(33,211,123,.25);
  }
  .cta:hover{filter:brightness(1.05)}
  .note{margin:12px 0 0;text-align:center;color:#73889f;font-size:12px}
  .steps{display:flex;justify-content:center;gap:8px;flex-wrap:wrap;margin-top:22px}
  .step{padding:8px 12px;border-radius:999px;border:1px solid var(--line);font-size:12px;color:#c5d4e4;background:rgba(255,255,255,.03)}
  .step.on{border-color:rgba(46,224,138,.4);color:#b9f6d8}
  footer{margin-top:26px;color:#5d738a;font-size:12px}
  @media(max-width:600px){
    .brand img{height:68px}
    h1{font-size:38px}
    .card{padding:16px}
  }
</style>
</head>
<body>
<div class="page">
  <a class="brand" href="cad.php"><img src="<?= htmlspecialchars(landing_asset('images/logo_sindcon.png'), ENT_QUOTES, 'UTF-8') ?>" alt="SindCON — Gestão Condominial Inteligente"></a>
  <div class="badge"><span class="pulse"></span> Próximo passo liberado</div>
  <h1>Agora falta só <span>entrar no grupo.</span></h1>
  <p class="sub">É por lá que saem os avisos das próximas lives e demonstrações. Quem está no grupo recebe a data antes de todo mundo.</p>

  <section class="card">
    <div class="inside">
      <div class="wa">WA</div>
      <div>
        <strong>Lives e demonstrações SindCON</strong>
        <small>Grupo no WhatsApp · avisos, datas e acessos</small>
      </div>
    </div>
    <ul class="gains">
      <?php foreach ($gains as $gain): ?>
      <li><?= htmlspecialchars($gain, ENT_QUOTES, 'UTF-8') ?></li>
      <?php endforeach; ?>
    </ul>
    <a class="cta" href="<?= htmlspecialchars($groupUrl, ENT_QUOTES, 'UTF-8') ?>">Entrar no grupo agora</a>
    <p class="note">O botão abre o WhatsApp. Você entra quando quiser e sai quando quiser.</p>
  </section>

  <div class="steps">
    <div class="step on">1 · Cadastro feito</div>
    <div class="step on">2 · Entrar no grupo</div>
    <div class="step">3 · Assistir a próxima live</div>
  </div>
  <footer>Gestão condominial inteligente</footer>
</div>
</body>
</html>
