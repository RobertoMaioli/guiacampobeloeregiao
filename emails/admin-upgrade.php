<?php
/**
 * E-mail #7 — Upgrade de plano solicitado (admin)
 * Vars: $nome_usuario, $email_usuario, $nome_empresa, $plano_atual, $plano_solicitado, $mensagem
 */
require_once __DIR__ . '/../config/mail.php';

// Defaults
$nome_usuario = $nome_usuario ?? '';
$email_usuario = $email_usuario ?? '';
$nome_empresa = $nome_empresa ?? '';
$plano_atual = $plano_atual ?? '';
$plano_solicitado = $plano_solicitado ?? '';
$mensagem = $mensagem ?? '';

$plano_label = fn($p) => ['essencial'=>'Essencial','profissional'=>'Profissional','premium'=>'Premium'][$p] ?? ucfirst($p);

$email_titulo  = 'Solicitação de upgrade de plano';
$email_preview = htmlspecialchars($nome_empresa) . ' quer fazer upgrade para ' . $plano_label($plano_solicitado ?? '');
$email_corpo   = '
<h1>Solicitação de upgrade</h1>
<p>Uma empresa solicitou upgrade de plano e aguarda sua ativação.</p>
<div class="highlight">
  <p><strong>Empresa:</strong> ' . htmlspecialchars($nome_empresa) . '</p>
  <p><strong>Responsável:</strong> ' . htmlspecialchars($nome_usuario) . '</p>
  <p><strong>E-mail:</strong> ' . htmlspecialchars($email_usuario) . '</p>
  <p><strong>Plano atual:</strong> ' . $plano_label($plano_atual ?? '') . '</p>
  <p><strong>Plano solicitado:</strong> <span class="status-badge badge-plan">' . $plano_label($plano_solicitado ?? '') . '</span></p>
  ' . ($mensagem ? '<p><strong>Mensagem:</strong> ' . nl2br(htmlspecialchars($mensagem)) . '</p>' : '') . '
</div>
<div class="btn-wrap">
  <a href="' . SITE_URL . '/admin/empresas/index.php" class="btn btn-green">
    Gerenciar no painel admin
  </a>
</div>
';

include __DIR__ . '/layout.php';