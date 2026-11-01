const adminSavedViews = () => {
  "use strict";
  const apiBase = document.querySelector('meta[name="admin-api-url"]')?.content || "/api/index.php";
  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || "";
  const moduleForPath = () => {
    const path = location.pathname.toLowerCase();
    if (path.includes("/products")) return "products";
    if (path.includes("/categories")) return "categories";
    if (path.includes("/orders")) return "orders";
    if (path.includes("/users")) return "customers";
    if (path.includes("/inventory")) return "inventory";
    if (path.includes("/payments")) return "payments";
    if (path.includes("/capabilities/index")) return "tickets";
    return null;
  };
  const module = document.querySelector("[data-admin-saved-view-module]")?.dataset.adminSavedViewModule || moduleForPath();
  if (!module) return;
  const toolbar = document.querySelector(".admin-data-toolbar");
  const search = document.querySelector(".admin-data-search");
  const statusFilter = document.querySelector(".admin-data-filter");
  if (!toolbar || !search) return;
  const select = document.createElement("select");
  select.className = "admin-data-filter admin-saved-view-select";
  select.setAttribute("aria-label", "نماهای ذخیره‌شده");
  const initial = document.createElement("option");
  initial.value = "";
  initial.textContent = "نماهای ذخیره‌شده";
  select.append(initial);
  const loadButton = document.createElement("button");
  loadButton.type = "button";
  loadButton.className = "admin-data-button admin-data-button-secondary";
  loadButton.textContent = "اعمال نما";
  const saveButton = document.createElement("button");
  saveButton.type = "button";
  saveButton.className = "admin-data-button";
  saveButton.textContent = "ذخیره نمای فعلی";
  toolbar.append(select, loadButton, saveButton);
  const endpoint = (path) => {
    const url = new URL(apiBase, location.href);
    url.searchParams.set("path", path);
    return url;
  };
  let views = [];
  const apply = view => {
    const filters = Array.isArray(view.filters) ? view.filters : [];
    const textFilter = filters.find(item => item.field === "search_term" && item.operator === "contains");
    const status = filters.find(item => item.field === "status" && item.operator === "eq");
    search.value = typeof textFilter?.value === "string" ? textFilter.value : "";
    if (statusFilter) statusFilter.value = typeof status?.value === "string" ? status.value : "";
    search.dispatchEvent(new Event("input", { bubbles: true }));
    statusFilter?.dispatchEvent(new Event("change", { bubbles: true }));
  };
  const load = async () => {
    try {
      const url = endpoint("/admin/capabilities/saved-views");
      url.searchParams.set("module", module);
      const response = await fetch(url, { credentials: "same-origin", headers: { Accept: "application/json" } });
      const payload = await response.json();
      if (!response.ok || payload.success !== true) throw new Error(payload.message || "نماها بارگذاری نشدند.");
      views = Array.isArray(payload.data?.items) ? payload.data.items : [];
      select.replaceChildren(initial);
      views.forEach(view => {
        const option = document.createElement("option");
        option.value = String(view.id);
        option.textContent = view.name + (Number(view.is_default) === 1 ? " (پیش‌فرض)" : "");
        select.append(option);
      });
      const defaultView = views.find(view => Number(view.is_default) === 1);
      if (defaultView) { select.value = String(defaultView.id); apply(defaultView); }
    } catch (error) {
      select.title = error.message || "خطا در دریافت نماها.";
    }
  };
  loadButton.addEventListener("click", () => {
    const view = views.find(item => String(item.id) === select.value);
    if (view) apply(view);
  });
  saveButton.addEventListener("click", async () => {
    const name = window.prompt("نام نمای ذخیره‌شده:");
    if (!name || !name.trim()) return;
    const filters = [];
    if (search.value.trim()) filters.push({ field: "search_term", operator: "contains", value: search.value.trim() });
    if (statusFilter?.value) filters.push({ field: "status", operator: "eq", value: statusFilter.value });
    try {
      const response = await fetch(endpoint("/admin/capabilities/saved-views"), {
        method: "POST", credentials: "same-origin",
        headers: { "Content-Type": "application/json", Accept: "application/json", "X-CSRF-TOKEN": csrf() },
        body: JSON.stringify({ module, name: name.trim(), filters, sort: null, is_default: false })
      });
      const payload = await response.json();
      if (!response.ok || payload.success !== true) throw new Error(payload.message || "ذخیره نما ناموفق بود.");
      await load();
      select.value = String(payload.data?.id || "");
    } catch (error) {
      window.alert(error.message || "خطا در ذخیره نما.");
    }
  });
  load();
};

export default adminSavedViews;
