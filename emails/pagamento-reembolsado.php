<?php
/**
 * emails/pagamento-reembolsado.php
 * Disparado quando um pagamento é reembolsado
 *
 * Variáveis: $nome, $plano, $valor
 */
if (!defined('SITE_URL')) require_once __DIR__ . '/../config/mail.php';

$email_titulo  = 'Reembolso processado — ' . SITE_NAME;
$email_preview = 'Seu reembolso foi processado. Seu plano voltou para o Essencial.';

ob_start(); ?>
<h1>Reembolso processado</h1>
<p>Olá, <strong><?= htmlspecialchars($nome) ?></strong>!</p>
<p>O reembolso referente ao seu plano <strong><?= ucfirst($plano) ?></strong> foi processado com sucesso.</p>

<div class="highlight">
  <p><strong>Valor reembolsado:</strong> R$ <?= number_format($valor, 2, ',', '.') ?></p>
  <p style="margin-top:8px;font-size:13px;color:#5c5558">
    O prazo para o valor aparecer na sua fatura pode variar conforme a operadora do cartão.
  </p>
</div>

<p>Seu perfil continua ativo no <strong><?= SITE_NAME ?></strong> com o plano <strong>Essencial</strong> gratuitamente.</p>

<div class="btn-wrap">
  <a href="<?= SITE_URL ?>/empresa/plano.php" class="btn btn-green">
    Ver planos disponíveis
  </a>
</div>

<p style="font-size:13px;color:#a09898">
  Se você não solicitou este reembolso, entre em contato conosco imediatamente.
</p>
<?php
$email_corpo = ob_get_clean();
include __DIR__ . '/layout.php';