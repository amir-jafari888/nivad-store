const adminPreferences = () => {
  "use strict";
  const key = "nivad-admin-preferences";
  const preferenceKey = "interface";
  const defaults = { density: "comfortable", fontScale: "normal", openFirstGroup: false };
  const apiUrl = document.querySelector('meta[name="admin-api-url"]')?.content || "";
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";

  const readLocal = () => {
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

  const api = async (method, body) => {
    if (!apiUrl) throw new Error("Admin API URL is not available.");
    const url = new URL(apiUrl, window.location.href);
    url.searchParams.set("path", "/admin/capabilities/preferences");
    const headers = { Accept: "application/json" };
    const options = { method, credentials: "same-origin", headers };
    if (method !== "GET") {
      headers["Content-Type"] = "application/json";
      headers["X-CSRF-TOKEN"] = csrf;
      options.body = JSON.stringify(body);
    }
    const response = await fetch(url, options);
    const payload = await response.json();
    if (!response.ok || payload.success !== true) throw new Error(payload.message || "Admin preferences request failed.");
    return payload.data || {};
  };

  const readRemote = async () => {
    const data = await api("GET");
    const value = data.items?.[preferenceKey]?.value;
    if (!value || typeof value !== "object") return null;
    return {
      density: value.density === "compact" ? "compact" : "comfortable",
      fontScale: value.fontScale === "large" ? "large" : "normal",
      openFirstGroup: value.openFirstGroup === true
    };
  };

  const saveRemote = preferences => api("PUT", { key: preferenceKey, value: preferences });

  const apply = preferences => {
    const root = document.documentElement;
    root.dataset.adminDensity = preferences.density;
    root.dataset.adminFontScale = preferences.fontScale;
    const groups = document.querySelectorAll(".admin-shared-nav-group, .admin-dashboard-nav-group");
    groups.forEach((group, index) => {
      if (index === 0) group.open = preferences.openFirstGroup;
    });
    document.querySelectorAll("[data-admin-preference]").forEach(control => {
      const name = control.dataset.adminPreference;
      if (!(name in preferences)) return;
      if (control.type === "checkbox") control.checked = Boolean(preferences[name]);
      else control.value = String(preferences[name]);
    });
  };

  const persistLocal = preferences => {
    try {
      window.localStorage.setItem(key, JSON.stringify(preferences));
      return true;
    } catch (_) {
      return false;
    }
  };

  const mount = async () => {
    let current = readLocal();
    apply(current);
    try {
      const remote = await readRemote();
      if (remote) {
        current = remote;
        apply(current);
        persistLocal(current);
      }
    } catch (_) {
      // The local preference cache remains usable when the API is unavailable.
    }

    const form = document.querySelector("[data-admin-preferences-form]");
    if (!form || form.dataset.bound === "true") return;
    form.dataset.bound = "true";
    const feedback = form.querySelector("[data-admin-preferences-feedback]");

    form.addEventListener("submit", async event => {
      event.preventDefault();
      const next = { ...defaults };
      form.querySelectorAll("[data-admin-preference]").forEach(control => {
        const name = control.dataset.adminPreference;
        if (!(name in next)) return;
        next[name] = control.type === "checkbox" ? control.checked : control.value;
      });
      apply(next);
      persistLocal(next);
      try {
        await saveRemote(next);
        if (feedback) feedback.textContent = "تنظیمات در حساب مدیر ذخیره شد.";
      } catch (_) {
        if (feedback) feedback.textContent = "تنظیمات فقط در این مرورگر ذخیره شد؛ ذخیره حساب مدیر ناموفق بود.";
      }
    });

    const reset = form.querySelector("[data-admin-preferences-reset]");
    if (reset) {
      reset.addEventListener("click", async () => {
        apply({ ...defaults });
        persistLocal(defaults);
        try {
          await saveRemote(defaults);
          if (feedback) feedback.textContent = "تنظیمات حساب مدیر به حالت پیش‌فرض برگشت.";
        } catch (_) {
          if (feedback) feedback.textContent = "حالت پیش‌فرض فقط در این مرورگر اعمال شد.";
        }
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
