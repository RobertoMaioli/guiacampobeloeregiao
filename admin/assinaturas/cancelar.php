<?php
/**
 * admin/assinaturas/cancelar.php
 * Cancela assinatura de uma empresa pelo admin
 */
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';
require_once __DIR__ . '/../../core/Asaas.php';

header('Content-Type: application/json');
Auth::require();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'erro' => 'Método inválido.']); exit;
}

$raw = json_decode(file_get_contents('php://input'), true) ?? [];

if (!Sanitize::csrfValid($raw['_token'] ?? '')) {
    echo json_encode(['ok' => false, 'erro' => 'Token inválido.']); exit;
}

$empresa_id = (int)($raw['empresa_id'] ?? 0);

if (!$empresa_id) {
    echo json_encode(['ok' => false, 'erro' => 'Empresa inválida.']); exit;
}

try {
    $admin   = Auth::admin();
    $empresa = DB::row(
        'SELECT e.id, e.plano_ativo, u.nome, u.email
         FROM empresas e JOIN usuarios u ON u.id = e.usuario_id
         WHERE e.id = ?',
        [$empresa_id]
    );

    if (!$empresa) {
        echo json_encode(['ok' => false, 'erro' => 'Empresa não encontrada.']); exit;
    }

    Asaas::cancelSubscription($empresa_id);

    // Loga ação do admin
    DB::exec(
        'INSERT INTO empresa_logs (empresa_id, admin_id, acao, detalhe, criado_em)
         VALUES (?, ?, "cancelamento_admin", ?, NOW())',
        [
            $empresa_id,
            $admin['id'],
            'Assinatura cancelada pelo admin ' . $admin['nome'] . '.',
        ]
    );

    echo json_encode(['ok' => true]);

} catch (AsaasException $e) {
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
} catch (Exception $e) {
    error_log('[cancelar admin] ' . $e->getMessage());
    echo json_encode(['ok' => false, 'erro' => 'Erro interno. Tente novamente.']);
}