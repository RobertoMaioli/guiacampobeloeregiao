<?php
/**
 * admin/login.php
 */
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Sanitize.php';

Auth::start();

// Já logado → redireciona
if (!empty($_SESSION['admin_id'])) {
    header('Location: /admin/dashboard.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Sanitize::csrfValid($_POST['_token'] ?? '')) {
        $erro = 'Sessão inválida. Recarregue a página.';
    } else {

        // ── Valida Turnstile ──────────────────────────────────────────
        $turnstileToken = $_POST['cf-turnstile-response'] ?? '';
        if (empty($turnstileToken)) {
            $erro = 'Verificação de segurança não concluída.';
        } else {
            $verify = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false,
                stream_context_create(['http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => http_build_query([
                        'secret'   => '0x4AAAAAAC-jyx9HkvIlwnbunU-So2xyHGU',
                        'response' => $turnstileToken,
                        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
                    ]),
                    'timeout' => 10,
                ]])
            );
            $result = $verify ? json_decode($verify, true) : [];
            if (empty($result['success'])) {
                $erro = 'Falha na verificação de segurança. Tente novamente.';
            }
        }
        // ─────────────────────────────────────────────────────────────

        if (!$erro) {
            $email = Sanitize::post('email', 'email');
            $senha = $_POST['senha'] ?? '';

            if (!$email || $senha === '') {
                $erro = 'Preencha e-mail e senha.';
            } elseif (!Auth::login($email, $senha)) {
                $erro = 'E-mail ou senha incorretos.';
            } else {
                header('Location: /admin/dashboard.php');
                exit;
            }
        }
    }
}

$csrf = Sanitize::csrfToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin — Guia Campo Belo</title>
    <meta name="robots" content="noindex" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,700;1,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />

    <!-- Cloudflare Turnstile -->
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

    <link rel="icon" type="image/png" href="/assets/img/logo.png" />

    <style>
        :root {
            --color-green:    #3d4733;
            --color-green-dk: #2a3022;
            --color-gold:     #c9aa6b;
            --color-gold-lt:  #ddc48a;
            --color-cream:    #faf8f3;
            --color-muted:    #8b8589;
            --color-bg-input: #f2f0eb;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: linear-gradient(135deg, var(--color-green-dk) 0%, var(--color-green) 100%);
        }

        .login-wrap {
            width: 100%;
            max-width: 400px;
        }

        /* ── Card ── */
        .login-card {
            background: var(--color-cream);
            border: none;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 24px 60px rgba(0, 0, 0, .30);
        }

        .login-card .card-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--color-green-dk);
            margin-bottom: .25rem;
        }

        .login-card .card-subtitle {
            font-size: .8125rem;
            color: var(--color-muted);
            margin-bottom: 1.75rem;
        }

        /* ── Labels ── */
        .login-card .form-label {
            font-size: .625rem;
            font-weight: 800;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--color-muted);
            margin-bottom: .5rem;
        }

        /* ── Inputs ── */
        .login-card .form-control {
            background: var(--color-bg-input);
            border: 1px solid rgba(61, 71, 51, .10);
            border-radius: .75rem;
            font-size: .875rem;
            padding: .75rem 1rem;
            color: var(--color-green-dk);
            transition: border-color .2s, box-shadow .2s;
        }

        .login-card .form-control:focus {
            background: var(--color-bg-input);
            border-color: rgba(201, 170, 107, .60);
            box-shadow: 0 0 0 .2rem rgba(201, 170, 107, .15);
            color: var(--color-green-dk);
            outline: none;
        }

        /* ── Turnstile ── */
        .cf-turnstile {
            display: flex;
            justify-content: center;
            margin-bottom: 1.25rem;
        }

        /* ── Submit button ── */
        .btn-login {
            width: 100%;
            padding: .875rem 1rem;
            background: var(--color-green-dk);
            color: #fff;
            font-family: 'Montserrat', sans-serif;
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            border: none;
            border-radius: 50px;
            transition: background-color .2s;
        }

        .btn-login:hover,
        .btn-login:focus {
            background: var(--color-green);
            color: #fff;
        }

        /* ── Error alert ── */
        .alert-login {
            display: flex;
            align-items: center;
            gap: .625rem;
            padding: .75rem 1rem;
            background: #fff5f5;
            border: 1px solid #fecaca;
            border-radius: .75rem;
            font-size: .8125rem;
            color: #dc2626;
            margin-bottom: 1.25rem;
        }

        /* ── Footer ── */
        .login-footer {
            text-align: center;
            font-size: .6875rem;
            color: rgba(255, 255, 255, .25);
            margin-top: 1.5rem;
        }
    </style>
</head>

<body>

    <div class="login-wrap">

        <!-- Logo -->
        <div class="text-center mb-4">
            <img src="/assets/img/logo.png" alt="Guia Campo Belo" style="height:70px">
        </div>

        <!-- Card -->
        <div class="login-card">
            <div class="card-title">Bem-vindo</div>
            <div class="card-subtitle">Acesso restrito ao administrador</div>

            <?php if ($erro): ?>
            <div class="alert-login">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <?= Sanitize::html($erro) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="/admin/login.php" novalidate>
                <input type="hidden" name="_token" value="<?= Sanitize::html($csrf) ?>" />

                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        required
                        autocomplete="email"
                        value="<?= Sanitize::html($_POST['email'] ?? '') ?>"
                    />
                </div>

                <div class="mb-4">
                    <label class="form-label">Senha</label>
                    <input
                        type="password"
                        name="senha"
                        class="form-control"
                        required
                        autocomplete="current-password"
                    />
                </div>

                <!-- Cloudflare Turnstile Widget -->
                <div class="cf-turnstile"
                     data-sitekey="0x4AAAAAAC-jy6CfuJSkRqaN"
                     data-theme="light">
                </div>

                <button type="submit" class="btn-login">
                    Entrar no painel
                </button>
            </form>
        </div>

        <p class="login-footer">
            &copy; <?= date('Y') ?> Guia Campo Belo &amp; Região
        </p>

    </div>

    <!-- Bootstrap 5 JS (necessário apenas se usar componentes JS do Bootstrap) -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->

</body>

</html>