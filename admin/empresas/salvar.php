<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

header('Content-Type: application/json');
Auth::require();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'erro' => 'Método inválido.']); exit;
}

if (!Sanitize::csrfValid($_POST['_token'] ?? '')) {
    echo json_encode(['ok' => false, 'erro' => 'Token inválido.']); exit;
}

$id   = (int)($_POST['usuario_id'] ?? 0);
$acao = $_POST['acao'] ?? '';

if (!$id) {
    echo json_encode(['ok' => false, 'erro' => 'Usuário inválido.']); exit;
}

if ($acao === 'dados') {
    $nome  = Sanitize::post('nome');
    $email = Sanitize::post('email', 'email');

    if (mb_strlen($nome) < 2) { echo json_encode(['ok'=>false,'erro'=>'Nome muito curto.']); exit; }
    if (!$email)               { echo json_encode(['ok'=>false,'erro'=>'E-mail inválido.']); exit; }

    $existe = DB::row('SELECT id FROM usuarios WHERE email = ? AND id != ?', [$email, $id]);
    if ($existe) { echo json_encode(['ok'=>false,'erro'=>'E-mail já em uso.']); exit; }

    DB::exec('UPDATE usuarios SET nome = ?, email = ? WHERE id = ?', [$nome, $email, $id]);
    echo json_encode(['ok' => true, 'msg' => 'Dados atualizados com sucesso.']);
    exit;
}

if ($acao === 'senha') {
    $nova = $_POST['nova_senha'] ?? '';
    $conf = $_POST['conf_senha'] ?? '';

    if (mb_strlen($nova) < 8) { echo json_encode(['ok'=>false,'erro'=>'Senha deve ter pelo menos 8 caracteres.']); exit; }
    if ($nova !== $conf)       { echo json_encode(['ok'=>false,'erro'=>'As senhas não coincidem.']); exit; }

    DB::exec(
        'UPDATE usuarios SET senha_hash = ? WHERE id = ?',
        [password_hash($nova, PASSWORD_BCRYPT, ['cost' => 12]), $id]
    );
    echo json_encode(['ok' => true, 'msg' => 'Senha alterada com sucesso.']);
    exit;
}

if ($acao === 'plano') {
    $plano = $_POST['plano'] ?? '';
    $validos = ['essencial', 'profissional', 'premium'];

    if (!in_array($plano, $validos)) {
        echo json_encode(['ok'=>false,'erro'=>'Plano inválido.']); exit;
    }

    // Busca empresa vinculada ao usuário
    $empresa = DB::row('SELECT id, lugar_id FROM empresas WHERE usuario_id = ?', [$id]);
    if (!$empresa) {
        echo json_encode(['ok'=>false,'erro'=>'Empresa não encontrada.']); exit;
    }

    DB::exec('UPDATE empresas SET plano_ativo = ? WHERE id = ?', [$plano, $empresa['id']]);

    if ($empresa['lugar_id']) {
        DB::exec('UPDATE lugares SET plano = ? WHERE id = ?', [$plano, $empresa['lugar_id']]);
    }

    echo json_encode(['ok' => true, 'msg' => 'Plano atualizado com sucesso.']);
    exit;
}

echo json_encode(['ok' => false, 'erro' => 'Ação inválida.']);