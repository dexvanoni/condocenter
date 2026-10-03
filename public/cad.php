<?php

$profiles = [
    'Síndico(a)',
    'Subsíndico(a)',
    'Administrador(a)',
    'Administradora de condomínios',
    'Outro',
];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SindCON — Gestão Condominial Inteligente</title>
<style>
  :root{
    --navy:#071426;
    --navy2:#0b1e35;
    --blue:#1687ff;
    --cyan:#22d3ee;
    --white:#f8fbff;
    --muted:#9db0c7;
    --line:rgba(255,255,255,.11);
  }
  *{box-sizing:border-box}
  body{
    margin:0;
    font-family:Inter,ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
    background:
      radial-gradient(circle at 82% 18%,rgba(22,135,255,.20),transparent 30%),
      radial-gradient(circle at 15% 85%,rgba(34,211,238,.10),transparent 28%),
      var(--navy);
    color:var(--white);
    min-height:100vh;
  }
  .page{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:28px 18px;
  }
  .shell{
    width:min(1080px,100%);
    display:grid;
    grid-template-columns:1.05fr .85fr;
    gap:48px;
    align-items:center;
  }
  .brand{
    display:inline-flex;
    align-items:center;
    gap:10px;
    font-weight:800;
    letter-spacing:-.03em;
    font-size:22px;
    margin-bottom:42px;
  }
  .brand-mark{
    width:38px;height:38px;border-radius:12px;
    background:linear-gradient(135deg,var(--blue),var(--cyan));
    display:grid;place-items:center;
    box-shadow:0 8px 30px rgba(22,135,255,.28);
    color:#fff;font-weight:900;
  }
  .eyebrow{
    color:#63c7ff;
    font-size:13px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.12em;
    margin-bottom:14px;
  }
  h1{
    font-size:clamp(38px,5vw,62px);
    line-height:.98;
    letter-spacing:-.055em;
    margin:0 0 20px;
    max-width:650px;
  }
  h1 span{
    background:linear-gradient(90deg,#fff,#6fd9ff);
    -webkit-background-clip:text;
    color:transparent;
  }
  .lead{
    color:var(--muted);
    font-size:18px;
    line-height:1.6;
    max-width:580px;
    margin:0 0 26px;
  }
  .benefits{
    display:flex;flex-wrap:wrap;gap:10px;
    margin-top:24px;
  }
  .benefit{
    border:1px solid var(--line);
    background:rgba(255,255,255,.035);
    border-radius:999px;
    padding:9px 13px;
    color:#d9e6f5;
    font-size:13px;
  }
  .card{
    background:linear-gradient(145deg,rgba(18,40,67,.94),rgba(8,23,41,.96));
    border:1px solid rgba(120,190,255,.18);
    border-radius:28px;
    padding:30px;
    box-shadow:0 28px 80px rgba(0,0,0,.30), inset 0 1px 0 rgba(255,255,255,.05);
    backdrop-filter:blur(18px);
  }
  .card h2{
    margin:0 0 8px;
    font-size:27px;
    letter-spacing:-.03em;
  }
  .card p{
    margin:0 0 24px;
    color:var(--muted);
    line-height:1.5;
    font-size:14px;
  }
  label{
    display:block;
    font-size:12px;
    color:#cbd8e8;
    margin:0 0 7px;
    font-weight:700;
  }
  input,select{
    width:100%;
    border:1px solid rgba(255,255,255,.12);
    background:rgba(4,14,27,.65);
    color:#fff;
    border-radius:13px;
    padding:14px 15px;
    outline:none;
    margin-bottom:16px;
    font-size:14px;
  }
  input:focus,select:focus{border-color:rgba(34,211,238,.65);box-shadow:0 0 0 3px rgba(34,211,238,.08)}
  button{
    width:100%;
    border:0;
    border-radius:14px;
    padding:15px;
    color:white;
    font-size:15px;
    font-weight:800;
    cursor:pointer;
    background:linear-gradient(100deg,#0878f9,#18b7f2);
    box-shadow:0 12px 28px rgba(22,135,255,.24);
    transition:.2s;
  }
  button:hover{transform:translateY(-1px);filter:brightness(1.06)}
  .privacy{
    text-align:center;
    color:#71859d;
    font-size:11px;
    line-height:1.45;
    margin:14px 8px 0;
  }
  .micro{
    display:flex;align-items:center;justify-content:center;gap:7px;
    margin-top:18px;color:#91a6bc;font-size:12px;
  }
  .dot{width:7px;height:7px;border-radius:50%;background:#35d39a;box-shadow:0 0 10px #35d39a}
  .success{display:none;text-align:center;padding:18px 0}
  .success .icon{font-size:38px;margin-bottom:8px}
  @media(max-width:800px){
    .page{padding:24px 16px}
    .shell{grid-template-columns:1fr;gap:28px}
    .brand{margin-bottom:28px}
    h1{font-size:42px}
    .lead{font-size:16px}
    .card{padding:24px;border-radius:22px}
  }
</style>
</head>
<body>
<div class="page">
  <main class="shell">
    <section>
      <div class="brand"><div class="brand-mark">S</div> SindCON</div>
      <div class="eyebrow">Gestão Condominial Inteligente</div>
      <h1>Seu condomínio pode ser <span>mais simples.</span></h1>
      <p class="lead">
        Cadastre-se para receber o acesso às próximas lives e demonstrações do SindCON
        e veja, na prática, como transformar a rotina da gestão.
      </p>
      <div class="benefits">
        <div class="benefit">✓ Demonstração prática</div>
        <div class="benefit">✓ Casos reais de condomínio</div>
        <div class="benefit">✓ Sem compromisso</div>
      </div>
    </section>

    <section class="card">
      <div id="formArea">
        <h2>Quero participar</h2>
        <p>Deixe seus dados e receba o próximo passo para entrar no grupo de lives e demonstrações.</p>

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

          <button type="submit">Continuar para o grupo →</button>
        </form>
        <div class="privacy">Seus dados serão usados para contato e envio de informações relacionadas às lives e demonstrações do SindCON.</div>
        <div class="micro"><span class="dot"></span> Próximo passo: acesso ao grupo</div>
      </div>

      <div class="success" id="success">
        <div class="icon">✓</div>
        <h2>Cadastro recebido!</h2>
        <p>Agora você será direcionado para a página de acesso ao grupo de lives e demonstrações.</p>
        <button onclick="window.location.href='lan.php'">Entrar no grupo →</button>
      </div>
    </section>
  </main>
</div>

<script>
document.getElementById('leadForm').addEventListener('submit', function(e){
  e.preventDefault();
  document.getElementById('formArea').style.display='none';
  document.getElementById('success').style.display='block';
});
</script>
</body>
</html>
