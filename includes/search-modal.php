<?php require_once __DIR__ . '/icons.php'; ?>

<div id="search-modal" role="dialog" aria-modal="true" aria-label="Busca">
  <div id="modal-backdrop"></div>
  <div class="search-modal-box">

    <!-- Input row -->
    <div class="search-modal-input-row">
      <span style="color:var(--gcb-gold);flex-shrink:0"><?= icon('search', 20) ?></span>
      <input type="text" id="modal-input"
             placeholder="Buscar restaurante, serviço, bairro…"
             autocomplete="off" aria-label="Buscar"/>
      <button class="modal-close-btn" onclick="SearchModal.close()" aria-label="Fechar">
        <?= icon('close', 14) ?>
      </button>
    </div>

    <div class="modal-scroll">

      <!-- Recentes -->
      <p class="dropdown-label">Buscas recentes</p>
      <?php foreach (['Brunch Campo Belo', 'Pet shop próximo'] as $r): ?>
      <div class="modal-item" data-query="<?= htmlspecialchars($r) ?>">
        <div class="modal-item-icon"><?= icon('clock', 16) ?></div>
        <div>
          <div style="font-size:14px;font-weight:600;color:var(--gcb-graphite)">
            <?= htmlspecialchars($r) ?>
          </div>
          <div style="font-size:11px;color:var(--gcb-warmgray)">Busca recente</div>
        </div>
      </div>
      <?php endforeach; ?>

      <hr class="mx-4 my-2" style="border-color:var(--gcb-offwhite)"/>

      <!-- Categorias -->
      <p class="dropdown-label">Categorias</p>
      <div class="modal-cat-chips">
        <?php
        $cats = [
          ['icon'=>'utensils',    'label'=>'Restaurantes'],
          ['icon'=>'coffee',      'label'=>'Cafés'],
          ['icon'=>'paw',         'label'=>'Pet Friendly'],
          ['icon'=>'spa',         'label'=>'Bem-estar'],
          ['icon'=>'shopping-bag','label'=>'Compras'],
          ['icon'=>'wine',        'label'=>'Wine Bar'],
          ['icon'=>'dumbbell',    'label'=>'Academia'],
          ['icon'=>'scissors',    'label'=>'Beleza'],
        ];
        foreach ($cats as $c): ?>
        <div class="modal-cat-chip" data-query="<?= htmlspecialchars($c['label']) ?>">
          <?= icon($c['icon'], 14) ?>
          <?= htmlspecialchars($c['label']) ?>
        </div>
        <?php endforeach; ?>
      </div>

      <hr class="mx-4 my-2" style="border-color:var(--gcb-offwhite)"/>

      <!-- Mais buscados -->
      <p class="dropdown-label">Mais buscados</p>
      <?php
      $suggestions = [
        ['icon'=>'star',    'name'=>'Osteria Moderna',   'sub'=>'Italiana · Campo Belo · ★★★★★'],
        ['icon'=>'utensils','name'=>'Nishiki Omakase',   'sub'=>'Japonesa · Brooklin · ★★★★★'],
        ['icon'=>'coffee',  'name'=>'Bossa Café & Bistrô','sub'=>'Café · Campo Belo · ★★★★☆'],
      ];
      foreach ($suggestions as $s): ?>
      <div class="modal-item" data-query="<?= htmlspecialchars($s['name']) ?>">
        <div class="modal-item-icon"><?= icon($s['icon'], 16) ?></div>
        <div>
          <div style="font-size:14px;font-weight:600;color:var(--gcb-graphite)">
            <?= htmlspecialchars($s['name']) ?>
          </div>
          <div style="font-size:11px;color:var(--gcb-warmgray)"><?= htmlspecialchars($s['sub']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>

    </div>
  </div>
</div>