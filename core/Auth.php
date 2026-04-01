<?php
/**
 * core/Auth.php
 * Autenticação do admin via sessão PHP
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/DB.php';

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(ADMIN_SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => ADMIN_SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
        }
    }

    /** Verifica se há admin logado — redireciona se não houver */
    public static function require(): void
    {
        self::start();

        if (empty($_SESSION['admin_id'])) {
            header('Location: /admin/login.php');
            exit;
        }

        // Renova a sessão a cada request (evita fixation)
        if (empty($_SESSION['_regenerated'])) {
            session_regenerate_id(true);
            $_SESSION['_regenerated'] = time();
        }
    }

    /** Retorna dados do admin logado */
    public static function admin(): ?array
    {
        self::start();
        if (empty($_SESSION['admin_id'])) return null;
        return [
            'id'   => $_SESSION['admin_id'],
            'nome' => $_SESSION['admin_nome'],
        ];
    }

    /** Login — retorna true/false */
    public static function login(string $email, string $senha): bool
    {
        $admin = DB::row(
            'SELECT id, nome, senha_hash FROM admins WHERE email = ? LIMIT 1',
            [trim($email)]
        );

        if (!$admin || !password_verify($senha, $admin['senha_hash'])) {
            return false;
        }

        self::start();
        session_regenerate_id(true);
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_nome'] = $admin['nome'];
        $_SESSION['_regenerated'] = time();

        DB::exec(
            'UPDATE admins SET ultimo_login = NOW() WHERE id = ?',
            [$admin['id']]
        );

        return true;
    }

    /** Logout */
    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
    }

    /** Troca de senha */
    public static function changePassword(int $id, string $novaSenha): void
    {
        DB::exec(
            'UPDATE admins SET senha_hash = ? WHERE id = ?',
            [password_hash($novaSenha, PASSWORD_BCRYPT, ['cost' => 12]), $id]
        );
    }
}
