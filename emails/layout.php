<?php
/**
 * emails/layout.php
 * Template HTML base para todos os e-mails
 *
 * Uso:
 *   ob_start();
 *   include __DIR__ . '/nome-do-email.php';
 *   $html = ob_get_clean();
 *
 * Variáveis esperadas: $email_titulo, $email_preview, $email_corpo
 */

$email_titulo  = $email_titulo  ?? SITE_NAME;
$email_preview = $email_preview ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<meta name="x-apple-disable-message-reformatting"/>
<title><?= htmlspecialchars($email_titulo) ?></title>
<!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: #f5f3ee; font-family: -apple-system, 'Segoe UI', Arial, sans-serif; }
  .wrap  { max-width: 600px; margin: 0 auto; padding: 32px 16px 48px; }
  .card  { background: #ffffff; border-radius: 16px; overflow: hidden;
           box-shadow: 0 2px 16px rgba(0,0,0,.07); }
  .header{ background: #2a3022; padding: 28px 32px; text-align: center; }
  .header img { height: 48px; }
  .body  { padding: 36px 36px 28px; }
  .body h1 { font-size: 22px; font-weight: 700; color: #2a3022; margin-bottom: 12px; line-height: 1.3; }
  .body p  { font-size: 15px; color: #5c5558; line-height: 1.7; margin-bottom: 14px; }
  .body p:last-child { margin-bottom: 0; }
  .highlight { background: #f5f3ee; border-radius: 10px; padding: 16px 20px; margin: 20px 0; }
  .highlight p { margin: 0; font-size: 14px; color: #2a3022; }
  .highlight strong { color: #2a3022; }
  .btn-wrap { text-align: center; margin: 28px 0; }
  .btn { display: inline-block; padding: 14px 32px; background: #c9aa6b;
         color: #2a3022 !important; font-weight: 700; font-size: 13px;
         letter-spacing: .06em; text-transform: uppercase; text-decoration: none;
         border-radius: 999px; }
  .btn-green { background: #2a3022; color: #fff !important; }
  .divider { height: 1px; background: #ede9e0; margin: 24px 0; }
  .footer { padding: 20px 32px 28px; text-align: center; }
  .footer p { font-size: 12px; color: #a09898; line-height: 1.6; }
  .footer a { color: #c9aa6b; text-decoration: none; }
  .status-badge { display: inline-block; padding: 6px 16px; border-radius: 999px;
                  font-size: 12px; font-weight: 700; letter-spacing: .08em;
                  text-transform: uppercase; }
  .badge-ok      { background: #ecfdf5; color: #065f46; }
  .badge-pending { background: #fef3c7; color: #92400e; }
  .badge-error   { background: #fef2f2; color: #991b1b; }
  .badge-plan    { background: #f5f3ee; color: #2a3022; }
  @media(max-width:600px) { .body { padding: 24px 20px 20px; } }
</style>
</head>
<body>
<!-- Preheader oculto -->
<div style="display:none;max-height:0;overflow:hidden;mso-hide:all">
  <?= htmlspecialchars($email_preview) ?>
  &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
</div>

<div class="wrap">
  <div class="card">

    <!-- Header -->
    <div class="header">
      <img src="<?= SITE_URL ?>/assets/img/logo.png" alt="<?= SITE_NAME ?>"/>
    </div>

    <!-- Corpo -->
    <div class="body">
      <?= $email_corpo ?>
    </div>

    <!-- Rodapé -->
    <div class="footer">
      <div class="divider"></div>
      <p>
        Você está recebendo este e-mail porque tem uma conta no
        <a href="<?= SITE_URL ?>"><?= SITE_NAME ?></a>.<br/>
        <!--Dúvidas? Fale conosco em-->
        <!--<a href="mailto:<?//= ADMIN_EMAIL ?>"><?//= ADMIN_EMAIL ?></a>-->
      </p>
    </div>

  </div>
</div>
</body>
</html>