/* Plateforme institutionnelle — comportements communs */
document.addEventListener('DOMContentLoaded', () => {
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Menu mobile */
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.main-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const open = nav.classList.toggle('open');
      toggle.setAttribute('aria-expanded', String(open));
    });
  }

  /* Confirmations (admin) */
  document.querySelectorAll('[data-confirm]').forEach(link => {
    link.addEventListener('click', event => {
      if (!window.confirm(link.dataset.confirm || 'Confirmer cette action ?')) event.preventDefault();
    });
  });

  /* En-tête + barre de progression */
  const header = document.querySelector('.site-header');
  const bar = document.createElement('div');
  bar.className = 'scroll-progress';
  document.body.appendChild(bar);
  let ticking = false;
  const onScroll = () => {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => {
      const y = window.scrollY;
      if (header) header.classList.toggle('is-scrolled', y > 24);
      const max = document.documentElement.scrollHeight - window.innerHeight;
      bar.style.width = (max > 0 ? (y / max) * 100 : 0) + '%';
      ticking = false;
    });
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* Hero : diaporama + vidéo de fond */
  const slides = [...document.querySelectorAll('.hero .slide')];
  const dots = [...document.querySelectorAll('.hero-dots button')];
  const SLIDE_MS = 6500;
  if (slides.length > 1 && !reduce) {
    document.querySelector('.hero')?.style.setProperty('--slide-ms', SLIDE_MS + 'ms');
    let cur = 0, timer;
    const show = n => {
      slides[cur].classList.remove('active'); dots[cur]?.classList.remove('active');
      cur = (n + slides.length) % slides.length;
      void slides[cur].offsetWidth;                 // relance l'animation Ken Burns
      slides[cur].classList.add('active'); dots[cur]?.classList.add('active');
    };
    const start = () => { clearInterval(timer); timer = setInterval(() => show(cur + 1), SLIDE_MS); };
    dots.forEach((d, i) => d.addEventListener('click', () => { show(i); start(); }));
    document.addEventListener('visibilitychange', () => document.hidden ? clearInterval(timer) : start());
    start();
  }
  const video = document.querySelector('.hero-video');
  if (video) {
    const lite = window.innerWidth < 700 || (navigator.connection && navigator.connection.saveData);
    if (lite || reduce) { video.remove(); }
    else {
      video.addEventListener('playing', () => video.classList.add('ready'), { once: true });
      video.play().catch(() => video.remove());
      const hero = document.querySelector('.hero');
      if ('IntersectionObserver' in window && hero) {
        new IntersectionObserver(([en]) => en.isIntersecting ? video.play().catch(() => {}) : video.pause()).observe(hero);
      }
    }
  }

  /* Apparition au défilement */
  const auto = '.section-heading, .stat-card, .feature, .content-card, .document-row, .video-card, .image-card, .media-tabs, .search-form, .prose > *, .form-card, .contact-layout > .prose';
  document.querySelectorAll(auto).forEach(el => {
    if (el.closest('.hero') || el.hasAttribute('data-reveal') || el.closest('.admin-page')) return;
    el.setAttribute('data-reveal', '');
    const idx = [...el.parentElement.children].indexOf(el);
    el.style.setProperty('--d', Math.min(idx, 6) * 90 + 'ms');
  });
  const targets = document.querySelectorAll('[data-reveal]');
  if ('IntersectionObserver' in window && !reduce) {
    const io = new IntersectionObserver(entries => entries.forEach(en => {
      if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
    }), { threshold: .12, rootMargin: '0px 0px -40px 0px' });
    targets.forEach(el => io.observe(el));
  } else {
    targets.forEach(el => el.classList.add('in'));
  }

  /* Compteurs animés (valeurs purement numériques) */
  document.querySelectorAll('.stat-card strong').forEach(strong => {
    const node = [...strong.childNodes].find(n => n.nodeType === 3 && n.textContent.trim() !== '');
    if (!node) return;
    const raw = node.textContent.trim().replace(/[\s ]/g, '');
    if (!/^\d+([.,]\d+)?$/.test(raw)) return;
    const dec = (raw.split(/[.,]/)[1] || '').length;
    const target = parseFloat(raw.replace(',', '.'));
    const fmt = v => v.toLocaleString('fr-FR', { minimumFractionDigits: dec, maximumFractionDigits: dec });
    const finalText = node.textContent;
    if (reduce) return;
    node.textContent = fmt(0) + ' ';
    const run = () => {
      const t0 = performance.now(), dur = 1600;
      const step = now => {
        const p = Math.min((now - t0) / dur, 1), k = 1 - Math.pow(1 - p, 3);
        node.textContent = fmt(target * k) + ' ';
        if (p < 1) requestAnimationFrame(step); else node.textContent = finalText;
      };
      requestAnimationFrame(step);
    };
    if ('IntersectionObserver' in window) {
      const io = new IntersectionObserver(([en]) => { if (en.isIntersecting) { io.disconnect(); run(); } }, { threshold: .6 });
      io.observe(strong);
    } else { node.textContent = finalText; }
  });

  /* Visionneuse d'images (médiathèque) */
  const links = document.querySelectorAll('[data-lightbox]');
  if (links.length) {
    const box = document.createElement('div');
    box.className = 'lightbox'; box.hidden = true;
    box.innerHTML = '<button type="button" aria-label="Fermer">×</button><figure><img alt=""><figcaption></figcaption></figure>';
    document.body.appendChild(box);
    const img = box.querySelector('img'), cap = box.querySelector('figcaption');
    const close = () => { box.hidden = true; img.removeAttribute('src'); };
    links.forEach(a => a.addEventListener('click', e => {
      e.preventDefault();
      img.src = a.href; img.alt = a.dataset.caption || ''; cap.textContent = a.dataset.caption || '';
      box.hidden = false;
    }));
    box.addEventListener('click', e => { if (e.target !== img) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
  }
});
