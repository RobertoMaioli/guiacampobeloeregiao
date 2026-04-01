<?php
/**
 * empresa/login.php
 * Login do usuário empresarial
 */
require_once __DIR__ . '/../core/UserAuth.php';
require_once __DIR__ . '/../core/Sanitize.php';

UserAuth::start();

// Já logado → redireciona
if (UserAuth::check()) {
    header('Location: /empresa/dashboard.php');
    exit;
}

$erro  = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Sanitize::csrfValid($_POST['_token'] ?? '')) {
        $erro = 'Sessão inválida. Recarregue a página.';
    } else {
        $email = Sanitize::post('email', 'email');
        $senha = $_POST['senha'] ?? '';

        if (!$email || $senha === '') {
            $erro = 'Preencha e-mail e senha.';
        } elseif (!UserAuth::login($email, $senha)) {
            $erro = 'E-mail ou senha incorretos.';
        } else {
            header('Location: ' . UserAuth::intendedUrl('/empresa/dashboard.php'));
            exit;
        }
    }
}

$csrf       = Sanitize::csrfToken();
$plan_hint  = Sanitize::get('plan', 'str', '');
$page_title = 'Entrar — Guia Campo Belo';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
  <style>
    body { background: var(--gcb-offwhite); }
    .auth-wrap {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
    }
    .auth-card {
      width: 100%;
      max-width: 420px;
      background: #fff;
      border-radius: 24px;
      padding: 2.5rem 2rem;
      box-shadow: 0 8px 40px rgba(29,29,27,.1);
      border: 1px solid rgba(61,71,51,.07);
    }
    .auth-logo { display:flex; justify-content:center; margin-bottom:2rem; }
    .auth-logo img { height:52px; }
    .auth-title {
      font-size:24px; font-weight:800; color:var(--gcb-green-dark);
      margin-bottom:4px; text-align:center;
    }
    .auth-subtitle {
      font-size:13px; font-weight:300; color:var(--gcb-warmgray);
      text-align:center; margin-bottom:2rem;
    }
    .auth-field-label {
      display:block; font-size:10px; font-weight:800;
      letter-spacing:.16em; text-transform:uppercase;
      color:var(--gcb-warmgray); margin-bottom:6px;
    }
    .auth-error {
      display:flex; align-items:flex-start; gap:8px;
      padding:12px 14px; background:#fef2f2;
      border:1px solid #fecaca; border-radius:10px;
      font-size:13px; color:#c0392b; margin-bottom:1.25rem; line-height:1.5;
    }
    .auth-error svg { flex-shrink:0; margin-top:1px; }
    .auth-divider {
      display:flex; align-items:center; gap:12px;
      margin:1.5rem 0; font-size:11px; color:var(--gcb-warmgray);
    }
    .auth-divider::before,.auth-divider::after {
      content:''; flex:1; height:1px; background:rgba(61,71,51,.1);
    }
    .auth-back {
      display:flex; align-items:center; justify-content:center; gap:5px;
      margin-top:1.5rem; font-size:11px; font-weight:600;
      color:var(--gcb-warmgray); text-decoration:none;
    }
    .auth-back:hover { color:var(--gcb-green-dark); }
  </style>
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">

    <div class="auth-logo">
      <a href="/"><img src="/assets/img/logo.png" alt="Guia Campo Belo"></a>
    </div>

    <h1 class="auth-title">Bem-vindo de volta</h1>
    <p class="auth-subtitle">Acesse o painel da sua empresa</p>

    <?php if ($erro): ?>
    <div class="auth-error">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2" stroke-linecap="round">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <?= Sanitize::html($erro) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/empresa/login.php" novalidate>
      <input type="hidden" name="_token" value="<?= Sanitize::html($csrf) ?>">
      <?php if ($plan_hint): ?>
      <input type="hidden" name="plan_hint" value="<?= Sanitize::html($plan_hint) ?>">
      <?php endif; ?>

      <div class="mb-3">
        <label class="auth-field-label" for="email">E-mail</label>
        <input type="email" id="email" name="email" required
               autocomplete="email" class="gcb-field"
               placeholder="seu@email.com"
               value="<?= Sanitize::html($email) ?>">
      </div>

      <div class="mb-4">
        <label class="auth-field-label" for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required
               autocomplete="current-password" class="gcb-field"
               placeholder="Sua senha">
      </div>

      <button type="submit" class="btn-gold w-100 d-flex align-items-center
              justify-content-center gap-2 py-3">
        Entrar no painel
      </button>
    </form>

    <div class="auth-divider">ou</div>

    <div class="text-center" style="font-size:13px;color:var(--gcb-warmgray)">
      Não tem conta?
      <a href="/empresa/cadastro.php<?= $plan_hint ? '?plan='.$plan_hint : '' ?>"
         style="color:var(--gcb-green-dark);font-weight:700;text-decoration:none">
        Cadastrar empresa
      </a>
    </div>

    <a href="/" class="auth-back">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2" stroke-linecap="round">
        <polyline points="15 18 9 12 15 6"/>
      </svg>
      Voltar ao site
    </a>

  </div>
</div>
</body>
</html>