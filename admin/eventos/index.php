<?php
/**
 * admin/eventos/index.php
 * Lista de eventos
 */
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

Auth::require();

$page_title  = 'Eventos';
$active_menu = '/admin/eventos/index.php';

// Busca contagens de cada evento
$total_inscritos = (int)(DB::row(
    'SELECT COUNT(*) n FROM evento_inscricoes
     WHERE evento_id = "guia-connect-soft-opening-mai25"'
)['n'] ?? 0);

$total_leads = (int)(DB::row(
    'SELECT COUNT(*) n FROM evento_leads
     WHERE evento_id = "guia-connect-soft-opening-mai25"'
)['n'] ?? 0);

$eventos = [
    [
        'id'          => 'guia-connect-soft-opening-mai25',
        'nome'        => 'Guia Connect — Soft Opening',
        'data'        => '19 de maio de 2025',
        'horario'     => 'A partir das 19h',
        'local'       => 'Cris Parilla — Campo Belo',
        'valor'       => 'R$ 59,00',
        'status'      => 'ativo',
        'inscritos'   => $total_inscritos,
        'leads'       => $total_leads,
        'url_cadastro'=> '/pages/evento-cadastro',
        'url_gestao'  => '/admin/evento-gerar-qrcode.php',
    ],
];

include __DIR__ . '/../../admin/_layout.php';
?>

<style>
  .ev-card {
    background: #fff; border-radius: 16px;
    box-shadow: 0 2px 16px rgba(29,29,27,.06);
    overflow: hidden; margin-bottom: 20px;
  }
  .ev-card-header {
    background: var(--green-dk); padding: 20px 24px;
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
  }
  .ev-card-header h3 {
    font-size: 16px; font-weight: 800; color: #fff; margin: 0;
  }
  .ev-card-header p {
    font-size: 11px; font-weight: 300; color: rgba(255,255,255,.5); margin: 4px 0 0;
  }
  .ev-status {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 12px; border-radius: 999px;
    font-size: 10px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase;
  }
  .ev-status.ativo   { background: rgba(201,170,107,.2); color: var(--gold-lt); }
  .ev-status.inativo { background: rgba(255,255,255,.1); color: rgba(255,255,255,.4); }
  .ev-status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

  .ev-card-body { padding: 20px 24px; }

  .ev-stats {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 12px; margin-bottom: 20px;
  }
  .ev-stat {
    background: var(--offwhite); border-radius: 10px; padding: 14px 16px; text-align: center;
  }
  .ev-stat-num { font-size: 28px; font-weight: 800; color: var(--green-dk); line-height: 1; }
  .ev-stat-label {
    font-size: 10px; font-weight: 700; letter-spacing: .1em;
    text-transform: uppercase; color: var(--warmgray); margin-top: 4px;
  }

  .ev-meta {
    display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;
  }
  .ev-meta-item {
    display: flex; align-items: center; gap: 7px;
    background: var(--offwhite); border-radius: 8px; padding: 7px 12px;
    font-size: 12px; font-weight: 600; color: var(--green-dk);
  }
  .ev-meta-item svg { color: var(--gold); flex-shrink: 0; }

  .ev-actions { display: flex; gap: 10px; flex-wrap: wrap; }
  .btn-ev {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 18px; border-radius: 9px; border: none; cursor: pointer;
    font-family: 'Montserrat', sans-serif; font-size: 11px; font-weight: 800;
    letter-spacing: .06em; text-transform: uppercase; text-decoration: none;
    transition: opacity .15s;
  }
  .btn-ev:hover { opacity: .85; }
  .btn-ev-green { background: var(--green-dk); color: #fff; }
  .btn-ev-gold  { background: var(--gold); color: var(--green-dk); }
  .btn-ev-outline {
    background: transparent; color: var(--green-dk);
    border: 1.5px solid rgba(61,71,51,.2);
  }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
  <div>
    <h2 style="font-size:20px;font-weight:800;color:var(--green-dk);margin:0">Eventos</h2>
    <p style="font-size:13px;color:var(--warmgray);margin:4px 0 0">
      Gerencie inscrições e ingressos dos eventos.
    </p>
  </div>
</div>

<?php foreach ($eventos as $ev): ?>
<div class="ev-card">

  <!-- Header -->
  <div class="ev-card-header">
    <div>
      <h3><?= htmlspecialchars($ev['nome']) ?></h3>
      <p><?= htmlspecialchars($ev['data']) ?> · <?= htmlspecialchars($ev['local']) ?></p>
    </div>
    <div class="ev-status <?= $ev['status'] ?>">
      <span class="ev-status-dot"></span>
      <?= $ev['status'] ?>
    </div>
  </div>

  <!-- Body -->
  <div class="ev-card-body">

    <!-- Stats -->
    <div class="ev-stats">
      <div class="ev-stat">
        <div class="ev-stat-num"><?= $ev['inscritos'] ?></div>
        <div class="ev-stat-label">Inscritos pagos</div>
      </div>
      <div class="ev-stat">
        <div class="ev-stat-num"><?= $ev['leads'] ?></div>
        <div class="ev-stat-label">Leads captados</div>
      </div>
      <div class="ev-stat">
        <div class="ev-stat-num">
          <?= $ev['leads'] > 0
            ? round(($ev['inscritos'] / $ev['leads']) * 100) . '%'
            : '—' ?>
        </div>
        <div class="ev-stat-label">Conversão</div>
      </div>
    </div>

    <!-- Meta -->
    <div class="ev-meta">
      <div class="ev-meta-item">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <?= htmlspecialchars($ev['data']) ?>
      </div>
      <div class="ev-meta-item">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <?= htmlspecialchars($ev['horario']) ?>
      </div>
      <div class="ev-meta-item">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <?= htmlspecialchars($ev['local']) ?>
      </div>
      <div class="ev-meta-item">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        <?= htmlspecialchars($ev['valor']) ?>
      </div>
    </div>

    <!-- Ações -->
    <div class="ev-actions">
      <a href="<?= $ev['url_gestao'] ?>" class="btn-ev btn-ev-green">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Gerenciar ingressos
      </a>
      <a href="<?= $ev['url_cadastro'] ?>" target="_blank" class="btn-ev btn-ev-outline">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        Ver página do evento
      </a>
    </div>

  </div>
</div>
<?php endforeach; ?>

</div><!-- /page-content -->
    </main>
</div><!-- /admin-wrapper -->
<?php include __DIR__ . '/../../admin/_layout_end.php'; ?>