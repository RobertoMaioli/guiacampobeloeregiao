<?php
/**
 * webhook/asaas.php
 * Endpoint público que recebe eventos do Asaas
 */
require_once __DIR__ . '/../config/asaas.php';
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Mailer.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

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

$payment      = $payload['payment']      ?? [];
$sub_id       = $payment['subscription'] ?? null;
$valor        = $payment['value']        ?? 0;
$pagamento_id = $payment['id']           ?? null;
$vencimento   = $payment['dueDate']      ?? null;
$pago_em      = $payment['paymentDate']  ?? null;

match($evento) {
    'PAYMENT_CONFIRMED',
    'PAYMENT_RECEIVED'     => handlePagamentoConfirmado($sub_id, $pagamento_id, $valor, $vencimento, $pago_em),
    'PAYMENT_OVERDUE'      => handlePagamentoVencido($sub_id, $pagamento_id, $valor, $vencimento),
    'SUBSCRIPTION_DELETED' => handleAssinaturaCancel($sub_id),
    'PAYMENT_REFUNDED'     => handlePagamentoReembolsado($sub_id, $pagamento_id),
    default                => null
};


// ── Funções de processamento ─────────────────────────────────────────

function handlePagamentoConfirmado(?string $sub_id, ?string $pagamento_id, float $valor, ?string $vencimento, ?string $pago_em): void
{
    $payload      = $GLOBALS['payload'];
    $customer_id  = $payload['payment']['customer']    ?? null;
    $payment_link = $payload['payment']['paymentLink'] ?? null;

    // ── Evento Guia Connect — trata separado e encerra ─────────────────
    if ($payment_link === ASAAS_PAYMENT_LINK_ID) {
        handleEventoGuiaConnect($pagamento_id, $customer_id);
        return;
    }
    // ──────────────────────────────────────────────────────────────────

    if (!$sub_id) return;

    $empresa = DB::row(
        'SELECT id, plan_intent, plano_ativo FROM empresas 
         WHERE asaas_subscription_id = ?',
        [$sub_id]
    );

    if (!$empresa && $customer_id) {
        $empresa = DB::row(
            'SELECT id, plan_intent, plano_ativo FROM empresas 
             WHERE asaas_customer_id = ?',
            [$customer_id]
        );
    }

    if (!$empresa) return;

    $empresa_id = $empresa['id'];

    $plano = $empresa['plan_intent'] ?? 'profissional';
    DB::exec(
        'UPDATE empresas SET 
            asaas_subscription_id = ?,
            plano_ativo = ?
         WHERE id = ? AND (asaas_subscription_id IS NULL OR asaas_subscription_id = "")',
        [$sub_id, $plano, $empresa_id]
    );

    DB::exec(
        'INSERT INTO asaas_pagamentos
            (empresa_id, asaas_payment_id, valor, status, vencimento, pago_em, criado_em)
         VALUES (?, ?, ?, "CONFIRMED", ?, ?, NOW())
         ON DUPLICATE KEY UPDATE status = "CONFIRMED", pago_em = ?',
        [$empresa_id, $pagamento_id, $valor, $vencimento, $pago_em, $pago_em]
    );

    DB::exec(
        'UPDATE asaas_webhooks_log SET processado = 1
         WHERE evento IN ("PAYMENT_CONFIRMED","PAYMENT_RECEIVED")
         ORDER BY criado_em DESC LIMIT 1'
    );

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
        } catch (Exception $ex) {
            error_log('[mail assinatura-confirmada] ' . $ex->getMessage());
        }
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

    DB::exec(
        'INSERT INTO asaas_pagamentos
            (empresa_id, asaas_payment_id, valor, status, vencimento, criado_em)
         VALUES (?, ?, ?, "OVERDUE", ?, NOW())
         ON DUPLICATE KEY UPDATE status = "OVERDUE"',
        [$empresa_id, $pagamento_id, $valor, $vencimento]
    );

    DB::exec(
        'INSERT INTO empresa_logs (empresa_id, acao, detalhe, criado_em)
         VALUES (?, "pagamento_vencido", ?, NOW())',
        [$empresa_id, 'Cobrança vencida em ' . $vencimento . ' — valor R$ ' . number_format($valor, 2, ',', '.')]
    );

    DB::exec(
        'UPDATE asaas_webhooks_log SET processado = 1
         WHERE evento = "PAYMENT_OVERDUE"
         ORDER BY criado_em DESC LIMIT 1'
    );

    try {
        $nome        = $empresa['nome'];
        $plano       = $empresa['plano_ativo'];
        $valor_email = $valor;
        ob_start();
        include __DIR__ . '/../emails/cobranca-vencida.php';
        $html = ob_get_clean();
        Mailer::send($empresa['email'], $nome, 'Sua cobrança está em atraso', $html);
    } catch (Exception $ex) {
        error_log('[mail cobranca-vencida] ' . $ex->getMessage());
    }
}

function handleAssinaturaCancel(?string $sub_id): void
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

    DB::exec(
        'UPDATE empresas SET plano_ativo = "essencial", asaas_subscription_id = NULL
         WHERE id = ?',
        [$empresa_id]
    );

    DB::exec(
        'INSERT INTO empresa_logs (empresa_id, acao, detalhe, criado_em)
         VALUES (?, "assinatura_cancelada", "Assinatura cancelada via webhook Asaas.", NOW())',
        [$empresa_id]
    );

    DB::exec(
        'UPDATE asaas_webhooks_log SET processado = 1
         WHERE evento = "SUBSCRIPTION_DELETED"
         ORDER BY criado_em DESC LIMIT 1'
    );

    try {
        $nome  = $empresa['nome'];
        $plano = $empresa['plano_ativo'];
        ob_start();
        include __DIR__ . '/../emails/assinatura-cancelada.php';
        $html = ob_get_clean();
        Mailer::send($empresa['email'], $nome, 'Sua assinatura foi cancelada', $html);
    } catch (Exception $ex) {
        error_log('[mail assinatura-cancelada] ' . $ex->getMessage());
    }
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

    DB::exec(
        'UPDATE asaas_pagamentos SET status = "REFUNDED"
         WHERE asaas_payment_id = ?',
        [$pagamento_id]
    );

    DB::exec(
        'UPDATE empresas SET plano_ativo = "essencial", asaas_subscription_id = NULL
         WHERE id = ?',
        [$empresa_id]
    );

    DB::exec(
        'INSERT INTO empresa_logs (empresa_id, acao, detalhe, criado_em)
         VALUES (?, "pagamento_reembolsado", "Pagamento reembolsado via webhook Asaas.", NOW())',
        [$empresa_id]
    );

    DB::exec(
        'UPDATE asaas_webhooks_log SET processado = 1
         WHERE evento = "PAYMENT_REFUNDED"
         ORDER BY criado_em DESC LIMIT 1'
    );

    try {
        $nome        = $empresa['nome'];
        $plano       = $empresa['plano_ativo'];
        $valor_email = 0;
        ob_start();
        include __DIR__ . '/../emails/pagamento-reembolsado.php';
        $html = ob_get_clean();
        Mailer::send($empresa['email'], $nome, 'Seu reembolso foi processado', $html);
    } catch (Exception $ex) {
        error_log('[mail pagamento-reembolsado] ' . $ex->getMessage());
    }
}


// ── Evento Guia Connect ───────────────────────────────────────────────────

function handleEventoGuiaConnect(?string $pagamento_id, ?string $customer_id): void
{
    error_log('[guia-connect] iniciando — payment: ' . $pagamento_id . ' customer: ' . $customer_id);

    if (!$pagamento_id || !$customer_id) {
        error_log('[guia-connect] pagamento_id ou customer_id vazio');
        return;
    }

    // Evita processar duas vezes
    $jaProcessado = DB::row(
        'SELECT id FROM evento_inscricoes WHERE asaas_payment_id = ?',
        [$pagamento_id]
    );
    if ($jaProcessado) {
        error_log('[guia-connect] já processado: ' . $pagamento_id);
        return;
    }

    // Busca e-mail na tabela de leads primeiro
    $lead = DB::row(
        'SELECT nome, email FROM evento_leads
         WHERE evento_id = ?
         ORDER BY criado_em DESC LIMIT 1',
        ['guia-connect-soft-opening-mai25']
    );

    if ($lead) {
        $nome_cliente  = $lead['nome'];
        $email_cliente = $lead['email'];
        error_log('[guia-connect] e-mail obtido do lead: ' . $email_cliente);
    } else {
        // Fallback: busca no Asaas
        error_log('[guia-connect] lead não encontrado, buscando no Asaas: ' . $customer_id);
        $ch = curl_init(ASAAS_BASE_URL . "/customers/{$customer_id}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => ['access_token: ' . ASAAS_KEY],
        ]);
        $resp          = curl_exec($ch);
        $curl_erro     = curl_error($ch);
        curl_close($ch);

        if ($curl_erro) {
            error_log('[guia-connect] curl erro: ' . $curl_erro);
            return;
        }

        $cliente       = json_decode($resp, true);
        $nome_cliente  = $cliente['name']  ?? 'Participante';
        $email_cliente = $cliente['email'] ?? '';
        error_log('[guia-connect] e-mail obtido do Asaas: ' . $email_cliente);
    }

    if (!$email_cliente) {
        error_log('[guia-connect] e-mail não encontrado: ' . $customer_id);
        return;
    }

    // Gera token único do ingresso
    $token = strtoupper(substr(md5($pagamento_id . $email_cliente . time()), 0, 12));
    error_log('[guia-connect] token gerado: ' . $token);

    // Salva no banco
    try {
        DB::exec(
            'INSERT INTO evento_inscricoes
                (evento_id, nome, email, asaas_payment_id, asaas_customer_id, token, status, criado_em)
             VALUES (?, ?, ?, ?, ?, ?, "confirmado", NOW())',
            ['guia-connect-soft-opening-mai25', $nome_cliente, $email_cliente,
             $pagamento_id, $customer_id, $token]
        );
        error_log('[guia-connect] salvo no banco com sucesso');
    } catch (Exception $e) {
        error_log('[guia-connect] DB erro: ' . $e->getMessage());
        return;
    }

    // Gera QR Code PNG e salva em disco
    $qrConteudo = "GUIACONNECT|{$token}|{$email_cliente}";
    $qrFilename = 'qr_' . $token . '.png';
    $qrPath     = __DIR__ . '/../uploads/qrcodes/' . $qrFilename;
    $qrUrl      = SITE_URL . '/uploads/qrcodes/' . $qrFilename;

    error_log('[guia-connect] gerando QR Code em: ' . $qrPath);

    try {
        $qrCode = new QrCode(
            data                : $qrConteudo,
            encoding            : new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size                : 300,
            margin              : 20,
            foregroundColor     : new Color(42, 48, 34),
            backgroundColor     : new Color(255, 255, 255)
        );
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        file_put_contents($qrPath, $result->getString());
        error_log('[guia-connect] QR Code gerado com sucesso');
    } catch (Exception $e) {
        error_log('[guia-connect] QR Code erro: ' . $e->getMessage());
        return;
    }

    // Atualiza token no banco
    DB::exec(
        'UPDATE evento_inscricoes SET qrcode_token = ? WHERE asaas_payment_id = ?',
        [$token, $pagamento_id]
    );

    // Envia e-mail com QR Code usando o layout padrão do Guia
    try {
        ob_start();
        include __DIR__ . '/../emails/evento-ingresso.php';
        $html = ob_get_clean();
        Mailer::send(
            $email_cliente,
            $nome_cliente,
            '🎉 Seu ingresso — Guia Connect Soft Opening',
            $html
        );
        error_log('[guia-connect] e-mail enviado para: ' . $email_cliente);
    } catch (Exception $ex) {
        error_log('[guia-connect] e-mail erro: ' . $ex->getMessage());
    }

    // Marca como processado no log
    DB::exec(
        'UPDATE asaas_webhooks_log SET processado = 1
         WHERE evento IN ("PAYMENT_CONFIRMED","PAYMENT_RECEIVED")
         AND payload LIKE ?
         ORDER BY criado_em DESC LIMIT 1',
        ['%' . $pagamento_id . '%']
    );

    error_log('[guia-connect] concluído com sucesso');
}