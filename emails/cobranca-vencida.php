<?php
/**
 * emails/cobranca-vencida.php
 * Disparado quando uma cobrança fica em atraso
 *
 * Variáveis: $nome, $plano, $valor, $vencimento
 */
if (!defined('SITE_URL')) require_once __DIR__ . '/../config/mail.php';

$email_titulo  = 'Cobrança vencida — ' . SITE_NAME;
$email_preview = 'Sua cobrança venceu. Regularize para não perder o acesso.';

ob_start(); ?>
<h1>Sua cobrança está em atraso</h1>
<p>Olá, <strong><?= htmlspecialchars($nome) ?></strong>!</p>
<p>Identificamos uma cobrança em atraso referente ao seu plano <strong><?= ucfirst($plano) ?></strong>.</p>

<div class="highlight">
  <p><strong>Plano:</strong> <?= ucfirst($plano) ?></p>
  <p><strong>Valor:</strong> R$ <?= number_format($valor, 2, ',', '.') ?></p>
  <p><strong>Vencimento:</strong> <?= date('d/m/Y', strtotime($vencimento)) ?></p>
</div>

<p>Para regularizar, acesse o link de pagamento que foi enviado pelo Asaas ou entre em contato conosco.</p>

<div class="btn-wrap">
  <a href="<?= SITE_URL ?>/empresa/plano.php" class="btn btn-green">
    Regularizar pagamento
  </a>
</div>

<p style="font-size:13px;color:#a09898">
  Caso o pagamento não seja regularizado, seu plano poderá ser suspenso automaticamente.
</p>
<?php
$email_corpo = ob_get_clean();
include __DIR__ . '/layout.php';