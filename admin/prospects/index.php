<?php
require_once __DIR__ . '/../../includes/icons.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Sanitize.php';

Auth::require();

$page_title = 'Cadastros de Campo';

$STATUS_VALIDOS = ['Prospect', 'Contatado', 'Convertido', 'Sem interesse', 'Descartado'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Sanitize::csrfValid($_POST['_token'] ?? '')) {
    $action = Sanitize::post('action');
    $pid    = Sanitize::post('id', 'int');

    if ($action === 'status') {
        $novoStatus = Sanitize::post('status');
        if (in_array($novoStatus, $STATUS_VALIDOS, true)) {
            DB::exec('UPDATE prospects SET status = ? WHERE id = ?', [$novoStatus, $pid]);
            $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Status atualizado.'];
        }
    } elseif ($action === 'excluir') {
        $p = DB::row('SELECT foto_fachada FROM prospects WHERE id = ?', [$pid]);
        DB::exec('DELETE FROM prospects WHERE id = ?', [$pid]);
        if ($p && $p['foto_fachada']) {
            $caminho = __DIR__ . '/../../' . ltrim($p['foto_fachada'], '/');
            if (is_file($caminho)) @unlink($caminho);
        }
        $_SESSION['flash'] = ['type' => 'ok', 'msg' => 'Cadastro excluído.'];
    }

    header('Location: /admin/prospects/index.php?' . http_build_query($_GET));
    exit;
}

$filtro_status = Sanitize::get('status', 'str', 'todos');
$filtro_regiao = Sanitize::get('regiao');
$filtro_busca  = Sanitize::get('busca');
$page          = max(1, Sanitize::get('page', 'int', 1));
$per           = 20;
$off           = ($page - 1) * $per;

$where  = ['1=1'];
$params = [];

if ($filtro_status !== 'todos' && in_array($filtro_status, $STATUS_VALIDOS, true)) {
    $where[] = 'status = ?'; $params[] = $filtro_status;
}
if ($filtro_regiao !== '') {
    $where[] = 'regiao = ?'; $params[] = $filtro_regiao;
}
if ($filtro_busca !== '') {
    $where[] = '(nome_fantasia LIKE ? OR decisor LIKE ? OR vendedor LIKE ? OR whatsapp LIKE ?)';
    $like = '%' . $filtro_busca . '%';
    array_push($params, $like, $like, $like, $like);
}

$whereSQL = implode(' AND ', $where);
$total    = (int)DB::row("SELECT COUNT(*) n FROM prospects WHERE $whereSQL", $params)['n'];
$pages    = (int)ceil($total / $per);

$prospects = DB::query(
    "SELECT * FROM prospects WHERE $whereSQL ORDER BY criado_em DESC LIMIT ? OFFSET ?",
    array_merge($params, [$per, $off])
);

$regioes_lista = DB::query('SELECT DISTINCT regiao FROM prospects WHERE regiao <> "" ORDER BY regiao');
$contagem_status = DB::query('SELECT status, COUNT(*) n FROM prospects GROUP BY status');
$statusCount = array_column($contagem_status, 'n', 'status');

include __DIR__ . '/../_layout.php';
?>

<style>
    .toolbar-select {
        height: 40px; padding: 0 .875rem;
        background: #fff; border: 1px solid rgba(61,71,51,.1); border-radius: 50px;
        font-size: .8125rem; font-family: 'Montserrat', sans-serif; color: var(--graphite);
        outline: none; cursor: pointer; max-width: 200px;
    }
    .toolbar-input {
        height: 40px; padding: 0 1rem;
        background: #fff; border: 1px solid rgba(61,71,51,.1); border-radius: 50px;
        font-size: .8125rem; font-family: 'Montserrat', sans-serif; color: var(--graphite);
        outline: none; width: 200px;
    }
    .btn-toolbar-filter {
        height: 40px; padding: 0 1.25rem;
        background: var(--green-dk); color: #fff;
        font-size: .75rem; font-weight: 700; border: none; border-radius: 50px;
        cursor: pointer; transition: background .2s; font-family: 'Montserrat', sans-serif; white-space: nowrap;
    }
    .btn-toolbar-filter:hover { background: var(--green); }

    .prospect-card {
        background: #fff; border: 1px solid rgba(61,71,51,.07); border-radius: 1rem;
        box-shadow: 0 1px 4px rgba(0,0,0,.04); margin-bottom: 1.5rem; overflow: hidden;
    }
    .prospect-item { padding: 1.1rem 1.25rem; border-bottom: 1px solid var(--offwhite); }
    .prospect-item:last-child { border-bottom: none; }

    .prospect-thumb {
        width: 56px; height: 56px; border-radius: .6rem; object-fit: cover;
        flex-shrink: 0; background: var(--offwhite);
    }
    .prospect-nome { font-size: .875rem; font-weight: 700; color: var(--graphite); }
    .prospect-meta { font-size: .72rem; color: var(--warmgray); display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .15rem; }

    .badge-status {
        padding: .15rem .55rem; border-radius: 50px; font-size: .5625rem; font-weight: 900;
        letter-spacing: .06em; text-transform: uppercase; white-space: nowrap;
    }
    .badge-status-Prospect      { background: #eff6ff; color: #1d4ed8; }
    .badge-status-Contatado     { background: #fff7ed; color: #c2410c; }
    .badge-status-Convertido    { background: #f0fdf4; color: #16a34a; }
    .badge-status-Sem-interesse,
    .badge-status-Descartado    { background: var(--offwhite); color: var(--warmgray); }

    .status-select {
        height: 32px; padding: 0 .6rem; font-size: .72rem;
        border: 1px solid rgba(61,71,51,.12); border-radius: .5rem;
        font-family: 'Montserrat', sans-serif; color: var(--graphite); background: #fff;
    }

    .btn-delete-sm {
        padding: .35rem .7rem; background: #fef2f2; color: #dc2626;
        font-size: .68rem; font-weight: 700; border: none; border-radius: .5rem; cursor: pointer;
        font-family: 'Montserrat', sans-serif;
    }
    .btn-delete-sm:hover { background: #fee2e2; }

    details.prospect-detalhe summary {
        cursor: pointer; font-size: .74rem; font-weight: 700; color: var(--green-dk);
        list-style: none; margin-top: .6rem; user-select: none;
    }
    details.prospect-detalhe summary::-webkit-details-marker { display: none; }
    details.prospect-detalhe summary::before { content: "▸ "; }
    details.prospect-detalhe[open] summary::before { content: "▾ "; }

    .detalhe-grade {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: .6rem 1.25rem; margin-top: .85rem; padding-top: .85rem; border-top: 1px solid var(--offwhite);
    }
    .detalhe-campo dt {
        font-size: .625rem; font-weight: 900; letter-spacing: .08em; text-transform: uppercase;
        color: var(--warmgray); margin: 0 0 .1rem;
    }
    .detalhe-campo dd { font-size: .8rem; color: var(--graphite); margin: 0; word-break: break-word; }
    .detalhe-campo dd:empty::after,
    .detalhe-campo dd.vazio { content: "—"; color: var(--warmgray); }
    .detalhe-foto-grande { max-width: 280px; border-radius: .6rem; margin-top: .85rem; display: block; }
    .detalhe-obs { grid-column: 1 / -1; }

    .empty-state { padding: 3.5rem 1.5rem; text-align: center; }
    .empty-state-title { font-size: .875rem; font-weight: 700; color: var(--green-dk); margin-bottom: .375rem; }
    .empty-state-sub { font-size: .8125rem; color: var(--warmgray); margin: 0; }

    .pagination-wrap { display: flex; justify-content: center; align-items: center; gap: .5rem; flex-wrap: wrap; }
    .page-btn {
        width: 36px; height: 36px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .8125rem; font-weight: 700; text-decoration: none; transition: all .2s;
        border: 1px solid rgba(61,71,51,.12); color: var(--warmgray);
    }
    .page-btn:hover  { border-color: var(--gold); color: var(--gold); }
    .page-btn.active { background: var(--green-dk); border-color: var(--green-dk); color: #fff; }
</style>

<!-- ── Toolbar ── -->
<div class="d-flex flex-wrap align-items-center gap-2 mb-4">
    <form method="GET" class="d-flex flex-wrap align-items-center gap-2">

        <select name="status" class="toolbar-select">
            <option value="todos" <?= $filtro_status==='todos' ? 'selected':'' ?>>Todos os status</option>
            <?php foreach ($STATUS_VALIDOS as $st): ?>
            <option value="<?= Sanitize::html($st) ?>" <?= $filtro_status===$st ? 'selected':'' ?>>
                <?= Sanitize::html($st) ?><?= isset($statusCount[$st]) ? " ({$statusCount[$st]})" : '' ?>
            </option>
            <?php endforeach; ?>
        </select>

        <select name="regiao" class="toolbar-select">
            <option value="">Todas as regiões</option>
            <?php foreach ($regioes_lista as $r): ?>
            <option value="<?= Sanitize::html($r['regiao']) ?>" <?= $filtro_regiao===$r['regiao'] ? 'selected':'' ?>>
                <?= Sanitize::html($r['regiao']) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="busca" class="toolbar-input" placeholder="Nome, decisor, vendedor, whatsapp…"
               value="<?= Sanitize::html($filtro_busca) ?>">

        <button type="submit" class="btn-toolbar-filter">Filtrar</button>
    </form>

    <span style="font-size:.75rem; color:var(--warmgray); margin-left:auto">
        <strong style="color:var(--graphite)"><?= $total ?></strong> cadastros
    </span>
</div>

<!-- ── Lista ── -->
<div class="prospect-card">
    <?php if (empty($prospects)): ?>
    <div class="empty-state">
        <p class="empty-state-title">Nenhum cadastro encontrado</p>
        <p class="empty-state-sub">Ajuste os filtros, ou compartilhe o link da ficha com os vendedores: <code>/cadastro/?vendedor=Nome</code></p>
    </div>
    <?php else: foreach ($prospects as $p):
        $statusClass = 'badge-status-' . str_replace(' ', '-', $p['status']);
    ?>
    <div class="prospect-item">
        <div class="d-flex align-items-start gap-3">
            <?php if ($p['foto_fachada']): ?>
            <img src="<?= Sanitize::html($p['foto_fachada']) ?>" class="prospect-thumb" alt="Fachada">
            <?php else: ?>
            <div class="prospect-thumb"></div>
            <?php endif; ?>

            <div style="flex:1; min-width:0">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="prospect-nome"><?= Sanitize::html($p['nome_fantasia']) ?></span>
                    <span class="badge-status <?= $statusClass ?>"><?= Sanitize::html($p['status']) ?></span>
                </div>
                <div class="prospect-meta">
                    <span><?= Sanitize::html($p['categoria']) ?></span>
                    <span>·</span>
                    <span><?= Sanitize::html($p['regiao']) ?></span>
                    <span>·</span>
                    <span>Vendedor: <?= Sanitize::html($p['vendedor'] ?: '—') ?></span>
                    <span>·</span>
                    <span><?= date('d/m/Y H:i', strtotime($p['criado_em'])) ?></span>
                </div>

                <div class="d-flex align-items-center flex-wrap gap-2 mt-2">
                    <a href="https://wa.me/55<?= preg_replace('/\D/', '', $p['whatsapp']) ?>" target="_blank"
                       style="font-size:.72rem; font-weight:700; color:var(--green-dk); text-decoration:none">
                        WhatsApp: <?= Sanitize::html($p['whatsapp']) ?>
                    </a>

                    <form method="POST" class="d-flex align-items-center gap-1"
                          onchange="this.requestSubmit()">
                        <input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
                        <input type="hidden" name="action" value="status"/>
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>"/>
                        <select name="status" class="status-select">
                            <?php foreach ($STATUS_VALIDOS as $st): ?>
                            <option value="<?= Sanitize::html($st) ?>" <?= $p['status']===$st ? 'selected':'' ?>><?= Sanitize::html($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <form method="POST" onsubmit="return confirm('Excluir este cadastro permanentemente? A foto também será apagada.')">
                        <input type="hidden" name="_token" value="<?= Sanitize::html(Sanitize::csrfToken()) ?>"/>
                        <input type="hidden" name="action" value="excluir"/>
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>"/>
                        <button type="submit" class="btn-delete-sm">Excluir</button>
                    </form>
                </div>

                <details class="prospect-detalhe">
                    <summary>Ver ficha completa</summary>
                    <?php if ($p['foto_fachada']): ?>
                    <img src="<?= Sanitize::html($p['foto_fachada']) ?>" class="detalhe-foto-grande" alt="Fachada">
                    <?php endif; ?>
                    <dl class="detalhe-grade">
                        <?php
                        $campos = [
                            'Razão social'        => $p['razao_social'],
                            'CNPJ'                => $p['cnpj'],
                            'Subcategoria'        => $p['subcategoria'],
                            'Ano de fundação'     => $p['ano_fundacao'],
                            'Endereço'            => $p['endereco'],
                            'Complemento'         => $p['complemento'],
                            'Bairro'              => $p['bairro'],
                            'CEP'                 => $p['cep'],
                            'Google Maps'         => $p['link_maps'],
                            'Decisor'             => $p['decisor'],
                            'Cargo'               => $p['cargo'],
                            'Telefone fixo'       => $p['telefone_fixo'],
                            'E-mail'              => $p['email'],
                            'Instagram'           => $p['instagram'],
                            'Seguidores'          => $p['seguidores'],
                            'Site'                => $p['site'],
                            'Delivery'            => $p['delivery'],
                            'Nota Google'         => $p['nota_google'],
                            'Horário'             => $p['horario'],
                            'Dia de fechamento'   => $p['dia_fechamento'],
                            'Abre em feriados'    => $p['feriado_atende'] ?: '—',
                            'Faixa de preço'      => $p['faixa_preco'],
                            'Ticket médio'        => $p['ticket_medio'],
                            'Capacidade'          => $p['capacidade'],
                            'Formas de pagamento' => $p['formas_pagamento'],
                            'Reservas'            => $p['reservas'],
                            'Estacionamento'      => $p['estacionamento'],
                            'Acessibilidade'      => $p['acessibilidade'],
                            'Pet friendly'        => $p['pet_friendly'],
                            'Origem do lead'      => $p['origem_lead'],
                            'Convidar CONNECT'    => $p['convidar_connect'],
                            'Potencial patrocínio'=> $p['potencial_patrocinio'],
                            'Consentimento LGPD'  => $p['consentimento'],
                            'IP de origem'        => $p['ip_origem'],
                        ];
                        foreach ($campos as $rotulo => $valor):
                        ?>
                        <div class="detalhe-campo">
                            <dt><?= Sanitize::html($rotulo) ?></dt>
                            <dd class="<?= $valor === '' ? 'vazio' : '' ?>"><?= Sanitize::html($valor) ?></dd>
                        </div>
                        <?php endforeach; ?>

                        <?php if ($p['observacoes']): ?>
                        <div class="detalhe-campo detalhe-obs">
                            <dt>Observações comerciais</dt>
                            <dd><?= nl2br(Sanitize::html($p['observacoes'])) ?></dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </details>
            </div>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>

<!-- ── Paginação ── -->
<?php if ($pages > 1): ?>
<div class="pagination-wrap">
    <?php for ($pg = 1; $pg <= $pages; $pg++): ?>
    <a href="?<?= http_build_query(['status'=>$filtro_status,'regiao'=>$filtro_regiao,'busca'=>$filtro_busca,'page'=>$pg]) ?>"
       class="page-btn <?= $pg === $page ? 'active' : '' ?>">
        <?= $pg ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../_layout_end.php'; ?>