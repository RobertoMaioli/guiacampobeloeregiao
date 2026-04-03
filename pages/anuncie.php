<?php
require_once __DIR__ . '/../core/UserAuth.php';
require_once __DIR__ . '/../includes/icons.php';
UserAuth::start(); // deve rodar antes de qualquer output HTML
$page_title = 'Anuncie no Guia Campo Belo — Apareça para quem importa';
$meta_desc  = 'Coloque seu negócio no radar de quem mora e frequenta Campo Belo. Planos a partir de R$0.';
$canonical  = 'https://guiacampobeloeregiao.com.br';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><?php include __DIR__ . '/../includes/head.php'; ?>
<style>
  .plan-price-mensal { display:block; }
  .plan-price-anual  { display:none; }
  body.anual .plan-price-mensal { display:none; }
  body.anual .plan-price-anual  { display:block; }
  .faq-body { display:none; }
  .faq-body.open { display:block; }
  .faq-icon { transition:transform .25s ease; }
  .faq-open .faq-icon { transform:rotate(180deg); }
  .plan-card { background:#fff; border-radius:24px; overflow:hidden; border:1px solid rgba(61,71,51,.07); box-shadow:0 2px 12px rgba(29,29,27,.07); display:flex; flex-direction:column; }
  .plan-card.featured { border-color:rgba(201,170,107,.4); box-shadow:0 20px 60px rgba(42,48,34,.25); }
  .step-circle { width:88px;height:88px;border-radius:50%;background:var(--gcb-green-dark);display:flex;align-items:center;justify-content:center;color:var(--gcb-gold);position:relative;box-shadow:0 8px 24px rgba(61,71,51,.18) }
  .step-num { position:absolute;top:-4px;right:-4px;width:28px;height:28px;border-radius:50%;background:var(--gcb-gold);display:flex;align-items:center;justify-content:center;color:var(--gcb-green-dark);font-size:10px;font-weight:800 }
</style>
</head>
<body>

<?php include __DIR__ . '/../includes/search-modal.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- HERO -->
<section style="background:var(--gcb-green-dark);padding-top:72px;position:relative;overflow:hidden">
  <div class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none"
       style="background:radial-gradient(ellipse 70% 80% at 100% 50%,rgba(201,170,107,.1) 0%,transparent 65%),
              radial-gradient(ellipse 50% 60% at 0% 80%,rgba(79,92,64,.4) 0%,transparent 60%)"></div>
  <div class="container py-5 py-lg-5 position-relative">
    <div class="row align-items-center g-5">
      <div class="col-12 col-lg-6">
        <h1 class="font-display fw-bold text-white mb-4" style="font-size:clamp(34px,5vw,60px);line-height:1.1">
          Apareça para<br/>quem <em class="fst-italic text-gold">importa.</em>
        </h1>
        <p style="font-size:15.5px;font-weight:300;color:rgba(255,255,255,.55);line-height:1.8;max-width:480px" class="mb-5">
          O Guia Campo Belo é a curadoria definitiva do bairro de maior IDH da Zona Sul.
          Coloque seu negócio na frente de um público qualificado, exigente e fiel.
        </p>
        <!-- Stats -->
        <div class="row g-0 rounded-3 overflow-hidden mb-5" style="background:rgba(255,255,255,.06)">
          <?php foreach ([['380+','Negócios listados'],['12','Categorias'],['0,935','IDH do bairro']] as $s): ?>
          <div class="col-4 text-center px-3 py-4 border-end" style="border-color:rgba(255,255,255,.08)!important">
            <div class="font-display fw-bold text-white" style="font-size:26px;line-height:1"><?= $s[0] ?></div>
            <div style="font-size:10px;font-weight:600;color:rgba(255,255,255,.35);margin-top:4px;line-height:1.3"><?= $s[1] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="d-flex flex-wrap gap-3">
          <a href="#planos" class="btn-gold d-inline-flex align-items-center gap-2">
            Ver planos <?= icon('arrow-right',13) ?>
          </a>
          <a href="https://wa.me/5511999999999" target="_blank" class="btn d-inline-flex align-items-center gap-2" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.2);color:#fff;border-radius:999px;font-size:12px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;padding:10px 24px">
            Falar conosco
          </a>
        </div>
      </div>

      <!-- Mock card -->
      <div class="col-12 col-lg-6 d-none d-lg-block position-relative">
        <div class="bg-white rounded-20 p-4 shadow" style="box-shadow:0 32px 80px rgba(0,0,0,.3)!important">
          <div class="rounded-3 overflow-hidden mb-4 position-relative" style="height:180px">
            <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=600&q=80" class="w-100 h-100 object-fit-cover" alt="Preview"/>
            <div class="position-absolute top-0 start-0 m-3"><span class="badge-gold">Destaque</span></div>
          </div>
          <p class="card-cat">Italiana · Contemporânea</p>
          <h3 class="card-title">Seu Negócio Aqui</h3>
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="stars">★★★★★</span>
            <span class="fw-bold" style="font-size:13px">5.0</span>
            <span style="font-size:12px;color:var(--gcb-warmgray)">(124 avaliações)</span>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <span class="tag-pill">Campo Belo</span>
            <span class="tag-pill">Reserva</span>
            <span class="tag-pill">Wi-Fi</span>
          </div>
        </div>
        <div class="position-absolute bg-gold rounded-3 px-3 py-2 shadow-gold" style="top:-16px;right:-16px;transform:rotate(3deg)">
          <p style="font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--gcb-green-dark)" class="mb-0">Visibilidade</p>
          <p class="font-display fw-bold mb-0" style="font-size:20px;color:var(--gcb-green-dark);line-height:1.2">Premium</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- BENEFÍCIOS -->
<section class="py-5 bg-offwhite">
  <div class="container">
    <div class="text-center mb-5">
      <span class="eyebrow">Por que anunciar</span>
      <h2 class="font-display fw-bold mb-0" style="color:var(--gcb-green-dark);font-size:clamp(26px,3.5vw,42px)">
        Um público <em class="fst-italic text-gold">diferente</em>
      </h2>
    </div>
    <div class="row g-4">
      <?php foreach ([
        ['icon'=>'trending-up','titulo'=>'Visibilidade segmentada','desc'=>'Apareça exatamente para quem busca o que você oferece — por categoria, bairro e palavras-chave.'],
        ['icon'=>'star',       'titulo'=>'Avaliações integradas',  'desc'=>'Conecte suas avaliações do Google e TripAdvisor. Construa reputação com quem já te conhece.'],
        ['icon'=>'map',        'titulo'=>'Destaque no mapa',       'desc'=>'Pin exclusivo no mapa interativo do Guia. Seja encontrado por quem está explorando a região agora.'],
        ['icon'=>'grid',       'titulo'=>'Galeria de fotos',       'desc'=>'Mostre seu espaço, seus produtos e sua equipe. Uma imagem vale mais que mil palavras.'],
        ['icon'=>'phone',      'titulo'=>'Contato direto',         'desc'=>'Botão de ligar, WhatsApp e e-mail direto na sua página. Zero atrito entre o cliente e você.'],
        ['icon'=>'award',      'titulo'=>'Selo de curadoria',      'desc'=>'Nosso time revisa cada cadastro. O selo "Curadoria Guia" comunica qualidade ao seu cliente.'],
      ] as $b): ?>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="bg-white rounded-20 p-4 shadow-card reveal h-100">
          <div class="rounded-3 d-flex align-items-center justify-content-center mb-4" style="width:48px;height:48px;background:var(--gcb-gold-pale);color:var(--gcb-green)">
            <?= icon($b['icon'],22) ?>
          </div>
          <h3 class="font-display fw-bold mb-2" style="color:var(--gcb-green-dark);font-size:17px"><?= htmlspecialchars($b['titulo']) ?></h3>
          <p style="font-size:13.5px;font-weight:300;color:var(--gcb-warmgray);line-height:1.7" class="mb-0"><?= htmlspecialchars($b['desc']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="py-5 bg-cream">
  <div class="container">
    <div class="text-center mb-5">
      <span class="eyebrow">Simples assim</span>
      <h2 class="font-display fw-bold mb-0" style="color:var(--gcb-green-dark);font-size:clamp(26px,3.5vw,42px)">
        Do cadastro ao <em class="fst-italic text-gold">destaque</em>
      </h2>
    </div>
    <div class="row g-4 justify-content-center text-center">
      <?php foreach ([
        ['num'=>'01','icon'=>'mail',        'titulo'=>'Entre em contato',  'desc'=>'Mande uma mensagem pelo WhatsApp ou e-mail. Respondemos em até 24h.'],
        ['num'=>'02','icon'=>'grid',        'titulo'=>'Monte seu perfil',  'desc'=>'Nossa equipe cadastra todas as informações, fotos e categorias do seu negócio.'],
        ['num'=>'03','icon'=>'trending-up', 'titulo'=>'Apareça e cresça',  'desc'=>'Seu negócio fica visível no mapa, na busca e nas categorias.'],
      ] as $s): ?>
      <div class="col-12 col-md-4 reveal">
        <div class="d-flex justify-content-center mb-4">
          <div class="step-circle">
            <?= icon($s['icon'],28) ?>
            <div class="step-num"><?= $s['num'] ?></div>
          </div>
        </div>
        <h3 class="font-display fw-bold mb-2" style="color:var(--gcb-green-dark);font-size:19px"><?= htmlspecialchars($s['titulo']) ?></h3>
        <p style="font-size:13.5px;font-weight:300;color:var(--gcb-warmgray);line-height:1.7;max-width:240px" class="mb-0 mx-auto"><?= htmlspecialchars($s['desc']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- PLANOS -->
<section class="py-5 bg-offwhite" id="planos">
  <div class="container">
    <div class="text-center mb-4">
      <span class="eyebrow">Planos</span>
      <h2 class="font-display fw-bold mb-0" style="color:var(--gcb-green-dark);font-size:clamp(26px,3.5vw,42px)">
        Escolha o que faz <em class="fst-italic text-gold">sentido</em>
      </h2>
    </div>
    <!-- Toggle -->
    <!--<div class="d-flex align-items-center justify-content-center gap-3 mb-5">-->
    <!--  <span id="lbl-mensal" class="fw-bold" style="font-size:13px;color:var(--gcb-green-dark)">Mensal</span>-->
    <!--  <button id="toggle-periodo" onclick="togglePeriodo()" class="position-relative border-0 rounded-pill" style="width:48px;height:24px;background:var(--gcb-green-dark);transition:background .2s">-->
    <!--    <span id="toggle-knob" class="position-absolute top-50 translate-middle-y rounded-circle" style="width:20px;height:20px;background:var(--gcb-gold);left:2px;transition:left .2s"></span>-->
    <!--  </button>-->
    <!--  <span id="lbl-anual" style="font-size:13px;font-weight:600;color:var(--gcb-warmgray)">-->
    <!--    Anual <span class="badge-gold ms-1">-20%</span>-->
    <!--  </span>-->
    <!--</div>-->


<style>
/* ── Reset mínimo ── */
.gcb-pricing * { box-sizing: border-box; }
.gcb-pricing { font-family: 'Montserrat', sans-serif; }

/* ── Variáveis ── */
.gcb-pricing {
  --green-dk:  #2a3022;
  --green:     #3d4733;
  --gold:      #c9aa6b;
  --gold-pale: #f7f2e8;
  --cream:     #faf8f3;
  --offwhite:  #f2efe8;
  --graphite:  #1d1d1b;
  --warmgray:  #6b6566;
  --border:    rgba(61,71,51,.10);
  --pro-bg:    #f0f5eb;
  --pro-head:  #2a3022;

  --font-base:    18px;   /* mínimo desktop */
  --font-sm:      15px;
  --font-xs:      13px;
  --icon-size:    22px;
  --row-pad:      14px 20px;
  --check-color:  #2a6e2a;
  --dash-color:   #aaa;
  --radius:       20px;
}

/* ── Legenda ── */
.gcb-pricing .pricing-legend {
  text-align: center;
  font-size: var(--font-sm);
  color: var(--warmgray);
  margin-bottom: 32px;
  font-weight: 500;
  letter-spacing: .02em;
}
.gcb-pricing .pricing-legend strong { color: var(--graphite); }

/* ── Wrapper scroll horizontal ── */
.gcb-pricing .table-scroll {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  border-radius: var(--radius);
  box-shadow: 0 4px 40px rgba(29,29,27,.08);
}

/* ── Tabela ── */
.gcb-pricing table {
  width: 100%;
  min-width: 640px;
  border-collapse: separate;
  border-spacing: 0;
  background: #fff;
  border-radius: var(--radius);
  overflow: hidden;
}

/* ── Cabeçalho sticky ── */
.gcb-pricing thead {
  position: sticky;
  top: 0;
  z-index: 20;
}
.gcb-pricing thead tr {
  background: #fff;
  box-shadow: 0 2px 0 var(--border);
}

/* ── Células gerais ── */
.gcb-pricing th,
.gcb-pricing td {
  padding: var(--row-pad);
  text-align: center;
  font-size: var(--font-base);
  vertical-align: middle;
  border-bottom: 1px solid var(--border);
}

/* Coluna de recurso: alinhada à esquerda */
.gcb-pricing td.feat-name,
.gcb-pricing th.feat-name {
  text-align: left;
  font-weight: 600;
  color: var(--graphite);
  white-space: nowrap;
  min-width: 240px;
}

/* ── Coluna PROFISSIONAL (destaque) ── */
.gcb-pricing .col-pro {
  background: var(--pro-bg);
}
.gcb-pricing thead .col-pro {
  background: var(--pro-head);
}

/* ── Cabeçalho de plano ── */
.gcb-pricing .plan-head {
  padding: 28px 20px 24px;
}
.gcb-pricing .plan-badge {
  display: inline-block;
  background: var(--gold);
  color: var(--green-dk);
  font-size: 11px;
  font-weight: 900;
  letter-spacing: .12em;
  text-transform: uppercase;
  padding: 4px 12px;
  border-radius: 999px;
  margin-bottom: 10px;
}
.gcb-pricing .plan-name {
  font-size: 22px;
  font-weight: 800;
  color: var(--green-dk);
  margin: 0 0 4px;
  line-height: 1.1;
}
.gcb-pricing thead .col-pro .plan-name {
  color: #fff;
}
.gcb-pricing .plan-desc {
  font-size: var(--font-xs);
  color: var(--warmgray);
  margin: 0 0 16px;
  font-weight: 400;
}
.gcb-pricing thead .col-pro .plan-desc {
  color: rgba(255,255,255,.55);
}
.gcb-pricing .plan-price {
  font-size: 40px;
  font-weight: 800;
  color: var(--green-dk);
  line-height: 1;
}
.gcb-pricing thead .col-pro .plan-price {
  color: #fff;
}
.gcb-pricing .plan-price sup {
  font-size: 18px;
  font-weight: 700;
  vertical-align: super;
  line-height: 1;
}
.gcb-pricing .plan-period {
  font-size: var(--font-xs);
  color: var(--warmgray);
  font-weight: 500;
  margin-top: 4px;
}
.gcb-pricing thead .col-pro .plan-period {
  color: rgba(255,255,255,.45);
}

/* Preços anuais - ocultos por padrão (toggle futuro) */
.gcb-pricing .preco-anual { display: none; }
/* .gcb-pricing .preco-mensal { display: none; }  ← descomentar ao ativar toggle */

/* ── Botões CTA ── */
.gcb-pricing .btn-plan {
  display: block;
  width: 100%;
  margin-top: 18px;
  padding: 14px 10px;
  border-radius: 999px;
  border: none;
  font-family: 'Montserrat', sans-serif;
  font-size: 13px;
  font-weight: 800;
  letter-spacing: .08em;
  text-transform: uppercase;
  text-decoration: none;
  text-align: center;
  cursor: pointer;
  transition: opacity .2s, transform .15s;
}
.gcb-pricing .btn-plan:hover {
  opacity: .88;
  transform: translateY(-1px);
}
.gcb-pricing .btn-essencial {
  background: transparent;
  border: 2px solid var(--green-dk);
  color: var(--green-dk);
}
.gcb-pricing .btn-pro {
  background: var(--gold);
  color: var(--green-dk);
  box-shadow: 0 6px 20px rgba(201,170,107,.35);
}
.gcb-pricing .btn-premium {
  background: var(--green-dk);
  color: #fff;
}

/* ── Ícones ✓ e — ── */
.gcb-pricing .ic {
  font-size: var(--icon-size);
  font-weight: 700;
  display: inline-block;
  line-height: 1;
}
.gcb-pricing .ic-yes {
  color: var(--check-color);
  font-size: 20px;
}
.gcb-pricing .ic-yes::before { content: "✓"; }
.gcb-pricing .ic-no {
  color: var(--dash-color);
  font-size: 20px;
}
.gcb-pricing .ic-no::before  { content: "—"; }

/* Texto de detalhe (ex: "5 imagens") */
.gcb-pricing .feat-detail {
  display: block;
  font-size: var(--font-xs);
  color: var(--warmgray);
  font-weight: 500;
  margin-top: 2px;
}

/* ── Linhas zebra ── */
.gcb-pricing tbody tr:nth-child(even) td { background: var(--cream); }
.gcb-pricing tbody tr:nth-child(even) td.col-pro { background: #e8efdf; }

/* ── Cabeçalho de seção ── */
.gcb-pricing .section-head td {
  background: var(--offwhite) !important;
  padding: 12px 20px;
  font-size: var(--font-xs);
  font-weight: 900;
  letter-spacing: .18em;
  text-transform: uppercase;
  color: var(--green);
  border-top: 2px solid var(--border);
  text-align: left;
}
.gcb-pricing .section-head td[colspan] {
  /* span toda a largura */
}

/* ── Rodapé ── */
.gcb-pricing .pricing-footer {
  text-align: center;
  font-size: var(--font-xs);
  color: var(--warmgray);
  margin-top: 20px;
  font-weight: 500;
}

/* ── Mobile ── */
@media (max-width: 767px) {
  .gcb-pricing {
    --font-base: 16px;
    --font-sm:   14px;
    --font-xs:   12px;
    --row-pad:   11px 14px;
    --icon-size: 20px;
  }
  .gcb-pricing .plan-price { font-size: 32px; }
  .gcb-pricing td.feat-name { min-width: 170px; }
  .gcb-pricing .btn-plan { font-size: 11px; padding: 12px 8px; }
  .gcb-pricing .table-scroll {
    /* dica visual de scroll */
    background: linear-gradient(to right, #fff 30%, rgba(255,255,255,0)),
                linear-gradient(to right, rgba(255,255,255,0), #fff 70%) 100% 0,
                radial-gradient(farthest-side at 0 50%, rgba(0,0,0,.07), transparent),
                radial-gradient(farthest-side at 100% 50%, rgba(0,0,0,.07), transparent) 100% 0;
    background-repeat: no-repeat;
    background-size: 40px 100%, 40px 100%, 14px 100%, 14px 100%;
    background-attachment: local, local, scroll, scroll;
  }
}
</style>

<!-- ═══════════════════════════════════════════ -->
<!--  BLOCO PRINCIPAL                           -->
<!-- ═══════════════════════════════════════════ -->
<div class="gcb-pricing">

  <!-- Legenda de acessibilidade -->
  <p class="pricing-legend">
    <strong>✓</strong> = incluído &nbsp;&nbsp;|&nbsp;&nbsp; <strong>—</strong> = não incluído neste plano
  </p>

  <!-- TOGGLE MENSAL/ANUAL (futuro — descomentar para ativar)
  <div style="display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:28px">
    <span id="lbl-mensal" style="font-size:15px;font-weight:700;color:var(--graphite)">Mensal</span>
    <button id="toggle-periodo" onclick="togglePeriodo()"
            style="position:relative;width:52px;height:28px;border-radius:999px;
                   border:none;cursor:pointer;background:var(--green-dk);padding:0">
      <span id="toggle-knob"
            style="position:absolute;top:4px;left:4px;width:20px;height:20px;
                   border-radius:50%;background:var(--gold);transition:left .2s"></span>
    </button>
    <span id="lbl-anual" style="font-size:15px;font-weight:600;color:var(--warmgray)">
      Anual <span style="background:var(--gold);color:var(--green-dk);font-size:10px;
                         font-weight:900;padding:2px 8px;border-radius:999px;margin-left:4px">-20%</span>
    </span>
  </div>
  <script>
  function togglePeriodo() {
    const mensal = document.querySelectorAll('.preco-mensal');
    const anual  = document.querySelectorAll('.preco-anual');
    const knob   = document.getElementById('toggle-knob');
    const isAnual = knob.style.left === '28px';
    knob.style.left = isAnual ? '4px' : '28px';
    mensal.forEach(el => el.style.display = isAnual ? '' : 'none');
    anual.forEach(el  => el.style.display = isAnual ? 'none' : '');
    document.getElementById('lbl-mensal').style.color = isAnual ? 'var(--graphite)' : 'var(--warmgray)';
    document.getElementById('lbl-anual').style.color  = isAnual ? 'var(--warmgray)' : 'var(--graphite)';
  }
  </script>
  -->

  <div class="table-scroll">
    <table>

      <!-- ══ CABEÇALHO ══ -->
      <thead>
        <tr>
          <!-- Coluna de recurso (vazia no head) -->
          <th class="feat-name" scope="col" style="background:#fff;border-bottom:1px solid var(--border)">
            <span style="font-size:13px;font-weight:700;color:var(--warmgray);letter-spacing:.08em;text-transform:uppercase">Recurso</span>
          </th>

          <!-- Essencial -->
          <th scope="col" style="background:#fff;border-bottom:1px solid var(--border)">
            <div class="plan-head">
              <p class="plan-name">Essencial</p>
              <p class="plan-desc">Para começar a aparecer</p>
              <div class="preco-mensal">
                <p class="plan-price">Grátis</p>
                <p class="plan-period">para sempre</p>
              </div>
              <div class="preco-anual">
                <p class="plan-price">Grátis</p>
                <p class="plan-period">para sempre</p>
              </div>
              <a href="/empresa/cadastro.php?plan=essencial" class="btn-plan btn-essencial">
                Cadastrar grátis
              </a>
            </div>
          </th>

          <!-- Profissional (destaque) -->
          <th scope="col" class="col-pro">
            <div class="plan-head">
              <span class="plan-badge">⭐ Mais popular</span>
              <p class="plan-name">Profissional</p>
              <p class="plan-desc">Para quem quer ser encontrado</p>
              <div class="preco-mensal">
                <p class="plan-price"><sup>R$</sup>89</p>
                <p class="plan-period">/mês · sem fidelidade</p>
              </div>
              <div class="preco-anual">
                <p class="plan-price"><sup>R$</sup>71</p>
                <p class="plan-period">/mês · cobrado anualmente</p>
              </div>
              <a href="/empresa/cadastro.php?plan=profissional" class="btn-plan btn-pro">
                Assinar Profissional
              </a>
            </div>
          </th>

          <!-- Premium -->
          <th scope="col" style="background:#fff;border-bottom:1px solid var(--border)">
            <div class="plan-head">
              <p class="plan-name">Premium</p>
              <p class="plan-desc">Para líderes de categoria</p>
              <div class="preco-mensal">
                <p class="plan-price"><sup>R$</sup>159</p>
                <p class="plan-period">/mês · sem fidelidade</p>
              </div>
              <div class="preco-anual">
                <p class="plan-price"><sup>R$</sup>127</p>
                <p class="plan-period">/mês · cobrado anualmente</p>
              </div>
              <a href="/empresa/cadastro.php?plan=premium" class="btn-plan btn-premium">
                Assinar Premium
              </a>
            </div>
          </th>
        </tr>
      </thead>

      <!-- ══ CORPO ══ -->
      <tbody>

        <!-- ── SEÇÃO: BÁSICO ── -->
        <tr class="section-head">
          <td colspan="4">📋 Básico</td>
        </tr>
        <tr>
          <td class="feat-name">Página da empresa no site</td>
          <td><span class="ic ic-yes" aria-label="Incluído"></span></td>
          <td class="col-pro"><span class="ic ic-yes" aria-label="Incluído"></span></td>
          <td><span class="ic ic-yes" aria-label="Incluído"></span></td>
        </tr>
        <tr>
          <td class="feat-name">Nome, endereço e contato</td>
          <td><span class="ic ic-yes" aria-label="Incluído"></span></td>
          <td class="col-pro"><span class="ic ic-yes" aria-label="Incluído"></span></td>
          <td><span class="ic ic-yes" aria-label="Incluído"></span></td>
        </tr>
        <tr>
          <td class="feat-name">Horários de funcionamento</td>
          <td><span class="ic ic-yes" aria-label="Incluído"></span></td>
          <td class="col-pro"><span class="ic ic-yes" aria-label="Incluído"></span></td>
          <td><span class="ic ic-yes" aria-label="Incluído"></span></td>
        </tr>
        <tr>
          <td class="feat-name">Descrição da empresa</td>
          <td><span class="ic ic-yes" aria-label="Incluído"></span></td>
          <td class="col-pro"><span class="ic ic-yes" aria-label="Incluído"></span></td>
          <td><span class="ic ic-yes" aria-label="Incluído"></span></td>
        </tr>

        <!-- ── SEÇÃO: VISUAL / GALERIA ── -->
        <tr class="section-head">
          <td colspan="4">🖼️ Visual e Galeria</td>
        </tr>
        <tr>
          <td class="feat-name">Imagem de capa</td>
          <td><span class="ic ic-no" aria-label="Não incluído"></span></td>
          <td class="col-pro"><span class="ic ic-yes" aria-label="Incluído"></span></td>
          <td><span class="ic ic-yes" aria-label="Incluído"></span></td>
        </tr>
        <tr>
          <td class="feat-name">Galeria de imagens</td>
          <td><span class="ic ic-no" aria-label="Não incluído"></span></td>
          <td class="col-pro">
            <span class="ic ic-yes" aria-label="Incluído"></span>
            <span class="feat-detail">até 5 imagens</span>
          </td>
          <td>
            <span class="ic ic-yes" aria-label="Incluído"></span>
            <span class="feat-detail">ilimitadas</span>
          </td>
        </tr>

        <!-- ── SEÇÃO: TAGS ── -->
        <tr class="section-head">
          <td colspan="4">🏷️ Tags</td>
        </tr>
        <tr>
          <td class="feat-name">Tags de categoria</td>
          <td><span class="ic ic-no" aria-label="Não incluído"></span></td>
          <td class="col-pro">
            <span class="ic ic-yes" aria-label="Incluído"></span>
            <span class="feat-detail">até 5 tags</span>
          </td>
          <td>
            <span class="ic ic-yes" aria-label="Incluído"></span>
            <span class="feat-detail">ilimitadas</span>
          </td>
        </tr>

        <!-- ── SEÇÃO: MAPA E BUSCA ── -->
        <tr class="section-head">
          <td colspan="4">📍 Mapa e Busca</td>
        </tr>
        <tr>
          <td class="feat-name">Pin no mapa interativo</td>
          <td><span class="ic ic-no" aria-label="Não incluído"></span></td>
          <td class="col-pro"><span class="ic ic-yes" aria-label="Incluído"></span></td>
          <td><span class="ic ic-yes" aria-label="Incluído"></span></td>
        </tr>

        <!-- ── SEÇÃO: CREDIBILIDADE ── -->
        <tr class="section-head">
          <td colspan="4">⭐ Credibilidade</td>
        </tr>
        <tr>
          <td class="feat-name">Sincronização Google Reviews</td>
          <td><span class="ic ic-no" aria-label="Não incluído"></span></td>
          <td class="col-pro"><span class="ic ic-yes" aria-label="Incluído"></span></td>
          <td><span class="ic ic-yes" aria-label="Incluído"></span></td>
        </tr>
        <tr>
          <td class="feat-name">Selo "Destaque" na listagem</td>
          <td><span class="ic ic-no" aria-label="Não incluído"></span></td>
          <td class="col-pro"><span class="ic ic-no" aria-label="Não incluído"></span></td>
          <td><span class="ic ic-yes" aria-label="Incluído"></span></td>
        </tr>
        <tr>
          <td class="feat-name">Sem anúncios de concorrentes</td>
          <td><span class="ic ic-no" aria-label="Não incluído"></span></td>
          <td class="col-pro"><span class="ic ic-no" aria-label="Não incluído"></span></td>
          <td><span class="ic ic-yes" aria-label="Incluído"></span></td>
        </tr>

        <!-- ── SEÇÃO: CONTATO ── -->
        <tr class="section-head">
          <td colspan="4">📞 Contato</td>
        </tr>
        <tr>
          <td class="feat-name">Link do site</td>
          <td><span class="ic ic-no" aria-label="Não incluído"></span></td>
          <td class="col-pro"><span class="ic ic-yes" aria-label="Incluído"></span></td>
          <td><span class="ic ic-yes" aria-label="Incluído"></span></td>
        </tr>
        <tr>
          <td class="feat-name">Botão WhatsApp direto</td>
          <td><span class="ic ic-no" aria-label="Não incluído"></span></td>
          <td class="col-pro"><span class="ic ic-yes" aria-label="Incluído"></span></td>
          <td><span class="ic ic-yes" aria-label="Incluído"></span></td>
        </tr>
        <tr>
          <td class="feat-name">Links de redes sociais</td>
          <td><span class="ic ic-no" aria-label="Não incluído"></span></td>
          <td class="col-pro"><span class="ic ic-yes" aria-label="Incluído"></span></td>
          <td><span class="ic ic-yes" aria-label="Incluído"></span></td>
        </tr>
       

        <!-- ── SEÇÃO: EXPERIÊNCIA PREMIUM ── -->
        <tr class="section-head">
          <td colspan="4">💎 Experiência Premium</td>
        </tr>
        <tr>
          <td class="feat-name">Gestão pela nossa equipe</td>
          <td><span class="ic ic-no" aria-label="Não incluído"></span></td>
          <td class="col-pro"><span class="ic ic-no" aria-label="Não incluído"></span></td>
          <td><span class="ic ic-yes" aria-label="Incluído"></span></td>
        </tr>
        <tr>
          <td class="feat-name">Suporte prioritário</td>
          <td><span class="ic ic-no" aria-label="Não incluído"></span></td>
          <td class="col-pro"><span class="ic ic-no" aria-label="Não incluído"></span></td>
          <td><span class="ic ic-yes" aria-label="Incluído"></span></td>
        </tr>

        <!-- ── Linha de CTA repetida no fundo (boa prática UX) ── -->
        <tr>
          <td class="feat-name" style="border-bottom:none"></td>
          <td style="padding:24px 20px;border-bottom:none">
            <a href="/empresa/cadastro.php?plan=essencial" class="btn-plan btn-essencial">
              Cadastrar grátis
            </a>
          </td>
          <td class="col-pro" style="padding:24px 20px;border-bottom:none">
            <a href="/empresa/cadastro.php?plan=profissional" class="btn-plan btn-pro">
              Assinar Profissional
            </a>
          </td>
          <td style="padding:24px 20px;border-bottom:none">
            <a href="/empresa/cadastro.php?plan=premium" class="btn-plan btn-premium">
              Assinar Premium
            </a>
          </td>
        </tr>

      </tbody>
    </table>
  </div><!-- /table-scroll -->


</div><!-- /gcb-pricing -->
  </div>
</section>

<!-- FAQ -->
<section class="py-5 bg-cream">
  <div class="container" style="max-width:760px">
    <div class="text-center mb-5">
      <span class="eyebrow">Dúvidas</span>
      <h2 class="font-display fw-bold mb-0" style="color:var(--gcb-green-dark);font-size:clamp(26px,3.5vw,38px)">
        Perguntas <em class="fst-italic text-gold">frequentes</em>
      </h2>
    </div>
    <?php foreach ([
      ['Preciso ter CNPJ para anunciar?','Não. Aceitamos MEI, autônomos, prestadores de serviço e profissionais liberais. Qualquer negócio que atue em Campo Belo e região pode anunciar.'],
      ['Como funciona o plano gratuito?','O plano Essencial é 100% gratuito e permanente. Inclui perfil básico com nome, endereço, contato e até 3 fotos.'],
      ['Posso cancelar quando quiser?','Sim. Não há fidelidade mínima nos planos mensais. Você pode cancelar a qualquer momento sem multa.'],
      ['Quem cadastra as informações do meu negócio?','Nos planos Profissional e Premium, nossa equipe faz o cadastro completo por você.'],
      ['Como funcionam os destaques na busca?','Negócios nos planos Profissional e Premium aparecem antes dos demais nos resultados de busca.'],
      ['A integração com o Google Reviews é automática?','Sim. Com o Place ID do seu negócio, sincronizamos sua nota e avaliações automaticamente.'],
    ] as $f): ?>
    <div class="border-bottom py-1" style="border-color:rgba(61,71,51,.07)!important">
      <button class="faq-btn d-flex align-items-center justify-content-between gap-4 w-100 bg-transparent border-0 py-4 text-start"
              onclick="toggleFaq(this)">
        <span style="font-size:15px;font-weight:600;color:var(--gcb-graphite);line-height:1.4"><?= htmlspecialchars($f[0]) ?></span>
        <span class="faq-icon rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:24px;height:24px;background:var(--gcb-offwhite);color:var(--gcb-warmgray)">
          <?= icon('chevron-down',14) ?>
        </span>
      </button>
      <div class="faq-body pb-4">
        <p style="font-size:14px;font-weight:300;color:var(--gcb-warmgray);line-height:1.75" class="mb-0"><?= htmlspecialchars($f[1]) ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
let isAnual = false;
function togglePeriodo() {
  isAnual = !isAnual;
  document.body.classList.toggle('anual', isAnual);
  document.getElementById('toggle-knob').style.left = isAnual ? '26px' : '2px';
  document.getElementById('toggle-periodo').style.background = isAnual ? 'var(--gcb-gold)' : 'var(--gcb-green-dark)';
  document.getElementById('lbl-mensal').style.color = isAnual ? 'var(--gcb-warmgray)' : 'var(--gcb-green-dark)';
  document.getElementById('lbl-anual').style.color  = isAnual ? 'var(--gcb-green-dark)' : 'var(--gcb-warmgray)';
}
function toggleFaq(btn) {
  const item = btn.parentElement;
  const body = item.querySelector('.faq-body');
  const isOpen = body.classList.contains('open');
  document.querySelectorAll('.faq-body').forEach(b => b.classList.remove('open'));
  document.querySelectorAll('.faq-btn').forEach(b => b.classList.remove('faq-open'));
  if (!isOpen) { body.classList.add('open'); btn.classList.add('faq-open'); }
}
</script>
</body>
</html>