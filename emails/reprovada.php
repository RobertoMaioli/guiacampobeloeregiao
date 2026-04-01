<?php
/**
 * E-mail #4 — Empresa reprovada
 * Vars: $nome, $nome_empresa, $motivo
 */
require_once __DIR__ . '/../config/mail.php';

// Defaults
$nome = $nome ?? '';
$nome_empresa = $nome_empresa ?? '';
$motivo = $motivo ?? '';

$email_titulo  = 'Sobre seu cadastro no ' . SITE_NAME;
$email_preview = 'Precisamos de alguns ajustes no seu cadastro.';
$email_corpo   = '
<h1>Seu cadastro precisa de ajustes</h1>
<p>Olá, <strong>' . htmlspecialchars($nome) . '</strong>. Analisamos o cadastro de <strong>' . htmlspecialchars($nome_empresa) . '</strong> e identificamos alguns pontos que precisam ser corrigidos antes da publicação.</p>
<div class="highlight">
  <p><span class="status-badge badge-error">Ajuste necessário</span></p>
  <p style="margin-top:12px"><strong>Motivo:</strong></p>
  <p>' . nl2br(htmlspecialchars($motivo)) . '</p>
</div>
<p>Corrija os pontos indicados e reenvie seu cadastro pelo painel. Nossa equipe vai revisar novamente em até 2 dias úteis.</p>
<div class="btn-wrap">
  <a href="' . SITE_URL . '/empresa/onboarding.php" class="btn">
    Corrigir e reenviar
  </a>
</div>
<p style="font-size:13px;color:#a09898;text-align:center">Tem dúvidas sobre o motivo? Responda este e-mail e esclarecemos.</p>
';

include __DIR__ . '/layout.php';