<?php
require_once __DIR__ . '/../../includes/icons.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

Auth::require();

$page_title = 'Serviços & Comodidades';
$erros = [];

/* ── POST: criar / editar / excluir ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Sanitize::csrfValid($_POST['_token'] ?? '')) {
    $action = Sanitize::post('action');

    if ($action === 'save') {
        $sid   = Sanitize::post('id', 'int');
        $nome  = Sanitize::post('nome');
        $icon  = Sanitize::post('icon') ?: 'verified';

        if ($nome === '') {
            $erros[] = 'Nome obrigatório.';
        } else {
            if ($sid > 0) {
                DB::exec('UPDATE servicos SET nome = ?, icon = ? WHERE id = ?', [$nome, $icon, $sid]);
                $_SESSION['flash'] = ['type'=>'ok','msg'=>'Serviço atualizado.'];
            } else {
                // Verifica duplicata
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
        // Verifica se está em uso
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

$servicos = DB::query('SELECT s.*, COUNT(ls.lugar_id) AS total_lugares
                       FROM servicos s
                       LEFT JOIN lugar_servicos ls ON ls.servico_id = s.id
                       GROUP BY s.id ORDER BY s.nome');

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

<!-- Form -->
<div class="bg-white rounded-2xl border border-green/[0.07] p-6 mb-6">
  <h3 class="font-display text-[16px] font-bold text-green-dark mb-5">
    <?= $edit_srv ? 'Editar serviço' : 'Novo serviço' ?>
  </h3>

  <?php if (!empty($erros)): ?>
  <div class="bg-red-50 border border-red-200 rounded-xl p-3 mb-4 text-[13px] text-red-600">
    <?= Sanitize::html(implode(' ', $erros)) ?>
  </div>
  <?php endif; ?>

  <form method="POST" class="flex flex-wrap gap-4 items-end">
    <input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
    <input type="hidden" name="action" value="save"/>
    <input type="hidden" name="id"     value="<?= (int)($edit_srv['id'] ?? 0) ?>"/>

    <div class="flex-1 min-w-[200px]">
      <label class="block text-[10px] font-black tracking-[0.18em] uppercase text-warmgray mb-1.5">
        Nome *
      </label>
      <input type="text" name="nome" required
             value="<?= Sanitize::html($edit_srv['nome'] ?? '') ?>"
             placeholder="ex: Wi-Fi, Estacionamento, Visa/Master"
             class="w-full px-4 py-2.5 bg-offwhite border border-green/[0.1] rounded-xl
                    text-[13.5px] outline-none focus:border-gold/50 transition-colors"/>
    </div>

    <div class="w-[180px]">
      <label class="block text-[10px] font-black tracking-[0.18em] uppercase text-warmgray mb-1.5">
        Ícone
      </label>
      <select name="icon"
              class="w-full px-4 py-2.5 bg-offwhite border border-green/[0.1] rounded-xl
                     text-[13.5px] outline-none focus:border-gold/50 transition-colors cursor-pointer">
        <?php foreach ($icons_disponiveis as $ic): ?>
        <option value="<?= $ic ?>" <?= ($edit_srv['icon'] ?? 'verified') === $ic ? 'selected' : '' ?>>
          <?= $ic ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="submit"
            class="h-[42px] px-6 bg-green-dark hover:bg-green text-white text-[12px]
                   font-black tracking-widest uppercase rounded-xl transition-colors">
      <?= $edit_srv ? 'Salvar' : 'Criar' ?>
    </button>

    <?php if ($edit_srv): ?>
    <a href="/admin/servicos/index.php"
       class="h-[42px] px-5 flex items-center bg-offwhite hover:bg-gold-pale text-graphite
              text-[12px] font-bold rounded-xl transition-colors">
      Cancelar
    </a>
    <?php endif; ?>
  </form>
</div>

<!-- Lista -->
<div class="bg-white rounded-2xl border border-green/[0.07] shadow-sm overflow-hidden">
  <div class="flex items-center justify-between px-6 py-4 border-b border-offwhite">
    <h3 class="font-display text-[16px] font-bold text-green-dark">
      Todos os serviços
    </h3>
    <span class="text-[12px] text-warmgray">
      <span class="font-black text-graphite"><?= count($servicos) ?></span> cadastrados
    </span>
  </div>

  <?php if (empty($servicos)): ?>
  <div class="py-12 text-center text-[13px] text-warmgray">
    Nenhum serviço cadastrado ainda.
  </div>
  <?php else: ?>
  <div class="divide-y divide-offwhite">
    <?php foreach ($servicos as $s): ?>
    <div class="flex items-center justify-between px-6 py-3.5
                hover:bg-offwhite/50 transition-colors">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-gold-pale flex items-center justify-center text-green">
          <?= icon($s['icon'] ?? 'verified', 15) ?>
        </div>
        <div>
          <p class="font-semibold text-[13.5px] text-graphite"><?= Sanitize::html($s['nome']) ?></p>
          <p class="text-[11px] text-warmgray"><?= (int)$s['total_lugares'] ?> lugar(es)</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <span class="text-[11px] font-mono text-warmgray/60"><?= Sanitize::html($s['icon']) ?></span>
        <a href="?edit=<?= (int)$s['id'] ?>"
           class="text-[11px] font-bold text-gold hover:text-gold-light transition-colors">
          Editar
        </a>
        <form method="POST" style="display:inline"
              onsubmit="return confirm('Excluir este serviço?')">
          <input type="hidden" name="_token"  value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
          <input type="hidden" name="action"  value="excluir"/>
          <input type="hidden" name="id"      value="<?= (int)$s['id'] ?>"/>
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

<?php include __DIR__ . '/../_layout_end.php'; ?>