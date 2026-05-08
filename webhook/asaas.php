<?php
/**
 * webhook/asaas.php
 * Endpoint público que recebe eventos do Asaas
 */
require_once __DIR__ . '/../config/asaas.php';
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Mailer.php';

// Valida token do webhook
$token_recebido = $_SERVER['HTTP_ASAAS_ACCESS_TOKEN'] ?? '';

if (ASAAS_WEBHOOK_SECRET && $token_recebido !== ASAAS_WEBHOOK_SECRET) {
    http_response_code(401);
    exit;
}

// Lê o payload bruto
$payload_raw = file_get_contents('php://input');
$payload     = json_decode($payload_raw, true);

// Sempre responde 200 imediatamente
// (se demorar, o Asaas vai reenviar achando que falhou)
http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['ok' => true]);

// Ignora se payload inválido
if (empty($payload['event'])) {
    exit;
}

$evento = $payload['event'];

// Expõe payload globalmente para uso nas funções
$GLOBALS['payload'] = $payload;

// Salva tudo no log ANTES de processar
DB::exec(
    'INSERT INTO asaas_webhooks_log (evento, payload, processado, criado_em)
     VALUES (?, ?, 0, NOW())',
    [$evento, $payload_raw]
);

// A partir daqui vamos tratar cada evento
$payment   = $payload['payment']      ?? [];
$sub_id    = $payment['subscription'] ?? null;
$valor     = $payment['value']        ?? 0;
$pagamento_id = $payment['id']        ?? null;
$vencimento   = $payment['dueDate']   ?? null;
$pago_em      = $payment['paymentDate'] ?? null;

match($evento) {
    'PAYMENT_CONFIRMED',
    'PAYMENT_RECEIVED' => handlePagamentoConfirmado($sub_id, $pagamento_id, $valor, $vencimento, $pago_em),

    'PAYMENT_OVERDUE'  => handlePagamentoVencido($sub_id, $pagamento_id, $valor, $vencimento),

    'SUBSCRIPTION_DELETED' => handleAssinaturaCancel($sub_id),

    'PAYMENT_REFUNDED' => handlePagamentoReembolsado($sub_id, $pagamento_id),

    default => null
};

// ── Funções de processamento ─────────────────────────────────────────

function handlePagamentoConfirmado(?string $sub_id, ?string $pagamento_id, float $valor, ?string $vencimento, ?string $pago_em): void
{
    if (!$sub_id) return;

    $payload     = $GLOBALS['payload'];
    $customer_id = $payload['payment']['customer'] ?? null;

    // Busca empresa pelo subscription_id primeiro
    $empresa = DB::row(
        'SELECT id, plan_intent, plano_ativo FROM empresas 
         WHERE asaas_subscription_id = ?',
        [$sub_id]
    );

    // Se não achou, busca pelo customer_id
    if (!$empresa && $customer_id) {
        $empresa = DB::row(
            'SELECT id, plan_intent, plano_ativo FROM empresas 
             WHERE asaas_customer_id = ?',
            [$customer_id]
        );
    }

    if (!$empresa) return;

    $empresa_id = $empresa['id'];

    // Salva subscription_id e ativa plano se ainda não estiver salvo
    $plano = $empresa['plan_intent'] ?? 'profissional';
    DB::exec(
        'UPDATE empresas SET 
            asaas_subscription_id = ?,
            plano_ativo = ?
         WHERE id = ? AND (asaas_subscription_id IS NULL OR asaas_subscription_id = "")',
        [$sub_id, $plano, $empresa_id]
    );

    // Registra pagamento no histórico
    DB::exec(
        'INSERT INTO asaas_pagamentos
            (empresa_id, asaas_payment_id, valor, status, vencimento, pago_em, criado_em)
         VALUES (?, ?, ?, "CONFIRMED", ?, ?, NOW())
         ON DUPLICATE KEY UPDATE status = "CONFIRMED", pago_em = ?',
        [$empresa_id, $pagamento_id, $valor, $vencimento, $pago_em, $pago_em]
    );

    // Marca webhook como processado
    DB::exec(
        'UPDATE asaas_webhooks_log SET processado = 1
         WHERE evento IN ("PAYMENT_CONFIRMED","PAYMENT_RECEIVED")
         ORDER BY criado_em DESC LIMIT 1'
    );

    // Busca dados para o e-mail
    $emp = DB::row(
        'SELECT e.plano_ativo, e.status AS empresa_status, u.nome, u.email
         FROM empresas e JOIN usuarios u ON u.id = e.usuario_id
         WHERE e.id = ?', [$empresa_id]
    );
    if ($emp && $emp['empresa_status'] === 'aprovada') {
        try {
            $nome               = $emp['nome'];
            $plano              = $emp['plano_ativo'];
            $valor_email        = $valor;
            $proximo_vencimento = date('Y-m-d', strtotime('+1 month'));
            ob_start();
            include __DIR__ . '/../emails/assinatura-confirmada.php';
            $html = ob_get_clean();
            Mailer::send($emp['email'], $nome, 'Plano ' . ucfirst($plano) . ' ativado! 🎉', $html);
        } catch (Exception $ex) { error_log('[mail assinatura-confirmada] ' . $ex->getMessage()); }
    }
}

function handlePagamentoVencido(?string $sub_id, ?string $pagamento_id, float $valor, ?string $vencimento): void
{
    if (!$sub_id) return;

    $empresa = DB::row(
        'SELECT e.id, e.plano_ativo, u.nome, u.email FROM empresas e
         JOIN usuarios u ON u.id = e.usuario_id
         WHERE e.asaas_subscription_id = ?',
        [$sub_id]
    );
    if (!$empresa) return;

    $empresa_id = $empresa['id'];

    // Atualiza status do pagamento para OVERDUE
    DB::exec(
        'INSERT INTO asaas_pagamentos
            (empresa_id, asaas_payment_id, valor, status, vencimento, criado_em)
         VALUES (?, ?, ?, "OVERDUE", ?, NOW())
         ON DUPLICATE KEY UPDATE status = "OVERDUE"',
        [$empresa_id, $pagamento_id, $valor, $vencimento]
    );

    // Loga o evento
    DB::exec(
        'INSERT INTO empresa_logs (empresa_id, acao, detalhe, criado_em)
         VALUES (?, "pagamento_vencido", ?, NOW())',
        [$empresa_id, 'Cobrança vencida em ' . $vencimento . ' — valor R$ ' . number_format($valor, 2, ',', '.')]
    );

    // Marca webhook como processado
    DB::exec(
        'UPDATE asaas_webhooks_log SET processado = 1
         WHERE evento = "PAYMENT_OVERDUE"
         ORDER BY criado_em DESC LIMIT 1'
    );

    // E-mail de aviso (implementaremos na Fase 6)
    try {
        $nome    = $empresa['nome'];
        $plano = $empresa['plano_ativo'];
        $valor_email = $valor;
        ob_start();
        include __DIR__ . '/../emails/cobranca-vencida.php';
        $html = ob_get_clean();
        Mailer::send($empresa['email'], $nome, 'Sua cobrança está em atraso', $html);
    } catch (Exception $ex) { error_log('[mail cobranca-vencida] ' . $ex->getMessage()); }
}


function handleAssinaturaCancel(?string $sub_id): void
{
    if (!$sub_id) return;

    // Query atualizada — busca nome e email também
    $empresa = DB::row(
        'SELECT e.id, e.plano_ativo, u.nome, u.email
         FROM empresas e JOIN usuarios u ON u.id = e.usuario_id
         WHERE e.asaas_subscription_id = ?',
        [$sub_id]
    );
    if (!$empresa) return;

    $empresa_id = $empresa['id'];

    // Rebaixa para essencial e limpa subscription_id
    DB::exec(
        'UPDATE empresas SET plano_ativo = "essencial", asaas_subscription_id = NULL
         WHERE id = ?',
        [$empresa_id]
    );

    // Loga o evento
    DB::exec(
        'INSERT INTO empresa_logs (empresa_id, acao, detalhe, criado_em)
         VALUES (?, "assinatura_cancelada", "Assinatura cancelada via webhook Asaas.", NOW())',
        [$empresa_id]
    );

    // Marca webhook como processado
    DB::exec(
        'UPDATE asaas_webhooks_log SET processado = 1
         WHERE evento = "SUBSCRIPTION_DELETED"
         ORDER BY criado_em DESC LIMIT 1'
    );

    // E-mail de confirmação de cancelamento
    try {
        $nome  = $empresa['nome'];
        $plano = $empresa['plano_ativo'];
        ob_start();
        include __DIR__ . '/../emails/assinatura-cancelada.php';
        $html = ob_get_clean();
        Mailer::send($empresa['email'], $nome, 'Sua assinatura foi cancelada', $html);
    } catch (Exception $ex) { error_log('[mail assinatura-cancelada] ' . $ex->getMessage()); }
}

function handlePagamentoReembolsado(?string $sub_id, ?string $pagamento_id): void
{
    if (!$sub_id) return;

    $empresa = DB::row(
        'SELECT e.id, e.plano_ativo, u.nome, u.email
         FROM empresas e JOIN usuarios u ON u.id = e.usuario_id
         WHERE e.asaas_subscription_id = ?',
        [$sub_id]
    );
    if (!$empresa) return;

    $empresa_id = $empresa['id'];

    // Atualiza status do pagamento para REFUNDED
    DB::exec(
        'UPDATE asaas_pagamentos SET status = "REFUNDED"
         WHERE asaas_payment_id = ?',
        [$pagamento_id]
    );

    // Rebaixa para essencial
    DB::exec(
        'UPDATE empresas SET plano_ativo = "essencial", asaas_subscription_id = NULL
         WHERE id = ?',
        [$empresa_id]
    );

    // Loga o evento
    DB::exec(
        'INSERT INTO empresa_logs (empresa_id, acao, detalhe, criado_em)
         VALUES (?, "pagamento_reembolsado", "Pagamento reembolsado via webhook Asaas.", NOW())',
        [$empresa_id]
    );

    // Marca webhook como processado
    DB::exec(
        'UPDATE asaas_webhooks_log SET processado = 1
         WHERE evento = "PAYMENT_REFUNDED"
         ORDER BY criado_em DESC LIMIT 1'
    );
    
    try {
        $nome        = $empresa['nome'];
        $plano       = $empresa['plano_ativo'];
        $valor_email = 0; // será atualizado quando buscarmos o valor do pagamento
        ob_start();
        include __DIR__ . '/../emails/pagamento-reembolsado.php';
        $html = ob_get_clean();
        Mailer::send($empresa['email'], $nome, 'Seu reembolso foi processado', $html);
    } catch (Exception $ex) { error_log('[mail pagamento-reembolsado] ' . $ex->getMessage()); }
    }