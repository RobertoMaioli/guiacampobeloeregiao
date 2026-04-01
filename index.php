<?php
require_once __DIR__ . '/core/UserAuth.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/core/DB.php';
UserAuth::start();

$page_title = 'Guia Campo Belo & Região — Curadoria Premium de SP';
$meta_desc  = 'A autoridade absoluta sobre o viver bem no Campo Belo e região. Restaurantes, serviços e experiências curados para quem valoriza tempo e qualidade.';
$canonical  = 'https://guiacampobeloeregiao.com.br/';

function stars(int $n): string {
    return str_repeat('★', max(0,min(5,$n))) . str_repeat('☆', 5-max(0,min(5,$n)));
}

/* ── Categorias ── */
$cats_db = DB::query('SELECT slug,label,icon FROM categorias WHERE ativo=1 ORDER BY ordem,label');
$categories = array_merge([['slug'=>'all','label'=>'Destaques','icon'=>'trending-up']], $cats_db);

/* ── Destaques editoriais ── */
$featured_db = DB::query(
    "SELECT l.id,l.slug,l.nome,l.cat_label,l.badge,c.label AS cat_nome,
            COALESCE(f.url,l.foto_principal) AS img
     FROM lugares l JOIN categorias c ON c.id=l.categoria_id
     LEFT JOIN fotos f ON f.lugar_id=l.id AND f.principal=1
     WHERE l.ativo=1 AND l.destaque=1 ORDER BY l.atualizado_em DESC LIMIT 3");
$featured = [];
foreach ($featured_db as $i => $row) {
    $featured[] = [
        'id'=>$row['id'],'tall'=>$i===0,
        'img'=>$row['img']??'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=900&q=80',
        'category'=>$row['cat_label']??$row['cat_nome'],
        'title'=>$row['nome'],'meta'=>$row['cat_nome'],'slug'=>$row['slug'],
    ];
}
if (empty($featured)) {
    $featured = [
        ['id'=>0,'tall'=>true,'img'=>'/assets/img/sem-imagem.png','category'=>'Curadoria','title'=>'Cadastre seus primeiros destaques no admin','meta'=>'Admin → Lugares → marcar como Destaque','slug'=>''],
        ['id'=>0,'tall'=>false,'img'=>'/assets/img/sem-imagem.png','category'=>'Dica','title'=>'Acesse o painel admin para cadastrar lugares','meta'=>'','slug'=>''],
        ['id'=>0,'tall'=>false,'img'=>'/assets/img/sem-imagem.png','category'=>'Dica','title'=>'Configure categorias e adicione fotos','meta'=>'','slug'=>''],
    ];
}

/* ── Listings ── */
$listings_db = DB::query(
    "SELECT l.id,l.slug,l.nome,l.cat_label,l.badge,l.plano,l.preco_simbolo,l.endereco,
            l.rating,l.total_reviews,c.slug AS cat_slug,c.label AS cat_nome,
            COALESCE(f.url,l.foto_principal) AS foto_capa,
            CASE WHEN EXISTS(SELECT 1 FROM horarios h WHERE h.lugar_id=l.id
              AND h.dia_semana=DAYOFWEEK(NOW())-1 AND h.fechado=0
              AND(h.dia_todo=1 OR(h.hora_abre<=TIME(NOW()) AND (IF(h.hora_fecha='00:00:00','24:00:00',h.hora_fecha)>=TIME(NOW()))))
            ) THEN 1 ELSE 0 END AS aberto_agora
     FROM lugares l JOIN categorias c ON c.id=l.categoria_id
     LEFT JOIN fotos f ON f.lugar_id=l.id AND f.principal=1
     WHERE l.ativo=1 ORDER BY l.destaque DESC,l.rating DESC LIMIT 6");
$listings = [];
foreach ($listings_db as $row) {
    $tags = DB::query('SELECT t.label FROM lugar_tags lt JOIN tags t ON t.id=lt.tag_id WHERE lt.lugar_id=? LIMIT 3',[$row['id']]);
    $row['tags'] = array_column($tags,'label');

    // Badge: premium sempre mostra "Destaque", senão usa o badge manual
    $badge_label = ($row['plano'] === 'premium') ? 'Destaque' : ($row['badge'] ?: null);
    $row['badge'] = $badge_label ? ['label' => $badge_label, 'gold' => strtolower($badge_label) !== 'novo'] : null;

    $row['name']     = $row['nome'];
    $row['category'] = $row['cat_label']??$row['cat_nome'];
    $row['price']    = $row['preco_simbolo'];
    $row['img']      = $row['foto_capa']??'/assets/img/sem-imagem.png';
    $row['address']  = $row['endereco'];
    $row['rating']   = (int)round($row['rating']);
    $row['reviews']  = (int)$row['total_reviews'];
    $listings[] = $row;
}

$map_features = [
    ['icon'=>'pin',     'title'=>'Localização em tempo real',       'desc'=>'O que está mais perto de você agora'],
    ['icon'=>'sliders', 'title'=>'Filtros por categoria e avaliação','desc'=>'Refine sua busca sem esforço'],
    ['icon'=>'verified','title'=>'Só o que passou pelo nosso crivo', 'desc'=>'Zero lugar medíocre no mapa'],
];
$quick_tags = ['Brunch','Sushi','Pet shop','Academia','Wine bar','Salão'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><?php include __DIR__ . '/includes/head.php'; ?></head>
<body>

<?php include __DIR__ . '/includes/search-modal.php'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>

<!-- ── HERO ── -->
<section class="hero-section">
  <!-- Foto de fundo — troque o src pela foto real do Campo Belo -->
  <img id="hero-bg-img"
       src="assets/img/campo-belo.jpg"
       alt=""
       style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;
              object-position:center;opacity:0;transition:opacity 1.4s ease;
              filter:saturate(.45) brightness(.32);z-index:0;
              animation:slowZoom 22s ease-in-out infinite alternate">
  <!-- Canvas 2D — partículas douradas -->
  <canvas id="hero-canvas"
          style="position:absolute;inset:0;width:100%;height:100%;z-index:1"></canvas>
  <div class="hero-overlay" style="z-index:2"></div>

  <div class="hero-content w-100 text-center px-3" style="z-index:3;position:relative">
    <!-- Eyebrow -->
    <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill mb-4 animate-fade-up delay-1"
         style="border:1px solid rgba(201,170,107,.35);background:rgba(201,170,107,.1);opacity:0">
      <span class="text-white"><?= icon('pin',12) ?></span>
      <span style="font-size:10px;font-weight:800;letter-spacing:.18em;text-transform:uppercase;color:var(--gcb-gold-light)">
        São Paulo &middot; Zona Sul &middot; Campo Belo e Região
      </span>
    </div>

    <!-- H1 -->
    <h1 class="text-white font-display fw-bold animate-fade-up delay-2 mb-3"
        style="font-size:clamp(36px,6vw,68px);line-height:1.1;opacity:0">
      Encontre o melhor de<br/> <em class="fst-italic text-gold">Campo Belo</em> e <em class="fst-italic text-gold">Região</em>
    </h1>

    <p class="animate-fade-up delay-3 mb-5 mx-auto"
       style="font-size:15px;font-weight:300;color:#fff;max-width:480px;opacity:0">
      Curadoria dos melhores restaurantes, serviços e experiências.<br class="d-none d-lg-block "/>
      Tudo filtrado, tudo verificado.
    </p>

    <!-- Search box -->
    <div class="animate-fade-up delay-4 mx-auto" style="max-width:760px;opacity:0">
      <div class="search-box" id="search-box">
        <!-- Keyword -->
        <div class="search-field">
          <span style="color:var(--gcb-gold);flex-shrink:0"><?= icon('search',18) ?></span>
          <label for="hero-search-input">O que você busca?</label>
          <input type="text" id="hero-search-input" name="q"
                 placeholder="Restaurante, café, serviço…" autocomplete="off"/>
          <!-- Autocomplete -->
          <div id="search-dropdown">
            <p class="dropdown-label">Populares agora</p>
            <?php
            $drops = [
              ['fill'=>'Osteria Moderna', 'icon'=>'utensils','sub'=>'Italiana · Campo Belo · ★★★★★'],
              ['fill'=>'Nishiki Omakase', 'icon'=>'utensils','sub'=>'Japonesa · Brooklin · ★★★★★'],
              ['fill'=>'Bossa Café',      'icon'=>'coffee',  'sub'=>'Café · Campo Belo · ★★★★☆'],
            ];
            foreach ($drops as $d): ?>
            <div class="dropdown-item-gcb" data-fill="<?= htmlspecialchars($d['fill']) ?>">
              <div class="dropdown-item-icon"><?= icon($d['icon'],16) ?></div>
              <div>
                <div class="dropdown-item-title"><?= htmlspecialchars($d['fill']) ?></div>
                <div class="dropdown-item-sub"><?= htmlspecialchars($d['sub']) ?></div>
              </div>
            </div>
            <?php endforeach; ?>
            <hr class="mx-4 my-1" style="border-color:var(--gcb-offwhite)"/>
            <p class="dropdown-label">Recentes</p>
            <?php foreach (['Brunch Campo Belo','Pet shop perto'] as $r): ?>
            <div class="dropdown-recent-row dropdown-item-gcb">
              <div class="d-flex align-items-center gap-2" style="font-size:13px;font-weight:500;color:var(--gcb-graphite)">
                <?= icon('clock',14) ?> <?= htmlspecialchars($r) ?>
              </div>
              <button class="recent-remove ms-auto border-0 bg-transparent p-0"
                      style="width:20px;height:20px;color:var(--gcb-warmgray)">
                <?= icon('close',12) ?>
              </button>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="search-box-divider"></div>

        <!-- Category -->
        <div class="search-cat">
          <span style="color:var(--gcb-gold);flex-shrink:0"><?= icon('grid',16) ?></span>
          <label for="hero-search-cat">Categoria</label>
          <select id="hero-search-cat" name="categoria">
            <option value="">Todas</option>
            <?php foreach (array_slice($categories,1) as $cat): ?>
            <option value="<?= htmlspecialchars($cat['slug']??'') ?>">
              <?= htmlspecialchars($cat['label']??'') ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Submit -->
        <button class="search-submit" type="button" aria-label="Buscar">
          <?= icon('search',22) ?>
          <span>Buscar</span>
        </button>
      </div>

    </div>
  </div>

  <!-- Stats bar -->
    <div class="hero-stats animate-fade-up delay-6" style="opacity:0">
      <div class="d-flex justify-content-center flex-wrap">
        <?php
        // Busca números reais do banco
        $stat_row = DB::row(
          "SELECT
             (SELECT COUNT(*) FROM lugares    WHERE ativo = 1) AS total_lugares,
             (SELECT COUNT(*) FROM categorias WHERE ativo = 1) AS total_cats,
             (SELECT ROUND(AVG(rating),1) FROM lugares WHERE ativo = 1 AND rating > 0) AS avg_rating"
        );
        $stats_hero = [
          ['num' => number_format((int)$stat_row['total_lugares']) . '+', 'label' => 'Estabelecimentos'],
          ['num' => (int)$stat_row['total_cats'],                   'label' => 'Categorias'],
          ['num' => number_format((float)$stat_row['avg_rating'],1,'.',',') . '★', 'label' => 'Avaliação média'],
          ['num' => '0,935', 'label' => 'IDH do bairro'],
        ];
        foreach ($stats_hero as $s): ?>
        <div class="hero-stat-item text-center">
          <div class="hero-stat-num"><?= $s['num'] ?></div>
          <div class="hero-stat-label"><?= $s['label'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
</section>

<!-- ── CATEGORY GRID ── -->
<section class="py-5 bg-offwhite" style="overflow:hidden;isolation:isolate">
  <div class="container">

    <div class="d-flex align-items-end justify-content-between mb-4 gap-3 flex-wrap">
      <div>
        <span class="eyebrow">Explorar</span>
        <h2 class="font-display fw-bold mb-0" style="color:var(--gcb-green-dark);font-size:clamp(22px,3vw,36px)">
          O que você está <em class="fst-italic text-gold">procurando?</em>
        </h2>
      </div>
      <a href="/pages/categoria.php" class="btn-outline-green" style="flex-shrink:0">
        Ver todos <?= icon('arrow-right', 13) ?>
      </a>
    </div>

    <?php
    // Busca categorias com contagem, ordenadas por quantidade de lugares
    $cats_com_count = DB::query(
      "SELECT c.slug, c.label, c.icon,
              COUNT(l.id) AS total
       FROM categorias c
       LEFT JOIN lugares l ON l.categoria_id = c.id AND l.ativo = 1
       WHERE c.ativo = 1
       GROUP BY c.id
       ORDER BY total DESC, c.ordem ASC, c.label ASC"
    );
    // Exibe no máximo 2 linhas (depende do grid responsivo — mostra todas, CSS limita visualmente)
    ?>

    <div class="cats-index-grid">
      <?php foreach ($cats_com_count as $cat):
        $cnt = (int)$cat['total'];
      ?>
      <a href="/pages/categoria.php?slug=<?= htmlspecialchars($cat['slug']) ?>"
         class="cat-index-block">
        <div class="cat-index-bg"></div>
        <div class="cat-index-inner">
          <div class="cat-index-icon">
            <?= icon($cat['icon'] ?? 'grid', 20) ?>
          </div>
          <div class="cat-index-label"><?= htmlspecialchars($cat['label']) ?></div>
          <div class="cat-index-count"><?= $cnt ?> lugar<?= $cnt !== 1 ? 'es' : '' ?></div>
        </div>
        <div class="cat-index-arrow"><?= icon('arrow-right', 10) ?></div>
      </a>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- ── FEATURED ── -->
<section class="py-5 bg-offwhite">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-5 gap-3 flex-wrap">
      <div>
        <span class="eyebrow">Em destaque</span>
        <h2 class="font-display fw-bold mb-0" style="color:var(--gcb-green-dark);font-size:clamp(26px,3.5vw,42px)">
          Seleções da <em class="fst-italic text-gold">semana</em>
        </h2>
        <div class="section-line"></div>
      </div>
      <a href="/pages/categoria.php" class="btn-outline-green">Ver todos</a>
    </div>

    <div class="row g-3" style="min-height:600px">
      <!-- Tall card -->
      <?php if (!empty($featured[0])): $f = $featured[0]; ?>
      <div class="col-12 col-lg-7">
        <a href="<?= $f['slug'] ? '/pages/lugar/'.htmlspecialchars($f['slug']) : '#' ?>"
           class="d-block h-100 position-relative rounded-20 overflow-hidden text-decoration-none reveal"
           style="min-height:600px">
          <img src="<?= htmlspecialchars($f['img']) ?>" alt="<?= htmlspecialchars($f['title']) ?>"
               class="w-100 h-100 object-fit-cover position-absolute top-0 start-0"
               style="transition:transform .7s ease" loading="eager"/>
          <div class="position-absolute inset-0"
               style="background:linear-gradient(180deg,transparent 28%,rgba(29,29,27,.82) 100%);inset:0"></div>
          <div class="position-absolute bottom-0 start-0 end-0 p-4">
            <p style="font-size:9px;font-weight:800;letter-spacing:.2em;text-transform:uppercase;color:var(--gcb-gold-light)" class="mb-2">
              <?= htmlspecialchars($f['category']) ?>
            </p>
            <p class="font-display fw-bold text-white mb-1" style="font-size:28px;line-height:1.25">
              <?= htmlspecialchars($f['title']) ?>
            </p>
            <p style="font-size:11px;color:rgba(255,255,255,.52);font-weight:500">
              <?= htmlspecialchars($f['meta']) ?>
            </p>
          </div>
        </a>
      </div>
      <?php endif; ?>

      <!-- Stacked cards -->
      <div class="col-12 col-lg-5 d-flex flex-column gap-3">
        <?php foreach (array_slice($featured,1) as $f): ?>
        <a href="<?= $f['slug'] ? '/pages/lugar/'.htmlspecialchars($f['slug']) : '#' ?>"
           class="d-block flex-fill position-relative rounded-20 overflow-hidden text-decoration-none reveal"
           style="min-height:285px">
          <img src="<?= htmlspecialchars($f['img']) ?>" alt="<?= htmlspecialchars($f['title']) ?>"
               class="w-100 h-100 object-fit-cover position-absolute top-0 start-0"
               style="transition:transform .7s ease" loading="lazy"/>
          <div class="position-absolute"
               style="inset:0;background:linear-gradient(180deg,transparent 28%,rgba(29,29,27,.82) 100%)"></div>
          <div class="position-absolute bottom-0 start-0 end-0 p-4">
            <p style="font-size:9px;font-weight:800;letter-spacing:.2em;text-transform:uppercase;color:var(--gcb-gold-light)" class="mb-1">
              <?= htmlspecialchars($f['category']) ?>
            </p>
            <p class="font-display fw-bold text-white mb-0" style="font-size:20px;line-height:1.25">
              <?= htmlspecialchars($f['title']) ?>
            </p>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ── MAP CTA ── -->
<section class="py-5" style="background:var(--gcb-green-dark);overflow:hidden">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-12 col-lg-6">
        <span class="eyebrow">Explore a região</span>
        <h2 class="font-display fw-bold text-white mb-3" style="font-size:clamp(26px,3.5vw,42px)">
          Tudo no mapa,<br/><em class="fst-italic text-gold">ao alcance</em><br/>das suas mãos
        </h2>
        <p style="font-size:15px;font-weight:300;color:rgba(255,255,255,.45);line-height:1.75" class="mb-4">
          Campo Belo, Brooklin, Moema e arredores em um só mapa interativo e filtrado.
        </p>
        <div class="d-flex flex-column gap-3 mb-5">
          <?php foreach ($map_features as $f): ?>
          <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0 rounded-3 d-flex align-items-center justify-content-center"
                 style="width:40px;height:40px;background:rgba(201,170,107,.12);color:var(--gcb-gold)">
              <?= icon($f['icon']??'pin',18) ?>
            </div>
            <div>
              <strong class="d-block text-white" style="font-size:13.5px"><?= htmlspecialchars($f['title']) ?></strong>
              <span style="font-size:12.5px;color:rgba(255,255,255,.4)"><?= htmlspecialchars($f['desc']) ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <a href="/pages/mapa.php" class="btn-gold d-inline-flex align-items-center gap-2">
          Abrir Mapa Interativo <?= icon('arrow-right',14) ?>
        </a>
      </div>
      <div class="col-12 col-lg-6 reveal">
        <div class="rounded-20 overflow-hidden position-relative" style="height:400px">
          <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?w=800&q=80"
               alt="Mapa da região" class="w-100 h-100 object-fit-cover"
               style="filter:saturate(.65) brightness(.85)"/>
          <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
               style="background:rgba(42,48,34,.25)">
            <a href="/pages/mapa.php" class="d-flex flex-column align-items-center gap-3 text-decoration-none">
              <div class="rounded-circle d-flex align-items-center justify-content-center animate-map-pulse"
                   style="width:66px;height:66px;background:var(--gcb-gold);color:var(--gcb-green-dark)">
                <?= icon('pin',26) ?>
              </div>
              <span class="text-white fw-bold" style="font-size:11.5px;letter-spacing:.1em;text-transform:uppercase">
                Campo Belo
              </span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── LISTINGS ── -->
<section class="py-5 bg-cream" style="overflow:hidden">
  <div class="container">
    <div class="text-center mb-5">
      <span class="eyebrow">Empresas</span>
      <h2 class="font-display fw-bold mb-0" style="color:var(--gcb-green-dark);font-size:clamp(26px,3.5vw,42px)">
        Melhor <em class="fst-italic text-gold">avaliados</em>
      </h2>
      <div class="section-line mx-auto mt-3"></div>
      <p class="mt-3 mx-auto" style="font-size:15px;font-weight:300;color:var(--gcb-warmgray);max-width:480px">
        Melhores avaliados a partir do Google Business
      </p>
    </div>
 
    <div class="row g-4">
      <?php foreach ($listings as $item): ?>
      <div class="col-12 col-md-6 col-lg-4">
        <article class="gcb-card listing-card h-100"
                 data-category="<?= htmlspecialchars($item['cat_slug']??'') ?>">
          <div class="card-img-wrap position-relative">
            <a href="/pages/lugar/<?= htmlspecialchars($item['slug']) ?>">
                <img src="<?= htmlspecialchars($item['img']) ?>"
                 alt="<?= htmlspecialchars($item['name']) ?>"
                 class="card-img-top" loading="lazy"/>
            </a>
            <div class="position-absolute top-0 start-0 end-0 d-flex justify-content-between p-3">
              <?php if ($item['badge']): ?>
              <span class="<?= $item['badge']['gold'] ? 'badge-gold' : 'badge-green' ?>">
                <?= htmlspecialchars($item['badge']['label']) ?>
              </span>
              <?php else: ?><span></span><?php endif; ?>
              <!--<button class="fav-btn" onclick="this.classList.toggle('is-fav')"-->
              <!--        aria-label="Salvar">-->
              <!--  <?= icon('heart',15) ?>-->
              <!--</button>-->
            </div>
            <?php if ($item['aberto_agora'] !== null): ?>
            <div class="position-absolute bottom-0 start-0 m-3">
              <span class="d-flex align-items-center gap-2 px-2 py-1 rounded-pill"
                    style="background:rgba(29,29,27,.55);font-size:11px;font-weight:600;
                           color:<?= $item['aberto_agora'] ? '#34d399' : '#f87171' ?>">
                <span class="rounded-circle" style="width:6px;height:6px;display:inline-block;
                      background:<?= $item['aberto_agora'] ? '#34d399' : '#f87171' ?>"></span>
                <?= $item['aberto_agora'] ? 'Aberto' : 'Fechado' ?>
              </span>
            </div>
            <?php endif; ?>
          </div>
          <div class="card-body">
            <p class="card-cat"><?= htmlspecialchars($item['category']) ?></p>
            <h3 class="card-title"><?= htmlspecialchars($item['name']) ?></h3>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
              <span class="stars"><?= stars($item['rating']) ?></span>
              <span style="font-size:12px;color:var(--gcb-warmgray)">(<?= $item['reviews'] ?>)</span>
              <span style="font-size:10px;color:var(--gcb-warmgray)">&middot;</span>
              <span style="font-size:13px;font-weight:700;color:var(--gcb-green)"><?= htmlspecialchars($item['price']) ?></span>
            </div>
            <div class="d-flex flex-wrap gap-2 mb-0">
              <?php foreach ($item['tags'] as $tag): ?>
              <span class="tag-pill"><?= htmlspecialchars($tag) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="card-footer-row px-4 pb-4">
            <span class="d-flex align-items-center gap-1" style="font-size:12px;color:var(--gcb-warmgray)">
              <?= icon('pin',11) ?> <?= htmlspecialchars($item['address']) ?>
            </span>
            <a href="/pages/lugar/<?= htmlspecialchars($item['slug']) ?>"
               style="font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--gcb-gold)">
              Ver mais <?= icon('arrow-right',11) ?>
            </a>
          </div>
        </article>
      </div>
      <?php endforeach; ?>
    </div>
 
    <?php if (empty($listings)): ?>
    <div class="text-center py-5">
      <p style="color:var(--gcb-warmgray)">
        Nenhum lugar cadastrado ainda.
        <a href="/admin/lugares/create.php" style="color:var(--gcb-gold)">Cadastrar agora</a>
      </p>
    </div>
    <?php endif; ?>
 
    <div class="text-center mt-5">
      <a href="/pages/categoria.php" class="btn-outline-green">
        Ver todos os estabelecimentos
      </a>
    </div>
  </div>
</section>

<!-- ── ANUNCIE SUA EMPRESA ── -->
<section class="py-5" style="background:linear-gradient(135deg,#2a3022 0%,#3d4733 100%);overflow:hidden;position:relative">
  <!-- Glows decorativos -->
  <div class="position-absolute" style="top:-80px;right:-80px;width:400px;height:400px;background:radial-gradient(circle,rgba(201,170,107,.1) 0%,transparent 70%);pointer-events:none"></div>
  <div class="position-absolute" style="bottom:-60px;left:-60px;width:300px;height:300px;background:radial-gradient(circle,rgba(79,92,64,.35) 0%,transparent 70%);pointer-events:none"></div>
 
  <div class="container position-relative">
    <div class="row align-items-center g-5">
 
      <!-- Texto -->
      <div class="col-12 col-lg-6">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill mb-4"
             style="background:rgba(201,170,107,.12);border:1px solid rgba(201,170,107,.25)">
          <?= icon('star', 12) ?>
          <span style="font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:rgba(201,170,107,.9)">
            Plano gratuito disponível
          </span>
        </div>
        <span class="eyebrow" style="color:rgba(201,170,107,.6)">Para empresas</span>
        <h2 class="font-display fw-bold text-white mb-3" style="font-size:clamp(28px,3.5vw,44px);line-height:1.15">
          Coloque sua empresa<br/>no <em class="fst-italic" style="color:var(--gcb-gold)">radar certo.</em>
        </h2>
        <p style="font-size:15px;font-weight:300;color:rgba(255,255,255,.5);line-height:1.75;max-width:480px" class="mb-5">
          Apareça para quem mora, trabalha e frequenta Campo Belo e região.
          Curadoria premium, público qualificado, resultado real.
        </p>
        <div class="d-flex flex-wrap gap-3">
          <a href="/empresa/cadastro.php?plan=essencial"
             class="btn-gold d-inline-flex align-items-center gap-2">
            <?= icon('building', 14) ?> Anunciar grátis
          </a>
          <a href="/pages/anuncie.php"
             class="d-inline-flex align-items-center gap-2 px-4 py-3 rounded-pill"
             style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);color:#fff;font-size:12px;font-weight:700;text-decoration:none;letter-spacing:.06em">
            Ver planos e preços <?= icon('arrow-right', 12) ?>
          </a>
        </div>
      </div>
 
      <!-- Features -->
      <div class="col-12 col-lg-6">
        <div class="d-flex flex-column gap-3">
          <?php foreach ([
            ['icon'=>'pin',          'title'=>'Apareça no mapa interativo',    'desc'=>'Pin exclusivo no mapa do bairro — seja encontrado por quem está explorando a região agora.'],
            ['icon'=>'verified',     'title'=>'Google Reviews integrado',      'desc'=>'Sincronize sua nota e avaliações automaticamente com o perfil do Google.'],
            ['icon'=>'whatsapp',     'title'=>'Botão WhatsApp direto',         'desc'=>'Contato imediato sem atrito — o cliente chega até você com um toque.'],
            ['icon'=>'trending-up',  'title'=>'Destaque nas buscas',           'desc'=>'Planos Profissional e Premium aparecem antes dos demais nos resultados.'],
          ] as $f): ?>
          <div class="d-flex align-items-start gap-3 p-3 rounded-20"
               style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.07)">
            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:40px;height:40px;background:rgba(201,170,107,.14);color:var(--gcb-gold)">
              <?= icon($f['icon'], 18) ?>
            </div>
            <div>
              <strong class="d-block text-white" style="font-size:13px;margin-bottom:2px"><?= $f['title'] ?></strong>
              <span style="font-size:12px;color:rgba(255,255,255,.4);line-height:1.5"><?= $f['desc'] ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
 
    </div>
  </div>
</section>


<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
(function(){
  var img = document.getElementById('hero-bg-img');
  if (img) {
    if (img.complete) img.style.opacity = 1;
    else img.onload = function(){ img.style.opacity = 1; };
  }

  var cv  = document.getElementById('hero-canvas');
  if (!cv) return;
  var ctx = cv.getContext('2d');
  var W, H, pts = [];
  var mx = -9999, my = -9999;
  var N = 160, THRESH = 120, GOLD = '201,170,107';

  function init() {
    W = cv.width  = cv.offsetWidth  || cv.parentElement.clientWidth;
    H = cv.height = cv.offsetHeight || cv.parentElement.clientHeight;
    pts = [];
    for (var i = 0; i < N; i++) pts.push({
      x:  Math.random() * W,
      y:  Math.random() * H,
      vx: (Math.random() - .5) * .5,
      vy: (Math.random() - .5) * .4,
      r:  Math.random() * 1.8 + .8
    });
  }

  document.addEventListener('mousemove', function(e) {
    var r = cv.getBoundingClientRect();
    mx = e.clientX - r.left;
    my = e.clientY - r.top;
  });

  function draw() {
    ctx.clearRect(0, 0, W, H);

    for (var i = 0; i < N; i++) {
      var p = pts[i];
      p.x += p.vx; p.y += p.vy;
      if (p.x < 0) { p.x = 0; p.vx *= -1; }
      if (p.x > W) { p.x = W; p.vx *= -1; }
      if (p.y < 0) { p.y = 0; p.vy *= -1; }
      if (p.y > H) { p.y = H; p.vy *= -1; }

      var dxm = mx - p.x, dym = my - p.y;
      var dm  = Math.sqrt(dxm*dxm + dym*dym);
      if (dm < 180 && dm > 0) {
        p.vx += (dxm / dm) * .018;
        p.vy += (dym / dm) * .018;
      }
      var spd = Math.sqrt(p.vx*p.vx + p.vy*p.vy);
      if (spd > 1.4) { p.vx *= .96; p.vy *= .96; }

      for (var j = i+1; j < N; j++) {
        var q  = pts[j];
        var dx = p.x - q.x, dy = p.y - q.y;
        var d  = Math.sqrt(dx*dx + dy*dy);
        if (d < THRESH) {
          ctx.beginPath();
          ctx.moveTo(p.x, p.y);
          ctx.lineTo(q.x, q.y);
          ctx.strokeStyle = 'rgba('+GOLD+','+(((.22*(1-d/THRESH)).toFixed(3)))+')';
          ctx.lineWidth = .8;
          ctx.stroke();
        }
      }
    }

    for (var i = 0; i < N; i++) {
      var p = pts[i];
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI*2);
      ctx.fillStyle = 'rgba('+GOLD+',.85)';
      ctx.fill();
    }

    requestAnimationFrame(draw);
  }

  init();
  window.addEventListener('resize', init);
  draw();
})();
</script>
</body>
</html>