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
                        'secret'   => '0x4AAAAAACzHCVgkYcexPpzay4BRYcHapUI',
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
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Playfair+Display:ital,wght@0,700;1,600&display=swap"
        rel="stylesheet" />
    <link rel="icon" type="image/png" href="/assets/img/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Cloudflare Turnstile -->
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

    <script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    display: ['"Montserrat"', 'sans-serif'],
                    body: ['Montserrat', 'sans-serif']
                },
                colors: {
                    green: {
                        DEFAULT: '#3d4733',
                        dark: '#2a3022'
                    },
                    gold: {
                        DEFAULT: '#c9aa6b',
                        light: '#ddc48a',
                        pale: '#f5edda'
                    },
                    cream: '#faf8f3',
                    graphite: '#1d1d1b'
                }
            }
        }
    }
    </script>
    <style>
    body { font-family: 'Montserrat', sans-serif; }
    /* Centraliza o widget Turnstile */
    .cf-turnstile { display: flex; justify-content: center; margin-bottom: 1.25rem; }
    </style>
</head>

<body class="bg-green-dark min-h-screen flex items-center justify-center px-4"
    style="background:linear-gradient(135deg,#2a3022 0%,#3d4733 100%)">

    <div class="w-full max-w-[400px]">

        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-3 mb-2">
                <img src="/assets/img/logo.png" alt="Guia Campo Belo" style="height:70px">
            </div>
        </div>

        <!-- Card -->
        <div class="bg-[#faf8f3] rounded-2xl p-8 shadow-[0_24px_60px_rgba(0,0,0,0.3)]">
            <h1 class="font-display text-[24px] font-bold text-[#2a3022] mb-1">Bem-vindo</h1>
            <p class="text-[13px] text-[#8b8589] mb-7">Acesso restrito ao administrador</p>

            <?php if ($erro): ?>
            <div class="flex items-center gap-2.5 px-4 py-3 bg-red-50 border border-red-200
                  rounded-xl text-[13px] text-red-600 mb-5">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                <?= Sanitize::html($erro) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="/admin/login.php" novalidate>
                <input type="hidden" name="_token" value="<?= Sanitize::html($csrf) ?>" />

                <div class="mb-4">
                    <label class="block text-[10px] font-black tracking-[0.18em] uppercase
                        text-[#8b8589] mb-2">E-mail</label>
                    <input type="email" name="email" required autocomplete="email"
                        value="<?= Sanitize::html($_POST['email'] ?? '') ?>"
                        class="w-full px-4 py-3 bg-[#f2f0eb] border border-[#3d4733]/10
                        rounded-xl text-[14px] outline-none
                        focus:border-[#c9aa6b]/60 focus:ring-2 focus:ring-[#c9aa6b]/15
                        transition-all duration-200" />
                </div>

                <div class="mb-6">
                    <label class="block text-[10px] font-black tracking-[0.18em] uppercase
                        text-[#8b8589] mb-2">Senha</label>
                    <input type="password" name="senha" required autocomplete="current-password"
                        class="w-full px-4 py-3 bg-[#f2f0eb] border border-[#3d4733]/10
                        rounded-xl text-[14px] outline-none
                        focus:border-[#c9aa6b]/60 focus:ring-2 focus:ring-[#c9aa6b]/15
                        transition-all duration-200" />
                </div>

                <!-- Cloudflare Turnstile Widget -->
                <div class="cf-turnstile"
                     data-sitekey="0x4AAAAAACzHCfnzOpQH45p_"
                     data-theme="light">
                </div>

                <button type="submit" class="w-full py-3.5 bg-[#2a3022] hover:bg-[#3d4733] text-white
                       font-black text-[12px] tracking-[0.12em] uppercase rounded-full
                       transition-colors duration-200">
                    Entrar no painel
                </button>
            </form>
        </div>

        <p class="text-center text-[11px] text-white/25 mt-6">
            &copy; <?= date('Y') ?> Guia Campo Belo &amp; Região
        </p>
    </div>

</body>

</html>