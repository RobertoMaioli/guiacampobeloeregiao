<?php
/**
 * E-mail #3 — Empresa aprovada
 * Vars: $nome, $nome_empresa, $plano, $slug
 */
require_once __DIR__ . '/../config/mail.php';

// Defaults — evita warnings se variável não for passada
$nome         = $nome         ?? '';
$nome_empresa = $nome_empresa ?? '';
$plano        = $plano        ?? 'essencial';
$slug         = $slug         ?? '';

$plano_label = ['essencial'=>'Essencial','profissional'=>'Profissional','premium'=>'Premium'][$plano] ?? 'Essencial';
$url_pagina  = $slug ? SITE_URL . '/pages/lugar.php?slug=' . urlencode($slug) : SITE_URL;

$email_titulo  = 'Sua empresa está no ar!';
$email_preview = htmlspecialchars($nome_empresa) . ' já aparece no Guia Campo Belo.';
$email_corpo   = '
<h1>Parabéns, sua empresa está no ar! 🎉</h1>
<p>Olá, <strong>' . htmlspecialchars($nome) . '</strong>. O cadastro de <strong>' . htmlspecialchars($nome_empresa) . '</strong> foi <strong style="color:#065f46">aprovado</strong> pela nossa equipe.</p>
<div class="highlight">
  <p><span class="status-badge badge-ok">✓ Aprovada</span></p>
  <p style="margin-top:10px">Plano ativo: <strong>' . $plano_label . '</strong></p>
</div>
<p>Sua página já está visível para todos os visitantes do Guia Campo Belo. Acesse o painel para editar informações, adicionar fotos e acompanhar seu desempenho.</p>
<div class="btn-wrap">
  <a href="' . SITE_URL . '/empresa/dashboard.php" class="btn btn-green" style="margin-right:10px">
    Acessar painel
  </a>
  <a href="' . $url_pagina . '" class="btn">
    Ver minha página
  </a>
</div>
';

include __DIR__ . '/layout.php';