<?php
/**
 * empresa/actions/submeter.php
 * Submete empresa para aprovação
 */
require_once __DIR__ . '/../../core/UserAuth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

function _renderEmail(string $path, array $vars = []): string {
    extract($vars);
    if (!defined('SITE_URL')) require_once __DIR__ . '/../../../config/mail.php';
    ob_start();
    include $path;
    return ob_get_clean();
}

header('Content-Type: application/json');
UserAuth::require();

$raw = json_decode(file_get_contents('php://input'), true) ?? [];

if (!Sanitize::csrfValid($raw['_token'] ?? '')) {
    echo json_encode(['ok'=>false,'erro'=>'Token inválido.']); exit;
}

$usuario    = UserAuth::current();
$empresa_id = (int)($usuario['empresa_id'] ?? 0);
$lugar_id   = (int)($raw['lugar_id'] ?? 0);

if (!$empresa_id || !$lugar_id) {
    echo json_encode(['ok'=>false,'erro'=>'Dados incompletos.']); exit;
}

// Verifica ownership
$empresa = DB::row('SELECT * FROM empresas WHERE id=? AND usuario_id=?',
    [$empresa_id, $_SESSION['usuario_id']]);

if (!$empresa) {
    echo json_encode(['ok'=>false,'erro'=>'Acesso negado.']); exit;
}

// Verifica campos mínimos
$lugar = DB::row('SELECT nome,endereco FROM lugares WHERE id=?',[$lugar_id]);
if (!$lugar || !$lugar['nome'] || !$lugar['endereco']) {
    echo json_encode(['ok'=>false,'erro'=>'Preencha nome e endereço antes de enviar.']); exit;
}

try {
    DB::exec(
        'UPDATE empresas SET status="pendente", submetido_em=NOW() WHERE id=?',
        [$empresa_id]
    );

    // Log
    DB::exec(
        'INSERT INTO empresa_logs (empresa_id,acao,detalhe,criado_em) VALUES (?,?,?,NOW())',
        [$empresa_id,'submetida','Empresa submetida para aprovação pelo usuário.']
    );

    // Busca dados completos para os e-mails
    $usuario_data = DB::row(
        'SELECT u.nome, u.email, u.plan_intent, l.nome AS nome_empresa
         FROM usuarios u
         JOIN empresas e ON e.usuario_id = u.id
         LEFT JOIN lugares l ON l.id = e.lugar_id
         WHERE e.id = ?', [$empresa_id]
    );

    if ($usuario_data) {
        require_once __DIR__ . '/../../core/Mailer.php';

        // E-mail #2 — Cadastro enviado (empresa)
        try {
            $nome         = $usuario_data['nome'];
            $nome_empresa = $usuario_data['nome_empresa'] ?? $nome;
            $html = _renderEmail(__DIR__ . '/../../emails/cadastro-enviado.php', ['nome' => $nome, 'nome_empresa' => $nome_empresa]);
            Mailer::send($usuario_data['email'], $nome, 'Recebemos seu cadastro — em análise', $html);
        } catch (Exception $ex) { error_log('[mail cadastro-enviado] ' . $ex->getMessage()); }

        // E-mail #6 — Nova empresa pendente (admin)
        try {
            $nome_usuario  = $usuario_data['nome'];
            $email_usuario = $usuario_data['email'];
            $nome_empresa  = $usuario_data['nome_empresa'] ?? $nome_usuario;
            $plano         = $usuario_data['plan_intent'];
            $html = _renderEmail(__DIR__ . '/../../emails/admin-nova-empresa.php', ['nome_usuario' => $nome_usuario, 'email_usuario' => $email_usuario, 'nome_empresa' => $nome_empresa, 'plano' => $plano, 'empresa_id' => $empresa_id]);
            Mailer::sendAdmin('Nova empresa aguardando aprovação: ' . $nome_empresa, $html);
        } catch (Exception $ex) { error_log('[mail admin-nova-empresa] ' . $ex->getMessage()); }
    }

    echo json_encode(['ok'=>true]);

} catch(Exception $e) {
    error_log('[submeter] '.$e->getMessage());
    echo json_encode(['ok'=>false,'erro'=>'Erro interno. Tente novamente.']);
}