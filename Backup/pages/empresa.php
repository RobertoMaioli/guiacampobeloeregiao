<?php
/**
 * lugar.php — Página de detalhe do estabelecimento
 * Guia Campo Belo & Região
 */
require_once __DIR__ . '/../includes/icons.php';

/* ── Mock data (substitua por query ao banco) ── */
$lugar = [
    'id'          => 10,
    'nome'        => 'Osteria Moderna',
    'categoria'   => 'Italiana · Contemporânea',
    'categoria_slug' => 'restaurantes',
    'badge'       => 'Curadoria Guia',
    'descricao'   => 'A Osteria Moderna é o encontro perfeito entre a tradição italiana e a sofisticação contemporânea. Fundada em 2018 pelo chef Marco Ricci, a casa conquistou seu espaço como uma das mesas mais disputadas da Zona Sul. O ambiente foi concebido para celebrar o prazer à mesa — iluminação intimista, adegas à vista e um serviço que equilibra precisão com calor humano.',
    'descricao_extra' => 'Cada prato nasce de ingredientes selecionados com rigor: massas artesanais produzidas diariamente, carnes maturadas e uma carta de vinhos com mais de 180 rótulos italianos. Às sextas e sábados, a casa oferece menu degustação com harmonização, uma experiência que esgota toda semana.',
    'rating'      => 4.8,
    'total_reviews'=> 247,
    'preco_range' => 'R$85 – R$220 por pessoa',
    'endereco'    => 'R. Lagoa Santa, 230 — Campo Belo, São Paulo',
    'telefone'    => '+55 (11) 3045-7892',
    'email'       => 'reservas@osteriamoderna.com.br',
    'site'        => 'www.osteriamoderna.com.br',
    'instagram'   => '@osteriamoderna',
    'aberto_agora'=> true,
    'horarios'    => [
        'Segunda'  => 'Fechado',
        'Terça'    => '12h – 15h · 19h – 23h',
        'Quarta'   => '12h – 15h · 19h – 23h',
        'Quinta'   => '12h – 15h · 19h – 23h',
        'Sexta'    => '12h – 15h · 19h – 00h',
        'Sábado'   => '12h – 00h',
        'Domingo'  => '12h – 17h',
    ],
    'dia_atual'   => 'Sexta',
    'servicos'    => ['Reservas', 'Wi-Fi', 'Estacionamento', 'Acessível', 'Visa/Master', 'American Express', 'Menu Vegano'],
    'hero_img'    => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1600&q=80',
    'galeria'     => [
        'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&q=80',
        'https://images.unsplash.com/photo-1544025162-d76694265947?w=800&q=80',
        'https://images.unsplash.com/photo-1481931098730-318b6f776db0?w=800&q=80',
        'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800&q=80',
        'https://images.unsplash.com/photo-1600891964599-f61ba0e24092?w=800&q=80',
    ],
    'menu' => [
        ['img' => 'https://images.unsplash.com/photo-1473093226795-af9932fe5856?w=200&q=80', 'nome' => 'Tagliatelle al Tartufo',    'preco' => 'R$ 98',  'desc' => 'Massa fresca, trufa negra, parmesão 36 meses e manteiga noisette'],
        ['img' => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=200&q=80', 'nome' => 'Bistecca alla Fiorentina', 'preco' => 'R$ 185', 'desc' => 'Contrafilé maturado 21 dias, rúcula, limone e azeite extravirgem'],
        ['img' => 'https://images.unsplash.com/photo-1476124369491-e7addf5db371?w=200&q=80', 'nome' => 'Risotto ai Funghi',        'preco' => 'R$ 89',  'desc' => 'Arroz carnaroli, mix de cogumelos selvagens, parmesão e vinho branco'],
        ['img' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=200&q=80', 'nome' => 'Vitello Tonnato',           'preco' => 'R$ 72',  'desc' => 'Vitela fatiada, molho de atum, alcaparras e gema curada'],
    ],
    'avaliacoes' => [
        ['avatar' => 'F',  'nome' => 'Fernanda Almeida', 'bairro' => 'Campo Belo',     'nota' => 5.0, 'data' => '12 Mar 2025', 'texto' => 'Uma experiência absolutamente impecável. O tagliatelle al tartufo é de outro nível — nunca comi algo igual em São Paulo. O serviço é discreto, atencioso e sem excessos. Voltarei certamente.'],
        ['avatar' => 'R',  'nome' => 'Ricardo Moura',    'bairro' => 'Brooklin',        'nota' => 4.5, 'data' => '28 Fev 2025', 'texto' => 'Fui para comemorar aniversário e a casa surpreendeu em todos os aspectos. A carta de vinhos é generosa e o sommelier é excelente. O único porém é a dificuldade de estacionamento na sexta-feira.'],
        ['avatar' => 'P',  'nome' => 'Priscila Tanaka',  'bairro' => 'Moema',           'nota' => 5.0, 'data' => '15 Jan 2025', 'texto' => 'O menu degustação com harmonização foi uma das melhores refeições da minha vida. Cada prato conta uma história. Obrigatório reservar com pelo menos 2 semanas de antecedência.'],
    ],
    'ratings_breakdown' => [
        'Qualidade'   => 4.9,
        'Localização' => 4.5,
        'Atendimento' => 4.8,
        'Custo-Ben.'  => 4.6,
    ],
    'similares' => [
        ['id'=>11,'img'=>'https://images.unsplash.com/photo-1611270629569-8b357cb88da9?w=600&q=80','nome'=>'Nishiki Omakase','cat'=>'Japonesa','rating'=>5.0,'reviews'=>183,'endereco'=>'Al. Arapanés, 450'],
        ['id'=>13,'img'=>'https://images.unsplash.com/photo-1600891964599-f61ba0e24092?w=600&q=80','nome'=>'Vino e Cucina',   'cat'=>'Italiana','rating'=>4.7,'reviews'=>112,'endereco'=>'R. Domingos Lins, 88'],
        ['id'=>14,'img'=>'https://images.unsplash.com/photo-1476224203421-9ac39bcb3327?w=600&q=80','nome'=>'Le Marché Bistró','cat'=>'Francesa','rating'=>4.6,'reviews'=>98, 'endereco'=>'Al. dos Arapanés, 100'],
    ],
];

$page_title = $lugar['nome'] . ' — Guia Campo Belo & Região';

function stars_full(float $n, int $size = 14): string {
    $full  = floor($n);
    $half  = ($n - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    $s = '';
    for ($i = 0; $i < $full;  $i++) $s .= '<span style="color:#c9aa6b">★</span>';
    if ($half)                       $s .= '<span style="color:#c9aa6b">½</span>';
    for ($i = 0; $i < $empty; $i++) $s .= '<span style="color:rgba(139,133,137,.35)">★</span>';
    return $s;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($lugar['nome']) ?> — <?= htmlspecialchars($lugar['categoria']) ?>. Confira avaliações, cardápio, horários e como chegar."/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,600&display=swap" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { display:['"Playfair Display"','Georgia','serif'], body:['Montserrat','sans-serif'] },
          colors: {
            green:{ DEFAULT:'#3d4733', dark:'#2a3022', light:'#4f5c40' },
            gold:{ DEFAULT:'#c9aa6b', light:'#ddc48a', pale:'#f5edda' },
            cream:'#faf8f3', offwhite:'#f2f0eb', graphite:'#1d1d1b', warmgray:'#8b8589'
          },
        }
      }
    }
  </script>
  <style>
    body { font-family:'Montserrat',sans-serif; background:#faf8f3; }
    .font-display { font-family:'Playfair Display',Georgia,serif; }
    .scrollbar-hide::-webkit-scrollbar{display:none}
    .scrollbar-hide{-ms-overflow-style:none;scrollbar-width:none}
    /* tab active */
    .tab-btn.active { color:#3d4733; border-bottom-color:#c9aa6b; }
    .tab-panel { display:none; }
    .tab-panel.active { display:block; }
    /* gallery lightbox */
    #lightbox { display:none; }
    #lightbox.open { display:flex; }
    /* rating bar fill */
    .rating-fill { transition: width 1s cubic-bezier(.4,0,.2,1); }
    /* smooth reveal */
    .reveal { opacity:0; transform:translateY(20px); transition:opacity .5s ease,transform .5s ease; }
    .reveal.in { opacity:1; transform:translateY(0); }
    /* hero parallax */
    #hero-bg { will-change:transform; }
  </style>
</head>
<body class="bg-cream text-graphite antialiased overflow-x-hidden">

<?php include __DIR__ . '/../includes/search-modal.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Lightbox -->
<div id="lightbox" class="fixed inset-0 z-[600] items-center justify-center bg-graphite/92 backdrop-blur-md">
  <button onclick="closeLightbox()" class="absolute top-6 right-6 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors"><?= icon('close',18) ?></button>
  <button onclick="prevImg()" class="absolute left-6 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-gold text-white flex items-center justify-center transition-colors"><?= icon('arrow-left',18) ?></button>
  <button onclick="nextImg()" class="absolute right-20 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-gold text-white flex items-center justify-center transition-colors"><?= icon('arrow-right',18) ?></button>
  <img id="lightbox-img" src="" alt="" class="max-w-[90vw] max-h-[85vh] object-contain rounded-xl shadow-2xl"/>
  <p id="lightbox-count" class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/50 text-sm font-medium"></p>
</div>

<!-- ══════════════════════════════════════════════
     HERO
══════════════════════════════════════════════ -->
<section class="relative h-[520px] overflow-hidden" id="hero">
  <div id="hero-bg"
       class="absolute inset-0 scale-[1.06]"
       style="background:url('<?= htmlspecialchars($lugar['hero_img']) ?>') center/cover no-repeat"></div>
  <div class="absolute inset-0"
       style="background:linear-gradient(180deg,rgba(42,48,34,.45) 0%,rgba(42,48,34,.72) 70%,rgba(42,48,34,.92) 100%)"></div>

  <!-- Content -->
  <div class="relative z-10 h-full flex flex-col justify-end pb-0">
    <div class="max-w-[1180px] mx-auto px-10 w-full pb-8">

      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 text-[11px] font-medium text-white/50 mb-5" aria-label="Breadcrumb">
        <a href="/index.php" class="hover:text-gold transition-colors">Início</a>
        <span class="text-white/25"><?= icon('chevron-right',10) ?></span>
        <a href="/pages/restaurantes.php" class="hover:text-gold transition-colors"><?= htmlspecialchars($lugar['categoria_slug']) ?></a>
        <span class="text-white/25"><?= icon('chevron-right',10) ?></span>
        <span class="text-white/70"><?= htmlspecialchars($lugar['nome']) ?></span>
      </nav>

      <div class="flex items-end justify-between gap-6 flex-wrap">
        <div>
          <!-- Category + Badge -->
          <div class="flex items-center gap-2.5 mb-3">
            <span class="text-[9px] font-black tracking-[0.2em] uppercase text-gold">
              <?= htmlspecialchars($lugar['categoria']) ?>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full
                         bg-gold text-green-dark text-[9px] font-black tracking-[0.12em] uppercase">
              <?= icon('verified',11) ?> <?= htmlspecialchars($lugar['badge']) ?>
            </span>
          </div>

          <!-- Name -->
          <h1 class="font-display text-[clamp(32px,5vw,56px)] font-bold text-white leading-[1.1] mb-4">
            <?= htmlspecialchars($lugar['nome']) ?>
          </h1>

          <!-- Meta row -->
          <div class="flex items-center gap-4 flex-wrap">
            <!-- Stars + count -->
            <div class="flex items-center gap-2">
              <div class="flex items-center gap-0.5 text-[15px]"><?= stars_full($lugar['rating']) ?></div>
              <span class="text-white font-bold text-[15px]"><?= number_format($lugar['rating'],1) ?></span>
              <span class="text-white/50 text-[13px]">(<?= $lugar['total_reviews'] ?> avaliações)</span>
            </div>
            <span class="text-white/25 hidden sm:block">|</span>
            <!-- Status -->
            <div class="flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full <?= $lugar['aberto_agora'] ? 'bg-emerald-400' : 'bg-red-400' ?>"></span>
              <span class="text-[13px] font-semibold <?= $lugar['aberto_agora'] ? 'text-emerald-400' : 'text-red-400' ?>">
                <?= $lugar['aberto_agora'] ? 'Aberto agora' : 'Fechado agora' ?>
              </span>
            </div>
            <span class="text-white/25 hidden sm:block">|</span>
            <!-- Address -->
            <div class="flex items-center gap-1.5 text-white/60 text-[13px]">
              <?= icon('pin',13) ?>
              <?= htmlspecialchars($lugar['endereco']) ?>
            </div>
          </div>
        </div>

        <!-- Action buttons -->
        <div class="flex items-center gap-2.5 flex-wrap">
          <button onclick="openGallery(0)"
                  class="flex items-center gap-2 px-5 py-2.5 bg-white/10 hover:bg-white/20
                         backdrop-blur-sm border border-white/20 rounded-full text-white
                         text-[11px] font-bold tracking-widest uppercase transition-all duration-200">
            <?= icon('grid',13) ?> Ver fotos
          </button>
          <button id="fav-btn"
                  class="flex items-center gap-2 px-5 py-2.5 bg-white/10 hover:bg-white/20
                         backdrop-blur-sm border border-white/20 rounded-full text-white
                         text-[11px] font-bold tracking-widest uppercase transition-all duration-200"
                  onclick="toggleFav(this)">
            <?= icon('heart',13) ?> <span id="fav-label">Salvar</span>
          </button>
          <a href="https://maps.google.com/?q=<?= urlencode($lugar['endereco']) ?>" target="_blank"
             class="flex items-center gap-2 px-5 py-2.5 bg-gold hover:bg-gold-light
                    text-green-dark text-[11px] font-black tracking-widest uppercase
                    rounded-full transition-all duration-200 shadow-[0_6px_20px_rgba(201,170,107,.35)]">
            <?= icon('navigation',13) ?> Como chegar
          </a>
        </div>
      </div>
    </div>

    <!-- Tab bar -->
    <div class="border-t border-white/10 bg-green-dark/80 backdrop-blur-md">
      <div class="max-w-[1180px] mx-auto px-10">
        <div class="flex gap-0">
          <?php foreach (['Sobre','Fotos','Cardápio','Avaliações','Localização'] as $i => $tab): ?>
          <button class="tab-btn px-6 py-4 text-[11px] font-bold tracking-[0.1em] uppercase
                         text-white/50 hover:text-white border-b-2 border-b-transparent
                         transition-all duration-200 <?= $i===0 ? 'active' : '' ?>"
                  data-tab="tab-<?= $i ?>"
                  onclick="switchTab(this)">
            <?= htmlspecialchars($tab) ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════════
     MAIN CONTENT + SIDEBAR
══════════════════════════════════════════════ -->
<div class="max-w-[1180px] mx-auto px-10 py-12">
  <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-10">

    <!-- ── LEFT COLUMN ── -->
    <div>

      <!-- TAB 0: Sobre -->
      <div id="tab-0" class="tab-panel active">
        <div class="bg-white rounded-2xl border border-green/[0.07] p-8 mb-6 reveal">
          <h2 class="font-display text-[22px] font-bold text-green-dark mb-5">
            Sobre o <?= htmlspecialchars($lugar['nome']) ?>
          </h2>
          <p class="text-[15px] font-light text-graphite/75 leading-[1.8] mb-4" id="desc-short">
            <?= htmlspecialchars($lugar['descricao']) ?>
          </p>
          <p class="text-[15px] font-light text-graphite/75 leading-[1.8] hidden" id="desc-full">
            <?= htmlspecialchars($lugar['descricao_extra']) ?>
          </p>
          <button onclick="toggleDesc()" id="desc-btn"
                  class="mt-2 text-[12px] font-bold tracking-widest uppercase text-gold
                         hover:text-gold-light transition-colors flex items-center gap-1.5">
            Ver mais <?= icon('chevron-down',13) ?>
          </button>
        </div>

        <!-- Services -->
        <div class="bg-white rounded-2xl border border-green/[0.07] p-8 mb-6 reveal">
          <h3 class="text-[11px] font-black tracking-[0.2em] uppercase text-gold mb-5">
            Serviços &amp; Comodidades
          </h3>
          <div class="flex flex-wrap gap-2.5">
            <?php foreach ($lugar['servicos'] as $s): ?>
            <span class="flex items-center gap-2 px-3.5 py-2 bg-offwhite rounded-full
                         text-[12px] font-semibold text-green border border-green/[0.08]">
              <?= icon('verified',12) ?> <?= htmlspecialchars($s) ?>
            </span>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Gallery preview -->
        <div class="bg-white rounded-2xl border border-green/[0.07] p-8 reveal">
          <div class="flex items-center justify-between mb-5">
            <h3 class="text-[11px] font-black tracking-[0.2em] uppercase text-gold">Fotos</h3>
            <button onclick="openGallery(0)"
                    class="text-[11px] font-bold tracking-widest uppercase text-green
                           hover:text-gold transition-colors flex items-center gap-1.5">
              Ver todas <?= icon('arrow-right',12) ?>
            </button>
          </div>
          <div class="grid grid-cols-3 gap-2.5">
            <?php foreach (array_slice($lugar['galeria'],0,3) as $i=>$img): ?>
            <div class="relative aspect-square rounded-xl overflow-hidden cursor-pointer group"
                 onclick="openGallery(<?= $i ?>)">
              <img src="<?= htmlspecialchars($img) ?>" alt="Foto <?= $i+1 ?>" loading="lazy"
                   class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"/>
              <div class="absolute inset-0 bg-green-dark/0 group-hover:bg-green-dark/30 transition-all duration-300"></div>
              <?php if ($i===2 && count($lugar['galeria'])>3): ?>
              <div class="absolute inset-0 flex items-center justify-center
                          bg-green-dark/55 backdrop-blur-[2px]"
                   onclick="openGallery(3)">
                <span class="text-white font-display text-2xl font-bold">
                  +<?= count($lugar['galeria'])-3 ?>
                </span>
              </div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- TAB 1: Fotos -->
      <div id="tab-1" class="tab-panel">
        <div class="bg-white rounded-2xl border border-green/[0.07] p-8">
          <h2 class="font-display text-[22px] font-bold text-green-dark mb-6">Galeria de Fotos</h2>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <?php foreach ($lugar['galeria'] as $i=>$img): ?>
            <div class="relative aspect-[4/3] rounded-xl overflow-hidden cursor-pointer group"
                 onclick="openGallery(<?= $i ?>)">
              <img src="<?= htmlspecialchars($img) ?>" alt="Foto <?= $i+1 ?>"
                   loading="lazy"
                   class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-108"/>
              <div class="absolute inset-0 bg-green-dark/0 group-hover:bg-green-dark/25 transition-all duration-300
                          flex items-center justify-center">
                <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200
                            w-10 h-10 rounded-full bg-white/90 flex items-center justify-center text-green">
                  <?= icon('external-link',16) ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- TAB 2: Cardápio -->
      <div id="tab-2" class="tab-panel">
        <div class="bg-white rounded-2xl border border-green/[0.07] p-8">
          <h2 class="font-display text-[22px] font-bold text-green-dark mb-2">Cardápio</h2>
          <p class="text-[13px] text-warmgray mb-7">Seleção de destaques. Cardápio completo sujeito a alterações.</p>
          <div class="space-y-4">
            <?php foreach ($lugar['menu'] as $item): ?>
            <div class="flex items-start gap-4 p-4 rounded-xl border border-green/[0.07]
                        hover:border-gold/30 hover:bg-gold-pale/30 transition-all duration-200 group">
              <div class="w-[72px] h-[72px] rounded-xl overflow-hidden flex-shrink-0">
                <img src="<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['nome']) ?>"
                     loading="lazy" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"/>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3">
                  <h4 class="font-display text-[16px] font-bold text-green-dark leading-tight">
                    <?= htmlspecialchars($item['nome']) ?>
                  </h4>
                  <span class="font-bold text-[15px] text-gold flex-shrink-0">
                    <?= htmlspecialchars($item['preco']) ?>
                  </span>
                </div>
                <p class="text-[13px] font-light text-graphite/60 leading-relaxed mt-1">
                  <?= htmlspecialchars($item['desc']) ?>
                </p>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="mt-6 pt-6 border-t border-offwhite flex items-center gap-3">
            <?= icon('sparkles',14,'text-gold') ?>
            <p class="text-[12.5px] text-warmgray">
              Cardápio degustação com harmonização disponível às sextas e sábados — reserva obrigatória.
            </p>
          </div>
        </div>
      </div>

      <!-- TAB 3: Avaliações -->
      <div id="tab-3" class="tab-panel">
        <!-- Score overview -->
        <div class="bg-white rounded-2xl border border-green/[0.07] p-8 mb-6">
          <div class="grid grid-cols-1 sm:grid-cols-[auto_1fr] gap-8 items-center">
            <!-- Big number -->
            <div class="flex flex-col items-center justify-center w-32 h-32 rounded-2xl
                        bg-green-dark text-center flex-shrink-0">
              <span class="font-display text-[42px] font-bold text-white leading-none">
                <?= number_format($lugar['rating'],1) ?>
              </span>
              <div class="flex gap-0.5 mt-1 text-[13px]"><?= stars_full($lugar['rating']) ?></div>
              <span class="text-[10px] font-semibold text-white/40 mt-1 tracking-wider uppercase">
                <?= $lugar['total_reviews'] ?> avaliações
              </span>
            </div>
            <!-- Breakdown bars -->
            <div class="space-y-3.5">
              <?php foreach ($lugar['ratings_breakdown'] as $cat => $val): ?>
              <div>
                <div class="flex justify-between mb-1.5">
                  <span class="text-[12px] font-semibold text-graphite"><?= htmlspecialchars($cat) ?></span>
                  <span class="text-[12px] font-bold text-gold"><?= number_format($val,1) ?></span>
                </div>
                <div class="h-1.5 bg-offwhite rounded-full overflow-hidden">
                  <div class="rating-fill h-full rounded-full bg-gradient-to-r from-gold to-gold-light"
                       style="width:<?= ($val/5)*100 ?>%"></div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Reviews list -->
        <div class="space-y-4 mb-8">
          <?php foreach ($lugar['avaliacoes'] as $av): ?>
          <div class="bg-white rounded-2xl border border-green/[0.07] p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-green-dark flex items-center justify-center
                            text-gold font-bold text-[15px] flex-shrink-0">
                  <?= htmlspecialchars($av['avatar']) ?>
                </div>
                <div>
                  <p class="font-bold text-[14px] text-graphite"><?= htmlspecialchars($av['nome']) ?></p>
                  <p class="text-[11.5px] text-warmgray"><?= htmlspecialchars($av['bairro']) ?> · <?= htmlspecialchars($av['data']) ?></p>
                </div>
              </div>
              <div class="flex items-center gap-1.5 px-3 py-1 bg-gold-pale rounded-full">
                <span class="text-gold text-[12px]">★</span>
                <span class="text-[13px] font-bold text-green-dark"><?= number_format($av['nota'],1) ?></span>
              </div>
            </div>
            <p class="text-[14px] font-light text-graphite/75 leading-[1.75] italic">
              "<?= htmlspecialchars($av['texto']) ?>"
            </p>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Add review form -->
        <div class="bg-white rounded-2xl border border-green/[0.07] p-8">
          <h3 class="font-display text-[20px] font-bold text-green-dark mb-6">Deixe sua avaliação</h3>

          <!-- Star pickers -->
          <div class="grid grid-cols-2 gap-4 mb-6">
            <?php foreach (array_keys($lugar['ratings_breakdown']) as $cat): ?>
            <div>
              <label class="block text-[11px] font-bold tracking-widest uppercase text-warmgray mb-2">
                <?= htmlspecialchars($cat) ?>
              </label>
              <div class="star-picker flex gap-1" data-cat="<?= htmlspecialchars($cat) ?>">
                <?php for ($i=1;$i<=5;$i++): ?>
                <button type="button"
                        class="star-pick text-[22px] text-warmgray/30 hover:text-gold transition-colors"
                        data-val="<?= $i ?>" onclick="pickStar(this)">★</button>
                <?php endfor; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <input type="text" placeholder="Seu nome"
                   class="px-4 py-3 bg-offwhite border border-green/[0.1] rounded-xl
                          text-[14px] outline-none focus:border-gold/50 transition-colors"/>
            <input type="email" placeholder="Seu e-mail"
                   class="px-4 py-3 bg-offwhite border border-green/[0.1] rounded-xl
                          text-[14px] outline-none focus:border-gold/50 transition-colors"/>
          </div>
          <textarea rows="4" placeholder="Conte sobre sua experiência…"
                    class="w-full px-4 py-3 bg-offwhite border border-green/[0.1] rounded-xl
                           text-[14px] outline-none focus:border-gold/50 transition-colors
                           resize-none mb-5"></textarea>
          <button class="flex items-center gap-2 px-7 py-3.5 bg-gold hover:bg-gold-light
                         text-green-dark text-[12px] font-black tracking-widest uppercase
                         rounded-full transition-all duration-200 shadow-[0_6px_20px_rgba(201,170,107,.3)]">
            Enviar avaliação <?= icon('arrow-right',13) ?>
          </button>
        </div>
      </div>

      <!-- TAB 4: Localização -->
      <div id="tab-4" class="tab-panel">
        <div class="bg-white rounded-2xl border border-green/[0.07] p-8">
          <h2 class="font-display text-[22px] font-bold text-green-dark mb-6">Como chegar</h2>
          <!-- Map embed placeholder -->
          <div class="relative w-full aspect-[16/7] rounded-xl overflow-hidden mb-6 bg-offwhite">
            <iframe
              src="https://maps.google.com/maps?q=<?= urlencode($lugar['endereco']) ?>&output=embed&z=16"
              class="w-full h-full border-0" allowfullscreen loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
          <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex items-start gap-3 flex-1 p-4 bg-offwhite rounded-xl">
              <?= icon('pin',18,'text-gold mt-0.5 flex-shrink-0') ?>
              <div>
                <p class="text-[11px] font-black tracking-widest uppercase text-warmgray mb-1">Endereço</p>
                <p class="text-[14px] font-semibold text-graphite"><?= htmlspecialchars($lugar['endereco']) ?></p>
              </div>
            </div>
            <a href="https://maps.google.com/?q=<?= urlencode($lugar['endereco']) ?>" target="_blank"
               class="flex items-center justify-center gap-2 px-6 py-4 bg-gold hover:bg-gold-light
                      text-green-dark text-[12px] font-black tracking-widest uppercase rounded-xl
                      transition-all duration-200 flex-shrink-0">
              <?= icon('navigation',14) ?> Abrir no Maps
            </a>
          </div>
        </div>
      </div>

    </div><!-- /left column -->


    <!-- ── RIGHT SIDEBAR ── -->
    <aside class="space-y-5">

      <!-- Quick info card -->
      <div class="bg-white rounded-2xl border border-green/[0.07] p-6 reveal">
        <h3 class="text-[10px] font-black tracking-[0.2em] uppercase text-gold mb-5">Informações</h3>

        <div class="space-y-4">
          <!-- Price -->
          <div class="flex items-start gap-3">
            <span class="text-gold mt-0.5 flex-shrink-0"><?= icon('star',15) ?></span>
            <div>
              <p class="text-[10px] font-black tracking-widest uppercase text-warmgray">Preço médio</p>
              <p class="text-[14px] font-semibold text-graphite mt-0.5"><?= htmlspecialchars($lugar['preco_range']) ?></p>
            </div>
          </div>
          <!-- Address -->
          <div class="flex items-start gap-3">
            <span class="text-gold mt-0.5 flex-shrink-0"><?= icon('pin',15) ?></span>
            <div>
              <p class="text-[10px] font-black tracking-widest uppercase text-warmgray">Endereço</p>
              <p class="text-[14px] font-semibold text-graphite mt-0.5 leading-snug"><?= htmlspecialchars($lugar['endereco']) ?></p>
            </div>
          </div>
          <!-- Phone -->
          <div class="flex items-start gap-3">
            <span class="text-gold mt-0.5 flex-shrink-0"><?= icon('phone',15) ?></span>
            <div>
              <p class="text-[10px] font-black tracking-widest uppercase text-warmgray">Telefone</p>
              <a href="tel:<?= htmlspecialchars($lugar['telefone']) ?>"
                 class="text-[14px] font-semibold text-graphite hover:text-gold transition-colors mt-0.5 block">
                <?= htmlspecialchars($lugar['telefone']) ?>
              </a>
            </div>
          </div>
          <!-- Email -->
          <div class="flex items-start gap-3">
            <span class="text-gold mt-0.5 flex-shrink-0"><?= icon('mail',15) ?></span>
            <div>
              <p class="text-[10px] font-black tracking-widest uppercase text-warmgray">E-mail</p>
              <a href="mailto:<?= htmlspecialchars($lugar['email']) ?>"
                 class="text-[14px] font-semibold text-graphite hover:text-gold transition-colors mt-0.5 block break-all">
                <?= htmlspecialchars($lugar['email']) ?>
              </a>
            </div>
          </div>
          <!-- Instagram -->
          <div class="flex items-start gap-3">
            <span class="text-gold mt-0.5 flex-shrink-0"><?= icon('instagram',15) ?></span>
            <div>
              <p class="text-[10px] font-black tracking-widest uppercase text-warmgray">Instagram</p>
              <p class="text-[14px] font-semibold text-graphite mt-0.5"><?= htmlspecialchars($lugar['instagram']) ?></p>
            </div>
          </div>
        </div>

        <div class="mt-5 pt-5 border-t border-offwhite">
          <a href="tel:<?= htmlspecialchars($lugar['telefone']) ?>"
             class="w-full flex items-center justify-center gap-2 py-3 bg-green-dark
                    hover:bg-green text-white text-[12px] font-black tracking-widest
                    uppercase rounded-full transition-colors duration-200">
            <?= icon('phone',13) ?> Ligar agora
          </a>
        </div>
      </div>

      <!-- Opening hours -->
      <div class="bg-white rounded-2xl border border-green/[0.07] p-6 reveal">
        <div class="flex items-center justify-between mb-5">
          <h3 class="text-[10px] font-black tracking-[0.2em] uppercase text-gold">Horários</h3>
          <span class="flex items-center gap-1.5 text-[11px] font-semibold
                       <?= $lugar['aberto_agora'] ? 'text-emerald-500' : 'text-red-400' ?>">
            <span class="w-1.5 h-1.5 rounded-full <?= $lugar['aberto_agora'] ? 'bg-emerald-400' : 'bg-red-400' ?>"></span>
            <?= $lugar['aberto_agora'] ? 'Aberto' : 'Fechado' ?>
          </span>
        </div>
        <div class="space-y-2.5">
          <?php foreach ($lugar['horarios'] as $dia => $hora): ?>
          <div class="flex items-center justify-between py-2
                      <?= $dia === $lugar['dia_atual'] ? 'bg-gold-pale -mx-2 px-2 rounded-lg' : '' ?>">
            <span class="text-[13px] font-<?= $dia === $lugar['dia_atual'] ? 'bold text-green-dark' : 'medium text-graphite/70' ?>">
              <?= htmlspecialchars($dia) ?>
            </span>
            <span class="text-[12.5px] font-<?= $dia === $lugar['dia_atual'] ? 'bold text-green' : 'medium text-graphite/60' ?>
                         <?= $hora === 'Fechado' ? '!text-red-400' : '' ?>">
              <?= htmlspecialchars($hora) ?>
            </span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Share -->
      <div class="bg-white rounded-2xl border border-green/[0.07] p-6 reveal">
        <h3 class="text-[10px] font-black tracking-[0.2em] uppercase text-gold mb-4">Compartilhar</h3>
        <div class="flex gap-2.5">
          <?php
          $url = 'https://guiacampobeloeregiao.com.br/lugar.php?id=' . $lugar['id'];
          $shares = [
            ['icon'=>'whatsapp',  'href'=>"https://wa.me/?text=".urlencode($lugar['nome'].' - '.$url), 'label'=>'WhatsApp'],
            ['icon'=>'instagram', 'href'=>"https://www.instagram.com/", 'label'=>'Instagram'],
            ['icon'=>'mail',      'href'=>"mailto:?subject=".urlencode($lugar['nome'])."&body=".urlencode($url), 'label'=>'E-mail'],
          ];
          foreach ($shares as $s): ?>
          <a href="<?= htmlspecialchars($s['href']) ?>" target="_blank" rel="noopener"
             class="flex-1 flex flex-col items-center gap-1.5 py-3 bg-offwhite
                    hover:bg-gold-pale hover:border-gold/30 border border-transparent
                    rounded-xl transition-all duration-200 text-green cursor-pointer"
             aria-label="<?= $s['label'] ?>">
            <?= icon($s['icon'],18) ?>
            <span class="text-[9px] font-bold tracking-wider uppercase text-warmgray">
              <?= $s['label'] ?>
            </span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

    </aside><!-- /sidebar -->
  </div>
</div>


<!-- ══════════════════════════════════════════════
     SIMILAR LISTINGS
══════════════════════════════════════════════ -->
<section class="py-16 bg-offwhite border-t border-green/[0.07]">
  <div class="max-w-[1180px] mx-auto px-10">
    <div class="flex items-end justify-between mb-8">
      <div>
        <span class="text-[10px] font-black tracking-[0.22em] uppercase text-gold mb-2 block">
          Você também pode gostar
        </span>
        <h2 class="font-display text-[28px] font-bold text-green-dark">
          Similares <em class="italic text-gold">próximos</em>
        </h2>
      </div>
      <a href="/pages/restaurantes.php"
         class="flex items-center gap-2 text-[11px] font-bold tracking-widest uppercase
                text-green hover:text-gold transition-colors">
        Ver todos <?= icon('arrow-right',12) ?>
      </a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($lugar['similares'] as $s): ?>
      <a href="/lugar.php?id=<?= (int)$s['id'] ?>"
         class="group bg-white rounded-[20px] overflow-hidden border border-green/[0.06]
                shadow-[0_2px_12px_rgba(29,29,27,.07)] hover:-translate-y-1
                hover:shadow-[0_8px_32px_rgba(29,29,27,.11)] transition-all duration-300">
        <div class="h-[180px] overflow-hidden relative">
          <img src="<?= htmlspecialchars($s['img']) ?>" alt="<?= htmlspecialchars($s['nome']) ?>"
               loading="lazy" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.07]"/>
        </div>
        <div class="p-5">
          <p class="text-[9.5px] font-black tracking-[0.18em] uppercase text-gold mb-1.5">
            <?= htmlspecialchars($s['cat']) ?>
          </p>
          <h3 class="font-display text-[17px] font-bold text-green-dark leading-tight mb-2.5">
            <?= htmlspecialchars($s['nome']) ?>
          </h3>
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-1.5">
              <span class="text-gold text-[13px]"><?= stars_full($s['rating']) ?></span>
              <span class="text-[12px] text-warmgray">(<?= $s['reviews'] ?>)</span>
            </div>
            <div class="flex items-center gap-1 text-[12px] text-warmgray">
              <?= icon('pin',11) ?> <?= htmlspecialchars($s['endereco']) ?>
            </div>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script src="/../assets/js/app.js" defer></script>
<script>
/* ── Tabs ── */
function switchTab(btn) {
  document.querySelectorAll('.tab-btn').forEach(b  => b.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById(btn.dataset.tab).classList.add('active');
  window.scrollTo({ top: document.getElementById('hero').offsetHeight - 60, behavior: 'smooth' });
}

/* ── Gallery ── */
const imgs = <?= json_encode($lugar['galeria']) ?>;
let cur = 0;

function openGallery(i) {
  cur = i;
  document.getElementById('lightbox-img').src = imgs[i];
  document.getElementById('lightbox-count').textContent = (i+1) + ' / ' + imgs.length;
  document.getElementById('lightbox').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeLightbox() {
  document.getElementById('lightbox').classList.remove('open');
  document.body.style.overflow = '';
}
function prevImg() { cur = (cur - 1 + imgs.length) % imgs.length; openGallery(cur); }
function nextImg() { cur = (cur + 1) % imgs.length; openGallery(cur); }

document.getElementById('lightbox').addEventListener('click', function(e) {
  if (e.target === this) closeLightbox();
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape')      closeLightbox();
  if (e.key === 'ArrowLeft')   prevImg();
  if (e.key === 'ArrowRight')  nextImg();
});

/* ── Favourite ── */
function toggleFav(btn) {
  const label = document.getElementById('fav-label');
  const saved = btn.classList.toggle('is-fav');
  label.textContent = saved ? 'Salvo' : 'Salvar';
  btn.style.background = saved ? 'rgba(224,85,85,0.15)' : '';
  btn.style.borderColor = saved ? 'rgba(224,85,85,0.4)' : '';
  btn.style.color = saved ? '#e05555' : '';
}

/* ── Description toggle ── */
let descOpen = false;
function toggleDesc() {
  descOpen = !descOpen;
  document.getElementById('desc-full').classList.toggle('hidden', !descOpen);
  document.getElementById('desc-btn').innerHTML = descOpen
    ? `Ver menos <?= icon('arrow-up',13) ?>`
    : `Ver mais <?= icon('chevron-down',13) ?>`;
}

/* ── Star picker ── */
function pickStar(btn) {
  const val = parseInt(btn.dataset.val);
  const group = btn.closest('.star-picker');
  group.querySelectorAll('.star-pick').forEach((s, i) => {
    s.style.color = i < val ? '#c9aa6b' : '';
  });
}

/* ── Hero parallax ── */
window.addEventListener('scroll', () => {
  const bg = document.getElementById('hero-bg');
  if (bg) bg.style.transform = `scale(1.06) translateY(${window.scrollY * 0.25}px)`;
}, { passive: true });

/* ── Reveal on scroll ── */
const ro = new IntersectionObserver(entries => {
  entries.forEach((e, i) => {
    if (e.isIntersecting) {
      setTimeout(() => e.target.classList.add('in'), i * 80);
      ro.unobserve(e.target);
    }
  });
}, { threshold: 0.08 });
document.querySelectorAll('.reveal').forEach(el => ro.observe(el));
</script>
</body>
</html>