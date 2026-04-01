<?php
/**
 * E-mail #8 — Lembrete de perfil incompleto
 * Vars: $nome, $nome_empresa, $pct, $itens_faltando (array de strings)
 */
require_once __DIR__ . '/../config/mail.php';

// Defaults
$nome = $nome ?? '';
$nome_empresa = $nome_empresa ?? '';
$pct = $pct ?? 0;
$itens_faltando = $itens_faltando ?? [];

$itens_html = '';
foreach ($itens_faltando ?? [] as $item) {
    $itens_html .= '<p style="padding:4px 0;border-bottom:1px solid #ede9e0">• ' . htmlspecialchars($item) . '</p>';
}

$email_titulo  = 'Complete o perfil de ' . htmlspecialchars($nome_empresa ?? '');
$email_preview = 'Seu perfil está ' . ($pct ?? 0) . '% completo. Perfis completos aparecem mais no guia.';
$email_corpo   = '
<h1>Seu perfil está quase lá! 📋</h1>
<p>Olá, <strong>' . htmlspecialchars($nome) . '</strong>. O perfil de <strong>' . htmlspecialchars($nome_empresa) . '</strong> está <strong>' . ($pct ?? 0) . '% completo</strong>.</p>
<p>Perfis completos aparecem com mais destaque nas buscas e geram até <strong>3x mais cliques</strong>.</p>
<div class="highlight">
  <p><strong>O que ainda falta:</strong></p>
  <div style="margin-top:8px">
    ' . $itens_html . '
  </div>
</div>
<div class="btn-wrap">
  <a href="' . SITE_URL . '/empresa/editar.php" class="btn">
    Completar perfil agora
  </a>
</div>
';

include __DIR__ . '/layout.php';