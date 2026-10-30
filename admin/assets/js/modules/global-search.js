const adminGlobalSearch = () => {
  "use strict";
  const root = document.querySelector("[data-admin-global-search]");
  if (!root || root.dataset.bound === "true") return;
  root.dataset.bound = "true";
  const input = root.querySelector("input[type=search]");
  const results = root.querySelector("[role=listbox]");
  const apiUrl = document.querySelector('meta[name="admin-api-url"]')?.content || "";
  let timer = 0;
  let requestId = 0;

  const close = () => {
    results.hidden = true;
    input.setAttribute("aria-expanded", "false");
  };
  const open = () => {
    if (!results.childElementCount) return;
    results.hidden = false;
    input.setAttribute("aria-expanded", "true");
  };
  const clear = () => results.replaceChildren();
  const showMessage = message => {
    clear();
    const item = document.createElement("li");
    item.setAttribute("role", "option");
    item.textContent = message;
    results.append(item);
    open();
  };
  const search = async term => {
    const id = ++requestId;
    const url = new URL(apiUrl, window.location.href);
    url.searchParams.set("path", "/admin/capabilities/search");
    url.searchParams.set("q", term);
    try {
      const response = await fetch(url, { credentials: "same-origin", headers: { Accept: "application/json" } });
      const payload = await response.json();
      if (id !== requestId) return;
      if (!response.ok || payload.success !== true) throw new Error(payload.message || "جست‌وجو انجام نشد.");
      const items = payload.data?.items || [];
      clear();
      if (!items.length) {
        showMessage("نتیجه‌ای پیدا نشد.");
        return;
      }
      items.forEach(result => {
        const item = document.createElement("li");
        item.setAttribute("role", "option");
        const link = document.createElement("a");
        link.href = result.href;
        const title = document.createElement("strong");
        title.textContent = result.label;
        const detail = document.createElement("small");
        detail.textContent = result.type + " · " + (result.detail || "");
        link.append(title, detail);
        item.append(link);
        results.append(item);
      });
      open();
    } catch (error) {
      if (id === requestId) showMessage(error.message);
    }
  };

  input.addEventListener("input", () => {
    window.clearTimeout(timer);
    const term = input.value.trim();
    if (term.length < 2) {
      close();
      clear();
      return;
    }
    timer = window.setTimeout(() => search(term), 260);
  });

  input.addEventListener("focus", () => {
    if (input.value.trim().length >= 2 && results.childElementCount) open();
  });
  input.addEventListener("keydown", event => {
    if (event.key === "Escape") {
      close();
      input.blur();
    }
    if (event.key === "ArrowDown" && !results.hidden) {
      event.preventDefault();
      results.querySelector("a")?.focus();
    }
  });
  results.addEventListener("keydown", event => {
    if (event.key === "Escape") {
      close();
      input.focus();
    }
  });
  document.addEventListener("click", event => {
    if (!root.contains(event.target)) close();
  });
  document.addEventListener("keydown", event => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "k") {
      event.preventDefault();
      input.focus();
      input.select();
    }
  });
};

export default adminGlobalSearch;
