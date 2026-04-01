<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

Auth::require();

$page_title = 'Categorias';
$erros = [];

/* ── POST: criar / editar / reordenar ── */
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

            if ($label === '') { $erros[] = 'Nome obrigatório.'; }
            else {
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

/* Conta lugares por categoria */
$counts = [];
foreach (DB::query('SELECT categoria_id, COUNT(*) n FROM lugares WHERE ativo=1 GROUP BY categoria_id') as $r) {
    $counts[$r['categoria_id']] = $r['n'];
}

$icons_disponiveis = [
    'utensils','coffee','wine','paw','spa','shopping-bag',
    'activity','dumbbell','scissors','pin','star','heart',
    'map','navigation','trending-up','grid','award','briefcase',
];

/* Edição inline */
$edit_id = Sanitize::get('edit', 'int', 0);
$edit_cat = $edit_id ? DB::row('SELECT * FROM categorias WHERE id=?', [$edit_id]) : null;

include __DIR__ . '/../_layout.php';
?>

<!-- Form nova / editar -->
<div class="bg-white rounded-2xl border border-green/[0.07] p-6 mb-6">
  <h3 class="font-display text-[16px] font-bold text-green-dark mb-5">
    <?= $edit_cat ? 'Editar categoria' : 'Nova categoria' ?>
  </h3>

  <?php if (!empty($erros)): ?>
  <div class="bg-red-50 border border-red-200 rounded-xl p-3 mb-4 text-[13px] text-red-600">
    <?= Sanitize::html(implode(' ', $erros)) ?>
  </div>
  <?php endif; ?>

  <form method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
    <input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
    <input type="hidden" name="action" value="save"/>
    <input type="hidden" name="id"     value="<?= (int)($edit_cat['id'] ?? 0) ?>"/>

    <div>
      <label class="block text-[10px] font-black tracking-[0.18em] uppercase text-warmgray mb-1.5">
        Nome *
      </label>
      <input type="text" name="label" required
             value="<?= Sanitize::html($edit_cat['label'] ?? '') ?>"
             class="w-full px-4 py-2.5 bg-offwhite border border-green/[0.1] rounded-xl
                    text-[13.5px] outline-none focus:border-gold/50 transition-colors"/>
    </div>

    <div>
      <label class="block text-[10px] font-black tracking-[0.18em] uppercase text-warmgray mb-1.5">
        Ícone
      </label>
      <select name="icon"
              class="w-full px-4 py-2.5 bg-offwhite border border-green/[0.1] rounded-xl
                     text-[13.5px] outline-none focus:border-gold/50 transition-colors cursor-pointer">
        <?php foreach ($icons_disponiveis as $ic): ?>
        <option value="<?= $ic ?>" <?= ($edit_cat['icon'] ?? '') === $ic ? 'selected' : '' ?>>
          <?= $ic ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="grid grid-cols-2 gap-2">
      <div>
        <label class="block text-[10px] font-black tracking-[0.18em] uppercase text-warmgray mb-1.5">
          Ordem
        </label>
        <input type="number" name="ordem" min="0"
               value="<?= (int)($edit_cat['ordem'] ?? 0) ?>"
               class="w-full px-4 py-2.5 bg-offwhite border border-green/[0.1] rounded-xl
                      text-[13.5px] outline-none focus:border-gold/50 transition-colors"/>
      </div>
      <div class="flex flex-col justify-end">
        <button type="submit"
                class="py-2.5 bg-green-dark hover:bg-green text-white text-[12px]
                       font-black tracking-widest uppercase rounded-xl transition-colors">
          <?= $edit_cat ? 'Salvar' : 'Criar' ?>
        </button>
      </div>
    </div>
  </form>

  <?php if ($edit_cat): ?>
  <a href="/admin/categorias"
     class="inline-block mt-3 text-[11px] font-bold text-warmgray hover:text-gold transition-colors">
    ← Cancelar edição
  </a>
  <?php endif; ?>
</div>

<!-- Lista -->
<div class="bg-white rounded-2xl border border-green/[0.07] shadow-sm overflow-hidden">
  <table class="w-full">
    <thead>
      <tr class="text-[10px] font-black tracking-[0.14em] uppercase text-warmgray border-b border-offwhite bg-offwhite/60">
        <th class="px-5 py-3.5 text-left">Categoria</th>
        <th class="px-5 py-3.5 text-center hidden sm:table-cell">Ícone</th>
        <th class="px-5 py-3.5 text-center hidden md:table-cell">Ordem</th>
        <th class="px-5 py-3.5 text-center hidden md:table-cell">Lugares</th>
        <th class="px-5 py-3.5 text-center">Status</th>
        <th class="px-5 py-3.5 text-right">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($categorias as $cat): ?>
      <tr class="border-b border-offwhite last:border-0 hover:bg-offwhite/50 transition-colors">
        <td class="px-5 py-3.5">
          <span class="font-semibold text-[13.5px] text-graphite"><?= Sanitize::html($cat['label']) ?></span>
          <span class="block text-[11px] text-warmgray"><?= Sanitize::html($cat['slug']) ?></span>
        </td>
        <td class="px-5 py-3.5 text-center hidden sm:table-cell">
          <span class="text-[12px] text-warmgray font-mono"><?= Sanitize::html($cat['icon']) ?></span>
        </td>
        <td class="px-5 py-3.5 text-center hidden md:table-cell">
          <span class="text-[13px] font-bold text-graphite"><?= (int)$cat['ordem'] ?></span>
        </td>
        <td class="px-5 py-3.5 text-center hidden md:table-cell">
          <span class="inline-flex items-center justify-center w-7 h-7 rounded-full
                       bg-gold-pale text-green-dark text-[12px] font-black">
            <?= $counts[$cat['id']] ?? 0 ?>
          </span>
        </td>
        <td class="px-5 py-3.5 text-center">
          <form method="POST" style="display:inline">
            <input type="hidden" name="_token"  value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
            <input type="hidden" name="action"  value="toggle"/>
            <input type="hidden" name="id"      value="<?= (int)$cat['id'] ?>"/>
            <button type="submit"
                    class="px-3 py-1 rounded-full text-[10px] font-black tracking-wider uppercase
                           transition-colors <?= $cat['ativo']
                               ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'
                               : 'bg-red-50 text-red-500 hover:bg-red-100' ?>">
              <?= $cat['ativo'] ? 'Ativo' : 'Inativo' ?>
            </button>
          </form>
        </td>
        <td class="px-5 py-3.5 text-right">
          <a href="?edit=<?= (int)$cat['id'] ?>"
             class="text-[11px] font-bold text-gold hover:text-gold-light transition-colors">
            Editar
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/../_layout_end.php'; ?>
