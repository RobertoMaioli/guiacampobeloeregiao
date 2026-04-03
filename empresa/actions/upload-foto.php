<?php
/**
 * empresa/actions/upload-foto.php
 * Upload de fotos via AJAX (multipart)
 */
require_once __DIR__ . '/../../core/UserAuth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';
require_once __DIR__ . '/../../core/Upload.php';
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');
UserAuth::require();

if (!Sanitize::csrfValid($_POST['_token'] ?? '')) {
    echo json_encode(['ok'=>false,'erro'=>'Token inválido.']); exit;
}

$lugar_id = (int)($_POST['lugar_id'] ?? 0);
$usuario  = UserAuth::current();
$emp_id   = (int)($usuario['empresa_id'] ?? 0);
$plan     = $usuario['empresa_plan_intent'] ?? 'essencial';

if (!$lugar_id || !DB::row('SELECT id FROM lugares WHERE id=? AND empresa_id=?',[$lugar_id,$emp_id])) {
    echo json_encode(['ok'=>false,'erro'=>'Acesso negado.']); exit;
}

// Limite por plano
$max     = $plan === 'premium' ? 999 : ($plan === 'profissional' ? 5 : 0);
$atual   = (int)(DB::row('SELECT COUNT(*) n FROM fotos WHERE lugar_id=?',[$lugar_id])['n'] ?? 0);
$restam  = max(0, $max - $atual);

$salvos  = 0;
$erros   = [];

foreach (($_FILES['fotos']['name'] ?? []) as $i => $name) {
    if ($salvos >= $restam) break;
    if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK) continue;

    $file = [
        'name'     => $name,
        'type'     => $_FILES['fotos']['type'][$i],
        'tmp_name' => $_FILES['fotos']['tmp_name'][$i],
        'error'    => $_FILES['fotos']['error'][$i],
        'size'     => $_FILES['fotos']['size'][$i],
    ];

    $res = Upload::image($file, 'lugares/'.$lugar_id.'/');
    if ($res['ok']) {
        $is_principal = ($atual + $salvos === 0) ? 1 : 0;
        DB::exec(
            'INSERT INTO fotos (lugar_id,url,principal,ordem) VALUES (?,?,?,?)',
            [$lugar_id, $res['url'], $is_principal, $atual + $salvos]
        );
        if ($is_principal) {
            DB::exec('UPDATE lugares SET foto_principal=? WHERE id=?',[$res['url'],$lugar_id]);
        }
        $salvos++;
    } else {
        $erros[] = $res['erro'];
    }
}

echo json_encode(['ok'=>true,'salvos'=>$salvos,'erros'=>$erros]);