<?php
/**
 * emails/evento-ingresso.php
 * Disparado quando o pagamento do evento é confirmado
 *
 * Variáveis: $nome_cliente, $token, $qrUrl
 */
if (!defined('SITE_URL')) require_once __DIR__ . '/../config/mail.php';
$email_titulo  = 'Seu ingresso — Guia Connect · ' . SITE_NAME;
$email_preview = 'Seu ingresso está confirmado! Apresente o QR Code na entrada do evento.';
ob_start(); ?>

<h1>Ingresso confirmado! 🎉</h1>
<p>Olá, <strong><?= htmlspecialchars($nome_cliente) ?></strong>!</p>
<p>Seu pagamento foi confirmado. Abaixo está o seu <strong>QR Code de acesso</strong> ao evento. Apresente-o na entrada.</p>

<!-- QR Code -->
<div style="text-align:center;margin:28px 0">
  <div style="display:inline-block;background:#faf8f3;border-radius:16px;padding:24px">
    <img src="<?= htmlspecialchars($qrUrl) ?>" alt="QR Code de acesso"
         width="200" height="200" style="display:block;border-radius:8px"/>
  </div>
</div>

<!-- Código legível -->
<div style="text-align:center;margin-bottom:28px">
  <p style="font-size:12px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;
            color:#8b8589;margin-bottom:4px">Código do ingresso</p>
  <p style="font-size:22px;font-weight:800;color:#2a3022;letter-spacing:.2em;margin:0">
    <?= htmlspecialchars($token) ?>
  </p>
</div>

<!-- Dados do evento -->
<div class="highlight">
  <p><strong>Evento:</strong> Guia Connect — Soft Opening</p>
  <p><strong>Data:</strong> Segunda, 19 de maio de 2025</p>
  <p><strong>Horário:</strong> A partir das 19h</p>
  <p><strong>Local:</strong> Cris Parilla — Rua República do Iraque, 1326, Campo Belo</p>
</div>

<div class="divider"></div>

<!-- Instruções -->
<p><strong>Como usar no dia:</strong></p>
<p>
  1. Abra este e-mail no seu celular<br/>
  2. Chegue ao <strong>Cris Parilla</strong><br/>
  3. Apresente o QR Code na entrada<br/>
  4. Aproveite a noite! ☕🥂
</p>

<p style="font-size:13px;color:#a09898">
  Guarde este e-mail — o QR Code é seu ingresso.<br/>
  Verifique também a caixa de spam caso não encontre.
</p>

<?php
$email_corpo = ob_get_clean();
include __DIR__ . '/layout.php';