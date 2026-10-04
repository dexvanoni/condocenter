<?php

require __DIR__.'/landing-url.php';

$groupPage = 'lan.php';

$posts = [
    [
        'initial' => 'S',
        'role' => 'Síndico(a)',
        'text' => 'Depois que começamos a organizar a rotina pelo SindCON, ficou muito mais fácil visualizar o que precisa ser resolvido e acompanhar as demandas do condomínio.',
    ],
    [
        'initial' => 'A',
        'role' => 'Administradora',
        'text' => 'O que mais gostei foi ter diferentes partes da gestão reunidas em um só lugar. A comunicação com os moradores também ficou muito mais organizada.',
        'note' => 'Substitua este texto por um depoimento autorizado e identificado antes de publicar.',
    ],
    [
        'initial' => 'C',
        'role' => 'Síndico(a)',
        'text' => 'A sensação é de ter mais controle da operação. Consigo acompanhar as informações sem depender de várias ferramentas diferentes.',
    ],
];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>O que estão falando do SindCON</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root{--bg:#f4f7fb;--ink:#142033;--muted:#66788e;--line:#e4ebf3}
  *{box-sizing:border-box}
  body{margin:0;background:var(--bg);color:var(--ink);font-family:Manrope,ui-sans-serif,system-ui,Segoe UI,sans-serif}
  .top{
    background:
      radial-gradient(700px 280px at 80% 0%, rgba(26,140,255,.28), transparent 60%),
      linear-gradient(160deg,#07111f,#0c2340);
    color:#fff;padding:22px 18px 92px;
  }
  .nav{width:min(860px,100%);margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:12px}
  .brand img{height:72px;width:auto;display:block;mix-blend-mode:screen}
  .live{font-size:12px;font-weight:700;letter-spacing:.04em;border:1px solid rgba(255,255,255,.16);border-radius:999px;padding:8px 12px;color:#c7def6}
  .hero{width:min(760px,100%);margin:42px auto 0;text-align:center}
  .kicker{margin:0;color:#7fd8ff;font-size:12px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
  h1{margin:12px 0 12px;font-size:clamp(36px,6vw,58px);line-height:.98;letter-spacing:-.05em}
  h1 span{background:linear-gradient(100deg,#fff,#7ee7ff);-webkit-background-clip:text;background-clip:text;color:transparent}
  .hero p{margin:0 auto;max-width:560px;color:#b7c9dc;font-size:17px;line-height:1.6}
  .feed{width:min(760px,calc(100% - 28px));margin:-52px auto 64px}
  .post{
    background:#fff;border:1px solid var(--line);border-radius:22px;padding:22px 22px 18px;margin:0 0 14px;
    box-shadow:0 16px 40px rgba(16,36,64,.06);
  }
  .head{display:flex;gap:12px;align-items:center;margin-bottom:12px}
  .avatar{
    width:44px;height:44px;border-radius:50%;display:grid;place-items:center;font-weight:800;color:#0b63c9;
    background:linear-gradient(135deg,#e7f3ff,#c9e4ff);
  }
  .name{font-weight:800;font-size:14px}
  .role{color:var(--muted);font-size:12px;margin-top:2px}
  .body{margin:0;font-size:17px;line-height:1.6;letter-spacing:-.02em}
  .note{margin:14px 0 0;padding:12px 14px;border-radius:12px;background:#f6f9fc;color:#5d7086;font-size:13px;border-left:3px solid #1a8cff}
  .cta{
    margin-top:8px;text-align:center;background:#07111f;color:#fff;border-radius:24px;padding:32px 22px;
  }
  .cta h2{margin:0 0 8px;font-size:28px;letter-spacing:-.03em}
  .cta p{margin:0 0 18px;color:#b7c9dc}
  .button{
    display:inline-block;text-decoration:none;font-weight:800;color:#042014;
    background:linear-gradient(100deg,#2ee08a,#14c56d);padding:15px 22px;border-radius:14px;
  }
  .disclaimer{text-align:center;color:#8b98aa;font-size:12px;line-height:1.5;max-width:560px;margin:18px auto 0}
  @media(max-width:640px){
    .live{display:none}
    .brand img{height:58px}
    .top{padding-bottom:78px}
    .hero{margin-top:28px}
  }
</style>
</head>
<body>
<header class="top">
  <nav class="nav">
    <a class="brand" href="cad.php"><img src="<?= htmlspecialchars(landing_asset('images/logo_sindcon.png'), ENT_QUOTES, 'UTF-8') ?>" alt="SindCON — Gestão Condominial Inteligente"></a>
    <div class="live">Quem já usa</div>
  </nav>
  <section class="hero">
    <p class="kicker">Experiências de quem gere</p>
    <h1>Quando a rotina fica clara,<br><span>o condomínio sente.</span></h1>
    <p>Síndicos e administradoras descrevem o que mudou depois de reunir a gestão em um só lugar.</p>
  </section>
</header>
<main class="feed">
  <?php foreach ($posts as $post): ?>
  <article class="post">
    <div class="head">
      <div class="avatar"><?= htmlspecialchars($post['initial'], ENT_QUOTES, 'UTF-8') ?></div>
      <div>
        <div class="name">Cliente SindCON</div>
        <div class="role"><?= htmlspecialchars($post['role'], ENT_QUOTES, 'UTF-8') ?></div>
      </div>
    </div>
    <p class="body">“<?= htmlspecialchars($post['text'], ENT_QUOTES, 'UTF-8') ?>”</p>
    <?php if (!empty($post['note'])): ?>
    <p class="note"><?= htmlspecialchars($post['note'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
  </article>
  <?php endforeach; ?>

  <section class="cta">
    <h2>Quer ver isso funcionando?</h2>
    <p>Entre no grupo e acompanhe a próxima live, com a tela aberta.</p>
    <a class="button" href="<?= htmlspecialchars($groupPage, ENT_QUOTES, 'UTF-8') ?>">Entrar no grupo</a>
  </section>
  <p class="disclaimer">Os textos acima são modelos de layout. Antes de publicar, troque por depoimentos reais, autorizados e identificados.</p>
</main>
</body>
</html>
