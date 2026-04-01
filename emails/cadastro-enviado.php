<?php
/**
 * E-mail #2 — Cadastro enviado para análise (empresa)
 * Vars: $nome, $nome_empresa
 */
require_once __DIR__ . '/../config/mail.php';

// Defaults
$nome = $nome ?? '';
$nome_empresa = $nome_empresa ?? '';

$email_titulo  = 'Cadastro recebido — em análise';
$email_preview = 'Recebemos seu cadastro. Nossa equipe vai revisar em até 2 dias úteis.';
$email_corpo   = '
<h1>Cadastro recebido! ⏳</h1>
<p>Olá, <strong>' . htmlspecialchars($nome) . '</strong>. Recebemos o cadastro de <strong>' . htmlspecialchars($nome_empresa) . '</strong> e já está em fila para análise.</p>
<div class="highlight">
  <p><span class="status-badge badge-pending">Em análise</span></p>
  <p style="margin-top:10px">Nossa equipe revisa todos os cadastros em até <strong>2 dias úteis</strong>.</p>
</div>
<p>Você receberá um e-mail assim que seu cadastro for aprovado. Enquanto isso, pode acompanhar o status pelo painel.</p>
<div class="btn-wrap">
  <a href="' . SITE_URL . '/empresa/status.php" class="btn">
    Ver status do cadastro
  </a>
</div>
';

include __DIR__ . '/layout.php';