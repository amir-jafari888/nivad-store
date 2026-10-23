(() => {
  'use strict';
  const storageKey = 'nivad-admin-theme';
  const root = document.documentElement;
  const readTheme = () => {
    try {
      const saved = localStorage.getItem(storageKey);
      if (saved === 'light' || saved === 'dark') return saved;
    } catch (_) {}
    return 'light';
  };
  const applyTheme = (theme) => {
    const next = theme === 'dark' ? 'dark' : 'light';
    root.dataset.adminTheme = next;
    const button = document.querySelector('[data-admin-theme-toggle]');
    if (button) {
      button.setAttribute('aria-pressed', String(next === 'dark'));
      button.setAttribute('aria-label', next === 'dark' ? 'فعال‌کردن پوسته روشن' : 'فعال‌کردن پوسته تیره');
      button.textContent = next === 'dark' ? 'پوسته روشن' : 'پوسته تیره';
    }
  };
  const mountToggle = () => {
    if (document.querySelector('[data-admin-theme-toggle]')) return;
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'admin-theme-toggle';
    button.dataset.adminThemeToggle = '';
    button.setAttribute('aria-pressed', String(root.dataset.adminTheme === 'dark'));
    button.textContent = root.dataset.adminTheme === 'dark' ? 'پوسته روشن' : 'پوسته تیره';
    button.setAttribute('aria-label', root.dataset.adminTheme === 'dark' ? 'فعال‌کردن پوسته روشن' : 'فعال‌کردن پوسته تیره');
    button.addEventListener('click', () => {
      const next = root.dataset.adminTheme === 'dark' ? 'light' : 'dark';
      try { localStorage.setItem(storageKey, next); } catch (_) {}
      applyTheme(next);
    });
    const header = document.querySelector('body > header, body > div > header, main > header');
    if (header) header.append(button);
    else document.body.append(button);
  };
  applyTheme(readTheme());
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountToggle, { once: true });
  } else {
    mountToggle();
  }
})();