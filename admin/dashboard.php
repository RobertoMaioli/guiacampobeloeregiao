<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Sanitize.php';

Auth::require();

$page_title = 'Dashboard';

// ── Totais ──
$totais = DB::row(
  'SELECT
     (SELECT COUNT(*) FROM lugares    WHERE ativo = 1)      AS lugares,
     (SELECT COUNT(*) FROM categorias WHERE ativo = 1)      AS categorias,
     (SELECT COUNT(*) FROM avaliacoes WHERE aprovado = 1)   AS avaliacoes,
     (SELECT COUNT(*) FROM lugares    WHERE destaque = 1)   AS destaques'
);

// ── Últimos cadastrados ──
$recentes = DB::query(
  'SELECT l.id, l.slug, l.nome, l.rating, l.criado_em,
          c.label AS cat_nome
   FROM lugares l
   JOIN categorias c ON c.id = l.categoria_id
   WHERE l.ativo = 1
   ORDER BY l.criado_em DESC LIMIT 6'
);

include __DIR__ . '/_layout.php';
?>

<style>
    /* ── Stat cards ── */
    .stat-card {
        background: #fff;
        border: 1px solid rgba(61,71,51,.07);
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: .75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 800;
        color: var(--green-dk);
        line-height: 1;
        margin-bottom: .25rem;
    }

    .stat-label {
        font-size: .75rem;
        font-weight: 600;
        color: var(--warmgray);
    }

    /* ── Quick action buttons ── */
    .btn-action-primary {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .625rem 1.25rem;
        background: var(--green-dk);
        color: #fff;
        font-size: .75rem;
        font-weight: 900;
        letter-spacing: .1em;
        text-transform: uppercase;
        border: none;
        border-radius: 50px;
        text-decoration: none;
        transition: background .2s;
        cursor: pointer;
    }
    .btn-action-primary:hover { background: var(--green); color: #fff; }

    .btn-action-secondary {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .625rem 1.25rem;
        background: #fff;
        color: var(--graphite);
        font-size: .75rem;
        font-weight: 900;
        letter-spacing: .1em;
        text-transform: uppercase;
        border: 1px solid rgba(61,71,51,.15);
        border-radius: 50px;
        text-decoration: none;
        transition: border-color .2s;
        cursor: pointer;
    }
    .btn-action-secondary:hover { border-color: var(--gold); color: var(--graphite); }

    .btn-action-blue {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .625rem 1.25rem;
        background: #4285F4;
        color: #fff;
        font-size: .75rem;
        font-weight: 900;
        letter-spacing: .1em;
        text-transform: uppercase;
        border: none;
        border-radius: 50px;
        text-decoration: none;
        transition: background .2s;
        cursor: pointer;
    }
    .btn-action-blue:hover { background: #3367D6; color: #fff; }
    .btn-action-blue:disabled { opacity: .7; cursor: not-allowed; }

    /* ── Recentes table card ── */
    .table-card {
        background: #fff;
        border: 1px solid rgba(61,71,51,.07);
        border-radius: 1rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        overflow: hidden;
    }

    .table-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--offwhite);
    }

    .table-card-title {
        font-size: 1.0625rem;
        font-weight: 700;
        color: var(--green-dk);
        margin: 0;
    }

    .table-card-link {
        font-size: .6875rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: var(--gold);
        text-decoration: none;
        transition: color .2s;
    }
    .table-card-link:hover { color: var(--gold-lt); }

    .data-table { width: 100%; border-collapse: collapse; }

    .data-table thead th {
        padding: .75rem 1.5rem;
        font-size: .625rem;
        font-weight: 900;
        letter-spacing: .15em;
        text-transform: uppercase;
        color: var(--warmgray);
        text-align: left;
        border-bottom: 1px solid var(--offwhite);
    }

    .data-table tbody tr {
        border-bottom: 1px solid var(--offwhite);
        transition: background .15s;
    }
    .data-table tbody tr:last-child { border-bottom: none; }
    .data-table tbody tr:hover { background: rgba(242,240,235,.6); }

    .data-table tbody td {
        padding: .875rem 1.5rem;
        vertical-align: middle;
    }

    .td-name    { font-size: .84375rem; font-weight: 600; color: var(--graphite); }
    .td-cat     { font-size: .75rem; color: var(--warmgray); }
    .td-rating  { font-size: .75rem; font-weight: 700; color: var(--gold); }
    .td-date    { font-size: .75rem; color: var(--warmgray); }
    .td-edit a  {
        font-size: .6875rem; font-weight: 700; letter-spacing: .08em;
        text-transform: uppercase; color: var(--gold); text-decoration: none;
        transition: color .2s;
    }
    .td-edit a:hover { color: var(--gold-lt); }

    /* ── Sync result ── */
    .sync-result {
        display: none;
        margin-top: .75rem;
        padding: .75rem 1rem;
        border-radius: .75rem;
        font-size: .8125rem;
        font-weight: 600;
    }
    .sync-result.ok  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .sync-result.err { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

    @keyframes spin { from { transform: rotate(0deg) } to { transform: rotate(360deg) } }
</style>

<!-- ── Stat cards ── -->
<div class="row g-4 mb-4">
    <?php
    $cards = [
        ['label' => 'Lugares ativos', 'val' => $totais['lugares'],    'bg' => 'var(--green-dk)', 'icon' => '<path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>'],
        ['label' => 'Categorias',     'val' => $totais['categorias'], 'bg' => 'var(--green)',    'icon' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>'],
        ['label' => 'Avaliações',     'val' => $totais['avaliacoes'], 'bg' => 'var(--gold)',     'icon' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'],
        ['label' => 'Em destaque',    'val' => $totais['destaques'],  'bg' => 'var(--green-lt)', 'icon' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>'],
    ];
    foreach ($cards as $c): ?>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:<?= $c['bg'] ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white"
                     stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <?= $c['icon'] ?>
                </svg>
            </div>
            <div class="stat-value"><?= number_format((int)$c['val']) ?></div>
            <div class="stat-label"><?= $c['label'] ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Quick actions ── -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="/admin/lugares/create.php" class="btn-action-primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2.5" stroke-linecap="round">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Novo Lugar
    </a>

    <a href="/admin/categorias/index.php" class="btn-action-secondary">
        Gerenciar Categorias
    </a>

    <button type="button" id="btn-sync-todos" class="btn-action-blue" onclick="syncTodos()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round">
            <polyline points="1 4 1 10 7 10"/>
            <path d="M3.51 15a9 9 0 1 0 .49-3.51"/>
        </svg>
        Sincronizar Google
    </button>
</div>

<div id="sync-todos-result" class="sync-result"></div>

<!-- ── Recentes ── -->
<div class="table-card">
    <div class="table-card-header">
        <h2 class="table-card-title">Últimos cadastrados</h2>
        <a href="/admin/lugares/index.php" class="table-card-link">Ver todos</a>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th class="d-none d-sm-table-cell">Categoria</th>
                    <th class="d-none d-md-table-cell">Rating</th>
                    <th class="d-none d-lg-table-cell">Cadastrado em</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentes as $r): ?>
                <tr>
                    <td class="td-name"><?= Sanitize::html($r['nome']) ?></td>
                    <td class="td-cat d-none d-sm-table-cell"><?= Sanitize::html($r['cat_nome']) ?></td>
                    <td class="td-rating d-none d-md-table-cell">
                        <?= $r['rating'] > 0 ? '★ ' . number_format($r['rating'], 1) : '—' ?>
                    </td>
                    <td class="td-date d-none d-lg-table-cell">
                        <?= date('d/m/Y', strtotime($r['criado_em'])) ?>
                    </td>
                    <td class="td-edit text-end">
                        <a href="/admin/lugares/edit.php?id=<?= (int)$r['id'] ?>">Editar</a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($recentes)): ?>
                <tr>
                    <td colspan="5" style="padding:2rem 1.5rem; text-align:center; font-size:.8125rem; color:var(--warmgray)">
                        Nenhum lugar cadastrado ainda.
                        <a href="/admin/lugares/create.php" style="color:var(--gold)">Cadastrar agora</a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
async function syncTodos() {
    const btn    = document.getElementById('btn-sync-todos');
    const result = document.getElementById('sync-todos-result');

    btn.disabled  = true;
    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="animation:spin 1s linear infinite"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg> Sincronizando…';
    result.className = 'sync-result';
    result.style.display = 'none';

    try {
        const fd = new FormData();
        fd.append('_token', '<?= Sanitize::html(Sanitize::csrfToken()) ?>');
        fd.append('modo', 'todos');

        const res  = await fetch('/admin/google-sync.php', { method: 'POST', body: fd });
        const data = await res.json();

        result.className   = 'sync-result ' + (data.ok ? 'ok' : 'err');
        result.textContent = data.ok ? '✓ ' + data.msg : '✗ ' + data.erro;
        result.style.display = 'block';

    } catch (e) {
        result.className   = 'sync-result err';
        result.textContent = '✗ Erro de conexão. Tente novamente.';
        result.style.display = 'block';
    } finally {
        btn.disabled  = false;
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg> Sincronizar Google';
    }
}
</script>

<?php include __DIR__ . '/_layout_end.php'; ?>