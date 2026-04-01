<?php
/**
 * empresa/actions/horarios.php
 * Salva horários via AJAX
 */
require_once __DIR__ . '/../../core/UserAuth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

header('Content-Type: application/json');
UserAuth::require();

$raw = json_decode(file_get_contents('php://input'), true) ?? [];

if (!Sanitize::csrfValid($raw['_token'] ?? '')) {
    echo json_encode(['ok'=>false]); exit;
}

$lugar_id = (int)($raw['lugar_id'] ?? 0);
$usuario  = UserAuth::current();
$emp_id   = (int)($usuario['empresa_id'] ?? 0);

// Verifica ownership
if (!$lugar_id || !DB::row('SELECT id FROM lugares WHERE id=? AND empresa_id=?',[$lugar_id,$emp_id])) {
    echo json_encode(['ok'=>false,'erro'=>'Acesso negado.']); exit;
}

$horarios = $raw['horarios'] ?? [];

try {
    foreach ($horarios as $h) {
        $d        = (int)($h['dia'] ?? 0);
        $fechado  = (int)($h['fechado']  ?? 0);
        $dia_todo = (int)($h['dia_todo'] ?? 0);
        $abre     = $h['abre']  ?: null;
        $fecha    = $h['fecha'] ?: null;

        DB::exec(
            'REPLACE INTO horarios (lugar_id,dia_semana,hora_abre,hora_fecha,fechado,dia_todo)
             VALUES (?,?,?,?,?,?)',
            [$lugar_id,$d,
             ($fechado||$dia_todo)?null:$abre,
             ($fechado||$dia_todo)?null:$fecha,
             $fechado,$dia_todo]
        );
    }
    echo json_encode(['ok'=>true]);
} catch(Exception $e) {
    error_log('[horarios] '.$e->getMessage());
    echo json_encode(['ok'=>false]);
}