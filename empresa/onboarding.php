<?php
/**
 * empresa/onboarding.php
 * Wizard de cadastro da empresa — 4 etapas com salvamento automático
 */
require_once __DIR__ . '/../core/UserAuth.php';
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Sanitize.php';
require_once __DIR__ . '/../includes/icons.php';

UserAuth::require();
$usuario = UserAuth::current();

// Usuário foi excluído do banco mas sessão ainda está ativa
if (!$usuario) {
    UserAuth::logout();
    header('Location: /empresa/login.php');
    exit;
}

if (($usuario['empresa_status'] ?? '') === 'aprovada') { header('Location: /empresa/dashboard.php'); exit; }
if (($usuario['empresa_status'] ?? '') === 'pendente')  { header('Location: /empresa/status.php');   exit; }

$empresa_id = (int)($usuario['empresa_id'] ?? 0);
$lugar_id   = (int)($usuario['lugar_id']   ?? 0);
$lugar      = $lugar_id ? DB::row('SELECT * FROM lugares WHERE id=? AND empresa_id=?',[$lugar_id,$empresa_id]) : null;
$categorias = DB::query('SELECT id,label FROM categorias WHERE ativo=1 ORDER BY ordem');
$horarios_db = [];
if ($lugar_id) {
    foreach (DB::query('SELECT * FROM horarios WHERE lugar_id=? ORDER BY dia_semana',[$lugar_id]) as $h)
        $horarios_db[$h['dia_semana']] = $h;
}
$fotos_atuais = $lugar_id ? DB::query('SELECT id,url FROM fotos WHERE lugar_id=? ORDER BY ordem',[$lugar_id]) : [];
$csrf         = Sanitize::csrfToken();
$plan         = $usuario['empresa_plan_intent'] ?? $usuario['plan_intent'] ?? 'essencial';
$plan_labels  = ['essencial'=>'Essencial','profissional'=>'Profissional','premium'=>'Premium'];
$dias_nomes   = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
$page_title   = 'Cadastrar empresa — Guia Campo Belo';
$v = fn($k,$d='') => htmlspecialchars($lugar[$k] ?? $d, ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<?php include __DIR__ . '/../includes/head.php'; ?>
<style>
body{background:var(--gcb-offwhite)}
.onb-wrap{min-height:100vh;display:flex;flex-direction:column}
.onb-topbar{background:var(--gcb-green-dark);padding:12px 20px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
.onb-topbar img{height:36px}
.save-status{font-size:11px;font-weight:600;color:rgba(255,255,255,.45);display:flex;align-items:center;gap:6px}
.save-dot{width:6px;height:6px;border-radius:50%;background:rgba(255,255,255,.25);transition:background .3s;flex-shrink:0}
.save-dot.saving{background:var(--gcb-gold);animation:pulse .8s infinite}
.save-dot.saved{background:#34d399}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.35}}
.plan-tag{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;background:rgba(201,170,107,.15);border:1px solid rgba(201,170,107,.3);border-radius:999px;font-size:10px;font-weight:700;color:var(--gcb-gold-light)}
.onb-prog{background:var(--gcb-green);padding:0 20px}
.onb-steps{display:flex;max-width:600px;margin:0 auto}
.onb-step{flex:1;padding:10px 6px;text-align:center;cursor:pointer;position:relative}
.onb-step::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;background:transparent;transition:background .2s}
.onb-step.active::after{background:var(--gcb-gold)}
.snum{width:22px;height:22px;border-radius:50%;background:rgba(255,255,255,.12);color:rgba(255,255,255,.5);font-size:10px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 3px;transition:all .2s}
.onb-step.done .snum{background:#34d399;color:#064e3b}
.onb-step.active .snum{background:var(--gcb-gold);color:var(--gcb-green-dark)}
.slbl{font-size:9px;font-weight:700;color:rgba(255,255,255,.45);letter-spacing:.08em;text-transform:uppercase}
.onb-step.active .slbl{color:#fff}
.onb-step.done .slbl{color:rgba(255,255,255,.65)}
.onb-content{flex:1;max-width:680px;margin:0 auto;padding:1.5rem 1rem;width:100%}
.onb-panel{display:none}.onb-panel.active{display:block}
.onb-card{background:#fff;border-radius:20px;border:1px solid rgba(61,71,51,.07);padding:22px;margin-bottom:14px;box-shadow:0 2px 10px rgba(29,29,27,.05)}
.card-ttl{font-size:14px;font-weight:800;color:var(--gcb-green-dark);margin-bottom:14px;display:flex;align-items:center;gap:8px}
.card-ttl svg{color:var(--gcb-gold);flex-shrink:0}
.olbl{display:block;font-size:10px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:var(--gcb-warmgray);margin-bottom:5px}
.ohint{font-size:11px;color:var(--gcb-warmgray);margin-top:3px;line-height:1.5}
.g2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
@media(max-width:520px){.g2{grid-template-columns:1fr}}
.locked{opacity:.4;pointer-events:none;position:relative}
.lock-lbl{font-size:9px;font-weight:700;color:var(--gcb-gold);margin-left:4px}
.hora-row{display:flex;align-items:center;gap:8px;padding:9px 0;border-bottom:.5px solid rgba(61,71,51,.07);flex-wrap:wrap}
.hora-row:last-child{border-bottom:none}
.hora-dia{font-size:12px;font-weight:700;color:var(--gcb-graphite);width:66px;flex-shrink:0}
.hck{display:flex;align-items:center;gap:4px;font-size:11px;color:var(--gcb-warmgray);cursor:pointer}
.htimes{display:flex;align-items:center;gap:6px;transition:opacity .2s}
.htimes.off{opacity:.2;pointer-events:none}
.hinput{padding:6px 8px;background:var(--gcb-offwhite);border:1.5px solid rgba(61,71,51,.1);border-radius:8px;font-size:12px;width:86px;outline:none;font-family:inherit}
.hinput:focus{border-color:rgba(201,170,107,.55)}
.foto-zone{border:2px dashed rgba(61,71,51,.14);border-radius:14px;padding:28px;text-align:center;cursor:pointer;transition:all .2s}
.foto-zone:hover{border-color:rgba(201,170,107,.5);background:var(--gcb-gold-pale)}
.foto-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(80px,1fr));gap:8px;margin-top:10px}
.fthumb{aspect-ratio:1;border-radius:10px;overflow:hidden;position:relative;background:var(--gcb-offwhite)}
.fthumb img{width:100%;height:100%;object-fit:cover}
.frem{position:absolute;top:3px;right:3px;width:18px;height:18px;border-radius:50%;background:rgba(29,29,27,.65);color:#fff;font-size:9px;display:flex;align-items:center;justify-content:center;cursor:pointer;border:none;line-height:1}
.prev-card{border:1px solid rgba(201,170,107,.25);border-radius:14px;overflow:hidden;background:#fff}
.prev-img{height:130px;background:var(--gcb-offwhite);display:flex;align-items:center;justify-content:center;color:var(--gcb-warmgray);font-size:11px;overflow:hidden}
.prev-img img{width:100%;height:100%;object-fit:cover}
.prev-body{padding:12px}
.res-row{display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:.5px solid rgba(61,71,51,.07);font-size:12px}
.res-row:last-child{border-bottom:none}
.res-lbl{color:var(--gcb-warmgray);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em}
.res-val{color:var(--gcb-graphite);font-weight:600;text-align:right;max-width:65%}
.onb-nav{display:flex;align-items:center;gap:10px;margin-top:6px}
.btn-back{padding:12px 18px;border:1.5px solid rgba(61,71,51,.15);border-radius:999px;font-size:12px;font-weight:700;color:var(--gcb-warmgray);background:transparent;cursor:pointer;transition:all .2s}
.btn-back:hover{border-color:var(--gcb-green-dark);color:var(--gcb-green-dark)}
.btn-next{flex:1;padding:13px;background:var(--gcb-green-dark);color:#fff;border:none;border-radius:999px;font-size:12px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;cursor:pointer;transition:background .2s}
.btn-next:hover{background:var(--gcb-green)}
.btn-sub{flex:1;padding:13px;background:var(--gcb-gold);color:var(--gcb-green-dark);border:none;border-radius:999px;font-size:12px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;cursor:pointer;box-shadow:0 6px 20px rgba(201,170,107,.3);transition:all .2s}
.btn-sub:hover{background:var(--gcb-gold-light)}
.btn-sub:disabled{opacity:.6;cursor:not-allowed}
</style>
</head>
<body>
<div class="onb-wrap">

<div class="onb-topbar">
  <a href="/"><img src="/assets/img/logo.png" alt="Guia Campo Belo"></a>
  <div class="save-status">
    <div class="save-dot" id="save-dot"></div>
    <span id="save-lbl">Salvamento automático ativo</span>
  </div>
  <span class="plan-tag">
    <?= icon('award',12) ?> <?= Sanitize::html($plan_labels[$plan] ?? 'Essencial') ?>
  </span>
</div>

<div class="onb-prog">
  <div class="onb-steps">
    <?php foreach(['Básico','Endereço','Horários','Finalizar'] as $i=>$s): ?>
    <div class="onb-step <?= $i===0?'active':'' ?>" id="stab-<?= $i ?>" onclick="goStep(<?= $i ?>)">
      <div class="snum" id="snum-<?= $i ?>"><?= $i+1 ?></div>
      <div class="slbl"><?= $s ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="onb-content">

<!-- STEP 0 — Básico -->
<div class="onb-panel active" id="panel-0">
  <div class="onb-card">
    <div class="card-ttl"><?= icon('building',17) ?> Sobre a empresa</div>
    <div class="mb-3">
      <label class="olbl">Nome da empresa *</label>
      <input type="text" class="gcb-field" id="f-nome"
             placeholder="Ex: Padaria Central"
             value="<?= $v('nome') ?>" oninput="autoSave()">
    </div>
    <div class="mb-3">
      <label class="olbl">Categoria *</label>
      <select class="gcb-field" id="f-categoria_id" onchange="autoSave()">
        <option value="">Selecione…</option>
        <?php foreach($categorias as $cat): ?>
        <option value="<?= $cat['id'] ?>" <?= ($lugar['categoria_id']??'')==$cat['id']?'selected':'' ?>>
          <?= Sanitize::html($cat['label']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="olbl">Descrição *</label>
      <textarea class="gcb-field" id="f-descricao" rows="4"
                placeholder="Descreva sua empresa, o que oferece, seus diferenciais…"
                oninput="autoSave()"><?= $v('descricao') ?></textarea>
      <div class="ohint">Mínimo 20 caracteres. Aparece na sua página pública.</div>
    </div>
    <div>
      <label class="olbl">Subtítulo da categoria</label>
      <input type="text" class="gcb-field" id="f-cat_label"
             placeholder="Ex: Italiana · Contemporânea"
             value="<?= $v('cat_label') ?>" oninput="autoSave()">
    </div>
  </div>
  <div class="onb-nav">
    <button class="btn-next" onclick="goStep(1)">Continuar → Endereço</button>
  </div>
</div>

<!-- STEP 1 — Endereço -->
<div class="onb-panel" id="panel-1">
  <div class="onb-card">
    <div class="card-ttl"><?= icon('pin',17) ?> Localização</div>
    <div class="mb-3">
      <label class="olbl">Endereço completo *</label>
      <input type="text" class="gcb-field" id="f-endereco"
             placeholder="Rua, número, bairro"
             value="<?= $v('endereco') ?>" oninput="autoSave()">
    </div>
    <div class="g2">
      <div>
        <label class="olbl">Bairro</label>
        <input type="text" class="gcb-field" id="f-bairro"
               placeholder="Campo Belo"
               value="<?= $v('bairro') ?>" oninput="autoSave()">
      </div>
      <div>
        <label class="olbl">CEP</label>
        <input type="text" class="gcb-field" id="f-cep"
               placeholder="04553-060"
               value="<?= $v('cep') ?>" oninput="autoSave()">
      </div>
    </div>
  </div>
  <div class="onb-card">
    <div class="card-ttl"><?= icon('phone',17) ?> Contato</div>
    <div class="g2 mb-3">
      <div>
        <label class="olbl">Telefone</label>
        <input type="text" class="gcb-field" id="f-telefone"
               placeholder="(11) 3045-7892"
               value="<?= $v('telefone') ?>" oninput="autoSave()">
      </div>
      <div>
        <label class="olbl">WhatsApp
          <?php if(!in_array($plan,['profissional','premium'])): ?>
          <span class="lock-lbl">Profissional+</span>
          <?php endif; ?>
        </label>
        <input type="text" class="gcb-field" id="f-whatsapp"
               placeholder="(11) 99999-9999"
               value="<?= $v('whatsapp') ?>"
               <?= !in_array($plan,['profissional','premium'])?'disabled':'' ?>
               oninput="autoSave()">
      </div>
    </div>
    <div class="mb-3 <?= !in_array($plan,['profissional','premium'])?'locked':'' ?>">
      <label class="olbl">Site <?php if(!in_array($plan,['profissional','premium'])): ?><span class="lock-lbl">Profissional+</span><?php endif; ?></label>
      <input type="text" class="gcb-field" id="f-site"
             placeholder="https://seusite.com.br"
             value="<?= $v('site') ?>" oninput="autoSave()">
    </div>
    <div class="<?= !in_array($plan,['profissional','premium'])?'locked':'' ?>">
      <label class="olbl">Instagram <?php if(!in_array($plan,['profissional','premium'])): ?><span class="lock-lbl">Profissional+</span><?php endif; ?></label>
      <input type="text" class="gcb-field" id="f-instagram"
             placeholder="@suaempresa"
             value="<?= $v('instagram') ?>" oninput="autoSave()">
    </div>
  </div>
  <div class="onb-nav">
    <button class="btn-back" onclick="goStep(0)">← Voltar</button>
    <button class="btn-next" onclick="goStep(2)">Continuar → Horários</button>
  </div>
</div>

<!-- STEP 2 — Horários -->
<div class="onb-panel" id="panel-2">
  <div class="onb-card">
    <div class="card-ttl"><?= icon('clock',17) ?> Horários de funcionamento</div>
    <div style="margin-bottom:12px">
      <button type="button" onclick="aplicarSemana()"
              class="btn-header-outline" style="font-size:11px;padding:6px 14px">
        Aplicar Seg–Sex igual
      </button>
    </div>
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
        <input type="checkbox" id="hf<?= $d ?>" <?= $fec?'checked':'' ?>
               onchange="togH(<?= $d ?>)" style="accent-color:var(--gcb-green-dark)">
        Fechado
      </label>
      <label class="hck">
        <input type="checkbox" id="hd<?= $d ?>" <?= $dtd?'checked':'' ?>
               onchange="togH(<?= $d ?>)" style="accent-color:var(--gcb-green-dark)">
        Dia todo
      </label>
      <div class="htimes <?= ($fec||$dtd)?'off':'' ?>" id="ht<?= $d ?>">
        <input type="time" class="hinput" id="ha<?= $d ?>"
               value="<?= htmlspecialchars($ab) ?>" onchange="saveH()">
        <span style="font-size:11px;color:var(--gcb-warmgray)">às</span>
        <input type="time" class="hinput" id="hc<?= $d ?>"
               value="<?= htmlspecialchars($fc) ?>" onchange="saveH()">
      </div>
    </div>
    <?php endfor; ?>
  </div>
  <div class="onb-nav">
    <button class="btn-back" onclick="goStep(1)">← Voltar</button>
    <button class="btn-next" onclick="goStep(3)">Continuar → Fotos</button>
  </div>
</div>

<!-- STEP 3 — Fotos + Envio -->
<div class="onb-panel" id="panel-3">
  <div class="onb-card">
    <div class="card-ttl">
      <?= icon('grid',17) ?> Fotos
      <span style="font-size:10px;color:var(--gcb-warmgray);font-weight:500">
        <?php if($plan==='essencial'):?>até 1<?php elseif($plan==='profissional'):?>até 5<?php else:?>ilimitadas<?php endif;?>
      </span>
    </div>
    <?php if(!empty($fotos_atuais)): ?>
    <div class="foto-grid" style="margin-bottom:10px">
      <?php foreach($fotos_atuais as $f): ?>
      <div class="fthumb"><img src="<?= Sanitize::html($f['url']) ?>"></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <label class="foto-zone">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--gcb-gold)"
           stroke-width="1.5" stroke-linecap="round">
        <rect x="3" y="3" width="18" height="18" rx="2"/>
        <circle cx="8.5" cy="8.5" r="1.5"/>
        <polyline points="21 15 16 10 5 21"/>
      </svg>
      <p style="font-size:13px;font-weight:600;color:var(--gcb-graphite);margin:6px 0 2px">Clique para adicionar fotos</p>
      <p style="font-size:11px;color:var(--gcb-warmgray)">JPG, PNG ou WebP — máx. 5MB cada</p>
      <input type="file" id="foto-input" multiple accept="image/jpeg,image/png,image/webp"
             class="d-none" onchange="handleFotos(this)">
    </label>
    <div class="foto-grid" id="foto-preview"></div>
  </div>

  <div class="onb-card">
    <div class="card-ttl"><?= icon('verified',17) ?> Preview da sua página</div>
    <div class="prev-card">
      <div class="prev-img" id="prev-img"><span>Adicione uma foto acima</span></div>
      <div class="prev-body">
        <div style="font-size:9px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--gcb-gold);margin-bottom:3px" id="prev-cat">Categoria</div>
        <div style="font-size:16px;font-weight:800;color:var(--gcb-green-dark);margin-bottom:4px" id="prev-nome">Nome da empresa</div>
        <div style="font-size:11px;color:var(--gcb-warmgray);display:flex;align-items:center;gap:5px">
          <span style="color:var(--gcb-gold)">★★★★★</span>
          <span id="prev-end">Endereço</span>
        </div>
      </div>
    </div>
  </div>

  <div class="onb-card">
    <div class="card-ttl"><?= icon('trending-up',17) ?> Resumo</div>
    <div class="res-row">
      <span class="res-lbl">Plano solicitado</span>
      <span class="res-val"><span class="plan-tag"><?= Sanitize::html($plan_labels[$plan]??'Essencial') ?></span></span>
    </div>
    <div class="res-row">
      <span class="res-lbl">Empresa</span>
      <span class="res-val" id="res-nome">—</span>
    </div>
    <div class="res-row">
      <span class="res-lbl">Endereço</span>
      <span class="res-val" id="res-end">—</span>
    </div>
    <div class="res-row">
      <span class="res-lbl">Próximo passo</span>
      <span class="res-val" style="color:var(--gcb-warmgray)">Nossa equipe revisa em até 2 dias úteis</span>
    </div>
  </div>

  <div class="onb-nav">
    <button class="btn-back" onclick="goStep(2)">← Voltar</button>
    <button class="btn-sub" id="btn-sub" onclick="submeter()">
      Enviar para análise ✓
    </button>
  </div>
</div>

</div><!-- /onb-content -->
</div><!-- /onb-wrap -->

<script>
const CSRF       = '<?= Sanitize::html($csrf) ?>';
const EMP_ID     = <?= $empresa_id ?>;
const LUGAR_DB   = <?= $lugar_id ?>;
const PLAN       = '<?= $plan ?>';
const MAX_FOTOS  = PLAN==='premium'?999:PLAN==='profissional'?5:1;
const JA_FOTOS   = <?= count($fotos_atuais) ?>;

let cur=0, lugarId=LUGAR_DB, fotoFiles=[], debouce=null;

function goStep(n){
  if(n>cur && !validar(cur)) return;
  document.querySelectorAll('.onb-panel').forEach(p=>p.classList.remove('active'));
  document.getElementById('panel-'+n).classList.add('active');
  document.querySelectorAll('.onb-step').forEach((t,i)=>{
    t.className='onb-step'+(i<n?' done':i===n?' active':'');
    const sn=document.getElementById('snum-'+i);
    sn.innerHTML=i<n?'✓':i+1;
  });
  cur=n;
  window.scrollTo({top:0,behavior:'smooth'});
  if(n===3) atualizarPreview();
}

function validar(step){
  if(step===0){
    if(!document.getElementById('f-nome').value.trim()){alert('Informe o nome da empresa.');return false;}
    if(!document.getElementById('f-categoria_id').value){alert('Selecione uma categoria.');return false;}
    if(document.getElementById('f-descricao').value.trim().length<20){alert('A descrição deve ter ao menos 20 caracteres.');return false;}
  }
  if(step===1){
    if(!document.getElementById('f-endereco').value.trim()){alert('Informe o endereço.');return false;}
  }
  return true;
}

function autoSave(){
  clearTimeout(debouce);
  setDot('saving');
  debouce=setTimeout(salvar,1500);
}

function setDot(s){
  document.getElementById('save-dot').className='save-dot '+s;
  document.getElementById('save-lbl').textContent=
    s==='saving'?'Salvando…':s==='saved'?'Salvo automaticamente':'Rascunho';
}

async function salvar(){
  const dados={
    _token:CSRF,empresa_id:EMP_ID,lugar_id:lugarId||'',
    nome:g('f-nome'),categoria_id:g('f-categoria_id'),cat_label:g('f-cat_label'),
    descricao:g('f-descricao'),endereco:g('f-endereco'),bairro:g('f-bairro'),
    cep:g('f-cep'),telefone:g('f-telefone'),whatsapp:g('f-whatsapp'),
    site:g('f-site'),instagram:g('f-instagram'),
  };
  try{
    const r=await fetch('/empresa/actions/salvar.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(dados)});
    const d=await r.json();
    if(d.ok){lugarId=d.lugar_id;setDot('saved');}else{setDot('');}
  }catch{setDot('');}
}

function g(id){const e=document.getElementById(id);return e?e.value:'';}

function togH(d){
  const f=document.getElementById('hf'+d).checked;
  const t=document.getElementById('hd'+d).checked;
  document.getElementById('ht'+d).classList.toggle('off',f||t);
  saveH();
}
function aplicarSemana(){
  const a=document.getElementById('ha1').value;
  const c=document.getElementById('hc1').value;
  for(let d=1;d<=5;d++){
    document.getElementById('ha'+d).value=a;
    document.getElementById('hc'+d).value=c;
    document.getElementById('hf'+d).checked=false;
    document.getElementById('hd'+d).checked=false;
    togH(d);
  }
  saveH();
}
async function saveH(){
  if(!lugarId)return;
  const h=[];
  for(let d=0;d<=6;d++)h.push({dia:d,fechado:document.getElementById('hf'+d).checked?1:0,dia_todo:document.getElementById('hd'+d).checked?1:0,abre:document.getElementById('ha'+d).value,fecha:document.getElementById('hc'+d).value});
  await fetch('/empresa/actions/horarios.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({_token:CSRF,lugar_id:lugarId,horarios:h})});
}

function handleFotos(input){
  const restam=MAX_FOTOS-fotoFiles.length-JA_FOTOS;
  if(restam<=0){alert('Limite de fotos do seu plano atingido.');return;}
  const novos=Array.from(input.files).slice(0,restam);
  fotoFiles=fotoFiles.concat(novos);
  renderFotos();
  uploadFotos(novos);
}
function renderFotos(){
  const g=document.getElementById('foto-preview');
  g.innerHTML='';
  fotoFiles.forEach((f,i)=>{
    const r=new FileReader();
    r.onload=e=>{
      const d=document.createElement('div');d.className='fthumb';
      d.innerHTML=`<img src="${e.target.result}"><button class="frem" onclick="remFoto(${i})">✕</button>`;
      g.appendChild(d);
      if(i===0){document.getElementById('prev-img').innerHTML=`<img src="${e.target.result}">`;}
    };
    r.readAsDataURL(f);
  });
}
function remFoto(i){fotoFiles.splice(i,1);renderFotos();}
async function uploadFotos(files){
  if(!lugarId)await salvar();
  if(!lugarId)return;
  const fd=new FormData();
  fd.append('_token',CSRF);fd.append('lugar_id',lugarId);
  files.forEach(f=>fd.append('fotos[]',f));
  await fetch('/empresa/actions/upload-foto.php',{method:'POST',body:fd});
}

function atualizarPreview(){
  const nome=g('f-nome')||'Nome da empresa';
  const sel=document.getElementById('f-categoria_id');
  const cat=sel.options[sel.selectedIndex]?.text||'Categoria';
  const end=g('f-endereco')||'Endereço';
  document.getElementById('prev-nome').textContent=nome;
  document.getElementById('prev-cat').textContent=cat;
  document.getElementById('prev-end').textContent=end;
  document.getElementById('res-nome').textContent=nome;
  document.getElementById('res-end').textContent=end;
}

async function submeter(){
  if(!validar(0)||!validar(1)){goStep(0);return;}
  if(!lugarId)await salvar();
  if(!lugarId){alert('Erro ao salvar. Tente novamente.');return;}
  const btn=document.getElementById('btn-sub');
  btn.disabled=true;btn.textContent='Enviando…';
  try{
    const r=await fetch('/empresa/actions/submeter.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({_token:CSRF,empresa_id:EMP_ID,lugar_id:lugarId})});
    const d=await r.json();
    if(d.ok){window.location.href='/empresa/status.php';}
    else{alert(d.erro||'Erro. Tente novamente.');btn.disabled=false;btn.textContent='Enviar para análise ✓';}
  }catch{alert('Erro de conexão.');btn.disabled=false;btn.textContent='Enviar para análise ✓';}
}
</script>
</body>
</html>