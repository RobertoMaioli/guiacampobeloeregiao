<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

Auth::require();

$page_title = 'Avaliações';

/* ── POST: aprovar / remover ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Sanitize::csrfValid($_POST['_token'] ?? '')) {
    $action = Sanitize::post('action');
    $aid    = Sanitize::post('id', 'int');

    if ($action === 'aprovar') {
        DB::exec('UPDATE avaliacoes SET aprovado = 1 WHERE id=?', [$aid]);
        $_SESSION['flash'] = ['type'=>'ok', 'msg'=>'Avaliação aprovada.'];
    } elseif ($action === 'rejeitar') {
        DB::exec('UPDATE avaliacoes SET aprovado = 0 WHERE id=?', [$aid]);
        $_SESSION['flash'] = ['type'=>'ok', 'msg'=>'Avaliação rejeitada.'];
    } elseif ($action === 'excluir') {
        DB::exec('DELETE FROM avaliacoes WHERE id=?', [$aid]);
        $_SESSION['flash'] = ['type'=>'ok', 'msg'=>'Avaliação removida.'];
    } elseif ($action === 'sync_rating') {
        /* Recalcula rating médio do lugar baseado nas avaliações aprovadas */
        $lid = Sanitize::post('lugar_id', 'int');
        $agg = DB::row(
            'SELECT AVG(nota) AS media, COUNT(*) AS total
             FROM avaliacoes WHERE lugar_id=? AND aprovado=1',
            [$lid]
        );
        DB::exec(
            'UPDATE lugares SET rating=?, total_reviews=? WHERE id=?',
            [round((float)$agg['media'], 1), (int)$agg['total'], $lid]
        );
        $_SESSION['flash'] = ['type'=>'ok','msg'=>'Rating atualizado.'];
    }

    header('Location: /admin/avaliacoes/index.php'); exit;
}

/* ── Filtros ── */
$filtro_fonte   = Sanitize::get('fonte');
$filtro_status  = Sanitize::get('status', 'str', 'todos');
$filtro_lugar   = Sanitize::get('lugar', 'int', 0);
$page           = max(1, Sanitize::get('page', 'int', 1));
$per            = 20;
$off            = ($page - 1) * $per;

$where  = ['1=1'];
$params = [];

if ($filtro_fonte !== '') {
    $where[] = 'a.fonte = ?'; $params[] = $filtro_fonte;
}
if ($filtro_status === 'aprovadas')   { $where[] = 'a.aprovado = 1'; }
if ($filtro_status === 'pendentes')   { $where[] = 'a.aprovado = 0'; }
if ($filtro_lugar > 0) { $where[] = 'a.lugar_id = ?'; $params[] = $filtro_lugar; }

$whereSQL = implode(' AND ', $where);
$total    = (int)DB::row("SELECT COUNT(*) n FROM avaliacoes a WHERE $whereSQL", $params)['n'];
$pages    = (int)ceil($total / $per);

$avaliacoes = DB::query(
    "SELECT a.*, l.nome AS lugar_nome, l.id AS lugar_id
     FROM avaliacoes a
     JOIN lugares l ON l.id = a.lugar_id
     WHERE $whereSQL
     ORDER BY a.criado_em DESC
     LIMIT ? OFFSET ?",
    array_merge($params, [$per, $off])
);

$lugares_lista = DB::query(
    'SELECT id, nome FROM lugares WHERE ativo=1 ORDER BY nome'
);

/* Contagem pendentes */
$pendentes = (int)DB::row('SELECT COUNT(*) n FROM avaliacoes WHERE aprovado=0')['n'];

include __DIR__ . '/../_layout.php';
?>

<!-- Filtros -->
<div class="flex flex-wrap items-center gap-3 mb-6">
  <form method="GET" class="flex flex-wrap items-center gap-2">
    <select name="fonte" class="h-10 px-3 bg-white border border-green/[0.1] rounded-full
                                text-[13px] outline-none cursor-pointer">
      <option value="">Todas as fontes</option>
      <option value="google"      <?= $filtro_fonte==='google'      ? 'selected':'' ?>>Google</option>
      <option value="tripadvisor" <?= $filtro_fonte==='tripadvisor' ? 'selected':'' ?>>TripAdvisor</option>
      <option value="manual"      <?= $filtro_fonte==='manual'      ? 'selected':'' ?>>Manual</option>
    </select>

    <select name="status" class="h-10 px-3 bg-white border border-green/[0.1] rounded-full
                                 text-[13px] outline-none cursor-pointer">
      <option value="todos"    <?= $filtro_status==='todos'    ? 'selected':'' ?>>Todas</option>
      <option value="aprovadas"<?= $filtro_status==='aprovadas'? 'selected':'' ?>>Aprovadas</option>
      <option value="pendentes"<?= $filtro_status==='pendentes'? 'selected':'' ?>>
        Pendentes <?= $pendentes > 0 ? "($pendentes)" : '' ?>
      </option>
    </select>

    <select name="lugar" class="h-10 px-3 bg-white border border-green/[0.1] rounded-full
                                text-[13px] outline-none cursor-pointer max-w-[200px]">
      <option value="0">Todos os lugares</option>
      <?php foreach ($lugares_lista as $l): ?>
      <option value="<?= $l['id'] ?>" <?= $filtro_lugar==$l['id'] ? 'selected':'' ?>>
        <?= Sanitize::html($l['nome']) ?>
      </option>
      <?php endforeach; ?>
    </select>

    <button type="submit"
            class="h-10 px-5 bg-green-dark hover:bg-green text-white text-[12px]
                   font-bold rounded-full transition-colors">Filtrar</button>
  </form>

  <span class="text-[12px] text-warmgray ml-auto">
    <span class="font-black text-graphite"><?= $total ?></span> avaliações
  </span>
</div>

<!-- Info fontes externas -->
<div class="bg-gold-pale border border-gold/30 rounded-2xl p-5 mb-6">
  <div class="flex items-start gap-3">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#c9aa6b"
         stroke-width="1.75" stroke-linecap="round" class="flex-shrink-0 mt-0.5">
      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
      <line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <div>
      <p class="text-[13px] font-bold text-green-dark mb-1">Integrações externas</p>
      <p class="text-[12.5px] text-green/70 leading-relaxed">
        Avaliações do Google Meu Negócio e TripAdvisor serão sincronizadas aqui automaticamente
        quando as integrações forem configuradas. Por ora, você pode adicionar avaliações manualmente
        abaixo ou importar via CSV.
      </p>
    </div>
  </div>
</div>

<!-- Lista de avaliações -->
<div class="bg-white rounded-2xl border border-green/[0.07] shadow-sm overflow-hidden mb-6">
  <?php if (empty($avaliacoes)): ?>
  <div class="py-14 text-center">
    <p class="text-[14px] font-display font-bold text-green-dark mb-1">Nenhuma avaliação</p>
    <p class="text-[13px] text-warmgray">Ajuste os filtros ou aguarde as integrações externas.</p>
  </div>
  <?php else: ?>
  <div class="divide-y divide-offwhite">
    <?php foreach ($avaliacoes as $av): ?>
    <div class="p-5 hover:bg-offwhite/40 transition-colors">
      <div class="flex items-start justify-between gap-4 flex-wrap">

        <!-- Autor + texto -->
        <div class="flex items-start gap-3 flex-1 min-w-0">
          <div class="w-9 h-9 rounded-full bg-green-dark flex items-center justify-center
                      text-gold font-black text-[13px] flex-shrink-0">
            <?= mb_substr($av['autor_nome'] ?? '?', 0, 1) ?>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap mb-1">
              <span class="font-bold text-[13.5px] text-graphite">
                <?= Sanitize::html($av['autor_nome'] ?? 'Anônimo') ?>
              </span>
              <!-- Fonte badge -->
              <span class="px-2 py-0.5 rounded-full text-[9px] font-black tracking-wider uppercase
                           <?= match($av['fonte']) {
                               'google'      => 'bg-blue-50 text-blue-600',
                               'tripadvisor' => 'bg-green-50 text-green-700',
                               default       => 'bg-offwhite text-warmgray'
                           } ?>">
                <?= Sanitize::html($av['fonte']) ?>
              </span>
              <!-- Status -->
              <span class="px-2 py-0.5 rounded-full text-[9px] font-black tracking-wider uppercase
                           <?= $av['aprovado'] ? 'bg-emerald-50 text-emerald-600' : 'bg-orange-50 text-orange-500' ?>">
                <?= $av['aprovado'] ? 'Aprovada' : 'Pendente' ?>
              </span>
            </div>
            <!-- Lugar + nota + data -->
            <div class="flex items-center gap-3 text-[11.5px] text-warmgray mb-2 flex-wrap">
              <span><?= Sanitize::html($av['lugar_nome']) ?></span>
              <span class="text-gold font-bold">
                <?= str_repeat('★', (int)round($av['nota'])) ?>
                <?= number_format($av['nota'], 1) ?>
              </span>
              <?php if ($av['data_avaliacao']): ?>
              <span><?= date('d/m/Y', strtotime($av['data_avaliacao'])) ?></span>
              <?php endif; ?>
            </div>
            <?php if ($av['texto']): ?>
            <p class="text-[13px] text-graphite/75 leading-relaxed italic">
              "<?= Sanitize::html(mb_substr($av['texto'], 0, 240)) ?><?= mb_strlen($av['texto']) > 240 ? '…' : '' ?>"
            </p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Ações -->
        <div class="flex items-center gap-2 flex-shrink-0">
          <?php if (!$av['aprovado']): ?>
          <form method="POST" style="display:inline">
            <input type="hidden" name="_token"  value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
            <input type="hidden" name="action"  value="aprovar"/>
            <input type="hidden" name="id"      value="<?= (int)$av['id'] ?>"/>
            <button type="submit"
                    class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white
                           text-[11px] font-bold rounded-full transition-colors">
              Aprovar
            </button>
          </form>
          <?php else: ?>
          <form method="POST" style="display:inline">
            <input type="hidden" name="_token"  value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
            <input type="hidden" name="action"  value="rejeitar"/>
            <input type="hidden" name="id"      value="<?= (int)$av['id'] ?>"/>
            <button type="submit"
                    class="px-3 py-1.5 bg-offwhite hover:bg-gold-pale text-warmgray
                           text-[11px] font-bold rounded-full transition-colors">
              Rejeitar
            </button>
          </form>
          <?php endif; ?>

          <!-- Recalcular rating do lugar -->
          <form method="POST" style="display:inline"
                title="Atualizar rating médio do lugar">
            <input type="hidden" name="_token"   value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
            <input type="hidden" name="action"   value="sync_rating"/>
            <input type="hidden" name="id"       value="<?= (int)$av['id'] ?>"/>
            <input type="hidden" name="lugar_id" value="<?= (int)$av['lugar_id'] ?>"/>
            <button type="submit"
                    class="px-3 py-1.5 bg-gold-pale hover:bg-gold text-green-dark
                           text-[11px] font-bold rounded-full transition-colors">
              ↺ Rating
            </button>
          </form>

          <form method="POST" style="display:inline"
                onsubmit="return confirm('Excluir esta avaliação permanentemente?')">
            <input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
            <input type="hidden" name="action" value="excluir"/>
            <input type="hidden" name="id"     value="<?= (int)$av['id'] ?>"/>
            <button type="submit"
                    class="text-[11px] font-bold text-red-400 hover:text-red-600
                           transition-colors px-2">
              Excluir
            </button>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Paginação -->
<?php if ($pages > 1): ?>
<div class="flex items-center justify-center gap-2">
  <?php for ($p = 1; $p <= $pages; $p++): ?>
  <a href="?<?= http_build_query(['fonte'=>$filtro_fonte,'status'=>$filtro_status,'lugar'=>$filtro_lugar,'page'=>$p]) ?>"
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
