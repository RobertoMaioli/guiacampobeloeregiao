<?php
/**
 * empresa/actions/salvar.php
 * Salva rascunho do lugar via AJAX (JSON)
 */
require_once __DIR__ . '/../../core/UserAuth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

header('Content-Type: application/json');
UserAuth::require();

$raw = json_decode(file_get_contents('php://input'), true) ?? [];

if (!Sanitize::csrfValid($raw['_token'] ?? '')) {
    echo json_encode(['ok'=>false,'erro'=>'Token inválido.']); exit;
}

$usuario    = UserAuth::current();
$empresa_id = (int)($usuario['empresa_id'] ?? 0);
$lugar_id   = (int)($raw['lugar_id'] ?? 0);

if (!$empresa_id) {
    echo json_encode(['ok'=>false,'erro'=>'Empresa não encontrada.']); exit;
}

$nome        = Sanitize::str($raw['nome'] ?? '');
$cat_id      = (int)($raw['categoria_id'] ?? 0);
$cat_label   = Sanitize::str($raw['cat_label'] ?? '');
$descricao   = Sanitize::str($raw['descricao'] ?? '', 2000);
$endereco    = Sanitize::str($raw['endereco'] ?? '');
$bairro      = Sanitize::str($raw['bairro'] ?? '');
$cep         = Sanitize::str($raw['cep'] ?? '');
$telefone    = Sanitize::str($raw['telefone'] ?? '');
$whatsapp    = Sanitize::str($raw['whatsapp'] ?? '');
$site        = Sanitize::str($raw['site'] ?? '');
$instagram   = Sanitize::str($raw['instagram'] ?? '');

if (!$nome) {
    echo json_encode(['ok'=>false,'erro'=>'Nome obrigatório.']); exit;
}

// Gera slug único
$slug_base = Sanitize::slug($nome);
$slug      = $slug_base;
$suffix    = 1;
while (DB::row('SELECT id FROM lugares WHERE slug=? AND id<>?',[$slug, $lugar_id ?: 0])) {
    $slug = $slug_base . '-' . $suffix++;
}

try {
    if ($lugar_id) {
        // Atualiza rascunho existente
        DB::exec(
            'UPDATE lugares SET
                nome=?, slug=?, categoria_id=?, cat_label=?, descricao=?,
                endereco=?, bairro=?, cep=?, telefone=?, whatsapp=?,
                site=?, instagram=?, atualizado_em=NOW()
             WHERE id=? AND empresa_id=?',
            [$nome,$slug,$cat_id?:null,$cat_label,$descricao,
             $endereco,$bairro,$cep,$telefone,$whatsapp,
             $site,$instagram,$lugar_id,$empresa_id]
        );
    } else {
        // Cria novo rascunho
        DB::exec(
            'INSERT INTO lugares
                (empresa_id,nome,slug,categoria_id,cat_label,descricao,
                 endereco,bairro,cep,telefone,whatsapp,site,instagram,
                 ativo,plano,criado_em)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,0,"essencial",NOW())',
            [$empresa_id,$nome,$slug,$cat_id?:null,$cat_label,$descricao,
             $endereco,$bairro,$cep,$telefone,$whatsapp,$site,$instagram]
        );
        $lugar_id = (int)DB::lastId();

        // Vincula lugar à empresa
        DB::exec('UPDATE empresas SET lugar_id=? WHERE id=?',[$lugar_id,$empresa_id]);
    }

    echo json_encode(['ok'=>true,'lugar_id'=>$lugar_id]);

} catch (Exception $e) {
    error_log('[salvar] '.$e->getMessage());
    echo json_encode(['ok'=>false,'erro'=>'Erro ao salvar.']);
}