<?php
/**
 * core/UserAuth.php
 * Autenticação dos usuários empresariais via sessão PHP
 * Coexiste com core/Auth.php (admin) sem conflito de sessão
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/DB.php';

class UserAuth
{
    /**
     * Nome da sessão — diferente do admin (ADMIN_SESSION_NAME)
     * para que as duas sessões possam existir simultaneamente
     */
    private const SESSION_NAME     = 'gcb_empresa';
    private const SESSION_LIFETIME = 60 * 60 * 24 * 30; // 30 dias

    /* ── Inicializa a sessão empresa ── */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(self::SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => self::SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax', // Lax (não Strict) para redirect pós-OAuth futuro
            ]);
            session_start();
        }
    }

    /* ── Exige login — redireciona se não autenticado ── */
    public static function require(): void
    {
        self::start();
        if (empty($_SESSION['usuario_id'])) {
            // Salva a URL que o usuário queria acessar para redirect pós-login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /empresa/login.php');
            exit;
        }
    }

    /* ── Login ── retorna true/false ── */
    public static function login(string $email, string $senha): bool
    {
        $usuario = DB::row(
            'SELECT id, nome, email, senha_hash FROM usuarios WHERE email = ? LIMIT 1',
            [trim($email)]
        );

        if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
            return false;
        }

        self::start();
        session_regenerate_id(true);

        $_SESSION['usuario_id']    = $usuario['id'];
        $_SESSION['usuario_nome']  = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['_regenerated']  = time();

        // Atualiza último login
        DB::exec(
            'UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?',
            [$usuario['id']]
        );

        return true;
    }

    /* ── Logout ── NÃO destrói a sessão inteira (admin pode estar ativo) ── */
    public static function logout(): void
    {
        self::start();
        unset(
            $_SESSION['usuario_id'],
            $_SESSION['usuario_nome'],
            $_SESSION['usuario_email'],
            $_SESSION['_regenerated'],
            $_SESSION['redirect_after_login']
        );
        // Apaga apenas o cookie desta sessão
        setcookie(self::SESSION_NAME, '', time() - 3600, '/');
        session_destroy();
    }

    /* ── Retorna dados do usuário logado + empresa vinculada ── */
    public static function current(): ?array
    {
        self::start();
        if (empty($_SESSION['usuario_id'])) return null;

        return DB::row(
            'SELECT
                u.id, u.nome, u.email, u.email_verified, u.plan_intent,
                e.id            AS empresa_id,
                e.status        AS empresa_status,
                e.plano_ativo,
                e.plan_intent   AS empresa_plan_intent,
                e.lugar_id,
                e.motivo_recusa,
                e.submetido_em
             FROM usuarios u
             LEFT JOIN empresas e ON e.usuario_id = u.id
             WHERE u.id = ?
             LIMIT 1',
            [$_SESSION['usuario_id']]
        );
    }

    /* ── Verifica se há usuário logado (sem redirecionar) ── */
    public static function check(): bool
    {
        self::start();
        return !empty($_SESSION['usuario_id']);
    }

    /* ── Retorna dados mínimos para o header (leve, sem JOIN) ── */
    public static function headerData(): ?array
    {
        self::start();
        if (empty($_SESSION['usuario_id'])) return null;

        return [
            'id'    => $_SESSION['usuario_id'],
            'nome'  => $_SESSION['usuario_nome']  ?? '',
            'email' => $_SESSION['usuario_email'] ?? '',
        ];
    }

    /* ── Troca de senha ── */
    public static function changePassword(int $id, string $novaSenha): void
    {
        DB::exec(
            'UPDATE usuarios SET senha_hash = ? WHERE id = ?',
            [password_hash($novaSenha, PASSWORD_BCRYPT, ['cost' => 12]), $id]
        );
    }

    /* ── URL para redirect pós-login ── */
    public static function intendedUrl(string $default = '/empresa/dashboard.php'): string
    {
        self::start();
        $url = $_SESSION['redirect_after_login'] ?? $default;
        unset($_SESSION['redirect_after_login']);
        return $url;
    }
}