<?php
/**
 * emails/assinatura-cancelada.php
 * Disparado quando a assinatura é cancelada
 *
 * Variáveis: $nome, $plano
 */
if (!defined('SITE_URL')) require_once __DIR__ . '/../config/mail.php';

$email_titulo  = 'Assinatura cancelada — ' . SITE_NAME;
$email_preview = 'Sua assinatura foi cancelada. Seu plano voltou para o Essencial.';

ob_start(); ?>
<h1>Assinatura cancelada</h1>
<p>Olá, <strong><?= htmlspecialchars($nome) ?></strong>!</p>
<p>Sua assinatura do plano <strong><?= ucfirst($plano) ?></strong> foi cancelada com sucesso.</p>

<div class="highlight">
  <p>Seu perfil continua ativo no <strong><?= SITE_NAME ?></strong> com o plano <strong>Essencial</strong> gratuitamente.</p>
</div>

<p>Se quiser reativar um plano pago a qualquer momento, basta acessar seu painel.</p>

<div class="btn-wrap">
  <a href="<?= SITE_URL ?>/empresa/plano.php" class="btn btn-green">
    Ver planos disponíveis
  </a>
</div>

<p style="font-size:13px;color:#a09898">
  Se você não solicitou este cancelamento, entre em contato conosco imediatamente.
</p>
<?php
$email_corpo = ob_get_clean();
include __DIR__ . '/layout.php';