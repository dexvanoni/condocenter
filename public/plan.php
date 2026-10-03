<?php

$plans = [
    [
        'icon' => '🟢',
        'name' => 'PLANO START',
        'units' => 'ATÉ 20 UNIDADES',
        'description' => 'Para condomínios de até 20 unidades.',
        'price' => 'R$ 97',
        'url' => 'https://pay.hotmart.com/M107632595B?off=6jcyj60b',
    ],
    [
        'icon' => '🔵',
        'name' => 'PLANO ESSENCIAL',
        'units' => 'ATÉ 50 UNIDADES',
        'description' => 'Para condomínios de até 50 unidades.',
        'price' => 'R$ 197',
        'url' => 'https://pay.hotmart.com/M107632595B?off=h5b3x578',
    ],
    [
        'icon' => '🔥',
        'name' => 'PLANO PRO',
        'units' => 'ATÉ 120 UNIDADES',
        'description' => 'Mais capacidade para uma gestão completa.',
        'price' => 'R$ 297',
        'url' => 'https://pay.hotmart.com/M107632595B?off=g2uoj55p',
    ],
    [
        'icon' => '💎',
        'name' => 'PLANO MASTER',
        'units' => 'ATÉ 250 UNIDADES',
        'description' => 'Para condomínios de maior porte.',
        'price' => 'R$ 497',
        'url' => 'https://pay.hotmart.com/M107632595B?off=oasnnj9o',
    ],
    [
        'icon' => '👑',
        'name' => 'PLANO PREMIUM',
        'units' => 'ATÉ 400 UNIDADES',
        'description' => 'Para grandes condomínios.',
        'price' => 'R$ 697',
        'url' => 'https://pay.hotmart.com/M107632595B?off=xfedays0',
    ],
    [
        'icon' => '🏆',
        'name' => 'PLANO ENTERPRISE',
        'units' => 'ATÉ 700 UNIDADES',
        'description' => 'Para condomínios de até 700 unidades.',
        'price' => 'R$ 1.047',
        'url' => 'https://pay.hotmart.com/M107632595B?off=95o7u178',
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
<style>
*{box-sizing:border-box}
body{margin:0;background:#040b20;color:#f8fafc;font-family:Arial,Helvetica,sans-serif}
.page{width:min(1160px,calc(100% - 32px));margin:0 auto;padding:34px 0 42px}
.header{text-align:center;margin-bottom:30px}
.logo{width:290px;max-height:95px;object-fit:contain;margin-bottom:10px}
.kicker{font-size:12px;font-weight:800;letter-spacing:2.2px;color:#19d3df}
h1{font-size:42px;line-height:1.05;margin:9px 0 10px;letter-spacing:-1px}
.subtitle{max-width:650px;margin:auto;color:#aebbd5;font-size:16px;line-height:1.5}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:17px}
.card{position:relative;min-height:285px;padding:23px 20px 20px;border:1px solid #27466f;border-radius:20px;background:linear-gradient(160deg,#102754 0%,#091733 100%);box-shadow:0 16px 45px rgba(0,0,0,.28);overflow:hidden}
.card:hover{transform:translateY(-3px);border-color:#16cddd}
.card{transition:.2s ease}
.bar{position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#1970f5,#16cddd)}
.icon{font-size:26px;margin-bottom:8px}
.name{font-size:17px;font-weight:900;letter-spacing:.3px}
.units{color:#16cddd;font-size:11px;font-weight:900;letter-spacing:1px;margin-top:5px}
.card p{color:#aebbd5;font-size:13px;line-height:1.4;min-height:38px;margin:14px 0 10px}
.price{font-size:29px;font-weight:900;letter-spacing:-.5px;margin-bottom:15px}
.price small{font-size:12px;color:#aebbd5;font-weight:400;margin-left:5px}
.btn{display:block;text-align:center;text-decoration:none;background:#22c55e;color:#04120a;font-weight:900;font-size:12px;letter-spacing:.3px;padding:14px 8px;border-radius:12px;box-shadow:0 9px 24px rgba(34,197,94,.22)}
.btn:hover{filter:brightness(1.08);box-shadow:0 11px 30px rgba(34,197,94,.32)}
.btn span{font-size:15px}
.footer{text-align:center;margin-top:25px;color:#7283a5;font-size:11px;line-height:1.5}
@media(max-width:880px){.grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:580px){.page{width:min(100% - 24px,520px);padding-top:24px}h1{font-size:34px}.grid{grid-template-columns:1fr}.logo{width:250px}}
</style>
</head>
<body>
<main class="page">
<header class="header">
<img class="logo" src="3f6b31c9-058b-4bd5-ad57-0c61fce55abe.png" alt="SindCON — Gestão Condominial Inteligente">
<div class="kicker">GESTÃO CONDOMINIAL INTELIGENTE</div>
<h1>Escolha o plano ideal<br>para o seu condomínio</h1>
<p class="subtitle">Encontre a opção de acordo com o número de unidades e clique no plano desejado para avançar diretamente para a contratação.</p>
</header>
<section class="grid">
<?php foreach ($plans as $plan): ?>
    <article class="card">
      <div class="bar"></div>
      <div class="icon"><?= $e($plan['icon']) ?></div>
      <div class="name"><?= $e($plan['name']) ?></div>
      <div class="units"><?= $e($plan['units']) ?></div>
      <p><?= $e($plan['description']) ?></p>
      <div class="price"><?= $e($plan['price']) ?><small>/mês</small></div>
      <a class="btn" href="<?= $e($plan['url']) ?>" target="_blank" rel="noopener noreferrer">ESCOLHER ESTE PLANO <span>→</span></a>
    </article>
<?php endforeach; ?>
</section>
<div class="footer">Planos com cobrança mensal. Ao clicar em “Escolher este plano”, você será direcionado ao checkout correspondente.</div>
</main>
</body>
</html>
