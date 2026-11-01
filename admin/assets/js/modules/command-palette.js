const adminCommandPalette = () => {
  "use strict";
  if (document.querySelector("[data-admin-command-palette]")) return;
  const apiBase = document.querySelector('meta[name="admin-api-url"]')?.content || "/api/index.php";
  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || "";
  const overlay = document.createElement("section");
  overlay.className = "admin-command-overlay";
  overlay.dataset.adminCommandPalette = "";
  overlay.hidden = true;
  overlay.setAttribute("role", "dialog");
  overlay.setAttribute("aria-modal", "true");
  overlay.setAttribute("aria-label", "فرمان‌های پنل مدیریت");
  const panel = document.createElement("div");
  panel.className = "admin-command-panel";
  const heading = document.createElement("h2");
  heading.textContent = "Command Palette";
  const input = document.createElement("input");
  input.type = "search";
  input.placeholder = "فرمان موردنظر را جست‌وجو کنید…";
  input.setAttribute("aria-label", "جست‌وجوی فرمان");
  const list = document.createElement("ul");
  list.setAttribute("role", "listbox");
  const status = document.createElement("p");
  status.className = "admin-command-status";
  status.setAttribute("role", "status");
  status.setAttribute("aria-live", "polite");
  const closeButton = document.createElement("button");
  closeButton.type = "button";
  closeButton.textContent = "بستن";
  closeButton.addEventListener("click", close);
  panel.append(heading, input, list, status, closeButton);
  overlay.append(panel);
  document.body.append(overlay);
  let commands = [];
  let activeIndex = 0;
  let timer = 0;
  let requestId = 0;
  let opener = null;

  function close() {
    overlay.hidden = true;
    document.body.classList.remove("admin-command-open");
    opener?.focus();
  }
  function open() {
    opener = document.activeElement;
    overlay.hidden = false;
    document.body.classList.add("admin-command-open");
    input.value = "";
    status.textContent = "در حال دریافت فرمان‌های مجاز…";
    input.focus();
    load("");
  }
  function render() {
    list.replaceChildren();
    commands.forEach((command, index) => {
      const item = document.createElement("li");
      item.setAttribute("role", "option");
      item.setAttribute("aria-selected", index === activeIndex ? "true" : "false");
      const button = document.createElement("button");
      button.type = "button";
      button.className = "admin-command-item";
      const label = document.createElement("strong");
      label.textContent = command.label;
      const detail = document.createElement("small");
      detail.textContent = command.detail || "";
      button.append(label, detail);
      button.addEventListener("click", () => execute(command.id));
      item.append(button);
      list.append(item);
    });
    status.textContent = commands.length ? commands.length + " فرمان در دسترس است." : "فرمان مجازی پیدا نشد.";
  }
  async function load(query) {
    const id = ++requestId;
    const url = new URL(apiBase, location.href);
    url.searchParams.set("path", "/admin/capabilities/commands");
    url.searchParams.set("q", query);
    try {
      const response = await fetch(url, { credentials: "same-origin", headers: { Accept: "application/json" } });
      const payload = await response.json();
      if (id !== requestId) return;
      if (!response.ok || payload.success !== true) throw new Error(payload.message || "دریافت فرمان‌ها ناموفق بود.");
      commands = Array.isArray(payload.data?.items) ? payload.data.items : [];
      activeIndex = 0;
      render();
    } catch (error) {
      if (id === requestId) status.textContent = error.message || "خطا در دریافت فرمان‌ها.";
    }
  }
  async function execute(commandId) {
    const url = new URL(apiBase, location.href);
    url.searchParams.set("path", "/admin/capabilities/commands/execute");
    status.textContent = "در حال اجرای فرمان…";
    try {
      const response = await fetch(url, {
        method: "POST", credentials: "same-origin",
        headers: { "Content-Type": "application/json", Accept: "application/json", "X-CSRF-TOKEN": csrf() },
        body: JSON.stringify({ command_id: commandId })
      });
      const payload = await response.json();
      if (!response.ok || payload.success !== true) throw new Error(payload.message || "اجرای فرمان ناموفق بود.");
      if (payload.data?.action === "navigate" && typeof payload.data.href === "string") location.assign(payload.data.href);
      else throw new Error("نوع فرمان پشتیبانی نمی‌شود.");
    } catch (error) {
      status.textContent = error.message || "خطا در اجرای فرمان.";
    }
  }
  input.addEventListener("input", () => {
    clearTimeout(timer);
    timer = setTimeout(() => load(input.value.trim()), 180);
  });
  input.addEventListener("keydown", event => {
    if (event.key === "Escape") { event.preventDefault(); close(); }
    if (event.key === "ArrowDown" || event.key === "ArrowUp") {
      event.preventDefault();
      if (!commands.length) return;
      activeIndex = (activeIndex + (event.key === "ArrowDown" ? 1 : -1) + commands.length) % commands.length;
      render();
      list.querySelectorAll("button")[activeIndex]?.focus();
    }
    if (event.key === "Enter" && commands[activeIndex]) execute(commands[activeIndex].id);
  });
  overlay.addEventListener("click", event => { if (event.target === overlay) close(); });
  document.addEventListener("keydown", event => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "k") {
      event.preventDefault();
      if (overlay.hidden) open(); else close();
    }
  });
  document.addEventListener("admin:command-palette:open", open);
};

export default adminCommandPalette;
