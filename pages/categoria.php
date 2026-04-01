<?php
/**
 * pages/categoria.php
 * Listagem de lugares por categoria com infinite scroll
 */
require_once __DIR__ . '/../core/UserAuth.php';
require_once __DIR__ . '/../includes/icons.php';
require_once __DIR__ . '/../core/DB.php';
UserAuth::start();

// ── Parâmetros ──────────────────────────────────────────────────────────────
$slug_raw   = trim($_GET['slug'] ?? '');
$slug_atual = ($slug_raw === '' || $slug_raw === 'todos') ? '' : $slug_raw;
$sort       = $_GET['sort'] ?? 'destaque';
$preco      = $_GET['preco'] ?? 'todos';
$q_busca    = trim($_GET['q'] ?? '');
$per_page   = 12;

$faixas = [
    'todos'  => 'Todos os preços',
    'barato' => 'Até R$ 60',
    'medio'  => 'R$ 60 – R$ 120',
    'alto'   => 'R$ 120 – R$ 200',
    'luxo'   => 'Acima de R$ 200',
];

// ── Categorias ──────────────────────────────────────────────────────────────
$cats_db = DB::query(
    'SELECT slug, label, icon,
     (SELECT COUNT(*) FROM lugares l WHERE l.categoria_id = c.id AND l.ativo = 1) AS total
     FROM categorias c WHERE c.ativo = 1 ORDER BY total DESC, c.label ASC'
);
$categorias = [];
foreach ($cats_db as $c) $categorias[$c['slug']] = $c;

$cat_atual = $slug_atual !== ''
    ? ($categorias[$slug_atual] ?? reset($categorias) ?: ['label' => 'Todos', 'icon' => 'grid', 'total' => 0, 'slug' => ''])
    : ['label' => 'Todas as empresas', 'icon' => 'grid', 'total' => 0, 'slug' => ''];

// ── Ordenação ────────────────────────────────────────────────────────────────
$order_sql = match($sort) {
    'avaliacao'  => 'l.rating DESC, l.total_reviews DESC',
    'novo'       => 'l.criado_em DESC',
    'preco-asc'  => 'l.preco_nivel ASC',
    'preco-desc' => 'l.preco_nivel DESC',
    default      => 'l.rating DESC',
};

// ── WHERE dinâmico ───────────────────────────────────────────────────────────
$where  = ['l.ativo = 1'];
$params = [];
if ($slug_atual !== '') { $where[] = 'c.slug = ?';                           $params[] = $slug_atual; }
if ($preco !== 'todos') { $where[] = 'l.preco_nivel = ?';                    $params[] = $preco; }
if ($q_busca !== '')    { $where[] = '(l.nome LIKE ? OR l.endereco LIKE ?)'; $like = "%$q_busca%"; $params[] = $like; $params[] = $like; }

// Destaque = premium primeiro, depois os demais
if ($sort === 'destaque') {
    $order_sql = "CASE WHEN l.plano = 'premium' THEN 0 ELSE 1 END, l.rating DESC";
}

$whereSQL = implode(' AND ', $where);

// ── Total ────────────────────────────────────────────────────────────────────
$total = (int) DB::row(
    "SELECT COUNT(*) n FROM lugares l JOIN categorias c ON c.id = l.categoria_id WHERE $whereSQL",
    $params
)['n'];

// ── Total e primeira página ──────────────────────────────────────────────────
$total = (int) DB::row(
    "SELECT COUNT(*) n FROM lugares l JOIN categorias c ON c.id = l.categoria_id WHERE $whereSQL",
    $params
)['n'];

$listings_db = DB::query(
    "SELECT l.id, l.slug, l.nome, l.cat_label, l.badge, l.plano,
            l.preco_nivel, l.preco_simbolo, l.endereco, l.rating, l.total_reviews,
            COALESCE(f.url, l.foto_principal) AS img, c.label AS cat_nome,
            CASE WHEN EXISTS(
                SELECT 1 FROM horarios h WHERE h.lugar_id = l.id
                AND h.dia_semana = DAYOFWEEK(NOW()) - 1 AND h.fechado = 0
                AND (h.dia_todo = 1 OR (h.hora_abre <= TIME(NOW())
                    AND (IF(h.hora_fecha = '00:00:00', '24:00:00', h.hora_fecha) >= TIME(NOW()))))
            ) THEN 1 ELSE 0 END AS aberto
     FROM lugares l
     JOIN categorias c ON c.id = l.categoria_id
     LEFT JOIN fotos f ON f.lugar_id = l.id AND f.principal = 1
     WHERE $whereSQL
     ORDER BY $order_sql
     LIMIT $per_page",
    $params
);

$listings = [];
foreach ($listings_db as $row) {
    $tags = DB::query(
        'SELECT t.label FROM lugar_tags lt JOIN tags t ON t.id = lt.tag_id WHERE lt.lugar_id = ? LIMIT 3',
        [$row['id']]
    );
    $row['tags']  = array_column($tags, 'label');
    $row['cat']   = $row['cat_label'] ?? $row['cat_nome'];
    $row['preco'] = $row['preco_simbolo'];
    $row['img']   = $row['img'] ?? '/assets/img/sem-imagem.png';
    $row['badge'] = ($row['plano'] === 'premium') ? 'Destaque' : ($row['badge'] ?: null);
    $listings[] = $row;
}

// ── Meta ─────────────────────────────────────────────────────────────────────
$page_title = ($cat_atual['label'] ?? 'Categoria') . ' em Campo Belo — Guia Campo Belo & Região';
$canonical  = 'https://guiacampobeloeregiao.com.br/pages/categoria?slug=' . urlencode($slug_atual);

function stars_cat(float $n): string {
    $f = (int) floor($n);
    return str_repeat('<span style="color:#c9aa6b">★</span>', $f)
         . str_repeat('<span style="color:rgba(139,133,137,.3)">★</span>', 5 - $f);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><?php include __DIR__ . '/../includes/head.php'; ?></head>
<body>
    
    <style>
/* Scrollbar webkit (Chrome/Safari) */
.cat-scroll::-webkit-scrollbar        { width: 4px; }
.cat-scroll::-webkit-scrollbar-track  { background: transparent; }
.cat-scroll::-webkit-scrollbar-thumb  { background: rgba(201,170,107,.4); border-radius: 99px; }
.cat-scroll::-webkit-scrollbar-thumb:hover { background: rgba(201,170,107,.75); }
@keyframes gcb-spin { to { transform: rotate(360deg); } }
</style>


<?php include __DIR__ . '/../includes/search-modal.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>


<!-- ════════════════════════════════════════════════════════════
     HERO
════════════════════════════════════════════════════════════ -->
<section style="background:var(--gcb-green-dark);padding-top:72px">
  <div class="container py-5">

    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb mb-0" style="font-size:11px">
        <li class="breadcrumb-item">
          <a href="/" style="color:rgba(255,255,255,.5)">Início</a>
        </li>
        <li class="breadcrumb-item active" style="color:rgba(255,255,255,.7)">
          <?= htmlspecialchars($cat_atual['label'] ?? '') ?>
        </li>
      </ol>
    </nav>

    <div class="row align-items-end g-4">
      <div class="col-12 col-md-8">
        <div class="d-flex align-items-center gap-4 mb-3">
          <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
               style="width:56px;height:56px;background:rgba(201,170,107,.15);
                      border:1px solid rgba(201,170,107,.2);color:var(--gcb-gold)">
            <?= icon($cat_atual['icon'] ?? 'grid', 26) ?>
          </div>
          <div>
            <p class="eyebrow mb-1">Categoria</p>
            <h1 class="font-display fw-bold text-white mb-0"
                style="font-size:clamp(28px,4.5vw,48px)">
              <?= htmlspecialchars($cat_atual['label'] ?? '') ?>
            </h1>
          </div>
        </div>
        <p style="font-size:14px;font-weight:300;color:rgba(255,255,255,.5)">
          <?= $total ?> estabelecimentos curados em Campo Belo e região.
        </p>
      </div>

      <div class="col-12 col-md-4">
        <div class="d-flex align-items-center gap-2 rounded-pill px-3 py-2"
             style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12)">
          <span style="color:var(--gcb-gold)"><?= icon('search', 16) ?></span>
          <input type="text" id="search-inline"
                 placeholder="Buscar em <?= htmlspecialchars($cat_atual['label'] ?? '') ?>…"
                 value="<?= htmlspecialchars($q_busca) ?>"
                 oninput="handleSearch(this.value)"
                 class="border-0 bg-transparent text-white flex-fill"
                 style="font-size:13px;outline:none"/>
        </div>
      </div>
    </div>

  </div>
</section>


<!-- ════════════════════════════════════════════════════════════
     TOOLBAR
════════════════════════════════════════════════════════════ -->
<div class="bg-white border-bottom sticky-top"
     style="z-index:100;border-color:rgba(61,71,51,.08)!important;top:0">
  <div class="container">
    <div class="d-flex align-items-center gap-3 py-2 flex-wrap">

      <p class="mb-0" style="font-size:12px;font-weight:600;color:var(--gcb-warmgray)">
        <span id="results-count" class="fw-black" style="color:var(--gcb-graphite)">
          <?= $total ?>
        </span> resultado<?= $total !== 1 ? 's' : '' ?>
      </p>

      <div class="ms-auto d-flex align-items-center gap-2">

        <!-- Ordenação -->
        <div class="dropdown">
          <button class="btn btn-sm bg-white border rounded-pill d-flex align-items-center gap-2"
                  style="font-size:11.5px;font-weight:600;border-color:rgba(61,71,51,.1)!important"
                  data-bs-toggle="dropdown">
            <?= icon('sliders', 14) ?>
            <span id="sort-label">
              <?= ['destaque'=>'Destaques','avaliacao'=>'Melhor avaliados','novo'=>'Mais recentes',
                   'preco-asc'=>'Menor preço','preco-desc'=>'Maior preço'][$sort] ?? 'Destaques' ?>
            </span>
            <?= icon('chevron-down', 12) ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end rounded-3 border-0 shadow">
            <?php foreach ([
                'destaque'   => 'Destaques',
                'avaliacao'  => 'Melhor avaliados',
                'novo'       => 'Mais recentes',
                'preco-asc'  => 'Menor preço',
                'preco-desc' => 'Maior preço',
            ] as $val => $lbl): ?>
            <li>
              <button class="dropdown-item sort-opt <?= $val === $sort ? 'fw-bold text-gold' : '' ?>"
                      onclick="setSort('<?= $val ?>', '<?= $lbl ?>')"
                      style="font-size:13px">
                <?= $lbl ?>
              </button>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Filtros mobile -->
        <button class="btn btn-sm bg-white border rounded-pill d-lg-none d-flex align-items-center gap-2"
                style="font-size:11.5px;font-weight:600;border-color:rgba(61,71,51,.1)!important"
                onclick="toggleMobileSidebar()">
          <?= icon('filter', 14) ?> Filtros
        </button>

      </div>
    </div>
  </div>
</div>


<!-- ════════════════════════════════════════════════════════════
     SIDEBAR MOBILE (overlay)
════════════════════════════════════════════════════════════ -->
<div id="sidebar-mobile"
     class="d-none position-fixed top-0 start-0 w-100 h-100 d-lg-none"
     style="z-index:300;background:rgba(29,29,27,.5);backdrop-filter:blur(4px)"
     onclick="toggleMobileSidebar()">
  <div class="position-absolute bottom-0 start-0 end-0 bg-white rounded-top p-4"
       style="max-height:80vh;overflow-y:auto"
       onclick="event.stopPropagation()">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h5 class="font-display fw-bold mb-0" style="color:var(--gcb-green-dark)">Filtros</h5>
      <button class="modal-close-btn" onclick="toggleMobileSidebar()">
        <?= icon('close', 15) ?>
      </button>
    </div>
    <?php include '_sidebar_content.php' ?>
  </div>
</div>


<!-- ════════════════════════════════════════════════════════════
     LAYOUT PRINCIPAL
════════════════════════════════════════════════════════════ -->
<div class="container py-5">
  <div class="row g-4">

    <!-- ── Sidebar desktop ─────────────────────────────────── -->
    <div class="col-lg-3 d-none d-lg-block">
      <div class="sticky-top" style="top:80px">

        <!-- Categorias -->
        <div class="bg-white rounded-20 p-4 mb-4 shadow-card">
          <p class="eyebrow">Categorias</p>
          <div class="d-flex flex-column gap-1" style="max-height:580px;overflow-y:auto;scrollbar-width:thin;scrollbar-color:rgba(201,170,107,.4) transparent">
            <?php foreach ($categorias as $slug => $cat): ?>
            <a href="/pages/categoria?slug=<?= $slug ?>"
               class="d-flex align-items-center justify-content-between px-3 py-2
                      rounded-3 text-decoration-none transition"
               style="font-size:13px;
                      <?= $slug === $slug_atual
                        ? 'background:var(--gcb-gold-pale);color:var(--gcb-green-dark);font-weight:700;border:1px solid rgba(201,170,107,.3)'
                        : 'color:rgba(29,29,27,.7);border:1px solid transparent' ?>">
              <div class="d-flex align-items-center gap-2">
                <span style="color:<?= $slug === $slug_atual ? 'var(--gcb-green)' : 'var(--gcb-warmgray)' ?>">
                  <?= icon($cat['icon'] ?? 'grid', 15) ?>
                </span>
                <?= htmlspecialchars($cat['label']) ?>
              </div>
              <span style="font-size:11px;font-weight:700;
                           color:<?= $slug === $slug_atual ? 'var(--gcb-gold)' : 'rgba(139,133,137,.6)' ?>">
                <?= $cat['total'] ?>
              </span>
            </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Faixa de preço -->
        <div class="bg-white d-none rounded-20 p-4 shadow-card">
          <p class="eyebrow">Faixa de Preço</p>
          <div class="d-flex flex-column gap-2">
            <?php foreach ($faixas as $val => $lbl): ?>
            <button onclick="setPreco('<?= $val ?>', this)"
                    data-price="<?= $val ?>"
                    class="price-btn d-flex align-items-center justify-content-between
                           px-3 py-2 rounded-3 border text-start w-100 bg-white"
                    style="font-size:13px;font-weight:500;cursor:pointer;transition:all .2s;
                           border-color:rgba(61,71,51,.1)!important;color:rgba(29,29,27,.7);
                           <?= $val === $preco
                             ? 'background:var(--gcb-green-dark)!important;color:var(--gcb-cream)!important;border-color:var(--gcb-green-dark)!important'
                             : '' ?>">
              <span><?= htmlspecialchars($lbl) ?></span>
              <?php if ($val === $preco): ?>
              <span style="color:var(--gcb-gold)"><?= icon('verified', 13) ?></span>
              <?php endif; ?>
            </button>
            <?php endforeach; ?>
          </div>
        </div>

      </div>
    </div>


    <!-- ── Área de cards ───────────────────────────────────── -->
    <div class="col-lg-9" id="cards-area">

      <!-- Sem resultados -->
      <div id="no-results"
           class="d-none flex-column align-items-center justify-content-center py-5 text-center">
        <div class="rounded-3 d-flex align-items-center justify-content-center mb-3"
             style="width:60px;height:60px;background:var(--gcb-offwhite);color:var(--gcb-warmgray)">
          <?= icon('search', 28) ?>
        </div>
        <h3 class="font-display fw-bold mb-2" style="color:var(--gcb-green-dark)">
          Nenhum resultado
        </h3>
        <p style="font-size:14px;color:var(--gcb-warmgray);max-width:300px">
          Tente outros termos ou remova os filtros.
        </p>
        <button onclick="clearFilters()" class="btn-gold mt-3">Limpar filtros</button>
      </div>

      <!-- Grid de cards -->
      <div class="row g-4" id="cards-grid">
        <?php if (empty($listings)): ?>
        <div class="col-12 text-center py-5">
          <p style="color:var(--gcb-warmgray)">
            Nenhum lugar encontrado nesta categoria ainda.
          </p>
        </div>
        <?php else: ?>
        <?php foreach ($listings as $item): ?>
        <div class="col-12 col-sm-6 col-xl-4">
          <article class="gcb-card listing-card h-100">
            <div class="card-img-wrap position-relative">
              <a href="/pages/lugar/<?= htmlspecialchars($item['slug']) ?>">
                <img src="<?= htmlspecialchars($item['img']) ?>"
                     alt="<?= htmlspecialchars($item['nome']) ?>"
                     class="card-img-top" loading="lazy"/>
              </a>
              <div class="position-absolute top-0 start-0 end-0 d-flex justify-content-between p-3">
                <?php if ($item['badge']): ?>
                <span class="<?= strtolower($item['badge']) === 'novo' ? 'badge-green' : 'badge-gold' ?>">
                  <?= htmlspecialchars($item['badge']) ?>
                </span>
                <?php else: ?><span></span><?php endif; ?>
              </div>
              <div class="position-absolute bottom-0 start-0 m-3">
                <span class="d-flex align-items-center gap-2 px-2 py-1 rounded-pill"
                      style="background:rgba(29,29,27,.55);font-size:11px;font-weight:600;
                             color:<?= $item['aberto'] ? '#34d399' : '#f87171' ?>">
                  <span class="rounded-circle"
                        style="width:6px;height:6px;display:inline-block;
                               background:<?= $item['aberto'] ? '#34d399' : '#f87171' ?>"></span>
                  <?= $item['aberto'] ? 'Aberto' : 'Fechado' ?>
                </span>
              </div>
            </div>
            <div class="card-body">
              <p class="card-cat"><?= htmlspecialchars($item['cat']) ?></p>
              <h2 class="card-title"><?= htmlspecialchars($item['nome']) ?></h2>
              <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                <span class="stars"><?= stars_cat($item['rating']) ?></span>
                <span style="font-size:13px;font-weight:700;color:var(--gcb-graphite)">
                  <?= number_format($item['rating'], 1) ?>
                </span>
                <span style="font-size:12px;color:var(--gcb-warmgray)">
                  (<?= (int) $item['total_reviews'] ?>)
                </span>
                <span style="font-size:10px;color:var(--gcb-warmgray)">&middot;</span>
                <span style="font-size:13px;font-weight:700;color:var(--gcb-green)">
                  <?= htmlspecialchars($item['preco']) ?>
                </span>
              </div>
              <div class="d-flex flex-wrap gap-2">
                <?php foreach ($item['tags'] as $tag): ?>
                <span class="tag-pill"><?= htmlspecialchars($tag) ?></span>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="card-footer-row px-4 pb-4">
              <span class="d-flex align-items-center gap-1"
                    style="font-size:12px;color:var(--gcb-warmgray)">
                <?= icon('pin', 11) ?> <?= htmlspecialchars($item['endereco']) ?>
              </span>
              <a href="/pages/lugar/<?= htmlspecialchars($item['slug']) ?>"
                 style="font-size:11px;font-weight:800;letter-spacing:.06em;
                        text-transform:uppercase;color:var(--gcb-gold)">
                Ver mais <?= icon('arrow-right', 11) ?>
              </a>
            </div>
          </article>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div><!-- /cards-grid -->


      <!-- ── Load More ─────────────────────────────────────── -->
      <?php if ($total > $per_page): ?>
      <div id="load-more-wrap"
           class="d-flex flex-column align-items-center gap-3 py-5 mt-2">

        <!-- Spinner -->
        <div id="cards-spinner" class="d-none">
          <div class="d-flex align-items-center gap-2"
               style="color:var(--gcb-warmgray);font-size:13px;font-weight:600">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2"
                 style="animation:gcb-spin .8s linear infinite">
              <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83
                       M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
            </svg>
            Carregando...
          </div>
        </div>

        <!-- Botão -->
        <button id="btn-load-more"
                class="btn-outline-green d-flex align-items-center gap-2">
          <?= icon('arrow-down', 14) ?>
          <span>Carregar mais</span>
          <span id="remaining-count"
                style="font-size:11px;opacity:.55">
            (<?= $total - $per_page ?> restantes)
          </span>
        </button>

      </div>
      <?php endif; ?>

      <!-- Fim dos resultados -->
      <div id="end-of-results" class="d-none text-center py-5"
           style="color:var(--gcb-warmgray);font-size:13px;font-weight:600">
        <?= icon('verified', 16) ?> Todos os resultados exibidos
      </div>

    </div><!-- /col cards-area -->
  </div><!-- /row -->
</div><!-- /container -->


<?php include __DIR__ . '/../includes/footer.php'; ?>


<!-- ════════════════════════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════════════════════════ -->

<script>
(function () {
  // ── Estado ──────────────────────────────────────────────────
  const STATE = {
    offset:  <?= $per_page ?>,
    total:   <?= $total ?>,
    loading: false,
    slug:    '<?= addslashes($slug_atual) ?>',
    preco:   '<?= addslashes($preco) ?>',
    sort:    '<?= addslashes($sort) ?>',
    q:       '<?= addslashes($q_busca) ?>',
  };

  // ── Referências DOM ─────────────────────────────────────────
  const grid      = document.getElementById('cards-grid');
  const btnWrap   = document.getElementById('load-more-wrap');
  const btn       = document.getElementById('btn-load-more');
  const spinner   = document.getElementById('cards-spinner');
  const endMsg    = document.getElementById('end-of-results');
  const remaining = document.getElementById('remaining-count');

  // ── Monta card HTML ─────────────────────────────────────────
  function buildCard(item) {
    const badge     = item.plano === 'premium' ? 'Destaque' : (item.badge || null);
    const badgeCls  = badge && badge.toLowerCase() === 'novo' ? 'badge-green' : 'badge-gold';
    const badgeHtml = badge ? `<span class="${badgeCls}">${badge}</span>` : '<span></span>';
    const statusClr = item.aberto_agora ? '#34d399' : '#f87171';
    const statusTxt = item.aberto_agora ? 'Aberto' : 'Fechado';
    const stars     = '★'.repeat(Math.min(5, Math.round(item.rating || 0)));
    const tagsHtml  = (item.tags || []).slice(0, 3)
      .map(t => `<span class="tag-pill">${t}</span>`).join('');

    return `
      <div class="col-12 col-sm-6 col-xl-4"
           style="opacity:0;transform:translateY(14px);
                  transition:opacity .35s ease,transform .35s ease">
        <article class="gcb-card listing-card h-100">
          <div class="card-img-wrap position-relative">
            <a href="/pages/lugar/${item.slug}">
              <img src="${item.foto_capa || '/assets/img/sem-imagem.png'}"
                   alt="${item.nome}" class="card-img-top" loading="lazy"/>
            </a>
            <div class="position-absolute top-0 start-0 end-0
                        d-flex justify-content-between p-3">
              ${badgeHtml}
            </div>
            <div class="position-absolute bottom-0 start-0 m-3">
              <span class="d-flex align-items-center gap-2 px-2 py-1 rounded-pill"
                    style="background:rgba(29,29,27,.55);font-size:11px;
                           font-weight:600;color:${statusClr}">
                <span class="rounded-circle"
                      style="width:6px;height:6px;display:inline-block;
                             background:${statusClr}"></span>
                ${statusTxt}
              </span>
            </div>
          </div>
          <div class="card-body">
            <p class="card-cat">${item.cat_label || item.cat_nome}</p>
            <h2 class="card-title">${item.nome}</h2>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
              <span class="stars" style="color:#c9aa6b">${stars}</span>
              <span style="font-size:13px;font-weight:700;color:var(--gcb-graphite)">
                ${Number(item.rating || 0).toFixed(1)}
              </span>
              <span style="font-size:12px;color:var(--gcb-warmgray)">
                (${item.total_reviews})
              </span>
              <span style="font-size:10px;color:var(--gcb-warmgray)">&middot;</span>
              <span style="font-size:13px;font-weight:700;color:var(--gcb-green)">
                ${item.preco_simbolo || ''}
              </span>
            </div>
            <div class="d-flex flex-wrap gap-2">${tagsHtml}</div>
          </div>
          <div class="card-footer-row px-4 pb-4">
            <span class="d-flex align-items-center gap-1"
                  style="font-size:12px;color:var(--gcb-warmgray)">
              ${item.endereco || ''}
            </span>
            <a href="/pages/lugar/${item.slug}"
               style="font-size:11px;font-weight:800;letter-spacing:.06em;
                      text-transform:uppercase;color:var(--gcb-gold);text-decoration:none">
              Ver mais →
            </a>
          </div>
        </article>
      </div>`;
  }

  // ── Carrega mais via API ─────────────────────────────────────
  async function loadMore() {
    if (STATE.loading || STATE.offset >= STATE.total) return;
    STATE.loading = true;

    if (btn)     btn.classList.add('d-none');
    if (spinner) spinner.classList.remove('d-none');

    try {
      const qs = new URLSearchParams({
        cat:    STATE.slug,
        preco:  STATE.preco,
        sort:   STATE.sort,
        q:      STATE.q,
        limit:  12,
        offset: STATE.offset,
      });

      const res  = await fetch('/api/lugares?' + qs.toString());
      const data = await res.json();

      if (data.ok && data.dados.length > 0) {
        data.dados.forEach((item, i) => {
          const wrapper = document.createElement('div');
          wrapper.innerHTML = buildCard(item).trim();
          const col = wrapper.firstElementChild;
          grid.appendChild(col);

          // Animação de entrada escalonada
          requestAnimationFrame(() => setTimeout(() => {
            col.style.opacity   = '1';
            col.style.transform = 'translateY(0)';
          }, i * 60));
        });

        STATE.offset += data.dados.length;

        const rest = STATE.total - STATE.offset;

        if (STATE.offset >= STATE.total) {
          if (btnWrap) btnWrap.classList.add('d-none');
          if (endMsg)  endMsg.classList.remove('d-none');
        } else {
          if (remaining) remaining.textContent = `(${rest} restantes)`;
          if (spinner)   spinner.classList.add('d-none');
          if (btn)       btn.classList.remove('d-none');
        }
      }
    } catch (err) {
      console.error('[GCB] Erro ao carregar mais:', err);
      if (spinner) spinner.classList.add('d-none');
      if (btn)     btn.classList.remove('d-none');
    }

    STATE.loading = false;
  }

  // ── Clique no botão ─────────────────────────────────────────
  btn?.addEventListener('click', loadMore);

  // ── Infinite scroll — só ativa após o usuário rolar ─────────
  let scrolled = false;

  window.addEventListener('scroll', function onFirstScroll() {
    scrolled = true;
    window.removeEventListener('scroll', onFirstScroll);

    if (btnWrap) {
      new IntersectionObserver(
        entries => { if (entries[0].isIntersecting) loadMore(); },
        { rootMargin: '300px' }
      ).observe(btnWrap);
    }
  }, { passive: true });

  // ── Filtro de preço ─────────────────────────────────────────
  window.setPreco = function (val, btn) {
    const url = new URL(window.location.href);
    url.searchParams.set('preco', val);
    url.searchParams.delete('page');
    window.location.href = url.toString();
  };

  // ── Ordenação ───────────────────────────────────────────────
  window.setSort = function (val, label) {
    document.getElementById('sort-label').textContent = label;
    const url = new URL(window.location.href);
    url.searchParams.set('sort', val);
    window.location.href = url.toString();
  };

  // ── Busca inline (debounce 400ms) ───────────────────────────
  let searchTimer;
  window.handleSearch = function (q) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      const url = new URL(window.location.href);
      if (q.trim()) url.searchParams.set('q', q.trim());
      else          url.searchParams.delete('q');
      window.location.href = url.toString();
    }, 400);
  };

  // ── Limpar filtros ──────────────────────────────────────────
  window.clearFilters = function () {
    const url = new URL(window.location.href);
    url.searchParams.delete('preco');
    url.searchParams.delete('q');
    url.searchParams.delete('sort');
    window.location.href = url.toString();
  };

  // ── Sidebar mobile ──────────────────────────────────────────
  window.toggleMobileSidebar = function () {
    document.getElementById('sidebar-mobile')?.classList.toggle('d-none');
  };

})();
</script>

</body>
</html>