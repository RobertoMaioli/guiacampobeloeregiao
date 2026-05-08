<?php
/**
 * empresa/actions/assinar.php
 * Endpoint de criação de assinatura via Asaas
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

$usuario      = UserAuth::current();
$empresa_id   = (int)($usuario['empresa_id'] ?? 0);
$plano        = $raw['plano']        ?? '';
$billing_type = $raw['billing_type'] ?? '';

// Validações básicas
$planos_validos  = ['profissional', 'premium'];
$billing_validos = ['PIX', 'CREDIT_CARD'];

if (!in_array($plano, $planos_validos)) {
    echo json_encode(['ok' => false, 'erro' => 'Plano inválido.']); exit;
}

if (!in_array($billing_type, $billing_validos)) {
    echo json_encode(['ok' => false, 'erro' => 'Forma de pagamento inválida.']); exit;
}

try {
    $customer_id = Asaas::getOrCreateCustomer(
        $empresa_id,
        $usuario['nome'],
        $usuario['email']
    );

    $checkout_url = Asaas::createCheckout(
        $empresa_id,
        $customer_id,
        $plano,
        $billing_type
    );

    echo json_encode([
        'ok'          => true,
        'redirectUrl' => $checkout_url,
    ]);

} catch (AsaasException $e) {
    echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
}