<?php require_once __DIR__ . '/icons.php'; ?>

<footer class="bg-[#1d1d1b]">
  <div class="max-w-[1180px] mx-auto px-10">

    <!-- Top grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-14 py-16
                border-b border-white/[0.06]">

      <!-- Brand -->
      <div>
        <a href="/index.php" class="flex items-center gap-2.5 mb-5">
          <div class="w-9 h-11 bg-[#c9aa6b] flex flex-col items-center justify-center gap-px px-1 py-1.5 flex-shrink-0"
               style="border-radius:50% 50% 50% 50% / 60% 60% 40% 40%">
            <span class="text-[7px] font-black text-[#2a3022] leading-none whitespace-nowrap">Guia</span>
            <span class="text-[7px] font-black text-[#2a3022] leading-none whitespace-nowrap">CB&amp;</span>
            <span class="text-[7px] font-black text-[#2a3022] leading-none whitespace-nowrap">Reg.</span>
          </div>
          <div class="flex flex-col leading-tight">
            <strong class="text-[13px] font-black text-white">Guia Campo Belo</strong>
            <small class="text-[10px] font-medium text-[#c9aa6b] tracking-widest">&amp; Região</small>
          </div>
        </a>
        <p class="text-[13.5px] leading-relaxed text-white/[0.38] mb-6 max-w-[260px]">
          A curadoria definitiva do bom gosto no Campo Belo e bairros vizinhos.
          Para quem valoriza tempo e qualidade.
        </p>
        <div class="flex gap-2">
          <?php
          $socials = [
            ['icon' => 'instagram', 'label' => 'Instagram', 'href' => 'https://instagram.com/guiacampobeloeregiao'],
            ['icon' => 'youtube',   'label' => 'YouTube',   'href' => 'https://youtube.com/'],
            ['icon' => 'whatsapp',  'label' => 'WhatsApp',  'href' => 'https://wa.me/5511999999999'],
            ['icon' => 'mail',      'label' => 'E-mail',    'href' => 'mailto:contato@guiacampobelo.com.br'],
          ];
          foreach ($socials as $s): ?>
          <a href="<?= $s['href'] ?>" target="_blank" rel="noopener"
             class="w-[34px] h-[34px] rounded-full border border-white/10 flex items-center
                    justify-center text-white/45 hover:bg-[#c9aa6b] hover:border-[#c9aa6b]
                    hover:text-[#2a3022] transition-all duration-200"
             aria-label="<?= $s['label'] ?>">
            <?= icon($s['icon'], 15) ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Explorar -->
      <div>
        <h5 class="text-[10px] font-black tracking-[0.18em] uppercase text-white mb-5">Explorar</h5>
        <ul class="space-y-3">
          <?php foreach ([
            'Restaurantes'    => '/pages/restaurantes.php',
            'Cafés & Padarias'=> '/pages/cafes.php',
            'Compras'         => '/pages/compras.php',
            'Bem-estar'       => '/pages/bem-estar.php',
            'Lazer & Arte'    => '/pages/lazer.php',
            'Pet Friendly'    => '/pages/pet.php',
          ] as $label => $href): ?>
          <li>
            <a href="<?= $href ?>"
               class="text-[13.5px] text-white/40 hover:text-[#c9aa6b] transition-colors duration-200">
              <?= htmlspecialchars($label) ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Bairros -->
      <div>
        <h5 class="text-[10px] font-black tracking-[0.18em] uppercase text-white mb-5">Bairros</h5>
        <ul class="space-y-3">
          <?php foreach ([
            'Campo Belo'       => '/pages/bairro/campo-belo.php',
            'Brooklin'         => '/pages/bairro/brooklin.php',
            'Moema'            => '/pages/bairro/moema.php',
            'Alto da Boa Vista'=> '/pages/bairro/alto-da-boa-vista.php',
            'Jabaquara'        => '/pages/bairro/jabaquara.php',
          ] as $label => $href): ?>
          <li>
            <a href="<?= $href ?>"
               class="text-[13.5px] text-white/40 hover:text-[#c9aa6b] transition-colors duration-200">
              <?= htmlspecialchars($label) ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Institucional -->
      <div>
        <h5 class="text-[10px] font-black tracking-[0.18em] uppercase text-white mb-5">Institucional</h5>
        <ul class="space-y-3">
          <?php foreach ([
            'Sobre o Guia'           => '/pages/sobre.php',
            'Anuncie conosco'        => '/pages/anuncie.php',
            'Nossa Curadoria'        => '/pages/curadoria.php',
            'Contato'                => '/pages/contato.php',
            '@guiacampobeloeregiao'  => 'https://instagram.com/guiacampobeloeregiao',
          ] as $label => $href): ?>
          <li>
            <a href="<?= $href ?>"
               class="text-[13.5px] text-white/40 hover:text-[#c9aa6b] transition-colors duration-200">
              <?= htmlspecialchars($label) ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

    </div>

    <!-- Bottom bar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-2 py-6
                text-[11.5px] text-white/28">
      <span class="text-white">&copy; <?= date('Y') ?> Guia Campo Belo &amp; Região &mdash;
        <a href="/pages/privacidade.php" class="text-[#c9aa6b] hover:underline">Privacidade</a>
      </span>
      <span class="text-white">Desenvolvido por
        <a href="https://maiolidesign.com.br" target="_blank" rel="noopener"
           class="text-[#c9aa6b] hover:underline">Maioli Design</a>
      </span>
    </div>

  </div>
</footer>

<!-- Back to top -->
<button id="back-to-top"
        onclick="window.scrollTo({top:0,behavior:'smooth'})"
        class="fixed bottom-7 right-7 z-50 w-11 h-11 rounded-full bg-[#c9aa6b]
               hover:bg-[#ddc48a] text-[#2a3022] flex items-center justify-center
               shadow-[0_6px_20px_rgba(201,170,107,0.4)] opacity-0 pointer-events-none
               translate-y-2 transition-all duration-300"
        aria-label="Voltar ao topo">
  <?= icon('arrow-up', 18) ?>
</button>
