<?php
require_once __DIR__ . '/../core/UserAuth.php';
require_once __DIR__ . '/../includes/icons.php';
require_once __DIR__ . '/../core/DB.php';
UserAuth::start();

$slug = trim($_GET['slug'] ?? '');
$id   = (int)($_GET['id'] ?? 0);
if (!$slug && !$id) { header('Location: /index.php'); exit; }

$col   = $slug ? 'l.slug' : 'l.id';
$param = $slug ?: $id;

$lugar_db = DB::row(
    "SELECT l.*, c.slug AS categoria_slug, c.label AS cat_nome,
     CASE WHEN EXISTS(SELECT 1 FROM horarios h WHERE h.lugar_id=l.id
       AND h.dia_semana=DAYOFWEEK(NOW())-1 AND h.fechado=0
       AND(h.dia_todo=1 OR(h.hora_abre<=TIME(NOW()) AND (IF(h.hora_fecha='00:00:00','24:00:00',h.hora_fecha)>=TIME(NOW()))))
     ) THEN 1 ELSE 0 END AS aberto_agora
     FROM lugares l JOIN categorias c ON c.id=l.categoria_id
     WHERE $col=? AND l.ativo=1 LIMIT 1", [$param]);

if (!$lugar_db) { http_response_code(404); echo '<!DOCTYPE html><html><body><h1>404 — Lugar não encontrado</h1></body></html>'; exit; }

$lid = (int)$lugar_db['id'];

$fotos_db = DB::query('SELECT url,alt,principal FROM fotos WHERE lugar_id=? ORDER BY principal DESC,ordem ASC',[$lid]);
$galeria  = array_column($fotos_db,'url');
$hero_img = $galeria[0] ?? $lugar_db['foto_principal'] ?? '/assets/img/sem-imagem.png';

$dias_nomes = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
$dia_atual  = $dias_nomes[date('w')];
$horarios   = [];
foreach (DB::query('SELECT * FROM horarios WHERE lugar_id=? ORDER BY dia_semana',[$lid]) as $h) {
    $horarios[$dias_nomes[$h['dia_semana']]] = $h['fechado'] ? 'Fechado'
        : ($h['dia_todo'] ? 'Aberto o dia todo' : substr($h['hora_abre'],0,5).'h – '.substr($h['hora_fecha'],0,5).'h');
}

$servicos = array_column(DB::query('SELECT s.nome FROM lugar_servicos ls JOIN servicos s ON s.id=ls.servico_id WHERE ls.lugar_id=?',[$lid]),'nome');
$tags     = array_column(DB::query('SELECT t.label FROM lugar_tags lt JOIN tags t ON t.id=lt.tag_id WHERE lt.lugar_id=?',[$lid]),'label');

$avaliacoes_db = DB::query('SELECT autor_nome,autor_foto,nota,texto,data_avaliacao,fonte FROM avaliacoes WHERE lugar_id=? AND aprovado=1 ORDER BY data_avaliacao DESC LIMIT 10',[$lid]);
$avaliacoes = [];
foreach ($avaliacoes_db as $av) {
    $avaliacoes[] = [
        'avatar' => mb_substr($av['autor_nome']??'?',0,1),
        'nome'   => $av['autor_nome']??'Anônimo',
        'bairro' => $av['fonte']==='google' ? 'Google' : ($av['fonte']==='tripadvisor' ? 'TripAdvisor' : 'Visitante'),
        'nota'   => (float)$av['nota'],
        'data'   => $av['data_avaliacao'] ? date('d M Y',strtotime($av['data_avaliacao'])) : '',
        'texto'  => $av['texto']??'',
    ];
}

$similares_db = DB::query(
    "SELECT l.id,l.slug,l.nome,l.cat_label,l.rating,l.total_reviews,l.endereco,
            c.label AS cat_nome, COALESCE(f.url,l.foto_principal) AS img
     FROM lugares l JOIN categorias c ON c.id=l.categoria_id
     LEFT JOIN fotos f ON f.lugar_id=l.id AND f.principal=1
     WHERE l.categoria_id=? AND l.id<>? AND l.ativo=1 ORDER BY l.rating DESC LIMIT 3",
    [$lugar_db['categoria_id'],$lid]);
$similares = [];
foreach ($similares_db as $s) {
    $similares[] = ['id'=>$s['id'],'slug'=>$s['slug'],'nome'=>$s['nome'],
        'cat'=>$s['cat_label']??$s['cat_nome'],'rating'=>(float)$s['rating'],
        'reviews'=>(int)$s['total_reviews'],'endereco'=>$s['endereco'],
        'img'=>$s['img']??'/assets/img/sem-imagem.png'];
}

$lugar = [
    'id'=>$lid,'nome'=>$lugar_db['nome'],
    'categoria'=>$lugar_db['cat_label']??$lugar_db['cat_nome'],
    'categoria_slug'=>$lugar_db['categoria_slug'],
    'badge'=>$lugar_db['badge']??'Curadoria Guia',
    'descricao'=>$lugar_db['descricao']??'',
    'descricao_extra'=>$lugar_db['descricao_extra']??'',
    'rating'=>(float)($lugar_db['rating']??0),
    'total_reviews'=>(int)($lugar_db['total_reviews']??0),
    'preco_range'=>$lugar_db['preco_range']??'',
    'endereco'=>$lugar_db['endereco']??'',
    'telefone'=>$lugar_db['telefone']??'',
    'email'=>$lugar_db['email']??'',
    'site'=>$lugar_db['site']??'',
    'instagram'=>$lugar_db['instagram']??'',
    'whatsapp'=>$lugar_db['whatsapp']??'',
    'aberto_agora'=>(bool)$lugar_db['aberto_agora'],
    'horarios'=>$horarios,'dia_atual'=>$dia_atual,
    'servicos'=>$servicos,'hero_img'=>$hero_img,
    'galeria'=>$galeria?:[$hero_img],
    'avaliacoes'=>$avaliacoes,'similares'=>$similares,
];

$page_title = $lugar['nome'].' — Guia Campo Belo & Região';
$canonical  = 'https://guiacampobeloeregiao.com.br';

function stars_full(float $n): string {
    $full=(int)floor($n); $half=($n-$full)>=0.5?1:0; $empty=5-$full-$half; $s='';
    for($i=0;$i<$full;$i++)  $s.='<span style="color:#c9aa6b">★</span>';
    if($half)                 $s.='<span style="color:#c9aa6b">½</span>';
    for($i=0;$i<$empty;$i++) $s.='<span style="color:rgba(139,133,137,.35)">★</span>';
    return $s;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><?php include __DIR__ . '/../includes/head.php'; ?>
<style>
  /* Lightbox */
  #lightbox { display:none; position:fixed; inset:0; z-index:1060; align-items:center; justify-content:center; background:rgba(29,29,27,.92); backdrop-filter:blur(12px); }
  #lightbox.open { display:flex; }
  /* Tabs */
  .gcb-tab-nav { border-bottom: 1px solid rgba(255,255,255,.1); background: rgba(42,48,34,.8); backdrop-filter: blur(12px); }
  .gcb-tab-btn { padding:16px 24px; font-size:11px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:rgba(255,255,255,.5); border:none; background:transparent; border-bottom:2px solid transparent; transition:all .2s; cursor:pointer; white-space:nowrap; }
  .gcb-tab-btn:hover { color:#fff; }
  .gcb-tab-btn.active { color:#fff; border-bottom-color:var(--gcb-gold); }
  .gcb-tab-pane { display:none; }
  .gcb-tab-pane.active { display:block; }
  /* Hero */
  .lugar-hero { position:relative; height:520px; overflow:hidden; }
  .lugar-hero-bg { position:absolute; inset:0; transform:scale(1.06); will-change:transform; }
  .lugar-hero-overlay { position:absolute; inset:0; background:linear-gradient(180deg,rgba(42,48,34,.45) 0%,rgba(42,48,34,.72) 70%,rgba(42,48,34,.92) 100%); }
  /* Rating bar */
  .rating-bar-fill { transition:width 1s cubic-bezier(.4,0,.2,1); }
</style>
</head>
<body>

<?php include __DIR__ . '/../includes/search-modal.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Lightbox -->
<div id="lightbox">
  <button onclick="closeLightbox()" class="position-absolute top-0 end-0 m-4 border-0 bg-white bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center text-white" style="width:40px;height:40px"><?= icon('close',18) ?></button>
  <button onclick="prevImg()" class="position-absolute start-0 top-50 translate-middle-y ms-3 border-0 rounded-circle d-flex align-items-center justify-content-center text-white" style="width:48px;height:48px;background:rgba(255,255,255,.1)"><?= icon('arrow-left',18) ?></button>
  <button onclick="nextImg()" class="position-absolute end-0 top-50 translate-middle-y me-3 border-0 rounded-circle d-flex align-items-center justify-content-center text-white" style="width:48px;height:48px;background:rgba(255,255,255,.1)"><?= icon('arrow-right',18) ?></button>
  <img id="lightbox-img" src="" alt="" style="max-width:90vw;max-height:85vh;object-fit:contain;border-radius:12px"/>
  <p id="lightbox-count" class="position-absolute bottom-0 start-50 translate-middle-x mb-4 text-white text-opacity-50" style="font-size:13px"></p>
</div>

<!-- HERO -->
<section class="lugar-hero" id="hero">
  <div class="lugar-hero-bg" id="hero-bg" style="background:url('<?= htmlspecialchars($lugar['hero_img']) ?>') center/cover no-repeat"></div>
  <div class="lugar-hero-overlay"></div>
  <div class="position-relative h-100 d-flex flex-column justify-content-end" style="z-index:2">
    <div class="container pb-0">
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size:11px">
          <li class="breadcrumb-item"><a href="/index.php" style="color:rgba(255,255,255,.5)">Início</a></li>
          <li class="breadcrumb-item"><a href="/pages/categoria.php?slug=<?= htmlspecialchars($lugar['categoria_slug']) ?>" style="color:rgba(255,255,255,.5)"><?= htmlspecialchars($lugar['categoria_slug']) ?></a></li>
          <li class="breadcrumb-item active" style="color:rgba(255,255,255,.7)"><?= htmlspecialchars($lugar['nome']) ?></li>
        </ol>
      </nav>
      <div class="d-flex align-items-end justify-content-between gap-4 flex-wrap pb-3">
        <div>
          <div class="d-flex align-items-center gap-3 mb-2">
            <span style="font-size:9px;font-weight:800;letter-spacing:.2em;text-transform:uppercase;color:var(--gcb-gold)"><?= htmlspecialchars($lugar['categoria']) ?></span>
            <span class="badge-gold d-inline-flex align-items-center gap-1"><?= icon('verified',11) ?> <?= htmlspecialchars($lugar['badge']) ?></span>
          </div>
          <h1 class="font-display fw-bold text-white mb-3" style="font-size:clamp(32px,5vw,56px);line-height:1.1"><?= htmlspecialchars($lugar['nome']) ?></h1>
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2">
              <span class="stars" style="font-size:15px"><?= stars_full($lugar['rating']) ?></span>
              <span class="text-white fw-bold"><?= number_format($lugar['rating'],1) ?></span>
              <span style="color:rgba(255,255,255,.5);font-size:13px">(<?= $lugar['total_reviews'] ?> avaliações)</span>
            </div>
            <span style="color:rgba(255,255,255,.25)">|</span>
            <span class="d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;color:<?= $lugar['aberto_agora']?'#34d399':'#f87171' ?>">
              <span class="rounded-circle" style="width:8px;height:8px;background:<?= $lugar['aberto_agora']?'#34d399':'#f87171' ?>;display:inline-block"></span>
              <?= $lugar['aberto_agora']?'Aberto agora':'Fechado agora' ?>
            </span>
            <span style="color:rgba(255,255,255,.25)">|</span>
            <span class="d-flex align-items-center gap-1" style="color:rgba(255,255,255,.6);font-size:13px"><?= icon('pin',13) ?> <?= htmlspecialchars($lugar['endereco']) ?></span>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <button onclick="openGallery(0)" class="btn d-flex align-items-center gap-2" style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase">
            <?= icon('grid',13) ?> Ver fotos
          </button>
          <button id="fav-btn" onclick="toggleFav(this)" class="btn d-flex align-items-center gap-2" style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase">
            <?= icon('heart',13) ?> <span id="fav-label">Salvar</span>
          </button>
          <a href="https://maps.google.com/?q=<?= urlencode($lugar['endereco']) ?>" target="_blank" class="btn-gold d-inline-flex align-items-center gap-2" style="font-size:11px">
            <?= icon('navigation',13) ?> Como chegar
          </a>
        </div>
      </div>
    </div>
    <!-- Tabs -->
    <div class="gcb-tab-nav mt-3">
      <div class="container">
        <div class="d-flex overflow-x-auto scrollbar-hide">
          <?php foreach (['Sobre','Fotos','Avaliações','Localização'] as $i=>$tab): ?>
          <button class="gcb-tab-btn <?= $i===0?'active':'' ?>" data-tab="tab-<?= $i ?>" onclick="switchTab(this)">
            <?= htmlspecialchars($tab) ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CONTENT -->
<div class="container py-5">
  <div class="row g-4">

    <!-- Left -->
    <div class="col-lg-8">

      <!-- Tab 0: Sobre -->
      <div id="tab-0" class="gcb-tab-pane active">
        <div class="bg-white rounded-20 p-4 mb-4 shadow-card reveal">
          <h2 class="font-display fw-bold mb-4" style="color:var(--gcb-green-dark);font-size:22px">Sobre o <?= htmlspecialchars($lugar['nome']) ?></h2>
          <p style="font-size:15px;font-weight:300;color:rgba(29,29,27,.75);line-height:1.8" id="desc-short"><?= htmlspecialchars($lugar['descricao']) ?></p>
          <?php if ($lugar['descricao_extra']): ?>
          <p style="font-size:15px;font-weight:300;color:rgba(29,29,27,.75);line-height:1.8;display:none" id="desc-full"><?= htmlspecialchars($lugar['descricao_extra']) ?></p>
          <button onclick="toggleDesc()" id="desc-btn" class="btn p-0 d-flex align-items-center gap-2 mt-2" style="font-size:12px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--gcb-gold)">
            Ver mais <?= icon('chevron-down',13) ?>
          </button>
          <?php endif; ?>
        </div>

        <!-- Serviços -->
        <?php if (!empty($lugar['servicos'])): ?>
        <div class="bg-white rounded-20 p-4 mb-4 shadow-card reveal">
          <p class="eyebrow">Serviços &amp; Comodidades</p>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach ($lugar['servicos'] as $s): ?>
            <span class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill" style="background:var(--gcb-offwhite);border:1px solid rgba(61,71,51,.08);font-size:12px;font-weight:600;color:var(--gcb-green)">
              <?= icon('verified',12) ?> <?= htmlspecialchars($s) ?>
            </span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Tags -->
        <?php if (!empty($tags)): ?>
        <div class="bg-white rounded-20 p-4 mb-4 shadow-card reveal">
          <p class="eyebrow">Tags</p>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach ($tags as $t): ?>
            <span class="tag-pill"><?= htmlspecialchars($t) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Galeria preview -->
        <div class="bg-white rounded-20 p-4 shadow-card reveal">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <p class="eyebrow mb-0">Fotos</p>
            <button onclick="openGallery(0)" class="btn p-0 d-flex align-items-center gap-1" style="font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--gcb-green)">
              Ver todas <?= icon('arrow-right',12) ?>
            </button>
          </div>
          <div class="row g-2">
            <?php foreach (array_slice($lugar['galeria'],0,3) as $i=>$img): ?>
            <div class="col-4">
              <div class="position-relative rounded-3 overflow-hidden" style="aspect-ratio:1;cursor:pointer" onclick="openGallery(<?= $i ?>)">
                <img src="<?= htmlspecialchars($img) ?>" loading="lazy" class="w-100 h-100 object-fit-cover" style="transition:transform .5s ease"/>
                <?php if ($i===2 && count($lugar['galeria'])>3): ?>
                <div class="position-absolute inset-0 d-flex align-items-center justify-content-center" style="inset:0;background:rgba(42,48,34,.55)">
                  <span class="font-display fw-bold text-white" style="font-size:24px">+<?= count($lugar['galeria'])-3 ?></span>
                </div>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Tab 1: Fotos -->
      <div id="tab-1" class="gcb-tab-pane">
        <div class="bg-white rounded-20 p-4 shadow-card">
          <h2 class="font-display fw-bold mb-4" style="color:var(--gcb-green-dark);font-size:22px">Galeria de Fotos</h2>
          <div class="row g-3">
            <?php foreach ($lugar['galeria'] as $i=>$img): ?>
            <div class="col-6 col-md-4">
              <div class="rounded-3 overflow-hidden" style="aspect-ratio:4/3;cursor:pointer" onclick="openGallery(<?= $i ?>)">
                <img src="<?= htmlspecialchars($img) ?>" loading="lazy" class="w-100 h-100 object-fit-cover" style="transition:transform .5s ease"/>
              </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($lugar['galeria'])): ?>
            <div class="col-12 text-center py-5" style="color:var(--gcb-warmgray)">Nenhuma foto cadastrada.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Tab 2: Avaliações -->
      <div id="tab-2" class="gcb-tab-pane">
        <!-- Score overview -->
        <div class="bg-white rounded-20 p-4 mb-4 shadow-card">
          <div class="d-flex flex-column flex-sm-row align-items-center gap-4">
            <div class="d-flex flex-column align-items-center justify-content-center rounded-3 flex-shrink-0 text-center"
                 style="width:144px;height:144px;background:var(--gcb-green-dark);box-shadow:0 8px 32px rgba(42,48,34,.18)">
              <span class="font-display fw-bold text-white" style="font-size:48px;line-height:1"><?= number_format($lugar['rating'],1) ?></span>
              <div class="mt-2 stars" style="font-size:14px"><?= stars_full($lugar['rating']) ?></div>
              <span style="font-size:9px;font-weight:800;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.35);margin-top:6px">de 5.0</span>
            </div>
            <div class="flex-fill">
              <p class="font-display fw-bold mb-1" style="font-size:28px;color:var(--gcb-green-dark);line-height:1"><?= number_format($lugar['total_reviews']) ?></p>
              <p style="font-size:12px;font-weight:600;color:var(--gcb-warmgray)" class="mb-3">avaliações no Google</p>
              <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <span style="font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--gcb-warmgray)">Avaliação geral</span>
                  <span style="font-size:12px;font-weight:700;color:var(--gcb-gold)"><?= number_format($lugar['rating'],1) ?> / 5.0</span>
                </div>
                <div class="rounded-pill overflow-hidden" style="height:8px;background:var(--gcb-offwhite)">
                  <div class="rating-bar-fill rounded-pill" style="height:100%;width:<?= ($lugar['rating']/5)*100 ?>%;background:linear-gradient(90deg,var(--gcb-gold),var(--gcb-gold-light))"></div>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                  <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                  <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                  <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                  <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                <span style="font-size:11.5px;font-weight:600;color:var(--gcb-warmgray)">Fonte: Google Meu Negócio</span>
                <?php if (!empty($lugar_db['google_synced_at'])): ?>
                <span style="font-size:10px;color:rgba(139,133,137,.5)">· <?= date('d/m/Y',strtotime($lugar_db['google_synced_at'])) ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Reviews -->
        <div class="d-flex flex-column gap-3 mb-4">
          <?php foreach ($avaliacoes as $av): ?>
          <div class="bg-white rounded-20 p-4 shadow-card">
            <div class="d-flex align-items-start justify-content-between mb-3">
              <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold text-gold" style="width:40px;height:40px;background:var(--gcb-green-dark);font-size:15px">
                  <?= htmlspecialchars($av['avatar']) ?>
                </div>
                <div>
                  <p class="fw-bold mb-0" style="font-size:14px;color:var(--gcb-graphite)"><?= htmlspecialchars($av['nome']) ?></p>
                  <p class="mb-0" style="font-size:11.5px;color:var(--gcb-warmgray)"><?= htmlspecialchars($av['bairro']) ?><?= $av['data']?' · '.$av['data']:'' ?></p>
                </div>
              </div>
              <div class="d-flex align-items-center gap-1 px-3 py-1 rounded-pill" style="background:var(--gcb-gold-pale)">
                <span class="text-gold">★</span>
                <span class="fw-bold" style="font-size:13px;color:var(--gcb-green-dark)"><?= number_format($av['nota'],1) ?></span>
              </div>
            </div>
            <?php if ($av['texto']): ?>
            <p class="fst-italic mb-0" style="font-size:14px;font-weight:300;color:rgba(29,29,27,.75);line-height:1.75">
              "<?= htmlspecialchars($av['texto']) ?>"
            </p>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
          <?php if (empty($avaliacoes)): ?>
          <div class="bg-white rounded-20 p-5 text-center shadow-card">
            <p style="color:var(--gcb-warmgray);font-size:14px">Nenhuma avaliação ainda. Seja o primeiro!</p>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Tab 3: Localização -->
      <div id="tab-3" class="gcb-tab-pane">
        <div class="bg-white rounded-20 p-4 shadow-card">
          <h2 class="font-display fw-bold mb-4" style="color:var(--gcb-green-dark);font-size:22px">Como chegar</h2>
          <div class="rounded-3 overflow-hidden mb-4" style="aspect-ratio:16/7">
            <iframe src="https://maps.google.com/maps?q=<?= urlencode($lugar['endereco']) ?>&output=embed&z=16"
                    class="w-100 h-100 border-0" allowfullscreen loading="lazy"></iframe>
          </div>
          <div class="d-flex flex-column flex-sm-row gap-3">
            <div class="d-flex align-items-start gap-3 flex-fill p-3 rounded-3" style="background:var(--gcb-offwhite)">
              <span class="mt-1 flex-shrink-0 text-gold"><?= icon('pin',18) ?></span>
              <div>
                <p style="font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--gcb-warmgray)" class="mb-1">Endereço</p>
                <p class="fw-semibold mb-0" style="font-size:14px;color:var(--gcb-graphite)"><?= htmlspecialchars($lugar['endereco']) ?></p>
              </div>
            </div>
            <a href="https://maps.google.com/?q=<?= urlencode($lugar['endereco']) ?>" target="_blank"
               class="btn-gold d-flex align-items-center justify-content-center gap-2 rounded-3 p-3 flex-shrink-0 text-decoration-none">
              <?= icon('navigation',14) ?> Abrir no Maps
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">

      <!-- Info card -->
      <div class="bg-white rounded-20 p-4 shadow-card mb-4 reveal">
        <p class="eyebrow">Informações</p>
        <div class="d-flex flex-column gap-3">
          <?php
          $info = [
            ['icon'=>'star',      'label'=>'Preço médio', 'val'=>$lugar['preco_range'],  'href'=>''],
            ['icon'=>'pin',       'label'=>'Endereço',    'val'=>$lugar['endereco'],      'href'=>''],
            ['icon'=>'phone',     'label'=>'Telefone',    'val'=>$lugar['telefone'],      'href'=>'tel:'.$lugar['telefone']],
            ['icon'=>'mail',      'label'=>'E-mail',      'val'=>$lugar['email'],         'href'=>'mailto:'.$lugar['email']],
            ['icon'=>'instagram', 'label'=>'Instagram',   'val'=>$lugar['instagram'],     'href'=>$lugar['instagram']],
            ['icon'=>'whatsapp',  'label'=>'WhatsApp',    'val'=>$lugar['whatsapp'],      'href'=>'https://wa.me/55'.preg_replace('/\D/','',$lugar['whatsapp'])],
          ];
          foreach ($info as $row): if (!$row['val']) continue; ?>
          <div class="d-flex align-items-start gap-3">
            <span class="mt-1 flex-shrink-0 text-gold"><?= icon($row['icon'],15) ?></span>
            <div>
              <p style="font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--gcb-warmgray)" class="mb-1"><?= $row['label'] ?></p>
              <?php if ($row['href']): ?>
              <a href="<?= htmlspecialchars($row['href']) ?>" style="font-size:14px;font-weight:600;color:var(--gcb-graphite)" class="text-decoration-none d-block"><?= htmlspecialchars($row['val']) ?></a>
              <?php else: ?>
              <p class="fw-semibold mb-0" style="font-size:14px;color:var(--gcb-graphite)"><?= htmlspecialchars($row['val']) ?></p>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php if ($lugar['telefone'] || $lugar['whatsapp']): ?>
        <div class="mt-4 pt-4 border-top d-flex flex-column gap-2">
          <?php if ($lugar['telefone']): ?>
          <a href="tel:<?= htmlspecialchars($lugar['telefone']) ?>"
             class="btn-green-dark w-100 d-flex align-items-center justify-content-center gap-2 text-decoration-none py-3">
            <?= icon('phone',13) ?> Ligar agora
          </a>
          <?php endif; ?>
          <?php if ($lugar['whatsapp']): ?>
          <a href="https://wa.me/55<?= preg_replace('/\D/','',$lugar['whatsapp']) ?>"
             target="_blank" rel="noopener"
             class="w-100 d-flex align-items-center justify-content-center gap-2 text-decoration-none py-3 rounded-pill fw-bold"
             style="background:#25D366;color:#fff;font-size:13px;letter-spacing:.04em">
            <?= icon('whatsapp',15) ?> Chamar no WhatsApp
          </a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Horários -->
      <?php if (!empty($lugar['horarios'])): ?>
      <div class="bg-white rounded-20 p-4 shadow-card mb-4 reveal">
        <div class="d-flex align-items-center justify-content-between mb-4">
          <p class="eyebrow mb-0">Horários</p>
          <span class="d-flex align-items-center gap-2 fw-semibold" style="font-size:11px;color:<?= $lugar['aberto_agora']?'#10b981':'#ef4444' ?>">
            <span class="rounded-circle" style="width:6px;height:6px;display:inline-block;background:<?= $lugar['aberto_agora']?'#34d399':'#f87171' ?>"></span>
            <?= $lugar['aberto_agora']?'Aberto':'Fechado' ?>
          </span>
        </div>
        <div class="d-flex flex-column gap-1">
          <?php foreach ($lugar['horarios'] as $dia=>$hora): ?>
              <div class="d-flex justify-content-between py-2 px-2 rounded-3"
                   style="<?= $dia===$lugar['dia_atual'] ? 'background:var(--gcb-gold-pale)' : '' ?>">
                <span style="font-size:13px;font-weight:<?= $dia===$lugar['dia_atual'] ? '700' : '500' ?>;color:<?= $dia===$lugar['dia_atual'] ? 'var(--gcb-green-dark)' : 'rgba(29,29,27,.7)' ?>">
                  <?= htmlspecialchars($dia) ?>
                </span>
                <span style="font-size:12.5px;font-weight:<?= $dia===$lugar['dia_atual'] ? '700' : '500' ?>;color:<?= $hora==='Fechado' ? '#ef4444' : ($dia===$lugar['dia_atual'] ? 'var(--gcb-green)' : 'rgba(29,29,27,.6)') ?>">
                  <?= htmlspecialchars($hora) ?>
                </span>
              </div>
            <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Compartilhar -->
      <div class="bg-white rounded-20 p-4 shadow-card reveal">
        <p class="eyebrow">Compartilhar</p>
        <?php
        $url_share = 'https://guiacampobeloeregiao.com.br/pages/lugar.php?slug='.urlencode($lugar['id'] ? ($lugar_db['slug'] ?? '') : '');
        $texto_share = urlencode($lugar['nome'].' — Guia Campo Belo & Região'."
".$url_share);
        ?>
        <div class="d-flex gap-2 flex-wrap">
          <!-- WhatsApp -->
          <a href="https://wa.me/?text=<?= $texto_share ?>" target="_blank" rel="noopener"
             class="flex-fill d-flex flex-column align-items-center gap-2 py-3 rounded-3 text-decoration-none"
             style="background:#e8fdf0;border:1px solid rgba(37,211,102,.2);color:#25D366;min-width:60px">
            <?= icon('whatsapp',18) ?>
            <span style="font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#25D366">WhatsApp</span>
          </a>
          <!-- E-mail -->
          <a href="mailto:?subject=<?= urlencode($lugar['nome']) ?>&body=<?= $texto_share ?>" target="_blank" rel="noopener"
             class="flex-fill d-flex flex-column align-items-center gap-2 py-3 rounded-3 text-decoration-none"
             style="background:var(--gcb-offwhite);border:1px solid rgba(61,71,51,.08);color:var(--gcb-green);min-width:60px">
            <?= icon('mail',18) ?>
            <span style="font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--gcb-warmgray)">E-mail</span>
          </a>
          <!-- Copiar link -->
          <button onclick="copyLink('<?= htmlspecialchars($url_share) ?>', this)"
             class="flex-fill d-flex flex-column align-items-center gap-2 py-3 rounded-3 border-0"
             style="background:var(--gcb-offwhite);border:1px solid rgba(61,71,51,.08) !important;color:var(--gcb-green);min-width:60px;cursor:pointer">
            <?= icon('external-link',18) ?>
            <span id="copy-label" style="font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--gcb-warmgray)">Copiar link</span>
          </button>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Similares -->
<?php if (!empty($similares)): ?>
<section class="py-5" style="background:var(--gcb-offwhite);border-top:1px solid rgba(61,71,51,.07)">
  <div class="container">
    <div class="d-flex align-items-end justify-content-between mb-4">
      <div>
        <span class="eyebrow">Você também pode gostar</span>
        <h2 class="font-display fw-bold mb-0" style="color:var(--gcb-green-dark);font-size:28px">
          Similares <em class="fst-italic text-gold">próximos</em>
        </h2>
      </div>
      <a href="/pages/categoria.php?slug=<?= htmlspecialchars($lugar['categoria_slug']) ?>" style="font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--gcb-green)">Ver todos</a>
    </div>
    <div class="row g-4">
      <?php foreach ($similares as $s): ?>
      <div class="col-12 col-md-4">
        <a href="/pages/lugar.php?slug=<?= htmlspecialchars($s['slug']) ?>" class="gcb-card d-block text-decoration-none">
          <div class="card-img-wrap">
            <img src="<?= htmlspecialchars($s['img']) ?>" alt="<?= htmlspecialchars($s['nome']) ?>" class="card-img-top" loading="lazy"/>
          </div>
          <div class="card-body">
            <p class="card-cat"><?= htmlspecialchars($s['cat']) ?></p>
            <h3 class="card-title" style="font-size:17px"><?= htmlspecialchars($s['nome']) ?></h3>
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <span class="stars" style="font-size:12px"><?= stars_full($s['rating']) ?></span>
                <span style="font-size:12px;color:var(--gcb-warmgray)">(<?= $s['reviews'] ?>)</span>
              </div>
              <span style="font-size:12px;color:var(--gcb-warmgray);display:flex;align-items:center;gap:4px"><?= icon('pin',11) ?> <?= htmlspecialchars($s['endereco']) ?></span>
            </div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
const imgs = <?= json_encode($lugar['galeria']) ?>;
let cur = 0;
function openGallery(i) {
  cur=i;
  document.getElementById('lightbox-img').src=imgs[i];
  document.getElementById('lightbox-count').textContent=(i+1)+' / '+imgs.length;
  document.getElementById('lightbox').classList.add('open');
  document.body.style.overflow='hidden';
}
function closeLightbox() { document.getElementById('lightbox').classList.remove('open'); document.body.style.overflow=''; }
function prevImg() { cur=(cur-1+imgs.length)%imgs.length; openGallery(cur); }
function nextImg() { cur=(cur+1)%imgs.length; openGallery(cur); }
document.getElementById('lightbox').addEventListener('click',function(e){ if(e.target===this) closeLightbox(); });
document.addEventListener('keydown',e=>{ if(e.key==='Escape') closeLightbox(); if(e.key==='ArrowLeft') prevImg(); if(e.key==='ArrowRight') nextImg(); });

function switchTab(btn) {
  document.querySelectorAll('.gcb-tab-btn').forEach(b=>b.classList.remove('active'));
  document.querySelectorAll('.gcb-tab-pane').forEach(p=>p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById(btn.dataset.tab).classList.add('active');
  window.scrollTo({top:document.getElementById('hero').offsetHeight-60,behavior:'smooth'});
}

function toggleFav(btn) {
  const label=document.getElementById('fav-label');
  const saved=btn.classList.toggle('is-fav');
  label.textContent=saved?'Salvo':'Salvar';
  btn.style.background=saved?'rgba(224,85,85,0.15)':'';
  btn.style.borderColor=saved?'rgba(224,85,85,0.4)':'';
  btn.style.color=saved?'#e05555':'';
}

let descOpen=false;
function toggleDesc() {
  descOpen=!descOpen;
  document.getElementById('desc-full').style.display=descOpen?'block':'none';
  document.getElementById('desc-btn').innerHTML=descOpen?'Ver menos <?= icon('arrow-up',13) ?>':'Ver mais <?= icon('chevron-down',13) ?>';
}

function copyLink(url, btn) {
  navigator.clipboard.writeText(url).then(() => {
    const label = btn.querySelector('#copy-label') || btn.querySelector('span');
    const orig = label.textContent;
    label.textContent = 'Copiado!';
    btn.style.background = 'var(--gcb-gold-pale)';
    setTimeout(() => { label.textContent = orig; btn.style.background = ''; }, 2000);
  }).catch(() => {
    // fallback para navegadores antigos
    const el = document.createElement('textarea');
    el.value = url;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
  });
}

window.addEventListener('scroll',()=>{
  const bg=document.getElementById('hero-bg');
  if(bg) bg.style.transform=`scale(1.06) translateY(${window.scrollY*.25}px)`;
},{passive:true});
</script>
</body>
</html>