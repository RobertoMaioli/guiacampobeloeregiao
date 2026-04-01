<?php
/**
 * includes/header.php
 * Header principal — suporta usuário empresa logado/deslogado
 */
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/../core/UserAuth.php';

$_usuario = UserAuth::headerData();    // leve: só lê $_SESSION, sem query
$_logado  = $_usuario !== null;

// Inicial do nome para o avatar (ex: "João Silva" → "JS")
$_iniciais = '';
if ($_logado) {
    $partes     = explode(' ', trim($_usuario['nome']));
    $_iniciais  = mb_strtoupper(mb_substr($partes[0], 0, 1));
    if (isset($partes[1])) $_iniciais .= mb_strtoupper(mb_substr($partes[1], 0, 1));
}
?>
<header id="site-header">
  <div id="header-bar">
    <div class="container">
      <div class="header-inner">

        <!-- ── Logo ── -->
        <a href="/" class="d-flex align-items-center text-decoration-none flex-shrink-0"
           aria-label="Guia Campo Belo">
          <img src="/assets/img/logo.png" alt="Guia Campo Belo" style="height:70px">
        </a>

        <!-- ── Nav desktop ── -->
        <nav class="d-none  align-items-center gap-4" aria-label="Navegação principal">
          <?php
          $nav = [
            'Restaurantes' => '/pages/categoria.php?slug=restaurantes',
            'Cafés'        => '/pages/categoria.php?slug=cafes',
            'Serviços'     => '/pages/categoria.php?slug=bem-estar',
            'Lazer'        => '/pages/categoria.php?slug=lazer',
            'Mapa'         => '/pages/mapa.php',
          ];
          foreach ($nav as $label => $href): ?>
          <a href="<?= $href ?>" class="nav-link-gcb"><?= htmlspecialchars($label) ?></a>
          <?php endforeach; ?>
        </nav>

        <!-- ── Actions ── -->
        <div class="d-flex align-items-center gap-2">
            
            <a href="/pages/mapa.php"
                 class="btn-header-outline d-none d-sm-inline-flex align-items-center gap-2"
                 style="color:#c9aa6b"
                 title="Ver mapa">
                <?= icon('map', 14) ?> Mapa
            </a>

          <?php if (!$_logado): ?>
            <!-- ── Deslogado: Registar + Entrar ── -->
            <a href="/pages/anuncie.php"
               class="btn-gold d-none d-sm-inline-flex align-items-center gap-2">
              Anuncie sua empresa
            </a>
            <a href="/empresa/login.php"
               class="btn-header-outline bg-white d-none d-sm-inline-flex align-items-center gap-2">
              <?= icon('user', 15) ?>
              Entrar
            </a>

          <?php else: ?>
            <!-- ── Logado: Avatar com dropdown ── -->
            <div class="header-user-menu" id="user-menu-wrap">
              <button class="header-avatar-btn" id="user-menu-btn"
                      onclick="toggleUserMenu()" aria-expanded="false"
                      aria-label="Menu da conta">
                <span class="avatar-initials"><?= htmlspecialchars($_iniciais) ?></span>
                <span class="avatar-name d-none d-md-inline">
                  <?= htmlspecialchars(explode(' ', $_usuario['nome'])[0]) ?>
                </span>
                <?= icon('chevron-down', 13) ?>
              </button>

              <div class="header-dropdown" id="user-dropdown" role="menu">
                <!-- Cabeçalho do dropdown -->
                <div class="dropdown-user-header">
                  <span class="dropdown-avatar"><?= htmlspecialchars($_iniciais) ?></span>
                  <div class="dropdown-user-info">
                    <strong><?= htmlspecialchars($_usuario['nome']) ?></strong>
                    <span><?= htmlspecialchars($_usuario['email']) ?></span>
                  </div>
                </div>
                <div class="dropdown-divider"></div>
                <!-- Links -->
                <a href="/empresa/dashboard.php" class="dropdown-item-gcb" role="menuitem">
                  <?= icon('grid', 15) ?> Minha empresa
                </a>
                <a href="/empresa/editar.php" class="dropdown-item-gcb" role="menuitem">
                  <?= icon('sliders', 15) ?> Editar perfil
                </a>
                <a href="/empresa/plano.php" class="dropdown-item-gcb" role="menuitem">
                  <?= icon('award', 15) ?> Meu plano
                </a>
                <div class="dropdown-divider"></div>
                <a href="/empresa/logout.php" class="dropdown-item-gcb dropdown-item-danger"
                   role="menuitem">
                  <?= icon('arrow-right', 15) ?> Sair
                </a>
              </div>
            </div>

          <?php endif; ?>

          <!-- ── Mobile toggle ── -->
          <button id="mobile-menu-btn" class="header-icon-btn d-lg-none"
                  aria-label="Menu" onclick="toggleMobileNav()">
            <?= icon('menu', 18) ?>
          </button>

        </div><!-- /actions -->
      </div>
    </div>

    <!-- ── Mobile nav ── -->
    <div id="mobile-nav" class="d-none bg-white border-top py-3"
         style="border-color:rgba(61,71,51,.1)!important">
      <div class="container">
        <?php foreach ($nav as $label => $href): ?>
        <a href="<?= $href ?>"
           class="d-flex align-items-center gap-3 py-3 text-decoration-none border-bottom"
           style="font-size:12px;font-weight:600;letter-spacing:.1em;
                  text-transform:uppercase;color:var(--gcb-graphite);
                  border-color:rgba(61,71,51,.06)!important">
          <?= htmlspecialchars($label) ?>
        </a>
        <?php endforeach; ?>

        <div class="pt-3 d-flex flex-column gap-2">
          <?php if (!$_logado): ?>
            <a href="/empresa/login.php"
               class="btn-gold d-flex align-items-center justify-content-center gap-2 py-3">
              <?= icon('user', 15) ?> Entrar
            </a>
            <a href="/pages/anuncie.php"
               class="btn-header-outline d-flex align-items-center justify-content-center gap-2 py-3">
              Anunciar empresa
            </a>
          <?php else: ?>
            <a href="/empresa/dashboard.php"
               class="btn-gold d-flex align-items-center justify-content-center gap-2 py-3">
              <?= icon('grid', 15) ?> Minha empresa
            </a>
            <a href="/empresa/logout.php"
               class="btn-header-outline d-flex align-items-center justify-content-center gap-2 py-3"
               style="color:var(--gcb-warmgray)">
              Sair
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div><!-- /#header-bar -->
</header>

<script>
/* ── Toggle mobile nav ── */
function toggleMobileNav() {
  const nav = document.getElementById('mobile-nav');
  const btn = document.getElementById('mobile-menu-btn');
  const open = nav.classList.toggle('d-none') === false;
  btn.setAttribute('aria-expanded', open);
}

/* ── Toggle user dropdown ── */
function toggleUserMenu() {
  const drop = document.getElementById('user-dropdown');
  const btn  = document.getElementById('user-menu-btn');
  if (!drop) return;
  const open = drop.classList.toggle('open');
  btn.setAttribute('aria-expanded', open);
}

/* Fecha dropdown ao clicar fora */
document.addEventListener('click', function(e) {
  const wrap = document.getElementById('user-menu-wrap');
  if (wrap && !wrap.contains(e.target)) {
    document.getElementById('user-dropdown')?.classList.remove('open');
    document.getElementById('user-menu-btn')?.setAttribute('aria-expanded', 'false');
  }
});

/* Fecha dropdown com Escape */
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.getElementById('user-dropdown')?.classList.remove('open');
    document.getElementById('user-menu-btn')?.setAttribute('aria-expanded', 'false');
  }
});
</script>