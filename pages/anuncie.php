<?php
require_once __DIR__ . '/../core/UserAuth.php';
require_once __DIR__ . '/../includes/icons.php';
UserAuth::start(); // deve rodar antes de qualquer output HTML
$page_title = 'Anuncie no Guia Campo Belo — Apareça para quem importa';
$meta_desc  = 'Coloque seu negócio no radar de quem mora e frequenta Campo Belo. Planos a partir de R$0.';
$canonical  = 'https://guiacampobeloeregiao.com.br';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><?php include __DIR__ . '/../includes/head.php'; ?>
<style>
  .plan-price-mensal { display:block; }
  .plan-price-anual  { display:none; }
  body.anual .plan-price-mensal { display:none; }
  body.anual .plan-price-anual  { display:block; }
  .faq-body { display:none; }
  .faq-body.open { display:block; }
  .faq-icon { transition:transform .25s ease; }
  .faq-open .faq-icon { transform:rotate(180deg); }
  .plan-card { background:#fff; border-radius:24px; overflow:hidden; border:1px solid rgba(61,71,51,.07); box-shadow:0 2px 12px rgba(29,29,27,.07); display:flex; flex-direction:column; }
  .plan-card.featured { border-color:rgba(201,170,107,.4); box-shadow:0 20px 60px rgba(42,48,34,.25); }
  .step-circle { width:88px;height:88px;border-radius:50%;background:var(--gcb-green-dark);display:flex;align-items:center;justify-content:center;color:var(--gcb-gold);position:relative;box-shadow:0 8px 24px rgba(61,71,51,.18) }
  .step-num { position:absolute;top:-4px;right:-4px;width:28px;height:28px;border-radius:50%;background:var(--gcb-gold);display:flex;align-items:center;justify-content:center;color:var(--gcb-green-dark);font-size:10px;font-weight:800 }
</style>
</head>
<body>

<?php include __DIR__ . '/../includes/search-modal.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- HERO -->
<section style="background:var(--gcb-green-dark);padding-top:72px;position:relative;overflow:hidden">
  <div class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none"
       style="background:radial-gradient(ellipse 70% 80% at 100% 50%,rgba(201,170,107,.1) 0%,transparent 65%),
              radial-gradient(ellipse 50% 60% at 0% 80%,rgba(79,92,64,.4) 0%,transparent 60%)"></div>
  <div class="container py-5 py-lg-5 position-relative">
    <div class="row align-items-center g-5">
      <div class="col-12 col-lg-6">
        <h1 class="font-display fw-bold text-white mb-4" style="font-size:clamp(34px,5vw,60px);line-height:1.1">
          Apareça para<br/>quem <em class="fst-italic text-gold">importa.</em>
        </h1>
        <p style="font-size:15.5px;font-weight:300;color:rgba(255,255,255,.55);line-height:1.8;max-width:480px" class="mb-5">
          O Guia Campo Belo é a curadoria definitiva do bairro de maior IDH da Zona Sul.
          Coloque seu negócio na frente de um público qualificado, exigente e fiel.
        </p>
        <!-- Stats -->
        <div class="row g-0 rounded-3 overflow-hidden mb-5" style="background:rgba(255,255,255,.06)">
          <?php foreach ([['380+','Negócios listados'],['12','Categorias'],['0,935','IDH do bairro']] as $s): ?>
          <div class="col-4 text-center px-3 py-4 border-end" style="border-color:rgba(255,255,255,.08)!important">
            <div class="font-display fw-bold text-white" style="font-size:26px;line-height:1"><?= $s[0] ?></div>
            <div style="font-size:10px;font-weight:600;color:rgba(255,255,255,.35);margin-top:4px;line-height:1.3"><?= $s[1] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="d-flex flex-wrap gap-3">
          <a href="#planos" class="btn-gold d-inline-flex align-items-center gap-2">
            Ver planos <?= icon('arrow-right',13) ?>
          </a>
          <a href="https://wa.me/5511999999999" target="_blank" class="btn d-inline-flex align-items-center gap-2" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.2);color:#fff;border-radius:999px;font-size:12px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;padding:10px 24px">
            Falar conosco
          </a>
        </div>
      </div>

      <!-- Mock card -->
      <div class="col-12 col-lg-6 d-none d-lg-block position-relative">
        <div class="bg-white rounded-20 p-4 shadow" style="box-shadow:0 32px 80px rgba(0,0,0,.3)!important">
          <div class="rounded-3 overflow-hidden mb-4 position-relative" style="height:180px">
            <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=600&q=80" class="w-100 h-100 object-fit-cover" alt="Preview"/>
            <div class="position-absolute top-0 start-0 m-3"><span class="badge-gold">Destaque</span></div>
          </div>
          <p class="card-cat">Italiana · Contemporânea</p>
          <h3 class="card-title">Seu Negócio Aqui</h3>
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="stars">★★★★★</span>
            <span class="fw-bold" style="font-size:13px">5.0</span>
            <span style="font-size:12px;color:var(--gcb-warmgray)">(124 avaliações)</span>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <span class="tag-pill">Campo Belo</span>
            <span class="tag-pill">Reserva</span>
            <span class="tag-pill">Wi-Fi</span>
          </div>
        </div>
        <div class="position-absolute bg-gold rounded-3 px-3 py-2 shadow-gold" style="top:-16px;right:-16px;transform:rotate(3deg)">
          <p style="font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--gcb-green-dark)" class="mb-0">Visibilidade</p>
          <p class="font-display fw-bold mb-0" style="font-size:20px;color:var(--gcb-green-dark);line-height:1.2">Premium</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- BENEFÍCIOS -->
<section class="py-5 bg-offwhite">
  <div class="container">
    <div class="text-center mb-5">
      <span class="eyebrow">Por que anunciar</span>
      <h2 class="font-display fw-bold mb-0" style="color:var(--gcb-green-dark);font-size:clamp(26px,3.5vw,42px)">
        Um público <em class="fst-italic text-gold">diferente</em>
      </h2>
    </div>
    <div class="row g-4">
      <?php foreach ([
        ['icon'=>'trending-up','titulo'=>'Visibilidade segmentada','desc'=>'Apareça exatamente para quem busca o que você oferece — por categoria, bairro e palavras-chave.'],
        ['icon'=>'star',       'titulo'=>'Avaliações integradas',  'desc'=>'Conecte suas avaliações do Google e TripAdvisor. Construa reputação com quem já te conhece.'],
        ['icon'=>'map',        'titulo'=>'Destaque no mapa',       'desc'=>'Pin exclusivo no mapa interativo do Guia. Seja encontrado por quem está explorando a região agora.'],
        ['icon'=>'grid',       'titulo'=>'Galeria de fotos',       'desc'=>'Mostre seu espaço, seus produtos e sua equipe. Uma imagem vale mais que mil palavras.'],
        ['icon'=>'phone',      'titulo'=>'Contato direto',         'desc'=>'Botão de ligar, WhatsApp e e-mail direto na sua página. Zero atrito entre o cliente e você.'],
        ['icon'=>'award',      'titulo'=>'Selo de curadoria',      'desc'=>'Nosso time revisa cada cadastro. O selo "Curadoria Guia" comunica qualidade ao seu cliente.'],
      ] as $b): ?>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="bg-white rounded-20 p-4 shadow-card reveal h-100">
          <div class="rounded-3 d-flex align-items-center justify-content-center mb-4" style="width:48px;height:48px;background:var(--gcb-gold-pale);color:var(--gcb-green)">
            <?= icon($b['icon'],22) ?>
          </div>
          <h3 class="font-display fw-bold mb-2" style="color:var(--gcb-green-dark);font-size:17px"><?= htmlspecialchars($b['titulo']) ?></h3>
          <p style="font-size:13.5px;font-weight:300;color:var(--gcb-warmgray);line-height:1.7" class="mb-0"><?= htmlspecialchars($b['desc']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- COMO FUNCIONA -->
<section class="py-5 bg-cream">
  <div class="container">
    <div class="text-center mb-5">
      <span class="eyebrow">Simples assim</span>
      <h2 class="font-display fw-bold mb-0" style="color:var(--gcb-green-dark);font-size:clamp(26px,3.5vw,42px)">
        Do cadastro ao <em class="fst-italic text-gold">destaque</em>
      </h2>
    </div>
    <div class="row g-4 justify-content-center text-center">
      <?php foreach ([
        ['num'=>'01','icon'=>'mail',        'titulo'=>'Entre em contato',  'desc'=>'Mande uma mensagem pelo WhatsApp ou e-mail. Respondemos em até 24h.'],
        ['num'=>'02','icon'=>'grid',        'titulo'=>'Monte seu perfil',  'desc'=>'Nossa equipe cadastra todas as informações, fotos e categorias do seu negócio.'],
        ['num'=>'03','icon'=>'trending-up', 'titulo'=>'Apareça e cresça',  'desc'=>'Seu negócio fica visível no mapa, na busca e nas categorias.'],
      ] as $s): ?>
      <div class="col-12 col-md-4 reveal">
        <div class="d-flex justify-content-center mb-4">
          <div class="step-circle">
            <?= icon($s['icon'],28) ?>
            <div class="step-num"><?= $s['num'] ?></div>
          </div>
        </div>
        <h3 class="font-display fw-bold mb-2" style="color:var(--gcb-green-dark);font-size:19px"><?= htmlspecialchars($s['titulo']) ?></h3>
        <p style="font-size:13.5px;font-weight:300;color:var(--gcb-warmgray);line-height:1.7;max-width:240px" class="mb-0 mx-auto"><?= htmlspecialchars($s['desc']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- PLANOS -->
<section class="py-5 bg-offwhite" id="planos">
  <div class="container">
    <div class="text-center mb-4">
      <span class="eyebrow">Planos</span>
      <h2 class="font-display fw-bold mb-0" style="color:var(--gcb-green-dark);font-size:clamp(26px,3.5vw,42px)">
        Escolha o que faz <em class="fst-italic text-gold">sentido</em>
      </h2>
    </div>
    <!-- Toggle -->
    <!--<div class="d-flex align-items-center justify-content-center gap-3 mb-5">-->
    <!--  <span id="lbl-mensal" class="fw-bold" style="font-size:13px;color:var(--gcb-green-dark)">Mensal</span>-->
    <!--  <button id="toggle-periodo" onclick="togglePeriodo()" class="position-relative border-0 rounded-pill" style="width:48px;height:24px;background:var(--gcb-green-dark);transition:background .2s">-->
    <!--    <span id="toggle-knob" class="position-absolute top-50 translate-middle-y rounded-circle" style="width:20px;height:20px;background:var(--gcb-gold);left:2px;transition:left .2s"></span>-->
    <!--  </button>-->
    <!--  <span id="lbl-anual" style="font-size:13px;font-weight:600;color:var(--gcb-warmgray)">-->
    <!--    Anual <span class="badge-gold ms-1">-20%</span>-->
    <!--  </span>-->
    <!--</div>-->

    <div class="row g-4 justify-content-center">
      <?php
      $planos = [
        ['nome'=>'Essencial','slug'=>'essencial','desc'=>'Para começar a aparecer','preco_m'=>'Grátis','preco_a'=>'Grátis','sfx_m'=>'','sfx_a'=>'',
         'featured'=>false,'cta'=>'Anunciar agora','features'=>[
          [true,'Página da empresa'],[true,'Nome, endereço e contato'],[true,'Horários de funcionamento'],[true,'Descrição da Empresa'],[false,'Imagem de Capa'], [false,'Até 3 tags'],
          [false,'Destaque nas buscas'],[false,'Pin no mapa interativo'],[false,'Galeria de imagens ilimitada'],[false,'Sincronização Google'],
        ]],
        ['nome'=>'Profissional','slug'=>'profissional','desc'=>'Para quem quer ser encontrado','preco_m'=>'R$ 89','preco_a'=>'R$ 71','sfx_m'=>'/mês','sfx_a'=>'/mês',
         'featured'=>true,'cta'=>'Anunciar agora','features'=>[
          [true,'Tudo do Essencial'],[true,'Imagem de Capa'],[true,'5 Imagens na galeria'],[true,'Até 5 tags'],[true,'Pin no mapa interativo'],
          [true,'Sincronização Google Reviews'],[true,'Link do Site'],[true,'Botão WhatsApp direto'],[true,'Links de redes sociais'],[true,'Formulário de Contato'],
          [false,'Selo Destaque na listagem']
        ]],
        ['nome'=>'Premium','slug'=>'premium','desc'=>'Para líderes de categoria','preco_m'=>'','preco_a'=>'R$ 151','sfx_m'=>'','sfx_a'=>'',
         'featured'=>false,'cta'=>'Anunciar agora','features'=>[
          [true,'Tudo do Profissional'],[true,'Imagens ilimitadas'],[true,'Tags Ilimitadas'],[true,'Selo "Destaque" na listagem'],
          [true,'Sem Propagandas'],
          [true,'Gestão pela nossa equipe'],[true,'Suporte prioritário']
        ]],
      ];
      foreach ($planos as $p):
          if ($p['slug'] !== 'premium') continue;
        ?>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="plan-card h-100 <?= $p['featured']?'featured':'' ?>">
          <?php if ($p['featured']): ?>
          <div class="position-relative">
            <span class="badge-gold position-absolute top-0 end-0 m-4">Mais popular</span>
          </div>
          <?php endif; ?>
          <div class="p-4 pb-3" style="border-bottom:1px solid <?= $p['featured']?'rgba(255,255,255,.1)':'rgba(61,71,51,.07)' ?>;background:<?= $p['featured']?'var(--gcb-green-dark)':'#fff' ?>">
            <h3 class="font-display fw-bold mb-1" style="color:<?= $p['featured']?'#fff':'var(--gcb-green-dark)' ?>;font-size:22px"><?= $p['nome'] ?></h3>
            <p class="mb-4" style="font-size:13px;color:<?= $p['featured']?'rgba(255,255,255,.45)':'var(--gcb-warmgray)' ?>"><?= $p['desc'] ?></p>
            <div class="plan-price-mensal">
              <div class="d-flex align-items-end gap-1">
                <span class="font-display fw-bold" style="font-size:42px;line-height:1;color:<?= $p['featured']?'#fff':'var(--gcb-green-dark)' ?>"><?= $p['preco_m'] ?></span>
                <?php if ($p['sfx_m']): ?><span style="font-size:13px;font-weight:600;padding-bottom:6px;color:<?= $p['featured']?'rgba(255,255,255,.4)':'var(--gcb-warmgray)' ?>"><?= $p['sfx_m'] ?></span><?php endif; ?>
              </div>
            </div>
            <div class="plan-price-anual">
              <div class="d-flex align-items-end gap-1">
                <span class="font-display fw-bold" style="font-size:42px;line-height:1;color:<?= $p['featured']?'#fff':'var(--gcb-green-dark)' ?>"><?= $p['preco_a'] ?></span>
                <?php if ($p['sfx_a']): ?><span style="font-size:13px;font-weight:600;padding-bottom:6px;color:<?= $p['featured']?'rgba(255,255,255,.4)':'var(--gcb-warmgray)' ?>"><?= $p['sfx_a'] ?></span><?php endif; ?>
              </div>
              <?php if ($p['preco_a']!=='Grátis'): ?><p style="font-size:11px;color:<?= $p['featured']?'rgba(255,255,255,.35)':'var(--gcb-warmgray)' ?>" class="mt-1 mb-0">cobrado anualmente</p><?php endif; ?>
            </div>
          </div>
          <div class="p-4 flex-fill" style="background:<?= $p['featured']?'var(--gcb-green-dark)':'#fff' ?>">
            <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
              <?php foreach ($p['features'] as $f): ?>
              <li class="d-flex align-items-start gap-2" style="font-size:13px;color:<?= $p['featured']?($f[0]?'rgba(255,255,255,.8)':'rgba(255,255,255,.25)'):($f[0]?'var(--gcb-graphite)':'rgba(139,133,137,.4)') ?>">
                <?php if ($f[0]): ?>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="<?= $p['featured']?'#c9aa6b':'#3d4733' ?>" stroke-width="2.5" stroke-linecap="round" class="flex-shrink-0 mt-1"><polyline points="20 6 9 17 4 12"/></svg>
                <?php else: ?>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="flex-shrink-0 mt-1 opacity-50"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                <?php endif; ?>
                <?= htmlspecialchars($f[1]) ?>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div class="p-4" style="background:<?= $p['featured']?'var(--gcb-green-dark)':'#fff' ?>">
            <a href="/empresa/cadastro.php?plan=<?= $p['slug'] ?>"
               class="d-flex align-items-center justify-content-center gap-2 py-3 rounded-pill text-decoration-none w-100"
               style="font-size:12px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;
                      <?= $p['featured']?'background:var(--gcb-gold);color:var(--gcb-green-dark);box-shadow:0 8px 24px rgba(201,170,107,.3)':'background:var(--gcb-green-dark);color:#fff' ?>">
              <?= htmlspecialchars($p['cta']) ?> <?= icon('arrow-right',13) ?>
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="text-center mt-4" style="font-size:12.5px;color:var(--gcb-warmgray)">
      Todos os planos incluem suporte via WhatsApp. Não há fidelidade mínima.
    </p>
  </div>
</section>

<!-- FAQ -->
<section class="py-5 bg-cream">
  <div class="container" style="max-width:760px">
    <div class="text-center mb-5">
      <span class="eyebrow">Dúvidas</span>
      <h2 class="font-display fw-bold mb-0" style="color:var(--gcb-green-dark);font-size:clamp(26px,3.5vw,38px)">
        Perguntas <em class="fst-italic text-gold">frequentes</em>
      </h2>
    </div>
    <?php foreach ([
      ['Preciso ter CNPJ para anunciar?','Não. Aceitamos MEI, autônomos, prestadores de serviço e profissionais liberais. Qualquer negócio que atue em Campo Belo e região pode anunciar.'],
      ['Como funciona o plano gratuito?','O plano Essencial é 100% gratuito e permanente. Inclui perfil básico com nome, endereço, contato e até 3 fotos.'],
      ['Posso cancelar quando quiser?','Sim. Não há fidelidade mínima nos planos mensais. Você pode cancelar a qualquer momento sem multa.'],
      ['Quem cadastra as informações do meu negócio?','Nos planos Profissional e Premium, nossa equipe faz o cadastro completo por você.'],
      ['Como funcionam os destaques na busca?','Negócios nos planos Profissional e Premium aparecem antes dos demais nos resultados de busca.'],
      ['A integração com o Google Reviews é automática?','Sim. Com o Place ID do seu negócio, sincronizamos sua nota e avaliações automaticamente.'],
    ] as $f): ?>
    <div class="border-bottom py-1" style="border-color:rgba(61,71,51,.07)!important">
      <button class="faq-btn d-flex align-items-center justify-content-between gap-4 w-100 bg-transparent border-0 py-4 text-start"
              onclick="toggleFaq(this)">
        <span style="font-size:15px;font-weight:600;color:var(--gcb-graphite);line-height:1.4"><?= htmlspecialchars($f[0]) ?></span>
        <span class="faq-icon rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:24px;height:24px;background:var(--gcb-offwhite);color:var(--gcb-warmgray)">
          <?= icon('chevron-down',14) ?>
        </span>
      </button>
      <div class="faq-body pb-4">
        <p style="font-size:14px;font-weight:300;color:var(--gcb-warmgray);line-height:1.75" class="mb-0"><?= htmlspecialchars($f[1]) ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
let isAnual = false;
function togglePeriodo() {
  isAnual = !isAnual;
  document.body.classList.toggle('anual', isAnual);
  document.getElementById('toggle-knob').style.left = isAnual ? '26px' : '2px';
  document.getElementById('toggle-periodo').style.background = isAnual ? 'var(--gcb-gold)' : 'var(--gcb-green-dark)';
  document.getElementById('lbl-mensal').style.color = isAnual ? 'var(--gcb-warmgray)' : 'var(--gcb-green-dark)';
  document.getElementById('lbl-anual').style.color  = isAnual ? 'var(--gcb-green-dark)' : 'var(--gcb-warmgray)';
}
function toggleFaq(btn) {
  const item = btn.parentElement;
  const body = item.querySelector('.faq-body');
  const isOpen = body.classList.contains('open');
  document.querySelectorAll('.faq-body').forEach(b => b.classList.remove('open'));
  document.querySelectorAll('.faq-btn').forEach(b => b.classList.remove('faq-open'));
  if (!isOpen) { body.classList.add('open'); btn.classList.add('faq-open'); }
}
</script>
</body>
</html>