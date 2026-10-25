const STORAGE_KEY = "digital-store-theme";

const theme = () => {
  const buttons = document.querySelectorAll("[data-theme-toggle]");
  const mediaQuery = window.matchMedia?.("(prefers-color-scheme: dark)");

  const readStoredTheme = () => {
    try {
      const stored = localStorage.getItem(STORAGE_KEY);
      return stored === "light" || stored === "dark" ? stored : null;
    } catch {
      return null;
    }
  };

  const systemTheme = () => (mediaQuery?.matches ? "dark" : "light");

  const updateControls = (value) => {
    const isDark = value === "dark";

    buttons.forEach((button) => {
      button.setAttribute("aria-pressed", String(isDark));
      button.setAttribute(
        "aria-label",
        isDark ? "فعال کردن حالت روشن" : "فعال کردن حالت تاریک",
      );

      const label = button.querySelector("[data-theme-label]");
      if (label) label.textContent = isDark ? "حالت روشن" : "حالت تاریک";
    });
  };

  const apply = (value, persist = false) => {
    const normalized = value === "dark" ? "dark" : "light";
    document.documentElement.dataset.theme = normalized;
    updateControls(normalized);

    if (persist) {
      try {
        localStorage.setItem(STORAGE_KEY, normalized);
      } catch {
        // Storage can be unavailable in restricted browsing contexts.
      }
    }
  };

  const initial = readStoredTheme() || document.documentElement.dataset.theme || systemTheme();
  apply(initial);

  buttons.forEach((button) => {
    button.addEventListener("click", () => {
      const current = document.documentElement.dataset.theme || systemTheme();
      apply(current === "dark" ? "light" : "dark", true);
    });
  });

  mediaQuery?.addEventListener?.("change", () => {
    if (!readStoredTheme()) {
      apply(systemTheme());
    }
  });
};

export default theme;
