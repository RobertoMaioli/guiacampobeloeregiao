<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

Auth::require();

$page_title = 'Categorias';
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Sanitize::csrfValid($_POST['_token'] ?? '')) {
        $erros[] = 'Token inválido.';
    } else {
        $action = Sanitize::post('action');

        if ($action === 'save') {
            $cid   = Sanitize::post('id', 'int');
            $label = Sanitize::post('label');
            $slug  = Sanitize::post('slug', 'slug') ?: Sanitize::slug($label);
            $icon  = Sanitize::post('icon');
            $ordem = Sanitize::post('ordem', 'int');
            $ativo = Sanitize::post('ativo', 'bool') ? 1 : 0;

            if ($label === '') {
                $erros[] = 'Nome obrigatório.';
            } else {
                if ($cid > 0) {
                    DB::exec(
                        'UPDATE categorias SET label=?, slug=?, icon=?, ordem=?, ativo=? WHERE id=?',
                        [$label, $slug, $icon, $ordem, $ativo, $cid]
                    );
                    $_SESSION['flash'] = ['type'=>'ok','msg'=>'Categoria atualizada.'];
                } else {
                    DB::exec(
                        'INSERT INTO categorias (slug,label,icon,ordem,ativo) VALUES (?,?,?,?,?)',
                        [$slug, $label, $icon, $ordem, 1]
                    );
                    $_SESSION['flash'] = ['type'=>'ok','msg'=>'Categoria criada.'];
                }
                header('Location: /admin/categorias'); exit;
            }

        } elseif ($action === 'toggle') {
            $cid = Sanitize::post('id', 'int');
            DB::exec('UPDATE categorias SET ativo = 1 - ativo WHERE id=?', [$cid]);
            header('Location: /admin/categorias'); exit;
        }
    }
}

$categorias = DB::query('SELECT * FROM categorias ORDER BY ordem, label');

$counts = [];
foreach (DB::query('SELECT categoria_id, COUNT(*) n FROM lugares WHERE ativo=1 GROUP BY categoria_id') as $r) {
    $counts[$r['categoria_id']] = $r['n'];
}

$icons_disponiveis = [
    'utensils','coffee','wine','paw','spa','shopping-bag',
    'activity','dumbbell','scissors','pin','star','heart',
    'map','navigation','trending-up','grid','award','briefcase',
];

$edit_id  = Sanitize::get('edit', 'int', 0);
$edit_cat = $edit_id ? DB::row('SELECT * FROM categorias WHERE id=?', [$edit_id]) : null;

include __DIR__ . '/../_layout.php';
?>

<style>
    /* ── Form card ── */
    .form-card {
        background: #fff;
        border: 1px solid rgba(61,71,51,.07);
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        margin-bottom: 1.5rem;
    }
    .form-card-title {
        font-size: 1rem; font-weight: 700;
        color: var(--green-dk); margin: 0 0 1.25rem;
    }

    .form-label-admin {
        display: block;
        font-size: .625rem; font-weight: 900;
        letter-spacing: .18em; text-transform: uppercase;
        color: var(--warmgray); margin-bottom: .375rem;
    }
    .form-field {
        width: 100%;
        padding: .625rem 1rem;
        background: var(--offwhite);
        border: 1px solid rgba(61,71,51,.1);
        border-radius: .75rem;
        font-family: 'Montserrat', sans-serif;
        font-size: .84375rem; color: var(--graphite);
        outline: none; transition: border-color .2s;
    }
    .form-field:focus { border-color: rgba(201,170,107,.5); }

    .btn-submit {
        width: 100%; padding: .625rem 1rem;
        background: var(--green-dk); color: #fff;
        font-size: .75rem; font-weight: 900;
        letter-spacing: .1em; text-transform: uppercase;
        border: none; border-radius: .75rem;
        cursor: pointer; transition: background .2s;
        font-family: 'Montserrat', sans-serif;
    }
    .btn-submit:hover { background: var(--green); }

    .cancel-link {
        display: inline-block; margin-top: .75rem;
        font-size: .6875rem; font-weight: 700;
        color: var(--warmgray); text-decoration: none;
        transition: color .2s;
    }
    .cancel-link:hover { color: var(--gold); }

    /* ── Error box ── */
    .error-inline {
        background: #fef2f2; border: 1px solid #fecaca;
        border-radius: .75rem; padding: .75rem 1rem;
        margin-bottom: 1rem;
        font-size: .8125rem; color: #dc2626;
    }

    /* ── Table card ── */
    .table-card {
        background: #fff;
        border: 1px solid rgba(61,71,51,.07);
        border-radius: 1rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        overflow: hidden;
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
        color: var(--warmgray);
    }
    .data-table thead th.th-left   { text-align: left; }
    .data-table thead th.th-center { text-align: center; }
    .data-table thead th.th-right  { text-align: right; }

    .data-table tbody tr {
        border-bottom: 1px solid var(--offwhite);
        transition: background .15s;
    }
    .data-table tbody tr:last-child { border-bottom: none; }
    .data-table tbody tr:hover { background: rgba(242,240,235,.5); }

    .data-table tbody td {
        padding: .875rem 1.25rem;
        vertical-align: middle;
    }
    .data-table tbody td.td-center { text-align: center; }
    .data-table tbody td.td-right  { text-align: right; }

    /* count badge */
    .count-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 28px; height: 28px;
        border-radius: 50%;
        background: var(--gold-pale); color: var(--green-dk);
        font-size: .75rem; font-weight: 900;
    }

    /* toggle status button */
    .btn-status {
        padding: .2rem .75rem;
        border: none; border-radius: 50px;
        font-size: .625rem; font-weight: 900;
        letter-spacing: .08em; text-transform: uppercase;
        cursor: pointer; transition: background .2s;
        font-family: 'Montserrat', sans-serif;
    }
    .btn-status.on  { background: #f0fdf4; color: #16a34a; }
    .btn-status.on:hover  { background: #dcfce7; }
    .btn-status.off { background: #fef2f2; color: #ef4444; }
    .btn-status.off:hover { background: #fee2e2; }

    /* edit link */
    .btn-edit {
        font-size: .6875rem; font-weight: 700;
        color: var(--gold); text-decoration: none;
        transition: color .2s;
    }
    .btn-edit:hover { color: var(--gold-lt); }

    /* icon monospace */
    .icon-mono {
        font-family: monospace; font-size: .75rem;
        color: var(--warmgray);
    }
</style>

<!-- ── Form nova / editar ── -->
<div class="form-card">
    <h3 class="form-card-title">
        <?= $edit_cat ? 'Editar categoria' : 'Nova categoria' ?>
    </h3>

    <?php if (!empty($erros)): ?>
    <div class="error-inline"><?= Sanitize::html(implode(' ', $erros)) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
        <input type="hidden" name="action" value="save"/>
        <input type="hidden" name="id"     value="<?= (int)($edit_cat['id'] ?? 0) ?>"/>

        <div class="row g-3 align-items-end">

            <div class="col-12 col-sm-4">
                <label class="form-label-admin">Nome *</label>
                <input type="text" name="label" required
                       value="<?= Sanitize::html($edit_cat['label'] ?? '') ?>"
                       class="form-field"/>
            </div>

            <div class="col-12 col-sm-4">
                <label class="form-label-admin">Ícone</label>
                <select name="icon" class="form-field">
                    <?php foreach ($icons_disponiveis as $ic): ?>
                    <option value="<?= $ic ?>"
                            <?= ($edit_cat['icon'] ?? '') === $ic ? 'selected' : '' ?>>
                        <?= $ic ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-6 col-sm-2">
                <label class="form-label-admin">Ordem</label>
                <input type="number" name="ordem" min="0"
                       value="<?= (int)($edit_cat['ordem'] ?? 0) ?>"
                       class="form-field"/>
            </div>

            <div class="col-6 col-sm-2">
                <button type="submit" class="btn-submit">
                    <?= $edit_cat ? 'Salvar' : 'Criar' ?>
                </button>
            </div>

        </div>
    </form>

    <?php if ($edit_cat): ?>
    <a href="/admin/categorias" class="cancel-link">← Cancelar edição</a>
    <?php endif; ?>
</div>

<!-- ── Lista ── -->
<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="th-left">Categoria</th>
                    <th class="th-center d-none d-sm-table-cell">Ícone</th>
                    <th class="th-center d-none d-md-table-cell">Ordem</th>
                    <th class="th-center d-none d-md-table-cell">Lugares</th>
                    <th class="th-center">Status</th>
                    <th class="th-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $cat): ?>
                <tr>
                    <td>
                        <span style="font-size:.84375rem; font-weight:600; color:var(--graphite)">
                            <?= Sanitize::html($cat['label']) ?>
                        </span>
                        <span style="display:block; font-size:.6875rem; color:var(--warmgray)">
                            <?= Sanitize::html($cat['slug']) ?>
                        </span>
                    </td>

                    <td class="td-center d-none d-sm-table-cell">
                        <span class="icon-mono"><?= Sanitize::html($cat['icon']) ?></span>
                    </td>

                    <td class="td-center d-none d-md-table-cell">
                        <span style="font-size:.8125rem; font-weight:700; color:var(--graphite)">
                            <?= (int)$cat['ordem'] ?>
                        </span>
                    </td>

                    <td class="td-center d-none d-md-table-cell">
                        <span class="count-badge"><?= $counts[$cat['id']] ?? 0 ?></span>
                    </td>

                    <td class="td-center">
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
                            <input type="hidden" name="action" value="toggle"/>
                            <input type="hidden" name="id"     value="<?= (int)$cat['id'] ?>"/>
                            <button type="submit"
                                    class="btn-status <?= $cat['ativo'] ? 'on' : 'off' ?>">
                                <?= $cat['ativo'] ? 'Ativo' : 'Inativo' ?>
                            </button>
                        </form>
                    </td>

                    <td class="td-right">
                        <a href="?edit=<?= (int)$cat['id'] ?>" class="btn-edit">Editar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../_layout_end.php'; ?>