<?php require_once __DIR__ . '/icons.php'; ?>

<div id="search-modal"
     class="fixed inset-0 z-[500] hidden items-start justify-center pt-20 px-4"
     role="dialog" aria-modal="true" aria-label="Busca">

  <!-- Backdrop -->
  <div id="modal-backdrop"
       class="absolute inset-0 bg-[#1d1d1b]/72 backdrop-blur-[10px]"
       onclick="SearchModal.close()"></div>

  <!-- Modal box -->
  <div class="relative w-full max-w-[680px] bg-white rounded-3xl shadow-[0_32px_100px_rgba(0,0,0,0.3)]
              overflow-hidden animate-[modalIn_0.22s_ease]">

    <!-- Input row -->
    <div class="flex items-center gap-3.5 px-6 py-5 border-b border-[#f2f0eb]">
      <span class="text-[#c9aa6b] flex-shrink-0"><?= icon('search', 20) ?></span>
      <input type="text" id="modal-input"
             placeholder="Buscar restaurante, serviço, bairro…"
             autocomplete="off"
             class="flex-1 border-none outline-none text-base text-[#1d1d1b] placeholder-[#8b8589]
                    font-[Montserrat]"
             aria-label="Buscar" />
      <button onclick="SearchModal.close()"
              class="w-[30px] h-[30px] rounded-full bg-[#f2f0eb] hover:bg-[#f5edda]
                     hover:text-[#3d4733] text-[#8b8589] flex items-center justify-center
                     transition-all duration-200 flex-shrink-0"
              aria-label="Fechar">
        <?= icon('close', 14) ?>
      </button>
    </div>

    <div class="py-3 pb-4 max-h-[70vh] overflow-y-auto">

      <!-- Recent -->
      <p class="px-6 pt-1 pb-1 text-[9px] font-black tracking-[0.18em] uppercase text-[#c9aa6b]">
        Buscas recentes
      </p>
      <?php
      $recents = ['Brunch Campo Belo', 'Pet shop próximo'];
      foreach ($recents as $r): ?>
      <div class="modal-item flex items-center gap-3.5 px-6 py-2.5 cursor-pointer
                  hover:bg-[#f2f0eb] transition-colors duration-150"
           data-query="<?= htmlspecialchars($r) ?>">
        <div class="w-9 h-9 rounded-[10px] bg-[#f5edda] flex items-center justify-center
                    text-[#3d4733] flex-shrink-0">
          <?= icon('clock', 16) ?>
        </div>
        <div>
          <p class="text-sm font-semibold text-[#1d1d1b]"><?= htmlspecialchars($r) ?></p>
          <p class="text-[11px] text-[#8b8589] mt-px">Busca recente</p>
        </div>
      </div>
      <?php endforeach; ?>

      <div class="h-px bg-[#f2f0eb] my-2"></div>

      <!-- Categories -->
      <p class="px-6 pt-1 pb-2 text-[9px] font-black tracking-[0.18em] uppercase text-[#c9aa6b]">
        Categorias
      </p>
      <div class="flex flex-wrap gap-2 px-6 pb-2">
        <?php
        $cats = [
          ['icon' => 'utensils',    'label' => 'Restaurantes'],
          ['icon' => 'coffee',      'label' => 'Cafés'],
          ['icon' => 'utensils',    'label' => 'Japonesa'],
          ['icon' => 'paw',         'label' => 'Pet Friendly'],
          ['icon' => 'spa',         'label' => 'Bem-estar'],
          ['icon' => 'shopping-bag','label' => 'Compras'],
          ['icon' => 'wine',        'label' => 'Wine Bar'],
          ['icon' => 'dumbbell',    'label' => 'Academia'],
        ];
        foreach ($cats as $c): ?>
        <button class="modal-cat flex items-center gap-2 px-4 py-2 bg-[#f2f0eb]
                       hover:bg-[#f5edda] hover:border-[#c9aa6b] rounded-full text-[12px]
                       font-semibold text-[#3d4733] border border-transparent
                       transition-all duration-200"
                data-query="<?= htmlspecialchars($c['label']) ?>">
          <?= icon($c['icon'], 14) ?>
          <?= htmlspecialchars($c['label']) ?>
        </button>
        <?php endforeach; ?>
      </div>

      <div class="h-px bg-[#f2f0eb] my-2"></div>

      <!-- Suggestions -->
      <p class="px-6 pt-1 pb-1 text-[9px] font-black tracking-[0.18em] uppercase text-[#c9aa6b]">
        Mais buscados
      </p>
      <?php
      $suggestions = [
        ['icon' => 'star',     'name' => 'Osteria Moderna',    'sub' => 'Italiana · Campo Belo · ★★★★★'],
        ['icon' => 'utensils', 'name' => 'Nishiki Omakase',    'sub' => 'Japonesa · Brooklin · ★★★★★'],
        ['icon' => 'coffee',   'name' => 'Bossa Café & Bistrô','sub' => 'Café · Campo Belo · ★★★★☆'],
      ];
      foreach ($suggestions as $s): ?>
      <div class="modal-item flex items-center gap-3.5 px-6 py-2.5 cursor-pointer
                  hover:bg-[#f2f0eb] transition-colors duration-150"
           data-query="<?= htmlspecialchars($s['name']) ?>">
        <div class="w-9 h-9 rounded-[10px] bg-[#f5edda] flex items-center justify-center
                    text-[#3d4733] flex-shrink-0">
          <?= icon($s['icon'], 16) ?>
        </div>
        <div>
          <p class="text-sm font-semibold text-[#1d1d1b]"><?= htmlspecialchars($s['name']) ?></p>
          <p class="text-[11px] text-[#8b8589] mt-px"><?= htmlspecialchars($s['sub']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>

    </div>
  </div>
</div>
