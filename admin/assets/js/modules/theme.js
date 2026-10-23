const adminTheme = () => {
  "use strict";

  const storageKey = "nivad-admin-theme";
  const root = document.documentElement;

  const readTheme = () => {
    try {
      const saved = window.localStorage.getItem(storageKey);
      if (saved === "light" || saved === "dark") return saved;
    } catch (_) {}
    return "light";
  };

  const applyTheme = (theme, button) => {
    const next = theme === "dark" ? "dark" : "light";
    root.dataset.adminTheme = next;
    if (!button) return;
    button.setAttribute("aria-pressed", String(next === "dark"));
    button.setAttribute(
      "aria-label",
      next === "dark" ? "فعال‌کردن پوسته روشن" : "فعال‌کردن پوسته تیره"
    );
    button.textContent = next === "dark" ? "پوسته روشن" : "پوسته تیره";
  };

  const mountToggle = () => {
    let button = document.querySelector("[data-admin-theme-toggle]");
    if (!button) {
      button = document.createElement("button");
      button.type = "button";
      button.className = "admin-theme-toggle";
      button.dataset.adminThemeToggle = "";
      const header = document.querySelector("body > header, body > div > header, main > header");
      (header || document.body).append(button);
    }

    applyTheme(root.dataset.adminTheme || readTheme(), button);
    button.addEventListener("click", () => {
      const next = root.dataset.adminTheme === "dark" ? "light" : "dark";
      try {
        window.localStorage.setItem(storageKey, next);
      } catch (_) {}
      applyTheme(next, button);
    });
  };

  applyTheme(readTheme(), null);
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", mountToggle, { once: true });
  } else {
    mountToggle();
  }
};

export default adminTheme;
