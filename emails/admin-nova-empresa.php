<?php
/**
 * E-mail #6 — Nova empresa pendente (admin)
 * Vars: $nome_usuario, $email_usuario, $nome_empresa, $plano, $empresa_id
 */
require_once __DIR__ . '/../config/mail.php';

// Defaults
$nome_usuario = $nome_usuario ?? '';
$email_usuario = $email_usuario ?? '';
$nome_empresa = $nome_empresa ?? '';
$plano = $plano ?? 'essencial';
$empresa_id = $empresa_id ?? 0;

$plano_label = ['essencial'=>'Essencial','profissional'=>'Profissional','premium'=>'Premium'][$plano ?? 'essencial'] ?? 'Essencial';
$url_admin   = SITE_URL . '/admin/empresas/index.php';

$email_titulo  = 'Nova empresa aguardando aprovação';
$email_preview = htmlspecialchars($nome_empresa) . ' acabou de enviar o cadastro para análise.';
$email_corpo   = '
<h1>Nova empresa para aprovar</h1>
<p>Uma nova empresa acaba de enviar o cadastro e está aguardando sua aprovação.</p>
<div class="highlight">
  <p><strong>Empresa:</strong> ' . htmlspecialchars($nome_empresa) . '</p>
  <p><strong>Responsável:</strong> ' . htmlspecialchars($nome_usuario) . '</p>
  <p><strong>E-mail:</strong> ' . htmlspecialchars($email_usuario) . '</p>
  <p><strong>Plano solicitado:</strong> <span class="status-badge badge-plan">' . $plano_label . '</span></p>
</div>
<div class="btn-wrap">
  <a href="' . $url_admin . '" class="btn btn-green">
    Analisar no painel admin
  </a>
</div>
';

include __DIR__ . '/layout.php';