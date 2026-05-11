<?php
/**
 * empresa/plano.php
 * Visualização do plano atual e solicitação de upgrade
 */
require_once __DIR__ . '/../core/UserAuth.php';
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Sanitize.php';
require_once __DIR__ . '/../includes/icons.php';

UserAuth::require();
$usuario = UserAuth::current();

// Sessão ativa mas usuário excluído do banco
if (!$usuario) {
    UserAuth::logout();
    header('Location: /empresa/login.php'); exit;
}

$origem       = Sanitize::get('origem', 'str', '');
$veio_onboard = $origem === 'onboarding';

if (!in_array($usuario['empresa_status'] ?? '', ['aprovada', 'suspensa'])) {
    if (!$veio_onboard || $usuario['empresa_status'] !== 'pendente') {
        header('Location: /empresa/status.php'); exit;
    }
}

$empresa_id = (int)($usuario['empresa_id'] ?? 0);
$plano      = $usuario['plano_ativo'] ?? 'essencial';

// ── TEMPORÁRIO: desabilitar checkout ──
if ($veio_onboard) {
    header('Location: /empresa/status.php'); exit;
}
// ─────────────────────────────────────

$empresa_id = (int)($usuario['empresa_id'] ?? 0);
$plano      = $usuario['plano_ativo'] ?? 'essencial';

// Configuração completa dos planos
$planos_config = [
    'essencial' => [
        'label'    => 'Essencial',
        'preco'    => 'Grátis',
        'cor'      => '#f2f0eb',
        'cor_text' => '#8b8589',
        'features' => [
            ['ok'=>true,  'label'=>'Página da empresa'],
            ['ok'=>true,  'label'=>'Nome, endereço e contato'],
            ['ok'=>true,  'label'=>'Horário de funcionamento'],
            ['ok'=>true,  'label'=>'Descrição da empresa'],
            ['ok'=>false, 'label'=>'Imagem de capa'],
            ['ok'=>false, 'label'=>'Galeria de fotos'],
            ['ok'=>false, 'label'=>'Tags'],
            ['ok'=>false, 'label'=>'Pin no mapa interativo'],
            ['ok'=>false, 'label'=>'Sincronização Google Reviews'],
            ['ok'=>false, 'label'=>'Botão WhatsApp'],
            ['ok'=>false, 'label'=>'Links de redes sociais'],
            ['ok'=>false, 'label'=>'Formulário de contato'],
        ],
    ],
    'profissional' => [
        'label'    => 'Profissional',
        'preco'    => 'R$ 89/mês',
        'cor'      => '#eff6ff',
        'cor_text' => '#1d4ed8',
        'features' => [
            ['ok'=>true, 'label'=>'Tudo do Essencial'],
            ['ok'=>true, 'label'=>'Imagem de capa'],
            ['ok'=>true, 'label'=>'Até 5 fotos na galeria'],
            ['ok'=>true, 'label'=>'Até 5 tags'],
            ['ok'=>true, 'label'=>'Pin no mapa interativo'],
            ['ok'=>true, 'label'=>'Sincronização Google Reviews'],
            ['ok'=>true, 'label'=>'Link do site'],
            ['ok'=>true, 'label'=>'Botão WhatsApp direto'],
            ['ok'=>true, 'label'=>'Links de redes sociais'],
            ['ok'=>true, 'label'=>'Formulário de contato'],
            ['ok'=>false,'label'=>'Fotos ilimitadas'],
            ['ok'=>false,'label'=>'Tags ilimitadas'],
            ['ok'=>false,'label'=>'Selo "Destaque"'],
            ['ok'=>false,'label'=>'Sem propagandas'],
            ['ok'=>false,'label'=>'Gestão pela equipe'],
            ['ok'=>false,'label'=>'Suporte prioritário'],
        ],
    ],
    'premium' => [
        'label'    => 'Premium',
        'preco'    => 'R$ 159/mês',
        'cor'      => '#f5edda',
        'cor_text' => '#2a3022',
        'features' => [
            ['ok'=>true, 'label'=>'Tudo do Profissional'],
            ['ok'=>true, 'label'=>'Fotos ilimitadas'],
            ['ok'=>true, 'label'=>'Tags ilimitadas'],
            ['ok'=>true, 'label'=>'Selo "Destaque" na listagem'],
            ['ok'=>true, 'label'=>'Sem propagandas'],
            ['ok'=>true, 'label'=>'Gestão pela nossa equipe'],
            ['ok'=>true, 'label'=>'Suporte prioritário'],
        ],
    ],
];

$planos_ordem  = ['essencial', 'profissional', 'premium'];
$idx_atual     = array_search($plano, $planos_ordem);
$proximo_plano = $planos_ordem[$idx_atual + 1] ?? null;

$csrf          = Sanitize::csrfToken();
$erro          = '';
$page_title = 'Meu plano — Guia Campo Belo';

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?php include __DIR__ . '/../includes/head.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('site-header')?.classList.add('header-scrolled');
        });
    </script>
    <style>
        body {
            background: var(--gcb-offwhite);
            padding-top: 72px;
        }

        .plano-wrap {
            max-width: 860px;
            margin: 0 auto;
            padding: 2rem 1rem 3rem;
        }

        /* Cards de plano */
        .plano-card {
            background: #fff;
            border-radius: 20px;
            border: 1.5px solid rgba(61, 71, 51, .08);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: box-shadow .2s, transform .2s;
        }

        .plano-card.atual {
            border-color: var(--gcb-green-dark);
            box-shadow: 0 8px 32px rgba(42, 48, 34, .15);
        }

        .plano-card.upgrade-target {
            border-color: rgba(201, 170, 107, .5);
            box-shadow: 0 4px 20px rgba(201, 170, 107, .15);
            cursor: pointer;
        }

        .plano-card.upgrade-target:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 36px rgba(201, 170, 107, .25);
        }

        .plano-card.upgrade-target.selecionado {
            border-color: var(--gcb-gold);
            border-width: 2px;
            box-shadow: 0 8px 32px rgba(201, 170, 107, .3);
        }

        .plano-card.inferior {
            opacity: .5;
        }

        .plano-header {
            padding: 20px 20px 16px;
        }

        .plano-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: 3px 10px;
            border-radius: 999px;
            margin-bottom: 8px;
        }

        .plano-nome {
            font-size: 20px;
            font-weight: 800;
            color: var(--gcb-green-dark);
            margin-bottom: 2px;
        }

        .plano-preco {
            font-size: 13px;
            color: var(--gcb-warmgray);
            font-weight: 500;
        }

        .plano-body {
            padding: 16px 20px 20px;
            flex: 1;
            border-top: 1px solid rgba(61, 71, 51, .07);
        }

        .feat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 0;
            font-size: 12.5px;
        }

        .feat-ok {
            color: var(--gcb-green);
            flex-shrink: 0;
        }

        .feat-no {
            color: rgba(61, 71, 51, .2);
            flex-shrink: 0;
        }

        .feat-txt-ok {
            color: var(--gcb-graphite);
        }

        .feat-txt-no {
            color: rgba(61, 71, 51, .35);
        }

        /* Atual badge */
        .badge-atual {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 10px;
            font-weight: 800;
            color: var(--gcb-green-dark);
            background: rgba(61, 71, 51, .08);
            padding: 4px 10px;
            border-radius: 999px;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        /* Form de upgrade */
        .upgrade-form-wrap {
            background: #fff;
            border-radius: 20px;
            border: 1px solid rgba(61, 71, 51, .07);
            padding: 24px;
            margin-top: 24px;
            box-shadow: 0 2px 12px rgba(29, 29, 27, .05);
        }

        .upgrade-form-title {
            font-size: 16px;
            font-weight: 800;
            color: var(--gcb-green-dark);
            margin-bottom: 4px;
        }

        .upgrade-form-sub {
            font-size: 13px;
            color: var(--gcb-warmgray);
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .olbl {
            display: block;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
            color: var(--gcb-warmgray);
            margin-bottom: 6px;
        }

        /* Premium — já no topo */
        .premium-top {
            background: var(--gcb-green-dark);
            border-radius: 20px;
            padding: 32px;
            text-align: center;
            margin-top: 24px;
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/../includes/search-modal.php'; ?>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <div class="plano-wrap">

        <!-- Título -->
        <div style="margin-bottom:24px">
            <a href="/empresa/dashboard.php" style="display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;
              color:var(--gcb-warmgray);text-decoration:none;margin-bottom:12px">
                <?= icon('arrow-right',11) ?> Voltar ao painel
            </a>
            <h1 style="font-size:24px;font-weight:800;color:var(--gcb-green-dark);margin-bottom:4px">
                Meu plano
            </h1>
            <?php if ($veio_onboard): ?>
                <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:12px;
                            padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:10px">
                  <span style="font-size:20px">✅</span>
                  <div>
                    <div style="font-size:13px;font-weight:700;color:#065f46">Cadastro enviado com sucesso!</div>
                    <div style="font-size:12px;color:#047857;margin-top:2px">
                      Ative seu plano agora para aparecer no Guia Campo Belo assim que for aprovado.
                    </div>
                  </div>
                </div>
            <?php endif; ?>
            <?php if (!$veio_onboard): ?>
            <p style="font-size:13px;color:var(--gcb-warmgray)">
                Você está no plano <strong style="color:var(--gcb-graphite)">
                    <?= ucfirst($plano) ?>
                </strong>.
                <?php if ($proximo_plano): ?>
                Faça upgrade para desbloquear mais recursos.
                <?php else: ?>
                Você está no plano mais completo.
                <?php endif; ?>
            </p>
            <?php endif; ?>
        </div>

        <?php if ($erro): ?>
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:12px 16px;
              font-size:13px;color:#c0392b;margin-bottom:20px">
            ✗
            <?= Sanitize::html($erro) ?>
        </div>
        <?php endif; ?>

        <!-- Grid de planos -->
        <?php if (!$veio_onboard): ?>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:8px" class="planos-grid">
            <?php foreach ($planos_ordem as $slug):
              $cfg        = $planos_config[$slug];
              $idx        = array_search($slug, $planos_ordem);
              $is_atual   = $slug === $plano;
              $is_upgrade = $idx > $idx_atual;
              $is_inf     = $idx < $idx_atual;
              $card_class = $is_atual ? 'atual' : ($is_upgrade ? 'upgrade-target' : 'inferior');
            ?>
            <div class="plano-card <?= $card_class ?>" id="card-<?= $slug ?>" <?=$is_upgrade ? "onclick=\"
                selecionarPlano('{$slug}')\"" : '' ?>>

                <div class="plano-header">
                    <!-- Badge status -->
                    <?php if ($is_atual): ?>
                    <div class="badge-atual">
                        <?= icon('verified', 11) ?> Plano atual
                    </div>
                    <?php elseif ($is_upgrade): ?>
                    <div class="plano-badge" style="background:<?= $cfg['cor'] ?>;color:<?= $cfg['cor_text'] ?>">
                        Upgrade disponível
                    </div>
                    <?php else: ?>
                    <div class="plano-badge" style="background:var(--gcb-offwhite);color:var(--gcb-warmgray)">
                        Plano inferior
                    </div>
                    <?php endif; ?>

                    <div class="plano-nome">
                        <?= $cfg['label'] ?>
                    </div>
                    <div class="plano-preco">
                        <?= $cfg['preco'] ?>
                    </div>
                </div>

                <div class="plano-body">
                    <?php foreach ($cfg['features'] as $f): ?>
                    <div class="feat-item">
                        <span class="<?= $f['ok'] ? 'feat-ok' : 'feat-no' ?>">
                            <?= $f['ok'] ? '✓' : '–' ?>
                        </span>
                        <span class="<?= $f['ok'] ? 'feat-txt-ok' : 'feat-txt-no' ?>">
                            <?= htmlspecialchars($f['label']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>

                    <!-- CTA no card -->
                    <?php if ($is_upgrade): ?>
                    <div style="margin-top:14px;padding-top:14px;border-top:1px solid rgba(61,71,51,.07)">
                        <button onclick="selecionarPlano('<?= $slug ?>')"
                            class="w-100 btn-gold d-flex align-items-center justify-content-center gap-2 py-2" style="font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;
                         border-radius:999px;border:none;cursor:pointer">
                            Solicitar
                            <?= $cfg['label'] ?>
                            <?= icon('arrow-right', 11) ?>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <style>
            @media(max-width:640px) {
                .planos-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
        <?php endif; ?>

        <?php if ($plano === 'premium'): ?>
        <!-- Já está no Premium -->
        <div class="premium-top">
            <div style="font-size:32px;margin-bottom:12px">🏆</div>
            <div style="font-size:18px;font-weight:800;color:#fff;margin-bottom:6px">
                Você está no plano mais completo
            </div>
            <div style="font-size:13px;color:rgba(255,255,255,.55);line-height:1.6;max-width:380px;margin:0 auto">
                Aproveite todos os recursos Premium: destaque no mapa, fotos ilimitadas,
                gestão pela nossa equipe e suporte prioritário.
            </div>
        </div>

        <?php else: ?>
        <!-- Checkout Asaas -->
        <div class="upgrade-form-wrap" id="form-upgrade">
            <div class="upgrade-form-title">Assinar plano</div>
            <div class="upgrade-form-sub">
                Pagamento seguro via cartão de crédito. Cancele quando quiser.
            </div>

            <!-- Seleção de forma de pagamento -->
            <div style="margin-bottom:20px">
                <label class="olbl">Forma de pagamento</label>
                <div style="display:flex;gap:8px;flex-wrap:wrap">

                    <!--<label id="lbl-pix"-->
                    <!--       style="display:flex;align-items:center;gap:8px;padding:10px 16px;-->
                    <!--              border-radius:12px;border:1.5px solid rgba(61,71,51,.12);-->
                    <!--              cursor:pointer;transition:all .2s;font-size:13px;font-weight:600">-->
                    <!--  <input type="radio" name="billing_type" value="PIX"-->
                    <!--         onchange="selecionarBilling('PIX')"-->
                    <!--         style="accent-color:var(--gcb-green-dark)">-->
                    <!--  Pix-->
                    <!--</label>-->

                    <label id="lbl-credit" style="display:flex;align-items:center;gap:8px;padding:10px 16px;
                    border-radius:12px;border:1.5px solid rgba(61,71,51,.12);
                    cursor:pointer;transition:all .2s;font-size:13px;font-weight:600">
                        <input type="radio" name="billing_type" value="CREDIT_CARD"
                            onchange="selecionarBilling('CREDIT_CARD')" style="accent-color:var(--gcb-green-dark)">
                        Cartão de crédito
                    </label>

                </div>
            </div>

            <!-- Resumo do plano selecionado -->
            <div id="resumo-plano" style="background:var(--gcb-offwhite);border-radius:12px;padding:14px 16px;
              margin-bottom:20px;font-size:13px;color:var(--gcb-graphite);display:none">
                Plano: <strong id="resumo-nome"></strong> —
                <strong id="resumo-preco"></strong>/mês
            </div>

            <!-- Erro -->
            <div id="checkout-erro" style="display:none;background:#fef2f2;border:1px solid #fecaca;
              border-radius:12px;padding:12px 16px;font-size:13px;
              color:#c0392b;margin-bottom:16px"></div>

            <!-- Botão -->
            <button id="btn-assinar" onclick="iniciarCheckout()" disabled
                class="btn-gold w-100 d-flex align-items-center justify-content-center gap-2 py-3" style="font-size:12px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;
                 border-radius:999px;border:none;cursor:pointer;opacity:.5">
                <?= icon('trending-up', 14) ?> Assinar agora
            </button>

            <p style="font-size:11px;color:var(--gcb-warmgray);text-align:center;
            margin-top:10px;line-height:1.5">
                Você será redirecionado para o ambiente seguro do Asaas para concluir o pagamento.
            </p>
        </div>
        <?php endif; ?>

    </div><!-- /plano-wrap -->

    <footer style="text-align:center;padding:2rem 1rem;font-size:11px;color:var(--gcb-warmgray)">
        &copy;
        <?= date('Y') ?> Guia Campo Belo &mdash;
        <a href="/" style="color:var(--gcb-gold)">Voltar ao site</a>
    </footer>
    

    <script>
        let planoSelecionado = null;
        let billingSelecionado = null;

        const precos = {
            profissional: 'R$ 89',
            premium: 'R$ 159',
        };

        function selecionarPlano(slug) {
            planoSelecionado = slug;

            // Destaca card
            document.querySelectorAll('.plano-card.upgrade-target').forEach(c => {
                c.classList.remove('selecionado');
            });
            const card = document.getElementById('card-' + slug);
            if (card) card.classList.add('selecionado');

            // Atualiza resumo
            document.getElementById('resumo-nome').textContent = slug.charAt(0).toUpperCase() + slug.slice(1);
            document.getElementById('resumo-preco').textContent = precos[slug] ?? '';
            document.getElementById('resumo-plano').style.display = 'block';

            // Scroll para o form
            const form = document.getElementById('form-upgrade');
            if (form) form.scrollIntoView({ behavior: 'smooth', block: 'start' });

            atualizarBotao();
        }

        function selecionarBilling(tipo) {
            billingSelecionado = tipo;

            // Destaca label selecionado
            ['PIX', 'CREDIT_CARD'].forEach(t => {
                const key = t === 'PIX' ? 'pix' : 'credit';
                const lbl = document.getElementById('lbl-' + key);
                if (lbl) {
                    lbl.style.borderColor = t === tipo ? 'var(--gcb-gold)' : 'rgba(61,71,51,.12)';
                    lbl.style.background = t === tipo ? 'var(--gcb-gold-pale)' : '';
                }
            });

            atualizarBotao();
        }

        function atualizarBotao() {
            const btn = document.getElementById('btn-assinar');
            if (!btn) return;
            const ok = planoSelecionado && billingSelecionado;
            btn.disabled = !ok;
            btn.style.opacity = ok ? '1' : '.5';
            btn.style.cursor = ok ? 'pointer' : 'not-allowed';
        }

        async function iniciarCheckout() {
            if (!planoSelecionado || !billingSelecionado) return;

            const btn = document.getElementById('btn-assinar');
            const erro = document.getElementById('checkout-erro');
            btn.disabled = true;
            btn.textContent = 'Aguarde...';
            erro.style.display = 'none';

            try {
                const res = await fetch('/empresa/actions/assinar.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        _token: '<?= $csrf ?>',
                        plano: planoSelecionado,
                        billing_type: billingSelecionado,
                    }),
                });

                const data = await res.json();
                console.log('Resposta assinar:', data);

                if (!data.ok) {
                    erro.textContent = data.erro ?? 'Erro ao iniciar checkout.';
                    erro.style.display = 'block';
                    btn.disabled = false;
                    btn.textContent = 'Assinar agora';
                    return;
                }

                // Redireciona para o checkout do Asaas
                console.log('Resposta assinar:', data); // temporário
                if (data.redirectUrl) {
                    window.location.href = data.redirectUrl;
                } else {
                    const destino = <?= $veio_onboard ? 'true' : 'false' ?>;
                    window.location.href = destino
                        ? '/empresa/status.php'
                        : '/empresa/pagamento-confirmado.php';
                }

            } catch (e) {
                erro.textContent = 'Erro de conexão. Tente novamente.';
                erro.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Assinar agora';
            }
        }

        // Init — destaca o próximo plano por padrão
        <?php
            $plano_presel = null;
            if ($veio_onboard) {
                // Usa o plano escolhido no cadastro
                $plano_presel = $usuario['empresa_plan_intent'] ?? $usuario['plan_intent'] ?? null;
                if ($plano_presel === 'essencial') $plano_presel = null; // essencial não precisa de checkout
            } elseif ($proximo_plano) {
                $plano_presel = $proximo_plano;
            }
            ?>
            <?php if ($plano_presel): ?>
                selecionarPlano('<?= $plano_presel ?>');
        <?php endif; ?>
    </script>

</body>

</html>