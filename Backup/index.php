<?php
/**
 * index.php — Guia Campo Belo & Região
 */
require_once __DIR__ . '/includes/icons.php';

$page_title = 'Guia Campo Belo & Região — Curadoria Premium de SP';
$meta_desc  = 'A autoridade absoluta sobre o viver bem no Campo Belo e região. Restaurantes, serviços e experiências curados para quem valoriza tempo e qualidade.';

/* ── helpers ── */
function stars(int $n): string {
    return str_repeat('★', max(0, min(5, $n))) . str_repeat('☆', 5 - max(0, min(5, $n)));
}

/* ── data (swap for DB queries) ── */
$categories = [
    ['icon' => 'trending-up',  'label' => 'Destaques',    'slug' => 'all'],
    ['icon' => 'utensils',     'label' => 'Restaurantes', 'slug' => 'restaurantes'],
    ['icon' => 'coffee',       'label' => 'Cafés',        'slug' => 'cafes'],
    ['icon' => 'utensils',     'label' => 'Japonesa',     'slug' => 'japonesa'],
    ['icon' => 'wine',         'label' => 'Wine & Bar',   'slug' => 'wine-bar'],
    ['icon' => 'coffee',       'label' => 'Brunch',       'slug' => 'brunch'],
    ['icon' => 'paw',          'label' => 'Pet Friendly', 'slug' => 'pet'],
    ['icon' => 'spa',          'label' => 'Bem-estar',    'slug' => 'bem-estar'],
    ['icon' => 'shopping-bag', 'label' => 'Compras',      'slug' => 'compras'],
    ['icon' => 'activity',     'label' => 'Lazer',        'slug' => 'lazer'],
    ['icon' => 'dumbbell',     'label' => 'Academia',     'slug' => 'academia'],
    ['icon' => 'scissors',     'label' => 'Beleza',       'slug' => 'beleza'],
];

$featured = [
    [
        'id'       => 1,
        'tall'     => true,
        'img'      => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=900&q=80',
        'category' => 'Curadoria Especial',
        'title'    => 'Os 10 restaurantes imperdíveis este mês em Campo Belo',
        'meta'     => 'Alta Gastronomia · 10 selecionados',
    ],
    [
        'id'       => 2,
        'tall'     => false,
        'img'      => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=700&q=80',
        'category' => 'Café & Brunch',
        'title'    => 'Os brunchs mais disputados do bairro',
        'meta'     => '18 opções · Fins de semana',
    ],
    [
        'id'       => 3,
        'tall'     => false,
        'img'      => 'https://images.unsplash.com/photo-1600891964599-f61ba0e24092?w=700&q=80',
        'category' => 'Novidade',
        'title'    => 'Wine bars que transformaram as noites aqui',
        'meta'     => '★★★★★ · Abriu essa semana',
    ],
];

$listings = [
    [
        'id'       => 10,
        'img'      => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=600&q=80',
        'badge'    => ['label' => 'Destaque', 'gold' => true],
        'category' => 'Italiana · Contemporânea',
        'name'     => 'Osteria Moderna',
        'rating'   => 5,
        'reviews'  => 247,
        'price'    => 'R$$$',
        'tags'     => ['Jantar Romântico', 'Carta de Vinhos', 'Reserva'],
        'address'  => 'R. Lagoa Santa, 230',
        'slug'     => 'restaurantes',
    ],
    [
        'id'       => 11,
        'img'      => 'https://images.unsplash.com/photo-1611270629569-8b357cb88da9?w=600&q=80',
        'badge'    => null,
        'category' => 'Japonesa · Omakase',
        'name'     => 'Nishiki Omakase',
        'rating'   => 5,
        'reviews'  => 183,
        'price'    => 'R$$$$',
        'tags'     => ['Omakase', 'Premium', '8 lugares'],
        'address'  => 'Al. Arapanés, 450',
        'slug'     => 'japonesa',
    ],
    [
        'id'       => 12,
        'img'      => 'https://images.unsplash.com/photo-1506354666786-959d6d497f1a?w=600&q=80',
        'badge'    => ['label' => 'Novo', 'gold' => false],
        'category' => 'Café · Brunch',
        'name'     => 'Bossa Café & Bistrô',
        'rating'   => 4,
        'reviews'  => 94,
        'price'    => 'R$$',
        'tags'     => ['Pet Friendly', 'Terraço', 'Brunch'],
        'address'  => 'R. Cap. A. Rosa, 09',
        'slug'     => 'cafes',
    ],
];

$map_features = [
    ['icon' => 'pin',        'title' => 'Localização em tempo real',         'desc' => 'O que está mais perto de você agora'],
    ['icon' => 'sliders',    'title' => 'Filtros por categoria e avaliação',  'desc' => 'Refine sua busca sem esforço'],
    ['icon' => 'verified',   'title' => 'Só o que passou pelo nosso crivo',   'desc' => 'Zero lugar medíocre no mapa'],
];

$quick_tags = ['Brunch', 'Sushi', 'Pet shop', 'Academia', 'Wine bar', 'Salão'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= htmlspecialchars($meta_desc) ?>" />
  <title><?= htmlspecialchars($page_title) ?></title>
  <link rel="canonical" href="https://guiacampobeloeregiao.com.br/" />
  <meta property="og:title"       content="<?= htmlspecialchars($page_title) ?>" />
  <meta property="og:description" content="<?= htmlspecialchars($meta_desc) ?>" />
  <meta property="og:type"        content="website" />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,600&display=swap" rel="stylesheet" />

  <!-- Tailwind CDN (prod: swap for compiled build) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            display: ['"Playfair Display"', 'Georgia', 'serif'],
            body:    ['Montserrat', 'sans-serif'],
          },
          colors: {
            green: {
              DEFAULT: '#3d4733',
              dark:    '#2a3022',
              light:   '#4f5c40',
            },
            gold: {
              DEFAULT: '#c9aa6b',
              light:   '#ddc48a',
              pale:    '#f5edda',
            },
            cream:    '#faf8f3',
            offwhite: '#f2f0eb',
            graphite: '#1d1d1b',
            warmgray: '#8b8589',
          },
          boxShadow: {
            'gold':    '0 8px 24px rgba(201,170,107,0.32)',
            'gold-lg': '0 16px 40px rgba(201,170,107,0.4)',
            'card':    '0 2px 12px rgba(29,29,27,0.07)',
            'card-hover': '0 8px 32px rgba(29,29,27,0.11)',
            'hero':    '0 24px 80px rgba(0,0,0,0.28), 0 4px 16px rgba(0,0,0,0.12)',
            'xl-dark': '0 32px 100px rgba(0,0,0,0.3)',
          },
          keyframes: {
            fadeUp:    { from: { opacity: 0, transform: 'translateY(22px)' }, to: { opacity: 1, transform: 'translateY(0)' } },
            slowZoom:  { from: { transform: 'scale(1.03)' }, to: { transform: 'scale(1.11)' } },
            modalIn:   { from: { opacity: 0, transform: 'translateY(-16px) scale(.97)' }, to: { opacity: 1, transform: 'translateY(0) scale(1)' } },
            mapPulse:  { '0%,100%': { boxShadow: '0 0 0 14px rgba(201,170,107,.22)' }, '50%': { boxShadow: '0 0 0 24px rgba(201,170,107,.07)' } },
            revealUp:  { from: { opacity: 0, transform: 'translateY(28px)' }, to: { opacity: 1, transform: 'translateY(0)' } },
          },
          animation: {
            'fade-up':   'fadeUp .7s ease forwards',
            'slow-zoom': 'slowZoom 22s ease-in-out infinite alternate',
            'modal-in':  'modalIn .22s ease',
            'map-pulse': 'mapPulse 2.4s ease-in-out infinite',
          },
        }
      }
    }
  </script>

  <!-- App CSS -->
  <link rel="stylesheet" href="/assets/css/app.css" />

  <style>
    /* Tailwind CDN doesn't process @layer — these overrides are needed */
    body { font-family: 'Montserrat', sans-serif; background: #faf8f3; }
    .font-display { font-family: 'Playfair Display', Georgia, serif; }
    #search-dropdown { display: none; }
    #search-dropdown.open { display: block; }
    #search-modal.open { display: flex; }
    .reveal { opacity: 0; transform: translateY(28px); transition: opacity .55s ease, transform .55s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    /* Hero overlay */
    .hero-overlay {
      background: linear-gradient(180deg,rgba(42,48,34,.80) 0%,rgba(42,48,34,.48) 45%,rgba(42,48,34,.88) 100%);
    }
    /* Header scrolled */
    .header-scrolled #header-bar {
      background: rgba(250,248,243,.97) !important;
      backdrop-filter: blur(20px) !important;
      box-shadow: 0 1px 0 rgba(61,71,51,.09), 0 2px 12px rgba(29,29,27,.07) !important;
      height: 64px !important;
    }
    .header-scrolled #logo-title     { color: #2a3022 !important; }
    .header-scrolled .nav-link       { color: #1d1d1b !important; }
    .header-scrolled .nav-link:hover { color: #3d4733 !important; }
    .header-scrolled #header-search-icon { border-color: rgba(61,71,51,.2) !important; color: #3d4733 !important; }
    .header-scrolled #header-pill { opacity: 1 !important; pointer-events: all !important; max-width: 320px !important; }
    /* cat-pill active */
    .cat-pill.active { border-bottom-color: #c9aa6b !important; }
    .cat-pill.active .pill-label { color: #3d4733 !important; font-weight: 700 !important; }
    .cat-pill.active svg { color: #3d4733 !important; }
  </style>
</head>
<body class="bg-cream text-graphite antialiased overflow-x-hidden">

<?php include __DIR__ . '/includes/search-modal.php'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>


<!-- ══════════════════════════════════════════
     HERO
══════════════════════════════════════════ -->
<section class="relative min-h-screen flex flex-col items-center justify-center pt-[72px] overflow-hidden">

  <!-- Background photo -->
  <div class="absolute inset-0 z-0 animate-slow-zoom"
       style="background:url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1600&q=80') center/cover no-repeat"
       role="img" aria-label="Rua arborizada de Campo Belo"></div>

  <!-- Overlay -->
  <div class="hero-overlay absolute inset-0 z-[1]" aria-hidden="true"></div>

  <!-- Content -->
  <div class="relative z-[2] w-full text-center px-6 flex flex-col items-center">

    <!-- Eyebrow -->
    <div class="opacity-0 animate-fade-up delay-100
                inline-flex items-center gap-2 px-4 py-1.5 mb-5
                rounded-full border border-gold/35 bg-gold/10
                text-[10px] font-bold tracking-[0.18em] uppercase text-gold-light">
      <?= icon('pin', 12) ?> São Paulo &middot; Zona Sul &middot; Campo Belo
    </div>

    <!-- H1 -->
    <h1 class="opacity-0 animate-fade-up delay-250
               font-display text-[clamp(36px,6vw,68px)] font-bold leading-[1.1]
               text-white mb-3.5 max-w-3xl">
      Encontre o melhor<br/>de <em class="italic text-gold-light">Campo Belo & Região</em>
    </h1>

    <!-- Subline -->
    <p class="opacity-0 animate-fade-up delay-400
              text-[15px] font-light text-white leading-relaxed max-w-[480px] mb-9">
      Curadoria dos melhores restaurantes, serviços e experiências.<br/>
      Tudo filtrado, tudo verificado.
    </p>

    <!-- ── SEARCH BOX ── -->
    <div class="opacity-0 animate-fade-up delay-550 w-full max-w-[760px]">

      <div id="search-box"
           class="relative flex items-stretch bg-white rounded-full h-[68px]
                  shadow-[0_24px_80px_rgba(0,0,0,.28),0_4px_16px_rgba(0,0,0,.12)]">

        <!-- Keyword -->
        <div class="flex flex-1 items-center gap-3 px-6 relative overflow-hidden
                    rounded-l-full" style="flex:2">
          <span class="text-gold flex-shrink-0"><?= icon('search', 18) ?></span>
          <label for="hero-search-input"
                 class="absolute top-[11px] left-[52px] text-[9px] font-black
                        tracking-[0.15em] uppercase text-gold pointer-events-none">
            O que você busca?
          </label>
          <div class="flex flex-col pt-[18px] flex-1">
            <input type="text" id="hero-search-input" name="q"
                   placeholder="Restaurante, café, serviço…"
                   autocomplete="off" aria-label="Buscar"
                   aria-controls="search-dropdown" aria-expanded="false"
                   class="border-none outline-none bg-transparent text-sm font-medium
                          text-graphite placeholder-warmgray mt-0.5" />
          </div>

          <!-- Autocomplete -->
          <div id="search-dropdown"
               class="absolute top-[calc(100%+12px)] left-0 right-0
                      bg-white rounded-[20px] shadow-[0_20px_60px_rgba(29,29,27,.15)]
                      border border-green/[0.07] py-2 z-[400]">

            <p class="px-5 pt-2 pb-1 text-[9px] font-black tracking-[0.18em] uppercase text-gold">
              Populares agora
            </p>
            <?php
            $drops = [
              ['fill' => 'Osteria Moderna',  'icon' => 'utensils', 'sub' => 'Italiana · Campo Belo · ★★★★★'],
              ['fill' => 'Nishiki Omakase',  'icon' => 'utensils', 'sub' => 'Japonesa · Brooklin · ★★★★★'],
              ['fill' => 'Bossa Café',       'icon' => 'coffee',   'sub' => 'Café · Campo Belo · ★★★★☆'],
            ];
            foreach ($drops as $d): ?>
            <div class="flex items-center gap-3 px-5 py-2.5 cursor-pointer hover:bg-offwhite
                        transition-colors duration-150" data-fill="<?= htmlspecialchars($d['fill']) ?>">
              <div class="w-[34px] h-[34px] rounded-[10px] bg-gold-pale flex items-center
                          justify-center text-green flex-shrink-0">
                <?= icon($d['icon'], 16) ?>
              </div>
              <div>
                <p class="text-sm font-semibold text-graphite"><?= htmlspecialchars($d['fill']) ?></p>
                <p class="text-[11px] text-warmgray mt-px"><?= htmlspecialchars($d['sub']) ?></p>
              </div>
            </div>
            <?php endforeach; ?>

            <div class="h-px bg-offwhite my-1"></div>
            <p class="px-5 pt-1 pb-1 text-[9px] font-black tracking-[0.18em] uppercase text-gold">
              Recentes
            </p>
            <?php foreach (['Brunch Campo Belo', 'Pet shop perto'] as $r): ?>
            <div class="dropdown-recent-row flex items-center justify-between px-5 py-2
                        cursor-pointer hover:bg-offwhite transition-colors duration-150">
              <div class="flex items-center gap-2.5 text-[13px] font-medium text-graphite">
                <?= icon('clock', 14) ?> <?= htmlspecialchars($r) ?>
              </div>
              <button class="recent-remove w-5 h-5 rounded-full flex items-center justify-center
                             text-warmgray hover:bg-offwhite transition-colors duration-150"
                      aria-label="Remover">
                <?= icon('close', 12) ?>
              </button>
            </div>
            <?php endforeach; ?>
          </div><!-- /dropdown -->
        </div>

        <!-- Divider -->
        <div class="w-px bg-green/[0.09] self-stretch"></div>

        <!-- Category -->
        <div class="flex items-center gap-3 px-6 relative">
          <span class="text-gold flex-shrink-0"><?= icon('grid', 16) ?></span>
          <label for="hero-search-cat"
                 class="absolute top-[11px] left-[52px] text-[9px] font-black
                        tracking-[0.15em] uppercase text-gold pointer-events-none">
            Categoria
          </label>
          <div class="flex flex-col pt-[18px]">
            <select id="hero-search-cat" name="categoria"
                    aria-label="Selecionar categoria"
                    class="border-none outline-none bg-transparent text-sm font-semibold
                           text-green cursor-pointer mt-0.5 pr-2">
              <option value="">Todas</option>
              <?php foreach (array_slice($categories, 1) as $cat): ?>
              <option value="<?= htmlspecialchars($cat['slug'] ?? '') ?>">
                <?= htmlspecialchars($cat['label'] ?? '') ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Submit -->
        <button type="button" aria-label="Buscar"
                class="flex flex-col items-center justify-center gap-1 w-[80px] flex-shrink-0
                       bg-gold hover:bg-gold-light text-green-dark rounded-full
                       transition-colors duration-200">
          <?= icon('search', 22) ?>
          <span class="text-[9px] font-black tracking-[0.1em] uppercase">Buscar</span>
        </button>

      </div><!-- /search-box -->

      <!-- Quick tags -->
      <div class="flex items-center gap-2 mt-[18px] flex-wrap justify-center opacity-0
                  animate-fade-up delay-700" aria-label="Buscas populares">
        <span class="text-[12px] text-white font-medium">Populares:</span>
        <?php foreach ($quick_tags as $tag): ?>
        <button class="quick-tag px-3.5 py-1.5 border border-white/20 rounded-full
                       text-[12px] font-semibold text-white bg-white/[0.06]
                       hover:bg-white/[0.14] hover:border-white/50 hover:text-white
                       transition-all duration-200"
                data-fill="<?= htmlspecialchars($tag) ?>"
                type="button">
          <?= htmlspecialchars($tag) ?>
        </button>
        <?php endforeach; ?>
      </div>
    </div><!-- /hero-search-wrap -->
  </div><!-- /hero-content -->

  <!-- Stats bar -->
  <div class="relative z-[2] w-full mt-14 border-t border-white/10 opacity-0
              animate-fade-up delay-850">
    <div class="flex justify-center flex-wrap">
      <?php
      $stats = [
        ['num' => '380+',  'label' => 'Estabelecimentos'],
        ['num' => '12',    'label' => 'Categorias'],
        ['num' => '4.9★',  'label' => 'Avaliação média'],
        ['num' => '0,935', 'label' => 'IDH do bairro'],
      ];
      foreach ($stats as $s): ?>
      <div class="flex flex-col items-center px-12 py-[18px] border-r border-white/10 last:border-r-0">
        <span class="font-display text-[28px] font-bold text-white leading-none">
          <?= htmlspecialchars($s['num']) ?>
        </span>
        <span class="text-[10px] font-semibold tracking-[0.1em] uppercase text-white mt-1">
          <?= htmlspecialchars($s['label']) ?>
        </span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</section><!-- /hero -->


<!-- ══════════════════════════════════════════
     CATEGORY STRIP (sticky)
══════════════════════════════════════════ -->
<div class="bg-white border-b border-green/[0.07] sticky top-[64px] z-[100]
            shadow-[0_2px_12px_rgba(29,29,27,0.055)]"
     role="navigation" aria-label="Filtrar por categoria">
  <div class="max-w-[1180px] mx-auto px-10">
    <div class="flex overflow-x-auto scrollbar-hide">
      <?php foreach ($categories as $i => $cat): ?>
      <button class="cat-pill flex flex-col items-center gap-1 px-5 py-3.5
                     border-b-[2.5px] border-b-transparent hover:bg-offwhite
                     hover:border-b-gold/50 transition-all duration-200
                     flex-shrink-0 whitespace-nowrap <?= $i === 0 ? 'active' : '' ?>"
              data-category="<?= htmlspecialchars($cat['slug'] ?? 'all') ?>"
              type="button">
        <span class="text-warmgray transition-colors duration-200">
          <?= icon($cat['icon'] ?? 'grid', 18) ?>
        </span>
        <span class="pill-label text-[10.5px] font-semibold text-warmgray
                     tracking-[0.03em] transition-colors duration-200">
          <?= htmlspecialchars($cat['label'] ?? '') ?>
        </span>
      </button>
      <?php endforeach; ?>
    </div>
  </div>
</div>


<!-- ══════════════════════════════════════════
     FEATURED EDITORIAL
══════════════════════════════════════════ -->
<section class="py-20 bg-offwhite">
  <div class="max-w-[1180px] mx-auto px-10">

    <div class="flex items-end justify-between mb-11 gap-5">
      <div>
        <span class="block text-[10px] font-black tracking-[0.22em] uppercase text-gold mb-2">
          Em destaque
        </span>
        <h2 class="font-display text-[clamp(26px,3.5vw,42px)] font-bold text-green-dark leading-[1.15]">
          Seleções da <em class="italic text-gold">semana</em>
        </h2>
        <div class="w-10 h-0.5 bg-gold mt-3.5"></div>
      </div>
      <a href="/pages/destaques.php"
         class="inline-flex items-center gap-2 px-6 py-3 border-[1.5px] border-green
                rounded-full text-[11.5px] font-bold tracking-[0.09em] uppercase text-green
                hover:bg-green hover:text-white transition-all duration-200 flex-shrink-0">
        Ver todos
      </a>
    </div>

    <!-- Grid: 1 tall + 2 stacked -->
    <div class="grid grid-cols-1 lg:grid-cols-[1.5fr_1fr] gap-[18px]"
         style="grid-template-rows: 380px 220px;">
      <?php foreach ($featured as $item): ?>
      <a href="/pages/destaque.php?id=<?= (int)$item['id'] ?>"
         class="feat-card reveal group relative rounded-[20px] overflow-hidden cursor-pointer
                <?= $item['tall'] ? 'lg:row-span-2' : '' ?>"
         style="height:<?= $item['tall'] ? 'auto' : 'auto' ?>"
         aria-label="<?= htmlspecialchars($item['title']) ?>">
        <img src="<?= htmlspecialchars($item['img']) ?>"
             alt="<?= htmlspecialchars($item['title']) ?>"
             loading="<?= $item['tall'] ? 'eager' : 'lazy' ?>"
             class="w-full h-full object-cover transition-transform duration-700
                    group-hover:scale-[1.06]" />
        <div class="absolute inset-0"
             style="background:linear-gradient(180deg,transparent 28%,rgba(29,29,27,.82) 100%)">
        </div>
        <div class="absolute bottom-0 left-0 right-0 p-6">
          <p class="text-[9px] font-black tracking-[0.2em] uppercase text-gold-light mb-1.5">
            <?= htmlspecialchars($item['category']) ?>
          </p>
          <p class="font-display <?= $item['tall'] ? 'text-[28px]' : 'text-[20px]' ?>
                    font-bold text-white leading-[1.25]">
            <?= htmlspecialchars($item['title']) ?>
          </p>
          <p class="text-[11px] text-white/52 mt-1.5 font-medium">
            <?= htmlspecialchars($item['meta']) ?>
          </p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ══════════════════════════════════════════
     LISTINGS
══════════════════════════════════════════ -->
<section class="py-20 bg-cream">
  <div class="max-w-[1180px] mx-auto px-10">

    <div class="flex flex-col items-center text-center mb-11">
      <span class="text-[10px] font-black tracking-[0.22em] uppercase text-gold mb-2">
        Gastronomia
      </span>
      <h2 class="font-display text-[clamp(26px,3.5vw,42px)] font-bold text-green-dark leading-[1.15]">
        Melhor <em class="italic text-gold">avaliados</em>
      </h2>
      <div class="w-10 h-0.5 bg-gold mt-3.5 mx-auto"></div>
      <p class="text-[15px] font-light text-warmgray leading-[1.75] mt-3 max-w-[480px]">
        Cada indicação passa por nossa curadoria rigorosa. Se está aqui, é porque merece.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($listings as $item): ?>
      <article class="listing-card reveal group bg-white rounded-[20px] overflow-hidden
                      border border-green/[0.06] shadow-card hover:-translate-y-1.5
                      hover:shadow-card-hover transition-all duration-300 cursor-pointer"
               data-category="<?= htmlspecialchars($item['slug'] ?? '') ?>">

        <!-- Image -->
        <div class="relative h-[200px] overflow-hidden">
          <img src="<?= htmlspecialchars($item['img']) ?>"
               alt="<?= htmlspecialchars($item['name']) ?>"
               loading="lazy"
               class="w-full h-full object-cover transition-transform duration-[650ms]
                      group-hover:scale-[1.07]" />
          <div class="absolute top-3 left-3 right-3 flex items-start justify-between">
            <?php if ($item['badge']): ?>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px]
                         font-black tracking-[0.14em] uppercase
                         <?= $item['badge']['gold'] ? 'bg-gold text-green-dark' : 'bg-green text-white' ?>">
              <?= htmlspecialchars($item['badge']['label']) ?>
            </span>
            <?php else: ?>
            <span></span>
            <?php endif; ?>

            <button class="fav-btn w-8 h-8 rounded-full bg-cream/93 backdrop-blur-sm
                           flex items-center justify-center text-warmgray
                           hover:scale-110 transition-all duration-200 border-none"
                    aria-label="Salvar nos favoritos">
              <?= icon('heart', 15) ?>
            </button>
          </div>
        </div>

        <!-- Body -->
        <div class="p-5">
          <p class="text-[9.5px] font-black tracking-[0.18em] uppercase text-gold mb-1.5">
            <?= htmlspecialchars($item['category']) ?>
          </p>
          <h3 class="font-display text-[18px] font-bold text-green-dark leading-[1.2] mb-2">
            <?= htmlspecialchars($item['name']) ?>
          </h3>
          <div class="flex items-center gap-2 flex-wrap mb-3">
            <span class="text-gold text-[12px] tracking-[0.04em]"
                  aria-label="<?= $item['rating'] ?> estrelas">
              <?= stars($item['rating']) ?>
            </span>
            <span class="text-[12px] text-warmgray">(<?= (int)$item['reviews'] ?>)</span>
            <span class="text-warmgray text-[10px]" aria-hidden="true">&middot;</span>
            <span class="text-[12px] font-bold text-green"><?= htmlspecialchars($item['price']) ?></span>
          </div>
          <div class="flex flex-wrap gap-1.5 mb-3.5">
            <?php foreach ($item['tags'] as $tag): ?>
            <span class="px-2.5 py-[3px] bg-offwhite rounded-full text-[10.5px]
                         font-semibold text-green">
              <?= htmlspecialchars($tag) ?>
            </span>
            <?php endforeach; ?>
          </div>
          <div class="flex items-center justify-between pt-3.5 border-t border-offwhite">
            <div class="flex items-center gap-1.5 text-[12px] text-warmgray">
              <?= icon('pin', 11) ?>
              <?= htmlspecialchars($item['address']) ?>
            </div>
            <a href="/pages/lugar.php?id=<?= (int)$item['id'] ?>"
               class="flex items-center gap-1.5 text-[11px] font-black tracking-[0.06em]
                      uppercase text-gold group-hover:gap-2.5 transition-all duration-200">
              Ver mais <?= icon('arrow-right', 11) ?>
            </a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <div class="flex justify-center mt-11">
      <a href="/pages/restaurantes.php"
         class="inline-flex items-center gap-2 px-7 py-3 border-[1.5px] border-green
                rounded-full text-[11.5px] font-bold tracking-[0.09em] uppercase text-green
                hover:bg-green hover:text-white transition-all duration-200">
        Ver todos os restaurantes
      </a>
    </div>

  </div>
</section>


<!-- ══════════════════════════════════════════
     MAP CTA
══════════════════════════════════════════ -->
<section class="bg-green-dark relative overflow-hidden py-[90px]">
  <!-- Decorative radials -->
  <div class="absolute inset-0 pointer-events-none"
       style="background:radial-gradient(circle at 15% 60%,rgba(201,170,107,.1) 0%,transparent 55%),
              radial-gradient(circle at 85% 20%,rgba(201,170,107,.06) 0%,transparent 45%)">
  </div>

  <div class="relative z-[1] max-w-[1180px] mx-auto px-10
              grid grid-cols-1 lg:grid-cols-2 items-center gap-[72px]">

    <!-- Text -->
    <div>
      <span class="block text-[10px] font-black tracking-[0.22em] uppercase text-gold mb-2">
        Explore a região
      </span>
      <h2 class="font-display text-[clamp(26px,3.5vw,42px)] font-bold text-white leading-[1.15]">
        Tudo no mapa,<br/><em class="italic text-gold">ao alcance</em><br/>das suas mãos
      </h2>
      <p class="text-[15px] font-light text-white/45 leading-[1.75] mt-3 max-w-full">
        Campo Belo, Brooklin, Moema e arredores em um só mapa interativo e filtrado.
      </p>

      <div class="flex flex-col gap-[18px] mt-8">
        <?php foreach ($map_features as $f): ?>
        <div class="flex items-start gap-3.5">
          <div class="w-10 h-10 rounded-xl bg-gold/[0.12] flex items-center justify-center
                      text-gold flex-shrink-0">
            <?= icon($f['icon'] ?? 'pin', 18) ?>
          </div>
          <div>
            <strong class="block text-[13.5px] font-bold text-white mb-0.5">
              <?= htmlspecialchars($f['title']) ?>
            </strong>
            <span class="text-[12.5px] text-white/40">
              <?= htmlspecialchars($f['desc']) ?>
            </span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <a href="/pages/mapa.php"
         class="inline-flex items-center gap-2 mt-8 px-7 py-3.5 bg-gold hover:bg-gold-light
                text-green-dark text-[12px] font-black tracking-[0.1em] uppercase rounded-full
                shadow-gold hover:-translate-y-0.5 transition-all duration-200">
        Abrir Mapa Interativo <?= icon('arrow-right', 14) ?>
      </a>
    </div>

    <!-- Map visual -->
    <div class="reveal relative rounded-[20px] overflow-hidden h-[400px] shadow-xl-dark">
      <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?w=800&q=80"
           alt="Mapa da região de Campo Belo" loading="lazy"
           class="w-full h-full object-cover" style="filter:saturate(.65) brightness(.85)" />
      <div class="absolute inset-0 flex items-center justify-center bg-green-dark/25">
        <a href="/pages/mapa.php" class="flex flex-col items-center gap-3 cursor-pointer
                                         hover:scale-105 transition-transform duration-200">
          <div class="w-[66px] h-[66px] rounded-full bg-gold flex items-center justify-center
                      text-green-dark animate-map-pulse">
            <?= icon('pin', 26) ?>
          </div>
          <span class="text-[11.5px] font-bold tracking-[0.1em] uppercase text-white">
            Campo Belo
          </span>
        </a>
      </div>
    </div>

  </div>
</section>


<!-- ══════════════════════════════════════════
     NEWSLETTER
══════════════════════════════════════════ -->
<section class="py-20 bg-cream">
  <div class="max-w-[1180px] mx-auto px-10">
    <div class="relative rounded-[28px] overflow-hidden p-16 lg:p-[64px_72px]
                grid grid-cols-1 lg:grid-cols-[1fr_auto] items-center gap-14"
         style="background:linear-gradient(130deg,#2a3022 0%,#4f5c40 100%)">

      <!-- Decorative circle -->
      <div class="absolute -top-20 -right-20 w-[300px] h-[300px] rounded-full
                  bg-gold/[0.07] pointer-events-none"></div>

      <div>
        <span class="block text-[10px] font-black tracking-[0.22em] uppercase text-gold-light mb-2">
          Fique por dentro
        </span>
        <h2 class="font-display text-[clamp(24px,3vw,36px)] font-bold text-white leading-[1.2]">
          Dica boa não fica solta,<br/>fica <em class="italic text-gold-light">Salva</em>!
        </h2>
        <p class="text-[14px] text-white/45 mt-2.5 leading-relaxed">
          Indicações, novidades e aberturas direto no seu e-mail. Curadoria semanal.
        </p>
      </div>

      <div>
        <form action="/actions/newsletter.php" method="POST"
              class="flex bg-white/10 border border-white/18 rounded-full overflow-hidden
                     min-w-[360px]">
          <input type="email" name="email" required
                 placeholder="Seu melhor e-mail"
                 aria-label="E-mail para newsletter"
                 class="flex-1 px-5 py-4 bg-transparent border-none outline-none
                        text-sm text-white placeholder-white/35" />
          <button type="submit"
                  class="px-6 py-4 bg-gold hover:bg-gold-light text-green-dark text-[11px]
                         font-black tracking-[0.1em] uppercase transition-colors duration-200">
            Assinar
          </button>
        </form>
        <p class="text-center text-[11px] text-white/28 mt-2.5">
          Sem spam. Cancele quando quiser.
        </p>
      </div>

    </div>
  </div>
</section>


<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="/assets/js/app.js" defer></script>
</body>
</html>
