<?php
/**
 * empresa/editar.php
 * Edição do perfil da empresa pelo empresário
 */
require_once __DIR__ . '/../core/UserAuth.php';
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Sanitize.php';
require_once __DIR__ . '/../core/Upload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/icons.php';

UserAuth::require();
$usuario = UserAuth::current();

// Sessão ativa mas usuário excluído do banco
if (!$usuario) {
    UserAuth::logout();
    header('Location: /empresa/login.php'); exit;
}

// Apenas aprovadas podem editar
if (!in_array($usuario['empresa_status'] ?? '', ['aprovada'])) {
    header('Location: /empresa/status.php'); exit;
}

$empresa_id = (int)($usuario['empresa_id'] ?? 0);
$lugar_id   = (int)($usuario['lugar_id']   ?? 0);
$plano      = $usuario['plano_ativo'] ?? 'essencial';

$lugar = DB::row('SELECT * FROM lugares WHERE id=? AND empresa_id=?', [$lugar_id, $empresa_id]);
if (!$lugar) { header('Location: /empresa/dashboard.php'); exit; }

// Features por plano
$features = [
    'essencial'    => ['max_fotos'=>1,  'max_tags'=>0,  'mapa'=>false,'google'=>false,'whatsapp'=>false,'redes'=>false],
    'profissional' => ['max_fotos'=>5,  'max_tags'=>5,  'mapa'=>true, 'google'=>true, 'whatsapp'=>true, 'redes'=>true],
    'premium'      => ['max_fotos'=>999,'max_tags'=>999,'mapa'=>true, 'google'=>true, 'whatsapp'=>true, 'redes'=>true],
];
$feat = $features[$plano] ?? $features['essencial'];

// Dados de suporte
$categorias  = DB::query('SELECT id,label FROM categorias WHERE ativo=1 ORDER BY ordem');
$tags_all    = DB::query('SELECT id,label FROM tags ORDER BY label');
$tags_atuais = array_column(DB::query('SELECT tag_id FROM lugar_tags WHERE lugar_id=?',[$lugar_id]),'tag_id');
$fotos       = DB::query('SELECT id,url,principal,ordem FROM fotos WHERE lugar_id=? ORDER BY principal DESC,ordem ASC',[$lugar_id]);
$horarios_db = [];
$dias_nomes  = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
foreach (DB::query('SELECT * FROM horarios WHERE lugar_id=? ORDER BY dia_semana',[$lugar_id]) as $h) {
    $horarios_db[$h['dia_semana']] = $h;
}

$erros  = [];
$sucesso = false;

// ── POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Sanitize::csrfValid($_POST['_token'] ?? '')) {
        $erros[] = 'Token inválido. Recarregue a página.';
    } else {
        $acao = $_POST['acao'] ?? 'dados';

        // ── Salvar dados principais ──
        if ($acao === 'dados') {
            $nome      = Sanitize::post('nome');
            $cat_id    = Sanitize::post('categoria_id','int');
            $cat_label = Sanitize::post('cat_label');
            $descricao = Sanitize::post('descricao');
            $descr_ext = Sanitize::post('descricao_extra');
            $endereco  = Sanitize::post('endereco');
            $bairro    = Sanitize::post('bairro');
            $cep       = Sanitize::post('cep');
            $telefone  = Sanitize::post('telefone');
            $email_emp = Sanitize::post('email','email') ?: null;
            $whatsapp  = $feat['whatsapp'] ? Sanitize::post('whatsapp') : $lugar['whatsapp'];
            $site      = $feat['redes']    ? Sanitize::post('site')     : $lugar['site'];
            $instagram = $feat['redes']    ? Sanitize::post('instagram'): $lugar['instagram'];
            $facebook  = $feat['redes']    ? Sanitize::post('facebook') : $lugar['facebook'];

            if (!$nome)     $erros[] = 'Nome é obrigatório.';
            if (!$endereco) $erros[] = 'Endereço é obrigatório.';

            if (empty($erros)) {
                DB::exec(
                    'UPDATE lugares SET
                        nome=?,categoria_id=?,cat_label=?,descricao=?,descricao_extra=?,
                        endereco=?,bairro=?,cep=?,telefone=?,email=?,
                        whatsapp=?,site=?,instagram=?,facebook=?,
                        atualizado_em=NOW()
                     WHERE id=? AND empresa_id=?',
                    [$nome,$cat_id?:null,$cat_label,$descricao,$descr_ext,
                     $endereco,$bairro,$cep,$telefone,$email_emp,
                     $whatsapp,$site,$instagram,$facebook,
                     $lugar_id,$empresa_id]
                );
                $sucesso = true;
                $lugar   = DB::row('SELECT * FROM lugares WHERE id=?',[$lugar_id]);
            }
        }

        // ── Salvar horários ──
        if ($acao === 'horarios') {
            for ($d = 0; $d <= 6; $d++) {
                $fechado  = isset($_POST['h_fechado'][$d]) ? 1 : 0;
                $dia_todo = isset($_POST['h_diatodo'][$d]) ? 1 : 0;
                $abre     = $_POST['h_abre'][$d]  ?? null;
                $fecha    = $_POST['h_fecha'][$d] ?? null;
                DB::exec(
                    'REPLACE INTO horarios (lugar_id,dia_semana,hora_abre,hora_fecha,fechado,dia_todo)
                     VALUES (?,?,?,?,?,?)',
                    [$lugar_id,$d,
                     ($fechado||$dia_todo)?null:($abre?:null),
                     ($fechado||$dia_todo)?null:($fecha?:null),
                     $fechado,$dia_todo]
                );
            }
            $sucesso = true;
            foreach (DB::query('SELECT * FROM horarios WHERE lugar_id=? ORDER BY dia_semana',[$lugar_id]) as $h)
                $horarios_db[$h['dia_semana']] = $h;
        }

        // ── Salvar tags ──
        if ($acao === 'tags' && $feat['max_tags'] > 0) {
            $novas = array_slice(array_map('intval', $_POST['tags'] ?? []), 0, $feat['max_tags'] ?: 999);
            DB::exec('DELETE FROM lugar_tags WHERE lugar_id=?',[$lugar_id]);
            foreach ($novas as $tid)
                DB::exec('INSERT INTO lugar_tags VALUES (?,?)',[$lugar_id,$tid]);
            $tags_atuais = $novas;
            $sucesso = true;
        }

        // ── Upload de foto ──
        if ($acao === 'foto' && !empty($_FILES['foto']['name'])) {
            $atual_count = count($fotos);
            if ($atual_count >= $feat['max_fotos']) {
                $erros[] = 'Limite de fotos do seu plano atingido (' . $feat['max_fotos'] . ').';
            } else {
                $res = Upload::image($_FILES['foto'], 'lugares/'.$lugar_id.'/');
                if ($res['ok']) {
                    $is_principal = empty($fotos) ? 1 : 0;
                    DB::exec(
                        'INSERT INTO fotos (lugar_id,url,principal,ordem) VALUES (?,?,?,?)',
                        [$lugar_id,$res['url'],$is_principal,$atual_count]
                    );
                    if ($is_principal)
                        DB::exec('UPDATE lugares SET foto_principal=? WHERE id=?',[$res['url'],$lugar_id]);
                    $fotos   = DB::query('SELECT id,url,principal,ordem FROM fotos WHERE lugar_id=? ORDER BY principal DESC,ordem ASC',[$lugar_id]);
                    $sucesso = true;
                } else {
                    $erros[] = $res['erro'];
                }
            }
        }

        // ── Remover foto ──
        if ($acao === 'remover_foto') {
            $foto_id = Sanitize::post('foto_id','int');
            $foto    = DB::row('SELECT * FROM fotos f JOIN lugares l ON l.id=f.lugar_id WHERE f.id=? AND l.empresa_id=?',[$foto_id,$empresa_id]);
            if ($foto) {
                DB::exec('DELETE FROM fotos WHERE id=?',[$foto_id]);
                // Se era principal, promove a próxima
                if ($foto['principal']) {
                    $prox = DB::row('SELECT id,url FROM fotos WHERE lugar_id=? LIMIT 1',[$lugar_id]);
                    if ($prox) {
                        DB::exec('UPDATE fotos SET principal=1 WHERE id=?',[$prox['id']]);
                        DB::exec('UPDATE lugares SET foto_principal=? WHERE id=?',[$prox['url'],$lugar_id]);
                    } else {
                        DB::exec('UPDATE lugares SET foto_principal=NULL WHERE id=?',[$lugar_id]);
                    }
                }
                $fotos   = DB::query('SELECT id,url,principal,ordem FROM fotos WHERE lugar_id=? ORDER BY principal DESC,ordem ASC',[$lugar_id]);
                $sucesso = true;
            }
        }

        // Redirect pós-POST para evitar resubmit
        if ($sucesso && empty($erros)) {
            $_SESSION['flash_empresa'] = 'Alterações salvas com sucesso!';
            header('Location: /empresa/editar.php'); exit;
        }
    }
}

// Flash message
$flash = $_SESSION['flash_empresa'] ?? '';
unset($_SESSION['flash_empresa']);

$csrf       = Sanitize::csrfToken();
$page_title = 'Editar empresa — Guia Campo Belo';
$v = fn($k,$d='') => htmlspecialchars($lugar[$k] ?? $d, ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
<script>
/* Força header opaco imediatamente nestas páginas de painel */
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('site-header')?.classList.add('header-scrolled');
});
</script>
  <style>
    body{background:var(--gcb-offwhite);padding-top:72px}
    
    .edit-wrap{max-width:860px;margin:0 auto;padding:1.5rem 1rem 3rem}
    .section-hdr{font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--gcb-warmgray);margin:24px 0 10px;display:flex;align-items:center;gap:8px}
    .section-hdr:first-child{margin-top:0}
    .edit-card{background:#fff;border-radius:20px;border:1px solid rgba(61,71,51,.07);padding:22px;margin-bottom:14px;box-shadow:0 2px 10px rgba(29,29,27,.04)}
    .olbl{display:block;font-size:10px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:var(--gcb-warmgray);margin-bottom:5px}
    .g2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    @media(max-width:540px){.g2{grid-template-columns:1fr}}
    .locked-field{opacity:.45;pointer-events:none;position:relative}
    .lock-tag{font-size:9px;font-weight:700;color:var(--gcb-gold);background:var(--gcb-gold-pale);padding:2px 6px;border-radius:4px;margin-left:5px}
    .foto-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:8px}
    .fthumb{aspect-ratio:1;border-radius:10px;overflow:hidden;position:relative;background:var(--gcb-offwhite)}
    .fthumb img{width:100%;height:100%;object-fit:cover}
    .fcapa{position:absolute;top:4px;left:4px;font-size:8px;font-weight:800;background:var(--gcb-gold);color:var(--gcb-green-dark);padding:2px 6px;border-radius:4px;text-transform:uppercase;letter-spacing:.06em}
    .frem{position:absolute;top:4px;right:4px;width:20px;height:20px;border-radius:50%;background:rgba(29,29,27,.65);color:#fff;font-size:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;border:none;padding:0;line-height:1}
    .foto-add-box{aspect-ratio:1;border-radius:10px;border:2px dashed rgba(61,71,51,.14);display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;color:var(--gcb-warmgray);font-size:11px;gap:4px;transition:all .2s}
    .foto-add-box:hover{border-color:rgba(201,170,107,.5);color:var(--gcb-gold);background:var(--gcb-gold-pale)}
    .hora-row{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:.5px solid rgba(61,71,51,.07);flex-wrap:wrap}
    .hora-row:last-child{border-bottom:none}
    .hora-dia{font-size:12px;font-weight:700;color:var(--gcb-graphite);width:70px;flex-shrink:0}
    .hck{display:flex;align-items:center;gap:4px;font-size:11px;color:var(--gcb-warmgray);cursor:pointer}
    .htimes{display:flex;align-items:center;gap:6px;transition:opacity .2s}
    .htimes.off{opacity:.2;pointer-events:none}
    .hinput{padding:7px 10px;background:var(--gcb-offwhite);border:1.5px solid rgba(61,71,51,.1);border-radius:8px;font-size:12px;width:88px;outline:none;font-family:inherit}
    .hinput:focus{border-color:rgba(201,170,107,.55)}
    .tag-sel{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:999px;border:1.5px solid rgba(61,71,51,.1);font-size:12px;font-weight:600;color:var(--gcb-warmgray);cursor:pointer;transition:all .2s;user-select:none}
    .tag-sel input{display:none}
    .tag-sel:has(input:checked){background:var(--gcb-green-dark);color:#fff;border-color:var(--gcb-green-dark)}
    .flash-ok{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;border-radius:12px;padding:12px 16px;font-size:13px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:8px}
    .err-box{background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:12px 16px;font-size:13px;color:#c0392b;margin-bottom:16px}
    .btn-save{width:100%;padding:13px;background:var(--gcb-green-dark);color:#fff;border:none;border-radius:999px;font-size:12px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;cursor:pointer;transition:background .2s}
    .btn-save:hover{background:var(--gcb-green)}
    .plan-lock-banner{background:var(--gcb-gold-pale);border:1.5px solid rgba(201,170,107,.35);border-radius:12px;padding:12px 14px;font-size:12px;color:var(--gcb-green-dark);display:flex;align-items:center;gap:10px;margin-bottom:14px}
  </style>
</head>
<body>

<?php include __DIR__ . '/../includes/search-modal.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="edit-wrap">

  <h1 style="font-size:22px;font-weight:800;color:var(--gcb-green-dark);margin-bottom:4px">
    Editar empresa
  </h1>
  <p style="font-size:13px;color:var(--gcb-warmgray);margin-bottom:20px">
    <?= Sanitize::html($lugar['nome']) ?> ·
    <span style="font-weight:700;color:var(--gcb-green)"><?= ucfirst($plano) ?></span>
  </p>

  <?php if ($flash): ?>
  <div class="flash-ok">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
    <?= Sanitize::html($flash) ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($erros)): ?>
  <div class="err-box">
    <?php foreach($erros as $e): ?><div>✗ <?= Sanitize::html($e) ?></div><?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- ══ DADOS PRINCIPAIS ══ -->
  <div class="section-hdr"><?= icon('building',14) ?> Informações da empresa</div>
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="<?= Sanitize::html($csrf) ?>">
    <input type="hidden" name="acao"   value="dados">

    <div class="edit-card">
      <div class="mb-3">
        <label class="olbl">Nome da empresa *</label>
        <input type="text" name="nome" class="gcb-field" required
               value="<?= $v('nome') ?>" placeholder="Nome da empresa">
      </div>
      <div class="g2 mb-3">
        <div>
          <label class="olbl">Categoria</label>
          <select name="categoria_id" class="gcb-field">
            <option value="">Selecione…</option>
            <?php foreach($categorias as $cat): ?>
            <option value="<?= $cat['id'] ?>"
                    <?= ($lugar['categoria_id']??'')==$cat['id']?'selected':'' ?>>
              <?= Sanitize::html($cat['label']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="olbl">Subtítulo da categoria</label>
          <input type="text" name="cat_label" class="gcb-field"
                 value="<?= $v('cat_label') ?>" placeholder="Ex: Italiana · Contemporânea">
        </div>
      </div>
      <div class="mb-3">
        <label class="olbl">Descrição principal</label>
        <textarea name="descricao" class="gcb-field" rows="4"
                  placeholder="Descreva sua empresa…"><?= $v('descricao') ?></textarea>
      </div>
      <div>
        <label class="olbl">Descrição adicional</label>
        <textarea name="descricao_extra" class="gcb-field" rows="3"
                  placeholder="Informações extras (expansível na página)…"><?= $v('descricao_extra') ?></textarea>
      </div>
    </div>

    <!-- Localização -->
    <div class="edit-card">
      <div style="font-size:13px;font-weight:800;color:var(--gcb-green-dark);margin-bottom:14px;display:flex;align-items:center;gap:6px">
        <?= icon('pin',14) ?> Localização
      </div>
      <div class="mb-3">
        <label class="olbl">Endereço completo *</label>
        <input type="text" name="endereco" class="gcb-field" required
               value="<?= $v('endereco') ?>" placeholder="Rua, número, bairro">
      </div>
      <div class="g2">
        <div>
          <label class="olbl">Bairro</label>
          <input type="text" name="bairro" class="gcb-field"
                 value="<?= $v('bairro') ?>" placeholder="Campo Belo">
        </div>
        <div>
          <label class="olbl">CEP</label>
          <input type="text" name="cep" class="gcb-field"
                 value="<?= $v('cep') ?>" placeholder="04553-060">
        </div>
      </div>
    </div>

    <!-- Contato -->
    <div class="edit-card">
      <div style="font-size:13px;font-weight:800;color:var(--gcb-green-dark);margin-bottom:14px;display:flex;align-items:center;gap:6px">
        <?= icon('phone',14) ?> Contato
      </div>
      <div class="g2 mb-3">
        <div>
          <label class="olbl">Telefone</label>
          <input type="text" name="telefone" class="gcb-field"
                 value="<?= $v('telefone') ?>" placeholder="(11) 3045-7892">
        </div>
        <div>
          <label class="olbl">
            WhatsApp
            <?php if(!$feat['whatsapp']): ?><span class="lock-tag">Profissional+</span><?php endif; ?>
          </label>
          <input type="text" name="whatsapp" class="gcb-field <?= !$feat['whatsapp']?'locked-field':'' ?>"
                 value="<?= $v('whatsapp') ?>" placeholder="(11) 99999-9999"
                 <?= !$feat['whatsapp']?'disabled':'' ?>>
        </div>
      </div>
      <div class="g2 mb-3">
        <div>
          <label class="olbl">E-mail</label>
          <input type="email" name="email" class="gcb-field"
                 value="<?= $v('email') ?>" placeholder="contato@empresa.com">
        </div>
        <div class="<?= !$feat['redes']?'locked-field':'' ?>">
          <label class="olbl">
            Site
            <?php if(!$feat['redes']): ?><span class="lock-tag">Profissional+</span><?php endif; ?>
          </label>
          <input type="text" name="site" class="gcb-field"
                 value="<?= $v('site') ?>" placeholder="https://seusite.com.br"
                 <?= !$feat['redes']?'disabled':'' ?>>
        </div>
      </div>
      <div class="g2">
        <div class="<?= !$feat['redes']?'locked-field':'' ?>">
          <label class="olbl">
            Instagram
            <?php if(!$feat['redes']): ?><span class="lock-tag">Profissional+</span><?php endif; ?>
          </label>
          <input type="text" name="instagram" class="gcb-field"
                 value="<?= $v('instagram') ?>" placeholder="@suaempresa"
                 <?= !$feat['redes']?'disabled':'' ?>>
        </div>
        <div class="<?= !$feat['redes']?'locked-field':'' ?>">
          <label class="olbl">
            Facebook
            <?php if(!$feat['redes']): ?><span class="lock-tag">Profissional+</span><?php endif; ?>
          </label>
          <input type="text" name="facebook" class="gcb-field"
                 value="<?= $v('facebook') ?>" placeholder="facebook.com/suaempresa"
                 <?= !$feat['redes']?'disabled':'' ?>>
        </div>
      </div>
    </div>

    <button type="submit" class="btn-save">Salvar informações</button>
  </form>

  <!-- ══ FOTOS ══ -->
  <div class="section-hdr" id="fotos" style="margin-top:28px"><?= icon('grid',14) ?> Fotos</div>

  <?php if (!$feat['max_fotos'] || $feat['max_fotos'] <= 1): ?>
  <div class="plan-lock-banner">
    <?= icon('award',16) ?>
    <div>Seu plano permite apenas 1 foto. <a href="/empresa/plano.php" style="color:var(--gcb-green-dark);font-weight:700">Faça upgrade</a> para adicionar mais.</div>
  </div>
  <?php endif; ?>

  <div class="edit-card">
    <div class="foto-grid" style="margin-bottom:<?= empty($fotos)?'0':'12px' ?>">
      <?php foreach($fotos as $foto): ?>
      <div class="fthumb">
        <img src="<?= Sanitize::html($foto['url']) ?>" alt="">
        <?php if($foto['principal']): ?>
        <span class="fcapa">capa</span>
        <?php endif; ?>
        <form method="POST" style="display:inline" onsubmit="return confirm('Remover esta foto?')">
          <input type="hidden" name="_token"   value="<?= Sanitize::html($csrf) ?>">
          <input type="hidden" name="acao"     value="remover_foto">
          <input type="hidden" name="foto_id"  value="<?= (int)$foto['id'] ?>">
          <button type="submit" class="frem">✕</button>
        </form>
      </div>
      <?php endforeach; ?>

      <?php if(count($fotos) < ($feat['max_fotos'] ?: 1)): ?>
      <label class="foto-add-box">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.5" stroke-linecap="round">
          <rect x="3" y="3" width="18" height="18" rx="2"/>
          <circle cx="8.5" cy="8.5" r="1.5"/>
          <polyline points="21 15 16 10 5 21"/>
        </svg>
        Adicionar
        <form method="POST" enctype="multipart/form-data" id="form-foto">
          <input type="hidden" name="_token" value="<?= Sanitize::html($csrf) ?>">
          <input type="hidden" name="acao"   value="foto">
          <input type="file" name="foto" accept="image/jpeg,image/png,image/webp"
                 style="display:none" onchange="this.form.submit()" id="inp-foto">
        </form>
      </label>
      <?php endif; ?>
    </div>
    <p style="font-size:11px;color:var(--gcb-warmgray)">
      <?= count($fotos) ?>/<?= $feat['max_fotos']>=999?'∞':$feat['max_fotos'] ?> fotos · JPG, PNG ou WebP · máx. <?= UPLOAD_MAX_MB ?>MB
    </p>
  </div>

  <!-- ══ HORÁRIOS ══ -->
  <div class="section-hdr" style="margin-top:28px"><?= icon('clock',14) ?> Horários de funcionamento</div>
  <form method="POST">
    <input type="hidden" name="_token" value="<?= Sanitize::html($csrf) ?>">
    <input type="hidden" name="acao"   value="horarios">
    <div class="edit-card">
      <?php for($d=0;$d<=6;$d++):
        $hd=$horarios_db[$d]??null;
        $fec=$hd&&$hd['fechado'];
        $dtd=$hd&&$hd['dia_todo'];
        $ab=$hd?substr($hd['hora_abre']??'',0,5):'';
        $fc=$hd?substr($hd['hora_fecha']??'',0,5):'';
      ?>
      <div class="hora-row">
        <span class="hora-dia"><?= $dias_nomes[$d] ?></span>
        <label class="hck">
          <input type="checkbox" name="h_fechado[<?= $d ?>]" id="hf<?= $d ?>"
                 <?= $fec?'checked':'' ?> onchange="togH(<?= $d ?>)"
                 style="accent-color:var(--gcb-green-dark)">
          Fechado
        </label>
        <label class="hck">
          <input type="checkbox" name="h_diatodo[<?= $d ?>]" id="hd<?= $d ?>"
                 <?= $dtd?'checked':'' ?> onchange="togH(<?= $d ?>)"
                 style="accent-color:var(--gcb-green-dark)">
          Dia todo
        </label>
        <div class="htimes <?= ($fec||$dtd)?'off':'' ?>" id="ht<?= $d ?>">
          <input type="time" name="h_abre[<?= $d ?>]" class="hinput"
                 value="<?= htmlspecialchars($ab) ?>">
          <span style="font-size:11px;color:var(--gcb-warmgray)">às</span>
          <input type="time" name="h_fecha[<?= $d ?>]" class="hinput"
                 value="<?= htmlspecialchars($fc) ?>">
        </div>
      </div>
      <?php endfor; ?>
    </div>
    <button type="submit" class="btn-save">Salvar horários</button>
  </form>

  <!-- ══ TAGS ══ -->
  <?php if($feat['max_tags'] > 0): ?>
  <div class="section-hdr" style="margin-top:28px"><?= icon('sparkles',14) ?> Tags
    <span style="font-size:10px;font-weight:500;color:var(--gcb-warmgray)">
      (até <?= $feat['max_tags']>=999?'ilimitadas':$feat['max_tags'] ?>)
    </span>
  </div>
  <form method="POST">
    <input type="hidden" name="_token" value="<?= Sanitize::html($csrf) ?>">
    <input type="hidden" name="acao"   value="tags">
    <div class="edit-card">
      <div style="display:flex;flex-wrap:wrap;gap:8px">
        <?php foreach($tags_all as $t): ?>
        <label class="tag-sel">
          <input type="checkbox" name="tags[]" value="<?= $t['id'] ?>"
                 <?= in_array($t['id'],$tags_atuais)?'checked':'' ?>>
          <?= Sanitize::html($t['label']) ?>
        </label>
        <?php endforeach; ?>
      </div>
      <p style="font-size:11px;color:var(--gcb-warmgray);margin-top:12px">
        Selecione até <?= $feat['max_tags']>=999?'quantas quiser':$feat['max_tags'] ?> tags que representam sua empresa.
      </p>
    </div>
    <button type="submit" class="btn-save">Salvar tags</button>
  </form>
  <?php else: ?>
  <div class="section-hdr" style="margin-top:28px"><?= icon('sparkles',14) ?> Tags</div>
  <div class="plan-lock-banner">
    <?= icon('award',16) ?>
    <div>Tags disponíveis no plano Profissional e Premium. <a href="/empresa/plano.php" style="color:var(--gcb-green-dark);font-weight:700">Ver upgrade →</a></div>
  </div>
  <?php endif; ?>

</div><!-- /edit-wrap -->

<script>
function togH(d){
  const f=document.getElementById('hf'+d).checked;
  const t=document.getElementById('hd'+d).checked;
  document.getElementById('ht'+d).classList.toggle('off',f||t);
}
// Clique na foto-add-box abre o input
document.querySelector('.foto-add-box')?.addEventListener('click',function(e){
  if(e.target.tagName!=='INPUT')
    document.getElementById('inp-foto')?.click();
});
// Limita tags por plano
<?php if($feat['max_tags'] > 0 && $feat['max_tags'] < 999): ?>
document.querySelectorAll('input[name="tags[]"]').forEach(cb=>{
  cb.addEventListener('change',()=>{
    const checked=document.querySelectorAll('input[name="tags[]"]:checked');
    if(checked.length > <?= $feat['max_tags'] ?>){
      cb.checked=false;
      alert('Limite de <?= $feat['max_tags'] ?> tags para o seu plano.');
    }
  });
});
<?php endif; ?>
</script>
</body>
</html>