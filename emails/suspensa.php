<?php
/**
 * E-mail #5 — Empresa suspensa
 * Vars: $nome, $nome_empresa
 */
require_once __DIR__ . '/../config/mail.php';

// Defaults
$nome = $nome ?? '';
$nome_empresa = $nome_empresa ?? '';

$email_titulo  = 'Sua página foi temporariamente suspensa';
$email_preview = 'A página de ' . htmlspecialchars($nome_empresa) . ' está temporariamente fora do ar.';
$email_corpo   = '
<h1>Página temporariamente suspensa</h1>
<p>Olá, <strong>' . htmlspecialchars($nome) . '</strong>. A página de <strong>' . htmlspecialchars($nome_empresa) . '</strong> foi temporariamente suspensa pelo nosso time editorial.</p>
<div class="highlight">
  <p><span class="status-badge badge-error">Suspensa</span></p>
  <p style="margin-top:10px">Sua empresa não está visível para os visitantes do guia no momento.</p>
</div>
<p>Entre em contato com nossa equipe para entender o motivo e regularizar sua situação. Estamos disponíveis por e-mail ou WhatsApp.</p>
<div class="btn-wrap">
  <a href="https://wa.me/5511999999999" class="btn">
    Falar com a equipe
  </a>
</div>
';

include __DIR__ . '/layout.php';