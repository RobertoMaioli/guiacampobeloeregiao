<?php
/**
 * admin/google-sync.php
 * Endpoint AJAX chamado pelo botão "Sincronizar Google" no admin
 * Retorna JSON
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Sanitize.php';
require_once __DIR__ . '/../core/Google.php';

// Só admin autenticado
Auth::require();

// Só POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'erro' => 'Método não permitido.']);
    exit;
}

// CSRF
if (!Sanitize::csrfValid($_POST['_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'erro' => 'Token inválido.']);
    exit;
}

$modo     = Sanitize::post('modo');      // 'um' ou 'todos'
$lugarId  = Sanitize::post('lugar_id', 'int');

try {
    if ($modo === 'todos') {
        $resultados = Google::syncAll();
        $ok      = count(array_filter($resultados, fn($r) => $r['ok']));
        $erro    = count(array_filter($resultados, fn($r) => !$r['ok']));

        echo json_encode([
            'ok'          => true,
            'msg'         => "Sincronização concluída: {$ok} lugar(es) atualizados, {$erro} erro(s).",
            'resultados'  => $resultados,
        ]);
    } else {
        if (!$lugarId) {
            echo json_encode(['ok' => false, 'erro' => 'ID do lugar não informado.']);
            exit;
        }
        $res = Google::syncLugar($lugarId);

        if ($res['ok']) {
            echo json_encode([
                'ok'  => true,
                'msg' => "✓ {$res['nome']} — Nota: {$res['rating']} ({$res['reviews']} reviews) · {$res['inseridas']} nova(s) avaliação(ões)",
            ]);
        } else {
            echo json_encode(['ok' => false, 'erro' => $res['erro']]);
        }
    }
} catch (Exception $e) {
    error_log('[Google Sync] ' . $e->getMessage());
    echo json_encode(['ok' => false, 'erro' => 'Erro interno. Verifique os logs.']);
}