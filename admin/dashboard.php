<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Sanitize.php';

Auth::require();

$page_title = 'Dashboard';

// ── Totais ──
$totais = DB::row(
  'SELECT
     (SELECT COUNT(*) FROM lugares  WHERE ativo = 1)    AS lugares,
     (SELECT COUNT(*) FROM categorias WHERE ativo = 1)  AS categorias,
     (SELECT COUNT(*) FROM avaliacoes WHERE aprovado = 1) AS avaliacoes,
     (SELECT COUNT(*) FROM lugares  WHERE destaque = 1) AS destaques'
);

// ── Últimos cadastrados ──
$recentes = DB::query(
  'SELECT l.id, l.slug, l.nome, l.rating, l.criado_em,
          c.label AS cat_nome
   FROM lugares l
   JOIN categorias c ON c.id = l.categoria_id
   WHERE l.ativo = 1
   ORDER BY l.criado_em DESC LIMIT 6'
);

include __DIR__ . '/_layout.php';
?>

<!-- Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
  <?php
  $cards = [
    ['label' => 'Lugares ativos',  'val' => $totais['lugares'],    'color' => 'bg-green-dark', 'icon' => '<path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>'],
    ['label' => 'Categorias',      'val' => $totais['categorias'], 'color' => 'bg-green',      'icon' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>'],
    ['label' => 'Avaliações',      'val' => $totais['avaliacoes'], 'color' => 'bg-[#c9aa6b]',  'icon' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'],
    ['label' => 'Em destaque',     'val' => $totais['destaques'],  'color' => 'bg-[#4f5c40]',  'icon' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>'],
  ];
  foreach ($cards as $c): ?>
  <div class="bg-white rounded-2xl p-6 border border-green/[0.07] shadow-sm">
    <div class="flex items-center justify-between mb-4">
      <div class="w-10 h-10 <?= $c['color'] ?> rounded-xl flex items-center justify-center">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white"
             stroke-width="1.75" stroke-linecap="round"><?= $c['icon'] ?></svg>
      </div>
    </div>
    <div class="font-display text-[32px] font-bold text-green-dark leading-none mb-1">
      <?= number_format((int)$c['val']) ?>
    </div>
    <div class="text-[12px] font-semibold text-warmgray"><?= $c['label'] ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Quick actions -->
<div class="flex flex-wrap gap-3 mb-8">
  <a href="/admin/lugares/create.php"
     class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-dark hover:bg-green
            text-white text-[12px] font-black tracking-widest uppercase rounded-full
            transition-colors duration-200">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2.5" stroke-linecap="round">
      <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
    Novo Lugar
  </a>
  <a href="/admin/categorias/index.php"
     class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-green/[0.15]
            hover:border-gold text-graphite text-[12px] font-black tracking-widest uppercase
            rounded-full transition-colors duration-200">
    Gerenciar Categorias
  </a>

  <button type="button" onclick="syncTodos()" id="btn-sync-todos"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#4285F4] hover:bg-[#3367D6]
                 text-white text-[12px] font-black tracking-widest uppercase rounded-full
                 transition-colors duration-200">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round">
      <polyline points="1 4 1 10 7 10"/>
      <path d="M3.51 15a9 9 0 1 0 .49-3.51"/>
    </svg>
    Sincronizar Google
  </button>
</div>

<div id="sync-todos-result" class="hidden mt-3 px-4 py-3 rounded-xl text-[13px] font-semibold"></div>

<!-- Recentes -->
<div class="bg-white rounded-2xl border border-green/[0.07] shadow-sm overflow-hidden">
  <div class="flex items-center justify-between px-6 py-4 border-b border-offwhite">
    <h2 class="font-display text-[17px] font-bold text-green-dark">Últimos cadastrados</h2>
    <a href="/admin/lugares/index.php"
       class="text-[11px] font-bold tracking-widest uppercase text-gold hover:text-gold-light
              transition-colors">Ver todos</a>
  </div>
  <table class="w-full">
    <thead>
      <tr class="text-[10px] font-black tracking-[0.15em] uppercase text-warmgray
                 border-b border-offwhite">
        <th class="px-6 py-3 text-left">Nome</th>
        <th class="px-6 py-3 text-left hidden sm:table-cell">Categoria</th>
        <th class="px-6 py-3 text-left hidden md:table-cell">Rating</th>
        <th class="px-6 py-3 text-left hidden lg:table-cell">Cadastrado em</th>
        <th class="px-6 py-3"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($recentes as $r): ?>
      <tr class="border-b border-offwhite last:border-0 hover:bg-offwhite/60 transition-colors">
        <td class="px-6 py-3.5">
          <span class="font-semibold text-[13.5px] text-graphite"><?= Sanitize::html($r['nome']) ?></span>
        </td>
        <td class="px-6 py-3.5 hidden sm:table-cell">
          <span class="text-[12px] text-warmgray"><?= Sanitize::html($r['cat_nome']) ?></span>
        </td>
        <td class="px-6 py-3.5 hidden md:table-cell">
          <span class="text-[#c9aa6b] text-[12px] font-bold">
            <?= $r['rating'] > 0 ? '★ ' . number_format($r['rating'],1) : '—' ?>
          </span>
        </td>
        <td class="px-6 py-3.5 hidden lg:table-cell">
          <span class="text-[12px] text-warmgray">
            <?= date('d/m/Y', strtotime($r['criado_em'])) ?>
          </span>
        </td>
        <td class="px-6 py-3.5 text-right">
          <a href="/admin/lugares/edit.php?id=<?= (int)$r['id'] ?>"
             class="text-[11px] font-bold tracking-wider uppercase text-gold
                    hover:text-gold-light transition-colors">Editar</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($recentes)): ?>
      <tr><td colspan="5" class="px-6 py-8 text-center text-[13px] text-warmgray">
        Nenhum lugar cadastrado ainda.
        <a href="/admin/lugares/create.php" class="text-gold hover:underline ml-1">Cadastrar agora</a>
      </td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script>
async function syncTodos() {
  const btn    = document.getElementById('btn-sync-todos');
  const result = document.getElementById('sync-todos-result');

  btn.disabled  = true;
  btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="animation:spin 1s linear infinite"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg> Sincronizando…';
  result.className = 'hidden';

  try {
    const fd = new FormData();
    fd.append('_token', '<?= Sanitize::html(Sanitize::csrfToken()) ?>');
    fd.append('modo',   'todos');

    const res  = await fetch('/admin/google-sync.php', { method: 'POST', body: fd });
    const data = await res.json();

    result.className = data.ok
      ? 'mt-3 px-4 py-3 rounded-xl text-[13px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200'
      : 'mt-3 px-4 py-3 rounded-xl text-[13px] font-semibold bg-red-50 text-red-600 border border-red-200';

    result.textContent = data.ok ? '✓ ' + data.msg : '✗ ' + data.erro;

  } catch(e) {
    result.className   = 'mt-3 px-4 py-3 rounded-xl text-[13px] font-semibold bg-red-50 text-red-600 border border-red-200';
    result.textContent = '✗ Erro de conexão. Tente novamente.';
  } finally {
    btn.disabled  = false;
    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg> Sincronizar Google';
  }
}
</script>
<style>@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}</style>

<?php include __DIR__ . '/_layout_end.php'; ?>