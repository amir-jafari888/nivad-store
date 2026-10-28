const adminPreferences = () => {
  "use strict";

  const key = "nivad-admin-preferences";
  const defaults = { density: "comfortable", fontScale: "normal", openFirstGroup: false };

  const read = () => {
    try {
      const saved = JSON.parse(window.localStorage.getItem(key) || "null");
      if (!saved || typeof saved !== "object") return { ...defaults };
      return {
        density: saved.density === "compact" ? "compact" : "comfortable",
        fontScale: saved.fontScale === "large" ? "large" : "normal",
        openFirstGroup: saved.openFirstGroup === true
      };
    } catch (_) {
      return { ...defaults };
    }
  };

  const apply = (preferences) => {
    const root = document.documentElement;
    root.dataset.adminDensity = preferences.density;
    root.dataset.adminFontScale = preferences.fontScale;

    const groups = document.querySelectorAll(".admin-shared-nav-group, .admin-dashboard-nav-group");
    groups.forEach((group, index) => {
      if (index === 0) group.open = preferences.openFirstGroup;
    });

    document.querySelectorAll("[data-admin-preference]").forEach((control) => {
      const name = control.dataset.adminPreference;
      if (!(name in preferences)) return;
      if (control.type === "checkbox") control.checked = Boolean(preferences[name]);
      else control.value = String(preferences[name]);
    });
  };

  const persist = (preferences) => {
    try {
      window.localStorage.setItem(key, JSON.stringify(preferences));
      return true;
    } catch (_) {
      return false;
    }
  };

  const mount = () => {
    const current = read();
    apply(current);

    const form = document.querySelector("[data-admin-preferences-form]");
    if (!form || form.dataset.bound === "true") return;
    form.dataset.bound = "true";

    const feedback = form.querySelector("[data-admin-preferences-feedback]");
    form.addEventListener("submit", (event) => {
      event.preventDefault();
      const next = { ...defaults };
      form.querySelectorAll("[data-admin-preference]").forEach((control) => {
        const name = control.dataset.adminPreference;
        if (!(name in next)) return;
        next[name] = control.type === "checkbox" ? control.checked : control.value;
      });
      if (persist(next)) {
        apply(next);
        if (feedback) feedback.textContent = "تنظیمات ذخیره شد.";
      } else if (feedback) {
        feedback.textContent = "ذخیره‌سازی مرورگر در دسترس نیست؛ تنظیمات فقط تا پایان این صفحه اعمال می‌شوند.";
        apply(next);
      }
    });

    const reset = form.querySelector("[data-admin-preferences-reset]");
    if (reset) {
      reset.addEventListener("click", () => {
        try { window.localStorage.removeItem(key); } catch (_) {}
        apply({ ...defaults });
        if (feedback) feedback.textContent = "تنظیمات به حالت پیش‌فرض برگشت.";
      });
    }
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", mount, { once: true });
  } else {
    mount();
  }
};

export default adminPreferences;
