<?php
/**
 * admin/assinaturas/index.php
 * Gestão centralizada de assinaturas Asaas
 */
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

Auth::require();

$page_title = 'Assinaturas';

// ── Mini dashboard financeiro ──
$total_ativos = (int)(DB::row(
    'SELECT COUNT(*) n FROM empresas WHERE plano_ativo IN ("profissional","premium")'
)['n'] ?? 0);

$total_prof = (int)(DB::row(
    'SELECT COUNT(*) n FROM empresas WHERE plano_ativo = "profissional"'
)['n'] ?? 0);

$total_prem = (int)(DB::row(
    'SELECT COUNT(*) n FROM empresas WHERE plano_ativo = "premium"'
)['n'] ?? 0);

$mrr = ($total_prof * 89) + ($total_prem * 159);

$total_mes = (float)(DB::row(
    'SELECT COALESCE(SUM(valor),0) n FROM asaas_pagamentos
     WHERE status IN ("CONFIRMED","RECEIVED")
     AND MONTH(pago_em) = MONTH(NOW())
     AND YEAR(pago_em)  = YEAR(NOW())'
)['n'] ?? 0);

$total_inadim = (int)(DB::row(
    'SELECT COUNT(DISTINCT empresa_id) n FROM asaas_pagamentos
     WHERE status = "OVERDUE"'
)['n'] ?? 0);

// ── Filtros ──
$filtro_plano  = Sanitize::get('plano',  'str', '');
$filtro_status = Sanitize::get('status', 'str', '');
$q             = Sanitize::get('q',      'str', '');

$where  = ['e.plano_ativo IN ("profissional","premium")'];
$params = [];

if ($filtro_plano) {
    $where[]  = 'e.plano_ativo = ?';
    $params[] = $filtro_plano;
}

if ($q) {
    $where[]  = '(u.nome LIKE ? OR u.email LIKE ? OR l.nome LIKE ?)';
    $like     = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$whereSQL = implode(' AND ', $where);

$assinaturas = DB::query(
    "SELECT
        e.id            AS empresa_id,
        e.plano_ativo,
        e.asaas_subscription_id,
        e.asaas_customer_id,
        u.nome          AS usuario_nome,
        u.email         AS usuario_email,
        l.nome          AS empresa_nome,
        -- Último pagamento confirmado
        (SELECT p.pago_em FROM asaas_pagamentos p
         WHERE p.empresa_id = e.id
         AND p.status IN ('CONFIRMED','RECEIVED')
         ORDER BY p.pago_em DESC LIMIT 1) AS ultimo_pagamento,
        -- Próximo vencimento
        (SELECT p.vencimento FROM asaas_pagamentos p
         WHERE p.empresa_id = e.id
         AND p.status = 'PENDING'
         ORDER BY p.vencimento ASC LIMIT 1) AS proximo_vencimento,
        -- Tem inadimplência?
        (SELECT COUNT(*) FROM asaas_pagamentos p
         WHERE p.empresa_id = e.id
         AND p.status = 'OVERDUE') AS qtd_overdue
     FROM empresas e
     JOIN usuarios u ON u.id = e.usuario_id
     LEFT JOIN lugares l ON l.id = e.lugar_id
     WHERE {$whereSQL}
     ORDER BY e.plano_ativo DESC, u.nome ASC",
    $params
);

// Filtra inadimplentes no PHP (mais simples)
if ($filtro_status === 'overdue') {
    $assinaturas = array_filter($assinaturas, fn($a) => $a['qtd_overdue'] > 0);
} elseif ($filtro_status === 'ok') {
    $assinaturas = array_filter($assinaturas, fn($a) => $a['qtd_overdue'] == 0);
}

include __DIR__ . '/../../admin/_layout.php';
?>

<style>
  .stat-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid rgba(61,71,51,.08);
    padding: 20px 24px;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
  }
  .stat-val  { font-size: 28px; font-weight: 800; color: var(--green-dk); line-height: 1; }
  .stat-lbl  { font-size: 11px; font-weight: 600; color: var(--warmgray);
               text-transform: uppercase; letter-spacing: .08em; margin-top: 4px; }
  .stat-sub  { font-size: 12px; color: var(--warmgray); margin-top: 2px; }

  .badge-prof    { background: #eff6ff; color: #1d4ed8; }
  .badge-premium { background: #f5edda; color: #2a3022; }
  .badge-overdue { background: #fef2f2; color: #dc2626; }
  .badge-ok      { background: #ecfdf5; color: #065f46; }

  .data-table th { font-size: 11px; font-weight: 700; text-transform: uppercase;
                   letter-spacing: .06em; color: var(--warmgray); border-bottom: 1px solid rgba(61,71,51,.08); }
  .data-table td { font-size: 13px; vertical-align: middle;
                   border-bottom: 1px solid rgba(61,71,51,.05); padding: 12px 16px; }
  .data-table tr:last-child td { border-bottom: none; }
  .data-table tr:hover td { background: var(--offwhite); }
</style>

<!-- Mini dashboard -->
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:28px">

  <div class="stat-card">
    <div class="stat-val"><?= $total_ativos ?></div>
    <div class="stat-lbl">Assinaturas ativas</div>
  </div>

  <div class="stat-card">
    <div class="stat-val"><?= $total_prof ?></div>
    <div class="stat-lbl">Profissional</div>
    <div class="stat-sub">R$ 89/mês cada</div>
  </div>

  <div class="stat-card">
    <div class="stat-val"><?= $total_prem ?></div>
    <div class="stat-lbl">Premium</div>
    <div class="stat-sub">R$ 159/mês cada</div>
  </div>

  <div class="stat-card">
    <div class="stat-val" style="color:var(--gold)">
      R$ <?= number_format($mrr, 0, ',', '.') ?>
    </div>
    <div class="stat-lbl">MRR estimado</div>
    <div class="stat-sub">Receita mensal recorrente</div>
  </div>

  <div class="stat-card">
    <div class="stat-val" style="color:<?= $total_inadim > 0 ? '#dc2626' : 'var(--green-dk)' ?>">
      <?= $total_inadim ?>
    </div>
    <div class="stat-lbl">Inadimplentes</div>
    <div class="stat-sub">Cobranças em atraso</div>
  </div>

</div>

<!-- Filtros -->
<div style="background:#fff;border-radius:14px;border:1px solid rgba(61,71,51,.08);
            padding:16px 20px;margin-bottom:20px;display:flex;gap:12px;flex-wrap:wrap;align-items:center">

  <input type="text" id="filtro-q" placeholder="Buscar por nome ou e-mail…"
         value="<?= Sanitize::html($q) ?>"
         onkeydown="if(event.key==='Enter') aplicarFiltros()"
         style="flex:1;min-width:200px;border:1px solid rgba(61,71,51,.12);border-radius:8px;
                padding:8px 12px;font-size:13px;font-family:inherit">

  <select id="filtro-plano" onchange="aplicarFiltros()"
          style="border:1px solid rgba(61,71,51,.12);border-radius:8px;padding:8px 12px;
                 font-size:13px;font-family:inherit">
    <option value="" <?= !$filtro_plano ? 'selected' : '' ?>>Todos os planos</option>
    <option value="profissional" <?= $filtro_plano==='profissional' ? 'selected' : '' ?>>Profissional</option>
    <option value="premium"      <?= $filtro_plano==='premium'      ? 'selected' : '' ?>>Premium</option>
  </select>

  <select id="filtro-status" onchange="aplicarFiltros()"
          style="border:1px solid rgba(61,71,51,.12);border-radius:8px;padding:8px 12px;
                 font-size:13px;font-family:inherit">
    <option value="" <?= !$filtro_status ? 'selected' : '' ?>>Todos os status</option>
    <option value="ok"     <?= $filtro_status==='ok'     ? 'selected' : '' ?>>Em dia</option>
    <option value="overdue"<?= $filtro_status==='overdue'? 'selected' : '' ?>>Inadimplentes</option>
  </select>

</div>

<!-- Tabela -->
<div style="background:#fff;border-radius:14px;border:1px solid rgba(61,71,51,.08);overflow:hidden">
  <div style="padding:16px 20px;border-bottom:1px solid rgba(61,71,51,.06);
              display:flex;justify-content:space-between;align-items:center">
    <h2 style="font-size:14px;font-weight:700;color:var(--green-dk);margin:0">
      <?= count($assinaturas) ?> assinatura<?= count($assinaturas) !== 1 ? 's' : '' ?> encontrada<?= count($assinaturas) !== 1 ? 's' : '' ?>
    </h2>
  </div>

  <?php if (empty($assinaturas)): ?>
  <div style="padding:3rem;text-align:center;font-size:13px;color:var(--warmgray)">
    Nenhuma assinatura encontrada.
  </div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="data-table w-100">
      <thead>
        <tr>
          <th style="padding:12px 16px">Empresa / Usuário</th>
          <th style="padding:12px 16px">Plano</th>
          <th style="padding:12px 16px">Status</th>
          <th style="padding:12px 16px">Último pagamento</th>
          <th style="padding:12px 16px">Próx. vencimento</th>
          <th style="padding:12px 16px;text-align:right">Ações</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($assinaturas as $a): ?>
      <tr>
        <td>
          <div style="font-weight:600;color:var(--graphite)"><?= Sanitize::html($a['empresa_nome'] ?? $a['usuario_nome']) ?></div>
          <div style="font-size:11px;color:var(--warmgray)"><?= Sanitize::html($a['usuario_email']) ?></div>
        </td>
        <td>
          <span class="badge rounded-pill <?= $a['plano_ativo'] === 'premium' ? 'badge-premium' : 'badge-prof' ?>"
                style="font-size:11px;font-weight:700;padding:4px 10px">
            <?= ucfirst($a['plano_ativo']) ?>
          </span>
        </td>
        <td>
          <?php if ($a['qtd_overdue'] > 0): ?>
          <span class="badge rounded-pill badge-overdue" style="font-size:11px;font-weight:700;padding:4px 10px">
            ⚠ Inadimplente
          </span>
          <?php else: ?>
          <span class="badge rounded-pill badge-ok" style="font-size:11px;font-weight:700;padding:4px 10px">
            ✓ Em dia
          </span>
          <?php endif; ?>
        </td>
        <td style="color:var(--warmgray)">
          <?= $a['ultimo_pagamento'] ? date('d/m/Y', strtotime($a['ultimo_pagamento'])) : '—' ?>
        </td>
        <td style="color:var(--warmgray)">
          <?= $a['proximo_vencimento'] ? date('d/m/Y', strtotime($a['proximo_vencimento'])) : '—' ?>
        </td>
        <td style="text-align:right">
          <button onclick="verPagamentos(<?= $a['empresa_id'] ?>, '<?= Sanitize::html($a['empresa_nome'] ?? $a['usuario_nome']) ?>')"
                  style="font-size:11px;font-weight:700;padding:6px 12px;border-radius:8px;
                         border:1px solid rgba(61,71,51,.12);background:#fff;cursor:pointer;
                         color:var(--green-dk);margin-right:6px">
            Histórico
          </button>
          <?php if ($a['asaas_subscription_id']): ?>
          <button onclick="cancelarAssinatura(<?= $a['empresa_id'] ?>, '<?= Sanitize::html($a['empresa_nome'] ?? $a['usuario_nome']) ?>')"
                  style="font-size:11px;font-weight:700;padding:6px 12px;border-radius:8px;
                         border:1px solid #fecaca;background:#fef2f2;cursor:pointer;color:#dc2626">
            Cancelar
          </button>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- Modal histórico de pagamentos -->
<div id="modal-historico" style="display:none;position:fixed;inset:0;z-index:1000;
     background:rgba(0,0,0,.5);align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:20px;width:100%;max-width:640px;
              max-height:80vh;overflow:hidden;display:flex;flex-direction:column;margin:20px">
    <div style="padding:20px 24px;border-bottom:1px solid rgba(61,71,51,.08);
                display:flex;justify-content:space-between;align-items:center">
      <h3 style="font-size:16px;font-weight:700;color:var(--green-dk);margin:0"
          id="modal-titulo">Histórico de pagamentos</h3>
      <button onclick="fecharModal()"
              style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--warmgray)">×</button>
    </div>
    <div id="modal-body" style="padding:20px 24px;overflow-y:auto;flex:1;font-size:13px">
      Carregando…
    </div>
  </div>
</div>

<script>
function aplicarFiltros() {
  const q      = document.getElementById('filtro-q').value;
  const plano  = document.getElementById('filtro-plano').value;
  const status = document.getElementById('filtro-status').value;
  const params = new URLSearchParams();
  if (q)      params.set('q', q);
  if (plano)  params.set('plano', plano);
  if (status) params.set('status', status);
  window.location.href = '/admin/assinaturas/index.php?' + params.toString();
}

async function verPagamentos(empresa_id, nome) {
  document.getElementById('modal-titulo').textContent = 'Histórico — ' + nome;
  document.getElementById('modal-body').innerHTML = 'Carregando…';
  document.getElementById('modal-historico').style.display = 'flex';

  const res  = await fetch('/admin/assinaturas/pagamentos.php?empresa_id=' + empresa_id);
  const html = await res.text();
  document.getElementById('modal-body').innerHTML = html;
}

function fecharModal() {
  document.getElementById('modal-historico').style.display = 'none';
}

async function cancelarAssinatura(empresa_id, nome) {
  if (!confirm('Cancelar assinatura de ' + nome + '? Esta ação rebaixará o plano para Essencial.')) return;

  const res  = await fetch('/admin/assinaturas/cancelar.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ _token: '<?= $csrf ?>', empresa_id }),
  });
  const data = await res.json();

  if (data.ok) {
    location.reload();
  } else {
    alert('Erro: ' + (data.erro ?? 'Tente novamente.'));
  }
}
</script>

<?php include __DIR__ . '/../../admin/_layout_end.php'; ?>