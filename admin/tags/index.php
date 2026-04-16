<?php
require_once __DIR__ . '/../../includes/icons.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

Auth::require();

$page_title = 'Tags / Palavras-chave';
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Sanitize::csrfValid($_POST['_token'] ?? '')) {
    $action = Sanitize::post('action');

    if ($action === 'save') {
        $tid   = Sanitize::post('id', 'int');
        $label = Sanitize::post('label');
        $slug  = Sanitize::post('slug', 'slug') ?: Sanitize::slug($label);

        if ($label === '') {
            $erros[] = 'Nome obrigatório.';
        } else {
            if ($tid > 0) {
                DB::exec('UPDATE tags SET label = ?, slug = ? WHERE id = ?', [$label, $slug, $tid]);
                $_SESSION['flash'] = ['type'=>'ok','msg'=>'Tag atualizada.'];
            } else {
                $existe = DB::row('SELECT id FROM tags WHERE slug = ?', [$slug]);
                if ($existe) {
                    $erros[] = 'Já existe uma tag com esse nome/slug.';
                } else {
                    DB::exec('INSERT INTO tags (slug, label) VALUES (?, ?)', [$slug, $label]);
                    $_SESSION['flash'] = ['type'=>'ok','msg'=>'Tag criada.'];
                }
            }
            if (empty($erros)) { header('Location: /admin/tags/index.php'); exit; }
        }

    } elseif ($action === 'excluir') {
        $tid = Sanitize::post('id', 'int');
        $uso = DB::row('SELECT COUNT(*) n FROM lugar_tags WHERE tag_id = ?', [$tid]);
        if ((int)$uso['n'] > 0) {
            $_SESSION['flash'] = ['type'=>'erro','msg'=>'Tag em uso por ' . $uso['n'] . ' lugar(es). Remova antes de excluir.'];
        } else {
            DB::exec('DELETE FROM tags WHERE id = ?', [$tid]);
            $_SESSION['flash'] = ['type'=>'ok','msg'=>'Tag excluída.'];
        }
        header('Location: /admin/tags/index.php'); exit;
    }
}

$tags = DB::query(
    'SELECT t.*, COUNT(lt.lugar_id) AS total_lugares
     FROM tags t
     LEFT JOIN lugar_tags lt ON lt.tag_id = t.id
     GROUP BY t.id ORDER BY t.label'
);

$edit_id  = Sanitize::get('edit', 'int', 0);
$edit_tag = $edit_id ? DB::row('SELECT * FROM tags WHERE id = ?', [$edit_id]) : null;

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
        color: var(--green-dk); margin: 0 0 .25rem;
    }
    .form-card-sub {
        font-size: .78125rem; color: var(--warmgray);
        margin: 0 0 1.25rem;
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
        height: 42px; padding: 0 1.5rem;
        background: var(--green-dk); color: #fff;
        font-size: .75rem; font-weight: 900;
        letter-spacing: .1em; text-transform: uppercase;
        border: none; border-radius: .75rem;
        cursor: pointer; transition: background .2s;
        font-family: 'Montserrat', sans-serif;
        white-space: nowrap;
    }
    .btn-submit:hover { background: var(--green); }

    .btn-cancel-inline {
        height: 42px; padding: 0 1.25rem;
        display: inline-flex; align-items: center;
        background: var(--offwhite); color: var(--graphite);
        font-size: .75rem; font-weight: 700;
        border: none; border-radius: .75rem;
        text-decoration: none; transition: background .2s;
        white-space: nowrap;
    }
    .btn-cancel-inline:hover { background: var(--gold-pale); color: var(--graphite); }

    .error-inline {
        background: #fef2f2; border: 1px solid #fecaca;
        border-radius: .75rem; padding: .75rem 1rem;
        margin-bottom: 1rem;
        font-size: .8125rem; color: #dc2626;
    }

    /* ── Tag cloud card ── */
    .cloud-card {
        background: #fff;
        border: 1px solid rgba(61,71,51,.07);
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        margin-bottom: 1.5rem;
    }
    .cloud-label {
        font-size: .625rem; font-weight: 900;
        letter-spacing: .2em; text-transform: uppercase;
        color: var(--gold); margin: 0 0 1rem;
    }

    .tag-cloud-pill {
        display: inline-flex; align-items: center; gap: .375rem;
        padding: .375rem .875rem;
        border-radius: 50px;
        border: 1px solid rgba(61,71,51,.1);
        background: var(--offwhite);
        font-size: .75rem; font-weight: 600;
        color: var(--green); text-decoration: none;
        transition: background .15s, border-color .15s;
    }
    .tag-cloud-pill:hover {
        background: var(--gold-pale);
        border-color: rgba(201,170,107,.4);
        color: var(--green);
    }
    .tag-cloud-count {
        font-size: .625rem; font-weight: 900;
        color: rgba(139,133,137,.6);
    }

    /* ── List card ── */
    .list-card {
        background: #fff;
        border: 1px solid rgba(61,71,51,.07);
        border-radius: 1rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        overflow: hidden;
    }
    .list-card-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--offwhite);
    }
    .list-card-title {
        font-size: 1rem; font-weight: 700;
        color: var(--green-dk); margin: 0;
    }
    .list-card-count { font-size: .75rem; color: var(--warmgray); }
    .list-card-count strong { color: var(--graphite); }

    /* ── Tag row ── */
    .tag-row {
        display: flex; align-items: center;
        justify-content: space-between;
        padding: .75rem 1.5rem;
        border-bottom: 1px solid var(--offwhite);
        transition: background .15s;
    }
    .tag-row:last-child { border-bottom: none; }
    .tag-row:hover { background: rgba(242,240,235,.5); }

    .tag-row-name {
        font-size: .84375rem; font-weight: 600;
        color: var(--graphite);
    }
    .tag-row-sub {
        font-size: .6875rem; color: var(--warmgray);
        margin: .1rem 0 0;
    }

    .btn-edit-link {
        font-size: .6875rem; font-weight: 700;
        color: var(--gold); text-decoration: none;
        transition: color .2s;
    }
    .btn-edit-link:hover { color: var(--gold-lt); }

    .btn-delete-link {
        background: none; border: none; padding: 0;
        font-size: .6875rem; font-weight: 700;
        color: #f87171; cursor: pointer; transition: color .2s;
        font-family: 'Montserrat', sans-serif;
    }
    .btn-delete-link:hover { color: #dc2626; }

    .empty-state {
        padding: 3rem 1.5rem; text-align: center;
        font-size: .8125rem; color: var(--warmgray);
    }
</style>

<!-- ── Form nova / editar ── -->
<div class="form-card">
    <h3 class="form-card-title"><?= $edit_tag ? 'Editar tag' : 'Nova tag' ?></h3>
    <p class="form-card-sub">Tags aparecem nos cards dos lugares e ajudam na busca e filtragem.</p>

    <?php if (!empty($erros)): ?>
    <div class="error-inline"><?= Sanitize::html(implode(' ', $erros)) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
        <input type="hidden" name="action" value="save"/>
        <input type="hidden" name="id"     value="<?= (int)($edit_tag['id'] ?? 0) ?>"/>

        <div class="row g-3 align-items-end">

            <div class="col-12 col-sm">
                <label class="form-label-admin">Nome da tag *</label>
                <input type="text" name="label" id="tag-label" required
                       value="<?= Sanitize::html($edit_tag['label'] ?? '') ?>"
                       placeholder="ex: Jantar Romântico, Pet Friendly, Brunch"
                       class="form-field"
                       oninput="autoTagSlug(this.value)"/>
            </div>

            <div class="col-12 col-sm-4">
                <label class="form-label-admin">Slug</label>
                <input type="text" name="slug" id="tag-slug"
                       value="<?= Sanitize::html($edit_tag['slug'] ?? '') ?>"
                       placeholder="gerado automaticamente"
                       class="form-field"/>
            </div>

            <div class="col-12 col-sm-auto d-flex gap-2">
                <button type="submit" class="btn-submit">
                    <?= $edit_tag ? 'Salvar' : 'Criar' ?>
                </button>
                <?php if ($edit_tag): ?>
                <a href="/admin/tags/index.php" class="btn-cancel-inline">Cancelar</a>
                <?php endif; ?>
            </div>

        </div>
    </form>
</div>

<!-- ── Nuvem de tags ── -->
<?php if (!empty($tags)): ?>
<div class="cloud-card">
    <p class="cloud-label">Visualização</p>
    <div class="d-flex flex-wrap gap-2">
        <?php foreach ($tags as $t): ?>
        <a href="?edit=<?= (int)$t['id'] ?>" class="tag-cloud-pill">
            <?= Sanitize::html($t['label']) ?>
            <?php if ($t['total_lugares'] > 0): ?>
            <span class="tag-cloud-count"><?= (int)$t['total_lugares'] ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ── Lista completa ── -->
<div class="list-card">
    <div class="list-card-header">
        <h3 class="list-card-title">Todas as tags</h3>
        <span class="list-card-count">
            <strong><?= count($tags) ?></strong> cadastradas
        </span>
    </div>

    <?php if (empty($tags)): ?>
    <div class="empty-state">Nenhuma tag cadastrada ainda.</div>
    <?php else: ?>
    <?php foreach ($tags as $t): ?>
    <div class="tag-row">
        <div>
            <div class="tag-row-name"><?= Sanitize::html($t['label']) ?></div>
            <div class="tag-row-sub">
                <?= Sanitize::html($t['slug']) ?> · <?= (int)$t['total_lugares'] ?> lugar(es)
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="?edit=<?= (int)$t['id'] ?>" class="btn-edit-link">Editar</a>
            <form method="POST" style="display:inline"
                  onsubmit="return confirm('Excluir esta tag?')">
                <input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
                <input type="hidden" name="action" value="excluir"/>
                <input type="hidden" name="id"     value="<?= (int)$t['id'] ?>"/>
                <button type="submit" class="btn-delete-link">Excluir</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function autoTagSlug(val) {
    const slugField = document.getElementById('tag-slug');
    if (slugField.dataset.manual) return;
    slugField.value = val.toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/g, '').trim()
        .replace(/\s+/g, '-').replace(/-+/g, '-');
}
document.getElementById('tag-slug').addEventListener('input', function () {
    this.dataset.manual = '1';
});
</script>

<?php include __DIR__ . '/../_layout_end.php'; ?>