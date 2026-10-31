const adminCapabilityCenter = () => {
  "use strict";
  const root = document.querySelector("[data-admin-capabilities]");
  if (!root || root.dataset.bound === "true") return;
  root.dataset.bound = "true";
  const apiUrl = root.dataset.apiUrl;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || "";
  const feedback = root.querySelector("[data-capability-feedback]");
  const apiPath = "/admin/capabilities";

  const endpoint = (path, query = {}) => {
    const url = new URL(apiUrl, window.location.href);
    url.searchParams.set("path", path.startsWith("/admin/") ? path : apiPath + path);
    Object.entries(query).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== "") url.searchParams.set(key, String(value));
    });
    return url.toString();
  };

  const request = async (path, options = {}, query = {}) => {
    const headers = { Accept: "application/json", ...(options.headers || {}) };
    if (options.method && options.method !== "GET") {
      headers["X-CSRF-TOKEN"] = csrf;
      headers["Content-Type"] = "application/json";
    }
    const response = await fetch(endpoint(path, query), { credentials: "same-origin", ...options, headers });
    let payload;
    try { payload = await response.json(); } catch (_) { throw new Error("پاسخ سرور JSON معتبر نیست."); }
    if (!response.ok || payload.success !== true) throw new Error(payload.message || "خطای درخواست (" + response.status + ")");
    return payload.data || {};
  };

  const setFeedback = (message, isError = false) => {
    if (!feedback) return;
    feedback.textContent = message;
    feedback.dataset.state = isError ? "error" : "success";
  };

  const node = (tag, text, className) => {
    const el = document.createElement(tag);
    if (text !== undefined && text !== null) el.textContent = String(text);
    if (className) el.className = className;
    return el;
  };

  const renderRows = (name, rows, columns) => {
    const target = root.querySelector('[data-capability-list="' + name + '"]');
    if (!target) return;
    target.replaceChildren();
    if (!rows.length) {
      target.append(node("p", "موردی برای نمایش وجود ندارد.", "admin-capability-empty"));
      return;
    }
    rows.forEach(row => {
      const article = node("article", undefined, "admin-capability-row");
      article.append(node("strong", columns.title(row)));
      const details = columns.details(row);
      if (details) article.append(node("p", details));
      if (columns.action) {
        const action = columns.action(row);
        if (action) article.append(action);
      }
      target.append(article);
    });
  };

  const loadNotifications = async () => {
    const data = await request("/notifications");
    renderRows("notifications", data.items || [], {
      title: row => row.title + " · " + row.severity,
      details: row => row.message + " · " + row.created_at + (Number(row.is_read) ? " · خوانده‌شده" : " · خوانده‌نشده"),
      action: row => {
        if (Number(row.is_read)) return null;
        const button = node("button", "علامت‌گذاری به‌عنوان خوانده‌شده");
        button.type = "button";
        button.addEventListener("click", async () => {
          try { await request("/notifications/" + row.id + "/read", { method: "PATCH", body: "{}" }); await loadNotifications(); }
          catch (error) { setFeedback(error.message, true); }
        });
        return button;
      }
    });
  };

  const loadIncidents = async () => {
    const data = await request("/incidents");
    let activeAdmins = [];
    try {
      const adminData = await request("/admin/access/admins");
      activeAdmins = (adminData.items || []).filter(admin => admin.status === "active");
    } catch (_) {
      activeAdmins = [];
    }
    renderRows("incidents", data.items || [], {
      title: row => row.title + " · " + row.severity,
      details: row => row.source + " · " + row.status + " · تعداد تکرار: " + row.occurrence_count + " · آخرین مشاهده: " + row.last_seen_at,
      action: row => {
        const wrap = node("div", undefined, "admin-capability-actions");
        const select = document.createElement("select");
        [["open","باز"],["acknowledged","در حال بررسی"],["resolved","حل‌شده"],["ignored","نادیده‌گرفته‌شده"]].forEach(([value,label]) => {
          const option = node("option", label); option.value = value; option.selected = value === row.status; select.append(option);
        });
        let assignee = null;
        if (activeAdmins.length) {
          assignee = document.createElement("select");
          const none = node("option", "بدون مسئول"); none.value = ""; assignee.append(none);
          activeAdmins.forEach(admin => {
            const option = node("option", admin.first_name + " " + admin.last_name + " · " + admin.username);
            option.value = admin.id; option.selected = Number(row.assigned_admin_id) === Number(admin.id); assignee.append(option);
          });
        }
        const button = node("button", "ثبت وضعیت"); button.type = "button";
        button.addEventListener("click", async () => {
          try {
            await request("/incidents/" + row.id, { method: "PATCH", body: JSON.stringify({ status: select.value, assigned_admin_id: assignee ? (Number(assignee.value) || null) : (row.assigned_admin_id || null) }) });
            await loadIncidents(); setFeedback("وضعیت رخداد به‌روزرسانی شد.");
          } catch (error) { setFeedback(error.message, true); }
        });
        wrap.append(select);
        if (assignee) wrap.append(assignee);
        wrap.append(button); return wrap;
      }
    });
  };

  const loadTickets = async () => {
    const filterForm = root.querySelector("[data-support-filters]");
    const filters = filterForm ? Object.fromEntries(new FormData(filterForm).entries()) : {};
    const data = await request("/tickets", {}, filters);
    let activeAdmins = [];
    try {
      const adminData = await request("/admin/access/admins");
      activeAdmins = (adminData.items || []).filter(admin => admin.status === "active");
    } catch (_) {
      activeAdmins = [];
    }
    renderRows("tickets", data.items || [], {
      title: row => row.ticket_number + " · " + row.subject,
      details: row => "وضعیت: " + row.status + " · اولویت: " + row.priority + " · مشتری: " + (row.user_id || "ثبت نشده") + " · پاسخ اولیه: " + (row.first_response_at || "در انتظار پاسخ") + " · آخرین تغییر: " + row.updated_at,
      action: row => {
        const wrap = node("div", undefined, "admin-capability-ticket-detail");
        const controls = node("div", undefined, "admin-capability-actions");
        const status = document.createElement("select");
        [["open","باز"],["pending_customer","منتظر مشتری"],["pending_admin","منتظر مدیر"],["resolved","حل‌شده"],["closed","بسته"]].forEach(([value,label]) => {
          const option = node("option", label); option.value = value; option.selected = value === row.status; status.append(option);
        });
        const priority = document.createElement("select");
        [["low","کم"],["normal","عادی"],["high","زیاد"],["urgent","فوری"]].forEach(([value,label]) => {
          const option = node("option", label); option.value = value; option.selected = value === row.priority; priority.append(option);
        });
        let assignee = null;
        if (activeAdmins.length) {
          assignee = document.createElement("select");
          const none = node("option", "بدون مسئول"); none.value = ""; assignee.append(none);
          activeAdmins.forEach(admin => {
            const option = node("option", admin.first_name + " " + admin.last_name + " · " + admin.username);
            option.value = admin.id; option.selected = Number(row.assigned_admin_id) === Number(admin.id); assignee.append(option);
          });
        }
        const update = node("button", "ثبت وضعیت و اولویت"); update.type = "button";
        update.addEventListener("click", async () => {
          try {
            await request("/tickets/" + row.id, { method: "PATCH", body: JSON.stringify({
              status: status.value, priority: priority.value, assigned_admin_id: assignee ? (Number(assignee.value) || null) : (row.assigned_admin_id || null)
            }) });
            await loadTickets(); setFeedback("تیکت به‌روزرسانی شد.");
          } catch (error) { setFeedback(error.message, true); }
        });
        controls.append(status, priority);
        if (assignee) controls.append(assignee);
        controls.append(update);
        const details = document.createElement("details");
        const summary = node("summary", "مشاهده پیام‌ها و پاسخ");
        const content = node("div", undefined, "admin-capability-ticket-detail");
        summary.addEventListener("click", async () => {
          if (details.dataset.loaded === "true") return;
          try {
            const result = await request("/tickets/" + row.id);
            (result.ticket.messages || []).forEach(message => {
              content.append(node("p", message.sender_type + (Number(message.is_internal) ? " · یادداشت داخلی" : "") + ": " + message.message));
            });
            content.append(node("h4", "تاریخچه تغییر وضعیت"));
            const history = result.ticket.status_history || [];
            if (!history.length) content.append(node("p", "تاریخچه‌ای ثبت نشده است."));
            history.forEach(item => {
              const actor = item.actor_type === "admin" ? "مدیر " + (item.first_name || "") + " " + (item.last_name || "") : item.actor_type === "customer" ? "مشتری" : "سیستم";
              content.append(node("p", (item.from_status || "شروع") + " ← " + item.to_status + " · " + actor + " · " + item.changed_at + (item.note ? " · " + item.note : "")));
            });
            const form = document.createElement("form");
            form.className = "admin-capability-inline-form";
            const textarea = document.createElement("textarea"); textarea.name = "message"; textarea.required = true; textarea.placeholder = "پاسخ یا یادداشت داخلی";
            const internalLabel = node("label", "یادداشت داخلی");
            const internal = document.createElement("input"); internal.type = "checkbox"; internal.name = "is_internal"; internal.value = "1";
            internalLabel.prepend(internal);
            const submit = node("button", "ارسال پیام"); submit.type = "submit";
            form.append(textarea, internalLabel, submit);
            form.addEventListener("submit", async event => {
              event.preventDefault();
              try {
                await request("/tickets/" + row.id + "/messages", { method: "POST", body: JSON.stringify({ message: textarea.value, is_internal: internal.checked }) });
                details.dataset.loaded = "false"; content.replaceChildren(); await loadTickets(); setFeedback("پیام ثبت شد.");
              } catch (error) { setFeedback(error.message, true); }
            });
            content.append(form); details.dataset.loaded = "true";
          } catch (error) { setFeedback(error.message, true); }
        });
        details.append(summary, content);
        wrap.append(controls, details);
        return wrap;
      }
    });
  };

  const loadAnalytics = async () => {
    const to = new Date();
    const from = new Date(); from.setDate(from.getDate() - 29);
    const iso = date => date.getFullYear() + "-" + String(date.getMonth()+1).padStart(2,"0") + "-" + String(date.getDate()).padStart(2,"0");
    const data = await request("/product-analytics", {}, { from: iso(from), to: iso(to) });
    renderRows("analytics", data.items || [], {
      title: row => (row.product_name || "رویداد عمومی") + " · " + row.event_name,
      details: row => "تعداد رویداد: " + row.event_count + " · نشست‌ها: " + row.sessions + " · بازدیدکنندگان یکتا: " + row.visitors
    });
    const target = root.querySelector('[data-capability-list="analytics"]');
    const summary = data.summary || {};
    if (target) {
      const line = [
        "بازدیدکنندگان یکتا: " + (summary.unique_viewers ?? 0),
        "شروع Checkout: " + (summary.checkout_sessions ?? 0),
        "پرداخت موفق: " + (summary.purchase_sessions ?? 0),
        "نمایش جستجو: " + (summary.search_impressions ?? 0) + " · کلیک جستجو: " + (summary.search_clicks ?? 0),
        "نمایش مکمل: " + (summary.related_impressions ?? 0) + " · کلیک مکمل: " + (summary.related_clicks ?? 0),
        "نمایش گزینه Discovery: " + (summary.discovery_option_impressions ?? 0) + " · انتخاب: " + (summary.discovery_selections ?? 0),
        "نرخ کلیک جستجو: " + (summary.search_click_rate === null ? "داده کافی نیست" : summary.search_click_rate + "%"),
        "نرخ کلیک محصولات مرتبط: " + (summary.related_click_rate === null ? "داده کافی نیست" : summary.related_click_rate + "%"),
        "نرخ انتخاب گزینه Discovery: " + (summary.discovery_selection_rate === null ? "داده کافی نیست" : summary.discovery_selection_rate + "%"),
        "تبدیل نشست‌های بازدید محصول به خرید: " + (summary.purchase_conversion_rate === null ? "داده کافی نیست" : summary.purchase_conversion_rate + "%"),
        "تبدیل Checkout به پرداخت موفق: " + (summary.checkout_conversion_rate === null ? "داده کافی نیست" : summary.checkout_conversion_rate + "%"),
        "بازدید مقایسه: " + (summary.comparison_views ?? 0)
      ].join(" · ");
      target.prepend(node("p", line));
    }
  };

  const loadHealth = async () => {
    const data = await request("/health");
    const checks = Object.entries(data.checks || {}).map(([name, value]) => ({
      name, status: value.status, detail: value.version || value.value || ""
    }));
    renderRows("health", checks, {
      title: row => row.name + " · " + row.status,
      details: row => row.detail
    });
    const target = root.querySelector('[data-capability-list="health"]');
    if (target) target.prepend(node("strong", "وضعیت کلی: " + data.status + " · " + data.checked_at));
  };

  const loadPreferences = async () => {
    const data = await request("/preferences");
    const rows = Object.entries(data.items || {}).map(([key, item]) => ({ key, ...item }));
    renderRows("preferences", rows, { title: row => row.key, details: row => JSON.stringify(row.value) });
  };

  const loadSavedViews = async () => {
    const form = root.querySelector('[data-capability-form="saved-view"]');
    const moduleName = form?.elements.module.value || "orders";
    const data = await request("/saved-views", {}, { module: moduleName });
    renderRows("saved-views", data.items || [], {
      title: row => row.name + " · " + row.module + (Number(row.is_default) ? " · پیش‌فرض" : ""),
      details: row => JSON.stringify(row.filters),
      action: row => {
        const button = node("button", "حذف نما"); button.type = "button";
        button.addEventListener("click", async () => {
          try { await request("/saved-views/" + row.id, { method: "DELETE", body: "{}" }); await loadSavedViews(); }
          catch (error) { setFeedback(error.message, true); }
        });
        return button;
      }
    });
  };

  const load = async name => {
    const loaders = {
      notifications: loadNotifications, incidents: loadIncidents, tickets: loadTickets,
      analytics: loadAnalytics, health: loadHealth, preferences: loadPreferences, "saved-views": loadSavedViews
    };
    if (!loaders[name]) return;
    try { await loaders[name](); setFeedback("اطلاعات با موفقیت دریافت شد."); }
    catch (error) { setFeedback(error.message, true); }
  };

  root.querySelectorAll("[data-capability-refresh]").forEach(button => {
    button.addEventListener("click", () => load(button.dataset.capabilityRefresh));
  });

  const bindForm = (name, handler) => {
    const form = root.querySelector('[data-capability-form="' + name + '"]');
    if (!form) return;
    form.addEventListener("submit", async event => {
      event.preventDefault();
      const button = form.querySelector('[type="submit"]');
      if (button) button.disabled = true;
      try { await handler(new FormData(form)); form.reset(); setFeedback("عملیات با موفقیت انجام شد."); }
      catch (error) { setFeedback(error.message, true); }
      finally { if (button) button.disabled = false; }
    });
  };

  bindForm("notification", async form => {
    await request("/notifications", { method: "POST", body: JSON.stringify({
      title: form.get("title"), message: form.get("message"),
      category: form.get("category"), severity: form.get("severity")
    }) });
    await loadNotifications();
  });

  bindForm("ticket", async form => {
    await request("/tickets", { method: "POST", body: JSON.stringify({
      user_id: form.get("user_id") || null, subject: form.get("subject"),
      priority: form.get("priority"), message: form.get("message")
    }) });
    await loadTickets();
  });

  const supportRange = () => {
    const form = root.querySelector("[data-support-range]");
    if (form && (!form.elements.from.value || !form.elements.to.value)) {
      const to = new Date();
      const from = new Date(); from.setDate(from.getDate() - 29);
      const iso = date => date.getFullYear() + "-" + String(date.getMonth() + 1).padStart(2, "0") + "-" + String(date.getDate()).padStart(2, "0");
      form.elements.from.value ||= iso(from);
      form.elements.to.value ||= iso(to);
    }
    return form ? { from: form.elements.from.value, to: form.elements.to.value } : {};
  };

  const loadSupportMetrics = async () => {
    const data = await request("/support/metrics", {}, supportRange());
    const summary = data.summary || {};
    const target = root.querySelector('[data-capability-list="support-metrics"]');
    if (target) {
      target.replaceChildren(
        node("p", "کل تیکت‌ها: " + (summary.total_tickets ?? 0)),
        node("p", "تیکت‌های فعال: " + (summary.active_tickets ?? 0)),
        node("p", "تیکت‌های تکمیل‌شده: " + (summary.completed_tickets ?? 0)),
        node("p", "میانگین پاسخ اولیه (دقیقه): " + (summary.avg_first_response_minutes === null ? "داده کافی نیست" : Number(summary.avg_first_response_minutes || 0).toFixed(1))),
        node("p", "در انتظار پاسخ اولیه: " + (summary.awaiting_first_response ?? 0)),
        node("p", "اولویت‌های دارای SLA فعال: " + (data.policies_configured ?? 0))
      );
      (data.sla || []).forEach(row => target.append(
        node("p", row.priority + " · پاسخ اولیه: " + row.response_within_sla + " در محدوده / " + row.response_breached + " نقض · حل تیکت: " + row.resolution_within_sla + " در محدوده / " + row.resolution_breached + " نقض")
      ));
    }
  };

  const loadSupportReports = async () => {
    const data = await request("/support/reports", {}, supportRange());
    renderRows("support-reports", data.items || [], {
      title: row => row.priority + " · " + row.status + " · " + row.ticket_count + " تیکت",
      details: row => "میانگین پاسخ اولیه (دقیقه): " + (row.avg_first_response_minutes === null ? "داده کافی نیست" : Number(row.avg_first_response_minutes || 0).toFixed(1))
    });
    renderRows("support-assignees", data.by_assignee || [], {
      title: row => (row.assigned_admin_name || "بدون مسئول") + " · " + row.ticket_count + " تیکت",
      details: row => "تکمیل‌شده: " + row.completed_count + " · میانگین پاسخ: " + (row.avg_first_response_minutes === null ? "داده کافی نیست" : Number(row.avg_first_response_minutes || 0).toFixed(1) + " دقیقه") + " · میانگین حل: " + (row.avg_resolution_minutes === null ? "داده کافی نیست" : Number(row.avg_resolution_minutes || 0).toFixed(1) + " دقیقه")
    });
  };

  const loadSupportSla = async () => {
    const data = await request("/support/sla");
    renderRows("support-sla", data.items || [], {
      title: row => row.priority + " · " + (Number(row.is_active) === 1 ? "فعال" : "تعریف‌نشده"),
      details: row => "پاسخ اولیه: " + (row.first_response_minutes ?? "—") + " دقیقه · حل: " + (row.resolution_minutes ?? "—") + " دقیقه · آخرین تغییر: " + (row.updated_at || "—")
    });
  };

  bindForm("support-sla", async form => {
    const priority = String(form.get("priority"));
    await request("/support/sla/" + encodeURIComponent(priority), {
      method: "PUT",
      body: JSON.stringify({
        first_response_minutes: Number(form.get("first_response_minutes")),
        resolution_minutes: Number(form.get("resolution_minutes"))
      })
    });
    await loadSupportSla();
    await loadSupportMetrics();
  });

  bindForm("preference", async form => {
    let value;
    try { value = JSON.parse(String(form.get("value"))); }
    catch (_) { throw new Error("مقدار تنظیم باید JSON معتبر باشد."); }
    await request("/preferences", { method: "PUT", body: JSON.stringify({ key: form.get("key"), value }) });
    await loadPreferences();
  });

  bindForm("saved-view", async form => {
    let filters;
    try { filters = JSON.parse(String(form.get("filters"))); }
    catch (_) { throw new Error("فیلترها باید JSON معتبر باشند."); }
    await request("/saved-views", { method: "POST", body: JSON.stringify({
      module: form.get("module"), name: form.get("name"), filters,
      is_default: form.get("is_default") === "1"
    }) });
    await loadSavedViews();
  });

  bindForm("revision", async form => {
    let snapshot;
    try { snapshot = JSON.parse(String(form.get("snapshot"))); }
    catch (_) { throw new Error("Snapshot باید JSON معتبر باشد."); }
    await request("/revisions", { method: "POST", body: JSON.stringify({
      entity_type: form.get("entity_type"), entity_id: Number(form.get("entity_id")),
      snapshot, change_summary: form.get("change_summary")
    }) });
  });

  bindForm("revision-search", async form => {
    const data = await request("/revisions", {}, {
      entity_type: form.get("entity_type"), entity_id: form.get("entity_id")
    });
    renderRows("revisions", data.items || [], {
      title: row => "نسخه " + row.revision_number + " · " + row.created_at,
      details: row => (row.change_summary || "بدون توضیح") + " · مدیر: " + (row.actor_admin_id || "نامشخص")
    });
  });

  const supportFilters = root.querySelector("[data-support-filters]");
  if (supportFilters) {
    supportFilters.addEventListener("submit", event => { event.preventDefault(); loadTickets().catch(error => setFeedback(error.message, true)); });
    supportFilters.addEventListener("reset", () => window.setTimeout(() => loadTickets().catch(error => setFeedback(error.message, true)), 0));
  }
  const supportRangeForm = root.querySelector("[data-support-range]");
  if (supportRangeForm) {
    supportRange();
    supportRangeForm.addEventListener("submit", event => {
      event.preventDefault();
      Promise.all([loadSupportMetrics(), loadSupportReports()]).catch(error => setFeedback(error.message, true));
    });
  }

  const supportLoaders = {
    "support-sla": loadSupportSla,
    "support-metrics": loadSupportMetrics,
    "support-reports": loadSupportReports
  };
  ["notifications", "incidents", "tickets", "analytics", "health", "preferences", "saved-views"].forEach(load);
  Object.entries(supportLoaders).forEach(([name, loader]) => {
    try { loader().catch(error => setFeedback(error.message, true)); }
    catch (error) { setFeedback(error.message, true); }
  });
};

export default adminCapabilityCenter;
