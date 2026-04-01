<?php
/**
 * admin/empresas/index.php
 * Lista e gestão de empresas cadastradas
 */
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

Auth::require();

$page_title = 'Empresas';

// Filtros
$filtro_status = Sanitize::get('status', 'str', 'todos');
$filtro_plano  = Sanitize::get('plano',  'str', 'todos');
$busca         = Sanitize::get('q',      'str', '');

$where  = ['1=1'];
$params = [];

if ($filtro_status !== 'todos') {
    $where[]  = 'e.status = ?';
    $params[] = $filtro_status;
}
if ($filtro_plano !== 'todos') {
    $where[]  = 'e.plan_intent = ?';
    $params[] = $filtro_plano;
}
if ($busca !== '') {
    $where[]  = '(u.nome LIKE ? OR u.email LIKE ? OR l.nome LIKE ?)';
    $like     = "%$busca%";
    $params[] = $like; $params[] = $like; $params[] = $like;
}

$whereSQL = implode(' AND ', $where);

$empresas = DB::query(
    "SELECT
        e.id, e.status, e.plan_intent, e.plano_ativo,
        e.submetido_em, e.criado_em, e.motivo_recusa,
        u.id AS usuario_id, u.nome AS usuario_nome, u.email AS usuario_email,
        l.nome AS lugar_nome, l.slug AS lugar_slug
     FROM empresas e
     JOIN usuarios u ON u.id = e.usuario_id
     LEFT JOIN lugares l ON l.id = e.lugar_id
     WHERE $whereSQL
     ORDER BY
       CASE e.status WHEN 'pendente' THEN 0 WHEN 'rascunho' THEN 1
         WHEN 'aprovada' THEN 2 ELSE 3 END,
       e.submetido_em DESC, e.criado_em DESC",
    $params
);

// Totais para os cards
$totais = DB::row(
    'SELECT
        COUNT(*) AS total,
        SUM(status="pendente")  AS pendentes,
        SUM(status="aprovada")  AS aprovadas,
        SUM(status="reprovada") AS reprovadas,
        SUM(status="rascunho")  AS rascunhos
     FROM empresas'
);

// ── Ação: excluir empresa ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Sanitize::csrfValid($_POST['_token'] ?? '')) {
    $eid = Sanitize::post('empresa_id', 'int');
    $act = Sanitize::post('action');

    if ($act === 'excluir_empresa' && $eid > 0) {
        $emp = DB::row('SELECT usuario_id, lugar_id FROM empresas WHERE id = ?', [$eid]);
        if ($emp) {
            DB::beginTransaction();
            try {
                $lid = (int)($emp['lugar_id'] ?? 0);
                $uid = (int)($emp['usuario_id'] ?? 0);

                if ($lid) {
                    DB::exec('DELETE FROM fotos         WHERE lugar_id = ?', [$lid]);
                    DB::exec('DELETE FROM horarios       WHERE lugar_id = ?', [$lid]);
                    DB::exec('DELETE FROM lugar_tags     WHERE lugar_id = ?', [$lid]);
                    DB::exec('DELETE FROM lugar_servicos WHERE lugar_id = ?', [$lid]);
                    DB::exec('DELETE FROM avaliacoes     WHERE lugar_id = ?', [$lid]);
                    DB::exec('DELETE FROM lugares        WHERE id = ?',       [$lid]);
                }
                DB::exec('DELETE FROM empresa_logs WHERE empresa_id = ?', [$eid]);
                DB::exec('DELETE FROM empresas     WHERE id = ?',         [$eid]);
                if ($uid) {
                    DB::exec('DELETE FROM usuarios WHERE id = ?', [$uid]);
                }

                DB::commit();
                $_SESSION['flash'] = ['type'=>'ok','msg'=>'Empresa e usuário excluídos permanentemente.'];
            } catch (Exception $e) {
                DB::rollback();
                error_log('[excluir empresa] ' . $e->getMessage());
                $_SESSION['flash'] = ['type'=>'erro','msg'=>'Erro ao excluir. Tente novamente.'];
            }
        }
        header('Location: /admin/empresas/index.php');
        exit;
    }
}

$csrf = Sanitize::csrfToken();
include __DIR__ . '/../_layout.php';
?>

<!-- ── Cards de totais ── -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
<?php
$cards_totais = [
    ['label'=>'Total',      'val'=>$totais['total']??0,      'color'=>'bg-green-dark'],
    ['label'=>'Pendentes',  'val'=>$totais['pendentes']??0,  'color'=>'bg-[#c9aa6b]'],
    ['label'=>'Aprovadas',  'val'=>$totais['aprovadas']??0,  'color'=>'bg-emerald-600'],
    ['label'=>'Reprovadas', 'val'=>$totais['reprovadas']??0, 'color'=>'bg-red-500'],
    ['label'=>'Rascunhos',  'val'=>$totais['rascunhos']??0,  'color'=>'bg-gray-400'],
];
foreach ($cards_totais as $c): ?>
<div class="bg-white rounded-2xl p-5 border border-green/[0.07] shadow-sm">
    <div class="w-8 h-8 <?= $c['color'] ?> rounded-lg mb-3"></div>
    <div class="font-display text-[28px] font-bold text-green-dark leading-none mb-1">
        <?= (int)$c['val'] ?>
    </div>
    <div class="text-[11px] font-semibold text-warmgray uppercase tracking-wider"><?= $c['label'] ?></div>
</div>
<?php endforeach; ?>
</div>

<!-- ── Filtros ── -->
<div class="bg-white rounded-2xl border border-green/[0.07] shadow-sm p-5 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-[10px] font-black tracking-widest uppercase text-warmgray mb-1.5">Buscar</label>
            <input type="text" name="q" value="<?= Sanitize::html($busca) ?>"
                   placeholder="Nome, e-mail ou empresa…"
                   class="w-full px-3 py-2 bg-offwhite border border-green/[0.12] rounded-xl
                          text-[13px] outline-none focus:border-gold/60 transition-colors">
        </div>
        <div>
            <label class="block text-[10px] font-black tracking-widest uppercase text-warmgray mb-1.5">Status</label>
            <select name="status"
                    class="px-3 py-2 bg-offwhite border border-green/[0.12] rounded-xl text-[13px] outline-none">
                <?php foreach (['todos'=>'Todos','pendente'=>'Pendente','aprovada'=>'Aprovada',
                                'reprovada'=>'Reprovada','rascunho'=>'Rascunho','suspensa'=>'Suspensa'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= $filtro_status===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-black tracking-widest uppercase text-warmgray mb-1.5">Plano solicitado</label>
            <select name="plano"
                    class="px-3 py-2 bg-offwhite border border-green/[0.12] rounded-xl text-[13px] outline-none">
                <?php foreach (['todos'=>'Todos','essencial'=>'Essencial',
                                'profissional'=>'Profissional','premium'=>'Premium'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= $filtro_plano===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit"
                class="px-5 py-2 bg-green-dark hover:bg-green text-white text-[12px]
                       font-black tracking-widest uppercase rounded-xl transition-colors">
            Filtrar
        </button>
        <?php if ($busca || $filtro_status!=='todos' || $filtro_plano!=='todos'): ?>
        <a href="/admin/empresas/index.php"
           class="px-4 py-2 border border-green/[0.15] text-[12px] font-semibold
                  text-warmgray rounded-xl hover:border-gold transition-colors">
            Limpar
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- ── Tabela ── -->
<div class="bg-white rounded-2xl border border-green/[0.07] shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-offwhite">
        <h2 class="font-display text-[16px] font-bold text-green-dark">
            <?= count($empresas) ?> empresa<?= count($empresas)!==1?'s':'' ?> encontrada<?= count($empresas)!==1?'s':'' ?>
        </h2>
    </div>

    <?php if (empty($empresas)): ?>
    <div class="px-6 py-12 text-center text-[13px] text-warmgray">
        Nenhuma empresa encontrada com os filtros selecionados.
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="text-[10px] font-black tracking-[0.15em] uppercase text-warmgray border-b border-offwhite">
                <th class="px-6 py-3 text-left">Usuário / Empresa</th>
                <th class="px-6 py-3 text-left">Plano solicitado</th>
                <th class="px-6 py-3 text-left">Plano ativo</th>
                <th class="px-6 py-3 text-left hidden md:table-cell">Status</th>
                <th class="px-6 py-3 text-left hidden lg:table-cell">Data</th>
                <th class="px-6 py-3 text-right">Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($empresas as $e):
            $status_cfg = [
                'pendente'  => ['bg-[#fef3c7] text-[#92400e]', 'Pendente'],
                'aprovada'  => ['bg-emerald-50 text-emerald-700', 'Aprovada'],
                'reprovada' => ['bg-red-50 text-red-600', 'Reprovada'],
                'rascunho'  => ['bg-gray-100 text-gray-500', 'Rascunho'],
                'suspensa'  => ['bg-orange-50 text-orange-600', 'Suspensa'],
            ];
            $plano_cfg = [
                'essencial'    => 'bg-gray-100 text-gray-600',
                'profissional' => 'bg-blue-50 text-blue-700',
                'premium'      => 'bg-[#fef3c7] text-[#92400e]',
            ];
            [$s_class, $s_label] = $status_cfg[$e['status']] ?? ['bg-gray-100 text-gray-500', $e['status']];
        ?>
        <tr class="border-b border-offwhite last:border-0 hover:bg-offwhite/50 transition-colors
                   <?= $e['status']==='pendente' ? 'bg-[#fffbeb]/40' : '' ?>">

            <!-- Usuário / Empresa -->
            <td class="px-6 py-4">
                <div class="font-semibold text-[13.5px] text-graphite">
                    <?= Sanitize::html($e['usuario_nome']) ?>
                </div>
                <div class="text-[11.5px] text-warmgray mt-0.5">
                    <?= Sanitize::html($e['usuario_email']) ?>
                </div>
                <?php if ($e['lugar_nome']): ?>
                <div class="text-[11px] text-green mt-0.5 font-semibold">
                    <?= Sanitize::html($e['lugar_nome']) ?>
                </div>
                <?php endif; ?>
            </td>

            <!-- Plano solicitado -->
            <td class="px-6 py-4">
                <span class="inline-block px-2.5 py-1 rounded-lg text-[11px] font-bold
                             <?= $plano_cfg[$e['plan_intent']] ?? 'bg-gray-100 text-gray-600' ?>">
                    <?= ucfirst($e['plan_intent']) ?>
                </span>
            </td>

            <!-- Plano ativo -->
            <td class="px-6 py-4">
                <?php if ($e['plano_ativo']): ?>
                <span class="inline-block px-2.5 py-1 rounded-lg text-[11px] font-bold
                             <?= $plano_cfg[$e['plano_ativo']] ?? 'bg-gray-100 text-gray-600' ?>">
                    <?= ucfirst($e['plano_ativo']) ?>
                </span>
                <?php else: ?>
                <span class="text-[12px] text-warmgray">—</span>
                <?php endif; ?>
            </td>

            <!-- Status -->
            <td class="px-6 py-4 hidden md:table-cell">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg
                             text-[11px] font-bold <?= $s_class ?>">
                    <?php if ($e['status']==='pendente'): ?>
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <?php endif; ?>
                    <?= $s_label ?>
                </span>
            </td>

            <!-- Data -->
            <td class="px-6 py-4 hidden lg:table-cell">
                <div class="text-[12px] text-warmgray">
                    <?php
                    $data = $e['submetido_em'] ?? $e['criado_em'];
                    echo $data ? date('d/m/Y H:i', strtotime($data)) : '—';
                    ?>
                </div>
                <?php if ($e['status']==='pendente' && $e['submetido_em']): ?>
                <?php
                $horas = round((time() - strtotime($e['submetido_em'])) / 3600);
                $cor   = $horas > 24 ? 'text-red-500' : 'text-warmgray';
                ?>
                <div class="text-[10px] <?= $cor ?> mt-0.5">
                    há <?= $horas ?>h <?= $horas > 24 ? '⚠ atrasado' : '' ?>
                </div>
                <?php endif; ?>
            </td>

            <!-- Ações -->
            <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">

                    <?php if ($e['status'] === 'pendente'): ?>
                    <button onclick="abrirModal(<?= (int)$e['id'] ?>, '<?= Sanitize::html($e['usuario_nome']) ?>', '<?= Sanitize::html($e['plan_intent']) ?>')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-dark
                                   hover:bg-green text-white text-[11px] font-bold rounded-lg
                                   transition-colors">
                        Analisar
                    </button>
                    <?php else: ?>
                    <button onclick="abrirModal(<?= (int)$e['id'] ?>, '<?= Sanitize::html($e['usuario_nome']) ?>', '<?= Sanitize::html($e['plan_intent']) ?>')"
                            class="text-[11px] font-bold text-warmgray hover:text-green-dark transition-colors">
                        Plano
                    </button>
                    <?php endif; ?>

                    <!-- Editar usuário — aparece em TODOS os status -->
                    <button onclick="abrirModalUsuario(<?= (int)$e['usuario_id'] ?>, '<?= Sanitize::html($e['usuario_nome']) ?>', '<?= Sanitize::html($e['usuario_email']) ?>')" class="text-[11px] font-bold text-gold hover:text-green-dark transition-colors">
                        Editar
                    </button>

                    <!-- Excluir -->
                    <form method="POST" style="display:inline" onsubmit="return confirm('ATENÇÃO: Excluir permanentemente esta empresa e o usuário?\nNão pode ser desfeito.')">
                      <input type="hidden" name="_token"     value="<?= Sanitize::html($csrf) ?>">
                      <input type="hidden" name="action"     value="excluir_empresa">
                      <input type="hidden" name="empresa_id" value="<?= (int)$e['id'] ?>">
                      <button type="submit" class="text-[11px] font-bold text-red-500 hover:text-red-700 transition-colors ml-1">
                        Excluir
                      </button>
                    </form>

                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>


<!-- ════════════════════════════════════════════════════════════
     MODAL — Analisar empresa
════════════════════════════════════════════════════════════ -->
<div id="modal-overlay"
     class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
     onclick="if(event.target===this) fecharModal()">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg" onclick="event.stopPropagation()">

        <div class="flex items-center justify-between px-6 py-5 border-b border-offwhite">
            <div>
                <h3 class="font-display text-[18px] font-bold text-green-dark" id="modal-titulo">
                    Analisar cadastro
                </h3>
                <p class="text-[12px] text-warmgray mt-0.5" id="modal-subtitulo"></p>
            </div>
            <button onclick="fecharModal()"
                    class="w-8 h-8 flex items-center justify-center rounded-full
                           hover:bg-offwhite transition-colors text-warmgray">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="/admin/empresas/aprovar.php" id="modal-form">
            <input type="hidden" name="_token"     value="<?= Sanitize::html($csrf) ?>">
            <input type="hidden" name="empresa_id" id="modal-empresa-id">
            <input type="hidden" name="acao"       id="modal-acao" value="aprovar">

            <div class="px-6 py-5 space-y-4">

                <div id="bloco-plano">
                    <label class="block text-[10px] font-black tracking-widest uppercase text-warmgray mb-2">
                        Plano a ativar
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <?php foreach (['essencial'=>'Essencial','profissional'=>'Profissional','premium'=>'Premium'] as $v=>$l): ?>
                        <label class="plano-opt flex flex-col items-center p-3 rounded-xl border-2
                                      border-green/[0.1] cursor-pointer transition-all text-center
                                      hover:border-gold/50" data-val="<?= $v ?>">
                            <input type="radio" name="plano_ativo" value="<?= $v ?>"
                                   class="sr-only" <?= $v==='profissional'?'checked':'' ?>>
                            <span class="text-[12px] font-bold text-graphite"><?= $l ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black tracking-widest uppercase text-warmgray mb-2"
                           id="label-obs">
                        Observação interna (opcional)
                    </label>
                    <textarea name="observacao" id="modal-obs" rows="3"
                              placeholder="Nota interna sobre esta decisão…"
                              class="w-full px-3 py-2.5 bg-offwhite border border-green/[0.12]
                                     rounded-xl text-[13px] outline-none resize-none
                                     focus:border-gold/60 transition-colors"></textarea>
                </div>

                <div id="bloco-motivo" class="hidden">
                    <label class="block text-[10px] font-black tracking-widest uppercase text-warmgray mb-2">
                        Motivo da recusa <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 gap-1.5 mb-3">
                        <?php foreach ([
                            'Informações incompletas ou incorretas',
                            'Negócio não atua em Campo Belo ou região',
                            'Conteúdo inadequado ou ofensivo',
                            'Cadastro duplicado',
                            'Outro motivo',
                        ] as $motivo): ?>
                        <label class="flex items-center gap-2.5 p-2.5 rounded-lg cursor-pointer
                                      hover:bg-offwhite transition-colors">
                            <input type="radio" name="motivo_preset"
                                   value="<?= htmlspecialchars($motivo) ?>"
                                   onchange="document.getElementById('motivo-texto').value=this.value"
                                   class="accent-red-500">
                            <span class="text-[12.5px] text-graphite"><?= htmlspecialchars($motivo) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <textarea name="motivo_recusa" id="motivo-texto" rows="2"
                              placeholder="Descreva o motivo (será enviado ao usuário)…"
                              class="w-full px-3 py-2.5 bg-offwhite border border-red-200
                                     rounded-xl text-[13px] outline-none resize-none
                                     focus:border-red-400 transition-colors"></textarea>
                </div>

            </div>

            <div class="flex items-center gap-3 px-6 py-4 border-t border-offwhite bg-offwhite/50 rounded-b-2xl">
                <button type="button" onclick="fecharModal()"
                        class="px-4 py-2 border border-green/[0.15] text-[12px] font-semibold
                               text-warmgray rounded-xl hover:border-gold transition-colors">
                    Cancelar
                </button>
                <div class="flex-1"></div>
                <button type="button" id="btn-reprovar" onclick="setAcao('reprovar')"
                        class="px-4 py-2 border border-red-200 text-[12px] font-bold
                               text-red-600 rounded-xl hover:bg-red-50 transition-colors">
                    Reprovar
                </button>
                <button type="button" id="btn-confirmar-reprovacao" onclick="confirmarReprovacao()"
                        class="hidden px-5 py-2 bg-red-600 hover:bg-red-700 text-white
                               text-[12px] font-black tracking-wider uppercase rounded-xl transition-colors">
                    Confirmar reprovação ✗
                </button>
                <button type="submit" id="btn-aprovar" onclick="setAcao('aprovar')"
                        class="px-5 py-2 bg-green-dark hover:bg-green text-white
                               text-[12px] font-black tracking-wider uppercase rounded-xl transition-colors">
                    Aprovar ✓
                </button>
            </div>
        </form>
    </div>
</div>


<!-- ════════════════════════════════════════════════════════════
     MODAL — Editar usuário
════════════════════════════════════════════════════════════ -->
<div id="modal-usuario"
     class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
     onclick="if(event.target===this) fecharModalUsuario()">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" onclick="event.stopPropagation()">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-offwhite">
            <div>
                <h3 class="font-display text-[18px] font-bold text-green-dark">Editar usuário</h3>
                <p class="text-[12px] text-warmgray mt-0.5" id="mu-subtitulo"></p>
            </div>
            <button onclick="fecharModalUsuario()"
                    class="w-8 h-8 flex items-center justify-center rounded-full
                           hover:bg-offwhite transition-colors text-warmgray">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-offwhite">
            <button onclick="muTab('dados')" id="mu-tab-dados"
                    class="flex-1 py-3 text-[12px] font-bold tracking-wider uppercase
                           border-b-2 border-green-dark text-green-dark transition-colors">
                Dados
            </button>
            <button onclick="muTab('senha')" id="mu-tab-senha"
                    class="flex-1 py-3 text-[12px] font-bold tracking-wider uppercase
                           border-b-2 border-transparent text-warmgray transition-colors">
                Senha
            </button>
        </div>

        <!-- Feedback -->
        <div id="mu-feedback"
             class="hidden mx-6 mt-4 px-4 py-3 rounded-xl text-[13px] font-semibold"></div>

        <form id="mu-form" class="px-6 py-5 flex flex-col gap-4">
            <input type="hidden" name="_token"     value="<?= Sanitize::html($csrf) ?>">
            <input type="hidden" id="mu-usuario-id" name="usuario_id" value="">
            <input type="hidden" id="mu-acao"       name="acao"       value="dados">

            <!-- Painel dados -->
            <div id="mu-painel-dados" class="flex flex-col gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-black tracking-[0.15em] uppercase text-warmgray">Nome</label>
                    <input type="text" id="mu-nome" name="nome"
                           class="px-3 py-2.5 bg-offwhite border border-green/[0.1] rounded-xl
                                  text-[13px] outline-none focus:border-gold transition-colors"/>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-black tracking-[0.15em] uppercase text-warmgray">E-mail</label>
                    <input type="email" id="mu-email" name="email"
                           class="px-3 py-2.5 bg-offwhite border border-green/[0.1] rounded-xl
                                  text-[13px] outline-none focus:border-gold transition-colors"/>
                </div>
            </div>

            <!-- Painel senha -->
            <div id="mu-painel-senha" class="hidden flex-col gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-black tracking-[0.15em] uppercase text-warmgray">Nova senha</label>
                    <input type="password" id="mu-nova-senha" name="nova_senha"
                           placeholder="Mínimo 8 caracteres"
                           class="px-3 py-2.5 bg-offwhite border border-green/[0.1] rounded-xl
                                  text-[13px] outline-none focus:border-gold transition-colors"/>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-black tracking-[0.15em] uppercase text-warmgray">Confirmar senha</label>
                    <input type="password" id="mu-conf-senha" name="conf_senha"
                           placeholder="Repita a senha"
                           class="px-3 py-2.5 bg-offwhite border border-green/[0.1] rounded-xl
                                  text-[13px] outline-none focus:border-gold transition-colors"/>
                </div>
            </div>
        </form>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-offwhite
                    bg-offwhite/50 rounded-b-2xl">
            <button type="button" onclick="fecharModalUsuario()"
                    class="px-4 py-2 border border-green/[0.15] text-[12px] font-semibold
                           text-warmgray rounded-xl hover:border-gold transition-colors">
                Cancelar
            </button>
            <button type="button" onclick="salvarUsuario()"
                    class="px-5 py-2 bg-green-dark hover:bg-green text-white
                           text-[12px] font-black tracking-wider uppercase rounded-xl transition-colors">
                Salvar
            </button>
        </div>
    </div>
</div>


<script>
// ════════════════════════════════════════════════════════════
//  MODAL ANALISAR
// ════════════════════════════════════════════════════════════
document.querySelectorAll('.plano-opt').forEach(lbl => {
    lbl.addEventListener('click', () => {
        document.querySelectorAll('.plano-opt').forEach(l => {
            l.classList.remove('border-gold', 'bg-[#f5edda]');
            l.classList.add('border-green/[0.1]');
        });
        lbl.classList.add('border-gold', 'bg-[#f5edda]');
        lbl.classList.remove('border-green/[0.1]');
        lbl.querySelector('input').checked = true;
    });
});
document.querySelector('.plano-opt[data-val="profissional"]')?.click();

function abrirModal(id, nome, planIntent) {
    document.getElementById('modal-empresa-id').value = id;
    document.getElementById('modal-titulo').textContent = 'Analisar: ' + nome;
    document.getElementById('modal-subtitulo').textContent = 'Plano solicitado: ' + planIntent;
    document.getElementById('modal-overlay').classList.remove('hidden');
    const opt = document.querySelector('.plano-opt[data-val="' + planIntent + '"]');
    if (opt) opt.click();
    setAcao('aprovar');
}

function fecharModal() {
    document.getElementById('modal-overlay').classList.add('hidden');
    document.getElementById('motivo-texto').value = '';
    document.getElementById('modal-obs').value = '';
}

function setAcao(acao) {
    document.getElementById('modal-acao').value = acao;
    const blocoPlano   = document.getElementById('bloco-plano');
    const blocoMotivo  = document.getElementById('bloco-motivo');
    const btnAprovar   = document.getElementById('btn-aprovar');
    const btnReprovar  = document.getElementById('btn-reprovar');
    const btnConfirmar = document.getElementById('btn-confirmar-reprovacao');

    if (acao === 'reprovar') {
        blocoPlano.classList.add('hidden');
        blocoMotivo.classList.remove('hidden');
        btnAprovar.classList.add('hidden');
        btnReprovar.classList.add('hidden');
        btnConfirmar.classList.remove('hidden');
        document.getElementById('motivo-texto').focus();
    } else {
        blocoPlano.classList.remove('hidden');
        blocoMotivo.classList.add('hidden');
        btnAprovar.classList.remove('hidden');
        btnReprovar.classList.remove('hidden');
        btnConfirmar.classList.add('hidden');
    }
}

function confirmarReprovacao() {
    const motivo = document.getElementById('motivo-texto').value.trim();
    if (!motivo) {
        const el = document.getElementById('motivo-texto');
        el.focus();
        el.style.borderColor = '#ef4444';
        setTimeout(() => { el.style.borderColor = ''; }, 2000);
        return;
    }
    document.getElementById('modal-form').requestSubmit();
}


// ════════════════════════════════════════════════════════════
//  MODAL EDITAR USUÁRIO
// ════════════════════════════════════════════════════════════
function abrirModalUsuario(id, nome, email) {
    document.getElementById('mu-usuario-id').value = id;
    document.getElementById('mu-subtitulo').textContent  = nome;
    document.getElementById('mu-nome').value  = nome;
    document.getElementById('mu-email').value = email;
    document.getElementById('mu-nova-senha').value = '';
    document.getElementById('mu-conf-senha').value = '';
    document.getElementById('mu-feedback').classList.add('hidden');
    muTab('dados');
    document.getElementById('modal-usuario').classList.remove('hidden');
}

function fecharModalUsuario() {
    document.getElementById('modal-usuario').classList.add('hidden');
}

function muTab(tab) {
    const isDados = tab === 'dados';
    const painelDados = document.getElementById('mu-painel-dados');
    const painelSenha = document.getElementById('mu-painel-senha');
    const tabDados    = document.getElementById('mu-tab-dados');
    const tabSenha    = document.getElementById('mu-tab-senha');

    painelDados.classList.toggle('hidden', !isDados);
    painelSenha.classList.toggle('hidden', isDados);

    document.getElementById('mu-acao').value = isDados ? 'dados' : 'senha';

    const ativo   = 'flex-1 py-3 text-[12px] font-bold tracking-wider uppercase border-b-2 border-green-dark text-green-dark transition-colors';
    const inativo = 'flex-1 py-3 text-[12px] font-bold tracking-wider uppercase border-b-2 border-transparent text-warmgray transition-colors';
    tabDados.className = isDados ? ativo : inativo;
    tabSenha.className = isDados ? inativo : ativo;
}

async function salvarUsuario() {
    const fb      = document.getElementById('mu-feedback');
    const payload = new FormData(document.getElementById('mu-form'));

    fb.className = 'hidden mx-6 mt-0 px-4 py-3 rounded-xl text-[13px] font-semibold';

    try {
        const res  = await fetch('/admin/empresas/salvar.php', { method: 'POST', body: payload });
        const data = await res.json();

        fb.classList.remove('hidden');
        if (data.ok) {
            fb.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
            fb.textContent = data.msg;
            if (payload.get('acao') === 'dados') {
                setTimeout(() => location.reload(), 1200);
            }
        } else {
            fb.classList.add('bg-red-50', 'text-red-600', 'border', 'border-red-200');
            fb.textContent = data.erro;
        }
    } catch (err) {
        fb.classList.remove('hidden');
        fb.classList.add('bg-red-50', 'text-red-600', 'border', 'border-red-200');
        fb.textContent = 'Erro de comunicação. Tente novamente.';
    }
}

// Fechar modais com Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        fecharModal();
        fecharModalUsuario();
    }
});
</script>

<?php include __DIR__ . '/../_layout_end.php'; ?>