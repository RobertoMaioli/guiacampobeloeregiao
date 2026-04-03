<?php
/**
 * core/RateLimit.php
 * Controle de tentativas por chave (IP, e-mail, etc.)
 * Usa a tabela `rate_limit` no MySQL.
 *
 * Uso típico no login:
 *   if (!RateLimit::allow('login', $_SERVER['REMOTE_ADDR'], 5, 60)) {
 *       $erro = 'Muitas tentativas. Aguarde 1 minuto.';
 *   }
 */

require_once __DIR__ . '/DB.php';

class RateLimit
{
    /**
     * Verifica se a ação é permitida e registra a tentativa.
     *
     * @param string $acao    Prefixo da ação, ex: 'login', 'cadastro'
     * @param string $sujeito IP ou e-mail do usuário
     * @param int    $max     Máximo de tentativas permitidas
     * @param int    $janela  Janela de tempo em segundos (ex: 60 = 1 minuto)
     * @param int    $bloqueio Tempo de bloqueio em segundos após exceder (padrão: igual à janela)
     *
     * @return bool  true = permitido | false = bloqueado
     */
    public static function allow(
        string $acao,
        string $sujeito,
        int $max      = 5,
        int $janela   = 60,
        int $bloqueio = 0
    ): bool {
        if ($bloqueio === 0) $bloqueio = $janela;

        $chave = $acao . ':' . mb_substr($sujeito, 0, 100);

        // Limpa registros antigos (sem bloqueio ativo) para manter tabela enxuta
        DB::exec(
            "DELETE FROM rate_limit
             WHERE chave = ? AND bloqueado_ate IS NULL
               AND atualizado_em < NOW() - INTERVAL ? SECOND",
            [$chave, $janela]
        );

        $row = DB::row('SELECT * FROM rate_limit WHERE chave = ?', [$chave]);

        // Ainda dentro do bloqueio ativo?
        if ($row && $row['bloqueado_ate'] !== null) {
            if (new DateTime() < new DateTime($row['bloqueado_ate'])) {
                return false; // continua bloqueado
            }
            // Bloqueio expirou — reset
            DB::exec('DELETE FROM rate_limit WHERE chave = ?', [$chave]);
            $row = null;
        }

        if (!$row) {
            // Primeira tentativa nesta janela
            DB::exec(
                'INSERT INTO rate_limit (chave, tentativas) VALUES (?, 1)',
                [$chave]
            );
            return true;
        }

        $novas = $row['tentativas'] + 1;

        if ($novas >= $max) {
            // Excedeu — aplica bloqueio
            DB::exec(
                'UPDATE rate_limit SET tentativas = ?, bloqueado_ate = NOW() + INTERVAL ? SECOND WHERE chave = ?',
                [$novas, $bloqueio, $chave]
            );
            return false;
        }

        // Incrementa tentativas
        DB::exec(
            'UPDATE rate_limit SET tentativas = ? WHERE chave = ?',
            [$novas, $chave]
        );

        return true;
    }

    /**
     * Reseta o contador de uma chave (chamar após login bem-sucedido).
     */
    public static function reset(string $acao, string $sujeito): void
    {
        $chave = $acao . ':' . mb_substr($sujeito, 0, 100);
        DB::exec('DELETE FROM rate_limit WHERE chave = ?', [$chave]);
    }

    /**
     * Retorna quantos segundos faltam para o desbloqueio (0 = livre).
     */
    public static function segundosRestantes(string $acao, string $sujeito): int
    {
        $chave = $acao . ':' . mb_substr($sujeito, 0, 100);
        $row = DB::row(
            'SELECT GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), bloqueado_ate)) AS restam
             FROM rate_limit WHERE chave = ? AND bloqueado_ate > NOW()',
            [$chave]
        );
        return $row ? (int)$row['restam'] : 0;
    }
}