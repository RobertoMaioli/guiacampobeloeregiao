<?php
/**
 * api/categorias.php
 * GET /api/categorias.php — lista todas as categorias ativas com contagem de lugares
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../core/DB.php';

$categorias = DB::query(
    "SELECT
       c.id, c.slug, c.label, c.icon, c.ordem,
       COUNT(l.id) AS total_lugares
     FROM categorias c
     LEFT JOIN lugares l ON l.categoria_id = c.id AND l.ativo = 1
     WHERE c.ativo = 1
     GROUP BY c.id
     ORDER BY c.ordem, c.label"
);

echo json_encode([
    'ok'    => true,
    'dados' => $categorias,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
