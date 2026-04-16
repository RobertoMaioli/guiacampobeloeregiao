<?php
/**
 * admin/empresas/index.php
 * Lista e gestão de empresas cadastradas
 */
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

Auth::require();

$page_title = 'Empresas';

// Filtros
$filtro_status = Sanitize::get('status', 'str', 'todos');
$filtro_plano  = Sanitize::get('plano',  'str', 'todos');
$busca         = Sanitize::get('q',      'str', '');

$where  = ['1=1'];
$params = [];

if ($filtro_status !== 'todos') {
    $where[]  = 'e.status = ?';
    $params[] = $filtro_status;
}
if ($filtro_plano !== 'todos') {
    $where[]  = 'e.plan_intent = ?';
    $params[] = $filtro_plano;
}
if ($busca !== '') {
    $where[]  = '(u.nome LIKE ? OR u.email LIKE ? OR l.nome LIKE ?)';
    $like     = "%$busca%";
    $params[] = $like; $params[] = $like; $params[] = $like;
}

$whereSQL = implode(' AND ', $where);

$empresas = DB::query(
    "SELECT
        e.id, e.status, e.plan_intent, e.plano_ativo,
        e.submetido_em, e.criado_em, e.motivo_recusa,
        u.id AS usuario_id, u.nome AS usuario_nome, u.email AS usuario_email,
        l.nome AS lugar_nome, l.slug AS lugar_slug
     FROM empresas e
     JOIN usuarios u ON u.id = e.usuario_id
     LEFT JOIN lugares l ON l.id = e.lugar_id
     WHERE $whereSQL
     ORDER BY
       CASE e.status WHEN 'pendente' THEN 0 WHEN 'rascunho' THEN 1
         WHEN 'aprovada' THEN 2 ELSE 3 END,
       e.submetido_em DESC, e.criado_em DESC",
    $params
);

// Totais para os cards
$totais = DB::row(
    'SELECT
        COUNT(*) AS total,
        SUM(status="pendente")  AS pendentes,
        SUM(status="aprovada")  AS aprovadas,
        SUM(status="reprovada") AS reprovadas,
        SUM(status="rascunho")  AS rascunhos
     FROM empresas'
);

// ── Ação: excluir empresa ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Sanitize::csrfValid($_POST['_token'] ?? '')) {
    $eid = Sanitize::post('empresa_id', 'int');
    $act = Sanitize::post('action');

    if ($act === 'excluir_empresa' && $eid > 0) {
        $emp = DB::row('SELECT usuario_id, lugar_id FROM empresas WHERE id = ?', [$eid]);
        if ($emp) {
            DB::beginTransaction();
            try {
                $lid = (int)($emp['lugar_id'] ?? 0);
                $uid = (int)($emp['usuario_id'] ?? 0);

                if ($lid) {
                    DB::exec('DELETE FROM fotos         WHERE lugar_id = ?', [$lid]);
                    DB::exec('DELETE FROM horarios       WHERE lugar_id = ?', [$lid]);
                    DB::exec('DELETE FROM lugar_tags     WHERE lugar_id = ?', [$lid]);
                    DB::exec('DELETE FROM lugar_servicos WHERE lugar_id = ?', [$lid]);
                    DB::exec('DELETE FROM avaliacoes     WHERE lugar_id = ?', [$lid]);
                    DB::exec('DELETE FROM lugares        WHERE id = ?',       [$lid]);
                }
                DB::exec('DELETE FROM empresa_logs WHERE empresa_id = ?', [$eid]);
                DB::exec('DELETE FROM empresas     WHERE id = ?',         [$eid]);
                if ($uid) {
                    DB::exec('DELETE FROM usuarios WHERE id = ?', [$uid]);
                }

                DB::commit();
                $_SESSION['flash'] = ['type'=>'ok','msg'=>'Empresa e usuário excluídos permanentemente.'];
            } catch (Exception $e) {
                DB::rollback();
                error_log('[excluir empresa] ' . $e->getMessage());
                $_SESSION['flash'] = ['type'=>'erro','msg'=>'Erro ao excluir. Tente novamente.'];
            }
        }
        header('Location: /admin/empresas/index.php');
        exit;
    }
}

$csrf = Sanitize::csrfToken();
include __DIR__ . '/../_layout.php';
?>

<style>
    /* ── Stat cards ── */
    .stat-card {
        background: #fff;
        border: 1px solid rgba(61,71,51,.07);
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }
    .stat-dot {
        width: 32px; height: 32px;
        border-radius: .5rem;
        margin-bottom: .75rem;
    }
    .stat-value {
        font-size: 1.75rem; font-weight: 800;
        color: var(--green-dk); line-height: 1; margin-bottom: .2rem;
    }
    .stat-label {
        font-size: .6875rem; font-weight: 600;
        color: var(--warmgray); text-transform: uppercase; letter-spacing: .08em;
    }

    /* ── Filter card ── */
    .filter-card {
        background: #fff;
        border: 1px solid rgba(61,71,51,.07);
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        margin-bottom: 1.25rem;
    }
    .filter-label {
        display: block;
        font-size: .625rem; font-weight: 900;
        letter-spacing: .15em; text-transform: uppercase;
        color: var(--warmgray); margin-bottom: .375rem;
    }
    .filter-input {
        width: 100%;
        padding: .5rem .75rem;
        background: var(--offwhite);
        border: 1px solid rgba(61,71,51,.12);
        border-radius: .75rem;
        font-size: .8125rem;
        color: var(--graphite);
        outline: none;
        transition: border-color .2s;
        font-family: 'Montserrat', sans-serif;
    }
    .filter-input:focus { border-color: rgba(201,170,107,.6); }

    .btn-filter {
        padding: .5rem 1.25rem;
        background: var(--green-dk);
        color: #fff;
        font-size: .75rem; font-weight: 900;
        letter-spacing: .1em; text-transform: uppercase;
        border: none; border-radius: .75rem;
        cursor: pointer; transition: background .2s;
        font-family: 'Montserrat', sans-serif;
        white-space: nowrap;
    }
    .btn-filter:hover { background: var(--green); }

    .btn-clear {
        padding: .5rem 1rem;
        background: transparent;
        color: var(--warmgray);
        font-size: .75rem; font-weight: 600;
        border: 1px solid rgba(61,71,51,.15);
        border-radius: .75rem;
        text-decoration: none;
        transition: border-color .2s;
        white-space: nowrap;
    }
    .btn-clear:hover { border-color: var(--gold); color: var(--warmgray); }

    /* ── Table card ── */
    .table-card {
        background: #fff;
        border: 1px solid rgba(61,71,51,.07);
        border-radius: 1rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        overflow: hidden;
    }
    .table-card-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--offwhite);
    }
    .table-card-title {
        font-size: 1rem; font-weight: 700; color: var(--green-dk); margin: 0;
    }

    .data-table { width: 100%; border-collapse: collapse; }
    .data-table thead th {
        padding: .75rem 1.5rem;
        font-size: .625rem; font-weight: 900;
        letter-spacing: .15em; text-transform: uppercase;
        color: var(--warmgray); text-align: left;
        border-bottom: 1px solid var(--offwhite);
        white-space: nowrap;
    }
    .data-table tbody tr {
        border-bottom: 1px solid var(--offwhite);
        transition: background .15s;
    }
    .data-table tbody tr:last-child { border-bottom: none; }
    .data-table tbody tr:hover { background: rgba(242,240,235,.5); }
    .data-table tbody tr.row-pendente { background: rgba(255,251,235,.4); }
    .data-table tbody td { padding: 1rem 1.5rem; vertical-align: middle; }

    /* badges */
    .badge-status, .badge-plano {
        display: inline-block;
        padding: .2rem .625rem;
        border-radius: .5rem;
        font-size: .6875rem; font-weight: 700;
        white-space: nowrap;
    }
    .badge-pendente   { background: #fef3c7; color: #92400e; }
    .badge-aprovada   { background: #f0fdf4; color: #15803d; }
    .badge-reprovada  { background: #fef2f2; color: #dc2626; }
    .badge-rascunho   { background: #f3f4f6; color: #6b7280; }
    .badge-suspensa   { background: #fff7ed; color: #c2410c; }
    .badge-essencial    { background: #f3f4f6; color: #4b5563; }
    .badge-profissional { background: #eff6ff; color: #1d4ed8; }
    .badge-premium      { background: #fef3c7; color: #92400e; }

    .pulse-dot {
        display: inline-block;
        width: 6px; height: 6px;
        border-radius: 50%;
        background: #f59e0b;
        margin-right: 4px;
        animation: pulse 1.5s ease-in-out infinite;
        vertical-align: middle;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; } 50% { opacity: .3; }
    }

    /* action buttons */
    .btn-analisar {
        display: inline-flex; align-items: center; gap: .375rem;
        padding: .3rem .75rem;
        background: var(--green-dk); color: #fff;
        font-size: .6875rem; font-weight: 700;
        border: none; border-radius: .5rem;
        cursor: pointer; transition: background .2s;
        font-family: 'Montserrat', sans-serif;
        text-decoration: none; white-space: nowrap;
    }
    .btn-analisar:hover { background: var(--green); color: #fff; }

    .btn-link-gold {
        background: none; border: none; padding: 0;
        font-size: .6875rem; font-weight: 700;
        color: var(--gold); cursor: pointer;
        transition: color .2s;
        font-family: 'Montserrat', sans-serif;
    }
    .btn-link-gold:hover { color: var(--green-dk); }

    .btn-link-muted {
        background: none; border: none; padding: 0;
        font-size: .6875rem; font-weight: 700;
        color: var(--warmgray); cursor: pointer;
        transition: color .2s;
        font-family: 'Montserrat', sans-serif;
    }
    .btn-link-muted:hover { color: var(--green-dk); }

    .btn-link-danger {
        background: none; border: none; padding: 0;
        font-size: .6875rem; font-weight: 700;
        color: #ef4444; cursor: pointer;
        transition: color .2s;
        font-family: 'Montserrat', sans-serif;
    }
    .btn-link-danger:hover { color: #b91c1c; }

    /* ── Modals ── */
    .modal-backdrop-custom {
        display: none;
        position: fixed; inset: 0;
        background: rgba(0,0,0,.5);
        z-index: 1050;
        align-items: center; justify-content: center;
        padding: 1rem;
    }
    .modal-backdrop-custom.open { display: flex; }

    .modal-box {
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 24px 60px rgba(0,0,0,.25);
        width: 100%; max-width: 520px;
        overflow: hidden;
    }
    .modal-box.modal-sm { max-width: 440px; }

    .modal-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--offwhite);
    }
    .modal-title {
        font-size: 1.125rem; font-weight: 700; color: var(--green-dk); margin: 0;
    }
    .modal-subtitle { font-size: .75rem; color: var(--warmgray); margin-top: .25rem; }

    .btn-close-modal {
        width: 32px; height: 32px;
        background: none; border: none;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: var(--warmgray); cursor: pointer;
        transition: background .2s;
    }
    .btn-close-modal:hover { background: var(--offwhite); }

    .modal-body { padding: 1.25rem 1.5rem; }
    .modal-footer {
        display: flex; align-items: center; gap: .75rem;
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--offwhite);
        background: rgba(242,240,235,.5);
    }
    .modal-footer .spacer { flex: 1; }

    /* Plano options */
    .plano-opt {
        display: flex; flex-direction: column; align-items: center;
        padding: .75rem; border-radius: .75rem;
        border: 2px solid rgba(61,71,51,.1);
        cursor: pointer; text-align: center;
        transition: border-color .2s, background .2s;
    }
    .plano-opt:hover { border-color: rgba(201,170,107,.5); }
    .plano-opt.selected { border-color: var(--gold); background: var(--gold-pale); }
    .plano-opt span { font-size: .75rem; font-weight: 700; color: var(--graphite); }

    /* Form fields inside modal */
    .modal-label {
        display: block;
        font-size: .625rem; font-weight: 900;
        letter-spacing: .15em; text-transform: uppercase;
        color: var(--warmgray); margin-bottom: .5rem;
    }
    .modal-input, .modal-textarea, .modal-select {
        width: 100%;
        padding: .625rem .75rem;
        background: var(--offwhite);
        border: 1px solid rgba(61,71,51,.12);
        border-radius: .75rem;
        font-size: .8125rem;
        color: var(--graphite);
        outline: none;
        transition: border-color .2s;
        font-family: 'Montserrat', sans-serif;
    }
    .modal-input:focus, .modal-textarea:focus { border-color: rgba(201,170,107,.6); }
    .modal-textarea { resize: none; }

    /* Motivo presets */
    .motivo-preset {
        display: flex; align-items: center; gap: .625rem;
        padding: .625rem .75rem;
        border-radius: .5rem; cursor: pointer;
        transition: background .15s;
    }
    .motivo-preset:hover { background: var(--offwhite); }
    .motivo-preset span { font-size: .8125rem; color: var(--graphite); }

    /* Tabs */
    .mu-tabs { display: flex; border-bottom: 1px solid var(--offwhite); }
    .mu-tab {
        flex: 1; padding: .75rem;
        background: none; border: none;
        font-size: .75rem; font-weight: 700;
        letter-spacing: .1em; text-transform: uppercase;
        cursor: pointer; transition: color .2s;
        font-family: 'Montserrat', sans-serif;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
    }
    .mu-tab.active { border-bottom-color: var(--green-dk); color: var(--green-dk); }
    .mu-tab:not(.active) { color: var(--warmgray); }

    /* Feedback */
    .mu-feedback {
        display: none;
        margin: 0 1.5rem 1rem;
        padding: .75rem 1rem;
        border-radius: .75rem;
        font-size: .8125rem; font-weight: 600;
    }
    .mu-feedback.ok  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .mu-feedback.err { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

    /* modal action buttons */
    .btn-modal-secondary {
        padding: .5rem 1rem;
        background: transparent; color: var(--warmgray);
        font-size: .75rem; font-weight: 600;
        border: 1px solid rgba(61,71,51,.15); border-radius: .75rem;
        cursor: pointer; transition: border-color .2s;
        font-family: 'Montserrat', sans-serif;
    }
    .btn-modal-secondary:hover { border-color: var(--gold); }

    .btn-modal-danger {
        padding: .5rem 1rem;
        background: transparent; color: #dc2626;
        font-size: .75rem; font-weight: 700;
        border: 1px solid #fecaca; border-radius: .75rem;
        cursor: pointer; transition: background .2s;
        font-family: 'Montserrat', sans-serif;
    }
    .btn-modal-danger:hover { background: #fef2f2; }

    .btn-modal-danger-solid {
        padding: .5rem 1.25rem;
        background: #dc2626; color: #fff;
        font-size: .75rem; font-weight: 900;
        letter-spacing: .08em; text-transform: uppercase;
        border: none; border-radius: .75rem;
        cursor: pointer; transition: background .2s;
        font-family: 'Montserrat', sans-serif;
    }
    .btn-modal-danger-solid:hover { background: #b91c1c; }

    .btn-modal-primary {
        padding: .5rem 1.25rem;
        background: var(--green-dk); color: #fff;
        font-size: .75rem; font-weight: 900;
        letter-spacing: .08em; text-transform: uppercase;
        border: none; border-radius: .75rem;
        cursor: pointer; transition: background .2s;
        font-family: 'Montserrat', sans-serif;
    }
    .btn-modal-primary:hover { background: var(--green); }

    @keyframes spin { from { transform: rotate(0deg) } to { transform: rotate(360deg) } }
</style>

<!-- ── Stat cards ── -->
<div class="row g-3 mb-4">
    <?php
    $cards_totais = [
        ['label' => 'Total',      'val' => $totais['total']??0,      'bg' => 'var(--green-dk)'],
        ['label' => 'Pendentes',  'val' => $totais['pendentes']??0,  'bg' => 'var(--gold)'],
        ['label' => 'Aprovadas',  'val' => $totais['aprovadas']??0,  'bg' => '#16a34a'],
        ['label' => 'Reprovadas', 'val' => $totais['reprovadas']??0, 'bg' => '#ef4444'],
        ['label' => 'Rascunhos',  'val' => $totais['rascunhos']??0,  'bg' => '#9ca3af'],
    ];
    foreach ($cards_totais as $c): ?>
    <div class="col-6 col-lg">
        <div class="stat-card">
            <div class="stat-dot" style="background:<?= $c['bg'] ?>"></div>
            <div class="stat-value"><?= (int)$c['val'] ?></div>
            <div class="stat-label"><?= $c['label'] ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Filtros ── -->
<div class="filter-card">
    <form method="GET">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md">
                <label class="filter-label">Buscar</label>
                <input type="text" name="q" value="<?= Sanitize::html($busca) ?>"
                       placeholder="Nome, e-mail ou empresa…"
                       class="filter-input">
            </div>
            <div class="col-6 col-md-auto">
                <label class="filter-label">Status</label>
                <select name="status" class="filter-input" style="width:auto">
                    <?php foreach (['todos'=>'Todos','pendente'=>'Pendente','aprovada'=>'Aprovada',
                                    'reprovada'=>'Reprovada','rascunho'=>'Rascunho','suspensa'=>'Suspensa'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= $filtro_status===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-auto">
                <label class="filter-label">Plano solicitado</label>
                <select name="plano" class="filter-input" style="width:auto">
                    <?php foreach (['todos'=>'Todos','essencial'=>'Essencial',
                                    'profissional'=>'Profissional','premium'=>'Premium'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= $filtro_plano===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn-filter">Filtrar</button>
                <?php if ($busca || $filtro_status!=='todos' || $filtro_plano!=='todos'): ?>
                <a href="/admin/empresas/index.php" class="btn-clear">Limpar</a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- ── Tabela ── -->
<div class="table-card">
    <div class="table-card-header">
        <h2 class="table-card-title">
            <?= count($empresas) ?> empresa<?= count($empresas)!==1?'s':'' ?> encontrada<?= count($empresas)!==1?'s':'' ?>
        </h2>
    </div>

    <?php if (empty($empresas)): ?>
    <div style="padding:3rem 1.5rem; text-align:center; font-size:.8125rem; color:var(--warmgray)">
        Nenhuma empresa encontrada com os filtros selecionados.
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Usuário / Empresa</th>
                    <th>Plano solicitado</th>
                    <th>Plano ativo</th>
                    <th class="d-none d-md-table-cell">Status</th>
                    <th class="d-none d-lg-table-cell">Data</th>
                    <th style="text-align:right">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($empresas as $e):
                $plano_cfg = [
                    'essencial'    => 'badge-essencial',
                    'profissional' => 'badge-profissional',
                    'premium'      => 'badge-premium',
                ];
                $status_class = 'badge-' . ($e['status'] ?? 'rascunho');
                $status_label = ucfirst($e['status'] ?? '');
            ?>
            <tr class="<?= $e['status']==='pendente' ? 'row-pendente' : '' ?>">

                <td>
                    <div style="font-size:.84375rem; font-weight:600; color:var(--graphite)">
                        <?= Sanitize::html($e['usuario_nome']) ?>
                    </div>
                    <div style="font-size:.71875rem; color:var(--warmgray); margin-top:.15rem">
                        <?= Sanitize::html($e['usuario_email']) ?>
                    </div>
                    <?php if ($e['lugar_nome']): ?>
                    <div style="font-size:.6875rem; color:var(--green); margin-top:.15rem; font-weight:600">
                        <?= Sanitize::html($e['lugar_nome']) ?>
                    </div>
                    <?php endif; ?>
                </td>

                <td>
                    <span class="badge-plano <?= $plano_cfg[$e['plan_intent']] ?? 'badge-essencial' ?>">
                        <?= ucfirst($e['plan_intent']) ?>
                    </span>
                </td>

                <td>
                    <?php if ($e['plano_ativo']): ?>
                    <span class="badge-plano <?= $plano_cfg[$e['plano_ativo']] ?? 'badge-essencial' ?>">
                        <?= ucfirst($e['plano_ativo']) ?>
                    </span>
                    <?php else: ?>
                    <span style="font-size:.75rem; color:var(--warmgray)">—</span>
                    <?php endif; ?>
                </td>

                <td class="d-none d-md-table-cell">
                    <span class="badge-status <?= $status_class ?>">
                        <?php if ($e['status']==='pendente'): ?>
                        <span class="pulse-dot"></span>
                        <?php endif; ?>
                        <?= $status_label ?>
                    </span>
                </td>

                <td class="d-none d-lg-table-cell">
                    <?php
                    $data = $e['submetido_em'] ?? $e['criado_em'];
                    $horas = $e['submetido_em'] ? round((time() - strtotime($e['submetido_em'])) / 3600) : null;
                    ?>
                    <div style="font-size:.75rem; color:var(--warmgray)">
                        <?= $data ? date('d/m/Y H:i', strtotime($data)) : '—' ?>
                    </div>
                    <?php if ($e['status']==='pendente' && $horas !== null): ?>
                    <div style="font-size:.625rem; color:<?= $horas > 24 ? '#ef4444' : 'var(--warmgray)' ?>; margin-top:.2rem">
                        há <?= $horas ?>h <?= $horas > 24 ? '⚠ atrasado' : '' ?>
                    </div>
                    <?php endif; ?>
                </td>

                <td>
                    <div class="d-flex align-items-center justify-content-end gap-2">
                        <?php if ($e['status'] === 'pendente'): ?>
                        <button class="btn-analisar"
                                onclick="abrirModal(<?= (int)$e['id'] ?>, '<?= Sanitize::html($e['usuario_nome']) ?>', '<?= Sanitize::html($e['plan_intent']) ?>')">
                            Analisar
                        </button>
                        <?php else: ?>
                        <button class="btn-link-muted"
                                onclick="abrirModal(<?= (int)$e['id'] ?>, '<?= Sanitize::html($e['usuario_nome']) ?>', '<?= Sanitize::html($e['plan_intent']) ?>')">
                            Plano
                        </button>
                        <?php endif; ?>

                        <button class="btn-link-gold"
                                onclick="abrirModalUsuario(<?= (int)$e['usuario_id'] ?>, '<?= Sanitize::html($e['usuario_nome']) ?>', '<?= Sanitize::html($e['usuario_email']) ?>')">
                            Editar
                        </button>

                        <form method="POST" style="display:inline"
                              onsubmit="return confirm('ATENÇÃO: Excluir permanentemente esta empresa e o usuário?\nNão pode ser desfeito.')">
                            <input type="hidden" name="_token"     value="<?= Sanitize::html($csrf) ?>">
                            <input type="hidden" name="action"     value="excluir_empresa">
                            <input type="hidden" name="empresa_id" value="<?= (int)$e['id'] ?>">
                            <button type="submit" class="btn-link-danger">Excluir</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>


<!-- ══════════════════════════════════════════════════════════
     MODAL — Analisar empresa
══════════════════════════════════════════════════════════ -->
<div id="modal-overlay" class="modal-backdrop-custom" onclick="if(event.target===this) fecharModal()">
    <div class="modal-box" onclick="event.stopPropagation()">

        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modal-titulo">Analisar cadastro</h3>
                <p class="modal-subtitle" id="modal-subtitulo"></p>
            </div>
            <button class="btn-close-modal" onclick="fecharModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="/admin/empresas/aprovar.php" id="modal-form">
            <input type="hidden" name="_token"     value="<?= Sanitize::html($csrf) ?>">
            <input type="hidden" name="empresa_id" id="modal-empresa-id">
            <input type="hidden" name="acao"       id="modal-acao" value="aprovar">

            <div class="modal-body" style="display:flex; flex-direction:column; gap:1rem">

                <div id="bloco-plano">
                    <label class="modal-label">Plano a ativar</label>
                    <div class="row g-2">
                        <?php foreach (['essencial'=>'Essencial','profissional'=>'Profissional','premium'=>'Premium'] as $v=>$l): ?>
                        <div class="col-4">
                            <label class="plano-opt" data-val="<?= $v ?>">
                                <input type="radio" name="plano_ativo" value="<?= $v ?>"
                                       style="position:absolute;opacity:0;pointer-events:none"
                                       <?= $v==='profissional'?'checked':'' ?>>
                                <span><?= $l ?></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <label class="modal-label">Observação interna (opcional)</label>
                    <textarea name="observacao" id="modal-obs" rows="3"
                              placeholder="Nota interna sobre esta decisão…"
                              class="modal-textarea"></textarea>
                </div>

                <div id="bloco-motivo" style="display:none">
                    <label class="modal-label">
                        Motivo da recusa <span style="color:#ef4444">*</span>
                    </label>
                    <div style="margin-bottom:.75rem">
                        <?php foreach ([
                            'Informações incompletas ou incorretas',
                            'Negócio não atua em Campo Belo ou região',
                            'Conteúdo inadequado ou ofensivo',
                            'Cadastro duplicado',
                            'Outro motivo',
                        ] as $motivo): ?>
                        <label class="motivo-preset">
                            <input type="radio" name="motivo_preset"
                                   value="<?= htmlspecialchars($motivo) ?>"
                                   onchange="document.getElementById('motivo-texto').value=this.value"
                                   style="accent-color:#ef4444">
                            <span><?= htmlspecialchars($motivo) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <textarea name="motivo_recusa" id="motivo-texto" rows="2"
                              placeholder="Descreva o motivo (será enviado ao usuário)…"
                              class="modal-textarea"
                              style="border-color:#fecaca"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal-secondary" onclick="fecharModal()">Cancelar</button>
                <div class="spacer"></div>
                <button type="button" id="btn-reprovar" class="btn-modal-danger" onclick="setAcao('reprovar')">
                    Reprovar
                </button>
                <button type="button" id="btn-confirmar-reprovacao" class="btn-modal-danger-solid"
                        style="display:none" onclick="confirmarReprovacao()">
                    Confirmar reprovação ✗
                </button>
                <button type="submit" id="btn-aprovar" class="btn-modal-primary" onclick="setAcao('aprovar')">
                    Aprovar ✓
                </button>
            </div>
        </form>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════
     MODAL — Editar usuário
══════════════════════════════════════════════════════════ -->
<div id="modal-usuario" class="modal-backdrop-custom" onclick="if(event.target===this) fecharModalUsuario()">
    <div class="modal-box modal-sm" onclick="event.stopPropagation()">

        <div class="modal-header">
            <div>
                <h3 class="modal-title">Editar usuário</h3>
                <p class="modal-subtitle" id="mu-subtitulo"></p>
            </div>
            <button class="btn-close-modal" onclick="fecharModalUsuario()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="mu-tabs">
            <button class="mu-tab active" id="mu-tab-dados" onclick="muTab('dados')">Dados</button>
            <button class="mu-tab" id="mu-tab-senha" onclick="muTab('senha')">Senha</button>
        </div>

        <div id="mu-feedback" class="mu-feedback"></div>

        <form id="mu-form">
            <input type="hidden" name="_token"     value="<?= Sanitize::html($csrf) ?>">
            <input type="hidden" id="mu-usuario-id" name="usuario_id" value="">
            <input type="hidden" id="mu-acao"        name="acao"       value="dados">

            <div class="modal-body" style="display:flex; flex-direction:column; gap:1rem">

                <!-- Dados -->
                <div id="mu-painel-dados">
                    <div style="margin-bottom:1rem">
                        <label class="modal-label">Nome</label>
                        <input type="text" id="mu-nome" name="nome" class="modal-input">
                    </div>
                    <div>
                        <label class="modal-label">E-mail</label>
                        <input type="email" id="mu-email" name="email" class="modal-input">
                    </div>
                </div>

                <!-- Senha -->
                <div id="mu-painel-senha" style="display:none">
                    <div style="margin-bottom:1rem">
                        <label class="modal-label">Nova senha</label>
                        <input type="password" id="mu-nova-senha" name="nova_senha"
                               placeholder="Mínimo 8 caracteres" class="modal-input">
                    </div>
                    <div>
                        <label class="modal-label">Confirmar senha</label>
                        <input type="password" id="mu-conf-senha" name="conf_senha"
                               placeholder="Repita a senha" class="modal-input">
                    </div>
                </div>

            </div>
        </form>

        <div class="modal-footer" style="justify-content:flex-end">
            <button type="button" class="btn-modal-secondary" onclick="fecharModalUsuario()">Cancelar</button>
            <button type="button" class="btn-modal-primary" onclick="salvarUsuario()">Salvar</button>
        </div>
    </div>
</div>


<script>
// ══════════════════════════════════════════════
//  MODAL ANALISAR
// ══════════════════════════════════════════════
document.querySelectorAll('.plano-opt').forEach(lbl => {
    lbl.addEventListener('click', () => {
        document.querySelectorAll('.plano-opt').forEach(l => l.classList.remove('selected'));
        lbl.classList.add('selected');
        lbl.querySelector('input').checked = true;
    });
});
document.querySelector('.plano-opt[data-val="profissional"]')?.click();

function abrirModal(id, nome, planIntent) {
    document.getElementById('modal-empresa-id').value = id;
    document.getElementById('modal-titulo').textContent    = 'Analisar: ' + nome;
    document.getElementById('modal-subtitulo').textContent = 'Plano solicitado: ' + planIntent;
    document.getElementById('modal-overlay').classList.add('open');
    const opt = document.querySelector('.plano-opt[data-val="' + planIntent + '"]');
    if (opt) opt.click();
    setAcao('aprovar');
}

function fecharModal() {
    document.getElementById('modal-overlay').classList.remove('open');
    document.getElementById('motivo-texto').value = '';
    document.getElementById('modal-obs').value    = '';
}

function setAcao(acao) {
    document.getElementById('modal-acao').value = acao;
    const blocoPlano   = document.getElementById('bloco-plano');
    const blocoMotivo  = document.getElementById('bloco-motivo');
    const btnAprovar   = document.getElementById('btn-aprovar');
    const btnReprovar  = document.getElementById('btn-reprovar');
    const btnConfirmar = document.getElementById('btn-confirmar-reprovacao');

    if (acao === 'reprovar') {
        blocoPlano.style.display   = 'none';
        blocoMotivo.style.display  = 'block';
        btnAprovar.style.display   = 'none';
        btnReprovar.style.display  = 'none';
        btnConfirmar.style.display = 'inline-block';
        document.getElementById('motivo-texto').focus();
    } else {
        blocoPlano.style.display   = 'block';
        blocoMotivo.style.display  = 'none';
        btnAprovar.style.display   = 'inline-block';
        btnReprovar.style.display  = 'inline-block';
        btnConfirmar.style.display = 'none';
    }
}

function confirmarReprovacao() {
    const el = document.getElementById('motivo-texto');
    if (!el.value.trim()) {
        el.focus();
        el.style.borderColor = '#ef4444';
        setTimeout(() => { el.style.borderColor = '#fecaca'; }, 2000);
        return;
    }
    document.getElementById('modal-form').requestSubmit();
}


// ══════════════════════════════════════════════
//  MODAL EDITAR USUÁRIO
// ══════════════════════════════════════════════
function abrirModalUsuario(id, nome, email) {
    document.getElementById('mu-usuario-id').value = id;
    document.getElementById('mu-subtitulo').textContent = nome;
    document.getElementById('mu-nome').value  = nome;
    document.getElementById('mu-email').value = email;
    document.getElementById('mu-nova-senha').value = '';
    document.getElementById('mu-conf-senha').value = '';
    const fb = document.getElementById('mu-feedback');
    fb.style.display = 'none'; fb.className = 'mu-feedback';
    muTab('dados');
    document.getElementById('modal-usuario').classList.add('open');
}

function fecharModalUsuario() {
    document.getElementById('modal-usuario').classList.remove('open');
}

function muTab(tab) {
    const isDados = tab === 'dados';
    document.getElementById('mu-painel-dados').style.display = isDados ? 'block' : 'none';
    document.getElementById('mu-painel-senha').style.display = isDados ? 'none'  : 'block';
    document.getElementById('mu-acao').value = isDados ? 'dados' : 'senha';

    document.getElementById('mu-tab-dados').classList.toggle('active', isDados);
    document.getElementById('mu-tab-senha').classList.toggle('active', !isDados);
}

async function salvarUsuario() {
    const fb      = document.getElementById('mu-feedback');
    const payload = new FormData(document.getElementById('mu-form'));

    fb.style.display = 'none';
    fb.className = 'mu-feedback';

    try {
        const res  = await fetch('/admin/empresas/salvar.php', { method: 'POST', body: payload });
        const data = await res.json();

        fb.className   = 'mu-feedback ' + (data.ok ? 'ok' : 'err');
        fb.textContent = data.ok ? data.msg : data.erro;
        fb.style.display = 'block';

        if (data.ok && payload.get('acao') === 'dados') {
            setTimeout(() => location.reload(), 1200);
        }
    } catch (err) {
        fb.className   = 'mu-feedback err';
        fb.textContent = 'Erro de comunicação. Tente novamente.';
        fb.style.display = 'block';
    }
}

// Fechar com Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { fecharModal(); fecharModalUsuario(); }
});
</script>

<?php include __DIR__ . '/../_layout_end.php'; ?>