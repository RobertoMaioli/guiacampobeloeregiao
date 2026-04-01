<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

Auth::require();

/**
 * Renderiza um template de e-mail de forma isolada
 * Evita conflito com ob_start() do layout admin
 */
function _renderEmail(string $path, array $vars = []): string {
    extract($vars);
    // Carrega config necessária para o layout.php dos emails
    if (!defined('SITE_URL')) require_once __DIR__ . '/../../config/mail.php';
    ob_start();
    include $path;
    return ob_get_clean();
}

$page_title = 'Lugares';

// ── Filtros ──
$q    = Sanitize::get('q');
$cat  = Sanitize::get('cat', 'int', 0);
$page = max(1, Sanitize::get('page', 'int', 1));
$per  = 15;
$off  = ($page - 1) * $per;

$where  = ['1=1']; // mostra ativos e inativos
$params = [];

if ($q !== '') {
    $where[]  = '(l.nome LIKE ? OR l.endereco LIKE ?)';
    $like     = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
}
if ($cat > 0) {
    $where[]  = 'l.categoria_id = ?';
    $params[] = $cat;
}

$whereSQL = implode(' AND ', $where);
$total    = (int)DB::row("SELECT COUNT(*) n FROM lugares l WHERE $whereSQL", $params)['n'];
$pages    = (int)ceil($total / $per);

$lugares = DB::query(
    "SELECT l.id, l.slug, l.nome, l.badge, l.preco_simbolo,
            l.rating, l.total_reviews, l.destaque, l.ativo, l.criado_em,
            c.label AS cat_nome,
            f.url   AS foto
     FROM lugares l
     JOIN categorias c ON c.id = l.categoria_id
     LEFT JOIN fotos f ON f.lugar_id = l.id AND f.principal = 1
     WHERE $whereSQL
     ORDER BY l.destaque DESC, l.nome ASC
     LIMIT ? OFFSET ?",
    array_merge($params, [$per, $off])
);

$categorias = DB::query('SELECT id, label FROM categorias WHERE ativo = 1 ORDER BY ordem');

// ── Ação: toggle destaque / inativar ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Sanitize::csrfValid($_POST['_token'] ?? '')) {
    $act = Sanitize::post('action');
    $lid = Sanitize::post('lugar_id', 'int');

    if ($act === 'toggle_destaque') {
        DB::exec('UPDATE lugares SET destaque = 1 - destaque WHERE id = ?', [$lid]);
    } elseif ($act === 'inativar') {
        DB::exec('UPDATE lugares SET ativo = 0 WHERE id = ?', [$lid]);
        DB::exec(
            'UPDATE empresas SET status = "suspensa"
             WHERE lugar_id = ? AND status = "aprovada"',
            [$lid]
        );

        // E-mail #5 — Empresa suspensa
        try {
            require_once __DIR__ . '/../../core/Mailer.php';
            $emp = DB::row(
                'SELECT u.nome, u.email, l.nome AS nome_empresa
                 FROM empresas e
                 JOIN usuarios u ON u.id = e.usuario_id
                 JOIN lugares l ON l.id = e.lugar_id
                 WHERE e.lugar_id = ?', [$lid]
            );
            if ($emp) {
                $html = _renderEmail(
                    __DIR__ . '/../../emails/suspensa.php',
                    ['nome' => $emp['nome'], 'nome_empresa' => $emp['nome_empresa']]
                );
                $res = Mailer::send(
                    $emp['email'],
                    $emp['nome'],
                    'Sua página foi temporariamente suspensa',
                    $html
                );
                error_log('[mail suspensa] ' . json_encode($res));
            } else {
                error_log('[mail suspensa] usuario nao encontrado para lugar_id=' . $lid);
            }
        } catch (Throwable $ex) {
            error_log('[mail suspensa] ERRO: ' . $ex->getMessage());
        }

        $_SESSION['flash'] = ['type'=>'ok','msg'=>'Lugar inativado e empresa suspensa.'];
        } elseif ($act === 'reativar') {
        DB::exec('UPDATE lugares SET ativo = 1 WHERE id = ?', [$lid]);
        DB::exec(
            'UPDATE empresas SET status = "aprovada"
             WHERE lugar_id = ? AND status = "suspensa"',
            [$lid]
        );
        $_SESSION['flash'] = ['type'=>'ok','msg'=>'Lugar reativado.'];
    } elseif ($act === 'excluir') {
        DB::beginTransaction();
        try {
            // Remove dependências na ordem correta
            DB::exec('DELETE FROM fotos           WHERE lugar_id = ?', [$lid]);
            DB::exec('DELETE FROM horarios         WHERE lugar_id = ?', [$lid]);
            DB::exec('DELETE FROM lugar_tags       WHERE lugar_id = ?', [$lid]);
            DB::exec('DELETE FROM lugar_servicos   WHERE lugar_id = ?', [$lid]);
            DB::exec('DELETE FROM avaliacoes       WHERE lugar_id = ?', [$lid]);
            // Desvincula empresa (não apaga a empresa, só o vínculo)
            DB::exec(
                'UPDATE empresas SET lugar_id = NULL, status = "rascunho", plano_ativo = NULL
                 WHERE lugar_id = ?',
                [$lid]
            );
            // Remove o lugar
            DB::exec('DELETE FROM lugares WHERE id = ?', [$lid]);
            DB::commit();
            $_SESSION['flash'] = ['type'=>'ok','msg'=>'Lugar excluído com sucesso.'];
        } catch (Exception $e) {
            DB::rollback();
            error_log('[excluir lugar] ' . $e->getMessage());
            $_SESSION['flash'] = ['type'=>'erro','msg'=>'Erro ao excluir. Tente novamente.'];
        }
    }
    header('Location: /admin/lugares/index.php?' . http_build_query(['q'=>$q,'cat'=>$cat,'page'=>$page]));
    exit;
}

include __DIR__ . '/../_layout.php';
?>

<!-- Toolbar -->
<div class="flex flex-wrap items-center gap-3 mb-6">
  <form method="GET" class="flex items-center gap-2 flex-1 min-w-[220px]">
    <div class="flex items-center gap-2 bg-white border border-green/[0.1] rounded-full
                px-4 h-10 flex-1 max-w-[280px]">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#c9aa6b"
           stroke-width="1.75" stroke-linecap="round">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <input type="text" name="q" value="<?= Sanitize::html($q) ?>"
             placeholder="Buscar por nome…"
             class="flex-1 border-none outline-none text-[13px] bg-transparent"/>
    </div>
    <select name="cat"
            class="h-10 px-3 bg-white border border-green/[0.1] rounded-full
                   text-[13px] font-medium text-graphite outline-none cursor-pointer">
      <option value="0">Todas categorias</option>
      <?php foreach ($categorias as $c): ?>
      <option value="<?= $c['id'] ?>" <?= $cat == $c['id'] ? 'selected' : '' ?>>
        <?= Sanitize::html($c['label']) ?>
      </option>
      <?php endforeach; ?>
    </select>
    <button type="submit"
            class="h-10 px-5 bg-green-dark hover:bg-green text-white text-[12px]
                   font-bold rounded-full transition-colors">Filtrar</button>
  </form>

  <a href="/admin/lugares/create.php"
     class="inline-flex items-center gap-2 h-10 px-5 bg-[#c9aa6b] hover:bg-[#ddc48a]
            text-[#2a3022] text-[12px] font-black tracking-widest uppercase rounded-full
            transition-colors ml-auto">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2.5" stroke-linecap="round">
      <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
    Novo Lugar
  </a>
</div>

<!-- Results info -->
<p class="text-[12px] text-warmgray mb-4">
  <span class="font-black text-graphite"><?= $total ?></span> lugar<?= $total != 1 ? 'es' : '' ?> encontrado<?= $total != 1 ? 's' : '' ?>
</p>

<!-- Table -->
<div class="bg-white rounded-2xl border border-green/[0.07] shadow-sm overflow-hidden mb-6">
  <table class="w-full">
    <thead>
      <tr class="text-[10px] font-black tracking-[0.14em] uppercase text-warmgray border-b border-offwhite bg-offwhite/60">
        <th class="px-5 py-3.5 text-left">Lugar</th>
        <th class="px-5 py-3.5 text-left hidden sm:table-cell">Categoria</th>
        <th class="px-5 py-3.5 text-center hidden md:table-cell">Rating</th>
        <th class="px-5 py-3.5 text-center hidden lg:table-cell">Destaque</th>
        <th class="px-5 py-3.5 text-right">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($lugares as $l): ?>
      <tr class="border-b border-offwhite last:border-0 hover:bg-offwhite/50 transition-colors">
        <td class="px-5 py-3.5">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 bg-offwhite">
              <?php if ($l['foto']): ?>
              <img src="<?= Sanitize::html($l['foto']) ?>" alt=""
                   class="w-full h-full object-cover"/>
              <?php else: ?>
              <div class="w-full h-full flex items-center justify-center text-warmgray/40">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/>
                  <circle cx="8.5" cy="8.5" r="1.5"/>
                  <polyline points="21 15 16 10 5 21"/>
                </svg>
              </div>
              <?php endif; ?>
            </div>
            <div>
              <p class="font-semibold text-[13.5px] text-graphite">
                <?= Sanitize::html($l['nome']) ?>
                <?php if (!$l['ativo']): ?>
                <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-black bg-red-100 text-red-600">INATIVO</span>
                <?php endif; ?>
              </p>
              <?php if ($l['badge']): ?>
              <span class="inline-block px-2 py-0.5 bg-gold-pale text-[#2a3022] text-[9px]
                           font-black tracking-wider uppercase rounded-full mt-0.5">
                <?= Sanitize::html($l['badge']) ?>
              </span>
              <?php endif; ?>
            </div>
          </div>
        </td>
        <td class="px-5 py-3.5 hidden sm:table-cell">
          <span class="text-[12.5px] text-warmgray"><?= Sanitize::html($l['cat_nome']) ?></span>
        </td>
        <td class="px-5 py-3.5 text-center hidden md:table-cell">
          <span class="text-[13px] font-bold text-[#c9aa6b]">
            <?= $l['rating'] > 0 ? '★ ' . number_format($l['rating'], 1) : '—' ?>
          </span>
        </td>
        <td class="px-5 py-3.5 text-center hidden lg:table-cell">
          <form method="POST" style="display:inline">
            <input type="hidden" name="_token"   value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
            <input type="hidden" name="action"   value="toggle_destaque"/>
            <input type="hidden" name="lugar_id" value="<?= $l['id'] ?>"/>
            <button type="submit"
                    class="w-7 h-7 rounded-full border transition-all duration-200
                           <?= $l['destaque']
                               ? 'bg-[#c9aa6b] border-[#c9aa6b] text-[#2a3022]'
                               : 'border-green/20 text-warmgray/40 hover:border-[#c9aa6b]' ?>">
              ★
            </button>
          </form>
        </td>
        <td class="px-5 py-3.5 text-right">
          <div class="flex items-center justify-end gap-2">
            <a href="/pages/<?= Sanitize::html($l['slug']) ?>" target="_blank"
               class="text-[11px] font-semibold text-warmgray hover:text-graphite transition-colors">
               Ver
            </a>
            <a href="/admin/lugares/edit.php?id=<?= (int)$l['id'] ?>"
               class="text-[11px] font-bold text-gold hover:text-gold-light transition-colors">
               Editar
            </a>
            <?php if ($l['ativo']): ?>
            <form method="POST" style="display:inline"
                  onsubmit="return confirm('Inativar este lugar?')">
              <input type="hidden" name="_token"   value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
              <input type="hidden" name="action"   value="inativar"/>
              <input type="hidden" name="lugar_id" value="<?= (int)$l['id'] ?>"/>
              <button type="submit"
                      class="text-[11px] font-bold text-red-400 hover:text-red-600 transition-colors">
                Inativar
              </button>
            </form>
            <?php else: ?>
            <form method="POST" style="display:inline">
              <input type="hidden" name="_token"   value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
              <input type="hidden" name="action"   value="reativar"/>
              <input type="hidden" name="lugar_id" value="<?= (int)$l['id'] ?>"/>
              <button type="submit"
                      class="text-[11px] font-bold text-emerald-500 hover:text-emerald-700 transition-colors">
                Reativar
              </button>
            </form>
            <?php endif; ?>
            <form method="POST" style="display:inline"
                  onsubmit="return confirm('ATENÇÃO: Excluir permanentemente este lugar?\nEsta ação não pode ser desfeita.')">
              <input type="hidden" name="_token"   value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
              <input type="hidden" name="action"   value="excluir"/>
              <input type="hidden" name="lugar_id" value="<?= (int)$l['id'] ?>"/>
              <button type="submit"
                      class="text-[11px] font-bold text-red-600 hover:text-red-800 transition-colors">
                Excluir
              </button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($lugares)): ?>
      <tr><td colspan="5" class="px-6 py-10 text-center text-[13px] text-warmgray">
        Nenhum lugar encontrado.
        <a href="/admin/lugares/create.php" class="text-gold hover:underline ml-1">Cadastrar</a>
      </td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Pagination -->
<?php if ($pages > 1): ?>
<div class="flex items-center justify-center gap-2">
  <?php for ($p = 1; $p <= $pages; $p++): ?>
  <a href="?<?= http_build_query(['q'=>$q,'cat'=>$cat,'page'=>$p]) ?>"
     class="w-9 h-9 rounded-full border text-[13px] font-bold flex items-center
            justify-center transition-all duration-200
            <?= $p === $page
                ? 'bg-green-dark text-white border-green-dark'
                : 'border-green/[0.12] text-warmgray hover:border-gold hover:text-gold' ?>">
    <?= $p ?>
  </a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../_layout_end.php'; ?>