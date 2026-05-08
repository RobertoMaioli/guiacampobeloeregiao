<?php
/**
 * empresa/pagamento-pendente.php
 * Exibida após o usuário voltar do checkout do Asaas
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
  <title>Pagamento em análise — Guia Campo Belo</title>
  <?php include __DIR__ . '/../includes/head-common.php'; ?>
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div style="max-width:520px;margin:80px auto;padding:0 20px;text-align:center">

  <div style="font-size:56px;margin-bottom:24px">⏳</div>

  <h1 style="font-size:22px;font-weight:800;color:var(--gcb-green-dark);margin-bottom:12px">
    Pagamento em análise
  </h1>

  <p style="font-size:14px;color:var(--gcb-warmgray);line-height:1.7;margin-bottom:32px">
    Recebemos sua solicitação. Assim que o pagamento for confirmado,
    seu plano será ativado automaticamente e você receberá um e-mail de confirmação.
  </p>

  <a href="/empresa/dashboard.php"
     style="display:inline-block;background:var(--gcb-green-dark);color:#fff;
            font-size:13px;font-weight:700;padding:12px 28px;border-radius:12px;
            text-decoration:none">
    Voltar ao painel
  </a>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>