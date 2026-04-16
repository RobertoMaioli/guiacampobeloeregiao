<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

Auth::require();

/**
 * Renderiza um template de e-mail de forma isolada
 */
function _renderEmail(string $path, array $vars = []): string {
    extract($vars);
    if (!defined('SITE_URL')) require_once __DIR__ . '/../../config/mail.php';
    ob_start();
    include $path;
    return ob_get_clean();
}

$page_title = 'Lugares';

// ── Filtros ──
$q    = Sanitize::get('q');
$cat  = Sanitize::get('cat', 'int', 0);
$page = max(1, Sanitize::get('page', 'int', 1));
$per  = 15;
$off  = ($page - 1) * $per;

$where  = ['1=1'];
$params = [];

if ($q !== '') {
    $where[]  = '(l.nome LIKE ? OR l.endereco LIKE ?)';
    $like     = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
}
if ($cat > 0) {
    $where[]  = 'l.categoria_id = ?';
    $params[] = $cat;
}

$whereSQL = implode(' AND ', $where);
$total    = (int)DB::row("SELECT COUNT(*) n FROM lugares l WHERE $whereSQL", $params)['n'];
$pages    = (int)ceil($total / $per);

$lugares = DB::query(
    "SELECT l.id, l.slug, l.nome, l.badge, l.preco_simbolo,
            l.rating, l.total_reviews, l.destaque, l.ativo, l.criado_em,
            c.label AS cat_nome,
            f.url   AS foto
     FROM lugares l
     JOIN categorias c ON c.id = l.categoria_id
     LEFT JOIN fotos f ON f.lugar_id = l.id AND f.principal = 1
     WHERE $whereSQL
     ORDER BY l.destaque DESC, l.nome ASC
     LIMIT ? OFFSET ?",
    array_merge($params, [$per, $off])
);

$categorias = DB::query('SELECT id, label FROM categorias WHERE ativo = 1 ORDER BY ordem');

// ── Ações POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Sanitize::csrfValid($_POST['_token'] ?? '')) {
    $act = Sanitize::post('action');
    $lid = Sanitize::post('lugar_id', 'int');

    if ($act === 'toggle_destaque') {
        DB::exec('UPDATE lugares SET destaque = 1 - destaque WHERE id = ?', [$lid]);

    } elseif ($act === 'inativar') {
        DB::exec('UPDATE lugares SET ativo = 0 WHERE id = ?', [$lid]);
        DB::exec('UPDATE empresas SET status = "suspensa" WHERE lugar_id = ? AND status = "aprovada"', [$lid]);

        try {
            require_once __DIR__ . '/../../core/Mailer.php';
            $emp = DB::row(
                'SELECT u.nome, u.email, l.nome AS nome_empresa
                 FROM empresas e
                 JOIN usuarios u ON u.id = e.usuario_id
                 JOIN lugares l ON l.id = e.lugar_id
                 WHERE e.lugar_id = ?', [$lid]
            );
            if ($emp) {
                $html = _renderEmail(
                    __DIR__ . '/../../emails/suspensa.php',
                    ['nome' => $emp['nome'], 'nome_empresa' => $emp['nome_empresa']]
                );
                $res = Mailer::send($emp['email'], $emp['nome'], 'Sua página foi temporariamente suspensa', $html);
                error_log('[mail suspensa] ' . json_encode($res));
            }
        } catch (Throwable $ex) {
            error_log('[mail suspensa] ERRO: ' . $ex->getMessage());
        }

        $_SESSION['flash'] = ['type'=>'ok','msg'=>'Lugar inativado e empresa suspensa.'];

    } elseif ($act === 'reativar') {
        DB::exec('UPDATE lugares SET ativo = 1 WHERE id = ?', [$lid]);
        DB::exec('UPDATE empresas SET status = "aprovada" WHERE lugar_id = ? AND status = "suspensa"', [$lid]);
        $_SESSION['flash'] = ['type'=>'ok','msg'=>'Lugar reativado.'];

    } elseif ($act === 'excluir') {
        DB::beginTransaction();
        try {
            DB::exec('DELETE FROM fotos         WHERE lugar_id = ?', [$lid]);
            DB::exec('DELETE FROM horarios       WHERE lugar_id = ?', [$lid]);
            DB::exec('DELETE FROM lugar_tags     WHERE lugar_id = ?', [$lid]);
            DB::exec('DELETE FROM lugar_servicos WHERE lugar_id = ?', [$lid]);
            DB::exec('DELETE FROM avaliacoes     WHERE lugar_id = ?', [$lid]);
            DB::exec('UPDATE empresas SET lugar_id = NULL, status = "rascunho", plano_ativo = NULL WHERE lugar_id = ?', [$lid]);
            DB::exec('DELETE FROM lugares WHERE id = ?', [$lid]);
            DB::commit();
            $_SESSION['flash'] = ['type'=>'ok','msg'=>'Lugar excluído com sucesso.'];
        } catch (Exception $e) {
            DB::rollback();
            error_log('[excluir lugar] ' . $e->getMessage());
            $_SESSION['flash'] = ['type'=>'erro','msg'=>'Erro ao excluir. Tente novamente.'];
        }
    }

    header('Location: /admin/lugares/index.php?' . http_build_query(['q'=>$q,'cat'=>$cat,'page'=>$page]));
    exit;
}

include __DIR__ . '/../_layout.php';
?>

<style>
    /* ── Toolbar ── */
    .toolbar-search {
        display: flex;
        align-items: center;
        gap: .5rem;
        background: #fff;
        border: 1px solid rgba(61,71,51,.1);
        border-radius: 50px;
        padding: 0 1rem;
        height: 40px;
        flex: 1;
        max-width: 280px;
    }
    .toolbar-search input {
        flex: 1; border: none; outline: none;
        font-size: .8125rem; background: transparent;
        font-family: 'Montserrat', sans-serif;
        color: var(--graphite);
    }
    .toolbar-select {
        height: 40px;
        padding: 0 .875rem;
        background: #fff;
        border: 1px solid rgba(61,71,51,.1);
        border-radius: 50px;
        font-size: .8125rem;
        font-family: 'Montserrat', sans-serif;
        color: var(--graphite);
        outline: none;
        cursor: pointer;
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

    .btn-toolbar-new {
        display: inline-flex; align-items: center; gap: .5rem;
        height: 40px; padding: 0 1.25rem;
        background: var(--gold); color: var(--green-dk);
        font-size: .75rem; font-weight: 900;
        letter-spacing: .1em; text-transform: uppercase;
        border: none; border-radius: 50px;
        text-decoration: none; transition: background .2s;
        white-space: nowrap;
    }
    .btn-toolbar-new:hover { background: var(--gold-lt); color: var(--green-dk); }

    /* ── Table card ── */
    .table-card {
        background: #fff;
        border: 1px solid rgba(61,71,51,.07);
        border-radius: 1rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .data-table { width: 100%; border-collapse: collapse; }
    .data-table thead tr {
        background: rgba(242,240,235,.6);
        border-bottom: 1px solid var(--offwhite);
    }
    .data-table thead th {
        padding: .875rem 1.25rem;
        font-size: .625rem; font-weight: 900;
        letter-spacing: .14em; text-transform: uppercase;
        color: var(--warmgray); text-align: left;
        white-space: nowrap;
    }
    .data-table thead th.th-center { text-align: center; }
    .data-table thead th.th-right  { text-align: right; }

    .data-table tbody tr {
        border-bottom: 1px solid var(--offwhite);
        transition: background .15s;
    }
    .data-table tbody tr:last-child { border-bottom: none; }
    .data-table tbody tr:hover { background: rgba(242,240,235,.5); }
    .data-table tbody td { padding: .875rem 1.25rem; vertical-align: middle; }
    .data-table tbody td.td-center { text-align: center; }
    .data-table tbody td.td-right  { text-align: right; }

    /* Thumbnail */
    .thumb {
        width: 40px; height: 40px;
        border-radius: .5rem; overflow: hidden;
        background: var(--offwhite); flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
    }
    .thumb img { width: 100%; height: 100%; object-fit: cover; }

    .badge-inativo {
        display: inline-block;
        margin-left: .375rem;
        padding: .1rem .375rem;
        background: #fee2e2; color: #dc2626;
        font-size: .5625rem; font-weight: 900;
        border-radius: .25rem; text-transform: uppercase;
        letter-spacing: .05em; vertical-align: middle;
    }

    .badge-tag {
        display: inline-block;
        padding: .15rem .5rem;
        background: var(--gold-pale); color: var(--green-dk);
        font-size: .5625rem; font-weight: 900;
        letter-spacing: .08em; text-transform: uppercase;
        border-radius: 50px; margin-top: .25rem;
    }

    /* Destaque toggle */
    .btn-destaque {
        width: 28px; height: 28px;
        border-radius: 50%;
        border: 1px solid;
        background: none; cursor: pointer;
        font-size: .875rem; line-height: 1;
        transition: all .2s;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .btn-destaque.on  { background: var(--gold); border-color: var(--gold); color: var(--green-dk); }
    .btn-destaque.off { border-color: rgba(61,71,51,.2); color: rgba(139,133,137,.4); }
    .btn-destaque.off:hover { border-color: var(--gold); }

    /* Action links */
    .btn-act { background: none; border: none; padding: 0; cursor: pointer; font-family: 'Montserrat', sans-serif; font-size: .6875rem; font-weight: 700; transition: color .2s; text-decoration: none; white-space: nowrap; }
    .btn-act-muted   { color: var(--warmgray); }  .btn-act-muted:hover   { color: var(--graphite); }
    .btn-act-gold    { color: var(--gold); }       .btn-act-gold:hover    { color: var(--gold-lt); }
    .btn-act-danger  { color: #f87171; }           .btn-act-danger:hover  { color: #dc2626; }
    .btn-act-danger2 { color: #dc2626; }           .btn-act-danger2:hover { color: #991b1b; }
    .btn-act-success { color: #34d399; }           .btn-act-success:hover { color: #059669; }

    /* ── Pagination ── */
    .pagination-wrap {
        display: flex; justify-content: center;
        align-items: center; gap: .5rem;
        flex-wrap: wrap;
    }
    .page-btn {
        width: 36px; height: 36px;
        border-radius: 50%;
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
<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap flex-grow-1">
        <div class="toolbar-search">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="var(--gold)" stroke-width="1.75" stroke-linecap="round">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="q" value="<?= Sanitize::html($q) ?>"
                   placeholder="Buscar por nome…"/>
        </div>
        <select name="cat" class="toolbar-select">
            <option value="0">Todas categorias</option>
            <?php foreach ($categorias as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $cat == $c['id'] ? 'selected' : '' ?>>
                <?= Sanitize::html($c['label']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-toolbar-filter">Filtrar</button>
    </form>

    <a href="/admin/lugares/create.php" class="btn-toolbar-new ms-auto">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2.5" stroke-linecap="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Novo Lugar
    </a>
</div>

<!-- Results info -->
<p style="font-size:.75rem; color:var(--warmgray); margin-bottom:1rem">
    <strong style="color:var(--graphite)"><?= $total ?></strong>
    lugar<?= $total != 1 ? 'es' : '' ?> encontrado<?= $total != 1 ? 's' : '' ?>
</p>

<!-- ── Table ── -->
<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Lugar</th>
                    <th class="d-none d-sm-table-cell">Categoria</th>
                    <th class="d-none d-md-table-cell th-center">Rating</th>
                    <th class="d-none d-lg-table-cell th-center">Destaque</th>
                    <th class="th-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lugares as $l): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="thumb">
                                <?php if ($l['foto']): ?>
                                <img src="<?= Sanitize::html($l['foto']) ?>" alt="">
                                <?php else: ?>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                     stroke="rgba(139,133,137,.4)" stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div style="font-size:.84375rem; font-weight:600; color:var(--graphite)">
                                    <?= Sanitize::html($l['nome']) ?>
                                    <?php if (!$l['ativo']): ?>
                                    <span class="badge-inativo">Inativo</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($l['badge']): ?>
                                <span class="badge-tag"><?= Sanitize::html($l['badge']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>

                    <td class="d-none d-sm-table-cell">
                        <span style="font-size:.78125rem; color:var(--warmgray)">
                            <?= Sanitize::html($l['cat_nome']) ?>
                        </span>
                    </td>

                    <td class="d-none d-md-table-cell td-center">
                        <span style="font-size:.8125rem; font-weight:700; color:var(--gold)">
                            <?= $l['rating'] > 0 ? '★ ' . number_format($l['rating'], 1) : '—' ?>
                        </span>
                    </td>

                    <td class="d-none d-lg-table-cell td-center">
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="_token"   value="<?= Sanitize::html(Sanitize::csrfToken()) ?>">
                            <input type="hidden" name="action"   value="toggle_destaque">
                            <input type="hidden" name="lugar_id" value="<?= $l['id'] ?>">
                            <button type="submit"
                                    class="btn-destaque <?= $l['destaque'] ? 'on' : 'off' ?>">
                                ★
                            </button>
                        </form>
                    </td>

                    <td class="td-right">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="/pages/<?= Sanitize::html($l['slug']) ?>" target="_blank"
                               class="btn-act btn-act-muted">Ver</a>

                            <a href="/admin/lugares/edit.php?id=<?= (int)$l['id'] ?>"
                               class="btn-act btn-act-gold">Editar</a>

                            <?php if ($l['ativo']): ?>
                            <form method="POST" style="display:inline"
                                  onsubmit="return confirm('Inativar este lugar?')">
                                <input type="hidden" name="_token"   value="<?= Sanitize::html(Sanitize::csrfToken()) ?>">
                                <input type="hidden" name="action"   value="inativar">
                                <input type="hidden" name="lugar_id" value="<?= (int)$l['id'] ?>">
                                <button type="submit" class="btn-act btn-act-danger">Inativar</button>
                            </form>
                            <?php else: ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="_token"   value="<?= Sanitize::html(Sanitize::csrfToken()) ?>">
                                <input type="hidden" name="action"   value="reativar">
                                <input type="hidden" name="lugar_id" value="<?= (int)$l['id'] ?>">
                                <button type="submit" class="btn-act btn-act-success">Reativar</button>
                            </form>
                            <?php endif; ?>

                            <form method="POST" style="display:inline"
                                  onsubmit="return confirm('ATENÇÃO: Excluir permanentemente este lugar?\nEsta ação não pode ser desfeita.')">
                                <input type="hidden" name="_token"   value="<?= Sanitize::html(Sanitize::csrfToken()) ?>">
                                <input type="hidden" name="action"   value="excluir">
                                <input type="hidden" name="lugar_id" value="<?= (int)$l['id'] ?>">
                                <button type="submit" class="btn-act btn-act-danger2">Excluir</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($lugares)): ?>
                <tr>
                    <td colspan="5" style="padding:2.5rem 1.5rem; text-align:center; font-size:.8125rem; color:var(--warmgray)">
                        Nenhum lugar encontrado.
                        <a href="/admin/lugares/create.php" style="color:var(--gold)">Cadastrar</a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Paginação ── -->
<?php if ($pages > 1): ?>
<div class="pagination-wrap">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
    <a href="?<?= http_build_query(['q'=>$q,'cat'=>$cat,'page'=>$p]) ?>"
       class="page-btn <?= $p === $page ? 'active' : '' ?>">
        <?= $p ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../_layout_end.php'; ?>