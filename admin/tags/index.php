<?php
require_once __DIR__ . '/../../includes/icons.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

Auth::require();

$page_title = 'Tags / Palavras-chave';
$erros = [];

/* ── POST: criar / editar / excluir ── */
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

$tags = DB::query('SELECT t.*, COUNT(lt.lugar_id) AS total_lugares
                   FROM tags t
                   LEFT JOIN lugar_tags lt ON lt.tag_id = t.id
                   GROUP BY t.id ORDER BY t.label');

$edit_id  = Sanitize::get('edit', 'int', 0);
$edit_tag = $edit_id ? DB::row('SELECT * FROM tags WHERE id = ?', [$edit_id]) : null;

include __DIR__ . '/../_layout.php';
?>

<!-- Form -->
<div class="bg-white rounded-2xl border border-green/[0.07] p-6 mb-6">
  <h3 class="font-display text-[16px] font-bold text-green-dark mb-1">
    <?= $edit_tag ? 'Editar tag' : 'Nova tag' ?>
  </h3>
  <p class="text-[12.5px] text-warmgray mb-5">
    Tags aparecem nos cards dos lugares e ajudam na busca e filtragem.
  </p>

  <?php if (!empty($erros)): ?>
  <div class="bg-red-50 border border-red-200 rounded-xl p-3 mb-4 text-[13px] text-red-600">
    <?= Sanitize::html(implode(' ', $erros)) ?>
  </div>
  <?php endif; ?>

  <form method="POST" class="flex flex-wrap gap-4 items-end">
    <input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
    <input type="hidden" name="action" value="save"/>
    <input type="hidden" name="id"     value="<?= (int)($edit_tag['id'] ?? 0) ?>"/>

    <div class="flex-1 min-w-[200px]">
      <label class="block text-[10px] font-black tracking-[0.18em] uppercase text-warmgray mb-1.5">
        Nome da tag *
      </label>
      <input type="text" name="label" id="tag-label" required
             value="<?= Sanitize::html($edit_tag['label'] ?? '') ?>"
             placeholder="ex: Jantar Romântico, Pet Friendly, Brunch"
             class="w-full px-4 py-2.5 bg-offwhite border border-green/[0.1] rounded-xl
                    text-[13.5px] outline-none focus:border-gold/50 transition-colors"
             oninput="autoTagSlug(this.value)"/>
    </div>

    <div class="w-[220px]">
      <label class="block text-[10px] font-black tracking-[0.18em] uppercase text-warmgray mb-1.5">
        Slug
      </label>
      <input type="text" name="slug" id="tag-slug"
             value="<?= Sanitize::html($edit_tag['slug'] ?? '') ?>"
             placeholder="gerado automaticamente"
             class="w-full px-4 py-2.5 bg-offwhite border border-green/[0.1] rounded-xl
                    text-[13.5px] outline-none focus:border-gold/50 transition-colors"/>
    </div>

    <button type="submit"
            class="h-[42px] px-6 bg-green-dark hover:bg-green text-white text-[12px]
                   font-black tracking-widest uppercase rounded-xl transition-colors">
      <?= $edit_tag ? 'Salvar' : 'Criar' ?>
    </button>

    <?php if ($edit_tag): ?>
    <a href="/admin/tags/index.php"
       class="h-[42px] px-5 flex items-center bg-offwhite hover:bg-gold-pale text-graphite
              text-[12px] font-bold rounded-xl transition-colors">
      Cancelar
    </a>
    <?php endif; ?>
  </form>
</div>

<!-- Nuvem de tags -->
<?php if (!empty($tags)): ?>
<div class="bg-white rounded-2xl border border-green/[0.07] p-6 mb-6">
  <h3 class="text-[10px] font-black tracking-[0.2em] uppercase text-gold mb-4">
    Visualização
  </h3>
  <div class="flex flex-wrap gap-2">
    <?php foreach ($tags as $t): ?>
    <a href="?edit=<?= (int)$t['id'] ?>"
       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full border
              border-green/[0.1] bg-offwhite hover:bg-gold-pale hover:border-gold/40
              text-[12px] font-semibold text-green transition-all duration-200 cursor-pointer">
      <?= Sanitize::html($t['label']) ?>
      <?php if ($t['total_lugares'] > 0): ?>
      <span class="text-[10px] font-black text-warmgray/60"><?= (int)$t['total_lugares'] ?></span>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Lista completa -->
<div class="bg-white rounded-2xl border border-green/[0.07] shadow-sm overflow-hidden">
  <div class="flex items-center justify-between px-6 py-4 border-b border-offwhite">
    <h3 class="font-display text-[16px] font-bold text-green-dark">Todas as tags</h3>
    <span class="text-[12px] text-warmgray">
      <span class="font-black text-graphite"><?= count($tags) ?></span> cadastradas
    </span>
  </div>

  <?php if (empty($tags)): ?>
  <div class="py-12 text-center text-[13px] text-warmgray">
    Nenhuma tag cadastrada ainda.
  </div>
  <?php else: ?>
  <div class="divide-y divide-offwhite">
    <?php foreach ($tags as $t): ?>
    <div class="flex items-center justify-between px-6 py-3
                hover:bg-offwhite/50 transition-colors">
      <div>
        <p class="font-semibold text-[13.5px] text-graphite"><?= Sanitize::html($t['label']) ?></p>
        <p class="text-[11px] text-warmgray"><?= Sanitize::html($t['slug']) ?> · <?= (int)$t['total_lugares'] ?> lugar(es)</p>
      </div>
      <div class="flex items-center gap-3">
        <a href="?edit=<?= (int)$t['id'] ?>"
           class="text-[11px] font-bold text-gold hover:text-gold-light transition-colors">
          Editar
        </a>
        <form method="POST" style="display:inline"
              onsubmit="return confirm('Excluir esta tag?')">
          <input type="hidden" name="_token"  value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
          <input type="hidden" name="action"  value="excluir"/>
          <input type="hidden" name="id"      value="<?= (int)$t['id'] ?>"/>
          <button type="submit"
                  class="text-[11px] font-bold text-red-400 hover:text-red-600 transition-colors">
            Excluir
          </button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<script>
function autoTagSlug(val) {
  const slugField = document.getElementById('tag-slug');
  if (slugField.dataset.manual) return;
  slugField.value = val.toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g,'')
    .replace(/[^a-z0-9\s-]/g,'').trim()
    .replace(/\s+/g,'-').replace(/-+/g,'-');
}
document.getElementById('tag-slug').addEventListener('input', function() {
  this.dataset.manual = '1';
});
</script>

<?php include __DIR__ . '/../_layout_end.php'; ?>