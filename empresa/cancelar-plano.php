<?php
/**
 * empresa/actions/cancelar-plano.php
 * Permite o usuário cancelar a própria assinatura
 */
require_once __DIR__ . '/../../core/UserAuth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';
require_once __DIR__ . '/../../core/Asaas.php';

header('Content-Type: application/json');
UserAuth::require();

$raw = json_decode(file_get_contents('php://input'), true) ?? [];

if (!Sanitize::csrfValid($raw['_token'] ?? '')) {
    echo json_encode(['ok' => false, 'erro' => 'Token inválido.']); exit;
}

$usuario    = UserAuth::current();
$empresa_id = (int)($usuario['empresa_id'] ?? 0);

if (!$empresa_id) {
    echo json_encode(['ok' => false, 'erro' => 'Empresa não encontrada.']); exit;
}

try {
    Asaas::cancelSubscription($empresa_id);

    echo json_encode([
        'ok'  => true,
        'msg' => 'Assinatura cancelada com sucesso. Seu plano foi rebaixado para Essencial.',
    ]);

} catch (AsaasException $e) {
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}