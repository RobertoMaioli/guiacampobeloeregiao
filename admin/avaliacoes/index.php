<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

Auth::require();

$page_title = 'Avaliações';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Sanitize::csrfValid($_POST['_token'] ?? '')) {
    $action = Sanitize::post('action');
    $aid    = Sanitize::post('id', 'int');

    if ($action === 'aprovar') {
        DB::exec('UPDATE avaliacoes SET aprovado = 1 WHERE id=?', [$aid]);
        $_SESSION['flash'] = ['type'=>'ok','msg'=>'Avaliação aprovada.'];
    } elseif ($action === 'rejeitar') {
        DB::exec('UPDATE avaliacoes SET aprovado = 0 WHERE id=?', [$aid]);
        $_SESSION['flash'] = ['type'=>'ok','msg'=>'Avaliação rejeitada.'];
    } elseif ($action === 'excluir') {
        DB::exec('DELETE FROM avaliacoes WHERE id=?', [$aid]);
        $_SESSION['flash'] = ['type'=>'ok','msg'=>'Avaliação removida.'];
    } elseif ($action === 'sync_rating') {
        $lid = Sanitize::post('lugar_id', 'int');
        $agg = DB::row(
            'SELECT AVG(nota) AS media, COUNT(*) AS total FROM avaliacoes WHERE lugar_id=? AND aprovado=1',
            [$lid]
        );
        DB::exec(
            'UPDATE lugares SET rating=?, total_reviews=? WHERE id=?',
            [round((float)$agg['media'], 1), (int)$agg['total'], $lid]
        );
        $_SESSION['flash'] = ['type'=>'ok','msg'=>'Rating atualizado.'];
    }

    header('Location: /admin/avaliacoes/index.php'); exit;
}

$filtro_fonte  = Sanitize::get('fonte');
$filtro_status = Sanitize::get('status', 'str', 'todos');
$filtro_lugar  = Sanitize::get('lugar', 'int', 0);
$page          = max(1, Sanitize::get('page', 'int', 1));
$per           = 20;
$off           = ($page - 1) * $per;

$where  = ['1=1'];
$params = [];

if ($filtro_fonte !== '') {
    $where[] = 'a.fonte = ?'; $params[] = $filtro_fonte;
}
if ($filtro_status === 'aprovadas') { $where[] = 'a.aprovado = 1'; }
if ($filtro_status === 'pendentes') { $where[] = 'a.aprovado = 0'; }
if ($filtro_lugar > 0) { $where[] = 'a.lugar_id = ?'; $params[] = $filtro_lugar; }

$whereSQL = implode(' AND ', $where);
$total    = (int)DB::row("SELECT COUNT(*) n FROM avaliacoes a WHERE $whereSQL", $params)['n'];
$pages    = (int)ceil($total / $per);

$avaliacoes = DB::query(
    "SELECT a.*, l.nome AS lugar_nome, l.id AS lugar_id
     FROM avaliacoes a
     JOIN lugares l ON l.id = a.lugar_id
     WHERE $whereSQL
     ORDER BY a.criado_em DESC
     LIMIT ? OFFSET ?",
    array_merge($params, [$per, $off])
);

$lugares_lista = DB::query('SELECT id, nome FROM lugares WHERE ativo=1 ORDER BY nome');
$pendentes     = (int)DB::row('SELECT COUNT(*) n FROM avaliacoes WHERE aprovado=0')['n'];

include __DIR__ . '/../_layout.php';
?>

<style>
    /* ── Toolbar selects ── */
    .toolbar-select {
        height: 40px; padding: 0 .875rem;
        background: #fff;
        border: 1px solid rgba(61,71,51,.1);
        border-radius: 50px;
        font-size: .8125rem;
        font-family: 'Montserrat', sans-serif;
        color: var(--graphite);
        outline: none; cursor: pointer;
        max-width: 200px;
    }
    .btn-toolbar-filter {
        height: 40px; padding: 0 1.25rem;
        background: var(--green-dk); color: #fff;
        font-size: .75rem; font-weight: 700;
        border: none; border-radius: 50px;
        cursor: pointer; transition: background .2s;
        font-family: 'Montserrat', sans-serif;
        white-space: nowrap;
    }
    .btn-toolbar-filter:hover { background: var(--green); }

    /* ── Info banner ── */
    .info-banner {
        background: var(--gold-pale);
        border: 1px solid rgba(201,170,107,.3);
        border-radius: 1rem;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        display: flex; gap: .75rem;
    }
    .info-banner-icon { flex-shrink: 0; margin-top: .1rem; }
    .info-banner-title {
        font-size: .8125rem; font-weight: 700;
        color: var(--green-dk); margin-bottom: .25rem;
    }
    .info-banner-text {
        font-size: .78125rem;
        color: rgba(61,71,51,.7);
        line-height: 1.6; margin: 0;
    }

    /* ── Reviews list card ── */
    .reviews-card {
        background: #fff;
        border: 1px solid rgba(61,71,51,.07);
        border-radius: 1rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .review-item {
        padding: 1.25rem;
        border-bottom: 1px solid var(--offwhite);
        transition: background .15s;
    }
    .review-item:last-child { border-bottom: none; }
    .review-item:hover { background: rgba(242,240,235,.4); }

    /* Avatar */
    .review-avatar {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: var(--green-dk);
        color: var(--gold);
        font-size: .8125rem; font-weight: 900;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    /* Inline badges */
    .badge-fonte {
        padding: .15rem .5rem;
        border-radius: 50px;
        font-size: .5625rem; font-weight: 900;
        letter-spacing: .08em; text-transform: uppercase;
    }
    .badge-fonte-google      { background: #eff6ff; color: #1d4ed8; }
    .badge-fonte-tripadvisor { background: #f0fdf4; color: #15803d; }
    .badge-fonte-manual      { background: var(--offwhite); color: var(--warmgray); }

    .badge-status-approved { background: #f0fdf4; color: #16a34a; padding: .15rem .5rem; border-radius: 50px; font-size: .5625rem; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; }
    .badge-status-pending  { background: #fff7ed; color: #c2410c; padding: .15rem .5rem; border-radius: 50px; font-size: .5625rem; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; }

    /* Meta line */
    .review-meta {
        font-size: .71875rem; color: var(--warmgray);
        display: flex; flex-wrap: wrap; align-items: center; gap: .5rem;
        margin-bottom: .5rem;
    }
    .review-rating { color: var(--gold); font-weight: 700; }

    /* Text */
    .review-text {
        font-size: .8125rem;
        color: rgba(29,29,27,.75);
        line-height: 1.6; font-style: italic; margin: 0;
    }

    /* Action buttons */
    .btn-approve {
        padding: .3rem .875rem;
        background: #22c55e; color: #fff;
        font-size: .6875rem; font-weight: 700;
        border: none; border-radius: 50px;
        cursor: pointer; transition: background .2s;
        font-family: 'Montserrat', sans-serif;
        white-space: nowrap;
    }
    .btn-approve:hover { background: #16a34a; }

    .btn-reject {
        padding: .3rem .875rem;
        background: var(--offwhite); color: var(--warmgray);
        font-size: .6875rem; font-weight: 700;
        border: none; border-radius: 50px;
        cursor: pointer; transition: background .2s;
        font-family: 'Montserrat', sans-serif;
        white-space: nowrap;
    }
    .btn-reject:hover { background: var(--gold-pale); }

    .btn-rating {
        padding: .3rem .875rem;
        background: var(--gold-pale); color: var(--green-dk);
        font-size: .6875rem; font-weight: 700;
        border: none; border-radius: 50px;
        cursor: pointer; transition: background .2s;
        font-family: 'Montserrat', sans-serif;
        white-space: nowrap;
    }
    .btn-rating:hover { background: var(--gold); }

    .btn-delete {
        background: none; border: none; padding: .3rem .5rem;
        font-size: .6875rem; font-weight: 700;
        color: #f87171; cursor: pointer; transition: color .2s;
        font-family: 'Montserrat', sans-serif;
    }
    .btn-delete:hover { color: #dc2626; }

    /* Empty state */
    .empty-state {
        padding: 3.5rem 1.5rem; text-align: center;
    }
    .empty-state-title {
        font-size: .875rem; font-weight: 700;
        color: var(--green-dk); margin-bottom: .375rem;
    }
    .empty-state-sub {
        font-size: .8125rem; color: var(--warmgray); margin: 0;
    }

    /* Pagination */
    .pagination-wrap {
        display: flex; justify-content: center;
        align-items: center; gap: .5rem; flex-wrap: wrap;
    }
    .page-btn {
        width: 36px; height: 36px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .8125rem; font-weight: 700;
        text-decoration: none; transition: all .2s;
        border: 1px solid rgba(61,71,51,.12);
        color: var(--warmgray);
    }
    .page-btn:hover  { border-color: var(--gold); color: var(--gold); }
    .page-btn.active { background: var(--green-dk); border-color: var(--green-dk); color: #fff; }
</style>

<!-- ── Toolbar ── -->
<div class="d-flex flex-wrap align-items-center gap-2 mb-4">
    <form method="GET" class="d-flex flex-wrap align-items-center gap-2">

        <select name="fonte" class="toolbar-select">
            <option value="">Todas as fontes</option>
            <option value="google"      <?= $filtro_fonte==='google'      ? 'selected':'' ?>>Google</option>
            <option value="tripadvisor" <?= $filtro_fonte==='tripadvisor' ? 'selected':'' ?>>TripAdvisor</option>
            <option value="manual"      <?= $filtro_fonte==='manual'      ? 'selected':'' ?>>Manual</option>
        </select>

        <select name="status" class="toolbar-select">
            <option value="todos"     <?= $filtro_status==='todos'     ? 'selected':'' ?>>Todas</option>
            <option value="aprovadas" <?= $filtro_status==='aprovadas' ? 'selected':'' ?>>Aprovadas</option>
            <option value="pendentes" <?= $filtro_status==='pendentes' ? 'selected':'' ?>>
                Pendentes<?= $pendentes > 0 ? " ($pendentes)" : '' ?>
            </option>
        </select>

        <select name="lugar" class="toolbar-select">
            <option value="0">Todos os lugares</option>
            <?php foreach ($lugares_lista as $l): ?>
            <option value="<?= $l['id'] ?>" <?= $filtro_lugar==$l['id'] ? 'selected':'' ?>>
                <?= Sanitize::html($l['nome']) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn-toolbar-filter">Filtrar</button>
    </form>

    <span style="font-size:.75rem; color:var(--warmgray); margin-left:auto">
        <strong style="color:var(--graphite)"><?= $total ?></strong> avaliações
    </span>
</div>

<!-- ── Info banner ── -->
<div class="info-banner">
    <div class="info-banner-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
             stroke="var(--gold)" stroke-width="1.75" stroke-linecap="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
    </div>
    <div>
        <p class="info-banner-title">Integrações externas</p>
        <p class="info-banner-text">
            Avaliações do Google Meu Negócio e TripAdvisor serão sincronizadas aqui automaticamente
            quando as integrações forem configuradas. Por ora, você pode adicionar avaliações manualmente
            ou importar via CSV.
        </p>
    </div>
</div>

<!-- ── Lista ── -->
<div class="reviews-card">
    <?php if (empty($avaliacoes)): ?>
    <div class="empty-state">
        <p class="empty-state-title">Nenhuma avaliação</p>
        <p class="empty-state-sub">Ajuste os filtros ou aguarde as integrações externas.</p>
    </div>
    <?php else: ?>
    <?php foreach ($avaliacoes as $av): ?>
    <div class="review-item">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">

            <!-- Autor + conteúdo -->
            <div class="d-flex align-items-start gap-3 flex-grow-1" style="min-width:0">
                <div class="review-avatar">
                    <?= mb_substr($av['autor_nome'] ?? '?', 0, 1) ?>
                </div>
                <div style="flex:1; min-width:0">
                    <!-- Nome + badges -->
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                        <span style="font-size:.84375rem; font-weight:700; color:var(--graphite)">
                            <?= Sanitize::html($av['autor_nome'] ?? 'Anônimo') ?>
                        </span>
                        <?php
                        $fonteClass = match($av['fonte']) {
                            'google'      => 'badge-fonte-google',
                            'tripadvisor' => 'badge-fonte-tripadvisor',
                            default       => 'badge-fonte-manual',
                        };
                        ?>
                        <span class="badge-fonte <?= $fonteClass ?>">
                            <?= Sanitize::html($av['fonte']) ?>
                        </span>
                        <span class="<?= $av['aprovado'] ? 'badge-status-approved' : 'badge-status-pending' ?>">
                            <?= $av['aprovado'] ? 'Aprovada' : 'Pendente' ?>
                        </span>
                    </div>

                    <!-- Lugar + nota + data -->
                    <div class="review-meta">
                        <span><?= Sanitize::html($av['lugar_nome']) ?></span>
                        <span class="review-rating">
                            <?= str_repeat('★', (int)round($av['nota'])) ?>
                            <?= number_format($av['nota'], 1) ?>
                        </span>
                        <?php if ($av['data_avaliacao']): ?>
                        <span><?= date('d/m/Y', strtotime($av['data_avaliacao'])) ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ($av['texto']): ?>
                    <p class="review-text">
                        "<?= Sanitize::html(mb_substr($av['texto'], 0, 240)) ?><?= mb_strlen($av['texto']) > 240 ? '…' : '' ?>"
                    </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ações -->
            <div class="d-flex align-items-center gap-2 flex-shrink-0">

                <?php if (!$av['aprovado']): ?>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
                    <input type="hidden" name="action" value="aprovar"/>
                    <input type="hidden" name="id"     value="<?= (int)$av['id'] ?>"/>
                    <button type="submit" class="btn-approve">Aprovar</button>
                </form>
                <?php else: ?>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
                    <input type="hidden" name="action" value="rejeitar"/>
                    <input type="hidden" name="id"     value="<?= (int)$av['id'] ?>"/>
                    <button type="submit" class="btn-reject">Rejeitar</button>
                </form>
                <?php endif; ?>

                <form method="POST" style="display:inline" title="Atualizar rating médio do lugar">
                    <input type="hidden" name="_token"   value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
                    <input type="hidden" name="action"   value="sync_rating"/>
                    <input type="hidden" name="id"       value="<?= (int)$av['id'] ?>"/>
                    <input type="hidden" name="lugar_id" value="<?= (int)$av['lugar_id'] ?>"/>
                    <button type="submit" class="btn-rating">↺ Rating</button>
                </form>

                <form method="POST" style="display:inline"
                      onsubmit="return confirm('Excluir esta avaliação permanentemente?')">
                    <input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
                    <input type="hidden" name="action" value="excluir"/>
                    <input type="hidden" name="id"     value="<?= (int)$av['id'] ?>"/>
                    <button type="submit" class="btn-delete">Excluir</button>
                </form>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ── Paginação ── -->
<?php if ($pages > 1): ?>
<div class="pagination-wrap">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
    <a href="?<?= http_build_query(['fonte'=>$filtro_fonte,'status'=>$filtro_status,'lugar'=>$filtro_lugar,'page'=>$p]) ?>"
       class="page-btn <?= $p === $page ? 'active' : '' ?>">
        <?= $p ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../_layout_end.php'; ?>