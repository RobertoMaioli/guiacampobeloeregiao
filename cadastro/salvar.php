<?php
/**
 * cadastro/salvar.php
 * Recebe a ficha de cadastro de campo (JSON), valida, salva a foto
 * da fachada em disco e grava o registro na tabela `prospects`.
 *
 * Endpoint público (sem login) — protegido por rate limit por IP.
 */
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Sanitize.php';
require_once __DIR__ . '/../core/RateLimit.php';
require_once __DIR__ . '/../config/database.php'; // UPLOAD_DIR / UPLOAD_URL

header('Content-Type: application/json; charset=utf-8');

function responder(int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['ok' => false, 'erro' => 'Método não permitido.']);
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// No máximo 20 cadastros por IP a cada 10 minutos — suficiente para um dia
// de visitas de campo, mas trava abuso/spam do endpoint público.
if (!RateLimit::allow('cadastro_prospect', $ip, 20, 600)) {
    responder(429, ['ok' => false, 'erro' => 'Muitos envios em pouco tempo. Aguarde alguns minutos.']);
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    responder(400, ['ok' => false, 'erro' => 'Corpo da requisição inválido.']);
}

function s($v, int $max = 255): string {
    return Sanitize::str(is_string($v) ? $v : '', $max);
}

// ── Validação server-side dos campos obrigatórios (espelha o front) ──
$nome_fantasia = s($data['nome_fantasia'] ?? '', 160);
$categoria     = s($data['categoria'] ?? '', 60);
$endereco      = s($data['endereco'] ?? '', 200);
$regiao        = s($data['regiao'] ?? '', 60);
$whatsapp      = s($data['whatsapp'] ?? '', 20);
$consentimento = s($data['consentimento'] ?? '', 160);
$fotoBase64    = is_string($data['foto_fachada'] ?? null) ? $data['foto_fachada'] : '';
$vendedor      = s($data['vendedor'] ?? '', 120);

$faltando = [];
if ($nome_fantasia === '')                                       $faltando[] = 'nome_fantasia';
if ($categoria === '')                                            $faltando[] = 'categoria';
if ($endereco === '')                                             $faltando[] = 'endereco';
if ($regiao === '')                                                $faltando[] = 'regiao';
if (strlen(preg_replace('/\D/', '', $whatsapp)) < 10)             $faltando[] = 'whatsapp';
if (stripos($consentimento, 'sim') !== 0)                          $faltando[] = 'consentimento';
if ($fotoBase64 === '' || !str_contains($fotoBase64, 'base64,'))   $faltando[] = 'foto';
if ($vendedor === '')                                              $faltando[] = 'vendedor';

if ($faltando) {
    responder(422, ['ok' => false, 'erro' => 'Campos obrigatórios ausentes.', 'campos' => $faltando]);
}

// ── Decodifica e salva a foto da fachada ──
$fotoUrl = '';
if (preg_match('/^data:image\/(jpeg|jpg|png);base64,(.+)$/', $fotoBase64, $m)) {
    $ext = $m[1] === 'png' ? 'png' : 'jpg';
    $bin = base64_decode($m[2], true);

    // limite de 8MB para a imagem decodificada (o front já comprime para ~1600px)
    if ($bin !== false && strlen($bin) > 0 && strlen($bin) <= 8 * 1024 * 1024) {
        $dir = rtrim(UPLOAD_DIR, '/') . '/prospects/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = uniqid('prospect_', true) . '.' . $ext;
        if (file_put_contents($dir . $filename, $bin) !== false) {
            $fotoUrl = rtrim(UPLOAD_URL, '/') . '/prospects/' . $filename;
        }
    }
}

if ($fotoUrl === '') {
    responder(422, ['ok' => false, 'erro' => 'Não foi possível salvar a foto da fachada.']);
}

// ── Monta o registro (demais campos, todos opcionais e sanitizados) ──
$campos = [
    'vendedor'             => $vendedor,
    'nome_fantasia'        => $nome_fantasia,
    'razao_social'         => s($data['razao_social'] ?? '', 200),
    'cnpj'                 => s($data['cnpj'] ?? '', 20),
    'categoria'            => $categoria,
    'subcategoria'         => s($data['subcategoria'] ?? '', 120),
    'ano_fundacao'         => s($data['ano_fundacao'] ?? '', 4),
    'endereco'             => $endereco,
    'complemento'          => s($data['complemento'] ?? '', 120),
    'bairro'               => s($data['bairro'] ?? '', 100),
    'cep'                  => s($data['cep'] ?? '', 10),
    'regiao'               => $regiao,
    'link_maps'            => s($data['link_maps'] ?? '', 300),
    'decisor'              => s($data['decisor'] ?? '', 120),
    'cargo'                => s($data['cargo'] ?? '', 60),
    'whatsapp'             => $whatsapp,
    'telefone_fixo'        => s($data['telefone_fixo'] ?? '', 20),
    'email'                => s($data['email'] ?? '', 160),
    'instagram'            => s($data['instagram'] ?? '', 100),
    'seguidores'           => s($data['seguidores'] ?? '', 20),
    'site'                 => s($data['site'] ?? '', 160),
    'delivery'             => s($data['delivery'] ?? '', 120),
    'nota_google'          => s($data['nota_google'] ?? '', 60),
    'horario'              => s($data['horario'] ?? '', 200),
    'dia_fechamento'       => s($data['dia_fechamento'] ?? '', 120),
    'faixa_preco'          => s($data['faixa_preco'] ?? '', 10),
    'ticket_medio'         => s($data['ticket_medio'] ?? '', 30),
    'capacidade'           => s($data['capacidade'] ?? '', 20),
    'formas_pagamento'     => s($data['formas_pagamento'] ?? '', 160),
    'reservas'             => s($data['reservas'] ?? '', 30),
    'estacionamento'       => s($data['estacionamento'] ?? '', 20),
    'acessibilidade'       => s($data['acessibilidade'] ?? '', 20),
    'pet_friendly'         => s($data['pet_friendly'] ?? '', 20),
    'dias_abertos'         => s($data['dias_abertos'] ?? '', 200),
    'hora_abre'            => s($data['hora_abre'] ?? '', 10),
    'hora_fecha'           => s($data['hora_fecha'] ?? '', 10),
    'hora_abre_fds'        => s($data['hora_abre_fds'] ?? '', 10),
    'hora_fecha_fds'       => s($data['hora_fecha_fds'] ?? '', 10),
    'horario_por_dia'      => s($data['horario_por_dia'] ?? '', 2000),
    'feriado_atende'       => s($data['feriado_atende'] ?? '', 10),
    'feriado_abre'         => s($data['feriado_abre'] ?? '', 10),
    'feriado_fecha'        => s($data['feriado_fecha'] ?? '', 10),
    'origem_lead'          => s($data['origem_lead'] ?? '', 60),
    'convidar_connect'     => s($data['convidar_connect'] ?? '', 20),
    'potencial_patrocinio' => s($data['potencial_patrocinio'] ?? '', 20),
    'foto_fachada'         => $fotoUrl,
    'observacoes'          => s($data['observacoes'] ?? '', 4000),
    'consentimento'        => $consentimento,
    'status'               => 'Prospect',
    'ip_origem'            => $ip,
];

$colunas      = implode(', ', array_keys($campos));
$interrogacao = implode(', ', array_fill(0, count($campos), '?'));

try {
    DB::exec("INSERT INTO prospects ($colunas) VALUES ($interrogacao)", array_values($campos));
    $novoId = DB::lastId();
} catch (Exception $e) {
    error_log('[cadastro/salvar] ' . $e->getMessage());
    responder(500, ['ok' => false, 'erro' => 'Erro ao gravar o cadastro. Tente novamente.']);
}

responder(200, ['ok' => true, 'id' => $novoId]);