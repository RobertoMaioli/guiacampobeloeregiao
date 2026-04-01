<?php
/**
 * api/lugares.php
 * GET /api/lugares.php
 *
 * Parâmetros:
 *   ?q=busca          → busca por nome
 *   ?cat=restaurantes → filtra categoria
 *   ?preco=alto       → filtra preço
 *   ?destaque=1       → só destaques
 *   ?limit=12         → paginação
 *   ?offset=0
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Sanitize.php';

// ── Params ──
$q        = Sanitize::get('q');
$cat      = Sanitize::get('cat');
$preco    = Sanitize::get('preco');
$sort     = Sanitize::get('sort');          // ← adicionar
$destaque = Sanitize::get('destaque', 'int', 0);
$limit    = min(Sanitize::get('limit', 'int', 12), 50);
$offset   = Sanitize::get('offset', 'int', 0);

// ── Build query ──
$where  = ['l.ativo = 1'];
$params = [];

if ($q !== '') {
    $where[]  = '(l.nome LIKE ? OR l.descricao LIKE ? OR l.endereco LIKE ?)';
    $like = '%' . $q . '%';
    $params = array_merge($params, [$like, $like, $like]);
}

if ($cat !== '' && $cat !== 'todos') {
    $where[]  = 'c.slug = ?';
    $params[] = $cat;
}

if ($preco !== '' && $preco !== 'todos') {
    $where[]  = 'l.preco_nivel = ?';
    $params[] = $preco;
}

if ($destaque) {
    $where[] = "l.plano = 'premium'";
}

$whereSQL = implode(' AND ', $where);

// ── Total count ──
$total = (int) DB::row(
    "SELECT COUNT(*) as n
     FROM lugares l
     JOIN categorias c ON c.id = l.categoria_id
     WHERE $whereSQL",
    $params
)['n'];

// ── Ordenação ──
$order_sql = match($sort) {
    'avaliacao'  => 'l.rating DESC, l.total_reviews DESC',
    'novo'       => 'l.criado_em DESC',
    'preco-asc'  => 'l.preco_nivel ASC',
    'preco-desc' => 'l.preco_nivel DESC',
    'destaque'   => "CASE WHEN l.plano = 'premium' THEN 0 ELSE 1 END, l.rating DESC",
    default      => 'l.rating DESC',
};

// ── Results ──
$rows = DB::query(
    "SELECT
       l.id, l.slug, l.nome, l.cat_label, l.badge,
       l.endereco, l.bairro, l.telefone, l.instagram,
       l.preco_nivel, l.preco_simbolo, l.preco_range,
       l.rating, l.total_reviews,
       l.lat, l.lng, l.destaque,
       c.slug  AS cat_slug,
       c.label AS cat_nome,
       c.icon  AS cat_icon,
       f.url   AS foto_capa,
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
     LEFT JOIN fotos f ON f.lugar_id = l.id AND f.principal = 1
     WHERE $whereSQL
     ORDER BY $order_sql
     LIMIT ? OFFSET ?",
    array_merge($params, [$limit, $offset])
);

// ── Tags por lugar ──
if (!empty($rows)) {
    $ids = array_column($rows, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $tagRows = DB::query(
        "SELECT lt.lugar_id, t.label
         FROM lugar_tags lt
         JOIN tags t ON t.id = lt.tag_id
         WHERE lt.lugar_id IN ($placeholders)
         ORDER BY lt.lugar_id",
        $ids
    );
    $tagMap = [];
    foreach ($tagRows as $tr) {
        $tagMap[$tr['lugar_id']][] = $tr['label'];
    }
    foreach ($rows as &$row) {
        $row['tags'] = $tagMap[$row['id']] ?? [];
    }
    unset($row);
}

echo json_encode([
    'ok'     => true,
    'total'  => $total,
    'limit'  => $limit,
    'offset' => $offset,
    'dados'  => $rows,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
