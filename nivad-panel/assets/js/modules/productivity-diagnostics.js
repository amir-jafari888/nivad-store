const adminProductivityDiagnostics = () => {
  "use strict";
  const root = document.querySelector("[data-admin-productivity-diagnostics]");
  if (!root || root.dataset.bound === "true") return;
  root.dataset.bound = "true";
  const apiBase = root.dataset.apiUrl || document.querySelector('meta[name="admin-api-url"]')?.content || "/api/index.php";
  const feedback = root.querySelector("[data-productivity-feedback]");
  const results = root.querySelector("[data-productivity-results]");
  const refresh = root.querySelector("[data-productivity-refresh]");

  const node = (tag, className, text) => {
    const element = document.createElement(tag);
    if (className) element.className = className;
    if (text !== undefined && text !== null) element.textContent = String(text);
    return element;
  };
  const card = (title, status, message, entries = []) => {
    const section = node("section", "admin-productivity-card");
    const heading = node("div", "admin-productivity-card-heading");
    heading.append(node("h2", "", title), node("span", "admin-productivity-state", status || "unknown"));
    section.append(heading);
    if (message) section.append(node("p", "admin-productivity-message", message));
    if (entries.length) {
      const list = node("ul", "admin-productivity-list");
      entries.forEach(entry => list.append(node("li", "", entry)));
      section.append(list);
    }
    return section;
  };
  const render = data => {
    results.replaceChildren();
    const backup = data.backup || {};
    results.append(card("آگاهی از پشتیبان‌گیری", backup.status, backup.message,
      Array.isArray(backup.backup_tables) ? backup.backup_tables.map(name => "جدول شناسایی‌شده: " + name).concat(["آزمون بازیابی: " + (backup.restore_tested ? "انجام شده" : "تأیید نشده")]) : ["وجود Backup و موفقیت Restore قابل اثبات نیست."]));

    const queues = data.queues || {};
    const queueEntries = Array.isArray(queues.items) ? queues.items.map(item =>
      (item.label || item.table || "Queue") + " — " + (item.state || "unknown") +
      (item.stale_count === null || item.stale_count === undefined ? "" : " · موارد معطل: " + item.stale_count)
    ) : [];
    results.append(card("پایش صف‌های موجود", queues.status, queues.message || "وضعیت صف‌ها از منابع موجود خوانده می‌شود.", queueEntries));

    const webhooks = data.webhooks || {};
    results.append(card("پایش Webhook", webhooks.status, webhooks.message,
      Array.isArray(webhooks.tables) ? webhooks.tables.map(name => "جدول: " + name) : []));

    const gateway = data.payment_gateway || {};
    const paymentEntries = Array.isArray(gateway.attempts) ? gateway.attempts.map(item =>
      String(item.gateway || "درگاه نامشخص") + " / " + String(item.status || "وضعیت نامشخص") +
      " — " + String(item.total || 0) + " تلاش"
    ) : [];
    results.append(card("تله‌متری درگاه پرداخت", gateway.status, gateway.message, paymentEntries));

    const inventory = data.inventory || {};
    const inventoryEntries = Array.isArray(inventory.items) ? inventory.items.map(item =>
      "Variant #" + item.variant_id + " — " + item.anomaly_type +
      " · موجودی: " + item.quantity + " · رزرو: " + item.reserved_quantity
    ) : [];
    results.append(card("ناهنجاری موجودی", inventory.status, inventory.message, inventoryEntries));

    const abuse = data.customer_abuse || {};
    results.append(card("نشانه‌های سوءاستفاده مشتری", abuse.status, abuse.message,
      Array.isArray(abuse.items) ? abuse.items.map(item => String(item.label || item.type || "سیگنال")) : []));
  };

  const load = async () => {
    refresh.disabled = true;
    feedback.textContent = "در حال دریافت اطلاعات عملیاتی…";
    const url = new URL(apiBase, location.href);
    url.searchParams.set("path", "/admin/capabilities/productivity-diagnostics");
    try {
      const response = await fetch(url, { credentials: "same-origin", headers: { Accept: "application/json" } });
      const payload = await response.json();
      if (!response.ok || payload.success !== true) throw new Error(payload.message || "دریافت اطلاعات عملیاتی ناموفق بود.");
      render(payload.data || {});
      feedback.textContent = "اطلاعات از منابع فعلی سیستم دریافت شد؛ موارد نامشخص به‌عنوان سالم نمایش داده نمی‌شوند.";
    } catch (error) {
      feedback.textContent = error.message || "خطا در دریافت اطلاعات عملیاتی.";
    } finally {
      refresh.disabled = false;
    }
  };
  refresh.addEventListener("click", load);
  load();
};

export default adminProductivityDiagnostics;
