<?php
/**
 * pages/evento-reenvio-qrcode.php
 * Permite ao participante solicitar reenvio do QR Code
 */
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Mailer.php';
require_once __DIR__ . '/../config/asaas.php';
require_once __DIR__ . '/../vendor/autoload.php';

session_start();

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

$page_title = 'Reenviar ingresso — Guia Connect';
$mensagem   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = ['tipo' => 'erro', 'texto' => 'Digite um e-mail válido.'];
    } else {

        // ── Regra: máx 1 reenvio a cada 5 minutos por e-mail ─────────
        $chave_sessao = 'reenvio_' . md5($email);
        $ultimo       = $_SESSION[$chave_sessao] ?? 0;
        $segundos     = time() - $ultimo;

        if ($segundos < 300) {
            $restam   = 300 - $segundos;
            $minutos  = ceil($restam / 60);
            $mensagem = ['tipo' => 'erro',
                'texto' => "Você já solicitou um reenvio recentemente. Aguarde {$minutos} minuto(s) e tente novamente."];
        } else {

            // ── Busca inscrição confirmada ────────────────────────────
            $inscricao = DB::row(
                'SELECT * FROM evento_inscricoes
                 WHERE email = ?
                 AND evento_id = "guia-connect-soft-opening-mai25"
                 AND status = "confirmado"
                 ORDER BY criado_em DESC LIMIT 1',
                [$email]
            );

            if (!$inscricao) {
                // Não revela se o e-mail existe ou não (segurança)
                $mensagem = ['tipo' => 'ok',
                    'texto' => 'Se este e-mail estiver cadastrado, você receberá o ingresso em breve.'];
            } else {
                // ── Regenera QR Code se não existir ──────────────────
                $token      = $inscricao['token'];
                $qrFilename = 'qr_' . $token . '.png';
                $qrPath     = __DIR__ . '/../uploads/qrcodes/' . $qrFilename;
                $qrUrl      = SITE_URL . '/uploads/qrcodes/' . $qrFilename;

                if (!file_exists($qrPath)) {
                    try {
                        $qrCode = new QrCode(
                            data                : "GUIACONNECT|{$token}|{$email}",
                            encoding            : new Encoding('UTF-8'),
                            errorCorrectionLevel: ErrorCorrectionLevel::High,
                            size                : 300,
                            margin              : 20,
                            foregroundColor     : new Color(42, 48, 34),
                            backgroundColor     : new Color(255, 255, 255)
                        );
                        $writer = new PngWriter();
                        file_put_contents($qrPath, $writer->write($qrCode)->getString());
                    } catch (Exception $e) {
                        error_log('[reenvio-qrcode] gerar QR: ' . $e->getMessage());
                    }
                }

                // ── Envia e-mail ──────────────────────────────────────
                $nome_cliente  = $inscricao['nome'];
                $email_cliente = $inscricao['email'];

                try {
                    ob_start();
                    include __DIR__ . '/../emails/evento-ingresso.php';
                    $html = ob_get_clean();
                    Mailer::send(
                        $email_cliente,
                        $nome_cliente,
                        '🎟️ Seu ingresso — Guia Connect Soft Opening',
                        $html
                    );
                } catch (Exception $e) {
                    error_log('[reenvio-qrcode] e-mail: ' . $e->getMessage());
                }

                // Registra timestamp na sessão
                $_SESSION[$chave_sessao] = time();

                $mensagem = ['tipo' => 'ok',
                    'texto' => 'Se este e-mail estiver cadastrado, você receberá o ingresso em breve.'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
  <style>
    body { background: var(--gcb-offwhite); }

    .reenvio-wrap {
      min-height: 80vh;
      display: flex; align-items: center; justify-content: center;
      padding: 40px 20px;
    }
    .reenvio-card {
      background: #fff; border-radius: 20px; padding: 44px 36px;
      max-width: 480px; width: 100%;
      box-shadow: 0 4px 28px rgba(29,29,27,.08);
      text-align: center;
    }

    /* Ícone topo */
    .reenvio-icon {
      width: 64px; height: 64px; border-radius: 50%;
      background: var(--gcb-gold-pale);
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 20px;
    }

    .reenvio-card h1 {
      font-size: 20px; font-weight: 800;
      color: var(--gcb-green-dark); margin-bottom: 8px;
    }
    .reenvio-card p.sub {
      font-size: 13px; font-weight: 300;
      color: var(--gcb-warmgray); line-height: 1.7; margin-bottom: 28px;
    }

    /* Evento badge */
    .evento-badge {
      background: var(--gcb-green-dark); border-radius: 10px;
      padding: 12px 16px; margin-bottom: 24px; text-align: left;
      display: flex; align-items: center; gap: 12px;
    }
    .evento-badge-icon {
      width: 36px; height: 36px; border-radius: 8px;
      background: rgba(201,170,107,.15); border: 1px solid rgba(201,170,107,.25);
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .evento-badge-info p:first-child {
      font-size: 13px; font-weight: 700; color: #fff; margin: 0;
    }
    .evento-badge-info p:last-child {
      font-size: 10px; font-weight: 300; color: rgba(255,255,255,.5); margin: 2px 0 0;
    }

    /* Form */
    .form-label {
      font-size: 10px; font-weight: 700; letter-spacing: .08em;
      text-transform: uppercase; color: var(--gcb-green-dark);
      margin-bottom: 6px; display: block; text-align: left;
    }
    .form-control {
      width: 100%; border: 1.5px solid rgba(61,71,51,.14);
      border-radius: 10px; font-family: 'Montserrat', sans-serif;
      font-size: 13px; padding: 11px 14px; background: var(--gcb-cream);
      transition: border-color .18s, box-shadow .18s; outline: none;
      margin-bottom: 14px;
    }
    .form-control:focus {
      border-color: var(--gcb-green);
      box-shadow: 0 0 0 3px rgba(61,71,51,.07);
      background: #fff;
    }

    .btn-reenviar {
      width: 100%; padding: 13px 24px;
      background: var(--gcb-green-dark); color: #fff;
      border: none; border-radius: 11px;
      font-family: 'Montserrat', sans-serif;
      font-size: 13px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
      cursor: pointer; transition: background .18s;
      display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-reenviar:hover { background: var(--gcb-green); }
    .btn-reenviar:disabled { opacity: .55; cursor: not-allowed; }

    /* Alertas */
    .alert {
      border-radius: 10px; padding: 12px 16px;
      font-size: 13px; font-weight: 600; margin-bottom: 20px;
      display: flex; align-items: flex-start; gap: 9px; text-align: left;
    }
    .alert svg { flex-shrink: 0; margin-top: 1px; }
    .alert-ok   { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert-erro { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

    .voltar {
      display: inline-flex; align-items: center; gap: 6px;
      margin-top: 20px; font-size: 12px; font-weight: 600;
      color: var(--gcb-warmgray); text-decoration: none;
    }
    .voltar:hover { color: var(--gcb-green-dark); }

    @media (max-width: 520px) {
      .reenvio-card { padding: 28px 20px; }
    }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>

<?php include __DIR__ . '/../includes/search-modal.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="reenvio-wrap">
  <div class="reenvio-card">

    <div class="reenvio-icon">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
           stroke="var(--gcb-gold)" stroke-width="2" stroke-linecap="round">
        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
        <polyline points="22,6 12,13 2,6"/>
      </svg>
    </div>

    <h1>Evento encerrado!</h1>
    <!--<p class="sub">-->
    <!--  Perdeu o e-mail com o QR Code? Digite seu e-mail abaixo e reenviaremos o ingresso.-->
    <!--</p>-->

    <!-- Evento -->
    <div class="evento-badge">
      <div class="evento-badge-icon">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
             stroke="var(--gcb-gold)" stroke-width="2" stroke-linecap="round">
          <rect x="3" y="4" width="18" height="18" rx="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8"  y1="2" x2="8"  y2="6"/>
          <line x1="3"  y1="10" x2="21" y2="10"/>
        </svg>
      </div>
      <div class="evento-badge-info">
        <p>Guia Connect — Soft Opening</p>
        <p>19 de maio · Cris Parilla · Campo Belo</p>
      </div>
    </div>

    <!-- Mensagem -->
    <?php if ($mensagem): ?>
    <div class="alert alert-<?= $mensagem['tipo'] ?>">
      <?php if ($mensagem['tipo'] === 'ok'): ?>
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
      <?php else: ?>
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?php endif; ?>
      <?= htmlspecialchars($mensagem['texto']) ?>
    </div>
    <?php endif; ?>

    <!-- Formulário -->
    <!--<?php if (!$mensagem || $mensagem['tipo'] === 'erro'): ?>-->
    <!--<form method="POST" id="form-reenvio">-->
    <!--  <label class="form-label" for="email">-->
    <!--    Seu e-mail <span style="color:#ef4444">*</span>-->
    <!--  </label>-->
    <!--  <input type="email" class="form-control" id="email" name="email"-->
    <!--         placeholder="seu@email.com.br" required autocomplete="email"-->
    <!--         value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/>-->
    <!--  <button type="submit" class="btn-reenviar" id="btn-reenviar">-->
    <!--    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"-->
    <!--         stroke-width="2.5" stroke-linecap="round">-->
    <!--      <line x1="22" y1="2" x2="11" y2="13"/>-->
    <!--      <polygon points="22 2 15 22 11 13 2 9 22 2"/>-->
    <!--    </svg>-->
    <!--    Reenviar meu ingresso-->
    <!--  </button>-->
    <!--</form>-->
    <!--<?php endif; ?>-->

    <a href="/pages/evento-cadastro" class="voltar">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2.5" stroke-linecap="round">
        <polyline points="15 18 9 12 15 6"/>
      </svg>
      Voltar para o evento
    </a>

  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
document.getElementById('form-reenvio')?.addEventListener('submit', function () {
  const btn = document.getElementById('btn-reenviar');
  btn.disabled = true;
  btn.innerHTML = `
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2.5" stroke-linecap="round"
         style="animation:spin .8s linear infinite">
      <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
    </svg>
    Enviando…`;
});
</script>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>

</body>
</html>