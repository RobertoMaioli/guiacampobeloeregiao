<?php
/**
 * pages/mapa.php — Mapa Interativo
 * Guia Campo Belo & Região
 * Leaflet.js + OpenStreetMap — sem API key
 */
require_once __DIR__ . '/../includes/icons.php';

/* ── Filtros via URL ── */
$slug_cat = $_GET['cat']   ?? 'todos';
$slug_pre = $_GET['preco'] ?? 'todos';
$busca    = $_GET['q']     ?? '';

/* ── Categorias ── */
$categorias = [
    'todos'        => ['label' => 'Todos',         'icon' => 'trending-up'],
    'restaurantes' => ['label' => 'Restaurantes',  'icon' => 'utensils'],
    'cafes'        => ['label' => 'Cafés',          'icon' => 'coffee'],
    'japonesa'     => ['label' => 'Japonesa',       'icon' => 'utensils'],
    'wine-bar'     => ['label' => 'Wine & Bar',     'icon' => 'wine'],
    'brunch'       => ['label' => 'Brunch',         'icon' => 'coffee'],
    'pet'          => ['label' => 'Pet Friendly',   'icon' => 'paw'],
    'bem-estar'    => ['label' => 'Bem-estar',      'icon' => 'spa'],
    'compras'      => ['label' => 'Compras',        'icon' => 'shopping-bag'],
    'beleza'       => ['label' => 'Beleza',         'icon' => 'scissors'],
];

/* ── Faixas de preço ── */
$faixas = [
    'todos'  => 'Todos os preços',
    'barato' => 'Até R$ 60',
    'medio'  => 'R$ 60 – R$ 120',
    'alto'   => 'R$ 120 – R$ 200',
    'luxo'   => 'Acima de R$ 200',
];

/* ── Mock listings com coordenadas
      (Campo Belo / Brooklin / Moema — SP)
      Quando tiver BD: substituir lat/lng por geocoding
── */
$lugares = [
    [
        'id'      => 1,
        'nome'    => 'Osteria Moderna',
        'cat'     => 'restaurantes',
        'cat_label'=> 'Italiana',
        'preco'   => 'alto',
        'preco_label'=> 'R$$$',
        'rating'  => 4.8,
        'reviews' => 247,
        'endereco'=> 'R. Lagoa Santa, 230 — Campo Belo',
        'telefone'=> '(11) 3045-7892',
        'img'     => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=400&q=75',
        'aberto'  => true,
        'badge'   => 'Destaque',
        'slug'    => 'osteria-moderna',
        'lat'     => -23.6185,
        'lng'     => -46.6675,
    ],
    [
        'id'      => 2,
        'nome'    => 'Nishiki Omakase',
        'cat'     => 'japonesa',
        'cat_label'=> 'Japonesa',
        'preco'   => 'luxo',
        'preco_label'=> 'R$$$$',
        'rating'  => 5.0,
        'reviews' => 183,
        'endereco'=> 'Al. Arapanés, 450 — Campo Belo',
        'telefone'=> '(11) 3044-9921',
        'img'     => 'https://images.unsplash.com/photo-1611270629569-8b357cb88da9?w=400&q=75',
        'aberto'  => true,
        'badge'   => null,
        'slug'    => 'nishiki-omakase',
        'lat'     => -23.6155,
        'lng'     => -46.6698,
    ],
    [
        'id'      => 3,
        'nome'    => 'Bossa Café & Bistrô',
        'cat'     => 'cafes',
        'cat_label'=> 'Café',
        'preco'   => 'barato',
        'preco_label'=> 'R$$',
        'rating'  => 4.4,
        'reviews' => 94,
        'endereco'=> 'R. Cap. A. Rosa, 09 — Campo Belo',
        'telefone'=> '(11) 3041-3300',
        'img'     => 'https://images.unsplash.com/photo-1506354666786-959d6d497f1a?w=400&q=75',
        'aberto'  => false,
        'badge'   => 'Novo',
        'slug'    => 'bossa-cafe',
        'lat'     => -23.6200,
        'lng'     => -46.6640,
    ],
    [
        'id'      => 4,
        'nome'    => 'Vino e Cucina',
        'cat'     => 'wine-bar',
        'cat_label'=> 'Wine Bar',
        'preco'   => 'alto',
        'preco_label'=> 'R$$$',
        'rating'  => 4.7,
        'reviews' => 112,
        'endereco'=> 'R. Domingos Lins, 88 — Campo Belo',
        'telefone'=> '(11) 3046-1188',
        'img'     => 'https://images.unsplash.com/photo-1600891964599-f61ba0e24092?w=400&q=75',
        'aberto'  => true,
        'badge'   => null,
        'slug'    => 'vino-e-cucina',
        'lat'     => -23.6172,
        'lng'     => -46.6720,
    ],
    [
        'id'      => 5,
        'nome'    => 'Le Marché Bistró',
        'cat'     => 'brunch',
        'cat_label'=> 'Bistrô',
        'preco'   => 'alto',
        'preco_label'=> 'R$$$',
        'rating'  => 4.6,
        'reviews' => 98,
        'endereco'=> 'Al. dos Arapanés, 100 — Campo Belo',
        'telefone'=> '(11) 3042-5566',
        'img'     => 'https://images.unsplash.com/photo-1476224203421-9ac39bcb3327?w=400&q=75',
        'aberto'  => true,
        'badge'   => null,
        'slug'    => 'le-marche-bistro',
        'lat'     => -23.6140,
        'lng'     => -46.6660,
    ],
    [
        'id'      => 6,
        'nome'    => 'Trattoria del Corso',
        'cat'     => 'restaurantes',
        'cat_label'=> 'Italiana',
        'preco'   => 'medio',
        'preco_label'=> 'R$$',
        'rating'  => 4.3,
        'reviews' => 76,
        'endereco'=> 'R. Arizona, 322 — Brooklin',
        'telefone'=> '(11) 3040-2211',
        'img'     => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=400&q=75',
        'aberto'  => false,
        'badge'   => null,
        'slug'    => 'trattoria-del-corso',
        'lat'     => -23.6220,
        'lng'     => -46.6590,
    ],
    [
        'id'      => 7,
        'nome'    => 'Studio Koi',
        'cat'     => 'beleza',
        'cat_label'=> 'Beleza',
        'preco'   => 'medio',
        'preco_label'=> 'R$$',
        'rating'  => 4.9,
        'reviews' => 201,
        'endereco'=> 'R. Jaceru, 47 — Campo Belo',
        'telefone'=> '(11) 3048-7700',
        'img'     => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=400&q=75',
        'aberto'  => true,
        'badge'   => 'Destaque',
        'slug'    => 'studio-koi',
        'lat'     => -23.6165,
        'lng'     => -46.6705,
    ],
    [
        'id'      => 8,
        'nome'    => 'Petz Campo Belo',
        'cat'     => 'pet',
        'cat_label'=> 'Pet',
        'preco'   => 'barato',
        'preco_label'=> 'R$',
        'rating'  => 4.5,
        'reviews' => 143,
        'endereco'=> 'Av. Vereador José Diniz, 3515',
        'telefone'=> '(11) 3044-0099',
        'img'     => 'https://images.unsplash.com/photo-1534361960057-19f4434a5d58?w=400&q=75',
        'aberto'  => true,
        'badge'   => null,
        'slug'    => 'petz-campo-belo',
        'lat'     => -23.6130,
        'lng'     => -46.6680,
    ],
];

$page_title = 'Mapa Interativo — Guia Campo Belo & Região';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="description" content="Explore o mapa interativo do Guia Campo Belo. Encontre restaurantes, cafés e serviços perto de você."/>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,600&display=swap" rel="stylesheet"/>

  <!-- Leaflet -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            display:['"Playfair Display"','Georgia','serif'],
            body:['Montserrat','sans-serif'],
          },
          colors: {
            green:    { DEFAULT:'#3d4733', dark:'#2a3022', light:'#4f5c40' },
            gold:     { DEFAULT:'#c9aa6b', light:'#ddc48a', pale:'#f5edda' },
            cream:    '#faf8f3',
            offwhite: '#f2f0eb',
            graphite: '#1d1d1b',
            warmgray: '#8b8589',
          },
        }
      }
    }
  </script>

  <style>
    /* ── Base ── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; overflow: hidden; }
    body { font-family: 'Montserrat', sans-serif; background: #faf8f3; }
    .font-display { font-family: 'Playfair Display', Georgia, serif; }

    /* ── Layout ── */
    #app {
      display: flex;
      flex-direction: column;
      height: 100vh;
      overflow: hidden;
    }
    #main-area {
      flex: 1;
      display: flex;
      overflow: hidden;
      position: relative;
    }

    /* ── LEFT PANEL ── */
    #panel {
      width: 400px;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      background: #faf8f3;
      border-right: 1px solid rgba(61,71,51,.09);
      z-index: 10;
      transition: transform .35s cubic-bezier(.4,0,.2,1);
    }
    #panel.collapsed {
      transform: translateX(-400px);
      position: absolute;
      height: 100%;
    }

    /* panel header */
    #panel-header {
      padding: 16px 20px;
      border-bottom: 1px solid rgba(61,71,51,.08);
      background: #faf8f3;
      flex-shrink: 0;
    }

    /* search */
    #search-wrap {
      display: flex;
      align-items: center;
      gap: 10px;
      background: #fff;
      border: 1.5px solid rgba(61,71,51,.1);
      border-radius: 999px;
      padding: 0 14px;
      height: 44px;
      transition: border-color .2s;
    }
    #search-wrap:focus-within { border-color: rgba(201,170,107,.6); }
    #search-wrap input {
      flex: 1; border: none; outline: none;
      font-family: 'Montserrat', sans-serif;
      font-size: 13px; color: #1d1d1b;
      background: transparent;
    }
    #search-wrap input::placeholder { color: #8b8589; }
    #search-wrap svg { color: #c9aa6b; flex-shrink: 0; }

    /* category pills */
    #cat-scroll {
      display: flex;
      gap: 6px;
      overflow-x: auto;
      padding: 12px 20px 0;
      scrollbar-width: none;
    }
    #cat-scroll::-webkit-scrollbar { display: none; }
    .cat-chip {
      display: flex;
      align-items: center;
      gap: 5px;
      padding: 6px 12px;
      border-radius: 999px;
      border: 1.5px solid rgba(61,71,51,.12);
      font-size: 11px;
      font-weight: 600;
      color: #8b8589;
      white-space: nowrap;
      cursor: pointer;
      background: #fff;
      transition: all .2s;
      flex-shrink: 0;
    }
    .cat-chip:hover { border-color: #c9aa6b; color: #3d4733; }
    .cat-chip.active { background: #2a3022; color: #faf8f3; border-color: #2a3022; }
    .cat-chip.active svg { color: #c9aa6b; }

    /* price filter */
    #price-row {
      display: flex;
      gap: 5px;
      padding: 10px 20px 14px;
      overflow-x: auto;
      scrollbar-width: none;
    }
    #price-row::-webkit-scrollbar { display: none; }
    .price-chip {
      padding: 5px 12px;
      border-radius: 999px;
      border: 1.5px solid rgba(61,71,51,.1);
      font-size: 11px;
      font-weight: 700;
      color: #8b8589;
      cursor: pointer;
      background: #fff;
      white-space: nowrap;
      transition: all .2s;
      flex-shrink: 0;
    }
    .price-chip:hover  { border-color: #c9aa6b; color: #3d4733; }
    .price-chip.active { background: #c9aa6b; color: #2a3022; border-color: #c9aa6b; }

    /* results header */
    #results-header {
      padding: 12px 20px 8px;
      border-bottom: 1px solid rgba(61,71,51,.06);
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    /* cards list */
    #cards-list {
      flex: 1;
      overflow-y: auto;
      padding: 12px;
      scrollbar-width: thin;
      scrollbar-color: rgba(61,71,51,.15) transparent;
    }
    #cards-list::-webkit-scrollbar { width: 4px; }
    #cards-list::-webkit-scrollbar-track { background: transparent; }
    #cards-list::-webkit-scrollbar-thumb { background: rgba(61,71,51,.15); border-radius: 4px; }

    /* compact card */
    .map-card {
      display: flex;
      gap: 12px;
      padding: 12px;
      border-radius: 16px;
      border: 1.5px solid transparent;
      cursor: pointer;
      transition: all .22s;
      background: #fff;
      margin-bottom: 8px;
    }
    .map-card:hover  { border-color: rgba(201,170,107,.4); box-shadow: 0 4px 16px rgba(29,29,27,.08); }
    .map-card.active { border-color: #c9aa6b; box-shadow: 0 4px 20px rgba(201,170,107,.22); }
    .map-card-img {
      width: 80px; height: 80px;
      border-radius: 10px;
      overflow: hidden;
      flex-shrink: 0;
    }
    .map-card-img img { width: 100%; height: 100%; object-fit: cover; }
    .map-card-body { flex: 1; min-width: 0; }
    .map-card-cat {
      font-size: 9px; font-weight: 800;
      letter-spacing: .16em; text-transform: uppercase;
      color: #c9aa6b; margin-bottom: 3px;
    }
    .map-card-name {
      font-family: 'Playfair Display', serif;
      font-size: 15px; font-weight: 700;
      color: #2a3022; line-height: 1.2;
      margin-bottom: 4px;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .map-card-meta {
      display: flex; align-items: center; gap: 6px;
      font-size: 11.5px; color: #8b8589;
    }
    .map-card-stars { color: #c9aa6b; font-size: 11px; }
    .map-card-status {
      display: flex; align-items: center; gap: 4px;
      font-size: 10.5px; font-weight: 600;
      margin-top: 5px;
    }
    .map-card-dot {
      width: 6px; height: 6px; border-radius: 50%;
    }

    /* no results */
    #no-results {
      display: none;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 48px 20px;
      text-align: center;
    }

    /* ── MAP ── */
    #map {
      flex: 1;
      height: 100%;
      z-index: 1;
    }

    /* Leaflet tile — dark/muted style via CSS filter */
    .leaflet-tile-pane {
      filter: saturate(0.7) brightness(0.97);
    }

    /* ── Custom Marker ── */
    .marker-pin {
      width: 38px; height: 44px;
      position: relative;
      cursor: pointer;
    }
    .marker-pin-inner {
      width: 38px; height: 38px;
      border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
      background: #2a3022;
      border: 2.5px solid #c9aa6b;
      display: flex; align-items: center; justify-content: center;
      color: #c9aa6b;
      transition: all .2s;
      box-shadow: 0 4px 12px rgba(42,48,34,.35);
    }
    .marker-pin-inner svg { width: 16px; height: 16px; }
    .marker-pin::after {
      content: '';
      position: absolute;
      bottom: 0; left: 50%;
      transform: translateX(-50%);
      width: 0; height: 0;
      border-left: 6px solid transparent;
      border-right: 6px solid transparent;
      border-top: 8px solid #c9aa6b;
    }
    /* active marker */
    .marker-pin.is-active .marker-pin-inner {
      background: #c9aa6b;
      color: #2a3022;
      transform: scale(1.18);
      box-shadow: 0 6px 20px rgba(201,170,107,.5);
    }
    /* cluster */
    .marker-cluster {
      width: 36px; height: 36px;
      border-radius: 50%;
      background: #2a3022;
      border: 2px solid #c9aa6b;
      display: flex; align-items: center; justify-content: center;
      font-family: 'Montserrat', sans-serif;
      font-size: 12px; font-weight: 800; color: #c9aa6b;
      box-shadow: 0 4px 12px rgba(42,48,34,.35);
    }

    /* ── Popup ── */
    .leaflet-popup-content-wrapper {
      border-radius: 18px !important;
      padding: 0 !important;
      overflow: hidden;
      box-shadow: 0 16px 48px rgba(29,29,27,.2) !important;
      border: 1.5px solid rgba(201,170,107,.3);
    }
    .leaflet-popup-content { margin: 0 !important; width: 260px !important; }
    .leaflet-popup-tip-container { display: none; }
    .leaflet-popup-close-button {
      top: 8px !important; right: 8px !important;
      width: 24px !important; height: 24px !important;
      border-radius: 50% !important;
      background: rgba(250,248,243,.9) !important;
      color: #8b8589 !important;
      font-size: 14px !important;
      display: flex; align-items: center; justify-content: center;
      z-index: 10;
    }
    .popup-img {
      width: 100%; height: 140px;
      object-fit: cover;
    }
    .popup-body { padding: 14px 16px 16px; }
    .popup-cat {
      font-size: 9px; font-weight: 800;
      letter-spacing: .18em; text-transform: uppercase;
      color: #c9aa6b; margin-bottom: 4px;
    }
    .popup-name {
      font-family: 'Playfair Display', serif;
      font-size: 17px; font-weight: 700;
      color: #2a3022; line-height: 1.2; margin-bottom: 8px;
    }
    .popup-meta {
      display: flex; align-items: center;
      justify-content: space-between;
      margin-bottom: 10px;
    }
    .popup-stars { color: #c9aa6b; font-size: 12px; }
    .popup-rating { font-weight: 700; font-size: 13px; color: #1d1d1b; margin-left: 4px; }
    .popup-reviews { font-size: 11px; color: #8b8589; margin-left: 3px; }
    .popup-price { font-size: 12px; font-weight: 700; color: #3d4733; }
    .popup-addr {
      display: flex; align-items: center; gap: 5px;
      font-size: 11.5px; color: #8b8589; margin-bottom: 12px;
    }
    .popup-status {
      display: flex; align-items: center; gap: 5px;
      font-size: 11px; font-weight: 600;
      margin-bottom: 12px;
    }
    .popup-btn {
      display: block; width: 100%;
      padding: 10px;
      background: #2a3022;
      color: #faf8f3;
      text-align: center;
      border-radius: 12px;
      font-size: 11.5px; font-weight: 700;
      letter-spacing: .08em; text-transform: uppercase;
      text-decoration: none;
      transition: background .2s;
    }
    .popup-btn:hover { background: #3d4733; }

    /* ── Toggle panel button ── */
    #toggle-panel {
      position: absolute;
      left: 0; top: 50%;
      transform: translateY(-50%);
      z-index: 20;
      width: 28px; height: 56px;
      background: #fff;
      border: 1px solid rgba(61,71,51,.12);
      border-left: none;
      border-radius: 0 12px 12px 0;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer;
      color: #8b8589;
      box-shadow: 2px 0 8px rgba(29,29,27,.06);
      transition: all .2s;
    }
    #toggle-panel:hover { color: #3d4733; background: #f5edda; }
    #toggle-panel.shifted { left: 400px; }

    /* ── Locate me button ── */
    #locate-btn {
      position: absolute;
      bottom: 32px; right: 16px;
      z-index: 20;
      width: 44px; height: 44px;
      background: #fff;
      border: 1.5px solid rgba(61,71,51,.12);
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer;
      color: #3d4733;
      box-shadow: 0 4px 12px rgba(29,29,27,.1);
      transition: all .2s;
    }
    #locate-btn:hover { border-color: #c9aa6b; color: #c9aa6b; }

    /* ── Mobile tabs ── */
    #mobile-tabs {
      display: none;
      position: fixed;
      bottom: 0; left: 0; right: 0;
      z-index: 100;
      background: #fff;
      border-top: 1px solid rgba(61,71,51,.09);
      padding: 8px 20px 20px;
    }
    #mobile-tabs .tab-bar {
      display: flex;
      background: #f2f0eb;
      border-radius: 12px;
      padding: 4px;
      gap: 4px;
    }
    #mobile-tabs button {
      flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;
      padding: 9px;
      border-radius: 9px;
      font-size: 12px; font-weight: 700;
      border: none; cursor: pointer;
      transition: all .2s;
      color: #8b8589; background: transparent;
    }
    #mobile-tabs button.active {
      background: #2a3022; color: #faf8f3;
    }
    #mobile-tabs button.active svg { color: #c9aa6b; }

    /* ── Responsive ── */
    @media (max-width: 1024px) {
      #panel { width: 340px; }
      #toggle-panel.shifted { left: 340px; }
    }
    @media (max-width: 768px) {
      html, body { overflow: auto; }
      #app { height: auto; overflow: visible; }
      #main-area { height: calc(100vh - 64px - 68px); flex-direction: column; }
      #panel {
        width: 100%; height: 100%;
        border-right: none; border-bottom: 1px solid rgba(61,71,51,.09);
        position: absolute; top: 0; left: 0;
        transform: translateX(0);
        display: none;
      }
      #panel.mobile-visible { display: flex; }
      #map { display: none; }
      #map.mobile-visible { display: block; height: 100%; }
      #toggle-panel { display: none; }
      #mobile-tabs { display: block; }
      #locate-btn { bottom: 88px; }
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/../includes/search-modal.php'; ?>

<div id="app">

  <!-- ── HEADER ── -->
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <!-- ── MAIN AREA ── -->
  <div id="main-area">

    <!-- ── LEFT PANEL ── -->
    <div id="panel">

      <!-- Panel header: search + filters -->
      <div id="panel-header">

        <!-- Search -->
        <div id="search-wrap">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input type="text" id="search-input"
                 placeholder="Buscar estabelecimento…"
                 oninput="applyFilters()" />
          <button id="clear-search" onclick="clearSearch()"
                  class="text-warmgray hover:text-graphite transition-colors hidden">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <!-- Category chips -->
        <div id="cat-scroll">
          <?php foreach ($categorias as $slug => $cat): ?>
          <div class="cat-chip <?= $slug === $slug_cat ? 'active' : '' ?>"
               data-cat="<?= $slug ?>"
               onclick="setCategory(this, '<?= $slug ?>')">
            <?= icon($cat['icon'], 12) ?>
            <?= htmlspecialchars($cat['label']) ?>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Price chips -->
        <div id="price-row">
          <?php foreach ($faixas as $val => $lbl): ?>
          <div class="price-chip <?= $val === $slug_pre ? 'active' : '' ?>"
               data-price="<?= $val ?>"
               onclick="setPrice(this, '<?= $val ?>')">
            <?= htmlspecialchars($lbl) ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Results count -->
      <div id="results-header">
        <p class="text-[12px] text-warmgray">
          <span id="count" class="font-black text-graphite text-[13px]">
            <?= count($lugares) ?>
          </span>
          lugares encontrados
        </p>
        <button onclick="clearFilters()"
                id="clear-btn"
                class="hidden text-[11px] font-bold tracking-widest uppercase
                       text-gold hover:text-gold-light transition-colors">
          Limpar
        </button>
      </div>

      <!-- Cards list -->
      <div id="cards-list">
        <?php foreach ($lugares as $lugar): ?>
        <div class="map-card"
             id="card-<?= $lugar['id'] ?>"
             data-id="<?= $lugar['id'] ?>"
             data-nome="<?= htmlspecialchars(strtolower($lugar['nome'])) ?>"
             data-cat="<?= htmlspecialchars($lugar['cat']) ?>"
             data-preco="<?= htmlspecialchars($lugar['preco']) ?>"
             onclick="selectPlace(<?= $lugar['id'] ?>)">

          <div class="map-card-img">
            <img src="<?= htmlspecialchars($lugar['img']) ?>"
                 alt="<?= htmlspecialchars($lugar['nome']) ?>"
                 loading="lazy"/>
          </div>

          <div class="map-card-body">
            <div class="map-card-cat"><?= htmlspecialchars($lugar['cat_label']) ?></div>
            <div class="map-card-name"><?= htmlspecialchars($lugar['nome']) ?></div>
            <div class="map-card-meta">
              <span class="map-card-stars">
                <?= str_repeat('★', (int)round($lugar['rating'])) ?>
              </span>
              <span class="font-bold text-graphite"><?= number_format($lugar['rating'],1) ?></span>
              <span>(<?= $lugar['reviews'] ?>)</span>
              <span class="text-warmgray/40">·</span>
              <span class="font-bold text-green text-[12px]">
                <?= htmlspecialchars($lugar['preco_label']) ?>
              </span>
            </div>
            <div class="map-card-status">
              <span class="map-card-dot <?= $lugar['aberto'] ? 'bg-emerald-400' : 'bg-red-400' ?>"></span>
              <span class="<?= $lugar['aberto'] ? 'text-emerald-500' : 'text-red-400' ?>">
                <?= $lugar['aberto'] ? 'Aberto agora' : 'Fechado' ?>
              </span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>

        <!-- No results -->
        <div id="no-results">
          <div style="width:52px;height:52px;background:#f2f0eb;border-radius:14px;
                      display:flex;align-items:center;justify-content:center;
                      color:#8b8589;margin-bottom:14px;">
            <?= icon('search', 24) ?>
          </div>
          <p class="font-display text-[18px] font-bold text-green-dark mb-2">
            Nenhum resultado
          </p>
          <p style="font-size:13px;color:#8b8589;max-width:220px;line-height:1.65;margin-bottom:16px;">
            Tente outros termos ou remova os filtros.
          </p>
          <button onclick="clearFilters()"
                  style="padding:9px 20px;background:#c9aa6b;color:#2a3022;border:none;
                         border-radius:999px;font-size:11px;font-weight:800;
                         letter-spacing:.1em;text-transform:uppercase;cursor:pointer;">
            Limpar filtros
          </button>
        </div>
      </div>
    </div><!-- /panel -->

    <!-- ── Toggle panel button ── -->
    <button id="toggle-panel" class="shifted" onclick="togglePanel()" aria-label="Mostrar/ocultar painel">
      <svg id="toggle-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="15 18 9 12 15 6"/>
      </svg>
    </button>

    <!-- ── MAP ── -->
    <div id="map"></div>

    <!-- Locate me -->
    <button id="locate-btn" onclick="locateMe()" title="Usar minha localização">
      <?= icon('navigation', 18) ?>
    </button>

  </div><!-- /main-area -->

  <!-- ── MOBILE TABS ── -->
  <div id="mobile-tabs">
    <div class="tab-bar">
      <button id="tab-lista" class="active" onclick="mobileTab('lista')">
        <?= icon('list', 16) ?> Lista
      </button>
      <button id="tab-mapa" onclick="mobileTab('mapa')">
        <?= icon('map', 16) ?> Mapa
      </button>
    </div>
  </div>

</div><!-- /app -->


<!-- ── Data para JS ── -->
<script>
const LUGARES = <?= json_encode($lugares, JSON_UNESCAPED_UNICODE) ?>;
</script>

<script>
/* ══════════════════════════════════════════
   MAP SETUP
══════════════════════════════════════════ */
const map = L.map('map', {
  center:  [-23.617, -46.668],
  zoom:    15,
  zoomControl: false,
});

/* Tile layer — CartoDB Positron (clean, minimal) */
L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
  attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>',
  subdomains: 'abcd',
  maxZoom: 19,
}).addTo(map);

/* Zoom control repositioned */
L.control.zoom({ position: 'bottomright' }).addTo(map);

/* ── Custom icon factory ── */
function makeIcon(catIcon, isActive = false) {
  const svg = `
    <div class="marker-pin ${isActive ? 'is-active' : ''}">
      <div class="marker-pin-inner">
        ${getCatSVG(catIcon)}
      </div>
    </div>`;
  return L.divIcon({
    html: svg,
    className: '',
    iconSize:    [38, 44],
    iconAnchor:  [19, 44],
    popupAnchor: [0, -46],
  });
}

function getCatSVG(cat) {
  const icons = {
    restaurantes: '<path d="M3 2v20M19 2v4a4 4 0 0 1-4 4h-1v12M15 2v4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>',
    cafes:        '<path d="M18 8h1a4 4 0 0 1 0 8h-1M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/><line x1="6" y1="1" x2="6" y2="4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/><line x1="10" y1="1" x2="10" y2="4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>',
    japonesa:     '<path d="M3 2v20M19 2v4a4 4 0 0 1-4 4h-1v12M15 2v4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>',
    'wine-bar':   '<path d="M8 22h8M7 10h10M12 15v7M12 15a5 5 0 0 0 5-5c0-2-.5-4-2-8H7c-1.5 4-2 6-2 8a5 5 0 0 0 5 5z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>',
    brunch:       '<path d="M18 8h1a4 4 0 0 1 0 8h-1M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>',
    pet:          '<circle cx="4.5" cy="9.5" r="2" stroke="currentColor" stroke-width="1.75"/><circle cx="9" cy="5.5" r="2" stroke="currentColor" stroke-width="1.75"/><circle cx="15" cy="5.5" r="2" stroke="currentColor" stroke-width="1.75"/><circle cx="19.5" cy="9.5" r="2" stroke="currentColor" stroke-width="1.75"/><path d="M12 10c-3.87 0-7 3.13-7 7 0 2.76 2.24 5 5 5h4c2.76 0 5-2.24 5-5 0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="1.75"/>',
    beleza:       '<circle cx="6" cy="6" r="3" stroke="currentColor" stroke-width="1.75"/><circle cx="6" cy="18" r="3" stroke="currentColor" stroke-width="1.75"/><line x1="20" y1="4" x2="8.12" y2="15.88" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/><line x1="14.47" y1="14.48" x2="20" y2="20" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>',
    default:      '<circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="1.75"/><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z" stroke="currentColor" stroke-width="1.75"/>',
  };
  const path = icons[cat] ?? icons.default;
  return `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">${path}</svg>`;
}

/* ── Popup HTML ── */
function makePopupHTML(p) {
  const stars = '★'.repeat(Math.round(p.rating)) + '☆'.repeat(5 - Math.round(p.rating));
  return `
    <img class="popup-img" src="${p.img}" alt="${p.nome}" loading="lazy"/>
    <div class="popup-body">
      <div class="popup-cat">${p.cat_label}</div>
      <div class="popup-name">${p.nome}</div>
      <div class="popup-meta">
        <div>
          <span class="popup-stars">${stars}</span>
          <span class="popup-rating">${p.rating.toFixed(1)}</span>
          <span class="popup-reviews">(${p.reviews})</span>
        </div>
        <span class="popup-price">${p.preco_label}</span>
      </div>
      <div class="popup-addr">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#c9aa6b" stroke-width="1.75">
          <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/>
          <circle cx="12" cy="10" r="3"/>
        </svg>
        ${p.endereco}
      </div>
      <div class="popup-status">
        <span style="width:7px;height:7px;border-radius:50%;background:${p.aberto ? '#34d399' : '#f87171'}"></span>
        <span style="color:${p.aberto ? '#10b981' : '#ef4444'}">${p.aberto ? 'Aberto agora' : 'Fechado agora'}</span>
      </div>
      <a href="/pages/${p.slug}" class="popup-btn">Ver estabelecimento →</a>
    </div>`;
}

/* ── Add markers ── */
const markers = {};
let activeId  = null;

LUGARES.forEach(p => {
  const marker = L.marker([p.lat, p.lng], {
    icon: makeIcon(p.cat),
  }).addTo(map);

  marker.bindPopup(makePopupHTML(p), {
    maxWidth:  260,
    minWidth:  260,
    className: 'gcb-popup',
  });

  marker.on('click', () => selectPlace(p.id));

  markers[p.id] = { marker, data: p };
});

/* ── Select place ── */
function selectPlace(id) {
  /* deactivate previous */
  if (activeId !== null) {
    const prev = markers[activeId];
    if (prev) {
      prev.marker.setIcon(makeIcon(prev.data.cat, false));
    }
    document.getElementById('card-' + activeId)?.classList.remove('active');
  }

  activeId = id;
  const cur = markers[id];
  if (!cur) return;

  /* activate marker */
  cur.marker.setIcon(makeIcon(cur.data.cat, true));
  cur.marker.openPopup();

  /* pan map */
  map.setView([cur.data.lat, cur.data.lng], 16, { animate: true, duration: .5 });

  /* activate card + scroll into view */
  const card = document.getElementById('card-' + id);
  if (card) {
    card.classList.add('active');
    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  /* on mobile: switch to map tab */
  if (window.innerWidth <= 768) mobileTab('mapa');
}

/* close popup → deactivate */
map.on('popupclose', () => {
  if (activeId !== null) {
    const prev = markers[activeId];
    if (prev) prev.marker.setIcon(makeIcon(prev.data.cat, false));
    document.getElementById('card-' + activeId)?.classList.remove('active');
    activeId = null;
  }
});


/* ══════════════════════════════════════════
   FILTERS
══════════════════════════════════════════ */
let activeCat   = '<?= $slug_cat ?>';
let activePrice = '<?= $slug_pre ?>';

function setCategory(el, val) {
  activeCat = val;
  document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
  el.classList.add('active');
  applyFilters();
}

function setPrice(el, val) {
  activePrice = val;
  document.querySelectorAll('.price-chip').forEach(c => c.classList.remove('active'));
  el.classList.add('active');
  applyFilters();
}

function applyFilters() {
  const q     = document.getElementById('search-input').value.trim().toLowerCase();
  let visible = 0;

  /* show/hide clear button */
  document.getElementById('clear-search').classList.toggle('hidden', !q);
  const hasFilter = activeCat !== 'todos' || activePrice !== 'todos' || q;
  document.getElementById('clear-btn').classList.toggle('hidden', !hasFilter);

  LUGARES.forEach(p => {
    const nameOk  = !q           || p.nome.toLowerCase().includes(q);
    const catOk   = activeCat   === 'todos' || p.cat   === activeCat;
    const priceOk = activePrice === 'todos' || p.preco === activePrice;
    const show    = nameOk && catOk && priceOk;

    /* card */
    const card = document.getElementById('card-' + p.id);
    if (card) card.style.display = show ? '' : 'none';

    /* marker */
    if (show) {
      markers[p.id]?.marker.addTo(map);
      visible++;
    } else {
      markers[p.id]?.marker.remove();
    }
  });

  document.getElementById('count').textContent = visible;
  document.getElementById('no-results').style.display = visible === 0 ? 'flex' : 'none';
}

function clearFilters() {
  activeCat   = 'todos';
  activePrice = 'todos';
  document.getElementById('search-input').value = '';
  document.querySelectorAll('.cat-chip').forEach(c =>
    c.classList.toggle('active', c.dataset.cat === 'todos'));
  document.querySelectorAll('.price-chip').forEach(c =>
    c.classList.toggle('active', c.dataset.price === 'todos'));
  applyFilters();
}

function clearSearch() {
  document.getElementById('search-input').value = '';
  applyFilters();
}


/* ══════════════════════════════════════════
   PANEL TOGGLE
══════════════════════════════════════════ */
let panelOpen = true;

function togglePanel() {
  panelOpen = !panelOpen;
  const panel = document.getElementById('panel');
  const btn   = document.getElementById('toggle-panel');
  const icon  = document.getElementById('toggle-icon');

  panel.classList.toggle('collapsed', !panelOpen);
  btn.classList.toggle('shifted', panelOpen);

  icon.innerHTML = panelOpen
    ? '<polyline points="15 18 9 12 15 6"/>'  /* left arrow */
    : '<polyline points="9 18 15 12 9 6"/>';  /* right arrow */

  setTimeout(() => map.invalidateSize(), 360);
}


/* ══════════════════════════════════════════
   LOCATE ME
══════════════════════════════════════════ */
let userMarker = null;

function locateMe() {
  if (!navigator.geolocation) {
    alert('Geolocalização não suportada pelo seu navegador.');
    return;
  }
  navigator.geolocation.getCurrentPosition(pos => {
    const { latitude: lat, longitude: lng } = pos.coords;

    if (userMarker) userMarker.remove();

    userMarker = L.circleMarker([lat, lng], {
      radius:      10,
      color:       '#c9aa6b',
      fillColor:   '#c9aa6b',
      fillOpacity: 0.35,
      weight:      2.5,
    }).addTo(map).bindPopup('<strong style="font-size:13px">Você está aqui</strong>');

    map.setView([lat, lng], 16, { animate: true });
  }, () => {
    alert('Não foi possível obter sua localização.');
  });
}


/* ══════════════════════════════════════════
   MOBILE TABS
══════════════════════════════════════════ */
function mobileTab(tab) {
  const panel = document.getElementById('panel');
  const mapEl = document.getElementById('map');
  const btnL  = document.getElementById('tab-lista');
  const btnM  = document.getElementById('tab-mapa');

  if (tab === 'lista') {
    panel.classList.add('mobile-visible');
    mapEl.classList.remove('mobile-visible');
    btnL.classList.add('active');
    btnM.classList.remove('active');
  } else {
    panel.classList.remove('mobile-visible');
    mapEl.classList.add('mobile-visible');
    btnM.classList.add('active');
    btnL.classList.remove('active');
    setTimeout(() => map.invalidateSize(), 50);
  }
}

/* Init mobile state */
if (window.innerWidth <= 768) {
  document.getElementById('panel').classList.add('mobile-visible');
}
</script>

</body>
</html>