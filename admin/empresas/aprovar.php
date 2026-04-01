<?php
/**
 * admin/empresas/aprovar.php
 * Processa aprovação ou reprovação de empresa
 */
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

function _renderEmail(string $path, array $vars = []): string {
    extract($vars);
    if (!defined('SITE_URL')) require_once __DIR__ . '/../../config/mail.php';
    ob_start();
    include $path;
    return ob_get_clean();
}

Auth::require();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/empresas/index.php');
    exit;
}

if (!Sanitize::csrfValid($_POST['_token'] ?? '')) {
    $_SESSION['flash'] = ['type'=>'erro','msg'=>'Token inválido. Tente novamente.'];
    header('Location: /admin/empresas/index.php');
    exit;
}

$empresa_id  = Sanitize::post('empresa_id', 'int');
$acao        = Sanitize::post('acao');
$plano_ativo = Sanitize::post('plano_ativo');
$observacao  = Sanitize::post('observacao');
$motivo      = Sanitize::post('motivo_recusa');
$admin_id    = (int)($_SESSION['admin_id'] ?? 0);

$planos_validos = ['essencial', 'profissional', 'premium'];

// Busca a empresa
$empresa = DB::row(
    'SELECT e.*, u.nome AS usuario_nome, u.email AS usuario_email
     FROM empresas e JOIN usuarios u ON u.id = e.usuario_id
     WHERE e.id = ?',
    [$empresa_id]
);

if (!$empresa) {
    $_SESSION['flash'] = ['type'=>'erro','msg'=>'Empresa não encontrada.'];
    header('Location: /admin/empresas/index.php');
    exit;
}

if ($acao === 'aprovar') {

    if (!in_array($plano_ativo, $planos_validos)) {
        $_SESSION['flash'] = ['type'=>'erro','msg'=>'Selecione um plano válido.'];
        header('Location: /admin/empresas/index.php');
        exit;
    }

    DB::beginTransaction();
    try {
        // 1. Atualiza empresa
        DB::exec(
            'UPDATE empresas SET
                status       = "aprovada",
                plano_ativo  = ?,
                aprovado_por = ?,
                aprovado_em  = NOW()
             WHERE id = ?',
            [$plano_ativo, $admin_id, $empresa_id]
        );

        // 2. Se já tem lugar vinculado → ativa e define plano
        if ($empresa['lugar_id']) {
            DB::exec(
                'UPDATE lugares SET ativo = 1, plano = ? WHERE id = ?',
                [$plano_ativo, (int)$empresa['lugar_id']]
            );
        }

        // 3. Log de auditoria
        $detalhe = "Aprovada com plano: $plano_ativo";
        if ($observacao) $detalhe .= " | Obs: $observacao";
        DB::exec(
            'INSERT INTO empresa_logs (empresa_id, admin_id, acao, detalhe, criado_em)
             VALUES (?, ?, "aprovada", ?, NOW())',
            [$empresa_id, $admin_id, $detalhe]
        );

        DB::commit();

        // E-mail #3 — Empresa aprovada
        try {
            require_once __DIR__ . '/../../core/Mailer.php';
            $nome         = $empresa['usuario_nome'];
            $nome_empresa = DB::row('SELECT nome FROM lugares WHERE id=?', [$empresa['lugar_id']])['nome'] ?? $nome;
            $plano        = $plano_ativo;
            $slug         = DB::row('SELECT slug FROM lugares WHERE id=?', [$empresa['lugar_id']])['slug'] ?? '';
            $html = _renderEmail(__DIR__ . '/../../emails/aprovada.php', compact('nome', 'nome_empresa'));
            Mailer::send($empresa['usuario_email'], $nome, 'Sua empresa está no ar! 🎉', $html);
        } catch (Exception $ex) { error_log('[mail aprovada] ' . $ex->getMessage()); }

        $_SESSION['flash'] = [
            'type' => 'ok',
            'msg'  => "✓ {$empresa['usuario_nome']} aprovada com plano " . ucfirst($plano_ativo) . ".",
        ];

    } catch (Exception $e) {
        DB::rollback();
        error_log('[aprovar] ' . $e->getMessage());
        $_SESSION['flash'] = ['type'=>'erro','msg'=>'Erro ao aprovar. Tente novamente.'];
    }

} elseif ($acao === 'reprovar') {

    if (empty(trim($motivo))) {
        $_SESSION['flash'] = ['type'=>'erro','msg'=>'Informe o motivo da recusa.'];
        header('Location: /admin/empresas/index.php');
        exit;
    }

    DB::beginTransaction();
    try {
        // 1. Atualiza empresa
        DB::exec(
            'UPDATE empresas SET
                status        = "reprovada",
                motivo_recusa = ?,
                aprovado_por  = ?,
                aprovado_em   = NOW()
             WHERE id = ?',
            [trim($motivo), $admin_id, $empresa_id]
        );

        // 2. Garante que o lugar (se existir) continua inativo
        if ($empresa['lugar_id']) {
            DB::exec('UPDATE lugares SET ativo = 0 WHERE id = ?', [$empresa['lugar_id']]);
        }

        // 3. Log
        $detalhe = "Reprovada. Motivo: $motivo";
        if ($observacao) $detalhe .= " | Obs: $observacao";
        DB::exec(
            'INSERT INTO empresa_logs (empresa_id, admin_id, acao, detalhe, criado_em)
             VALUES (?, ?, "reprovada", ?, NOW())',
            [$empresa_id, $admin_id, $detalhe]
        );

        DB::commit();

        // E-mail #4 — Empresa reprovada
        try {
            require_once __DIR__ . '/../../core/Mailer.php';
            $nome         = $empresa['usuario_nome'];
            $nome_empresa = DB::row('SELECT nome FROM lugares WHERE id=?', [$empresa['lugar_id']])['nome'] ?? $nome;
            $html = _renderEmail(__DIR__ . '/../../emails/reprovada.php', ['nome' => $nome, 'nome_empresa' => $nome_empresa, 'motivo' => $motivo]);
            Mailer::send($empresa['usuario_email'], $nome, 'Sobre seu cadastro no Guia Campo Belo', $html);
        } catch (Exception $ex) { error_log('[mail reprovada] ' . $ex->getMessage()); }

        $_SESSION['flash'] = [
            'type' => 'ok',
            'msg'  => "Cadastro de {$empresa['usuario_nome']} reprovado.",
        ];

    } catch (Exception $e) {
        DB::rollback();
        error_log('[reprovar] ' . $e->getMessage());
        $_SESSION['flash'] = ['type'=>'erro','msg'=>'Erro ao reprovar. Tente novamente.'];
    }

} else {
    $_SESSION['flash'] = ['type'=>'erro','msg'=>'Ação inválida.'];
}

header('Location: /admin/empresas/index.php');
exit;