<?php
/**
 * empresa/cadastro.php
 * Cadastro do usuário empresarial — preserva plano escolhido em anuncie.php
 */
require_once __DIR__ . '/../core/UserAuth.php';
require_once __DIR__ . '/../core/Sanitize.php';
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../includes/icons.php';

function _renderEmail(string $path, array $vars = []): string {
    extract($vars);
    if (!defined('SITE_URL')) require_once __DIR__ . '/../config/mail.php';
    ob_start();
    include $path;
    return ob_get_clean();
}

UserAuth::start();

// Já logado → redireciona
if (UserAuth::check()) {
    $u    = UserAuth::current();
    $dest = ($u['empresa_status'] === 'aprovada')
          ? '/empresa/dashboard.php'
          : '/empresa/onboarding.php';
    header('Location: ' . $dest);
    exit;
}

// Plano vindo da URL ou sessão
$planos_validos = ['essencial', 'profissional', 'premium'];
$plan_param     = Sanitize::get('plan', 'str', '');
if (in_array($plan_param, $planos_validos)) {
    $_SESSION['plan_intent'] = $plan_param;
}
$plan_intent = $_SESSION['plan_intent'] ?? 'essencial';

$plan_labels = [
    'essencial'    => 'Essencial — Grátis',
    'profissional' => 'Profissional',
    'premium'      => 'Premium',
];

$erro  = '';
$nome  = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Sanitize::csrfValid($_POST['_token'] ?? '')) {
        $erro = 'Sessão inválida. Recarregue a página.';
    } else {
        $nome  = Sanitize::post('nome');
        $email = Sanitize::post('email', 'email');
        $senha = $_POST['senha'] ?? '';
        $plan_intent = in_array($_POST['plan_intent'] ?? '', $planos_validos)
                     ? $_POST['plan_intent'] : 'essencial';

        if (mb_strlen($nome) < 2) {
            $erro = 'Informe seu nome completo.';
        } elseif (!$email) {
            $erro = 'Informe um e-mail válido.';
        } elseif (mb_strlen($senha) < 8) {
            $erro = 'A senha deve ter pelo menos 8 caracteres.';
        } elseif (DB::row('SELECT id FROM usuarios WHERE email = ?', [$email])) {
            $erro = 'Este e-mail já está cadastrado. <a href="/empresa/login.php" style="color:#c0392b;font-weight:700">Entrar</a>';
        } else {
            DB::beginTransaction();
            try {
                DB::exec(
                    'INSERT INTO usuarios (nome, email, senha_hash, plan_intent, criado_em)
                     VALUES (?, ?, ?, ?, NOW())',
                    [$nome, $email,
                     password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]),
                     $plan_intent]
                );
                $usuario_id = (int) DB::lastId();

                DB::exec(
                    'INSERT INTO empresas (usuario_id, plan_intent, status, criado_em)
                     VALUES (?, ?, "rascunho", NOW())',
                    [$usuario_id, $plan_intent]
                );

                DB::commit();

                UserAuth::login($email, $senha);

                // E-mail #1 — Boas-vindas
                try {
                    require_once __DIR__ . '/../core/Mailer.php';
                    $nome  = $nome;
                    $plano = $plan_intent;
                    $html = _renderEmail(__DIR__ . '/../emails/boas-vindas.php', ['nome' => $nome, 'email' => $email, 'plano' => $plan_intent]);
                    Mailer::send($email, $nome, 'Bem-vindo ao Guia Campo Belo & Região', $html);
                } catch (Exception $ex) { error_log('[mail boas-vindas] ' . $ex->getMessage()); }

                header('Location: /empresa/onboarding.php');
                exit;

            } catch (Exception $e) {
                DB::rollback();
                error_log('[cadastro] ' . $e->getMessage());
                $erro = 'Erro ao criar a conta. Tente novamente.';
            }
        }
    }
}

$csrf       = Sanitize::csrfToken();
$page_title = 'Cadastrar empresa — Guia Campo Belo';
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
      align-items: stretch;
    }

    /* ── Painel esquerdo visual ── */
    .auth-visual {
      display: none;
      flex: 0 0 400px;
      background: var(--gcb-green-dark);
      padding: 3rem 2.5rem;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
      overflow: hidden;
    }
    @media(min-width:900px) { .auth-visual { display: flex; } }
    .auth-visual::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse 80% 60% at 110% 40%,
                  rgba(201,170,107,.12) 0%, transparent 65%);
      pointer-events: none;
    }
    .auth-visual-logo img { height: 48px; position: relative; }
    .auth-visual-body { position: relative; }
    .auth-visual-body h2 {
      font-size: 30px;
      font-weight: 800;
      color: #fff;
      line-height: 1.2;
      margin-bottom: 1rem;
    }
    .auth-visual-body h2 em {
      font-style: italic;
      font-weight: 300;
      color: var(--gcb-gold-light);
    }
    .auth-visual-body p {
      font-size: 13.5px;
      font-weight: 300;
      color: rgba(255,255,255,.45);
      line-height: 1.7;
    }
    .auth-visual-steps { position: relative; }
    .auth-step {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      margin-bottom: 1.1rem;
    }
    .auth-step-num {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background: rgba(201,170,107,.15);
      border: 1px solid rgba(201,170,107,.3);
      color: var(--gcb-gold-light);
      font-size: 10px;
      font-weight: 800;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      margin-top: 2px;
    }
    .auth-step-text { font-size: 13px; color: rgba(255,255,255,.6); line-height: 1.5; }
    .auth-step-text strong { display: block; color: #fff; font-weight: 700; margin-bottom: 1px; }

    /* ── Painel direito formulário ── */
    .auth-form-side {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2.5rem 1.5rem;
    }
    .auth-card {
      width: 100%;
      max-width: 420px;
    }
    .auth-mobile-logo {
      display: flex;
      justify-content: center;
      margin-bottom: 2rem;
    }
    .auth-mobile-logo img { height: 48px; }
    @media(min-width:900px) { .auth-mobile-logo { display: none; } }

    .auth-title {
      font-size: 24px;
      font-weight: 800;
      color: var(--gcb-green-dark);
      margin-bottom: 4px;
    }
    .auth-subtitle {
      font-size: 13px;
      font-weight: 300;
      color: var(--gcb-warmgray);
      margin-bottom: 1.5rem;
    }
    /* Badge de plano */
    .plan-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 5px 12px;
      background: var(--gcb-gold-pale);
      border: 1.5px solid rgba(201,170,107,.4);
      border-radius: 999px;
      font-size: 11px;
      font-weight: 700;
      color: var(--gcb-green-dark);
      margin-bottom: 1.5rem;
    }
    .plan-badge a {
      color: var(--gcb-warmgray);
      font-weight: 500;
      text-decoration: none;
      font-size: 10px;
      margin-left: 2px;
    }
    .plan-badge a:hover { color: var(--gcb-green); }
    .auth-field-label {
      display: block;
      font-size: 10px;
      font-weight: 800;
      letter-spacing: .16em;
      text-transform: uppercase;
      color: var(--gcb-warmgray);
      margin-bottom: 6px;
    }
    .auth-error {
      display: flex;
      align-items: flex-start;
      gap: 8px;
      padding: 12px 14px;
      background: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: 10px;
      font-size: 13px;
      color: #c0392b;
      margin-bottom: 1.25rem;
      line-height: 1.5;
    }
    .auth-error svg { flex-shrink: 0; margin-top: 1px; }
    /* Força da senha */
    .senha-bars {
      display: flex;
      gap: 4px;
      align-items: center;
      margin-top: 6px;
    }
    .senha-bar {
      flex: 1;
      height: 3px;
      border-radius: 2px;
      background: rgba(61,71,51,.1);
      transition: background .3s;
    }
    .senha-bar-label {
      font-size: 10px;
      font-weight: 600;
      color: var(--gcb-warmgray);
      min-width: 44px;
      text-align: right;
    }
    .auth-terms {
      font-size: 11px;
      color: var(--gcb-warmgray);
      text-align: center;
      margin-top: 1rem;
      line-height: 1.6;
    }
    .auth-terms a { color: var(--gcb-green); text-decoration: none; }
    .auth-divider {
      display: flex;
      align-items: center;
      gap: 12px;
      margin: 1.5rem 0;
      font-size: 11px;
      color: var(--gcb-warmgray);
    }
    .auth-divider::before,
    .auth-divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: rgba(61,71,51,.1);
    }
    .auth-back {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
      margin-top: 1.5rem;
      font-size: 11px;
      font-weight: 600;
      color: var(--gcb-warmgray);
      text-decoration: none;
    }
    .auth-back:hover { color: var(--gcb-green-dark); }
  </style>
</head>
<body>
<div class="auth-wrap">

  <!-- ── Visual lateral (desktop) ── -->
  <div class="auth-visual">
    <div class="auth-visual-logo">
      <a href="/"><img src="/assets/img/logo.png" alt="Guia Campo Belo"></a>
    </div>
    <div class="auth-visual-body">
      <h2>Apareça para<br/>quem <em>importa.</em></h2>
      <p>Cadastre sua empresa no guia definitivo de Campo Belo e seja encontrado por quem já está procurando.</p>
    </div>
    <div class="auth-visual-steps">
      <?php foreach ([
        ['Crie sua conta',       'Menos de 1 minuto'],
        ['Cadastre sua empresa', 'Preencha os dados no painel'],
        ['Aguarde a aprovação',  'Nossa equipe revisa em até 2 dias'],
      ] as $i => $s): ?>
      <div class="auth-step">
        <div class="auth-step-num"><?= $i + 1 ?></div>
        <div class="auth-step-text">
          <strong><?= $s[0] ?></strong><?= $s[1] ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ── Formulário ── -->
  <div class="auth-form-side">
    <div class="auth-card">

      <div class="auth-mobile-logo">
        <a href="/"><img src="/assets/img/logo.png" alt="Guia Campo Belo"></a>
      </div>

      <h1 class="auth-title">Criar conta</h1>
      <p class="auth-subtitle">É rápido. Os dados da empresa vêm depois.</p>

      <!-- Badge do plano selecionado -->
      <div class="plan-badge">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2.5" stroke-linecap="round">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        Plano: <?= Sanitize::html($plan_labels[$plan_intent]) ?>
        <a href="/pages/anuncie.php#planos">trocar</a>
      </div>

      <?php if ($erro): ?>
      <div class="auth-error">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <?= $erro ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="/empresa/cadastro.php" novalidate id="form-cadastro">
        <input type="hidden" name="_token"      value="<?= Sanitize::html($csrf) ?>">
        <input type="hidden" name="plan_intent" value="<?= Sanitize::html($plan_intent) ?>">

        <div class="mb-3">
          <label class="auth-field-label" for="nome">Seu nome completo</label>
          <input type="text" id="nome" name="nome" required
                 autocomplete="name" class="gcb-field"
                 placeholder="Ex: João Silva"
                 value="<?= Sanitize::html($nome) ?>">
        </div>

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
                 autocomplete="new-password" class="gcb-field"
                 placeholder="Mínimo 8 caracteres"
                 oninput="checkSenha(this.value)">
          <div class="senha-bars">
            <div class="senha-bar" id="sb1"></div>
            <div class="senha-bar" id="sb2"></div>
            <div class="senha-bar" id="sb3"></div>
            <div class="senha-bar" id="sb4"></div>
            <span class="senha-bar-label" id="sb-label"></span>
          </div>
        </div>

        <button type="submit" id="btn-submit"
                class="btn-gold w-100 d-flex align-items-center
                       justify-content-center gap-2 py-3">
          Criar conta e continuar →
        </button>

        <p class="auth-terms">
          Ao criar sua conta você concorda com os
          <a href="#">Termos de uso</a> e
          <a href="#">Política de privacidade</a>.
        </p>
      </form>

      <div class="auth-divider">ou</div>

      <div class="text-center" style="font-size:13px;color:var(--gcb-warmgray)">
        Já tem conta?
        <a href="/empresa/login.php"
           style="color:var(--gcb-green-dark);font-weight:700;text-decoration:none">
          Entrar
        </a>
      </div>

      <a href="/pages/anuncie.php" class="auth-back">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round">
          <polyline points="15 18 9 12 15 6"/>
        </svg>
        Voltar aos planos
      </a>

    </div>
  </div>

</div>

<script>
function checkSenha(val) {
  const colors = ['#ef4444','#f97316','#eab308','#22c55e'];
  const labels = ['Fraca','Razoável','Boa','Forte'];
  let score = 0;
  if (val.length >= 8)             score++;
  if (/[A-Z]/.test(val))           score++;
  if (/[0-9]/.test(val))           score++;
  if (/[^A-Za-z0-9]/.test(val))   score++;
  [sb1,sb2,sb3,sb4].forEach((b,i) => {
    b.style.background = i < score ? colors[score-1] : 'rgba(61,71,51,.1)';
  });
  const lbl = document.getElementById('sb-label');
  lbl.textContent = val.length === 0 ? '' : (labels[score-1] ?? 'Fraca');
  lbl.style.color = val.length === 0 ? '' : colors[score-1];
}

document.getElementById('form-cadastro').addEventListener('submit', function() {
  const btn = document.getElementById('btn-submit');
  btn.disabled = true;
  btn.textContent = 'Criando conta…';
});
</script>
</body>
</html>