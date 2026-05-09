<?php
/**
 * empresa/actions/evento-inscricao.php
 * Salva pré-cadastro silencioso no Guia e registra lead do evento
 */
require_once __DIR__ . '/../../core/DB.php';

header('Content-Type: application/json');

$raw  = json_decode(file_get_contents('php://input'), true) ?? [];

$nome           = trim($raw['nome']         ?? '');
$email          = filter_var(trim($raw['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$whatsapp       = preg_replace('/\D/', '', $raw['whatsapp']      ?? '');
$documento      = preg_replace('/\D/', '', $raw['documento']     ?? '');
$tipo_documento = ($raw['tipo_documento']   ?? 'cpf') === 'cnpj' ? 'cnpj' : 'cpf';
$empresa_nome   = trim($raw['empresa_nome'] ?? '');
$evento_id      = trim($raw['evento_id']    ?? '');

if (!$nome || !$email || strlen($whatsapp) < 10) {
    echo json_encode(['ok' => false]); exit;
}

// ── 1. SALVA LEAD DO EVENTO (usado pelo webhook para recuperar e-mail) ──
try {
    DB::exec(
        'INSERT INTO evento_leads
            (evento_id, nome, email, whatsapp, documento, tipo_documento, empresa_nome, criado_em)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE
            nome          = VALUES(nome),
            whatsapp      = VALUES(whatsapp),
            documento     = VALUES(documento),
            empresa_nome  = VALUES(empresa_nome)',
        [$evento_id, $nome, $email, $whatsapp, $documento, $tipo_documento, $empresa_nome]
    );
} catch (Exception $e) {
    error_log('[evento-inscricao] lead: ' . $e->getMessage());
}

// ── 2. PRÉ-CADASTRO SILENCIOSO NO GUIA ─────────────────────────────────
try {
    $existe = DB::row('SELECT id FROM usuarios WHERE email = ?', [$email]);
    if (!$existe) {
        $senha_temp = password_hash(bin2hex(random_bytes(12)), PASSWORD_BCRYPT, ['cost' => 10]);
        DB::beginTransaction();
        DB::exec(
            'INSERT INTO usuarios (nome, email, senha_hash, telefone, plan_intent, criado_em)
             VALUES (?, ?, ?, ?, "essencial", NOW())',
            [$nome, $email, $senha_temp, $whatsapp]
        );
        $uid = (int) DB::lastId();
        DB::exec(
            'INSERT INTO empresas (usuario_id, plan_intent, status, criado_em)
             VALUES (?, "essencial", "rascunho", NOW())',
            [$uid]
        );
        DB::exec(
            'INSERT INTO empresa_logs (empresa_id, acao, detalhe, criado_em)
             SELECT id, "precadastro_evento", ?, NOW() FROM empresas WHERE usuario_id = ?',
            ["Evento: {$evento_id} | Empresa: {$empresa_nome}", $uid]
        );
        DB::commit();
    }
} catch (Exception $e) {
    DB::rollback();
    error_log('[evento-inscricao] pre-cadastro: ' . $e->getMessage());
}

echo json_encode(['ok' => true]);