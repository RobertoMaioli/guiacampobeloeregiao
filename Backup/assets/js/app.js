/**
 * Guia Campo Belo & Região — app.js
 */

/* ══════════════════════════════════════════════
   HEADER — scroll behaviour
══════════════════════════════════════════════ */
const Header = (() => {
  const el        = document.getElementById('site-header');
  const backToTop = document.getElementById('back-to-top');
  const searchIcon= document.getElementById('header-search-icon');

  function update() {
    const scrolled = window.scrollY > 50;

    el?.classList.toggle('header-scrolled', scrolled);

    if (backToTop) {
      backToTop.classList.toggle('opacity-0',         !scrolled);
      backToTop.classList.toggle('pointer-events-none',!scrolled);
      backToTop.classList.toggle('translate-y-2',     !scrolled);
    }

    if (searchIcon) {
      searchIcon.style.display = scrolled ? 'none' : '';
    }
  }

  window.addEventListener('scroll', update, { passive: true });
  update();
})();


/* ══════════════════════════════════════════════
   MOBILE MENU
══════════════════════════════════════════════ */
const MobileMenu = (() => {
  const btn = document.getElementById('mobile-menu-btn');
  const nav = document.getElementById('mobile-nav');
  if (!btn || !nav) return;

  btn.addEventListener('click', () => {
    nav.classList.toggle('hidden');
  });
})();


/* ══════════════════════════════════════════════
   SEARCH MODAL
══════════════════════════════════════════════ */
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
    const m = modal();
    if (!m) return;
    m.classList.remove('open');
    document.body.style.overflow = '';
  }

  // ESC + Cmd/Ctrl+K
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') close();
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') { e.preventDefault(); open(); }
  });

  // Click on modal items → fill hero input or navigate
  document.querySelectorAll('.modal-item, .modal-cat').forEach(item => {
    item.addEventListener('click', () => {
      const q = item.dataset.query ?? '';
      const heroInput = document.getElementById('hero-search-input');
      if (heroInput) heroInput.value = q;
      close();
    });
  });

  return { open, close };
})();


/* ══════════════════════════════════════════════
   HERO SEARCH — autocomplete dropdown
══════════════════════════════════════════════ */
const HeroSearch = (() => {
  const input    = document.getElementById('hero-search-input');
  const dropdown = document.getElementById('search-dropdown');
  const box      = document.getElementById('search-box');
  if (!input || !dropdown) return;

  // Open on focus
  input.addEventListener('focus', () => dropdown.classList.add('open'));

  // Close on outside click
  document.addEventListener('click', e => {
    if (!box?.contains(e.target)) dropdown.classList.remove('open');
  });

  // Click dropdown item → fill input
  dropdown.querySelectorAll('[data-fill]').forEach(item => {
    item.addEventListener('click', () => {
      input.value = item.dataset.fill ?? '';
      dropdown.classList.remove('open');
      input.focus();
    });
  });

  // Remove recent
  dropdown.querySelectorAll('.recent-remove').forEach(btn => {
    btn.addEventListener('click', e => {
      e.stopPropagation();
      btn.closest('.dropdown-recent-row')?.remove();
    });
  });
})();


/* ══════════════════════════════════════════════
   QUICK TAGS — fill hero search input
══════════════════════════════════════════════ */
document.querySelectorAll('.quick-tag').forEach(tag => {
  tag.addEventListener('click', () => {
    const input = document.getElementById('hero-search-input');
    if (input) { input.value = tag.dataset.fill ?? tag.textContent.trim(); input.focus(); }
  });
});


/* ══════════════════════════════════════════════
   CATEGORY STRIP — active pill + filter
══════════════════════════════════════════════ */
document.querySelectorAll('.cat-pill').forEach(pill => {
  pill.addEventListener('click', function () {
    document.querySelectorAll('.cat-pill').forEach(p => {
      p.classList.remove('border-b-[#c9aa6b]', 'active-pill');
      p.querySelector('.pill-label')?.classList.remove('text-[#3d4733]', 'font-bold');
    });
    this.classList.add('border-b-[#c9aa6b]', 'active-pill');
    this.querySelector('.pill-label')?.classList.add('text-[#3d4733]', 'font-bold');

    const category = this.dataset.category ?? 'all';
    filterCards(category);
  });
});

function filterCards(category) {
  document.querySelectorAll('.listing-card').forEach(card => {
    const match = category === 'all' || (card.dataset.category ?? '') === category;
    card.style.display = match ? '' : 'none';
  });
}


/* ══════════════════════════════════════════════
   FAVOURITES — heart toggle
══════════════════════════════════════════════ */
document.querySelectorAll('.fav-btn').forEach(btn => {
  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    this.classList.toggle('is-fav');
    const fav = this.classList.contains('is-fav');
    this.style.color = fav ? '#e05555' : '';
    this.setAttribute('aria-label', fav ? 'Remover dos favoritos' : 'Salvar nos favoritos');
  });
});


/* ══════════════════════════════════════════════
   SCROLL REVEAL
══════════════════════════════════════════════ */
const revealObserver = new IntersectionObserver(entries => {
  entries.forEach((entry, i) => {
    if (entry.isIntersecting) {
      setTimeout(() => entry.target.classList.add('visible'), i * 90);
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.08 });

document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));
