<?php
/**
 * pages/categoria.php — Listagem por categoria
 * Guia Campo Belo & Região
 * URL futura: /pages/restaurantes  /pages/cafes  etc.
 */
require_once __DIR__ . '/../includes/icons.php';

/* ── Parâmetros da URL ── */
$slug_atual = $_GET['slug'] ?? 'restaurantes';
$sort       = $_GET['sort'] ?? 'destaque';
$preco      = $_GET['preco'] ?? 'todos';

/* ── Mapa de categorias ── */
$categorias = [
    'restaurantes' => ['label' => 'Restaurantes',  'icon' => 'utensils',    'count' => 84],
    'cafes'        => ['label' => 'Cafés',          'icon' => 'coffee',      'count' => 42],
    'japonesa'     => ['label' => 'Japonesa',       'icon' => 'utensils',    'count' => 27],
    'wine-bar'     => ['label' => 'Wine & Bar',     'icon' => 'wine',        'count' => 19],
    'brunch'       => ['label' => 'Brunch',         'icon' => 'coffee',      'count' => 18],
    'pet'          => ['label' => 'Pet Friendly',   'icon' => 'paw',         'count' => 29],
    'bem-estar'    => ['label' => 'Bem-estar',      'icon' => 'spa',         'count' => 38],
    'compras'      => ['label' => 'Compras',        'icon' => 'shopping-bag','count' => 67],
    'lazer'        => ['label' => 'Lazer',          'icon' => 'activity',    'count' => 51],
    'academia'     => ['label' => 'Academia',       'icon' => 'dumbbell',    'count' => 14],
    'beleza'       => ['label' => 'Beleza',         'icon' => 'scissors',    'count' => 31],
];

$cat_atual = $categorias[$slug_atual] ?? $categorias['restaurantes'];

/* ── Faixas de preço ── */
$faixas = [
    'todos'  => 'Todos os preços',
    'barato' => 'Até R$ 60',
    'medio'  => 'R$ 60 – R$ 120',
    'alto'   => 'R$ 120 – R$ 200',
    'luxo'   => 'Acima de R$ 200',
];

/* ── Mock listings ── */
$listings = [
    [
        'id'      => 10,
        'nome'    => 'Osteria Moderna',
        'cat'     => 'Italiana · Contemporânea',
        'slug'    => 'osteria-moderna',
        'img'     => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=600&q=80',
        'badge'   => 'Destaque',
        'rating'  => 4.8,
        'reviews' => 247,
        'preco'   => 'R$$$',
        'preco_range' => 'R$85 – R$220',
        'tags'    => ['Jantar Romântico', 'Carta de Vinhos', 'Reserva'],
        'endereco'=> 'R. Lagoa Santa, 230',
        'aberto'  => true,
    ],
    [
        'id'      => 11,
        'nome'    => 'Nishiki Omakase',
        'cat'     => 'Japonesa · Omakase',
        'slug'    => 'nishiki-omakase',
        'img'     => 'https://images.unsplash.com/photo-1611270629569-8b357cb88da9?w=600&q=80',
        'badge'   => null,
        'rating'  => 5.0,
        'reviews' => 183,
        'preco'   => 'R$$$$',
        'preco_range' => 'R$180 – R$380',
        'tags'    => ['Omakase', 'Premium', '8 lugares'],
        'endereco'=> 'Al. Arapanés, 450',
        'aberto'  => true,
    ],
    [
        'id'      => 12,
        'nome'    => 'Bossa Café & Bistrô',
        'cat'     => 'Café · Brunch',
        'slug'    => 'bossa-cafe',
        'img'     => 'https://images.unsplash.com/photo-1506354666786-959d6d497f1a?w=600&q=80',
        'badge'   => 'Novo',
        'rating'  => 4.4,
        'reviews' => 94,
        'preco'   => 'R$$',
        'preco_range' => 'R$35 – R$80',
        'tags'    => ['Pet Friendly', 'Terraço', 'Brunch'],
        'endereco'=> 'R. Cap. A. Rosa, 09',
        'aberto'  => false,
    ],
    [
        'id'      => 13,
        'nome'    => 'Vino e Cucina',
        'cat'     => 'Italiana · Wine Bar',
        'slug'    => 'vino-e-cucina',
        'img'     => 'https://images.unsplash.com/photo-1600891964599-f61ba0e24092?w=600&q=80',
        'badge'   => null,
        'rating'  => 4.7,
        'reviews' => 112,
        'preco'   => 'R$$$',
        'preco_range' => 'R$90 – R$180',
        'tags'    => ['Carta de Vinhos', 'Romântico', 'Música ao vivo'],
        'endereco'=> 'R. Domingos Lins, 88',
        'aberto'  => true,
    ],
    [
        'id'      => 14,
        'nome'    => 'Le Marché Bistró',
        'cat'     => 'Francesa · Bistrô',
        'slug'    => 'le-marche-bistro',
        'img'     => 'https://images.unsplash.com/photo-1476224203421-9ac39bcb3327?w=600&q=80',
        'badge'   => null,
        'rating'  => 4.6,
        'reviews' => 98,
        'preco'   => 'R$$$',
        'preco_range' => 'R$75 – R$160',
        'tags'    => ['Brunch', 'Croissant', 'Terraço'],
        'endereco'=> 'Al. dos Arapanés, 100',
        'aberto'  => true,
    ],
    [
        'id'      => 15,
        'nome'    => 'Trattoria del Corso',
        'cat'     => 'Italiana · Tradicional',
        'slug'    => 'trattoria-del-corso',
        'img'     => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600&q=80',
        'badge'   => null,
        'rating'  => 4.3,
        'reviews' => 76,
        'preco'   => 'R$$',
        'preco_range' => 'R$45 – R$110',
        'tags'    => ['Familiar', 'Massas Frescas', 'Almoço'],
        'endereco'=> 'R. Arizona, 322',
        'aberto'  => false,
    ],
];

$total = count($listings);

$page_title = $cat_atual['label'] . ' em Campo Belo — Guia Campo Belo & Região';

function stars_cat(float $n): string {
    $full  = floor($n);
    $empty = 5 - $full;
    return str_repeat('<span style="color:#c9aa6b">★</span>', $full)
         . str_repeat('<span style="color:rgba(139,133,137,.3)">★</span>', $empty);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="description" content="Os melhores <?= htmlspecialchars(strtolower($cat_atual['label'])) ?> de Campo Belo e região. Curadoria especializada com avaliações reais."/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,600&display=swap" rel="stylesheet"/>
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
    body { font-family:'Montserrat',sans-serif; background:#faf8f3; }
    .font-display { font-family:'Playfair Display',Georgia,serif; }
    .scrollbar-hide::-webkit-scrollbar { display:none; }
    .scrollbar-hide { -ms-overflow-style:none; scrollbar-width:none; }
    /* grid/list toggle */
    .view-grid  .list-only  { display:none; }
    .view-list  .grid-only  { display:none; }
    .view-list  #cards-grid { grid-template-columns:1fr !important; }
    .view-list  .card-list-img { width:220px !important; height:100% !important; flex-shrink:0; }
    /* active states */
    .cat-link.active  { background:#f5edda; color:#2a3022; border-color:#c9aa6b; font-weight:700; }
    .price-btn.active { background:#2a3022; color:#faf8f3; border-color:#2a3022; }
    .sort-opt.active  { color:#c9aa6b; font-weight:700; }
    /* reveal */
    .reveal { opacity:0; transform:translateY(16px); transition:opacity .45s ease,transform .45s ease; }
    .reveal.in { opacity:1; transform:translateY(0); }
    /* mobile sidebar */
    #sidebar-mobile { display:none; }
    #sidebar-mobile.open { display:flex; }
  </style>
</head>
<body class="bg-cream antialiased overflow-x-hidden">

<?php include __DIR__ . '/../includes/search-modal.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>


<!-- ══════════════════════════════════════════
     HERO BANNER
══════════════════════════════════════════ -->
<section class="relative bg-green-dark pt-[72px] overflow-hidden">
  <!-- BG pattern -->
  <div class="absolute inset-0 pointer-events-none opacity-[0.04]"
       style="background-image:linear-gradient(rgba(201,170,107,1) 1px,transparent 1px),linear-gradient(90deg,rgba(201,170,107,1) 1px,transparent 1px);background-size:60px 60px">
  </div>
  <div class="absolute inset-0 pointer-events-none"
       style="background:radial-gradient(ellipse 60% 80% at 80% 50%,rgba(79,92,64,.5) 0%,transparent 70%)">
  </div>

  <div class="relative z-10 max-w-[1180px] mx-auto px-10 py-14">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-[11px] font-medium text-white/40 mb-6">
      <a href="/index.php" class="hover:text-gold transition-colors">Início</a>
      <span><?= icon('chevron-right', 10) ?></span>
      <span class="text-white/70"><?= htmlspecialchars($cat_atual['label']) ?></span>
    </nav>

    <div class="flex items-end justify-between gap-6 flex-wrap">
      <div>
        <!-- Icon + title -->
        <div class="flex items-center gap-4 mb-3">
          <div class="w-14 h-14 rounded-2xl bg-gold/15 border border-gold/20
                      flex items-center justify-center text-gold">
            <?= icon($cat_atual['icon'], 26) ?>
          </div>
          <div>
            <p class="text-[10px] font-black tracking-[0.22em] uppercase text-gold mb-1">
              Categoria
            </p>
            <h1 class="font-display text-[clamp(28px,4.5vw,48px)] font-bold text-white leading-none">
              <?= htmlspecialchars($cat_atual['label']) ?>
            </h1>
          </div>
        </div>
        <p class="text-[14px] font-light text-white/50 mt-4 max-w-[500px] leading-relaxed">
          <?= $total ?> estabelecimentos curados em Campo Belo e região.
          Cada indicação verificada pelo nosso time.
        </p>
      </div>

      <!-- Inline search -->
      <div class="flex items-center gap-2 bg-white/8 border border-white/12
                  rounded-full px-4 py-2.5 backdrop-blur-sm w-full sm:w-auto max-w-[320px]">
        <span class="text-gold"><?= icon('search', 16) ?></span>
        <input type="text" id="search-inline"
               placeholder="Buscar em <?= htmlspecialchars($cat_atual['label']) ?>…"
               class="bg-transparent border-none outline-none text-[13px] text-white
                      placeholder-white/35 flex-1 font-[Montserrat]"
               oninput="filterCards(this.value)" />
      </div>
    </div>

  </div>
</section>


<!-- ══════════════════════════════════════════
     TOOLBAR
══════════════════════════════════════════ -->
<div class="sticky top-[64px] z-[90] bg-cream/95 backdrop-blur-md
            border-b border-green/[0.08] shadow-[0_2px_8px_rgba(29,29,27,.05)]">
  <div class="max-w-[1180px] mx-auto px-10">
    <div class="flex items-center justify-between h-14 gap-4">

      <!-- Results count -->
      <p class="text-[12px] font-semibold text-warmgray flex-shrink-0">
        <span id="results-count" class="font-black text-graphite"><?= $total ?></span>
        resultado<?= $total !== 1 ? 's' : '' ?>
      </p>

      <div class="flex items-center gap-3 ml-auto">
        <!-- Sort -->
        <div class="relative hidden sm:block" id="sort-dropdown">
          <button onclick="toggleSort()"
                  class="flex items-center gap-2 px-4 py-2 bg-white border border-green/[0.1]
                         rounded-full text-[11.5px] font-semibold text-graphite
                         hover:border-gold/50 transition-colors">
            <?= icon('sliders', 14) ?>
            <span id="sort-label">Destaques</span>
            <?= icon('chevron-down', 12) ?>
          </button>
          <div id="sort-menu"
               class="hidden absolute right-0 top-[calc(100%+6px)] w-[180px] bg-white
                      border border-green/[0.08] rounded-2xl shadow-[0_8px_32px_rgba(29,29,27,.11)]
                      py-2 z-50">
            <?php
            $sorts = [
              'destaque'   => 'Destaques',
              'avaliacao'  => 'Melhor avaliados',
              'novo'       => 'Mais recentes',
              'preco-asc'  => 'Menor preço',
              'preco-desc' => 'Maior preço',
            ];
            foreach ($sorts as $val => $lbl): ?>
            <button onclick="setSort('<?= $val ?>', '<?= $lbl ?>')"
                    class="sort-opt w-full text-left px-4 py-2.5 text-[13px] font-medium
                           text-graphite hover:bg-offwhite transition-colors
                           <?= $val === $sort ? 'active' : '' ?>">
              <?= htmlspecialchars($lbl) ?>
            </button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- View toggle -->
        <div class="flex items-center bg-white border border-green/[0.1] rounded-full p-1">
          <button id="btn-grid" onclick="setView('grid')"
                  class="w-8 h-8 rounded-full flex items-center justify-center
                         bg-green-dark text-white transition-all duration-200"
                  aria-label="Vista em grade">
            <?= icon('grid', 14) ?>
          </button>
          <button id="btn-list" onclick="setView('list')"
                  class="w-8 h-8 rounded-full flex items-center justify-center
                         text-warmgray hover:text-graphite transition-all duration-200"
                  aria-label="Vista em lista">
            <?= icon('list', 14) ?>
          </button>
        </div>

        <!-- Mobile filter -->
        <button onclick="toggleMobileSidebar()"
                class="lg:hidden flex items-center gap-2 px-4 py-2 bg-white border
                       border-green/[0.1] rounded-full text-[11.5px] font-semibold text-graphite
                       hover:border-gold/50 transition-colors">
          <?= icon('filter', 14) ?> Filtros
        </button>
      </div>
    </div>
  </div>
</div>


<!-- ══════════════════════════════════════════
     MOBILE SIDEBAR OVERLAY
══════════════════════════════════════════ -->
<div id="sidebar-mobile"
     class="fixed inset-0 z-[300] flex items-end lg:hidden"
     onclick="toggleMobileSidebar()">
  <div class="absolute inset-0 bg-graphite/50 backdrop-blur-sm"></div>
  <div class="relative z-10 w-full bg-cream rounded-t-3xl p-6 max-h-[80vh] overflow-y-auto"
       onclick="event.stopPropagation()">
    <div class="flex items-center justify-between mb-6">
      <h3 class="font-display text-[20px] font-bold text-green-dark">Filtros</h3>
      <button onclick="toggleMobileSidebar()"
              class="w-9 h-9 rounded-full bg-offwhite flex items-center justify-center text-warmgray">
        <?= icon('close', 15) ?>
      </button>
    </div>
    <?php include '_sidebar_content.php'; ?>
  </div>
</div>


<!-- ══════════════════════════════════════════
     MAIN LAYOUT
══════════════════════════════════════════ -->
<div class="max-w-[1180px] mx-auto px-10 py-10">
  <div class="flex gap-8 items-start">

    <!-- ── SIDEBAR (desktop) ── -->
    <aside class="hidden lg:block w-[260px] flex-shrink-0 sticky top-[130px]">
      <?php

      /* ─ inline sidebar content (reused in mobile too) ─ */
      function render_sidebar(array $categorias, string $slug_atual, array $faixas, string $preco): void { ?>

      <!-- Categories -->
      <div class="bg-white rounded-2xl border border-green/[0.07] p-6 mb-4">
        <h3 class="text-[10px] font-black tracking-[0.22em] uppercase text-gold mb-4">
          Categorias
        </h3>
        <div class="space-y-1">
          <?php foreach ($categorias as $slug => $cat): ?>
          <a href="/pages/categoria.php?slug=<?= $slug ?>"
             class="cat-link flex items-center justify-between px-3 py-2.5 rounded-xl
                    border border-transparent text-[13px] font-medium text-graphite/70
                    hover:bg-gold-pale hover:text-green-dark hover:border-gold/30
                    transition-all duration-200 <?= $slug === $slug_atual ? 'active' : '' ?>">
            <div class="flex items-center gap-2.5">
              <span class="<?= $slug === $slug_atual ? 'text-green' : 'text-warmgray' ?> transition-colors">
                <?= icon($cat['icon'], 15) ?>
              </span>
              <?= htmlspecialchars($cat['label']) ?>
            </div>
            <span class="text-[11px] font-bold <?= $slug === $slug_atual ? 'text-gold' : 'text-warmgray/60' ?>">
              <?= $cat['count'] ?>
            </span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Price range -->
      <div class="bg-white rounded-2xl border border-green/[0.07] p-6">
        <h3 class="text-[10px] font-black tracking-[0.22em] uppercase text-gold mb-4">
          Faixa de Preço
        </h3>
        <div class="space-y-2">
          <?php foreach ($faixas as $val => $lbl): ?>
          <button onclick="setPreco('<?= $val ?>', this)"
                  class="price-btn w-full flex items-center justify-between px-3.5 py-2.5
                         rounded-xl border border-green/[0.08] text-[13px] font-medium
                         text-graphite/70 hover:bg-gold-pale hover:border-gold/30
                         transition-all duration-200 <?= $val === $preco ? 'active' : '' ?>">
            <span><?= htmlspecialchars($lbl) ?></span>
            <?php if ($val === $preco): ?>
            <span class="text-gold"><?= icon('verified', 13) ?></span>
            <?php endif; ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>

      <?php } // end render_sidebar

      render_sidebar($categorias, $slug_atual, $faixas, $preco);
      ?>
    </aside>


    <!-- ── CARDS AREA ── -->
    <div class="flex-1 min-w-0 view-grid" id="cards-area">

      <!-- No results (hidden by default) -->
      <div id="no-results" class="hidden flex-col items-center justify-center py-24 text-center">
        <div class="w-16 h-16 rounded-2xl bg-offwhite flex items-center justify-center
                    text-warmgray mb-4">
          <?= icon('search', 28) ?>
        </div>
        <h3 class="font-display text-[22px] font-bold text-green-dark mb-2">
          Nenhum resultado
        </h3>
        <p class="text-[14px] text-warmgray max-w-[300px] leading-relaxed">
          Tente outros termos ou remova os filtros aplicados.
        </p>
        <button onclick="clearFilters()"
                class="mt-6 px-6 py-2.5 bg-gold hover:bg-gold-light text-green-dark
                       text-[12px] font-black tracking-widest uppercase rounded-full
                       transition-colors duration-200">
          Limpar filtros
        </button>
      </div>

      <!-- Grid -->
      <div id="cards-grid"
           class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">

        <?php foreach ($listings as $i => $item): ?>
        <article class="listing-card reveal group bg-white rounded-[20px] overflow-hidden
                        border border-green/[0.06] shadow-[0_2px_12px_rgba(29,29,27,.07)]
                        hover:-translate-y-1.5 hover:shadow-[0_8px_32px_rgba(29,29,27,.11)]
                        transition-all duration-300"
                 data-nome="<?= htmlspecialchars(strtolower($item['nome'])) ?>"
                 data-preco="<?= htmlspecialchars($item['preco']) ?>"
                 style="animation-delay:<?= $i * 60 ?>ms">

          <!-- Image -->
          <div class="card-list-img relative h-[200px] overflow-hidden">
            <img src="<?= htmlspecialchars($item['img']) ?>"
                 alt="<?= htmlspecialchars($item['nome']) ?>"
                 loading="lazy"
                 class="w-full h-full object-cover transition-transform duration-[600ms]
                        group-hover:scale-[1.07]"/>

            <div class="absolute top-3 left-3 right-3 flex items-start justify-between">
              <?php if ($item['badge']): ?>
              <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px]
                           font-black tracking-[0.14em] uppercase
                           <?= $item['badge']==='Novo' ? 'bg-green text-white' : 'bg-gold text-green-dark' ?>">
                <?= htmlspecialchars($item['badge']) ?>
              </span>
              <?php else: ?>
              <span></span>
              <?php endif; ?>

              <button class="fav-btn w-8 h-8 rounded-full bg-cream/92 backdrop-blur-sm
                             flex items-center justify-center text-warmgray border-none
                             hover:scale-110 transition-all duration-200"
                      onclick="event.stopPropagation(); toggleFav(this)"
                      aria-label="Salvar">
                <?= icon('heart', 15) ?>
              </button>
            </div>

            <!-- Status badge -->
            <div class="absolute bottom-3 left-3">
              <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full
                           backdrop-blur-sm bg-graphite/55 text-[11px] font-semibold
                           <?= $item['aberto'] ? 'text-emerald-400' : 'text-red-400' ?>">
                <span class="w-1.5 h-1.5 rounded-full
                             <?= $item['aberto'] ? 'bg-emerald-400' : 'bg-red-400' ?>"></span>
                <?= $item['aberto'] ? 'Aberto' : 'Fechado' ?>
              </span>
            </div>
          </div>

          <!-- Body -->
          <div class="p-5">
            <p class="text-[9.5px] font-black tracking-[0.18em] uppercase text-gold mb-1.5">
              <?= htmlspecialchars($item['cat']) ?>
            </p>
            <h2 class="font-display text-[18px] font-bold text-green-dark leading-tight mb-2.5">
              <?= htmlspecialchars($item['nome']) ?>
            </h2>

            <!-- Rating + price -->
            <div class="flex items-center gap-2 flex-wrap mb-3">
              <div class="flex items-center gap-1 text-[12.5px]">
                <?= stars_cat($item['rating']) ?>
              </div>
              <span class="font-bold text-[13px] text-graphite"><?= number_format($item['rating'],1) ?></span>
              <span class="text-[12px] text-warmgray">(<?= $item['reviews'] ?>)</span>
              <span class="text-warmgray/40 text-[10px]">·</span>
              <span class="text-[13px] font-bold text-green tracking-wide">
                <?= htmlspecialchars($item['preco']) ?>
              </span>
            </div>

            <!-- Tags -->
            <div class="flex flex-wrap gap-1.5 mb-4">
              <?php foreach (array_slice($item['tags'], 0, 3) as $tag): ?>
              <span class="px-2.5 py-1 bg-offwhite rounded-full text-[10.5px]
                           font-semibold text-green">
                <?= htmlspecialchars($tag) ?>
              </span>
              <?php endforeach; ?>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between pt-3.5 border-t border-offwhite">
              <div class="flex items-center gap-1.5 text-[12px] text-warmgray">
                <?= icon('pin', 11) ?>
                <?= htmlspecialchars($item['endereco']) ?>
              </div>
              <a href="/pages/<?= htmlspecialchars($item['slug']) ?>"
                 class="flex items-center gap-1.5 text-[11px] font-black tracking-[0.06em]
                        uppercase text-gold group-hover:gap-2.5 transition-all duration-200">
                Ver mais <?= icon('arrow-right', 11) ?>
              </a>
            </div>
          </div>
        </article>
        <?php endforeach; ?>

      </div><!-- /cards-grid -->

      <!-- Pagination -->
      <div class="flex items-center justify-center gap-2 mt-10" id="pagination">
        <button class="w-9 h-9 rounded-full border border-green/[0.12] flex items-center
                       justify-center text-warmgray hover:border-gold hover:text-gold
                       transition-all duration-200 disabled:opacity-30" disabled>
          <?= icon('arrow-left', 14) ?>
        </button>
        <?php for ($p = 1; $p <= 3; $p++): ?>
        <button class="w-9 h-9 rounded-full border text-[13px] font-bold transition-all duration-200
                       <?= $p===1 ? 'bg-green-dark text-white border-green-dark'
                                  : 'border-green/[0.12] text-warmgray hover:border-gold hover:text-gold' ?>">
          <?= $p ?>
        </button>
        <?php endfor; ?>
        <button class="w-9 h-9 rounded-full border border-green/[0.12] flex items-center
                       justify-center text-warmgray hover:border-gold hover:text-gold
                       transition-all duration-200">
          <?= icon('arrow-right', 14) ?>
        </button>
      </div>

    </div><!-- /cards-area -->
  </div>
</div>


<?php include __DIR__ . '/../includes/footer.php'; ?>

<script src="/assets/js/app.js" defer></script>
<script>
/* ── Reveal ── */
const ro = new IntersectionObserver(entries => {
  entries.forEach((e, i) => {
    if (e.isIntersecting) {
      setTimeout(() => e.target.classList.add('in'), i * 70);
      ro.unobserve(e.target);
    }
  });
}, { threshold: 0.06 });
document.querySelectorAll('.reveal').forEach(el => ro.observe(el));

/* ── View toggle ── */
function setView(v) {
  const area   = document.getElementById('cards-area');
  const btnG   = document.getElementById('btn-grid');
  const btnL   = document.getElementById('btn-list');
  area.className = area.className.replace(/view-\w+/, '') + ' view-' + v;
  if (v === 'grid') {
    btnG.classList.add('bg-green-dark','text-white');
    btnG.classList.remove('text-warmgray');
    btnL.classList.remove('bg-green-dark','text-white');
    btnL.classList.add('text-warmgray');
  } else {
    btnL.classList.add('bg-green-dark','text-white');
    btnL.classList.remove('text-warmgray');
    btnG.classList.remove('bg-green-dark','text-white');
    btnG.classList.add('text-warmgray');
  }
}

/* ── Sort dropdown ── */
function toggleSort() {
  document.getElementById('sort-menu').classList.toggle('hidden');
}
function setSort(val, lbl) {
  document.getElementById('sort-label').textContent = lbl;
  document.getElementById('sort-menu').classList.add('hidden');
  document.querySelectorAll('.sort-opt').forEach(b => b.classList.remove('active'));
  event.target.classList.add('active');
}
document.addEventListener('click', e => {
  const dd = document.getElementById('sort-dropdown');
  if (!dd.contains(e.target)) document.getElementById('sort-menu').classList.add('hidden');
});

/* ── Price filter ── */
const PRICE_MAP = { 'barato':'R$$','medio':'R$$$','alto':'R$$$$','luxo':'R$$$$$','todos':null };
let activePreco = '<?= $preco ?>';

function setPreco(val, btn) {
  activePreco = val;
  document.querySelectorAll('.price-btn').forEach(b => {
    b.classList.remove('active');
    b.innerHTML = b.innerHTML.replace(/<span[^>]*>.*?<\/span>\s*$/, '');
  });
  btn.classList.add('active');
  applyFilters();
}

/* ── Search filter ── */
function filterCards(q) { applyFilters(q); }

/* ── Apply all filters ── */
function applyFilters(q) {
  const query   = (q ?? document.getElementById('search-inline').value).toLowerCase().trim();
  const cards   = document.querySelectorAll('.listing-card');
  const target  = PRICE_MAP[activePreco];
  let visible   = 0;

  cards.forEach(card => {
    const nome   = card.dataset.nome  ?? '';
    const preco  = card.dataset.preco ?? '';
    const nameOk = !query  || nome.includes(query);
    const precoOk= !target || preco === target;
    const show   = nameOk && precoOk;
    card.style.display = show ? '' : 'none';
    if (show) visible++;
  });

  document.getElementById('results-count').textContent = visible;
  document.getElementById('no-results').classList.toggle('hidden', visible > 0);
  document.getElementById('no-results').style.display = visible === 0 ? 'flex' : 'none';
  document.getElementById('pagination').style.display = visible === 0 ? 'none' : '';
}

function clearFilters() {
  activePreco = 'todos';
  document.getElementById('search-inline').value = '';
  document.querySelectorAll('.price-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.listing-card').forEach(c => c.style.display = '');
  document.getElementById('results-count').textContent = document.querySelectorAll('.listing-card').length;
  document.getElementById('no-results').style.display  = 'none';
  document.getElementById('pagination').style.display  = '';
}

/* ── Mobile sidebar ── */
function toggleMobileSidebar() {
  document.getElementById('sidebar-mobile').classList.toggle('open');
  document.body.style.overflow =
    document.getElementById('sidebar-mobile').classList.contains('open') ? 'hidden' : '';
}

/* ── Favourite toggle ── */
function toggleFav(btn) {
  btn.classList.toggle('is-fav');
  const on = btn.classList.contains('is-fav');
  btn.style.color = on ? '#e05555' : '';
}
</script>
</body>
</html>