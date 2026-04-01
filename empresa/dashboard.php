<?php
/**
 * empresa/dashboard.php
 * Painel do empresário pós-aprovação
 */
require_once __DIR__ . '/../core/UserAuth.php';
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Sanitize.php';
require_once __DIR__ . '/../includes/icons.php';

UserAuth::require();
$usuario = UserAuth::current();

// Sessão ativa mas usuário excluído do banco
if (!$usuario) {
    UserAuth::logout();
    header('Location: /empresa/login.php'); exit;
}

// Roteamento por status
// Apenas rascunho, pendente e reprovada saem do dashboard
// Suspensa e aprovada ficam aqui (suspensa mostra banner de aviso)
match($usuario['empresa_status'] ?? '') {
    'rascunho'  => (header('Location: /empresa/onboarding.php') ?: exit()),
    'pendente'  => (header('Location: /empresa/status.php')     ?: exit()),
    'reprovada' => (header('Location: /empresa/status.php')     ?: exit()),
    default     => null
};
$empresa_suspensa = ($usuario['empresa_status'] ?? '') === 'suspensa';

$empresa_id = (int)($usuario['empresa_id'] ?? 0);
$lugar_id   = (int)($usuario['lugar_id']   ?? 0);
$plano      = $usuario['plano_ativo'] ?? 'essencial';

// Dados completos do lugar
$lugar = DB::row(
    'SELECT l.*, c.label AS cat_nome
     FROM lugares l
     LEFT JOIN categorias c ON c.id = l.categoria_id
     WHERE l.id = ? AND l.empresa_id = ?',
    [$lugar_id, $empresa_id]
);
// Se lugar não encontrado mas empresa existe, mostra painel vazio
if (!$lugar && $empresa_id) {
    $lugar = ['nome'=>'', 'slug'=>'', 'ativo'=>0, 'foto_principal'=>null,
              'cat_nome'=>'', 'endereco'=>'', 'telefone'=>'', 'whatsapp'=>'',
              'email'=>'', 'site'=>'', 'instagram'=>'', 'facebook'=>'',
              'categoria_id'=>null, 'rating'=>0, 'total_reviews'=>0,
              'google_place_id'=>null, 'descricao'=>'', 'empresa_id'=>$empresa_id];
}

if (!$lugar) {
    header('Location: /empresa/onboarding.php'); exit;
}

// Dados de suporte
$fotos      = DB::query('SELECT id, url, principal FROM fotos WHERE lugar_id=? ORDER BY principal DESC, ordem ASC', [$lugar_id]);
$tags       = array_column(DB::query('SELECT t.label FROM lugar_tags lt JOIN tags t ON t.id=lt.tag_id WHERE lt.lugar_id=?', [$lugar_id]), 'label');
$horarios   = [];
$dias_nomes = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
foreach (DB::query('SELECT * FROM horarios WHERE lugar_id=? ORDER BY dia_semana', [$lugar_id]) as $h) {
    $horarios[$dias_nomes[$h['dia_semana']]] = $h['fechado'] ? 'Fechado'
        : ($h['dia_todo'] ? 'Dia todo' : substr($h['hora_abre'],0,5).'h – '.substr($h['hora_fecha'],0,5).'h');
}

// Configuração de features por plano
$features = [
    'essencial'    => ['max_fotos'=>1,  'max_tags'=>0,  'mapa'=>false,'google'=>false,'whatsapp'=>false,'destaque'=>false,'sem_anuncios'=>false],
    'profissional' => ['max_fotos'=>5,  'max_tags'=>5,  'mapa'=>true, 'google'=>true, 'whatsapp'=>true, 'destaque'=>false,'sem_anuncios'=>false],
    'premium'      => ['max_fotos'=>999,'max_tags'=>999,'mapa'=>true, 'google'=>true, 'whatsapp'=>true, 'destaque'=>true, 'sem_anuncios'=>true],
];
$plan_feat = $features[$plano] ?? $features['essencial'];

// Cálculo de completude do perfil
$completude = [
    ['campo'=>'nome',      'label'=>'Nome da empresa',     'peso'=>15, 'ok'=>!empty($lugar['nome'])],
    ['campo'=>'descricao', 'label'=>'Descrição',           'peso'=>15, 'ok'=>!empty($lugar['descricao'])],
    ['campo'=>'endereco',  'label'=>'Endereço',            'peso'=>10, 'ok'=>!empty($lugar['endereco'])],
    ['campo'=>'telefone',  'label'=>'Telefone',            'peso'=>10, 'ok'=>!empty($lugar['telefone'])],
    ['campo'=>'horarios',  'label'=>'Horários',            'peso'=>10, 'ok'=>!empty($horarios)],
    ['campo'=>'foto',      'label'=>'Foto de capa',        'peso'=>20, 'ok'=>!empty($lugar['foto_principal']) || !empty($fotos)],
    ['campo'=>'categoria', 'label'=>'Categoria',           'peso'=>10, 'ok'=>!empty($lugar['categoria_id'])],
    ['campo'=>'whatsapp',  'label'=>'WhatsApp',            'peso'=>10, 'ok'=>!empty($lugar['whatsapp'])],
];
$pct_total = array_sum(array_column(array_filter($completude, fn($i) => $i['ok']), 'peso'));
$incompletos = array_filter($completude, fn($i) => !$i['ok']);

// Próximo plano
$planos_ordem = ['essencial','profissional','premium'];
$idx_atual    = array_search($plano, $planos_ordem);
$proximo_plano = $planos_ordem[$idx_atual + 1] ?? null;

$page_title = 'Minha empresa — Guia Campo Belo';
$hero_img   = $lugar['foto_principal'] ?? (!empty($fotos) ? $fotos[0]['url'] : '');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
<script>
/* Força header opaco imediatamente nestas páginas de painel */
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('site-header')?.classList.add('header-scrolled');
});
</script>
  <style>
    .dash-wrap { max-width: 960px; margin: 0 auto; padding: 0 1.25rem 3rem; }
    .dash-main-grid { grid-template-columns: 1fr 1fr; }
    @media(max-width: 680px) { .dash-main-grid { grid-template-columns: 1fr; } }

    /* Hero da empresa — full width sem interferência do Bootstrap */
    body { background: var(--gcb-offwhite); overflow-x: hidden; padding-top: 72px; }
    .empresa-hero {
      position: relative;
      width: 100vw;
      margin-left: calc(-50vw + 50%);
      height: 220px;
      overflow: hidden;
      background: var(--gcb-green);
      margin-bottom: 0;
    }
    .empresa-hero img { width: 100%; height: 100%; object-fit: cover; object-position: center; }
    .empresa-hero-overlay { position: absolute; inset: 0; background: linear-gradient(180deg,transparent 30%,rgba(29,29,27,.65)); }
    .empresa-hero-empty {
      width: 100vw;
      margin-left: calc(-50vw + 50%);
      height: 220px;
      background: linear-gradient(135deg, var(--gcb-green-dark), var(--gcb-green));
      display: flex;
      align-items: center;
      justify-content: center;
      color: rgba(255,255,255,.3);
      font-size: 13px;
    }

    /* Info header */
    .empresa-info-bar {
      background: #fff;
      border-radius: 20px;
      border: 1px solid rgba(61,71,51,.07);
      padding: 20px;
      margin-bottom: 16px;
      display: flex;
      align-items: flex-start;
      gap: 16px;
      box-shadow: 0 2px 12px rgba(29,29,27,.05);
    }
    .empresa-logo-wrap {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      overflow: hidden;
      flex-shrink: 0;
      background: var(--gcb-offwhite);
      border: 1px solid rgba(61,71,51,.07);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--gcb-warmgray);
      font-size: 22px;
      font-weight: 800;
    }
    .empresa-logo-wrap img { width: 100%; height: 100%; object-fit: cover; }

    /* Cards */
    .dash-card {
      background: #fff;
      border-radius: 20px;
      border: 1px solid rgba(61,71,51,.07);
      padding: 20px;
      margin-bottom: 14px;
      box-shadow: 0 2px 10px rgba(29,29,27,.04);
    }
    .dash-card-title {
      font-size: 13px;
      font-weight: 800;
      color: var(--gcb-green-dark);
      margin-bottom: 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .dash-card-title a {
      font-size: 11px;
      font-weight: 700;
      color: var(--gcb-gold);
      text-decoration: none;
    }
    .dash-card-title a:hover { color: var(--gcb-green); }

    /* Métricas */
    .metric-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
    @media(max-width:480px) { .metric-grid { grid-template-columns: 1fr 1fr; } }
    .metric-box {
      background: var(--gcb-offwhite);
      border-radius: 12px;
      padding: 14px 12px;
      text-align: center;
    }
    .metric-val { font-size: 24px; font-weight: 800; color: var(--gcb-green-dark); line-height: 1; }
    .metric-lbl { font-size: 10px; font-weight: 600; color: var(--gcb-warmgray); margin-top: 4px; text-transform: uppercase; letter-spacing: .06em; }
    .metric-coming { font-size: 10px; color: var(--gcb-warmgray); margin-top: 4px; }

    /* Completude */
    .pct-bar { height: 6px; background: var(--gcb-offwhite); border-radius: 3px; overflow: hidden; margin: 8px 0 12px; }
    .pct-fill { height: 100%; border-radius: 3px; background: var(--gcb-green-dark); transition: width .5s; }
    .completude-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 0;
      border-bottom: .5px solid rgba(61,71,51,.07);
      font-size: 12px;
    }
    .completude-item:last-child { border-bottom: none; }
    .completude-ok   { color: var(--gcb-green); }
    .completude-pend { color: var(--gcb-warmgray); }

    /* Info rows */
    .info-row {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      padding: 9px 0;
      border-bottom: .5px solid rgba(61,71,51,.07);
      font-size: 13px;
    }
    .info-row:last-child { border-bottom: none; }
    .info-row-icon { color: var(--gcb-gold); flex-shrink: 0; margin-top: 1px; }
    .info-row-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--gcb-warmgray); }
    .info-row-val { color: var(--gcb-graphite); font-weight: 500; margin-top: 2px; }

    /* Plano card */
    .plano-card {
      border-radius: 16px;
      padding: 18px;
      margin-bottom: 10px;
    }
    .plano-atual { background: var(--gcb-green-dark); }
    .plano-prox  { background: var(--gcb-offwhite); border: 1px solid rgba(61,71,51,.1); }
    .feat-row { display: flex; align-items: center; gap: 6px; font-size: 12px; padding: 3px 0; }
    .feat-ok { color: #34d399; }
    .feat-no { color: rgba(255,255,255,.2); }
    .feat-no-light { color: rgba(61,71,51,.2); }

    /* Galeria */
    .foto-grid-dash {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
      gap: 8px;
    }
    .foto-thumb-dash {
      aspect-ratio: 1;
      border-radius: 10px;
      overflow: hidden;
      background: var(--gcb-offwhite);
    }
    .foto-thumb-dash img { width: 100%; height: 100%; object-fit: cover; }
    .foto-add {
      aspect-ratio: 1;
      border-radius: 10px;
      border: 2px dashed rgba(61,71,51,.15);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      color: var(--gcb-warmgray);
      font-size: 22px;
      transition: all .2s;
      text-decoration: none;
    }
    .foto-add:hover { border-color: var(--gcb-gold); color: var(--gcb-gold); background: var(--gcb-gold-pale); }

    /* Upgrade banner */
    .upgrade-banner {
      background: linear-gradient(135deg, var(--gcb-green-dark), var(--gcb-green));
      border-radius: 16px;
      padding: 20px;
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 14px;
    }
    .upgrade-icon {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      background: rgba(201,170,107,.2);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--gcb-gold);
      flex-shrink: 0;
    }

    /* Horários grid */
    .hora-grid { display: grid; grid-template-columns: auto 1fr; gap: 6px 12px; font-size: 12px; }
    .hora-dia { font-weight: 700; color: var(--gcb-graphite); }
    .hora-val { color: var(--gcb-warmgray); }
    .hora-hoje { color: var(--gcb-green); font-weight: 700; }

    /* Quick actions */
    .quick-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
    .qa-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 14px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 700;
      text-decoration: none;
      transition: all .15s;
      border: 1.5px solid transparent;
    }
    .qa-primary { background: var(--gcb-green-dark); color: #fff; }
    .qa-primary:hover { background: var(--gcb-green); color: #fff; }
    .qa-outline { border-color: rgba(61,71,51,.2); color: var(--gcb-graphite); background: #fff; }
    .qa-outline:hover { border-color: var(--gcb-gold); color: var(--gcb-green-dark); }
    .qa-gold { background: var(--gcb-gold); color: var(--gcb-green-dark); }
    .qa-gold:hover { background: var(--gcb-gold-light); }
  </style>
</head>
<body>

<?php include __DIR__ . '/../includes/search-modal.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- ── Hero ── -->
<?php if ($hero_img): ?>
<div class="empresa-hero">
  <img src="<?= Sanitize::html($hero_img) ?>" alt="">
  <div class="empresa-hero-overlay"></div>
</div>
<?php else: ?>
<div class="empresa-hero-empty">
  <span>Adicione uma foto de capa</span>
</div>
<?php endif; ?>

<div class="dash-wrap" style="margin-top:16px">

  <!-- ── Info header ── -->
  <div class="empresa-info-bar">
    <div class="empresa-logo-wrap">
      <?php if ($hero_img): ?>
      <img src="<?= Sanitize::html($hero_img) ?>" alt="">
      <?php else: ?>
      <?= mb_strtoupper(mb_substr($lugar['nome'] ?? 'E', 0, 1)) ?>
      <?php endif; ?>
    </div>
    <div style="flex:1;min-width:0">
      <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px">
        <h1 style="font-size:18px;font-weight:800;color:var(--gcb-green-dark);margin:0">
          <?= Sanitize::html($lugar['nome'] ?? '') ?>
        </h1>
        <?php
        $plano_colors = [
          'essencial'    => 'background:var(--gcb-offwhite);color:var(--gcb-warmgray)',
          'profissional' => 'background:#eff6ff;color:#1d4ed8',
          'premium'      => 'background:var(--gcb-gold-pale);color:var(--gcb-green-dark)',
        ];
        ?>
        <span style="font-size:10px;font-weight:800;padding:3px 10px;border-radius:999px;
                     <?= $plano_colors[$plano] ?? $plano_colors['essencial'] ?>">
          <?= ucfirst($plano) ?>
        </span>
        <?php if ($lugar['ativo']): ?>
        <span style="display:flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:#059669">
          <span style="width:6px;height:6px;border-radius:50%;background:#34d399;display:inline-block"></span>
          Publicada
        </span>
        <?php endif; ?>
      </div>
      <div style="font-size:12px;color:var(--gcb-warmgray)">
        <?= Sanitize::html($lugar['cat_nome'] ?? '') ?>
        <?php if ($lugar['endereco']): ?>
        · <?= Sanitize::html($lugar['endereco']) ?>
        <?php endif; ?>
      </div>
    </div>
    <div class="quick-actions" style="margin-bottom:0;flex-shrink:0">
      <a href="/empresa/editar.php" class="qa-btn qa-outline">
        <?= icon('sliders', 13) ?> Editar
      </a>
    </div>
  </div>

  <!-- ── Banner de suspensão ── -->
  <?php if ($empresa_suspensa): ?>
  <div style="background:#fef3c7;border:1.5px solid #fcd34d;border-radius:14px;padding:14px 18px;
              margin-bottom:14px;display:flex;align-items:center;gap:12px">
    <span style="font-size:20px">⚠️</span>
    <div>
      <div style="font-size:13px;font-weight:700;color:#92400e">Empresa suspensa</div>
      <div style="font-size:12px;color:#78350f;margin-top:2px">
        Sua página não está visível ao público. Entre em contato com nossa equipe para mais informações.
        <a href="https://wa.me/5511999999999" target="_blank"
           style="color:#92400e;font-weight:700"> Falar no WhatsApp →</a>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── Quick actions ── -->
  <div class="quick-actions">
    <a href="/empresa/editar.php" class="qa-btn qa-primary">
      <?= icon('sliders', 13) ?> Editar empresa
    </a>
    <?php if ($lugar['slug']): ?>
    <a href="/pages/lugar.php?slug=<?= Sanitize::html($lugar['slug']) ?>"
       target="_blank" class="qa-btn qa-outline">
      <?= icon('external-link', 13) ?> Ver página pública
    </a>
    <?php endif; ?>
    <?php if ($plan_feat['google'] && $lugar['google_place_id']): ?>
    <a href="#" onclick="syncGoogle()" class="qa-btn qa-outline">
      <?= icon('verified', 13) ?> Sincronizar Google
    </a>
    <?php endif; ?>
    <?php if ($proximo_plano): ?>
    <a href="/empresa/plano.php" class="qa-btn qa-gold">
      <?= icon('trending-up', 13) ?> Fazer upgrade
    </a>
    <?php endif; ?>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px" class="dash-main-grid">

    <!-- ── Coluna esquerda ── -->
    <div style="display:flex;flex-direction:column;gap:14px">

      <!-- Métricas -->
      <div class="dash-card d-none">
        <div class="dash-card-title">
          Visibilidade
          <span style="font-size:10px;font-weight:500;color:var(--gcb-warmgray)">Em breve</span>
        </div>
        <div class="metric-grid">
          <div class="metric-box">
            <div class="metric-val" style="color:var(--gcb-warmgray)">—</div>
            <div class="metric-lbl">Visualizações</div>
            <div class="metric-coming">em breve</div>
          </div>
          <div class="metric-box">
            <div class="metric-val" style="color:var(--gcb-warmgray)">—</div>
            <div class="metric-lbl">Cliques WhatsApp</div>
            <div class="metric-coming">em breve</div>
          </div>
          <div class="metric-box">
            <?php if ($lugar['rating'] > 0): ?>
            <div class="metric-val"><?= number_format($lugar['rating'],1) ?></div>
            <div class="metric-lbl">Nota Google</div>
            <div class="metric-coming"><?= (int)$lugar['total_reviews'] ?> avaliações</div>
            <?php else: ?>
            <div class="metric-val" style="color:var(--gcb-warmgray)">—</div>
            <div class="metric-lbl">Nota Google</div>
            <div class="metric-coming">sem dados</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Completude do perfil -->
      <div class="dash-card">
        <div class="dash-card-title">
          Perfil
          <a href="/empresa/editar.php">Completar →</a>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
          <span style="font-size:13px;font-weight:700;color:var(--gcb-graphite)"><?= $pct_total ?>% completo</span>
          <?php if ($pct_total < 80): ?>
          <span style="font-size:10px;color:var(--gcb-gold);font-weight:600">Complete para mais visibilidade</span>
          <?php endif; ?>
        </div>
        <div class="pct-bar">
          <div class="pct-fill" style="width:<?= $pct_total ?>%;background:<?= $pct_total >= 80 ? '#34d399' : 'var(--gcb-gold)' ?>"></div>
        </div>
        <?php foreach ($completude as $item): ?>
        <div class="completude-item">
          <span style="color:var(--gcb-graphite)"><?= $item['label'] ?></span>
          <?php if ($item['ok']): ?>
          <span class="completude-ok">✓</span>
          <?php else: ?>
          <a href="/empresa/editar.php" style="font-size:11px;font-weight:700;color:var(--gcb-gold);text-decoration:none">
            Adicionar
          </a>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Informações da empresa -->
      <div class="dash-card">
        <div class="dash-card-title">
          Informações
          <a href="/empresa/editar.php">Editar</a>
        </div>
        <?php
        $infos = [
          ['icon'=>'pin',       'label'=>'Endereço',   'val'=>$lugar['endereco']],
          ['icon'=>'phone',     'label'=>'Telefone',   'val'=>$lugar['telefone']],
          ['icon'=>'whatsapp',  'label'=>'WhatsApp',   'val'=>$lugar['whatsapp']],
          ['icon'=>'mail',      'label'=>'E-mail',     'val'=>$lugar['email']],
          ['icon'=>'external-link','label'=>'Site',    'val'=>$lugar['site']],
          ['icon'=>'instagram', 'label'=>'Instagram',  'val'=>$lugar['instagram']],
        ];
        foreach ($infos as $info):
          if (!$info['val']) continue;
        ?>
        <div class="info-row">
          <span class="info-row-icon"><?= icon($info['icon'], 14) ?></span>
          <div>
            <div class="info-row-label"><?= $info['label'] ?></div>
            <div class="info-row-val"><?= Sanitize::html($info['val']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (!$lugar['endereco'] && !$lugar['telefone']): ?>
        <a href="/empresa/editar.php" style="font-size:12px;color:var(--gcb-gold)">
          Adicionar informações de contato →
        </a>
        <?php endif; ?>
      </div>

    </div>

    <!-- ── Coluna direita ── -->
    <div style="display:flex;flex-direction:column;gap:14px">

      <!-- Plano atual -->
      <div class="dash-card" style="padding:0;overflow:hidden">
        <div class="plano-card plano-atual">
          <div style="font-size:10px;font-weight:700;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.1em;margin-bottom:4px">Plano atual</div>
          <div style="font-size:20px;font-weight:800;color:#fff;margin-bottom:12px"><?= ucfirst($plano) ?></div>
          <?php
          $feat_plano = [
            ['ok'=>true,  'label'=>'Página da empresa'],
            ['ok'=>true,  'label'=>'Nome, endereço e contato'],
            ['ok'=>$plan_feat['max_fotos']>1,  'label'=>'Galeria de fotos'],
            ['ok'=>$plan_feat['mapa'],          'label'=>'Pin no mapa'],
            ['ok'=>$plan_feat['google'],        'label'=>'Google Reviews'],
            ['ok'=>$plan_feat['whatsapp'],      'label'=>'Botão WhatsApp'],
            ['ok'=>$plan_feat['destaque'],      'label'=>'Selo Destaque'],
            ['ok'=>$plan_feat['sem_anuncios'],  'label'=>'Sem propagandas'],
          ];
          foreach ($feat_plano as $f): ?>
          <div class="feat-row">
            <span class="<?= $f['ok'] ? 'feat-ok' : 'feat-no' ?>">
              <?= $f['ok'] ? '✓' : '–' ?>
            </span>
            <span style="color:<?= $f['ok'] ? 'rgba(255,255,255,.85)' : 'rgba(255,255,255,.25)' ?>;font-size:12px">
              <?= $f['label'] ?>
            </span>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if ($proximo_plano): ?>
        <div class="plano-card plano-prox" style="margin:0;border-radius:0">
          <div style="font-size:10px;font-weight:700;color:var(--gcb-warmgray);text-transform:uppercase;letter-spacing:.1em;margin-bottom:4px">
            Upgrade: <?= ucfirst($proximo_plano) ?>
          </div>
          <?php
          $feat_prox = $features[$proximo_plano];
          $novidades = [
            $feat_prox['max_fotos'] > $plan_feat['max_fotos'] => $proximo_plano === 'premium' ? 'Fotos ilimitadas' : 'Até 5 fotos',
            $feat_prox['mapa']      && !$plan_feat['mapa']    => 'Pin no mapa interativo',
            $feat_prox['google']    && !$plan_feat['google']  => 'Google Reviews automático',
            $feat_prox['whatsapp']  && !$plan_feat['whatsapp']=> 'Botão WhatsApp',
            $feat_prox['destaque']  && !$plan_feat['destaque']=> 'Selo Destaque',
            $feat_prox['sem_anuncios'] && !$plan_feat['sem_anuncios'] => 'Sem propagandas',
          ];
          foreach ($novidades as $cond => $label):
            if (!$cond) continue;
          ?>
          <div class="feat-row">
            <span style="color:var(--gcb-gold)">+</span>
            <span style="font-size:12px;color:var(--gcb-graphite)"><?= $label ?></span>
          </div>
          <?php endforeach; ?>
          <a href="/empresa/plano.php"
             class="btn-gold d-flex align-items-center justify-content-center gap-2 py-2 mt-3"
             style="font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;border-radius:999px;text-decoration:none">
            Solicitar upgrade <?= icon('arrow-right', 11) ?>
          </a>
        </div>
        <?php endif; ?>
      </div>

      <!-- Galeria -->
      <div class="dash-card">
        <div class="dash-card-title">
          Fotos
          <span style="font-size:11px;font-weight:500;color:var(--gcb-warmgray)">
            <?= count($fotos) ?>/<?= $plan_feat['max_fotos'] >= 999 ? '∞' : $plan_feat['max_fotos'] ?>
          </span>
        </div>
        <div class="foto-grid-dash">
          <?php foreach ($fotos as $foto): ?>
          <div class="foto-thumb-dash">
            <img src="<?= Sanitize::html($foto['url']) ?>" alt="">
          </div>
          <?php endforeach; ?>
          <?php if (count($fotos) < $plan_feat['max_fotos']): ?>
          <a href="/empresa/editar.php#fotos" class="foto-add">+</a>
          <?php endif; ?>
        </div>
        <?php if (empty($fotos)): ?>
        <p style="font-size:12px;color:var(--gcb-warmgray);margin-top:8px">
          Nenhuma foto adicionada.
          <a href="/empresa/editar.php" style="color:var(--gcb-gold)">Adicionar agora</a>
        </p>
        <?php endif; ?>
      </div>

      <!-- Horários -->
      <?php if (!empty($horarios)): ?>
      <div class="dash-card">
        <div class="dash-card-title">
          Horários
          <a href="/empresa/editar.php">Editar</a>
        </div>
        <div class="hora-grid">
          <?php
          $dia_hoje = $dias_nomes[date('w')];
          foreach ($dias_nomes as $dia):
            $hora = $horarios[$dia] ?? 'Não informado';
            $hoje = $dia === $dia_hoje;
          ?>
          <span class="hora-dia <?= $hoje ? 'hora-hoje' : '' ?>"><?= $dia ?></span>
          <span class="hora-val <?= $hoje ? 'hora-hoje' : '' ?>"><?= htmlspecialchars($hora) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Tags -->
      <?php if (!empty($tags)): ?>
      <div class="dash-card">
        <div class="dash-card-title">Tags</div>
        <div style="display:flex;flex-wrap:wrap;gap:6px">
          <?php foreach ($tags as $tag): ?>
          <span class="tag-pill"><?= Sanitize::html($tag) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

    </div>

  </div><!-- /grid -->

  <!-- ── Upgrade banner (só Essencial) ── -->
  <?php if ($plano === 'essencial'): ?>
  <div class="upgrade-banner">
    <div class="upgrade-icon"><?= icon('trending-up', 20) ?></div>
    <div style="flex:1">
      <div style="font-size:13px;font-weight:800;color:#fff;margin-bottom:2px">
        Apareça para muito mais pessoas
      </div>
      <div style="font-size:12px;color:rgba(255,255,255,.55)">
        Com o plano Profissional você tem pin no mapa, WhatsApp, galeria de fotos e Google Reviews.
      </div>
    </div>
    <a href="/empresa/plano.php"
       class="btn-gold d-flex align-items-center gap-2"
       style="font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;border-radius:999px;text-decoration:none;white-space:nowrap;flex-shrink:0">
      Ver planos <?= icon('arrow-right', 11) ?>
    </a>
  </div>
  <?php endif; ?>

</div><!-- /dash-wrap -->

<script>
<?php if ($plan_feat['google'] && $lugar['google_place_id']): ?>
async function syncGoogle() {
  const r = await fetch('/api/google-sync-empresa.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
      _token: '<?= Sanitize::html(Sanitize::csrfToken()) ?>',
      lugar_id: <?= $lugar_id ?>
    })
  });
  const d = await r.json();
  alert(d.ok ? '✓ Sincronizado! Nota: ' + d.rating : '✗ ' + (d.erro || 'Erro.'));
}
<?php endif; ?>
</script>

<footer style="text-align:center;padding:2rem 1rem;font-size:11px;color:var(--gcb-warmgray)">
  &copy; <?= date('Y') ?> Guia Campo Belo &amp; Região &mdash;
  <a href="/" style="color:var(--gcb-gold)">Voltar ao site</a>
</footer>
</body>
</html>