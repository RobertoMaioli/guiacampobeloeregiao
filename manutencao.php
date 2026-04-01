<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Em Manutenção — Guia Campo Belo & Região</title>
  <meta name="robots" content="noindex, nofollow" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet" />

  <style>
    /* ── Tokens ─────────────────────────────────── */
    :root {
      --green:     #3d4733;
      --green-dk:  #2a3022;
      --green-lt:  #4f5c40;
      --gold:      #c9aa6b;
      --gold-lt:   #ddc48a;
      --gold-pale: #f5edda;
      --cream:     #faf8f3;
      --graphite:  #1d1d1b;
      --gray:      #8b8589;
    }

    /* ── Reset ───────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; }
    body {
      font-family: 'Montserrat', sans-serif;
      background: var(--green-dk);
      color: var(--cream);
      overflow: hidden;
      -webkit-font-smoothing: antialiased;
    }

    /* ── Background layers ───────────────────────── */
    .bg-layer {
      position: fixed;
      inset: 0;
      z-index: 0;
      pointer-events: none;
    }

    /* Grain texture */
    .bg-grain {
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
      background-size: 200px 200px;
      opacity: .55;
      mix-blend-mode: overlay;
    }

    /* Radial glows */
    .bg-glow-gold {
      background: radial-gradient(ellipse 60% 50% at 15% 80%, rgba(201,170,107,.14) 0%, transparent 70%);
    }
    .bg-glow-green {
      background: radial-gradient(ellipse 55% 60% at 85% 10%, rgba(79,92,64,.35) 0%, transparent 65%);
    }

    /* Animated floating orb */
    .bg-orb {
      width: 600px; height: 600px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(201,170,107,.07) 0%, transparent 70%);
      position: fixed;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      animation: orbFloat 12s ease-in-out infinite;
    }
    @keyframes orbFloat {
      0%,100% { transform: translate(-50%, -50%) scale(1);    opacity: .6; }
      33%      { transform: translate(-44%, -54%) scale(1.08); opacity: .9; }
      66%      { transform: translate(-56%, -47%) scale(.95);  opacity: .7; }
    }

    /* Thin horizontal lines (editorial grid feel) */
    .bg-lines {
      background-image:
        linear-gradient(rgba(201,170,107,.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(201,170,107,.04) 1px, transparent 1px);
      background-size: 80px 80px;
    }

    /* ── Layout ──────────────────────────────────── */
    .stage {
      position: relative;
      z-index: 1;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 48px 24px;
      gap: 0;
    }

    /* ── Logo ────────────────────────────────────── */
    .logo {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 72px;
      opacity: 0;
      animation: fadeUp .7s .1s ease forwards;
    }
    .logo-bubble {
      width: 42px; height: 50px;
      background: var(--gold);
      border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
      display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      gap: 1px; padding: 7px 5px;
    }
    .logo-bubble span {
      font-size: 7.5px; font-weight: 800;
      color: var(--green-dk); line-height: 1; white-space: nowrap;
    }
    .logo-words { display: flex; flex-direction: column; line-height: 1.2; }
    .logo-words strong {
      font-size: 14px; font-weight: 800; color: var(--cream); letter-spacing: .01em;
    }
    .logo-words small {
      font-size: 10px; font-weight: 500; color: var(--gold); letter-spacing: .08em;
    }

    /* ── Card ────────────────────────────────────── */
    .card {
      width: 100%;
      max-width: 560px;
      text-align: center;
    }

    /* ── Icon circle ─────────────────────────────── */
    .icon-ring {
      width: 88px; height: 88px;
      border-radius: 50%;
      border: 1.5px solid rgba(201,170,107,.25);
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 36px;
      position: relative;
      opacity: 0;
      animation: fadeUp .7s .2s ease forwards;
    }
    .icon-ring::before {
      content: '';
      position: absolute; inset: -8px;
      border-radius: 50%;
      border: 1px solid rgba(201,170,107,.1);
    }
    .icon-ring::after {
      content: '';
      position: absolute; inset: -16px;
      border-radius: 50%;
      border: 1px solid rgba(201,170,107,.05);
    }
    .icon-ring svg {
      color: var(--gold);
      animation: gearSpin 12s linear infinite;
    }
    @keyframes gearSpin {
      from { transform: rotate(0deg); }
      to   { transform: rotate(360deg); }
    }

    /* ── Eyebrow ─────────────────────────────────── */
    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 5px 16px;
      border: 1px solid rgba(201,170,107,.3);
      border-radius: 999px;
      background: rgba(201,170,107,.08);
      font-size: 9.5px;
      font-weight: 800;
      letter-spacing: .2em;
      text-transform: uppercase;
      color: var(--gold);
      margin-bottom: 22px;
      opacity: 0;
      animation: fadeUp .7s .3s ease forwards;
    }
    .eyebrow-dot {
      width: 6px; height: 6px;
      border-radius: 50%;
      background: var(--gold);
      animation: blink 1.8s ease-in-out infinite;
    }
    @keyframes blink {
      0%,100% { opacity: 1; }
      50%      { opacity: .2; }
    }

    /* ── Headline ────────────────────────────────── */
    .headline {
      font-family: 'Playfair Display', Georgia, serif;
      font-size: clamp(36px, 6vw, 58px);
      font-weight: 700;
      line-height: 1.1;
      color: var(--cream);
      margin-bottom: 20px;
      opacity: 0;
      animation: fadeUp .7s .4s ease forwards;
    }
    .headline em {
      font-style: italic;
      color: var(--gold);
    }

    /* ── Body text ───────────────────────────────── */
    .body-text {
      font-size: 15px;
      font-weight: 300;
      line-height: 1.75;
      color: rgba(250,248,243,.55);
      max-width: 420px;
      margin: 0 auto 44px;
      opacity: 0;
      animation: fadeUp .7s .5s ease forwards;
    }

    /* ── Progress bar ────────────────────────────── */
    .progress-wrap {
      margin-bottom: 44px;
      opacity: 0;
      animation: fadeUp .7s .6s ease forwards;
    }
    .progress-labels {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      font-size: 11px;
      font-weight: 600;
      color: rgba(250,248,243,.4);
      letter-spacing: .05em;
    }
    .progress-labels span:last-child { color: var(--gold); }
    .progress-track {
      height: 3px;
      background: rgba(201,170,107,.12);
      border-radius: 999px;
      overflow: hidden;
    }
    .progress-fill {
      height: 100%;
      width: 0%;
      background: linear-gradient(90deg, var(--gold) 0%, var(--gold-lt) 100%);
      border-radius: 999px;
      animation: fillBar 2.5s 1.2s cubic-bezier(.4,0,.2,1) forwards;
      position: relative;
    }
    .progress-fill::after {
      content: '';
      position: absolute;
      right: 0; top: 50%;
      transform: translateY(-50%);
      width: 8px; height: 8px;
      border-radius: 50%;
      background: var(--gold-lt);
      box-shadow: 0 0 8px var(--gold);
    }
    @keyframes fillBar {
      from { width: 0%; }
      to   { width: 78%; }
    }

    /* ── Divider ─────────────────────────────────── */
    .divider {
      width: 100%;
      height: 1px;
      background: rgba(201,170,107,.12);
      margin-bottom: 36px;
      opacity: 0;
      animation: fadeUp .7s .65s ease forwards;
    }

    /* ── Notify form ─────────────────────────────── */
    .notify-label {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: rgba(250,248,243,.45);
      margin-bottom: 14px;
      opacity: 0;
      animation: fadeUp .7s .7s ease forwards;
    }
    .notify-form {
      display: flex;
      background: rgba(250,248,243,.06);
      border: 1.5px solid rgba(201,170,107,.2);
      border-radius: 999px;
      overflow: hidden;
      max-width: 420px;
      margin: 0 auto 36px;
      opacity: 0;
      animation: fadeUp .7s .75s ease forwards;
      transition: border-color .25s ease;
    }
    .notify-form:focus-within {
      border-color: rgba(201,170,107,.55);
      box-shadow: 0 0 0 4px rgba(201,170,107,.08);
    }
    .notify-form input {
      flex: 1;
      padding: 14px 20px;
      background: transparent;
      border: none; outline: none;
      font-family: 'Montserrat', sans-serif;
      font-size: 13.5px; color: var(--cream);
    }
    .notify-form input::placeholder { color: rgba(250,248,243,.3); }
    .notify-form button {
      padding: 12px 22px;
      background: var(--gold);
      border: none; cursor: pointer;
      font-family: 'Montserrat', sans-serif;
      font-size: 11px; font-weight: 800;
      letter-spacing: .1em; text-transform: uppercase;
      color: var(--green-dk);
      transition: background .2s ease;
      margin: 3px;
      border-radius: 999px;
    }
    .notify-form button:hover { background: var(--gold-lt); }

    /* Success state */
    .notify-success {
      display: none;
      align-items: center;
      justify-content: center;
      gap: 8px;
      max-width: 420px;
      margin: 0 auto 36px;
      padding: 14px 24px;
      background: rgba(201,170,107,.1);
      border: 1.5px solid rgba(201,170,107,.3);
      border-radius: 999px;
      font-size: 13px;
      font-weight: 600;
      color: var(--gold-lt);
    }

    /* ── Social links ────────────────────────────── */
    .socials {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      opacity: 0;
      animation: fadeUp .7s .85s ease forwards;
    }
    .socials-label {
      font-size: 11px;
      font-weight: 600;
      letter-spacing: .08em;
      color: rgba(250,248,243,.3);
      margin-right: 4px;
    }
    .social-btn {
      width: 36px; height: 36px;
      border-radius: 50%;
      border: 1px solid rgba(201,170,107,.18);
      display: flex; align-items: center; justify-content: center;
      color: rgba(250,248,243,.4);
      cursor: pointer; background: transparent;
      transition: all .2s ease;
      text-decoration: none;
    }
    .social-btn:hover {
      border-color: var(--gold);
      color: var(--gold);
      background: rgba(201,170,107,.08);
      transform: translateY(-2px);
    }

    /* ── Footer strip ────────────────────────────── */
    .footer-strip {
      position: fixed;
      bottom: 0; left: 0; right: 0;
      padding: 16px 40px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-top: 1px solid rgba(201,170,107,.08);
      background: rgba(42,48,34,.6);
      backdrop-filter: blur(20px);
      font-size: 11.5px;
      color: rgba(250,248,243,.25);
      z-index: 10;
      opacity: 0;
      animation: fadeUp .5s 1.1s ease forwards;
    }
    .footer-strip a { color: var(--gold); }
    .footer-strip a:hover { text-decoration: underline; }

    /* ── Countdown ───────────────────────────────── */
    .countdown {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      opacity: 0;
      animation: fadeUp .7s .8s ease forwards;
      margin-bottom: 36px;
    }
    .cd-unit {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 2px;
      min-width: 52px;
    }
    .cd-num {
      font-family: 'Playfair Display', serif;
      font-size: 32px;
      font-weight: 700;
      color: var(--cream);
      line-height: 1;
      letter-spacing: -.02em;
    }
    .cd-label {
      font-size: 9px;
      font-weight: 700;
      letter-spacing: .15em;
      text-transform: uppercase;
      color: rgba(250,248,243,.35);
    }
    .cd-sep {
      font-family: 'Playfair Display', serif;
      font-size: 28px;
      font-weight: 400;
      color: var(--gold);
      line-height: 1;
      margin-bottom: 14px;
      opacity: .6;
    }

    /* ── Animations ──────────────────────────────── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(18px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Responsive ──────────────────────────────── */
    @media (max-width: 600px) {
      .logo { margin-bottom: 48px; }
      .icon-ring { width: 72px; height: 72px; }
      .footer-strip { flex-direction: column; gap: 4px; text-align: center; padding: 12px 20px; }
      .notify-form { flex-direction: column; border-radius: 16px; }
      .notify-form button { margin: 0 4px 4px; border-radius: 12px; }
    }
  </style>
</head>
<body>

  <!-- Background layers -->
  <div class="bg-layer bg-lines"></div>
  <div class="bg-layer bg-grain"></div>
  <div class="bg-layer bg-glow-gold"></div>
  <div class="bg-layer bg-glow-green"></div>
  <div class="bg-orb"></div>

  <!-- Main stage -->
  <main class="stage">

    <!-- Logo -->
    <div class="logo">
      <div class="logo-bubble">
        <span>Guia</span>
        <span>CB&amp;</span>
        <span>Reg.</span>
      </div>
      <div class="logo-words">
        <strong>Guia Campo Belo</strong>
        <small>&amp; Região</small>
      </div>
    </div>

    <div class="card">

      <!-- Gear icon -->
      <div class="icon-ring">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="3"/>
          <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
        </svg>
      </div>

      <!-- Eyebrow -->
      <div class="eyebrow">
        <span class="eyebrow-dot"></span>
        Manutenção em andamento
      </div>

      <!-- Headline -->
      <h1 class="headline">
        Voltamos em<br/><em>breve</em>, promessa.
      </h1>

      <!-- Body -->
      <p class="body-text">
        Estamos aprimorando a experiência do Guia para entregar algo ainda melhor.
        Cada detalhe está sendo cuidado com a mesma dedicação que aplicamos às nossas indicações.
      </p>

      <!-- Progress -->
      <div class="progress-wrap">
        <div class="progress-labels">
          <span>Progresso</span>
          <span id="progress-pct">0%</span>
        </div>
        <div class="progress-track">
          <div class="progress-fill" id="progress-fill"></div>
        </div>
      </div>

      <!-- Countdown -->
      <div class="countdown" id="countdown">
        <div class="cd-unit">
          <span class="cd-num" id="cd-h">00</span>
          <span class="cd-label">Horas</span>
        </div>
        <span class="cd-sep">:</span>
        <div class="cd-unit">
          <span class="cd-num" id="cd-m">00</span>
          <span class="cd-label">Min</span>
        </div>
        <span class="cd-sep">:</span>
        <div class="cd-unit">
          <span class="cd-num" id="cd-s">00</span>
          <span class="cd-label">Seg</span>
        </div>
      </div>

      <div class="divider"></div>

      <!-- Notify -->
      <p class="notify-label">Avise-me quando voltar</p>

      <div class="notify-form" id="notify-form">
        <input type="email" id="notify-email" placeholder="Seu melhor e-mail" />
        <button type="button" onclick="submitNotify()">Avisar</button>
      </div>

      <div class="notify-success" id="notify-success">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        Perfeito! Você será o primeiro a saber.
      </div>

      <!-- Socials -->
      <div class="socials">
        <span class="socials-label">Enquanto isso:</span>

        <a href="https://instagram.com/guiacampobeloeregiao" target="_blank" rel="noopener"
           class="social-btn" aria-label="Instagram">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
            <circle cx="12" cy="12" r="4"/>
            <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>
          </svg>
        </a>

        <a href="https://wa.me/5511999999999" target="_blank" rel="noopener"
           class="social-btn" aria-label="WhatsApp">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
          </svg>
        </a>

        <a href="mailto:contato@guiacampobelo.com.br"
           class="social-btn" aria-label="E-mail">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>
        </a>
      </div>

    </div>
  </main>

  <!-- Footer strip -->
  <div class="footer-strip">
    <span>&copy; <?php echo date('Y'); ?> Guia Campo Belo &amp; Região</span>
    <span>Desenvolvido por <a href="https://banzaibmkt.com.br" target="_blank" rel="noopener">Banzai Branding</a></span>
  </div>

  <script>
    /* ── Countdown — set your target date here ── */
    const TARGET = new Date();
    TARGET.setHours(TARGET.getHours() + 4); // 4h from now — adjust as needed

    function pad(n) { return String(n).padStart(2, '0'); }

    function tick() {
      const diff = Math.max(0, TARGET - Date.now());
      const h = Math.floor(diff / 3_600_000);
      const m = Math.floor((diff % 3_600_000) / 60_000);
      const s = Math.floor((diff % 60_000) / 1_000);

      document.getElementById('cd-h').textContent = pad(h);
      document.getElementById('cd-m').textContent = pad(m);
      document.getElementById('cd-s').textContent = pad(s);

      if (diff <= 0) clearInterval(timer);
    }

    const timer = setInterval(tick, 1000);
    tick();

    /* ── Progress counter ── */
    const fill = document.getElementById('progress-fill');
    const pct  = document.getElementById('progress-pct');
    let current = 0;
    const target = 78;

    setTimeout(() => {
      const counter = setInterval(() => {
        if (current >= target) { clearInterval(counter); return; }
        current++;
        pct.textContent = current + '%';
      }, 2500 / target);
    }, 1200);

    /* ── Notify form ── */
    function submitNotify() {
      const email = document.getElementById('notify-email').value.trim();
      if (!email || !email.includes('@')) {
        document.getElementById('notify-email').style.borderColor = 'rgba(220,80,80,.5)';
        setTimeout(() => document.getElementById('notify-email').style.borderColor = '', 1500);
        return;
      }
      document.getElementById('notify-form').style.display    = 'none';
      document.getElementById('notify-success').style.display = 'flex';
      // TODO: POST to /actions/notify.php
    }

    document.getElementById('notify-email')
      .addEventListener('keydown', e => { if (e.key === 'Enter') submitNotify(); });
  </script>

</body>
</html>
