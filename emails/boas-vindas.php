<?php
/**
 * E-mail #1 — Boas-vindas
 * Vars: $nome, $email, $plano
 */
require_once __DIR__ . '/../config/mail.php';

// Defaults
$nome = $nome ?? '';
$email = $email ?? '';
$plano = $plano ?? 'essencial';

$plano_label = ['essencial'=>'Essencial','profissional'=>'Profissional','premium'=>'Premium'][$plano ?? 'essencial'] ?? 'Essencial';

$email_titulo  = 'Bem-vindo ao ' . SITE_NAME;
$email_preview = 'Sua conta foi criada. Complete seu cadastro para aparecer no guia.';
$email_corpo   = '
<h1>Olá, ' . htmlspecialchars($nome) . '! 👋</h1>
<p>Sua conta no <strong>' . SITE_NAME . '</strong> foi criada com sucesso.</p>
<div class="highlight">
  <p>📌 Plano selecionado: <strong>' . $plano_label . '</strong></p>
  <p>📧 Acesso: <strong>' . htmlspecialchars($email) . '</strong></p>
</div>
<p>O próximo passo é completar o perfil da sua empresa — nome, endereço, foto e horários — para que possamos revisar e publicar sua página.</p>
<div class="btn-wrap">
  <a href="' . SITE_URL . '/empresa/onboarding.php" class="btn">
    Completar meu cadastro
  </a>
</div>
<p style="font-size:13px;color:#a09898;text-align:center">Tem dúvidas? Responda este e-mail ou fale pelo WhatsApp.</p>
';

include __DIR__ . '/layout.php';