/**
 * Guia Campo Belo & Região — gcb.js
 * Requer Bootstrap 5.3 já carregado
 */

/* ══════════════════════════════════════════
   HEADER SCROLL
══════════════════════════════════════════ */
const GCBHeader = (() => {
  const header   = document.getElementById('site-header');
  const backTop  = document.getElementById('back-to-top');
  const srchIcon = document.getElementById('header-search-icon');

  function update() {
    const scrolled = window.scrollY > 50;
    header?.classList.toggle('header-scrolled', scrolled);
    backTop?.classList.toggle('visible', scrolled);
    if (srchIcon) srchIcon.style.display = scrolled ? 'none' : '';
  }

  window.addEventListener('scroll', update, { passive: true });
  update();
})();


/* ══════════════════════════════════════════
   MOBILE NAV
══════════════════════════════════════════ */
// Mobile nav controlado por toggleMobileNav() em header.php


/* ══════════════════════════════════════════
   SEARCH MODAL
══════════════════════════════════════════ */
const SearchModal = (() => {
  const modal = () => document.getElementById('search-modal');
  const input = () => document.getElementById('modal-input');

  function open() {
    const m = modal();
    if (!m) return;
    m.classList.add('open');
    document.body.style.overflow = 'hidden';
    setTimeout(() => input()?.focus(), 60);
  }

  function close() {
    modal()?.classList.remove('open');
    document.body.style.overflow = '';
  }

  // ESC + Cmd/Ctrl+K
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') close();
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') { e.preventDefault(); open(); }
  });

  // Click backdrop
  document.getElementById('modal-backdrop')
    ?.addEventListener('click', close);

  // Modal items → fill search + close
  document.querySelectorAll('.modal-item, .modal-cat-chip').forEach(el => {
    el.addEventListener('click', () => {
      const q = el.dataset.query ?? el.textContent.trim();
      const heroInput = document.getElementById('hero-search-input');
      if (heroInput) heroInput.value = q;
      close();
    });
  });

  return { open, close };
})();


/* ══════════════════════════════════════════
   HERO SEARCH AUTOCOMPLETE
══════════════════════════════════════════ */
(() => {
  const input    = document.getElementById('hero-search-input');
  const dropdown = document.getElementById('search-dropdown');
  const box      = document.getElementById('search-box');
  if (!input || !dropdown) return;

  input.addEventListener('focus', () => dropdown.classList.add('open'));

  document.addEventListener('click', e => {
    if (!box?.contains(e.target)) dropdown.classList.remove('open');
  });

  dropdown.querySelectorAll('[data-fill]').forEach(item => {
    item.addEventListener('click', () => {
      input.value = item.dataset.fill;
      dropdown.classList.remove('open');
      input.focus();
    });
  });

  dropdown.querySelectorAll('.recent-remove').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      btn.closest('.dropdown-recent-row')?.remove();
    });
  });

  // Submit on Enter
  function doSearch() {
    const q   = input.value.trim();
    const cat = document.getElementById('hero-search-cat')?.value ?? '';
    let url = '/pages/categoria.php';
    const params = new URLSearchParams();
    if (cat) params.set('slug', cat);
    if (q)   params.set('q', q);
    const qs = params.toString();
    window.location.href = qs ? url + '?' + qs : url;
  }

  input.addEventListener('keydown', e => {
    if (e.key === 'Enter') doSearch();
  });

  document.querySelector('.search-submit')?.addEventListener('click', doSearch);
})();


/* ══════════════════════════════════════════
   QUICK TAGS
══════════════════════════════════════════ */
document.querySelectorAll('.quick-tag').forEach(tag => {
  tag.addEventListener('click', () => {
    const input = document.getElementById('hero-search-input');
    if (input) { input.value = tag.dataset.fill ?? tag.textContent.trim(); input.focus(); }
  });
});


/* ══════════════════════════════════════════
   CATEGORY STRIP
══════════════════════════════════════════ */
document.querySelectorAll('.cat-pill').forEach(pill => {
  pill.addEventListener('click', function () {
    document.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
    this.classList.add('active');
    filterCards(this.dataset.category ?? 'all');
  });
});

function filterCards(category) {
  let visible = 0;
  document.querySelectorAll('.listing-card').forEach(card => {
    const show = category === 'all' || (card.dataset.category ?? '') === category;
    card.style.display = show ? '' : 'none';
    if (show) visible++;
  });
  const count = document.getElementById('results-count');
  if (count) count.textContent = visible;
}


/* ══════════════════════════════════════════
   FAVOURITES
══════════════════════════════════════════ */
document.querySelectorAll('.fav-btn').forEach(btn => {
  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    this.classList.toggle('is-fav');
  });
});


/* ══════════════════════════════════════════
   BACK TO TOP
══════════════════════════════════════════ */
document.getElementById('back-to-top')
  ?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));


/* ══════════════════════════════════════════
   REVEAL ON SCROLL
══════════════════════════════════════════ */
const revealObs = new IntersectionObserver(entries => {
  entries.forEach((entry, i) => {
    if (entry.isIntersecting) {
      setTimeout(() => entry.target.classList.add('in'), i * 90);
      revealObs.unobserve(entry.target);
    }
  });
}, { threshold: 0.08 });

document.querySelectorAll('.reveal').forEach(el => revealObs.observe(el));


/* ══════════════════════════════════════════
   CATEGORY PAGE — FILTERS
══════════════════════════════════════════ */
window.applyFilters = function () {
  const q      = (document.getElementById('search-inline')?.value ?? '').trim().toLowerCase();
  const preco  = window.activePreco ?? 'todos';
  const cards  = document.querySelectorAll('.listing-card');
  let visible  = 0;

  cards.forEach(card => {
    const nameOk  = !q     || (card.dataset.nome  ?? '').includes(q);
    const precoOk = preco === 'todos' || (card.dataset.preco ?? '') === preco;
    const show    = nameOk && precoOk;
    card.style.display = show ? '' : 'none';
    if (show) visible++;
  });

  const count    = document.getElementById('results-count');
  const noResult = document.getElementById('no-results');
  const pag      = document.getElementById('pagination');
  if (count)    count.textContent = visible;
  if (noResult) noResult.style.display = visible === 0 ? 'flex' : 'none';
  if (pag)      pag.style.display     = visible === 0 ? 'none' : '';

  const clearBtn = document.getElementById('clear-btn');
  if (clearBtn) clearBtn.classList.toggle('d-none', preco === 'todos' && !q);
};

window.setPreco = function (val, btn) {
  window.activePreco = val;
  document.querySelectorAll('.price-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  applyFilters();
};

window.clearFilters = function () {
  window.activePreco = 'todos';
  const si = document.getElementById('search-inline');
  if (si) si.value = '';
  document.querySelectorAll('.price-btn').forEach(b => b.classList.remove('active'));
  document.querySelector('.price-btn[data-price="todos"]')?.classList.add('active');
  applyFilters();
};

// Init
window.activePreco = 'todos';


/* ══════════════════════════════════════════
   SORT DROPDOWN (categoria page)
══════════════════════════════════════════ */
window.setSort = function (val, lbl) {
  const label = document.getElementById('sort-label');
  if (label) label.textContent = lbl;
  document.querySelectorAll('.sort-opt').forEach(b => b.classList.remove('active'));
  event?.target?.classList.add('active');
};


/* ══════════════════════════════════════════
   VIEW TOGGLE (grid/list)
══════════════════════════════════════════ */
window.setView = function (v) {
  const area = document.getElementById('cards-area');
  const btnG = document.getElementById('btn-grid');
  const btnL = document.getElementById('btn-list');
  if (!area) return;
  area.dataset.view = v;
  if (v === 'grid') {
    area.classList.remove('view-list');
    btnG?.classList.add('active-view');
    btnL?.classList.remove('active-view');
  } else {
    area.classList.add('view-list');
    btnL?.classList.add('active-view');
    btnG?.classList.remove('active-view');
  }
};


/* ══════════════════════════════════════════
   MOBILE SIDEBAR (categoria page)
══════════════════════════════════════════ */
window.toggleMobileSidebar = function () {
  const sb = document.getElementById('sidebar-mobile');
  if (!sb) return;
  const isOpen = sb.classList.toggle('show');
  document.body.style.overflow = isOpen ? 'hidden' : '';
};