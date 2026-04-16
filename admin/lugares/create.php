<?php
/**
 * admin/lugares/create.php — Cadastro de novo lugar
 * admin/lugares/edit.php   — Edição (redireciona aqui com ?id=X)
 */
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';
require_once __DIR__ . '/../../core/Upload.php';

Auth::require();

$id      = Sanitize::get('id', 'int', 0);
$editing = $id > 0;
$lugar   = null;
$erros   = [];

if ($editing) {
    $lugar = DB::row('SELECT * FROM lugares WHERE id = ?', [$id]);
    if (!$lugar) { header('Location: /admin/lugares/index.php'); exit; }
}

$page_title = $editing ? 'Editar: ' . ($lugar['nome'] ?? '') : 'Novo Lugar';

$categorias = DB::query('SELECT id, label FROM categorias WHERE ativo = 1 ORDER BY ordem');
$servicos   = DB::query('SELECT id, nome FROM servicos ORDER BY nome');
$tags       = DB::query('SELECT id, label FROM tags ORDER BY label');
$preco_opts = ['barato'=>'Barato (R$)', 'medio'=>'Médio (R$$)', 'alto'=>'Alto (R$$$)', 'luxo'=>'Luxo (R$$$$)'];

$srv_atuais  = $editing ? array_column(DB::query('SELECT servico_id FROM lugar_servicos WHERE lugar_id=?',[$id]),'servico_id') : [];
$tags_atuais = $editing ? array_column(DB::query('SELECT tag_id FROM lugar_tags WHERE lugar_id=?',[$id]),'tag_id') : [];
$fotos_atuais= $editing ? DB::query('SELECT id,url,alt,principal,ordem FROM fotos WHERE lugar_id=? ORDER BY ordem',[$id]) : [];
$horarios_db = [];
if ($editing) {
    foreach (DB::query('SELECT * FROM horarios WHERE lugar_id=? ORDER BY dia_semana',[$id]) as $h) {
        $horarios_db[$h['dia_semana']] = $h;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Sanitize::csrfValid($_POST['_token'] ?? '')) {
        $erros[] = 'Token de segurança inválido. Recarregue a página.';
    } else {
        $nome   = Sanitize::post('nome');
        $slug   = Sanitize::post('slug', 'slug') ?: Sanitize::slug($nome);
        $cat_id = Sanitize::post('categoria_id', 'int');
        $cat_lbl= Sanitize::post('cat_label');

        if ($nome === '') $erros[] = 'Nome é obrigatório.';
        if ($cat_id < 1)  $erros[] = 'Selecione uma categoria.';

        if ($slug !== '') {
            $exists = DB::row('SELECT id FROM lugares WHERE slug = ? AND id <> ?', [$slug, $id]);
            if ($exists) $erros[] = 'Slug já existe. Escolha outro.';
        }

        if (empty($erros)) {
            $dados = [
                'slug'            => $slug,
                'nome'            => $nome,
                'descricao'       => Sanitize::post('descricao'),
                'descricao_extra' => Sanitize::post('descricao_extra'),
                'categoria_id'    => $cat_id,
                'cat_label'       => $cat_lbl,
                'badge'           => Sanitize::post('badge') ?: null,
                'endereco'        => Sanitize::post('endereco'),
                'bairro'          => Sanitize::post('bairro'),
                'cep'             => Sanitize::post('cep'),
                'lat'             => Sanitize::post('lat','float') ?: null,
                'lng'             => Sanitize::post('lng','float') ?: null,
                'telefone'        => Sanitize::post('telefone'),
                'email'           => Sanitize::post('email','email') ?: null,
                'site'            => Sanitize::post('site'),
                'instagram'       => Sanitize::post('instagram'),
                'whatsapp'        => Sanitize::post('whatsapp'),
                'google_place_id' => Sanitize::post('google_place_id') ?: null,
                'preco_nivel'     => Sanitize::post('preco_nivel'),
                'preco_simbolo'   => Sanitize::post('preco_simbolo'),
                'preco_range'     => Sanitize::post('preco_range'),
                'destaque'        => Sanitize::post('destaque','bool') ? 1 : 0,
                'ativo'           => 1,
                'empresa_id'      => Sanitize::post('empresa_id','int') ?: null,
                'plano'           => in_array(Sanitize::post('plano'), ['essencial','profissional','premium'])
                                     ? Sanitize::post('plano') : 'essencial',
            ];

            try {
                DB::beginTransaction();

                if ($editing) {
                    $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($dados)));
                    $dados['id'] = $id;
                    DB::exec("UPDATE lugares SET $sets WHERE id = :id", $dados);
                } else {
                    $cols = implode(', ', array_keys($dados));
                    $plch = implode(', ', array_map(fn($k) => ":$k", array_keys($dados)));
                    DB::exec("INSERT INTO lugares ($cols) VALUES ($plch)", $dados);
                    $id = (int) DB::lastId();
                }

                $dias_nomes = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
                for ($d = 0; $d <= 6; $d++) {
                    $fechado  = isset($_POST['h_fechado'][$d]) ? 1 : 0;
                    $dia_todo = isset($_POST['h_diatodo'][$d]) ? 1 : 0;
                    $abre     = $_POST['h_abre'][$d]  ?? null;
                    $fecha    = $_POST['h_fecha'][$d] ?? null;
                    DB::exec(
                        'REPLACE INTO horarios (lugar_id, dia_semana, hora_abre, hora_fecha, fechado, dia_todo)
                         VALUES (?, ?, ?, ?, ?, ?)',
                        [$id, $d,
                         $fechado||$dia_todo ? null : ($abre ?: null),
                         $fechado||$dia_todo ? null : ($fecha ?: null),
                         $fechado, $dia_todo]
                    );
                }

                DB::exec('DELETE FROM lugar_servicos WHERE lugar_id = ?', [$id]);
                foreach ($_POST['servicos'] ?? [] as $sid) {
                    DB::exec('INSERT INTO lugar_servicos VALUES (?,?)', [$id, (int)$sid]);
                }

                DB::exec('DELETE FROM lugar_tags WHERE lugar_id = ?', [$id]);
                foreach ($_POST['tags'] ?? [] as $tid) {
                    DB::exec('INSERT INTO lugar_tags VALUES (?,?)', [$id, (int)$tid]);
                }

                if (!empty($_FILES['fotos']['name'][0])) {
                    $sub = 'lugares/' . $id . '/';
                    foreach ($_FILES['fotos']['name'] as $i => $name) {
                        if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK) continue;
                        $file = [
                            'name'     => $name,
                            'type'     => $_FILES['fotos']['type'][$i],
                            'tmp_name' => $_FILES['fotos']['tmp_name'][$i],
                            'error'    => $_FILES['fotos']['error'][$i],
                            'size'     => $_FILES['fotos']['size'][$i],
                        ];
                        $res = Upload::image($file, $sub);
                        if ($res['ok']) {
                            $isPrincipal = ($i === 0 && empty($fotos_atuais)) ? 1 : 0;
                            DB::exec('INSERT INTO fotos (lugar_id, url, principal, ordem) VALUES (?,?,?,?)',
                                [$id, $res['url'], $isPrincipal, $i]);
                        } else {
                            $erros[] = 'Foto ' . ($i+1) . ': ' . $res['erro'];
                        }
                    }
                }

                $emp_uid = Sanitize::post('empresa_id','int');
                if ($emp_uid > 0) {
                    $emp_row = DB::row('SELECT id FROM empresas WHERE usuario_id = ?', [$emp_uid]);
                    if ($emp_row) {
                        $plano_sel = in_array(Sanitize::post('plano'), ['essencial','profissional','premium'])
                                   ? Sanitize::post('plano') : 'essencial';
                        DB::exec(
                            'UPDATE empresas SET lugar_id = ?, plano_ativo = ?, status = "aprovada" WHERE usuario_id = ?',
                            [$id, $plano_sel, $emp_uid]
                        );
                    }
                }

                DB::commit();
                $_SESSION['flash'] = ['type'=>'ok','msg'=> $editing ? 'Lugar atualizado!' : 'Lugar cadastrado!'];
                header('Location: /admin/lugares/edit.php?id=' . $id);
                exit;

            } catch (Exception $e) {
                DB::rollback();
                error_log('[ADMIN] Erro ao salvar lugar: ' . $e->getMessage());
                $erros[] = 'Erro interno. Tente novamente.';
            }
        }
    }
}

$f = $lugar ?? [];
$v = fn($k, $d='') => htmlspecialchars($_POST[$k] ?? $f[$k] ?? $d, ENT_QUOTES);

include __DIR__ . '/../_layout.php';
?>

<style>
    /* ── Field system ── */
    .form-label-admin {
        display: block;
        font-size: .625rem; font-weight: 900;
        letter-spacing: .18em; text-transform: uppercase;
        color: var(--warmgray); margin-bottom: .375rem;
    }
    .form-field {
        width: 100%;
        padding: .625rem .875rem;
        background: var(--offwhite);
        border: 1.5px solid rgba(61,71,51,.1);
        border-radius: .75rem;
        font-family: 'Montserrat', sans-serif;
        font-size: .84375rem;
        color: var(--graphite);
        outline: none;
        transition: border-color .2s;
    }
    .form-field:focus { border-color: rgba(201,170,107,.55); }
    textarea.form-field { resize: none; }

    .form-field-sm {
        padding: .4375rem .625rem;
        background: var(--offwhite);
        border: 1.5px solid rgba(61,71,51,.1);
        border-radius: .5rem;
        font-size: .78125rem;
        color: var(--graphite);
        outline: none;
    }

    /* ── Section cards ── */
    .form-card {
        background: #fff;
        border: 1px solid rgba(61,71,51,.07);
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
        margin-bottom: 1.25rem;
    }
    .form-card-title {
        font-size: 1rem; font-weight: 700;
        color: var(--green-dk); margin: 0 0 1.25rem;
    }

    /* ── Error box ── */
    .error-box {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 1rem;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }
    .error-box-title {
        font-size: .8125rem; font-weight: 700; color: #b91c1c; margin-bottom: .375rem;
    }
    .error-box ul { margin: 0; padding-left: 1.25rem; }
    .error-box li { font-size: .8125rem; color: #dc2626; }

    /* ── Horários row ── */
    .hora-row {
        display: flex; align-items: center; flex-wrap: wrap;
        gap: .75rem; padding: .75rem 0;
        border-bottom: 1px solid var(--offwhite);
    }
    .hora-row:last-child { border-bottom: none; }
    .hora-dia {
        font-size: .8125rem; font-weight: 600;
        color: var(--graphite); width: 72px; flex-shrink: 0;
    }
    .hora-check {
        display: flex; align-items: center; gap: .375rem;
        font-size: .75rem; color: var(--warmgray); cursor: pointer;
    }
    .hora-check input { accent-color: var(--green); }
    .hora-times { display: flex; align-items: center; gap: .5rem; }
    .hora-times.disabled { opacity: .3; pointer-events: none; }
    .hora-sep { font-size: .75rem; color: var(--warmgray); }

    /* ── Serviços grid ── */
    .servico-opt {
        display: flex; align-items: center; gap: .625rem;
        padding: .75rem; border-radius: .75rem;
        border: 1px solid rgba(61,71,51,.08);
        cursor: pointer; transition: background .15s, border-color .15s;
    }
    .servico-opt:hover { background: var(--gold-pale); border-color: rgba(201,170,107,.3); }
    .servico-opt input { accent-color: var(--green); }
    .servico-opt span { font-size: .78125rem; font-weight: 500; color: var(--graphite); }
    .servico-opt:has(input:checked) { background: var(--gold-pale); border-color: rgba(201,170,107,.4); }

    /* ── Tags pills ── */
    .tag-pill {
        display: inline-flex; align-items: center; gap: .375rem;
        padding: .375rem .875rem;
        border-radius: 50px;
        border: 1px solid rgba(61,71,51,.1);
        font-size: .75rem; font-weight: 600;
        color: var(--warmgray); cursor: pointer;
        transition: all .15s;
    }
    .tag-pill:hover { border-color: rgba(201,170,107,.4); color: var(--green); }
    .tag-pill input { display: none; }
    .tag-pill:has(input:checked) {
        background: var(--green-dk); color: #fff; border-color: var(--green-dk);
    }

    /* ── Drop zone ── */
    .drop-zone {
        display: flex; flex-direction: column; align-items: center;
        justify-content: center; gap: .75rem;
        padding: 2rem;
        border: 2px dashed rgba(61,71,51,.15);
        border-radius: .75rem; cursor: pointer;
        transition: border-color .2s, background .2s;
    }
    .drop-zone:hover { border-color: rgba(201,170,107,.4); background: rgba(245,237,218,.3); }
    .drop-zone p { margin: 0; }

    /* ── Foto thumbs ── */
    .foto-thumb {
        position: relative;
        border-radius: .75rem; overflow: hidden;
        border: 1px solid rgba(61,71,51,.08);
        aspect-ratio: 1;
    }
    .foto-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .foto-thumb .badge-capa {
        position: absolute; top: 6px; left: 6px;
        padding: .1rem .375rem;
        background: var(--gold); color: var(--green-dk);
        font-size: .5625rem; font-weight: 900;
        border-radius: 50px;
    }

    /* ── Sidebar save button ── */
    .btn-save {
        flex: 1; padding: .75rem 1rem;
        background: var(--green-dk); color: #fff;
        font-size: .75rem; font-weight: 900;
        letter-spacing: .1em; text-transform: uppercase;
        border: none; border-radius: 50px;
        cursor: pointer; transition: background .2s;
        font-family: 'Montserrat', sans-serif;
    }
    .btn-save:hover { background: var(--green); }

    .btn-cancel {
        padding: .75rem 1rem;
        background: var(--offwhite); color: var(--graphite);
        font-size: .75rem; font-weight: 700;
        border: none; border-radius: 50px;
        text-decoration: none; transition: background .2s;
    }
    .btn-cancel:hover { background: var(--gold-pale); color: var(--graphite); }

    /* ── Google sync button ── */
    .btn-gsync {
        display: inline-flex; align-items: center; gap: .5rem;
        padding: .625rem 1rem;
        background: #4285F4; color: #fff;
        font-size: .6875rem; font-weight: 900;
        letter-spacing: .08em; text-transform: uppercase;
        border: none; border-radius: .75rem;
        cursor: pointer; transition: background .2s;
        font-family: 'Montserrat', sans-serif;
        white-space: nowrap; flex-shrink: 0;
    }
    .btn-gsync:hover { background: #3367D6; }
    .btn-gsync:disabled { opacity: .7; cursor: not-allowed; }

    /* ── Sync result ── */
    .sync-result { display:none; margin-top:.75rem; padding:.75rem 1rem; border-radius:.75rem; font-size:.8125rem; font-weight:600; }
    .sync-result.ok  { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
    .sync-result.err { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }

    @keyframes spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
</style>

<?php if (!empty($erros)): ?>
<div class="error-box">
    <p class="error-box-title">Corrija os erros:</p>
    <ul>
        <?php foreach ($erros as $e): ?>
        <li><?= Sanitize::html($e) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" novalidate>
<input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>

<div class="row g-4 align-items-start">

    <!-- ══ COLUNA PRINCIPAL ══ -->
    <div class="col-12 col-xl-8">

        <!-- Informações básicas -->
        <div class="form-card">
            <h3 class="form-card-title">Informações básicas</h3>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label-admin">Nome do estabelecimento *</label>
                    <input type="text" name="nome" value="<?= $v('nome') ?>" required
                           class="form-field" oninput="autoSlug(this.value)"/>
                </div>
                <div class="col-12 col-sm-6">
                    <label class="form-label-admin">Slug (URL) *</label>
                    <input type="text" name="slug" id="slug-field" value="<?= $v('slug') ?>"
                           required class="form-field" placeholder="ex: osteria-moderna"/>
                    <p style="font-size:.6875rem; color:var(--warmgray); margin:.25rem 0 0">
                        /pages/<span id="slug-preview"><?= $v('slug') ?></span>
                    </p>
                </div>
                <div class="col-12 col-sm-6">
                    <label class="form-label-admin">Subtítulo da categoria</label>
                    <input type="text" name="cat_label" value="<?= $v('cat_label') ?>"
                           class="form-field" placeholder="ex: Italiana · Contemporânea"/>
                </div>
                <div class="col-12">
                    <label class="form-label-admin">Descrição principal</label>
                    <textarea name="descricao" rows="4" class="form-field"><?= $v('descricao') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label-admin">Descrição adicional (expansível)</label>
                    <textarea name="descricao_extra" rows="3" class="form-field"><?= $v('descricao_extra') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Localização -->
        <div class="form-card">
            <h3 class="form-card-title">Localização</h3>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label-admin">Endereço completo</label>
                    <input type="text" name="endereco" value="<?= $v('endereco') ?>"
                           class="form-field" placeholder="R. Lagoa Santa, 230 — Campo Belo"/>
                </div>
                <div class="col-12 col-sm-6">
                    <label class="form-label-admin">Bairro</label>
                    <input type="text" name="bairro" value="<?= $v('bairro') ?>" class="form-field"/>
                </div>
                <div class="col-12 col-sm-6">
                    <label class="form-label-admin">CEP</label>
                    <input type="text" name="cep" value="<?= $v('cep') ?>" class="form-field" placeholder="04553-060"/>
                </div>
                <div class="col-12 col-sm-6">
                    <label class="form-label-admin">Latitude</label>
                    <input type="text" name="lat" value="<?= $v('lat') ?>" class="form-field" placeholder="-23.6185"/>
                </div>
                <div class="col-12 col-sm-6">
                    <label class="form-label-admin">Longitude</label>
                    <input type="text" name="lng" value="<?= $v('lng') ?>" class="form-field" placeholder="-46.6675"/>
                </div>
            </div>
        </div>

        <!-- Contato -->
        <div class="form-card">
            <h3 class="form-card-title">Contato</h3>
            <div class="row g-3">
                <div class="col-12 col-sm-6">
                    <label class="form-label-admin">Telefone</label>
                    <input type="text" name="telefone" value="<?= $v('telefone') ?>"
                           class="form-field" placeholder="(11) 3045-7892"/>
                </div>
                <div class="col-12 col-sm-6">
                    <label class="form-label-admin">WhatsApp</label>
                    <input type="text" name="whatsapp" value="<?= $v('whatsapp') ?>"
                           class="form-field" placeholder="(11) 99999-9999"/>
                </div>
                <div class="col-12 col-sm-6">
                    <label class="form-label-admin">E-mail</label>
                    <input type="email" name="email" value="<?= $v('email') ?>" class="form-field"/>
                </div>
                <div class="col-12 col-sm-6">
                    <label class="form-label-admin">Site</label>
                    <input type="text" name="site" value="<?= $v('site') ?>"
                           class="form-field" placeholder="www.site.com.br"/>
                </div>
                <div class="col-12 col-sm-6">
                    <label class="form-label-admin">Instagram</label>
                    <input type="text" name="instagram" value="<?= $v('instagram') ?>"
                           class="form-field" placeholder="@osteriamoderna"/>
                </div>
                <div class="col-12">
                    <label class="form-label-admin">Google Place ID</label>
                    <div class="d-flex gap-2">
                        <input type="text" name="google_place_id" id="google_place_id"
                               value="<?= $v('google_place_id') ?>" class="form-field"
                               placeholder="ex: ChIJN1t_tDeuEmsRUsoyG83frY4"/>
                        <?php if ($editing && !empty($lugar['google_place_id'])): ?>
                        <button type="button" id="btn-sync" class="btn-gsync"
                                onclick="syncGoogle(<?= $id ?>)">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <polyline points="1 4 1 10 7 10"/>
                                <path d="M3.51 15a9 9 0 1 0 .49-3.51"/>
                            </svg>
                            Sincronizar Google
                        </button>
                        <?php endif; ?>
                    </div>
                    <p style="font-size:.6875rem; color:var(--warmgray); margin:.375rem 0 0">
                        Encontre o Place ID em:
                        <a href="https://developers.google.com/maps/documentation/places/web-service/place-id"
                           target="_blank" style="color:var(--gold)">developers.google.com/maps/place-id</a>
                    </p>
                    <div id="sync-result" class="sync-result"></div>
                    <?php if ($editing && !empty($lugar['google_synced_at'])): ?>
                    <p style="font-size:.6875rem; color:var(--warmgray); margin:.25rem 0 0">
                        Última sincronização: <?= date('d/m/Y H:i', strtotime($lugar['google_synced_at'])) ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Horários -->
        <div class="form-card">
            <h3 class="form-card-title">Horários de Funcionamento</h3>
            <?php
            $dias_nomes = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
            for ($d = 0; $d <= 6; $d++):
                $hd = $horarios_db[$d] ?? null;
                $disabled = $hd && ($hd['fechado'] || $hd['dia_todo']);
            ?>
            <div class="hora-row">
                <span class="hora-dia"><?= $dias_nomes[$d] ?></span>
                <label class="hora-check">
                    <input type="checkbox" name="h_fechado[<?= $d ?>]" value="1"
                           <?= ($hd && $hd['fechado']) ? 'checked' : '' ?>
                           onchange="toggleHorario(<?= $d ?>)">
                    Fechado
                </label>
                <label class="hora-check">
                    <input type="checkbox" name="h_diatodo[<?= $d ?>]" value="1"
                           <?= ($hd && $hd['dia_todo']) ? 'checked' : '' ?>
                           onchange="toggleHorario(<?= $d ?>)">
                    Dia todo
                </label>
                <div id="h_times_<?= $d ?>" class="hora-times <?= $disabled ? 'disabled' : '' ?>">
                    <input type="time" name="h_abre[<?= $d ?>]"
                           value="<?= $hd ? substr($hd['hora_abre']??'',0,5) : '' ?>"
                           class="form-field-sm"/>
                    <span class="hora-sep">às</span>
                    <input type="time" name="h_fecha[<?= $d ?>]"
                           value="<?= $hd ? substr($hd['hora_fecha']??'',0,5) : '' ?>"
                           class="form-field-sm"/>
                </div>
            </div>
            <?php endfor; ?>
        </div>

        <!-- Serviços -->
        <div class="form-card">
            <h3 class="form-card-title">Serviços & Comodidades</h3>
            <div class="row g-2">
                <?php foreach ($servicos as $s): ?>
                <div class="col-6 col-sm-4">
                    <label class="servico-opt">
                        <input type="checkbox" name="servicos[]" value="<?= $s['id'] ?>"
                               <?= in_array($s['id'], $srv_atuais) ? 'checked' : '' ?>>
                        <span><?= Sanitize::html($s['nome']) ?></span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tags -->
        <div class="form-card">
            <h3 class="form-card-title">Tags / Palavras-chave</h3>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($tags as $t): ?>
                <label class="tag-pill">
                    <input type="checkbox" name="tags[]" value="<?= $t['id'] ?>"
                           <?= in_array($t['id'], $tags_atuais) ? 'checked' : '' ?>>
                    <?= Sanitize::html($t['label']) ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Fotos -->
        <div class="form-card">
            <h3 class="form-card-title">Fotos</h3>
            <p style="font-size:.78125rem; color:var(--warmgray); margin-bottom:1.25rem">
                JPG, PNG ou WebP. Máx. <?= UPLOAD_MAX_MB ?>MB por foto. A primeira foto enviada vira a capa.
            </p>

            <?php if (!empty($fotos_atuais)): ?>
            <div class="row g-2 mb-4">
                <?php foreach ($fotos_atuais as $foto): ?>
                <div class="col-4 col-sm-3">
                    <div class="foto-thumb">
                        <img src="<?= Sanitize::html($foto['url']) ?>" alt="">
                        <?php if ($foto['principal']): ?>
                        <span class="badge-capa">Capa</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <label class="drop-zone">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                     stroke="var(--gold)" stroke-width="1.5" stroke-linecap="round">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                    <line x1="12" y1="12" x2="12" y2="18"/>
                    <line x1="9" y1="15" x2="15" y2="15"/>
                </svg>
                <div style="text-align:center">
                    <p style="font-size:.8125rem; font-weight:600; color:var(--graphite); margin:0">
                        Clique para selecionar fotos
                    </p>
                    <p style="font-size:.71875rem; color:var(--warmgray); margin:.25rem 0 0">
                        ou arraste aqui
                    </p>
                </div>
                <input type="file" name="fotos[]" multiple accept="image/jpeg,image/png,image/webp"
                       style="display:none" onchange="previewFotos(this)"/>
            </label>

            <div id="foto-preview" class="row g-2 mt-2"></div>
        </div>

    </div><!-- /col principal -->


    <!-- ══ SIDEBAR DIREITA ══ -->
    <div class="col-12 col-xl-4">

        <!-- Publicar -->
        <div class="form-card">
            <h3 class="form-card-title">Publicar</h3>

            <div class="d-flex align-items-center justify-content-between mb-3">
                <span style="font-size:.8125rem; font-weight:600; color:var(--graphite)">Em destaque</span>
                <input type="checkbox" name="destaque" value="1"
                       <?= ($v('destaque','0') == '1' || (!empty($lugar['destaque']))) ? 'checked' : '' ?>
                       style="width:20px; height:20px; accent-color:var(--green); cursor:pointer">
            </div>

            <div style="margin-bottom:1rem">
                <label class="form-label-admin">Badge</label>
                <input type="text" name="badge" value="<?= $v('badge') ?>"
                       class="form-field" placeholder="ex: Destaque, Novo"/>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn-save">
                    <?= $editing ? 'Salvar' : 'Cadastrar' ?>
                </button>
                <a href="/admin/lugares/index.php" class="btn-cancel">Cancelar</a>
            </div>
        </div>

        <!-- Categoria & Preço -->
        <div class="form-card">
            <h3 class="form-card-title">Categoria & Preço</h3>

            <div style="margin-bottom:1rem">
                <label class="form-label-admin">Categoria *</label>
                <select name="categoria_id" required class="form-field">
                    <option value="">Selecione…</option>
                    <?php foreach ($categorias as $c): ?>
                    <option value="<?= $c['id'] ?>"
                            <?= ($v('categoria_id','0') == $c['id'] || ($lugar['categoria_id'] ?? 0) == $c['id']) ? 'selected' : '' ?>>
                        <?= Sanitize::html($c['label']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom:1rem">
                <label class="form-label-admin">Nível de preço</label>
                <select name="preco_nivel" class="form-field">
                    <?php foreach ($preco_opts as $val => $lbl): ?>
                    <option value="<?= $val ?>" <?= ($v('preco_nivel') === $val) ? 'selected' : '' ?>>
                        <?= $lbl ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom:1rem">
                <label class="form-label-admin">Símbolo de preço</label>
                <input type="text" name="preco_simbolo" value="<?= $v('preco_simbolo','R$$') ?>"
                       class="form-field" placeholder="R$$"/>
            </div>

            <div>
                <label class="form-label-admin">Faixa de preço</label>
                <input type="text" name="preco_range" value="<?= $v('preco_range') ?>"
                       class="form-field" placeholder="ex: R$85 – R$220 por pessoa"/>
            </div>
        </div>

        <!-- Empresa & Plano -->
        <div class="form-card">
            <h3 class="form-card-title">Empresa & Plano</h3>

            <div style="margin-bottom:1rem">
                <label class="form-label-admin">Usuário / Empresa</label>
                <select name="empresa_id" class="form-field">
                    <option value="">— Sem vínculo —</option>
                    <?php
                    $usuarios_lista = DB::query(
                        'SELECT u.id, u.nome, u.email, e.status
                         FROM usuarios u
                         LEFT JOIN empresas e ON e.usuario_id = u.id
                         ORDER BY u.nome'
                    );
                    foreach ($usuarios_lista as $ul):
                        $selected = (!empty($lugar['empresa_id']) && (int)$lugar['empresa_id'] === (int)$ul['id']) ? 'selected' : '';
                    ?>
                    <option value="<?= (int)$ul['id'] ?>" <?= $selected ?>>
                        <?= Sanitize::html($ul['nome']) ?> (<?= Sanitize::html($ul['email']) ?>)
                        <?= $ul['status'] ? '— ' . $ul['status'] : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <p style="font-size:.6875rem; color:var(--warmgray); margin:.375rem 0 0">
                    Vincula este lugar a um usuário cadastrado.
                </p>
            </div>

            <div>
                <label class="form-label-admin">Plano ativo</label>
                <select name="plano" class="form-field">
                    <?php foreach (['essencial'=>'Essencial','profissional'=>'Profissional','premium'=>'Premium'] as $pv=>$pl): ?>
                    <option value="<?= $pv ?>" <?= ($lugar['plano'] ?? 'essencial') === $pv ? 'selected' : '' ?>>
                        <?= $pl ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

    </div><!-- /sidebar -->

</div><!-- /row -->
</form>

<script>
/* ── Auto-slug ── */
function autoSlug(val) {
    if (document.getElementById('slug-field').dataset.manual) return;
    const slug = val.toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g,'')
        .replace(/[^a-z0-9\s-]/g,'').trim()
        .replace(/\s+/g,'-').replace(/-+/g,'-');
    document.getElementById('slug-field').value = slug;
    document.getElementById('slug-preview').textContent = slug;
}
document.getElementById('slug-field').addEventListener('input', function(){
    this.dataset.manual = '1';
    document.getElementById('slug-preview').textContent = this.value;
});

/* ── Horários toggle ── */
function toggleHorario(d) {
    const times = document.getElementById('h_times_' + d);
    const fech  = document.querySelector(`input[name="h_fechado[${d}]"]`);
    const todo  = document.querySelector(`input[name="h_diatodo[${d}]"]`);
    const off   = (fech && fech.checked) || (todo && todo.checked);
    times.classList.toggle('disabled', off);
}

/* ── Preview fotos ── */
function previewFotos(input) {
    const box = document.getElementById('foto-preview');
    box.innerHTML = '';
    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const col = document.createElement('div');
            col.className = 'col-4 col-sm-3';
            col.innerHTML = `<div class="foto-thumb"><img src="${e.target.result}" alt=""></div>`;
            box.appendChild(col);
        };
        reader.readAsDataURL(file);
    });
}

/* ── Google Sync ── */
async function syncGoogle(lugarId) {
    const btn    = document.getElementById('btn-sync');
    const result = document.getElementById('sync-result');

    btn.disabled  = true;
    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="animation:spin 1s linear infinite"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg> Sincronizando…';
    result.style.display = 'none';

    try {
        const fd = new FormData();
        fd.append('_token',   '<?= Sanitize::html(Sanitize::csrfToken()) ?>');
        fd.append('modo',     'um');
        fd.append('lugar_id', lugarId);

        const res  = await fetch('/admin/google-sync.php', { method:'POST', body:fd });
        const data = await res.json();

        result.className   = 'sync-result ' + (data.ok ? 'ok' : 'err');
        result.textContent = data.ok ? data.msg : '✗ ' + data.erro;
        result.style.display = 'block';
    } catch(e) {
        result.className   = 'sync-result err';
        result.textContent = '✗ Erro de conexão. Tente novamente.';
        result.style.display = 'block';
    } finally {
        btn.disabled  = false;
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg> Sincronizar Google';
    }
}
</script>

<?php include __DIR__ . '/../_layout_end.php'; ?>