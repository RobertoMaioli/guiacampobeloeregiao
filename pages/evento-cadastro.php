<?php
/**
 * pages/evento-cadastro.php
 * Inscrição Guia Connect — Soft Opening
 */
session_start();
$flash    = $_SESSION['flash_evento'] ?? null;
unset($_SESSION['flash_evento']);

$page_title = 'Guia Connect — Soft Opening · Guia Campo Belo & Região';
$meta_desc  = 'Reserve sua vaga no Soft Opening do Guia Connect com experiência de cafés do Bares SP.';
$canonical  = 'https://guiacampobeloeregiao.com.br/pages/evento-cadastro.php';
$ASAAS_URL  = 'https://sandbox.asaas.com/c/bjhre1nsdqwojhlw';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <?php include __DIR__ . '/../includes/head.php'; ?>
  <style>
    body { background: var(--gcb-offwhite); }

    /* ── HERO ── */
    .ev-hero {
      background: var(--gcb-green-dark);
      padding: 80px 0 64px;
      position: relative;
      overflow: hidden;
    }
    .ev-hero::before {
      content: '';
      position: absolute; inset: 0;
      background-image:
        linear-gradient(rgba(201,170,107,.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(201,170,107,.04) 1px, transparent 1px);
      background-size: 48px 48px;
      pointer-events: none;
    }
    .ev-hero::after {
      content: '';
      position: absolute; top: -80px; right: -80px;
      width: 400px; height: 400px; border-radius: 50%;
      background: radial-gradient(circle, rgba(201,170,107,.07) 0%, transparent 70%);
      pointer-events: none;
    }
    .ev-hero .container { position: relative; z-index: 1; }

    .ev-tag {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 4px 12px; border-radius: 999px;
      border: 1px solid rgba(201,170,107,.35);
      background: rgba(201,170,107,.1);
      font-size: 10px; font-weight: 800; letter-spacing: .2em;
      text-transform: uppercase; color: var(--gcb-gold-light);
      margin-bottom: 16px;
    }
    .ev-hero h1 {
      font-size: clamp(30px, 5vw, 52px);
      font-weight: 800; color: #fff; line-height: 1.1; margin-bottom: 6px;
    }
    .ev-hero h1 em { font-style: italic; font-weight: 300; color: var(--gcb-gold-light); }
    .ev-eyebrow {
      font-size: 11px; font-weight: 300; color: rgba(255,255,255,.45);
      letter-spacing: .14em; text-transform: uppercase; margin-bottom: 10px;
    }
    .ev-desc {
      font-size: 14px; font-weight: 300; color: rgba(255,255,255,.55);
      max-width: 480px; line-height: 1.7; margin-top: 14px;
    }
    .ev-meta { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 24px; }
    .ev-meta-item {
      display: flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.1);
      border-radius: 10px; padding: 8px 14px;
      font-size: 12px; font-weight: 600; color: rgba(255,255,255,.82);
    }
    .ev-meta-item svg { color: var(--gcb-gold); flex-shrink: 0; }
    .vagas-badge {
      display: inline-flex; align-items: center; gap: 7px;
      background: rgba(201,170,107,.14); border: 1px solid rgba(201,170,107,.28);
      border-radius: 999px; padding: 5px 13px;
      font-size: 10px; font-weight: 700; color: var(--gcb-gold-light);
      margin-top: 18px;
    }
    .vdot {
      width: 6px; height: 6px; border-radius: 50%; background: #f59e0b;
      animation: pdot 1.4s ease-in-out infinite;
    }
    @keyframes pdot { 0%,100%{opacity:1} 50%{opacity:.3} }

    /* ── LAYOUT PRINCIPAL ── */
    .ev-main { padding: 56px 0 80px; }

    /* ── CARD BENEFÍCIOS ── */
    .card-beneficios {
      background: var(--gcb-green-dark);
      border-radius: 20px; padding: 28px 24px; color: #fff;
    }
    .card-beneficios h3 {
      font-size: 15px; font-weight: 800;
      color: var(--gcb-gold-light); margin-bottom: 16px;
    }
    .b-item {
      display: flex; align-items: flex-start; gap: 10px;
      padding: 9px 0; border-bottom: 1px solid rgba(255,255,255,.07);
      font-size: 13px; line-height: 1.5; color: rgba(255,255,255,.82);
    }
    .b-item:last-child { border-bottom: none; }
    .b-check {
      width: 18px; height: 18px; border-radius: 50%;
      background: rgba(201,170,107,.14); border: 1px solid rgba(201,170,107,.28);
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0; margin-top: 2px;
    }
    .card-preco {
      background: var(--gcb-gold-pale); border-radius: 14px;
      padding: 18px 20px; margin-top: 20px; text-align: center;
    }
    .preco-label {
      font-size: 9px; font-weight: 800; letter-spacing: .18em;
      text-transform: uppercase; color: var(--gcb-warmgray); margin-bottom: 4px;
    }
    .preco-valor { font-size: 32px; font-weight: 800; color: var(--gcb-green-dark); line-height: 1; }
    .preco-obs   { font-size: 10px; color: var(--gcb-warmgray); margin-top: 3px; }

    /* ── CARD FORMULÁRIO ── */
    .card-form {
      background: #fff; border-radius: 20px; padding: 32px 28px;
      box-shadow: 0 4px 28px rgba(29,29,27,.07);
    }
    .card-form h2 { font-size: 18px; font-weight: 800; color: var(--gcb-green-dark); margin-bottom: 4px; }
    .form-sub { font-size: 12px; font-weight: 300; color: var(--gcb-warmgray); margin-bottom: 24px; line-height: 1.55; }

    .form-label {
      font-size: 10px; font-weight: 700; letter-spacing: .08em;
      text-transform: uppercase; color: var(--gcb-green-dark); margin-bottom: 5px;
    }
    .form-control {
      border: 1.5px solid rgba(61,71,51,.14); border-radius: 9px;
      font-family: 'Montserrat', sans-serif; font-size: 13px;
      padding: 9px 12px; background: var(--gcb-cream);
      transition: border-color .18s, box-shadow .18s;
    }
    .form-control:focus {
      border-color: var(--gcb-green);
      box-shadow: 0 0 0 3px rgba(61,71,51,.07);
      background: #fff;
    }
    .form-control.is-invalid { border-color: #ef4444 !important; }

    /* ── TOGGLE CPF/CNPJ ── */
    .doc-toggle {
      display: flex; background: var(--gcb-offwhite);
      border-radius: 8px; padding: 3px; gap: 3px; margin-bottom: 6px;
    }
    .doc-btn {
      flex: 1; padding: 7px 10px; border: none; border-radius: 6px;
      font-family: 'Montserrat', sans-serif; font-size: 11px; font-weight: 700;
      cursor: pointer; transition: background .15s, color .15s, box-shadow .15s;
      background: transparent; color: var(--gcb-warmgray);
    }
    .doc-btn.active {
      background: #fff; color: var(--gcb-green-dark);
      box-shadow: 0 1px 4px rgba(29,29,27,.1);
    }

    /* ── AVISO GUIA ── */
    .guia-notice {
      background: var(--gcb-gold-pale); border: 1px solid rgba(201,170,107,.28);
      border-radius: 10px; padding: 12px 14px;
      display: flex; align-items: flex-start; gap: 9px;
      font-size: 12px; color: var(--gcb-graphite); line-height: 1.6;
      margin-top: 16px;
    }
    .guia-notice strong { color: var(--gcb-green-dark); }

    /* ── RESUMO + BOTÃO ── */
    .checkout-resumo {
      background: var(--gcb-gold-pale); border-radius: 10px;
      padding: 14px 16px; margin: 22px 0 14px;
      display: flex; align-items: center; justify-content: space-between; gap: 12px;
    }
    .checkout-resumo-info p:first-child { font-size: 13px; font-weight: 600; color: var(--gcb-green-dark); margin: 0; }
    .checkout-resumo-info p:last-child  { font-size: 10px; font-weight: 300; color: var(--gcb-warmgray); margin: 3px 0 0; }
    .checkout-resumo-valor { font-size: 22px; font-weight: 800; color: var(--gcb-green-dark); white-space: nowrap; }

    .btn-pagar {
      width: 100%; padding: 15px 24px;
      background: var(--gcb-green-dark); color: #fff;
      border: none; border-radius: 12px;
      font-family: 'Montserrat', sans-serif;
      font-size: 13px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
      cursor: pointer; transition: background .18s, transform .15s;
      display: flex; align-items: center; justify-content: center; gap: 10px;
    }
    .btn-pagar:hover   { background: var(--gcb-green); transform: translateY(-1px); }
    .btn-pagar:active  { transform: translateY(0); }
    .btn-pagar:disabled { opacity: .55; cursor: not-allowed; transform: none; }

    /* ── SELOS ── */
    .seg-row {
      display: flex; align-items: center; justify-content: center;
      gap: 16px; flex-wrap: wrap; margin-top: 12px;
    }
    .seg-item {
      display: flex; align-items: center; gap: 5px;
      font-size: 10.5px; font-weight: 600; color: var(--gcb-warmgray);
    }

    /* ── FLASH ── */
    .ev-flash {
      border-radius: 10px; padding: 12px 16px; font-size: 13px; font-weight: 600;
      margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
    }
    .ev-flash.ok   { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .ev-flash.erro { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

    @keyframes spin { to { transform: rotate(360deg); } }
    @media (max-width: 768px) {
      .card-form        { padding: 24px 18px; }
      .card-beneficios  { padding: 22px 18px; }
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/../includes/search-modal.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- HERO -->
<section class="ev-hero">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <div class="ev-tag">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          Soft Opening · Evento Exclusivo
        </div>
        <p class="ev-eyebrow">Guia Campo Belo &amp; Região</p>
        <h1>Guia <em>Connect</em></h1>
        <p style="font-size:12px;font-weight:600;color:rgba(255,255,255,.45);letter-spacing:.1em;text-transform:uppercase;margin-top:6px">
          Com experiência de cafés do Bares SP
        </p>
        <p class="ev-desc">
          O primeiro encontro da comunidade do Guia Campo Belo. Uma noite com os melhores cafés do Bares SP, networking e muito sabor.
        </p>
        <div class="ev-meta">
          <div class="ev-meta-item">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            19 de maio de 2025
          </div>
          <div class="ev-meta-item">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            A partir das 19h
          </div>
          <div class="ev-meta-item">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            Cris Parilla — Rua República do Iraque, 1326
          </div>
        </div>
        <div class="vagas-badge">
          <span class="vdot"></span>
          Vagas limitadas
        </div>
      </div>
    </div>
  </div>
</section>


<!-- CONTEÚDO PRINCIPAL -->
<section class="ev-main">
  <div class="container">
    <div class="row g-4 align-items-start">

      <!-- Coluna esquerda: benefícios -->
      <div class="col-lg-4">
        <div class="card-beneficios">
          <h3>O que esperar</h3>
          <div class="b-item">
            <div class="b-check"><svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="var(--gcb-gold)" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg></div>
            Networking de verdade com empresários e líderes locais
          </div>
          <div class="b-item">
            <div class="b-check"><svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="var(--gcb-gold)" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg></div>
            Estrutura e dinâmica diferenciada para focar em resultados
          </div>
          <div class="b-item">
            <div class="b-check"><svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="var(--gcb-gold)" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg></div>
            Experiência de cafés selecionados realizado pelo BaresSP
          </div>
          <div class="b-item">
            <div class="b-check"><svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="var(--gcb-gold)" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg></div>
            Lançamentos e ações surpresas
          </div>
          
          <div class="card-preco">
            <p class="preco-label">Ingresso por pessoa</p>
            <p class="preco-valor">R$ 59</p>
            <p class="preco-obs">pagamento único · Pix ou Cartão de Crédito</p>
          </div>
        </div>
      </div>

      <!-- Coluna direita: formulário -->
      <div class="col-lg-8">
        <div class="card-form">
          <h2>Reserve sua vaga</h2>
          <p class="form-sub">Preencha seus dados e clique em <strong>Ir para o pagamento</strong>.</p>

          <?php if ($flash): ?>
          <div class="ev-flash <?= $flash['type'] === 'ok' ? 'ok' : 'erro' ?>">
            <?= htmlspecialchars($flash['msg']) ?>
          </div>
          <?php endif; ?>

          <form id="ev-form" novalidate>
            <input type="hidden" name="evento_id" value="guia-connect-soft-opening-mai25"/>

            <div class="row g-3">

              <!-- Nome -->
              <div class="col-sm-6">
                <label class="form-label">Nome completo <span style="color:#ef4444">*</span></label>
                <input type="text" class="form-control" id="nome" name="nome"
                       placeholder="Seu nome completo" required autocomplete="given-name"/>
              </div>

              <!-- WhatsApp -->
              <div class="col-sm-6">
                <label class="form-label">WhatsApp <span style="color:#ef4444">*</span></label>
                <input type="tel" class="form-control" id="whatsapp" name="whatsapp"
                       placeholder="(11) 9 0000-0000" required autocomplete="tel"/>
              </div>

              <!-- E-mail -->
              <div class="col-12">
                <label class="form-label">E-mail <span style="color:#ef4444">*</span></label>
                <input type="email" class="form-control" id="email" name="email"
                       placeholder="seu@email.com.br" required autocomplete="email"/>
              </div>

              <!-- Documento CPF/CNPJ -->
              <div class="col-sm-6">
                <label class="form-label">Documento <span style="color:#ef4444">*</span></label>
                <div class="doc-toggle">
                  <button type="button" class="doc-btn active" id="btn-cpf"  onclick="setDoc('cpf')">CPF</button>
                  <button type="button" class="doc-btn"        id="btn-cnpj" onclick="setDoc('cnpj')">CNPJ</button>
                </div>
                <input type="text" class="form-control" id="documento" name="documento"
                       placeholder="000.000.000-00" maxlength="14" required/>
                <input type="hidden" id="tipo_documento" name="tipo_documento" value="cpf"/>
              </div>

              <!-- Empresa -->
              <div class="col-sm-6">
                <label class="form-label">Nome da Empresa</label>
                <input type="text" class="form-control" id="empresa_nome" name="empresa_nome"
                       placeholder="Nome da sua empresa"/>
              </div>

            </div><!-- /row -->

            <!-- Aviso pré-cadastro silencioso -->
            <div class="guia-notice">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--gcb-gold)" stroke-width="2" stroke-linecap="round" style="flex-shrink:0;margin-top:2px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              <span>Ao se inscrever, você será <strong>pré-cadastrado gratuitamente no Guia Campo Belo</strong> e poderá publicar sua página após o evento.</span>
            </div>

            <!-- Resumo do ingresso -->
            <div class="checkout-resumo">
              <div class="checkout-resumo-info">
                <p>Guia Connect — Soft Opening</p>
                <p>19 de maio · Cris Parilla · Campo Belo</p>
              </div>
              <div class="checkout-resumo-valor">R$ 59</div>
            </div>

            <!-- Botão principal -->
            <button type="submit" class="btn-pagar" id="btn-pagar">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
              Ir para o pagamento
            </button>

            <!-- Selos de segurança -->
            <div class="seg-row">
              <div class="seg-item">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="var(--gcb-green)" stroke-width="2.5" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                SSL criptografado
              </div>
              <div class="seg-item">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="var(--gcb-green)" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                Pix · Cartão de Crédito
              </div>
              
            </div>

          </form>
        </div>
      </div>

    </div>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
const ASAAS_URL = '<?= $ASAAS_URL ?>';
let tipoDoc = 'cpf';

/* ── Toggle CPF / CNPJ ── */
function setDoc(tipo) {
  tipoDoc = tipo;
  document.getElementById('tipo_documento').value = tipo;
  document.getElementById('btn-cpf').classList.toggle('active',  tipo === 'cpf');
  document.getElementById('btn-cnpj').classList.toggle('active', tipo === 'cnpj');
  const inp = document.getElementById('documento');
  inp.value       = '';
  inp.placeholder = tipo === 'cpf' ? '000.000.000-00' : '00.000.000/0001-00';
  inp.maxLength   = tipo === 'cpf' ? 14 : 18;
  inp.classList.remove('is-invalid');
}

/* ── Máscara CPF / CNPJ ── */
document.getElementById('documento').addEventListener('input', function () {
  let v = this.value.replace(/\D/g, '');
  if (tipoDoc === 'cpf') {
    v = v.substring(0, 11);
    v = v.replace(/(\d{3})(\d)/, '$1.$2')
         .replace(/(\d{3})(\d)/, '$1.$2')
         .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
  } else {
    v = v.substring(0, 14);
    v = v.replace(/(\d{2})(\d)/,       '$1.$2')
         .replace(/(\d{3})(\d)/,       '$1.$2')
         .replace(/(\d{3})(\d)/,       '$1/$2')
         .replace(/(\d{4})(\d{1,2})$/, '$1-$2');
  }
  this.value = v;
});

/* ── Máscara WhatsApp ── */
document.getElementById('whatsapp').addEventListener('input', function () {
  let v = this.value.replace(/\D/g, '').substring(0, 11);
  if (v.length > 10)     v = v.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
  else if (v.length > 6) v = v.replace(/^(\d{2})(\d{4})(\d+)$/,   '($1) $2-$3');
  else if (v.length > 2) v = v.replace(/^(\d{2})(\d+)$/,           '($1) $2');
  this.value = v;
});

/* ── Limpa erro ao digitar ── */
document.querySelectorAll('.form-control').forEach(el =>
  el.addEventListener('input', () => el.classList.remove('is-invalid'))
);

/* ── Submit ── */
document.getElementById('ev-form').addEventListener('submit', async function (e) {
  e.preventDefault();

  /* Validação */
  const obrigatorios = ['nome', 'email', 'whatsapp', 'documento'];
  let valido = true;
  obrigatorios.forEach(id => {
    const el = document.getElementById(id);
    if (!el.value.trim()) { el.classList.add('is-invalid'); valido = false; }
  });

  /* Valida tamanho do documento */
  const docLimpo = document.getElementById('documento').value.replace(/\D/g, '');
  if (valido && tipoDoc === 'cpf'  && docLimpo.length !== 11) {
    document.getElementById('documento').classList.add('is-invalid'); valido = false;
  }
  if (valido && tipoDoc === 'cnpj' && docLimpo.length !== 14) {
    document.getElementById('documento').classList.add('is-invalid'); valido = false;
  }

  if (!valido) {
    document.querySelector('.is-invalid')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    return;
  }

  /* Loading no botão */
  const btn = document.getElementById('btn-pagar');
  btn.disabled = true;
  btn.innerHTML = `
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2.5" stroke-linecap="round"
         style="animation:spin .8s linear infinite">
      <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
    </svg>
    Processando…`;

  /* Pré-cadastro silencioso no Guia */
  try {
    await fetch('/empresa/actions/evento-inscricao.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        nome:           document.getElementById('nome').value.trim(),
        email:          document.getElementById('email').value.trim(),
        whatsapp:       document.getElementById('whatsapp').value.replace(/\D/g, ''),
        documento:      docLimpo,
        tipo_documento: tipoDoc,
        empresa_nome:   document.getElementById('empresa_nome').value.trim(),
        evento_id:      'guia-connect-soft-opening-mai25'
      })
    });
  } catch (_) { /* silencioso — não bloqueia o pagamento */ }

  /* Redireciona para o checkout Asaas */
  // Abre o Asaas em nova aba
    window.open(ASAAS_URL, '_blank');
    
    // Redireciona a página atual para o sucesso
    window.location.href = '/pages/evento-sucesso.php';
});
</script>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>

</body>
</html>