<?php

$groupUrl = 'https://chat.whatsapp.com/SEU-LINK-AQUI';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SindCON — Entre no Grupo</title>
<style>
:root{
  --bg:#061321; --bg2:#0a1b2e; --text:#f7fbff; --muted:#9eb1c7;
  --blue:#1688ff; --cyan:#27d5f2; --green:#21d37b; --green2:#13b965;
  --card:rgba(14,37,61,.82); --line:rgba(255,255,255,.10);
}
*{box-sizing:border-box}
body{
  margin:0; min-height:100vh; color:var(--text);
  font-family:Inter,ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Arial;
  background:
    radial-gradient(circle at 15% 10%,rgba(22,136,255,.20),transparent 28%),
    radial-gradient(circle at 88% 85%,rgba(33,211,123,.12),transparent 30%),
    linear-gradient(145deg,var(--bg),var(--bg2));
}
.page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:28px 18px}
.wrap{width:min(980px,100%);text-align:center}
.logo{
  display:inline-flex;align-items:center;gap:10px;margin-bottom:42px;
  font-weight:850;font-size:22px;letter-spacing:-.04em;
}
.logoMark{
  width:39px;height:39px;border-radius:12px;display:grid;place-items:center;
  background:linear-gradient(135deg,var(--blue),var(--cyan));
  box-shadow:0 8px 30px rgba(22,136,255,.3);font-weight:900
}
.badge{
  display:inline-flex;align-items:center;gap:8px;padding:8px 13px;
  border:1px solid rgba(33,211,123,.25);border-radius:999px;
  background:rgba(33,211,123,.07);color:#77edaa;font-size:12px;font-weight:800;
  text-transform:uppercase;letter-spacing:.09em
}
.pulse{width:7px;height:7px;background:var(--green);border-radius:50%;box-shadow:0 0 13px var(--green)}
h1{
  margin:18px auto 15px;max-width:760px;font-size:clamp(40px,6vw,70px);
  line-height:.96;letter-spacing:-.06em
}
h1 span{
  background:linear-gradient(90deg,#fff,#7bdfff);-webkit-background-clip:text;color:transparent
}
.sub{
  max-width:610px;margin:0 auto;color:var(--muted);font-size:17px;line-height:1.6
}
.card{
  max-width:650px;margin:34px auto 0;padding:26px;border-radius:28px;
  background:linear-gradient(145deg,rgba(17,43,70,.9),rgba(7,25,43,.88));
  border:1px solid var(--line);box-shadow:0 30px 90px rgba(0,0,0,.32);
  backdrop-filter:blur(16px)
}
.inside{
  display:flex;align-items:center;gap:18px;text-align:left;
  padding:17px;border-radius:19px;background:rgba(255,255,255,.035);
  border:1px solid rgba(255,255,255,.07);margin-bottom:18px
}
.wa{
  width:50px;height:50px;flex:none;border-radius:16px;display:grid;place-items:center;
  background:rgba(33,211,123,.12);color:#5df09d;font-size:25px
}
.inside strong{display:block;font-size:15px;margin-bottom:3px}
.inside small{color:#8fa5bc;font-size:12px}
.cta{
  display:block;width:100%;border:0;border-radius:17px;padding:19px 22px;
  color:#04150d;text-decoration:none;font-size:17px;font-weight:900;
  background:linear-gradient(100deg,#27df82,#16c96e);
  box-shadow:0 14px 34px rgba(33,211,123,.24),inset 0 1px rgba(255,255,255,.35);
  transition:.2s
}
.cta:hover{transform:translateY(-2px);filter:brightness(1.06);box-shadow:0 18px 40px rgba(33,211,123,.32)}
.note{margin:15px 0 0;color:#71879e;font-size:11px}
.steps{
  margin:28px auto 0;display:flex;justify-content:center;gap:9px;flex-wrap:wrap
}
.step{
  padding:8px 12px;border-radius:999px;background:rgba(255,255,255,.035);
  border:1px solid var(--line);font-size:12px;color:#b9c9d9
}
.step b{color:#65e99c;margin-right:5px}
footer{margin-top:30px;color:#526a82;font-size:11px}
@media(max-width:600px){
  .page{padding:22px 15px}
  .logo{margin-bottom:34px}
  h1{font-size:43px}
  .sub{font-size:15px}
  .card{padding:18px;margin-top:27px;border-radius:23px}
  .inside{padding:14px}
}
</style>
</head>
<body>
<div class="page">
  <main class="wrap">
    <div class="logo"><div class="logoMark">S</div> SindCON</div>

    <div class="badge"><span class="pulse"></span> Próximo passo liberado</div>

    <h1>Agora falta só <span>entrar no grupo.</span></h1>

    <p class="sub">
      É por lá que você receberá os avisos das próximas Lives e Demonstrações
      do SindCON e poderá acompanhar tudo em primeira mão.
    </p>

    <section class="card">
      <div class="inside">
        <div class="wa">◉</div>
        <div>
          <strong>Grupo de Lives & Demonstrações SindCON</strong>
          <small>Avisos, datas, acessos e novidades das próximas apresentações.</small>
        </div>
      </div>

      <a class="cta" href="<?= htmlspecialchars($groupUrl, ENT_QUOTES, 'UTF-8') ?>">
        ENTRAR NO GRUPO AGORA →
      </a>

      <div class="note">Você será direcionado para o grupo após clicar no botão.</div>
    </section>

    <div class="steps">
      <div class="step"><b>✓</b> Cadastro realizado</div>
      <div class="step"><b>2</b> Entrar no grupo</div>
      <div class="step"><b>3</b> Participar da próxima Live</div>
    </div>

    <footer>Gestão Condominial Inteligente · SindCON</footer>
  </main>
</div>
</body>
</html>
