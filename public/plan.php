<?php

require __DIR__.'/landing-url.php';

$plans = [
    [
        'name' => 'Start',
        'units' => 'Até 20 unidades',
        'description' => 'Para quem está organizando a gestão de um condomínio menor.',
        'price' => '97',
        'url' => 'https://pay.hotmart.com/M107632595B?off=6jcyj60b',
        'featured' => false,
    ],
    [
        'name' => 'Essencial',
        'units' => 'Até 50 unidades',
        'description' => 'O ponto de partida da maioria dos condomínios residenciais.',
        'price' => '197',
        'url' => 'https://pay.hotmart.com/M107632595B?off=h5b3x578',
        'featured' => false,
    ],
    [
        'name' => 'Pro',
        'units' => 'Até 120 unidades',
        'description' => 'Mais folga para uma gestão completa, sem trocar de plano tão cedo.',
        'price' => '297',
        'url' => 'https://pay.hotmart.com/M107632595B?off=g2uoj55p',
        'featured' => true,
    ],
    [
        'name' => 'Master',
        'units' => 'Até 250 unidades',
        'description' => 'Para condomínios de maior porte e rotina mais intensa.',
        'price' => '497',
        'url' => 'https://pay.hotmart.com/M107632595B?off=oasnnj9o',
        'featured' => false,
    ],
    [
        'name' => 'Premium',
        'units' => 'Até 400 unidades',
        'description' => 'Capacidade para grandes condomínios e várias frentes ao mesmo tempo.',
        'price' => '697',
        'url' => 'https://pay.hotmart.com/M107632595B?off=xfedays0',
        'featured' => false,
    ],
    [
        'name' => 'Enterprise',
        'units' => 'Até 700 unidades',
        'description' => 'O maior plano, para operações que não podem ficar no limite.',
        'price' => '1.047',
        'url' => 'https://pay.hotmart.com/M107632595B?off=95o7u178',
        'featured' => false,
    ],
];

$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SindCON — Escolha seu plano</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root{--navy:#06101c;--ink:#f4f8ff;--muted:#9aafc6;--line:rgba(150,190,230,.22)}
  *{box-sizing:border-box}
  body{
    margin:0;color:var(--ink);
    font-family:Manrope,ui-sans-serif,system-ui,Segoe UI,sans-serif;
    background:
      radial-gradient(900px 380px at 50% -10%, rgba(26,140,255,.28), transparent 60%),
      var(--navy);
  }
  .page{width:min(1100px,calc(100% - 36px));margin:0 auto;padding:28px 0 48px}
  .header{text-align:center}
  .brand{display:flex;justify-content:center}
  .brand img{height:86px;width:auto;mix-blend-mode:screen}
  h1{margin:8px 0 10px;font-size:clamp(34px,4.5vw,52px);line-height:1.02;letter-spacing:-.045em}
  .subtitle{max-width:620px;margin:0 auto 28px;color:var(--muted);font-size:16px;line-height:1.55}
  .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
  .card{
    position:relative;display:flex;flex-direction:column;min-height:280px;
    padding:22px 20px 18px;border-radius:22px;border:1px solid var(--line);
    background:linear-gradient(165deg,#102646,#0a1730);
    box-shadow:0 18px 40px rgba(0,0,0,.22);
  }
  .card.featured{border-color:rgba(42,212,238,.55);background:linear-gradient(165deg,#12315f,#0b1c38);transform:translateY(-6px)}
  .tag{
    position:absolute;top:14px;right:14px;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;
    color:#042014;background:linear-gradient(100deg,#7ee7ff,#2ee08a);border-radius:999px;padding:5px 8px;
  }
  .units{color:#7fd8ff;font-size:12px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
  .name{margin-top:8px;font-size:26px;font-weight:800;letter-spacing:-.03em}
  .card p{color:var(--muted);font-size:14px;line-height:1.45;min-height:44px;margin:12px 0 8px}
  .price{font-size:34px;font-weight:800;letter-spacing:-.04em;margin-top:auto}
  .price small{font-size:13px;font-weight:600;color:var(--muted);margin-left:4px}
  .price .currency{font-size:16px;margin-right:2px}
  .btn{
    display:block;margin-top:16px;text-align:center;text-decoration:none;border-radius:12px;padding:13px 10px;
    background:#f4f8ff;color:#07111f;font-weight:800;font-size:13px;
  }
  .featured .btn{background:linear-gradient(100deg,#1a8cff,#18d2c8);color:#fff}
  .btn:hover{filter:brightness(1.06)}
  .footer{text-align:center;margin-top:22px;color:#7487a3;font-size:12px;line-height:1.5}
  @media(max-width:900px){.grid{grid-template-columns:repeat(2,1fr)}.card.featured{transform:none}}
  @media(max-width:620px){
    .page{width:min(100% - 24px,520px)}
    .grid{grid-template-columns:1fr}
    .brand img{height:68px}
    .card p{min-height:0}
  }
</style>
</head>
<body>
<main class="page">
  <header class="header">
    <a class="brand" href="cad.php"><img src="<?= htmlspecialchars(landing_asset('images/logo_sindcon.png'), ENT_QUOTES, 'UTF-8') ?>" alt="SindCON — Gestão Condominial Inteligente"></a>
    <h1>Escolha pelo tamanho<br>do condomínio</h1>
    <p class="subtitle">O plano acompanha o número de unidades. O checkout abre na hora, já no valor mensal correspondente.</p>
  </header>
  <section class="grid">
    <?php foreach ($plans as $plan): ?>
    <article class="card<?= $plan['featured'] ? ' featured' : '' ?>">
      <?php if ($plan['featured']): ?><div class="tag">Mais escolhido</div><?php endif; ?>
      <div class="units"><?= $e($plan['units']) ?></div>
      <div class="name"><?= $e($plan['name']) ?></div>
      <p><?= $e($plan['description']) ?></p>
      <div class="price"><span class="currency">R$</span><?= $e($plan['price']) ?><small>/mês</small></div>
      <a class="btn" href="<?= $e($plan['url']) ?>" target="_blank" rel="noopener noreferrer">Escolher este plano</a>
    </article>
    <?php endforeach; ?>
  </section>
  <p class="footer">Cobrança mensal. Ao escolher um plano, você segue para o checkout desse valor.</p>
</main>
</body>
</html>
