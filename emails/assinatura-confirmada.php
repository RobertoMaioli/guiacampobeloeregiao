<?php
/**
 * emails/assinatura-confirmada.php
 * Disparado quando o pagamento é confirmado e o plano ativado
 *
 * Variáveis: $nome, $plano, $valor, $proximo_vencimento
 */
if (!defined('SITE_URL')) require_once __DIR__ . '/../config/mail.php';

$email_titulo  = 'Plano ativado — ' . SITE_NAME;
$email_preview = 'Seu plano ' . ucfirst($plano) . ' está ativo. Aproveite!';

ob_start(); ?>
<h1>Plano ativado com sucesso! 🎉</h1>
<p>Olá, <strong><?= htmlspecialchars($nome) ?></strong>!</p>
<p>Seu pagamento foi confirmado e o plano <strong><?= ucfirst($plano) ?></strong> já está ativo na sua conta.</p>

<div class="highlight">
  <p><strong>Plano:</strong> <?= ucfirst($plano) ?></p>
  <p><strong>Valor:</strong> R$ <?= number_format($valor, 2, ',', '.') ?>/mês</p>
  <p><strong>Próximo vencimento:</strong> <?= date('d/m/Y', strtotime($proximo_vencimento)) ?></p>
</div>

<div class="btn-wrap">
  <a href="<?= SITE_URL ?>/empresa/dashboard.php" class="btn btn-green">
    Acessar meu painel
  </a>
</div>

<p>Aproveite todos os recursos do seu novo plano. Qualquer dúvida estamos à disposição.</p>
<?php
$email_corpo = ob_get_clean();
include __DIR__ . '/layout.php';