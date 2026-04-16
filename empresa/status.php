<?php
/**
 * empresa/status.php
 * Página de aguardo de aprovação
 */
require_once __DIR__ . '/../core/UserAuth.php';
require_once __DIR__ . '/../core/Sanitize.php';
require_once __DIR__ . '/../includes/icons.php';

UserAuth::require();
$usuario = UserAuth::current();

// Sessão ativa mas usuário excluído do banco
if (!$usuario) {
    UserAuth::logout();
    header('Location: /empresa/login.php'); exit;
}

// Roteamento por status
match($usuario['empresa_status'] ?? 'rascunho') {
    'rascunho'  => (header('Location: /empresa/onboarding.php') ?: exit()),
    'aprovada'  => (header('Location: /empresa/dashboard.php')  ?: exit()),
    default     => null
};

$motivo    = $usuario['motivo_recusa'] ?? '';
$submetido = $usuario['submetido_em']  ?? '';
$status    = $usuario['empresa_status'] ?? 'pendente';
$page_title = 'Status do cadastro — Guia Campo Belo';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
  <style>
    body{background:var(--gcb-offwhite)}
    .status-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem 1rem}
    .status-card{width:100%;max-width:500px;background:#fff;border-radius:24px;padding:2.5rem 2rem;box-shadow:0 8px 40px rgba(29,29,27,.09);border:1px solid rgba(61,71,51,.07)}
    .status-icon{width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:28px}
    .tl-item{display:flex;gap:12px;margin-bottom:14px;position:relative}
    .tl-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;margin-top:3px}
    .tl-line{position:absolute;left:4px;top:13px;bottom:-14px;width:2px;background:rgba(61,71,51,.08)}
    .tl-item:last-child .tl-line{display:none}
    .tl-label{font-size:13px;font-weight:600;color:var(--gcb-graphite)}
    .tl-sub{font-size:11px;color:var(--gcb-warmgray);margin-top:2px}
  </style>
</head>
<body>
<div class="status-wrap">
  <div class="status-card">

    <div class="text-center mb-4">
      <a href="/"><img src="/assets/img/logo.png" alt="" style="height:42px;margin-bottom:1.5rem"></a>
    </div>

    <?php if ($status === 'pendente'): ?>
    <div class="status-icon" style="background:#fef3c7">⏳</div>
    <h1 style="font-size:20px;font-weight:800;color:var(--gcb-green-dark);text-align:center;margin-bottom:6px">
      Cadastro recebido!
    </h1>
    <p style="font-size:13px;color:var(--gcb-warmgray);text-align:center;margin-bottom:2rem;line-height:1.6">
      Nossa equipe vai revisar e ativar sua empresa em até <strong style="color:var(--gcb-graphite)">2 dias úteis</strong>.
      <?php if($submetido): ?>
      <br>Enviado em <?= date('d/m/Y \à\s H:i', strtotime($submetido)) ?>.
      <?php endif; ?>
    </p>

    <?php elseif ($status === 'reprovada'): ?>
    <div class="status-icon" style="background:#fef2f2">❌</div>
    <h1 style="font-size:20px;font-weight:800;color:var(--gcb-green-dark);text-align:center;margin-bottom:6px">
      Cadastro não aprovado
    </h1>
    <?php if($motivo): ?>
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:14px;margin-bottom:1.5rem">
      <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#c0392b;margin-bottom:4px">Motivo</p>
      <p style="font-size:13px;color:var(--gcb-graphite);line-height:1.6"><?= Sanitize::html($motivo) ?></p>
    </div>
    <?php endif; ?>
    <a href="/empresa/onboarding.php"
       class="btn-gold d-flex align-items-center justify-content-center gap-2 py-3 mb-3">
      Corrigir e reenviar
    </a>
    <?php else: ?>
    <div class="status-icon" style="background:var(--gcb-offwhite)">⏸</div>
    <h1 style="font-size:20px;font-weight:800;color:var(--gcb-green-dark);text-align:center;margin-bottom:6px">
      Conta suspensa
    </h1>
    <p style="font-size:13px;color:var(--gcb-warmgray);text-align:center;margin-bottom:1.5rem">
      Entre em contato com nossa equipe para mais informações.
    </p>
    <?php endif; ?>

    <!-- Timeline -->
    <div style="margin:1.5rem 0;padding:1.5rem;background:var(--gcb-offwhite);border-radius:14px">
      <?php
      $steps = [
        ['Conta criada',            'ok',      ''],
        ['Dados da empresa enviados',$status!=='rascunho'?'ok':'pend',''],
        ['Em análise pela equipe',   $status==='pendente'?'active':($status==='aprovada'?'ok':($status==='reprovada'?'err':'pend')),''],
        ['Plano ativado',            $status==='aprovada'?'ok':'pend',''],
      ];
      $dot_colors = ['ok'=>'#34d399','active'=>'#c9aa6b','err'=>'#ef4444','pend'=>'rgba(61,71,51,.15)'];
      foreach($steps as $s):
        $col = $dot_colors[$s[1]] ?? '#ccc';
      ?>
      <div class="tl-item">
        <div class="tl-dot" style="background:<?= $col ?>"></div>
        <div class="tl-line"></div>
        <div>
          <div class="tl-label" style="<?= $s[1]==='pend'?'color:var(--gcb-warmgray)':'' ?>"><?= $s[0] ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($status === 'pendente'): ?>
    <!--<a href="/empresa/onboarding.php"-->
    <!--   class="btn-header-outline d-flex align-items-center justify-content-center gap-2 py-3 mb-3">-->
    <!--  Editar dados enquanto aguarda-->
    <!--</a>-->
    <?php endif; ?>

    <a href="/" style="display:flex;align-items:center;justify-content:center;gap:5px;
                       font-size:11px;font-weight:600;color:var(--gcb-warmgray);text-decoration:none;margin-top:.5rem">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
      Voltar ao site
    </a>

  </div>
</div>
</body>
</html>