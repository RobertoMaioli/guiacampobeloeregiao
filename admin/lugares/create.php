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

// ── Carrega para edição ──
if ($editing) {
    $lugar = DB::row('SELECT * FROM lugares WHERE id = ?', [$id]);
    if (!$lugar) { header('Location: /admin/lugares/index.php'); exit; }
}

$page_title = $editing ? 'Editar: ' . ($lugar['nome'] ?? '') : 'Novo Lugar';

// ── Dados de apoio ──
$categorias = DB::query('SELECT id, label FROM categorias WHERE ativo = 1 ORDER BY ordem');
$servicos   = DB::query('SELECT id, nome FROM servicos ORDER BY nome');
$tags       = DB::query('SELECT id, label FROM tags ORDER BY label');
$preco_opts = ['barato'=>'Barato (R$)', 'medio'=>'Médio (R$$)', 'alto'=>'Alto (R$$$)', 'luxo'=>'Luxo (R$$$$)'];

// ── Dados atuais ao editar ──
$srv_atuais  = $editing ? array_column(DB::query('SELECT servico_id FROM lugar_servicos WHERE lugar_id=?',[$id]),'servico_id') : [];
$tags_atuais = $editing ? array_column(DB::query('SELECT tag_id FROM lugar_tags WHERE lugar_id=?',[$id]),'tag_id') : [];
$fotos_atuais= $editing ? DB::query('SELECT id,url,alt,principal,ordem FROM fotos WHERE lugar_id=? ORDER BY ordem',[$id]) : [];
$horarios_db = [];
if ($editing) {
    foreach (DB::query('SELECT * FROM horarios WHERE lugar_id=? ORDER BY dia_semana',[$id]) as $h) {
        $horarios_db[$h['dia_semana']] = $h;
    }
}

// ── POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Sanitize::csrfValid($_POST['_token'] ?? '')) {
        $erros[] = 'Token de segurança inválido. Recarregue a página.';
    } else {

        $nome    = Sanitize::post('nome');
        $slug    = Sanitize::post('slug', 'slug') ?: Sanitize::slug($nome);
        $cat_id  = Sanitize::post('categoria_id', 'int');
        $cat_lbl = Sanitize::post('cat_label');

        if ($nome === '') $erros[] = 'Nome é obrigatório.';
        if ($cat_id < 1)  $erros[] = 'Selecione uma categoria.';

        // Slug único
        if ($slug !== '') {
            $exists = DB::row(
                'SELECT id FROM lugares WHERE slug = ? AND id <> ?',
                [$slug, $id]
            );
            if ($exists) $erros[] = 'Slug já existe. Escolha outro.';
        }

        if (empty($erros)) {
            $dados = [
                'slug'              => $slug,
                'nome'              => $nome,
                'descricao'         => Sanitize::post('descricao'),
                'descricao_extra'   => Sanitize::post('descricao_extra'),
                'categoria_id'      => $cat_id,
                'cat_label'         => $cat_lbl,
                'badge'             => Sanitize::post('badge') ?: null,
                'endereco'          => Sanitize::post('endereco'),
                'bairro'            => Sanitize::post('bairro'),
                'cep'               => Sanitize::post('cep'),
                'lat'               => Sanitize::post('lat','float') ?: null,
                'lng'               => Sanitize::post('lng','float') ?: null,
                'telefone'          => Sanitize::post('telefone'),
                'email'             => Sanitize::post('email','email') ?: null,
                'site'              => Sanitize::post('site'),
                'instagram'         => Sanitize::post('instagram'),
                'whatsapp'          => Sanitize::post('whatsapp'),
                'google_place_id'   => Sanitize::post('google_place_id') ?: null,
                'preco_nivel'       => Sanitize::post('preco_nivel'),
                'preco_simbolo'     => Sanitize::post('preco_simbolo'),
                'preco_range'       => Sanitize::post('preco_range'),
                'destaque'          => Sanitize::post('destaque','bool') ? 1 : 0,
                'ativo'             => 1,
                'empresa_id'        => Sanitize::post('empresa_id','int') ?: null,
                'plano'             => in_array(Sanitize::post('plano'), ['essencial','profissional','premium'])
                                       ? Sanitize::post('plano') : 'essencial',
            ];

            try {
                DB::beginTransaction();

                if ($editing) {
                    // UPDATE
                    $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($dados)));
                    $dados['id'] = $id;
                    DB::exec("UPDATE lugares SET $sets WHERE id = :id", $dados);
                } else {
                    // INSERT
                    $cols = implode(', ', array_keys($dados));
                    $plch = implode(', ', array_map(fn($k) => ":$k", array_keys($dados)));
                    DB::exec("INSERT INTO lugares ($cols) VALUES ($plch)", $dados);
                    $id = (int) DB::lastId();
                }

                // ── Horários ──
                $dias_nomes = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
                for ($d = 0; $d <= 6; $d++) {
                    $fechado  = isset($_POST['h_fechado'][$d]) ? 1 : 0;
                    $dia_todo = isset($_POST['h_diatodo'][$d]) ? 1 : 0;
                    $abre     = $_POST['h_abre'][$d]  ?? null;
                    $fecha    = $_POST['h_fecha'][$d] ?? null;

                    DB::exec(
                        'REPLACE INTO horarios (lugar_id, dia_semana, hora_abre, hora_fecha, fechado, dia_todo)
                         VALUES (?, ?, ?, ?, ?, ?)',
                        [$id, $d, $fechado||$dia_todo ? null : ($abre ?: null),
                                  $fechado||$dia_todo ? null : ($fecha ?: null),
                                  $fechado, $dia_todo]
                    );
                }

                // ── Serviços ──
                DB::exec('DELETE FROM lugar_servicos WHERE lugar_id = ?', [$id]);
                foreach ($_POST['servicos'] ?? [] as $sid) {
                    DB::exec('INSERT INTO lugar_servicos VALUES (?,?)', [$id, (int)$sid]);
                }

                // ── Tags ──
                DB::exec('DELETE FROM lugar_tags WHERE lugar_id = ?', [$id]);
                foreach ($_POST['tags'] ?? [] as $tid) {
                    DB::exec('INSERT INTO lugar_tags VALUES (?,?)', [$id, (int)$tid]);
                }

                // ── Upload de fotos ──
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
                            DB::exec(
                                'INSERT INTO fotos (lugar_id, url, principal, ordem) VALUES (?,?,?,?)',
                                [$id, $res['url'], $isPrincipal, $i]
                            );
                        } else {
                            $erros[] = 'Foto ' . ($i+1) . ': ' . $res['erro'];
                        }
                    }
                }

                // Sincroniza empresa.lugar_id se houver vínculo
                $emp_uid = Sanitize::post('empresa_id','int');
                if ($emp_uid > 0) {
                    $emp_row = DB::row('SELECT id FROM empresas WHERE usuario_id = ?', [$emp_uid]);
                    if ($emp_row) {
                        $plano_sel = in_array(Sanitize::post('plano'), ['essencial','profissional','premium'])
                                   ? Sanitize::post('plano') : 'essencial';
                        DB::exec(
                            'UPDATE empresas SET lugar_id = ?, plano_ativo = ?, status = "aprovada"
                             WHERE usuario_id = ?',
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

// Preenche form com dados existentes ou POST
$f = $lugar ?? [];
$v = fn($k, $d='') => htmlspecialchars($_POST[$k] ?? $f[$k] ?? $d, ENT_QUOTES);

include __DIR__ . '/../_layout.php';
?>

<?php if (!empty($erros)): ?>
<div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6">
  <p class="font-bold text-red-700 text-[13px] mb-1">Corrija os erros:</p>
  <ul class="list-disc list-inside space-y-1">
    <?php foreach ($erros as $e): ?>
    <li class="text-[13px] text-red-600"><?= Sanitize::html($e) ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" novalidate>
<input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-6">

  <!-- ── COLUNA PRINCIPAL ── -->
  <div class="space-y-5">

    <!-- Informações básicas -->
    <div class="bg-white rounded-2xl border border-green/[0.07] p-6">
      <h3 class="font-display text-[16px] font-bold text-green-dark mb-5">Informações básicas</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        <div class="sm:col-span-2">
          <label class="label-field">Nome do estabelecimento *</label>
          <input type="text" name="nome" value="<?= $v('nome') ?>" required
                 class="field" oninput="autoSlug(this.value)"/>
        </div>

        <div>
          <label class="label-field">Slug (URL) *</label>
          <input type="text" name="slug" id="slug-field" value="<?= $v('slug') ?>" required
                 class="field" placeholder="ex: osteria-moderna"/>
          <p class="text-[11px] text-warmgray mt-1">/pages/<span id="slug-preview"><?= $v('slug') ?></span></p>
        </div>

        <div>
          <label class="label-field">Subtítulo da categoria</label>
          <input type="text" name="cat_label" value="<?= $v('cat_label') ?>"
                 class="field" placeholder="ex: Italiana · Contemporânea"/>
        </div>

        <div class="sm:col-span-2">
          <label class="label-field">Descrição principal</label>
          <textarea name="descricao" rows="4" class="field resize-none"><?= $v('descricao') ?></textarea>
        </div>

        <div class="sm:col-span-2">
          <label class="label-field">Descrição adicional (expansível)</label>
          <textarea name="descricao_extra" rows="3" class="field resize-none"><?= $v('descricao_extra') ?></textarea>
        </div>
      </div>
    </div>

    <!-- Localização -->
    <div class="bg-white rounded-2xl border border-green/[0.07] p-6">
      <h3 class="font-display text-[16px] font-bold text-green-dark mb-5">Localização</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
          <label class="label-field">Endereço completo</label>
          <input type="text" name="endereco" value="<?= $v('endereco') ?>"
                 class="field" placeholder="R. Lagoa Santa, 230 — Campo Belo"/>
        </div>
        <div>
          <label class="label-field">Bairro</label>
          <input type="text" name="bairro" value="<?= $v('bairro') ?>" class="field"/>
        </div>
        <div>
          <label class="label-field">CEP</label>
          <input type="text" name="cep" value="<?= $v('cep') ?>" class="field" placeholder="04553-060"/>
        </div>
        <div>
          <label class="label-field">Latitude</label>
          <input type="text" name="lat" value="<?= $v('lat') ?>" class="field" placeholder="-23.6185"/>
        </div>
        <div>
          <label class="label-field">Longitude</label>
          <input type="text" name="lng" value="<?= $v('lng') ?>" class="field" placeholder="-46.6675"/>
        </div>
      </div>
    </div>

    <!-- Contato -->
    <div class="bg-white rounded-2xl border border-green/[0.07] p-6">
      <h3 class="font-display text-[16px] font-bold text-green-dark mb-5">Contato</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="label-field">Telefone</label>
          <input type="text" name="telefone" value="<?= $v('telefone') ?>" class="field" placeholder="(11) 3045-7892"/>
        </div>
        <div>
          <label class="label-field">WhatsApp</label>
          <input type="text" name="whatsapp" value="<?= $v('whatsapp') ?>" class="field" placeholder="(11) 99999-9999"/>
        </div>
        <div>
          <label class="label-field">E-mail</label>
          <input type="email" name="email" value="<?= $v('email') ?>" class="field"/>
        </div>
        <div>
          <label class="label-field">Site</label>
          <input type="text" name="site" value="<?= $v('site') ?>" class="field" placeholder="www.site.com.br"/>
        </div>
        <div>
          <label class="label-field">Instagram</label>
          <input type="text" name="instagram" value="<?= $v('instagram') ?>" class="field" placeholder="@osteriamoderna"/>
        </div>

        <!-- Google Place ID -->
        <div class="sm:col-span-2">
          <label class="label-field">Google Place ID</label>
          <div class="flex gap-2">
            <input type="text" name="google_place_id"
                   id="google_place_id"
                   value="<?= $v('google_place_id') ?>"
                   class="field flex-1"
                   placeholder="ex: ChIJN1t_tDeuEmsRUsoyG83frY4"/>
            <?php if ($editing && !empty($lugar['google_place_id'])): ?>
            <button type="button" onclick="syncGoogle(<?= $id ?>)"
                    id="btn-sync"
                    class="px-4 py-2.5 bg-[#4285F4] hover:bg-[#3367D6] text-white text-[11px]
                           font-black tracking-widest uppercase rounded-xl transition-colors
                           flex items-center gap-2 flex-shrink-0">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                   stroke-width="2" stroke-linecap="round"><polyline points="1 4 1 10 7 10"/>
                <path d="M3.51 15a9 9 0 1 0 .49-3.51"/>
              </svg>
              Sincronizar Google
            </button>
            <?php endif; ?>
          </div>
          <p class="text-[11px] text-warmgray mt-1.5">
            Encontre o Place ID em:
            <a href="https://developers.google.com/maps/documentation/places/web-service/place-id"
               target="_blank" class="text-gold hover:underline">
              developers.google.com/maps/place-id
            </a>
          </p>
          <!-- Sync result -->
          <div id="sync-result" class="hidden mt-3 px-4 py-3 rounded-xl text-[13px] font-semibold"></div>
          <?php if ($editing && !empty($lugar['google_synced_at'])): ?>
          <p class="text-[11px] text-warmgray mt-1">
            Última sincronização: <?= date('d/m/Y H:i', strtotime($lugar['google_synced_at'])) ?>
          </p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Horários -->
    <div class="bg-white rounded-2xl border border-green/[0.07] p-6">
      <h3 class="font-display text-[16px] font-bold text-green-dark mb-5">Horários de Funcionamento</h3>
      <?php
      $dias_nomes = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
      for ($d = 0; $d <= 6; $d++):
        $hd = $horarios_db[$d] ?? null;
      ?>
      <div class="flex items-center gap-3 py-3 border-b border-offwhite last:border-0 flex-wrap">
        <span class="text-[13px] font-semibold text-graphite w-20 flex-shrink-0">
          <?= $dias_nomes[$d] ?>
        </span>
        <label class="flex items-center gap-1.5 text-[12px] text-warmgray cursor-pointer">
          <input type="checkbox" name="h_fechado[<?= $d ?>]" value="1"
                 <?= ($hd && $hd['fechado']) ? 'checked' : '' ?>
                 onchange="toggleHorario(<?= $d ?>)"
                 class="rounded accent-[#3d4733]"/>
          Fechado
        </label>
        <label class="flex items-center gap-1.5 text-[12px] text-warmgray cursor-pointer">
          <input type="checkbox" name="h_diatodo[<?= $d ?>]" value="1"
                 <?= ($hd && $hd['dia_todo']) ? 'checked' : '' ?>
                 onchange="toggleHorario(<?= $d ?>)"
                 class="rounded accent-[#3d4733]"/>
          Dia todo
        </label>
        <div id="h_times_<?= $d ?>"
             class="flex items-center gap-2 <?= ($hd && ($hd['fechado'] || $hd['dia_todo'])) ? 'opacity-30 pointer-events-none' : '' ?>">
          <input type="time" name="h_abre[<?= $d ?>]"
                 value="<?= $hd ? substr($hd['hora_abre']??'',0,5) : '' ?>"
                 class="field-sm"/>
          <span class="text-warmgray text-[12px]">às</span>
          <input type="time" name="h_fecha[<?= $d ?>]"
                 value="<?= $hd ? substr($hd['hora_fecha']??'',0,5) : '' ?>"
                 class="field-sm"/>
        </div>
      </div>
      <?php endfor; ?>
    </div>

    <!-- Serviços -->
    <div class="bg-white rounded-2xl border border-green/[0.07] p-6">
      <h3 class="font-display text-[16px] font-bold text-green-dark mb-5">Serviços & Comodidades</h3>
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
        <?php foreach ($servicos as $s): ?>
        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-green/[0.08]
                      cursor-pointer hover:bg-gold-pale hover:border-gold/30 transition-all
                      has-[:checked]:bg-gold-pale has-[:checked]:border-gold/40">
          <input type="checkbox" name="servicos[]" value="<?= $s['id'] ?>"
                 <?= in_array($s['id'], $srv_atuais) ? 'checked' : '' ?>
                 class="accent-[#3d4733] rounded"/>
          <span class="text-[12.5px] font-medium text-graphite"><?= Sanitize::html($s['nome']) ?></span>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Tags -->
    <div class="bg-white rounded-2xl border border-green/[0.07] p-6">
      <h3 class="font-display text-[16px] font-bold text-green-dark mb-5">Tags / Palavras-chave</h3>
      <div class="flex flex-wrap gap-2">
        <?php foreach ($tags as $t): ?>
        <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-green/[0.1]
                      cursor-pointer text-[12px] font-semibold text-warmgray
                      hover:border-gold/40 hover:text-green transition-all
                      has-[:checked]:bg-green-dark has-[:checked]:text-white has-[:checked]:border-green-dark">
          <input type="checkbox" name="tags[]" value="<?= $t['id'] ?>"
                 <?= in_array($t['id'], $tags_atuais) ? 'checked' : '' ?>
                 class="hidden"/>
          <?= Sanitize::html($t['label']) ?>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Fotos -->
    <div class="bg-white rounded-2xl border border-green/[0.07] p-6">
      <h3 class="font-display text-[16px] font-bold text-green-dark mb-2">Fotos</h3>
      <p class="text-[12.5px] text-warmgray mb-5">
        JPG, PNG ou WebP. Máx. <?= UPLOAD_MAX_MB ?>MB por foto. A primeira foto enviada vira a capa.
      </p>

      <?php if (!empty($fotos_atuais)): ?>
      <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 mb-5">
        <?php foreach ($fotos_atuais as $foto): ?>
        <div class="relative group aspect-square rounded-xl overflow-hidden border border-green/[0.08]">
          <img src="<?= Sanitize::html($foto['url']) ?>" class="w-full h-full object-cover"/>
          <?php if ($foto['principal']): ?>
          <div class="absolute top-1.5 left-1.5 px-2 py-0.5 bg-gold text-[#2a3022]
                      text-[9px] font-black rounded-full">Capa</div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <label class="flex flex-col items-center justify-center gap-3 p-8
                    border-2 border-dashed border-green/[0.15] rounded-xl cursor-pointer
                    hover:border-gold/40 hover:bg-gold-pale/30 transition-all group">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#c9aa6b"
             stroke-width="1.5" stroke-linecap="round">
          <rect x="3" y="3" width="18" height="18" rx="2"/>
          <circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
          <line x1="12" y1="12" x2="12" y2="18"/><line x1="9" y1="15" x2="15" y2="15"/>
        </svg>
        <div class="text-center">
          <p class="text-[13px] font-semibold text-graphite">Clique para selecionar fotos</p>
          <p class="text-[11.5px] text-warmgray mt-0.5">ou arraste aqui</p>
        </div>
        <input type="file" name="fotos[]" multiple accept="image/jpeg,image/png,image/webp"
               class="hidden" onchange="previewFotos(this)"/>
      </label>

      <div id="foto-preview" class="grid grid-cols-3 sm:grid-cols-5 gap-2 mt-4"></div>
    </div>

  </div><!-- /coluna principal -->


  <!-- ── SIDEBAR DIREITA ── -->
  <div class="space-y-5">

    <!-- Publicar -->
    <div class="bg-white rounded-2xl border border-green/[0.07] p-6">
      <h3 class="font-display text-[16px] font-bold text-green-dark mb-5">Publicar</h3>
      <label class="flex items-center justify-between mb-4 cursor-pointer">
        <span class="text-[13px] font-semibold text-graphite">Em destaque</span>
        <input type="checkbox" name="destaque" value="1"
               <?= ($v('destaque','0') == '1' || (!empty($lugar['destaque']))) ? 'checked' : '' ?>
               class="w-5 h-5 rounded accent-[#3d4733]"/>
      </label>
      <div class="mb-4">
        <label class="label-field">Badge</label>
        <input type="text" name="badge" value="<?= $v('badge') ?>"
               class="field" placeholder="ex: Destaque, Novo"/>
      </div>
      <div class="flex gap-2 mt-5 flex-wrap">
        <button type="submit"
                class="flex-1 py-3 bg-green-dark hover:bg-green text-white text-[12px]
                       font-black tracking-widest uppercase rounded-full transition-colors">
          <?= $editing ? 'Salvar' : 'Cadastrar' ?>
        </button>
        <a href="/admin/lugares/index.php"
           class="py-3 px-4 bg-offwhite hover:bg-gold-pale text-graphite text-[12px]
                  font-bold rounded-full transition-colors">
          Cancelar
        </a>
      </div>
    </div>

    <!-- Categoria + Preço -->
    <div class="bg-white rounded-2xl border border-green/[0.07] p-6">
      <h3 class="font-display text-[16px] font-bold text-green-dark mb-5">Categoria & Preço</h3>

      <div class="mb-4">
        <label class="label-field">Categoria *</label>
        <select name="categoria_id" required class="field">
          <option value="">Selecione…</option>
          <?php foreach ($categorias as $c): ?>
          <option value="<?= $c['id'] ?>"
                  <?= ($v('categoria_id','0') == $c['id'] || ($lugar['categoria_id'] ?? 0) == $c['id']) ? 'selected' : '' ?>>
            <?= Sanitize::html($c['label']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-4">
        <label class="label-field">Nível de preço</label>
        <select name="preco_nivel" class="field">
          <?php foreach ($preco_opts as $val => $lbl): ?>
          <option value="<?= $val ?>" <?= ($v('preco_nivel') === $val) ? 'selected' : '' ?>>
            <?= $lbl ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-4">
        <label class="label-field">Símbolo de preço</label>
        <input type="text" name="preco_simbolo" value="<?= $v('preco_simbolo','R$$') ?>"
               class="field" placeholder="R$$"/>
      </div>

      <div>
        <label class="label-field">Faixa de preço</label>
        <input type="text" name="preco_range" value="<?= $v('preco_range') ?>"
               class="field" placeholder="ex: R$85 – R$220 por pessoa"/>
      </div>
    </div>

    <!-- Empresa vinculada -->
    <div class="bg-white rounded-2xl border border-green/[0.07] p-6">
      <h3 class="font-display text-[16px] font-bold text-green-dark mb-5">Empresa &amp; Plano</h3>
      <div class="mb-4">
        <label class="label-field">Usu&aacute;rio / Empresa</label>
        <select name="empresa_id" class="field">
          <option value="">&#8212; Sem v&iacute;nculo &#8212;</option>
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
        <p class="text-[11px] text-warmgray mt-1">Vincula este lugar a um usu&aacute;rio cadastrado.</p>
      </div>
      <div>
        <label class="label-field">Plano ativo</label>
        <select name="plano" class="field">
          <?php foreach (['essencial'=>'Essencial','profissional'=>'Profissional','premium'=>'Premium'] as $pv=>$pl): ?>
          <option value="<?= $pv ?>" <?= ($lugar['plano'] ?? 'essencial') === $pv ? 'selected' : '' ?>>
            <?= $pl ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

  </div><!-- /sidebar direita -->
</div>
</form>

<!-- Styles locais -->
<style>
  .label-field {
    display:block; font-size:10px; font-weight:800;
    letter-spacing:.18em; text-transform:uppercase; color:#8b8589; margin-bottom:6px;
  }
  .field {
    width:100%; padding:10px 14px; background:#f2f0eb;
    border:1.5px solid rgba(61,71,51,.1); border-radius:12px;
    font-family:'Montserrat',sans-serif; font-size:13.5px; color:#1d1d1b;
    outline:none; transition:border-color .2s;
  }
  .field:focus { border-color:rgba(201,170,107,.55); }
  .field-sm {
    padding:7px 10px; background:#f2f0eb;
    border:1.5px solid rgba(61,71,51,.1); border-radius:8px;
    font-size:12.5px; color:#1d1d1b; outline:none;
  }
</style>

<script>
/* Auto-slug */
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

/* Horários toggle */
function toggleHorario(d) {
  const times = document.getElementById('h_times_' + d);
  const fech  = document.querySelector(`input[name="h_fechado[${d}]"]`);
  const todo  = document.querySelector(`input[name="h_diatodo[${d}]"]`);
  const off   = (fech && fech.checked) || (todo && todo.checked);
  times.classList.toggle('opacity-30', off);
  times.classList.toggle('pointer-events-none', off);
}

/* Preview fotos */
function previewFotos(input) {
  const box = document.getElementById('foto-preview');
  box.innerHTML = '';
  Array.from(input.files).forEach(file => {
    const reader = new FileReader();
    reader.onload = e => {
      const div = document.createElement('div');
      div.className = 'aspect-square rounded-xl overflow-hidden border border-green/10';
      div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover"/>`;
      box.appendChild(div);
    };
    reader.readAsDataURL(file);
  });
}
</script>

<!-- Google Sync JS -->
<script>
async function syncGoogle(lugarId) {
  const btn    = document.getElementById('btn-sync');
  const result = document.getElementById('sync-result');

  btn.disabled    = true;
  btn.innerHTML   = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="animate-spin"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg> Sincronizando…';
  result.className = 'hidden';

  try {
    const fd = new FormData();
    fd.append('_token',   '<?= Sanitize::html(Sanitize::csrfToken()) ?>');
    fd.append('modo',     'um');
    fd.append('lugar_id', lugarId);

    const res  = await fetch('/admin/google-sync.php', { method: 'POST', body: fd });
    const data = await res.json();

    result.className = data.ok
      ? 'mt-3 px-4 py-3 rounded-xl text-[13px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200'
      : 'mt-3 px-4 py-3 rounded-xl text-[13px] font-semibold bg-red-50 text-red-600 border border-red-200';

    result.textContent = data.ok ? data.msg : '✗ ' + data.erro;

  } catch(e) {
    result.className   = 'mt-3 px-4 py-3 rounded-xl text-[13px] font-semibold bg-red-50 text-red-600 border border-red-200';
    result.textContent = '✗ Erro de conexão. Tente novamente.';
  } finally {
    btn.disabled  = false;
    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg> Sincronizar Google';
  }
}
</script>

<?php include __DIR__ . '/../_layout_end.php'; ?>