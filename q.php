<?php
/**
 * q.php
 * Redirecionador de QR Code dinâmico.
 *
 * Este é o único endereço que deve ser impresso/gravado no QR Code:
 *   https://SEUDOMINIO.com.br/q.php?c=SLUG
 *
 * O destino (para onde o visitante é enviado) fica salvo no banco
 * e pode ser trocado a qualquer momento pelo painel admin, sem
 * precisar gerar um novo QR Code.
 */
require_once __DIR__ . '/core/DB.php';
require_once __DIR__ . '/core/Sanitize.php';

$slug = Sanitize::get('c', 'slug', '');

if ($slug === '') {
    header('Location: /');
    exit;
}

$qr = DB::row(
    'SELECT id, destino_url FROM qrcodes WHERE slug = ? AND ativo = 1',
    [$slug]
);

if (!$qr || !$qr['destino_url']) {
    // Slug inexistente, inativo ou sem destino definido ainda
    header('Location: /');
    exit;
}

// Conta o clique de forma assíncrona (não atrasa o redirect)
DB::exec('UPDATE qrcodes SET cliques = cliques + 1 WHERE id = ?', [$qr['id']]);

// 302 (temporário) — permite trocar o destino livremente no futuro
header('Location: ' . $qr['destino_url'], true, 302);
exit;