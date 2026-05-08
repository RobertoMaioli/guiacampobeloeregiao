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

    body { background: var(--cream); font-family: 'Montserrat', sans-serif; color: var(--graphite); }

    /* ════════════════════════
       HERO
    ════════════════════════ */
    .par-hero {
      background: var(--green-dk);
      padding: 140px 0 90px;
      position: relative;
      overflow: hidden;
    }
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
    .hero-headline em { font-style: normal; color: var(--gold); }
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
       SEÇÕES DE PARCEIROS
    ════════════════════════ */

    /* Cada seção ocupa 100% da largura */
    .partner-section {
      width: 100%;
      display: flex;
      align-items: stretch;
      min-height: 480px;
      border-bottom: 1px solid var(--border);
      overflow: hidden;
    }

    /* Seções pares: cream. Seções ímpares: offwhite */
    .partner-section:nth-child(odd)  { background: var(--cream); }
    .partner-section:nth-child(even) { background: var(--offwhite); }

    /* Coluna da imagem */
    .ps-img-col {
      width: 45%;
      flex-shrink: 0;
      position: relative;
      overflow: hidden;
    }
    .ps-img-col img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform .6s ease;
    }
    .partner-section:hover .ps-img-col img {
      transform: scale(1.03);
    }

    /* Número grande decorativo sobre a imagem */
    .ps-img-num {
      position: absolute;
      bottom: 20px;
      right: 24px;
      font-size: 96px;
      font-weight: 900;
      line-height: 1;
      letter-spacing: -0.04em;
      color: #fff;
      opacity: .14;
      user-select: none;
      pointer-events: none;
    }

    /* Barra vertical colorida entre imagem e texto */
    .ps-accent-bar {
      width: 5px;
      flex-shrink: 0;
    }

    /* Coluna do conteúdo */
    .ps-content {
      flex: 1;
      padding: 64px 56px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 12px;
    }

    /* Layout zigue-zague: seções pares invertem */
    .partner-section.reverse { flex-direction: row-reverse; }

    /* Eyebrow */
    .ps-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      font-size: 9px;
      font-weight: 900;
      letter-spacing: .22em;
      text-transform: uppercase;
      margin-bottom: 2px;
    }

    /* Nome */
    .ps-name {
      font-size: clamp(28px, 3.5vw, 42px);
      font-weight: 800;
      color: var(--green-dk);
      line-height: 1.05;
      margin: 0;
    }

    /* Subtítulo */
    .ps-role {
      font-size: var(--fs-xs);
      font-weight: 400;
      color: var(--warm);
      margin: 0;
    }

    /* Divisor decorativo */
    .ps-divider-line {
      width: 40px;
      height: 2px;
      border-radius: 99px;
      margin: 4px 0;
    }

    /* Descrição */
    .ps-desc {
      font-size: var(--fs-sm);
      font-weight: 300;
      color: var(--graphite);
      line-height: 1.8;
      max-width: 460px;
      margin: 0;
    }

    /* Pills de serviços */
    .ps-pills {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-top: 4px;
    }
    .ps-pill {
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .05em;
      padding: 5px 14px;
      border-radius: 999px;
    }

    /* Botões */
    .ps-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 8px;
    }
    .ps-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 14px 28px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: .08em;
      text-transform: uppercase;
      text-decoration: none;
      transition: background .2s, transform .15s, border-color .2s;
      border: none;
      cursor: pointer;
    }
    .ps-btn:hover { transform: translateY(-2px); }
    .ps-btn-ghost {
      background: transparent;
      border: 2px solid rgba(61,71,51,.18) !important;
      color: var(--green-dk) !important;
    }
    .ps-btn-ghost:hover {
      border-color: var(--gold) !important;
      background: var(--gold-pale) !important;
    }
    .ps-btn svg { transition: transform .2s; }
    .ps-btn:hover svg { transform: translateX(3px); }

    /* ── Cores por parceiro ── */

    /* 1 – Banzai (dourado) */
    .ps-banzai .ps-accent-bar { background: var(--gold); }
    .ps-banzai .ps-eyebrow    { color: var(--gold); }
    .ps-banzai .ps-divider-line { background: var(--gold); }
    .ps-banzai .ps-pill {
      background: rgba(201,170,107,.12);
      color: #7a5c20;
      border: 1px solid rgba(201,170,107,.35);
    }
    .ps-banzai .ps-btn-main {
      background: var(--green-dk);
      color: #fff;
    }
    .ps-banzai .ps-btn-main:hover { background: var(--green); }

    /* 2 – Maioli (verde escuro) */
    .ps-maioli .ps-accent-bar { background: var(--green); }
    .ps-maioli .ps-eyebrow    { color: var(--green); }
    .ps-maioli .ps-divider-line { background: var(--green); }
    .ps-maioli .ps-pill {
      background: rgba(61,71,51,.08);
      color: var(--green-dk);
      border: 1px solid rgba(61,71,51,.2);
    }
    .ps-maioli .ps-btn-main {
      background: var(--green-dk);
      color: #fff;
    }
    .ps-maioli .ps-btn-main:hover { background: var(--green); }

    /* 3 – Massago (dourado mais quente) */
    .ps-massago .ps-accent-bar { background: #b8934a; }
    .ps-massago .ps-eyebrow    { color: #b8934a; }
    .ps-massago .ps-divider-line { background: #b8934a; }
    .ps-massago .ps-pill {
      background: rgba(184,147,74,.1);
      color: #7a5c20;
      border: 1px solid rgba(184,147,74,.3);
    }
    .ps-massago .ps-btn-main {
      background: var(--green-dk);
      color: #fff;
    }
    .ps-massago .ps-btn-main:hover { background: var(--green); }

    /* 4 – Tato (verde) */
    .ps-tato .ps-accent-bar { background: var(--green); }
    .ps-tato .ps-eyebrow    { color: var(--green); }
    .ps-tato .ps-divider-line { background: var(--green); }
    .ps-tato .ps-pill {
      background: rgba(61,71,51,.08);
      color: var(--green-dk);
      border: 1px solid rgba(61,71,51,.2);
    }
    .ps-tato .ps-btn-main {
      background: var(--green-dk);
      color: #fff;
    }
    .ps-tato .ps-btn-main:hover { background: var(--green); }

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
      font-size: clamp(26px, 3.5vw, 42px);
      font-weight: 800;
      line-height: 1.15;
      margin: 0 0 16px;
    }
    .section-sub {
      font-size: var(--fs-base);
      font-weight: 300;
      line-height: 1.75;
      max-width: 580px;
    }

    .steps-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 28px;
      margin-top: 52px;
    }
    .step-card {
      background: rgba(255,255,255,.04);
      border: 1px solid rgba(255,255,255,.08);
      border-radius: var(--radius);
      padding: 32px 28px;
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
       RODAPÉ DE PÁGINA
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
    .par-footer-quote em { font-style: normal; color: var(--gold); }
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

    /* ════════════════════════
       RESPONSIVE
    ════════════════════════ */
    @media (max-width: 900px) {
      .partner-section,
      .partner-section.reverse {
        flex-direction: column !important;
      }
      .ps-img-col {
        width: 100%;
        height: 280px;
      }
      .ps-accent-bar {
        width: 100%;
        height: 4px;
      }
      .ps-content {
        padding: 40px 28px;
      }
      .ps-img-num { font-size: 64px; }
      .steps-row { grid-template-columns: 1fr; }
      .hero-stat-row { gap: 24px; flex-wrap: wrap; }
      .par-hero { padding: 120px 0 64px; }
    }
    @media (max-width: 767px) {
      :root { --fs-base: 16px; --fs-sm: 14px; }
      .ps-content { padding: 32px 20px; }
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

        <div class="hero-stat-row">
          <div>
            <div class="hero-stat-num">4</div>
            <div class="hero-stat-lbl">Parceiros</div>
          </div>
          <div>
            <div class="hero-stat-num">100%</div>
            <div class="hero-stat-lbl">Indicados</div>
          </div>
          <div>
            <div class="hero-stat-num">1</div>
            <div class="hero-stat-lbl">Por frente</div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     SEÇÕES DE PARCEIROS
══════════════════════════════════════════ -->
<div id="parceiros">

  <!-- ── 01: BANZAI — Marketing ── -->
  <section class="partner-section ps-banzai" id="marketing" aria-label="Banzai - Marketing">

    <div class="ps-img-col">
      <!-- Troque o src pela imagem real da Banzai -->
      <img src="/assets/img/lugares/lugares/6/img_69c6ce713ed304.48263646.jpg" alt="Banzai — Branding e Marketing Digital" loading="lazy">
      <div class="ps-img-num">01</div>
    </div>

    <div class="ps-accent-bar"></div>

    <div class="ps-content">
      <span class="ps-eyebrow">✦ Seu Marketing</span>
      <h2 class="ps-name">Banzai</h2>
      <p class="ps-role">Branding, Design e Marketing Digital</p>
      <div class="ps-divider-line"></div>
      <p class="ps-desc">
        Marca fraca é receita que fica na mesa. A Banzai trabalha branding, naming, design gráfico e marketing digital — o conjunto que faz um negócio ser reconhecido antes mesmo de ser experimentado. Se você quer que as pessoas lembrem do seu nome, saibam o que você representa e confiem antes de entrar em contato, é aqui que começa.
      </p>
      <div class="ps-pills">
        <span class="ps-pill">Identidade visual e posicionamento</span>
        <span class="ps-pill">Design gráfico e digital</span>
        <span class="ps-pill">Marketing digital</span>
      </div>
      <div class="ps-actions">
        <a href="https://banzaibmkt.com.br/" target="_blank" rel="noopener" class="ps-btn ps-btn-main">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
          Acessar site
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
        <a href="https://api.whatsapp.com/send?phone=5511983558500" target="_blank" rel="noopener" class="ps-btn ps-btn-ghost">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          WhatsApp
        </a>
      </div>
    </div>

  </section>


  <!-- ── 02: MAIOLI DESIGN — Site ── -->
  <section class="partner-section ps-maioli reverse" id="site" aria-label="Maioli Design - Sites">

    <div class="ps-img-col">
      <!-- Troque o src pela imagem real da Maioli -->
      <img src="/assets/img/lugares/lugares/4/img_69c42054e5f810.24458299.jpg" alt="Maioli Design — Criação de Sites" loading="lazy">
      <div class="ps-img-num">02</div>
    </div>

    <div class="ps-accent-bar"></div>

    <div class="ps-content">
      <span class="ps-eyebrow">✦ Seu Site e Sistema</span>
      <h2 class="ps-name">Maioli Design</h2>
      <p class="ps-role">Criação de Sites e Sistemas</p>
      <div class="ps-divider-line"></div>
      <p class="ps-desc">
        Um site feio ou inexistente fala antes de você falar. A Maioli Design cria sites profissionais com foco em qualidade, resultado e presença online — atendendo negócios de diferentes segmentos com soluções sob medida. Além da criação, oferece manutenção, hospedagem, otimização de SEO e UI/UX. O mesmo estúdio que desenvolveu este Guia.
      </p>
      <div class="ps-pills">
        <span class="ps-pill">Criação, manutenção e hospedagem</span>
        <span class="ps-pill">SEO para aparecer no Google</span>
        <span class="ps-pill">UI/UX focado em conversão</span>
      </div>
      <div class="ps-actions">
        <a href="https://maiolidesign.com.br/" target="_blank" rel="noopener" class="ps-btn ps-btn-main">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
          Acessar site
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
        <a href="https://api.whatsapp.com/send?phone=5511978348787&text=Olá,%20vim%20pelo%20Guia%20Campo%20Belo%20e%20gostaria%20de%20orçamento%20de%20site!" target="_blank" rel="noopener" class="ps-btn ps-btn-ghost">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          WhatsApp
        </a>
      </div>
    </div>

  </section>


  <!-- ── 03: MASSAGO — Vídeo ── -->
  <section class="partner-section ps-massago" id="video" aria-label="Massago.rec - Audiovisual">

    <div class="ps-img-col">
      <!-- Troque o src pela imagem real do Massago -->
      <img src="/assets/img/parceiros/massago.jpg" alt="Massago.rec — Produção Audiovisual" loading="lazy">
      <div class="ps-img-num">03</div>
    </div>

    <div class="ps-accent-bar"></div>

    <div class="ps-content">
      <span class="ps-eyebrow">✦ Seu Vídeo</span>
      <h2 class="ps-name">Massago Produções</h2>
      <p class="ps-role">Produção Audiovisual</p>
      <div class="ps-divider-line"></div>
      <p class="ps-desc">
        Vídeo parado é dinheiro parado. Guilherme Massago é realizador audiovisual com atuação em direção de fotografia, operação de câmera, montagem e colorização. Produção com olhar apurado, resultado que representa o nível real do seu negócio — e não uma versão amadora dele.
      </p>
      <div class="ps-pills">
        <span class="ps-pill">Direção de fotografia</span>
        <span class="ps-pill">Montagem e colorização</span>
        <span class="ps-pill">Conteúdo para redes e apresentações</span>
      </div>
      <div class="ps-actions">
        <a href="https://massagorec.myportfolio.com/home" target="_blank" rel="noopener" class="ps-btn ps-btn-main">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          Ver portfólio
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
      </div>
    </div>

  </section>


  <!-- ── 04: TATO OFFICE — Escritório ── -->
  <section class="partner-section ps-tato reverse" id="escritorio" aria-label="Tato Office Boutique - Coworking">

    <div class="ps-img-col">
      <!-- Troque o src pela imagem real do Tato Office -->
      <img src="/assets/img/lugares/lugares/3/img_69bf41ccabf5b6.05978289.webp" alt="Tato Office Boutique — Coworking Premium" loading="lazy">
      <div class="ps-img-num">04</div>
    </div>

    <div class="ps-accent-bar"></div>

    <div class="ps-content">
      <span class="ps-eyebrow">✦ Seu Escritório</span>
      <h2 class="ps-name">Tato Office Boutique</h2>
      <p class="ps-role">Ambiente de Sucesso — Coworking Premium</p>
      <div class="ps-divider-line"></div>
      <p class="ps-desc">
        O ambiente onde você trabalha comunica quem você é — para clientes, parceiros e para você mesmo. O Tato Office Boutique é um espaço de coworking com conceito boutique: presença, exclusividade e o cenário certo para quem quer elevar o nível das reuniões, atendimentos e do próprio trabalho do dia a dia.
      </p>
      <div class="ps-pills">
        <span class="ps-pill">Coworking premium</span>
        <span class="ps-pill">Salas de reunião</span>
        <span class="ps-pill">Endereço comercial</span>
      </div>
      <div class="ps-actions">
        <a href="https://tatocoworking.com.br" target="_blank" rel="noopener" class="ps-btn ps-btn-main">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
          Conhecer espaço
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
        <a href="#" target="_blank" rel="noopener" class="ps-btn ps-btn-ghost">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          WhatsApp
        </a>
      </div>
    </div>

  </section>

</div><!-- /#parceiros -->


<!-- ══════════════════════════════════════════
     COMO ESCOLHER
══════════════════════════════════════════ -->
<section class="como-section">
  <div class="container">

    <span class="eyebrow-par">Por onde começar?</span>
    <h2 class="section-title" style="color:#fff">Simples assim.</h2>
    <p class="section-sub" style="color:rgba(255,255,255,.45)">Não precisa resolver tudo de uma vez. Escolha a frente mais urgente e comece por ela.</p>

    <div class="steps-row">

      <div class="step-card">
        <div class="step-num">01</div>
        <div class="step-pill">Diagnóstico</div>
        <h3 class="step-title">Identifique o gargalo.</h3>
        <p class="step-desc">Marca fraca? Site inexistente? Sem vídeo? Sem espaço profissional? Escolha a frente que está travando seu negócio agora.</p>
      </div>

      <div class="step-card">
        <div class="step-num">02</div>
        <div class="step-pill">Contato</div>
        <h3 class="step-title">Fale com o parceiro certo.</h3>
        <p class="step-desc">Cada um foi escolhido a dedo — você não vai precisar explicar do zero o que quer.</p>
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