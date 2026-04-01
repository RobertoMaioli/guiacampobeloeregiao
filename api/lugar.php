<?php
/**
 * api/lugar.php
 * GET /api/lugar.php?slug=osteria-moderna
 * GET /api/lugar.php?id=10
 *
 * Retorna todos os dados de um lugar: info, fotos, horários, serviços, avaliações, tags
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Sanitize.php';

$slug = Sanitize::get('slug');
$id   = Sanitize::get('id', 'int', 0);

if ($slug === '' && $id === 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Parâmetro slug ou id obrigatório.']);
    exit;
}

// ── Lugar base ──
$param  = $slug !== '' ? $slug : $id;
$coluna = $slug !== '' ? 'l.slug' : 'l.id';

$lugar = DB::row(
    "SELECT
       l.*,
       c.slug  AS cat_slug,
       c.label AS cat_nome,
       c.icon  AS cat_icon,
       CASE
         WHEN EXISTS (
           SELECT 1 FROM horarios h
           WHERE h.lugar_id   = l.id
             AND h.dia_semana = DAYOFWEEK(NOW()) - 1
             AND h.fechado    = 0
             AND (h.dia_todo  = 1 OR (h.hora_abre <= TIME(NOW()) AND h.hora_fecha >= TIME(NOW())))
         ) THEN 1 ELSE 0
       END AS aberto_agora
     FROM lugares l
     JOIN categorias c ON c.id = l.categoria_id
     WHERE $coluna = ? AND l.ativo = 1
     LIMIT 1",
    [$param]
);

if (!$lugar) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'erro' => 'Lugar não encontrado.']);
    exit;
}

$id = (int) $lugar['id'];

// ── Fotos ──
$fotos = DB::query(
    'SELECT url, alt, principal FROM fotos
     WHERE lugar_id = ? ORDER BY principal DESC, ordem ASC',
    [$id]
);

// ── Horários ──
$dias = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
$hRows = DB::query(
    'SELECT dia_semana, hora_abre, hora_fecha, fechado, dia_todo
     FROM horarios WHERE lugar_id = ? ORDER BY dia_semana',
    [$id]
);
$horarios = [];
foreach ($hRows as $h) {
    $horarios[$dias[$h['dia_semana']]] = $h['fechado']
        ? 'Fechado'
        : ($h['dia_todo'] ? 'Aberto o dia todo' : substr($h['hora_abre'],0,5) . 'h – ' . substr($h['hora_fecha'],0,5) . 'h');
}

// ── Serviços ──
$servicos = DB::query(
    'SELECT s.nome, s.icon FROM lugar_servicos ls
     JOIN servicos s ON s.id = ls.servico_id
     WHERE ls.lugar_id = ?',
    [$id]
);

// ── Tags ──
$tags = DB::query(
    'SELECT t.slug, t.label FROM lugar_tags lt
     JOIN tags t ON t.id = lt.tag_id
     WHERE lt.lugar_id = ?',
    [$id]
);

// ── Avaliações ──
$avaliacoes = DB::query(
    'SELECT fonte, autor_nome, autor_foto, nota, texto, data_avaliacao
     FROM avaliacoes
     WHERE lugar_id = ? AND aprovado = 1
     ORDER BY data_avaliacao DESC
     LIMIT 10',
    [$id]
);

// ── Similares ──
$similares = DB::query(
    "SELECT l.id, l.slug, l.nome, l.cat_label, l.preco_simbolo,
            l.rating, l.total_reviews, l.endereco,
            c.slug AS cat_slug, f.url AS foto_capa
     FROM lugares l
     JOIN categorias c ON c.id = l.categoria_id
     LEFT JOIN fotos f ON f.lugar_id = l.id AND f.principal = 1
     WHERE l.categoria_id = ? AND l.id <> ? AND l.ativo = 1
     ORDER BY l.rating DESC LIMIT 3",
    [$lugar['categoria_id'], $id]
);

echo json_encode([
    'ok'        => true,
    'lugar'     => $lugar,
    'fotos'     => $fotos,
    'horarios'  => $horarios,
    'servicos'  => $servicos,
    'tags'      => $tags,
    'avaliacoes'=> $avaliacoes,
    'similares' => $similares,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
