<?php

require __DIR__.'/landing-url.php';

$profiles = [
    'Síndico(a)',
    'Subsíndico(a)',
    'Administrador(a)',
    'Administradora de condomínios',
    'Outro',
];

$proofs = [
    ['n' => '01', 't' => 'Demonstração ao vivo', 'd' => 'Você vê a rotina real: financeiro, portaria e comunicação no mesmo lugar.'],
    ['n' => '02', 't' => 'Feito para quem gere', 'd' => 'Síndicos, subsíndicos e administradoras acompanham sem jargão técnico.'],
    ['n' => '03', 't' => 'Sem compromisso', 'd' => 'O cadastro só libera o grupo das próximas lives. A decisão fica com você.'],
];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SindCON — Gestão Condominial Inteligente</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root{
    --navy:#07111f;
    --ink:#eef6ff;
    --muted:#9aafc6;
    --line:rgba(255,255,255,.12);
    --blue:#1a8cff;
    --cyan:#2ad4ee;
  }
  *{box-sizing:border-box}
  body{
    margin:0;
    min-height:100vh;
    color:var(--ink);
    font-family:Manrope,ui-sans-serif,system-ui,Segoe UI,sans-serif;
    background:
      radial-gradient(900px 480px at 88% -10%, rgba(26,140,255,.28), transparent 60%),
      radial-gradient(700px 420px at 0% 100%, rgba(42,212,238,.12), transparent 55%),
      var(--navy);
  }
  .page{width:min(1120px,calc(100% - 40px));margin:0 auto;padding:28px 0 56px}
  .brand img{height:78px;width:auto;display:block;mix-blend-mode:screen}
  .shell{display:grid;grid-template-columns:1.15fr .85fr;gap:56px;align-items:center;margin-top:28px}
  .eyebrow{color:#7fd8ff;font-size:12px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;margin:0 0 14px}
  h1{margin:0 0 16px;font-size:clamp(40px,5vw,64px);line-height:.98;letter-spacing:-.055em;font-weight:800}
  h1 span{background:linear-gradient(100deg,#fff 20%,#7ee7ff 80%);-webkit-background-clip:text;background-clip:text;color:transparent}
  .lead{margin:0;max-width:520px;color:var(--muted);font-size:18px;line-height:1.65}
  .proofs{margin:28px 0 0;padding:0;list-style:none;display:grid;gap:14px}
  .proofs li{display:grid;grid-template-columns:42px 1fr;gap:12px;align-items:start}
  .proofs b{display:block;font-size:14px;margin-bottom:2px}
  .proofs span{color:var(--muted);font-size:13px;line-height:1.5}
  .num{color:#7fd8ff;font-size:12px;font-weight:800;letter-spacing:.08em;padding-top:2px}
  .card{
    background:linear-gradient(165deg,rgba(16,36,62,.92),rgba(7,16,30,.94));
    border:1px solid rgba(130,196,255,.2);
    border-radius:28px;
    padding:28px 26px 22px;
    box-shadow:0 30px 80px rgba(0,0,0,.35);
  }
  .card h2{margin:0 0 6px;font-size:28px;letter-spacing:-.04em}
  .card .intro{margin:0 0 22px;color:var(--muted);font-size:14px;line-height:1.55}
  label{display:block;font-size:12px;font-weight:700;color:#d5e4f4;margin:0 0 7px}
  input,select{
    width:100%;border:1px solid var(--line);background:rgba(3,10,20,.55);color:#fff;
    border-radius:14px;padding:14px 15px;margin-bottom:14px;font:inherit;font-size:14px;outline:none;
  }
  select option{color:#102033;background:#fff}
  input:focus,select:focus{border-color:rgba(42,212,238,.7);box-shadow:0 0 0 4px rgba(42,212,238,.12)}
  button{
    width:100%;border:0;border-radius:14px;padding:15px 16px;color:#fff;font:inherit;font-weight:800;font-size:15px;cursor:pointer;
    background:linear-gradient(100deg,#0b7cf2,#18c6ee);
    box-shadow:0 14px 30px rgba(22,135,255,.28);
  }
  button:hover{filter:brightness(1.06)}
  .privacy{text-align:center;color:#7d93ab;font-size:11px;line-height:1.45;margin:12px 4px 0}
  .success{display:none;text-align:center;padding:18px 6px 8px}
  .success .mark{width:54px;height:54px;margin:0 auto 12px;border-radius:50%;display:grid;place-items:center;background:rgba(33,211,123,.14);color:#5eecc0;font-size:24px}
  @media(max-width:860px){
    .page{width:min(100% - 28px,640px);padding-top:18px}
    .shell{grid-template-columns:1fr;gap:28px}
    .brand img{height:64px}
    h1{font-size:40px}
  }
</style>
</head>
<body>
<div class="page">
  <a class="brand" href="cad.php"><img src="<?= htmlspecialchars(landing_asset('images/logo_sindcon.png'), ENT_QUOTES, 'UTF-8') ?>" alt="SindCON — Gestão Condominial Inteligente"></a>
  <main class="shell">
    <section>
      <p class="eyebrow">Próximas lives e demonstrações</p>
      <h1>A gestão do condomínio pode ser <span>mais clara.</span></h1>
      <p class="lead">Deixe seu contato e entre no grupo onde avisamos as próximas apresentações do SindCON. Em poucos minutos você vê como a rotina deixa de ficar espalhada.</p>
      <ul class="proofs">
        <?php foreach ($proofs as $item): ?>
        <li>
          <div class="num"><?= htmlspecialchars($item['n'], ENT_QUOTES, 'UTF-8') ?></div>
          <div>
            <b><?= htmlspecialchars($item['t'], ENT_QUOTES, 'UTF-8') ?></b>
            <span><?= htmlspecialchars($item['d'], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
    </section>

    <section class="card">
      <div id="formArea">
        <h2>Quero participar</h2>
        <p class="intro">Seus dados servem só para o convite das lives. O próximo passo é o grupo.</p>
        <form id="leadForm">
          <label for="name">Seu nome</label>
          <input id="name" name="name" placeholder="Como podemos te chamar?" required>
          <label for="email">Seu melhor e-mail</label>
          <input id="email" name="email" type="email" placeholder="voce@email.com" required>
          <label for="profile">Você atua como</label>
          <select id="profile" name="profile" required>
            <option value="" disabled selected>Selecione uma opção</option>
            <?php foreach ($profiles as $profile): ?>
            <option><?= htmlspecialchars($profile, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit">Continuar para o grupo</button>
        </form>
        <p class="privacy">Usamos estes dados para contato e para enviar informações das lives e demonstrações do SindCON.</p>
      </div>
      <div class="success" id="success">
        <div class="mark">✓</div>
        <h2>Cadastro recebido</h2>
        <p class="intro">O grupo é onde saem data, acesso e o que vai ser mostrado na próxima live.</p>
        <button type="button" onclick="window.location.href='lan.php'">Entrar no grupo</button>
      </div>
    </section>
  </main>
</div>
<script>
document.getElementById('leadForm').addEventListener('submit', function (e) {
  e.preventDefault();
  document.getElementById('formArea').style.display = 'none';
  document.getElementById('success').style.display = 'block';
});
</script>
</body>
</html>
