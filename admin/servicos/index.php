<?php
require_once __DIR__ . '/../../includes/icons.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

Auth::require();

$page_title = 'Serviços & Comodidades';
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Sanitize::csrfValid($_POST['_token'] ?? '')) {
    $action = Sanitize::post('action');

    if ($action === 'save') {
        $sid  = Sanitize::post('id', 'int');
        $nome = Sanitize::post('nome');
        $icon = Sanitize::post('icon') ?: 'verified';

        if ($nome === '') {
            $erros[] = 'Nome obrigatório.';
        } else {
            if ($sid > 0) {
                DB::exec('UPDATE servicos SET nome = ?, icon = ? WHERE id = ?', [$nome, $icon, $sid]);
                $_SESSION['flash'] = ['type'=>'ok','msg'=>'Serviço atualizado.'];
            } else {
                $existe = DB::row('SELECT id FROM servicos WHERE nome = ?', [$nome]);
                if ($existe) {
                    $erros[] = 'Já existe um serviço com esse nome.';
                } else {
                    DB::exec('INSERT INTO servicos (nome, icon) VALUES (?, ?)', [$nome, $icon]);
                    $_SESSION['flash'] = ['type'=>'ok','msg'=>'Serviço criado.'];
                }
            }
            if (empty($erros)) { header('Location: /admin/servicos/index.php'); exit; }
        }

    } elseif ($action === 'excluir') {
        $sid = Sanitize::post('id', 'int');
        $uso = DB::row('SELECT COUNT(*) n FROM lugar_servicos WHERE servico_id = ?', [$sid]);
        if ((int)$uso['n'] > 0) {
            $_SESSION['flash'] = ['type'=>'erro','msg'=>'Serviço em uso por ' . $uso['n'] . ' lugar(es). Remova antes de excluir.'];
        } else {
            DB::exec('DELETE FROM servicos WHERE id = ?', [$sid]);
            $_SESSION['flash'] = ['type'=>'ok','msg'=>'Serviço excluído.'];
        }
        header('Location: /admin/servicos/index.php'); exit;
    }
}

$servicos = DB::query(
    'SELECT s.*, COUNT(ls.lugar_id) AS total_lugares
     FROM servicos s
     LEFT JOIN lugar_servicos ls ON ls.servico_id = s.id
     GROUP BY s.id ORDER BY s.nome'
);

$edit_id  = Sanitize::get('edit', 'int', 0);
$edit_srv = $edit_id ? DB::row('SELECT * FROM servicos WHERE id = ?', [$edit_id]) : null;

$icons_disponiveis = [
    'verified','wifi','pin','phone','mail','star','heart','award',
    'coffee','utensils','wine','paw','spa','scissors','dumbbell',
    'shopping-bag','activity','map','clock','users','building',
    'monitor','briefcase','camera','music','leaf','card',
];

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
    .list-card-count {
        font-size: .75rem; color: var(--warmgray);
    }
    .list-card-count strong { color: var(--graphite); }

    /* ── Service row ── */
    .service-row {
        display: flex; align-items: center;
        justify-content: space-between;
        padding: .875rem 1.5rem;
        border-bottom: 1px solid var(--offwhite);
        transition: background .15s;
    }
    .service-row:last-child { border-bottom: none; }
    .service-row:hover { background: rgba(242,240,235,.5); }

    .service-icon-wrap {
        width: 32px; height: 32px;
        border-radius: .5rem;
        background: var(--gold-pale);
        color: var(--green);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .service-name {
        font-size: .84375rem; font-weight: 600;
        color: var(--graphite);
    }
    .service-sub {
        font-size: .6875rem; color: var(--warmgray);
        margin: .1rem 0 0;
    }

    .service-icon-label {
        font-family: monospace; font-size: .6875rem;
        color: rgba(139,133,137,.6);
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
    <h3 class="form-card-title">
        <?= $edit_srv ? 'Editar serviço' : 'Novo serviço' ?>
    </h3>

    <?php if (!empty($erros)): ?>
    <div class="error-inline"><?= Sanitize::html(implode(' ', $erros)) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
        <input type="hidden" name="action" value="save"/>
        <input type="hidden" name="id"     value="<?= (int)($edit_srv['id'] ?? 0) ?>"/>

        <div class="row g-3 align-items-end">

            <div class="col-12 col-sm">
                <label class="form-label-admin">Nome *</label>
                <input type="text" name="nome" required
                       value="<?= Sanitize::html($edit_srv['nome'] ?? '') ?>"
                       placeholder="ex: Wi-Fi, Estacionamento, Visa/Master"
                       class="form-field"/>
            </div>

            <div class="col-12 col-sm-auto" style="min-width:180px">
                <label class="form-label-admin">Ícone</label>
                <select name="icon" class="form-field">
                    <?php foreach ($icons_disponiveis as $ic): ?>
                    <option value="<?= $ic ?>"
                            <?= ($edit_srv['icon'] ?? 'verified') === $ic ? 'selected' : '' ?>>
                        <?= $ic ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 col-sm-auto d-flex gap-2">
                <button type="submit" class="btn-submit">
                    <?= $edit_srv ? 'Salvar' : 'Criar' ?>
                </button>
                <?php if ($edit_srv): ?>
                <a href="/admin/servicos/index.php" class="btn-cancel-inline">Cancelar</a>
                <?php endif; ?>
            </div>

        </div>
    </form>
</div>

<!-- ── Lista ── -->
<div class="list-card">
    <div class="list-card-header">
        <h3 class="list-card-title">Todos os serviços</h3>
        <span class="list-card-count">
            <strong><?= count($servicos) ?></strong> cadastrados
        </span>
    </div>

    <?php if (empty($servicos)): ?>
    <div class="empty-state">Nenhum serviço cadastrado ainda.</div>
    <?php else: ?>
    <?php foreach ($servicos as $s): ?>
    <div class="service-row">

        <!-- Ícone + nome -->
        <div class="d-flex align-items-center gap-3">
            <div class="service-icon-wrap">
                <?= icon($s['icon'] ?? 'verified', 15) ?>
            </div>
            <div>
                <div class="service-name"><?= Sanitize::html($s['nome']) ?></div>
                <div class="service-sub"><?= (int)$s['total_lugares'] ?> lugar(es)</div>
            </div>
        </div>

        <!-- Ações -->
        <div class="d-flex align-items-center gap-3">
            <span class="service-icon-label"><?= Sanitize::html($s['icon']) ?></span>

            <a href="?edit=<?= (int)$s['id'] ?>" class="btn-edit-link">Editar</a>

            <form method="POST" style="display:inline"
                  onsubmit="return confirm('Excluir este serviço?')">
                <input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
                <input type="hidden" name="action" value="excluir"/>
                <input type="hidden" name="id"     value="<?= (int)$s['id'] ?>"/>
                <button type="submit" class="btn-delete-link">Excluir</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../_layout_end.php'; ?>