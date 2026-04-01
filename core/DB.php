<?php
/**
 * core/DB.php
 * Singleton PDO — uso: DB::get()
 */

require_once __DIR__ . '/../config/database.php';

class DB
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function get(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci; SET time_zone = '-03:00'",
                ]);
            } catch (PDOException $e) {
                // Não expõe detalhes ao visitante
                error_log('[DB] Falha na conexão: ' . $e->getMessage());
                http_response_code(503);
                die(json_encode(['erro' => 'Serviço temporariamente indisponível.']));
            }
        }

        return self::$instance;
    }

    /** Atalho: SELECT e retorna todos os resultados */
    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Atalho: SELECT e retorna uma linha */
    public static function row(string $sql, array $params = []): ?array
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Atalho: INSERT / UPDATE / DELETE e retorna rows afetadas */
    public static function exec(string $sql, array $params = []): int
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /** Último ID inserido */
    public static function lastId(): string
    {
        return self::get()->lastInsertId();
    }

    /** Transação */
    public static function beginTransaction(): void  { self::get()->beginTransaction(); }
    public static function commit(): void            { self::get()->commit(); }
    public static function rollback(): void          { self::get()->rollBack(); }
}
