<?php
/**
 * core/Sanitize.php
 * Helpers de sanitização e validação de input
 */

class Sanitize
{
    /** String limpa */
    public static function str(?string $v, int $max = 255): string
    {
        return mb_substr(trim((string)$v), 0, $max);
    }

    /** Inteiro */
    public static function int($v): int
    {
        return (int) filter_var($v, FILTER_SANITIZE_NUMBER_INT);
    }

    /** Float */
    public static function float($v): float
    {
        return (float) filter_var($v, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /** Slug — só letras, números e hífens */
    public static function slug(string $v): string
    {
        $v = mb_strtolower(trim($v));
        $v = preg_replace('/[áàãâä]/u', 'a', $v);
        $v = preg_replace('/[éèêë]/u',  'e', $v);
        $v = preg_replace('/[íìîï]/u',  'i', $v);
        $v = preg_replace('/[óòõôö]/u', 'o', $v);
        $v = preg_replace('/[úùûü]/u',  'u', $v);
        $v = preg_replace('/[ç]/u',     'c', $v);
        $v = preg_replace('/[^a-z0-9\-]/u', '-', $v);
        $v = preg_replace('/-+/', '-', $v);
        return trim($v, '-');
    }

    /** E-mail */
    public static function email(string $v): string|false
    {
        return filter_var(trim($v), FILTER_VALIDATE_EMAIL);
    }

    /** URL */
    public static function url(string $v): string|false
    {
        return filter_var(trim($v), FILTER_VALIDATE_URL);
    }

    /** Booleano vindo de checkbox/formulário */
    public static function bool($v): bool
    {
        return in_array($v, [1, '1', 'on', 'true', true], true);
    }

    /** POST com fallback */
    public static function post(string $key, string $type = 'str', mixed $default = ''): mixed
    {
        $val = $_POST[$key] ?? $default;
        return match($type) {
            'int'   => self::int($val),
            'float' => self::float($val),
            'bool'  => self::bool($val),
            'slug'  => self::slug((string)$val),
            'email' => self::email((string)$val) ?: $default,
            default => self::str((string)$val),
        };
    }

    /** GET com fallback */
    public static function get(string $key, string $type = 'str', mixed $default = ''): mixed
    {
        $val = $_GET[$key] ?? $default;
        return match($type) {
            'int'   => self::int($val),
            'float' => self::float($val),
            'slug'  => self::slug((string)$val),
            default => self::str((string)$val),
        };
    }

    /** Escapa para HTML */
    public static function html(?string $v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /** CSRF token — gera e valida */
    public static function csrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfValid(string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}
