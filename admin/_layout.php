<?php
/**
 * admin/_layout.php
 * Layout compartilhado do painel admin
 * Uso: include com $page_title e $active_menu definidos antes
 */
$admin = Auth::admin();
$csrf  = Sanitize::csrfToken();

// Badge de pendentes para o menu
$_pendentes = (int)(DB::row('SELECT COUNT(*) n FROM empresas WHERE status="pendente"')['n'] ?? 0);

$menu = [
    ['href' => '/admin/dashboard.php',       'icon' => 'activity',  'label' => 'Dashboard'],
    ['href' => '/admin/empresas/index.php',  'icon' => 'users',     'label' => 'Empresas', 'badge' => $_pendentes],
    ['href' => '/admin/lugares/index.php',   'icon' => 'pin',       'label' => 'Lugares'],
    ['href' => '/admin/categorias/index.php','icon' => 'grid',      'label' => 'Categorias'],
    ['href' => '/admin/avaliacoes/index.php','icon' => 'star',      'label' => 'Avaliações'],
    ['href' => '/admin/servicos/index.php',  'icon' => 'verified',  'label' => 'Serviços'],
    ['href' => '/admin/tags/index.php',      'icon' => 'sparkles',  'label' => 'Tags'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= Sanitize::html($page_title ?? 'Admin') ?> — Guia Campo Belo</title>
  <meta name="robots" content="noindex"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet"/>
  <link rel="icon" type="image/png" href="/../assets/img/logo.png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: {
        fontFamily: { display:['"Montserrat"','sans-serif'], body:['Montserrat','sans-serif'] },
        colors: {
          green:{ DEFAULT:'#3d4733', dark:'#2a3022', light:'#4f5c40' },
          gold:{ DEFAULT:'#c9aa6b', light:'#ddc48a', pale:'#f5edda' },
          cream:'#faf8f3', offwhite:'#f2f0eb', graphite:'#1d1d1b', warmgray:'#8b8589'
        }
      }}
    }
  </script>
  <style>
    body { font-family:'Montserrat',sans-serif; background:#f2f0eb; }
    .font-display { font-family:'Montserrat',serif; }
    .nav-link.active { background:#c9aa6b; color:#2a3022; }
    .nav-link.active svg { color:#2a3022; }
  </style>
</head>
<body class="bg-offwhite text-graphite antialiased">
<div class="flex h-screen overflow-hidden">

  <!-- ── SIDEBAR ── -->
  <aside class="w-[220px] flex-shrink-0 bg-green-dark flex flex-col">

    <!-- Logo -->
    <div class="px-5 py-5 border-b border-white/[0.07]">
      <div class="flex items-center gap-2.5">
        <img src="/../assets/img/logo.png" alt="Guia Campo Belo" style="height:70px">
      </div>
    </div>

    <!-- Nav -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
      <?php foreach ($menu as $item):
        $isActive = str_contains($_SERVER['REQUEST_URI'], $item['href']);
        $svgIcons = [
          'activity' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
          'pin'      => '<path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
          'grid'     => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
          'star'     => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
          'verified'  => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
          'sparkles'  => '<path d="M12 3v3m0 12v3M3 12h3m12 0h3m-2.636-7.364-2.122 2.122M8.757 15.243l-2.121 2.121m0-12.728 2.121 2.121m6.364 6.364 2.122 2.121"/>',
          'users'     => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        ];
      ?>
      <a href="<?= $item['href'] ?>"
         class="nav-link <?= $isActive ? 'active' : '' ?>
                flex items-center gap-3 px-3.5 py-2.5 rounded-xl
                text-[12.5px] font-semibold text-white/65
                hover:bg-white/[0.08] hover:text-white transition-all duration-200">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
             class="<?= $isActive ? 'text-[#2a3022]' : 'text-white/40' ?>">
          <?= $svgIcons[$item['icon']] ?? '' ?>
        </svg>
        <?= Sanitize::html($item['label']) ?>
        <?php if (!empty($item['badge']) && $item['badge'] > 0): ?>
        <span class="ml-auto bg-[#c9aa6b] text-[#2a3022] text-[10px] font-black
                     rounded-full px-1.5 py-0.5 min-w-[18px] text-center leading-tight">
          <?= (int)$item['badge'] ?>
        </span>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </nav>

    <!-- Admin info + logout -->
    <div class="px-3 py-4 border-t border-white/[0.07]">
      <div class="flex items-center gap-2.5 px-3 mb-3">
        <div class="w-8 h-8 rounded-full bg-[#c9aa6b]/20 flex items-center justify-center
                    text-[#c9aa6b] text-[12px] font-black flex-shrink-0">
          <?= mb_substr($admin['nome'] ?? 'A', 0, 1) ?>
        </div>
        <div class="min-w-0">
          <p class="text-white text-[12px] font-semibold truncate"><?= Sanitize::html($admin['nome'] ?? '') ?></p>
          <p class="text-white/35 text-[10px]">Administrador</p>
        </div>
      </div>
      <a href="/admin/logout.php"
         class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-[12px] font-semibold
                text-white/50 hover:bg-white/[0.08] hover:text-white transition-all duration-200 w-full">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="text-white/30">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        Sair
      </a>
    </div>
  </aside>

  <!-- ── CONTENT AREA ── -->
  <main class="flex-1 flex flex-col overflow-hidden">

    <!-- Topbar -->
    <header class="h-14 bg-white border-b border-green/[0.08] flex items-center
                   justify-between px-8 flex-shrink-0 shadow-sm">
      <h1 class="font-display text-[18px] font-bold text-green-dark">
        <?= Sanitize::html($page_title ?? '') ?>
      </h1>
      <div class="flex items-center gap-3">
        <a href="/" target="_blank"
           class="flex items-center gap-1.5 text-[11px] font-semibold text-warmgray
                  hover:text-gold transition-colors">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="1.75" stroke-linecap="round">
            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
            <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
          </svg>
          Ver site
        </a>
      </div>
    </header>

    <!-- Page content (scrollable) -->
    <div class="flex-1 overflow-y-auto p-8">