<?php
$page_title = 'Parceiros Recomendados — Guia Campo Belo & Região';
$meta_desc  = 'Marketing, site, vídeo e escritório: 4 parceiros para elevar a presença do seu negócio em Campo Belo.';
$canonical  = 'https://guiacampobeloeregiao.com.br/pages/parceiros.php';
include __DIR__ . '/../core/UserAuth.php';
UserAuth::start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
  <style>
    /* ── Variáveis ── */
    :root {
      --green-dk:  #2a3022;
      --green:     #3d4733;
      --gold:      #c9aa6b;
      --gold-pale: #f7f2e8;
      --cream:     #faf8f3;
      --offwhite:  #f2efe8;
      --graphite:  #1d1d1b;
      --warm:      #6b6566;
      --border:    rgba(61,71,51,.09);
      --radius:    20px;
      --fs-base:   18px;
      --fs-sm:     15px;
      --fs-xs:     13px;
    }

    /* ── Base ── */
    body { background: var(--cream); font-family: 'Montserrat', sans-serif; color: var(--graphite); }

    /* ── Utilitários tipográficos ── */
    .eyebrow-par {
      display: inline-block;
      font-size: 10px;
      font-weight: 800;
      letter-spacing: .22em;
      text-transform: uppercase;
      color: var(--gold);
      margin-bottom: 10px;
    }
    .section-title {
      font-family: 'Montserrat', sans-serif;
      font-size: clamp(26px, 3.5vw, 42px);
      font-weight: 800;
      color: var(--green-dk);
      line-height: 1.15;
      margin: 0 0 16px;
    }
    .section-sub {
      font-size: var(--fs-base);
      font-weight: 300;
      color: var(--warm);
      line-height: 1.75;
      max-width: 580px;
    }

    /* ════════════════════════
       HERO
    ════════════════════════ */
    .par-hero {
      background: var(--green-dk);
      padding: 140px 0 90px;
      position: relative;
      overflow: hidden;
    }
    /* linha decorativa sutil */
    .par-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image:
        linear-gradient(rgba(201,170,107,.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(201,170,107,.04) 1px, transparent 1px);
      background-size: 64px 64px;
      pointer-events: none;
    }
    .par-hero::after {
      content: '';
      position: absolute;
      top: -120px; right: -120px;
      width: 500px; height: 500px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(201,170,107,.07) 0%, transparent 70%);
      pointer-events: none;
    }
    .par-hero .container { position: relative; z-index: 1; }

    .hero-tag {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 16px;
      border: 1px solid rgba(201,170,107,.3);
      border-radius: 999px;
      background: rgba(201,170,107,.08);
      font-size: 10px;
      font-weight: 800;
      letter-spacing: .18em;
      text-transform: uppercase;
      color: var(--gold);
      margin-bottom: 28px;
    }
    .hero-headline {
      font-size: clamp(36px, 5.5vw, 64px);
      font-weight: 800;
      color: #fff;
      line-height: 1.1;
      margin: 0 0 20px;
      max-width: 680px;
    }
    .hero-headline em {
      font-style: normal;
      color: var(--gold);
    }
    .hero-pain {
      font-size: clamp(17px, 2vw, 21px);
      font-weight: 300;
      color: rgba(255,255,255,.6);
      line-height: 1.65;
      max-width: 560px;
      margin: 0 0 14px;
    }
    .hero-sub {
      font-size: var(--fs-sm);
      font-weight: 400;
      color: rgba(255,255,255,.38);
      line-height: 1.6;
      max-width: 500px;
      margin: 0 0 40px;
    }
    .btn-hero {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 16px 36px;
      background: var(--gold);
      color: var(--green-dk);
      border-radius: 999px;
      font-size: 13px;
      font-weight: 800;
      letter-spacing: .1em;
      text-transform: uppercase;
      text-decoration: none;
      transition: background .2s, transform .15s;
    }
    .btn-hero:hover { background: #ddc48a; transform: translateY(-2px); color: var(--green-dk); }
    .btn-hero svg { transition: transform .2s; }
    .btn-hero:hover svg { transform: translateX(3px); }

    /* número flutuante decorativo */
    .hero-stat-row {
      display: flex;
      gap: 40px;
      margin-top: 56px;
      padding-top: 40px;
      border-top: 1px solid rgba(255,255,255,.08);
    }
    .hero-stat-num {
      font-size: 32px;
      font-weight: 800;
      color: #fff;
      line-height: 1;
    }
    .hero-stat-lbl {
      font-size: 11px;
      color: rgba(255,255,255,.35);
      font-weight: 500;
      margin-top: 4px;
      letter-spacing: .06em;
    }

    /* ════════════════════════
       CARDS DE PARCEIROS
    ════════════════════════ */
    .parceiros-section {
      padding: 96px 0 80px;
      background: var(--cream);
    }
    .section-header { margin-bottom: 56px; }

    .partner-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 28px;
    }
    @media (max-width: 900px) {
      .partner-grid { grid-template-columns: 1fr; }
    }

    .partner-card {
      background: #fff;
      border-radius: var(--radius);
      border: 1px solid var(--border);
      box-shadow: 0 2px 20px rgba(29,29,27,.06);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      transition: transform .3s ease, box-shadow .3s ease;
    }
    .partner-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 12px 40px rgba(29,29,27,.10);
    }

    /* faixa colorida topo do card */
    .card-accent {
      height: 5px;
      background: linear-gradient(90deg, var(--green) 0%, var(--gold) 100%);
    }

    .card-body-par { padding: 32px 32px 24px; flex: 1; }

    .card-tag {
      display: inline-block;
      font-size: 9px;
      font-weight: 900;
      letter-spacing: .22em;
      text-transform: uppercase;
      color: var(--gold);
      background: var(--gold-pale);
      border: 1px solid rgba(201,170,107,.25);
      border-radius: 999px;
      padding: 4px 12px;
      margin-bottom: 14px;
    }
    .card-partner-name {
      font-size: 22px;
      font-weight: 800;
      color: var(--green-dk);
      margin: 0 0 4px;
      line-height: 1.2;
    }
    .card-partner-role {
      font-size: var(--fs-xs);
      color: var(--warm);
      font-weight: 400;
      margin: 0 0 20px;
    }
    .card-desc {
      font-size: var(--fs-sm);
      color: var(--graphite);
      font-weight: 300;
      line-height: 1.75;
      margin: 0 0 24px;
    }

    /* bullets */
    .card-bullets {
      list-style: none;
      padding: 0;
      margin: 0 0 28px;
      display: flex;
      flex-direction: column;
      gap: 9px;
    }
    .card-bullets li {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      font-size: var(--fs-sm);
      color: var(--graphite);
      font-weight: 500;
      line-height: 1.5;
    }
    .bullet-dot {
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background: var(--gold-pale);
      border: 1.5px solid rgba(201,170,107,.35);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      margin-top: 1px;
    }
    .bullet-dot::after {
      content: '';
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: var(--gold);
    }

    /* botões do card */
    .card-footer-par {
      padding: 20px 32px 28px;
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      border-top: 1px solid var(--border);
    }
    .btn-site {
      flex: 1;
      min-width: 140px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 14px 20px;
      background: var(--green-dk);
      color: #fff;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: .08em;
      text-transform: uppercase;
      text-decoration: none;
      transition: background .2s, transform .15s;
    }
    .btn-site:hover { background: var(--green); transform: translateY(-1px); color: #fff; }
    .btn-whats {
      flex: 1;
      min-width: 140px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 14px 20px;
      background: transparent;
      color: var(--green-dk);
      border: 2px solid rgba(61,71,51,.2);
      border-radius: 999px;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: .08em;
      text-transform: uppercase;
      text-decoration: none;
      transition: border-color .2s, background .2s, transform .15s;
    }
    .btn-whats:hover {
      border-color: var(--gold);
      background: var(--gold-pale);
      color: var(--green-dk);
      transform: translateY(-1px);
    }

    /* ════════════════════════
       SEÇÃO "COMO ESCOLHER"
    ════════════════════════ */
    .como-section {
      background: var(--green-dk);
      padding: 90px 0;
      position: relative;
      overflow: hidden;
    }
    .como-section::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image:
        linear-gradient(rgba(201,170,107,.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(201,170,107,.03) 1px, transparent 1px);
      background-size: 48px 48px;
    }
    .como-section .container { position: relative; z-index: 1; }
    .como-section .section-title { color: #fff; }
    .como-section .section-sub { color: rgba(255,255,255,.45); }

    .steps-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 28px;
      margin-top: 52px;
    }
    @media (max-width: 720px) {
      .steps-row { grid-template-columns: 1fr; }
    }

    .step-card {
      background: rgba(255,255,255,.04);
      border: 1px solid rgba(255,255,255,.08);
      border-radius: var(--radius);
      padding: 32px 28px;
      position: relative;
    }
    .step-num {
      font-size: 52px;
      font-weight: 900;
      color: rgba(201,170,107,.15);
      line-height: 1;
      margin-bottom: 16px;
      letter-spacing: -.02em;
    }
    .step-title {
      font-size: 17px;
      font-weight: 800;
      color: #fff;
      margin: 0 0 10px;
    }
    .step-desc {
      font-size: var(--fs-sm);
      color: rgba(255,255,255,.45);
      font-weight: 300;
      line-height: 1.7;
      margin: 0;
    }
    .step-pill {
      display: inline-block;
      font-size: 9px;
      font-weight: 900;
      letter-spacing: .18em;
      text-transform: uppercase;
      color: var(--gold);
      border: 1px solid rgba(201,170,107,.3);
      border-radius: 999px;
      padding: 3px 10px;
      margin-bottom: 16px;
    }

    /* ════════════════════════
       RODAPÉ DE SEÇÃO
    ════════════════════════ */
    .par-footer-section {
      background: var(--offwhite);
      padding: 60px 0;
      text-align: center;
      border-top: 1px solid var(--border);
    }
    .par-footer-quote {
      font-size: clamp(18px, 2.5vw, 26px);
      font-weight: 800;
      color: var(--green-dk);
      line-height: 1.4;
      margin: 0 0 10px;
    }
    .par-footer-quote em {
      font-style: normal;
      color: var(--gold);
    }
    .par-footer-sub {
      font-size: var(--fs-xs);
      color: var(--warm);
      font-weight: 400;
      margin: 0 0 28px;
    }
    .btn-back-home {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 14px 28px;
      background: var(--green-dk);
      color: #fff;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: .08em;
      text-transform: uppercase;
      text-decoration: none;
      transition: background .2s;
    }
    .btn-back-home:hover { background: var(--green); color: #fff; }

    /* ── Mobile ajustes ── */
    @media (max-width: 767px) {
      :root { --fs-base: 16px; --fs-sm: 14px; }
      .par-hero { padding: 120px 0 64px; }
      .hero-stat-row { gap: 24px; flex-wrap: wrap; }
      .card-body-par, .card-footer-par { padding-left: 22px; padding-right: 22px; }
      .parceiros-section { padding: 64px 0 56px; }
      .como-section { padding: 64px 0; }
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/../includes/search-modal.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- ══════════════════════════════════════════
     HERO
══════════════════════════════════════════ -->
<section class="par-hero">
  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-9">

        <div class="hero-tag">
          <span>✦</span> Parceiros recomendados pelo Guia
        </div>

        <h1 class="hero-headline">
          Quem não é visto,<br>não é <em>lembrado.</em>
        </h1>

        <p class="hero-pain">
          Se a comunicação do seu negócio está fraca, ele parece menor do que é — independente da qualidade do que você entrega.
        </p>

        <p class="hero-sub">
          Aqui estão 4 parceiros que recomendamos para elevar sua presença, percepção e resultado.
        </p>

        <a href="#parceiros" class="btn-hero">
          Escolha o que você precisa
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>

      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     CARDS DE PARCEIROS
══════════════════════════════════════════ -->
<section class="parceiros-section" id="parceiros">
  <div class="container">

    <div class="section-header">
      <span class="eyebrow-par">Escolha seu próximo passo</span>
      <h2 class="section-title">4 frentes.<br>Um negócio mais forte.</h2>
      <p class="section-sub">Cada parceiro resolve uma parte específica da sua presença. Você pode começar por uma ou avançar em todas.</p>
    </div>

    <div class="partner-grid">

      <!-- ── CARD 1: MARKETING — Banzai ── -->
      <article class="partner-card" aria-label="Banzai - Marketing">
        <div class="card-accent"></div>
        <div class="card-body-par">
          <span class="card-tag">✦ Seu Marketing</span>
          <h3 class="card-partner-name">Banzai</h3>
          <p class="card-partner-role">Branding, Design e Marketing Digital</p>
          <p class="card-desc">
            Marca fraca é receita que fica na mesa. A Banzai trabalha branding, naming, design gráfico e marketing digital — o conjunto que faz um negócio ser reconhecido antes mesmo de ser experimentado. Se você quer que as pessoas lembrem do seu nome, saibam o que você representa e confiem antes de entrar em contato, é aqui que começa.
          </p>
          <ul class="card-bullets" aria-label="O que resolve">
            <li><span class="bullet-dot" aria-hidden="true"></span> Identidade visual e posicionamento de marca</li>
            <li><span class="bullet-dot" aria-hidden="true"></span> Design gráfico e digital para comunicação</li>
            <li><span class="bullet-dot" aria-hidden="true"></span> Estratégia e execução de marketing digital</li>
          </ul>
        </div>
        <div class="card-footer-par">
          <a href="https://banzaibmkt.com.br/" target="_blank" rel="noopener" class="btn-site">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            Acessar site
          </a>
          <a href="https://api.whatsapp.com/send?phone=5511983558500" target="_blank" rel="noopener" class="btn-whats">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            Falar no WhatsApp
          </a>
        </div>
      </article>

      <!-- ── CARD 2: SITE — Maioli Design ── -->
      <article class="partner-card" aria-label="Maioli Design - Sites">
        <div class="card-accent"></div>
        <div class="card-body-par">
          <span class="card-tag">✦ Seu Site</span>
          <h3 class="card-partner-name">Maioli Design</h3>
          <p class="card-partner-role">Criação de Sites Profissionais</p>
          <p class="card-desc">
            Um site feio ou inexistente fala antes de você falar. A Maioli Design cria sites profissionais com foco em qualidade, resultado e presença online — atendendo negócios de diferentes segmentos com soluções sob medida. Além da criação, oferece manutenção, hospedagem, otimização de SEO e UI/UX. O mesmo estúdio que desenvolveu este Guia.
          </p>
          <ul class="card-bullets" aria-label="O que resolve">
            <li><span class="bullet-dot" aria-hidden="true"></span> Criação, manutenção e hospedagem de sites</li>
            <li><span class="bullet-dot" aria-hidden="true"></span> Otimização SEO para aparecer no Google</li>
            <li><span class="bullet-dot" aria-hidden="true"></span> Design de interface com foco em conversão</li>
          </ul>
        </div>
        <div class="card-footer-par">
          <a href="https://maiolidesign.com.br/" target="_blank" rel="noopener" class="btn-site">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            Acessar site
          </a>
          <a href="https://api.whatsapp.com/send?phone=5511978348787&text=Olá,%20vim%20pelo%20Guia%20Campo%20Belo%20e%20gostaria%20de%20orçamento%20de%20site!" target="_blank" rel="noopener" class="btn-whats">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            Falar no WhatsApp
          </a>
        </div>
      </article>

      <!-- ── CARD 3: VÍDEO — Massago ── -->
      <article class="partner-card" aria-label="Massago - Audiovisual">
        <div class="card-accent"></div>
        <div class="card-body-par">
          <span class="card-tag">✦ Seu Vídeo</span>
          <h3 class="card-partner-name">Massago.rec</h3>
          <p class="card-partner-role">Produção Audiovisual</p>
          <p class="card-desc">
            Vídeo parado é dinheiro parado. Guilherme Massago é realizador audiovisual com atuação em direção de fotografia, operação de câmera, montagem e colorização. Produção com olhar apurado, resultado que representa o nível real do seu negócio — e não uma versão amadora dele.
          </p>
          <ul class="card-bullets" aria-label="O que resolve">
            <li><span class="bullet-dot" aria-hidden="true"></span> Direção de fotografia e operação de câmera</li>
            <li><span class="bullet-dot" aria-hidden="true"></span> Montagem e colorização profissional</li>
            <li><span class="bullet-dot" aria-hidden="true"></span> Conteúdo audiovisual para redes e apresentações</li>
          </ul>
        </div>
        <div class="card-footer-par">
          <a href="https://massagorec.myportfolio.com/home" target="_blank" rel="noopener" class="btn-site">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            Ver portfólio
          </a>
        </div>
      </article>

      <!-- ── CARD 4: ESCRITÓRIO — Tato Office ── -->
      <article class="partner-card" aria-label="Tato Office - Coworking">
        <div class="card-accent"></div>
        <div class="card-body-par">
          <span class="card-tag">✦ Seu Escritório</span>
          <h3 class="card-partner-name">Tato Office Boutique</h3>
          <p class="card-partner-role">Ambiente de Sucesso — Coworking Premium</p>
          <p class="card-desc">
            O ambiente onde você trabalha comunica quem você é — para clientes, parceiros e para você mesmo. O Tato Office Boutique é um espaço de coworking com conceito boutique: presença, exclusividade e o cenário certo para quem quer elevar o nível das reuniões, atendimentos e do próprio trabalho do dia a dia.
          </p>
          <ul class="card-bullets" aria-label="O que resolve">
            <li><span class="bullet-dot" aria-hidden="true"></span> Espaço profissional sem custo fixo de sede</li>
            <li><span class="bullet-dot" aria-hidden="true"></span> Ambiente de alto padrão para reuniões e atendimentos</li>
            <li><span class="bullet-dot" aria-hidden="true"></span> Presença e exclusividade no dia a dia de trabalho</li>
          </ul>
        </div>
        <div class="card-footer-par">
          <a href="https://www.tatocoworking.com.br/" target="_blank" rel="noopener" class="btn-site">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            Acessar site
          </a>
        </div>
      </article>

    </div><!-- /partner-grid -->
  </div>
</section>


<!-- ══════════════════════════════════════════
     COMO ESCOLHER — 3 PASSOS
══════════════════════════════════════════ -->
<section class="como-section">
  <div class="container">

    <div class="row">
      <div class="col-12 col-lg-7">
        <span class="eyebrow-par">Como escolher</span>
        <h2 class="section-title">Três passos.<br>Zero desperdício.</h2>
        <p class="section-sub">Não precisa resolver tudo de uma vez. O segredo é começar pelo que trava mais.</p>
      </div>
    </div>

    <div class="steps-row">

      <div class="step-card">
        <div class="step-num">01</div>
        <div class="step-pill">Diagnóstico</div>
        <h3 class="step-title">Onde está o buraco?</h3>
        <p class="step-desc">Identifique o que está mais fraco hoje: marca sem identidade, site ausente, conteúdo sem vídeo ou sem um espaço profissional. Comece por aí.</p>
      </div>

      <div class="step-card">
        <div class="step-num">02</div>
        <div class="step-pill">Ação</div>
        <h3 class="step-title">Contrate um parceiro.</h3>
        <p class="step-desc">Entre em contato com o parceiro que resolve o seu problema agora. Cada um foi escolhido a dedo — você não vai precisar explicar do zero o que quer.</p>
      </div>

      <div class="step-card">
        <div class="step-num">03</div>
        <div class="step-pill">Consistência</div>
        <h3 class="step-title">Repita e evolua.</h3>
        <p class="step-desc">Comunicação forte não é um projeto único — é uma construção. Com as bases certas, cada melhoria compõe e o negócio ganha peso de verdade no mercado.</p>
      </div>

    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     RODAPÉ DA PÁGINA
══════════════════════════════════════════ -->
<section class="par-footer-section">
  <div class="container">
    <p class="par-footer-quote">
      "Dica boa não fica solta —<br><em>Fica salva e vira guia.</em>"
    </p>
    <p class="par-footer-sub">Guia Campo Belo &amp; Região · Curadoria para quem valoriza tempo e qualidade.</p>
    <a href="/" class="btn-back-home">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
      Voltar ao Guia
    </a>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>