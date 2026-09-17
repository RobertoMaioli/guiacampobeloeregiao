<?php
require_once __DIR__ . '/../../includes/icons.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

Auth::require();

if (!defined('SITE_URL')) require_once __DIR__ . '/../../config/mail.php';

$page_title = 'QR Codes';
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Sanitize::csrfValid($_POST['_token'] ?? '')) {
    $action = Sanitize::post('action');

    if ($action === 'save') {
        $qid    = Sanitize::post('id', 'int');
        $nome   = Sanitize::post('nome');
        $slug   = Sanitize::post('slug', 'slug') ?: Sanitize::slug($nome);
        $destino = Sanitize::str($_POST['destino_url'] ?? '', 2000);
        $ativo  = Sanitize::post('ativo', 'bool') ? 1 : 0;

        if ($nome === '') {
            $erros[] = 'Nome obrigatório.';
        } elseif ($slug === '') {
            $erros[] = 'Slug obrigatório.';
        } elseif ($destino === '' || !Sanitize::url($destino)) {
            $erros[] = 'Informe um link de destino válido (com http:// ou https://).';
        } else {
            $existe = DB::row('SELECT id FROM qrcodes WHERE slug = ? AND id <> ?', [$slug, $qid ?: 0]);
            if ($existe) {
                $erros[] = 'Já existe um QR Code com esse slug. Escolha outro.';
            } elseif ($qid > 0) {
                DB::exec(
                    'UPDATE qrcodes SET nome = ?, slug = ?, destino_url = ?, ativo = ? WHERE id = ?',
                    [$nome, $slug, $destino, $ativo, $qid]
                );
                $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'QR Code atualizado. O código impresso continua o mesmo.'];
            } else {
                DB::exec(
                    'INSERT INTO qrcodes (nome, slug, destino_url, ativo) VALUES (?, ?, ?, ?)',
                    [$nome, $slug, $destino, $ativo]
                );
                $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'QR Code criado.'];
            }
            if (empty($erros)) { header('Location: /admin/qrcodes/index.php'); exit; }
        }

    } elseif ($action === 'excluir') {
        $qid = Sanitize::post('id', 'int');
        DB::exec('DELETE FROM qrcodes WHERE id = ?', [$qid]);
        $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'QR Code excluído.'];
        header('Location: /admin/qrcodes/index.php'); exit;
    }
}

$qrcodes = DB::query('SELECT * FROM qrcodes ORDER BY criado_em DESC');

$edit_id = Sanitize::get('edit', 'int', 0);
$edit_qr = $edit_id ? DB::row('SELECT * FROM qrcodes WHERE id = ?', [$edit_id]) : null;

include __DIR__ . '/../_layout.php';
?>

<style>
    .form-card {
        background: #fff;
        border: 1px solid rgba(61,71,51,.07);
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        margin-bottom: 1.5rem;
    }
    .form-card-title { font-size: 1rem; font-weight: 700; color: var(--green-dk); margin: 0 0 .25rem; }
    .form-card-sub   { font-size: .78125rem; color: var(--warmgray); margin: 0 0 1.25rem; }

    .form-label-admin {
        display: block; font-size: .625rem; font-weight: 900;
        letter-spacing: .18em; text-transform: uppercase;
        color: var(--warmgray); margin-bottom: .375rem;
    }
    .form-field {
        width: 100%; padding: .625rem 1rem;
        background: var(--offwhite);
        border: 1px solid rgba(61,71,51,.1); border-radius: .75rem;
        font-family: 'Montserrat', sans-serif; font-size: .84375rem; color: var(--graphite);
        outline: none; transition: border-color .2s;
    }
    .form-field:focus { border-color: rgba(201,170,107,.5); }

    .form-check-inline { display: flex; align-items: center; gap: .5rem; height: 42px; }
    .form-check-inline label { font-size: .8125rem; font-weight: 600; color: var(--graphite); }

    .btn-submit {
        height: 42px; padding: 0 1.5rem;
        background: var(--green-dk); color: #fff;
        font-size: .75rem; font-weight: 900; letter-spacing: .1em; text-transform: uppercase;
        border: none; border-radius: .75rem; cursor: pointer; transition: background .2s;
        font-family: 'Montserrat', sans-serif; white-space: nowrap;
    }
    .btn-submit:hover { background: var(--green); }

    .btn-cancel-inline {
        height: 42px; padding: 0 1.25rem; display: inline-flex; align-items: center;
        background: var(--offwhite); color: var(--graphite);
        font-size: .75rem; font-weight: 700; border: none; border-radius: .75rem;
        text-decoration: none; transition: background .2s; white-space: nowrap;
    }
    .btn-cancel-inline:hover { background: var(--gold-pale); color: var(--graphite); }

    .error-inline {
        background: #fef2f2; border: 1px solid #fecaca; border-radius: .75rem;
        padding: .75rem 1rem; margin-bottom: 1rem; font-size: .8125rem; color: #dc2626;
    }
    .info-inline {
        background: var(--gold-pale); border: 1px solid rgba(201,170,107,.4); border-radius: .75rem;
        padding: .75rem 1rem; margin-bottom: 1.5rem; font-size: .8125rem; color: var(--green-dk);
    }

    .qr-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
    }
    .qr-card {
        background: #fff; border: 1px solid rgba(61,71,51,.07); border-radius: 1rem;
        padding: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,.04);
        display: flex; flex-direction: column; gap: .75rem;
    }
    .qr-card.inativo { opacity: .55; }
    .qr-card-top { display: flex; gap: 1rem; align-items: flex-start; }
    .qr-box {
        flex-shrink: 0; width: 100px; height: 100px;
        display: flex; align-items: center; justify-content: center;
        background: var(--offwhite); border-radius: .75rem; overflow: hidden;
    }
    /* O canvas é gerado em alta resolução (para impressão grande),
       mas exibido pequeno aqui no painel via CSS */
    .qr-box canvas, .qr-box img {
        width: 100px !important; height: 100px !important;
    }
    .qr-info { min-width: 0; flex: 1; }
    .qr-nome { font-size: .875rem; font-weight: 700; color: var(--graphite); margin: 0 0 .2rem; }
    .qr-slug { font-size: .6875rem; color: var(--gold); font-weight: 700; margin: 0 0 .35rem; }
    .qr-destino {
        font-size: .6875rem; color: var(--warmgray); word-break: break-all;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .qr-cliques { font-size: .6875rem; color: var(--warmgray); margin-top: .35rem; }
    .qr-cliques strong { color: var(--graphite); }
    .qr-actions {
        display: flex; flex-wrap: wrap; gap: .5rem; padding-top: .5rem; border-top: 1px solid var(--offwhite);
    }
    .qr-actions button, .qr-actions a {
        flex: 1 1 calc(50% - .25rem); text-align: center; font-size: .6875rem; font-weight: 800;
        letter-spacing: .04em; text-transform: uppercase;
        padding: .5rem; border-radius: .5rem; border: none; cursor: pointer;
        font-family: 'Montserrat', sans-serif; text-decoration: none;
    }
    .qr-btn-baixar  { background: var(--offwhite); color: var(--graphite); }
    .qr-btn-baixar:hover  { background: var(--gold-pale); }
    .qr-btn-editar  { background: var(--offwhite); color: var(--green-dk); }
    .qr-btn-editar:hover  { background: var(--gold-pale); }
    .qr-btn-excluir { background: #fef2f2; color: #dc2626; }
    .qr-btn-excluir:hover { background: #fee2e2; }

    .empty-state { padding: 3rem 1.5rem; text-align: center; font-size: .8125rem; color: var(--warmgray); }
</style>

<div class="info-inline">
    Cada QR Code impresso aponta para um endereço fixo. Trocar o <strong>link de destino</strong> aqui atualiza
    para onde o QR Code leva — sem precisar gerar ou reimprimir um novo código.
</div>

<!-- ── Form nova / editar ── -->
<div class="form-card">
    <h3 class="form-card-title"><?= $edit_qr ? 'Editar QR Code' : 'Novo QR Code' ?></h3>
    <p class="form-card-sub">Dê um nome interno para identificar onde esse QR Code será usado (ex: cartão de visita, banner, mesa 4).</p>

    <?php if (!empty($erros)): ?>
    <div class="error-inline"><?= Sanitize::html(implode(' ', $erros)) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
        <input type="hidden" name="action" value="save"/>
        <input type="hidden" name="id"     value="<?= (int)($edit_qr['id'] ?? 0) ?>"/>

        <div class="row g-3">
            <div class="col-12 col-md-5">
                <label class="form-label-admin">Nome interno *</label>
                <input type="text" name="nome" id="qr-nome" required
                       value="<?= Sanitize::html($edit_qr['nome'] ?? '') ?>"
                       placeholder="ex: Cartão de visita, Banner loja"
                       class="form-field"
                       oninput="autoQrSlug(this.value)"/>
            </div>

            <div class="col-12 col-md-2">
                <label class="form-label-admin">Slug</label>
                <input type="text" name="slug" id="qr-slug"
                       value="<?= Sanitize::html($edit_qr['slug'] ?? '') ?>"
                       placeholder="gerado automaticamente"
                       class="form-field"/>
            </div>

            <div class="col-12 col-md-5">
                <label class="form-label-admin">Link de destino *</label>
                <input type="url" name="destino_url" required
                       value="<?= Sanitize::html($edit_qr['destino_url'] ?? '') ?>"
                       placeholder="https://..."
                       class="form-field"/>
            </div>
        </div>

        <div class="row g-3 align-items-end mt-1">
            <div class="col-auto form-check-inline">
                <input type="checkbox" name="ativo" id="qr-ativo" value="1"
                       <?= (!$edit_qr || $edit_qr['ativo']) ? 'checked' : '' ?>/>
                <label for="qr-ativo">Ativo</label>
            </div>

            <div class="col d-flex gap-2 justify-content-end">
                <button type="submit" class="btn-submit"><?= $edit_qr ? 'Salvar' : 'Criar' ?></button>
                <?php if ($edit_qr): ?>
                <a href="/admin/qrcodes/index.php" class="btn-cancel-inline">Cancelar</a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- ── Lista de QR Codes ── -->
<?php if (empty($qrcodes)): ?>
<div class="empty-state">Nenhum QR Code cadastrado ainda.</div>
<?php else: ?>
<div class="qr-grid">
    <?php foreach ($qrcodes as $q):
        $linkFixo = rtrim(SITE_URL, '/') . '/q.php?c=' . urlencode($q['slug']);
    ?>
    <div class="qr-card <?= $q['ativo'] ? '' : 'inativo' ?>">
        <div class="qr-card-top">
            <div class="qr-box" id="qr-<?= Sanitize::html($q['slug']) ?>"
                 data-url="<?= Sanitize::html($linkFixo) ?>"></div>
            <div class="qr-info">
                <p class="qr-nome"><?= Sanitize::html($q['nome']) ?></p>
                <p class="qr-slug"><?= Sanitize::html($q['slug']) ?></p>
                <p class="qr-destino" title="<?= Sanitize::html($q['destino_url']) ?>">
                    → <?= Sanitize::html($q['destino_url']) ?>
                </p>
                <p class="qr-cliques"><strong><?= (int)$q['cliques'] ?></strong> escaneamentos</p>
            </div>
        </div>
        <div class="qr-actions">
            <button type="button" class="qr-btn-baixar" onclick="baixarQR('<?= Sanitize::html($q['slug']) ?>')">PNG fundo branco</button>
            <button type="button" class="qr-btn-baixar" onclick="baixarQRTransparente('<?= Sanitize::html($q['slug']) ?>')">PNG transparente</button>
            <a href="?edit=<?= (int)$q['id'] ?>" class="qr-btn-editar">Editar</a>
            <form method="POST" style="flex:1;display:contents" onsubmit="return confirm('Excluir este QR Code? Se ele já estiver impresso em algum lugar, vai parar de funcionar.')">
                <input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
                <input type="hidden" name="action" value="excluir"/>
                <input type="hidden" name="id"     value="<?= (int)$q['id'] ?>"/>
                <button type="submit" class="qr-btn-excluir">Excluir</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
// Gera visualmente cada QR Code no navegador (o link em si nunca muda)
document.querySelectorAll('.qr-box').forEach(function (el) {
    new QRCode(el, {
        text: el.dataset.url,
        width: 640,
        height: 640,
        colorDark: '#1d1d1b',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
    });
});

function baixarQR(slug) {
    var box = document.getElementById('qr-' + slug);
    var canvas = box ? box.querySelector('canvas') : null;
    if (!canvas) return;
    var link = document.createElement('a');
    link.download = 'qrcode-' + slug + '.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
}

// Gera uma versão à parte, com fundo transparente, só na hora do download
// (o QR exibido no painel continua com fundo branco, para ficar legível ali)
function baixarQRTransparente(slug) {
    var box = document.getElementById('qr-' + slug);
    if (!box) return;
    var url = box.dataset.url;

    var temp = document.createElement('div');
    temp.style.position = 'fixed';
    temp.style.left = '-9999px';
    document.body.appendChild(temp);

    new QRCode(temp, {
        text: url,
        width: 640,
        height: 640,
        colorDark: '#1d1d1b',
        colorLight: 'rgba(0,0,0,0)',
        correctLevel: QRCode.CorrectLevel.M
    });

    var canvas = temp.querySelector('canvas');
    if (canvas) {
        var link = document.createElement('a');
        link.download = 'qrcode-' + slug + '-transparente.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }
    document.body.removeChild(temp);
}

function autoQrSlug(val) {
    var slugField = document.getElementById('qr-slug');
    if (slugField.dataset.manual) return;
    slugField.value = val.toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/g, '').trim()
        .replace(/\s+/g, '-').replace(/-+/g, '-');
}
document.getElementById('qr-slug').addEventListener('input', function () {
    this.dataset.manual = '1';
});
</script>

<?php include __DIR__ . '/../_layout_end.php'; ?>