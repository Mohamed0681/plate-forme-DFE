document.addEventListener('DOMContentLoaded', () => {
  const root = document.documentElement;
  document.getElementById('adm-theme')?.addEventListener('click', () => {
    const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    root.setAttribute('data-theme', next);
    try { localStorage.setItem('adm-theme', next); } catch (e) {}
  });
  document.querySelectorAll('[data-confirm]').forEach(el => el.addEventListener('click', e => { if (!confirm(el.dataset.confirm || 'Confirmer cette action ?')) e.preventDefault(); }));
  const toggle = open => document.body.classList.toggle('side-open', open);
  document.getElementById('adm-burger')?.addEventListener('click', () => toggle(true));
  document.getElementById('adm-scrim')?.addEventListener('click', () => toggle(false));
  const t = document.getElementById('adm-time');
  if (t) setInterval(() => { const d = new Date(); t.textContent = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0'); }, 15000);
  document.querySelectorAll('.table tbody tr[data-href]').forEach(r => r.addEventListener('click', e => { if (!e.target.closest('a,button,form')) location.href = r.dataset.href; }));
});
