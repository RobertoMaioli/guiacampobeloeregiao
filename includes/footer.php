<?php require_once __DIR__ . '/icons.php'; ?>

<footer class="gcb-footer pt-5 pb-0">
  <div class="container">
    <div class="row g-5 py-5 border-bottom border-white border-opacity-10">

      <!-- Brand -->
      <div class="col-12 col-sm-6 col-lg-3">
        <a href="/index.php" class="d-flex align-items-center gap-3 mb-4 text-decoration-none">
          <img src="/assets/img/logo.png" alt="Guia Campo Belo" style="height:48px">
        </a>
        <p style="font-size:13.5px;line-height:1.8;color:rgba(250,248,243,.38);max-width:260px">
          A curadoria definitiva do bom gosto no Campo Belo e bairros vizinhos.
          Para quem valoriza tempo e qualidade.
        </p>
        
      </div>

      <!-- Explorar -->
      <div class="col-6 col-lg-2 offset-lg-1">
        <p class="footer-nav-title">Explorar</p>
        <?php foreach ([
          'Restaurantes'    => '/pages/categoria.php?slug=restaurantes',
          'Cafés & Padarias'=> '/pages/categoria.php?slug=cafes',
          'Compras'         => '/pages/categoria.php?slug=compras',
          'Bem-estar'       => '/pages/categoria.php?slug=bem-estar',
          'Lazer & Arte'    => '/pages/categoria.php?slug=lazer',
          'Pet Friendly'    => '/pages/categoria.php?slug=pet',
        ] as $label => $href): ?>
        <a href="<?= $href ?>" class="footer-nav-link"><?= htmlspecialchars($label) ?></a>
        <?php endforeach; ?>
      </div>

      <!-- Bairros -->
      <div class="col-6 col-lg-2">
        <p class="footer-nav-title">Bairros</p>
        <?php foreach ([
          'Campo Belo'        => '#',
          'Brooklin'          => '#',
          'Moema'             => '#',
          'Alto da Boa Vista' => '#',
          'Jabaquara'         => '#',
        ] as $label => $href): ?>
        <a href="<?= $href ?>" class="footer-nav-link"><?= htmlspecialchars($label) ?></a>
        <?php endforeach; ?>
      </div>

      <!-- Institucional -->
      <div class="col-6 col-lg-2">
        <p class="footer-nav-title">Institucional</p>
        <?php foreach ([
          'Sobre o Guia'          => '#',
          'Anuncie conosco'       => '/pages/anuncie.php',
          'Nossa Curadoria'       => '#',
          'Contato'               => '#',
          '@guiacampobeloeregiao' => 'https://instagram.com/guiacampobeloeregiao',
        ] as $label => $href): ?>
        <a href="<?= $href ?>" class="footer-nav-link"><?= htmlspecialchars($label) ?></a>
        <?php endforeach; ?>
      </div>

    </div>

    <!-- Bottom bar -->
    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between
                gap-2 py-4" style="font-size:11.5px;color:rgba(250,248,243,.28)">
      <span>&copy; <?= date('Y') ?> Guia Campo Belo &amp; Região &mdash;
        <a href="#" style="color:var(--gcb-gold)">Privacidade</a>
      </span>
      <span>Desenvolvido por
        <a href="https://maiolidesign.com.br" target="_blank" rel="noopener"
           style="color:var(--gcb-gold)">Maioli Design</a>
      </span>
    </div>
  </div>
</footer>

<!-- Back to top -->
<button id="back-to-top" aria-label="Voltar ao topo">
  <?= icon('arrow-up', 18) ?>
</button>

<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<!-- Guia Campo Belo JS -->
<script src="/assets/js/gcb.js?v1.0"></script>