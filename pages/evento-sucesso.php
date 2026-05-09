<?php
session_start();
$page_title = 'Inscrição confirmada — Guia Connect';
$meta_desc  = 'Sua vaga no Guia Connect está garantida!';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
  <style>
    body { background: var(--gcb-offwhite); }

    .sucesso-wrap {
      min-height: 80vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
    }

    .sucesso-card {
      background: #fff;
      border-radius: 24px;
      padding: 48px 40px;
      max-width: 560px;
      width: 100%;
      box-shadow: 0 8px 40px rgba(29,29,27,.08);
      text-align: center;
    }

    .check-circle {
      width: 72px; height: 72px; border-radius: 50%;
      background: #d1fae5;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 24px;
    }

    .sucesso-card h1 {
      font-size: 24px; font-weight: 800;
      color: var(--gcb-green-dark); margin-bottom: 8px;
    }
    .sucesso-card .sub {
      font-size: 14px; font-weight: 300;
      color: var(--gcb-warmgray); line-height: 1.7; margin-bottom: 32px;
    }
    .sucesso-card .sub strong { color: var(--gcb-green-dark); }

    /* Card do evento */
    .evento-card {
      background: var(--gcb-green-dark);
      border-radius: 14px; padding: 18px 20px;
      margin-bottom: 24px; text-align: left;
      display: flex; align-items: center; gap: 16px;
    }
    .evento-card-icon {
      width: 44px; height: 44px; border-radius: 10px;
      background: rgba(201,170,107,.15);
      border: 1px solid rgba(201,170,107,.25);
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .evento-card-info p:first-child {
      font-size: 14px; font-weight: 700; color: #fff; margin: 0;
    }
    .evento-card-info p:last-child {
      font-size: 11px; font-weight: 300;
      color: rgba(255,255,255,.5); margin: 3px 0 0;
    }

    /* Aviso QR */
    .qr-aviso {
      background: var(--gcb-gold-pale);
      border: 1px solid rgba(201,170,107,.3);
      border-radius: 12px; padding: 14px 16px;
      display: flex; align-items: flex-start; gap: 10px;
      font-size: 12.5px; color: var(--gcb-graphite);
      line-height: 1.6; margin-bottom: 24px; text-align: left;
    }
    .qr-aviso strong { color: var(--gcb-green-dark); }

    /* Instruções */
    .instrucoes {
      background: var(--gcb-offwhite);
      border-radius: 16px; padding: 24px;
      text-align: left; margin-bottom: 28px;
    }
    .instrucoes h3 {
      font-size: 13px; font-weight: 800;
      letter-spacing: .08em; text-transform: uppercase;
      color: var(--gcb-green-dark); margin-bottom: 16px;
    }
    .instr-item {
      display: flex; align-items: flex-start; gap: 12px;
      margin-bottom: 14px;
    }
    .instr-item:last-child { margin-bottom: 0; }
    .instr-num {
      width: 24px; height: 24px; border-radius: 50%;
      background: var(--gcb-green-dark); color: var(--gcb-gold);
      font-size: 11px; font-weight: 800;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0; margin-top: 1px;
    }
    .instr-text {
      font-size: 13px; font-weight: 400;
      color: var(--gcb-graphite); line-height: 1.6;
    }
    .instr-text strong { color: var(--gcb-green-dark); }

    .btn-home {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 12px 28px; border-radius: 10px;
      background: var(--gcb-green-dark); color: #fff;
      font-size: 12px; font-weight: 800;
      letter-spacing: .06em; text-transform: uppercase;
      text-decoration: none;
      transition: background .18s;
    }
    .btn-home:hover { background: var(--gcb-green); color: #fff; }

    @media (max-width: 600px) {
      .sucesso-card { padding: 32px 20px; }
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/../includes/search-modal.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="sucesso-wrap">
  <div class="sucesso-card">

    <!-- Ícone de sucesso -->
    <div class="check-circle">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
           stroke="#059669" stroke-width="2.5" stroke-linecap="round">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
    </div>

    <h1>Quase lá! 🎉</h1>
    <p class="sub">
      Seu pedido foi registrado com sucesso.<br>
      <strong>Após a confirmação do pagamento, você receberá um e-mail com o QR Code de acesso ao evento.
    </p>

    <!-- Dados do evento -->
    <div class="evento-card">
      <div class="evento-card-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
             stroke="var(--gcb-gold)" stroke-width="2" stroke-linecap="round">
          <rect x="3" y="4" width="18" height="18" rx="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8"  y1="2" x2="8"  y2="6"/>
          <line x1="3"  y1="10" x2="21" y2="10"/>
        </svg>
      </div>
      <div class="evento-card-info">
        <p>Guia Connect — Soft Opening</p>
        <p>19 de maio · A partir das 19h · Cris Parilla, Campo Belo</p>
      </div>
    </div>

    <!-- Aviso QR Code -->
    <div class="qr-aviso">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
           stroke="var(--gcb-gold)" stroke-width="2" stroke-linecap="round"
           style="flex-shrink:0;margin-top:2px">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8"  x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <span>
          O QR Code será enviado para o seu e-mail <strong>somente após a confirmação do pagamento</strong>.
          Verifique sua caixa de entrada e também o spam.
        </span>
    </div>

    <!-- Instruções -->
    <div class="instrucoes">
      <h3>Como usar no dia</h3>
      <div class="instr-item">
        <div class="instr-num">1</div>
        <div class="instr-text">
          <strong>Abra o e-mail</strong> de confirmação com o QR Code
          (remetente: Guia Campo Belo &amp; Região)
        </div>
      </div>
      <div class="instr-item">
        <div class="instr-num">2</div>
        <div class="instr-text">
          Chegue ao <strong>Cris Parilla</strong> —
          Rua República do Iraque, 1326, Campo Belo
        </div>
      </div>
      <div class="instr-item">
        <div class="instr-num">3</div>
        <div class="instr-text">
          Apresente o <strong>QR Code na entrada</strong> para a equipe escanear
        </div>
      </div>
      <div class="instr-item">
        <div class="instr-num">4</div>
        <div class="instr-text">
          Aproveite a noite! ☕🥂
        </div>
      </div>
    </div>

    <a href="/" class="btn-home">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
        <polyline points="9 22 9 12 15 12 15 22"/>
      </svg>
      Voltar ao Guia
    </a>

  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>