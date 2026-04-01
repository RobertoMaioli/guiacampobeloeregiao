<?php require_once __DIR__ . '/icons.php'; ?>

<header id="site-header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
  <div id="header-bar" class="flex items-center justify-between px-10 h-[72px] transition-all duration-300">

    <!-- Logo -->
    <a href="/index.php" class="items-center mt-5" aria-label="Guia Campo Belo">
      <img src="assets/img/logo.png" style="width:80px">
    </a>

    <!-- Nav -->
    <nav class="hidden lg:flex items-center gap-8" aria-label="Navegação principal">
      <?php
      $nav = [
        'Restaurantes' => '/pages/restaurantes.php',
        'Cafés'        => '/pages/cafes.php',
        'Serviços'     => '/pages/servicos.php',
        'Lazer'        => '/pages/lazer.php',
        'Mapa'         => '/pages/mapa.php',
      ];
      foreach ($nav as $label => $href): ?>
      <a href="<?= $href ?>"
         class="nav-link text-[11.5px] font-semibold tracking-widest uppercase text-white/80
                hover:text-white transition-colors duration-200 relative
                after:absolute after:left-0 after:-bottom-1 after:h-[1.5px] after:w-0
                after:bg-[#c9aa6b] after:transition-all after:duration-300 hover:after:w-full">
        <?= htmlspecialchars($label) ?>
      </a>
      <?php endforeach; ?>
    </nav>

    <!-- Actions -->
    <div class="flex items-center gap-2.5">

      <!-- Sticky search pill (appears on scroll) -->
      <div id="header-pill"
           class="flex items-center bg-white rounded-full border border-[#3d4733]/10 h-[42px]
                  overflow-hidden shadow-sm opacity-0 pointer-events-none max-w-0
                  transition-all duration-300"
           role="search">
        <input type="text" placeholder="Buscar em Campo Belo…"
               class="border-none outline-none bg-transparent font-[Montserrat] text-[13px]
                      text-[#1d1d1b] px-4 w-[200px] cursor-pointer"
               onclick="SearchModal.open()" readonly aria-label="Abrir busca" />
        <button onclick="SearchModal.open()"
                class="w-[42px] h-[42px] flex items-center justify-center
                       bg-[#c9aa6b] hover:bg-[#ddc48a] text-[#2a3022]
                       rounded-full -mr-px transition-colors duration-200 flex-shrink-0"
                aria-label="Buscar">
          <?= icon('search', 15) ?>
        </button>
      </div>

      <!-- Search icon (visible before scroll) -->
      <button id="header-search-icon" onclick="SearchModal.open()"
              class="w-[38px] h-[38px] rounded-full border border-white/30 flex items-center
                     justify-content text-white hover:bg-white/10 hover:border-white/60
                     transition-all duration-200 flex items-center justify-center"
              aria-label="Buscar">
        <?= icon('search', 17) ?>
      </button>

      <a href="/pages/anuncie.php"
         class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 bg-[#c9aa6b]
                hover:bg-[#ddc48a] text-[#2a3022] text-[11px] font-black tracking-widest
                uppercase rounded-full transition-all duration-200
                shadow-[0_6px_20px_rgba(201,170,107,0.3)] hover:-translate-y-px">
        Anuncie
      </a>

      <!-- Mobile menu btn -->
      <button id="mobile-menu-btn"
              class="lg:hidden w-[38px] h-[38px] rounded-full border border-white/30
                     flex items-center justify-center text-white hover:bg-white/10
                     transition-all duration-200" aria-label="Menu">
        <?= icon('menu', 18) ?>
      </button>
    </div>
  </div>

  <!-- Mobile nav -->
  <div id="mobile-nav"
       class="hidden lg:hidden bg-[#faf8f3]/97 backdrop-blur-xl border-t border-[#3d4733]/10 px-6 py-4">
    <?php foreach ($nav as $label => $href): ?>
    <a href="<?= $href ?>"
       class="flex items-center gap-3 py-3 text-[12px] font-semibold tracking-widest
              uppercase text-[#1d1d1b] hover:text-[#3d4733] border-b border-[#3d4733]/06
              transition-colors duration-200">
      <?= htmlspecialchars($label) ?>
    </a>
    <?php endforeach; ?>
  </div>
</header>
