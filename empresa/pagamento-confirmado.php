<?php
/**
 * empresa/pagamento-confirmado.php
 * Exibida após confirmação imediata de pagamento (cartão de crédito)
 */
require_once __DIR__ . '/../core/UserAuth.php';
require_once __DIR__ . '/../includes/icons.php';

UserAuth::require();
$usuario = UserAuth::current();
$plano   = $usuario['plano_ativo'] ?? 'essencial';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Pagamento confirmado — Guia Campo Belo</title>
  <?php include __DIR__ . '/../includes/head-common.php'; ?>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div style="max-width:520px;margin:80px auto;padding:0 20px;text-align:center">

  <div style="font-size:56px;margin-bottom:24px">🎉</div>

  <h1 style="font-size:22px;font-weight:800;color:var(--gcb-green-dark);margin-bottom:12px">
    Plano ativado com sucesso!
  </h1>

  <p style="font-size:14px;color:var(--gcb-warmgray);line-height:1.7;margin-bottom:8px">
    Seu plano <strong style="color:var(--gcb-green-dark)"><?= ucfirst($plano) ?></strong>
    já está ativo. Você receberá um e-mail de confirmação em instantes.
  </p>

  <p style="font-size:13px;color:var(--gcb-warmgray);line-height:1.7;margin-bottom:32px">
    Aproveite todos os recursos disponíveis no seu painel.
  </p>

  <a href="/empresa/dashboard.php"
     style="display:inline-block;background:var(--gcb-green-dark);color:#fff;
            font-size:13px;font-weight:700;padding:12px 28px;border-radius:12px;
            text-decoration:none">
    Ir para o painel
  </a>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>