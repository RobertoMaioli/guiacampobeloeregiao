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
    ['href' => '/admin/dashboard.php',        'icon' => 'activity', 'label' => 'Dashboard'],
    ['href' => '/admin/empresas/index.php',   'icon' => 'users',    'label' => 'Empresas',   'badge' => $_pendentes],
    ['href' => '/admin/lugares/index.php',    'icon' => 'pin',      'label' => 'Lugares'],
    ['href' => '/admin/categorias/index.php', 'icon' => 'grid',     'label' => 'Categorias'],
    ['href' => '/admin/avaliacoes/index.php', 'icon' => 'star',     'label' => 'Avaliações'],
    ['href' => '/admin/servicos/index.php',   'icon' => 'verified', 'label' => 'Serviços'],
    ['href' => '/admin/tags/index.php',       'icon' => 'sparkles', 'label' => 'Tags'],
];

$svgIcons = [
    'activity' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
    'pin'      => '<path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
    'grid'     => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
    'star'     => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
    'verified' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
    'sparkles' => '<path d="M12 3v3m0 12v3M3 12h3m12 0h3m-2.636-7.364-2.122 2.122M8.757 15.243l-2.121 2.121m0-12.728 2.121 2.121m6.364 6.364 2.122 2.121"/>',
    'users'    => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= Sanitize::html($page_title ?? 'Admin') ?> — Guia Campo Belo</title>
    <meta name="robots" content="noindex"/>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet"/>

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"/>

    <link rel="icon" type="image/png" href="/../assets/img/logo.png"/>

    <style>
        :root {
            --green:      #3d4733;
            --green-dk:   #2a3022;
            --green-lt:   #4f5c40;
            --gold:       #c9aa6b;
            --gold-lt:    #ddc48a;
            --gold-pale:  #f5edda;
            --cream:      #faf8f3;
            --offwhite:   #f2f0eb;
            --graphite:   #1d1d1b;
            --warmgray:   #8b8589;
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            background: var(--offwhite);
            color: var(--graphite);
        }

        /* ── Wrapper ── */
        .admin-wrapper {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ══════════════════════════════
           SIDEBAR
        ══════════════════════════════ */
        .sidebar {
            width: 220px;
            flex-shrink: 0;
            background: var(--green-dk);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-logo {
            padding: 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,.07);
        }

        .sidebar-nav {
            flex: 1;
            padding: 1rem .75rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: .25rem;
        }

        .nav-item-link {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .625rem .875rem;
            border-radius: .75rem;
            font-size: .78125rem;
            font-weight: 600;
            color: rgba(255,255,255,.65);
            text-decoration: none;
            transition: background .2s, color .2s;
            white-space: nowrap;
        }

        .nav-item-link:hover {
            background: rgba(255,255,255,.08);
            color: #fff;
        }

        .nav-item-link.active {
            background: var(--gold);
            color: var(--green-dk);
        }

        .nav-item-link svg {
            flex-shrink: 0;
            color: rgba(255,255,255,.40);
        }

        .nav-item-link.active svg {
            color: var(--green-dk);
        }

        .nav-badge {
            margin-left: auto;
            background: var(--gold);
            color: var(--green-dk);
            font-size: .625rem;
            font-weight: 900;
            border-radius: 50px;
            padding: .1rem .375rem;
            min-width: 18px;
            text-align: center;
            line-height: 1.4;
        }

        .nav-item-link.active .nav-badge {
            background: var(--green-dk);
            color: var(--gold);
        }

        /* ── Sidebar footer ── */
        .sidebar-footer {
            padding: .75rem;
            border-top: 1px solid rgba(255,255,255,.07);
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: .625rem;
            padding: .5rem .75rem;
            margin-bottom: .5rem;
        }

        .admin-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(201,170,107,.20);
            color: var(--gold);
            font-size: .75rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .admin-name {
            font-size: .75rem;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .admin-role {
            font-size: .625rem;
            color: rgba(255,255,255,.35);
        }

        .logout-link {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .625rem .875rem;
            border-radius: .75rem;
            font-size: .75rem;
            font-weight: 600;
            color: rgba(255,255,255,.50);
            text-decoration: none;
            transition: background .2s, color .2s;
        }

        .logout-link:hover {
            background: rgba(255,255,255,.08);
            color: #fff;
        }

        .logout-link svg { color: rgba(255,255,255,.30); }

        /* ══════════════════════════════
           MAIN CONTENT
        ══════════════════════════════ */
        .admin-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ── Topbar ── */
        .topbar {
            height: 56px;
            flex-shrink: 0;
            background: #fff;
            border-bottom: 1px solid rgba(61,71,51,.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }

        .topbar-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--green-dk);
            margin: 0;
        }

        .topbar-link {
            display: flex;
            align-items: center;
            gap: .375rem;
            font-size: .6875rem;
            font-weight: 600;
            color: var(--warmgray);
            text-decoration: none;
            transition: color .2s;
        }

        .topbar-link:hover { color: var(--gold); }

        /* ── Page content ── */
        .page-content {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
        }

        /* ══════════════════════════════
           TOAST
        ══════════════════════════════ */
        #toast {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .875rem 1.25rem;
            border-radius: 1rem;
            box-shadow: 0 8px 24px rgba(0,0,0,.18);
            font-size: .8125rem;
            font-weight: 600;
            animation: toastIn .3s ease;
        }

        #toast.toast-ok  { background: var(--green-dk); color: #fff; }
        #toast.toast-err { background: #dc2626;          color: #fff; }

        @keyframes toastIn {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">

    <!-- ══ SIDEBAR ══ -->
    <aside class="sidebar">

        <!-- Logo -->
        <div class="sidebar-logo">
            <img src="/../assets/img/logo.png" alt="Guia Campo Belo" style="height:70px">
        </div>

        <!-- Nav -->
        <nav class="sidebar-nav">
            <?php foreach ($menu as $item):
                $isActive = str_contains($_SERVER['REQUEST_URI'], $item['href']);
            ?>
            <a href="<?= $item['href'] ?>"
               class="nav-item-link <?= $isActive ? 'active' : '' ?>">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.75"
                     stroke-linecap="round" stroke-linejoin="round">
                    <?= $svgIcons[$item['icon']] ?? '' ?>
                </svg>
                <?= Sanitize::html($item['label']) ?>
                <?php if (!empty($item['badge']) && $item['badge'] > 0): ?>
                <span class="nav-badge"><?= (int)$item['badge'] ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </nav>

        <!-- Admin info + logout -->
        <div class="sidebar-footer">
            <div class="admin-info">
                <div class="admin-avatar">
                    <?= mb_substr($admin['nome'] ?? 'A', 0, 1) ?>
                </div>
                <div style="min-width:0">
                    <div class="admin-name"><?= Sanitize::html($admin['nome'] ?? '') ?></div>
                    <div class="admin-role">Administrador</div>
                </div>
            </div>
            <a href="/admin/logout.php" class="logout-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.75"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Sair
            </a>
        </div>
    </aside>

    <!-- ══ MAIN ══ -->
    <main class="admin-main">

        <!-- Topbar -->
        <header class="topbar">
            <h1 class="topbar-title"><?= Sanitize::html($page_title ?? '') ?></h1>
            <div>
                <a href="/" target="_blank" class="topbar-link">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.75" stroke-linecap="round">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                        <polyline points="15 3 21 3 21 9"/>
                        <line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                    Ver site
                </a>
            </div>
        </header>

        <!-- Page content (scrollable) — fechado no _layout_end.php -->
        <div class="page-content">